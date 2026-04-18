# OneClickContent - Titles

![Plugin Banner](assets/banner-772x250.png)

Version: 2.1.0

Free AI title assistant for WordPress. Bring your own OpenAI or Google Gemini API key to generate, compare, and apply post titles directly in the editor.

## Key Features

- Free plugin with a bring-your-own-key model.
- Generate multiple title options from post content.
- Compare options with clear title quality signals.
- Apply winning titles directly in the editor.
- Support for OpenAI and Google Gemini providers.
- Load Google Gemini model choices from the API when available.
- Built-in training and help screens for editorial teams.

## What Is New In v2.1.0

- Repositioned the plugin as a free, bring-your-own-key AI title assistant for WordPress.
- Added live Google Gemini model loading from the Models API with caching and safe fallbacks.
- Switched Gemini generation to structured JSON output and improved provider error handling.
- Refined the editor, settings, and help screens for a simpler editorial workflow.
- Tightened the release package so `npm run dist` produces an install-ready zip.

## Quick Start

1. Install and activate the plugin.
2. Go to `Settings -> Title Assistant`.
3. Configure provider and API key.
4. Open a post in the editor.
5. Click **Generate Titles**.
6. Compare results and click **Apply** on the best one.

## Pricing Model

This plugin is free. You bring your own OpenAI or Google Gemini API key and pay your provider directly, if that provider charges for usage.

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

### 2.1.0

- Free bring-your-own-key positioning and documentation refresh for release.
- Added live Google Gemini model loading and safer structured-output parsing.
- Improved editor, settings, and help UX.
- Tightened dist packaging and install-ready zip validation.

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
