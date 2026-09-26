#!/bin/sh
set -e

php artisan filament:assets --ansi
php artisan migrate --force --no-interaction
php artisan serve --host=0.0.0.0 --port="${PORT:-8080}" --no-reload
