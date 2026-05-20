<?php
// ============================================================
// NOXARA - includes/functions.php
// Compatible: PHP 7.2+
// ============================================================

function formatRupiah($amount) {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

function formatNumber($num) {
    if ($num >= 1000000) return number_format($num/1000000, 1) . 'Jt';
    if ($num >= 1000) return number_format($num/1000, 1) . 'Rb';
    return number_format($num, 0);
}

function getSetting($key, $default = '') {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $k = dbEscape($key);
    $r = dbQuery("SELECT value FROM settings WHERE key_name='$k' LIMIT 1");
    if ($r && $r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $cache[$key] = isset($row['value']) ? $row['value'] : $default;
        return $cache[$key];
    }
    return $default;
}

function setSetting($key, $value) {
    $k = dbEscape($key);
    $v = dbEscape($value);
    dbQuery("INSERT INTO settings (key_name,value) VALUES ('$k','$v') ON DUPLICATE KEY UPDATE value='$v'");
}

function generateReferralCode() {
    do {
        $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $r = dbQuery("SELECT id FROM users WHERE referral_code='$code' LIMIT 1");
    } while ($r && $r->num_rows > 0);
    return $code;
}

function generateTxId() {
    return 'NX' . strtoupper(uniqid()) . mt_rand(100,999);
}

function hashPassword($pw) {
    return password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword($pw, $hash) {
    return password_verify($pw, $hash);
}

function hashPin($pin) {
    return password_hash($pin, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPin($pin, $hash) {
    return password_verify($pin, $hash);
}

function sanitize($val) {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function getClientIp() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
    return '0.0.0.0';
}

function getUserAgent() {
    return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
}

function getGreeting() {
    $h = (int)date('G');
    if ($h >= 5 && $h < 11) return 'Selamat Pagi';
    if ($h >= 11 && $h < 15) return 'Selamat Siang';
    if ($h >= 15 && $h < 18) return 'Selamat Sore';
    return 'Selamat Malam';
}

function getGreetingEn() {
    $h = (int)date('G');
    if ($h >= 5 && $h < 12) return 'Good Morning';
    if ($h >= 12 && $h < 17) return 'Good Afternoon';
    if ($h >= 17 && $h < 21) return 'Good Evening';
    return 'Good Night';
}

function getVipInfo($level) {
    $level = (int)$level;
    $r = dbQuery("SELECT * FROM vip_settings WHERE level=$level LIMIT 1");
    if ($r && $r->num_rows > 0) return $r->fetch_assoc();
    return ['level'=>0,'name'=>'VIP 0','min_withdraw'=>100000,'withdraw_fee_percent'=>15];
}

function getUserById($id) {
    $id = (int)$id;
    $stmt = db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res ? $res : null;
}

function addTransaction($userId, $type, $amount, $desc = '', $refId = 0) {
    $user = getUserById($userId);
    if (!$user) return;
    $bal = (float)$user['balance'];
    $after = $bal + $amount;
    $stmt = db()->prepare("INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,description,ref_id) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('isdddsi', $userId, $type, $amount, $bal, $after, $desc, $refId);
    $stmt->execute();
    $stmt->close();
}

function addNotification($userId, $title, $msg, $type = 'info') {
    $stmt = db()->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)");
    $stmt->bind_param('isss', $userId, $title, $msg, $type);
    $stmt->execute();
    $stmt->close();
}

function broadcastNotification($title, $msg, $type = 'info') {
    $stmt = db()->prepare("INSERT INTO notifications (user_id,title,message,type,is_broadcast) VALUES (NULL,?,?,?,1)");
    $stmt->bind_param('sss', $title, $msg, $type);
    $stmt->execute();
    $stmt->close();
}

function getUnreadNotifCount($userId) {
    $userId = (int)$userId;
    $stmt = db()->prepare("SELECT COUNT(*) as c FROM notifications WHERE (user_id=? OR is_broadcast=1) AND is_read=0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['c'] ?? 0);
}

function getUnreadChatCount($userId) {
    $userId = (int)$userId;
    $stmt = db()->prepare("SELECT COUNT(*) as c FROM live_chats WHERE user_id=? AND sender='admin' AND is_read=0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['c'] ?? 0);
}

function calcRoi($price, $profitDay, $days) {
    if ((float)$price <= 0) return 0;
    return round(((float)$profitDay * $days / (float)$price) * 100, 1);
}

function calcTotalProfit($profitDay, $days) {
    return (float)$profitDay * $days;
}

function vipBadgeColor($level) {
    $level = (int)$level;
    if ($level === 1) return '#C0C0C0';
    if ($level === 2) return '#FFD700';
    if ($level === 3) return '#FF6B35';
    return '#6B7280';
}

function vipBadgeName($level) {
    return 'VIP ' . (int)$level;
}

function logAdminAction($adminId, $action, $detail = '') {
    $adminId = (int)$adminId;
    $ip = dbEscape(getClientIp());
    $act = dbEscape($action);
    $det = dbEscape($detail);
    dbQuery("INSERT INTO admin_logs (admin_id,action,detail,ip_address) VALUES ($adminId,'$act','$det','$ip')");
}

function checkMaintenanceMode() {
    if (getSetting('maintenance_mode') === '1') {
        if (!isAdmin()) {
            include __DIR__ . '/../pages/maintenance.php';
            exit;
        }
    }
}
