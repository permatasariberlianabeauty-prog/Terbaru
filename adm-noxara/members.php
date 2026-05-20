<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle='Manajemen Member'; $currentAdminPage='members';

if($_SERVER['REQUEST_METHOD']==='POST'&&isAjax()){
    requireAdmin(); verifyCsrf();
    $action=$_POST['action']??'';
    $uid=(int)($_POST['user_id']??0);
    if($action==='block')     { dbQuery("UPDATE users SET status='blocked' WHERE id=$uid"); logAdminAction((int)$_SESSION['admin_id'],'block_user',"uid=$uid"); jsonResponse(['success'=>true,'message'=>'User diblokir']); }
    if($action==='unblock')   { dbQuery("UPDATE users SET status='active' WHERE id=$uid"); logAdminAction((int)$_SESSION['admin_id'],'unblock_user',"uid=$uid"); jsonResponse(['success'=>true,'message'=>'User diaktifkan']); }
    if($action==='freeze')    { dbQuery("UPDATE users SET status='frozen' WHERE id=$uid"); jsonResponse(['success'=>true,'message'=>'Saldo user dibekukan']); }
    if($action==='add_balance'){
        $amount=(float)($_POST['amount']??0); $type=sanitize($_POST['type']??'bonus');
        dbQuery("UPDATE users SET balance=balance+$amount WHERE id=$uid");
        addTransaction($uid,$type,$amount,'Penambahan saldo oleh admin');
        logAdminAction((int)$_SESSION['admin_id'],'add_balance',"uid=$uid,amount=$amount");
        jsonResponse(['success'=>true,'message'=>'Saldo berhasil ditambahkan']);
    }
    if($action==='reset_pin') { dbQuery("UPDATE users SET pin=NULL WHERE id=$uid"); jsonResponse(['success'=>true,'message'=>'PIN direset']); }
    if($action==='reset_pw')  {
        $newPw=hashPassword('noxara123');
        dbQuery("UPDATE users SET password='$newPw' WHERE id=$uid");
        jsonResponse(['success'=>true,'message'=>'Password direset ke: noxara123']);
    }
    if($action==='get_detail'){
        $r=dbQuery("SELECT u.*,ub.bank_name,ub.account_name,ub.account_number FROM users u LEFT JOIN user_banks ub ON u.id=ub.user_id WHERE u.id=$uid LIMIT 1");
        if($r&&$r->num_rows>0) jsonResponse(['success'=>true,'user'=>$r->fetch_assoc()]);
        jsonResponse(['success'=>false,'message'=>'User tidak ditemukan']);
    }
}

