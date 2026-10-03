#!/usr/bin/env bash
# Shared guards so CWACAM and BeyondTechWorld never deploy or migrate into each other.
# Usage: source "$(dirname "$0")/lib/site-identity.sh"   (from tools/*.sh)
#    or: source "$(dirname "$0")/../lib/site-identity.sh" (from nested tools)

site_identity_read() {
  local dir="${1:-}"
  local file="$dir/.site-identity"
  if [[ -n "$dir" && -f "$file" ]]; then
    tr -d '[:space:]' < "$file"
    return 0
  fi
  echo ""
}

site_identity_repo_root() {
  local here="${1:-}"
  if [[ -z "$here" ]]; then
    here="$(cd "$(dirname "${BASH_SOURCE[1]}")/.." && pwd)"
  fi
  echo "$here"
}

# Refuse BeyondTechWorld deploy/migrate when this checkout is CWACAM.
assert_not_cwacam_checkout() {
  local root
  root="$(cd "$(dirname "${BASH_SOURCE[1]}")/.." && pwd 2>/dev/null || pwd)"
  local id
  id="$(site_identity_read "$root")"
  if [[ "$id" == "cwacam" ]]; then
    echo "ERROR: This checkout is CWACAM (.site-identity=cwacam)."
    echo "Refusing to run a BeyondTechWorld / Alpha Bridge production script from here."
    echo "Use /var/www/beyondtechworld or the Beyond Tech project for that site."
    exit 1
  fi
  if echo "$root" | grep -Eiq 'CWACAM|/cwacam($|/)'; then
    echo "ERROR: Refusing BeyondTechWorld script inside a CWACAM directory: $root"
    exit 1
  fi
}

# CWACAM deploy must stay on the CWACAM tree and must not touch Beyond.
assert_cwacam_deploy_root() {
  local root="${1:-}"
  if [[ -z "$root" ]]; then
    echo "ERROR: deploy root is empty"
    exit 1
  fi
  if echo "$root" | grep -Eiq 'beyondtechworld|beyondworld|/beyondtech'; then
    echo "ERROR: CWACAM deploy refuses BeyondTechWorld path: $root"
    exit 1
  fi
  if [[ "$root" == /var/www/* && "$root" != /var/www/cwacam && "$root" != /var/www/cwacam/* ]]; then
    echo "ERROR: CWACAM production path must be /var/www/cwacam (got $root)"
    exit 1
  fi
  local id
  id="$(site_identity_read "$root")"
  if [[ -n "$id" && "$id" != "cwacam" ]]; then
    echo "ERROR: $root/.site-identity is '$id', not cwacam. Aborting."
    exit 1
  fi
}

assert_not_beyond_database() {
  local name="${1:-}"
  if echo "$name" | grep -Eiq 'beyondworld|beyondtech|u152889834_beyond'; then
    echo "ERROR: Database '$name' belongs to BeyondTechWorld. CWACAM must use its own database."
    exit 1
  fi
}

assert_not_beyond_host() {
  local host="${1:-}"
  if echo "$host" | grep -Eiq 'beyondtechworld|beyondworld|193\.203\.168\.163'; then
    echo "ERROR: Host '$host' belongs to BeyondTechWorld. CWACAM must use its own MySQL."
    exit 1
  fi
}

assert_not_beyond_git_remote() {
  local url="${1:-}"
  if echo "$url" | grep -Eiq 'BeyondTechWorld|beyondtechworld\.git'; then
    echo "ERROR: Refusing to push or pull CWACAM against BeyondTechWorld.git"
    exit 1
  fi
}
