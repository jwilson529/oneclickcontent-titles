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
