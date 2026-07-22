=== OneClickContent - Titles ===
Contributors: jwilson529
Donate link: https://oneclickcontent.com/donate/
Tags: ai, seo, titles, openai, gemini
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 2.1.7
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
- Use GPT-5.5 as the tested automatic OpenAI choice while preserving explicit saved model selections.
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

== External Services and Privacy ==

This plugin connects to the OpenAI API or Google Gemini API only for the provider features you configure. It does not include tracking or send data to OneClickContent.

When you validate an API key or load available models, the plugin sends the saved provider key to that provider. Google validation also sends a short test prompt. When you choose Generate Titles, the plugin sends the post content, title-generation instructions, selected controls, and any configured brand-voice guidance to the selected provider. The provider may charge for API usage according to your account plan.

API keys and generated title results are stored in your WordPress database. Optional troubleshooting logs are disabled by default on new installations. If you enable them, they can contain request metadata; API keys are redacted. Uninstalling the plugin removes its options, saved title results, and log artifacts.

- OpenAI terms of use: https://openai.com/policies/terms-of-use/
- OpenAI privacy policy: https://openai.com/privacy
- Google Gemini API terms: https://ai.google.dev/gemini-api/terms
- Google privacy policy: https://policies.google.com/privacy

== Changelog ==

= 2.1.7 =

- Restore the in-canvas Gutenberg launcher after switching from Code Editor back to Visual Editor.
- Add regression coverage for editor-canvas iframe reloads.
- Verify the packaged plugin across Classic, Block Visual, and Block Code editor workflows.
- Refresh the WordPress.org screenshots from the final WordPress 7.0.2 release-candidate UI.
- Revalidate real OpenAI generation, responsive settings, WordPress stable and nightly tests, Plugin Check, and release packaging.

= 2.1.6 =

- Fix OpenAI title generation for models that reject the `temperature` parameter.
- Add a tested automatic model choice while preserving explicit saved model selections.
- Make the editor workflow more compact with state-aware Options, Undo, and result controls.
- Restore the Title Assistant launcher in Classic Editor plus the Block Editor Visual and Code views.
- Make Apply update the canonical Gutenberg title store, with a visible-field fallback for editor compatibility.
- Prevent background result reloads from resetting Details and Detailed analysis interactions.
- Keep diagnostic logging off by default on new installations and document external-service data flows.
- Complete uninstall cleanup, WordPress 5.0 compatibility guards, translation updates, and release-tooling hardening.
- Verify the release against WordPress 7.0.2 and the WordPress nightly test suite.

= 2.1.5 =

- Add WordPress 7.0 compatibility metadata after testing against WordPress 7.0.
- Pin the Docker PHPUnit harness to WordPress 7.0 by default and refresh cached test core when the requested WordPress version changes.

= 2.1.4 =

- Refresh the WordPress.org SVG icon so it matches the current PNG icon artwork.

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

= 2.1.7 =
Recommended editor reliability update. Restores the Gutenberg launcher after Code-to-Visual switches and refreshes the verified release package and screenshots.

= 2.1.6 =
Recommended compatibility and editor UX update. Fixes generation with newer OpenAI models and restores reliable launch, details, and title application across supported editors.

= 2.1.5 =
WordPress 7.0 compatibility release. The plugin has been tested against WordPress 7.0.

= 2.1.4 =
WordPress.org asset correction release. The SVG icon now matches the current PNG icon artwork.

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
