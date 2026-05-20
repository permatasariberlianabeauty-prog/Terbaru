<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Beranda';
$currentPage = 'home';

// Welcome popup
$showWelcome = isset($_GET['welcome']) || !isset($_SESSION['welcomed']);
if (!isset($_SESSION['welcomed'])) $_SESSION['welcomed'] = true;

// Stats
$vip = getVipInfo((int)$user['vip_level']);
$todayEarn  = 0; $monthEarn = 0;
$r1 = dbQuery("SELECT COALESCE(SUM(amount),0) as t FROM transactions WHERE user_id=$uid AND type IN('mining','referral','bonus','daily') AND DATE(created_at)=CURDATE()");
if ($r1) $todayEarn = (float)$r1->fetch_assoc()['t'];
$r2 = dbQuery("SELECT COALESCE(SUM(amount),0) as t FROM transactions WHERE user_id=$uid AND type IN('mining','referral','bonus','daily') AND DATE_FORMAT(created_at,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')");
if ($r2) $monthEarn = (float)$r2->fetch_assoc()['t'];
$totalRef = dbQuery("SELECT COUNT(*) as c FROM users WHERE referred_by=$uid");
$totalRefCount = $totalRef ? (int)$totalRef->fetch_assoc()['c'] : 0;

// Mining status
$miningPkgs = dbQuery("SELECT id,mining_today,last_mining,profit_per_day FROM user_packages WHERE user_id=$uid AND status='active'");
$hasMining = false; $miningCountdown = 0;
if ($miningPkgs && $miningPkgs->num_rows > 0) {
    while ($mp = $miningPkgs->fetch_assoc()) {
        if ($mp['mining_today']) { $hasMining = true; $miningCountdown = max(0, strtotime($mp['last_mining']) + (MINING_COUNTDOWN_HOURS*3600) - time()); break; }
    }
}

// Banners
$banners = dbQuery("SELECT * FROM banners WHERE status=1 ORDER BY sort_order ASC LIMIT 3");

// Leaderboard
$leaders = dbQuery("SELECT username,total_mining FROM users WHERE status='active' ORDER BY total_mining DESC LIMIT 5");

// Recent transactions
$recentTx = dbQuery("SELECT * FROM transactions WHERE user_id=$uid ORDER BY created_at DESC LIMIT 5");

// Notifications
$notifs = dbQuery("SELECT * FROM notifications WHERE (user_id=$uid OR is_broadcast=1) ORDER BY created_at DESC LIMIT 20");

include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrapper">

<!-- HEADER -->
<div class="dash-header">
  <div class="dash-user-info">
    <div class="dash-avatar-wrap">
      <div class="dash-avatar" style="background: linear-gradient(135deg,#FFD700,#FF8C00)">
        <?= strtoupper(substr($user['username'],0,1)) ?>
      </div>
      <div class="vip-badge-small" style="background:<?= vipBadgeColor((int)$user['vip_level']) ?>">
        V<?= $user['vip_level'] ?>
      </div>
    </div>
    <div class="dash-greeting">
      <span class="greeting-text"><?= getGreeting() ?>,</span>
      <span class="greeting-name"><?= htmlspecialchars($user['username']) ?></span>
    </div>
  </div>
  <div class="dash-actions">
    <button class="icon-btn notif-btn" id="notifBtn">
      <i data-lucide="bell"></i>
      <?php if ($unreadNotif > 0): ?><span class="badge-dot"><?= $unreadNotif ?></span><?php endif; ?>
    </button>
  </div>
</div>

<!-- NOTIFICATION PANEL -->
<div class="notif-panel" id="notifPanel" style="display:none">
  <div class="notif-panel-header">
    <h3>Notifikasi</h3>
    <button onclick="markAllRead()"><i data-lucide="check-check"></i></button>
    <button onclick="closeNotif()"><i data-lucide="x"></i></button>
  </div>
  <div class="notif-list">
    <?php if ($notifs && $notifs->num_rows > 0): while ($n = $notifs->fetch_assoc()): ?>
    <div class="notif-item <?= !$n['is_read']?'unread':'' ?>" data-id="<?= $n['id'] ?>">
      <div class="notif-icon notif-<?= $n['type'] ?>"><i data-lucide="<?= $n['type']==='success'?'check-circle':($n['type']==='warning'?'alert-triangle':'info') ?>"></i></div>
      <div class="notif-content">
        <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
        <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
        <div class="notif-time"><?= date('d M H:i', strtotime($n['created_at'])) ?></div>
      </div>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state"><i data-lucide="bell-off"></i><p>Belum ada notifikasi</p></div>
    <?php endif; ?>
  </div>
</div>

