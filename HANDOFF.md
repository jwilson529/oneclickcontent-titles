# Development Handoff

Date: 2026-07-21

## Start Here

- Repository: `/Users/jameswilson/oneclickcontent-titles`
- Centerstone Local site: `/Users/jameswilson/Local Sites/centerstone/app/public`
- Active plugin path is a symlink from the Centerstone site to this repository:
  `/Users/jameswilson/Local Sites/centerstone/app/public/wp-content/plugins/oneclickcontent-titles`
- Preview site: `http://centerstone.local/`
- Settings screen: `http://centerstone.local/wp-admin/options-general.php?page=occ_titles-settings`
- Model compatibility and settings work is checkpointed in commit `a678bc8`.
- The 2.1.6 release candidate work after the model checkpoint is intentional and validated. Do not restore or discard it.
- Repository hygiene is checkpointed in `d0816d8` (`chore: stop tracking local WordPress harnesses`).

Before editing, read `AGENTS.md`, `SPEC.md`, `MEMORY.md`, `PLAN.md`, `PLAYBOOK.md`, and this file.

## Centerstone State

- Plugin source is version 2.1.6.
- Selected provider: `openai`.
- Saved OpenAI model: `gpt-5.6-terra`.
- That model is an explicit existing choice and must remain unchanged unless James changes it in the UI.
- No production site changes have been made. This work is only on the local Centerstone site and source repository.

## Checkpointed Model Work

Commit: `a678bc8` (`fix: support current generation models`)

That commit includes all of the following conversation work:

1. Removed the duplicate `Title Help` Settings-menu item while keeping the help screen routable and linked from Title Assistant.
2. Reduced excessive border radii using the plugin's 4px, 8px, and 12px radius tokens.
3. Reworked Editor Locations so each whole card is the checkbox control, with hover, selected, and keyboard-focus states. The separate `Select` label is gone.
4. Reworked provider model selection:
   - Main control now offers `Automatic (Recommended)` and a short list of tested choices.
   - New installs store `auto`; existing saved model selections are not migrated or overwritten.
   - OpenAI automatic resolves to the release-tested `gpt-5.5` default.
   - Google automatic resolves to `gemini-2.5-flash`.
   - The complete live provider model catalog is behind `Choose another model` and a searchable native datalist.
   - A model from the live list is only saved after the user explicitly chooses it.
   - Unknown/new models are described as advanced and potentially untested.
   - Explicit saved models that are not curated are pinned under `Your current choice`.
   - OpenAI model IDs returned by `/v1/models` are now validated, deduplicated, and naturally sorted.
   - Model values are sanitized in both Settings API and AJAX autosave paths.
   - Runtime helpers resolve `auto` immediately before generation and Google key validation.
5. Fixed OpenAI generation for reasoning models:
   - Responses API requests no longer send the optional `temperature` parameter.
   - This fixes the live `gpt-5.6-terra` error: `Unsupported parameter: 'temperature' is not supported with this model.`
   - The omission is model-agnostic because the account catalog can contain arbitrary models and `/v1/models` does not provide a dependable temperature-capability flag.
   - Settings notifications now insert server messages as text instead of interpolating them into HTML.
   - The empty-catalog note now says to validate the API key rather than implying it is necessarily unsaved.

## Current Editor Work

The post editor Title Assistant now uses a compact, editor-only layout:

- One toolbar contains status, Generate Titles, Options, and Collapse results.
- After results exist, the primary action reads Regenerate and Options shows an active-option count when generation choices are set.
- Generation options remain full-sized but are hidden by default.
- Goal, style, ellipsis, and selected keyword state survive generation, rescoring, iteration, and saved-result reloads.
- The top recommendation is a compact row with Apply and Revert.
- Metrics and the explanation remain available under Details.
- Remaining recommendations and the full scoring, preview, copy, and CSV tools use separate disclosures so detailed analysis opens directly with one click.
- Fresh results expand automatically. Opening Options from a minimized panel expands directly to the controls.
- Applying a title collapses the current view without changing the user's saved collapse preference and leaves an immediate Undo action in the toolbar.
- Keyword chips expose their selected state through `aria-pressed`.
- Editor detection now uses WordPress' `block-editor-page` body class. It deliberately does not use `core/editor` store availability because that store can exist on Classic Editor screens.
- A small editor bridge owns title reads and writes. Gutenberg Apply dispatches exactly once through `core/editor`, verifies the edited title, and falls back to the visible iframe field only when the store did not update. Classic Apply emits both `input` and `change` events.
- Saved results are requested only once after the results container exists, preventing background rerenders from closing an open Details disclosure.
- The Classic launcher is anchored inside `#titlewrap` with reserved title-field padding instead of relying on absolute positioning relative to an input sibling.
- Gutenberg now binds directly to the editor-canvas iframe load lifecycle, so switching from Code Editor back to Visual Editor restarts injection checks and restores the sparkle beside the title after the canvas settles.
- Settings screens and saved provider/model choices are unchanged.
- Docker cache validation now compares the actual installed core version with the requested version before reusing a cached WordPress checkout.
- `npm test` and `npm run test:stable` run the JavaScript editor bridge suite and target exact WordPress 7.0.2. `npm run test:next` runs the same JavaScript coverage plus nightly core and the trunk test suite.
- Docker core and test-suite downloads use container tmpfs mounts, so test runs cannot dirty tracked `.wp-core` or `.wp-tests` fixtures.

## Model-Selection Decision

Do not automatically promote the newest model returned by a provider. Provider catalogs do not reliably communicate compatibility, price, latency, or suitability for title generation.

