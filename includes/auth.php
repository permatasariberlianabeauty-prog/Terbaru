<?php
// ============================================================
// NOXARA - includes/auth.php
// Compatible: PHP 7.2+
// ============================================================

function loginUser($identifier, $password) {
    $stmt = db()->prepare("SELECT * FROM users WHERE (email=? OR phone=? OR username=?) LIMIT 1");
    $stmt->bind_param('sss', $identifier, $identifier, $identifier);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) return ['success'=>false,'message'=>'Akun tidak ditemukan'];
    if ($user['status'] === 'blocked') return ['success'=>false,'message'=>'Akun kamu diblokir. Hubungi admin.'];
    if ($user['status'] === 'frozen') return ['success'=>false,'message'=>'Akun kamu dibekukan. Hubungi admin.'];
    if (!verifyPassword($password, $user['password'])) return ['success'=>false,'message'=>'Password salah'];

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'];

    $ip = dbEscape(getClientIp());
    $ua = dbEscape(getUserAgent());
    $uid = (int)$user['id'];
    $stmt2 = db()->prepare("INSERT INTO user_login_history (user_id,ip_address,device) VALUES (?,?,?)");
    $stmt2->bind_param('iss', $uid, $ip, $ua);
    $stmt2->execute();
    $stmt2->close();

    completeMissionProgress((int)$user['id'], 'login');

    return ['success'=>true,'user'=>$user];
}

function registerUser($data) {
    $username = dbEscape(strtolower(trim($data['username'])));
    $fullname = dbEscape(trim($data['full_name']));
    $email    = dbEscape(strtolower(trim($data['email'])));
    $phone    = dbEscape(trim($data['phone']));
    $password = hashPassword($data['password']);
    $refCode  = generateReferralCode();
    $refBy    = null;

    $check = db()->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $check->bind_param('ss', $username, $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $check->close();
        return ['success'=>false,'message'=>'Username atau email sudah digunakan'];
    }
    $check->close();

    if (!empty($data['referral_code'])) {
        $rc = dbEscape($data['referral_code']);
        $r = dbQuery("SELECT id FROM users WHERE referral_code='$rc' LIMIT 1");
        if ($r && $r->num_rows > 0) $refBy = (int)$r->fetch_assoc()['id'];
    }

    $bonus = (float)getSetting('register_bonus', '15000');

    if ($refBy) {
        $stmt = db()->prepare("INSERT INTO users (username,full_name,email,phone,password,referral_code,referred_by,balance,bonus_balance) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssidd', $username, $fullname, $email, $phone, $password, $refCode, $refBy, $bonus, $bonus);
    } else {
        $stmt = db()->prepare("INSERT INTO users (username,full_name,email,phone,password,referral_code,balance,bonus_balance) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssdd', $username, $fullname, $email, $phone, $password, $refCode, $bonus, $bonus);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return ['success'=>false,'message'=>'Gagal mendaftar. Coba lagi.'];
    }
    $userId = (int)db()->insert_id;
    $stmt->close();

    $bonusDesc = dbEscape('Bonus registrasi');
    dbQuery("INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,description) VALUES ($userId,'bonus',$bonus,0,$bonus,'$bonusDesc')");

    addNotification($userId, 'Selamat Datang!', 'Akun Noxara kamu berhasil dibuat. Selamat mining!', 'success');

    return ['success'=>true,'user_id'=>$userId];
}

function completeMissionProgress($userId, $type) {
    $userId = (int)$userId;
    $today = date('Y-m-d');
    $t = dbEscape($type);
    $missions = dbQuery("SELECT * FROM daily_missions WHERE type='$t' AND status=1");
    if (!$missions) return;
    while ($m = $missions->fetch_assoc()) {
        $mid = (int)$m['id'];
        $check = db()->prepare("SELECT * FROM user_mission_progress WHERE user_id=? AND mission_id=? AND mission_date=? LIMIT 1");
        $check->bind_param('iis', $userId, $mid, $today);
        $check->execute();
        $prog = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$prog) {
            $stmt = db()->prepare("INSERT INTO user_mission_progress (user_id,mission_id,progress,mission_date) VALUES (?,?,1,?)");
            $stmt->bind_param('iis', $userId, $mid, $today);
            $stmt->execute();
            $stmt->close();
        } elseif (!$prog['completed']) {
            $newProg = (int)$prog['progress'] + 1;
            $pid = (int)$prog['id'];
            if ($newProg >= (int)$m['target']) {
                $now = date('Y-m-d H:i:s');
                dbQuery("UPDATE user_mission_progress SET progress=$newProg,completed=1,completed_at='$now' WHERE id=$pid");
                $reward = (float)$m['reward_amount'];
                if ($reward > 0) {
                    dbQuery("UPDATE users SET balance=balance+$reward WHERE id=$userId");
                    $title = dbEscape($m['title']);
                    addTransaction($userId, 'daily', $reward, 'Reward misi harian: '.$m['title']);
                    addNotification($userId, 'Misi Selesai!', 'Kamu mendapat Rp '.number_format($reward,0,',','.').' dari misi: '.$m['title'], 'success');
                }
            } else {
                dbQuery("UPDATE user_mission_progress SET progress=$newProg WHERE id=$pid");
            }
        }
    }
}
