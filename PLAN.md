<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Converge the plugin to SPEC/AGENTS baseline with deterministic tooling and tests: WPCS clean, WPPB structure validated, PHPUnit and logger wired.

## Assumptions
- Existing plugin architecture and feature behavior should remain unchanged (safe modernization).
- Current repository has intentional non-task edits in docs and should not be reverted.

## Questions (non-blocking)
- None.

## Files to change
- package.json
- .codex_index.json
- PLAN.md
- MEMORY.md

## Steps
1. Read AGENTS/SPEC plus run-state files; validate plugin structure and guardrails.
2. Run `npm run fix` and `npm run check`; use `check.txt` as backlog until clean.
3. Validate local PHPUnit execution path and lock scripts/config to repo-pinned runner.
4. Refresh index and run-memory artifacts.

## Commands to run
- npm run fix
- npm run check
- npm run test:local

## Acceptance criteria
- PHPCS clean (0 errors, 0 warnings) or explain why not possible.
- Tests pass (if configured).

## Run status
- `npm run fix`: pass
- `npm run check`: pass (`check.txt` empty)
- `npm run test:local`: pass (12 tests, 26 assertions) after pinning runner to `vendor/bin/phpunit -c phpunit.xml`
