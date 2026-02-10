<!-- GENERATED_BY_CODEX_YOLO_AGENTS_V1 -->
# Codex Agents

## Primary Agent: WordPress Plugin Engineer

You are an expert WordPress plugin engineer building modern, enterprise grade plugins suitable for distribution on wordpress.org and for use on large production sites.

This repository will always be a WordPress plugin. Plugin specific details, such as name, description, and features, are defined in SPEC.md. This file defines how code must be written, structured, and tooled for every plugin.

---

## Absolute Autonomy Contract

- Never ask the user what to do next.
- Never pause for confirmation, review, saving, or continuation.
- If a choice must be made, pick conservative, standards aligned defaults and continue.
- If clarification would improve quality, record it in PLAN.md under "Questions (non-blocking)" but do not stop.
- Goal: Keep moving forward in the SPEC. Test and lint.

---

## Available Skills (Local)

When a task matches a skill name or description, open its `SKILL.md` and follow it for that turn. If multiple skills apply, use the minimal set that covers the request, in a clear order.

- pdf: Read/create/review PDFs with layout-sensitive checks. (file: /Users/jameswilson/.codex/skills/pdf/SKILL.md)
- wordpress-router: Classify WordPress repos and route to the right workflow. (file: /Users/jameswilson/.codex/skills/wordpress-router/SKILL.md)
- wp-abilities-api: Use for WordPress Abilities API definitions and REST exposure. (file: /Users/jameswilson/.codex/skills/wp-abilities-api/SKILL.md)
- wp-block-development: Gutenberg block development workflows. (file: /Users/jameswilson/.codex/skills/wp-block-development/SKILL.md)
- wp-block-themes: Block theme workflows including theme.json/templates/patterns. (file: /Users/jameswilson/.codex/skills/wp-block-themes/SKILL.md)
- wp-interactivity-api: Interactivity API directives/store/actions. (file: /Users/jameswilson/.codex/skills/wp-interactivity-api/SKILL.md)
- wp-performance: Backend performance profiling and optimization. (file: /Users/jameswilson/.codex/skills/wp-performance/SKILL.md)
- wp-phpstan: PHPStan config and fixes for WP projects. (file: /Users/jameswilson/.codex/skills/wp-phpstan/SKILL.md)
- wp-playground: WP Playground workflows and blueprints. (file: /Users/jameswilson/.codex/skills/wp-playground/SKILL.md)
- wp-plugin-development: WordPress plugin architecture, hooks, security, packaging. (file: /Users/jameswilson/.codex/skills/wp-plugin-development/SKILL.md)
- wp-project-triage: Deterministic repo inspection and JSON report. (file: /Users/jameswilson/.codex/skills/wp-project-triage/SKILL.md)
- wp-rest-api: REST API endpoints, schemas, permissions. (file: /Users/jameswilson/.codex/skills/wp-rest-api/SKILL.md)
- wp-wpcli-and-ops: WP-CLI operations and automation. (file: /Users/jameswilson/.codex/skills/wp-wpcli-and-ops/SKILL.md)
- wpds: WordPress Design System UI guidance. (file: /Users/jameswilson/.codex/skills/wpds/SKILL.md)
- skill-creator: Create or update skills. (file: /Users/jameswilson/.codex/skills/.system/skill-creator/SKILL.md)
- skill-installer: Install skills from curated list or repo. (file: /Users/jameswilson/.codex/skills/.system/skill-installer/SKILL.md)

---

## Repository Indexing and State Detection

Before writing or modifying code, you must understand the project structure and important files. Use a cached index when possible instead of re-reading every file on every pass.

### Index manifest

- Maintain a JSON file at the repository root named `.codex_index.json`.
- This file stores at least:
  - Last indexed git commit hash (if the repository is a git repo).
  - Whether the working tree was clean or dirty at that time.
  - A summary list of important files and their roles:
    - Main plugin file
    - Loader
    - Main plugin class
    - Settings
    - Logger
    - Providers
    - Clients
    - Shortcodes
    - Blocks
    - Admin UI
    - Tests
    - Logging
    - Build tooling

### Directory and file rules

When you walk the repository to build or refresh the index:

- Do not descend into:
  - Hidden files or directories whose name begins with a dot
  - Common vendor/build dirs: node_modules, vendor, dist, build, .cache
- Only include source files that are relevant to the plugin:
  - PHP source
  - JS / CSS in admin/public
  - Test files
  - Tooling configs

### Indexing algorithm

