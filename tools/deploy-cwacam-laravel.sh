#!/usr/bin/env bash
# Deploy the live CWACAM Laravel site (cwacam.org) on the VPS.
# Isolated from /var/www/beyondtechworld. Run as root on the VPS.
set -euo pipefail
# shellcheck source=lib/site-identity.sh
source "$(cd "$(dirname "$0")" && pwd)/lib/site-identity.sh"

ROOT="${ROOT:-/var/www/cwacam}"
assert_cwacam_deploy_root "$ROOT"
APP="$ROOT/laravel-app"
WEB_USER="${WEB_USER:-www-data}"
WEB_GROUP="${WEB_GROUP:-www-data}"
REPO="${REPO:-https://github.com/tefumbole/cwa.git}"

if [[ ! -d "$ROOT/.git" ]]; then
  echo "==> Clone $REPO → $ROOT"
  git clone "$REPO" "$ROOT"
fi

cd "$ROOT"
ORIGIN_URL="$(git remote get-url origin 2>/dev/null || true)"
assert_not_beyond_git_remote "$ORIGIN_URL"
echo "==> Pull latest"
git pull --ff-only origin main

if [[ ! -d "$APP" ]]; then
  echo "Laravel app not found at $APP"
  exit 1
fi

if [[ ! -f "$APP/.env" ]]; then
  if [[ -f /var/www/cwacmr/laravel-app/.env ]]; then
    echo "==> Seed .env from existing CWA Laravel env (not BeyondTechWorld)"
    cp /var/www/cwacmr/laravel-app/.env "$APP/.env"
  else
    echo "Missing $APP/.env — copy laravel-app/.env.example and set DB_* first"
    exit 1
  fi
fi

python3 - <<'PY'
from pathlib import Path
p = Path("/var/www/cwacam/laravel-app/.env")
text = p.read_text()
updates = {
    "APP_NAME": '"CWACAM"',
    "APP_URL": "https://cwacam.org",
    "APP_TIMEZONE": "Africa/Douala",
    "LAUNCH_AT": "2026-10-01T00:00:00",
    "LAUNCH_WINDOW_DAYS": "21",
}
lines = []
seen = set()
for line in text.splitlines():
    if line and not line.startswith("#") and "=" in line:
        key = line.split("=", 1)[0]
        if key in updates:
            lines.append(f"{key}={updates[key]}")
            seen.add(key)
            continue
    lines.append(line)
for key, val in updates.items():
    if key not in seen:
        lines.append(f"{key}={val}")
p.write_text("\n".join(lines) + "\n")
print("Updated APP_URL / LAUNCH_AT in laravel-app/.env")
PY

DB_NAME="$(python3 - <<'PY'
from pathlib import Path
name = ""
for line in Path("/var/www/cwacam/laravel-app/.env").read_text().splitlines():
    if line.startswith("DB_DATABASE="):
        name = line.split("=", 1)[1].strip().strip('"').strip("'")
        break
print(name)
PY
)"
DATA_DB_NAME="$(python3 - <<'PY'
from pathlib import Path
name = ""
for line in Path("/var/www/cwacam/laravel-app/.env").read_text().splitlines():
    if line.startswith("BEYOND_DATA_DB_DATABASE="):
        name = line.split("=", 1)[1].strip().strip('"').strip("'")
        break
print(name)
PY
)"
DATA_DB_HOST="$(python3 - <<'PY'
from pathlib import Path
name = ""
for line in Path("/var/www/cwacam/laravel-app/.env").read_text().splitlines():
    if line.startswith("BEYOND_DATA_DB_HOST="):
        name = line.split("=", 1)[1].strip().strip('"').strip("'")
        break
print(name)
PY
)"
assert_not_beyond_database "$DB_NAME"
assert_not_beyond_database "$DATA_DB_NAME"
assert_not_beyond_host "$DATA_DB_HOST"
printf 'cwacam\n' > "$ROOT/.site-identity"

ln -sfn public/branding "$APP/branding"
# Saleora assets use /public/vendor/... while nginx root is laravel-app/public
if [[ ! -e "$APP/public/public" ]]; then
  ln -sfn . "$APP/public/public"
fi

if [[ ! -d "$APP/vendor/laravel" && -d /var/www/cwacmr/laravel-app/vendor/laravel ]]; then
  echo "==> Copy vendor from existing CWA install"
  rsync -a /var/www/cwacmr/laravel-app/vendor/ "$APP/vendor/"
fi

if [[ ! -d "$APP/vendor/laravel" ]]; then
  echo "==> composer install"
  (cd "$APP" && composer install --no-dev --optimize-autoloader --no-interaction)
fi

# Admin UI CSS/JS live under public/vendor and are not in git.
if [[ ! -f "$APP/public/vendor/bootstrap/css/bootstrap.min.css" ]]; then
  if [[ -f /var/www/cwacmr/laravel-app/public/vendor/bootstrap/css/bootstrap.min.css ]]; then
    echo "==> Restore admin public/vendor assets from existing CWA site"
    rsync -a /var/www/cwacmr/laravel-app/public/vendor/ "$APP/public/vendor/"
  elif [[ -f /var/www/beyondtechworld/laravel-app/public/vendor/bootstrap/css/bootstrap.min.css ]]; then
    echo "==> Restore admin public/vendor assets from Saleora vendor copy"
    rsync -a --ignore-existing /var/www/beyondtechworld/laravel-app/public/vendor/ "$APP/public/vendor/"
  fi
fi

run_artisan() {
  if id -u "$WEB_USER" >/dev/null 2>&1 && [[ "$(id -u)" -eq 0 ]]; then
    sudo -u "$WEB_USER" -H php artisan "$@"
  else
    php artisan "$@"
  fi
}

mkdir -p \
  "$APP/storage/framework/"{cache/data,sessions,views} \
  "$APP/storage/logs" \
  "$APP/bootstrap/cache" \
  "$APP/public/images/leaders" \
  "$APP/public/uploads/membership"

chown "$WEB_USER:$WEB_GROUP" "$APP/.env"
chmod 640 "$APP/.env"
chown -R "$WEB_USER:$WEB_GROUP" "$APP/storage" "$APP/bootstrap/cache" "$APP/public/images" "$APP/public/uploads"
chmod -R ug+rwx "$APP/storage" "$APP/bootstrap/cache" "$APP/public/images" "$APP/public/uploads"

cd "$APP"
run_artisan config:clear || true
run_artisan view:clear || true
# Do not config:cache — a cached empty DB password takes the site down.

chown -R "$WEB_USER:$WEB_GROUP" "$APP/storage" "$APP/bootstrap/cache"

echo "CWACAM Laravel ready at $APP"
