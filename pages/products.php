<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Produk Investasi';
$currentPage = 'products';

// Handle buy AJAX
if ($_SERVER['REQUEST_METHOD']==='POST' && isAjax()) {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'buy') {
        $productId   = (int)($_POST['product_id'] ?? 0);
        $voucherCode = sanitize($_POST['voucher'] ?? '');

        $p = dbQuery("SELECT * FROM products WHERE id=$productId AND status=1 LIMIT 1");
        if (!$p || $p->num_rows === 0) jsonResponse(['success'=>false,'message'=>'Produk tidak ditemukan']);
        $prod = $p->fetch_assoc();

        // Check quota
        if ($prod['quota'] > 0 && $prod['quota_used'] >= $prod['quota'])
            jsonResponse(['success'=>false,'message'=>'Kuota produk ini sudah habis!']);

        // Check max buy
        if ($prod['max_buy'] > 0) {
            $bought = dbQuery("SELECT COUNT(*) as c FROM user_packages WHERE user_id=$uid AND product_id=$productId");
            if ($bought && (int)$bought->fetch_assoc()['c'] >= $prod['max_buy'])
                jsonResponse(['success'=>false,'message'=>'Kamu sudah mencapai batas pembelian produk ini ('.$prod['max_buy'].'x)']);
        }

        $price = (float)$prod['price'];
        // Flash sale
        if ($prod['flash_sale'] && $prod['flash_start'] && $prod['flash_end']) {
            $now = date('Y-m-d H:i:s');
            if ($now >= $prod['flash_start'] && $now <= $prod['flash_end'])
                $price = (float)$prod['flash_price'];
        }

        // Voucher
        $discount = 0;
        if ($voucherCode) {
            $vc = dbEscape($voucherCode);
            $vr = dbQuery("SELECT * FROM vouchers WHERE code='$vc' AND type='product' AND status=1 AND (expired_at IS NULL OR expired_at>NOW()) LIMIT 1");
            if ($vr && $vr->num_rows > 0) {
                $vd = $vr->fetch_assoc();
                if ((int)$vd['min_vip'] > (int)$user['vip_level'])
                    jsonResponse(['success'=>false,'message'=>'Voucher ini untuk VIP '.$vd['min_vip'].'+. Upgrade VIP kamu!']);
                $used = db()->prepare("SELECT id FROM voucher_uses WHERE voucher_id=? AND user_id=? LIMIT 1");
                $used->bind_param('ii',(int)$vd['id'],$uid); $used->execute();
                if ($used->get_result()->num_rows > 0) jsonResponse(['success'=>false,'message'=>'Voucher sudah digunakan']);
                $used->close();
                $discount = round($price * (float)$vd['discount_percent'] / 100, 2);
            }
        }

        $finalPrice = max(0, $price - $discount);

        // Balance check - use bonus first
        $bonusBal = (float)$user['bonus_balance'];
        $mainBal  = (float)$user['balance'];
        $totalBal = $mainBal;

        if ($totalBal < $finalPrice) jsonResponse(['success'=>false,'message'=>'Saldo tidak cukup. Isi ulang dulu.']);

        // Deduct balance
        dbQuery("UPDATE users SET balance=balance-$finalPrice WHERE id=$uid AND balance>=$finalPrice");
        if (db()->affected_rows === 0) jsonResponse(['success'=>false,'message'=>'Saldo tidak cukup']);

        // Deduct bonus
        if ($bonusBal > 0) {
            $bonusUsed = min($bonusBal, $finalPrice);
            deductBonusBalance($uid, $bonusUsed);
        }

        // Create package
        $expAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        $profitDay = (float)$prod['profit_per_day'];
        $stmt = db()->prepare("INSERT INTO user_packages (user_id,product_id,buy_price,profit_per_day,duration_days,expired_at) VALUES (?,?,?,?,30,?)");
        $stmt->bind_param('iidds',$uid,$productId,$finalPrice,$profitDay,$expAt);
        $stmt->execute();
        $pkgId = db()->insert_id;
        $stmt->close();

        // Update product stats
        dbQuery("UPDATE products SET quota_used=quota_used+1,total_buyers=total_buyers+1 WHERE id=$productId");

        // Transaction log
        addTransaction($uid,'purchase',-$finalPrice,'Pembelian produk: '.$prod['name'],$pkgId);

        // Voucher use
        if ($voucherCode && $discount > 0) {
            $vr2 = dbQuery("SELECT id FROM vouchers WHERE code='".dbEscape($voucherCode)."' LIMIT 1");
            if ($vr2 && $vr2->num_rows > 0) {
                $vid = (int)$vr2->fetch_assoc()['id'];
                dbQuery("UPDATE vouchers SET used_count=used_count+1 WHERE id=$vid");
                $ins = db()->prepare("INSERT INTO voucher_uses (voucher_id,user_id) VALUES (?,?)");
                $ins->bind_param('ii',$vid,$uid); $ins->execute(); $ins->close();
            }
        }

        processReferralCommission($uid, $finalPrice, 'product');
        addNotification($uid,'Pembelian Berhasil!','Produk '.$prod['name'].' berhasil dibeli. Mulai mining sekarang!','success');
        completeMissionProgress($uid,'purchase');

        jsonResponse(['success'=>true,'message'=>'Pembelian berhasil! Silakan mining sekarang.','pkg_id'=>$pkgId]);
    }

    if ($action === 'toggle_favorite') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $check = db()->prepare("SELECT id FROM product_favorites WHERE user_id=? AND product_id=? LIMIT 1");
        $check->bind_param('ii',$uid,$pid); $check->execute();
        if ($check->get_result()->num_rows > 0) {
            dbQuery("DELETE FROM product_favorites WHERE user_id=$uid AND product_id=$pid");
            jsonResponse(['success'=>true,'favorited'=>false]);
        } else {
            dbQuery("INSERT INTO product_favorites (user_id,product_id) VALUES ($uid,$pid)");
            jsonResponse(['success'=>true,'favorited'=>true]);
        }
        $check->close();
    }
}

