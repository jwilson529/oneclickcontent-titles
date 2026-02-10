=== OneClickContent - Titles ===
Contributors: jwilson529
Donate link: https://oneclickcontent.com/donate/
Tags: ai, seo, titles, content-optimization, content-enhancement
Requires at least: 5.0
Tested up to: 6.6.1
Stable tag: 1.1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate, score, and apply SEO-friendly titles in the editor with OpenAI or Google Gemini.

== Description ==

OneClickContent - Titles gives writers a practical workflow:

1. Click Generate Titles.
2. Compare recommended options.
3. Apply the best title.

This release also includes a built-in training page for teams:

- Settings -> Title Help

Title Help includes:

- Step-by-step usage training.
- Best practices for title quality and SEO.
- Definitions for each UI label and score signal.
- `placehold.co` image placeholders for your internal screenshot replacements.

== Installation ==

1. Upload plugin files to `/wp-content/plugins/oneclickcontent-titles`.
2. Activate via the Plugins screen.
3. Go to `Settings -> OCC - Titles`.
4. Add your API key and provider.
5. Open a post and click Generate Titles.

== Training Workflow ==

Use this flow to train editors:

1. Open post editor and launch title panel.
2. Set Goal, Style, and optional keyword targets.
3. Generate titles and compare Score, Insights, and Preview.
4. Apply the best row and publish.

Training image placeholders:

1. https://placehold.co/1200x675?text=Step+1:+Open+Post+Editor+and+Click+Generate+Titles
2. https://placehold.co/1200x675?text=Step+2:+Set+Goal+Style+and+Keywords
3. https://placehold.co/1200x675?text=Step+3:+Compare+Rows+and+Apply+Best+Title
4. https://placehold.co/1200x675?text=Step+4:+Check+Preview+and+Publish

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

= Does this replace writer judgment? =

No. It accelerates ideation and scoring, but editors should still validate clarity and accuracy.

== Privacy ==

The plugin sends post content to your selected provider for title generation.

- OpenAI privacy policy: https://openai.com/privacy
- Google privacy policy: https://policies.google.com/privacy

== Changelog ==

= 1.1.0 =
* Added Google Gemini provider support.
* Improved title generation workflow and settings.
* Added richer scoring and title comparison experience.

== Upgrade Notice ==

= 1.1.0 =
Adds Google Gemini support and improved title workflow.
