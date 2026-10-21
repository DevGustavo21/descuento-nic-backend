#!/bin/sh
set -e

php artisan config:clear || true
php artisan storage:link || true
php artisan migrate --force

# Seed demo data only when explicitly enabled (seeder is not idempotent)
if [ "${SEED_ON_DEPLOY}" = "true" ]; then
    php artisan db:seed --force || true
fi

php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
