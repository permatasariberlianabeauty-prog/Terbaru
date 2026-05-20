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
