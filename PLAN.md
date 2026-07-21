<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal

Publish the current Title Assistant changes as a polished WordPress.org release with current screenshots, a verified installable ZIP, and no unresolved directory blockers.

## Assumptions

- The current WordPress.org release baseline and repository version must be verified before choosing the next patch version.
- Existing Centerstone settings, including the explicit `gpt-5.6-terra` model, must remain unchanged.
- Release fixes should remain small and standards aligned; unrelated refactors are out of scope.
- WordPress 7.0.2 is the stable compatibility gate and nightly remains the forward-compatibility signal.
- The release ZIP must contain runtime files only and must install under one top-level `oneclickcontent-titles/` directory.
- Release screenshots must come from a clean default WordPress site populated only with non-secret demonstration data.

## Questions (non-blocking)

- None.

## Files in the Current Change Set

- `.codex_index.json`
- `.github/workflows/plugin-check.yaml`
- `.gitignore`
- `HANDOFF.md`
- `MARKETING_PLAN.md`
- `MEMORY.md`
- `PLAN.md`
- `README.md`
- `RELEASE.md`
- `admin/class-occ-titles-admin.php`
- `admin/class-occ-titles-settings.php`
- `admin/css/occ-titles-admin.css`
- `admin/js/occ-titles-admin.js`
- `admin/js/occ-titles-editor-bridge.js`
- `bin/build-dist.sh`
- `bin/docker-tests.sh`
- `docker-compose.yml`
- `includes/class-occ-titles-activator.php`
- `includes/class-occ-titles-logger.php`
- `includes/class-occ-titles-uninstaller.php`
- `includes/class-occ-titles.php`
- `languages/occ-titles.pot`
- `oneclickcontent-titles.php`
- `package.json`
- `phpmd.xml`
- `readme.txt`
- `tests/class-adminresultstest.php`
- `tests/class-optionstest.php`
- `tests/class-uninstallertest.php`
- `tests/js/editor-bridge.test.js`
- Tracked generated caches removed from the index: `.wp-core/`, `.wp-tests/`, and `check.txt` (local copies remain ignored)

## Release Slices

1. Baseline and plan: verify the public directory metadata, repository release contract, versions, and current working-tree scope.
2. Security and rendering: audit every AJAX action, provider error path, secret transport, capability check, nonce, sanitization boundary, and output sink.
3. Harness and compatibility: run local, WordPress 7.0.2, nightly, syntax, PHPCS, PHPMD, activation, deactivation, and uninstall checks.
4. Metadata and assets: align plugin headers, `readme.txt`, changelog, screenshots, icons, banners, translations, and next patch version.
5. Package and Plugin Check: build the runtime-only ZIP, inspect its contents, install it on a clean WordPress site, and run official Plugin Check.
6. Release documentation: run a clean default WordPress 7.0.2 site, capture and inspect the editor and settings screens, and present the screenshots in both readmes.
7. Final convergence: rerun all green gates, refresh the repository index and handoff, commit and push the release candidate, publish the release, and verify the deployment.

## Acceptance Criteria

- The main plugin header, package metadata, readme stable tag, changelog, and release version agree.
- `readme.txt` passes directory formatting and short-description limits.
- No secrets, development fixtures, logs, test harnesses, or repository-only documents ship in the ZIP.
- All privileged actions pair capability checks with nonce verification and sanitize explicit inputs.
- Provider errors and remote data cannot become executable admin markup.
- Activation, deactivation, and uninstall complete without plugin-owned data leakage or unrelated deletion.
- The compact Title Assistant works in Classic Editor plus Gutenberg Code and Visual Editor modes.
- Details opens on the first click and Apply updates the actual title in all three editor modes.
- Local, WordPress 7.0.2, and nightly PHPUnit suites pass.
- PHPCS, PHPMD, PHP syntax, JavaScript syntax, and shell syntax pass.
- Official Plugin Check reports no release-blocking errors for the built plugin.
- The built ZIP installs and activates on a clean WordPress site.

## Run Status

- Slice 1, baseline and plan: complete
- Slice 2, security and rendering: reopened after editor regression reports; implementation fix and automated regression coverage complete, live browser verification pending
- Slice 3, harness and compatibility: complete
- Slice 4, metadata and assets: metadata complete; replacement screenshots pending live browser capture
- Slice 5, package and Plugin Check: complete
- Slice 6, release documentation: in progress; a clean WordPress 7.0.2 Playground site was created, but no controllable browser is attached to this session
- Slice 7, final convergence: pending
- Last checkpoint before current uncommitted work: `a678bc8`
- Release candidate: `2.1.6`
- Release ZIP: `dist/oneclickcontent-titles.zip`
- Release ZIP SHA-256: pending the final screenshot-gated build
- Centerstone saved model: `gpt-5.6-terra`, verified unchanged

## Latest Run

- Date: 2026-07-21
- Summary: Reopened release validation after live reports that the Classic launcher was missing, Gutenberg details required repeated interaction, and Apply did not update the title. Added an editor bridge with five JavaScript regression tests, restored deterministic Classic/Block detection, made Apply verify the Gutenberg store with a field fallback, prevented repeated saved-result reloads, and moved detailed analysis out of the nested disclosure. Verified packaged assets on real WordPress 7.0.2 Block and Classic Editor pages, passed 38 PHP tests on stable and nightly, and removed 4,994 generated harness/report files from Git tracking while preserving their ignored local copies. Fresh browser verification and screenshots remain gated on an available browser attachment.
