<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Prepare and publish the 2.1.3 corrective release with a WordPress.org-safe short description.

## Assumptions
- Existing saved OpenAI model choices should remain unchanged on upgrade.
- The GPT-5.5 default from 2.1.2 should remain intact.
- The `readme.txt` short description must be 150 characters or fewer.
- Screenshot guidance must only reference verified real plugin UI.

## Questions (non-blocking)
- None.

## Files to change
- readme.txt
- README.md
- MARKETING_PLAN.md
- RELEASE.md
- oneclickcontent-titles.php
- package.json
- includes/class-occ-titles.php
- PLAN.md
- MEMORY.md

## Steps
1. Shorten the `readme.txt` short description under 150 characters.
2. Add a release-process guard for the short-description limit.
3. Bump release metadata from 2.1.2 to 2.1.3.
4. Update readme and marketing copy for the corrective release.
5. Run the release gate and publish the release.

## Commands to run
- npm run check
- npm run dist
- npm test

## Acceptance criteria
- Plugin metadata and readmes identify version 2.1.3.
- `readme.txt` short description is 150 characters or fewer.
- New installs still default OpenAI generation to `gpt-5.5`.
- Existing saved OpenAI model choices are not overwritten on upgrade.
- `npm run check`, `npm run dist`, and `npm test` pass or any release blocker is documented.
- GitHub release `v2.1.3` is published and the release workflow result is checked.

## Run status
- `npm run check`: pass (`check.txt` empty)
- `npm run dist`: pass (`dist/oneclickcontent-titles.zip` created)
- `npm test`: pass (27 tests, 68 assertions)

## Latest run
- Date: 2026-04-24
- Summary: Prepared 2.1.3 to correct the WordPress.org short-description import warning and passed the local release gate.
