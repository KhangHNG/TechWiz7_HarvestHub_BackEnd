#!/bin/sh
set -eu

cd "$(dirname "$0")/.."

# Railway MySQL injects MYSQLHOST / MYSQL_URL. Laravel ignores those while
# DB_CONNECTION is empty and falls back to sqlite, so migrate never reaches MySQL.
if [ -z "${DB_CONNECTION:-}" ]; then
  if [ -n "${MYSQL_URL:-}" ] || [ -n "${MYSQLHOST:-}" ] || [ -n "${MYSQL_HOST:-}" ]; then
    export DB_CONNECTION=mysql
    echo "DB_CONNECTION was empty; using mysql from Railway MySQL variables."
  fi
fi

# File logs disappear on Railway's ephemeral disk. stderr shows up in deploy logs.
if [ -n "${RAILWAY_ENVIRONMENT:-}" ]; then
  export LOG_CHANNEL=stderr
fi

mkdir -p storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

php artisan storage:link --ansi || true
php artisan filament:assets --ansi
php artisan migrate --force --no-interaction

# Skip route:cache. Filament registers routes that cannot be serialized.
php artisan config:cache --ansi
php artisan event:cache --ansi || echo "event:cache skipped"
php artisan view:cache --ansi || echo "view:cache skipped"

WORKER_PID=""
SERVER_PID=""

cleanup() {
  if [ -n "$SERVER_PID" ]; then
    kill "$SERVER_PID" 2>/dev/null || true
  fi
  if [ -n "$WORKER_PID" ]; then
    kill "$WORKER_PID" 2>/dev/null || true
  fi
}

trap cleanup TERM INT

(
  while true; do
    php artisan queue:work --sleep=3 --tries=3 --max-time=3600 || true
    sleep 2
  done
) &
WORKER_PID=$!

if ! command -v docker-php-entrypoint >/dev/null 2>&1; then
  echo "FrankenPHP entrypoint not found. This service must be built with Railpack (builder = RAILPACK)." >&2
  cleanup
  exit 1
fi

# Railpack serves Laravel with FrankenPHP. php artisan serve does not stay bound to $PORT.
docker-php-entrypoint --config /Caddyfile --adapter caddyfile &
SERVER_PID=$!

set +e
wait "$SERVER_PID"
STATUS=$?
set -e
cleanup

# Railway sends SIGTERM on redeploy. Exit 0 so that stop is not treated as a crash.
if [ "$STATUS" -eq 143 ] || [ "$STATUS" -eq 130 ]; then
  exit 0
fi
if [ "$STATUS" -eq 0 ]; then
  exit 1
fi
exit "$STATUS"
