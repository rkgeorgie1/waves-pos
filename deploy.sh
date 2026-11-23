#!/usr/bin/env bash
set -e

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader

echo "==> Installing JS dependencies and building assets"
npm ci
npm run build

echo "==> Running migrations"
php artisan migrate --force

echo "==> Linking storage"
php artisan storage:link || true

echo "==> Caching config/routes/views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Setting permissions"
chmod -R 775 storage bootstrap/cache

echo "Deployment complete."
