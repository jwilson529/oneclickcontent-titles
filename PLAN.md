<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Prepare and publish the 2.1.5 WordPress 7.0 compatibility release.

## Assumptions
- Existing saved OpenAI model choices should remain unchanged on upgrade.
- The GPT-5.5 default from 2.1.2 should remain intact.
- The `readme.txt` short description must be 150 characters or fewer.
- Docker PHPUnit must run against WordPress 7.0, not a stale cached core version.
- Screenshot guidance must only reference verified real plugin UI.

## Questions (non-blocking)
- None.

## Files to change
- readme.txt
- README.md
- MARKETING_PLAN.md
- RELEASE.md
- bin/docker-tests.sh
- docker-compose.yml
- oneclickcontent-titles.php
- package.json
- includes/class-occ-titles.php
- PLAN.md
- MEMORY.md

## Steps
1. Update the Docker PHPUnit harness so requested WordPress versions refresh cached test core and test-suite files.
2. Set the default test WordPress version to 7.0.
3. Bump release metadata from 2.1.4 to 2.1.5.
4. Update WordPress.org metadata to `Tested up to: 7.0`.
5. Run the release gate and publish the release.

## Commands to run
- npm run check
- npm run dist
- npm test

## Acceptance criteria
- Plugin metadata and readmes identify version 2.1.5.
- `readme.txt` short description is 150 characters or fewer.
- Docker PHPUnit output confirms active WordPress core version `7.0`.
- New installs still default OpenAI generation to `gpt-5.5`.
- Existing saved OpenAI model choices are not overwritten on upgrade.
- `npm run check`, `npm run dist`, and `npm test` pass or any release blocker is documented.
- GitHub release `v2.1.5` is published and the release workflow result is checked.

## Run status
- `npm run check`: pass (`check.txt` empty)
- `npm run dist`: pass (`dist/oneclickcontent-titles.zip` created)
- `npm test`: pass on WordPress 7.0 (27 tests, 68 assertions)

## Latest run
- Date: 2026-05-21
- Summary: Prepared 2.1.5 for WordPress 7.0 compatibility and passed the local release gate against WordPress 7.0.
