<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Push the plugin over the production-readiness threshold for real-world use: remove admin placeholder dependencies, make uninstall deterministic, harden logging behavior, and keep tooling green.

## Assumptions
- Existing plugin architecture and feature behavior should remain unchanged (safe modernization).
- Current repository has intentional non-task edits in docs and should not be reverted.

## Questions (non-blocking)
- None.

## Files to change
- SPEC.md
- admin/class-occ-titles-settings.php
- includes/class-occ-titles-logger.php
- includes/class-occ-titles-uninstaller.php
- uninstall.php
- tests/class-loggertest.php
- tests/class-uninstallertest.php
- .codex_index.json
- PLAN.md
- MEMORY.md

## Steps
1. Tighten SPEC/plan with explicit production-readiness gates.
2. Remove third-party placeholder dependencies from the admin help UI.
3. Implement deterministic uninstall cleanup for plugin-owned options, post meta, and log artifacts.
4. Improve logger append behavior to avoid whole-file rewrites on direct filesystem paths.
5. Add or update PHPUnit coverage for the logger and uninstall paths.
6. Run `npm run fix`, `npm run check`, and `npm run test:local`; document any blocked secondary path.

## Commands to run
- npm run fix
- npm run check
- npm run test:local
- npm run test

## Acceptance criteria
- No remote placeholder assets are used in wp-admin for plugin help/training content.
- Uninstall removes plugin-owned options, saved meta, and log files/directories when present.
- Logger avoids whole-file rewrites on direct filesystem paths while remaining testable.
- PHPCS clean (0 errors, 0 warnings) or explain why not possible.
- `npm run test:local` passes.

## Run status
- `npm run fix`: pass
- `npm run check`: pass (`check.txt` empty)
- `npm run test:local`: pass (13 tests, 34 assertions)
- `npm run test`: fail in Docker test bootstrap (`ERROR 2005 (HY000): Unknown server host 'db' (-2)`)

## Latest run
- Date: 2026-03-13
- Summary: Added production-readiness gates and hardened the plugin for initial real-world use by removing remote admin placeholders, adding deterministic uninstall cleanup, and improving logger append behavior.
