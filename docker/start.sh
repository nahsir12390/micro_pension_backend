#!/usr/bin/env bash
set -e

php artisan config:clear
php artisan migrate --force

if [ "${SEED_DATABASE:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache

exec apache2-foreground
