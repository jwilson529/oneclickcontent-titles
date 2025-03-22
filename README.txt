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

# OneClickContent - Titles: Generate SEO-Optimized Titles with OpenAI and Google Gemini. Bring your own API keys.

## Description

OneClickContent - Titles is an AI-powered WordPress plugin that makes crafting SEO-friendly titles a breeze. With a single click, generate up to five engaging, keyword-rich titles tailored to boost your content’s search engine visibility and captivate your audience. Now featuring support for **Google Gemini** alongside OpenAI, this update brings a simpler interface and rock-solid performance.

Perfect for content creators, marketers, and website owners, this plugin streamlines title creation to save you time and enhance your posts.

### Important Information

OneClickContent - Titles uses external AI services—OpenAI and Google Gemini—to generate titles. Your content will be sent to the selected provider’s servers for processing. By using this plugin, you agree to the respective terms and policies:
- OpenAI: [Terms of Use](https://openai.com/terms) and [Privacy Policy](https://openai.com/privacy)
- Google Gemini: [Terms of Service](https://cloud.google.com/terms) and [Privacy Policy](https://policies.google.com/privacy)

### API Endpoints Used
- **OpenAI**: `https://api.openai.com/v1/completions` or `https://api.openai.com/v1/chat/completions` (depending on model) - Powers title generation with standard models.
- **Google Gemini**: `https://generativelanguage.googleapis.com/v1beta/models` - Drives title generation with Google’s AI capabilities.

### Features

- **Dual AI Providers**: Choose between OpenAI and the new **Google Gemini** (highlighted in this release) for flexible title generation.
- **AI-Powered Titles**: Generate up to five SEO-optimized titles instantly, tailored to your content.
- **Variety of Styles**: Select from styles like How-To, Listicle, Question, and more to suit your post’s vibe.
- **Simplified Interface**: A cleaner, more intuitive design for faster title creation.
- **Custom Post Types**: Pick which post types (e.g., posts, pages) the generator supports via settings.
- **Editor Integration**: Access title generation right in the Classic or Block Editor.
- **SEO & Engagement Boost**: Optimize titles for search engines and hook readers with compelling headlines.

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- API keys for OpenAI and/or Google Gemini (sign up at [OpenAI](https://openai.com/) or [Google Cloud](https://cloud.google.com/gemini))
- Awareness of potential API usage costs

## Installation

1. Upload the plugin files to `/wp-content/plugins/oneclickcontent-titles`.
2. Activate the plugin via the 'Plugins' screen in WordPress.
3. Go to **Settings -> OneClickContent - Titles** to configure.
4. Enter your OpenAI and/or Google Gemini API keys.

## Important Note

You’ll need your own API keys for OpenAI and/or Google Gemini to use this plugin’s AI features. Costs depend on your usage—review pricing at OpenAI and Google Cloud before diving in.

## Getting Started

1. After activation, head to **Settings -> OneClickContent - Titles**.
2. Add your OpenAI and/or Google Gemini API keys.
3. Choose your preferred AI provider and post types.
4. Open a post or page in the editor.
5. Click "Generate Titles" to see AI-crafted options.
6. Pick a title and publish!

## Privacy

We value your privacy. OneClickContent - Titles only sends post content to your chosen AI provider (OpenAI or Google Gemini) for title generation—no personal data is shared. Review the providers’ privacy policies for details on their data practices:
- [OpenAI Privacy Policy](https://openai.com/privacy)
- [Google Privacy Policy](https://policies.google.com/privacy)

## Frequently Asked Questions

### How does OneClickContent - Titles generate titles?

The plugin taps into OpenAI or Google Gemini’s AI models to analyze your content and produce up to five SEO-optimized titles, blending keywords and engagement hooks.

### What’s new with Google Gemini?

Version 1.1.0 introduces **Google Gemini** as an AI provider option, offering a fresh alternative to OpenAI with robust title generation capabilities.

### Can I switch between OpenAI and Google Gemini?

Yes! The updated settings screen lets you configure and select your preferred provider—use one or both, depending on your API keys.

### Why did you drop the OpenAI Assistants API?

We switched to OpenAI’s standard models (e.g., completions or chat) for better stability, simpler integration, and broader compatibility—making the plugin more reliable.

### What if I don’t add API keys?

You need at least one valid API key (OpenAI or Google Gemini) for title generation to work. Without keys, the feature won’t activate.

### Are there costs to using this plugin?

The plugin is free, but OpenAI and Google Gemini APIs have usage fees. Check their pricing pages to estimate costs based on your needs.

### Where can I get support?

Reach out via the [WordPress support forums](https://wordpress.org/support/plugin/oneclickcontent-titles) or visit [GitHub](https://github.com/jwilson529/occ-titles) for help.

## Screenshots

1. ![Simplified Title Generation](assets/OneClickContentTitles-Block.png)  
   *Generate titles with a cleaner, one-click interface in the Block Editor.*

2. ![Classic and Block Support](assets/OneClickContentTitles.png)  
   *Works seamlessly in both editors with style options.*

3. ![Updated Settings Screen](assets/OneClickContentTitles-Settings.png)  
   *Configure API keys and providers with an improved settings layout.*

## Changelog

### 1.1.0
* **New Feature**: Added Google Gemini as an AI provider—try it out!
* **Major Change**: Switched from OpenAI Assistants API to standard models for better performance.
* **UI Update**: Simplified interface for a smoother user experience.
* **Settings Revamp**: Updated settings screen to support dual providers and post type selection.
* **Stability**: Enhanced reliability with streamlined AI integration.

### 1.0.3
* Improved error handling for API key validation.
* Added title tips in the spinner for a better UX.
* Minor bug fixes and visual tweaks.

### 1.0.2
* Initial WordPress Directory release.

### 1.0.0
* Initial release.

## Upgrade Notice

### 1.1.0
This update brings Google Gemini as a new AI option, a simpler interface, and improved stability by moving to OpenAI’s standard models. Update now to enjoy these enhancements—don’t forget to add your Gemini API key!

## Future Plans

We’re always improving OneClickContent - Titles. Upcoming ideas include:
- More AI provider options (pending availability, like Grok).
- Enhanced style customization.
- Performance analytics for generated titles.

Stay tuned via the [GitHub repo](https://github.com/jwilson529/occ-titles)!

## License

Licensed under GPLv2 or later.