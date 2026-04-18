=== OneClickContent - Titles ===
Contributors: jwilson529
Donate link: https://oneclickcontent.com/donate/
Tags: ai, seo, titles, openai, gemini
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free AI title assistant for WordPress. Bring your own OpenAI or Google Gemini API key to generate, compare, and apply titles in the editor.

== Description ==

OneClickContent - Titles is a free AI title assistant for WordPress. Bring your own API key, generate several headline options from post content, compare them with clear quality signals, and apply the best title without leaving the editor.

This plugin is part of the free OneClickContent bring-your-own-key AI plugin line. You use your own provider account, choose your preferred model, and keep control of your usage directly with OpenAI or Google Gemini.

Writers and editors get a practical title workflow:

1. Click Generate Titles.
2. Compare recommended options with quality signals.
3. Apply the best title.

== Features ==

- Free plugin with no bundled AI markup. Bring your own OpenAI or Google Gemini API key.
- Generate title options directly from post content in the editor.
- Compare recommendations with scoring, keyword fit, and quality signals.
- Apply a chosen title without copying and pasting between screens.
- Load Google Gemini model choices from the API when available.
- Train editors with the built-in Title Help page and guided settings experience.

== Installation ==

1. Upload plugin files to `/wp-content/plugins/oneclickcontent-titles`.
2. Activate the plugin via the Plugins screen.
3. Go to `Settings -> Title Assistant`.
4. Add your API key and select your provider.
5. Open a post and click Generate Titles.

== Frequently Asked Questions ==

= Is the plugin free? =

Yes. The plugin is free. You bring your own API key and pay your AI provider directly, if your provider charges for usage.

= Do I need API keys? =

Yes. Add a valid OpenAI or Google Gemini API key in settings.

= Which providers are supported? =

OpenAI and Google Gemini are supported. Gemini model choices are loaded from the API when available so the list stays current without plugin updates.

= Where is the training page? =

Go to `Settings -> Title Help`.

= Does the plugin store my content? =

The plugin stores generated title suggestions inside WordPress so you can review and apply them later. Post content is sent to your selected provider only when you generate titles.

= Does this replace writer judgment? =

No. It accelerates ideation and scoring, but editors should still validate clarity and accuracy.

== Privacy ==

The plugin sends post content to your selected provider for title generation.

- OpenAI privacy policy: https://openai.com/privacy
- Google privacy policy: https://policies.google.com/privacy

== Changelog ==

= 2.1.0 =
* Repositioned the plugin as a free, bring-your-own-key AI title assistant for WordPress.
* Added live Google Gemini model loading from the Models API with caching and safe fallbacks.
* Switched Gemini generation to structured JSON output and improved provider error handling.
* Refined the editor, settings, and help experiences for a clearer, simpler workflow.
* Tightened release packaging and installable zip validation.

= 2.0.1 =
* Added server-side cooldown protection for title generation requests to reduce accidental bursts.
* Updated API key validation to run on field completion (blur/change) instead of per-keystroke events.
* Production hardening and stability improvements.
* Added deterministic uninstall cleanup for plugin options, saved title results, and log artifacts.
* Reworked help and training content so wp-admin no longer depends on remote placeholder assets.

= 2.0.0 =
* Major release with substantial workflow, scoring, and settings improvements.
* Refined editor experience for generating, comparing, and applying titles.
* Documentation refresh for product onboarding and distribution.

= 1.1.0 =
* Added Google Gemini provider support.
* Improved title generation workflow and settings.
* Added richer scoring and title comparison experience.

== Upgrade Notice ==

= 2.1.0 =
Free bring-your-own-key release with live Gemini model loading, stronger Gemini response handling, and a more approachable admin experience.

= 2.0.1 =
Hardening release with generation cooldown protection, uninstall cleanup, and reduced API key validation request volume.

= 2.0.0 =
Major release with improved title generation, scoring, and editor workflows.
