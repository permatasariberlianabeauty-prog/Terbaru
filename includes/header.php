<?php
// ============================================================
// NOXARA - includes/header.php
// Compatible: PHP 7.2+
// ============================================================
if (!defined('APP_NAME')) require_once __DIR__ . '/../config/bootstrap.php';
checkMaintenanceMode();
$user        = currentUser();
$pageTitle   = isset($pageTitle) ? $pageTitle : APP_NAME;
$unreadNotif = $user ? getUnreadNotifCount((int)$user['id']) : 0;
$unreadChat  = $user ? getUnreadChatCount((int)$user['id']) : 0;
$userTheme   = $user ? ($user['theme'] ?? 'dark') : 'dark';
$userLang    = $user ? ($user['lang'] ?? 'id') : 'id';
?>
<!DOCTYPE html>
<html lang="<?= $userLang ?>" data-theme="<?= $userTheme ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#0A0E1A">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<title><?= htmlspecialchars($pageTitle) ?> - <?= APP_NAME ?></title>

<!-- Inline CSS: embedded directly so it always loads regardless of server config -->
<?php include __DIR__ . '/inline_styles.php'; ?>
<!-- Fallback external CSS (loaded async after inline takes effect) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"></noscript>
<!-- Lucide Icons - load dari LOCAL file, fallback ke CDN -->
<script>
(function(){
  var s = document.createElement('script');
  s.src = '<?= APP_URL ?>/assets/js/lucide.min.js';
  s.onload = function(){ if(typeof lucide!=='undefined') lucide.createIcons(); };
  s.onerror = function(){
    var s2 = document.createElement('script');
    s2.src = 'https://unpkg.com/lucide@0.263.1/dist/umd/lucide.min.js';
    s2.onload = function(){ if(typeof lucide!=='undefined') lucide.createIcons(); };
    document.head.appendChild(s2);
  };
  document.head.appendChild(s);
})();
</script>
</head>
<body class="theme-<?= $userTheme ?>">
<div id="app">
<?php if ($user): ?>
<!-- Running Text -->
<?php $runningText = getSetting('running_text'); if ($runningText): ?>
<div class="running-text-bar">
  <div class="running-text-inner">
    <i data-lucide="megaphone" class="rt-icon"></i>
    <div class="running-text-track">
      <span><?= htmlspecialchars($runningText) ?></span>
      <span><?= htmlspecialchars($runningText) ?></span>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>
