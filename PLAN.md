<!-- GENERATED_BY_CODEX_YOLO_PLAN_V1 -->
# Plan

Codex must keep this file updated during each run.

## Goal

Finish and comprehensively verify model compatibility and the simplified settings UX for Centerstone, including live title generation with the updated OpenAI model choices.

## Assumptions

- Existing saved model choices must remain unchanged on upgrade.
- New installs should default to `Automatic (Recommended)`.
- Automatic recommendations are release-controlled and compatibility-tested, not selected by model recency alone.
- Newly available provider models should remain discoverable through an advanced search.
- Centerstone's current explicit `gpt-5.6-terra` selection must remain intact.

## Questions (non-blocking)

- None.

## Files in the Current Change Set

- `.codex_index.json`
- `HANDOFF.md`
- `PLAN.md`
- `admin/class-occ-titles-admin.php`
- `admin/class-occ-titles-google-helper.php`
- `admin/class-occ-titles-openai-helper.php`
- `admin/class-occ-titles-settings.php`
- `admin/css/occ-titles-admin.css`
- `admin/js/occ-titles-settings.js`
- `includes/class-occ-titles-activator.php`
- `tests/class-optionstest.php`
- `tests/class-providerhelpertest.php`

## Steps

1. Preserve the already completed help-menu, border-radius, and Editor Locations improvements.
2. Keep the new simple/advanced model picker and automatic runtime resolution behavior.
3. Remove unsupported optional sampling parameters from OpenAI Responses API requests.
4. Add regression coverage for automatic and explicit reasoning-model payloads, model catalogs, and AJAX autosave.
5. Test provider generation through both the helper and the complete WordPress AJAX handler.
6. Run PHP/JS syntax checks, PHPCS, PHPMD, and Docker PHPUnit.
7. Visually test the settings screen in Centerstone Local at desktop and narrow widths when a browser backend is available.
8. Update project memory and hand off without committing or pushing unless requested.

## Acceptance Criteria

- The main model control does not expose the giant provider list.
- Automatic clearly shows the actual release-tested model it resolves to.
- New provider models remain searchable in the advanced disclosure.
- An advanced model is never selected without an explicit user action.
- Existing saved models remain visible and selected.
- Editor Location cards are fully clickable and keyboard accessible.
- The duplicate help menu item remains removed while its screen remains reachable.
- OpenAI requests work with both reasoning and legacy model choices without sending unsupported `temperature` parameters.
- API and settings messages are rendered as text rather than interpolated as HTML.
- `npm run check` and `npm run test` pass.

## Run Status

- OpenAI `gpt-5.6-terra` helper generation: pass with valid structured output
- OpenAI automatic `gpt-5.5` helper generation: pass with valid structured output
- OpenAI legacy `gpt-4o-mini` helper generation: pass with valid structured output
- Full WordPress AJAX generation handler: pass with a successful response envelope
- Live OpenAI catalog/render checks: pass (125 models; saved, automatic, and advanced-picker markup verified)
- PHP syntax checks: pass for every plugin and test PHP file
- JavaScript syntax checks: pass for every admin/public JavaScript file
- `npm run check`: pass (`check.txt` empty)
- `npm run phpmd`: pass (`phpmd.txt` empty)
- `npm run test`: pass (34 tests, 95 assertions)
- Centerstone saved model preservation: pass (`gpt-5.6-terra`)
- Browser visual/click/keyboard QA: pending because the browser runtime reported no available browser backends
- Live Google generation: unavailable because Centerstone has no Google API key configured; Google request, response, catalog, and automatic-resolution paths remain covered by PHPUnit
- WordPress Plugin Check: unavailable because `wp plugin check` is not installed in the Centerstone WP-CLI environment

## Latest Run

- Date: 2026-07-21
- Summary: Fixed reasoning-model generation compatibility, hardened settings notifications, and completed automated plus live provider validation. Visual QA remains blocked by browser availability.
