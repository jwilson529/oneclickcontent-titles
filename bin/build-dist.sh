#!/usr/bin/env bash

set -e

PLUGIN_SLUG="oneclickcontent-titles"
STAGING_ROOT=".dist"
STAGING_DIR="${STAGING_ROOT}/${PLUGIN_SLUG}"
ARCHIVE_NAME="${PLUGIN_SLUG}.zip"

rm -rf "${STAGING_ROOT}" "${ARCHIVE_NAME}"
mkdir -p "${STAGING_DIR}"

cp -R \
	oneclickcontent-titles.php \
	index.php \
	uninstall.php \
	README.txt \
	LICENSE.txt \
	admin \
	assets \
	img \
	includes \
	languages \
	public \
	"${STAGING_DIR}"

find "${STAGING_DIR}" -name ".DS_Store" -delete

(
	cd "${STAGING_ROOT}"
	zip -rq "../${ARCHIVE_NAME}" "${PLUGIN_SLUG}"
)

rm -rf "${STAGING_ROOT}"

echo "Created ${ARCHIVE_NAME}"
