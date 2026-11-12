#!/usr/bin/env bash
set -e
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:cache --env=production 2>/dev/null || true
php artisan route:cache --env=production 2>/dev/null || true
php artisan view:cache --env=production 2>/dev/null || true
