#!/bin/bash
# ══════════════════════════════════════════════════════════════
# Script de déploiement — LaBioTrack (labiotrack.tech)
# Hostinger VPS / Ubuntu 22.04+
# ══════════════════════════════════════════════════════════════
set -e

APP_DIR="/var/www/labiotrack-web"
REPO_BRANCH="main"
DB_NAME="labiotrack_db"
DB_USER="labiotrack_user"

echo "══════════════════════════════════════════════════════════"
echo "  LaBioTrack — Déploiement sur Hostinger VPS"
echo "══════════════════════════════════════════════════════════"

# ── 1. Paquets système ────────────────────────────────────────
echo ""
echo "▶ [1/9] Installation des paquets système..."
sudo apt update && sudo apt upgrade -y
sudo apt install -y \
    nginx \
    mysql-server \
    php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath \
    php8.2-readline php8.2-dom \
    git unzip curl certbot python3-certbot-nginx

# ── 2. Composer ───────────────────────────────────────────────
echo ""
echo "▶ [2/9] Installation de Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# ── 3. Node.js (LTS) ─────────────────────────────────────────
echo ""
echo "▶ [3/9] Installation de Node.js LTS..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
fi

# ── 4. Base de données MySQL ──────────────────────────────────
echo ""
echo "▶ [4/9] Configuration de MySQL..."
echo "   ⚠  Entrez un mot de passe fort pour l'utilisateur $DB_USER :"
read -s -p "   Mot de passe DB: " DB_PASS
echo ""

sudo mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Importer la base si le fichier SQL existe
if [ -f "$APP_DIR/biomed_db.sql" ]; then
    echo "   ▶ Import de biomed_db.sql..."
    sudo mysql "$DB_NAME" < "$APP_DIR/biomed_db.sql"
    echo "   ✓ Base importée."
fi

# ── 5. Déployer le code ──────────────────────────────────────
echo ""
echo "▶ [5/9] Déploiement du code dans $APP_DIR..."
sudo mkdir -p "$APP_DIR"
sudo chown -R $USER:www-data "$APP_DIR"

if [ -d "$APP_DIR/.git" ]; then
    cd "$APP_DIR"
    git pull origin "$REPO_BRANCH"
else
    echo "   ⚠  Copiez le projet dans $APP_DIR ou clonez votre dépôt Git."
    echo "   Ex: git clone git@github.com:votre-user/labiotrack-web.git $APP_DIR"
    echo "   Puis relancez ce script."
    exit 1
fi

# ── 6. Dépendances Laravel ───────────────────────────────────
echo ""
echo "▶ [6/9] Installation des dépendances..."
cd "$APP_DIR"

# Copier .env.production → .env
cp .env.production .env

# Remplacer le mot de passe DB dans .env
sed -i "s/CHANGE_ME_STRONG_PASSWORD/$DB_PASS/" .env

# Générer la clé applicative
composer install --no-dev --optimize-autoloader
php artisan key:generate --force

# Assets front
npm ci --production=false
npm run build

# ── 7. Permissions Laravel ───────────────────────────────────
echo ""
echo "▶ [7/9] Permissions des dossiers..."
sudo chown -R $USER:www-data "$APP_DIR"
sudo chmod -R 775 storage bootstrap/cache
php artisan storage:link

# ── 8. Optimisations Laravel (production) ────────────────────
echo ""
echo "▶ [8/9] Optimisations Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ── 9. Nginx + SSL ───────────────────────────────────────────
echo ""
echo "▶ [9/9] Configuration Nginx + SSL..."
sudo cp deploy/nginx/labiotrack.tech.conf /etc/nginx/sites-available/labiotrack.tech
sudo ln -sf /etc/nginx/sites-available/labiotrack.tech /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

# SSL Let's Encrypt (première fois uniquement)
if [ ! -d "/etc/letsencrypt/live/labiotrack.tech" ]; then
    echo "   ▶ Génération du certificat SSL..."
    sudo certbot --nginx -d labiotrack.tech -d www.labiotrack.tech --non-interactive --agree-tos -m contact@labiotrack.tech
fi

sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

echo ""
echo "══════════════════════════════════════════════════════════"
echo "  ✓ Déploiement terminé !"
echo "  ✓ https://www.labiotrack.tech"
echo "══════════════════════════════════════════════════════════"
echo ""
echo "  ⚠  N'oubliez pas de :"
echo "     1. Modifier MAIL_PASSWORD dans .env"
echo "     2. Pointer le DNS de labiotrack.tech → IP du VPS"
echo "     3. Tester : curl -I https://www.labiotrack.tech"
echo ""
