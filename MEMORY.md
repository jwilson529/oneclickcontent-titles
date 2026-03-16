<!-- GENERATED_BY_CODEX_YOLO_MEMORY_V1 -->
# Project Memory

This file is persistent context for Codex runs. Keep it short and practical.

## Project
- Plugin slug: oneclickcontent-titles
- Repo root: /Users/jameswilson/Local Sites/wp-clean-install/app/public/wp-content/plugins/oneclickcontent-titles

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
- The build zip process uses .gitignore to decide what ships. Do not add dev artifacts to the distributable.

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
