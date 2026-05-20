<?php
// ============================================================
// NOXARA - includes/header.php
// ============================================================
if (!defined('APP_NAME')) require_once __DIR__ . '/../config/bootstrap.php';
checkMaintenanceMode();
$user = currentUser();
$pageTitle = $pageTitle ?? APP_NAME;
$unreadNotif = $user ? getUnreadNotifCount((int)$user['id']) : 0;
$unreadChat  = $user ? getUnreadChatCount((int)$user['id']) : 0;
$userTheme   = $user['theme'] ?? 'dark';
$userLang    = $user['lang'] ?? 'id';
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/animations.css">
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
