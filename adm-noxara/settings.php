<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle='Pengaturan Sistem'; $currentAdminPage='settings';

if($_SERVER['REQUEST_METHOD']==='POST'&&isAjax()){
    requireAdmin(); verifyCsrf();
    $action=$_POST['action']??'';
    if($action==='save_settings'){
        $keys=['site_name','site_tagline','register_bonus','min_deposit','max_deposit','deposit_bonus_percent','deposit_bonus_status','min_saldo_tersisa','mining_reset_time','mining_countdown_hours','wa_group_link','wa_admin','wa_official','email_admin','app_download_link','running_text','admin_status','maintenance_mode','maintenance_message','maintenance_estimate','cashify_license_key','cashify_qris_id','daily_withdraw_per_user','leaderboard_status','referral_status','spin_status','checkin_status','popup_welcome_status','popup_welcome_text'];
        foreach($keys as $k){ if(isset($_POST[$k])) setSetting($k, sanitize($_POST[$k])); }
        logAdminAction((int)$_SESSION['admin_id'],'save_settings');
        jsonResponse(['success'=>true,'message'=>'Pengaturan berhasil disimpan!']);
    }
    if($action==='change_admin_pw'){
        $oldPw=$_POST['old_password']??''; $newPw=$_POST['new_password']??'';
        $adminId=(int)$_SESSION['admin_id'];
        $r=dbQuery("SELECT password FROM admins WHERE id=$adminId LIMIT 1");
        $admin=$r->fetch_assoc();
        if(!verifyPassword($oldPw,$admin['password'])) jsonResponse(['success'=>false,'message'=>'Password lama salah']);
        $hashed=hashPassword($newPw);
        dbQuery("UPDATE admins SET password='$hashed' WHERE id=$adminId");
        jsonResponse(['success'=>true,'message'=>'Password admin berhasil diubah!']);
    }
}

include __DIR__.'/_header.php';
$s=function($k,$d=''){return htmlspecialchars(getSetting($k,$d));};
?>
<div class="admin-page-header"><h1>Pengaturan Sistem</h1></div>

<div class="settings-tabs">
  <button class="stab active" onclick="showTab('general')">Umum</button>
  <button class="stab" onclick="showTab('deposit')">Deposit</button>
  <button class="stab" onclick="showTab('mining')">Mining</button>
  <button class="stab" onclick="showTab('referral')">Referral</button>
  <button class="stab" onclick="showTab('contact')">Kontak</button>
  <button class="stab" onclick="showTab('api')">API</button>
  <button class="stab" onclick="showTab('security')">Keamanan</button>
</div>

<form id="settingsForm">
<!-- General Tab -->
<div class="stab-content active" id="tab-general">
  <div class="admin-card">
    <h3>Pengaturan Umum</h3>
    <div class="form-group"><label class="form-label">Nama Website</label><input type="text" name="site_name" class="form-input" value="<?=$s('site_name',APP_NAME)?>"></div>
    <div class="form-group"><label class="form-label">Tagline</label><input type="text" name="site_tagline" class="form-input" value="<?=$s('site_tagline',APP_TAGLINE)?>"></div>
    <div class="form-group"><label class="form-label">Running Text</label><input type="text" name="running_text" class="form-input" value="<?=$s('running_text')?>"></div>
    <div class="form-group"><label class="form-label">Bonus Registrasi (Rp)</label><input type="number" name="register_bonus" class="form-input" value="<?=$s('register_bonus','15000')?>"></div>
    <div class="form-group"><label class="form-label">Link Download Aplikasi</label><input type="text" name="app_download_link" class="form-input" value="<?=$s('app_download_link')?>"></div>
    <div class="form-group"><label class="form-label">Link Grup WhatsApp</label><input type="text" name="wa_group_link" class="form-input" value="<?=$s('wa_group_link')?>"></div>
    <div class="form-group"><label class="form-label">Status Admin Chat</label><select name="admin_status" class="form-input"><option value="online" <?=getSetting('admin_status')==='online'?'selected':''?>>Online</option><option value="offline" <?=getSetting('admin_status')==='offline'?'selected':''?>>Offline</option></select></div>
    <div class="form-group"><label class="form-label">Mode Maintenance</label><select name="maintenance_mode" class="form-input"><option value="0" <?=getSetting('maintenance_mode')==='0'?'selected':''?>>Nonaktif</option><option value="1" <?=getSetting('maintenance_mode')==='1'?'selected':''?>>Aktif</option></select></div>
    <div class="form-group"><label class="form-label">Pesan Maintenance</label><input type="text" name="maintenance_message" class="form-input" value="<?=$s('maintenance_message')?>"></div>
    <div class="form-group"><label class="form-label">Estimasi Maintenance</label><input type="text" name="maintenance_estimate" class="form-input" value="<?=$s('maintenance_estimate')?>"></div>
    <div class="form-group"><label class="form-label">Popup Welcome</label><select name="popup_welcome_status" class="form-input"><option value="1" <?=getSetting('popup_welcome_status')==='1'?'selected':''?>>Aktif</option><option value="0">Nonaktif</option></select></div>
    <div class="form-group"><label class="form-label">Teks Popup Welcome</label><input type="text" name="popup_welcome_text" class="form-input" value="<?=$s('popup_welcome_text')?>"></div>
    <div class="form-group"><label class="form-label">Leaderboard</label><select name="leaderboard_status" class="form-input"><option value="1" <?=getSetting('leaderboard_status')==='1'?'selected':''?>>Aktif</option><option value="0">Nonaktif</option></select></div>
  </div>
</div>

