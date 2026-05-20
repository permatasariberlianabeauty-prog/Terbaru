<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle='Dashboard'; $currentAdminPage='index';
include __DIR__.'/_header.php';

// Helper function to get single value from query
function adminStat($sql, $col = 'c', $default = 0) {
    $r = dbQuery($sql);
    if (!$r || $r->num_rows === 0) return $default;
    $row = $r->fetch_assoc();
    return isset($row[$col]) ? $row[$col] : $default;
}

$totalUsers   = (int)adminStat("SELECT COUNT(*) as c FROM users WHERE status!='blocked'");
$newToday     = (int)adminStat("SELECT COUNT(*) as c FROM users WHERE DATE(created_at)=CURDATE()");
$totalDep     = (float)adminStat("SELECT COALESCE(SUM(original_amount),0) as c FROM deposits WHERE status='paid'");
$depToday     = (float)adminStat("SELECT COALESCE(SUM(original_amount),0) as c FROM deposits WHERE status='paid' AND DATE(created_at)=CURDATE()");
$totalWd      = (float)adminStat("SELECT COALESCE(SUM(amount),0) as c FROM withdrawals WHERE status='success'");
$wdPending    = (int)adminStat("SELECT COUNT(*) as c FROM withdrawals WHERE status='pending'");
$activeMining = (int)adminStat("SELECT COUNT(*) as c FROM user_packages WHERE status='active'");

// Chart data 7 days
$chartDays = []; $chartDep = []; $chartWd = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartDays[] = date('d/m', strtotime($d));
    $chartDep[]  = (float)adminStat("SELECT COALESCE(SUM(original_amount),0) as c FROM deposits WHERE status='paid' AND DATE(created_at)='$d'");
    $chartWd[]   = (float)adminStat("SELECT COALESCE(SUM(amount),0) as c FROM withdrawals WHERE status='success' AND DATE(created_at)='$d'");
}

$recentUsers        = dbQuery("SELECT id,username,full_name,vip_level,status,created_at FROM users ORDER BY created_at DESC LIMIT 5");
$pendingWithdrawals = dbQuery("SELECT w.*,u.username FROM withdrawals w JOIN users u ON w.user_id=u.id WHERE w.status='pending' ORDER BY w.created_at ASC LIMIT 5");
$adminName = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
?>
<div class="admin-page-header">
  <h1>Dashboard</h1>
  <p>Selamat datang, <?= htmlspecialchars($adminName) ?></p>
</div>

<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div class="asc-icon" style="background:#3B82F6"><i data-lucide="users"></i></div>
    <div class="asc-info"><div class="asc-val"><?= number_format($totalUsers) ?></div><div class="asc-label">Total Member</div><div class="asc-sub">+<?= $newToday ?> hari ini</div></div>
  </div>
  <div class="admin-stat-card">
    <div class="asc-icon" style="background:#10B981"><i data-lucide="trending-up"></i></div>
    <div class="asc-info"><div class="asc-val"><?= formatRupiah($totalDep) ?></div><div class="asc-label">Total Deposit</div><div class="asc-sub">Hari ini: <?= formatRupiah($depToday) ?></div></div>
  </div>
  <div class="admin-stat-card">
    <div class="asc-icon" style="background:#F59E0B"><i data-lucide="trending-down"></i></div>
    <div class="asc-info"><div class="asc-val"><?= formatRupiah($totalWd) ?></div><div class="asc-label">Total Penarikan</div><div class="asc-sub">Pending: <?= $wdPending ?></div></div>
  </div>
  <div class="admin-stat-card">
    <div class="asc-icon" style="background:#8B5CF6"><i data-lucide="cpu"></i></div>
    <div class="asc-info"><div class="asc-val"><?= number_format($activeMining) ?></div><div class="asc-label">Mining Aktif</div><div class="asc-sub">Paket aktif</div></div>
  </div>
</div>

<div class="admin-card mt-3">
  <h3>Grafik 7 Hari Terakhir</h3>
  <canvas id="adminChart" height="200"></canvas>
</div>

<?php if ($wdPending > 0): ?>
<div class="admin-card mt-3">
  <div class="admin-card-header">
    <h3>Penarikan Pending <span class="badge-count"><?= $wdPending ?></span></h3>
    <a href="<?= APP_URL ?>/adm-noxara/withdrawals.php" class="btn btn-sm btn-outline">Lihat Semua</a>
  </div>
  <?php if ($pendingWithdrawals): while ($w = $pendingWithdrawals->fetch_assoc()): ?>
  <div class="admin-list-item">
    <div class="ali-info">
      <div class="ali-name">@<?= htmlspecialchars($w['username']) ?></div>
      <div class="ali-sub"><?= htmlspecialchars($w['bank_name']) ?> - <?= htmlspecialchars($w['account_number']) ?></div>
      <div class="ali-date"><?= date('d M H:i', strtotime($w['created_at'])) ?></div>
    </div>
    <div class="ali-right">
      <div class="ali-amount"><?= formatRupiah((float)$w['amount']) ?></div>
      <a href="<?= APP_URL ?>/adm-noxara/withdrawals.php" class="btn btn-sm btn-primary">Proses</a>
    </div>
  </div>
  <?php endwhile; endif; ?>
</div>
<?php endif; ?>

<div class="admin-card mt-3">
  <div class="admin-card-header">
    <h3>Member Terbaru</h3>
    <a href="<?= APP_URL ?>/adm-noxara/members.php" class="btn btn-sm btn-outline">Lihat Semua</a>
  </div>
  <?php if ($recentUsers && $recentUsers->num_rows > 0): while ($u = $recentUsers->fetch_assoc()): ?>
  <div class="admin-list-item">
    <div class="ali-avatar"><?= strtoupper(substr($u['username'], 0, 1)) ?></div>
    <div class="ali-info">
      <div class="ali-name">@<?= htmlspecialchars($u['username']) ?></div>
      <div class="ali-sub"><?= htmlspecialchars($u['full_name']) ?></div>
    </div>
    <div class="ali-right">
      <div class="vip-badge-small" style="background:<?= vipBadgeColor((int)$u['vip_level']) ?>">V<?= $u['vip_level'] ?></div>
      <div class="status-badge status-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></div>
    </div>
  </div>
  <?php endwhile; endif; ?>
</div>

<?php include __DIR__.'/_footer.php'; ?>
<script>
var days = <?= json_encode($chartDays) ?>;
var deps = <?= json_encode($chartDep) ?>;
var wds  = <?= json_encode($chartWd) ?>;
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('adminChart');
  if (!ctx) return;
  if (typeof Chart !== 'undefined') {
    new Chart(ctx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: days,
        datasets: [
          { label: 'Deposit', data: deps, backgroundColor: 'rgba(16,185,129,0.7)' },
          { label: 'Penarikan', data: wds, backgroundColor: 'rgba(245,158,11,0.7)' }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#9ca3af' } } },
        scales: { x: { ticks: { color: '#9ca3af' } }, y: { ticks: { color: '#9ca3af' } } }
      }
    });
  } else {
    ctx.parentElement.innerHTML = '<p style="color:#9ca3af;text-align:center;padding:20px">Chart tidak tersedia. Install Chart.js untuk tampilkan grafik.</p>';
  }
});
</script>
