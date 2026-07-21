<!-- GENERATED_BY_CODEX_YOLO_MEMORY_V1 -->
# Project Memory

This file is persistent context for Codex runs. Keep it short and practical.

## Project
- Plugin slug: oneclickcontent-titles
- Repo root: /Users/jameswilson/oneclickcontent-titles

## Non-negotiables
- WordPress Coding Standards (WPCS).
- Tabs for indentation in code.
- No em dashes in assistant output.
- When updating code you must output full updated methods or full updated files when requested.
- Prefer WPPB structure: includes/, admin/, public/, tests/.
- Do not stop mid-run. Keep moving forward in the SPEC. Test and lint.

## Tooling workflow
- Primary loop:
  - npm run fix
  - npm run check (writes check.txt)
- Treat check.txt as the backlog.

## Packaging
- `bin/build-dist.sh` explicitly stages runtime files and rejects tests, dependencies, repository metadata, assets, and local tooling from the distributable.

## Run recap log
Append a brief recap after each run:
- Date:
- Summary:
- Notable changes:
- Tool results:
- Remaining gaps:

- Date: 2026-02-10
- Summary: Converged plugin baseline and hardened local PHPUnit execution to use project-pinned runner.
- Notable changes: Updated `package.json` `test:local` script to `vendor/bin/phpunit -c phpunit.xml`; updated `phpunit.xml.dist` to define `ABSPATH` and align formatting; refreshed `.codex_index.json`; updated `PLAN.md`.
- Tool results: `npm run fix` clean; `npm run check` clean (`check.txt` empty); `npm run test:local` passed (12 tests, 26 assertions).
- Remaining gaps: None identified for current SPEC baseline.

## Run recap (2026-02-10 12:33:07)
- Exit code: 130
- Docker: pass
- PHPCS: dirty
- MySQL port: 33067

## Run recap (2026-02-10 12:40:00)
- Exit code: 0
- Summary: Re-converged baseline and fixed local test runner drift.
- Notable changes: Updated `package.json` `test:local` to `vendor/bin/phpunit -c phpunit.xml`; refreshed `.codex_index.json`; updated `PLAN.md`.
- Tool results: `npm run fix` clean; `npm run check` clean (`check.txt` empty); `npm run test:local` passed (12 tests, 26 assertions).
- Remaining gaps: None identified for current SPEC baseline.

## Run recap (2026-02-16 14:17:10 CST)
- Exit code: 0
- Summary: Re-converged tooling and tests, and fixed local runner/deprecation drift.
- Notable changes: Restored `package.json` `test:local` to `vendor/bin/phpunit -c phpunit.xml`; removed deprecated `ReflectionProperty::setAccessible()` call in `tests/class-loggertest.php`; refreshed `.codex_index.json`; updated `PLAN.md`.
- Tool results: `npm run fix` clean; `npm run check` clean (`check.txt` empty); `npm run test:local` passed (12 tests, 26 assertions); `npm run test` blocked (Docker daemon unavailable at `/Users/jameswilson/.docker/run/docker.sock`).
- Remaining gaps: None identified for current SPEC baseline.

- Date: 2026-03-13
- Summary: Hardened the plugin for production readiness and documented the release gate in-spec.
- Notable changes: Updated `SPEC.md` and `PLAN.md` with production-readiness criteria; removed remote help-page placeholders in `admin/class-occ-titles-settings.php`; added `includes/class-occ-titles-uninstaller.php` and wired `uninstall.php`; improved direct logger append behavior in `includes/class-occ-titles-logger.php`; added uninstall coverage in `tests/class-uninstallertest.php`; refreshed `.codex_index.json`.
- Tool results: `npm run fix` pass; `npm run check` pass (`check.txt` empty); `npm run test:local` pass (13 tests, 34 assertions); `npm run test` fail in Docker bootstrap (`ERROR 2005 (HY000): Unknown server host 'db' (-2)`).
- Remaining gaps: Docker test harness still needs a networking/bootstrap fix so the `tests` container can resolve `db` consistently.

- Date: 2026-04-24
- Summary: Prepared release-facing screenshot documentation and WordPress.org visual assets for the plugin page without code changes.
- Notable changes: Updated `readme.txt` screenshot captions; added the screenshot set to `README.md`; added the capture plan to `MARKETING_PLAN.md`; added a screenshot release gate to `RELEASE.md`; generated new banner and icon PNG assets; cropped verified screenshot assets to remove WordPress admin chrome; refreshed `PLAN.md`.
- Tool results: `npm run check` pass (`check.txt` empty); `npm run dist` pass (`dist/oneclickcontent-titles.zip` created); `npm test` pass (25 tests, 62 assertions).
- Remaining gaps: Clean Docker admin capture hit headless authentication issues, so the final screenshot set uses cropped versions of already verified UI screenshots rather than newly captured Docker screenshots.

