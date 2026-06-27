#!/usr/bin/env bash
#
# Server-side API deploy. Invoked by the Deploy API GitHub Action over SSH.
# Pulls the latest code, installs deps, backs up the DB, migrates, caches,
# and reloads PHP-FPM. Safe to run repeatedly.
#
set -euo pipefail

APP_DIR=/var/www/Narzin
API_DIR="$APP_DIR/narzinapp-main"
PHP_FPM=php8.2-fpm

echo "==> Pulling latest code"
cd "$APP_DIR"
git fetch --all --quiet
git reset --hard origin/main

cd "$API_DIR"

echo "==> Backing up database (best effort)"
if [ -f .env ]; then
  # shellcheck disable=SC2046
  set -a
  source <(grep -E '^(DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' .env | sed 's/\r$//')
  set +a
  BACKUP_DIR="$HOME/db-backups"
  mkdir -p "$BACKUP_DIR"
  TS=$(date +%F_%H%M%S)
  if mysqldump -h "${DB_HOST:-127.0.0.1}" -u "${DB_USERNAME:-}" -p"${DB_PASSWORD:-}" "${DB_DATABASE:-}" \
        > "$BACKUP_DIR/${DB_DATABASE:-db}-$TS.sql" 2>/dev/null; then
    gzip "$BACKUP_DIR/${DB_DATABASE:-db}-$TS.sql"
    echo "    backup saved to $BACKUP_DIR"
    # keep only the 14 most recent
    ls -1t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | tail -n +15 | xargs -r rm -f
  else
    echo "    WARN: DB backup skipped/failed (continuing)"
    rm -f "$BACKUP_DIR/${DB_DATABASE:-db}-$TS.sql"
  fi
fi

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

if [ -f package.json ]; then
  echo "==> Building admin-panel assets"
  npm ci --no-audit --no-fund
  npm run build
fi

echo "==> Migrating and caching"
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true

echo "==> Reloading PHP-FPM"
sudo systemctl reload "$PHP_FPM"

echo "==> API deploy complete."
