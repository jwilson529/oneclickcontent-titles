#!/usr/bin/env bash

set -euo pipefail

PLUGIN_SLUG="oneclickcontent-titles"
DIST_ROOT="dist"
STAGING_DIR="${DIST_ROOT}/${PLUGIN_SLUG}"
ARCHIVE_NAME="${PLUGIN_SLUG}.zip"
ARCHIVE_PATH="${DIST_ROOT}/${ARCHIVE_NAME}"

rm -rf "${DIST_ROOT}"
mkdir -p "${STAGING_DIR}"

cp -R \
	oneclickcontent-titles.php \
	index.php \
	uninstall.php \
	readme.txt \
	LICENSE.txt \
	admin \
	img \
	includes \
	languages \
	public \
	"${STAGING_DIR}"

required_runtime_paths=(
	"${STAGING_DIR}/oneclickcontent-titles.php"
	"${STAGING_DIR}/readme.txt"
	"${STAGING_DIR}/includes/class-occ-titles.php"
	"${STAGING_DIR}/admin/class-occ-titles-admin.php"
)

for required_path in "${required_runtime_paths[@]}"; do
	if [ ! -e "${required_path}" ]; then
		echo "Release staging is missing required runtime file: ${required_path}"
		rm -rf "${DIST_ROOT}"
		exit 1
	fi
done

# Remove common junk files from staged release payload.
find "${STAGING_DIR}" -name ".DS_Store" -delete
find "${STAGING_DIR}" -name "Thumbs.db" -delete
find "${STAGING_DIR}" -name "*.swp" -delete
find "${STAGING_DIR}" -name "*.tmp" -delete

(
	cd "${DIST_ROOT}"
	zip -rq "${ARCHIVE_NAME}" "${PLUGIN_SLUG}"
)

zip_listing="$(unzip -l "${ARCHIVE_PATH}")"

if ! printf '%s\n' "${zip_listing}" | grep -q "oneclickcontent-titles/oneclickcontent-titles.php$"; then
	echo "Release archive is missing the main plugin file at the plugin root."
	rm -f "${ARCHIVE_PATH}"
	rm -rf "${DIST_ROOT}"
	exit 1
fi

if ! printf '%s\n' "${zip_listing}" | grep -q "oneclickcontent-titles/readme.txt$"; then
	echo "Release archive is missing readme.txt at the plugin root."
	rm -f "${ARCHIVE_PATH}"
	rm -rf "${DIST_ROOT}"
	exit 1
fi

# Ensure dev-only artifacts did not leak into distributable zip.
if printf '%s\n' "${zip_listing}" | grep -E "oneclickcontent-titles/(tests/|vendor/|node_modules/|\.wp-core/|\.wp-tests/|\.git/|\.github/|assets/|AGENTS\.md|APP_FLOW\.md|DESIGN_LESSONS\.md|DESIGN_SYSTEM\.md|FRONTEND_GUIDELINES\.md|MEMORY\.md|PLAN\.md|PLAYBOOK\.md|README\.md|RELEASE\.md|SPEC\.md|UI_AUDIT\.md|UX_SUGGESTIONS\.md|\.codex|check\.txt|phpmd\.txt|occ-titles\.log|plugin-error\.log)" > /dev/null; then
	echo "Release archive contains disallowed development artifacts."
	rm -f "${ARCHIVE_PATH}"
	rm -rf "${DIST_ROOT}"
	exit 1
fi

echo "Created ${ARCHIVE_PATH}"
