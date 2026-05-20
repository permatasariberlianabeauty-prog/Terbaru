<?php
// ============================================================
// NOXARA - auth/register.php
// ============================================================
require_once __DIR__ . '/../config/bootstrap.php';

if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$error   = '';
$refCode = isset($_GET['ref']) ? sanitize($_GET['ref']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = sanitize(isset($_POST['username'])         ? $_POST['username']         : '');
    $fullname = sanitize(isset($_POST['full_name'])        ? $_POST['full_name']        : '');
    $phone    = sanitize(isset($_POST['phone'])            ? $_POST['phone']            : '');
    $email    = sanitize(isset($_POST['email'])            ? $_POST['email']            : '');
    $password = isset($_POST['password'])                  ? $_POST['password']         : '';
    $confirm  = isset($_POST['confirm_password'])          ? $_POST['confirm_password'] : '';
    $ref      = sanitize(isset($_POST['referral_code'])    ? $_POST['referral_code']    : '');
    $terms    = isset($_POST['terms'])                     ? $_POST['terms']            : '';

    if (!$username || !$fullname || !$phone || !$email || !$password || !$confirm) {
        $error = 'Semua field wajib diisi';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = 'Username 3-20 karakter, hanya huruf, angka, dan underscore';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } elseif (!preg_match('/^[0-9]{9,15}$/', $phone)) {
        $error = 'Nomor HP tidak valid (9-15 angka)';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok';
    } elseif (!$terms) {
        $error = 'Kamu harus menyetujui syarat & ketentuan';
    } else {
        $result = registerUser([
            'username'     => $username,
            'full_name'    => $fullname,
            'phone'        => $phone,
            'email'        => $email,
            'password'     => $password,
            'referral_code'=> $ref,
        ]);
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            redirect(APP_URL . '/pages/dashboard.php?welcome=1');
        } else {
            $error = $result['message'];
        }
    }
}