<!-- ID CARD -->
<div class="id-card animate-fadeInUp">
  <div class="id-card-inner">
    <div class="id-card-top">
      <div class="id-card-logo"><i data-lucide="zap"></i> <?= APP_NAME ?></div>
      <div class="id-card-vip" style="background:<?= vipBadgeColor((int)$user['vip_level']) ?>"><?= vipBadgeName((int)$user['vip_level']) ?></div>
    </div>
    <div class="id-card-name"><?= htmlspecialchars($user['full_name']) ?></div>
    <div class="id-card-username">@<?= htmlspecialchars($user['username']) ?></div>
    <div class="id-card-balance-label">Total Saldo</div>
    <div class="id-card-balance"><?= formatRupiah((float)$user['balance']) ?></div>
    <div class="id-card-actions">
      <a href="<?= APP_URL ?>/pages/deposit.php" class="id-card-btn btn-deposit">
        <i data-lucide="plus-circle"></i> Isi Ulang
      </a>
      <a href="<?= APP_URL ?>/pages/withdraw.php" class="id-card-btn btn-withdraw">
        <i data-lucide="arrow-down-circle"></i> Tarik Dana
      </a>
    </div>
  </div>
</div>

<!-- STATS MINI -->
<div class="stats-mini">
  <div class="stat-mini-item">
    <div class="stat-mini-val"><?= formatRupiah($todayEarn) ?></div>
    <div class="stat-mini-label">Hari Ini</div>
  </div>
  <div class="stat-mini-item">
    <div class="stat-mini-val"><?= formatRupiah($monthEarn) ?></div>
    <div class="stat-mini-label">Bulan Ini</div>
  </div>
  <div class="stat-mini-item">
    <div class="stat-mini-val"><?= $totalRefCount ?></div>
    <div class="stat-mini-label">Referral</div>
  </div>
  <div class="stat-mini-item">
    <div class="stat-mini-val"><?= formatRupiah((float)$user['total_deposit']) ?></div>
    <div class="stat-mini-label">Total Deposit</div>
  </div>
  <div class="stat-mini-item">
    <div class="stat-mini-val"><?= formatRupiah((float)$user['total_withdraw']) ?></div>
    <div class="stat-mini-label">Total Tarik</div>
  </div>
</div>

<!-- MINING STATUS -->
<div class="mining-status-bar <?= $hasMining?'active':'' ?>">
  <div class="msb-left">
    <i data-lucide="cpu"></i>
    <span><?= $hasMining ? 'Mining Berjalan' : 'Belum Mining Hari Ini' ?></span>
  </div>
  <?php if ($hasMining && $miningCountdown > 0): ?>
  <div class="msb-countdown" data-seconds="<?= $miningCountdown ?>">
    <?= gmdate('H:i:s', $miningCountdown) ?>
  </div>
  <?php elseif (!$hasMining): ?>
  <a href="<?= APP_URL ?>/pages/mining.php" class="msb-btn">Mining Sekarang</a>
  <?php endif; ?>
</div>

<!-- 8 MENU GRID -->
<div class="menu-grid">
  <a href="<?= APP_URL ?>/pages/mining.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="cpu"></i></div>
    <span>Mining</span>
  </a>
  <a href="<?= APP_URL ?>/pages/vip.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="crown"></i></div>
    <span>VIP</span>
  </a>
  <a href="<?= APP_URL ?>/pages/voucher.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="ticket"></i></div>
    <span>Voucher</span>
  </a>
  <a href="<?= APP_URL ?>/pages/daily.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="calendar-check"></i></div>
    <span>Daily</span>
  </a>
  <a href="<?= APP_URL ?>/pages/apps.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="smartphone"></i></div>
    <span>Aplikasi</span>
  </a>
  <a href="<?= APP_URL ?>/pages/history.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="clock"></i></div>
    <span>Riwayat</span>
  </a>
  <a href="<?= APP_URL ?>/pages/info.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="info"></i></div>
    <span>Informasi</span>
  </a>
  <a href="<?= APP_URL ?>/pages/contact.php" class="menu-item">
    <div class="menu-icon"><i data-lucide="headphones"></i></div>
    <span>Kontak Admin</span>
  </a>
</div>

<!-- BANNER PROMO -->
<div class="banner-slider" id="bannerSlider">
  <?php if ($banners && $banners->num_rows > 0): $bi=0; while ($b = $banners->fetch_assoc()): ?>
  <div class="banner-slide <?= $bi===0?'active':'' ?>" style="background:linear-gradient(135deg,<?= htmlspecialchars($b['color_from']) ?>,<?= htmlspecialchars($b['color_to']) ?>)">
    <div class="banner-content">
      <h3><?= htmlspecialchars($b['title']) ?></h3>
      <p><?= htmlspecialchars($b['subtitle']) ?></p>
    </div>
  </div>
  <?php $bi++; endwhile; else: ?>
  <div class="banner-slide active" style="background:linear-gradient(135deg,#FFD700,#FF8C00)">
    <div class="banner-content"><h3>Mining Rupiah Setiap Hari</h3><p>Klik tombol mining & raih profit otomatis</p></div>
  </div>
  <?php endif; ?>
  <div class="banner-dots" id="bannerDots"></div>
