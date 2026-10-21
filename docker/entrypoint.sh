#!/bin/sh
set -e

php artisan config:clear || true
php artisan migrate --force
php artisan db:seed --force || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
