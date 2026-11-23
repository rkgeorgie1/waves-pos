$ErrorActionPreference = "Stop"

Write-Host "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader

Write-Host "==> Installing JS dependencies and building assets"
npm ci
npm run build

Write-Host "==> Running migrations"
php artisan migrate --force

Write-Host "==> Linking storage"
php artisan storage:link

Write-Host "==> Caching config/routes/views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "Deployment complete."
