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
