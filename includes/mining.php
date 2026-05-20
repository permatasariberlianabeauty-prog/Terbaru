<?php
// ============================================================
// NOXARA - includes/mining.php
// ============================================================

function getUserActivePackages(int $userId): array {
    $stmt = db()->prepare("SELECT up.*,p.name as product_name,p.category FROM user_packages up JOIN products p ON up.product_id=p.id WHERE up.user_id=? AND up.status='active' ORDER BY up.purchased_at DESC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $packages = [];
    while ($row = $res->fetch_assoc()) $packages[] = $row;
    $stmt->close();
    return $packages;
}

function startMining(int $userId, int $packageId): array {
    $stmt = db()->prepare("SELECT up.*,p.name as product_name FROM user_packages up JOIN products p ON up.product_id=p.id WHERE up.id=? AND up.user_id=? AND up.status='active' LIMIT 1");
    $stmt->bind_param('ii', $packageId, $userId);
    $stmt->execute();
    $pkg = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$pkg) return ['success'=>false,'message'=>'Paket tidak ditemukan'];
    if ($pkg['mining_today']) return ['success'=>false,'message'=>'Sudah mining hari ini! Kembali lagi besok.'];

    $now = date('Y-m-d H:i:s');
    $finishAt = date('Y-m-d H:i:s', strtotime('+'.MINING_COUNTDOWN_HOURS.' hours'));

    $stmt2 = db()->prepare("UPDATE user_packages SET mining_today=1,last_mining=? WHERE id=?");
    $stmt2->bind_param('si', $now, $packageId);
    $stmt2->execute();
    $stmt2->close();

    // Schedule profit via session (actual cron handles real credit)
    $_SESSION['mining_finish_'.$packageId] = strtotime($finishAt);
    $_SESSION['mining_profit_'.$packageId] = $pkg['profit_per_day'];

    return [
        'success' => true,
        'finish_at' => $finishAt,
        'finish_ts' => strtotime($finishAt),
        'profit' => $pkg['profit_per_day'],
        'product_name' => $pkg['product_name']
    ];
}

function completeMining(int $userId, int $packageId): array {
    $stmt = db()->prepare("SELECT up.*,p.name as product_name FROM user_packages up JOIN products p ON up.product_id=p.id WHERE up.id=? AND up.user_id=? AND up.status='active' AND up.mining_today=1 LIMIT 1");
    $stmt->bind_param('ii', $packageId, $userId);
    $stmt->execute();
    $pkg = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$pkg) return ['success'=>false,'message'=>'Paket tidak valid'];

    $lastMining = strtotime($pkg['last_mining'] ?? '');
    $hoursAgo = (time() - $lastMining) / 3600;
    if ($hoursAgo < MINING_COUNTDOWN_HOURS) {
        $remaining = (MINING_COUNTDOWN_HOURS * 3600) - (time() - $lastMining);
        return ['success'=>false,'message'=>'Mining belum selesai','remaining'=>$remaining];
    }

    $profit = (float)$pkg['profit_per_day'];
    $newDays = (int)$pkg['days_elapsed'] + 1;
    $newTotal = (float)$pkg['total_profit'] + $profit;

    $status = $newDays >= (int)$pkg['duration_days'] ? 'expired' : 'active';
    $stmt2 = db()->prepare("UPDATE user_packages SET days_elapsed=?,total_profit=?,status=? WHERE id=?");
    $stmt2->bind_param('idsi', $newDays, $newTotal, $status, $packageId);
    $stmt2->execute();
    $stmt2->close();

    // Credit balance
    dbQuery("UPDATE users SET balance=balance+$profit,total_mining=total_mining+$profit WHERE id=$userId");
    $desc = dbEscape('Profit mining: '.$pkg['product_name'].' hari ke-'.$newDays);
    dbQuery("INSERT INTO transactions (user_id,type,amount,description) VALUES ($userId,'mining',$profit,'$desc')");
    dbQuery("INSERT INTO mining_logs (user_id,package_id,profit,status) VALUES ($userId,$packageId,$profit,'success')");

    addNotification($userId, 'Profit Mining Masuk!', 'Rp '.number_format($profit,0,',','.').' dari '.$pkg['product_name'].' berhasil masuk ke saldo.', 'success');
    completeMissionProgress($userId, 'mining');

    return ['success'=>true,'profit'=>$profit,'days'=>$newDays,'status'=>$status];
}

function resetMiningDaily(): void {
    dbQuery("UPDATE user_packages SET mining_today=0 WHERE status='active'");
    // Log skipped mining
    $pkgs = dbQuery("SELECT up.id,up.user_id FROM user_packages up WHERE up.status='active' AND up.mining_today=0 AND DATE(up.last_mining) < CURDATE()");
    if ($pkgs) {
        while ($p = $pkgs->fetch_assoc()) {
            dbQuery("INSERT INTO mining_logs (user_id,package_id,profit,status) VALUES ({$p['user_id']},{$p['id']},0,'skipped')");
        }
    }
}

function getMiningStats(int $userId): array {
    $today = date('Y-m-d');
    $month = date('Y-m');

    $todayProfit = dbQuery("SELECT COALESCE(SUM(profit),0) as total FROM mining_logs WHERE user_id=$userId AND status='success' AND DATE(mined_at)='$today'");
    $monthProfit = dbQuery("SELECT COALESCE(SUM(profit),0) as total FROM mining_logs WHERE user_id=$userId AND status='success' AND DATE_FORMAT(mined_at,'%Y-%m')='$month'");
    $totalProfit = dbQuery("SELECT COALESCE(SUM(profit),0) as total FROM mining_logs WHERE user_id=$userId AND status='success'");

    return [
        'today'  => (float)($todayProfit?->fetch_assoc()['total'] ?? 0),
        'month'  => (float)($monthProfit?->fetch_assoc()['total'] ?? 0),
        'total'  => (float)($totalProfit?->fetch_assoc()['total'] ?? 0),
    ];
}

function getMiningCalendar(int $userId, string $month): array {
    $m = dbEscape($month);
    $logs = dbQuery("SELECT DATE(mined_at) as log_date, status FROM mining_logs WHERE user_id=$userId AND DATE_FORMAT(mined_at,'%Y-%m')='$m'");
    $calendar = [];
    if ($logs) {
        while ($l = $logs->fetch_assoc()) {
            $calendar[$l['log_date']] = $l['status'];
        }
    }
    return $calendar;
}

function getMiningChart(int $userId): array {
    $days = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $r = dbQuery("SELECT COALESCE(SUM(profit),0) as total FROM mining_logs WHERE user_id=$userId AND status='success' AND DATE(mined_at)='$date'");
        $days[] = [
            'date'  => date('d/m', strtotime($date)),
            'total' => (float)($r?->fetch_assoc()['total'] ?? 0)
        ];
    }
    return $days;
}
