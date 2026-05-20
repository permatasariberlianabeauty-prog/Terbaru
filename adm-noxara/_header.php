<?php
if(!defined('APP_NAME')) require_once __DIR__.'/../config/bootstrap.php';
requireAdmin();
$adminUsername=$_SESSION['admin_username']??'Admin';
$adminPageTitle=$adminPageTitle??'Dashboard';
$pendingWd=(int)(dbQuery("SELECT COUNT(*) as c FROM withdrawals WHERE status='pending'")->fetch_assoc()['c']??0);
$pendingChat=(int)(dbQuery("SELECT COUNT(*) as c FROM live_chats WHERE sender='user' AND is_read=0")->fetch_assoc()['c']??0);
?>
<!DOCTYPE html><html lang="id" data-theme="dark"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title><?=htmlspecialchars($adminPageTitle)?> - Admin <?=APP_NAME?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?=APP_URL?>/assets/css/style.css">
<link rel="stylesheet" href="<?=APP_URL?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?=APP_URL?>/assets/css/animations.css">
</head><body class="theme-dark admin-body">
<div id="adminApp">
<div class="admin-topbar">
  <button class="admin-menu-btn" onclick="toggleAdminMenu()"><i data-lucide="menu"></i></button>
  <div class="admin-brand"><i data-lucide="shield"></i> <?=APP_NAME?> Admin</div>
  <div class="admin-topbar-right">
    <?php if($pendingWd>0):?><div class="admin-badge-btn"><i data-lucide="arrow-down-circle"></i><span class="badge-dot"><?=$pendingWd?></span></div><?php endif;?>
    <?php if($pendingChat>0):?><div class="admin-badge-btn"><i data-lucide="message-circle"></i><span class="badge-dot"><?=$pendingChat?></span></div><?php endif;?>
    <span class="admin-user-name"><?=htmlspecialchars($adminUsername)?></span>
  </div>
</div>
<div class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-overlay" onclick="toggleAdminMenu()"></div>
  <div class="sidebar-inner">
    <div class="sidebar-logo"><i data-lucide="zap"></i> <?=APP_NAME?></div>
    <nav class="sidebar-nav">
      <?php $cp=$currentAdminPage??'';
      $navItems=[
        ['index','home','Dashboard'],
        ['members','users','Member'],
        ['deposits','plus-circle','Deposit'],
        ['withdrawals','minus-circle','Penarikan'],
        ['products','package','Produk'],
        ['vouchers','ticket','Voucher'],
        ['vip_settings','crown','VIP Settings'],
        ['mining_settings','cpu','Mining'],
        ['daily_settings','calendar-check','Daily'],
        ['content','image','Konten'],
        ['chat','message-circle','Live Chat'],
        ['notifications','bell','Notifikasi'],
        ['reports','bar-chart-2','Laporan'],
        ['settings','settings','Pengaturan'],
      ];
      foreach($navItems as $n): ?>
      <a href="<?=APP_URL?>/adm-noxara/<?=$n[0]?>.php" class="sidebar-link <?=$cp===$n[0]?'active':''?>">
        <i data-lucide="<?=$n[1]?>"></i> <?=$n[2]?>
        <?php if($n[0]==='withdrawals'&&$pendingWd>0):?><span class="nav-badge"><?=$pendingWd?></span><?php endif;?>
        <?php if($n[0]==='chat'&&$pendingChat>0):?><span class="nav-badge"><?=$pendingChat?></span><?php endif;?>
      </a>
      <?php endforeach;?>
      <a href="<?=APP_URL?>/adm-noxara/logout.php" class="sidebar-link danger"><i data-lucide="log-out"></i> Keluar</a>
    </nav>
  </div>
</div>
<div class="admin-content">
