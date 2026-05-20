<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser(); $uid=(int)$user['id'];
$pageTitle='Daily Reward'; $currentPage='daily';
$today=date('Y-m-d');

if($_SERVER['REQUEST_METHOD']==='POST'&&isAjax()){
    verifyCsrf(); $action=$_POST['action']??'';
    if($action==='checkin'){
        $existing=dbQuery("SELECT id FROM daily_checkins WHERE user_id=$uid AND checkin_date='$today'");
        if($existing&&$existing->num_rows>0) jsonResponse(['success'=>false,'message'=>'Sudah check-in hari ini!']);
        $streak=(int)$user['login_streak']+1;
        $lastCI=$user['last_checkin'];
        if($lastCI&&date('Y-m-d',strtotime('-1 day',strtotime($today)))!==$lastCI) $streak=1;
        $dayNum=($streak%7)?:7;
        $rewards=[1=>500,2=>1000,3=>1500,4=>2000,5=>2500,6=>3000,7=>10000];
        $reward=$rewards[$dayNum]??500;
        dbQuery("INSERT INTO daily_checkins (user_id,day_number,reward_amount,checkin_date) VALUES ($uid,$dayNum,$reward,'$today')");
        dbQuery("UPDATE users SET balance=balance+$reward,last_checkin='$today',login_streak=$streak WHERE id=$uid");
        addTransaction($uid,'daily',$reward,"Check-in hari ke-$dayNum");
        completeMissionProgress($uid,'login');
        jsonResponse(['success'=>true,'reward'=>$reward,'day'=>$dayNum,'streak'=>$streak]);
    }
    if($action==='spin'){
        $existing=dbQuery("SELECT id FROM spin_logs WHERE user_id=$uid AND spin_date='$today'");
        if($existing&&$existing->num_rows>0) jsonResponse(['success'=>false,'message'=>'Sudah spin hari ini!']);
        $prizes=[['balance',5000,'Rp 5.000'],['balance',2000,'Rp 2.000'],['balance',1000,'Rp 1.000'],['balance',500,'Rp 500'],['balance',10000,'Rp 10.000'],['balance',3000,'Rp 3.000'],['balance',750,'Rp 750'],['balance',1500,'Rp 1.500']];
        $idx=rand(0,count($prizes)-1); $prize=$prizes[$idx];
        dbQuery("INSERT INTO spin_logs (user_id,reward_type,reward_amount,reward_desc,spin_date) VALUES ($uid,'{$prize[0]}',{$prize[1]},'{$prize[2]}','$today')");
        dbQuery("UPDATE users SET balance=balance+{$prize[1]},last_spin='$today' WHERE id=$uid");
        addTransaction($uid,'daily',(float)$prize[1],'Hadiah spin: '.$prize[2]);
        jsonResponse(['success'=>true,'prize_index'=>$idx,'amount'=>$prize[1],'desc'=>$prize[2]]);
    }
}

$checkedIn=dbQuery("SELECT id FROM daily_checkins WHERE user_id=$uid AND checkin_date='$today'")->num_rows>0;
$spunToday=dbQuery("SELECT id FROM spin_logs WHERE user_id=$uid AND spin_date='$today'")->num_rows>0;
$streak=(int)$user['login_streak'];
$missions=dbQuery("SELECT dm.*,ump.progress,ump.completed FROM daily_missions dm LEFT JOIN user_mission_progress ump ON dm.id=ump.mission_id AND ump.user_id=$uid AND ump.mission_date='$today' WHERE dm.status=1");
include __DIR__.'/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header"><a href="<?=APP_URL?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title">Daily Reward</h1></div>

<div class="daily-streak-bar">
  <i data-lucide="flame"></i> Streak: <strong><?=$streak?> Hari</strong>
  <?php if($streak>=7):?><span class="streak-badge">🔥 7 Hari!</span><?php endif;?>
</div>

