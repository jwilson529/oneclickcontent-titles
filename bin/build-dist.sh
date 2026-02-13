#!/usr/bin/env bash

set -euo pipefail

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
	readme.txt \
	LICENSE.txt \
	admin \
	assets \
	img \
	includes \
	languages \
	public \
	"${STAGING_DIR}"

# Remove common junk files from staged release payload.
find "${STAGING_DIR}" -name ".DS_Store" -delete
find "${STAGING_DIR}" -name "Thumbs.db" -delete
find "${STAGING_DIR}" -name "*.swp" -delete
find "${STAGING_DIR}" -name "*.tmp" -delete

(
	cd "${STAGING_ROOT}"
	zip -rq "../${ARCHIVE_NAME}" "${PLUGIN_SLUG}"
)

# Ensure dev-only artifacts did not leak into distributable zip.
if unzip -l "${ARCHIVE_NAME}" | grep -E "oneclickcontent-titles/(tests/|vendor/|node_modules/|\.wp-core/|\.wp-tests/|\.git/|\.github/|check\.txt|phpmd\.txt)" > /dev/null; then
	echo "Release archive contains disallowed development artifacts."
	rm -f "${ARCHIVE_NAME}"
	rm -rf "${STAGING_ROOT}"
	exit 1
fi

rm -rf "${STAGING_ROOT}"

echo "Created ${ARCHIVE_NAME}"
