<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle='Manajemen Deposit'; $currentAdminPage='deposits';
include __DIR__.'/_header.php';
$status=sanitize($_GET['status']??'all'); $page=max(1,(int)($_GET['p']??1)); $limit=20; $offset=($page-1)*$limit;
$where="WHERE 1=1"; if($status!=='all') $where.=" AND d.status='".dbEscape($status)."'";
$total=(int)(dbQuery("SELECT COUNT(*) as c FROM deposits d $where")->fetch_assoc()['c']??0);
$deps=dbQuery("SELECT d.*,u.username FROM deposits d JOIN users u ON d.user_id=u.id $where ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset");
$todayTotal=dbQuery("SELECT COALESCE(SUM(original_amount),0) as t FROM deposits WHERE status='paid' AND DATE(created_at)=CURDATE()")->fetch_assoc()['t']??0;
$totalAll=dbQuery("SELECT COALESCE(SUM(original_amount),0) as t FROM deposits WHERE status='paid'")->fetch_assoc()['t']??0;
?>
<div class="admin-page-header"><h1>Manajemen Deposit</h1></div>
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
      <thead><tr><th>User</th><th>Nominal</th><th>Total Bayar</th><th>Status</th><th>Voucher</th><th>Waktu</th></tr></thead>
      <tbody>
      <?php if($deps&&$deps->num_rows>0):while($d=$deps->fetch_assoc()):?>
      <tr>
        <td>@<?=htmlspecialchars($d['username'])?></td>
        <td><?=formatRupiah((float)$d['original_amount'])?></td>
        <td><?=formatRupiah((float)$d['total_amount'])?></td>
        <td><span class="status-badge status-<?=$d['status']?>"><?=ucfirst($d['status'])?></span></td>
        <td><?=htmlspecialchars($d['voucher_code']??'-')?></td>
        <td><?=date('d M H:i',strtotime($d['created_at']))?></td>
      </tr>
      <?php endwhile;else:?><tr><td colspan="6" class="text-center text-muted">Tidak ada deposit</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/_footer.php';?>
