<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle='Manajemen Produk'; $currentAdminPage='products';

if($_SERVER['REQUEST_METHOD']==='POST'&&isAjax()){
    requireAdmin(); verifyCsrf();
    $action=$_POST['action']??'';
    if($action==='save'){
        $id=(int)($_POST['id']??0);
        $name=sanitize($_POST['name']??''); $cat=sanitize($_POST['category']??'starter');
        $price=(float)($_POST['price']??0); $profit=(float)($_POST['profit_per_day']??0);
        $desc=sanitize($_POST['description']??''); $badge=sanitize($_POST['badge']??'');
        $quota=(int)($_POST['quota']??0); $maxBuy=(int)($_POST['max_buy']??0);
        $rating=(float)($_POST['rating']??4.5); $buyers=(int)($_POST['total_buyers']??0);
        $status=(int)($_POST['status']??1);
        if($id>0){
            dbQuery("UPDATE products SET name='".dbEscape($name)."',category='".dbEscape($cat)."',price=$price,profit_per_day=$profit,description='".dbEscape($desc)."',badge='".dbEscape($badge)."',quota=$quota,max_buy=$maxBuy,rating=$rating,total_buyers=$buyers,status=$status WHERE id=$id");
        } else {
            dbQuery("INSERT INTO products (name,category,price,profit_per_day,description,badge,quota,max_buy,rating,total_buyers,status) VALUES ('".dbEscape($name)."','".dbEscape($cat)."',$price,$profit,'".dbEscape($desc)."','".dbEscape($badge)."',$quota,$maxBuy,$rating,$buyers,$status)");
        }
        logAdminAction((int)$_SESSION['admin_id'],'save_product',"name=$name");
        jsonResponse(['success'=>true,'message'=>'Produk berhasil disimpan']);
    }
    if($action==='delete'){$id=(int)($_POST['id']??0);dbQuery("UPDATE products SET status=0 WHERE id=$id");jsonResponse(['success'=>true,'message'=>'Produk dinonaktifkan']);}
    if($action==='get'){$id=(int)($_POST['id']??0);$r=dbQuery("SELECT * FROM products WHERE id=$id LIMIT 1");if($r&&$r->num_rows>0)jsonResponse(['success'=>true,'product'=>$r->fetch_assoc()]);jsonResponse(['success'=>false]);}
}

include __DIR__.'/_header.php';
$products=dbQuery("SELECT * FROM products ORDER BY category,sort_order ASC");
?>
<div class="admin-page-header"><h1>Manajemen Produk</h1><button class="btn btn-primary" onclick="openProductModal(0)"><i data-lucide="plus"></i> Tambah Produk</button></div>
<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Profit/Hari</th><th>ROI</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if($products&&$products->num_rows>0):while($p=$products->fetch_assoc()):
        $roi=calcRoi((float)$p['price'],(float)$p['profit_per_day'],30);$total=calcTotalProfit((float)$p['profit_per_day'],30);?>
      <tr>
        <td><?=htmlspecialchars($p['name'])?><?php if($p['badge']):?><span class="pc-badge" style="font-size:10px;padding:2px 6px"><?=$p['badge']?></span><?php endif;?></td>
        <td><span class="cat-badge"><?=ucfirst($p['category'])?></span></td>
        <td><?=formatRupiah((float)$p['price'])?></td>
        <td><?=formatRupiah((float)$p['profit_per_day'])?></td>
        <td class="text-gold"><?=$roi?>%</td>
        <td><?=formatRupiah($total)?></td>
        <td><span class="status-badge <?=$p['status']?'status-active':'status-blocked'?>"><?=$p['status']?'Aktif':'Nonaktif'?></span></td>
        <td>
          <button class="btn btn-xs btn-outline" onclick="openProductModal(<?=$p['id']?>)"><i data-lucide="edit"></i></button>
          <button class="btn btn-xs btn-danger" onclick="deleteProduct(<?=$p['id']?>)"><i data-lucide="trash-2"></i></button>
        </td>
      </tr>
      <?php endwhile;endif;?>
      </tbody>
    </table>
  </div>
</div>

