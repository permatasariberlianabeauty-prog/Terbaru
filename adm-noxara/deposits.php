<?php
require_once __DIR__.'/../config/bootstrap.php';
requireAdmin();
$adminPageTitle='Manajemen Deposit'; $currentAdminPage='deposits';

// ============================================================
// HANDLE AJAX: Manual ACC deposit pending
// ============================================================
if ($_SERVER['REQUEST_METHOD']==='POST' && isAjax()) {
    verifyCsrf();
    $action = sanitize($_POST['action'] ?? '');

    // --- ACC Manual ---
    if ($action === 'acc') {
        $depId = (int)($_POST['dep_id'] ?? 0);
        if (!$depId) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);

        $dep = dbQuery("SELECT * FROM deposits WHERE id=$depId AND status='pending' LIMIT 1");
        if (!$dep || $dep->num_rows === 0)
            jsonResponse(['success'=>false,'message'=>'Deposit tidak ditemukan atau sudah diproses']);

        $d   = $dep->fetch_assoc();
        $uid = (int)$d['user_id'];
        $total = (float)$d['total_amount'] + (float)$d['bonus_amount'];

        // Tandai paid
        dbQuery("UPDATE deposits SET status='paid', paid_at=NOW() WHERE id=$depId");

        // Credit saldo user
        dbQuery("UPDATE users SET balance=balance+$total, total_deposit=total_deposit+".(float)$d['original_amount']." WHERE id=$uid");

        // Catat ke transactions
        addTransaction($uid, 'deposit', $total, 'Isi ulang via QRIS (acc manual admin)', $depId);

        // Proses voucher jika ada
        if (!empty($d['voucher_code'])) {
            $vr = dbQuery("SELECT id FROM vouchers WHERE code='".dbEscape($d['voucher_code'])."' LIMIT 1");
            if ($vr && $vr->num_rows > 0) {
                $vid = (int)$vr->fetch_assoc()['id'];
                dbQuery("UPDATE vouchers SET used_count=used_count+1 WHERE id=$vid");
                $ins2 = db()->prepare("INSERT IGNORE INTO voucher_uses (voucher_id,user_id) VALUES (?,?)");
                $ins2->bind_param('ii', $vid, $uid);
                $ins2->execute();
                $ins2->close();
            }
        }

        processReferralCommission($uid, (float)$d['original_amount'], 'deposit');
        checkAndUpgradeVip($uid);
        addNotification($uid, 'Deposit Berhasil! ✅', 'Isi ulang '.formatRupiah($total).' telah dikonfirmasi admin dan masuk ke saldo kamu.', 'success');

        // Log admin action
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        logAdminAction($adminId, 'ACC_DEPOSIT', "Deposit ID $depId user_id $uid total ".formatRupiah($total));

        jsonResponse(['success'=>true,'message'=>'Deposit berhasil di-ACC. Saldo user sudah ditambah.']);
    }

    // --- Reject / Expire Manual ---
    if ($action === 'reject') {
        $depId = (int)($_POST['dep_id'] ?? 0);
        if (!$depId) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);

        $dep = dbQuery("SELECT id,user_id FROM deposits WHERE id=$depId AND status='pending' LIMIT 1");
        if (!$dep || $dep->num_rows === 0)
            jsonResponse(['success'=>false,'message'=>'Deposit tidak ditemukan atau sudah diproses']);

        $d = $dep->fetch_assoc();
        dbQuery("UPDATE deposits SET status='expired' WHERE id=$depId");
        addNotification((int)$d['user_id'], 'Deposit Ditolak', 'Deposit kamu tidak dapat dikonfirmasi dan telah ditolak admin.', 'warning');

        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        logAdminAction($adminId, 'REJECT_DEPOSIT', "Deposit ID $depId ditolak");

        jsonResponse(['success'=>true,'message'=>'Deposit ditandai sebagai expired/ditolak.']);
    }

    jsonResponse(['success'=>false,'message'=>'Action tidak dikenal']);
}

include __DIR__.'/_header.php';

$status  = sanitize($_GET['status'] ?? 'all');
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1,(int)($_GET['p'] ?? 1));
$limit   = 20; $offset = ($page-1)*$limit;

$where = "WHERE 1=1";
if ($status !== 'all') $where .= " AND d.status='".dbEscape($status)."'";
if ($search) $where .= " AND (u.username LIKE '%".dbEscape($search)."%' OR d.transaction_id LIKE '%".dbEscape($search)."%')";

