<?php
require_once __DIR__ . '/config/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');
if (isAdmin()) redirect(APP_URL . '/adm-noxara/index.php');
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="theme-color" content="#0A0E1A">
<meta name="description" content="Noxara - Platform Mining Rupiah Terpercaya. Invest Smarter, Grow Faster.">
<title><?= APP_NAME ?> - <?= getSetting('site_tagline', APP_TAGLINE) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/animations.css">
</head>
<body class="theme-dark landing-page">
<div id="particles-bg"></div>

<!-- HERO SECTION WITH LOGIN -->
<section class="hero-section">
  <div class="hero-content animate-fadeInDown">
    <div class="hero-logo">
      <div class="hero-logo-icon pulse-glow"><i data-lucide="zap"></i></div>
      <h1 class="hero-title"><?= APP_NAME ?></h1>
      <p class="hero-tagline"><?= getSetting('site_tagline', APP_TAGLINE) ?></p>
    </div>
  </div>

  <!-- LOGIN CARD -->
  <div class="landing-login-card animate-fadeInUp">
    <div class="login-card-tabs">
      <button class="tab-btn active" id="tabLogin" onclick="switchTab('login')">Masuk</button>
      <button class="tab-btn" id="tabRegister" onclick="switchTab('register')">Daftar</button>
    </div>

    <!-- Login Form -->
    <div id="formLogin">
      <form method="POST" action="<?= APP_URL ?>/auth/login.php" id="landingLoginForm">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="input-wrapper mb-3">
          <i data-lucide="user" class="input-icon"></i>
          <input type="text" name="identifier" class="form-input" placeholder="Email / No. HP / Username" required>
        </div>
        <div class="input-wrapper mb-3">
          <i data-lucide="lock" class="input-icon"></i>
          <input type="password" name="password" id="landingPw" class="form-input" placeholder="Password" required>
          <button type="button" class="input-toggle-pw" onclick="togglePw('landingPw',this)"><i data-lucide="eye"></i></button>
        </div>
        <div class="captcha-row mb-3">
          <div class="captcha-display" id="captchaDisplayLanding"></div>
          <button type="button" class="captcha-refresh" onclick="refreshCaptcha('Landing')"><i data-lucide="refresh-cw"></i></button>
          <input type="text" id="captchaInputLanding" class="form-input captcha-input" placeholder="Kode" required>
          <input type="hidden" id="captchaKeyLanding">
        </div>
        <button type="submit" class="btn btn-primary btn-full">
          <i data-lucide="log-in"></i> Masuk
        </button>
        <div class="text-center mt-2">
          <a href="<?= APP_URL ?>/auth/forgot.php" class="text-gold text-sm">Lupa Password?</a>
        </div>
      </form>
    </div>

    <!-- Register Quick Link -->
    <div id="formRegister" style="display:none">
      <div class="register-quick">
        <div class="register-bonus-badge animate-pulse">
          <i data-lucide="gift"></i> Bonus Registrasi <?= formatRupiah((float)getSetting('register_bonus','15000')) ?>
        </div>
        <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-primary btn-full btn-lg mt-3">
          <i data-lucide="user-plus"></i> Daftar Gratis Sekarang
        </a>
        <p class="register-note">Sudah 125.000+ member bergabung!</p>
      </div>
    </div>
  </div>

  <!-- PLATFORM STATS -->
  <div class="landing-stats animate-fadeInUp">
    <div class="lstat"><span class="lstat-num" data-target="125000">0</span><span class="lstat-label">Member</span></div>
    <div class="lstat-div"></div>
    <div class="lstat"><span class="lstat-num" data-prefix="Rp " data-target="8500" data-suffix="Jt+">0</span><span class="lstat-label">Terbayar</span></div>
    <div class="lstat-div"></div>
    <div class="lstat"><span class="lstat-num" data-target="34" data-suffix=" Prov">0</span><span class="lstat-label">Wilayah</span></div>
    <div class="lstat-div"></div>
    <div class="lstat"><span class="lstat-num" data-target="99" data-suffix=".9%">0</span><span class="lstat-label">Uptime</span></div>
  </div>
</section>

