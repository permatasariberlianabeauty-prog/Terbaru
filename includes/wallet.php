<?php
// ============================================================
// NOXARA - includes/wallet.php
// Compatible: PHP 7.2+
// ============================================================

function creditBalance($userId, $amount, $type, $desc = '', $refId = 0) {
    $userId = (int)$userId;
    $amount = (float)$amount;
    $stmt = db()->prepare("UPDATE users SET balance=balance+? WHERE id=?");
    $stmt->bind_param('di', $amount, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        $user = getUserById($userId);
        $bal = (float)($user['balance'] ?? 0);
        $before = $bal - $amount;
        $ins = db()->prepare("INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,description,ref_id) VALUES (?,?,?,?,?,?,?)");
        $ins->bind_param('isdddsi', $userId, $type, $amount, $before, $bal, $desc, $refId);
        $ins->execute();
        $ins->close();
    }
    return $ok;
}

function debitBalance($userId, $amount, $type, $desc = '', $refId = 0) {
    $userId = (int)$userId;
    $amount = (float)$amount;
    $user = getUserById($userId);
    if (!$user || (float)$user['balance'] < $amount) return false;
    $stmt = db()->prepare("UPDATE users SET balance=balance-? WHERE id=? AND balance>=?");
    $stmt->bind_param('did', $amount, $userId, $amount);
    $ok = $stmt->execute() && db()->affected_rows > 0;
    $stmt->close();
    if ($ok) {
        $newBal = (float)$user['balance'] - $amount;
        $ins = db()->prepare("INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,description,ref_id,status) VALUES (?,?,?,?,?,?,?,'success')");
        $neg = -$amount;
        $ins->bind_param('isddds i', $userId, $type, $neg, $user['balance'], $newBal, $desc, $refId);
        $ins->execute();
        $ins->close();
    }
    return $ok;
}

function deductBonusBalance($userId, $amount) {
    $userId = (int)$userId;
    $amount = (float)$amount;
    $stmt = db()->prepare("UPDATE users SET bonus_balance=GREATEST(0,bonus_balance-?) WHERE id=?");
    $stmt->bind_param('di', $amount, $userId);
    $stmt->execute();
    $stmt->close();
}

