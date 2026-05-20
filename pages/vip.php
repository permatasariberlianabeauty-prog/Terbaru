<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $user=currentUser(); $uid=(int)$user['id'];
$pageTitle='VIP'; $currentPage='vip';
$vips=dbQuery("SELECT * FROM vip_settings ORDER BY level ASC");
include __DIR__.'/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header"><a href="<?=APP_URL?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title">Level VIP</h1></div>
<div class="current-vip-hero" style="background:linear-gradient(135deg,<?=vipBadgeColor((int)$user['vip_level'])?>,#0A0E1A)">
  <div class="cvh-badge animate-glow"><?=vipBadgeName((int)$user['vip_level'])?></div>
  <div class="cvh-name"><?=htmlspecialchars($user['full_name'])?></div>
  <div class="cvh-dep">Total Deposit: <?=formatRupiah((float)$user['total_deposit'])?></div>
</div>
<?php if($vips&&$vips->num_rows>0):while($v=$vips->fetch_assoc()):$isCurrent=(int)$v['level']===(int)$user['vip_level'];?>
<div class="vip-card <?=$isCurrent?'current':'' ?>" style="<?=$isCurrent?'border-color:'.vipBadgeColor((int)$v['level']).';':''?>">
  <div class="vip-card-header"><div class="vip-level-badge" style="background:<?=vipBadgeColor((int)$v['level'])?>"><?=$v['name']?></div><?php if($isCurrent):?><div class="current-label">Level Kamu</div><?php endif;?></div>
  <div class="vip-details">
    <div class="vd-row"><span>Syarat Deposit</span><strong><?=$v['min_deposit']>0?formatRupiah((float)$v['min_deposit']):'Tanpa Syarat'?></strong></div>
    <div class="vd-row"><span>Min. Penarikan</span><strong><?=$v['min_withdraw']>0?formatRupiah((float)$v['min_withdraw']):'Tidak Ada'?></strong></div>
    <div class="vd-row"><span>Biaya Admin</span><strong><?=$v['withdraw_fee_percent']?>%</strong></div>
    <div class="vd-row"><span>Rabat Deposit L1/L2/L3</span><strong><?=$v['referral_deposit_l1']?>% / <?=$v['referral_deposit_l2']?>% / <?=$v['referral_deposit_l3']?>%</strong></div>
    <div class="vd-row"><span>Rabat Transaksi L1/L2/L3</span><strong><?=$v['referral_product_l1']?>% / <?=$v['referral_product_l2']?>% / <?=$v['referral_product_l3']?>%</strong></div>
  </div>
</div>
<?php endwhile;endif;?>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php';?>
<?php include __DIR__.'/../includes/footer.php';?>