<!-- FEATURES SECTION -->
<section class="landing-section">
  <h2 class="section-title">Kenapa Pilih <span class="text-gold">Noxara?</span></h2>
  <div class="features-grid">
    <div class="feature-card animate-fadeInUp">
      <div class="feature-icon"><i data-lucide="shield-check"></i></div>
      <h3>Aman & Terpercaya</h3>
      <p>Platform terenkripsi dengan sistem keamanan berlapis untuk melindungi aset kamu.</p>
    </div>
    <div class="feature-card animate-fadeInUp">
      <div class="feature-icon"><i data-lucide="trending-up"></i></div>
      <h3>Profit Harian</h3>
      <p>Dapatkan profit setiap hari dari aktivitas mining yang kamu lakukan.</p>
    </div>
    <div class="feature-card animate-fadeInUp">
      <div class="feature-icon"><i data-lucide="users"></i></div>
      <h3>Referral 3 Level</h3>
      <p>Ajak teman dan dapatkan komisi hingga 10% dari setiap aktivitas mereka.</p>
    </div>
    <div class="feature-card animate-fadeInUp">
      <div class="feature-icon"><i data-lucide="zap"></i></div>
      <h3>Withdraw Cepat</h3>
      <p>Proses penarikan cepat langsung ke rekening bank atau e-wallet kamu.</p>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="landing-section bg-section">
  <h2 class="section-title">Cara Kerja <span class="text-gold">Noxara</span></h2>
  <div class="steps-list">
    <div class="step-item"><div class="step-num">1</div><div class="step-content"><h4>Daftar Akun</h4><p>Buat akun gratis dan dapatkan bonus saldo langsung</p></div></div>
    <div class="step-item"><div class="step-num">2</div><div class="step-content"><h4>Isi Saldo</h4><p>Top up saldo via QRIS dari berbagai e-wallet</p></div></div>
    <div class="step-item"><div class="step-num">3</div><div class="step-content"><h4>Beli Produk</h4><p>Pilih paket investasi sesuai budget kamu</p></div></div>
    <div class="step-item"><div class="step-num">4</div><div class="step-content"><h4>Mining Harian</h4><p>Klik mining setiap hari dan raih profit otomatis</p></div></div>
    <div class="step-item"><div class="step-num">5</div><div class="step-content"><h4>Tarik Dana</h4><p>Withdraw kapan saja langsung ke rekening bank</p></div></div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="landing-section cta-section">
  <h2 class="cta-title">Mulai Mining Sekarang!</h2>
  <p class="cta-desc">Bergabung bersama 125.000+ member yang sudah merasakan manfaat Noxara</p>
  <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-primary btn-lg btn-cta">
    <i data-lucide="cpu"></i> Mining Sekarang
  </a>
  <a href="<?= APP_URL ?>/pages/about.php" class="btn btn-outline btn-lg mt-2">
    <i data-lucide="info"></i> Pelajari Lebih Lanjut
  </a>
</section>

<!-- FOOTER -->
<footer class="landing-footer">
  <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
  <div class="footer-links">
    <a href="<?= APP_URL ?>/pages/terms.php">Syarat & Ketentuan</a>
    <a href="<?= APP_URL ?>/pages/privacy.php">Privasi</a>
    <a href="<?= APP_URL ?>/pages/about.php">Tentang</a>
  </div>
</footer>

<div id="toastContainer" class="toast-container"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script src="<?= APP_URL ?>/assets/js/animations.js"></script>
<script>
lucide.createIcons();
window.NOXARA = { appUrl: '<?= APP_URL ?>' };
initCaptcha('Landing');
animateCounters();
initParticlesBg();
function switchTab(t) {
  document.getElementById('formLogin').style.display = t==='login'?'block':'none';
  document.getElementById('formRegister').style.display = t==='register'?'block':'none';
  document.getElementById('tabLogin').classList.toggle('active', t==='login');
  document.getElementById('tabRegister').classList.toggle('active', t==='register');
}
document.getElementById('landingLoginForm').addEventListener('submit', function(e) {
  const input  = document.getElementById('captchaInputLanding').value.trim().toUpperCase();
  const stored = atob(document.getElementById('captchaKeyLanding').value);
  if (input !== stored) { e.preventDefault(); showToast('Kode verifikasi salah!','error'); refreshCaptcha('Landing'); document.getElementById('captchaInputLanding').value=''; }
});
</script>
</body>
</html>
