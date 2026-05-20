<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $user=currentUser(); $uid=(int)$user['id'];
$pageTitle='about'; $currentPage='about';
include __DIR__.'/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header"><a href="<?=APP_URL?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title"><?php echo ucfirst('about'); ?></h1></div>
<div class="section-card">
<?php
  echo "<p class='text-muted'>Konten halaman ini dapat dikelola dari dashboard admin.</p>";
?>
</div>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php';?>
<?php include __DIR__.'/../includes/footer.php';?>
