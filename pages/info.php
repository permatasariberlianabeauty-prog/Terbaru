<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $user=currentUser(); $uid=(int)$user['id'];
$pageTitle='info'; $currentPage='info';
include __DIR__.'/../includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header"><a href="<?=APP_URL?>/pages/dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a><h1 class="page-title"><?php echo ucfirst('info'); ?></h1></div>
<div class="section-card">
<?php
  echo '<div class="info-list">';
  $items=[
    ['Tentang Noxara','Noxara adalah platform mining rupiah terpercaya yang memberikan profit harian bagi para investor. Didirikan untuk memberikan solusi investasi yang mudah, aman, dan menguntungkan bagi semua kalangan.'],
    ['Minimal Penarikan','Minimal penarikan tergantung level VIP kamu. VIP 0: Rp 100.000, VIP 1: Rp 50.000, VIP 2: Rp 30.000, VIP 3: Tidak ada minimal.'],
    ['Biaya Penarikan','Biaya admin tergantung level VIP. VIP 0: 15%, VIP 1&2: 5%, VIP 3: Gratis.'],
    ['Cara Kerja','Beli produk investasi → Mining setiap hari → Profit otomatis masuk ke saldo → Tarik kapan saja.'],
    ['Keamanan','Platform dilindungi dengan enkripsi SSL, sistem PIN, dan verifikasi berlapis.'],
    ['Jam Operasional','Platform beroperasi 24/7. Penarikan diproses dalam 1x24 jam kerja.'],
  ];
  foreach($items as $i){ echo "<div class='info-item'><div class='info-title'>".$i[0]."</div><div class='info-desc'>".$i[1]."</div></div>"; }
  echo '</div>';
?>
</div>
</div>
<?php include __DIR__.'/../includes/mobile_nav.php';?>
<?php include __DIR__.'/../includes/footer.php';?>
