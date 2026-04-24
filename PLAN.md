<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal
Prepare the release-facing documentation and WordPress.org visual assets without changing plugin code.

## Assumptions
- Existing plugin architecture and feature behavior should remain unchanged (safe modernization).
- Current repository has intentional non-task edits in docs and should not be reverted.
- Screenshot guidance must only reference verified real plugin UI.
- WordPress admin chrome should be cropped out of screenshot assets for the plugin page.

## Questions (non-blocking)
- None.

## Files to change
- readme.txt
- README.md
- MARKETING_PLAN.md
- RELEASE.md
- assets/banner-1544x500.png
- assets/banner-772x250.png
- assets/icon-128x128.png
- assets/icon-256x256.png
- assets/screenshot-1.png
- assets/screenshot-2.png
- assets/screenshot-3.png
- PLAN.md
- MEMORY.md

## Steps
1. Update `readme.txt` screenshot captions to match the release screenshot sequence.
2. Add a WordPress.org screenshot set section to `README.md`.
3. Add the screenshot capture plan to `MARKETING_PLAN.md`.
4. Add screenshot release gates to `RELEASE.md`.
5. Generate updated banner/icon artwork and crop verified screenshots.
6. Run the release check and document the result.

## Commands to run
- npm run check
- npm run dist
- npm test

## Acceptance criteria
- `readme.txt` has final WordPress.org captions ready to paste/publish.
- `MARKETING_PLAN.md` documents the final screenshot order, shot contents, captions, and capture notes.
- `RELEASE.md` includes a screenshot gate so asset/caption mismatches are caught before publish.
- No code changes are made for this task.
- WordPress.org banner and icon assets exist at the expected dimensions.
- Screenshot assets are cropped to remove WordPress admin chrome.
- `npm run check`, `npm run dist`, and `npm test` pass or any release blocker is documented.

## Run status
- `npm run check`: pass (`check.txt` empty)
- `npm run dist`: pass (`dist/oneclickcontent-titles.zip` created)
- `npm test`: pass (25 tests, 62 assertions)

## Latest run
- Date: 2026-04-24
- Summary: Prepared WordPress.org screenshot documentation plus updated banner, icon, and cropped screenshot assets without code changes.
