<?php
require_once __DIR__.'/../config/bootstrap.php';
// About page is PUBLIC - accessible without login
$user       = currentUser();
$pageTitle  = 'Tentang Noxara';
$currentPage = 'about';

// If user is logged in, show with full header; otherwise show public version
if ($user) {
    include __DIR__.'/../includes/header.php';
    echo '<div class="page-wrapper">';
    echo '<div class="page-header"><a href="' . APP_URL . '/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title">Tentang Platform</h1></div>';
} else {
    // Public mode - minimal header
    echo '<!DOCTYPE html><html lang="id" data-theme="dark"><head>';
    echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">';
    echo '<title>Tentang Noxara</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media=\'all\'">';
    echo '<script src="https://unpkg.com/lucide@0.263.1/dist/umd/lucide.min.js" defer></script>';
    echo '<link rel="stylesheet" href="' . APP_URL . '/assets/css/style.css">';
    echo '<link rel="stylesheet" href="' . APP_URL . '/assets/css/mobile.css">';
    echo '<style>*{box-sizing:border-box;margin:0;padding:0}body{background:#0A0E1A;color:#E8EAED;font-family:Inter,sans-serif;min-height:100vh}a{text-decoration:none;color:inherit}</style>';
    echo '</head><body class="theme-dark">';
    echo '<div style="padding:16px;max-width:480px;margin:0 auto">';
    echo '<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding:12px 0;border-bottom:1px solid #1E2A45">';
    echo '<a href="' . APP_URL . '" style="color:#9CA3AF;display:flex"><i data-lucide="arrow-left" style="width:20px;height:20px"></i></a>';
    echo '<h1 style="font-size:18px;font-weight:700">Tentang Platform</h1></div>';
}
?>
<div class="section-card">
  <h3 style="font-size:16px;font-weight:700;color:#FFD700;margin-bottom:12px">Tentang Noxara</h3>
  <p style="font-size:13px;color:#9CA3AF;line-height:1.7;margin-bottom:12px">
    Noxara adalah platform mining rupiah terpercaya yang memberikan profit harian bagi para investor.
    Didirikan untuk memberikan solusi investasi yang mudah, aman, dan menguntungkan bagi semua kalangan.
  </p>

  <?php
  $items = [
    ['cpu',          'Cara Kerja',         'Beli produk investasi → Mining setiap hari → Profit otomatis masuk ke saldo → Tarik kapan saja ke rekening bank.'],
    ['shield-check', 'Keamanan',           'Platform dilindungi dengan enkripsi SSL, sistem PIN 6 digit, dan verifikasi berlapis untuk setiap transaksi.'],
    ['trending-up',  'Sistem Profit',      'Profit dihitung berdasarkan paket yang dibeli. Mining 1x sehari, profit masuk otomatis ke saldo utama dalam 2 jam.'],
    ['dollar-sign',  'Minimal Penarikan',  'VIP 0: Rp 100.000 (biaya 15%) | VIP 1: Rp 50.000 (biaya 5%) | VIP 2: Rp 30.000 (biaya 5%) | VIP 3: Tidak ada (gratis).'],
    ['clock',        'Jam Operasional',    'Platform beroperasi 24/7. Penarikan diproses dalam 1x24 jam kerja. Reset mining setiap hari pukul 00:01 WIB.'],
    ['award',        'Referral 3 Level',   'Level 1: 10% | Level 2: 5% | Level 3: 2% dari setiap deposit bawahan. Komisi masuk otomatis ke saldo utama.'],
    ['users',        'Registrasi Gratis',  'Daftar 100% gratis dan langsung dapat bonus saldo Rp 15.000. Bonus bisa dipakai untuk beli produk investasi.'],
    ['zap',          'Deposit via QRIS',   'Top up saldo menggunakan QRIS dari berbagai e-wallet (DANA, GoPay, OVO, ShopeePay, dll). Saldo masuk otomatis.'],
  ];
  foreach ($items as $it):
  ?>
  <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #1E2A45">
    <div style="width:36px;height:36px;background:rgba(255,215,0,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <i data-lucide="<?= $it[0] ?>" style="width:18px;height:18px;color:#FFD700"></i>
    </div>
    <div>
      <div style="font-size:14px;font-weight:600;margin-bottom:4px"><?= $it[1] ?></div>
      <div style="font-size:12px;color:#9CA3AF;line-height:1.6"><?= $it[2] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if ($user): ?>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php'; ?>
<?php include __DIR__.'/../includes/footer.php'; ?>
<?php else: ?>
<div style="text-align:center;margin-top:24px;padding-bottom:40px">
  <a href="<?= APP_URL ?>/auth/register.php" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:linear-gradient(135deg,#FFD700,#FF8C00);color:#000;font-weight:700;font-size:14px;border-radius:12px;text-decoration:none;margin-bottom:12px">
    <i data-lucide="cpu" style="width:18px;height:18px"></i> Mining Sekarang - GRATIS
  </a>
  <br>
  <a href="<?= APP_URL ?>" style="color:#9CA3AF;font-size:13px">Kembali ke Beranda</a>
</div>
<script>
(function t(){typeof lucide!=='undefined'?lucide.createIcons():setTimeout(t,50);})();
</script>
</body></html>
<?php endif; ?>
