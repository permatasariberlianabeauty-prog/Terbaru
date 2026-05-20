// ============================================================
// NOXARA - animations.js  (ES5 compatible)
// ============================================================

// ============ PARTICLES BG ============
function initParticlesBg() {
  var c = document.getElementById('particles-bg');
  if (!c) return;
  c.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden';
  for (var i=0; i<12; i++) {
    var p = document.createElement('div');
    var size = 2 + Math.random()*3;
    p.style.cssText = 'position:absolute;width:'+size+'px;height:'+size+'px;background:rgba(255,215,0,'+(0.04+Math.random()*0.15)+');border-radius:50%;left:'+(Math.random()*100)+'%;top:'+(Math.random()*100)+'%;animation:particleFloat '+(8+Math.random()*12)+'s linear '+(Math.random()*6)+'s infinite';
    c.appendChild(p);
  }
  var style = document.createElement('style');
  style.textContent = '@keyframes particleFloat{0%{transform:translateY(0) translateX(0);opacity:0}10%{opacity:1}90%{opacity:1}100%{transform:translateY(-100vh) translateX(20px);opacity:0}}@keyframes spin{to{transform:rotate(360deg)}}';
  document.head.appendChild(style);
}

// ============ WELCOME LIGHTS ============
function initWelcomeLights() {
  var container = document.getElementById('welcomeLights');
  if (!container) return;
  var colors = ['#FFD700','#FF8C00','#FFA500','#FFEC8B','#FF6347','#FF69B4','#00CED1'];
  for (var i=0; i<16; i++) {
    var light = document.createElement('div');
    light.className = 'light-dot';
    var size = 4 + Math.random()*8;
    light.style.cssText = 'width:'+size+'px;height:'+size+'px;background:'+colors[i%colors.length]+';position:absolute;border-radius:50%;left:'+(Math.random()*100)+'%;top:'+(Math.random()*100)+'%;animation:lightFlash '+(0.6+Math.random()*0.8)+'s ease-in-out '+(Math.random()*1.5)+'s infinite';
    container.appendChild(light);
  }
  // Confetti
  for (var j=0; j<25; j++) {
    (function(idx){
      var conf = document.createElement('div');
      conf.style.cssText = 'position:fixed;width:8px;height:8px;background:'+colors[idx%colors.length]+';left:'+(Math.random()*100)+'vw;top:-10px;border-radius:2px;z-index:9999;animation:confettiFall '+(1.2+Math.random()*1.5)+'s ease-in '+(Math.random()*2)+'s forwards';
      document.body.appendChild(conf);
      setTimeout(function(){ if(conf.parentNode) conf.parentNode.removeChild(conf); }, 4000);
    })(j);
  }
  var style = document.createElement('style');
  style.textContent = '@keyframes lightFlash{0%,100%{opacity:0.3;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}@keyframes confettiFall{0%{transform:translateY(0) rotate(0);opacity:1}100%{transform:translateY(100vh) rotate(360deg);opacity:0}}';
  document.head.appendChild(style);
}

// ============ SPIN WHEEL ============
var _spinPrizes = ['Rp 5.000','Rp 2.000','Rp 1.000','Rp 500','Rp 10.000','Rp 3.000','Rp 750','Rp 1.500'];
var _spinColors = ['#FFD700','#FF8C00','#FFA500','#FFEC8B','#FF6347','#FFD700','#FF8C00','#FFA500'];
var _currentAngle = 0;

function initSpinWheel() {
  var canvas = document.getElementById('spinWheel');
  if (!canvas) return;
  _drawWheel(canvas.getContext('2d'), canvas.width, canvas.height, _currentAngle);
}

function _drawWheel(ctx, w, h, rotation) {
  var cx=w/2, cy=h/2, r=cx-10;
  var slices = _spinPrizes.length;
  var arc = 2*Math.PI/slices;
  ctx.clearRect(0,0,w,h);
  for (var i=0; i<slices; i++) {
    var start = rotation + i*arc;
    ctx.beginPath(); ctx.moveTo(cx,cy); ctx.arc(cx,cy,r,start,start+arc); ctx.closePath();
    ctx.fillStyle = _spinColors[i]; ctx.fill();
    ctx.strokeStyle = 'rgba(255,255,255,0.3)'; ctx.lineWidth = 2; ctx.stroke();
    ctx.save(); ctx.translate(cx,cy); ctx.rotate(start+arc/2);
    ctx.textAlign = 'right'; ctx.fillStyle = '#000'; ctx.font = 'bold 11px Inter';
    ctx.fillText(_spinPrizes[i], r-10, 5); ctx.restore();
  }
  ctx.beginPath(); ctx.arc(cx,cy,18,0,2*Math.PI);
  ctx.fillStyle = '#0A0E1A'; ctx.fill();
  ctx.strokeStyle = '#FFD700'; ctx.lineWidth = 3; ctx.stroke();
  ctx.fillStyle = '#FFD700'; ctx.font = 'bold 10px Inter'; ctx.textAlign = 'center';
  ctx.fillText('SPIN', cx, cy+4);
}

