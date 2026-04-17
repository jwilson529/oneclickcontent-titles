<!-- GENERATED_BY_CODEX_YOLO_SPEC_V1 -->
# SPEC

This SPEC defines how Codex operates inside this repository and how it coordinates with AGENTS.md, tooling, and cached state.

Codex must read the following files before making changes:
- AGENTS.md
- SPEC.md
- MEMORY.md
- PLAN.md
- PLAYBOOK.md

## Absolute Execution Rules

- Never pause to ask for confirmation, review, saving, or continuation.
- Never block on unanswered questions.
- If a choice must be made, pick conservative, standards aligned defaults and continue.
- If clarification would improve quality, record it in PLAN.md under "Questions (non-blocking)" but do not stop.
- Goal: Keep moving forward in the SPEC. Test and lint.

---

## 1. Repository Assumptions

This repository is always a WordPress plugin. The root directory contains:

- AGENTS.md
- SPEC.md (this file)
- PLAYBOOK.md
- package.json (contains fix and check scripts)
- WPPB-style folders: includes/, admin/, public/, tests/
- Optional runner artifacts:
  - .codex_index.json
  - .codex_run_meta.json
  - .codex_tasks.csv
  - .codex-backups/

If any required part is missing or incomplete, Codex must create or repair it autonomously.

---

## 2. Codex Execution Modes

Codex may be invoked under different modes. These modes are advisory and allow flexibility.

Supported modes include:

- yolo (full autonomy, default)
- fix-only (linters and autofixes, no structural work)
- test-only (focus on PHPUnit)
- analyze (classify repo and improve by changes)
- scaffold (create missing WPPB structure, tests, logging, tooling)
- bootstrap (scaffold then converge)
- release (optional, future)

If no mode is specified, treat the run as yolo.

---

## 3. Workflow: Fix, Check, Converge

Codex must use an iterative convergence loop:

1. Apply changes based on AGENTS.md and repository state.
2. Run npm run fix
3. Run npm run check (writes check.txt)
4. Read check.txt and treat violations as the backlog
5. Continue until check.txt shows:
   - errors == 0 and warnings == 0
   or the runner reaches the iteration cap

Notes:
- If tests fail and block execution, fix tests first.
- Do not stop mid-run. Keep moving forward.

---

## 4. Tests and Diagnostics

If PHPUnit, PHPMD, or other tools exist:

- Tools must be respected.
- Failures must not be ignored.
- Test failures should be corrected before chasing style violations when failures block execution.
- New code should have test coverage where appropriate.

---

## 5. Indexing, Cached State, and Manifest

Codex may use .codex_index.json to reduce full repo re-reads:

- If manifest commit matches current commit and tree is clean:
  - Use the manifest and avoid full re-index.
- If commit differs or tree is dirty:
  - Re-index and rewrite the manifest.

---

## 6. Modernization Policy: Safe vs Surgery

Codex must read the MODERNIZE_POLICY environment variable when set.

### 6.1 Safe (default)

If MODERNIZE_POLICY is safe or unset:

- Preserve existing structure as much as possible.
- Do not refactor large procedural sections into class-based architecture unless trivial.
- Do not change public APIs, hook names, or function signatures.
- Do not remove files or rename directories.
- May add:
  - PHPCS fixes
  - ABSPATH guards
  - logging
  - tests
  - i18n wrappers
  - sanitization and escaping
  - fixes for deprecated APIs
  - security fixes
  - WPPB scaffolding only if the plugin is basically empty or missing structure

### 6.2 Surgery

If MODERNIZE_POLICY is surgery, Codex may:

- Create or rearrange WPPB directories
- Extract procedural code into classes
- Split monolithic files into smaller modules
- Consolidate duplicated logic
- Add or update tests for extracted logic

### 6.3 Behavioral Guarantees

Regardless of policy, Codex must:

- Preserve functional behavior unless tests require adaptation
- Maintain plugin headers, slugs, and text domains
- Keep or improve security posture
- Converge toward PHPCS clean where possible
- Update or add tests when behavior changes in surgery mode

---