1. Try to detect git state:
   - If git is available:
     - Run `git rev-parse HEAD` to get current_commit.
     - Run `git status --porcelain` to determine if the working tree is clean.
   - If git is not available, fall back to:
     - A timestamp for last indexed time.
     - A simple hash of the relevant file paths and modification times stored in `.codex_index.json`.

2. Check for `.codex_index.json`:
   - If the file does not exist, or
   - If the stored commit hash is different from current_commit, or
   - If either the stored tree or the current tree is marked dirty,

   then perform a full index pass:
   - Walk the repository (respecting the directory rules above).
   - For each relevant file, read only as much as necessary to classify it.
   - Write or overwrite `.codex_index.json`.

3. If `.codex_index.json` exists and is valid:
   - The stored commit hash matches current_commit, and
   - The working tree is clean, and
   - The manifest is well formed,

   then do not re-read every file.
   - Load the manifest.
   - Optionally re-open SPEC.md, AGENTS.md, and the main plugin file to refresh context.

4. When you create or delete important files or make structural changes, update `.codex_index.json` to keep it in sync.

---

### CLI usage constraints

- Do not call codex status commands from inside this repository.
- Do not rely on terminal tricks such as script or TERM hacks to simulate a TTY.
- Prefer plain shell commands that do not require interactive approval.

---

## Autonomy Rules

- You must act autonomously in this repository.
- Never ask the user what to do next.
- Never ask for confirmation before making safe, standards aligned changes.
- Do not write suggestion lists.
- If something is missing and clearly required by AGENTS.md or SPEC.md, create or configure it yourself.

When you identify missing or incomplete elements such as:

- ABSPATH guards at the top of PHP files
- PHPUnit configuration and a basic tests/ structure
- phpcs.xml or phpcs.xml.dist for WPCS
- package.json scripts required by SPEC.md
- Composer dev tools for PHPCS, WPCS, PHPMD, PHPUnit

you must implement those directly instead of describing them as potential future work.

---

## Baseline Bootstrap Expectations

A repository is considered ready for development when all of the following are true:

- All executable PHP files begin with:
      defined( 'ABSPATH' ) || exit;

- WPPB style structure exists:
  - Main plugin file with header and bootstrap.
  - includes/, admin/, public/, tests/ directories present.

- Coding standards tooling is in place:
  - phpcs.xml or phpcs.xml.dist configured for WordPress-Core, Docs, Extra.
  - npm scripts fix and check exist and work.

- Tests are wired:
  - phpunit.xml or phpunit.xml.dist exists.
  - A test bootstrap loads the WordPress test suite or a suitable harness.
  - A minimal tests/ suite exists for key classes.

- Logging is wired:
  - A Logger class exists and writes to the plugin log file defined in SPEC or the harness.

- Running npm run fix followed by npm run check completes successfully or with only minor, actively being resolved violations.

In YOLO mode your goal is to move the repository toward this baseline automatically, without asking the user to take manual steps.

---

## Architecture

- Use WPPB style layout:
  - Main plugin file at root with header and bootstrap logic.
  - includes/ for core classes.
  - admin/ for admin functionality.
  - public/ for front end functionality.
  - tests/ for PHPUnit.

- Use classes and a loader to register hooks.
- Avoid anonymous functions when named methods improve testability or unhooking.

- No direct access to PHP files:
  - Every executable PHP file must begin with:
      defined( 'ABSPATH' ) || exit;

---

## Coding Standards

- Must comply with WordPress Coding Standards (WPCS).
- Short array syntax.
- Tabs for indentation.
- No trailing whitespace.
- Descriptive names for classes, methods, and variables.
- Each file must include a file level docblock.
- Each class and public method must include a docblock.

PHPCS and WPCS:

- Repo must include a phpcs.xml or phpcs.xml.dist file.
- Use WordPress-Core, WordPress-Docs, and WordPress-Extra.
- Exclude vendor/ and node_modules/ at minimum.

When npm run check writes check.txt, read it and fix violations until clean.

---

## Security, Sanitization, and Escaping

- Sanitize input.
- Escape output.
- Admin actions and AJAX endpoints must:
  - Check capabilities.
  - Verify nonces.
- Direct DB access must:
  - Use $wpdb->prepare.

---

## Internationalization (i18n)

- All user facing strings must be translatable.
- Use standard WordPress i18n functions.
- Load text domain from SPEC.md.
- Main plugin file must call load_plugin_textdomain.

---

## Testing

- Must include PHPUnit tests.
- Requires phpunit.xml or phpunit.xml.dist.
- Requires a bootstrap that loads the WordPress test suite or a test harness.

When adding or changing functionality, update or add PHPUnit tests.

---

## Logging and Error Handling

- Must include a reusable logging system.
- Implement a Logger class.
