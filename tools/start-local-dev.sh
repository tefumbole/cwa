#!/usr/bin/env bash
# Start AlphaBridge locally: Docker MySQL + API + Vite frontend
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [ ! -f .env ]; then
  cp .env.local.example .env
  echo "Created .env from .env.local.example"
fi

if [ ! -f apps/api/.env ]; then
  cp apps/api/.env.local.example apps/api/.env
  echo "Created apps/api/.env from apps/api/.env.local.example"
fi

echo "Starting MySQL (Docker)..."
docker compose up -d

echo "Waiting for MySQL..."
for i in {1..30}; do
  if docker compose exec -T mysql mysqladmin ping -h localhost -u root -palphabridge_local --silent 2>/dev/null; then
    break
  fi
  sleep 1
done

echo "Running database migration..."
npm run db:migrate

echo ""
echo "============================================"
echo "  AlphaBridge local dev"
echo "  Frontend: http://localhost:3000"
echo "  API:      http://localhost:3003"
echo "  Login:    admin@alpha-bridge.net / ChangeMe@123456"
echo "  OTP:      skipped locally (VITE_DEV_SKIP_OTP=true)"
echo "============================================"
echo ""

npm run dev:api &
API_PID=$!
npm run dev &
VITE_PID=$!

cleanup() {
  kill "$API_PID" "$VITE_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

wait
