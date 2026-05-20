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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"></noscript>
<script src="https://unpkg.com/lucide@0.263.1/dist/umd/lucide.min.js" defer></script>
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
<script src="<?= APP_URL ?>/assets/js/main.js" onerror="this.remove()"></script>
<script src="<?= APP_URL ?>/assets/js/animations.js" onerror="this.remove()"></script>
<script>
/* INLINE FALLBACK - Runs if external JS fails to load */
if(typeof showToast === 'undefined') {
// ============================================================
// NOXARA - main.js
// ============================================================
'use strict';

// ============ UTILITY ============
function showToast(msg, type='info', duration=3000) {
  const c = document.getElementById('toastContainer');
  if (!c) return;
  const icons = {success:'check-circle',error:'x-circle',warning:'alert-triangle',info:'info'};
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<i data-lucide="${icons[type]||'info'}"></i><span>${msg}</span>`;
  c.appendChild(t);
  if (typeof lucide !== 'undefined') lucide.createIcons({nodes:[t]});
  setTimeout(() => { t.style.opacity='0'; t.style.transform='translateX(20px)'; t.style.transition='all 0.3s'; setTimeout(()=>t.remove(),300); }, duration);
}

function showModal(type, title, msg, onClose=null, extraBtns=[]) {
  const overlay = document.getElementById('modalOverlay');
  const box = document.getElementById('modalBox');
  if (!overlay || !box) { alert(msg); if(onClose) onClose(); return; }
  const icons = {success:'check-circle',error:'x-circle',warning:'alert-triangle',info:'info'};
  const colors = {success:'#10B981',error:'#EF4444',warning:'#F59E0B',info:'#3B82F6'};
  document.getElementById('modalIcon').className = `modal-icon ${type}`;
  document.getElementById('modalIcon').innerHTML = `<i data-lucide="${icons[type]}"></i>`;
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMsg').textContent = msg;
  const actions = document.getElementById('modalActions');
  actions.innerHTML = '';
  const okBtn = document.createElement('button');
  okBtn.className = 'btn btn-primary';
  okBtn.textContent = 'OK';
  okBtn.onclick = () => { overlay.style.display='none'; if(onClose) onClose(); };
  extraBtns.forEach(b => {
    const btn = document.createElement('a');
    btn.className = 'btn btn-outline btn-sm';
    btn.textContent = b.text;
    if(b.href) btn.href = b.href;
    if(b.target) btn.target = b.target;
    actions.appendChild(btn);
  });
  actions.appendChild(okBtn);
  overlay.style.display = 'flex';
  box.className = 'modal-box';
  if (typeof lucide !== 'undefined') lucide.createIcons({nodes:[box]});
  // click outside
  overlay.onclick = (e) => { if(e.target===overlay){overlay.style.display='none';if(onClose)onClose();} };
}

function showConfirm(title, msg, onConfirm) {
  const overlay = document.getElementById('modalOverlay');
  const box = document.getElementById('modalBox');
  if (!overlay || !box) { if(confirm(msg)) onConfirm(); return; }
  document.getElementById('modalIcon').className = 'modal-icon warning';
  document.getElementById('modalIcon').innerHTML = '<i data-lucide="alert-triangle"></i>';
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMsg').textContent = msg;
  const actions = document.getElementById('modalActions');
  actions.innerHTML = '';
  const cancelBtn = document.createElement('button');
  cancelBtn.className = 'btn btn-outline';
  cancelBtn.textContent = 'Batal';
  cancelBtn.onclick = () => overlay.style.display='none';
  const confirmBtn = document.createElement('button');
  confirmBtn.className = 'btn btn-primary';
  confirmBtn.textContent = 'Ya, Lanjutkan';
  confirmBtn.onclick = () => { overlay.style.display='none'; onConfirm(); };
  actions.appendChild(cancelBtn);
  actions.appendChild(confirmBtn);
  overlay.style.display = 'flex';
  if (typeof lucide !== 'undefined') lucide.createIcons({nodes:[box]});
}

let loadingEl = null;
function showLoading() {
  if (loadingEl) return;
  loadingEl = document.createElement('div');
  loadingEl.className = 'loading-overlay';
  loadingEl.innerHTML = '<div class="loading-spinner"></div>';
  document.body.appendChild(loadingEl);
}
function hideLoading() {
  if (loadingEl) { loadingEl.remove(); loadingEl = null; }
}

// ============ PASSWORD TOGGLE ============
function togglePw(id, btn) {
  const input = document.getElementById(id);
  if (!input) return;
  const isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  btn.innerHTML = isHidden ? '<i data-lucide="eye-off"></i>' : '<i data-lucide="eye"></i>';
  if (typeof lucide !== 'undefined') lucide.createIcons({nodes:[btn]});
}

// ============ CAPTCHA ============
function initCaptcha(suffix='') {
  refreshCaptcha(suffix);
}
function refreshCaptcha(suffix='') {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  let code = '';
  for (let i=0; i<5; i++) code += chars[Math.floor(Math.random()*chars.length)];
  const display = document.getElementById('captchaDisplay'+suffix);
  const key = document.getElementById('captchaKey'+suffix);
  if (display) {
    display.textContent = '';
    code.split('').forEach((c,i) => {
      const span = document.createElement('span');
      span.textContent = c;
      span.style.cssText = `color:hsl(${45+i*20},90%,65%);transform:rotate(${(Math.random()-0.5)*15}deg);display:inline-block;margin:0 1px`;
      display.appendChild(span);
    });
  }
  if (key) key.value = btoa(code);
}

// ============ COUNTER ANIMATION ============
function animateCounters() {
  document.querySelectorAll('[data-target]').forEach(el => {
    const target = parseInt(el.dataset.target);
    const prefix = el.dataset.prefix || '';
    const suffix = el.dataset.suffix || '';
    let current = 0;
    const step = target / 60;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = prefix + Math.floor(current).toLocaleString('id-ID') + suffix;
    }, 25);
  });
}

// ============ BANNER SLIDER ============
function initBannerSlider() {
  const slider = document.getElementById('bannerSlider');
  if (!slider) return;
  const slides = slider.querySelectorAll('.banner-slide');
  if (slides.length <= 1) return;
  const dotsContainer = document.getElementById('bannerDots');
  if (dotsContainer) {
    slides.forEach((_, i) => {
      const dot = document.createElement('div');
      dot.className = 'banner-dot' + (i===0?' active':'');
      dot.onclick = () => goToSlide(i);
      dotsContainer.appendChild(dot);
    });
  }
  let current = 0;
  let startX = 0;
  function goToSlide(idx) {
    slides[current].classList.remove('active');
    dotsContainer?.querySelectorAll('.banner-dot')[current]?.classList.remove('active');
    current = (idx + slides.length) % slides.length;
    slides[current].classList.add('active');
    dotsContainer?.querySelectorAll('.banner-dot')[current]?.classList.add('active');
  }
  const auto = setInterval(() => goToSlide(current+1), 4000);
  slider.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, {passive:true});
  slider.addEventListener('touchend', e => {
    const diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) goToSlide(diff > 0 ? current+1 : current-1);
  });
}

// ============ PIN INPUT ============
function initPinInput() {
  document.querySelectorAll('.pin-digit').forEach((input, idx, all) => {
    input.addEventListener('input', () => {
      if (input.value.length >= 1) {
        input.value = input.value.slice(-1);
        const next = all[idx+1];
        if (next) next.focus();
      }
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !input.value && idx > 0) all[idx-1].focus();
    });
  });
}
function getPinValue(cls='pin-digit') {
  return [...document.querySelectorAll('.'+cls)].map(i=>i.value).join('');
}

// ============ SCROLL TO TOP ============
window.addEventListener('scroll', () => {
  const fab = document.getElementById('fabTop');
  if (fab) fab.classList.toggle('visible', window.scrollY > 300);
});
document.getElementById('fabTop')?.addEventListener('click', () => window.scrollTo({top:0,behavior:'smooth'}));

// ============ LIVE CHAT ============
const chatPanel = document.getElementById('chatPanel');
document.getElementById('fabChat')?.addEventListener('click', () => {
  if (!chatPanel) return;
  chatPanel.classList.toggle('open');
  if (chatPanel.classList.contains('open')) loadChatMessages();
});
document.getElementById('chatClose')?.addEventListener('click', () => chatPanel?.classList.remove('open'));

function loadChatMessages() {
  if (!window.NOXARA?.userId) return;
  fetch(window.NOXARA.appUrl+'/api/chat.php?action=load', {headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(r=>r.json()).then(d=>{
    const container = document.getElementById('chatMsgs');
    if (!container || !d.messages) return;
    const qr = document.getElementById('quickReplies');
    if (d.messages.length > 0 && qr) qr.style.display='none';
    container.innerHTML = '';
    d.messages.forEach(m => {
      const div = document.createElement('div');
      div.className = `chat-msg-item ${m.sender}`;
      div.innerHTML = `<div class="chat-bubble">${m.message}</div><div class="chat-msg-time">${m.created_at}</div>`;
      container.appendChild(div);
    });
    container.scrollTop = container.scrollHeight;
  });
}

document.getElementById('chatSend')?.addEventListener('click', sendChatMessage);
document.getElementById('chatInput')?.addEventListener('keydown', e => { if(e.key==='Enter') sendChatMessage(); });

function sendChatMessage() {
  const input = document.getElementById('chatInput');
  const msg = input?.value.trim();
  if (!msg) return;
  const csrf = document.querySelector('input[name="csrf_token"]')?.value || '';
  fetch(window.NOXARA?.appUrl+'/api/chat.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:`action=send&message=${encodeURIComponent(msg)}&csrf_token=${csrf}`
  }).then(()=>{ input.value=''; loadChatMessages(); });
}

document.querySelectorAll('.quick-reply-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const answer = btn.dataset.answer;
    const container = document.getElementById('chatMsgs');
    if (container) {
      const div = document.createElement('div');
      div.className = 'chat-msg-item admin';
      div.innerHTML = `<div class="chat-bubble">${answer}</div>`;
      container.appendChild(div);
      container.scrollTop = container.scrollHeight;
      document.getElementById('quickReplies').style.display='none';
    }
  });
});

// Star rating
document.querySelectorAll('.star-btn').forEach((btn, i, all) => {
  btn.addEventListener('click', () => {
    const star = parseInt(btn.dataset.star);
    all.forEach((b,j) => b.classList.toggle('active', j<star));
    const csrf = document.querySelector('input[name="csrf_token"]')?.value||'';
    fetch(window.NOXARA?.appUrl+'/api/chat.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`action=rate&rating=${star}&csrf_token=${csrf}`});
    showToast('Terima kasih atas penilaianmu!','success');
  });
});

// ============ MINING COUNTDOWN ============
function initMiningCountdown() {
  const el = document.querySelector('.msb-countdown[data-seconds]');
  if (!el) return;
  let seconds = parseInt(el.dataset.seconds);
  if (seconds <= 0) return;
  const iv = setInterval(() => {
    seconds--;
    if (seconds <= 0) { clearInterval(iv); el.textContent='Selesai!'; return; }
    const h=Math.floor(seconds/3600),m=Math.floor((seconds%3600)/60),s=seconds%60;
    el.textContent=`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  }, 1000);
}

