#!/bin/sh
set -e

cd /var/www/html

if [ -n "$DB_HOST" ]; then
  echo "Waiting for database at ${DB_HOST}:${DB_PORT:-5432}..."
  attempts=0
  until php -r "
    try {
      new PDO(
        'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
      );
      exit(0);
    } catch (Throwable \$e) {
      file_put_contents('php://stderr', \$e->getMessage() . PHP_EOL);
      exit(1);
    }
  "; do
    attempts=$((attempts + 1))
    if [ "$attempts" -ge 30 ]; then
      echo "Database not reachable after 60s. Check DB_PASSWORD matches postgres (see .env and backend/.env.docker)." >&2
      exit 1
    fi
    sleep 2
  done
  echo "Database is ready."
fi

if [ -z "${APP_KEY:-}" ] && [ "${1:-}" = "/usr/bin/supervisord" ]; then
  echo "APP_KEY is not set. Add it to backend/.env.docker (php artisan key:generate --show)." >&2
  exit 1
fi

php artisan storage:link --force 2>/dev/null || true

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache

exec "$@"
