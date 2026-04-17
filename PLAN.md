<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Ship a release-hardening pass in small, PR-sized slices: document the work in SPEC, fix security and secret-handling risks, polish shipped release assets/copy, and converge with tests plus the npm fix/check loop.

## Assumptions
- Existing plugin architecture and feature behavior should remain unchanged unless a security fix requires a small compatibility-safe adjustment.
- Current repository has intentional non-task edits in docs and repo-local `.codex*` artifacts that should not be reverted.
- Release metadata can be updated when supported by the completed implementation and verification performed in this run.

## Questions (non-blocking)
- None.

## Files to change
- SPEC.md
- PLAN.md
- admin/class-occ-titles-openai-helper.php
- admin/class-occ-titles-google-helper.php
- admin/class-occ-titles-settings.php
- admin/js/occ-titles-admin.js
- bin/docker-tests.sh
- bin/ensure-composer-deps.sh
- docker-compose.yml
- oneclickcontent-titles.php
- README.md
- readme.txt
- tests/class-optionstest.php
- tests/class-mainclasstest.php
- tests/class-providerhelpertest.php
- additional tests as needed
- package.json
- phpunit.xml
- phpunit.xml.dist
- .codex_index.json
- MEMORY.md

## Steps
1. Expand SPEC and PLAN with explicit release-hardening slices and acceptance criteria.
2. Implement slice 2: sanitize remote/provider error handling and harden admin error rendering with tests.
3. Implement slice 3: stabilize the test harness so the repo-supported test path runs from this checkout.
4. Implement slice 4: remove Gemini API keys from request URLs and cover request construction with tests.
5. Implement slice 5: replace shipped placeholder assets, rename/update `readme.txt`, and refresh BYO-key release copy with tests where practical.
6. After each slice, run `npm run fix`, `npm run check`, and the relevant test path before moving on.
7. Refresh index and memory artifacts once all slices are green.

## Commands to run
- npm run fix
- npm run check
- npm run test:local
- npm test

## Acceptance criteria
- Each release slice meets its SPEC acceptance criteria before the next begins.
- PHPCS clean (0 errors, 0 warnings) or explain the blocker precisely.
- Tests pass on an available repo-supported path, or any infrastructure blocker is explicit and justified.

## Run status
- Slice 1: completed
- Slice 2: completed
- Slice 3: completed
- Slice 4: completed
- Slice 5: completed
- Slice 6: completed
- `npm run fix`: pass
- `npm run check`: pass (`check.txt` empty)
- `npm run test:local`: pass (18 tests, 36 assertions)
- `npm test`: pass (18 tests, 36 assertions)
