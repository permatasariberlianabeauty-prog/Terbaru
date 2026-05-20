<?php
require_once __DIR__ . '/config/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');
if (isAdmin()) redirect(APP_URL . '/adm-noxara/index.php');

// Detect base URL dynamically as fallback
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'noxara.page';
$baseUrl = defined('APP_URL') ? APP_URL : $protocol . '://' . $host;
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
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/animations.css">
<style>
/* === NOXARA LANDING PAGE - INLINE CRITICAL CSS === */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:#0A0E1A;color:#E8EAED;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;line-height:1.6;min-height:100vh;overflow-x:hidden;-webkit-tap-highlight-color:transparent}
a{text-decoration:none;color:inherit}
img{max-width:100%}
button{cursor:pointer;border:none;background:none;font-family:inherit}
:root{--bg:#0A0E1A;--bg2:#0F1423;--bg3:#141928;--card:#161C2E;--card2:#1A2035;--border:#1E2A45;--gold:#FFD700;--gold2:#FFA500;--gold3:#FF8C00;--text:#E8EAED;--text2:#9CA3AF;--text3:#6B7280;--green:#10B981;--red:#EF4444;--blue:#3B82F6;--orange:#F59E0B;--radius:16px;--radius-sm:10px;--transition:all 0.3s cubic-bezier(0.4,0,0.2,1)}
.landing-page{background:var(--bg);overflow-x:hidden}
.hero-section{padding:40px 20px 20px;max-width:480px;margin:0 auto;text-align:center}
.hero-logo{margin-bottom:24px}
.hero-logo-icon{width:72px;height:72px;background:linear-gradient(135deg,#FFD700,#FF8C00);border-radius:22px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px}
.hero-logo-icon svg{width:36px;height:36px;color:#000}
.hero-title{font-size:36px;font-weight:900;background:linear-gradient(135deg,#FFD700,#FF8C00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:6px}
.hero-tagline{font-size:15px;color:#9CA3AF}
.landing-login-card{background:#161C2E;border:1px solid #1E2A45;border-radius:20px;padding:20px;margin-bottom:20px;text-align:left}
.login-card-tabs{display:flex;gap:4px;background:#0A0E1A;border-radius:12px;padding:4px;margin-bottom:16px}
.tab-btn{flex:1;padding:8px;border-radius:8px;font-size:14px;font-weight:600;color:#6B7280;transition:all 0.3s}
.tab-btn.active{background:linear-gradient(135deg,#FFD700,#FF8C00);color:#000}
.form-group{margin-bottom:16px}
.form-label{display:block;margin-bottom:6px;font-weight:500;color:#9CA3AF;font-size:13px}
.input-wrapper{position:relative;display:flex;align-items:center}
.input-wrapper .form-input{padding-left:44px}
.input-icon{position:absolute;left:14px;width:18px;height:18px;color:#6B7280;flex-shrink:0}
.form-input{width:100%;padding:12px 16px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:14px;outline:none}
.form-input:focus{border-color:#FFD700}
.input-toggle-pw{position:absolute;right:14px;color:#6B7280;display:flex}
.input-toggle-pw svg{width:18px;height:18px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 20px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;border:2px solid transparent;width:auto}
.btn-full{width:100%}
.btn-lg{padding:14px 24px;font-size:16px;border-radius:16px}
.btn-primary{background:linear-gradient(135deg,#FFD700,#FF8C00);color:#000;font-weight:700}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 0 20px rgba(255,215,0,0.3)}
.btn-outline{background:transparent;border-color:#1E2A45;color:#E8EAED}
.btn-outline:hover{border-color:#FFD700;color:#FFD700}
.btn svg{width:18px;height:18px}
.captcha-wrapper{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.captcha-display{background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px 16px;font-family:monospace;font-size:20px;font-weight:700;letter-spacing:6px;color:#FFD700;flex:1;text-align:center;user-select:none}
.captcha-refresh{background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px;color:#9CA3AF;flex-shrink:0}
.captcha-row{display:flex;align-items:center;gap:8px}
.captcha-input{max-width:100px;text-align:center;letter-spacing:4px;font-weight:700}
.text-gold{color:#FFD700}
.text-sm{font-size:12px}
.text-center{text-align:center}
.mt-2{margin-top:8px}.mt-3{margin-top:16px}.mb-3{margin-bottom:16px}
.landing-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:20px}
.lstat{background:#161C2E;border:1px solid #1E2A45;border-radius:12px;padding:10px 6px;text-align:center}
.lstat-num{display:block;font-size:14px;font-weight:800;color:#FFD700}
.lstat-label{display:block;font-size:9px;color:#6B7280;margin-top:2px}
.landing-section{padding:32px 20px;max-width:480px;margin:0 auto}
.bg-section{background:#161C2E}
.section-title{font-size:22px;font-weight:800;text-align:center;margin-bottom:24px}
.features-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.feature-card{background:#161C2E;border:1px solid #1E2A45;border-radius:16px;padding:16px;text-align:center}
.feature-icon{width:44px;height:44px;background:rgba(255,215,0,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
.feature-icon svg{width:22px;height:22px;color:#FFD700}
.feature-card h3{font-size:13px;font-weight:700;margin-bottom:6px}
.feature-card p{font-size:11px;color:#9CA3AF;line-height:1.5}
.steps-list{display:flex;flex-direction:column;gap:12px}
.step-item{display:flex;align-items:center;gap:14px;padding:12px;background:#161C2E;border-radius:14px;border:1px solid #1E2A45}
.step-num{width:36px;height:36px;background:linear-gradient(135deg,#FFD700,#FF8C00);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:#000;flex-shrink:0}
.step-content h4{font-size:14px;font-weight:700;margin-bottom:2px}
.step-content p{font-size:12px;color:#9CA3AF}
.cta-section{text-align:center}
.cta-title{font-size:24px;font-weight:800;margin-bottom:8px}
.cta-desc{font-size:13px;color:#9CA3AF;margin-bottom:20px}
.landing-footer{padding:24px 20px;text-align:center;color:#6B7280;font-size:12px;border-top:1px solid #1E2A45}
.footer-links{display:flex;justify-content:center;gap:16px;margin-top:8px}
.footer-links a{color:#6B7280;font-size:12px}
.register-quick{text-align:center;padding:10px 0}
.register-bonus-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;background:rgba(255,215,0,0.1);border:1px solid rgba(255,215,0,0.2);border-radius:12px;color:#FFD700;font-weight:600;font-size:14px}
.register-note{font-size:12px;color:#6B7280;margin-top:8px}
.toast-container{position:fixed;top:20px;right:16px;z-index:400;display:flex;flex-direction:column;gap:8px;max-width:300px}
.toast{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#161C2E;border:1px solid #1E2A45;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.4);font-size:13px;animation:slideInRight 0.3s ease;min-width:200px}
.toast svg{width:18px;height:18px;flex-shrink:0}
.toast-success{border-color:rgba(16,185,129,0.3);color:#10B981}
.toast-error{border-color:rgba(239,68,68,0.3);color:#EF4444}
.toast-warning{border-color:rgba(245,158,11,0.3);color:#F59E0B}
@keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}
.animate-fadeInDown{animation:fadeInDown 0.5s ease forwards}
.animate-fadeInUp{animation:fadeInUp 0.5s ease forwards}
.animate-pulse{animation:pulse 2s ease-in-out infinite}
.pulse-glow{animation:pulseGlow 2s ease-in-out infinite}
@keyframes pulseGlow{0%,100%{box-shadow:0 0 10px rgba(255,215,0,0.3)}50%{box-shadow:0 0 25px rgba(255,215,0,0.6)}}
.checkbox-wrapper{display:flex;align-items:flex-start;gap:10px;cursor:pointer}
.checkbox-wrapper input[type="checkbox"]{width:18px;height:18px;accent-color:#FFD700;flex-shrink:0;margin-top:2px}
.checkbox-label{font-size:13px;color:#9CA3AF}
.about-platform-link{display:flex;align-items:center;justify-content:center;gap:8px;color:#9CA3AF;font-size:13px;margin-top:12px}
.about-platform-link:hover{color:#FFD700}
.about-platform-link svg{width:16px;height:16px}
.lstat-div{width:1px;background:#1E2A45}
#particles-bg{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
</style>
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