$activeCategory = sanitize($_GET['cat'] ?? 'starter');
$sortBy = sanitize($_GET['sort'] ?? 'default');
$nowDt  = date('Y-m-d H:i:s');

// Compatible PHP 7.2+ (no match expression)
if ($sortBy === 'price_asc')  $orderBy = 'p.price ASC';
elseif ($sortBy === 'price_desc') $orderBy = 'p.price DESC';
elseif ($sortBy === 'roi_desc')   $orderBy = '(p.profit_per_day*30/p.price) DESC';
elseif ($sortBy === 'popular')    $orderBy = 'p.total_buyers DESC';
else                              $orderBy = 'p.sort_order ASC';

$products = dbQuery("SELECT p.*,IF(pf.id IS NOT NULL,1,0) as is_favorite FROM products p LEFT JOIN product_favorites pf ON p.id=pf.product_id AND pf.user_id=$uid WHERE p.category='".dbEscape($activeCategory)."' AND p.status=1 ORDER BY $orderBy");

include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
  <h1 class="page-title">Produk Investasi</h1>
  <div class="sort-select-wrap">
    <select class="sort-select" onchange="location.href='?cat=<?= $activeCategory ?>&sort='+this.value">
      <option value="default" <?= $sortBy==='default'?'selected':'' ?>>Default</option>
      <option value="price_asc" <?= $sortBy==='price_asc'?'selected':'' ?>>Harga Terendah</option>
      <option value="price_desc" <?= $sortBy==='price_desc'?'selected':'' ?>>Harga Tertinggi</option>
      <option value="roi_desc" <?= $sortBy==='roi_desc'?'selected':'' ?>>ROI Tertinggi</option>
      <option value="popular" <?= $sortBy==='popular'?'selected':'' ?>>Terpopuler</option>
    </select>
  </div>