function hasBonusBalance($userId) {
    $userId = (int)$userId;
    $stmt = db()->prepare("SELECT bonus_balance FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float)($row['bonus_balance'] ?? 0) > 0;
}

function processReferralCommission($userId, $amount, $type) {
    $userId = (int)$userId;
    $amount = (float)$amount;
    $stmt = db()->prepare("SELECT id,referred_by,vip_level FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user || !$user['referred_by']) return;

    $levels = [1 => (int)$user['referred_by']];
    $s2 = db()->prepare("SELECT referred_by FROM users WHERE id=? LIMIT 1");
    $s2->bind_param('i', $levels[1]);
    $s2->execute();
    $r2 = $s2->get_result()->fetch_assoc();
    $s2->close();
    if ($r2 && $r2['referred_by']) {
        $levels[2] = (int)$r2['referred_by'];
        $s3 = db()->prepare("SELECT referred_by FROM users WHERE id=? LIMIT 1");
        $s3->bind_param('i', $levels[2]);
        $s3->execute();
        $r3 = $s3->get_result()->fetch_assoc();
        $s3->close();
        if ($r3 && $r3['referred_by']) $levels[3] = (int)$r3['referred_by'];
    }

    foreach ($levels as $lvl => $uplineId) {
        $upline = getUserById($uplineId);
        if (!$upline) continue;
        $vipInfo = getVipInfo((int)$upline['vip_level']);

        $pct = 0;
        if ($type === 'deposit') {
            if ($lvl === 1) $pct = (float)$vipInfo['referral_deposit_l1'];
            elseif ($lvl === 2) $pct = (float)$vipInfo['referral_deposit_l2'];
            elseif ($lvl === 3) $pct = (float)$vipInfo['referral_deposit_l3'];
        } else {
            if ($lvl === 1) $pct = (float)$vipInfo['referral_product_l1'];
            elseif ($lvl === 2) $pct = (float)$vipInfo['referral_product_l2'];
            elseif ($lvl === 3) $pct = (float)$vipInfo['referral_product_l3'];
        }

        if ($pct <= 0) continue;
        $commission = round($amount * $pct / 100, 2);
        if ($commission <= 0) continue;
        dbQuery("UPDATE users SET balance=balance+$commission,total_referral=total_referral+$commission WHERE id=$uplineId");
        $uname = dbEscape($upline['username'] ?? '');
        $desc = dbEscape("Rabat ".($type==='deposit'?'isi ulang':'transaksi')." level $lvl dari $uname");
        addNotification($uplineId, 'Komisi Referral Masuk!', "Kamu mendapat komisi Rp ".number_format($commission,0,',','.')." (level $lvl)", 'success');
        $ins = db()->prepare("INSERT INTO transactions (user_id,type,amount,description) VALUES (?,'referral',?,?)");
        $ins->bind_param('ids', $uplineId, $commission, $desc);
        $ins->execute();
        $ins->close();
    }
}

function checkAndUpgradeVip($userId) {
    $userId = (int)$userId;
    $user = getUserById($userId);
    if (!$user) return;
    $totalDep = (float)$user['total_deposit'];
    $currentVip = (int)$user['vip_level'];

    $vips = dbQuery("SELECT * FROM vip_settings WHERE level > 0 ORDER BY level DESC");
    if (!$vips) return;
    while ($v = $vips->fetch_assoc()) {
        if ($totalDep >= (float)$v['min_deposit'] && $currentVip < (int)$v['level']) {
            $newVip = (int)$v['level'];
            dbQuery("UPDATE users SET vip_level=$newVip WHERE id=$userId");
            addNotification($userId, 'Selamat! Naik VIP '.$newVip, 'Kamu berhasil upgrade ke '.$v['name'].'! Nikmati keuntungan eksklusifnya.', 'success');
            break;
        }
    }
}

function generateQris($amount, $voucherCode = '') {
    $amount = (float)$amount;
    $discount = 0;
    $voucherId = 0;

    if ($voucherCode) {
        $vc = dbEscape($voucherCode);
        $r = dbQuery("SELECT * FROM vouchers WHERE code='$vc' AND type='deposit' AND status=1 AND (expired_at IS NULL OR expired_at > NOW()) LIMIT 1");
        if ($r && $r->num_rows > 0) {
            $v = $r->fetch_assoc();
            if ((int)$v['used_count'] < (int)$v['limit_total']) {
                $discount = round($amount * (float)$v['discount_percent'] / 100, 2);
                $voucherId = (int)$v['id'];
            }
        }
    }

    $finalAmount = max(1000, $amount - $discount);
    $payload = json_encode([
        'id' => CASHIFY_QRIS_ID,
        'amount' => (int)$finalAmount,
        'useUniqueCode' => true,
        'packageIds' => [CASHIFY_PACKAGE_ID],
        'expiredInMinutes' => CASHIFY_EXPIRED_MINUTES
    ]);

    $ch = curl_init(CASHIFY_BASE_URL . '/qris');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-license-key: ' . CASHIFY_LICENSE_KEY
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['success'=>false,'message'=>'Gagal generate QRIS: '.$err];
    $data = json_decode($res, true);
    if (!$data || ($data['status'] ?? 0) !== 200) return ['success'=>false,'message'=>'Gagal generate QRIS dari server'];

    return ['success'=>true,'data'=>$data['data'],'discount'=>$discount,'voucher_id'=>$voucherId,'original_amount'=>$amount,'final_amount'=>$finalAmount];
}

function checkPaymentStatus($transactionId) {
    $payload = json_encode(['transactionId' => $transactionId]);
    $ch = curl_init(CASHIFY_BASE_URL . '/check-status');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json','x-license-key: '.CASHIFY_LICENSE_KEY],
        CURLOPT_TIMEOUT => 15
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    return isset($data['data']) ? $data['data'] : [];
}

function cancelPayment($transactionId) {
    $payload = json_encode(['transactionId' => $transactionId]);
    $ch = curl_init(CASHIFY_BASE_URL . '/cancel-status');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json','x-license-key: '.CASHIFY_LICENSE_KEY],
        CURLOPT_TIMEOUT => 15
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    return ($data['status'] ?? 0) === 200;
}
