<?php
require_once __DIR__ . '/../config/bootstrap.php';
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
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/animations.css">
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
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>lucide.createIcons(); window.NOXARA={appUrl:'<?=APP_URL?>'};</script>
</body>
</html>
