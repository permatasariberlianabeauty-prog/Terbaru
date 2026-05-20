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

<!-- Fast font loading: non-blocking -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"></noscript>
<!-- Icons: deferred so it does NOT block page render -->
<script src="https://unpkg.com/lucide@0.263.1/dist/umd/lucide.min.js" defer></script>
<!-- App CSS -->
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/animations.css">
<!-- Critical CSS: instant render, no FOUC -->
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:#0A0E1A;color:#E8EAED;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Roboto,Arial,sans-serif;min-height:100vh;overflow-x:hidden;-webkit-tap-highlight-color:transparent}
a{text-decoration:none;color:inherit}
button{cursor:pointer;border:none;background:none;font-family:inherit}
img{max-width:100%}
/* Prevent layout shift */
.page-wrapper{padding:16px;padding-bottom:90px;max-width:480px;margin:0 auto}
</style>
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
