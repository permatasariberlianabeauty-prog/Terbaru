-- ============================================================
-- NOXARA - Database SQL
-- ENGINE=InnoDB, utf8mb4_unicode_ci
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pin` varchar(255) DEFAULT NULL,
  `referral_code` varchar(20) NOT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `vip_level` tinyint(1) NOT NULL DEFAULT 0,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bonus_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_deposit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_withdraw` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_mining` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_referral` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','blocked','frozen') NOT NULL DEFAULT 'active',
  `bank_changes` tinyint(1) NOT NULL DEFAULT 0,
  `lang` varchar(5) NOT NULL DEFAULT 'id',
  `theme` varchar(10) NOT NULL DEFAULT 'dark',
  `avatar` varchar(10) NOT NULL DEFAULT '1',
  `login_streak` int(11) NOT NULL DEFAULT 0,
  `last_checkin` date DEFAULT NULL,
  `last_spin` date DEFAULT NULL,
  `onboarding_done` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `referral_code` (`referral_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: user_banks
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_banks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: user_login_history
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` enum('starter','growth','elite') NOT NULL DEFAULT 'starter',
  `name` varchar(50) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `profit_per_day` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `badge` varchar(20) DEFAULT NULL,
  `quota` int(11) NOT NULL DEFAULT 0,
  `quota_used` int(11) NOT NULL DEFAULT 0,
  `rating` decimal(3,1) NOT NULL DEFAULT 4.5,
  `total_buyers` int(11) NOT NULL DEFAULT 0,
  `max_buy` int(11) NOT NULL DEFAULT 0,
  `flash_sale` tinyint(1) NOT NULL DEFAULT 0,
  `flash_price` decimal(15,2) DEFAULT NULL,
  `flash_start` datetime DEFAULT NULL,
  `flash_end` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: user_packages
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `buy_price` decimal(15,2) NOT NULL,
  `profit_per_day` decimal(15,2) NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `days_elapsed` int(11) NOT NULL DEFAULT 0,
  `total_profit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `last_mining` datetime DEFAULT NULL,
  `mining_today` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','expired') NOT NULL DEFAULT 'active',
  `purchased_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expired_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: mining_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `mining_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `profit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('success','skipped') NOT NULL DEFAULT 'success',
  `mined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: deposits
-- ============================================================
CREATE TABLE IF NOT EXISTS `deposits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `original_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `unique_nominal` int(11) NOT NULL DEFAULT 0,
  `voucher_code` varchar(50) DEFAULT NULL,
  `voucher_discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bonus_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qr_string` text DEFAULT NULL,
  `status` enum('pending','paid','expired','cancel') NOT NULL DEFAULT 'pending',
  `expired_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: withdrawals
-- ============================================================
CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_received` decimal(15,2) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `status` enum('pending','processing','success','rejected') NOT NULL DEFAULT 'pending',
  `reject_reason` text DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdraw','mining','referral','bonus','voucher','purchase','daily') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `status` enum('success','pending','failed') NOT NULL DEFAULT 'success',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: vouchers
-- ============================================================
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('deposit','product','balance') NOT NULL DEFAULT 'balance',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `bonus_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `min_vip` tinyint(1) NOT NULL DEFAULT 0,
  `limit_total` int(11) NOT NULL DEFAULT 1,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `expired_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: voucher_uses
-- ============================================================
CREATE TABLE IF NOT EXISTS `voucher_uses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `used_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: vip_settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `vip_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` tinyint(1) NOT NULL,
  `name` varchar(30) NOT NULL,
  `min_deposit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `min_withdraw` decimal(15,2) NOT NULL DEFAULT 100000.00,
  `withdraw_fee_percent` decimal(5,2) NOT NULL DEFAULT 15.00,
  `daily_withdraw_limit` decimal(15,2) NOT NULL DEFAULT 5000000.00,
  `referral_deposit_l1` decimal(5,2) NOT NULL DEFAULT 10.00,
  `referral_deposit_l2` decimal(5,2) NOT NULL DEFAULT 5.00,
  `referral_deposit_l3` decimal(5,2) NOT NULL DEFAULT 2.00,
  `referral_product_l1` decimal(5,2) NOT NULL DEFAULT 10.00,
  `referral_product_l2` decimal(5,2) NOT NULL DEFAULT 4.00,
  `referral_product_l3` decimal(5,2) NOT NULL DEFAULT 1.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','promo','system') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_broadcast` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: live_chats
-- ============================================================
CREATE TABLE IF NOT EXISTS `live_chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sender` enum('user','admin') NOT NULL DEFAULT 'user',
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: chat_ratings
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: banners
-- ============================================================
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `color_from` varchar(20) NOT NULL DEFAULT '#FFD700',
  `color_to` varchar(20) NOT NULL DEFAULT '#FFA500',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: daily_checkins
-- ============================================================
CREATE TABLE IF NOT EXISTS `daily_checkins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `day_number` int(11) NOT NULL DEFAULT 1,
  `reward_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `checkin_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: spin_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `spin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reward_type` enum('balance','voucher','mining_bonus') NOT NULL DEFAULT 'balance',
  `reward_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reward_desc` varchar(100) DEFAULT NULL,
  `spin_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: daily_missions
-- ============================================================
CREATE TABLE IF NOT EXISTS `daily_missions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `type` enum('mining','deposit','referral','login','purchase') NOT NULL DEFAULT 'login',
  `target` int(11) NOT NULL DEFAULT 1,
  `reward_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: user_mission_progress
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_mission_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `mission_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: product_favorites
-- ============================================================
CREATE TABLE IF NOT EXISTS `product_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: product_bundles
-- ============================================================
CREATE TABLE IF NOT EXISTS `product_bundles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id_1` int(11) NOT NULL,
  `product_id_2` int(11) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 5.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admins
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admin_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: quick_replies
-- ============================================================
CREATE TABLE IF NOT EXISTS `quick_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(200) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: events
-- ============================================================
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: achievements
-- ============================================================
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `type` enum('mining','referral','deposit','streak') NOT NULL DEFAULT 'mining',
  `target` int(11) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: user_achievements
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_ach` (`user_id`,`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA: admins
-- Password: Jakakece (hashed)
-- ============================================================
INSERT IGNORE INTO `admins` (`username`, `password`) VALUES
('Jaka17', '$2y$12$/OEZEcTuu5Jd7yt9peqRb./14cEVc7e90SrNgBO2SQiDe6Sf4Sdnu');

-- ============================================================
-- SEED DATA: vip_settings
-- ============================================================
INSERT IGNORE INTO `vip_settings` (`level`,`name`,`min_deposit`,`min_withdraw`,`withdraw_fee_percent`,`daily_withdraw_limit`,`referral_deposit_l1`,`referral_deposit_l2`,`referral_deposit_l3`,`referral_product_l1`,`referral_product_l2`,`referral_product_l3`) VALUES
(0,'VIP 0',0.00,100000.00,15.00,5000000.00,10.00,5.00,2.00,10.00,4.00,1.00),
(1,'VIP 1',30000.00,50000.00,5.00,10000000.00,10.00,5.00,2.00,10.00,4.00,1.00),
(2,'VIP 2',500000.00,30000.00,5.00,20000000.00,10.00,5.00,2.00,10.00,4.00,1.00),
(3,'VIP 3',1000000.00,0.00,0.00,99999999.00,10.00,5.00,2.00,10.00,4.00,1.00);

-- ============================================================
-- SEED DATA: settings
-- ============================================================
INSERT IGNORE INTO `settings` (`key_name`, `value`) VALUES
('site_name','Noxara'),
('site_tagline','Invest Smarter, Grow Faster'),
('site_url','https://noxara.page'),
('maintenance_mode','0'),
('maintenance_message','Sedang dalam perbaikan. Tunggu sebentar ya!'),
('maintenance_estimate','2 jam'),
('register_bonus','15000'),
('min_deposit','10000'),
('max_deposit','100000000'),
('deposit_bonus_percent','0'),
('deposit_bonus_status','0'),
('min_saldo_tersisa','0'),
('mining_reset_time','00:01'),
('mining_countdown_hours','2'),
('wa_group_link','https://chat.whatsapp.com/'),
('wa_admin','628000000000'),
('wa_official','628000000000'),
('email_admin','admin@noxara.page'),
('app_download_link','https://noxara.page'),
('cashify_license_key','cashify_261885e5c5f830e68f929de05e3bfdf72e118d859edc5419472f79a813eed3ea'),
('cashify_qris_id','1b935c41-bf43-4075-8f57-56b6cbfa2d07'),
('admin_status','online'),
('running_text','Selamat datang di Noxara! Platform mining rupiah terpercaya.'),
('leaderboard_status','1'),
('referral_status','1'),
('spin_status','1'),
('checkin_status','1'),
('daily_withdraw_per_user','5000000'),
('popup_welcome_status','1'),
('popup_welcome_text','Selamat datang di Noxara! Mulai mining dan raih penghasilan setiap hari.');

-- ============================================================
-- SEED DATA: products (Starter)
-- ============================================================
INSERT INTO `products` (`category`,`name`,`price`,`duration_days`,`profit_per_day`,`description`,`badge`,`quota`,`quota_used`,`rating`,`total_buyers`,`max_buy`,`status`,`sort_order`) VALUES
('starter','Flash',10000.00,30,500.00,'Paket pemula terjangkau untuk memulai perjalanan investasimu.','Populer',1000,247,4.5,1250,3,1,1),
('starter','Spark',25000.00,30,1300.00,'Tingkatkan penghasilan harianmu dengan paket Spark.','',1000,189,4.6,980,3,1,2),
('starter','Boost',50000.00,30,2700.00,'Paket Boost memberikan profit lebih besar setiap hari.','Best Seller',500,312,4.7,1560,3,1,3),
('starter','Surge',100000.00,30,5500.00,'Paket Surge untuk investor yang ingin hasil lebih optimal.','',500,156,4.8,780,3,1,4),
('starter','Blaze',250000.00,30,14000.00,'Paket Blaze dengan profit harian tinggi dan stabil.','',200,89,4.9,445,3,1,5);

-- ============================================================
-- SEED DATA: products (Growth)
-- ============================================================
INSERT INTO `products` (`category`,`name`,`price`,`duration_days`,`profit_per_day`,`description`,`badge`,`quota`,`quota_used`,`rating`,`total_buyers`,`max_buy`,`status`,`sort_order`) VALUES
('growth','Rise',500000.00,30,29000.00,'Mulai perjalanan growth investasimu bersama Noxara.','',200,67,4.7,335,3,1,1),
('growth','Climb',1000000.00,30,60000.00,'Paket Climb untuk investor serius yang ingin berkembang.','Populer',100,78,4.8,390,3,1,2),
('growth','Ascend',2500000.00,30,155000.00,'Naikan level investasimu dengan paket Ascend premium.','Best Seller',50,34,4.9,170,3,1,3),
('growth','Elevate',5000000.00,30,315000.00,'Investasi tingkat lanjut dengan return maksimal.','',30,19,4.9,95,3,1,4),
('growth','Apex',10000000.00,30,640000.00,'Puncak paket Growth dengan profit terbaik.','',20,12,5.0,60,3,1,5);

-- ============================================================
-- SEED DATA: products (Elite)
-- ============================================================
INSERT INTO `products` (`category`,`name`,`price`,`duration_days`,`profit_per_day`,`description`,`badge`,`quota`,`quota_used`,`rating`,`total_buyers`,`max_buy`,`status`,`sort_order`) VALUES
('elite','Crown',25000000.00,30,1625000.00,'Paket Crown eksklusif untuk investor elite Noxara.','Eksklusif',10,5,5.0,25,3,1,1),
('elite','Legend',50000000.00,30,3300000.00,'Jadilah legenda investasi bersama Noxara.','',5,3,5.0,15,3,1,2),
('elite','Titan',100000000.00,30,6700000.00,'Paket Titan untuk investor kelas atas.','',5,2,5.0,10,3,1,3),
('elite','Nexus',250000000.00,30,17000000.00,'Nexus - koneksi investasi terbesar.','',3,1,5.0,5,3,1,4),
('elite','Noxara',500000000.00,30,34500000.00,'Paket tertinggi bearing nama Noxara sendiri.','Langka',2,1,5.0,3,3,1,5);


-- ============================================================
-- SEED DATA: banners
-- ============================================================
INSERT INTO `banners` (`title`,`subtitle`,`color_from`,`color_to`,`sort_order`,`status`) VALUES
('Mining Rupiah Setiap Hari','Klik tombol mining & raih profit otomatis','#FFD700','#FF8C00',1,1),
('Bonus Referral 3 Level','Ajak teman & dapat komisi hingga 10%','#00D4FF','#0066FF',2,1),
('VIP Eksklusif Menanti','Upgrade VIP & nikmati keuntungan premium','#9D4EDD','#FF006E',3,1);

-- ============================================================
-- SEED DATA: daily_missions
-- ============================================================
INSERT INTO `daily_missions` (`title`,`description`,`type`,`target`,`reward_amount`,`status`) VALUES
('Login Harian','Login ke akun Noxara hari ini','login',1,500.00,1),
('Mining Hari Ini','Lakukan mining minimal 1 produk','mining',1,1000.00,1),
('Ajak Teman','Bagikan link referral kamu','referral',1,2000.00,1),
('Beli Produk','Beli minimal 1 produk investasi','purchase',1,5000.00,1),
('Deposit Hari Ini','Lakukan isi ulang hari ini','deposit',1,3000.00,1);

-- ============================================================
-- SEED DATA: quick_replies
-- ============================================================
INSERT INTO `quick_replies` (`question`,`answer`,`sort_order`,`status`) VALUES
('Cara Deposit','Untuk deposit, buka menu Isi Ulang di Home, masukkan nominal, scan QRIS dan bayar. Saldo otomatis masuk dalam beberapa detik.',1,1),
('Cara Withdraw','Untuk withdraw, pastikan sudah daftarkan akun bank di Profil, lalu buka menu Penarikan dan masukkan nominal.',2,1),
('Cara Mining','Buka menu Mining, klik tombol Mining pada produk yang aktif. Mining bisa dilakukan 1x sehari dan profit masuk 2 jam setelah mining.',3,1),
('Minimal Penarikan','Minimal penarikan tergantung level VIP kamu. VIP 0: Rp 100.000, VIP 1: Rp 50.000, VIP 2: Rp 30.000, VIP 3: Tidak ada minimal.',4,1),
('Cara Upgrade VIP','VIP naik otomatis sesuai total deposit kamu. VIP 1: deposit Rp 30.000, VIP 2: deposit Rp 500.000, VIP 3: deposit Rp 1.000.000.',5,1);

-- ============================================================
-- SEED DATA: achievements
-- ============================================================
INSERT INTO `achievements` (`title`,`description`,`icon`,`type`,`target`,`status`) VALUES
('Mining Pemula','Mining pertama kali','award','mining',1,1),
('Mining 7 Hari','Mining 7 hari berturut-turut','zap','streak',7,1),
('Mining 30 Hari','Mining 30 hari berturut-turut','star','streak',30,1),
('Referral Pertama','Berhasil mengajak 1 teman','users','referral',1,1),
('Referral Pro','Berhasil mengajak 10 teman','crown','referral',10,1),
('Deposit Pertama','Melakukan deposit pertama','dollar-sign','deposit',1,1),
('Investor Aktif','Total deposit Rp 1.000.000','trending-up','deposit',1000000,1);

SET FOREIGN_KEY_CHECKS=1;
