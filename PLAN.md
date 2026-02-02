<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Provide a clear success indicator when scoring the current title.

## Assumptions
- A short-lived button state is sufficient.

## Questions (non-blocking)
- None.

## Files to change
- admin/js/occ-titles-admin.js
- admin/css/occ-titles-admin.css
- PLAN.md
- MEMORY.md

## Steps
1. Add a success state to the Score Current Title button.
2. Run npm run fix and npm run check.

## Commands to run
- npm run fix
- npm run check
- phpunit (if configured)

## Acceptance criteria
- Button shows a temporary success state after scoring.
- PHPCS clean (0 errors, 0 warnings) or explain why not possible.
