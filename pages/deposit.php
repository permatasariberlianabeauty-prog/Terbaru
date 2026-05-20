<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Isi Ulang';
$currentPage = 'deposit';

$minDep = (float)getSetting('min_deposit','10000');
$maxDep = (float)getSetting('max_deposit','100000000');
$bonusPct = (float)getSetting('deposit_bonus_percent','0');
$bonusOn  = getSetting('deposit_bonus_status','0') === '1';

// Handle AJAX generate QRIS
if ($_SERVER['REQUEST_METHOD']==='POST' && isAjax()) {
    verifyCsrf();
    $amount  = (float)($_POST['amount'] ?? 0);
    $voucher = sanitize($_POST['voucher'] ?? '');

    if ($amount < $minDep) jsonResponse(['success'=>false,'message'=>"Minimal deposit ".formatRupiah($minDep)]);
    if ($amount > $maxDep) jsonResponse(['success'=>false,'message'=>"Maksimal deposit ".formatRupiah($maxDep)]);

    // Check voucher VIP
    if ($voucher) {
        $vc = dbEscape($voucher);
        $vr = dbQuery("SELECT * FROM vouchers WHERE code='$vc' AND type='deposit' AND status=1 LIMIT 1");
        if ($vr && $vr->num_rows > 0) {
            $vd = $vr->fetch_assoc();
            if ((int)$vd['min_vip'] > (int)$user['vip_level'])
                jsonResponse(['success'=>false,'message'=>'Voucher ini hanya untuk VIP '.$vd['min_vip'].' ke atas. Upgrade VIP kamu!']);
            // Check user already used
            $used = db()->prepare("SELECT id FROM voucher_uses WHERE voucher_id=? AND user_id=? LIMIT 1");
            $used->bind_param('ii',$vd['id'],$uid);
            $used->execute();
            if ($used->get_result()->num_rows > 0)
                jsonResponse(['success'=>false,'message'=>'Voucher sudah pernah kamu gunakan']);
            $used->close();
        }
    }

    $result = generateQris($amount, $voucher);
    if (!$result['success']) jsonResponse(['success'=>false,'message'=>$result['message']]);

    $qdata = $result['data'];
    $bonus = $bonusOn ? round($amount * $bonusPct / 100, 2) : 0;

    // Save deposit
    $tid = dbEscape($qdata['transactionId']);
    $oa  = (float)$qdata['originalAmount'];
    $ta  = (float)$qdata['totalAmount'];
    $un  = (int)$qdata['uniqueNominal'];
    $qr  = dbEscape($qdata['qr_string']);
    $vc  = dbEscape($voucher);
    $dis = (float)$result['discount'];
    $exp = dbEscape(date('Y-m-d H:i:s', strtotime('+'.CASHIFY_EXPIRED_MINUTES.' minutes')));

    $ins = db()->prepare("INSERT INTO deposits (user_id,transaction_id,original_amount,total_amount,unique_nominal,voucher_code,voucher_discount,bonus_amount,qr_string,status,expired_at) VALUES (?,?,?,?,?,?,?,?,?,'pending',?)");
    $ins->bind_param('isddisddss',$uid,$tid,$oa,$ta,$un,$vc,$dis,$bonus,$qr,$exp);
    $ins->execute();
    $ins->close();

    jsonResponse(['success'=>true,'transaction_id'=>$tid,'qr_string'=>$qdata['qr_string'],'total_amount'=>$ta,'original_amount'=>$oa,'unique_nominal'=>$un,'expired_at'=>$exp,'bonus'=>$bonus]);
}

