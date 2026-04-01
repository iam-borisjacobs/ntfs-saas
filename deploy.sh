#!/bin/bash
# Zero-Downtime Deployment Script for NTFS
# Usage: ./deploy.sh [environment] (e.g., ./deploy.sh production)

set -e

# Configuration
ENV_FILE=".env"
APP_DIR="/var/www/ntfs"
REPO_URL="https://github.com/organization/ntfs.git"
BRANCH="main"

echo "============================================="
echo " Starting NTFS Generic Deployment Strategy   "
echo "============================================="

if [ ! -d "$APP_DIR/releases" ]; then
    echo "Creating directory structure..."
    mkdir -p $APP_DIR/releases
    mkdir -p $APP_DIR/shared/storage
fi

# 1. Clone new release
TIME=$(date +"%Y%m%d%H%M%S")
RELEASE_DIR="$APP_DIR/releases/$TIME"
echo "-> Cloning into $RELEASE_DIR"
git clone --depth 1 -b $BRANCH $REPO_URL $RELEASE_DIR

# 2. Shared Environment & Storage
echo "-> Linking storage and environment..."
ln -nfs $APP_DIR/shared/storage $RELEASE_DIR/storage
ln -nfs $APP_DIR/shared/.env $RELEASE_DIR/.env

# 3. Build & Install
echo "-> Installing dependencies..."
cd $RELEASE_DIR
composer install --no-dev --optimize-autoloader --quiet
npm install --silent
npm run build --silent

# 4. Migrate database
echo "-> Migrating database..."
php artisan migrate --force

# 5. Optimize framework
echo "-> Caching configuration..."
php artisan optimize

# 6. Activate Release (Symlink Swap)
echo "-> Swapping symlinks (Zero-Downtime)..."
ln -nfs $RELEASE_DIR $APP_DIR/current

# 7. Reload FPM (Example path, adjust for your server)
echo "-> Reloading PHP-FPM..."
# sudo systemctl reload php8.2-fpm

# 8. Cleanup old releases (keep last 5)
echo "-> Cleaning up old releases..."
ls -dt $APP_DIR/releases/* | tail -n +6 | xargs -d '\n' rm -rf

echo "============================================="
echo " Deployment Completed Successfully!          "
echo "============================================="
