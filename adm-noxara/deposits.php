<?php
require_once __DIR__.'/../config/bootstrap.php';
requireAdmin();

// ── AJAX: ACC / Tolak manual ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isAjax()) {
    verifyCsrf();
    $action = sanitize($_POST['action'] ?? '');
    $depId  = (int)($_POST['dep_id'] ?? 0);
    if (!$depId) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);

    $dep = dbQuery("SELECT * FROM deposits WHERE id=$depId AND status='pending' LIMIT 1");
    if (!$dep || $dep->num_rows===0)
        jsonResponse(['success'=>false,'message'=>'Deposit tidak ditemukan atau sudah diproses']);
    $d = $dep->fetch_assoc();

    if ($action === 'acc') {
        $uid   = (int)$d['user_id'];
        $total = (float)$d['total_amount'] + (float)$d['bonus_amount'];
        dbQuery("UPDATE deposits SET status='paid',paid_at=NOW() WHERE id=$depId");
        dbQuery("UPDATE users SET balance=balance+$total,total_deposit=total_deposit+".(float)$d['original_amount']." WHERE id=$uid");
        addTransaction($uid,'deposit',$total,'Isi ulang via QRIS (acc manual admin)',$depId);
        processReferralCommission($uid,(float)$d['original_amount'],'deposit');
        checkAndUpgradeVip($uid);
        addNotification($uid,'Deposit Berhasil! ✅','Isi ulang '.formatRupiah($total).' dikonfirmasi admin dan masuk ke saldo kamu.','success');
        logAdminAction((int)($_SESSION['admin_id']??0),'ACC_DEPOSIT',"ID $depId user $uid total ".formatRupiah($total));
        jsonResponse(['success'=>true,'message'=>'Deposit berhasil di-ACC!']);
    }

    if ($action === 'reject') {
        dbQuery("UPDATE deposits SET status='expired' WHERE id=$depId");
        addNotification((int)$d['user_id'],'Deposit Ditolak','Deposit kamu tidak dapat dikonfirmasi.','warning');
        logAdminAction((int)($_SESSION['admin_id']??0),'REJECT_DEPOSIT',"ID $depId ditolak");
        jsonResponse(['success'=>true,'message'=>'Deposit ditolak.']);
    }

    jsonResponse(['success'=>false,'message'=>'Action tidak dikenal']);
}

$adminPageTitle='Manajemen Deposit'; $currentAdminPage='deposits';
include __DIR__.'/_header.php';
$status=sanitize($_GET['status']??'all'); $page=max(1,(int)($_GET['p']??1)); $limit=20; $offset=($page-1)*$limit;
$where="WHERE 1=1"; if($status!=='all') $where.=" AND d.status='".dbEscape($status)."'";
$total=(int)(dbQuery("SELECT COUNT(*) as c FROM deposits d $where")->fetch_assoc()['c']??0);
$deps=dbQuery("SELECT d.*,u.username,u.id as uid FROM deposits d JOIN users u ON d.user_id=u.id $where ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset");
$todayTotal=dbQuery("SELECT COALESCE(SUM(original_amount),0) as t FROM deposits WHERE status='paid' AND DATE(created_at)=CURDATE()")->fetch_assoc()['t']??0;
$totalAll=dbQuery("SELECT COALESCE(SUM(original_amount),0) as t FROM deposits WHERE status='paid'")->fetch_assoc()['t']??0;
$pendingCnt=(int)(dbQuery("SELECT COUNT(*) as c FROM deposits WHERE status='pending'")->fetch_assoc()['c']??0);
?>
<div class="admin-page-header">
  <h1>Manajemen Deposit</h1>
  <?php if($pendingCnt>0):?><span style="background:#ef4444;color:#fff;font-size:12px;padding:3px 10px;border-radius:20px;font-weight:600"><?=$pendingCnt?> Pending</span><?php endif;?>
</div>
<div class="admin-stats-grid">
  <div class="admin-stat-card"><div class="asc-info"><div class="asc-val"><?=formatRupiah((float)$todayTotal)?></div><div class="asc-label">Deposit Hari Ini</div></div></div>
  <div class="admin-stat-card"><div class="asc-info"><div class="asc-val"><?=formatRupiah((float)$totalAll)?></div><div class="asc-label">Total Semua Deposit</div></div></div>
</div>
<div class="admin-filters">
  <?php foreach(['all'=>'Semua','paid'=>'Berhasil','pending'=>'Pending','expired'=>'Expired','cancel'=>'Dibatalkan'] as $s=>$l):?>
  <a href="?status=<?=$s?>" class="btn btn-sm <?=$status===$s?'btn-primary':'btn-outline'?>"><?=$l?></a>
  <?php endforeach;?>
</div>
<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>User</th><th>Nominal</th><th>Total Bayar</th><th>Status</th><th>Voucher</th><th>Waktu</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if($deps&&$deps->num_rows>0):while($d=$deps->fetch_assoc()):?>
      <tr>
        <td>@<?=htmlspecialchars($d['username'])?></td>
        <td><?=formatRupiah((float)$d['original_amount'])?></td>
        <td><?=formatRupiah((float)$d['total_amount'])?></td>
        <td><span class="status-badge status-<?=$d['status']?>"><?=ucfirst($d['status'])?></span></td>
        <td><?=htmlspecialchars($d['voucher_code']??'-')?></td>
        <td><?=date('d M H:i',strtotime($d['created_at']))?></td>
        <td>
          <?php if($d['status']==='pending'):?>
          <button class="btn btn-sm" style="background:#22c55e;color:#fff;padding:4px 10px;font-size:11px" onclick="accDep(<?=$d['id']?>,'<?=htmlspecialchars($d['username'])?>','<?=formatRupiah((float)$d['total_amount']+(float)$d['bonus_amount'])?>',this)">✓ ACC</button>
          <button class="btn btn-sm btn-outline" style="padding:4px 10px;font-size:11px;margin-left:4px" onclick="rejectDep(<?=$d['id']?>,'<?=htmlspecialchars($d['username'])?>',this)">✗ Tolak</button>
          <?php else:?>—<?php endif;?>
        </td>
      </tr>
      <?php endwhile;else:?><tr><td colspan="7" class="text-center text-muted">Tidak ada deposit</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>
<script>
const CSRF='<?=csrfToken()?>';
function accDep(id,user,amt,btn){
  if(!confirm('ACC deposit @'+user+' sebesar '+amt+'?\nSaldo user langsung ditambah.'))return;
  btn.disabled=true; btn.textContent='...';
  fetch(location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:'csrf_token='+CSRF+'&action=acc&dep_id='+id})
  .then(r=>r.json()).then(d=>{
    alert(d.message);
    if(d.success)location.reload();
    else btn.disabled=false;
  });
}
function rejectDep(id,user,btn){
  if(!confirm('Tolak deposit @'+user+'?'))return;
  btn.disabled=true;
  fetch(location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:'csrf_token='+CSRF+'&action=reject&dep_id='+id})
  .then(r=>r.json()).then(d=>{
    alert(d.message);
    if(d.success)location.reload();
    else btn.disabled=false;
  });
}
</script>
<?php include __DIR__.'/_footer.php';?>