<!-- Check In -->
<div class="section-card">
  <h3 class="section-subtitle"><i data-lucide="calendar-check"></i> Check In Harian</h3>
  <div class="checkin-week">
    <?php for($d=1;$d<=7;$d++):$active=$streak>=$d;$today7=$d===($streak%7?:7);?>
    <div class="ci-day <?=$active?'done':'' ?> <?=$today7&&!$checkedIn?'current':''?>">
      <div class="ci-day-num">H<?=$d?></div>
      <div class="ci-reward"><?=formatRupiah([1=>500,2=>1000,3=>1500,4=>2000,5=>2500,6=>3000,7=>10000][$d]??500)?></div>
      <?php if($active):?><i data-lucide="check"></i><?php endif;?>
    </div>
    <?php endfor;?>
  </div>
  <?php if(!$checkedIn):?>
  <button class="btn btn-primary btn-full mt-3" onclick="doCheckin()"><i data-lucide="zap"></i> Check In Sekarang</button>
  <?php else:?>
  <div class="alert alert-success mt-2"><i data-lucide="check-circle"></i> Sudah check-in hari ini! Kembali besok.</div>
  <?php endif;?>
</div>

<!-- Spin -->
<div class="section-card">
  <h3 class="section-subtitle"><i data-lucide="circle"></i> Spin Keberuntungan</h3>
  <div class="spin-wheel-wrap">
    <canvas id="spinWheel" width="280" height="280"></canvas>
    <div class="spin-pointer"><i data-lucide="chevron-down"></i></div>
  </div>
  <?php if(!$spunToday):?>
  <button class="btn btn-gold btn-full mt-3" id="spinBtn" onclick="doSpin()"><i data-lucide="refresh-cw"></i> Spin Sekarang</button>
  <?php else:?>
  <div class="alert alert-success mt-2"><i data-lucide="check-circle"></i> Sudah spin hari ini!</div>
  <?php endif;?>
</div>

<!-- Missions -->
<div class="section-card">
  <h3 class="section-subtitle"><i data-lucide="target"></i> Misi Harian</h3>
  <?php if($missions&&$missions->num_rows>0):while($m=$missions->fetch_assoc()):
    $prog=(int)($m['progress']??0);$done=(bool)($m['completed']??false);$pct=min(100,round($prog/$m['target']*100));?>
  <div class="mission-item <?=$done?'done':''?>">
    <div class="mi-info">
      <div class="mi-title"><?=htmlspecialchars($m['title'])?></div>
      <div class="mi-desc"><?=htmlspecialchars($m['description']??'')?></div>
      <div class="mi-progress-bar"><div class="mi-progress-fill" style="width:<?=$pct?>%"></div></div>
      <div class="mi-progress-text"><?=$prog?>/<?=$m['target']?></div>
    </div>
    <div class="mi-reward"><?=formatRupiah((float)$m['reward_amount'])?><?=$done?'<i data-lucide="check-circle"></i>':''?></div>
  </div>
  <?php endwhile;endif;?>
</div>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php';?>
<?php include __DIR__.'/../includes/footer.php';?>
<script>
const CSRF='<?=csrfToken()?>';
function doCheckin(){
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=checkin`})
  .then(r=>r.json()).then(d=>{hideLoading();if(d.success){showModal('success','Check In Berhasil!','Kamu mendapat Rp '+d.reward.toLocaleString('id-ID')+' (Hari ke-'+d.day+') 🎉',()=>location.reload());}else{showModal('error','Oops',d.message);}});
}
function doSpin(){
  const btn=document.getElementById('spinBtn'); btn.disabled=true;
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=spin`})
  .then(r=>r.json()).then(d=>{hideLoading();if(d.success){animateSpin(d.prize_index,()=>{showModal('success','Selamat!','Kamu mendapat '+d.desc+' 🎊',()=>location.reload());});}else{showModal('error','Oops',d.message);btn.disabled=false;}});
}
document.addEventListener('DOMContentLoaded',()=>initSpinWheel());
</script>
