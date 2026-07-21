# Development Handoff

Date: 2026-07-21

## Start Here

- Repository: `/Users/jameswilson/oneclickcontent-titles`
- Centerstone Local site: `/Users/jameswilson/Local Sites/centerstone/app/public`
- Active plugin path is a symlink from the Centerstone site to this repository:
  `/Users/jameswilson/Local Sites/centerstone/app/public/wp-content/plugins/oneclickcontent-titles`
- Preview site: `http://centerstone.local/`
- Settings screen: `http://centerstone.local/wp-admin/options-general.php?page=occ_titles-settings`
- The working tree contains intentional, uncommitted development changes. Do not restore or discard them.

Before editing, read `AGENTS.md`, `SPEC.md`, `MEMORY.md`, `PLAN.md`, `PLAYBOOK.md`, and this file.

## Centerstone State

- Plugin is active at version 2.1.5.
- Selected provider: `openai`.
- Saved OpenAI model: `gpt-5.6-terra`.
- That model is an explicit existing choice and must remain unchanged unless James changes it in the UI.
- No production site changes have been made. This work is only on the local Centerstone site and source repository.

## Work Completed in This Uncommitted Branch

The current diff includes all of the following conversation work:

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
- `npm run test` passed in Docker: 34 tests, 95 assertions.
- Docker test containers and network were removed with `docker compose down`.
- WP-CLI confirmed the Centerstone plugin is active and the saved `gpt-5.6-terra` choice remains intact.
- Live generation passed with valid structured output for saved `gpt-5.6-terra`, automatic `gpt-5.5`, and legacy-tested `gpt-4o-mini` without changing the saved option.
- The complete WordPress AJAX generation handler passed with nonce, capability, sanitization, provider routing, and success-response checks exercised together.
- Live OpenAI model validation returned 125 account models.
- Rendering the callback through Centerstone WP-CLI confirmed `gpt-5.6-terra` appears under `Your current choice`, automatic resolves visibly to `gpt-5.5`, and the advanced disclosure/search markup is present.
- Live Google generation could not run because Centerstone has no Google API key configured; Google request construction, response parsing, catalog filtering/caching, and automatic model resolution pass PHPUnit.
- `wp plugin check` could not run because Plugin Check is not installed as a WP-CLI subcommand in the Centerstone environment.

## Remaining Work

1. Open the settings screen in a signed-in browser and visually test desktop and narrow widths. The in-app browser was unavailable in the interrupted session, so no visual click-through was completed.
2. Confirm these interactions:
   - `Automatic (Recommended)` shows `gpt-5.5` as the current resolved OpenAI model.
   - Centerstone initially continues to show `gpt-5.6-terra` as its saved current choice.
   - Opening `Choose another model`, searching an exact live model ID, and clicking `Use model` updates the main selector and autosaves once.
   - Keyboard focus is visible on the advanced disclosure, search, button, and Editor Location cards.
3. Do not commit, push, package, or change the local saved model unless James explicitly asks.

## Useful Restart Commands

```bash
cd /Users/jameswilson/oneclickcontent-titles
git status --short
git diff --check
npm run check
npm run test
wp --path='/Users/jameswilson/Local Sites/centerstone/app/public' plugin status oneclickcontent-titles
wp --path='/Users/jameswilson/Local Sites/centerstone/app/public' option get occ_titles_openai_model
```