<!-- Deposit Tab -->
<div class="stab-content" id="tab-deposit">
  <div class="admin-card">
    <h3>Pengaturan Deposit</h3>
    <div class="form-group"><label class="form-label">Minimal Deposit (Rp)</label><input type="number" name="min_deposit" class="form-input" value="<?=$s('min_deposit','10000')?>"></div>
    <div class="form-group"><label class="form-label">Maksimal Deposit (Rp)</label><input type="number" name="max_deposit" class="form-input" value="<?=$s('max_deposit','100000000')?>"></div>
    <div class="form-group"><label class="form-label">Bonus Deposit (%)</label><input type="number" name="deposit_bonus_percent" class="form-input" value="<?=$s('deposit_bonus_percent','0')?>" step="0.1"></div>
    <div class="form-group"><label class="form-label">Status Bonus Deposit</label><select name="deposit_bonus_status" class="form-input"><option value="1" <?=getSetting('deposit_bonus_status')==='1'?'selected':''?>>Aktif</option><option value="0">Nonaktif</option></select></div>
    <div class="form-group"><label class="form-label">Saldo Minimum Tersisa (Rp)</label><input type="number" name="min_saldo_tersisa" class="form-input" value="<?=$s('min_saldo_tersisa','0')?>"></div>
    <div class="form-group"><label class="form-label">Batas Tarik Per User/Hari (Rp)</label><input type="number" name="daily_withdraw_per_user" class="form-input" value="<?=$s('daily_withdraw_per_user','5000000')?>"></div>
  </div>
</div>

<!-- Mining Tab -->
<div class="stab-content" id="tab-mining">
  <div class="admin-card">
    <h3>Pengaturan Mining</h3>
    <div class="form-group"><label class="form-label">Jam Reset Mining (WIB)</label><input type="time" name="mining_reset_time" class="form-input" value="<?=$s('mining_reset_time','00:01')?>"></div>
    <div class="form-group"><label class="form-label">Durasi Countdown Mining (Jam)</label><input type="number" name="mining_countdown_hours" class="form-input" value="<?=$s('mining_countdown_hours','2')?>"></div>
  </div>
</div>

<!-- Referral Tab -->
<div class="stab-content" id="tab-referral">
  <div class="admin-card">
    <h3>Pengaturan Referral</h3>
    <div class="form-group"><label class="form-label">Status Referral</label><select name="referral_status" class="form-input"><option value="1" <?=getSetting('referral_status')==='1'?'selected':''?>>Aktif</option><option value="0">Nonaktif</option></select></div>
    <p class="text-muted text-sm mt-2">Persentase rabat diatur di menu VIP Settings per level.</p>
  </div>
</div>

<!-- Contact Tab -->
<div class="stab-content" id="tab-contact">
  <div class="admin-card">
    <h3>Pengaturan Kontak</h3>
    <div class="form-group"><label class="form-label">WhatsApp Admin</label><input type="text" name="wa_admin" class="form-input" value="<?=$s('wa_admin')?>"><small class="text-muted">Format: 628xxx (tanpa +)</small></div>
    <div class="form-group"><label class="form-label">WhatsApp Official</label><input type="text" name="wa_official" class="form-input" value="<?=$s('wa_official')?>"></div>
    <div class="form-group"><label class="form-label">Email Admin</label><input type="email" name="email_admin" class="form-input" value="<?=$s('email_admin')?>"></div>
  </div>
</div>

<!-- API Tab -->
<div class="stab-content" id="tab-api">
  <div class="admin-card">
    <h3>API Cashify (QRIS)</h3>
    <div class="form-group"><label class="form-label">License Key</label><input type="text" name="cashify_license_key" class="form-input" value="<?=$s('cashify_license_key')?>"></div>
    <div class="form-group"><label class="form-label">ID QRIS</label><input type="text" name="cashify_qris_id" class="form-input" value="<?=$s('cashify_qris_id')?>"></div>
  </div>
</div>

<!-- Security Tab -->
<div class="stab-content" id="tab-security">
  <div class="admin-card">
    <h3>Ganti Password Admin</h3>
    <div class="form-group"><label class="form-label">Password Lama</label><input type="password" id="oldAdminPw" class="form-input"></div>
    <div class="form-group"><label class="form-label">Password Baru</label><input type="password" id="newAdminPw" class="form-input"></div>
    <button type="button" class="btn btn-warning btn-full" onclick="changeAdminPw()">Ganti Password Admin</button>
  </div>
</div>

<div class="sticky-save-btn">
  <button type="button" class="btn btn-primary btn-full btn-lg" onclick="saveSettings()"><i data-lucide="save"></i> Simpan Pengaturan</button>
</div>
</form>

<?php include __DIR__.'/_footer.php';?>
<script>
const CSRF='<?=csrfToken()?>';
function showTab(t){document.querySelectorAll('.stab,.stab-content').forEach(e=>{e.classList.remove('active');});document.querySelector('.stab-content#tab-'+t).classList.add('active');event.target.classList.add('active');}
function saveSettings(){
  const form=document.getElementById('settingsForm');
  const data=new FormData(form);
  data.append('action','save_settings');data.append('csrf_token',CSRF);
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:new URLSearchParams(data)})
  .then(r=>r.json()).then(d=>{hideLoading();if(d.success){showToast(d.message,'success');}else{showModal('error','Gagal',d.message);}});
}
function changeAdminPw(){
  const old=document.getElementById('oldAdminPw').value,nw=document.getElementById('newAdminPw').value;
  if(!old||!nw){showToast('Isi semua field','error');return;}
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=change_admin_pw&old_password=${encodeURIComponent(old)}&new_password=${encodeURIComponent(nw)}`})
  .then(r=>r.json()).then(d=>{if(d.success){showModal('success','Berhasil!',d.message);}else{showModal('error','Gagal',d.message);}});
}
</script>