</div>

<!-- Category Tabs -->
<div class="cat-tabs">
  <?php foreach (['starter'=>'Starter','growth'=>'Growth','elite'=>'Elite'] as $cat=>$label): ?>
  <a href="?cat=<?= $cat ?>&sort=<?= $sortBy ?>" class="cat-tab <?= $activeCategory===$cat?'active':'' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<!-- Product Grid -->
<div class="product-grid">
<?php if ($products && $products->num_rows > 0): while ($prod = $products->fetch_assoc()):
  $roi = calcRoi((float)$prod['price'],(float)$prod['profit_per_day'],(int)$prod['duration_days']);
  $totalProfit = calcTotalProfit((float)$prod['profit_per_day'],(int)$prod['duration_days']);
  $isFlash = $prod['flash_sale'] && $nowDt >= $prod['flash_start'] && $nowDt <= $prod['flash_end'];
  $displayPrice = $isFlash ? (float)$prod['flash_price'] : (float)$prod['price'];
  $quotaPct = $prod['quota'] > 0 ? min(100, round($prod['quota_used']/$prod['quota']*100)) : 0;
?>
<div class="product-card animate-fadeInUp" id="prod-<?= $prod['id'] ?>">
  <div class="pc-top">
    <?php if ($prod['badge']): ?><div class="pc-badge"><?= htmlspecialchars($prod['badge']) ?></div><?php endif; ?>
    <?php if ($isFlash): ?><div class="pc-badge flash">FLASH SALE</div><?php endif; ?>
    <button class="pc-fav <?= $prod['is_favorite']?'active':'' ?>" onclick="toggleFav(<?= $prod['id'] ?>,this)">
      <i data-lucide="heart"></i>
    </button>
    <div class="pc-name"><?= htmlspecialchars($prod['name']) ?></div>
    <div class="pc-category"><?= ucfirst($prod['category']) ?></div>
  </div>
  <div class="pc-price">
    <?php if ($isFlash && (float)$prod['price'] !== $displayPrice): ?>
    <span class="pc-price-old"><?= formatRupiah((float)$prod['price']) ?></span>
    <?php endif; ?>
    <span class="pc-price-main"><?= formatRupiah($displayPrice) ?></span>
  </div>
  <div class="pc-stats">
    <div class="pc-stat"><span>Profit/Hari</span><strong><?= formatRupiah((float)$prod['profit_per_day']) ?></strong></div>
    <div class="pc-stat"><span>Durasi</span><strong><?= $prod['duration_days'] ?> Hari</strong></div>
    <div class="pc-stat"><span>ROI</span><strong class="text-gold"><?= $roi ?>%</strong></div>
    <div class="pc-stat"><span>Total Profit</span><strong><?= formatRupiah($totalProfit) ?></strong></div>
  </div>
  <?php if ($prod['quota'] > 0): ?>
  <div class="pc-quota">
    <div class="pc-quota-bar"><div class="pc-quota-fill" style="width:<?= $quotaPct ?>%"></div></div>
    <div class="pc-quota-text">Tersisa <?= $prod['quota']-$prod['quota_used'] ?>/<?= $prod['quota'] ?> slot</div>
  </div>
  <?php endif; ?>
  <?php if ($isFlash): ?>
  <div class="pc-flash-countdown" data-end="<?= strtotime($prod['flash_end']) ?>"></div>
  <?php endif; ?>
  <div class="pc-buyers"><i data-lucide="users"></i> <?= number_format($prod['total_buyers']) ?> pembeli &bull; ⭐ <?= $prod['rating'] ?></div>
  <div class="pc-actions">
    <button class="btn btn-primary btn-full" onclick="openBuyModal(<?= $prod['id'] ?>,'<?= addslashes($prod['name']) ?>',<?= $displayPrice ?>)">
      <i data-lucide="shopping-cart"></i> Beli
    </button>
    <button class="btn btn-outline btn-sm" onclick="openDetailModal(<?= $prod['id'] ?>)">
      <i data-lucide="info"></i>
    </button>
    <button class="btn btn-outline btn-sm" onclick="shareProduct(<?= $prod['id'] ?>,'<?= addslashes($prod['name']) ?>')">
      <i data-lucide="share-2"></i>
    </button>
  </div>
