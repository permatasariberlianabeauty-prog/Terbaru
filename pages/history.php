<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Riwayat Transaksi';
$currentPage = 'history';

$type   = sanitize($_GET['type'] ?? 'all');
$status = sanitize($_GET['status'] ?? 'all');
$period = sanitize($_GET['period'] ?? 'month');
$search = sanitize($_GET['search'] ?? '');
$page   = max(1,(int)($_GET['p'] ?? 1));
$limit  = 20; $offset = ($page-1)*$limit;

$where = "WHERE user_id=$uid";
if ($type !== 'all') $where .= " AND type='".dbEscape($type)."'";
if ($status !== 'all') $where .= " AND status='".dbEscape($status)."'";
if ($period === 'today') $where .= " AND DATE(created_at)=CURDATE()";
elseif ($period === 'week') $where .= " AND created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)";
elseif ($period === 'month') $where .= " AND DATE_FORMAT(created_at,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')";
if ($search) $where .= " AND (description LIKE '%".dbEscape($search)."%')";

$totalRows = dbQuery("SELECT COUNT(*) as c FROM transactions $where")->fetch_assoc()['c'];
$txs = dbQuery("SELECT * FROM transactions $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");

// Summary
$sumIn  = dbQuery("SELECT COALESCE(SUM(amount),0) as t FROM transactions WHERE user_id=$uid AND amount>0 AND DATE_FORMAT(created_at,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")->fetch_assoc()['t'];
$sumOut = dbQuery("SELECT COALESCE(SUM(ABS(amount)),0) as t FROM transactions WHERE user_id=$uid AND amount<0 AND DATE_FORMAT(created_at,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")->fetch_assoc()['t'];

include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
  <a href="<?= APP_URL ?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a>
  <h1 class="page-title">Riwayat</h1>
</div>

<!-- Summary -->
<div class="history-summary">
  <div class="hs-item income"><div class="hs-val">+<?= formatRupiah((float)$sumIn) ?></div><div class="hs-label">Pemasukan Bulan Ini</div></div>
  <div class="hs-item expense"><div class="hs-val">-<?= formatRupiah((float)$sumOut) ?></div><div class="hs-label">Pengeluaran Bulan Ini</div></div>
</div>

<!-- Search -->
<div class="input-wrapper mb-3">
  <i data-lucide="search" class="input-icon"></i>
  <input type="text" id="histSearch" class="form-input" placeholder="Cari transaksi..." value="<?= htmlspecialchars($search) ?>" onchange="applyFilter()">
</div>

<!-- Filters -->
<div class="filter-scroll">
  <select class="filter-select" id="fType" onchange="applyFilter()">
    <option value="all" <?= $type==='all'?'selected':'' ?>>Semua Jenis</option>
    <option value="deposit" <?= $type==='deposit'?'selected':'' ?>>Deposit</option>
    <option value="withdraw" <?= $type==='withdraw'?'selected':'' ?>>Penarikan</option>
    <option value="mining" <?= $type==='mining'?'selected':'' ?>>Mining</option>
    <option value="referral" <?= $type==='referral'?'selected':'' ?>>Referral</option>
    <option value="bonus" <?= $type==='bonus'?'selected':'' ?>>Bonus</option>
    <option value="purchase" <?= $type==='purchase'?'selected':'' ?>>Pembelian</option>
    <option value="daily" <?= $type==='daily'?'selected':'' ?>>Daily</option>
  </select>
  <select class="filter-select" id="fStatus" onchange="applyFilter()">
    <option value="all" <?= $status==='all'?'selected':'' ?>>Semua Status</option>
    <option value="success" <?= $status==='success'?'selected':'' ?>>Berhasil</option>
    <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
    <option value="failed" <?= $status==='failed'?'selected':'' ?>>Gagal</option>
  </select>
  <select class="filter-select" id="fPeriod" onchange="applyFilter()">
    <option value="all" <?= $period==='all'?'selected':'' ?>>Semua Waktu</option>
    <option value="today" <?= $period==='today'?'selected':'' ?>>Hari Ini</option>
    <option value="week" <?= $period==='week'?'selected':'' ?>>7 Hari</option>
    <option value="month" <?= $period==='month'?'selected':'' ?>>Bulan Ini</option>
  </select>
