</div><!-- .admin-content -->
</div><!-- #adminApp -->
<div id="toastContainer" class="toast-container"></div>
<div class="modal-overlay" id="modalOverlay" style="display:none"><div class="modal-box" id="modalBox"><div class="modal-icon" id="modalIcon"></div><div class="modal-title" id="modalTitle"></div><div class="modal-msg" id="modalMsg"></div><div class="modal-actions" id="modalActions"></div></div></div>
<script src="<?=APP_URL?>/assets/js/main.js"></script>
<script src="<?=APP_URL?>/assets/js/animations.js"></script>
<script>
lucide.createIcons();
window.NOXARA={appUrl:'<?=APP_URL?>',isAdmin:true};
function toggleAdminMenu(){
  document.getElementById('adminSidebar').classList.toggle('open');
}
</script>
</body></html>
