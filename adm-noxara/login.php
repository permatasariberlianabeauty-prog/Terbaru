<?php
require_once __DIR__.'/../config/bootstrap.php';
if(isAdmin()) redirect(APP_URL.'/adm-noxara/index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf();
    $user=sanitize($_POST['username']??'');
    $pass=$_POST['password']??'';
    if(!$user||!$pass){ $error='Isi semua field'; }
    else {
        $u=dbEscape($user);
        $r=dbQuery("SELECT * FROM admins WHERE username='$u' LIMIT 1");
        if($r&&$r->num_rows>0){
            $admin=$r->fetch_assoc();
            if(verifyPassword($pass,$admin['password'])){
                $_SESSION['admin_id']=$admin['id'];
                $_SESSION['admin_username']=$admin['username'];
                dbQuery("UPDATE admins SET last_login=NOW() WHERE id=".(int)$admin['id']);
                redirect(APP_URL.'/adm-noxara/index.php');
            } else { $error='Password salah'; }
        } else { $error='Admin tidak ditemukan'; }
    }
}
?>
<!DOCTYPE html><html lang="id" data-theme="dark"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no"><title>Admin Login - <?=APP_NAME?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?=APP_URL?>/assets/css/style.css">
<link rel="stylesheet" href="<?=APP_URL?>/assets/css/mobile.css">
</head><body class="theme-dark auth-page">
<div class="auth-bg"><div class="auth-container">
<div class="auth-logo animate-fadeInDown">
  <div class="logo-icon" style="background:linear-gradient(135deg,#FF4444,#FF8C00)"><i data-lucide="shield"></i></div>
  <h1 class="logo-text"><?=APP_NAME?> Admin</h1>
  <p class="logo-tagline">Panel Kontrol Administrator</p>
</div>
<div class="auth-card animate-fadeInUp">
  <h2 class="auth-title">Login Admin</h2>
  <?php if($error):?><div class="alert alert-error animate-shake"><i data-lucide="alert-circle"></i> <?=htmlspecialchars($error)?></div><?php endif;?>
  <form method="POST">
    <?=csrfField()?>
    <div class="form-group"><label class="form-label">Username Admin</label><div class="input-wrapper"><i data-lucide="user" class="input-icon"></i><input type="text" name="username" class="form-input" required autofocus></div></div>
    <div class="form-group"><label class="form-label">Password</label><div class="input-wrapper"><i data-lucide="lock" class="input-icon"></i><input type="password" name="password" id="adminPw" class="form-input" required><button type="button" class="input-toggle-pw" onclick="togglePw('adminPw',this)"><i data-lucide="eye"></i></button></div></div>
    <button type="submit" class="btn btn-primary btn-full btn-lg"><i data-lucide="log-in"></i> Masuk ke Panel</button>
  </form>
</div>
</div></div>
<script src="<?=APP_URL?>/assets/js/main.js"></script>
<script>lucide.createIcons();window.NOXARA={appUrl:'<?=APP_URL?>'};</script>
</body></html>
