<?php
// ============================================================
// NOXARA - includes/footer.php
// ============================================================
?>
</div><!-- #app -->

<!-- Floating Buttons -->
<?php if (isset($user) && $user): ?>
<!-- Floating Mining -->
<a href="<?= APP_URL ?>/pages/mining.php" class="fab fab-mining" title="Mining Sekarang">
  <i data-lucide="cpu"></i>
</a>
<!-- Floating Live Chat -->
<button class="fab fab-chat" id="fabChat" title="Live Chat">
  <i data-lucide="message-circle"></i>
  <?php if ($unreadChat > 0): ?>
  <span class="fab-badge"><?= $unreadChat ?></span>
  <?php endif; ?>
</button>

<!-- Scroll to Top -->
<button class="fab fab-top" id="fabTop" title="Ke atas">
  <i data-lucide="chevron-up"></i>
</button>

<!-- Live Chat Panel -->
<div class="chat-panel" id="chatPanel">
  <div class="chat-header">
    <div class="chat-header-info">
      <div class="chat-avatar-admin">N</div>
      <div>
        <div class="chat-admin-name">Admin Noxara</div>
        <div class="chat-admin-status" id="adminStatus">
          <span class="status-dot <?= getSetting('admin_status')==='online'?'online':'offline' ?>"></span>
          <?= getSetting('admin_status')==='online'?'Online':'Offline' ?>
        </div>
      </div>
    </div>
    <button class="chat-close" id="chatClose"><i data-lucide="x"></i></button>
  </div>
  <div class="chat-messages" id="chatMessages">
    <div class="chat-welcome">
      <p>Halo! Ada yang bisa kami bantu? 😊</p>
      <?php if (getSetting('admin_status') !== 'online'): ?>
      <p class="chat-offline-msg">Admin sedang offline. Tinggalkan pesan, kami akan segera membalas.</p>
      <?php endif; ?>
    </div>
    <!-- Quick Replies -->
    <div class="quick-replies" id="quickReplies">
      <?php
      $qrs = dbQuery("SELECT * FROM quick_replies WHERE status=1 ORDER BY sort_order ASC LIMIT 5");
      if ($qrs) while ($qr = $qrs->fetch_assoc()): ?>
      <button class="quick-reply-btn" data-answer="<?= htmlspecialchars($qr['answer']) ?>">
        <?= htmlspecialchars($qr['question']) ?>
      </button>
      <?php endwhile; ?>
    </div>
    <div id="chatMsgs"></div>
  </div>
  <div class="chat-input-area">
    <label class="chat-img-label" for="chatImg">
      <i data-lucide="image"></i>
      <input type="file" id="chatImg" accept="image/*" style="display:none">
    </label>
    <input type="text" id="chatInput" placeholder="Ketik pesan..." autocomplete="off">
    <button id="chatSend"><i data-lucide="send"></i></button>
  </div>
  <!-- Rating -->
  <div class="chat-rating" id="chatRating" style="display:none">
    <p>Bagaimana pelayanan kami?</p>
    <div class="rating-stars">
      <?php for ($s=1;$s<=5;$s++): ?>
      <button class="star-btn" data-star="<?= $s ?>"><i data-lucide="star"></i></button>
      <?php endfor; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Pop Up / Toast -->
<div id="toastContainer" class="toast-container"></div>

<!-- Global Modal -->
<div class="modal-overlay" id="modalOverlay" style="display:none">
  <div class="modal-box" id="modalBox">
    <div class="modal-icon" id="modalIcon"></div>
    <div class="modal-title" id="modalTitle"></div>
    <div class="modal-msg" id="modalMsg"></div>
    <div class="modal-actions" id="modalActions"></div>
  </div>
</div>

<!-- QRCode library for QRIS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  window.NOXARA = {
    <?php if (isset($user) && $user): ?>
    userId: <?= (int)$user['id'] ?>,
    username: '<?= addslashes($user['username']) ?>',
    <?php endif; ?>
    appUrl: '<?= APP_URL ?>',
    lang: '<?= isset($userLang) ? $userLang : "id" ?>',
    theme: '<?= isset($userTheme) ? $userTheme : "dark" ?>'
  };
  // Final init after all scripts loaded
  document.addEventListener('DOMContentLoaded', function(){
    // Init Lucide icons
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
    // Init banner slider
    if (typeof initBannerSlider === 'function') {
      initBannerSlider();
    }
    // Init mining countdown if exists
    if (typeof initMiningCountdown === 'function') {
      initMiningCountdown();
    }
  });
</script>
</body>
</html>
