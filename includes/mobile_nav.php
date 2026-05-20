<?php
// ============================================================
// NOXARA - includes/mobile_nav.php
// ============================================================
$currentPage = $currentPage ?? '';
?>
<nav class="bottom-nav">
  <a href="<?= APP_URL ?>/pages/dashboard.php" class="nav-item <?= $currentPage==='home'?'active':'' ?>">
    <i data-lucide="home"></i>
    <span><?= $userLang==='en'?'Home':'Beranda' ?></span>
  </a>
  <a href="<?= APP_URL ?>/pages/referral.php" class="nav-item <?= $currentPage==='team'?'active':'' ?>">
    <i data-lucide="users"></i>
    <span><?= $userLang==='en'?'Team':'Tim' ?></span>
  </a>
  <a href="<?= APP_URL ?>/pages/products.php" class="nav-item nav-center <?= $currentPage==='products'?'active':'' ?>">
    <div class="nav-center-btn">
      <i data-lucide="plus"></i>
    </div>
    <span><?= $userLang==='en'?'Product':'Produk' ?></span>
  </a>
  <a href="<?= APP_URL ?>/pages/mining.php" class="nav-item <?= $currentPage==='mining'?'active':'' ?>">
    <i data-lucide="cpu"></i>
    <span>Mining</span>
  </a>
  <a href="<?= APP_URL ?>/pages/profile.php" class="nav-item <?= $currentPage==='profile'?'active':'' ?>">
    <i data-lucide="user"></i>
    <span><?= $userLang==='en'?'Profile':'Profil' ?></span>
  </a>
</nav>
<div class="bottom-nav-spacer"></div>
