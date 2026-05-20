<?php
// NOXARA - Cron Job: Reset Mining Harian
// Crontab: 1 0 * * * /usr/bin/php /www/wwwroot/noxara.page/cron/mining_reset.php
define('CRON_RUN', true);
require_once __DIR__ . '/../config/bootstrap.php';
echo "[".date('Y-m-d H:i:s')."] Mining reset started\n";
dbQuery("UPDATE user_packages SET mining_today=0 WHERE status='active'");
echo "Reset done: ".db()->affected_rows." packages\n";
dbQuery("UPDATE user_packages SET status='expired' WHERE status='active' AND days_elapsed>=duration_days");
echo "Expired: ".db()->affected_rows." packages\n";
echo "[".date('Y-m-d H:i:s')."] Done\n";
