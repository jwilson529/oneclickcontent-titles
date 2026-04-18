#!/usr/bin/env bash
# GENERATED_BY_CODEX_YOLO_COMPOSER_DEPS_V1
set -euo pipefail

WORKDIR="${1:-$(pwd)}"

if [ -f "${WORKDIR}/vendor/autoload.php" ]; then
	exit 0
fi

if ! php -r 'exit( PHP_VERSION_ID >= 80400 ? 0 : 1 );'; then
	echo "Project test dependencies currently require PHP 8.4+ for local installation. Use npm test or run this script with PHP 8.4." >&2
	exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
	echo "Composer is required to install project test dependencies." >&2
	exit 1
fi

echo "==> Installing Composer dependencies"
cd "${WORKDIR}"
git config --global --add safe.directory "${WORKDIR}" >/dev/null 2>&1 || true
COMPOSER_ALLOW_SUPERUSER="${COMPOSER_ALLOW_SUPERUSER:-1}" composer install --no-interaction --prefer-dist
