<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle='Manajemen Penarikan'; $currentAdminPage='withdrawals';

if($_SERVER['REQUEST_METHOD']==='POST'&&isAjax()){
    requireAdmin(); verifyCsrf();
    $action=$_POST['action']??''; $wid=(int)($_POST['wd_id']??0);
    if($action==='approve'){
        dbQuery("UPDATE withdrawals SET status='success',processed_at=NOW() WHERE id=$wid AND status='pending'");
        $wd=dbQuery("SELECT * FROM withdrawals WHERE id=$wid LIMIT 1")->fetch_assoc();
        if($wd){ addNotification((int)$wd['user_id'],'Penarikan Berhasil!','Penarikan '.formatRupiah((float)$wd['amount']).' telah diproses.','success'); }
        logAdminAction((int)$_SESSION['admin_id'],'approve_wd',"wid=$wid");
        jsonResponse(['success'=>true,'message'=>'Penarikan disetujui']);
    }
    if($action==='reject'){
        $reason=sanitize($_POST['reason']??'');
        $wd=dbQuery("SELECT * FROM withdrawals WHERE id=$wid LIMIT 1")->fetch_assoc();
        if($wd){
            dbQuery("UPDATE withdrawals SET status='rejected',reject_reason='".dbEscape($reason)."',processed_at=NOW() WHERE id=$wid");
            dbQuery("UPDATE users SET balance=balance+".(float)$wd['amount'].",total_withdraw=total_withdraw-".(float)$wd['amount']." WHERE id=".(int)$wd['user_id']);
            addTransaction((int)$wd['user_id'],'bonus',(float)$wd['amount'],'Pengembalian penarikan ditolak');
            addNotification((int)$wd['user_id'],'Penarikan Ditolak','Penarikan kamu ditolak. Alasan: '.$reason.' Saldo telah dikembalikan.','warning');
        }
        jsonResponse(['success'=>true,'message'=>'Penarikan ditolak & saldo dikembalikan']);
    }
}

include __DIR__.'/_header.php';
$status=sanitize($_GET['status']??'pending'); $page=max(1,(int)($_GET['p']??1)); $limit=20; $offset=($page-1)*$limit;
$where="WHERE 1=1"; if($status!=='all') $where.=" AND w.status='".dbEscape($status)."'";
$_r=dbQuery("SELECT COUNT(*) as c FROM withdrawals w $where"); $total=$_r?(int)$_r->fetch_assoc()['c']:0;
$wds=dbQuery("SELECT w.*,u.username,u.vip_level FROM withdrawals w JOIN users u ON w.user_id=u.id $where ORDER BY w.created_at DESC LIMIT $limit OFFSET $offset");
?>
<div class="admin-page-header"><h1>Manajemen Penarikan</h1></div>
<div class="admin-filters">
  <a href="?status=pending" class="btn btn-sm <?=$status==='pending'?'btn-primary':'btn-outline'?>">Pending <?php $pc=dbQuery("SELECT COUNT(*) as c FROM withdrawals WHERE status='pending'")->fetch_assoc(); $__r = $__r ? $__r['c'] : 0; if($pc>0):?><span class="nav-badge"><?=$pc?></span><?php endif;?></a>
  <a href="?status=success" class="btn btn-sm <?=$status==='success'?'btn-primary':'btn-outline'?>">Berhasil</a>
  <a href="?status=rejected" class="btn btn-sm <?=$status==='rejected'?'btn-primary':'btn-outline'?>">Ditolak</a>
  <a href="?status=all" class="btn btn-sm <?=$status==='all'?'btn-primary':'btn-outline'?>">Semua</a>
</div>
<div class="admin-card">
  <?php if($wds&&$wds->num_rows>0):while($w=$wds->fetch_assoc()):?>
  <div class="admin-wd-item">
    <div class="awi-header">
      <div class="awi-user">@<?=htmlspecialchars($w['username'])?> <span class="vip-badge-small" style="background:<?=vipBadgeColor((int)$w['vip_level'])?>">V<?=$w['vip_level']?></span></div>
      <div class="status-badge status-<?=$w['status']?>"><?=ucfirst($w['status'])?></div>
    </div>
    <div class="awi-detail">
      <div><span>Nominal</span> <strong><?=formatRupiah((float)$w['amount'])?></strong></div>
      <div><span>Diterima</span> <strong class="text-gold"><?=formatRupiah((float)$w['amount_received'])?></strong></div>
      <div><span>Bank</span> <strong><?=htmlspecialchars($w['bank_name'])?></strong></div>
      <div><span>Rekening</span> <strong><?=htmlspecialchars($w['account_number'])?></strong></div>
      <div><span>A/N</span> <strong><?=htmlspecialchars($w['account_name'])?></strong></div>
      <div><span>Waktu</span> <strong><?=date('d M Y H:i',strtotime($w['created_at']))?></strong></div>
      <?php if($w['reject_reason']):?><div><span>Alasan</span> <strong><?=htmlspecialchars($w['reject_reason'])?></strong></div><?php endif;?>
    </div>
    <?php if($w['status']==='pending'):?>
    <div class="awi-actions">
      <button class="btn btn-success btn-sm" onclick="approveWd(<?=$w['id']?>)"><i data-lucide="check"></i> Setujui</button>
      <button class="btn btn-danger btn-sm" onclick="rejectWd(<?=$w['id']?>)"><i data-lucide="x"></i> Tolak</button>
    </div>
    <?php endif;?>
  </div>
  <?php endwhile;else:?><div class="empty-state"><i data-lucide="inbox"></i><p>Tidak ada penarikan</p></div><?php endif;?>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal" style="display:none">
  <div class="modal-box"><div class="modal-title">Tolak Penarikan</div>
    <textarea id="rejectReason" class="form-input mt-2" rows="3" placeholder="Alasan penolakan..."></textarea>
    <div class="modal-actions mt-2"><button class="btn btn-outline" onclick="document.getElementById('rejectModal').style.display='none'">Batal</button><button class="btn btn-danger" onclick="submitReject()">Tolak</button></div>
  </div>
</div>

<?php include __DIR__.'/_footer.php';?>
<script>
const CSRF='<?=csrfToken()?>'; let rejectWdId=null;
function approveWd(id){showConfirm('Setujui Penarikan','Yakin setujui penarikan ini?',()=>{postAction('approve',id,null,()=>location.reload());});}
function rejectWd(id){rejectWdId=id;document.getElementById('rejectModal').style.display='flex';}
function submitReject(){const r=document.getElementById('rejectReason').value;postAction('reject',rejectWdId,r,()=>location.reload());document.getElementById('rejectModal').style.display='none';}
function postAction(action,id,reason,cb){
  const body=`csrf_token=${CSRF}&action=${action}&wd_id=${id}${reason?'&reason='+encodeURIComponent(reason):''}`;
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body}).then(r=>r.json()).then(d=>{hideLoading();if(d.success){showToast(d.message,'success');if(cb)setTimeout(cb,1000);}else{showModal('error','Gagal',d.message);}});
}
</script>
