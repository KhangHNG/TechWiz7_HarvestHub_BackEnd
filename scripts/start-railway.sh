#!/bin/sh
set -e

php artisan filament:assets --ansi
php artisan migrate --force --no-interaction

(
  while true; do
    php artisan queue:work --sleep=3 --tries=3 --max-time=3600 || true
  done
) &
WORKER_PID=$!

cleanup() {
  kill "$WORKER_PID" 2>/dev/null || true
  wait "$WORKER_PID" 2>/dev/null || true
}

trap cleanup TERM INT

php artisan serve --host=0.0.0.0 --port="${PORT:-8080}" --no-reload
cleanup
