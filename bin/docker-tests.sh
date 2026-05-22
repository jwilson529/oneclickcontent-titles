#!/usr/bin/env bash
# GENERATED_BY_CODEX_YOLO_DOCKER_TESTS_V4
set -euo pipefail

WORKDIR="/work"
WP_TESTS_DIR="${WORKDIR}/.wp-tests"
WP_CORE_DIR="${WORKDIR}/.wp-core"

DB_HOST="${WP_TESTS_DB_HOST:-db}"
DB_NAME="${WP_TESTS_DB_NAME:-wordpress_test}"
DB_USER="${WP_TESTS_DB_USER:-root}"
DB_PASS="${WP_TESTS_DB_PASS:-root}"
WP_VERSION="${WP_VERSION:-7.0}"
PHPUNIT_VERSION="${PHPUNIT_VERSION:-9.6.20}"
WP_DEVELOP_REF="${WP_DEVELOP_REF:-}"

resolve_wp_develop_ref() {
    if [ -n "${WP_DEVELOP_REF}" ]; then
        printf '%s\n' "${WP_DEVELOP_REF}"
        return
    fi

    case "${WP_VERSION}" in
        latest|trunk|nightly)
            printf '%s\n' "trunk"
            ;;
        [0-9]*.[0-9])
            printf '%s.0\n' "${WP_VERSION}"
            ;;
        *)
            printf '%s\n' "${WP_VERSION}"
            ;;
    esac
}

wp_download_url() {
    case "${WP_VERSION}" in
        latest)
            printf '%s\n' "https://wordpress.org/latest.tar.gz"
            ;;
        trunk|nightly)
            printf '%s\n' "https://wordpress.org/nightly-builds/wordpress-latest.zip"
            ;;
        *)
            printf 'https://wordpress.org/wordpress-%s.tar.gz\n' "${WP_VERSION}"
            ;;
    esac
}

echo "==> Installing system deps"
apt-get update -qq
apt-get install -y -qq git unzip mariadb-client curl rsync

echo "==> Installing Composer"
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version

echo "==> Installing PHPUnit ${PHPUNIT_VERSION}"
curl -Ls -o /usr/local/bin/phpunit "https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar"
chmod +x /usr/local/bin/phpunit
php -d error_reporting="E_ALL&~E_DEPRECATED" /usr/local/bin/phpunit --version

echo "==> Installing WP core ${WP_VERSION} for tests"
mkdir -p "${WP_TESTS_DIR}"

if [ ! -f "${WP_CORE_DIR}/wp-load.php" ] || [ ! -f "${WP_CORE_DIR}/.wp-version" ] || [ "$(cat "${WP_CORE_DIR}/.wp-version")" != "${WP_VERSION}" ]; then
    rm -rf "${WP_CORE_DIR}"
    mkdir -p "${WP_CORE_DIR}"
    rm -rf /tmp/wordpress /tmp/wp.tar.gz /tmp/wp.zip

    if [ "trunk" = "${WP_VERSION}" ] || [ "nightly" = "${WP_VERSION}" ]; then
        curl -fLs -o /tmp/wp.zip "$(wp_download_url)"
        unzip -q /tmp/wp.zip -d /tmp
    else
        curl -fLs -o /tmp/wp.tar.gz "$(wp_download_url)"
        tar -xzf /tmp/wp.tar.gz -C /tmp
    fi

    rsync -a /tmp/wordpress/ "${WP_CORE_DIR}/"
    printf '%s\n' "${WP_VERSION}" > "${WP_CORE_DIR}/.wp-version"
fi

echo "==> Active WordPress core version"
php -r "require '${WP_CORE_DIR}/wp-includes/version.php'; echo \$wp_version . PHP_EOL;"

echo "==> Installing WP ${WP_VERSION} test suite"
if [ ! -d "${WP_TESTS_DIR}/includes" ] || [ ! -f "${WP_TESTS_DIR}/.wp-version" ] || [ "$(cat "${WP_TESTS_DIR}/.wp-version")" != "${WP_VERSION}" ]; then
    rm -rf "${WP_TESTS_DIR}" /tmp/wp-develop
    mkdir -p "${WP_TESTS_DIR}"
    wp_develop_ref="$(resolve_wp_develop_ref)"
    git clone --depth=1 https://github.com/WordPress/wordpress-develop.git /tmp/wp-develop
    if [ "trunk" != "${wp_develop_ref}" ]; then
        git -C /tmp/wp-develop fetch --depth=1 origin "refs/tags/${wp_develop_ref}:refs/tags/${wp_develop_ref}"
        git -C /tmp/wp-develop checkout "tags/${wp_develop_ref}"
    fi
    rsync -a /tmp/wp-develop/tests/phpunit/ "${WP_TESTS_DIR}/"
    printf '%s\n' "${WP_VERSION}" > "${WP_TESTS_DIR}/.wp-version"
fi

echo "==> Creating wp-tests-config.php"
cat > "${WP_TESTS_DIR}/wp-tests-config.php" <<CFG
<?php
define( 'DB_NAME', '${DB_NAME}' );
define( 'DB_USER', '${DB_USER}' );
define( 'DB_PASSWORD', '${DB_PASS}' );
define( 'DB_HOST', '${DB_HOST}' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'ABSPATH', '${WP_CORE_DIR}/' );
define( 'WP_DEBUG', true );

\$table_prefix = 'wptests_';

require_once '${WP_TESTS_DIR}/includes/functions.php';
CFG

echo "==> Creating test database if needed"
MYSQL_SSL_FLAG=""
if mysql --help 2>/dev/null | grep -q "ssl-mode"; then
    MYSQL_SSL_FLAG="--ssl-mode=DISABLED"
elif mysql --help 2>/dev/null | grep -q "skip-ssl"; then
    MYSQL_SSL_FLAG="--skip-ssl"
elif mysql --help 2>/dev/null | grep -q "ssl"; then
    MYSQL_SSL_FLAG="--ssl=0"
fi
mysql ${MYSQL_SSL_FLAG} -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;" >/dev/null

echo "==> Running PHPUnit"
cd "${WORKDIR}"
bash ./bin/ensure-composer-deps.sh "${WORKDIR}"
php -d error_reporting="E_ALL&~E_DEPRECATED" /usr/local/bin/phpunit
