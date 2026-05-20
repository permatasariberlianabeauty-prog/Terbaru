<?php
// ============================================================
// NOXARA - includes/functions.php
// ============================================================

function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatNumber(float $num): string {
    if ($num >= 1000000) return number_format($num/1000000, 1) . 'Jt';
    if ($num >= 1000) return number_format($num/1000, 1) . 'Rb';
    return number_format($num, 0);
}

function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $k = dbEscape($key);
    $r = dbQuery("SELECT value FROM settings WHERE key_name='$k' LIMIT 1");
    if ($r && $r->num_rows > 0) {
        $cache[$key] = $r->fetch_assoc()['value'] ?? $default;
        return $cache[$key];
    }
    return $default;
}

function setSetting(string $key, string $value): void {
    $k = dbEscape($key);
    $v = dbEscape($value);
    dbQuery("INSERT INTO settings (key_name,value) VALUES ('$k','$v') ON DUPLICATE KEY UPDATE value='$v'");
}

function generateReferralCode(): string {
    do {
        $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $r = dbQuery("SELECT id FROM users WHERE referral_code='$code' LIMIT 1");
    } while ($r && $r->num_rows > 0);
    return $code;
}

function generateTxId(): string {
    return 'NX' . strtoupper(uniqid()) . mt_rand(100,999);
}

function hashPassword(string $pw): string {
    return password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword(string $pw, string $hash): bool {
    return password_verify($pw, $hash);
}

function hashPin(string $pin): string {
    return password_hash($pin, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPin(string $pin, string $hash): bool {
    return password_verify($pin, $hash);
}

function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isAjax(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function getClientIp(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function getUserAgent(): string {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

function getGreeting(): string {
    $h = (int)date('G');
    if ($h >= 5 && $h < 11) return 'Selamat Pagi';
    if ($h >= 11 && $h < 15) return 'Selamat Siang';
    if ($h >= 15 && $h < 18) return 'Selamat Sore';
    return 'Selamat Malam';
}

function getGreetingEn(): string {
    $h = (int)date('G');
    if ($h >= 5 && $h < 12) return 'Good Morning';
    if ($h >= 12 && $h < 17) return 'Good Afternoon';
    if ($h >= 17 && $h < 21) return 'Good Evening';
    return 'Good Night';
}

function getVipInfo(int $level): array {
    $r = dbQuery("SELECT * FROM vip_settings WHERE level=$level LIMIT 1");
    if ($r && $r->num_rows > 0) return $r->fetch_assoc();
    return ['level'=>0,'name'=>'VIP 0','min_withdraw'=>100000,'withdraw_fee_percent'=>15];
}

function getUserById(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res ?: null;
}

function addTransaction(int $userId, string $type, float $amount, string $desc='', int $refId=0): void {
    $user = getUserById($userId);
    if (!$user) return;
    $bal = (float)$user['balance'];
    $after = $bal + $amount;
    $stmt = db()->prepare("INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,description,ref_id) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('isddds i', $userId, $type, $amount, $bal, $after, $desc, $refId);
    $stmt->execute();
    $stmt->close();
}

function addNotification(int $userId, string $title, string $msg, string $type='info'): void {
    $stmt = db()->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)");
    $stmt->bind_param('isss', $userId, $title, $msg, $type);
    $stmt->execute();
    $stmt->close();
}

function broadcastNotification(string $title, string $msg, string $type='info'): void {
    $stmt = db()->prepare("INSERT INTO notifications (user_id,title,message,type,is_broadcast) VALUES (NULL,?,?,?,1)");
    $stmt->bind_param('sss', $title, $msg, $type);
    $stmt->execute();
    $stmt->close();
}

function getUnreadNotifCount(int $userId): int {
    $stmt = db()->prepare("SELECT COUNT(*) as c FROM notifications WHERE (user_id=? OR is_broadcast=1) AND is_read=0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['c'] ?? 0);
}

function getUnreadChatCount(int $userId): int {
    $stmt = db()->prepare("SELECT COUNT(*) as c FROM live_chats WHERE user_id=? AND sender='admin' AND is_read=0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['c'] ?? 0);
}

function isMiningToday(int $packageId): bool {
    $stmt = db()->prepare("SELECT mining_today FROM user_packages WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool)($row['mining_today'] ?? false);
}

function calcRoi(float $price, float $profitDay, int $days): float {
    if ($price <= 0) return 0;
    return round(($profitDay * $days / $price) * 100, 1);
}

function calcTotalProfit(float $profitDay, int $days): float {
    return $profitDay * $days;
}

function vipBadgeColor(int $level): string {
    return match($level) {
        1 => '#C0C0C0',
        2 => '#FFD700',
        3 => '#FF6B35',
        default => '#6B7280'
    };
}

function vipBadgeName(int $level): string {
    return 'VIP ' . $level;
}

function logAdminAction(int $adminId, string $action, string $detail=''): void {
    $ip = dbEscape(getClientIp());
    $act = dbEscape($action);
    $det = dbEscape($detail);
    dbQuery("INSERT INTO admin_logs (admin_id,action,detail,ip_address) VALUES ($adminId,'$act','$det','$ip')");
}

function checkMaintenanceMode(): void {
    if (getSetting('maintenance_mode') === '1') {
        if (!isAdmin()) {
            include __DIR__ . '/../pages/maintenance.php';
            exit;
        }
    }
}
