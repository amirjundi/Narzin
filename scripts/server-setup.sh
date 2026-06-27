#!/usr/bin/env bash
#
# One-time (idempotent) VPS provisioning for the Narzin monorepo.
# Run as root on the new server:  sudo bash server-setup.sh
#
# It installs nginx + PHP 8.2-FPM + MySQL + Node + Composer + Certbot,
# sets up the `deployer` user, a read-only GitHub deploy key, the nginx
# vhosts, MySQL database, a scaffolded production .env, SSL certs, and the
# first build. Re-run it after adding the printed Deploy Key to GitHub.
#
set -euo pipefail

# ─── Config (edit if your domains differ) ─────────────────────────────
REPO_SSH="git@github.com:amirjundi/Narzin.git"
APP_DIR=/var/www/Narzin
API_DIR="$APP_DIR/narzinapp-main"
WEB_DIST="$APP_DIR/narzin-main/dist"
DEPLOY_USER=deployer
PHP_VER=8.2
API_DOMAIN=admin.narzin.com
WEB_DOMAIN=narzin.com
WEB_WWW=www.narzin.com
CERTBOT_EMAIL=amer.samoqi@gmail.com
DB_NAME=narzin
DB_USER=narzin
# ──────────────────────────────────────────────────────────────────────

export DEBIAN_FRONTEND=noninteractive
log() { echo -e "\n\033[1;36m==> $*\033[0m"; }

[ "$(id -u)" -eq 0 ] || { echo "Run as root."; exit 1; }

log "Installing base packages + PHP $PHP_VER"
apt-get update -y
apt-get install -y software-properties-common curl git unzip ca-certificates gnupg lsb-release
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y nginx mysql-server \
  php${PHP_VER}-fpm php${PHP_VER}-cli php${PHP_VER}-mysql php${PHP_VER}-mbstring \
  php${PHP_VER}-xml php${PHP_VER}-curl php${PHP_VER}-zip php${PHP_VER}-bcmath \
  php${PHP_VER}-gd php${PHP_VER}-intl php${PHP_VER}-redis \
  certbot python3-certbot-nginx

if ! command -v node >/dev/null; then
  log "Installing Node 20"
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

if ! command -v composer >/dev/null; then
  log "Installing Composer"
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

log "Setting up $DEPLOY_USER"
id "$DEPLOY_USER" >/dev/null 2>&1 || useradd -m -s /bin/bash "$DEPLOY_USER"
usermod -aG www-data "$DEPLOY_USER"
mkdir -p "$APP_DIR"
chown -R "$DEPLOY_USER":www-data "$APP_DIR"

log "Installing CI deploy key (lets GitHub Actions SSH in as $DEPLOY_USER)"
CI_KEY='ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIEIwlx1MCBlyzbFU1NV4D9mJoLA/8RwJYqToMGSDuZmM github-actions-deploy@narzin'
install -d -o "$DEPLOY_USER" -g "$DEPLOY_USER" -m 700 /home/$DEPLOY_USER/.ssh
grep -qF "$CI_KEY" /home/$DEPLOY_USER/.ssh/authorized_keys 2>/dev/null || echo "$CI_KEY" >> /home/$DEPLOY_USER/.ssh/authorized_keys
chown "$DEPLOY_USER":"$DEPLOY_USER" /home/$DEPLOY_USER/.ssh/authorized_keys
chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys

log "Opening firewall (if ufw active)"
if command -v ufw >/dev/null && ufw status | grep -q "Status: active"; then
  ufw allow 22/tcp; ufw allow 80/tcp; ufw allow 443/tcp
fi

log "Granting deployer passwordless reload of php-fpm + nginx"
cat >/etc/sudoers.d/deployer-deploy <<EOF
$DEPLOY_USER ALL=(root) NOPASSWD: /bin/systemctl reload php${PHP_VER}-fpm, /bin/systemctl reload nginx
EOF
chmod 440 /etc/sudoers.d/deployer-deploy

log "Ensuring deployer GitHub deploy key"
sudo -u "$DEPLOY_USER" bash -lc '
  mkdir -p ~/.ssh && chmod 700 ~/.ssh
  [ -f ~/.ssh/id_ed25519 ] || ssh-keygen -t ed25519 -N "" -C "narzin-vps-deploy-key" -f ~/.ssh/id_ed25519 -q
  grep -q github.com ~/.ssh/known_hosts 2>/dev/null || ssh-keyscan -t ed25519 github.com >> ~/.ssh/known_hosts 2>/dev/null
'

