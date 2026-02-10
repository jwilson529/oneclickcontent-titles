<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Slim the repository by ignoring local test/runtime folders and artifacts.

## Assumptions
- .wp-core and .wp-tests are local-only.

## Questions (non-blocking)
- None.

## Files to change
- .gitignore
- PLAN.md
- MEMORY.md

## Steps
1. Add local test/runtime folders and artifacts to .gitignore.
2. Confirm ignore coverage for Codex artifacts and screenshots.

## Commands to run
- npm run fix
- npm run check
- phpunit (if configured)

## Acceptance criteria
- .gitignore excludes .wp-core, .wp-tests, and related local artifacts.
