<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Penarikan Dana';
$currentPage = 'withdraw';
$vip  = getVipInfo((int)$user['vip_level']);

// Handle POST
if ($_SERVER['REQUEST_METHOD']==='POST' && isAjax()) {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'check_pin') {
        $pin = $_POST['pin'] ?? '';
        if (!$user['pin']) jsonResponse(['success'=>false,'message'=>'Belum buat PIN. Buat PIN dulu di Profil.','need_pin'=>true]);
        if (!verifyPin($pin, $user['pin'])) jsonResponse(['success'=>false,'message'=>'PIN salah!']);
        jsonResponse(['success'=>true]);
    }

    if ($action === 'withdraw') {
        $amount = (float)($_POST['amount'] ?? 0);
        $pin    = $_POST['pin'] ?? '';

        if (!$user['pin']) jsonResponse(['success'=>false,'message'=>'Buat PIN dulu di Profil','need_pin'=>true]);
        if (!verifyPin($pin, $user['pin'])) jsonResponse(['success'=>false,'message'=>'PIN salah!']);

        $bank = dbQuery("SELECT * FROM user_banks WHERE user_id=$uid LIMIT 1");
        if (!$bank || $bank->num_rows === 0) jsonResponse(['success'=>false,'message'=>'Daftarkan akun bank dulu di Profil','need_bank'=>true]);
        $bankData = $bank->fetch_assoc();

        $minWd = (float)$vip['min_withdraw'];
        $fee   = (float)$vip['withdraw_fee_percent'];
        $minSaldo = (float)getSetting('min_saldo_tersisa','0');
        $dayLimit = (float)getSetting('daily_withdraw_per_user','5000000');

        if ($minWd > 0 && $amount < $minWd) jsonResponse(['success'=>false,'message'=>'Minimal penarikan '.formatRupiah($minWd)]);
        if (hasBonusBalance($uid)) jsonResponse(['success'=>false,'message'=>'Kamu masih memiliki saldo bonus. Gunakan saldo bonus untuk beli produk terlebih dahulu.']);

        $bal = (float)$user['balance'];
        if ($amount > $bal - $minSaldo) jsonResponse(['success'=>false,'message'=>'Saldo tidak cukup. Saldo minimum tersisa '.formatRupiah($minSaldo)]);

        // Daily limit check
        $todayWd = dbQuery("SELECT COALESCE(SUM(amount),0) as t FROM withdrawals WHERE user_id=$uid AND DATE(created_at)=CURDATE() AND status NOT IN('rejected')");
        $todayTotal = $todayWd ? (float)$todayWd->fetch_assoc()['t'] : 0;
        if ($dayLimit > 0 && ($todayTotal + $amount) > $dayLimit) jsonResponse(['success'=>false,'message'=>'Batas penarikan harian '.formatRupiah($dayLimit).' sudah tercapai']);

        $adminFee = round($amount * $fee / 100, 2);
        $received = $amount - $adminFee;

        // Debit balance
        dbQuery("UPDATE users SET balance=balance-$amount,total_withdraw=total_withdraw+$amount WHERE id=$uid AND balance>=$amount");
        if (db()->affected_rows === 0) jsonResponse(['success'=>false,'message'=>'Saldo tidak cukup']);

        $bn = dbEscape($bankData['bank_name']); $an = dbEscape($bankData['account_name']); $acc = dbEscape($bankData['account_number']);
        $stmt = db()->prepare("INSERT INTO withdrawals (user_id,amount,admin_fee,amount_received,bank_name,account_name,account_number) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('idddsss',$uid,$amount,$adminFee,$received,$bn,$an,$acc);
        $stmt->execute();
        $wid = db()->insert_id;
        $stmt->close();

        addTransaction($uid,'withdraw',-$amount,'Penarikan dana ke '.$bankData['bank_name'],$wid);
        addNotification($uid,'Penarikan Diproses','Penarikan '.formatRupiah($amount).' sedang diproses.','info');

        jsonResponse(['success'=>true,'message'=>'Penarikan berhasil diajukan!','amount'=>$amount,'received'=>$received,'fee'=>$adminFee]);
    }
}

$bank = dbQuery("SELECT * FROM user_banks WHERE user_id=$uid LIMIT 1");
$bankData = ($bank && $bank->num_rows > 0) ? $bank->fetch_assoc() : null;
$minWd    = (float)$vip['min_withdraw'];
$feePct   = (float)$vip['withdraw_fee_percent'];

include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
  <a href="<?= APP_URL ?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a>
  <h1 class="page-title">Penarikan Dana</h1>
</div>

<!-- VIP WD Info -->
<div class="wd-info-card">
  <div class="wd-info-row"><span>Level VIP</span><span class="text-gold"><?= vipBadgeName((int)$user['vip_level']) ?></span></div>
  <div class="wd-info-row"><span>Minimal Penarikan</span><span><?= $minWd > 0 ? formatRupiah($minWd) : 'Tidak Ada' ?></span></div>
  <div class="wd-info-row"><span>Biaya Admin</span><span><?= $feePct ?>%</span></div>
  <div class="wd-info-row"><span>Saldo Kamu</span><span class="text-gold"><?= formatRupiah((float)$user['balance']) ?></span></div>
</div>

<?php if (!$bankData): ?>
<div class="alert alert-warning">
  <i data-lucide="alert-triangle"></i>
  Kamu belum mendaftarkan akun bank. <a href="<?= APP_URL ?>/pages/profile.php#bank" class="text-gold">Daftarkan sekarang</a>
</div>
<?php elseif (!$user['pin']): ?>
<div class="alert alert-warning">
  <i data-lucide="alert-triangle"></i>
  Kamu belum membuat PIN. <a href="<?= APP_URL ?>/pages/profile.php#pin" class="text-gold">Buat PIN sekarang</a>