log "Cloning the repository"
if [ ! -d "$APP_DIR/.git" ]; then
  if ! sudo -u "$DEPLOY_USER" git clone "$REPO_SSH" "$APP_DIR" 2>/dev/null; then
    echo
    echo "────────────────────────────────────────────────────────────────"
    echo " Repo not cloneable yet. Add THIS public key as a *read-only*"
    echo " Deploy Key:  GitHub repo → Settings → Deploy keys → Add deploy key"
    echo "────────────────────────────────────────────────────────────────"
    sudo -u "$DEPLOY_USER" cat /home/$DEPLOY_USER/.ssh/id_ed25519.pub
    echo "────────────────────────────────────────────────────────────────"
    echo " Then re-run:  sudo bash $0"
    exit 0
  fi
fi

log "Creating MySQL database + user"
DB_PASS_FILE=/root/.narzin_db_pass
if [ -f "$DB_PASS_FILE" ]; then DB_PASS=$(cat "$DB_PASS_FILE"); else DB_PASS=$(openssl rand -base64 24); echo "$DB_PASS" > "$DB_PASS_FILE"; chmod 600 "$DB_PASS_FILE"; fi
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

log "Scaffolding production .env (if missing)"
if [ ! -f "$API_DIR/.env" ]; then
  sudo -u "$DEPLOY_USER" cp "$API_DIR/.env.example" "$API_DIR/.env"

  # Robustly set a key whether it is present-uncommented, present-commented
  # (e.g. "# DB_DATABASE=..."), or absent. Avoids the duplicate-key pitfall
  # where phpdotenv keeps the FIRST definition.
  set_env() {
    local key="$1" val="$2" file="$API_DIR/.env"
    if sudo -u "$DEPLOY_USER" grep -qE "^[# ]*${key}=" "$file"; then
      sudo -u "$DEPLOY_USER" sed -i -E "s|^[# ]*${key}=.*|${key}=${val}|" "$file"
    else
      echo "${key}=${val}" | sudo -u "$DEPLOY_USER" tee -a "$file" >/dev/null
    fi
  }
  set_env APP_ENV production
  set_env APP_DEBUG false
  set_env APP_URL "https://$API_DOMAIN"
  set_env DB_CONNECTION mysql
  set_env DB_HOST 127.0.0.1
  set_env DB_PORT 3306
  set_env DB_DATABASE "$DB_NAME"
  set_env DB_USERNAME "$DB_USER"
  set_env DB_PASSWORD "$DB_PASS"
  set_env SESSION_DRIVER database
  set_env SESSION_DOMAIN ".narzin.com"
  set_env SESSION_SECURE_COOKIE true
  set_env SANCTUM_STATEFUL_DOMAINS "$WEB_DOMAIN,$WEB_WWW"
  set_env CORS_ALLOWED_ORIGINS "https://$WEB_DOMAIN,https://$WEB_WWW"
  sudo -u "$DEPLOY_USER" bash -lc "cd $API_DIR && composer install --no-dev -o --no-interaction && php artisan key:generate"
  echo "!! EDIT $API_DIR/.env and fill NASS_* (production) + MAIL_* before going live."
fi

log "Installing/refreshing app + building"
sudo -u "$DEPLOY_USER" bash -lc "
  cd $API_DIR
  composer install --no-dev -o --no-interaction
  [ -f package.json ] && npm ci --no-audit --no-fund && npm run build || true
  php artisan migrate --force
  php artisan storage:link || true
  php artisan config:cache && php artisan view:cache
"

log "Fixing Laravel writable perms"
chgrp -R www-data "$API_DIR/storage" "$API_DIR/bootstrap/cache"
chmod -R 2775 "$API_DIR/storage" "$API_DIR/bootstrap/cache"

log "Writing nginx vhosts"
cat >/etc/nginx/sites-available/narzin-api <<EOF
server {
    listen 80;
    server_name $API_DOMAIN;
    root $API_DIR/public;
    index index.php;
    client_max_body_size 20M;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
EOF

cat >/etc/nginx/sites-available/narzin-web <<EOF
server {
    listen 80;
    server_name $WEB_DOMAIN $WEB_WWW;
    root $WEB_DIST;
    index index.html;
    location / { try_files \$uri \$uri/ /index.html; }
}
EOF

ln -sf /etc/nginx/sites-available/narzin-api /etc/nginx/sites-enabled/narzin-api
ln -sf /etc/nginx/sites-available/narzin-web /etc/nginx/sites-enabled/narzin-web
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

log "Obtaining SSL certificates (Let's Encrypt)"
certbot --nginx --non-interactive --agree-tos -m "$CERTBOT_EMAIL" --redirect \
  -d "$API_DOMAIN" -d "$WEB_DOMAIN" -d "$WEB_WWW" || \
  echo "WARN: certbot failed — ensure DNS A records point here and ports 80/443 are open, then re-run certbot."

log "Done. DB password stored at $DB_PASS_FILE"
echo "Next: add the GitHub Action secrets and push to main to auto-deploy."
