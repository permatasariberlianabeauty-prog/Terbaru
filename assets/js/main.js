// ============================================================
// NOXARA - main.js  (ES5 compatible, no arrow functions at top level)
// ============================================================

// ============ TOAST ============
function showToast(msg, type, duration) {
  type = type || 'info';
  duration = duration || 3000;
  var c = document.getElementById('toastContainer');
  if (!c) return;
  var icons = {success:'check-circle',error:'x-circle',warning:'alert-triangle',info:'info'};
  var t = document.createElement('div');
  t.className = 'toast toast-' + type;
  t.innerHTML = '<span>' + msg + '</span>';
  c.appendChild(t);
  setTimeout(function(){ if(t.parentNode) t.parentNode.removeChild(t); }, duration);
}

// ============ MODAL ============
function showModal(type, title, msg, onClose, extraBtns) {
  var overlay = document.getElementById('modalOverlay');
  var box = document.getElementById('modalBox');
  if (!overlay || !box) { alert(msg); if(onClose) onClose(); return; }
  var icons = {success:'check-circle',error:'x-circle',warning:'alert-triangle',info:'info'};
  document.getElementById('modalIcon').className = 'modal-icon ' + type;
  document.getElementById('modalIcon').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>';
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMsg').textContent = msg;
  var actions = document.getElementById('modalActions');
  actions.innerHTML = '';
  var okBtn = document.createElement('button');
  okBtn.className = 'btn btn-primary';
  okBtn.textContent = 'OK';
  okBtn.onclick = function(){ overlay.style.display='none'; if(onClose) onClose(); };
  actions.appendChild(okBtn);
  overlay.style.display = 'flex';
  overlay.onclick = function(e){ if(e.target===overlay){overlay.style.display='none';if(onClose)onClose();} };
  if (typeof lucide !== 'undefined') lucide.createIcons({nodes:[box]});
}

function showConfirm(title, msg, onConfirm) {
  var overlay = document.getElementById('modalOverlay');
  var box = document.getElementById('modalBox');
  if (!overlay || !box) { if(confirm(msg)) onConfirm(); return; }
  document.getElementById('modalIcon').className = 'modal-icon warning';
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMsg').textContent = msg;
  var actions = document.getElementById('modalActions');
  actions.innerHTML = '';
  var cancelBtn = document.createElement('button');
  cancelBtn.className = 'btn btn-outline';
  cancelBtn.textContent = 'Batal';
  cancelBtn.onclick = function(){ overlay.style.display='none'; };
  var confirmBtn = document.createElement('button');
  confirmBtn.className = 'btn btn-primary';
  confirmBtn.textContent = 'Ya, Lanjutkan';
  confirmBtn.onclick = function(){ overlay.style.display='none'; onConfirm(); };
  actions.appendChild(cancelBtn);
  actions.appendChild(confirmBtn);
  overlay.style.display = 'flex';
}

var _loadingEl = null;
function showLoading() {
  if (_loadingEl) return;
  _loadingEl = document.createElement('div');
  _loadingEl.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center';
  _loadingEl.innerHTML = '<div style="width:48px;height:48px;border:4px solid #1E2A45;border-top-color:#FFD700;border-radius:50%;animation:spin 0.8s linear infinite"></div>';
  document.body.appendChild(_loadingEl);
}
function hideLoading() {
  if (_loadingEl) { _loadingEl.remove(); _loadingEl = null; }
}

// ============ PASSWORD TOGGLE ============
function togglePw(id, btn) {
  var input = document.getElementById(id);
  if (!input) return;
  var isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  btn.innerHTML = isHidden
    ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
    : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}

// ============ CAPTCHA ============
function initCaptcha(suffix) { refreshCaptcha(suffix || ''); }
function refreshCaptcha(suffix) {
  suffix = suffix || '';
  var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  var code = '';
  for (var i=0; i<5; i++) code += chars[Math.floor(Math.random()*chars.length)];
  var display = document.getElementById('captchaDisplay'+suffix);
  var key = document.getElementById('captchaKey'+suffix);
  if (display) {
    display.innerHTML = '';
    for (var j=0; j<code.length; j++) {
      var span = document.createElement('span');
      span.textContent = code[j];
      span.style.cssText = 'color:hsl('+(45+j*20)+',90%,65%);transform:rotate('+((Math.random()-0.5)*15)+'deg);display:inline-block;margin:0 1px;font-style:italic';
      display.appendChild(span);
    }
  }
  if (key) key.value = btoa(code);
}

