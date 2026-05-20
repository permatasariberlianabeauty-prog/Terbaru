# NOXARA - Panduan Setup VPS aaPanel

## 1. Upload File
Upload semua file ke: `/www/wwwroot/noxara.page/`

## 2. Database
- Buat database: `noxara_Oke12`
- Buat user: `noxara_Oke11` dengan password: `Jakakece12`
- Import: `database/dashboard.sql`

## 3. Konfigurasi
Edit `config/config.php` jika perlu mengubah:
- Database credentials
- APP_URL
- Cashify API credentials

## 4. Nginx aaPanel
- Gunakan `nginx.conf` sebagai referensi konfigurasi
- Atau biarkan aaPanel generate otomatis, lalu tambahkan rules dari nginx.conf

## 5. Cron Job
Tambahkan ke crontab aaPanel:
```
1 0 * * * /usr/bin/php /www/wwwroot/noxara.page/cron/mining_reset.php >> /www/wwwlogs/noxara_cron.log 2>&1
```

## 6. Permission
```bash
chmod 755 /www/wwwroot/noxara.page
chmod 777 /www/wwwroot/noxara.page/uploads
chmod 777 /www/wwwroot/noxara.page/logs
```

## 7. Admin Login
- URL: https://noxara.page/adm-noxara/login.php
- Username: Jaka17
- Password: Jakakece (SEGERA GANTI setelah login pertama!)

## 8. GANTI PASSWORD ADMIN!
Setelah login pertama, segera ganti password di:
Admin Panel > Pengaturan > Keamanan > Ganti Password Admin
