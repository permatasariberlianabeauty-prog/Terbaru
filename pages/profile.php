<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Profil';
$currentPage = 'profile';

if ($_SERVER['REQUEST_METHOD']==='POST' && isAjax()) {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_info') {
        $fullname = sanitize($_POST['full_name'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $phone    = sanitize($_POST['phone'] ?? '');
        $pw       = $_POST['password'] ?? '';
        if (!verifyPassword($pw, $user['password'])) jsonResponse(['success'=>false,'message'=>'Password salah']);
        $fn = dbEscape($fullname); $em = dbEscape($email); $ph = dbEscape($phone);
        dbQuery("UPDATE users SET full_name='$fn',email='$em',phone='$ph' WHERE id=$uid");
        jsonResponse(['success'=>true,'message'=>'Info akun berhasil diperbarui']);
    }

    if ($action === 'set_pin') {
        $pin  = $_POST['pin'] ?? '';
        $conf = $_POST['confirm_pin'] ?? '';
        $pw   = $_POST['password'] ?? '';
        if (!verifyPassword($pw, $user['password'])) jsonResponse(['success'=>false,'message'=>'Password salah']);
        if (strlen($pin) !== 6 || !ctype_digit($pin)) jsonResponse(['success'=>false,'message'=>'PIN harus 6 digit angka']);
        if ($pin !== $conf) jsonResponse(['success'=>false,'message'=>'Konfirmasi PIN tidak cocok']);
        $hashed = hashPin($pin);
        $stmt = db()->prepare("UPDATE users SET pin=? WHERE id=?");
        $stmt->bind_param('si',$hashed,$uid); $stmt->execute(); $stmt->close();
        addNotification($uid,'PIN Berhasil Dibuat','PIN kamu berhasil dikonfigurasi.','success');
        jsonResponse(['success'=>true,'message'=>'PIN berhasil dibuat!']);
    }

    if ($action === 'change_pin') {
        $oldPin = $_POST['old_pin'] ?? '';
        $newPin = $_POST['new_pin'] ?? '';
        $conf   = $_POST['confirm_pin'] ?? '';
        if (!$user['pin']) jsonResponse(['success'=>false,'message'=>'Belum ada PIN. Buat PIN dulu.']);
        if (!verifyPin($oldPin, $user['pin'])) jsonResponse(['success'=>false,'message'=>'PIN lama salah']);
        if (strlen($newPin) !== 6 || !ctype_digit($newPin)) jsonResponse(['success'=>false,'message'=>'PIN baru harus 6 digit angka']);
        if ($newPin !== $conf) jsonResponse(['success'=>false,'message'=>'Konfirmasi PIN tidak cocok']);
        $hashed = hashPin($newPin);
        $stmt = db()->prepare("UPDATE users SET pin=? WHERE id=?");
        $stmt->bind_param('si',$hashed,$uid); $stmt->execute(); $stmt->close();
        jsonResponse(['success'=>true,'message'=>'PIN berhasil diubah!']);
    }

    if ($action === 'set_bank') {
        $bankName = sanitize($_POST['bank_name'] ?? '');
        $accName  = sanitize($_POST['account_name'] ?? '');
        $accNum   = sanitize($_POST['account_number'] ?? '');
        $pin      = $_POST['pin'] ?? '';
        if (!$user['pin']) jsonResponse(['success'=>false,'message'=>'Buat PIN dulu sebelum mendaftarkan bank']);
        if (!verifyPin($pin, $user['pin'])) jsonResponse(['success'=>false,'message'=>'PIN salah']);
        $existing = dbQuery("SELECT id FROM user_banks WHERE user_id=$uid LIMIT 1");
        if ($existing && $existing->num_rows > 0) {
            if ((int)$user['bank_changes'] >= 3) jsonResponse(['success'=>false,'message'=>'Batas ganti bank sudah tercapai (3x). Hubungi CS.','contact_cs'=>true]);
            $pw = $_POST['password'] ?? '';
            if (!verifyPassword($pw, $user['password'])) jsonResponse(['success'=>false,'message'=>'Password salah']);
            $bn=dbEscape($bankName);$an=dbEscape($accName);$ac=dbEscape($accNum);
            dbQuery("UPDATE user_banks SET bank_name='$bn',account_name='$an',account_number='$ac' WHERE user_id=$uid");
            dbQuery("UPDATE users SET bank_changes=bank_changes+1 WHERE id=$uid");
        } else {
            $stmt=db()->prepare("INSERT INTO user_banks (user_id,bank_name,account_name,account_number) VALUES (?,?,?,?)");
            $stmt->bind_param('isss',$uid,$bankName,$accName,$accNum);$stmt->execute();$stmt->close();
        }
        addNotification($uid,'Akun Bank Diperbarui','Informasi bank kamu berhasil disimpan.','success');
        jsonResponse(['success'=>true,'message'=>'Akun bank berhasil disimpan!']);
    }

    if ($action === 'change_password') {
        $oldPw  = $_POST['old_password'] ?? '';
        $newPw  = $_POST['new_password'] ?? '';
        $pin    = $_POST['pin'] ?? '';
        if (!verifyPassword($oldPw, $user['password'])) jsonResponse(['success'=>false,'message'=>'Password lama salah']);
        if (strlen($newPw) < 6) jsonResponse(['success'=>false,'message'=>'Password minimal 6 karakter']);
        if ($user['pin'] && !verifyPin($pin, $user['pin'])) jsonResponse(['success'=>false,'message'=>'PIN salah']);
        $hashed = hashPassword($newPw);
        $stmt=db()->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param('si',$hashed,$uid);$stmt->execute();$stmt->close();
        addNotification($uid,'Password Diubah','Password akun kamu berhasil diubah.','warning');
        jsonResponse(['success'=>true,'message'=>'Password berhasil diubah!']);
    }

    if ($action === 'change_theme') {
        $theme = sanitize($_POST['theme'] ?? 'dark');
        dbQuery("UPDATE users SET theme='".dbEscape($theme)."' WHERE id=$uid");
        jsonResponse(['success'=>true]);
    }

    if ($action === 'change_lang') {
        $lang = sanitize($_POST['lang'] ?? 'id');
        dbQuery("UPDATE users SET lang='".dbEscape($lang)."' WHERE id=$uid");
        jsonResponse(['success'=>true]);
    }

    if ($action === 'delete_account') {
        $pw  = $_POST['password'] ?? '';
        $pin = $_POST['pin'] ?? '';
        if (!verifyPassword($pw, $user['password'])) jsonResponse(['success'=>false,'message'=>'Password salah']);
        if ($user['pin'] && !verifyPin($pin, $user['pin'])) jsonResponse(['success'=>false,'message'=>'PIN salah']);
        dbQuery("UPDATE users SET status='blocked',username=CONCAT('deleted_',id,'_',username) WHERE id=$uid");
        session_destroy();
        jsonResponse(['success'=>true,'message'=>'Akun berhasil dihapus.','redirect'=>APP_URL]);
    }
}

$bank = dbQuery("SELECT * FROM user_banks WHERE user_id=$uid LIMIT 1");
$bankData = ($bank && $bank->num_rows > 0) ? $bank->fetch_assoc() : null;
$loginHistory = dbQuery("SELECT * FROM user_login_history WHERE user_id=$uid ORDER BY created_at DESC LIMIT 5");
$vip = getVipInfo((int)$user['vip_level']);
$nextVip = getVipInfo(min(3,(int)$user['vip_level']+1));
$vipProgress = $nextVip['level'] > $user['vip_level']
    ? min(100, round((float)$user['total_deposit'] / (float)$nextVip['min_deposit'] * 100))
    : 100;
$achievements = dbQuery("SELECT a.*,ua.earned_at FROM achievements a LEFT JOIN user_achievements ua ON a.id=ua.achievement_id AND ua.user_id=$uid WHERE a.status=1");
$waAdmin = getSetting('wa_admin','628000000000');
include __DIR__ . '/../includes/header.php';


?>
<div class="page-wrapper">
<div class="page-header"><h1 class="page-title">Profil</h1></div>

<!-- Profile Card -->
<div class="profile-hero">
  <div class="profile-avatar-wrap">
    <div class="profile-avatar"><?= strtoupper(substr($user['username'],0,1)) ?></div>
    <div class="profile-vip-badge" style="background:<?= vipBadgeColor((int)$user['vip_level']) ?>"><?= vipBadgeName((int)$user['vip_level']) ?></div>
  </div>
  <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
  <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>
  <div class="profile-id">ID: #<?= $uid ?> <button onclick="navigator.clipboard.writeText('<?= $uid ?>').then(()=>showToast('ID disalin','success'))"><i data-lucide="copy"></i></button></div>
  <div class="status-badge status-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></div>
</div>

<!-- VIP Progress -->
<?php if ((int)$user['vip_level'] < 3): ?>
<div class="section-card">
  <div class="vip-progress-header">
    <span>Progress VIP <?= $user['vip_level']+1 ?></span>
    <span><?= formatRupiah((float)$user['total_deposit']) ?> / <?= formatRupiah((float)$nextVip['min_deposit']) ?></span>
  </div>
  <div class="vip-progress-bar"><div class="vip-progress-fill" style="width:<?= $vipProgress ?>%"></div></div>
  <p class="vip-progress-note">Deposit <?= formatRupiah(max(0,(float)$nextVip['min_deposit']-(float)$user['total_deposit'])) ?> lagi untuk naik VIP <?= $user['vip_level']+1 ?></p>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="profile-stats-grid">
  <div class="psg-item"><div class="psg-val"><?= formatRupiah((float)$user['total_deposit']) ?></div><div class="psg-label">Total Deposit</div></div>
  <div class="psg-item"><div class="psg-val"><?= formatRupiah((float)$user['total_withdraw']) ?></div><div class="psg-label">Total Tarik</div></div>
  <div class="psg-item"><div class="psg-val"><?= formatRupiah((float)$user['total_mining']) ?></div><div class="psg-label">Total Mining</div></div>
  <div class="psg-item"><div class="psg-val"><?= date('d M Y',strtotime($user['created_at'])) ?></div><div class="psg-label">Bergabung</div></div>
</div>

<!-- Menu List -->
<div class="profile-menu-list">
  <div class="pm-group">
    <button class="pm-item" onclick="toggleSection('infoSection')"><i data-lucide="user"></i><span>Info Akun</span><i data-lucide="chevron-right"></i></button>
    <div class="pm-section" id="infoSection" style="display:none">
      <div class="form-group"><label class="form-label">Nama Lengkap</label><div class="input-wrapper"><i data-lucide="user" class="input-icon"></i><input type="text" id="infName" class="form-input" value="<?= htmlspecialchars($user['full_name']) ?>"></div></div>
      <div class="form-group"><label class="form-label">Email</label><div class="input-wrapper"><i data-lucide="mail" class="input-icon"></i><input type="email" id="infEmail" class="form-input" value="<?= htmlspecialchars($user['email']) ?>"></div></div>
      <div class="form-group"><label class="form-label">No. HP</label><div class="input-wrapper"><i data-lucide="phone" class="input-icon"></i><input type="tel" id="infPhone" class="form-input" value="<?= htmlspecialchars($user['phone']) ?>"></div></div>
      <div class="form-group"><label class="form-label">Password (Konfirmasi)</label><div class="input-wrapper"><i data-lucide="lock" class="input-icon"></i><input type="password" id="infPw" class="form-input" placeholder="Masukkan password"></div></div>
      <button class="btn btn-primary btn-full" onclick="saveInfo()"><i data-lucide="save"></i> Simpan</button>
    </div>
  </div>

  <div class="pm-group">
    <button class="pm-item" onclick="toggleSection('pinSection')"><i data-lucide="lock"></i><span><?= $user['pin']?'Ubah PIN':'Buat PIN' ?></span><i data-lucide="chevron-right"></i></button>
    <div class="pm-section" id="pinSection" style="display:none">
      <?php if ($user['pin']): ?>
      <div class="pin-input-group"><label>PIN Lama</label><?php for($i=0;$i<6;$i++): ?><input type="password" class="pin-digit old-pin" maxlength="1" inputmode="numeric"><?php endfor; ?></div>
      <?php endif; ?>
      <div class="pin-input-group mt-2"><label>PIN Baru</label><?php for($i=0;$i<6;$i++): ?><input type="password" class="pin-digit new-pin" maxlength="1" inputmode="numeric"><?php endfor; ?></div>
      <div class="pin-input-group mt-2"><label>Konfirmasi PIN</label><?php for($i=0;$i<6;$i++): ?><input type="password" class="pin-digit conf-pin" maxlength="1" inputmode="numeric"><?php endfor; ?></div>
      <?php if (!$user['pin']): ?>
      <div class="form-group mt-2"><div class="input-wrapper"><i data-lucide="lock" class="input-icon"></i><input type="password" id="setPinPw" class="form-input" placeholder="Konfirmasi password"></div></div>
      <?php endif; ?>
      <button class="btn btn-primary btn-full mt-2" onclick="savePin()"><?= $user['pin']?'Ubah PIN':'Buat PIN' ?></button>
      <?php if ($user['pin']): ?>
      <p class="text-center mt-2"><a href="javascript:showForgotPin()" class="text-gold text-sm">Lupa PIN?</a></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="pm-group" id="bankSection">
    <button class="pm-item" onclick="toggleSection('bankForm')"><i data-lucide="credit-card"></i><span>Akun Bank</span><i data-lucide="chevron-right"></i></button>
    <div class="pm-section" id="bankForm" style="display:none">
      <div class="form-group"><label class="form-label">Nama Bank</label><div class="input-wrapper"><i data-lucide="building" class="input-icon"></i><input type="text" id="bankName" class="form-input" value="<?= htmlspecialchars($bankData['bank_name']??'') ?>" placeholder="BCA, BRI, Mandiri, dll"></div></div>
      <div class="form-group"><label class="form-label">Nama Pemilik</label><div class="input-wrapper"><i data-lucide="user" class="input-icon"></i><input type="text" id="accName" class="form-input" value="<?= htmlspecialchars($bankData['account_name']??'') ?>"></div></div>
      <div class="form-group"><label class="form-label">Nomor Rekening</label><div class="input-wrapper"><i data-lucide="hash" class="input-icon"></i><input type="text" id="accNum" class="form-input" value="<?= htmlspecialchars($bankData['account_number']??'') ?>"></div></div>
      <?php if ($bankData): ?><p class="text-sm text-muted">Sisa ganti: <?= 3-(int)$user['bank_changes'] ?>x</p><?php endif; ?>
      <div class="pin-input-group mt-2"><label>PIN</label><?php for($i=0;$i<6;$i++): ?><input type="password" class="pin-digit bank-pin" maxlength="1" inputmode="numeric"><?php endfor; ?></div>
      <?php if ($bankData): ?><div class="form-group mt-2"><div class="input-wrapper"><i data-lucide="lock" class="input-icon"></i><input type="password" id="bankPw" class="form-input" placeholder="Konfirmasi password"></div></div><?php endif; ?>
      <button class="btn btn-primary btn-full mt-2" onclick="saveBank()"><i data-lucide="save"></i> Simpan Bank</button>
    </div>
  </div>

  <div class="pm-group">
    <button class="pm-item" onclick="toggleSection('pwSection')"><i data-lucide="key"></i><span>Ganti Password</span><i data-lucide="chevron-right"></i></button>
    <div class="pm-section" id="pwSection" style="display:none">
      <div class="form-group"><div class="input-wrapper"><i data-lucide="lock" class="input-icon"></i><input type="password" id="oldPw" class="form-input" placeholder="Password lama"><button type="button" class="input-toggle-pw" onclick="togglePw('oldPw',this)"><i data-lucide="eye"></i></button></div></div>
      <div class="form-group"><div class="input-wrapper"><i data-lucide="lock" class="input-icon"></i><input type="password" id="newPw" class="form-input" placeholder="Password baru"><button type="button" class="input-toggle-pw" onclick="togglePw('newPw',this)"><i data-lucide="eye"></i></button></div></div>
      <div class="pin-input-group mt-2"><label>PIN Konfirmasi</label><?php for($i=0;$i<6;$i++): ?><input type="password" class="pin-digit pw-pin" maxlength="1" inputmode="numeric"><?php endfor; ?></div>
      <button class="btn btn-primary btn-full mt-2" onclick="savePw()">Ganti Password</button>
      <p class="text-center mt-2 text-sm">Lupa password? <a href="https://wa.me/<?= $waAdmin ?>" target="_blank" class="text-gold">Hubungi Admin</a></p>
    </div>
  </div>

  <button class="pm-item" onclick="toggleSection('loginHistSection')"><i data-lucide="shield"></i><span>Riwayat Login</span><i data-lucide="chevron-right"></i></button>
  <div class="pm-section" id="loginHistSection" style="display:none">
    <?php if ($loginHistory && $loginHistory->num_rows > 0): while ($lh = $loginHistory->fetch_assoc()): ?>
    <div class="login-hist-item"><i data-lucide="monitor"></i><div class="lhi-info"><div class="lhi-device"><?= htmlspecialchars(substr($lh['device']??'Unknown',0,40)) ?></div><div class="lhi-date"><?= date('d M Y H:i',strtotime($lh['created_at'])) ?> &bull; <?= htmlspecialchars($lh['ip_address']??'-') ?></div></div></div>
    <?php endwhile; endif; ?>
  </div>

  <button class="pm-item" onclick="toggleSection('achSection')"><i data-lucide="award"></i><span>Badge Pencapaian</span><i data-lucide="chevron-right"></i></button>
  <div class="pm-section" id="achSection" style="display:none">
    <div class="achievements-grid">
      <?php if ($achievements && $achievements->num_rows > 0): while ($a = $achievements->fetch_assoc()): ?>
      <div class="ach-item <?= $a['earned_at']?'earned':'' ?>"><i data-lucide="<?= htmlspecialchars($a['icon']??'star') ?>"></i><span><?= htmlspecialchars($a['title']) ?></span></div>
      <?php endwhile; endif; ?>
    </div>
  </div>

  <div class="pm-group">
    <div class="pm-item-row"><i data-lucide="moon"></i><span>Mode Tampilan</span>
      <div class="toggle-switch"><button class="toggle-opt <?= $user['theme']==='dark'?'active':'' ?>" onclick="changeTheme('dark')">Gelap</button><button class="toggle-opt <?= $user['theme']==='light'?'active':'' ?>" onclick="changeTheme('light')">Terang</button></div>
    </div>
    <div class="pm-item-row"><i data-lucide="globe"></i><span>Bahasa</span>
      <div class="toggle-switch"><button class="toggle-opt <?= $user['lang']==='id'?'active':'' ?>" onclick="changeLang('id')">ID</button><button class="toggle-opt <?= $user['lang']==='en'?'active':'' ?>" onclick="changeLang('en')">EN</button></div>
    </div>
  </div>

  <a href="<?= APP_URL ?>/pages/apps.php" class="pm-item"><i data-lucide="smartphone"></i><span>Download Aplikasi</span><i data-lucide="chevron-right"></i></a>
  <a href="<?= APP_URL ?>/pages/terms.php" class="pm-item"><i data-lucide="file-text"></i><span>Kebijakan & Privasi</span><i data-lucide="chevron-right"></i></a>
  <a href="<?= APP_URL ?>/pages/faq.php" class="pm-item"><i data-lucide="help-circle"></i><span>FAQ</span><i data-lucide="chevron-right"></i></a>
  <a href="<?= APP_URL ?>/pages/contact.php" class="pm-item"><i data-lucide="headphones"></i><span>Kontak Admin</span><i data-lucide="chevron-right"></i></a>
  <a href="https://wa.me/<?= $waAdmin ?>" target="_blank" class="pm-item"><i data-lucide="message-circle"></i><span>Hubungi Admin</span><i data-lucide="chevron-right"></i></a>
  <button class="pm-item danger" onclick="showDeleteAccount()"><i data-lucide="trash-2"></i><span>Hapus Akun</span><i data-lucide="chevron-right"></i></button>
  <a href="<?= APP_URL ?>/auth/logout.php" class="pm-item danger"><i data-lucide="log-out"></i><span>Keluar</span><i data-lucide="chevron-right"></i></a>
</div>
</div>
<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
const CSRF='<?= csrfToken() ?>'; const APP_URL='<?= APP_URL ?>';
function toggleSection(id){const el=document.getElementById(id);el.style.display=el.style.display==='none'?'block':'none';}
function getPinDigits(cls){return [...document.querySelectorAll('.'+cls)].map(i=>i.value).join('');}
function saveInfo(){
  const fn=document.getElementById('infName').value,em=document.getElementById('infEmail').value,ph=document.getElementById('infPhone').value,pw=document.getElementById('infPw').value;
  postAction({action:'update_info',full_name:fn,email:em,phone:ph,password:pw},'Info berhasil diperbarui!');
}
function savePin(){
  const newPin=getPinDigits('new-pin'),conf=getPinDigits('conf-pin');
  const isNew=!<?= $user['pin']?'true':'false' ?>;
  const data={action:isNew?'set_pin':'change_pin',new_pin:newPin,confirm_pin:conf};
  if(!isNew)data.old_pin=getPinDigits('old-pin');
  else data.password=document.getElementById('setPinPw')?.value||'';
  postAction(data,'PIN berhasil!');
}
function saveBank(){
  const data={action:'set_bank',bank_name:document.getElementById('bankName').value,account_name:document.getElementById('accName').value,account_number:document.getElementById('accNum').value,pin:getPinDigits('bank-pin')};
  <?php if ($bankData): ?>data.password=document.getElementById('bankPw').value;<?php endif; ?>
  postAction(data,'Bank berhasil disimpan!');
}
function savePw(){
  postAction({action:'change_password',old_password:document.getElementById('oldPw').value,new_password:document.getElementById('newPw').value,pin:getPinDigits('pw-pin')},'Password berhasil diubah!');
}
function changeTheme(t){fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=change_theme&theme=${t}`}).then(()=>{document.documentElement.setAttribute('data-theme',t);document.body.className='theme-'+t;});}
function changeLang(l){fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=change_lang&lang=${l}`}).then(()=>location.reload());}
function showForgotPin(){showModal('info','Lupa PIN','Masukkan password kamu untuk reset PIN.',null,[{text:'Hubungi Admin',href:'https://wa.me/<?= $waAdmin ?>'}]);}
function showDeleteAccount(){showConfirm('Hapus Akun','Akun yang dihapus tidak bisa dipulihkan. Yakin?',()=>{
  const pw=prompt('Masukkan password kamu:');const pin=prompt('Masukkan PIN kamu:');
  if(!pw||!pin)return;
  postAction({action:'delete_account',password:pw,pin:pin},'Akun berhasil dihapus',()=>location.href=APP_URL);
});}
function postAction(data,msg,cb){
  data.csrf_token=CSRF;
  const body=new URLSearchParams(data).toString();
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body})
  .then(r=>r.json()).then(d=>{hideLoading();if(d.success){showModal('success','Berhasil!',d.message||msg,cb||null);}else{showModal('error','Gagal',d.message);}});
}
initPinInput();
</script>