function initMiningCountdowns() {
  document.querySelectorAll('.mc-countdown-time[data-finish]').forEach(el => {
    const finish = parseInt(el.dataset.finish) * 1000;
    const ring = el.closest('.mc-countdown-ring')?.querySelector('.countdown-ring-fill');
    const total = parseInt(ring?.dataset.total||7200) * 1000;
    const iv = setInterval(() => {
      const remaining = Math.max(0, finish - Date.now());
      if (remaining <= 0) { clearInterval(iv); el.textContent='00:00:00'; checkCompleteMining(el.closest('.mining-card')?.id?.replace('mcard-','')); return; }
      const h=Math.floor(remaining/3600000),m=Math.floor(remaining%3600000/60000),s=Math.floor(remaining%60000/1000);
      el.textContent=`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
      if (ring) { const pct=remaining/total; ring.style.strokeDashoffset=339.3*(1-pct); }
    }, 1000);
  });
}

function checkCompleteMining(pkgId) {
  if (!pkgId || !window.NOXARA) return;
  const csrf = document.querySelector('input[name="csrf_token"]')?.value||'';
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${csrf}&action=complete&package_id=${pkgId}`})
  .then(r=>r.json()).then(d=>{ if(d.success){ showProfitAnimation(d.profit); setTimeout(()=>location.reload(),2000); } });
}

function showProfitAnimation(profit) {
  // Coin particles
  for(let i=0;i<8;i++){
    const coin=document.createElement('div');
    coin.className='coin-particle';
    coin.textContent='💰';
    coin.style.cssText=`left:${20+Math.random()*60}vw;top:${20+Math.random()*30}vh;animation-delay:${Math.random()*0.5}s;animation-duration:${1+Math.random()*0.5}s`;
    document.body.appendChild(coin);
    setTimeout(()=>coin.remove(),2000);
  }
  showToast(`+Rp ${Number(profit).toLocaleString('id-ID')} masuk ke saldo! 💰`,'success',4000);
}

// ============ INIT ============
document.addEventListener('DOMContentLoaded', () => {
  initPinInput();
  initMiningCountdown();
});

// ============================================================
// NOXARA - animations.js
// ============================================================
'use strict';

// ============ PARTICLES ============
function initParticles(containerId='particles') {
  const c = document.getElementById(containerId);
  if (!c) return;
  for (let i=0;i<20;i++) {
    const p=document.createElement('div');
    p.style.cssText=`position:absolute;width:${2+Math.random()*4}px;height:${2+Math.random()*4}px;background:rgba(255,215,0,${0.1+Math.random()*0.3});border-radius:50%;left:${Math.random()*100}%;top:${Math.random()*100}%;animation:floatParticle ${5+Math.random()*10}s linear ${Math.random()*5}s infinite`;
    c.appendChild(p);
  }
}

function initParticlesBg() {
  const c=document.getElementById('particles-bg');
  if(!c)return;
  c.style.cssText='position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden';
  for(let i=0;i<15;i++){
    const p=document.createElement('div');
    const size=2+Math.random()*3;
    p.style.cssText=`position:absolute;width:${size}px;height:${size}px;background:rgba(255,215,0,${0.05+Math.random()*0.2});border-radius:50%;left:${Math.random()*100}%;animation:particleFloat ${8+Math.random()*15}s linear ${Math.random()*8}s infinite`;
    p.style.top=`${Math.random()*100}%`;
    c.appendChild(p);
  }
  const style=document.createElement('style');
  style.textContent='@keyframes particleFloat{0%{transform:translateY(0) translateX(0);opacity:0}10%{opacity:1}90%{opacity:1}100%{transform:translateY(-100vh) translateX(20px);opacity:0}}@keyframes floatParticle{0%{transform:translate(0,0) rotate(0)}33%{transform:translate(20px,-20px) rotate(120deg)}66%{transform:translate(-10px,10px) rotate(240deg)}100%{transform:translate(0,0) rotate(360deg)}}';
  document.head.appendChild(style);
}

// ============ WELCOME LIGHTS ============
function initWelcomeLights() {
  const container=document.getElementById('welcomeLights');
  if(!container)return;
  const colors=['#FFD700','#FF8C00','#FFA500','#FFEC8B','#FFD700','#FF6347','#FF69B4','#00CED1'];
  for(let i=0;i<16;i++){
    const light=document.createElement('div');
    light.className='light-dot';
    const size=4+Math.random()*8;
    light.style.cssText=`width:${size}px;height:${size}px;background:${colors[i%colors.length]};left:${Math.random()*100}%;top:${Math.random()*100}%;animation-delay:${Math.random()*1.5}s;animation-duration:${0.6+Math.random()*0.8}s`;
    container.appendChild(light);
  }
  // Confetti
  for(let i=0;i<30;i++){
    const conf=document.createElement('div');
    conf.className='confetti-piece';
    conf.style.cssText=`background:${colors[i%colors.length]};left:${Math.random()*100}vw;top:-10px;animation-delay:${Math.random()*2}s;animation-duration:${1+Math.random()*1.5}s;transform:rotate(${Math.random()*360}deg)`;
    document.body.appendChild(conf);
    setTimeout(()=>conf.remove(),4000);
  }
}

// ============ SPIN WHEEL ============
const spinPrizes=['Rp 5.000','Rp 2.000','Rp 1.000','Rp 500','Rp 10.000','Rp 3.000','Rp 750','Rp 1.500'];
const spinColors=['#FFD700','#FF8C00','#FFA500','#FFEC8B','#FF6347','#FFD700','#FF8C00','#FFA500'];
let currentAngle=0;

function initSpinWheel() {
  const canvas=document.getElementById('spinWheel');
  if(!canvas)return;
  const ctx=canvas.getContext('2d');
  drawWheel(ctx,canvas.width,canvas.height,currentAngle);
}

function drawWheel(ctx, w, h, rotation) {
  const cx=w/2,cy=h/2,r=cx-10;
  const slices=spinPrizes.length;
  const arc=2*Math.PI/slices;
  ctx.clearRect(0,0,w,h);
  for(let i=0;i<slices;i++){
    const start=rotation+i*arc;
    ctx.beginPath();ctx.moveTo(cx,cy);ctx.arc(cx,cy,r,start,start+arc);ctx.closePath();
    ctx.fillStyle=spinColors[i];ctx.fill();
    ctx.strokeStyle='rgba(255,255,255,0.3)';ctx.lineWidth=2;ctx.stroke();
    ctx.save();ctx.translate(cx,cy);ctx.rotate(start+arc/2);
    ctx.textAlign='right';ctx.fillStyle='#000';ctx.font='bold 11px Inter';
    ctx.fillText(spinPrizes[i],r-10,5);ctx.restore();
  }
  // Center circle
  ctx.beginPath();ctx.arc(cx,cy,18,0,2*Math.PI);
  ctx.fillStyle='#0A0E1A';ctx.fill();
  ctx.strokeStyle='#FFD700';ctx.lineWidth=3;ctx.stroke();
  ctx.fillStyle='#FFD700';ctx.font='bold 10px Inter';ctx.textAlign='center';ctx.fillText('SPIN',cx,cy+4);
}

function animateSpin(prizeIndex, callback) {
  const canvas=document.getElementById('spinWheel');
  if(!canvas){if(callback)callback();return;}
  const ctx=canvas.getContext('2d');
  const slices=spinPrizes.length;
  const arc=2*Math.PI/slices;
  const targetSlice=prizeIndex;
  const extraSpins=5+Math.random()*3;
  const targetAngle=-(targetSlice*arc+arc/2)+Math.PI/2;
  const totalRotation=extraSpins*2*Math.PI+((targetAngle-currentAngle)%(2*Math.PI));
  const duration=4000;
  const start=performance.now();
  const startAngle=currentAngle;
  function ease(t){return t<0.5?2*t*t:1-Math.pow(-2*t+2,2)/2;}
  function frame(now) {
    const elapsed=now-start;
    const progress=Math.min(elapsed/duration,1);
    currentAngle=startAngle+totalRotation*ease(progress);
    drawWheel(ctx,canvas.width,canvas.height,currentAngle);
    if(progress<1){requestAnimationFrame(frame);}
    else{currentAngle=currentAngle%(2*Math.PI);if(callback)callback();}
  }
  requestAnimationFrame(frame);
}

// ============ MINING CHART ============
function initMiningChart() {
  const container=document.getElementById('miningChart');
  if(!container)return;
  const data=JSON.parse(container.dataset.chart||'[]');
  if(!data.length)return;
  const maxVal=Math.max(...data.map(d=>d.total),1);
  container.innerHTML='';
  data.forEach(d=>{
    const wrap=document.createElement('div');
    wrap.className='chart-bar-wrap';
    const pct=Math.max(4,(d.total/maxVal)*140);
    wrap.innerHTML=`<div class="chart-bar-val">${d.total>0?'Rp '+Math.floor(d.total/1000)+'rb':''}</div><div class="chart-bar" style="height:${pct}px"></div><div class="chart-bar-label">${d.date}</div>`;
    container.appendChild(wrap);
  });
}

// ============ MINING CALENDAR ============
function initMiningCalendar() {
  const container=document.getElementById('miningCalendar');
  if(!container)return;
  const calendar=JSON.parse(container.dataset.calendar||'{}');
  const month=container.dataset.month;
  if(!month)return;
  const [y,m]=month.split('-').map(Number);
  const daysInMonth=new Date(y,m,0).getDate();
  const firstDay=new Date(y,m-1,1).getDay();
  const today=new Date().toISOString().split('T')[0];
  const headers=['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
  let html=headers.map(h=>`<div class="cal-day-header">${h}</div>`).join('');
  for(let i=0;i<firstDay;i++) html+=`<div class="cal-day empty"></div>`;
  for(let d=1;d<=daysInMonth;d++){
    const date=`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const status=calendar[date];
    const isToday=date===today;
    const cls=status==='success'?'success':status==='skipped'?'skipped':date>today?'future':'';
    html+=`<div class="cal-day ${cls} ${isToday?'today':''}">${d}</div>`;
  }
  container.innerHTML=html;
}

// ============ INIT ============
document.addEventListener('DOMContentLoaded',()=>{
  // Observe elements for fade-in animation
  const observer=new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='translateY(0)';observer.unobserve(e.target);}
    });
  },{threshold:0.1});
  document.querySelectorAll('.animate-fadeInUp').forEach(el=>{
    el.style.opacity='0';el.style.transform='translateY(20px)';el.style.transition='opacity 0.5s ease,transform 0.5s ease';
    observer.observe(el);
  });
});

}
</script>
<script>
(function tryLucide(){if(typeof lucide!=="undefined"){lucide.createIcons();}else{setTimeout(tryLucide,50);}})();
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
