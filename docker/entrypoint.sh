#!/bin/sh
set -e

# Railway/Render memberi variabel PORT. Apache default 80.
PORT="${PORT:-8080}"

sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf || true
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf || true

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY belum di-set. Set APP_KEY di environment hosting."
fi

# Storage link untuk file publik
php artisan storage:link >/dev/null 2>&1 || true

# Tunggu database siap, lalu jalankan migrasi
i=0
until php artisan migrate --force; do
    i=$((i + 1))
    if [ "$i" -ge 10 ]; then
        echo "WARNING: migrasi gagal setelah 10 percobaan. Aplikasi tetap dijalankan."
        break
    fi
    echo "Menunggu database siap... ($i/10)"
    sleep 3
done

# Cache konfigurasi & view (route:cache tidak dipakai karena ada route closure)
php artisan config:cache || true
php artisan view:cache || true

exec apache2-foreground
