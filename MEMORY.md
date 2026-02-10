<!-- GENERATED_BY_CODEX_YOLO_MEMORY_V1 -->
# Project Memory

This file is persistent context for Codex runs. Keep it short and practical.

## Project
- Plugin slug: oneclickcontent-titles
- Repo root: /Users/jameswilson/Local Sites/wp-clean-install/app/public/wp-content/plugins/oneclickcontent-titles

## Non-negotiables
- WordPress Coding Standards (WPCS).
- Tabs for indentation in code.
- No em dashes in assistant output.
- When updating code you must output full updated methods or full updated files when requested.
- Prefer WPPB structure: includes/, admin/, public/, tests/.
- Do not stop mid-run. Keep moving forward in the SPEC. Test and lint.

## Tooling workflow
- Primary loop:
  - npm run fix
  - npm run check (writes check.txt)
- Treat check.txt as the backlog.

## Packaging
- The build zip process uses .gitignore to decide what ships. Do not add dev artifacts to the distributable.

## Run recap log
Append a brief recap after each run:
- Date:
- Summary:
- Notable changes:
- Tool results:
- Remaining gaps:

- Date: 2026-02-01
- Summary: Renamed and refactored test files to satisfy WPCS, added in-memory filesystem test helpers, and adjusted logger to support injectable filesystem.
- Notable changes: Added test helper classes, updated logger filter, aligned phpunit test discovery suffix, cleaned test implementations.
- Tool results: npm run fix (clean), npm run check (clean), phpunit (12 tests OK).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Added Discover and Top Stories guidance to SPEC and wired intent-based prompt rules for both OpenAI and Google helpers.
- Notable changes: New Discover intent option in admin UI; prompt guidance appended when intent mentions Discover or Top Stories.
- Tool results: npm run fix (clean), npm run check (clean), phpunit (12 tests OK).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Fixed style dropdown population by defining options before rendering controls.
- Notable changes: Moved styles options array creation earlier in display_titles.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Added Discover-style preview card when Discover or Top Stories intent is selected.
- Notable changes: New preview builder and styling for Discover card, preview column now adapts to intent.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Preview now refreshes on intent changes so Discover selection shows card layout immediately.
- Notable changes: Stored pixel metrics on rows and added intent change handler.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Added a curiosity ellipsis toggle and passed it through generation prompts for both providers.
- Notable changes: New UI toggle, AJAX payload additions, helper prompt guidance.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Refined Discover preview markup and styling to better match Google card layout.
- Notable changes: Added top-stories label, source row with favicon, and tuned card spacing/typography.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Added writer-friendly pixel target guidance and explanations in the preview and controls.
- Notable changes: Updated SERP and Discover preview descriptions plus pixel meta copy.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Moved pixel guidance to a single help/footer note and used the real post slug/permalink in previews.
- Notable changes: Localized post data for previews and simplified SERP description.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Moved keyword/pixel guidance to a top walkthrough bar and removed the footer for a cleaner, writer-friendly UI.
- Notable changes: Added guidance cards and refined header/controls/table styling.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Added panel collapse control and a "Score Current Title" action to compare the live headline.
- Notable changes: New header buttons, collapsible results panel, current-title row badge and styling.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Added a temporary success state to the Score Current Title button.
- Notable changes: Button flashes "Scored" with green styling after insertion.
- Tool results: npm run fix (clean), npm run check (clean).
- Remaining gaps: None noted.
- Date: 2026-02-01
- Summary: Expanded .gitignore to keep the repo lean by excluding local test and Codex artifacts.
- Notable changes: Ignored .wp-core/.wp-tests, Codex metadata, coverage, screenshots, and local tooling files.
- Tool results: Not run (docs/config only).
- Remaining gaps: None noted.
