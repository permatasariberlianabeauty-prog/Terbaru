<?php
// ============================================================
// NOXARA - config/config.php
// ============================================================
define('APP_NAME', 'Noxara');
define('APP_TAGLINE', 'Invest Smarter, Grow Faster');

// Auto-detect URL atau gunakan hardcoded
if (!defined('APP_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'noxara.page';
    define('APP_URL', $protocol . '://' . $host);
}

define('APP_VERSION', '1.0.0');

// Database
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'noxara_Oke12');
define('DB_USER', 'noxara_Oke11');
define('DB_PASS', 'Jakakece12');

// Admin path
define('ADMIN_PATH', '/adm-noxara');
define('ADMIN_URL', APP_URL . '/adm-noxara');

// Cashify API
define('CASHIFY_LICENSE_KEY', 'cashify_261885e5c5f830e68f929de05e3bfdf72e118d859edc5419472f79a813eed3ea');
define('CASHIFY_QRIS_ID', '1b935c41-bf43-4075-8f57-56b6cbfa2d07');
define('CASHIFY_BASE_URL', 'https://cashify.my.id/api/generate');
define('CASHIFY_PACKAGE_ID', 'com.orderkuota.app');
define('CASHIFY_EXPIRED_MINUTES', 15);

// Session
define('SESSION_NAME', 'noxara_sess');
define('SESSION_LIFETIME', 86400); // 24 jam

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Mining reset time (WIB)
define('MINING_RESET_HOUR', 0);
define('MINING_RESET_MINUTE', 1);
define('MINING_COUNTDOWN_HOURS', 2);

// Upload path
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('LOG_PATH', __DIR__ . '/../logs/');

// Bonus registrasi
define('REGISTER_BONUS', 15000);

// Error reporting (production: 0)
error_reporting(0);
ini_set('display_errors', 0);
