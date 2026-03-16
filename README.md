# OneClickContent - Titles

![Plugin Banner](assets/banner-772x250.png)

Version: 2.0.1

Generate, score, and apply SEO-friendly post titles directly in the WordPress editor using OpenAI or Google Gemini.

## Key Features

- Generate multiple title options from post content.
- Score each option with title quality signals.
- Apply winning titles directly in the editor.
- Support for OpenAI and Google Gemini providers.
- Built-in training page for editorial teams.

## What Is New In v2.0.1

- Added server-side cooldown protection for title generation requests to reduce accidental bursts and API cost spikes.
- Updated settings behavior so API key validation runs on field completion (blur/change) instead of per-keystroke.
- Added deterministic uninstall cleanup for plugin-owned options, saved title results, and log artifacts.
- Reworked the help page so wp-admin no longer depends on remote placeholder assets.
- Continued hardening of production readiness while preserving existing editor workflows.

## Quick Start

1. Install and activate the plugin.
2. Go to `Settings -> OCC - Titles`.
3. Configure provider and API key.
4. Open a post in the editor.
5. Click **Generate Titles**.
6. Compare results and click **Apply** on the best one.

## Training Page

The plugin includes an editor training page at:

- `Settings -> Title Help`

It includes:

- Step-by-step usage guidance.
- Title quality best practices.
- Control and label definitions.
- Self-contained placeholder panels your team can replace with local screenshots later.

## Privacy

This plugin sends post content to your selected provider for title generation.

- OpenAI: https://openai.com/privacy
- Google: https://policies.google.com/privacy

## Changelog

### 2.0.1

- Added per-user/per-post generation cooldown enforcement on AJAX title generation.
- Changed API key validation trigger to field completion (`change`/`blur`) instead of continuous typing events.
- Stability and hardening update for production deployments.

### 2.0.0

- Major release with workflow, scoring, settings, and training improvements.
- Documentation refresh for both GitHub and WordPress.org distribution.

### 1.1.0

- Added Google Gemini provider support.
- Improved title generation workflow and settings.
- Added richer scoring and title comparison experience.

## License

GPLv2 or later.
