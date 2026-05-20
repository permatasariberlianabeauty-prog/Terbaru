<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $user=currentUser(); $uid=(int)$user['id'];
$pageTitle='Voucher'; $currentPage='voucher';
if($_SERVER['REQUEST_METHOD']==='POST'&&isAjax()){
    verifyCsrf();
    $code=sanitize($_POST['code']??'');
    $vc=dbEscape($code);
    $r=dbQuery("SELECT * FROM vouchers WHERE code='$vc' AND type='balance' AND status=1 AND (expired_at IS NULL OR expired_at>NOW()) LIMIT 1");
    if(!$r||$r->num_rows===0) jsonResponse(['success'=>false,'message'=>'Voucher tidak valid atau sudah expired']);
    $v=$r->fetch_assoc();
    if((int)$v['used_count']>=(int)$v['limit_total']) jsonResponse(['success'=>false,'message'=>'Voucher sudah habis']);
    if((int)$v['min_vip']>(int)$user['vip_level']) jsonResponse(['success'=>false,'message'=>'Voucher ini untuk VIP '.$v['min_vip'].'+']);
    $used=db()->prepare("SELECT id FROM voucher_uses WHERE voucher_id=? AND user_id=? LIMIT 1");
    $used->bind_param('ii',$v['id'],$uid);$used->execute();
    if($used->get_result()->num_rows>0) jsonResponse(['success'=>false,'message'=>'Voucher sudah kamu gunakan']);
    $used->close();
    $amount=(float)$v['bonus_amount'];
    dbQuery("UPDATE users SET balance=balance+$amount WHERE id=$uid");
    dbQuery("UPDATE vouchers SET used_count=used_count+1 WHERE id=".(int)$v['id']);
    $ins=db()->prepare("INSERT INTO voucher_uses (voucher_id,user_id) VALUES (?,?)");
    $ins->bind_param('ii',$v['id'],$uid);$ins->execute();$ins->close();
    addTransaction($uid,'voucher',$amount,'Voucher saldo: '.$code);
    addNotification($uid,'Voucher Berhasil!','Saldo '.formatRupiah($amount).' berhasil ditambahkan dari voucher.','success');
    jsonResponse(['success'=>true,'amount'=>$amount,'message'=>'Voucher berhasil! Saldo '.formatRupiah($amount).' ditambahkan.']);
}
$vouchers=dbQuery("SELECT * FROM vouchers WHERE status=1 AND (expired_at IS NULL OR expired_at>NOW()) ORDER BY min_vip ASC,id DESC");
include __DIR__.'/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header"><a href="<?=APP_URL?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title">Voucher</h1></div>
<div class="section-card">
  <h3 class="section-subtitle">Daftar Voucher Tersedia</h3>
  <?php if($vouchers&&$vouchers->num_rows>0):while($v=$vouchers->fetch_assoc()):
    $locked=(int)$v['min_vip']>(int)$user['vip_level'];
    $typeLabel=['deposit'=>'Isi Ulang','product'=>'Produk','balance'=>'Saldo Gratis'][$v['type']]??$v['type'];
  ?>
  <div class="voucher-card <?=$locked?'locked':''?>">
    <div class="vc-type"><?=$typeLabel?></div>
    <div class="vc-code"><?=htmlspecialchars($v['code'])?></div>
    <div class="vc-desc">
      <?php if($v['discount_percent']>0):?>Diskon <?=$v['discount_percent']?>%<?php endif;?>
      <?php if($v['bonus_amount']>0):?>Saldo <?=formatRupiah((float)$v['bonus_amount'])?><?php endif;?>
    </div>
    <?php if($v['min_vip']>0):?><div class="vc-vip">Min. VIP <?=$v['min_vip']?></div><?php endif;?>
    <div class="vc-limit">Sisa: <?=max(0,(int)$v['limit_total']-(int)$v['used_count'])?> / <?=$v['limit_total']?></div>
    <?php if($v['expired_at']):?><div class="vc-exp">Exp: <?=date('d M Y',strtotime($v['expired_at']))?></div><?php endif;?>
    <?php if($locked):?><div class="vc-locked"><i data-lucide="lock"></i> Perlu VIP <?=$v['min_vip']?></div><?php endif;?>
  </div>
  <?php endwhile;else:?><div class="empty-state"><i data-lucide="ticket"></i><p>Belum ada voucher tersedia</p></div><?php endif;?>
</div>
<div class="section-card">
  <h3 class="section-subtitle">Tukar Voucher Saldo Gratis</h3>
  <div class="input-wrapper"><i data-lucide="ticket" class="input-icon"></i><input type="text" id="voucherCode" class="form-input" placeholder="Masukkan kode voucher"></div>
  <button class="btn btn-primary btn-full mt-3" onclick="redeemVoucher()"><i data-lucide="gift"></i> Tukar Voucher</button>
</div>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php';?>
<?php include __DIR__.'/../includes/footer.php';?>
<script>
const CSRF='<?=csrfToken()?>';
function redeemVoucher(){
  const code=document.getElementById('voucherCode').value.trim();
  if(!code){showToast('Masukkan kode voucher','error');return;}
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&code=${encodeURIComponent(code)}`})
  .then(r=>r.json()).then(d=>{hideLoading();if(d.success){showModal('success','Berhasil!',d.message,()=>location.reload());}else{showModal('error','Gagal',d.message);}});
}
</script>
