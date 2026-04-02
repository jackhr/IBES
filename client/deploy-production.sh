#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

PHP_BIN="${PHP_BIN:-/opt/cpanel/ea-php83/root/usr/bin/php}"
COMPOSER_BIN="${COMPOSER_BIN:-/opt/cpanel/composer/bin/composer}"
NPM_BIN="${NPM_BIN:-npm}"
MIGRATIONS_MODE="${MIGRATIONS_MODE:-auto}" # auto|always|never
SKIP_BUILD="${SKIP_BUILD:-false}"

usage() {
  cat <<'EOF'
Usage: ./deploy-production.sh [options]

Options:
  --php-bin <path>        PHP binary path (default: /opt/cpanel/ea-php83/root/usr/bin/php)
  --composer-bin <path>   Composer binary path (default: /opt/cpanel/composer/bin/composer)
  --npm-bin <path>        npm binary path (default: npm)
  --skip-build            Skip npm install and npm run build
  --migrations <mode>     Migration mode: auto | always | never (default: auto)
  -h, --help              Show this help

Environment variable overrides:
  PHP_BIN, COMPOSER_BIN, NPM_BIN, SKIP_BUILD, MIGRATIONS_MODE
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
    --skip-build)
      SKIP_BUILD="true"
      shift
      ;;
    --migrations)
      MIGRATIONS_MODE="$2"
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

if [[ ! -x "$PHP_BIN" ]]; then
  echo "PHP binary not executable: $PHP_BIN" >&2
  exit 1
fi

if [[ "$MIGRATIONS_MODE" != "auto" && "$MIGRATIONS_MODE" != "always" && "$MIGRATIONS_MODE" != "never" ]]; then
  echo "Invalid --migrations mode: $MIGRATIONS_MODE (expected auto|always|never)" >&2
  exit 1
fi

if [[ "$SKIP_BUILD" != "true" && "$SKIP_BUILD" != "false" ]]; then
  echo "Invalid SKIP_BUILD value: $SKIP_BUILD (expected true|false)" >&2
  exit 1
fi

run() {
  echo ""
  echo "==> $*"
  "$@"
}

run "$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction

if [[ "$SKIP_BUILD" == "true" ]]; then
  echo ""
  echo "==> Skipping frontend build"
else
  if ! command -v "$NPM_BIN" >/dev/null 2>&1; then
    echo "npm binary not found: $NPM_BIN" >&2
    exit 1
  fi

  run "$NPM_BIN" install --no-fund --no-audit
  run "$NPM_BIN" run build
fi

run "$PHP_BIN" artisan optimize:clear --no-interaction

run_migrations="no"

if [[ "$MIGRATIONS_MODE" == "always" ]]; then
  run_migrations="yes"
elif [[ "$MIGRATIONS_MODE" == "auto" ]]; then
  echo ""
  echo "==> Checking for pending migrations"
  pending_output="$("$PHP_BIN" artisan migrate:status --pending --no-ansi --no-interaction)"
  echo "$pending_output"

  if echo "$pending_output" | grep -Eq '^\s*\|'; then
    run_migrations="yes"
  fi
fi

if [[ "$run_migrations" == "yes" ]]; then
  run "$PHP_BIN" artisan migrate --force --no-interaction
else
  echo ""
  echo "==> Skipping migrations (mode: $MIGRATIONS_MODE)"
fi

run "$PHP_BIN" artisan config:cache --no-interaction
run "$PHP_BIN" artisan route:cache --no-interaction
run "$PHP_BIN" artisan view:cache --no-interaction

echo ""
echo "Deployment steps completed."
