# OneClickContent - Titles

![Plugin Banner](assets/banner-772x250.png)

**OneClickContent - Titles: Generate SEO-Optimized Titles with OpenAI and Google Gemini. Bring your own API keys.**

## Description

OneClickContent - Titles is an AI-powered WordPress plugin that simplifies creating SEO-friendly titles. With one click, generate up to five engaging, keyword-rich titles designed to boost your content’s search engine performance and grab readers’ attention. This update introduces **Google Gemini** as a new AI provider alongside OpenAI, with a streamlined interface and enhanced stability.

Ideal for content creators, marketers, and website owners, this plugin makes title crafting quick and effective.

### Important Information

OneClickContent - Titles uses OpenAI and Google Gemini APIs to generate titles. Your content will be sent to the chosen provider’s servers for processing. By using this plugin, you agree to their terms and policies:
- OpenAI: [Terms of Use](https://openai.com/terms) and [Privacy Policy](https://openai.com/privacy)
- Google Gemini: [Terms of Service](https://cloud.google.com/terms) and [Privacy Policy](https://policies.google.com/privacy)

### API Endpoints Used

- **OpenAI**: `https://api.openai.com/v1/completions` or `https://api.openai.com/v1/chat/completions` - Powers title generation with standard models.
- **Google Gemini**: `https://generativelanguage.googleapis.com/v1beta/models` - Drives title generation with Google’s AI.

### Features

- **Dual AI Providers**: Generate titles with OpenAI or the new **Google Gemini** (new in this release!).
- **AI-Powered Titles**: Instantly create up to five SEO-optimized titles tailored to your content.
- **Variety of Styles**: Pick from How-To, Listicle, Question, and more to match your post’s tone.
- **Simplified Interface**: A cleaner, more intuitive design for effortless use.
- **Custom Post Types**: Choose which post types (e.g., posts, pages) to enable via settings.
- **Editor Integration**: Works seamlessly in Classic and Block Editors.
- **SEO & Engagement Boost**: Optimize titles for search visibility and reader interest.

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- API keys for OpenAI and/or Google Gemini ([OpenAI](https://openai.com/), [Google Cloud](https://cloud.google.com/gemini))
- Awareness of API usage costs

## Installation

1. Upload the plugin files to `/wp-content/plugins/oneclickcontent-titles`.
2. Activate via the 'Plugins' screen in WordPress.
3. Go to **Settings -> OneClickContent - Titles** to configure.
4. Enter your OpenAI and/or Google Gemini API keys.

## Important Note

You’ll need your own API keys for OpenAI and/or Google Gemini to unlock AI features. Costs vary by usage—check pricing at OpenAI and Google Cloud.

## Getting Started

1. After activation, visit **Settings -> OneClickContent - Titles**.
2. Input your OpenAI and/or Google Gemini API keys.
3. Select your preferred AI provider and post types.
4. Open a post or page in the editor.
5. Click "Generate Titles" to get AI-crafted options.
6. Choose a title and publish!

## Privacy

Your privacy matters. OneClickContent - Titles sends only post content to OpenAI or Google Gemini for title generation—no personal data is shared. Review their privacy policies:
- [OpenAI Privacy Policy](https://openai.com/privacy)
- [Google Privacy Policy](https://policies.google.com/privacy)

## Frequently Asked Questions

### How does OneClickContent - Titles generate titles?

It leverages OpenAI or Google Gemini’s AI to analyze your content and produce up to five SEO-optimized, engaging titles.

### What’s new with Google Gemini?

Version 1.1.0 adds **Google Gemini** as an AI provider, offering a powerful alternative to OpenAI for title generation.

### Can I switch between OpenAI and Google Gemini?

Absolutely! The updated settings let you configure and choose your provider—use one or both with valid API keys.

### Why switch from OpenAI Assistants API?

We moved to OpenAI’s standard models for better stability and simpler integration, improving overall performance.

### What if I don’t add API keys?

You need at least one API key (OpenAI or Google Gemini) for title generation. Without them, the feature won’t work.

### Are there costs involved?

The plugin is free, but OpenAI and Google Gemini APIs have usage fees. Review their pricing to manage costs.

### Where can I get support?

Contact us via [WordPress forums](https://wordpress.org/support/plugin/oneclickcontent-titles) or [GitHub](https://github.com/jwilson529/occ-titles).

## Screenshots

1. ![Simplified Title Generation](assets/OneClickContentTitles-Block.png)  
   *Generate titles with a cleaner interface in the Block Editor.*

2. ![Classic and Block Support](assets/OneClickContentTitles.png)  
   *Style options in both editors.*

3. ![Updated Settings Screen](assets/OneClickContentTitles-Settings.png)  
   *Configure API keys and providers easily.*

## Changelog

### 1.1.0
- **New Feature**: Added Google Gemini as an AI provider.
- **Major Change**: Switched from OpenAI Assistants API to standard models.
- **UI Update**: Simplified interface for better usability.
- **Settings Revamp**: Updated to support dual providers and post types.
- **Stability**: Improved reliability with streamlined AI calls.

### 1.0.3
- Enhanced error handling for API validation.
- Added title tips in the spinner.
- Minor UI and bug fixes.

### 1.0.2
- Initial WordPress Directory release.

### 1.0.0
- Initial release.

## Upgrade Notice

### 1.1.0
Upgrade for Google Gemini support, a simpler interface, and improved stability with OpenAI’s standard models. Add your Gemini API key to try it!

## Future Plans

- More AI providers (e.g., Grok, if available).
- Enhanced style options.
- Title performance insights.

Follow updates on [GitHub](https://github.com/jwilson529/occ-titles)!

## License

Licensed under GPLv2 or later.