// Handle check status AJAX
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='check') {
    $tid = sanitize($_POST['transaction_id'] ?? '');
    if (!$tid) jsonResponse(['success'=>false,'message'=>'Invalid']);
    $status = checkPaymentStatus($tid);
    if (($status['status'] ?? '') === 'paid') {
        // Credit user
        $dep = dbQuery("SELECT * FROM deposits WHERE transaction_id='".dbEscape($tid)."' AND user_id=$uid AND status='pending' LIMIT 1");
        if ($dep && $dep->num_rows > 0) {
            $d = $dep->fetch_assoc();
            $total = (float)$d['total_amount'] + (float)$d['bonus_amount'];
            dbQuery("UPDATE deposits SET status='paid',paid_at=NOW() WHERE id=".(int)$d['id']);
            dbQuery("UPDATE users SET balance=balance+$total,total_deposit=total_deposit+".(float)$d['original_amount']." WHERE id=$uid");
            addTransaction($uid,'deposit',$total,'Isi ulang via QRIS',(int)$d['id']);
            if ($d['voucher_code']) {
                $vr2 = dbQuery("SELECT id FROM vouchers WHERE code='".dbEscape($d['voucher_code'])."' LIMIT 1");
                if ($vr2 && $vr2->num_rows > 0) {
                    $vid = (int)$vr2->fetch_assoc()['id'];
                    dbQuery("UPDATE vouchers SET used_count=used_count+1 WHERE id=$vid");
                    $ins2 = db()->prepare("INSERT INTO voucher_uses (voucher_id,user_id) VALUES (?,?)");
                    $ins2->bind_param('ii',$vid,$uid);
                    $ins2->execute();
                    $ins2->close();
                }
            }
            processReferralCommission($uid, (float)$d['original_amount'], 'deposit');
            checkAndUpgradeVip($uid);
            addNotification($uid,'Deposit Berhasil!','Isi ulang '.formatRupiah($total).' berhasil masuk ke saldo kamu.','success');
            completeMissionProgress($uid,'deposit');
        }
        jsonResponse(['success'=>true,'status'=>'paid','message'=>'Pembayaran berhasil!']);
    }
    jsonResponse(['success'=>true,'status'=>$status['status'] ?? 'pending']);
}

// Handle cancel AJAX
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='cancel') {
    $tid = sanitize($_POST['transaction_id'] ?? '');
    cancelPayment($tid);
    dbQuery("UPDATE deposits SET status='cancel' WHERE transaction_id='".dbEscape($tid)."' AND user_id=$uid");
    jsonResponse(['success'=>true,'message'=>'Transaksi dibatalkan']);
}

include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
  <a href="<?= APP_URL ?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a>
  <h1 class="page-title">Isi Ulang</h1>
</div>

<div id="depositStep1">
  <!-- VIP Info -->
  <div class="vip-info-bar">
    <i data-lucide="crown"></i>
    <span>Level: <strong><?= vipBadgeName((int)$user['vip_level']) ?></strong></span>
  </div>

  <?php if ($bonusOn && $bonusPct > 0): ?>
  <div class="bonus-bar animate-pulse">
    <i data-lucide="gift"></i> Bonus Deposit <strong><?= $bonusPct ?>%</strong> aktif hari ini!
  </div>
  <?php endif; ?>

  <!-- Payment Methods (display only) -->
  <div class="section-card">
    <h3 class="section-subtitle">Metode Pembayaran</h3>
    <div class="payment-grid">
      <?php $methods = [['DANA','dana'],['GoPay','gopay'],['OVO','ovo'],['ShopeePay','shopee'],['LinkAja','linkaja'],['BCA','bca'],['BRI','bri'],['Mandiri','mandiri'],['BNI','bni'],['QRIS','qris']];
      foreach ($methods as $m): ?>
      <div class="payment-item" onclick="selectPayment(this,'<?= $m[1] ?>')">
        <div class="payment-logo payment-<?= $m[1] ?>"><?= $m[0] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="payment-note"><i data-lucide="info"></i> Semua pembayaran diproses via QRIS</p>
  </div>

  <!-- Amount -->
  <div class="section-card">
    <h3 class="section-subtitle">Nominal Isi Ulang</h3>
    <div class="quick-amounts">
      <?php foreach ([10000,25000,50000,100000,250000,500000] as $qa): ?>
      <button class="quick-amt" onclick="setAmount(<?= $qa ?>)"><?= formatRupiah($qa) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="input-wrapper mt-3">
      <i data-lucide="dollar-sign" class="input-icon"></i>
      <input type="number" id="depAmount" class="form-input" placeholder="Masukkan nominal" min="<?= $minDep ?>" max="<?= $maxDep ?>">
    </div>
    <div class="dep-info mt-2">
      <span>Min: <?= formatRupiah($minDep) ?></span>
      <span>Max: <?= formatRupiah($maxDep) ?></span>
    </div>
  </div>

  <!-- Voucher -->
  <div class="section-card">
    <h3 class="section-subtitle">Kode Voucher (Opsional)</h3>
    <div class="input-wrapper">
      <i data-lucide="ticket" class="input-icon"></i>
      <input type="text" id="depVoucher" class="form-input" placeholder="Masukkan kode voucher">
    </div>
  </div>

  <button class="btn btn-primary btn-full btn-lg mt-3" onclick="proceedDeposit()">
    <i data-lucide="arrow-right"></i> Lanjutkan
  </button>