$regBonus = formatRupiah((float)getSetting('register_bonus', '15000'));
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="theme-color" content="#0A0E1A">
<title>Daftar Gratis - <?= APP_NAME ?></title>
<?php include __DIR__ . '/../includes/head_assets.php'; ?>
</head>
<body class="theme-dark">
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(ellipse at top,rgba(255,215,0,0.05),transparent 60%)">
  <div style="width:100%;max-width:400px">

    <!-- Logo -->
    <div style="text-align:center;margin-bottom:24px">
      <div style="width:64px;height:64px;background:linear-gradient(135deg,#FFD700,#FF8C00);border-radius:18px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px">
        <i data-lucide="zap" style="width:32px;height:32px;color:#000"></i>
      </div>
      <h1 style="font-size:28px;font-weight:900;background:linear-gradient(135deg,#FFD700,#FF8C00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"><?= APP_NAME ?></h1>
      <p style="color:#9CA3AF;font-size:13px;margin-top:4px">Daftar Gratis &amp; Dapat Bonus <?= $regBonus ?></p>
    </div>

    <!-- Card -->
    <div class="auth-card" style="background:#161C2E;border:1px solid #1E2A45;border-radius:16px;padding:24px;margin-bottom:16px">
      <h2 style="font-size:20px;font-weight:700;margin-bottom:16px">Buat Akun Baru</h2>

      <?php if ($error): ?>
      <div class="alert alert-error" style="padding:12px 16px;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#FCA5A5;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;font-size:13px">
        <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" id="regForm" autocomplete="off">
        <?= csrfField() ?>

        <?php
        $fields = [
            ['username',         'text',     'at-sign',      'Username *',        'Buat username unik (3-20 karakter)'],
            ['full_name',        'text',     'user',         'Nama Lengkap *',    'Nama sesuai KTP'],
            ['phone',            'tel',      'phone',        'Nomor WhatsApp *',  'Contoh: 08123456789'],
            ['email',            'email',    'mail',         'Email *',           'Masukkan email aktif'],
        ];
        foreach ($fields as $f):
            $val = htmlspecialchars(isset($_POST[$f[0]]) ? $_POST[$f[0]] : '');
        ?>
        <div style="margin-bottom:12px">
          <label style="display:block;margin-bottom:5px;font-size:13px;color:#9CA3AF"><?= $f[3] ?></label>
          <div style="position:relative;display:flex;align-items:center">
            <i data-lucide="<?= $f[2] ?>" style="position:absolute;left:14px;width:16px;height:16px;color:#6B7280"></i>
            <input type="<?= $f[1] ?>" name="<?= $f[0] ?>" value="<?= $val ?>"
              placeholder="<?= $f[4] ?>" required
              style="width:100%;padding:11px 16px 11px 42px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:15px;outline:none">
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Password -->
        <div style="margin-bottom:12px">
          <label style="display:block;margin-bottom:5px;font-size:13px;color:#9CA3AF">Password * (min 6 karakter)</label>
          <div style="position:relative;display:flex;align-items:center">
            <i data-lucide="lock" style="position:absolute;left:14px;width:16px;height:16px;color:#6B7280"></i>
            <input type="password" name="password" id="pw1" required placeholder="Minimal 6 karakter"
              style="width:100%;padding:11px 44px 11px 42px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:15px;outline:none">
            <button type="button" onclick="tpw('pw1',this)" style="position:absolute;right:12px;color:#6B7280;background:none;border:none;cursor:pointer;display:flex">
              <i data-lucide="eye" style="width:18px;height:18px"></i>
            </button>
          </div>
        </div>

        <!-- Confirm Password -->
        <div style="margin-bottom:12px">
          <label style="display:block;margin-bottom:5px;font-size:13px;color:#9CA3AF">Konfirmasi Password *</label>
          <div style="position:relative;display:flex;align-items:center">
            <i data-lucide="lock" style="position:absolute;left:14px;width:16px;height:16px;color:#6B7280"></i>
            <input type="password" name="confirm_password" id="pw2" required placeholder="Ulangi password"
              style="width:100%;padding:11px 44px 11px 42px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:15px;outline:none">
            <button type="button" onclick="tpw('pw2',this)" style="position:absolute;right:12px;color:#6B7280;background:none;border:none;cursor:pointer;display:flex">
              <i data-lucide="eye" style="width:18px;height:18px"></i>
            </button>
          </div>
        </div>

        <!-- Captcha -->
        <div style="margin-bottom:12px">
          <label style="display:block;margin-bottom:5px;font-size:13px;color:#9CA3AF">Kode Verifikasi</label>
          <div style="display:flex;gap:8px;margin-bottom:8px">
            <div id="captchaDisplay" style="flex:1;background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px 16px;font-family:monospace;font-size:20px;font-weight:700;letter-spacing:6px;color:#FFD700;text-align:center;user-select:none"></div>
            <button type="button" onclick="genCaptcha()" style="background:#141928;border:1.5px solid #1E2A45;border-radius:8px;padding:10px 14px;color:#9CA3AF;cursor:pointer">
              <i data-lucide="refresh-cw" style="width:18px;height:18px"></i>
            </button>
          </div>
          <div style="position:relative;display:flex;align-items:center">
            <i data-lucide="shield" style="position:absolute;left:14px;width:16px;height:16px;color:#6B7280"></i>
            <input type="text" id="captchaInput" placeholder="Masukkan kode" required
              style="width:100%;padding:11px 16px 11px 42px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:15px;outline:none;letter-spacing:4px;font-weight:700">
          </div>
          <input type="hidden" id="captchaKey" name="captcha_key">
        </div>

        <!-- Referral -->
        <div style="margin-bottom:12px">
          <label style="display:block;margin-bottom:5px;font-size:13px;color:#9CA3AF">Kode Referral (Opsional)</label>
          <div style="position:relative;display:flex;align-items:center">
            <i data-lucide="gift" style="position:absolute;left:14px;width:16px;height:16px;color:#6B7280"></i>
            <input type="text" name="referral_code" value="<?= htmlspecialchars($refCode) ?>" placeholder="Kode referral teman"
              style="width:100%;padding:11px 16px 11px 42px;background:#141928;border:1.5px solid #1E2A45;border-radius:10px;color:#E8EAED;font-size:15px;outline:none">
          </div>
        </div>

        <!-- Terms -->
        <div style="margin-bottom:16px;display:flex;align-items:flex-start;gap:10px">
          <input type="checkbox" name="terms" id="terms" value="1" required
            <?= isset($_POST['terms']) ? 'checked' : '' ?>
            style="width:18px;height:18px;accent-color:#FFD700;margin-top:2px;flex-shrink:0">
          <label for="terms" style="font-size:13px;color:#9CA3AF;cursor:pointer">
            Saya menyetujui
            <a href="<?= APP_URL ?>/pages/terms.php" target="_blank" style="color:#FFD700">Syarat &amp; Ketentuan</a>
          </label>
        </div>

        <button type="submit" id="regBtn"
          style="width:100%;padding:14px;background:linear-gradient(135deg,#FFD700,#FF8C00);color:#000;font-weight:700;font-size:15px;border:none;border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
          <i data-lucide="user-check" style="width:18px;height:18px"></i>
          Daftar Sekarang
        </button>
      </form>

      <div style="text-align:center;margin-top:16px">
        <a href="<?= APP_URL ?>/auth/login.php" style="color:#9CA3AF;font-size:13px;display:inline-flex;align-items:center;gap:6px">
          <i data-lucide="log-in" style="width:14px;height:14px"></i>
          Sudah punya akun? Masuk
        </a>
      </div>
    </div>

  </div>