</div>
<?php endwhile; else: ?>
<div class="empty-state"><i data-lucide="package-open"></i><p>Belum ada produk</p></div>
<?php endif; ?>
</div>
</div>

<!-- Buy Modal -->
<div class="modal-overlay" id="buyModal" style="display:none">
  <div class="modal-box">
    <div class="modal-title">Konfirmasi Pembelian</div>
    <div class="buy-summary" id="buySummary"></div>
    <div class="input-wrapper mt-3">
      <i data-lucide="ticket" class="input-icon"></i>
      <input type="text" id="buyVoucher" class="form-input" placeholder="Kode voucher (opsional)">
    </div>
    <div class="modal-actions mt-3">
      <button class="btn btn-outline" onclick="closeBuyModal()">Batal</button>
      <button class="btn btn-primary" onclick="confirmBuy()"><i data-lucide="check"></i> Beli Sekarang</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
const CSRF='<?= csrfToken() ?>'; const APP_URL='<?= APP_URL ?>';
let buyProductId=null, buyProductPrice=0;

function openBuyModal(id,name,price){
  buyProductId=id; buyProductPrice=price;
  const bal=<?= (float)$user['balance'] ?>;
  document.getElementById('buySummary').innerHTML=`
    <div class="buy-row"><span>Produk</span><strong>${name}</strong></div>
    <div class="buy-row"><span>Harga</span><strong>Rp ${price.toLocaleString('id-ID')}</strong></div>
    <div class="buy-row"><span>Saldo Kamu</span><strong>Rp ${bal.toLocaleString('id-ID')}</strong></div>`;
  document.getElementById('buyModal').style.display='flex';
}
function closeBuyModal(){ document.getElementById('buyModal').style.display='none'; }

function confirmBuy(){
  const voucher=document.getElementById('buyVoucher').value.trim();
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:`csrf_token=${CSRF}&action=buy&product_id=${buyProductId}&voucher=${encodeURIComponent(voucher)}`})
  .then(r=>r.json()).then(d=>{
    hideLoading(); closeBuyModal();
    if(d.success){ showModal('success','Berhasil!',d.message+' 🎉',()=>location.href=APP_URL+'/pages/mining.php'); }
    else { showModal('error','Gagal',d.message); }
  });
}

function toggleFav(id,btn){
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:`csrf_token=${CSRF}&action=toggle_favorite&product_id=${id}`})
  .then(r=>r.json()).then(d=>{ btn.classList.toggle('active',d.favorited); showToast(d.favorited?'Ditambah ke favorit':'Dihapus dari favorit','success'); });
}

function shareProduct(id,name){
  const ref='<?= $user['referral_code'] ?>';
  const url=APP_URL+'/auth/register.php?ref='+ref;
  const text=`Investasi di Noxara pakai produk ${name}! Daftar sekarang: ${url}`;
  if(navigator.share){ navigator.share({title:'Noxara - '+name,text:text,url:url}); }
  else { navigator.clipboard.writeText(text); showToast('Link disalin!','success'); }
}

// Flash sale countdown
document.querySelectorAll('.pc-flash-countdown').forEach(el=>{
  const end=parseInt(el.dataset.end)*1000;
  const iv=setInterval(()=>{
    const rem=Math.max(0,end-Date.now());
    if(!rem){clearInterval(iv);el.textContent='Berakhir';return;}
    const h=Math.floor(rem/3600000),m=Math.floor(rem%3600000/60000),s=Math.floor(rem%60000/1000);
    el.textContent=`Flash Sale: ${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  },1000);
});
</script>
