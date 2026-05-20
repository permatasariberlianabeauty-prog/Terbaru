<?php
require_once __DIR__ . '/../config/bootstrap.php';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = defined('APP_URL') ? APP_URL : $protocol . '://' . $_SERVER['HTTP_HOST'];
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$step    = (int)($_GET['step'] ?? 1);
$success = '';
$error   = '';
$waAdmin = getSetting('wa_admin','628000000000');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $identifier = sanitize($_POST['identifier'] ?? '');
    if (empty($identifier)) {
        $error = 'Masukkan email atau nomor HP';
    } else {
        $id = dbEscape($identifier);
        $r  = dbQuery("SELECT id,full_name,phone FROM users WHERE email='$id' OR phone='$id' LIMIT 1");
        if ($r && $r->num_rows > 0) {
            $u   = $r->fetch_assoc();
            $msg = urlencode("Halo Admin Noxara, saya ingin reset password akun saya. Nama: {$u['full_name']}, No HP: {$u['phone']}");
            $success = "https://wa.me/$waAdmin?text=$msg";
        } else {
            $error = 'Akun tidak ditemukan. Periksa kembali email/nomor HP.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title>Lupa Password - <?= APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?=  ?>/assets/css/style.css">
<link rel="stylesheet" href="<?=  ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?=  ?>/assets/css/animations.css">

<style>
/* === NOXARA CRITICAL INLINE CSS === */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0A0E1A;color:#E8EAED;font-family:Inter,-apple-system,sans-serif;min-height:100vh;overflow-x:hidden}
:root{--bg:#0A0E1A;--bg2:#0F1423;--bg3:#141928;--card:#161C2E;--border:#1E2A45;--gold:#FFD700;--gold3:#FF8C00;--text:#E8EAED;--text2:#9CA3AF;--text3:#6B7280;--green:#10B981;--red:#EF4444;--orange:#F59E0B;--radius:16px;--radius-sm:10px;--transition:all 0.3s}
a{text-decoration:none;color:inherit}
button{cursor:pointer;border:none;background:none;font-family:inherit}
.auth-page{background:var(--bg)}
.auth-bg{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(ellipse at top,rgba(255,215,0,0.05),transparent 60%)}
.auth-container{width:100%;max-width:400px}
.auth-logo{text-align:center;margin-bottom:24px}
.logo-icon{width:64px;height:64px;background:linear-gradient(135deg,#FFD700,#FF8C00);border-radius:18px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;box-shadow:0 0 20px rgba(255,215,0,0.2)}
.logo-icon svg{width:32px;height:32px;color:#000}
.logo-text{font-size:28px;font-weight:900;background:linear-gradient(135deg,#FFD700,#FF8C00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.logo-tagline{color:#9CA3AF;font-size:13px;margin-top:4px}
.auth-card{background:#161C2E;border:1px solid #1E2A45;border-radius:16px;padding:24px;margin-bottom:16px}
.auth-title{font-size:20px;font-weight:700;margin-bottom:4px}
.auth-subtitle{color:#9CA3AF;font-size:13px;margin-bottom:20px}
.form-group{margin-bottom:16px}
.form-label{display:block;margin-bottom:6px;font-weight:500;color:#9CA3AF;font-size:13px}
.form-input{width:100%;padding:12px 16px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:16px;outline:none;transition:border-color 0.3s}
.form-input:focus{border-color:#FFD700;box-shadow:0 0 0 3px rgba(255,215,0,0.1)}
.form-input::placeholder{color:#6B7280}
.input-wrapper{position:relative;display:flex;align-items:center}
.input-wrapper .form-input{padding-left:44px}
.input-icon{position:absolute;left:14px;width:18px;height:18px;color:#6B7280;flex-shrink:0}
.input-toggle-pw{position:absolute;right:14px;color:#6B7280;display:flex}
.input-toggle-pw svg{width:18px;height:18px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 20px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;border:2px solid transparent}
.btn-full{width:100%}
.btn-lg{padding:14px 24px;font-size:16px;border-radius:16px}
.btn-primary{background:linear-gradient(135deg,#FFD700,#FF8C00);color:#000;font-weight:700}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 0 20px rgba(255,215,0,0.3)}
.btn-outline{background:transparent;border:2px solid #1E2A45;color:#E8EAED}
.btn-outline:hover{border-color:#FFD700;color:#FFD700}
.btn svg{width:18px;height:18px}
.alert{padding:12px 16px;border-radius:10px;display:flex;align-items:center;gap:10px;font-size:13px;margin-bottom:16px}
.alert svg{width:18px;height:18px;flex-shrink:0}
.alert-error{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#FCA5A5}
.alert-success{background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#6EE7B7}
.alert-warning{background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#FCD34D}
.captcha-wrapper{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.captcha-display{background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px 16px;font-family:monospace;font-size:20px;font-weight:700;letter-spacing:6px;color:#FFD700;flex:1;text-align:center;user-select:none}
.captcha-refresh{background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px;color:#9CA3AF;flex-shrink:0}
.captcha-refresh svg{width:18px;height:18px}
.captcha-row{display:flex;align-items:center;gap:8px}
.captcha-input{max-width:100px;text-align:center;letter-spacing:4px;font-weight:700}
.auth-links{text-align:center;margin-top:16px}
.auth-link{color:#9CA3AF;font-size:13px;display:inline-flex;align-items:center;gap:6px}
.auth-link:hover{color:#FFD700}
.auth-link svg{width:14px;height:14px}
.auth-divider{text-align:center;color:#6B7280;font-size:13px;margin:16px 0;position:relative}
.auth-divider::before,.auth-divider::after{content:'';position:absolute;top:50%;width:40%;height:1px;background:#1E2A45}
.auth-divider::before{left:0}.auth-divider::after{right:0}
.text-gold{color:#FFD700}
.text-sm{font-size:12px}
.text-center{text-align:center}
.mt-2{margin-top:8px}.mt-3{margin-top:16px}.mb-2{margin-bottom:8px}.mb-3{margin-bottom:16px}
.required{color:#EF4444}
.platform-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:20px 0}
.stat-item{text-align:center;padding:12px 8px;background:#161C2E;border:1px solid #1E2A45;border-radius:10px}
.stat-num{display:block;font-size:18px;font-weight:800;color:#FFD700}
.stat-label{font-size:11px;color:#9CA3AF;display:block}
.about-platform-link{display:flex;align-items:center;justify-content:center;gap:8px;color:#9CA3AF;font-size:13px;margin-top:12px}
.about-platform-link svg{width:16px;height:16px}
.checkbox-wrapper{display:flex;align-items:flex-start;gap:10px;cursor:pointer}
.checkbox-wrapper input[type="checkbox"]{width:18px;height:18px;accent-color:#FFD700;flex-shrink:0;margin-top:2px}
.checkbox-label{font-size:13px;color:#9CA3AF}
.auth-back a{display:inline-flex;align-items:center;gap:6px;color:#9CA3AF;font-size:13px;margin-bottom:16px}
.auth-back a svg{width:16px;height:16px}
.toast-container{position:fixed;top:20px;right:16px;z-index:400;display:flex;flex-direction:column;gap:8px;max-width:300px}
.toast{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#161C2E;border:1px solid #1E2A45;border-radius:12px;font-size:13px;min-width:200px}
.toast svg{width:18px;height:18px;flex-shrink:0}
.toast-success{border-color:rgba(16,185,129,0.3);color:#10B981}
.toast-error{border-color:rgba(239,68,68,0.3);color:#EF4444}
@keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
@keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
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
      <h1 class="logo-text"><?= APP_NAME ?></h1>
    </div>

    <div class="auth-card animate-fadeInUp">
      <div class="auth-back">
        <a href="<?= APP_URL ?>/auth/login.php">
          <i data-lucide="arrow-left"></i> Kembali
        </a>
      </div>
      <h2 class="auth-title">Lupa Password</h2>
      <p class="auth-subtitle">Masukkan email atau nomor HP terdaftar untuk mendapatkan bantuan reset password via WhatsApp Admin.</p>

      <?php if ($error): ?>
      <div class="alert alert-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="alert alert-success animate-fadeIn">
        <i data-lucide="check-circle"></i> Akun ditemukan! Klik tombol di bawah untuk menghubungi Admin.
      </div>
      <a href="<?= $success ?>" target="_blank" class="btn btn-whatsapp btn-full btn-lg">
        <i data-lucide="message-circle"></i> Hubungi Admin via WhatsApp
      </a>
      <?php else: ?>
      <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Email atau Nomor HP</label>
          <div class="input-wrapper">
            <i data-lucide="search" class="input-icon"></i>
            <input type="text" name="identifier" class="form-input" placeholder="Masukkan email atau nomor HP" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full">
          <i data-lucide="send"></i> Cari Akun Saya
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
<script>lucide.createIcons(); window.NOXARA={appUrl:'<?=APP_URL?>'};</script>
</body>
</html>
