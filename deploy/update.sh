#!/bin/bash
# ══════════════════════════════════════════════════════════════
# Script de mise a jour manuelle — LaBioTrack
# Usage : ssh user@vps 'bash /docker/labiotrack-web/code/deploy/update.sh'
# ══════════════════════════════════════════════════════════════
set -e

echo "══════════════════════════════════════"
echo "  LaBioTrack — Mise a jour manuelle"
echo "══════════════════════════════════════"

cd /docker/labiotrack-web/code

# Pull
echo "▶ [1/5] Pull du code..."
git pull origin master

# Composer
echo "▶ [2/5] Composer install..."
sudo docker exec labiotrack-app composer install --no-dev --optimize-autoloader --no-interaction

# Migrations
echo "▶ [3/5] Migrations..."
sudo docker exec labiotrack-app php artisan migrate --force

# Cache
echo "▶ [4/5] Cache Laravel..."
sudo docker exec labiotrack-app php artisan config:cache
sudo docker exec labiotrack-app php artisan route:cache
sudo docker exec labiotrack-app php artisan view:cache
sudo docker exec labiotrack-app php artisan event:cache

# Permissions
echo "▶ [5/5] Permissions..."
sudo docker exec -u root labiotrack-app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Restart
cd /docker/labiotrack-web
sudo docker compose restart app

echo ""
echo "✅ Mise a jour terminee !"
echo "   https://www.labiotrack.tech"
