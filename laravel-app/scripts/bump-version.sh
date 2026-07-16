#!/usr/bin/env bash
# Thin wrapper — prefer: npm run version:bump / node tools/bump-version.js
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT_DIR"
node tools/bump-version.js