</div>
<?php else: ?>

<!-- Bank Info -->
<div class="bank-card">
  <div class="bank-icon"><i data-lucide="credit-card"></i></div>
  <div class="bank-info">
    <div class="bank-name"><?= htmlspecialchars($bankData['bank_name']) ?></div>
    <div class="bank-acc"><?= htmlspecialchars($bankData['account_name']) ?></div>
    <div class="bank-num"><?= htmlspecialchars($bankData['account_number']) ?></div>
  </div>
</div>

<!-- Amount Input -->
<div class="section-card">
  <div class="quick-amounts">
    <?php foreach ([50000,100000,250000,500000,1000000] as $qa): ?>
    <button class="quick-amt" onclick="setWdAmount(<?= $qa ?>)"><?= formatRupiah($qa) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="input-wrapper mt-3">
    <i data-lucide="dollar-sign" class="input-icon"></i>
    <input type="number" id="wdAmount" class="form-input" placeholder="Nominal penarikan" oninput="calcWd()">
  </div>
  <div class="wd-calc mt-3" id="wdCalc" style="display:none">
    <div class="wd-calc-row"><span>Nominal Tarik</span><span id="calcAmount">-</span></div>
    <div class="wd-calc-row"><span>Biaya Admin (<?= $feePct ?>%)</span><span id="calcFee">-</span></div>
    <div class="wd-calc-row total"><span>Jumlah Diterima</span><span id="calcReceived" class="text-gold">-</span></div>
    <div class="wd-calc-row"><span>Email</span><span><?= htmlspecialchars($user['email']) ?></span></div>
  </div>
</div>

<button class="btn btn-primary btn-full btn-lg" onclick="showPinModal()">
  <i data-lucide="send"></i> Tarik Dana
</button>
<?php endif; ?>

<!-- Riwayat Penarikan -->
<div class="section-card mt-3">
  <h3 class="section-subtitle">Riwayat Penarikan</h3>
  <?php $wds = dbQuery("SELECT * FROM withdrawals WHERE user_id=$uid ORDER BY created_at DESC LIMIT 10"); ?>
  <?php if ($wds && $wds->num_rows > 0): while ($w = $wds->fetch_assoc()): ?>
  <div class="wd-item">
    <div class="wd-item-info">
      <div class="wd-item-bank"><?= htmlspecialchars($w['bank_name']) ?></div>
      <div class="wd-item-date"><?= date('d M Y H:i', strtotime($w['created_at'])) ?></div>
      <?php if ($w['reject_reason']): ?><div class="wd-reject-reason"><?= htmlspecialchars($w['reject_reason']) ?></div><?php endif; ?>
    </div>
    <div class="wd-item-right">
      <div class="wd-item-amount"><?= formatRupiah((float)$w['amount']) ?></div>
      <div class="status-badge status-<?= $w['status'] ?>"><?= ucfirst($w['status']) ?></div>
    </div>
  </div>
  <?php endwhile; else: ?>
  <div class="empty-state"><i data-lucide="inbox"></i><p>Belum ada riwayat penarikan</p></div>
  <?php endif; ?>
</div>
</div>

<!-- PIN Modal -->
<div class="modal-overlay" id="pinModal" style="display:none">
  <div class="modal-box">
    <div class="modal-icon"><i data-lucide="lock"></i></div>
    <div class="modal-title">Konfirmasi PIN</div>
    <div class="modal-msg">Masukkan PIN kamu untuk melanjutkan penarikan</div>
    <div class="pin-input-group" id="pinInputGroup">
      <?php for($i=0;$i<6;$i++): ?>
      <input type="password" class="pin-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
      <?php endfor; ?>
    </div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="document.getElementById('pinModal').style.display='none'">Batal</button>
      <button class="btn btn-primary" onclick="submitWithdraw()">Konfirmasi</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
const CSRF='<?= csrfToken() ?>';
const FEE=<?= $feePct ?>;
function setWdAmount(a){document.getElementById('wdAmount').value=a;calcWd();}
function calcWd(){
  const a=parseFloat(document.getElementById('wdAmount').value)||0;
  const fee=Math.round(a*FEE/100);
  const recv=a-fee;
  document.getElementById('wdCalc').style.display=a>0?'block':'none';
  document.getElementById('calcAmount').textContent='Rp '+a.toLocaleString('id-ID');
  document.getElementById('calcFee').textContent='Rp '+fee.toLocaleString('id-ID');
  document.getElementById('calcReceived').textContent='Rp '+recv.toLocaleString('id-ID');
}
function showPinModal(){
  const a=parseFloat(document.getElementById('wdAmount').value)||0;
  if(!a){showToast('Masukkan nominal penarikan','error');return;}
  document.getElementById('pinModal').style.display='flex';
  initPinInput();
}
function submitWithdraw(){
  const pin=getPinValue();
  if(pin.length<6){showToast('PIN harus 6 digit','error');return;}
  const amount=parseFloat(document.getElementById('wdAmount').value);
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:`csrf_token=${CSRF}&action=withdraw&amount=${amount}&pin=${pin}`})
  .then(r=>r.json()).then(d=>{
    hideLoading();
    document.getElementById('pinModal').style.display='none';
    if(d.success){
      showModal('success','Berhasil!',`Penarikan Rp ${d.amount.toLocaleString('id-ID')} diajukan. Kamu akan menerima Rp ${d.received.toLocaleString('id-ID')} setelah diproses.`,()=>location.reload());
    } else {
      showModal('error','Gagal',d.message);
    }
  });
}
</script>
