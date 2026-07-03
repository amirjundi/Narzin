# Deployment

Monorepo layout:

| Folder | What | Deploys to |
|--------|------|------------|
| `narzinapp-main/` | Laravel API | `admin.narzin.com` → `/var/www/Narzin/narzinapp-main/public` |
| `narzin-main/` | React web | `narzin.com` → `/var/www/Narzin/narzin-main/dist` |
| `Narzin-app/` | Flutter apps | Not server-deployed (built into APKs/AABs) |

## One-time server provisioning

On the VPS as root:

```bash
sudo bash /var/www/Narzin/scripts/server-setup.sh   # after first clone
# or paste the script and run it before the repo exists
```

The script installs nginx + PHP 8.2-FPM + MySQL + Node + Composer + Certbot,
creates the `deployer` user, generates a **read-only GitHub Deploy Key** (printed
once — add it under repo → Settings → Deploy keys), scaffolds the production
`.env`, runs migrations, configures the nginx vhosts, and issues SSL certs.
Re-run it after adding the Deploy Key. Fill `NASS_*` and `MAIL_*` in
`/var/www/Narzin/narzinapp-main/.env` before going live.

## CI/CD (auto-deploy on merge to `main`)

GitHub Actions secrets (repo → Settings → Secrets and variables → Actions):

| Name | Kind | Value |
|------|------|-------|
| `SSH_HOST` | secret | `75.119.157.158` |
| `SSH_USER` | secret | `deployer` |
| `SSH_PORT` | secret | `22` |
| `SSH_PRIVATE_KEY` | secret | contents of the CI deploy private key |
| `VITE_API_URL` | variable | `https://admin.narzin.com/api/` |

- **`.github/workflows/deploy-api.yml`** — on backend changes, SSHes in and runs
  `scripts/deploy-api.sh` (pull → composer → DB backup → migrate → cache → reload).
- **`.github/workflows/deploy-web.yml`** — on web changes, builds on the runner
  and rsyncs `dist/` to the server.

The production `.env` lives only on the server and is never committed.

## Homepage Builder (Phase 1)

Release steps for the HomeContent module:

1. `php artisan migrate` (creates `home_blocks`).
2. Set in `.env`: `HOME_PREVIEW_TOKEN` (any long random string) and `STOREFRONT_URL` (the React storefront origin, e.g. https://narzin.com).
3. `php artisan home:migrate-legacy` — one-time conversion of `banners` + `before_nav` rows into blocks (idempotent).
4. `php artisan config:clear && php artisan route:clear && php artisan cache:clear`.
5. Verify `GET /api/v1/home?platform=web`, `GET /api/v1/banners/mobile`, `GET /api/v1/before-nav/current`.

The legacy `banners`/`before_nav` tables and endpoints stay until Phase 4 cleanup.

### Legacy retirement (post-adoption — DO NOT do at launch)

The legacy endpoints `/api/v1/banners/mobile`, `/api/v1/banners/web`, `/api/v1/before-nav/current`
are still served (reading from home_blocks) because installed app builds older than the
block-renderer release depend on them. The legacy `banners` / `before_nav` tables still exist
but are no longer read. Retire them only once app-store analytics show the old versions are gone:

1. Remove the routes + controllers in `Modules/Banners`.
2. Drop the `banners` and `before_nav` tables (migration).
3. Remove the Flutter legacy home body + BannersCubit (the block view then becomes the only path).

Phase 3 notes: the app refetches blocks whenever the Home tab remounts (locale changes take effect
on next Home visit); the feed request has a 15s timeout; category/url block links are not yet
tappable in the app.
