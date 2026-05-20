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
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mobile.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/animations.css">

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

<script>
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

</script>
</body>
</html>