function animateSpin(prizeIndex, callback) {
  var canvas = document.getElementById('spinWheel');
  if (!canvas) { if(callback) callback(); return; }
  var ctx = canvas.getContext('2d');
  var slices = _spinPrizes.length;
  var arc = 2*Math.PI/slices;
  var extraSpins = 5 + Math.random()*3;
  var targetAngle = -(prizeIndex*arc + arc/2) + Math.PI/2;
  var totalRotation = extraSpins*2*Math.PI + ((targetAngle - _currentAngle) % (2*Math.PI));
  var duration = 4000;
  var start = performance.now();
  var startAngle = _currentAngle;
  function ease(t){ return t<0.5 ? 2*t*t : 1-Math.pow(-2*t+2,2)/2; }
  function frame(now) {
    var elapsed = now - start;
    var progress = Math.min(elapsed/duration, 1);
    _currentAngle = startAngle + totalRotation*ease(progress);
    _drawWheel(ctx, canvas.width, canvas.height, _currentAngle);
    if (progress < 1) { requestAnimationFrame(frame); }
    else { _currentAngle = _currentAngle % (2*Math.PI); if(callback) callback(); }
  }
  requestAnimationFrame(frame);
}

// ============ MINING CHART ============
function initMiningChart() {
  var container = document.getElementById('miningChart');
  if (!container) return;
  var data = JSON.parse(container.dataset.chart || '[]');
  if (!data.length) return;
  var vals = data.map(function(d){ return d.total; });
  var maxVal = Math.max.apply(null, vals.concat([1]));
  container.innerHTML = '';
  data.forEach(function(d){
    var wrap = document.createElement('div');
    wrap.className = 'chart-bar-wrap';
    var pct = Math.max(4, (d.total/maxVal)*140);
    wrap.innerHTML = '<div class="chart-bar-val">'+(d.total>0?'Rp '+Math.floor(d.total/1000)+'rb':'')+'</div><div class="chart-bar" style="height:'+pct+'px"></div><div class="chart-bar-label">'+d.date+'</div>';
    container.appendChild(wrap);
  });
}

// ============ MINING CALENDAR ============
function initMiningCalendar() {
  var container = document.getElementById('miningCalendar');
  if (!container) return;
  var calendar = JSON.parse(container.dataset.calendar || '{}');
  var month = container.dataset.month;
  if (!month) return;
  var parts = month.split('-').map(Number);
  var y = parts[0], m = parts[1];
  var daysInMonth = new Date(y, m, 0).getDate();
  var firstDay = new Date(y, m-1, 1).getDay();
  var today = new Date().toISOString().split('T')[0];
  var headers = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
  var html = headers.map(function(h){ return '<div class="cal-day-header">'+h+'</div>'; }).join('');
  for (var i=0; i<firstDay; i++) html += '<div class="cal-day empty"></div>';
  for (var d=1; d<=daysInMonth; d++) {
    var date = y+'-'+String(m).padStart(2,'0')+'-'+String(d).padStart(2,'0');
    var status = calendar[date];
    var isToday = date === today;
    var cls = status==='success'?'success':status==='skipped'?'skipped':date>today?'future':'';
    html += '<div class="cal-day '+cls+(isToday?' today':'')+'">'+d+'</div>';
  }
  container.innerHTML = html;
}

// ============ FADE IN OBSERVER ============
document.addEventListener('DOMContentLoaded', function(){
  if (typeof IntersectionObserver !== 'undefined') {
    var observer = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting) {
          e.target.style.opacity = '1';
          e.target.style.transform = 'translateY(0)';
          observer.unobserve(e.target);
        }
      });
    }, {threshold: 0.1});
    document.querySelectorAll('.animate-fadeInUp').forEach(function(el){
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      observer.observe(el);
    });
  }
});
