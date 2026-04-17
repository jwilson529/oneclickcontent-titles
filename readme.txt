=== OneClickContent - Titles ===
Contributors: jwilson529
Donate link: https://oneclickcontent.com/donate/
Tags: ai, seo, titles, content-optimization, content-enhancement
Requires at least: 5.0
Tested up to: 6.9.1
Stable tag: 1.1.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free BYO-key AI title generation and scoring for WordPress with OpenAI or Google Gemini.

== Description ==

OneClickContent - Titles gives writers and editors a practical, free bring-your-own-key workflow:

1. Click Generate Titles.
2. Compare recommended options.
3. Apply the best title.

Why teams use it:

- Free plugin with no hosted credit layer.
- Uses your own OpenAI or Google Gemini API key.
- Scores titles for intent, keyword fit, readability, and preview width.
- Works directly in the editor.
- Includes a built-in Title Help page with bundled screenshots and training notes.

This release includes a built-in training page for teams:

- Settings -> Title Help

Title Help includes:

- Step-by-step usage training.
- Best practices for title quality and SEO.
- Definitions for each UI label and score signal.
- Bundled screenshots from the plugin interface.

== Installation ==

1. Upload plugin files to `/wp-content/plugins/oneclickcontent-titles`.
2. Activate via the Plugins screen.
3. Go to `Settings -> OCC - Titles`.
4. Choose your provider and add your API key.
5. Open a post and click Generate Titles.

== Training Workflow ==

Use this flow to train editors:

1. Open post editor and launch title panel.
2. Set Goal, Style, and optional keyword targets.
3. Generate titles and compare Score, Insights, and Preview.
4. Apply the best row and publish.

Bundled help screenshots ship with the plugin in the `assets/` directory and are used by the Title Help screen inside wp-admin.

== Label Reference ==

- Goal: Optimization target for generation.
- Style: Headline format template.
- Curiosity ellipsis: Allows `...` ending style.
- Keyword targets: Selected keywords to prioritize.
- Score: Overall quality score.
- Pass / Needs work: Quick quality gate.
- Length: Title length guidance.
- Keyword fit: Density quality signal.
- Density: Keyword density percentage.
- Preview + Pixel meter: Truncation guidance by pixel width.
- Best / Current: Highest-scored row and existing title markers.
- Apply / Undo / Iterate: Main title actions.

== Frequently Asked Questions ==

= Where is the training page? =

Go to `Settings -> Title Help`.

= Do I need API keys? =

Yes. Add a valid OpenAI or Google Gemini API key in settings.

= Is this a subscription product? =

No. The plugin is free. You bring your own provider key and your usage is billed directly by OpenAI or Google based on your own account.

= Does this replace writer judgment? =

No. It accelerates ideation and scoring, but editors should still validate clarity and accuracy.

== Privacy ==

The plugin sends post content to your selected provider for title generation and key validation.

- OpenAI privacy policy: https://openai.com/privacy
- Google privacy policy: https://policies.google.com/privacy

== Changelog ==

= 1.1.1 =
* Repositioned the plugin around a free bring-your-own-key workflow.
* Hardened remote error handling so provider failures are surfaced as safe plain text.
* Moved Google Gemini API key transport out of request URLs and into headers.
* Replaced shipped placeholder help images with bundled plugin screenshots.
* Stabilized the repo test harness for clean-checkout runs.

= 1.1.0 =
* Added Google Gemini provider support.
* Improved title generation workflow and settings.
* Added richer scoring and title comparison experience.

== Upgrade Notice ==

= 1.1.1 =
Hardens provider handling, updates the BYO-key release copy, and ships bundled help screenshots.