<!-- Product Modal -->
<div class="modal-overlay" id="productModal" style="display:none">
  <div class="modal-box modal-lg">
    <div class="modal-title" id="productModalTitle">Tambah Produk</div>
    <input type="hidden" id="prodId">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Nama</label><input type="text" id="prodName" class="form-input"></div>
      <div class="form-group"><label class="form-label">Kategori</label><select id="prodCat" class="form-input"><option value="starter">Starter</option><option value="growth">Growth</option><option value="elite">Elite</option></select></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Harga</label><input type="number" id="prodPrice" class="form-input" oninput="calcAutoRoi()"></div>
      <div class="form-group"><label class="form-label">Profit/Hari</label><input type="number" id="prodProfit" class="form-input" oninput="calcAutoRoi()"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">ROI (Auto)</label><input type="text" id="prodRoi" class="form-input" readonly></div>
      <div class="form-group"><label class="form-label">Total Profit (Auto)</label><input type="text" id="prodTotal" class="form-input" readonly></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Badge</label><input type="text" id="prodBadge" class="form-input" placeholder="Best Seller, Populer, dll"></div>
      <div class="form-group"><label class="form-label">Kuota (0=unlimited)</label><input type="number" id="prodQuota" class="form-input"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Max Beli</label><input type="number" id="prodMaxBuy" class="form-input"></div>
      <div class="form-group"><label class="form-label">Rating</label><input type="number" id="prodRating" class="form-input" step="0.1" min="1" max="5"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Total Pembeli</label><input type="number" id="prodBuyers" class="form-input"></div>
      <div class="form-group"><label class="form-label">Status</label><select id="prodStatus" class="form-input"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
    </div>
    <div class="form-group"><label class="form-label">Deskripsi</label><textarea id="prodDesc" class="form-input" rows="3"></textarea></div>
    <div class="modal-actions mt-2">
      <button class="btn btn-outline" onclick="document.getElementById('productModal').style.display='none'">Batal</button>
      <button class="btn btn-primary" onclick="saveProduct()"><i data-lucide="save"></i> Simpan</button>
    </div>
  </div>
</div>
<?php include __DIR__.'/_footer.php';?>
<script>
const CSRF='<?=csrfToken()?>';
function calcAutoRoi(){const p=parseFloat(document.getElementById('prodPrice').value)||0;const d=parseFloat(document.getElementById('prodProfit').value)||0;document.getElementById('prodRoi').value=p>0?((d*30/p)*100).toFixed(1)+'%':'0%';document.getElementById('prodTotal').value='Rp '+(d*30).toLocaleString('id-ID');}
function openProductModal(id){
  document.getElementById('prodId').value=id;
  document.getElementById('productModalTitle').textContent=id?'Edit Produk':'Tambah Produk';
  if(id){
    fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=get&id=${id}`})
    .then(r=>r.json()).then(d=>{if(d.success){const p=d.product;document.getElementById('prodName').value=p.name;document.getElementById('prodCat').value=p.category;document.getElementById('prodPrice').value=p.price;document.getElementById('prodProfit').value=p.profit_per_day;document.getElementById('prodBadge').value=p.badge||'';document.getElementById('prodQuota').value=p.quota;document.getElementById('prodMaxBuy').value=p.max_buy;document.getElementById('prodRating').value=p.rating;document.getElementById('prodBuyers').value=p.total_buyers;document.getElementById('prodStatus').value=p.status;document.getElementById('prodDesc').value=p.description||'';calcAutoRoi();}});
  } else {
    ['prodName','prodBadge','prodDesc'].forEach(i=>document.getElementById(i).value='');
    ['prodPrice','prodProfit','prodQuota','prodMaxBuy','prodBuyers'].forEach(i=>document.getElementById(i).value=0);
    document.getElementById('prodRating').value=4.5;document.getElementById('prodStatus').value=1;calcAutoRoi();
  }
  document.getElementById('productModal').style.display='flex';
}
function saveProduct(){
  const data={action:'save',id:document.getElementById('prodId').value,name:document.getElementById('prodName').value,category:document.getElementById('prodCat').value,price:document.getElementById('prodPrice').value,profit_per_day:document.getElementById('prodProfit').value,description:document.getElementById('prodDesc').value,badge:document.getElementById('prodBadge').value,quota:document.getElementById('prodQuota').value,max_buy:document.getElementById('prodMaxBuy').value,rating:document.getElementById('prodRating').value,total_buyers:document.getElementById('prodBuyers').value,status:document.getElementById('prodStatus').value,csrf_token:CSRF};
  showLoading();
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:new URLSearchParams(data)})
  .then(r=>r.json()).then(d=>{hideLoading();if(d.success){showToast(d.message,'success');document.getElementById('productModal').style.display='none';setTimeout(()=>location.reload(),1000);}else{showModal('error','Gagal',d.message);}});
}
function deleteProduct(id){showConfirm('Nonaktifkan Produk','Yakin nonaktifkan produk ini?',()=>{fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=delete&id=${id}`}).then(r=>r.json()).then(d=>{if(d.success){showToast(d.message,'success');setTimeout(()=>location.reload(),1000);}});});}
</script>