</div>

<div id="toastCont" style="position:fixed;top:20px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px"></div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
// Init icons after lucide loads
function initIcons() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    } else {
        setTimeout(initIcons, 100);
    }
}
initIcons();

window.NOXARA = { appUrl: '<?= APP_URL ?>' };

// Simple toast
function toast(msg, type) {
    var c = document.getElementById('toastCont');
    var d = document.createElement('div');
    d.style.cssText = 'padding:12px 16px;background:#161C2E;border:1px solid ' + (type==='error'?'rgba(239,68,68,.4)':'rgba(16,185,129,.4)') + ';color:' + (type==='error'?'#FCA5A5':'#6EE7B7') + ';border-radius:10px;font-size:13px;box-shadow:0 4px 20px rgba(0,0,0,.4);min-width:200px';
    d.textContent = msg;
    c.appendChild(d);
    setTimeout(function() { if (d.parentNode) d.parentNode.removeChild(d); }, 3000);
}

// Toggle password
function tpw(id, btn) {
    var el = document.getElementById(id);
    if (!el) return;
    el.type = el.type === 'password' ? 'text' : 'password';
    btn.innerHTML = el.type === 'text'
        ? '<i data-lucide="eye-off" style="width:18px;height:18px"></i>'
        : '<i data-lucide="eye" style="width:18px;height:18px"></i>';
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [btn] });
}

// Captcha
var captchaCode = '';
function genCaptcha() {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    captchaCode = '';
    for (var i = 0; i < 5; i++) captchaCode += chars[Math.floor(Math.random() * chars.length)];
    var d = document.getElementById('captchaDisplay');
    if (d) {
        d.innerHTML = '';
        for (var j = 0; j < captchaCode.length; j++) {
            var s = document.createElement('span');
            s.textContent = captchaCode[j];
            s.style.cssText = 'color:hsl(' + (45 + j * 20) + ',90%,65%);display:inline-block;margin:0 1px;transform:rotate(' + ((Math.random() - .5) * 15) + 'deg)';
            d.appendChild(s);
        }
    }
    var k = document.getElementById('captchaKey');
    if (k) k.value = btoa(captchaCode);
}
genCaptcha();

document.getElementById('regForm').addEventListener('submit', function(e) {
    var inp = document.getElementById('captchaInput').value.trim().toUpperCase();
    var key = document.getElementById('captchaKey').value;
    if (!key || inp !== atob(key)) {
        e.preventDefault();
        toast('Kode verifikasi salah!', 'error');
        genCaptcha();
        document.getElementById('captchaInput').value = '';
        return;
    }
    var pw = document.getElementById('pw1').value;
    var cf = document.getElementById('pw2').value;
    if (pw !== cf) {
        e.preventDefault();
        toast('Password tidak cocok!', 'error');
        return;
    }
    document.getElementById('regBtn').textContent = 'Mendaftar...';
    document.getElementById('regBtn').disabled = true;
});
</script>
</body>
</html>
