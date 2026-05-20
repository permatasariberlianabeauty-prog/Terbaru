<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $user=currentUser(); $uid=(int)$user['id'];
$pageTitle='apps'; $currentPage='apps';
include __DIR__.'/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header"><a href="<?=APP_URL?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title"><?php echo ucfirst('apps'); ?></h1></div>
<div class="section-card">
<?php
  $appLink=getSetting('app_download_link','#');
  echo "<div class='app-download-page text-center'>";
  echo "<div class='app-icon'><i data-lucide='smartphone'></i></div>";
  echo "<h2>Download Aplikasi Noxara</h2>";
  echo "<p>Akses Noxara lebih mudah melalui aplikasi mobile kami.</p>";
  echo "<a href='".htmlspecialchars($appLink)."' target='_blank' class='btn btn-primary btn-lg mt-3'><i data-lucide='download'></i> Download Sekarang</a>";
  echo "</div>";
?>
</div>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php';?>
<?php include __DIR__.'/../includes/footer.php';?>
