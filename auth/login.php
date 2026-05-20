<?php
require_once __DIR__ . '/../config/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');
if (isAdmin()) redirect(APP_URL . '/adm-noxara/index.php');

$error = '';

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
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="theme-color" content="#0A0E1A">
<title>Login - <?php echo APP_NAME; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"></noscript>
<script src="https://unpkg.com/lucide@0.263.1/dist/umd/lucide.min.js" defer></script>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/animations.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0A0E1A;color:#E8EAED;font-family:Inter,-apple-system,sans-serif;min-height:100vh;overflow-x:hidden}
a{text-decoration:none;color:inherit}
button{cursor:pointer;border:none;background:none;font-family:inherit}
.auth-bg{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(ellipse at top,rgba(255,215,0,0.05),transparent 60%)}
.auth-container{width:100%;max-width:400px}
.auth-logo{text-align:center;margin-bottom:24px}
.logo-icon{width:64px;height:64px;background:linear-gradient(135deg,#FFD700,#FF8C00);border-radius:18px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px}
.logo-icon svg{width:32px;height:32px;color:#000}
.logo-text{font-size:28px;font-weight:900;background:linear-gradient(135deg,#FFD700,#FF8C00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.logo-tagline{color:#9CA3AF;font-size:13px;margin-top:4px}
.auth-card{background:#161C2E;border:1px solid #1E2A45;border-radius:16px;padding:24px;margin-bottom:16px}
.auth-title{font-size:20px;font-weight:700;margin-bottom:4px}
.auth-subtitle{color:#9CA3AF;font-size:13px;margin-bottom:20px}
.form-group{margin-bottom:14px}
.form-label{display:block;margin-bottom:6px;font-weight:500;color:#9CA3AF;font-size:13px}
.form-input{width:100%;padding:12px 16px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:16px;outline:none}
.form-input:focus{border-color:#FFD700}
.form-input::placeholder{color:#6B7280}
.input-wrapper{position:relative;display:flex;align-items:center}
.input-wrapper .form-input{padding-left:44px}
.input-icon{position:absolute;left:14px;width:18px;height:18px;color:#6B7280}
.input-toggle-pw{position:absolute;right:14px;color:#6B7280;display:flex;cursor:pointer}
.input-toggle-pw svg{width:18px;height:18px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:12px 20px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;border:2px solid transparent;width:auto}
.btn-full{width:100%}
.btn-lg{padding:14px 24px;font-size:16px;border-radius:14px}
.btn-primary{background:linear-gradient(135deg,#FFD700,#FF8C00);color:#000;font-weight:700}
.btn-outline{background:transparent;border:2px solid #1E2A45;color:#E8EAED}
.btn svg{width:18px;height:18px}
.alert{padding:12px 16px;border-radius:10px;display:flex;align-items:center;gap:10px;font-size:13px;margin-bottom:16px}
.alert svg{width:18px;height:18px;flex-shrink:0}
.alert-error{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#FCA5A5}
.captcha-wrapper{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.captcha-display{background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px 16px;font-family:monospace;font-size:20px;font-weight:700;letter-spacing:6px;color:#FFD700;flex:1;text-align:center;user-select:none}
.captcha-refresh{background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px;color:#9CA3AF;flex-shrink:0;cursor:pointer}
.captcha-refresh svg{width:18px;height:18px}
.mt-2{margin-top:8px}.mb-3{margin-bottom:16px}
.text-gold{color:#FFD700}
.text-sm{font-size:12px}
.text-center{text-align:center}
.platform-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:20px 0}
.stat-item{text-align:center;padding:12px 8px;background:#161C2E;border:1px solid #1E2A45;border-radius:10px}
.stat-num{display:block;font-size:18px;font-weight:800;color:#FFD700}
.stat-label{font-size:11px;color:#9CA3AF;display:block}
.auth-links{text-align:center;margin-top:16px}
.auth-link{color:#9CA3AF;font-size:13px;display:inline-flex;align-items:center;gap:6px}
.auth-divider{text-align:center;color:#6B7280;font-size:13px;margin:16px 0;position:relative}
.auth-divider::before,.auth-divider::after{content:'';position:absolute;top:50%;width:40%;height:1px;background:#1E2A45}
.auth-divider::before{left:0}.auth-divider::after{right:0}
.about-platform-link{display:flex;align-items:center;justify-content:center;gap:8px;color:#9CA3AF;font-size:13px;margin-top:12px}
.toast-container{position:fixed;top:20px;right:16px;z-index:400;display:flex;flex-direction:column;gap:8px}
.toast{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#161C2E;border:1px solid #1E2A45;border-radius:12px;font-size:13px;min-width:200px}
.toast-error{border-color:rgba(239,68,68,0.3);color:#EF4444}
@keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.animate-fadeInUp{animation:fadeInUp 0.5s ease forwards}
.animate-fadeInDown{animation:fadeInDown 0.5s ease forwards}
.animate-shake{animation:shake 0.4s ease}
</style>
</head>
<body class="theme-dark auth-page">
<div class="auth-bg">
  <div class="auth-container">
    <div class="auth-logo animate-fadeInDown">
      <div class="logo-icon"><i data-lucide="zap"></i></div>
      <h1 class="logo-text"><?php echo APP_NAME; ?></h1>
      <p class="logo-tagline"><?php echo getSetting('site_tagline', APP_TAGLINE); ?></p>
    </div>

    <div class="auth-card animate-fadeInUp">
      <h2 class="auth-title">Masuk ke Akun</h2>
      <p class="auth-subtitle">Gunakan email, nomor HP, atau username</p>

      <?php if ($error): ?>
      <div class="alert alert-error animate-shake">
        <i data-lucide="alert-circle"></i> <?php echo htmlspecialchars($error); ?>
      </div>
      <?php endif; ?>

      <form method="POST" id="loginForm" autocomplete="off">
        <?php echo csrfField(); ?>
        <div class="form-group">
          <label class="form-label">Email / No. HP / Username</label>
          <div class="input-wrapper">
            <i data-lucide="user" class="input-icon"></i>
            <input type="text" name="identifier" class="form-input" placeholder="Masukkan identitas" value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>" required autofocus>
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
        <a href="<?php echo APP_URL; ?>/auth/forgot.php" class="auth-link">
          <i data-lucide="help-circle"></i> Lupa Password?
        </a>
      </div>
    </div>

    <div class="auth-divider"><span>Belum punya akun?</span></div>
    <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-outline btn-full">
      <i data-lucide="user-plus"></i> Daftar Sekarang - GRATIS
    </a>

    <div class="platform-stats animate-fadeInUp">
      <div class="stat-item">
        <span class="stat-num" data-target="125000">125.000+</span>
        <span class="stat-label">Member Aktif</span>
      </div>
      <div class="stat-item">
        <span class="stat-num">Rp 8,5Jt+</span>
        <span class="stat-label">Total Terbayar</span>
      </div>
      <div class="stat-item">
        <span class="stat-num">99.9%</span>
        <span class="stat-label">Uptime</span>
      </div>
    </div>

    <a href="<?php echo APP_URL; ?>/pages/about.php" class="about-platform-link">
      <i data-lucide="info"></i> Tentang Platform Noxara
    </a>
  </div>
</div>

<div id="toastContainer" class="toast-container"></div>
<script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/animations.js"></script>
<script>
/* Init lucide setelah defer load selesai */
(function tryLucide(){
    if(typeof lucide!=='undefined'){ lucide.createIcons(); }
    else { setTimeout(tryLucide, 50); }
})();

window.NOXARA = { appUrl: '<?php echo APP_URL; ?>' };

function showToast(msg, type) {
    type = type || 'info';
    var c = document.getElementById('toastContainer');
    if (!c) return;
    var t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.innerHTML = '<span>' + msg + '</span>';
    c.appendChild(t);
    setTimeout(function() { if(t.parentNode) t.parentNode.removeChild(t); }, 3000);
}

function togglePw(id, btn) {
    var input = document.getElementById(id);
    if (!input) return;
    var isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden
        ? '<i data-lucide="eye-off" style="width:18px;height:18px;display:block"></i>'
        : '<i data-lucide="eye" style="width:18px;height:18px;display:block"></i>';
    if (typeof lucide !== 'undefined') lucide.createIcons({nodes:[btn]});
}

function refreshCaptcha() {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    var code = '';
    for (var i = 0; i < 5; i++) code += chars[Math.floor(Math.random() * chars.length)];
    var display = document.getElementById('captchaDisplay');
    var key = document.getElementById('captchaKey');
    if (display) {
        display.innerHTML = '';
        for (var j = 0; j < code.length; j++) {
            var span = document.createElement('span');
            span.textContent = code[j];
            span.style.cssText = 'color:hsl(' + (45 + j * 20) + ',90%,65%);transform:rotate(' + ((Math.random() - 0.5) * 15) + 'deg);display:inline-block;margin:0 1px;font-style:italic';
            display.appendChild(span);
        }
    }
    if (key) key.value = btoa(code);
}

/* Run captcha immediately when DOM ready */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', refreshCaptcha);
} else {
    refreshCaptcha();
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    var inputEl = document.getElementById('captchaInput');
    var keyEl   = document.getElementById('captchaKey');
    if (!inputEl || !keyEl || !keyEl.value) {
        e.preventDefault();
        showToast('Kode verifikasi belum dimuat. Refresh halaman.', 'error');
        return;
    }
    var input  = inputEl.value.trim().toUpperCase();
    var stored = '';
    try { stored = atob(keyEl.value); } catch(err) { stored = ''; }
    if (!stored || input !== stored) {
        e.preventDefault();
        showToast('Kode verifikasi salah!', 'error');
        refreshCaptcha();
        inputEl.value = '';
    }
});
</script>
</body>
</html>
