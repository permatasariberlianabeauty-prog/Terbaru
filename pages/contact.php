<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $user=currentUser(); $uid=(int)$user['id'];
$pageTitle='contact'; $currentPage='contact';
include __DIR__.'/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header"><a href="<?=APP_URL?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title"><?php echo ucfirst('contact'); ?></h1></div>
<div class="section-card">
<?php
  $wa=getSetting('wa_admin','628000000000');
  $email=getSetting('email_admin','admin@noxara.page');
  echo "<div class='contact-list'>";
  echo "<a href='https://wa.me/$wa' target='_blank' class='contact-item'><i data-lucide='message-circle'></i><div class='ci-info'><div>WhatsApp Admin</div><div class='ci-val'>+$wa</div></div><i data-lucide='chevron-right'></i></a>";
  echo "<a href='mailto:$email' class='contact-item'><i data-lucide='mail'></i><div class='ci-info'><div>Email Admin</div><div class='ci-val'>$email</div></div><i data-lucide='chevron-right'></i></a>";
  echo "</div>";
?>
</div>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php';?>
<?php include __DIR__.'/../includes/footer.php';?>
