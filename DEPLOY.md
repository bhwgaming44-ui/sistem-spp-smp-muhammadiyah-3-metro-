# Panduan Publikasi Online — E-SPP SMP Muhammadiyah 3 Metro

## Penting dibaca dulu

Tidak ada "API key" yang bisa membuat aplikasi Laravel + MySQL langsung online.
Agar website bisa dibuka dari HP/laptop lain **tanpa menyalakan server di laptop**,
aplikasi harus dijalankan di **hosting / VPS yang menyala 24 jam**.

API key hanya dipakai untuk login ke layanan hosting (Railway, Render, Hostinger, dll.),
dan itu milik akun hosting Anda masing-masing — bukan sesuatu yang bisa diberikan dari kode aplikasi.

Ada 3 pilihan:

| Pilihan | Biaya | Tanpa laptop menyala | Tingkat kesulitan |
|---|---|---|---|
| Railway (Docker + MySQL) | Free trial, lalu berbayar | Ya | Mudah–sedang |
| Shared hosting cPanel (Hostinger/Niagahoster/InfinityFree) | Berbayar / ada free tier | Ya | Sedang |
| VPS (DigitalOcean/Contabo) | Berbayar | Ya | Sulit |

Rekomendasi untuk demo skripsi: **Railway** (paling cepat) atau **shared hosting** jika sudah punya domain.

---

## Opsi A — Railway (rekomendasi, memakai Dockerfile)

File yang sudah disiapkan di proyek:
- `Dockerfile`
- `docker/entrypoint.sh`
- `.dockerignore`
- `deploy/.env.production.example`

### Langkah

1. **Upload proyek ke GitHub**
   ```bash
   cd spp-app
   git init
   git add .
   git commit -m "E-SPP siap deploy"
   git branch -M main
   git remote add origin https://github.com/USERNAME/spp-app.git
   git push -u origin main
   ```

2. **Buat akun Railway** di https://railway.app (login dengan GitHub).

3. **New Project → Deploy from GitHub repo** → pilih repo `spp-app`.
   Railway otomatis mendeteksi `Dockerfile`.

4. **Tambahkan MySQL**
   - Di project Railway: `New → Database → MySQL`.
   - Buka service MySQL → tab **Variables**, catat:
     `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`.

5. **Set Variables di service aplikasi** (bukan di MySQL):
   ```
   APP_NAME="E-SPP SMP Muhammadiyah 3 Metro"
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:ISI_HASIL_KEY_GENERATE
   APP_URL=https://domain-dari-railway
   APP_TIMEZONE=Asia/Jakarta

   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync

   MIDTRANS_ENABLED=true
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_MERCHANT_ID=YOUR_MERCHANT_ID
   MIDTRANS_CLIENT_KEY=YOUR_MIDTRANS_CLIENT_KEY
   MIDTRANS_SERVER_KEY=YOUR_MIDTRANS_SERVER_KEY
   ```

   Membuat `APP_KEY` (jalankan di laptop):
   ```bash
   C:\xampp\php\php.exe artisan key:generate --show
   ```
   Salin hasil `base64:...` ke variable `APP_KEY`.

6. **Generate domain**
   - Service aplikasi → `Settings → Networking → Generate Domain`.
   - Salin URL (contoh `https://spp-production.up.railway.app`).
   - Update `APP_URL` dengan URL tersebut, lalu redeploy.

7. **Cek**
   - Buka `https://domain-anda/health` → harus muncul `{"status":"ok"}`.
   - Buka `https://domain-anda/` → aplikasi tampil.
   - Di aplikasi, login petugas → menu **🌐 Publikasi Online** → tempel URL → simpan.

8. **Midtrans**
   - Dashboard Midtrans → Settings → Configuration → Payment Notification URL:
     ```
     https://domain-anda/api/midtrans/notification
     ```
   - Untuk transaksi nyata, ganti ke key Production dan set `MIDTRANS_IS_PRODUCTION=true`.

---

## Opsi B — Shared hosting cPanel (Hostinger / Niagahoster / InfinityFree)

Cocok jika Anda sudah punya domain + hosting PHP 8.2 dan MySQL.

1. **Buat database MySQL** di cPanel, catat host/user/password/nama database.
2. **Upload proyek**
   - Upload seluruh folder `spp-app` ke luar `public_html`, misalnya `/home/USER/spp-app`.
   - Arahkan domain/subdomain ke folder `spp-app/public`
     (cPanel → Domains → document root).
   - Jika hosting tidak mengizinkan ubah document root:
     - Pindahkan isi `public/` ke `public_html/`.
     - Edit `public_html/index.php`:
       ```php
       require __DIR__.'/../spp-app/vendor/autoload.php';
       $app = require_once __DIR__.'/../spp-app/bootstrap/app.php';
       ```
3. **Install dependency** (jika hosting menyediakan Terminal/SSH):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   Jika tidak ada SSH, upload folder `vendor/` dari laptop.
4. **Buat `.env`** dari `deploy/.env.production.example`, isi `APP_KEY`, `APP_URL`, dan `DB_*`.
5. **Import database**
   - Buat dump dari laptop:
     ```bash
     C:\xampp\mysql\bin\mysqldump.exe -u root db_spp_smp > deploy\db_spp_smp.sql
     ```
   - Import `deploy/db_spp_smp.sql` lewat phpMyAdmin.
   - Alternatif: jalankan `php artisan migrate --force` lewat Terminal/cron.
6. **Jalankan perintah**
   ```bash
   php artisan storage:link
   php artisan config:cache
   php artisan view:cache
   ```
7. **HTTPS** — aktifkan SSL gratis (Let's Encrypt) di cPanel.
8. **Midtrans notification URL** → `https://domain-anda/api/midtrans/notification`.

---

## Opsi C — VPS (Ubuntu)

Ringkas:
```bash
sudo apt update
sudo apt install nginx php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip mysql-server composer git unzip -y
# clone / upload proyek, lalu:
composer install --no-dev --optimize-autoloader
cp deploy/.env.production.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan view:cache
```
Arahkan Nginx `root` ke `/var/www/spp-app/public`, lalu pasang Certbot untuk HTTPS.

---

## Checklist setelah online

- [ ] `https://domain-anda/health` mengembalikan `status: ok`
- [ ] Halaman utama terbuka dari HP
- [ ] Login petugas berhasil
- [ ] Menu **Publikasi Online** menampilkan status **Online**
- [ ] Database terhubung (status di menu Publikasi)
- [ ] `APP_DEBUG=false` dan `APP_ENV=production`
- [ ] HTTPS aktif
- [ ] Webhook Midtrans sudah diarahkan ke domain baru
- [ ] `APP_URL` sama dengan domain yang dipakai

---

## Peringatan keamanan sebelum publik

1. **Login aplikasi masih demo di browser (localStorage).** Siapa pun yang tahu kredensial demo bisa membuka menu petugas. Untuk produksi, ganti dengan autentikasi Laravel di server.
2. **Server Key Midtrans saat ini ikut dikirim ke browser** lewat `/api/midtrans/config`. Untuk produksi, hapus `server_key` dari response dan simpan hanya di server.
3. Jangan commit `.env` ke GitHub.
4. Gunakan key **Production** Midtrans hanya setelah akun production aktif.