include __DIR__.'/_header.php';
$search=sanitize($_GET['search']??''); $status=sanitize($_GET['status']??'all'); $vip=sanitize($_GET['vip']??'all');
$page=max(1,(int)($_GET['p']??1)); $limit=20; $offset=($page-1)*$limit;
$where="WHERE 1=1";
if($search) $where.=" AND (username LIKE '%".dbEscape($search)."%' OR full_name LIKE '%".dbEscape($search)."%' OR email LIKE '%".dbEscape($search)."%' OR phone LIKE '%".dbEscape($search)."%')";
if($status!=='all') $where.=" AND status='".dbEscape($status)."'";
if($vip!=='all') $where.=" AND vip_level=".dbEscape($vip);
$total=(int)(dbQuery("SELECT COUNT(*) as c FROM users $where")->fetch_assoc()['c']??0);
$users=dbQuery("SELECT * FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
?>
<div class="admin-page-header"><h1>Manajemen Member <span class="badge-count"><?=$total?></span></h1></div>
<div class="admin-filters">
  <input type="text" class="admin-search" placeholder="Cari username/nama/email..." value="<?=htmlspecialchars($search)?>" onchange="applyFilter()">
  <select class="admin-select" onchange="applyFilter()" id="fStatus"><option value="all">Semua Status</option><option value="active" <?=$status==='active'?'selected':''?>>Aktif</option><option value="blocked" <?=$status==='blocked'?'selected':''?>>Diblokir</option><option value="frozen" <?=$status==='frozen'?'selected':''?>>Dibekukan</option></select>
  <select class="admin-select" onchange="applyFilter()" id="fVip"><option value="all">Semua VIP</option><?php for($v=0;$v<=3;$v++):?><option value="<?=$v?>" <?=$vip==(string)$v?'selected':''?>>VIP <?=$v?></option><?php endfor;?></select>
</div>
<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>User</th><th>VIP</th><th>Saldo</th><th>Deposit</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if($users&&$users->num_rows>0):while($u=$users->fetch_assoc()):?>
      <tr>
        <td><div class="at-user"><div class="at-avatar"><?=strtoupper(substr($u['username'],0,1))?></div><div><div class="at-name">@<?=htmlspecialchars($u['username'])?></div><div class="at-sub"><?=htmlspecialchars($u['email'])?></div></div></div></td>
        <td><span class="vip-badge-small" style="background:<?=vipBadgeColor((int)$u['vip_level'])?>">V<?=$u['vip_level']?></span></td>
        <td><?=formatRupiah((float)$u['balance'])?></td>
        <td><?=formatRupiah((float)$u['total_deposit'])?></td>
        <td><span class="status-badge status-<?=$u['status']?>"><?=ucfirst($u['status'])?></span></td>
        <td>
          <button class="btn btn-xs btn-outline" onclick="viewUser(<?=$u['id']?>)"><i data-lucide="eye"></i></button>
          <?php if($u['status']==='active'):?>
          <button class="btn btn-xs btn-danger" onclick="memberAction('block',<?=$u['id']?>)"><i data-lucide="ban"></i></button>
          <?php else:?>
          <button class="btn btn-xs btn-success" onclick="memberAction('unblock',<?=$u['id']?>)"><i data-lucide="check"></i></button>
          <?php endif;?>
          <button class="btn btn-xs btn-warning" onclick="addBalanceModal(<?=$u['id']?>,<?=htmlspecialchars(json_encode($u['username']))?>)"><i data-lucide="plus"></i></button>
        </td>
      </tr>
      <?php endwhile;else:?><tr><td colspan="6" class="text-center text-muted">Tidak ada member</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
  <?php if($total>$limit):?><div class="admin-pagination"><?php for($i=1;$i<=ceil($total/$limit);$i++):?><a href="?search=<?=urlencode($search)?>&status=<?=$status?>&vip=<?=$vip?>&p=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a><?php endfor;?></div><?php endif;?>
</div>

<!-- User Detail Modal -->
<div class="modal-overlay" id="userDetailModal" style="display:none">
  <div class="modal-box modal-lg"><div class="modal-title">Detail Member</div><div id="userDetailContent"></div>
    <div class="modal-actions">
      <button class="btn btn-danger btn-sm" id="modalBtnBlock"></button>
      <button class="btn btn-warning btn-sm" onclick="resetPin()">Reset PIN</button>
      <button class="btn btn-warning btn-sm" onclick="resetPw()">Reset PW</button>
      <button class="btn btn-outline" onclick="document.getElementById('userDetailModal').style.display='none'">Tutup</button>
    </div>
  </div>
</div>

<!-- Add Balance Modal -->
<div class="modal-overlay" id="balanceModal" style="display:none">
  <div class="modal-box"><div class="modal-title">Tambah Saldo</div>
    <input type="number" id="balAmount" class="form-input mt-2" placeholder="Nominal">
    <div class="modal-actions mt-2">
      <button class="btn btn-outline" onclick="document.getElementById('balanceModal').style.display='none'">Batal</button>
      <button class="btn btn-primary" onclick="submitAddBalance()">Tambah</button>
    </div>
  </div>
</div>

<?php include __DIR__.'/_footer.php';?>
<script>
const CSRF='<?=csrfToken()?>'; let currentUid=null;
function applyFilter(){const s=document.querySelector('.admin-search').value;const st=document.getElementById('fStatus').value;const v=document.getElementById('fVip').value;location.href=`?search=${encodeURIComponent(s)}&status=${st}&vip=${v}`;}
function memberAction(action,uid){
  showConfirm('Konfirmasi','Yakin '+action+' user ini?',()=>{
    fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=${action}&user_id=${uid}`})
    .then(r=>r.json()).then(d=>{if(d.success){showToast(d.message,'success');setTimeout(()=>location.reload(),1000);}else{showToast(d.message,'error');}});
  });
}
function viewUser(uid){
  currentUid=uid;
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=get_detail&user_id=${uid}`})
  .then(r=>r.json()).then(d=>{
    if(!d.success)return;
    const u=d.user;
    document.getElementById('userDetailContent').innerHTML=`
      <div class="tx-detail-row"><span>Username</span><strong>@${u.username}</strong></div>
      <div class="tx-detail-row"><span>Nama</span><strong>${u.full_name}</strong></div>
      <div class="tx-detail-row"><span>Email</span><strong>${u.email}</strong></div>
      <div class="tx-detail-row"><span>No. HP</span><strong>${u.phone}</strong></div>
      <div class="tx-detail-row"><span>VIP</span><strong>VIP ${u.vip_level}</strong></div>
      <div class="tx-detail-row"><span>Saldo</span><strong>Rp ${Number(u.balance).toLocaleString('id-ID')}</strong></div>
      <div class="tx-detail-row"><span>Total Deposit</span><strong>Rp ${Number(u.total_deposit).toLocaleString('id-ID')}</strong></div>
      <div class="tx-detail-row"><span>Bank</span><strong>${u.bank_name||'-'} - ${u.account_number||'-'}</strong></div>
      <div class="tx-detail-row"><span>Status</span><strong>${u.status}</strong></div>
      <div class="tx-detail-row"><span>Bergabung</span><strong>${u.created_at}</strong></div>`;
    const blockBtn=document.getElementById('modalBtnBlock');
    blockBtn.textContent=u.status==='active'?'Blokir':'Aktifkan';
    blockBtn.onclick=()=>memberAction(u.status==='active'?'block':'unblock',uid);
    document.getElementById('userDetailModal').style.display='flex';
  });
}
function addBalanceModal(uid,name){currentUid=uid;document.getElementById('balanceModal').style.display='flex';}
function submitAddBalance(){
  const amount=document.getElementById('balAmount').value;
  if(!amount){showToast('Masukkan nominal','error');return;}
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=add_balance&user_id=${currentUid}&amount=${amount}&type=bonus`})
  .then(r=>r.json()).then(d=>{document.getElementById('balanceModal').style.display='none';if(d.success){showToast(d.message,'success');setTimeout(()=>location.reload(),1000);}});
}
function resetPin(){memberAction('reset_pin',currentUid);}
function resetPw(){memberAction('reset_pw',currentUid);}
</script>
