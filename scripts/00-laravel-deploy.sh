#!/usr/bin/env bash
php artisan config:cache
php artisan route:cache
php artisan migrate --force