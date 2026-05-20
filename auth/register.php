<?php
require_once __DIR__ . '/../config/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$error = '';
$refCode = sanitize($_GET['ref'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username  = sanitize($_POST['username'] ?? '');
    $fullname  = sanitize($_POST['full_name'] ?? '');
    $phone     = sanitize($_POST['phone'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $ref       = sanitize($_POST['referral_code'] ?? '');
    $terms     = $_POST['terms'] ?? '';

    if (!$username||!$fullname||!$phone||!$email||!$password||!$confirm)
        $error = 'Semua field wajib diisi';
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
        $error = 'Username 3-20 karakter, hanya huruf, angka, dan underscore';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Format email tidak valid';
    elseif (!preg_match('/^[0-9]{9,15}$/', $phone))
        $error = 'Nomor HP tidak valid';
    elseif (strlen($password) < 6)
        $error = 'Password minimal 6 karakter';
    elseif ($password !== $confirm)
        $error = 'Konfirmasi password tidak cocok';
    elseif (!$terms)
        $error = 'Kamu harus menyetujui syarat & ketentuan';
    else {
        $result = registerUser([
            'username'  => $username,
            'full_name' => $fullname,
            'phone'     => $phone,
            'email'     => $email,
            'password'  => $password,
            'referral_code' => $ref
        ]);
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            redirect(APP_URL . '/pages/dashboard.php?welcome=1');
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
<title>Daftar - <?php echo APP_NAME; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
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
.required{color:#EF4444}
.checkbox-wrapper{display:flex;align-items:flex-start;gap:10px;cursor:pointer}
.checkbox-wrapper input[type="checkbox"]{width:18px;height:18px;accent-color:#FFD700;flex-shrink:0;margin-top:2px}
.checkbox-label{font-size:13px;color:#9CA3AF}
.auth-links{text-align:center;margin-top:16px}
.auth-link{color:#9CA3AF;font-size:13px;display:inline-flex;align-items:center;gap:6px}
.toast-container{position:fixed;top:20px;right:16px;z-index:400;display:flex;flex-direction:column;gap:8px}
.toast{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#161C2E;border:1px solid #1E2A45;border-radius:12px;font-size:13px;min-width:200px}
.toast-error{border-color:rgba(239,68,68,0.3);color:#EF4444}
.toast-success{border-color:rgba(16,185,129,0.3);color:#10B981}
@keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.animate-fadeInUp{animation:fadeInUp 0.5s ease forwards}
.animate-fadeInDown{animation:fadeInUp 0.5s ease forwards}
.animate-shake{animation:shake 0.4s ease}
</style>
</head>
<body class="theme-dark auth-page">
<div class="auth-bg">
  <div class="auth-container">
    <div class="auth-logo animate-fadeInDown">
      <div class="logo-icon"><i data-lucide="zap"></i></div>
      <h1 class="logo-text"><?php echo APP_NAME; ?></h1>
      <p class="logo-tagline">Daftar Gratis &amp; Dapat Bonus <?php echo formatRupiah((float)getSetting('register_bonus','15000')); ?></p>
    </div>

    <div class="auth-card animate-fadeInUp">
      <h2 class="auth-title">Buat Akun Baru</h2>
      <?php if ($error): ?>
      <div class="alert alert-error animate-shake">
        <i data-lucide="alert-circle"></i> <?php echo htmlspecialchars($error); ?>
      </div>
      <?php endif; ?>

      <form method="POST" id="registerForm" autocomplete="off">
        <?php echo csrfField(); ?>
        <div class="form-group">
          <label class="form-label">Username <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="at-sign" class="input-icon"></i>
            <input type="text" name="username" class="form-input" placeholder="Buat username unik" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="user" class="input-icon"></i>
            <input type="text" name="full_name" class="form-input" placeholder="Nama sesuai KTP" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nomor WhatsApp <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="phone" class="input-icon"></i>
            <input type="tel" name="phone" class="form-input" placeholder="Contoh: 08123456789" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="mail" class="input-icon"></i>
            <input type="email" name="email" class="form-input" placeholder="Masukkan email aktif" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="lock" class="input-icon"></i>
            <input type="password" name="password" id="regPw" class="form-input" placeholder="Minimal 6 karakter" required>
            <button type="button" class="input-toggle-pw" onclick="togglePw('regPw',this)">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="lock" class="input-icon"></i>
            <input type="password" name="confirm_password" id="regPwConf" class="form-input" placeholder="Ulangi password" required>
            <button type="button" class="input-toggle-pw" onclick="togglePw('regPwConf',this)">
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
            <input type="text" name="captcha" id="captchaInput" class="form-input" placeholder="Kode verifikasi" required>
          </div>
          <input type="hidden" name="captcha_key" id="captchaKey">
        </div>
        <div class="form-group">
          <label class="form-label">Kode Referral (Opsional)</label>
          <div class="input-wrapper">
            <i data-lucide="gift" class="input-icon"></i>
            <input type="text" name="referral_code" class="form-input" placeholder="Kode referral teman" value="<?php echo htmlspecialchars($_POST['referral_code'] ?? $refCode); ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="checkbox-wrapper">
            <input type="checkbox" name="terms" value="1" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
            <span class="checkbox-label">Saya menyetujui <a href="<?php echo APP_URL; ?>/pages/terms.php" target="_blank" class="text-gold">Syarat &amp; Ketentuan</a></span>
          </label>
        </div>
        <button type="submit" class="btn btn-primary btn-full">
          <i data-lucide="user-check"></i> Daftar Sekarang
        </button>
      </form>

      <div class="auth-links">
        <a href="<?php echo APP_URL; ?>/auth/login.php" class="auth-link">
          <i data-lucide="log-in"></i> Sudah punya akun? Masuk
        </a>
      </div>
    </div>
  </div>
</div>
<div id="toastContainer" class="toast-container"></div>
<script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/animations.js"></script>
<script>
lucide.createIcons();
window.NOXARA = { appUrl: '<?php echo APP_URL; ?>' };

function showToast(msg, type) {
    type = type || 'info';
    var c = document.getElementById('toastContainer');
    if (!c) return;
    var t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.innerHTML = '<span>' + msg + '</span>';
    c.appendChild(t);
    setTimeout(function() { t.remove(); }, 3000);
}

function togglePw(id, btn) {
    var input = document.getElementById(id);
    if (!input) return;
    var isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden ? '<i data-lucide="eye-off"></i>' : '<i data-lucide="eye"></i>';
    if (typeof lucide !== 'undefined') lucide.createIcons({nodes:[btn]});
}

function refreshCaptcha() {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    var code = '';
    for (var i = 0; i < 5; i++) code += chars[Math.floor(Math.random() * chars.length)];
    var display = document.getElementById('captchaDisplay');
    var key = document.getElementById('captchaKey');
    if (display) {
        display.textContent = '';
        for (var j = 0; j < code.length; j++) {
            var span = document.createElement('span');
            span.textContent = code[j];
            span.style.cssText = 'color:hsl(' + (45 + j * 20) + ',90%,65%);transform:rotate(' + ((Math.random() - 0.5) * 15) + 'deg);display:inline-block;margin:0 1px';
            display.appendChild(span);
        }
    }
    if (key) key.value = btoa(code);
}
refreshCaptcha();

document.getElementById('registerForm').addEventListener('submit', function(e) {
    var input = document.getElementById('captchaInput').value.trim().toUpperCase();
    var stored = atob(document.getElementById('captchaKey').value);
    if (input !== stored) {
        e.preventDefault();
        showToast('Kode verifikasi salah!', 'error');
        refreshCaptcha();
        document.getElementById('captchaInput').value = '';
        return;
    }
    var pw = document.getElementById('regPw').value;
    var cf = document.getElementById('regPwConf').value;
    if (pw !== cf) {
        e.preventDefault();
        showToast('Password tidak cocok!', 'error');
    }
});
</script>
</body>
</html>
