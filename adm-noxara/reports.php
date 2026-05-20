<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle=ucfirst(str_replace('_',' ','reports')); $currentAdminPage='reports';
include __DIR__.'/_header.php';
?>
<div class="admin-page-header"><h1><?php echo ucwords(str_replace('_',' ','reports')); ?></h1></div>
<div class="admin-card"><p class="text-muted text-center py-4">Halaman ini aktif dan siap dikonfigurasi.</p></div>
<?php include __DIR__.'/_footer.php';?>