The live OpenAI resolver identified `gpt-5.6-sol` as the current flagship on 2026-07-21, but the official migration guidance explicitly warns against blindly mapping cost-sensitive or picker-based workloads to Sol. The current implementation therefore keeps automatic routing on the already tested `gpt-5.5` until a representative title-generation evaluation supports changing it. New models still appear immediately in the advanced account list.

Official guidance consulted:

- `https://developers.openai.com/api/docs/guides/upgrading-to-gpt-5p6-sol.md`
- `https://developers.openai.com/api/docs/guides/prompt-guidance-gpt-5p6.md`

## Validation Already Completed

- PHP syntax checks passed for every plugin and test PHP file.
- JavaScript syntax checks passed for every admin/public JavaScript file.
- `git diff --check` passed.
- `npm run fix` passed with no remaining violations.
- `npm run check` passed with an empty `check.txt`.
- `npm run phpmd` passed with an empty `phpmd.txt`.
- `npm run test:js` passed: 5 tests covering Classic detection, Block detection, Classic title changes, one-shot Gutenberg store updates, and iframe fallback.
- `npm run test:stable` passed in Docker against exact WordPress 7.0.2: 38 PHP tests, 130 assertions, plus the 5 JavaScript tests.
- `npm run test:next` passed in Docker against WordPress 7.1-beta2-62813: 38 PHP tests, 130 assertions, plus the 5 JavaScript tests.
- `npm run test:local` passed: 38 tests, 130 assertions.
- Docker test containers and network were removed with `docker compose down`.
- WP-CLI confirmed the Centerstone plugin is active and the saved `gpt-5.6-terra` choice remains intact.
- Live generation passed with valid structured output for saved `gpt-5.6-terra`, automatic `gpt-5.5`, and legacy-tested `gpt-4o-mini` without changing the saved option.
- The complete WordPress AJAX generation handler passed with nonce, capability, sanitization, provider routing, and success-response checks exercised together.
- Live OpenAI model validation returned 125 account models.
- Rendering the callback through Centerstone WP-CLI confirmed `gpt-5.6-terra` appears under `Your current choice`, automatic resolves visibly to `gpt-5.5`, and the advanced disclosure/search markup is present.
- Live Google generation could not run because Centerstone has no Google API key configured; Google request construction, response parsing, catalog filtering/caching, and automatic model resolution pass PHPUnit.
- Official Plugin Check 2.0 standard and experimental update-mode scans passed with no findings against the installed release ZIP on WordPress 7.0.2.
- Separate disposable WordPress 7.0.2 Playground sites confirmed that the packaged CSS, editor bridge, main script, localized data, and SVG URL are present on real Block and Classic Editor pages. The Block page has the canonical `block-editor-page` signal; the Classic page has `#titlewrap/#title` and no Block Editor signal.
- The active Centerstone editor enqueues the compact assets and localized labels, including Regenerate, Options, Details, and Undo; the saved model remains `gpt-5.6-terra`.
- A PHPUnit regression now protects the compact editor localization contract.
- Live Centerstone browser QA reproduced and fixed the missing Block Editor launcher. Code Editor shows the sparkle below the title, switching to Visual Editor keeps a header fallback during canvas loading, and the settled iframe shows the sparkle beside the title.
- Live WordPress 7.0.2 browser QA passed for the Classic, Block Visual, and Block Code launchers; first-click Details; Apply; plugin Undo; and the Code-to-Visual iframe transition on the exact packaged plugin.
- Isolated Chrome QA performed real OpenAI generation, captured the loading and results states, reported no runtime exceptions, and confirmed the settings page has no horizontal overflow at a 600px viewport.

## WordPress.org Release Candidate

- Version metadata is aligned at 2.1.6 in the plugin header, runtime constant, package, GitHub readme, WordPress.org readme, changelog, and POT catalog.
- `dist/oneclickcontent-titles.zip` now includes the editor bridge and remains a single-root, runtime-only package.
- Final ZIP SHA-256: `8a928b3e4cdf26aa337cf5fc2838980fd6bbfe91ed6121ad7c911608a8b0a954` (101,608 bytes).
- The ZIP installs and activates on WordPress 7.0.2. A real uninstall cycle removed every plugin option, saved results meta, the Gemini model cache, and log artifacts; reinstall then activated with diagnostics off by default.
- Every PHP file parses under the declared PHP 7.2 minimum. Composer audit reports no known advisories.
- `assets/screenshot-1.png` through `screenshot-3.png` are current real-plugin captures from the WordPress 7.0.2 QA site. They match the readme order, expose no API key, omit browser chrome, and show recommendations, guided settings, and the loading workflow.
- `.gitignore` now keeps harnesses, reports, browser artifacts, secrets, dependencies, and root-level ad hoc captures out of Git while leaving `assets/screenshot-*.png` trackable. The 4,993 tracked WordPress core/test cache files plus tracked `check.txt` were removed from the index; local copies remain ignored.
- Release workflow action runtimes are current and Node 24-compatible: checkout v7, setup-node v7, upload-artifact v7, and action-gh-release v3.

## Remaining Release Actions

1. Commit the complete release candidate, push it, and verify the `CI and Release` push workflow.
2. Create and publish `v2.1.6`; the release workflow will deploy to WordPress.org SVN and attach the ZIP.
3. Verify the public WordPress.org page and screenshots after SVN propagation.
4. Do not change the local saved model unless James explicitly chooses another model.

## Useful Restart Commands

```bash
cd /Users/jameswilson/oneclickcontent-titles
git status --short
git diff --check
npm run check
npm run test:stable
npm run test:next
wp --path='/Users/jameswilson/Local Sites/centerstone/app/public' plugin status oneclickcontent-titles
wp --path='/Users/jameswilson/Local Sites/centerstone/app/public' option get occ_titles_openai_model
```