</div>

<!-- LEADERBOARD -->
<?php if (getSetting('leaderboard_status') === '1'): ?>
<div class="section-card">
  <div class="section-header">
    <h3><i data-lucide="trophy"></i> Top Mining</h3>
  </div>
  <div class="leaderboard-list">
    <?php if ($leaders && $leaders->num_rows > 0): $li=1; while ($l = $leaders->fetch_assoc()): ?>
    <div class="leader-item">
      <div class="leader-rank rank-<?= $li ?>"><?= $li ?></div>
      <div class="leader-name"><?= htmlspecialchars(substr($l['username'],0,3)).'***' ?></div>
      <div class="leader-amount"><?= formatRupiah((float)$l['total_mining']) ?></div>
    </div>
    <?php $li++; endwhile; endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- RECENT TX -->
<div class="section-card">
  <div class="section-header">
    <h3><i data-lucide="list"></i> Transaksi Terbaru</h3>
    <a href="<?= APP_URL ?>/pages/history.php" class="see-all">Lihat Semua</a>
  </div>
  <div class="tx-list">
    <?php if ($recentTx && $recentTx->num_rows > 0): while ($tx = $recentTx->fetch_assoc()): ?>
    <div class="tx-item">
      <div class="tx-icon tx-<?= $tx['type'] ?>"><i data-lucide="<?= $tx['type']==='deposit'?'plus-circle':($tx['type']==='withdraw'?'minus-circle':($tx['type']==='mining'?'cpu':'gift')) ?>"></i></div>
      <div class="tx-info">
        <div class="tx-desc"><?= htmlspecialchars($tx['description'] ?? ucfirst($tx['type'])) ?></div>
        <div class="tx-date"><?= date('d M H:i', strtotime($tx['created_at'])) ?></div>
      </div>
      <div class="tx-amount <?= $tx['amount']>0?'positive':'negative' ?>"><?= ($tx['amount']>0?'+':'').formatRupiah(abs((float)$tx['amount'])) ?></div>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state"><i data-lucide="inbox"></i><p>Belum ada transaksi</p></div>
    <?php endif; ?>
  </div>
</div>

</div><!-- .page-wrapper -->

<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>

<!-- WELCOME POPUP -->
<?php if ($showWelcome && getSetting('popup_welcome_status')==='1'): ?>
<div class="welcome-overlay" id="welcomeOverlay">
  <div class="welcome-popup">
    <div class="welcome-lights" id="welcomeLights"></div>
    <div class="welcome-content">
      <div class="welcome-icon animate-bounce"><i data-lucide="zap"></i></div>
      <h2 class="welcome-title"><?= getSetting('popup_welcome_text','Selamat Datang!') ?></h2>
      <p class="welcome-user">Halo, <strong><?= htmlspecialchars($user['full_name']) ?></strong>!</p>
      <p class="welcome-msg">Semoga hari kamu menyenangkan dan penuh profit! 🚀</p>
      <?php $waGroup = getSetting('wa_group_link'); if ($waGroup): ?>
      <a href="<?= htmlspecialchars($waGroup) ?>" target="_blank" class="welcome-wa-btn">
        <i data-lucide="message-circle"></i> Gabung Grup WhatsApp
      </a>
      <?php endif; ?>
      <button class="welcome-close-btn" onclick="closeWelcome()">Mulai Mining <i data-lucide="arrow-right"></i></button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
initBannerSlider();
initMiningCountdown();
<?php if ($showWelcome && getSetting('popup_welcome_status')==='1'): ?>
setTimeout(()=>{ initWelcomeLights(); },300);
<?php endif; ?>
document.getElementById('notifBtn')?.addEventListener('click',()=>{
  const p = document.getElementById('notifPanel');
  p.style.display = p.style.display==='none'?'block':'none';
});
function closeNotif(){ document.getElementById('notifPanel').style.display='none'; }
function markAllRead(){
  fetch('<?= APP_URL ?>/api/mark_notif_read.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({all:true})});
  document.querySelectorAll('.notif-item.unread').forEach(e=>e.classList.remove('unread'));
  document.querySelector('.badge-dot')?.remove();
}
function closeWelcome(){ document.getElementById('welcomeOverlay').style.display='none'; }
</script>