</div>

<!-- Transaction List -->
<div class="tx-full-list">
  <?php if ($txs && $txs->num_rows > 0): while ($tx = $txs->fetch_assoc()):
    $icons = ['deposit'=>'plus-circle','withdraw'=>'minus-circle','mining'=>'cpu','referral'=>'users','bonus'=>'gift','purchase'=>'shopping-bag','daily'=>'calendar-check'];
    $icon = $icons[$tx['type']] ?? 'circle';
  ?>
  <div class="tx-item-full" onclick="showTxDetail(<?= htmlspecialchars(json_encode($tx)) ?>)">
    <div class="tif-icon tx-<?= $tx['type'] ?>"><i data-lucide="<?= $icon ?>"></i></div>
    <div class="tif-info">
      <div class="tif-desc"><?= htmlspecialchars($tx['description'] ?? ucfirst($tx['type'])) ?></div>
      <div class="tif-date"><?= date('d M Y H:i', strtotime($tx['created_at'])) ?></div>
      <div class="tif-id">ID: #<?= $tx['id'] ?></div>
    </div>
    <div class="tif-right">
      <div class="tif-amount <?= $tx['amount']>=0?'positive':'negative' ?>"><?= ($tx['amount']>=0?'+':'').formatRupiah(abs((float)$tx['amount'])) ?></div>
      <div class="status-badge status-<?= $tx['status'] ?>"><?= ucfirst($tx['status']) ?></div>
    </div>
  </div>
  <?php endwhile; else: ?>
  <div class="empty-state"><i data-lucide="inbox"></i><p>Tidak ada transaksi</p></div>
  <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalRows > $limit): ?>
<div class="pagination">
  <?php for($i=1;$i<=ceil($totalRows/$limit);$i++): ?>
  <a href="?type=<?= $type ?>&status=<?= $status ?>&period=<?= $period ?>&p=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
</div>

<!-- TX Detail Modal -->
<div class="modal-overlay" id="txDetailModal" style="display:none">
  <div class="modal-box">
    <div class="modal-title">Detail Transaksi</div>
    <div id="txDetailContent"></div>
    <div class="modal-actions">
      <button class="btn btn-outline btn-sm" onclick="copyTxId()"><i data-lucide="copy"></i> Salin ID</button>
      <button class="btn btn-primary" onclick="document.getElementById('txDetailModal').style.display='none'">Tutup</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
let currentTxId=null;
function applyFilter(){
  const t=document.getElementById('fType').value;
  const s=document.getElementById('fStatus').value;
  const p=document.getElementById('fPeriod').value;
  const q=document.getElementById('histSearch').value;
  location.href=`?type=${t}&status=${s}&period=${p}&search=${encodeURIComponent(q)}`;
}
function showTxDetail(tx){
  currentTxId=tx.id;
  document.getElementById('txDetailContent').innerHTML=`
    <div class="tx-detail-row"><span>ID Transaksi</span><strong>#${tx.id}</strong></div>
    <div class="tx-detail-row"><span>Jenis</span><strong>${tx.type}</strong></div>
    <div class="tx-detail-row"><span>Jumlah</span><strong>${tx.amount>=0?'+':''}Rp ${Math.abs(tx.amount).toLocaleString('id-ID')}</strong></div>
    <div class="tx-detail-row"><span>Status</span><strong>${tx.status}</strong></div>
    <div class="tx-detail-row"><span>Keterangan</span><strong>${tx.description||'-'}</strong></div>
    <div class="tx-detail-row"><span>Tanggal</span><strong>${tx.created_at}</strong></div>`;
  document.getElementById('txDetailModal').style.display='flex';
}
function copyTxId(){
  navigator.clipboard.writeText('#'+currentTxId).then(()=>showToast('ID disalin!','success'));
}
</script>
