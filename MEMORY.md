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

- Date: 2026-04-17
- Summary: Completed a release-hardening pass for the free BYO-key relaunch with explicit SPEC slices, safer provider handling, stabilized tests, and refreshed release copy/assets.
- Notable changes: Expanded `SPEC.md` with PR-sized release slices and acceptance criteria; sanitized provider error handling and admin rendering; moved Gemini API keys from URLs into request headers; added `bin/ensure-composer-deps.sh`; updated Docker/local test bootstrapping; switched shipped help images to bundled assets; renamed `readme.txt`; refreshed plugin/readme metadata to version `1.1.1`.
- Tool results: `npm run fix` pass; `npm run check` pass; `npm run test:local` pass (18 tests, 36 assertions); `npm test` pass (18 tests, 36 assertions).
- Remaining gaps: None identified in the current release-hardening scope.
