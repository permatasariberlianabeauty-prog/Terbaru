<?php
require_once __DIR__ . '/../config/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');
if (isAdmin()) redirect(APP_URL . '/adm-noxara/index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $identifier = sanitize($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Semua field wajib diisi';
    } else {
        $result = loginUser($identifier, $password);
        if ($result['success']) {
            redirect(APP_URL . '/pages/dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="theme-color" content="#0A0E1A">
<title>Login - <?= APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/animations.css">
</head>
<body class="theme-dark auth-page">
<div class="auth-bg">
  <div class="auth-particles" id="particles"></div>
  <div class="auth-container">
    <!-- Logo -->
    <div class="auth-logo animate-fadeInDown">
      <div class="logo-icon"><i data-lucide="zap"></i></div>
      <h1 class="logo-text"><?= APP_NAME ?></h1>
      <p class="logo-tagline"><?= getSetting('site_tagline', APP_TAGLINE) ?></p>
    </div>

    <!-- Login Card -->
    <div class="auth-card animate-fadeInUp">
      <h2 class="auth-title">Masuk ke Akun</h2>
      <p class="auth-subtitle">Gunakan email, nomor HP, atau username</p>

      <?php if ($error): ?>
      <div class="alert alert-error animate-shake">
        <i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" id="loginForm" autocomplete="off">
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Email / No. HP / Username</label>
          <div class="input-wrapper">
            <i data-lucide="user" class="input-icon"></i>
            <input type="text" name="identifier" class="form-input" placeholder="Masukkan identitas" value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>" required autofocus>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-wrapper">
            <i data-lucide="lock" class="input-icon"></i>
            <input type="password" name="password" id="loginPassword" class="form-input" placeholder="Masukkan password" required>
            <button type="button" class="input-toggle-pw" onclick="togglePw('loginPassword',this)">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>
        <!-- Captcha -->
        <div class="form-group">
          <label class="form-label">Verifikasi</label>
          <div class="captcha-wrapper">
            <div class="captcha-display" id="captchaDisplay"></div>
            <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
              <i data-lucide="refresh-cw"></i>
            </button>
          </div>
          <div class="input-wrapper mt-2">
            <i data-lucide="shield" class="input-icon"></i>
            <input type="text" name="captcha" id="captchaInput" class="form-input" placeholder="Masukkan kode verifikasi" required>
          </div>
          <input type="hidden" name="captcha_key" id="captchaKey">
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg" id="loginBtn">
          <i data-lucide="log-in"></i> Masuk Sekarang
        </button>
      </form>

      <div class="auth-links">
        <a href="<?= APP_URL ?>/auth/forgot.php" class="auth-link">
          <i data-lucide="help-circle"></i> Lupa Password?
        </a>
      </div>
    </div>

    <!-- Divider -->
    <div class="auth-divider">
      <span>Belum punya akun?</span>
    </div>
    <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-outline btn-full">
      <i data-lucide="user-plus"></i> Daftar Sekarang - GRATIS
    </a>

    <!-- Platform Info Teaser -->
    <div class="platform-stats animate-fadeInUp">
      <div class="stat-item">
        <span class="stat-num" data-target="125000">0</span>
        <span class="stat-label">Member Aktif</span>
      </div>
      <div class="stat-item">
        <span class="stat-num" data-target="8500" data-prefix="Rp " data-suffix="Jt+">0</span>
        <span class="stat-label">Total Terbayar</span>
      </div>
      <div class="stat-item">
        <span class="stat-num" data-target="99" data-suffix=".9%">0</span>
        <span class="stat-label">Uptime</span>
      </div>
    </div>

    <!-- About Platform Link -->
    <a href="<?= APP_URL ?>/pages/about.php" class="about-platform-link">
      <i data-lucide="info"></i> Tentang Platform Noxara
    </a>
  </div>
</div>

<div id="toastContainer" class="toast-container"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script src="<?= APP_URL ?>/assets/js/animations.js"></script>
<script>
lucide.createIcons();
window.NOXARA = { appUrl: '<?= APP_URL ?>' };
initCaptcha();
animateCounters();
initParticles();
// Submit with captcha check
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const input = document.getElementById('captchaInput').value.trim().toUpperCase();
  const key   = document.getElementById('captchaKey').value;
  const stored = atob(key);
  if (input !== stored) {
    e.preventDefault();
    showToast('Kode verifikasi salah!', 'error');
    refreshCaptcha();
    document.getElementById('captchaInput').value = '';
  }
});
</script>
</body>
</html>
