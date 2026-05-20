<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Mining';
$currentPage = 'mining';

// AJAX handlers
if ($_SERVER['REQUEST_METHOD']==='POST' && isAjax()) {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'start') {
        $pkgId = (int)($_POST['package_id'] ?? 0);
        $result = startMining($uid, $pkgId);
        jsonResponse($result);
    }
    if ($action === 'complete') {
        $pkgId = (int)($_POST['package_id'] ?? 0);
        $result = completeMining($uid, $pkgId);
        jsonResponse($result);
    }
    if ($action === 'mine_all') {
        $pkgs = getUserActivePackages($uid);
        $results = [];
        foreach ($pkgs as $p) {
            if (!$p['mining_today']) {
                $results[] = startMining($uid, (int)$p['id']);
            }
        }
        jsonResponse(['success'=>true,'results'=>$results]);
    }
}

$packages  = getUserActivePackages($uid);
$stats     = getMiningStats($uid);
$chartData = getMiningChart($uid);
$calendar  = getMiningCalendar($uid, date('Y-m'));

include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
  <h1 class="page-title">Mining Center</h1>
  <?php if (count($packages) > 1): ?>
  <button class="btn btn-sm btn-gold" onclick="mineAll()"><i data-lucide="zap"></i> Mining Semua</button>
  <?php endif; ?>
</div>

<!-- Stats -->
<div class="mining-stats-row">
  <div class="mining-stat"><div class="ms-val"><?= formatRupiah($stats['today']) ?></div><div class="ms-label">Hari Ini</div></div>
  <div class="mining-stat"><div class="ms-val"><?= formatRupiah($stats['month']) ?></div><div class="ms-label">Bulan Ini</div></div>
  <div class="mining-stat"><div class="ms-val"><?= formatRupiah($stats['total']) ?></div><div class="ms-label">Total</div></div>
</div>

<!-- Total sisa hari -->
<?php if (count($packages) > 0):
  $totalDays = array_sum(array_map(fn($p)=>max(0,$p['duration_days']-$p['days_elapsed']),$packages));
  $avgDays = round($totalDays/count($packages));
?>
<div class="mining-summary-bar">
  <i data-lucide="package"></i>
  <span><?= count($packages) ?> produk aktif &bull; Rata-rata sisa <strong><?= $avgDays ?> hari</strong></span>
</div>
<?php endif; ?>

<!-- Packages -->
<?php if (count($packages) > 0): foreach ($packages as $pkg):
  $daysLeft = max(0, $pkg['duration_days'] - $pkg['days_elapsed']);
  $progress = $pkg['days_elapsed'] / $pkg['duration_days'] * 100;
  $estSisa   = $pkg['profit_per_day'] * $daysLeft;
  $miningFinishTs = $pkg['mining_today'] ? (strtotime($pkg['last_mining']) + MINING_COUNTDOWN_HOURS*3600) : 0;
  $countdown = max(0, $miningFinishTs - time());
  $statusClass = $pkg['mining_today'] ? ($countdown > 0 ? 'mining-running' : 'mining-done') : 'mining-idle';
?>
<div class="mining-card <?= $statusClass ?>" id="mcard-<?= $pkg['id'] ?>">
  <div class="mc-header">
    <div class="mc-name"><?= htmlspecialchars($pkg['product_name']) ?></div>
    <div class="mc-badge <?= $statusClass ?>">
      <?php if ($pkg['status']==='expired'): ?>
        <i data-lucide="x-circle"></i> Expired
      <?php elseif ($pkg['mining_today'] && $countdown > 0): ?>
        <i data-lucide="clock"></i> Berjalan
      <?php elseif ($pkg['mining_today']): ?>
        <i data-lucide="check-circle"></i> Selesai
      <?php else: ?>
        <i data-lucide="alert-circle"></i> Belum Mining
      <?php endif; ?>
    </div>
  </div>

  <div class="mc-progress-wrap">
    <div class="mc-progress-label">
      <span>Hari <?= $pkg['days_elapsed'] ?>/<?= $pkg['duration_days'] ?></span>
      <span><?= $daysLeft ?> hari lagi</span>
    </div>
    <div class="mc-progress-bar">
      <div class="mc-progress-fill" style="width:<?= $progress ?>%"></div>
    </div>
  </div>

  <div class="mc-stats">
    <div class="mc-stat"><span>Profit/Hari</span><strong><?= formatRupiah((float)$pkg['profit_per_day']) ?></strong></div>
    <div class="mc-stat"><span>Est. Sisa</span><strong><?= formatRupiah($estSisa) ?></strong></div>
  </div>

  <?php if ($pkg['status']==='expired'): ?>
  <a href="<?= APP_URL ?>/pages/products.php" class="btn btn-outline btn-full mt-2">
    <i data-lucide="shopping-bag"></i> Beli Lagi
  </a>
  <?php elseif ($pkg['mining_today'] && $countdown > 0): ?>
  <div class="mc-countdown-wrap">
    <div class="mc-countdown-ring">
      <svg viewBox="0 0 80 80">
        <circle cx="40" cy="40" r="34" fill="none" stroke="#1a1f35" stroke-width="6"/>
        <circle cx="40" cy="40" r="34" fill="none" stroke="#FFD700" stroke-width="6" stroke-dasharray="213.6" stroke-linecap="round" transform="rotate(-90 40 40)" class="countdown-ring-fill" data-total="<?= MINING_COUNTDOWN_HOURS*3600 ?>" data-remaining="<?= $countdown ?>"/>
      </svg>
      <div class="mc-countdown-time" data-pkg="<?= $pkg['id'] ?>" data-finish="<?= $miningFinishTs ?>"><?= gmdate('H:i:s',$countdown) ?></div>
    </div>
  </div>
  <?php elseif (!$pkg['mining_today']): ?>
  <button class="btn btn-primary btn-full mining-btn mt-2" data-pkg="<?= $pkg['id'] ?>" onclick="startMining(<?= $pkg['id'] ?>)">
    <i data-lucide="play-circle"></i> Mining Sekarang
  </button>
  <?php else: ?>
  <div class="mc-done-msg"><i data-lucide="check-circle"></i> Profit sudah masuk ke saldo</div>
  <?php endif; ?>