$total      = (int)(dbQuery("SELECT COUNT(*) as c FROM deposits d JOIN users u ON d.user_id=u.id $where")->fetch_assoc()['c'] ?? 0);
$deps       = dbQuery("SELECT d.*,u.username,u.id as uid FROM deposits d JOIN users u ON d.user_id=u.id $where ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset");
$todayTotal = dbQuery("SELECT COALESCE(SUM(original_amount),0) as t FROM deposits WHERE status='paid' AND DATE(created_at)=CURDATE()")->fetch_assoc()['t'] ?? 0;
$totalAll   = dbQuery("SELECT COALESCE(SUM(original_amount),0) as t FROM deposits WHERE status='paid'")->fetch_assoc()['t'] ?? 0;
$pendingCnt = dbQuery("SELECT COUNT(*) as c FROM deposits WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
?>

<div class="admin-page-header">
  <h1>Manajemen Deposit</h1>
  <?php if ($pendingCnt > 0): ?>
  <span class="badge-alert"><?= $pendingCnt ?> Pending butuh perhatian</span>
  <?php endif; ?>
</div>

<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div class="asc-info">
      <div class="asc-val"><?= formatRupiah((float)$todayTotal) ?></div>
      <div class="asc-label">Deposit Hari Ini</div>
    </div>
  </div>
  <div class="admin-stat-card">
    <div class="asc-info">
      <div class="asc-val"><?= formatRupiah((float)$totalAll) ?></div>
      <div class="asc-label">Total Semua Deposit</div>
    </div>
  </div>
  <div class="admin-stat-card <?= $pendingCnt > 0 ? 'asc-warning' : '' ?>">
    <div class="asc-info">
      <div class="asc-val"><?= $pendingCnt ?></div>
      <div class="asc-label">Pending Belum Terkonfirmasi</div>
    </div>
  </div>
</div>

<!-- Filter tabs -->
<div class="admin-filters">
  <?php foreach(['all'=>'Semua','paid'=>'Berhasil','pending'=>'Pending','expired'=>'Expired','cancel'=>'Dibatalkan'] as $s=>$l): ?>
  <a href="?status=<?= $s ?><?= $search ? '&search='.urlencode($search) : '' ?>"
     class="btn btn-sm <?= $status===$s ? 'btn-primary' : 'btn-outline' ?>">
    <?= $l ?>
    <?php if ($s==='pending' && $pendingCnt > 0): ?>
      <span class="badge-count"><?= $pendingCnt ?></span>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Search -->
<div class="admin-search-bar mb-3">
  <form method="GET" style="display:flex;gap:8px">
    <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
    <input type="text" name="search" class="form-input" placeholder="Cari username / transaction ID..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary btn-sm">Cari</button>
    <?php if ($search): ?><a href="?status=<?= $status ?>" class="btn btn-outline btn-sm">Reset</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Transaction ID</th>
          <th>Nominal</th>
          <th>Total Bayar</th>
          <th>Bonus</th>
          <th>Status</th>
          <th>Waktu</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($deps && $deps->num_rows > 0): while ($d = $deps->fetch_assoc()): ?>
      <tr class="<?= $d['status']==='pending' ? 'row-pending' : '' ?>">
        <td>
          <strong>@<?= htmlspecialchars($d['username']) ?></strong>
          <div class="text-xs text-muted">UID #<?= $d['uid'] ?></div>
        </td>
        <td>
          <code class="tx-id-code" title="<?= htmlspecialchars($d['transaction_id']) ?>">
            <?= htmlspecialchars(substr($d['transaction_id'], 0, 18)) ?>...
          </code>
        </td>
        <td><?= formatRupiah((float)$d['original_amount']) ?></td>
        <td><?= formatRupiah((float)$d['total_amount']) ?></td>
        <td><?= $d['bonus_amount'] > 0 ? '+'.formatRupiah((float)$d['bonus_amount']) : '-' ?></td>
        <td><span class="status-badge status-<?= $d['status'] ?>"><?= ucfirst($d['status']) ?></span></td>
        <td>
          <div><?= date('d M Y', strtotime($d['created_at'])) ?></div>
          <div class="text-xs text-muted"><?= date('H:i', strtotime($d['created_at'])) ?> WIB</div>
          <?php if ($d['status']==='pending' && $d['expired_at']): ?>
          <div class="text-xs <?= strtotime($d['expired_at']) < time() ? 'text-danger' : 'text-warning' ?>">
            Exp: <?= date('H:i', strtotime($d['expired_at'])) ?>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($d['status'] === 'pending'): ?>
          <div style="display:flex;gap:6px;flex-wrap:wrap">
            <button class="btn btn-success btn-xs"
              onclick="accDeposit(<?= $d['id'] ?>, '<?= htmlspecialchars($d['username']) ?>', '<?= formatRupiah((float)$d['total_amount']+(float)$d['bonus_amount']) ?>')">
              <i data-lucide="check"></i> ACC
            </button>
            <button class="btn btn-danger btn-xs"
              onclick="rejectDeposit(<?= $d['id'] ?>, '<?= htmlspecialchars($d['username']) ?>')">
              <i data-lucide="x"></i> Tolak
            </button>
          </div>
          <?php elseif ($d['status'] === 'paid'): ?>
          <span class="text-success text-xs">✓ Diproses</span>
          <?php else: ?>
          <span class="text-muted text-xs">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada deposit ditemukan</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total > $limit): ?>
  <div class="admin-pagination">
    <?php for ($i=1; $i<=ceil($total/$limit); $i++): ?>
    <a href="?status=<?= $status ?>&p=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"
       class="page-btn <?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<style>