</div>

<!-- STEP 2: QRIS -->
<div id="depositStep2" style="display:none">
  <div class="qris-container">
    <div class="qris-title">Scan & Bayar</div>
    <div class="qris-amount-label">Total yang harus dibayar</div>
    <div class="qris-amount" id="qrisAmount"></div>
    <div class="qris-unique" id="qrisUnique"></div>
    <div class="qris-code" id="qrisCode"></div>
    <div class="qris-countdown-wrap">
      <svg class="qris-countdown-ring" viewBox="0 0 120 120">
        <circle cx="60" cy="60" r="54" fill="none" stroke="#1a1f35" stroke-width="8"/>
        <circle id="countdownRing" cx="60" cy="60" r="54" fill="none" stroke="#FFD700" stroke-width="8" stroke-dasharray="339.3" stroke-dashoffset="0" stroke-linecap="round" transform="rotate(-90 60 60)"/>
      </svg>
      <div class="qris-timer" id="qrisTimer">15:00</div>
    </div>
    <p class="qris-note">Bayar sesuai nominal di atas. Transaksi otomatis terdeteksi.</p>
    <div class="qris-actions">
      <button class="btn btn-success btn-full" onclick="checkPayment()">
        <i data-lucide="check-circle"></i> Saya Sudah Bayar
      </button>
      <button class="btn btn-outline btn-full mt-2" onclick="saveQris()">
        <i data-lucide="download"></i> Simpan QR
      </button>
      <button class="btn btn-danger btn-full mt-2" onclick="cancelDeposit()">
        <i data-lucide="x-circle"></i> Batalkan Transaksi
      </button>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<!-- QRCode.js untuk generate QR image -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let currentTxId = null, pollInterval = null, qrisExpiry = 0;
const CSRF = '<?= csrfToken() ?>';
const APP_URL = '<?= APP_URL ?>';

function setAmount(a){ document.getElementById('depAmount').value=a; }
function selectPayment(el){
  document.querySelectorAll('.payment-item').forEach(function(e){e.classList.remove('selected');});
  el.classList.add('selected');
}

function proceedDeposit(){
  var amount = parseFloat(document.getElementById('depAmount').value);
  var voucher = document.getElementById('depVoucher').value.trim();
  if (!amount || amount < <?= $minDep ?>) {
    if(typeof showModal==='function') showModal('error','Gagal','Minimal deposit <?= formatRupiah($minDep) ?>');
    else alert('Minimal deposit <?= formatRupiah($minDep) ?>');
    return;
  }
  if(typeof showLoading==='function') showLoading();
  fetch(window.location.href, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: 'csrf_token='+CSRF+'&amount='+amount+'&voucher='+encodeURIComponent(voucher)
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if(typeof hideLoading==='function') hideLoading();
    if(!d.success){
      if(typeof showModal==='function') showModal('error','Gagal',d.message);
      else alert(d.message);
      return;
    }
    currentTxId = d.transaction_id;
    qrisExpiry = Math.floor(new Date(d.expired_at).getTime()/1000);
    document.getElementById('qrisAmount').textContent = 'Rp '+Number(d.total_amount).toLocaleString('id-ID');
    document.getElementById('qrisUnique').textContent = d.unique_nominal > 0 ? '(+kode unik Rp '+d.unique_nominal+')' : '';
    generateQRImage(d.qr_string);
    document.getElementById('depositStep1').style.display='none';
    document.getElementById('depositStep2').style.display='block';
    startQrisCountdown();
    startPolling();
  })
  .catch(function(){
    if(typeof hideLoading==='function') hideLoading();
    if(typeof showModal==='function') showModal('error','Error','Terjadi kesalahan. Coba lagi.');
    else alert('Terjadi kesalahan. Coba lagi.');
  });
}

