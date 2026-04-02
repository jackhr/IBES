#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"

usage() {
  cat <<'EOF'
Usage: ./deploy-local.sh [options]

Options:
  --php-bin <path>        PHP binary path (default: php)
  --composer-bin <path>   Composer binary path (default: composer)
  --npm-bin <path>        npm binary path (default: npm)
  -h, --help              Show this help

Environment variable overrides:
  PHP_BIN, COMPOSER_BIN, NPM_BIN
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --php-bin)
      PHP_BIN="$2"
      shift 2
      ;;
    --composer-bin)
      COMPOSER_BIN="$2"
      shift 2
      ;;
    --npm-bin)
      NPM_BIN="$2"
      shift 2
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage
      exit 1
      ;;
  esac
done

if [[ ! -x "$PHP_BIN" && "$PHP_BIN" != "php" ]]; then
  echo "PHP binary not executable: $PHP_BIN" >&2
  exit 1
fi

if ! command -v "$NPM_BIN" >/dev/null 2>&1; then
  echo "npm binary not found: $NPM_BIN" >&2
  exit 1
fi

run() {
  echo ""
  echo "==> $*"
  "$@"
}

run "$COMPOSER_BIN" install --no-interaction
run "$NPM_BIN" install --no-fund --no-audit
run "$NPM_BIN" run build

run "$PHP_BIN" artisan optimize:clear --no-interaction

echo ""
echo "Local deploy steps completed."
