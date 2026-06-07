#!/usr/bin/env bash
# Enable project git hooks (auto version bump on push).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

chmod +x .githooks/pre-push
git config core.hooksPath .githooks

echo "Git hooks enabled (.githooks/pre-push will bump version on each push)."
echo "Skip once with: SKIP_VERSION_BUMP=1 git push"
