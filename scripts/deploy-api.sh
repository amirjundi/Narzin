#!/usr/bin/env bash
#
# Server-side API deploy. Invoked by the Deploy API GitHub Action over SSH.
# Pulls the latest code, backs up PostgreSQL, runs migrations inside the
# Docker container, clears caches, and restarts the backend container.
# Safe to run repeatedly (idempotent).
#
set -euo pipefail

APP_DIR=/var/www/Narzin
API_DIR="$APP_DIR/narzinapp-main"
COMPOSE_DIR="$APP_DIR"

echo "==> Pulling latest code"
cd "$APP_DIR"
git fetch --all --quiet
git reset --hard origin/main

echo "==> Ensuring Docker stack is up (build if images/config changed)"
docker compose -f "$COMPOSE_DIR/docker-compose.yml" up -d --build

echo "==> Backing up PostgreSQL database (best effort)"
if [ -f "$API_DIR/.env" ]; then
  set -a
  source <(grep -E '^(DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD|DB_PORT)=' "$API_DIR/.env" | sed 's/\r$//')
  set +a
  BACKUP_DIR="$HOME/db-backups"
  mkdir -p "$BACKUP_DIR"
  TS=$(date +%F_%H%M%S)
  # Run pg_dump inside the running db container
  if docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T db \
       pg_dump -U "${DB_USERNAME:-narzin_user}" "${DB_DATABASE:-narzin}" \
       > "$BACKUP_DIR/${DB_DATABASE:-narzin}-$TS.sql" 2>/dev/null; then
    gzip "$BACKUP_DIR/${DB_DATABASE:-narzin}-$TS.sql"
    echo "    backup saved to $BACKUP_DIR"
    # Keep only the 14 most recent backups
    ls -1t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | tail -n +15 | xargs -r rm -f
  else
    echo "    WARN: DB backup skipped/failed (continuing)"
    rm -f "$BACKUP_DIR/${DB_DATABASE:-narzin}-$TS.sql"
  fi
fi

echo "==> Installing PHP dependencies inside container"
docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T backend \
  composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Clearing nwidart module cache (prevents 500 + stale module routes)"
docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T backend \
  bash -c "rm -f bootstrap/cache/*_module.php bootstrap/cache/modules.php"

echo "==> Running database migrations"
docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T backend \
  php artisan migrate --force

echo "==> Linking storage and clearing caches"
docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T backend \
  php artisan storage:link || true
docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T backend \
  php artisan optimize:clear
docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T backend \
  php artisan view:cache
docker compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T backend \
  php artisan queue:restart || true

echo "==> Restarting backend container to pick up code changes"
docker compose -f "$COMPOSE_DIR/docker-compose.yml" restart backend web

echo "==> API deploy complete."