</div>
<?php endforeach;
else: ?>
<div class="empty-state-big">
  <i data-lucide="package-open"></i>
  <h3>Belum Ada Produk Aktif</h3>
  <p>Beli produk investasi untuk mulai mining</p>
  <a href="<?= APP_URL ?>/pages/products.php" class="btn btn-primary mt-3">
    <i data-lucide="shopping-bag"></i> Beli Produk
  </a>
</div>
<?php endif; ?>

<!-- Chart 7 hari -->
<?php if (count($packages) > 0): ?>
<div class="section-card mt-3">
  <h3 class="section-subtitle"><i data-lucide="bar-chart-2"></i> Profit 7 Hari Terakhir</h3>
  <div class="mining-chart" id="miningChart" data-chart='<?= json_encode($chartData) ?>'></div>
</div>

<!-- Kalender Mining -->
<div class="section-card mt-3">
  <h3 class="section-subtitle"><i data-lucide="calendar"></i> Kalender Mining <?= date('F Y') ?></h3>
  <div class="mining-calendar" id="miningCalendar" data-calendar='<?= json_encode($calendar) ?>' data-month="<?= date('Y-m') ?>"></div>
</div>
<?php endif; ?>

<!-- Riwayat Mining -->
<div class="section-card mt-3">
  <h3 class="section-subtitle"><i data-lucide="list"></i> Riwayat Mining</h3>
  <?php $logs = dbQuery("SELECT ml.*,p.name as product_name FROM mining_logs ml JOIN user_packages up ON ml.package_id=up.id JOIN products p ON up.product_id=p.id WHERE ml.user_id=$uid ORDER BY ml.mined_at DESC LIMIT 20"); ?>
  <?php if ($logs && $logs->num_rows > 0): while ($l = $logs->fetch_assoc()): ?>
  <div class="mining-log-item">
    <div class="ml-icon <?= $l['status'] ?>"><i data-lucide="<?= $l['status']==='success'?'check-circle':'x-circle' ?>"></i></div>
    <div class="ml-info">
      <div class="ml-name"><?= htmlspecialchars($l['product_name']) ?></div>
      <div class="ml-date"><?= date('d M Y H:i', strtotime($l['mined_at'])) ?></div>
    </div>
    <div class="ml-profit <?= $l['status'] ?>">
      <?= $l['status']==='success' ? '+'.formatRupiah((float)$l['profit']) : 'Tidak Mining' ?>
    </div>
  </div>
  <?php endwhile; else: ?>
  <div class="empty-state"><i data-lucide="inbox"></i><p>Belum ada riwayat mining</p></div>
  <?php endif; ?>
</div>
</div>

<!-- Mining Confirm Modal -->
<div class="modal-overlay" id="miningConfirmModal" style="display:none">
  <div class="modal-box">
    <div class="modal-icon animate-bounce"><i data-lucide="cpu"></i></div>
    <div class="modal-title">Mulai Mining?</div>
    <div class="modal-msg">Yakin ingin mulai mining sekarang? Profit akan masuk dalam 2 jam.</div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="document.getElementById('miningConfirmModal').style.display='none'">Batal</button>
      <button class="btn btn-primary" id="confirmMineBtn">Ya, Mining!</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
const CSRF='<?= csrfToken() ?>';
let pendingPkgId=null;

function startMining(pkgId){
  pendingPkgId=pkgId;
  document.getElementById('miningConfirmModal').style.display='flex';
  document.getElementById('confirmMineBtn').onclick=()=>doMining(pkgId);
}

function doMining(pkgId){
  document.getElementById('miningConfirmModal').style.display='none';
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:`csrf_token=${CSRF}&action=start&package_id=${pkgId}`})
  .then(r=>r.json()).then(d=>{
    hideLoading();
    if(d.success){
      showModal('success','Mining Dimulai!',`Mining berjalan! Profit ${formatRupiah(d.profit)} akan masuk dalam 2 jam. ⛏️`,()=>location.reload());
    } else { showModal('error','Gagal',d.message); }
  });
}

function mineAll(){
  showConfirm('Mining Semua','Mulai mining untuk semua produk aktif?',()=>{
    showLoading();
    fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
      body:`csrf_token=${CSRF}&action=mine_all`})
    .then(r=>r.json()).then(d=>{ hideLoading(); showModal('success','Berhasil!','Semua produk sedang mining!',()=>location.reload()); });
  });
}

function formatRupiah(n){ return 'Rp '+Number(n).toLocaleString('id-ID'); }

// Init chart & calendar
document.addEventListener('DOMContentLoaded',()=>{
  initMiningChart();
  initMiningCalendar();
  initMiningCountdowns();
});
</script>