// ============ COUNTER ANIMATION ============
function animateCounters() {
  var els = document.querySelectorAll('[data-target]');
  for (var i=0; i<els.length; i++) {
    (function(el){
      var target = parseInt(el.dataset.target);
      var prefix = el.dataset.prefix || '';
      var suffix = el.dataset.suffix || '';
      var current = 0, step = target / 60;
      var timer = setInterval(function(){
        current += step;
        if (current >= target) { current = target; clearInterval(timer); }
        el.textContent = prefix + Math.floor(current).toLocaleString('id-ID') + suffix;
      }, 25);
    })(els[i]);
  }
}

// ============ BANNER SLIDER ============
function initBannerSlider() {
  var slider = document.getElementById('bannerSlider');
  if (!slider) return;
  var slides = slider.querySelectorAll('.banner-slide');
  if (slides.length <= 1) {
    // Single slide - just show dots
    return;
  }
  var dotsContainer = document.getElementById('bannerDots');
  var current = 0;
  var startX = 0;

  if (dotsContainer) {
    dotsContainer.innerHTML = '';
    for (var i=0; i<slides.length; i++) {
      (function(idx){
        var dot = document.createElement('button');
        dot.className = 'banner-dot' + (idx===0 ? ' active' : '');
        dot.onclick = function(){ goToSlide(idx); };
        dotsContainer.appendChild(dot);
      })(i);
    }
  }

  function goToSlide(idx) {
    slides[current].classList.remove('active');
    if (dotsContainer) {
      var dots = dotsContainer.querySelectorAll('.banner-dot');
      if (dots[current]) dots[current].classList.remove('active');
    }
    current = (idx + slides.length) % slides.length;
    slides[current].classList.add('active');
    if (dotsContainer) {
      var dots2 = dotsContainer.querySelectorAll('.banner-dot');
      if (dots2[current]) dots2[current].classList.add('active');
    }
  }

  var auto = setInterval(function(){ goToSlide(current+1); }, 4000);

  slider.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; }, {passive:true});
  slider.addEventListener('touchend', function(e){
    var diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) goToSlide(diff > 0 ? current+1 : current-1);
  });
}

// ============ PIN INPUT ============
function initPinInput() {
  var inputs = document.querySelectorAll('.pin-digit');
  for (var i=0; i<inputs.length; i++) {
    (function(input, idx, all){
      input.addEventListener('input', function(){
        if (input.value.length >= 1) {
          input.value = input.value.slice(-1);
          if (all[idx+1]) all[idx+1].focus();
        }
      });
      input.addEventListener('keydown', function(e){
        if (e.key === 'Backspace' && !input.value && idx > 0) all[idx-1].focus();
      });
    })(inputs[i], i, inputs);
  }
}
function getPinValue(cls) {
  cls = cls || 'pin-digit';
  var inputs = document.querySelectorAll('.'+cls);
  var val = '';
  for (var i=0; i<inputs.length; i++) val += inputs[i].value;
  return val;
}

// ============ SCROLL TO TOP ============
window.addEventListener('scroll', function(){
  var fab = document.getElementById('fabTop');
  if (fab) {
    if (window.scrollY > 300) fab.classList.add('visible');
    else fab.classList.remove('visible');
  }
});