.row-pending { background: rgba(245,158,11,0.06) !important; }
.row-pending td { border-left: 0; }
.row-pending:first-child td:first-child { border-left: 3px solid #f59e0b; }
.badge-alert {
  background: #ef4444; color: #fff;
  font-size: 12px; padding: 4px 10px;
  border-radius: 20px; font-weight: 600;
}
.badge-count {
  background: #ef4444; color: #fff;
  font-size: 10px; padding: 1px 6px;
  border-radius: 10px; margin-left: 4px;
}
.asc-warning { border-color: #f59e0b !important; }
.asc-warning .asc-val { color: #f59e0b !important; }
.tx-id-code {
  font-size: 11px; background: rgba(255,255,255,0.05);
  padding: 2px 6px; border-radius: 4px;
  cursor: pointer; word-break: break-all;
}
.btn-xs { padding: 4px 10px !important; font-size: 11px !important; }
.text-xs { font-size: 11px; }
.text-danger { color: #ef4444; }
.text-warning { color: #f59e0b; }
.text-success { color: #22c55e; }
.py-4 { padding: 16px 0; }
.pending-note {
  background: rgba(245,158,11,0.1);
  border-left: 3px solid #f59e0b;
  padding: 8px 12px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 13px;
}
</style>

<script>
const CSRF = '<?= csrfToken() ?>';

function accDeposit(depId, username, amount) {
  if (!confirm(`ACC deposit @${username} sebesar ${amount}?\n\nSaldo user akan langsung ditambah.`)) return;
  showAdminLoading();
  fetch(window.location.href, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: `csrf_token=${CSRF}&action=acc&dep_id=${depId}`
  })
  .then(r => r.json())
  .then(d => {
    hideAdminLoading();
    if (d.success) {
      showAdminToast(d.message, 'success');
      setTimeout(() => location.reload(), 1200);
    } else {
      showAdminToast(d.message || 'Gagal ACC deposit', 'error');
    }
  })
  .catch(() => { hideAdminLoading(); showAdminToast('Terjadi kesalahan', 'error'); });
}

function rejectDeposit(depId, username) {
  if (!confirm(`Tolak deposit @${username}?\n\nDeposit akan ditandai expired.`)) return;
  showAdminLoading();
  fetch(window.location.href, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: `csrf_token=${CSRF}&action=reject&dep_id=${depId}`
  })
  .then(r => r.json())
  .then(d => {
    hideAdminLoading();
    if (d.success) {
      showAdminToast(d.message, 'success');
      setTimeout(() => location.reload(), 1200);
    } else {
      showAdminToast(d.message || 'Gagal menolak deposit', 'error');
    }
  })
  .catch(() => { hideAdminLoading(); showAdminToast('Terjadi kesalahan', 'error'); });
}

// Fallback jika admin panel tidak punya showAdminLoading
function showAdminLoading() {
  if (typeof window.showLoading === 'function') showLoading();
}
function hideAdminLoading() {
  if (typeof window.hideLoading === 'function') hideLoading();
}
function showAdminToast(msg, type) {
  if (typeof window.showToast === 'function') {
    showToast(msg, type);
  } else {
    alert(msg);
  }
}
</script>

<?php include __DIR__.'/_footer.php'; ?>
