<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Handles the settings for OneClickContent - Titles.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Occ_Titles_Settings
 *
 * Manages the settings page for the OneClickContent - Titles plugin.
 */
class Occ_Titles_Settings {

	/**
	 * Registers the settings page under the options menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_register_options_page() {
		add_options_page(
			__( 'OneClickContent - Titles Settings', 'oneclickcontent-titles' ),
			__( 'OCC - Titles', 'oneclickcontent-titles' ),
			'manage_options',
			'occ_titles-settings',
			array( $this, 'occ_titles_options_page' )
		);

		add_submenu_page(
			'options-general.php',
			__( 'OneClickContent - Title Help', 'oneclickcontent-titles' ),
			__( 'Title Help', 'oneclickcontent-titles' ),
			'manage_options',
			'occ_titles-help',
			array( $this, 'occ_titles_help_page' )
		);
	}

	/**
	 * Outputs the settings page content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_options_page() {
		?>
		<div id="occ_titles" class="wrap">
			<p>
				<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'options-general.php?page=occ_titles-help' ) ); ?>">
					<?php esc_html_e( 'Open Title Help', 'oneclickcontent-titles' ); ?>
				</a>
			</p>
			<form class="occ_titles-settings-form" method="post" action="options.php">
				<?php
				settings_fields( 'occ_titles_settings' );
				do_settings_sections( 'occ_titles_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Outputs the Title Help page content.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function occ_titles_help_page() {
		$training_steps = array(
			array(
				'step'    => __( 'Step 1', 'oneclickcontent-titles' ),
				'title'   => __( 'Step 1: Open your post and click Generate Titles', 'oneclickcontent-titles' ),
				'content' => __( 'In the post editor, click the spark icon next to the title field. This opens the title panel where you can generate and score options.', 'oneclickcontent-titles' ),
				'image'   => 'https://placehold.co/1200x675?text=Step+1:+Open+Post+Editor+and+Click+Generate+Titles',
				'alt'     => __( 'Placeholder screenshot for opening the title generator', 'oneclickcontent-titles' ),
				'focus'   => __( 'Focus: open the panel and confirm content context before generating.', 'oneclickcontent-titles' ),
			),
			array(
				'step'    => __( 'Step 2', 'oneclickcontent-titles' ),
				'title'   => __( 'Step 2: Set Goal, Style, and optional keyword targets', 'oneclickcontent-titles' ),
				'content' => __( 'Choose a goal and style before generating. Select keyword chips that must appear in your headlines for better alignment with your SEO strategy.', 'oneclickcontent-titles' ),
				'image'   => 'https://placehold.co/1200x675?text=Step+2:+Set+Goal+Style+and+Keywords',
				'alt'     => __( 'Placeholder screenshot for title controls', 'oneclickcontent-titles' ),
				'focus'   => __( 'Focus: align controls with the real publishing objective.', 'oneclickcontent-titles' ),
			),
			array(
				'step'    => __( 'Step 3', 'oneclickcontent-titles' ),
				'title'   => __( 'Step 3: Generate, compare, and apply the best title', 'oneclickcontent-titles' ),
				'content' => __( 'Review score, insights, keyword fit, and preview width. Click Apply on the row you want to use, or iterate with Shorter, Punchier, More benefit, and Add keyword.', 'oneclickcontent-titles' ),
				'image'   => 'https://placehold.co/1200x675?text=Step+3:+Compare+Rows+and+Apply+Best+Title',
				'alt'     => __( 'Placeholder screenshot for generated title rows', 'oneclickcontent-titles' ),
				'focus'   => __( 'Focus: select the strongest option, not just the most dramatic one.', 'oneclickcontent-titles' ),
			),
			array(
				'step'    => __( 'Step 4', 'oneclickcontent-titles' ),
				'title'   => __( 'Step 4: Validate in preview and finalize', 'oneclickcontent-titles' ),
				'content' => __( 'Use the preview column and pixel meter to keep titles in a clean display range. Save or publish your post once your selected title matches the goal.', 'oneclickcontent-titles' ),
				'image'   => 'https://placehold.co/1200x675?text=Step+4:+Check+Preview+and+Publish',
				'alt'     => __( 'Placeholder screenshot for preview validation', 'oneclickcontent-titles' ),
				'focus'   => __( 'Focus: confirm quality and clarity before publishing.', 'oneclickcontent-titles' ),
			),
		);

		$label_definitions = array(
			array(
				'label'       => __( 'Goal', 'oneclickcontent-titles' ),
				'description' => __( 'Sets the optimization target for the batch. Options include Increase CTR, Rank for keyword, Discover cards, Social share, Thought leadership, and Lead gen.', 'oneclickcontent-titles' ),
				'group'       => __( 'Controls', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Style', 'oneclickcontent-titles' ),
				'description' => __( 'Controls title format such as How-to, Listicle, Question, Command, News headline, Comparison, and other templates.', 'oneclickcontent-titles' ),
				'group'       => __( 'Controls', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Curiosity ellipsis', 'oneclickcontent-titles' ),
				'description' => __( 'Allows generated titles to end with "...". Use only when it improves curiosity without becoming clickbait.', 'oneclickcontent-titles' ),
				'group'       => __( 'Controls', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Keyword targets', 'oneclickcontent-titles' ),
				'description' => __( 'Keyword chips extracted from your content. Selected chips are prioritized during title generation.', 'oneclickcontent-titles' ),
				'group'       => __( 'Controls', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Score', 'oneclickcontent-titles' ),
				'description' => __( 'Overall quality score that combines length, readability, sentiment, and keyword usage signals.', 'oneclickcontent-titles' ),
				'group'       => __( 'Signals', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Pass / Needs work', 'oneclickcontent-titles' ),
				'description' => __( 'Quick quality gate. Pass means the title meets more of the recommended conditions.', 'oneclickcontent-titles' ),
				'group'       => __( 'Signals', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Length', 'oneclickcontent-titles' ),
				'description' => __( 'Shows whether the title is short, ideal, or long based on character guidance.', 'oneclickcontent-titles' ),
				'group'       => __( 'Signals', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Keyword fit', 'oneclickcontent-titles' ),
				'description' => __( 'Indicates if keyword density is low, high, or too high for the selected title.', 'oneclickcontent-titles' ),
				'group'       => __( 'Signals', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Density', 'oneclickcontent-titles' ),
				'description' => __( 'Displays the keyword density percentage measured for that title.', 'oneclickcontent-titles' ),
				'group'       => __( 'Signals', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Preview + Pixel meter', 'oneclickcontent-titles' ),
				'description' => __( 'Shows how your title may render. Keep the pixel target near 560 to 600 px to reduce truncation risk.', 'oneclickcontent-titles' ),
				'group'       => __( 'Signals', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Best / Current badges', 'oneclickcontent-titles' ),
				'description' => __( 'Best marks the strongest scored option. Current marks your post’s existing live title for comparison.', 'oneclickcontent-titles' ),
				'group'       => __( 'Rows', 'oneclickcontent-titles' ),
			),
			array(
				'label'       => __( 'Apply / Undo / Iterate buttons', 'oneclickcontent-titles' ),
				'description' => __( 'Apply sets the title in the editor. Undo restores the previous title. Iterate buttons request focused rewrites.', 'oneclickcontent-titles' ),
				'group'       => __( 'Rows', 'oneclickcontent-titles' ),
			),
		);

		$best_practices = array(
			__( 'Match the Goal to the page intent before generating titles.', 'oneclickcontent-titles' ),
			__( 'Prioritize clarity first, then curiosity. Avoid misleading promises.', 'oneclickcontent-titles' ),
			__( 'Use one primary keyword naturally in the title.', 'oneclickcontent-titles' ),
			__( 'Aim for strong, specific nouns and verbs instead of vague wording.', 'oneclickcontent-titles' ),
			__( 'Keep title width in range using the pixel meter, not only character count.', 'oneclickcontent-titles' ),
			__( 'Use Score Current Title before replacing an existing high performer.', 'oneclickcontent-titles' ),
			__( 'Save voice samples in real projects so future generations match your brand tone.', 'oneclickcontent-titles' ),
		);

		$common_mistakes = array(
			__( 'Choosing a style before deciding the actual publishing goal.', 'oneclickcontent-titles' ),
			__( 'Overloading the title with too many keywords.', 'oneclickcontent-titles' ),
			__( 'Using curiosity language that does not match the article promise.', 'oneclickcontent-titles' ),
			__( 'Ignoring preview width and publishing truncated headlines.', 'oneclickcontent-titles' ),
			__( 'Applying a title without comparing against the current one.', 'oneclickcontent-titles' ),
		);
		?>
		<div class="wrap occ_titles_help">
			<section class="occ_titles_help__hero">
				<div class="occ_titles_help__hero_content">
					<p class="occ_titles_help__eyebrow"><?php esc_html_e( 'Editor Training', 'oneclickcontent-titles' ); ?></p>
					<h1><?php esc_html_e( 'Title Help and Training', 'oneclickcontent-titles' ); ?></h1>
					<p><?php esc_html_e( 'Use this page as your team playbook for writing titles with confidence. It combines workflow, scoring guidance, and clear definitions so editors can make consistent decisions.', 'oneclickcontent-titles' ); ?></p>
					<div class="occ_titles_help__hero_actions">
						<a class="button button-primary" href="<?php echo esc_url( admin_url( 'options-general.php?page=occ_titles-settings' ) ); ?>">
							<?php esc_html_e( 'Open Plugin Settings', 'oneclickcontent-titles' ); ?>
						</a>
						<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'options-general.php?page=occ_titles-settings' ) ); ?>">
							<?php esc_html_e( 'Configure Provider and Post Types', 'oneclickcontent-titles' ); ?>
						</a>
					</div>
				</div>
				<div class="occ_titles_help__hero_stats">
					<div class="occ_titles_help__stat_card">
						<span class="occ_titles_help__stat_value">4</span>
						<span class="occ_titles_help__stat_label"><?php esc_html_e( 'Workflow steps', 'oneclickcontent-titles' ); ?></span>
					</div>
					<div class="occ_titles_help__stat_card">
						<span class="occ_titles_help__stat_value"><?php echo esc_html( (string) count( $label_definitions ) ); ?></span>
						<span class="occ_titles_help__stat_label"><?php esc_html_e( 'UI labels explained', 'oneclickcontent-titles' ); ?></span>
					</div>
					<div class="occ_titles_help__stat_card">
						<span class="occ_titles_help__stat_value"><?php esc_html_e( '560-600', 'oneclickcontent-titles' ); ?></span>
						<span class="occ_titles_help__stat_label"><?php esc_html_e( 'Pixel target range', 'oneclickcontent-titles' ); ?></span>
					</div>
				</div>
			</section>

			<section class="occ_titles_help__section">
				<div class="occ_titles_help__section_header">
					<h2><?php esc_html_e( 'Quick Workflow', 'oneclickcontent-titles' ); ?></h2>
					<p><?php esc_html_e( 'Teach this exact order so your writers get predictable outputs and fewer rewrites.', 'oneclickcontent-titles' ); ?></p>
				</div>
				<ol class="occ_titles_help__timeline">
					<li><?php esc_html_e( 'Open the post editor and launch the title panel.', 'oneclickcontent-titles' ); ?></li>
					<li><?php esc_html_e( 'Choose Goal and Style, then set optional keyword targets.', 'oneclickcontent-titles' ); ?></li>
					<li><?php esc_html_e( 'Generate titles and compare score, insights, and preview.', 'oneclickcontent-titles' ); ?></li>
					<li><?php esc_html_e( 'Apply the strongest option and publish.', 'oneclickcontent-titles' ); ?></li>
				</ol>
			</section>

			<section class="occ_titles_help__section">
				<div class="occ_titles_help__section_header">
					<h2><?php esc_html_e( 'How Scoring Works', 'oneclickcontent-titles' ); ?></h2>
					<p><?php esc_html_e( 'The score combines multiple signals and adapts weights to your selected Goal. This helps the ranking reflect the outcome you actually want.', 'oneclickcontent-titles' ); ?></p>
				</div>
				<div class="occ_titles_help__glossary">
					<article class="occ_titles_help__glossary_item">
						<div class="occ_titles_help__glossary_meta"><?php esc_html_e( 'Core signal', 'oneclickcontent-titles' ); ?></div>
						<h3><?php esc_html_e( 'Length score', 'oneclickcontent-titles' ); ?></h3>
						<p><?php esc_html_e( 'Higher when the title stays in the recommended range (about 50 to 60 characters).', 'oneclickcontent-titles' ); ?></p>
					</article>
					<article class="occ_titles_help__glossary_item">
						<div class="occ_titles_help__glossary_meta"><?php esc_html_e( 'Core signal', 'oneclickcontent-titles' ); ?></div>
						<h3><?php esc_html_e( 'Sentiment score', 'oneclickcontent-titles' ); ?></h3>
						<p><?php esc_html_e( 'Positive and neutral phrasing generally score better than negative phrasing.', 'oneclickcontent-titles' ); ?></p>
					</article>
					<article class="occ_titles_help__glossary_item">
						<div class="occ_titles_help__glossary_meta"><?php esc_html_e( 'Core signal', 'oneclickcontent-titles' ); ?></div>
						<h3><?php esc_html_e( 'Keyword fit score', 'oneclickcontent-titles' ); ?></h3>
						<p><?php esc_html_e( 'Uses selected or returned keywords to measure fit for short headline length. If no keyword targets are provided, this factor is neutral.', 'oneclickcontent-titles' ); ?></p>
					</article>
					<article class="occ_titles_help__glossary_item">
						<div class="occ_titles_help__glossary_meta"><?php esc_html_e( 'Core signal', 'oneclickcontent-titles' ); ?></div>
						<h3><?php esc_html_e( 'Readability score', 'oneclickcontent-titles' ); ?></h3>
						<p><?php esc_html_e( 'Rewards titles that are easy to parse quickly while scanning results.', 'oneclickcontent-titles' ); ?></p>
					</article>
					<article class="occ_titles_help__glossary_item">
						<div class="occ_titles_help__glossary_meta"><?php esc_html_e( 'Additional signals', 'oneclickcontent-titles' ); ?></div>
						<h3><?php esc_html_e( 'Intent, opening, specificity, clarity, and pixel fit', 'oneclickcontent-titles' ); ?></h3>
						<p><?php esc_html_e( 'These signals refine ranking quality for each goal. Example: Rank for keyword weighs keyword fit higher, while Increase CTR weighs opening and specificity higher.', 'oneclickcontent-titles' ); ?></p>
					</article>
				</div>
				<p class="description">
					<?php esc_html_e( 'Quality gate (Pass / Needs work) is separate from overall score. It checks practical readiness conditions, while the weighted score and letter grade (A/B/C) rank options against each other.', 'oneclickcontent-titles' ); ?>
				</p>
			</section>

			<section class="occ_titles_help__section">
				<div class="occ_titles_help__section_header">
					<h2><?php esc_html_e( 'Training Steps', 'oneclickcontent-titles' ); ?></h2>
					<p><?php esc_html_e( 'Replace each placeholder with your own screenshots after recording your internal process.', 'oneclickcontent-titles' ); ?></p>
				</div>
				<div class="occ_titles_help__step_grid">
					<?php foreach ( $training_steps as $step ) : ?>
						<article class="occ_titles_help__step_card">
							<div class="occ_titles_help__step_head">
								<span class="occ_titles_help__step_badge"><?php echo esc_html( $step['step'] ); ?></span>
								<h3><?php echo esc_html( $step['title'] ); ?></h3>
							</div>
							<p><?php echo esc_html( $step['content'] ); ?></p>
							<p class="occ_titles_help__step_focus"><?php echo esc_html( $step['focus'] ); ?></p>
							<figure class="occ_titles_help__media">
								<img src="<?php echo esc_url( $step['image'] ); ?>" alt="<?php echo esc_attr( $step['alt'] ); ?>" />
								<figcaption><?php esc_html_e( 'Placeholder image. Replace with your real screenshot.', 'oneclickcontent-titles' ); ?></figcaption>
							</figure>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="occ_titles_help__section">
				<div class="occ_titles_help__section_header">
					<h2><?php esc_html_e( 'Label Glossary', 'oneclickcontent-titles' ); ?></h2>
					<p><?php esc_html_e( 'Give this section to new editors so they understand exactly what each signal means.', 'oneclickcontent-titles' ); ?></p>
				</div>
				<div class="occ_titles_help__glossary">
					<?php foreach ( $label_definitions as $row ) : ?>
						<article class="occ_titles_help__glossary_item">
							<div class="occ_titles_help__glossary_meta"><?php echo esc_html( $row['group'] ); ?></div>
							<h3><?php echo esc_html( $row['label'] ); ?></h3>
							<p><?php echo esc_html( $row['description'] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="occ_titles_help__section">
				<div class="occ_titles_help__section_header">
					<h2><?php esc_html_e( 'Best Practices and Pitfalls', 'oneclickcontent-titles' ); ?></h2>
					<p><?php esc_html_e( 'Use this as a final quality check before publishing.', 'oneclickcontent-titles' ); ?></p>
				</div>
				<div class="occ_titles_help__two_col">
					<div class="occ_titles_help__panel">
						<h3><?php esc_html_e( 'Do This', 'oneclickcontent-titles' ); ?></h3>
						<ul>
							<?php foreach ( $best_practices as $practice ) : ?>
								<li><?php echo esc_html( $practice ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="occ_titles_help__panel occ_titles_help__panel_warning">
						<h3><?php esc_html_e( 'Avoid This', 'oneclickcontent-titles' ); ?></h3>
						<ul>
							<?php foreach ( $common_mistakes as $mistake ) : ?>
								<li><?php echo esc_html( $mistake ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</section>
		</div>
		<?php
	}

	/**
	 * Registers the settings and their fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_register_settings() {
		// Always register the AI Provider and Post Types settings.
		register_setting(
			'occ_titles_settings',
			'occ_titles_ai_provider',
			array( 'sanitize_callback' => array( __CLASS__, 'occ_titles_sanitize_ai_provider' ) )
		);
		register_setting(
			'occ_titles_settings',
			'occ_titles_post_types',
			array( 'sanitize_callback' => array( __CLASS__, 'occ_titles_sanitize_post_types' ) )
		);
		register_setting(
			'occ_titles_settings',
			'occ_titles_logging_enabled',
			array( 'sanitize_callback' => array( __CLASS__, 'occ_titles_sanitize_logging_enabled' ) )
		);
		register_setting(
			'occ_titles_settings',
			'occ_titles_voice_profile',
			array( 'sanitize_callback' => array( __CLASS__, 'occ_titles_sanitize_voice_profile' ) )
		);

		// Add the settings section.
		add_settings_section(
			'occ_titles_settings_section',
			__( 'OneClickContent - Titles Settings', 'oneclickcontent-titles' ),
			array( $this, 'occ_titles_settings_section_callback' ),
			'occ_titles_settings'
		);

		add_settings_section(
			'occ_titles_voice_section',
			__( 'Brand Voice Profile', 'oneclickcontent-titles' ),
			array( $this, 'occ_titles_voice_section_callback' ),
			'occ_titles_settings'
		);

		// Always add the AI Provider dropdown.
		add_settings_field(
			'occ_titles_ai_provider',
			__( 'AI Provider', 'oneclickcontent-titles' ),
			array( $this, 'occ_titles_ai_provider_callback' ),
			'occ_titles_settings',
			'occ_titles_settings_section'
		);
		add_settings_field(
			'occ_titles_logging_enabled',
			__( 'Enable Logging', 'oneclickcontent-titles' ),
			array( $this, 'occ_titles_logging_enabled_callback' ),
			'occ_titles_settings',
			'occ_titles_settings_section'
		);

		add_settings_field(
			'occ_titles_voice_profile',
			__( 'Voice Profile', 'oneclickcontent-titles' ),
			array( $this, 'occ_titles_voice_profile_callback' ),
			'occ_titles_settings',
			'occ_titles_voice_section'
		);

		// Retrieve the selected provider (default to "openai").
		$provider = get_option( 'occ_titles_ai_provider', 'openai' );

		// Conditionally register and add the provider-specific API key and model fields.
		if ( 'openai' === $provider ) {
			// Register OpenAI settings.
			register_setting(
				'occ_titles_settings',
				'occ_titles_openai_api_key',
				array( 'sanitize_callback' => array( __CLASS__, 'occ_titles_sanitize_openai_api_key' ) )
			);
			register_setting(
				'occ_titles_settings',
				'occ_titles_openai_model',
				array( 'sanitize_callback' => 'sanitize_text_field' )
			);
			add_settings_field(
				'occ_titles_openai_api_key',
				__( 'OpenAI API Key', 'oneclickcontent-titles' ),
				array( $this, 'occ_titles_openai_api_key_callback' ),
				'occ_titles_settings',
				'occ_titles_settings_section',
				array( 'label_for' => 'occ_titles_openai_api_key' )
			);
			add_settings_field(
				'occ_titles_openai_model',
				__( 'OpenAI Model', 'oneclickcontent-titles' ),
				array( $this, 'occ_titles_openai_model_callback' ),
				'occ_titles_settings',
				'occ_titles_settings_section',
				array( 'label_for' => 'occ_titles_openai_model' )
			);
		} elseif ( 'google' === $provider ) {
			// Register Google Gemini settings.
			register_setting(
				'occ_titles_settings',
				'occ_titles_google_api_key',
				array( 'sanitize_callback' => array( __CLASS__, 'occ_titles_sanitize_google_api_key' ) )
			);

			add_settings_field(
				'occ_titles_google_api_key',
				__( 'Google Gemini API Key', 'oneclickcontent-titles' ),
				array( $this, 'occ_titles_google_api_key_callback' ),
				'occ_titles_settings',
				'occ_titles_settings_section',
				array( 'label_for' => 'occ_titles_google_api_key' )
			);

		}

		// Always add the Post Types field.
		add_settings_field(
			'occ_titles_post_types',
			__( 'Post Types', 'oneclickcontent-titles' ),
			array( $this, 'occ_titles_post_types_callback' ),
			'occ_titles_settings',
			'occ_titles_settings_section'
		);
	}
	/**
	 * Custom sanitize callback for the post types setting.
	 *
	 * Ensures that the value is stored as an array.
	 *
	 * @since 1.0.0
	 * @param mixed $input The input value.
	 * @return array The sanitized array of post types.
	 */
	public static function occ_titles_sanitize_post_types( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}
		return array_map( 'sanitize_text_field', $input );
	}

	/**
	 * Sanitize the logging enabled setting.
	 *
	 * @since 1.1.0
	 * @param mixed $input Raw input.
	 * @return int Sanitized value.
	 */
	public static function occ_titles_sanitize_logging_enabled( $input ) {
		return absint( $input ) ? 1 : 0;
	}

	/**
	 * Sanitize the voice profile settings.
	 *
	 * @since 1.1.1
	 * @param array $input Raw input.
	 * @return array Sanitized profile.
	 */
	public static function occ_titles_sanitize_voice_profile( $input ) {
		$input = is_array( $input ) ? $input : array();

		$profile = array(
			'tone'            => sanitize_text_field( $input['tone'] ?? '' ),
			'formality'       => sanitize_text_field( $input['formality'] ?? '' ),
			'sentence_length' => sanitize_text_field( $input['sentence_length'] ?? '' ),
			'cta_style'       => sanitize_text_field( $input['cta_style'] ?? '' ),
			'must_use'        => self::sanitize_list( $input['must_use'] ?? '' ),
			'avoid'           => self::sanitize_list( $input['avoid'] ?? '' ),
			'examples'        => self::sanitize_list( $input['examples'] ?? '' ),
		);

		return $profile;
	}

	/**
	 * Sanitize list input to an array of strings.
	 *
	 * @since 1.1.1
	 * @param string|array $value Raw value.
	 * @return array
	 */
	private static function sanitize_list( $value ) {
		if ( is_array( $value ) ) {
			$items = $value;
		} else {
			$items = preg_split( '/[\r\n,]+/', (string) $value );
		}

		$items = array_filter(
			array_map(
				static function ( $item ) {
					$item = sanitize_text_field( $item );
					return '' === $item ? null : $item;
				},
				$items
			)
		);

		return array_values( array_unique( $items ) );
	}

	/**
	 * Sanitize the AI provider setting.
	 *
	 * @since 1.1.1
	 * @param string $input Raw input.
	 * @return string Sanitized provider.
	 */
	public static function occ_titles_sanitize_ai_provider( $input ) {
		$provider = sanitize_text_field( $input );
		$allowed  = array( 'openai', 'google' );

		if ( ! in_array( $provider, $allowed, true ) ) {
			$provider = 'openai';
		}

		self::maybe_add_settings_updated_notice();

		return $provider;
	}

	/**
	 * Sanitize the OpenAI API key.
	 *
	 * @since 1.1.1
	 * @param string $input Raw input.
	 * @return string Sanitized value.
	 */
	public static function occ_titles_sanitize_openai_api_key( $input ) {
		$api_key = sanitize_text_field( $input );
		self::update_api_key_status( 'openai', 'unknown', '' );

		return $api_key;
	}

	/**
	 * Sanitize the Google API key.
	 *
	 * @since 1.1.1
	 * @param string $input Raw input.
	 * @return string Sanitized value.
	 */
	public static function occ_titles_sanitize_google_api_key( $input ) {
		$api_key = sanitize_text_field( $input );
		self::update_api_key_status( 'google', 'unknown', '' );

		return $api_key;
	}

	/**
	 * Update the stored API key validation status.
	 *
	 * @since 1.1.1
	 * @param string $provider Provider slug.
	 * @param string $status   Status value.
	 * @param string $message  Optional status message.
	 * @return void
	 */
	public static function update_api_key_status( $provider, $status, $message = '' ) {
		$provider = sanitize_text_field( $provider );
		$status   = sanitize_text_field( $status );
		$message  = sanitize_text_field( $message );

		$option = self::get_api_key_status_option( $provider );
		if ( '' === $option ) {
			return;
		}

		$allowed_statuses = array( 'valid', 'invalid', 'unknown' );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = 'unknown';
		}

		$payload = array(
			'status'     => $status,
			'message'    => $message,
			'checked_at' => 'unknown' === $status ? '' : current_time( 'mysql' ),
		);

		update_option( $option, $payload );
	}

	/**
	 * Get the API key status option name for a provider.
	 *
	 * @since 1.1.1
	 * @param string $provider Provider slug.
	 * @return string
	 */
	private static function get_api_key_status_option( $provider ) {
		if ( 'openai' === $provider ) {
			return 'occ_titles_openai_api_key_status';
		}

		if ( 'google' === $provider ) {
			return 'occ_titles_google_api_key_status';
		}

		return '';
	}

	/**
	 * Add a single settings updated notice for the settings page.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	private static function maybe_add_settings_updated_notice() {
		static $added = false;

		if ( $added ) {
			return;
		}

		if ( empty( $_POST['option_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$option_page = sanitize_text_field( wp_unslash( $_POST['option_page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 'occ_titles_settings' !== $option_page ) {
			return;
		}

		add_settings_error(
			'occ_titles_settings',
			'settings_updated',
			__( 'Settings saved.', 'oneclickcontent-titles' ),
			'success'
		);

		$added = true;
	}

	/**
	 * Callback function for the AI Provider setting field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_ai_provider_callback() {
		$selected = get_option( 'occ_titles_ai_provider', 'openai' );
		echo '<select id="occ_titles_ai_provider" name="occ_titles_ai_provider">';
		echo '<option value="openai"' . selected( $selected, 'openai', false ) . '>OpenAI</option>';
		echo '<option value="google"' . selected( $selected, 'google', false ) . '>Google Gemini</option>';
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Select the AI Provider to use for generating titles.', 'oneclickcontent-titles' ) . '</p>';
	}

	/**
	 * Callback function for the OpenAI API Key setting field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_openai_api_key_callback() {
		// Only show this field if the AI Provider is set to OpenAI.
		$provider = get_option( 'occ_titles_ai_provider', 'openai' );
		if ( 'openai' !== $provider ) {
			return;
		}
		$value = get_option( 'occ_titles_openai_api_key', '' );
		echo '<input type="password" name="occ_titles_openai_api_key" value="' . esc_attr( $value ) . '" />';
		$this->render_api_key_badge( 'openai' );
		echo '<p class="description">' . wp_kses_post( __( 'Get your OpenAI API Key <a href="https://beta.openai.com/signup/">here</a>.', 'oneclickcontent-titles' ) ) . '</p>';
	}

	/**
	 * Callback function for the Google Gemini API Key setting field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_google_api_key_callback() {
		// Only show this field if the AI Provider is set to Google Gemini.
		$provider = get_option( 'occ_titles_ai_provider', 'openai' );
		if ( 'google' !== $provider ) {
			return;
		}
		$value = get_option( 'occ_titles_google_api_key', '' );
		echo '<input type="password" name="occ_titles_google_api_key" value="' . esc_attr( $value ) . '" />';
		$this->render_api_key_badge( 'google' );
		echo '<p class="description">' . esc_html__( 'Get your Google Gemini API Key from your provider dashboard.', 'oneclickcontent-titles' ) . '</p>';
	}

	/**
	 * Callback function for the OpenAI Model setting field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_openai_model_callback() {
		// Set "gpt-4o-mini" as the default if none is saved.
		$selected_model = get_option( 'occ_titles_openai_model', 'gpt-4o-mini' );
		$api_key        = get_option( 'occ_titles_openai_api_key', '' );

		if ( empty( $api_key ) ) {
			echo '<p class="error">' . esc_html__( 'Please enter a valid OpenAI API key first.', 'oneclickcontent-titles' ) . '</p>';
			return;
		}

		// Retrieve models using our helper method.
		$models = Occ_Titles_OpenAI_Helper::validate_openai_api_key( $api_key );

		if ( ! $models || ! is_array( $models ) ) {
			echo '<p class="error">' . esc_html__( 'Unable to retrieve models. Please check your API key.', 'oneclickcontent-titles' ) . '</p>';
			return;
		}

		echo '<select name="occ_titles_openai_model" id="occ_titles_openai_model">';
		foreach ( $models as $model ) {
			echo '<option value="' . esc_attr( $model ) . '"' . selected( $selected_model, $model, false ) . '>' . esc_html( $model ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Select the OpenAI model to use for completions.', 'oneclickcontent-titles' ) . '</p>';
	}

	/**
	 * Callback function for the logging enabled setting field.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function occ_titles_logging_enabled_callback() {
		$enabled = (int) get_option( 'occ_titles_logging_enabled', 1 );
		echo '<label for="occ_titles_logging_enabled">';
		echo '<input type="checkbox" id="occ_titles_logging_enabled" name="occ_titles_logging_enabled" value="1" ' . checked( 1, $enabled, false ) . '>';
		echo esc_html__( 'Write diagnostic logs to the plugin log file.', 'oneclickcontent-titles' );
		echo '</label>';
	}

	/**
	 * Callback for the voice profile settings.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function occ_titles_voice_profile_callback() {
		$profile = get_option( 'occ_titles_voice_profile', array() );

		$tone            = $profile['tone'] ?? '';
		$formality       = $profile['formality'] ?? '';
		$sentence_length = $profile['sentence_length'] ?? '';
		$cta_style       = $profile['cta_style'] ?? '';
		$must_use        = isset( $profile['must_use'] ) ? implode( "\n", (array) $profile['must_use'] ) : '';
		$avoid           = isset( $profile['avoid'] ) ? implode( "\n", (array) $profile['avoid'] ) : '';
		$examples        = isset( $profile['examples'] ) ? implode( "\n", (array) $profile['examples'] ) : '';

		echo '<div class="occ_titles-voice-grid">';
		echo '<p><label for="occ_titles_voice_tone"><strong>' . esc_html__( 'Tone', 'oneclickcontent-titles' ) . '</strong></label></p>';
		echo '<select id="occ_titles_voice_tone" name="occ_titles_voice_profile[tone]">';
		echo '<option value="">' . esc_html__( 'Select tone', 'oneclickcontent-titles' ) . '</option>';
		foreach ( array( 'casual', 'authoritative', 'playful', 'friendly', 'direct', 'journalistic' ) as $option ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option ),
				selected( $tone, $option, false ),
				esc_html( ucfirst( $option ) )
			);
		}
		echo '</select>';

		echo '<p><label for="occ_titles_voice_formality"><strong>' . esc_html__( 'Formality', 'oneclickcontent-titles' ) . '</strong></label></p>';
		echo '<select id="occ_titles_voice_formality" name="occ_titles_voice_profile[formality]">';
		echo '<option value="">' . esc_html__( 'Select formality', 'oneclickcontent-titles' ) . '</option>';
		foreach ( array( 'informal', 'balanced', 'formal' ) as $option ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option ),
				selected( $formality, $option, false ),
				esc_html( ucfirst( $option ) )
			);
		}
		echo '</select>';

		echo '<p><label for="occ_titles_voice_sentence_length"><strong>' . esc_html__( 'Sentence Length', 'oneclickcontent-titles' ) . '</strong></label></p>';
		echo '<select id="occ_titles_voice_sentence_length" name="occ_titles_voice_profile[sentence_length]">';
		echo '<option value="">' . esc_html__( 'Select length', 'oneclickcontent-titles' ) . '</option>';
		foreach ( array( 'short', 'medium', 'long' ) as $option ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option ),
				selected( $sentence_length, $option, false ),
				esc_html( ucfirst( $option ) )
			);
		}
		echo '</select>';

		echo '<p><label for="occ_titles_voice_cta"><strong>' . esc_html__( 'CTA Style', 'oneclickcontent-titles' ) . '</strong></label></p>';
		echo '<select id="occ_titles_voice_cta" name="occ_titles_voice_profile[cta_style]">';
		echo '<option value="">' . esc_html__( 'Select CTA', 'oneclickcontent-titles' ) . '</option>';
		foreach ( array( 'none', 'soft', 'direct' ) as $option ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option ),
				selected( $cta_style, $option, false ),
				esc_html( ucfirst( $option ) )
			);
		}
		echo '</select>';

		echo '<p><label for="occ_titles_voice_must_use"><strong>' . esc_html__( 'Must-use words', 'oneclickcontent-titles' ) . '</strong></label></p>';
		echo '<textarea id="occ_titles_voice_must_use" name="occ_titles_voice_profile[must_use]" rows="3" class="large-text">' . esc_textarea( $must_use ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'One word or phrase per line. These should appear when possible.', 'oneclickcontent-titles' ) . '</p>';

		echo '<p><label for="occ_titles_voice_avoid"><strong>' . esc_html__( 'Avoid words', 'oneclickcontent-titles' ) . '</strong></label></p>';
		echo '<textarea id="occ_titles_voice_avoid" name="occ_titles_voice_profile[avoid]" rows="3" class="large-text">' . esc_textarea( $avoid ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'One word or phrase per line. The model should avoid these.', 'oneclickcontent-titles' ) . '</p>';

		echo '<p><label for="occ_titles_voice_examples"><strong>' . esc_html__( 'Example titles', 'oneclickcontent-titles' ) . '</strong></label></p>';
		echo '<textarea id="occ_titles_voice_examples" name="occ_titles_voice_profile[examples]" rows="4" class="large-text">' . esc_textarea( $examples ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'Paste 3–10 example titles that represent your voice.', 'oneclickcontent-titles' ) . '</p>';
		echo '</div>';
	}
	/**
	 * Callback function for the Post Types setting field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_post_types_callback() {
		$selected_post_types = (array) get_option( 'occ_titles_post_types', array( 'post' ) );
		$post_types          = get_post_types( array( 'public' => true ), 'names', 'and' );
		unset( $post_types['attachment'] );

		echo '<p>' . esc_html__( 'Select which post types OneClickContent - Titles should be enabled on:', 'oneclickcontent-titles' ) . '</p>';
		echo '<p><em>' . esc_html__( 'Custom post types must have titles enabled.', 'oneclickcontent-titles' ) . '</em></p>';

		foreach ( $post_types as $post_type ) {
			$checked         = in_array( $post_type, $selected_post_types, true ) ? 'checked' : '';
			$post_type_label = str_replace( '_', ' ', ucwords( $post_type ) );
			echo '<label class="toggle-switch">';
			echo '<input type="checkbox" name="occ_titles_post_types[]" value="' . esc_attr( $post_type ) . '" class="occ_titles-settings-checkbox" ' . esc_attr( $checked ) . '>';
			echo '<span class="slider"></span>';
			echo '</label>';
			echo '<span class="post-type-label">' . esc_html( $post_type_label ) . '</span><br>';
		}
	}

	/**
	 * Callback function for the settings section description.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure the settings for the OneClickContent - Titles plugin.', 'oneclickcontent-titles' ) . '</p>';
	}

	/**
	 * Callback function for the voice profile section description.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function occ_titles_voice_section_callback() {
		echo '<p>' . esc_html__( 'Define the tone and examples that should guide title generation.', 'oneclickcontent-titles' ) . '</p>';
	}


	/**
	 * Auto-saves the settings via AJAX.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function occ_titles_auto_save() {
		if ( ! check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce', false ) ) {
			Occ_Titles_Logger::get_instance()->warning(
				'Settings autosave failed nonce verification.',
				array( 'action' => 'occ_titles_auto_save' )
			);
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'oneclickcontent-titles' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			Occ_Titles_Logger::get_instance()->warning(
				'Settings autosave denied due to insufficient permissions.',
				array(
					'action'     => 'occ_titles_auto_save',
					'capability' => 'manage_options',
				)
			);
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oneclickcontent-titles' ) ) );
		}

		$allowed_fields = array(
			'occ_titles_ai_provider',
			'occ_titles_openai_api_key',
			'occ_titles_post_types',
			'occ_titles_openai_model',
			'occ_titles_google_api_key',
			'occ_titles_logging_enabled',
		);

		if ( isset( $_POST['field_name'], $_POST['field_value'] ) ) {
			$field_name = sanitize_text_field( wp_unslash( $_POST['field_name'] ) );
			if ( ! in_array( $field_name, $allowed_fields, true ) ) {
				Occ_Titles_Logger::get_instance()->warning(
					'Settings autosave rejected invalid field.',
					array( 'field_name' => $field_name )
				);
				wp_send_json_error( array( 'message' => __( 'Invalid field name.', 'oneclickcontent-titles' ) ) );
			}

			if ( 'occ_titles_logging_enabled' === $field_name ) {
				$field_value = self::occ_titles_sanitize_logging_enabled( wp_unslash( $_POST['field_value'] ) );
			} elseif ( 'occ_titles_openai_api_key' === $field_name ) {
				$field_value = self::occ_titles_sanitize_openai_api_key( wp_unslash( $_POST['field_value'] ) );
			} elseif ( 'occ_titles_google_api_key' === $field_name ) {
				$field_value = self::occ_titles_sanitize_google_api_key( wp_unslash( $_POST['field_value'] ) );
			} else {
				$field_value = is_array( $_POST['field_value'] )
					? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_value'] ) )
					: sanitize_text_field( wp_unslash( $_POST['field_value'] ) );
			}

			update_option( $field_name, $field_value );

			// If the AI Provider setting is changed, signal the front-end to refresh the page.
			if ( 'occ_titles_ai_provider' === $field_name ) {
				wp_send_json_success(
					array(
						'message' => __( 'Settings saved successfully.', 'oneclickcontent-titles' ),
						'refresh' => true,
					)
				);
			} else {
				wp_send_json_success(
					array(
						'message' => __( 'Settings saved successfully.', 'oneclickcontent-titles' ),
					)
				);
			}
		} else {
			Occ_Titles_Logger::get_instance()->warning(
				'Settings autosave missing expected payload.',
				array( 'action' => 'occ_titles_auto_save' )
			);
			wp_send_json_error( array( 'message' => __( 'Missing field_name or field_value.', 'oneclickcontent-titles' ) ) );
		}
	}

	/**
	 * Render the API key validation badge.
	 *
	 * @since 1.1.1
	 * @param string $provider Provider slug.
	 * @return void
	 */
	private function render_api_key_badge( $provider ) {
		$status_data = $this->get_api_key_status_data( $provider );
		$status      = $status_data['status'];
		$checked_at  = $status_data['checked_at'];
		$label       = $this->get_api_key_status_label( $status );
		$meta        = $this->get_api_key_status_meta( $status, $checked_at );
		$class       = 'occ_titles-api-badge status-' . $status;

		echo '<div class="occ_titles-api-status">';
		printf(
			'<span class="%1$s" data-provider="%2$s" data-status="%3$s">%4$s</span>',
			esc_attr( $class ),
			esc_attr( $provider ),
			esc_attr( $status ),
			esc_html( $label )
		);
		printf(
			'<span class="occ_titles-api-meta" data-provider="%1$s">%2$s</span>',
			esc_attr( $provider ),
			esc_html( $meta )
		);
		echo '</div>';
	}

	/**
	 * Get the API key status data.
	 *
	 * @since 1.1.1
	 * @param string $provider Provider slug.
	 * @return array
	 */
	private function get_api_key_status_data( $provider ) {
		$option = self::get_api_key_status_option( $provider );
		$data   = $option ? get_option( $option, array() ) : array();

		return wp_parse_args(
			is_array( $data ) ? $data : array(),
			array(
				'status'     => 'unknown',
				'message'    => '',
				'checked_at' => '',
			)
		);
	}

	/**
	 * Get the status label.
	 *
	 * @since 1.1.1
	 * @param string $status Status value.
	 * @return string
	 */
	private function get_api_key_status_label( $status ) {
		if ( 'valid' === $status ) {
			return __( 'Valid', 'oneclickcontent-titles' );
		}

		if ( 'invalid' === $status ) {
			return __( 'Invalid', 'oneclickcontent-titles' );
		}

		return __( 'Not validated', 'oneclickcontent-titles' );
	}

	/**
	 * Get the status meta line.
	 *
	 * @since 1.1.1
	 * @param string $status     Status value.
	 * @param string $checked_at Checked timestamp.
	 * @return string
	 */
	private function get_api_key_status_meta( $status, $checked_at ) {
		if ( 'unknown' === $status || empty( $checked_at ) ) {
			return __( 'Not checked yet.', 'oneclickcontent-titles' );
		}

		return sprintf(
			/* translators: %s: date/time of last API key validation. */
			__( 'Last checked: %s', 'oneclickcontent-titles' ),
			$checked_at
		);
	}

	/**
	 * Display admin notices for settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function display_admin_notices() {
		if ( empty( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$page = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'occ_titles-settings' !== $page ) {
			return;
		}

		$errors = get_settings_errors( 'occ_titles_settings' );
		if ( empty( $errors ) ) {
			return;
		}

		$updated_shown = false;

		foreach ( $errors as $error ) {
			$type    = isset( $error['type'] ) ? $error['type'] : 'error';
			$message = isset( $error['message'] ) ? $error['message'] : '';

			$code = isset( $error['code'] ) ? $error['code'] : '';

			if ( 'updated' === $type || 'settings_updated' === $code ) {
				if ( $updated_shown ) {
					continue;
				}
				$updated_shown = true;
				$type          = 'success';
			}

			if ( '' === $message ) {
				continue;
			}

			$css_class = 'notice notice-' . sanitize_html_class( $type ) . ' is-dismissible';
			printf(
				'<div class="%1$s"><p>%2$s</p></div>',
				esc_attr( $css_class ),
				esc_html( $message )
			);
		}
	}
}
