<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Remove unnecessary local artifacts, harden `.gitignore`, and ensure distribution zips include only runtime plugin files.

## Assumptions
- Generated screenshots/logs/check outputs are not required source files.
- Runtime plugin package should only include files needed for WordPress execution and plugin directory metadata.

## Questions (non-blocking)
- None for this pass.

## Files to change
- .gitignore
- package.json
- bin/build-dist.sh
- .codex_index.json
- PLAN.md
- MEMORY.md

## Steps
1. Clean generated artifacts from repo root.
2. Tighten `.gitignore` to ignore local/runtime clutter and build outputs.
3. Replace dist blacklist with an allowlist packager script.
4. Build and inspect zip contents to confirm no test/dev files ship.

## Commands to run
- npm run dist
- unzip -l oneclickcontent-titles.zip

## Acceptance criteria
- `.gitignore` blocks generated artifacts and local harness folders.
- `npm run dist` produces a zip with runtime plugin files only.
- No tests/dev/config artifacts are included in zip.
