=== OneClickContent - Titles ===
Contributors: jwilson529
Donate link: https://oneclickcontent.com/donate/
Tags: ai, seo, titles, openai, gemini
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.1.3
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free BYO-key AI title assistant for WordPress. Generate, score, compare, and apply stronger post titles with OpenAI or Gemini.

== Description ==

OneClickContent - Titles is a free BYO-key AI title assistant for WordPress. It helps writers, editors, marketers, and site owners generate multiple headline options from post content, compare them with useful quality signals, and apply the best title without leaving the editor.

Instead of locking you into a bundled AI subscription, OneClickContent keeps the model simple: use your own OpenAI or Google Gemini account, choose the provider and model you prefer, and keep control of usage directly with that provider.

This plugin is built for teams that want a practical workflow, not just raw AI output:

1. Generate multiple title options from the post you are already editing.
2. Compare the options with scoring, keyword fit, and preview signals.
3. Apply the best title instantly and keep moving.

That makes the plugin especially useful for:

- blog editors trying to improve headline quality without leaving WordPress
- content teams that want faster title ideation and comparison
- SEO-minded publishers who want keyword-aware title suggestions
- WordPress site owners who want AI help without SaaS lock-in

== Features ==

- Free plugin from the OneClickContent bring-your-own-key AI plugin line.
- Generate title options directly from post content inside the WordPress editor.
- Compare recommendations with scoring, keyword fit, preview width, and quality signals.
- Apply a chosen title without copying and pasting between screens.
- Support both OpenAI and Google Gemini so you can choose the provider that fits your workflow.
- Use GPT-5.5 as the default OpenAI model on new installs, with model choices still loaded from your OpenAI account.
- Load Google Gemini model choices from the API when available.
- Train editors with the built-in Title Help page and guided settings experience.
- Keep your workflow inside WordPress instead of bouncing between external AI tools and the editor.

== Screenshots ==

1. Generate title recommendations directly inside the WordPress post editor, then compare scores and apply the best option without leaving the page.
2. Configure your AI provider, enabled editor locations, diagnostics, and brand voice from the guided Title Assistant settings page.
3. See the in-editor generation workflow with content-aware guidance while a fresh title batch is created.

== Installation ==

1. Upload plugin files to `/wp-content/plugins/oneclickcontent-titles`.
2. Activate the plugin via the Plugins screen.
3. Go to `Settings -> Title Assistant`.
4. Add your API key and select your provider.
5. Open a post and click Generate Titles.

== Frequently Asked Questions ==

= Who is this plugin for? =

This plugin is for WordPress writers, editors, marketers, bloggers, and site owners who want better titles without leaving the editor. It is especially useful if you already work in WordPress and want AI help without adopting a separate SaaS workflow.

= Is the plugin free? =

Yes. The plugin is free. OneClickContent's model is bring your own API key and pay your AI provider directly only if that provider charges for usage.

= Do I need API keys? =

Yes. Add a valid OpenAI or Google Gemini API key in settings.

= Why is it bring your own key? =

OneClickContent is built around a BYO-key model so you keep control of provider choice, usage, and cost. That also means there is no bundled AI subscription required just to use the plugin.

= Which providers are supported? =

OpenAI and Google Gemini are supported. OpenAI and Gemini model choices are loaded from the provider APIs when available so the lists stay current without plugin updates.

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

= 2.1.3 =

- Shorten the WordPress.org short description so the plugin directory import no longer truncates it.
- Keep the 2.1.2 GPT-5.5 default OpenAI model update intact.

= 2.1.2 =

- Add GPT-5.5 as the default OpenAI model for new installs and unset model fallbacks.
- Keep OpenAI model selection loaded from the OpenAI Models API so GPT-5.5 appears when the connected account has access.
- Refresh the WordPress.org release package with updated banner, icon, and cropped real-plugin screenshots.
- Update GitHub and WordPress.org readme copy for the current release.

= 2.1.1 =

- Enable posts and pages by default for new installs.
- Normalize the old posts-only default to include pages unless the post-type setting was manually customized.
- Fix missing editor controls on page edit screens caused by the legacy default.

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

= 2.1.3 =
Readme correction release. The WordPress.org short description is now within the plugin directory limit.

= 2.1.2 =
Recommended update. New installs default to GPT-5.5 for OpenAI title generation, while existing saved model choices remain unchanged.

= 2.1.1 =
Recommended update. Pages are now enabled by default on fresh installs, older posts-only defaults normalize safely to include pages, and missing page editor controls are fixed.

= 2.1.0 =
Free bring-your-own-key release with live Gemini model loading, stronger Gemini response handling, and a more approachable admin experience.

= 2.0.1 =
Hardening release with generation cooldown protection, uninstall cleanup, and reduced API key validation request volume.

= 2.0.0 =
Major release with improved title generation, scoring, and editor workflows.
