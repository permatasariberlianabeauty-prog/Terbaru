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
<title>Daftar - <?= APP_NAME ?></title>
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
      <p class="logo-tagline">Daftar Gratis & Dapat Bonus <?= formatRupiah((float)getSetting('register_bonus','15000')) ?></p>
    </div>

    <div class="auth-card animate-fadeInUp">
      <h2 class="auth-title">Buat Akun Baru</h2>
      <?php if ($error): ?>
      <div class="alert alert-error animate-shake">
        <i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" id="registerForm" autocomplete="off">
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Username <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="at-sign" class="input-icon"></i>
            <input type="text" name="username" class="form-input" placeholder="Buat username unik" value="<?= htmlspecialchars($_POST['username']??'') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="user" class="input-icon"></i>
            <input type="text" name="full_name" class="form-input" placeholder="Nama sesuai KTP" value="<?= htmlspecialchars($_POST['full_name']??'') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nomor WhatsApp <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="phone" class="input-icon"></i>
            <input type="tel" name="phone" class="form-input" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($_POST['phone']??'') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email <span class="required">*</span></label>
          <div class="input-wrapper">
            <i data-lucide="mail" class="input-icon"></i>
            <input type="email" name="email" class="form-input" placeholder="Masukkan email aktif" value="<?= htmlspecialchars($_POST['email']??'') ?>" required>
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
            <input type="text" name="captcha" id="captchaInput" class="form-input" placeholder="Kode verifikasi" required>
          </div>
          <input type="hidden" name="captcha_key" id="captchaKey">
        </div>
        <div class="form-group">
          <label class="form-label">Kode Referral (Opsional)</label>
          <div class="input-wrapper">
            <i data-lucide="gift" class="input-icon"></i>
            <input type="text" name="referral_code" class="form-input" placeholder="Kode referral teman" value="<?= htmlspecialchars($_POST['referral_code']??$refCode) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="checkbox-wrapper">
            <input type="checkbox" name="terms" value="1" <?= isset($_POST['terms'])?'checked':'' ?> required>
            <span class="checkmark"></span>
            <span class="checkbox-label">Saya menyetujui <a href="<?= APP_URL ?>/pages/terms.php" target="_blank" class="text-gold">Syarat & Ketentuan</a></span>
          </label>
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg">
          <i data-lucide="user-check"></i> Daftar Sekarang
        </button>
      </form>

      <div class="auth-links">
        <a href="<?= APP_URL ?>/auth/login.php" class="auth-link">
          <i data-lucide="log-in"></i> Sudah punya akun? Masuk
        </a>
      </div>
    </div>
  </div>
</div>
<div id="toastContainer" class="toast-container"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script src="<?= APP_URL ?>/assets/js/animations.js"></script>
<script>
lucide.createIcons();
window.NOXARA = { appUrl: '<?= APP_URL ?>' };
initCaptcha();
document.getElementById('registerForm').addEventListener('submit', function(e) {
  const input  = document.getElementById('captchaInput').value.trim().toUpperCase();
  const stored = atob(document.getElementById('captchaKey').value);
  if (input !== stored) {
    e.preventDefault();
    showToast('Kode verifikasi salah!', 'error');
    refreshCaptcha();
    document.getElementById('captchaInput').value = '';
  }
  const pw = document.getElementById('regPw').value;
  const cf = document.getElementById('regPwConf').value;
  if (pw !== cf) {
    e.preventDefault();
    showToast('Password tidak cocok!', 'error');
  }
});
</script>
</body>
</html>
