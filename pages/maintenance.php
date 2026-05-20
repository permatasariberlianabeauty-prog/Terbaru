<!DOCTYPE html><html lang="id" data-theme="dark"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Maintenance - Noxara</title><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"><script src="https://unpkg.com/lucide@0.263.1/dist/umd/lucide.min.js" defer></script><style>*{margin:0;padding:0;box-sizing:border-box}body{background:#0A0E1A;color:#fff;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:20px}.maintenance-box{max-width:400px}.maint-icon{font-size:80px;margin-bottom:20px;color:#FFD700;animation:pulse 2s infinite}h1{font-size:28px;margin-bottom:10px;color:#FFD700}p{color:#9ca3af;margin-bottom:20px}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}</style></head><body>
<div class="maintenance-box">
  <div class="maint-icon"><i data-lucide="wrench" style="width:80px;height:80px;color:#FFD700"></i></div>
  <h1>Sedang Maintenance</h1>
  <p><?php echo htmlspecialchars(getSetting('maintenance_message','Platform sedang dalam perbaikan.')); ?></p>
  <p>Estimasi selesai: <strong><?php echo htmlspecialchars(getSetting('maintenance_estimate','Segera')); ?></strong></p>
</div>
<script>lucide.createIcons();</script>
</body></html>