## 7. Archival and Backup Behavior

Backups, metadata, run summaries, or changelogs may be written to:

- .codex-backups/
- .codex_index.json
- .codex_run_meta.json
- .codex_tasks.csv

Codex must treat these artifacts as protected and must not lint or rewrite them.

---

## 8. Uncertainty Policy

When requirements are unclear:

- Choose the most conservative, standards aligned implementation.
- Prefer scaffolding, interfaces, placeholders, or TODOs over blocking execution.
- Never ask the user to choose between approaches mid-run.
- Record notes in PLAN.md if helpful, but continue.

---

## 9. Completion Criteria

A session is considered converged when:

- check.txt indicates 0 errors and 0 warnings, or the runner hits its iteration limit after making best effort progress
- tests pass (when configured)
- AGENTS.md rules are satisfied
- the repo is in a better state than it started

---

## 10. Summary of Required Behavior

- Follow AGENTS.md and this SPEC.
- Use npm run fix and npm run check to converge.
- Treat check.txt as the backlog.
- Create missing elements instead of asking for guidance.
- Use cached index when valid.
- Maintain autonomy and keep moving forward.

---

## 11. Release Hardening Slices

When the user requests a release-readiness pass, re-release prep, or a ship blocker review, Codex must convert findings into small, PR-sized delivery slices and complete them sequentially.

### 11.1 Slice Format

Each slice must include:

- A short scope statement.
- A concrete file list.
- Explicit acceptance criteria.
- Required tests to add or update.
- A green gate:
  - npm run fix passes
  - npm run check produces 0 errors and 0 warnings
  - the relevant test command passes

Do not start the next slice until the current slice is green or there is a clearly documented blocker.

### 11.2 Required Release Sequence For This Plugin

For this repository, release-hardening work must be executed in the following order unless a blocking dependency forces a different order:

1. SPEC and plan update
2. Remote error and admin rendering hardening
3. Test harness stabilization
4. API key transport hardening
5. Release copy, bundled assets, and metadata polish
6. Final convergence pass across linting, tests, and manifest state

### 11.3 Slice 1: SPEC and Plan Update

Scope:
- Record release findings in the repo contract files so the work is traceable and repeatable.

Files:
- SPEC.md
- PLAN.md
- MEMORY.md when the run completes

Acceptance criteria:
- SPEC includes release-hardening slice rules with acceptance criteria.
- PLAN reflects the active slice order and current run status.
- The documented workflow matches the actual implementation order used in the run.

Tests:
- No dedicated unit tests required for documentation-only updates.

Green gate:
- npm run fix
- npm run check

### 11.4 Slice 2: Remote Error and Admin Rendering Hardening

Scope:
- Prevent provider-supplied or network-sourced error strings from becoming executable or unsafe admin HTML.
- Ensure remote failures are surfaced to users as plain text.

Required implementation outcomes:
- Sanitize remote provider error messages on the server before returning them to AJAX callers.
- Do not return raw HTML fragments, JSON blobs, or remote response bodies directly to wp-admin.
- Admin JavaScript must render error text safely and must not inject untrusted strings with `.html()`.
- Existing success behavior for title generation must remain intact.

Files expected to change:
- admin/class-occ-titles-openai-helper.php
- admin/class-occ-titles-google-helper.php if shared error handling is introduced there
- admin/class-occ-titles-admin.php if shared sanitization is coordinated there
- admin/js/occ-titles-admin.js
- tests/ for helper and admin coverage

Acceptance criteria:
- Provider error messages are reduced to plain text, human-readable failures.
- HTML-bearing remote error messages do not render as markup in the admin UI.
- Title generation success responses remain structurally unchanged.

Tests:
- Add or update PHPUnit coverage for sanitized remote error handling.
- Assert HTML-bearing provider errors are normalized to safe plain text.
- Assert the AJAX-facing helper behavior still returns arrays on success and safe strings on failure.

Green gate:
- npm run fix
- npm run check
- test command covering updated helper/admin behavior passes

### 11.5 Slice 3: Test Harness Stabilization

