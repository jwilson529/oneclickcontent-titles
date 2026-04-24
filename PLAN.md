<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Prepare and publish the 2.1.2 release with GPT-5.5 OpenAI support, refreshed readmes, and existing WordPress.org visual assets.

## Assumptions
- Existing saved OpenAI model choices should remain unchanged on upgrade.
- New installs and unset OpenAI model fallbacks should use GPT-5.5.
- Screenshot guidance must only reference verified real plugin UI.
- WordPress admin chrome should remain cropped out of screenshot assets for the plugin page.

## Questions (non-blocking)
- None.

## Files to change
- readme.txt
- README.md
- MARKETING_PLAN.md
- oneclickcontent-titles.php
- package.json
- includes/class-occ-titles.php
- includes/class-occ-titles-activator.php
- admin/class-occ-titles-openai-helper.php
- admin/class-occ-titles-settings.php
- tests/class-optionstest.php
- tests/class-providerhelpertest.php
- PLAN.md
- MEMORY.md

## Steps
1. Verify GPT-5.5 availability against official OpenAI docs.
2. Bump release metadata from 2.1.1 to 2.1.2.
3. Update OpenAI defaults to GPT-5.5 while preserving saved settings.
4. Update readme and marketing copy for the release.
5. Add regression coverage for GPT-5.5 default/model-list behavior.
6. Run the release gate and publish the release.

## Commands to run
- npm run check
- npm run dist
- npm test

## Acceptance criteria
- Plugin metadata and readmes identify version 2.1.2.
- New installs default OpenAI generation to `gpt-5.5`.
- Existing saved OpenAI model choices are not overwritten on upgrade.
- Tests cover the GPT-5.5 default and OpenAI model-list exposure.
- `npm run check`, `npm run dist`, and `npm test` pass or any release blocker is documented.
- GitHub release `v2.1.2` is published and the release workflow result is checked.

## Run status
- `npm run check`: pass (`check.txt` empty)
- `npm run dist`: pass (`dist/oneclickcontent-titles.zip` created)
- `npm test`: pass (27 tests, 68 assertions)

## Latest run
- Date: 2026-04-24
- Summary: Prepared 2.1.2 with GPT-5.5 as the new OpenAI default for fresh installs, updated release/readme copy, and validated the package.