- Date: 2026-04-24
- Summary: Prepared the 2.1.2 release with GPT-5.5 as the OpenAI default for new installs.
- Notable changes: Bumped plugin/package/readme versions to 2.1.2; changed unset OpenAI model fallbacks to `gpt-5.5`; added provider helper coverage for the GPT-5.5 default and OpenAI model-list exposure; refreshed GitHub, WordPress.org, and marketing release copy.
- Tool results: `npm run check` pass (`check.txt` empty); `npm run dist` pass (`dist/oneclickcontent-titles.zip` created); `npm test` pass (27 tests, 68 assertions).
- Remaining gaps: None identified before tagging.

- Date: 2026-04-24
- Summary: Preparing a 2.1.3 corrective release for the WordPress.org short-description warning.
- Notable changes: Shortened the `readme.txt` short description to 126 characters; bumped plugin/package/readme versions to 2.1.3; added a release-process guard for the 150-character short-description limit.
- Tool results: `npm run check` pass (`check.txt` empty); `npm run dist` pass (`dist/oneclickcontent-titles.zip` created); `npm test` pass (27 tests, 68 assertions).
- Remaining gaps: Publish and verify `v2.1.3`.

- Date: 2026-04-24
- Summary: Preparing a 2.1.4 asset correction release for the WordPress.org SVG icon.
- Notable changes: Replaced `assets/icon.svg` with vector artwork matching the current PNG icon set; bumped plugin/package/readme versions to 2.1.4.
- Tool results: SVG parses as `256x256`; rendered with headless Chrome for visual check; `npm run check` pass (`check.txt` empty); `npm run dist` pass (`dist/oneclickcontent-titles.zip` created); `npm test` pass (27 tests, 68 assertions).
- Remaining gaps: Publish and verify `v2.1.4`.

- Date: 2026-05-21
- Summary: Preparing a 2.1.5 WordPress 7.0 compatibility release.
- Notable changes: Bumped plugin/package/readme versions to 2.1.5; updated `readme.txt` to `Tested up to: 7.0`; changed Docker PHPUnit default to `WP_VERSION=7.0`; made `.wp-core` and `.wp-tests` refresh when the requested WordPress version changes.
- Tool results: `npm run check` pass (`check.txt` empty); `npm test` pass against active WordPress core version `7.0` with the WordPress develop `7.0.0` test suite (27 tests, 68 assertions); `npm run dist` pass (`dist/oneclickcontent-titles.zip` created).
- Remaining gaps: Publish and verify `v2.1.5`.

- Date: 2026-07-21
- Summary: Fixed OpenAI reasoning-model generation and comprehensively tested the updated model-selection workflow.
- Notable changes: Removed the optional `temperature` field from Responses API requests so `gpt-5.6-terra` and other reasoning models are compatible; added request, model-catalog, and AJAX autosave regressions; changed settings notifications to render server messages as text; clarified the model-catalog fallback note.
- Tool results: PHP and JavaScript syntax checks pass; `npm run check` pass (`check.txt` empty); `npm run phpmd` pass (`phpmd.txt` empty); `npm run test` pass (34 tests, 95 assertions); live helper generation pass for `gpt-5.6-terra`, automatic `gpt-5.5`, and `gpt-4o-mini`; full WordPress AJAX generation pass; live account catalog/render checks pass with 125 models; saved Centerstone model remains `gpt-5.6-terra`.
- Remaining gaps: Signed-in desktop/narrow visual and keyboard QA could not run because no browser backend was available. Live Google generation could not run because Centerstone has no Google API key configured, although its provider paths pass PHPUnit. WordPress Plugin Check is not installed in the local WP-CLI environment.

- Date: 2026-07-21
- Summary: Checkpointed the model compatibility work and compacted the Title Assistant on post editor screens.
- Notable changes: Created commit `a678bc8`; merged the results heading and actions into one toolbar; placed generation controls, primary metrics, alternate titles, and detailed analysis behind accessible disclosures; kept Apply and Revert available; made applying a title collapse only the current view without overwriting the saved panel preference; fixed Docker cache validation so a stale core cannot masquerade as the requested WordPress version.
- Tool results: PHP, JavaScript, and Docker shell syntax checks pass; `npm run fix`, `npm run check`, and `npm run phpmd` pass with empty reports; Docker PHPUnit passes against verified WordPress 7.0 core and local PHPUnit passes (34 tests, 95 assertions each); active Centerstone editor assets/localized labels pass a WP-CLI integration check; plugin remains active at 2.1.5; saved model remains `gpt-5.6-terra`.
- Remaining gaps: Signed-in desktop, narrow, and keyboard visual QA could not run because no browser backend was available.