Scope:
- Make the repo-supported test paths usable from a clean or near-clean checkout so slice gates can actually be closed green.

Required implementation outcomes:
- The canonical repo test path must install or bootstrap required test dependencies when they are missing, or fail fast with an explicit prerequisite message.
- The Docker test path must not assume `vendor/autoload.php` already exists if the repository does not commit `vendor/`.
- If `npm run test:local` remains dependency-sensitive, it must use the project-pinned PHPUnit path and fail clearly instead of crashing obscurely.

Files expected to change:
- bin/docker-tests.sh
- tests/bootstrap.php
- package.json
- phpunit.xml or phpunit.xml.dist if needed
- tests/ if bootstrap assumptions change

Acceptance criteria:
- `npm test` is capable of running from the repository checkout used in this run.
- Test bootstrap failures are replaced by deterministic dependency bootstrapping or explicit diagnostics.
- The chosen fix does not weaken the existing test harness expectations.

Tests:
- Verify the repo-supported test command now executes far enough to run PHPUnit instead of failing on missing Composer autoload files.
- Add or update coverage only if bootstrap logic is extracted into testable helpers.

Green gate:
- npm run fix
- npm run check
- npm test

### 11.6 Slice 4: API Key Transport Hardening

Scope:
- Remove avoidable API key leakage from outbound requests and release logs.

Required implementation outcomes:
- Gemini API requests must not place the API key in the request URL when a supported header-based alternative exists.
- Request construction must prefer header transport for secrets.
- Logs and surfaced messages must not include raw API keys.

Files expected to change:
- admin/class-occ-titles-google-helper.php
- tests/ covering outbound request construction

Acceptance criteria:
- Gemini `generateContent` and validation requests omit the key from the URL.
- Gemini requests send the key through headers.
- Test coverage proves the endpoint no longer contains the secret and the header does.

Tests:
- Add PHPUnit coverage that captures outbound `wp_remote_post()` arguments.
- Assert request URL does not contain the API key.
- Assert request headers contain the expected authentication header.

Green gate:
- npm run fix
- npm run check
- test command covering Google helper request construction passes

### 11.7 Slice 5: Release Copy, Bundled Assets, and Metadata Polish

Scope:
- Make the shipped plugin feel complete and aligned with the intended free BYO-key positioning.

Required implementation outcomes:
- Remove third-party placeholder imagery from the shipped admin help experience.
- Prefer bundled assets already in the repository for help/training screens.
- Update release copy to emphasize the plugin as a free, bring-your-own-key AI title workflow for WordPress.
- Align WordPress.org-facing metadata files and naming with repository expectations.
- `readme.txt` must exist in lowercase for plugin-directory compatibility.

Files expected to change:
- admin/class-occ-titles-settings.php
- oneclickcontent-titles.php
- README.md
- README.txt moved or replaced as readme.txt
- tests/ for new helper or asset URL behavior where practical

Acceptance criteria:
- No shipped admin help step depends on `placehold.co`.
- WordPress.org readme exists as `readme.txt`.
- Plugin header and readme short descriptions reflect free BYO-key positioning.
- Metadata is internally consistent for the release version being prepared.

Tests:
- Add PHPUnit coverage for any new helper methods that assemble local help asset URLs or release metadata helpers.
- Where logic is extracted for testability, assert bundled asset URLs point to local plugin assets.

Green gate:
- npm run fix
- npm run check
- relevant PHPUnit coverage passes

### 11.8 Slice 6: Final Convergence

Scope:
- Confirm the repository is green after all release slices and refresh cached repo state.

Required implementation outcomes:
- Re-run the fix/check/test loop after the last code slice.
- Refresh PLAN.md run status.
- Refresh MEMORY.md recap.
- Refresh .codex_index.json if important files or structure changed.

Acceptance criteria:
- All completed slices are green.
- Remaining gaps, if any, are concrete and minimal.
- Final summary distinguishes between completed work and any external blocker such as missing local dependencies or privileged test infrastructure.

Tests:
- Use the repo’s canonical test path when available.

Green gate:
- npm run fix
- npm run check
- available test path passes
