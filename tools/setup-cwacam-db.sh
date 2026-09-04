#!/usr/bin/env bash
# Create the isolated local CWACAM MySQL database and run fresh Node migrations.
# Never imports BeyondTechWorld or Alpha Bridge dumps.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ENV_EXAMPLE="apps/api/.env.cwacam.example"
ENV_FILE="${1:-apps/api/.env.cwacam}"

if [[ ! -f "$ENV_EXAMPLE" ]]; then
  echo "Missing $ENV_EXAMPLE"
  exit 1
fi

if [[ ! -f "$ENV_FILE" ]]; then
  cp "$ENV_EXAMPLE" "$ENV_FILE"
  echo "Created $ENV_FILE from $ENV_EXAMPLE"
else
  echo "Using existing $ENV_FILE (not overwritten)"
fi

if [[ ! -f "apps/api/.env" ]]; then
  cp "$ENV_FILE" "apps/api/.env"
  echo "Created apps/api/.env from $ENV_FILE"
fi

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a
# Exported CWACAM vars win over a leftover Beyond apps/api/.env (dotenv does not override).

if [[ -z "${DB_NAME:-}" ]]; then
  echo "ERROR: DB_NAME is empty in $ENV_FILE"
  exit 1
fi

if echo "$DB_NAME" | grep -Eiq 'beyondworld|beyondtech'; then
  echo "ERROR: DB_NAME \"$DB_NAME\" looks like a BeyondTechWorld database."
  echo "CWACAM must use its own database (local: cwacam)."
  exit 1
fi

create_local_homebrew_db() {
  echo "==> Docker unavailable — creating isolated \"$DB_NAME\" on local Homebrew MySQL (3306)..."
  local mysql_cmd=(mysql -h 127.0.0.1 --protocol=TCP -uroot)
  if [[ -n "${MYSQL_ROOT_PASSWORD:-}" ]]; then
    mysql_cmd+=(-p"${MYSQL_ROOT_PASSWORD}")
  fi
  "${mysql_cmd[@]}" <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
SQL
  export DB_PORT=3306
  export DB_HOST=127.0.0.1
}

if docker info >/dev/null 2>&1; then
  echo "==> Starting cwacam-mysql on port 3308..."
  docker compose up -d cwacam-mysql

  echo "==> Waiting for MySQL to accept connections..."
  for i in $(seq 1 40); do
    if docker compose exec -T cwacam-mysql mysqladmin ping -h 127.0.0.1 -ucwacam -pcwacam_local --silent >/dev/null 2>&1; then
      break
    fi
    if [[ "$i" -eq 40 ]]; then
      echo "ERROR: cwacam-mysql did not become ready."
      exit 1
    fi
    sleep 2
  done

  echo "==> Ensuring empty database \"$DB_NAME\" exists..."
  docker compose exec -T cwacam-mysql mysql -uroot -pcwacam_local -e \
    "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
     GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';
     FLUSH PRIVILEGES;"
else
  create_local_homebrew_db
fi

echo "==> Running Node schema + seeds (no remote import)..."
npm run db:migrate --prefix apps/api
npm run db:seed-admin --prefix apps/api

echo ""
echo "CWACAM local database ready: ${DB_NAME}@${DB_HOST}:${DB_PORT}"
echo "Admin seed: ${SEED_ADMIN_EMAIL:-admin@cwacam.org} / ${SEED_ADMIN_PASSWORD:-ChangeMe@123456}"
echo "This database is isolated from BeyondTechWorld. Do not point BEYOND_DATA_DB_* at beyondworld."