- Date: 2026-07-21
- Summary: Finished the state-aware compact UX pass and modernized the WordPress compatibility matrix.
- Notable changes: Preserved goal, style, ellipsis, and keyword selections across every result rerender; added active option counts, Regenerate copy, accessible keyword state, automatic expansion for fresh results, and persistent-in-view Undo after Apply; pinned the stable gate to WordPress 7.0.2; added an always-fresh nightly gate; isolated downloaded core/test suites with tmpfs; added a localized-control regression test.
- Tool results: `npm run fix`, `npm run check`, and `npm run phpmd` pass with empty reports; PHP, JavaScript, and Docker shell syntax pass; local PHPUnit passes with 35 tests and 102 assertions; Docker PHPUnit passes on WordPress 7.0.2 and WordPress 7.1-beta2-62808 with 35 tests and 102 assertions each; live Centerstone localization integration passes; plugin remains active and saved model remains `gpt-5.6-terra`.
- Remaining gaps: Signed-in desktop, narrow, and keyboard visual QA could not run because no browser backend was available. WPDS guidance could not be queried because the configured service was unavailable.

- Date: 2026-07-21
- Summary: Fixed the missing Title Assistant launcher in the WordPress 7.0 Block Editor.
- Notable changes: Replaced ambiguous `.wp-editor-area` mode detection with positive Gutenberg detection using the `block-editor-page` body class and `core/editor` data store; added a regression protecting the detection contract.
- Tool results: Reproduced the failure in the signed-in Centerstone editor; confirmed the old runtime incorrectly reported Classic Editor while Gutenberg Code Editor was open; verified the restored sparkle in both Code Editor and settled Visual Editor iframe states; PHPCS and PHPMD pass with empty reports; local, WordPress 7.0.2, and WordPress 7.1-beta2-62808 PHPUnit runs pass with 36 tests and 106 assertions each.
- Remaining gaps: Compact generated-results interactions still need narrow-width and keyboard browser QA.

- Date: 2026-07-21
- Summary: Prepared and verified the WordPress.org 2.1.6 release candidate.
- Notable changes: Aligned all release metadata at 2.1.6; fixed the WordPress 5.0 compatibility guard and autosave sanitization finding; made diagnostics opt-in for new installs; completed uninstall cleanup for the customized-post-type flag and Gemini model cache; regenerated the POT catalog; documented external-service data flows; updated GitHub Actions to Node 24-compatible releases; scoped PHPMD to shipped runtime code.
- Tool results: PHPCS and PHPMD clean with empty reports; local, WordPress 7.0.2, and WordPress 7.1-beta2-62808 PHPUnit runs pass with 36 tests and 109 assertions; every PHP file parses on PHP 7.2; Composer audit found no advisories; JavaScript, shell, and workflow YAML syntax pass; standard and experimental Plugin Check 2.0 report no findings; fresh install, activation, uninstall cleanup, and reinstall pass on WordPress 7.0.2; final ZIP is 100 KB with SHA-256 `8f3c3d0d74c3fd33d2d7e69367e8aab02810b51f6687450eadabd48d0b2edf11`.
- Remaining gaps: Commit, push, tag, GitHub Release publication, and WordPress.org SVN deployment remain external release actions. The current real WordPress.org screenshots passed direct content and dimension review; no new browser capture was possible because the browser backend was unavailable.

- Date: 2026-07-21
- Summary: Reopened the 2.1.6 release after live editor regressions and rebuilt the test boundary around editor behavior.
- Notable changes: Added a standalone editor bridge; made Classic/Block detection depend on the page-level Gutenberg signal; restored the Classic title-row launcher; made Gutenberg Apply verify the canonical store with an iframe fallback; limited saved-result loading to one request; moved detailed analysis out of the nested disclosure; added five Node regression tests; embedded the screenshot gallery in `README.md`; tightened `.gitignore` and release-package exclusions; removed 4,994 generated cache/report files from Git tracking while preserving local ignored copies.
- Tool results: JavaScript suite passes 5/5; local PHPUnit passes 38 tests and 125 assertions; WordPress 7.0.2 and WordPress 7.1-beta2-62808 Docker suites each pass 38 tests and 125 assertions; PHPCS, PHPMD, PHP 7.2 syntax, JavaScript syntax, shell syntax, workflow YAML, Composer audit, standard Plugin Check, and experimental Plugin Check are clean. Packaged assets were verified in real WordPress 7.0.2 Block and forced-Classic admin HTML.
- Remaining gaps: The in-session browser backend is unavailable, so live click-through QA and replacement WordPress.org screenshots are still required before push/tag/release. The existing screenshot set remains unchanged and the release has not been published.
