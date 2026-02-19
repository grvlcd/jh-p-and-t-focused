#!/usr/bin/env bash
set -e

php artisan config:cache
php artisan route:cache
php artisan migrate --force

exec /start.sh