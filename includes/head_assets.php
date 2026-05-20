<?php
// ============================================================
// NOXARA - includes/head_assets.php
// Centralized fast-loading assets
// ============================================================
$APP = defined('APP_URL') ? APP_URL : '';
?>
<!-- Google Fonts - async/non-blocking -->

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"></noscript>

<!-- Lucide Icons - defer loading -->
<script src="https://unpkg.com/lucide@0.263.1/dist/umd/lucide.min.js" defer></script>

<!-- App CSS -->
<link rel="stylesheet" href="<?= $APP ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $APP ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= $APP ?>/assets/css/animations.css">

<!-- Critical inline CSS: system fonts fallback while Inter loads -->
<style>
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
</style>