// ============ LIVE CHAT ============
document.addEventListener('DOMContentLoaded', function(){
  var fabChat = document.getElementById('fabChat');
  var chatPanel = document.getElementById('chatPanel');
  var chatClose = document.getElementById('chatClose');
  var fabTop = document.getElementById('fabTop');

  if (fabChat && chatPanel) {
    fabChat.addEventListener('click', function(){
      chatPanel.classList.toggle('open');
      if (chatPanel.classList.contains('open')) loadChatMessages();
    });
  }
  if (chatClose && chatPanel) {
    chatClose.addEventListener('click', function(){ chatPanel.classList.remove('open'); });
  }
  if (fabTop) {
    fabTop.addEventListener('click', function(){ window.scrollTo({top:0,behavior:'smooth'}); });
  }

  // Quick replies
  var qrBtns = document.querySelectorAll('.quick-reply-btn');
  for (var i=0; i<qrBtns.length; i++) {
    (function(btn){
      btn.addEventListener('click', function(){
        var answer = btn.dataset.answer;
        var container = document.getElementById('chatMsgs');
        if (container) {
          var div = document.createElement('div');
          div.className = 'chat-msg-item admin';
          div.innerHTML = '<div class="chat-bubble">'+answer+'</div>';
          container.appendChild(div);
          container.scrollTop = container.scrollHeight;
          var qr = document.getElementById('quickReplies');
          if (qr) qr.style.display='none';
        }
      });
    })(qrBtns[i]);
  }

  // Chat send
  var chatSend = document.getElementById('chatSend');
  var chatInput = document.getElementById('chatInput');
  if (chatSend) chatSend.addEventListener('click', sendChatMessage);
  if (chatInput) chatInput.addEventListener('keydown', function(e){ if(e.key==='Enter') sendChatMessage(); });
});

function loadChatMessages() {
  if (!window.NOXARA || !window.NOXARA.userId) return;
  fetch(window.NOXARA.appUrl+'/api/chat.php?action=load', {headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(function(r){ return r.json(); })
  .then(function(d){
    var container = document.getElementById('chatMsgs');
    if (!container || !d.messages) return;
    var qr = document.getElementById('quickReplies');
    if (d.messages.length > 0 && qr) qr.style.display='none';
    container.innerHTML = '';
    d.messages.forEach(function(m){
      var div = document.createElement('div');
      div.className = 'chat-msg-item ' + m.sender;
      div.innerHTML = '<div class="chat-bubble">'+m.message+'</div><div class="chat-msg-time">'+m.created_at+'</div>';
      container.appendChild(div);
    });
    container.scrollTop = container.scrollHeight;
  });
}

function sendChatMessage() {
  var input = document.getElementById('chatInput');
  var msg = input ? input.value.trim() : '';
  if (!msg) return;
  var csrf = '';
  var csrfEl = document.querySelector('input[name="csrf_token"]');
  if (csrfEl) csrf = csrfEl.value;
  fetch((window.NOXARA ? window.NOXARA.appUrl : '') + '/api/chat.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:'action=send&message='+encodeURIComponent(msg)+'&csrf_token='+csrf
  }).then(function(){ input.value=''; loadChatMessages(); });
}

// ============ MINING COUNTDOWN ============
function initMiningCountdown() {
  var el = document.querySelector('.msb-countdown[data-seconds]');
  if (!el) return;
  var seconds = parseInt(el.dataset.seconds);
  if (seconds <= 0) return;
  var iv = setInterval(function(){
    seconds--;
    if (seconds <= 0) { clearInterval(iv); el.textContent='Selesai!'; return; }
    var h=Math.floor(seconds/3600), m=Math.floor((seconds%3600)/60), s=seconds%60;
    el.textContent = String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
  }, 1000);
}

function initMiningCountdowns() {
  var els = document.querySelectorAll('.mc-countdown-time[data-finish]');
  for (var i=0; i<els.length; i++) {
    (function(el){
      var finish = parseInt(el.dataset.finish) * 1000;
      var ring = el.closest ? el.closest('.mc-countdown-ring') : null;
      if (ring) ring = ring.querySelector('.countdown-ring-fill');
      var total = ring ? parseInt(ring.dataset.total || 7200) * 1000 : 7200000;
      var iv = setInterval(function(){
        var remaining = Math.max(0, finish - Date.now());
        if (remaining <= 0) { clearInterval(iv); el.textContent='00:00:00'; return; }
        var h=Math.floor(remaining/3600000), m=Math.floor(remaining%3600000/60000), s=Math.floor(remaining%60000/1000);
        el.textContent = String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
        if (ring) { var pct=remaining/total; ring.style.strokeDashoffset=339.3*(1-pct); }
      }, 1000);
    })(els[i]);
  }
}

function showProfitAnimation(profit) {
  showToast('+Rp '+Number(profit).toLocaleString('id-ID')+' masuk ke saldo!', 'success', 4000);
}

// ============ INIT ============
document.addEventListener('DOMContentLoaded', function(){
  initPinInput();
  initMiningCountdown();
  // Re-init lucide after everything loads
  setTimeout(function(){
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }, 200);
});
