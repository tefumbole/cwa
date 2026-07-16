#!/usr/bin/env bash
# Enable project git hooks (auto version bump on every commit).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

chmod +x .githooks/pre-commit 2>/dev/null || true
[ -f .githooks/pre-push ] && chmod +x .githooks/pre-push || true
git config core.hooksPath .githooks

echo "Git hooks enabled (core.hooksPath -> .githooks)."
echo ".githooks/pre-commit bumps laravel-app/VERSION (CWA V x.y.z patch) on each commit."
echo ".githooks/pre-push bumps once more if this push did not already change VERSION."
echo "Skip once with: SKIP_VERSION_BUMP=1 git commit ...  (or git push)"
