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
