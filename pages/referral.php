<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();
$user = currentUser();
$uid  = (int)$user['id'];
$pageTitle = 'Tim Referral';
$currentPage = 'team';
$vip = getVipInfo((int)$user['vip_level']);

$refLink = APP_URL . '/auth/register.php?ref=' . $user['referral_code'];

// Stats
$totalTeam = dbQuery("SELECT COUNT(*) as c FROM users WHERE referred_by=$uid");
$totalOn   = dbQuery("SELECT COUNT(*) as c FROM users WHERE referred_by=$uid AND total_deposit>0");
$totalRef  = dbQuery("SELECT COALESCE(SUM(amount),0) as t FROM transactions WHERE user_id=$uid AND type='referral'");

$totalTeamCount = $totalTeam ? (int)$totalTeam->fetch_assoc()['c'] : 0;
$totalOnCount   = $totalOn   ? (int)$totalOn->fetch_assoc()['c'] : 0;
$totalOffCount  = $totalTeamCount - $totalOnCount;
$totalRefEarn   = $totalRef ? (float)$totalRef->fetch_assoc()['t'] : 0;

// Level 1 members
$l1 = dbQuery("SELECT id,username,full_name,phone,total_deposit FROM users WHERE referred_by=$uid ORDER BY created_at DESC");

include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
  <h1 class="page-title">Tim Referral</h1>
</div>

<!-- Referral Link -->
<div class="ref-link-card">
  <div class="ref-link-label">Link Referral Kamu</div>
  <div class="ref-link-display" id="refLink"><?= htmlspecialchars($refLink) ?></div>
  <div class="ref-code">Kode: <strong><?= htmlspecialchars($user['referral_code']) ?></strong></div>
  <div class="ref-actions">
    <button class="btn btn-gold btn-sm" onclick="copyRefLink()"><i data-lucide="copy"></i> Salin Link</button>
    <a href="https://wa.me/?text=<?= urlencode('Gabung Noxara dan dapat bonus! Daftar: '.$refLink) ?>" target="_blank" class="btn btn-whatsapp btn-sm"><i data-lucide="message-circle"></i></a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($refLink) ?>" target="_blank" class="btn btn-facebook btn-sm"><i data-lucide="facebook"></i></a>
    <a href="https://t.me/share/url?url=<?= urlencode($refLink) ?>&text=<?= urlencode('Gabung Noxara!') ?>" target="_blank" class="btn btn-telegram btn-sm"><i data-lucide="send"></i></a>
  </div>
</div>

<!-- Stats -->
<div class="ref-stats-grid">
  <div class="ref-stat-item">
    <div class="ref-stat-num"><?= $totalTeamCount ?></div>
    <div class="ref-stat-label">Total Tim</div>
  </div>
  <div class="ref-stat-item on">
    <div class="ref-stat-num"><?= $totalOnCount ?></div>
    <div class="ref-stat-label">Aktif (ON)</div>
  </div>
  <div class="ref-stat-item off">
    <div class="ref-stat-num"><?= $totalOffCount ?></div>
    <div class="ref-stat-label">Belum Aktif (OFF)</div>
  </div>
  <div class="ref-stat-item earn">
    <div class="ref-stat-num"><?= formatRupiah($totalRefEarn) ?></div>
    <div class="ref-stat-label">Total Rabat</div>
  </div>
</div>

<!-- Rabat Isi Ulang -->
<div class="section-card">
  <h3 class="section-subtitle"><i data-lucide="trending-up"></i> Rabat Isi Ulang</h3>
  <div class="rabat-table">
    <div class="rabat-row header"><span>Level</span><span>Komisi</span><span>Anggota</span></div>
    <div class="rabat-row"><span>Level 1</span><span class="text-gold"><?= $vip['referral_deposit_l1'] ?>%</span><span><?= $totalOnCount ?></span></div>
    <div class="rabat-row"><span>Level 2</span><span class="text-gold"><?= $vip['referral_deposit_l2'] ?>%</span><span>-</span></div>
    <div class="rabat-row"><span>Level 3</span><span class="text-gold"><?= $vip['referral_deposit_l3'] ?>%</span><span>-</span></div>
  </div>
</div>

<!-- Rabat Transaksi -->
<div class="section-card">
  <h3 class="section-subtitle"><i data-lucide="shopping-bag"></i> Rabat Transaksi</h3>
  <div class="rabat-table">
    <div class="rabat-row header"><span>Level</span><span>Komisi</span></div>
    <div class="rabat-row"><span>Level 1</span><span class="text-gold"><?= $vip['referral_product_l1'] ?>%</span></div>
    <div class="rabat-row"><span>Level 2</span><span class="text-gold"><?= $vip['referral_product_l2'] ?>%</span></div>
    <div class="rabat-row"><span>Level 3</span><span class="text-gold"><?= $vip['referral_product_l3'] ?>%</span></div>
  </div>
</div>

<!-- Team List -->
<div class="section-card">
  <h3 class="section-subtitle"><i data-lucide="users"></i> Daftar Bawahan (Level 1)</h3>
  <?php if ($l1 && $l1->num_rows > 0): while ($m = $l1->fetch_assoc()): ?>
  <div class="member-item">
    <div class="member-avatar"><?= strtoupper(substr($m['username'],0,1)) ?></div>
    <div class="member-info">
      <div class="member-name"><?= htmlspecialchars($m['full_name']) ?></div>
      <div class="member-phone"><?= htmlspecialchars($m['phone']) ?></div>
    </div>
    <div class="member-status <?= $m['total_deposit']>0?'on':'off' ?>">
      <?= $m['total_deposit']>0?'ON':'OFF' ?>
    </div>
  </div>
  <?php endwhile; else: ?>
  <div class="empty-state"><i data-lucide="users"></i><p>Belum ada anggota tim. Bagikan link referral kamu!</p></div>
  <?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/../includes/mobile_nav.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
function copyRefLink(){
  navigator.clipboard.writeText('<?= $refLink ?>').then(()=>showToast('Link referral disalin!','success'));
}
</script>