function generateQRImage(qrString){
  var c = document.getElementById('qrisCode');
  c.innerHTML = '';
  if(typeof QRCode !== 'undefined'){
    var canvas = document.createElement('canvas');
    c.appendChild(canvas);
    try {
      new QRCode(canvas, {
        text: qrString,
        width: 220,
        height: 220,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
    } catch(e) {
      // fallback: show raw string as text
      c.innerHTML = '<p style="font-size:10px;word-break:break-all;background:#fff;color:#000;padding:10px;border-radius:8px;max-width:250px;margin:0 auto">'+qrString+'</p>';
    }
  } else {
    // No QRCode lib - show text fallback
    c.innerHTML = '<div style="background:#fff;color:#000;padding:16px;border-radius:12px;font-size:9px;word-break:break-all;max-width:250px;margin:0 auto;text-align:left"><b>QR String:</b><br>'+qrString+'</div>';
  }
}

function startQrisCountdown(){
  var total = <?= CASHIFY_EXPIRED_MINUTES * 60 ?>;
  var ring = document.getElementById('countdownRing');
  var timerEl = document.getElementById('qrisTimer');
  var circumference = 339.3;
  var iv = setInterval(function(){
    var remaining = qrisExpiry - Math.floor(Date.now()/1000);
    if(remaining <= 0){ clearInterval(iv); timerEl.textContent='00:00'; handleExpired(); return; }
    var m = Math.floor(remaining/60), s = remaining%60;
    timerEl.textContent = String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    var offset = circumference * (1 - remaining/total);
    if(ring) ring.style.strokeDashoffset = offset;
    if(remaining <= 120 && ring) ring.style.stroke = '#FF4444';
    if(remaining === 120 && typeof showToast==='function') showToast('Waktu pembayaran hampir habis!','warning');
  }, 1000);
}

function startPolling(){ pollInterval = setInterval(checkPayment, 5000); }

function checkPayment(){
  if(!currentTxId) return;
  fetch(window.location.href, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: 'csrf_token='+CSRF+'&action=check&transaction_id='+currentTxId
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if(d.status==='paid'){
      clearInterval(pollInterval);
      if(typeof showModal==='function') showModal('success','Pembayaran Berhasil!','Saldo kamu berhasil ditambahkan. Selamat mining! 🎉', function(){ location.href=APP_URL+'/pages/dashboard.php'; });
      else { alert('Pembayaran berhasil!'); location.href=APP_URL+'/pages/dashboard.php'; }
    }
  });
}

function cancelDeposit(){
  if(!confirm('Yakin ingin membatalkan transaksi ini?')) return;
  clearInterval(pollInterval);
  fetch(window.location.href, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: 'csrf_token='+CSRF+'&action=cancel&transaction_id='+currentTxId
  }).then(function(){ location.reload(); });
}

function handleExpired(){
  clearInterval(pollInterval);
  if(typeof showModal==='function') showModal('warning','Transaksi Expired','Waktu pembayaran habis. Silakan buat transaksi baru.', function(){ location.reload(); });
  else { alert('Waktu habis. Silakan buat transaksi baru.'); location.reload(); }
}
function saveQris(){ if(typeof showToast==='function') showToast('Screenshot halaman ini untuk menyimpan QR','info'); }
</script>