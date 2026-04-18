<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Handles the admin-specific hooks for enqueuing stylesheets and JavaScript,
 * and provides the functionality for generating SEO-optimized titles using OpenAI.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and handles the admin-specific hooks.
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/admin
 */
class Occ_Titles_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Helper class instance for handling OpenAI API requests.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Occ_Titles_OpenAI_Helper $openai_helper Instance of the helper class.
	 */
	private $openai_helper;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name   = $plugin_name;
		$this->version       = $version;
		$this->openai_helper = new Occ_Titles_OpenAI_Helper();
	}


	/**
	 * Add a custom meta box to the Block Editor.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_box() {
		$screen = get_current_screen();
		if ( 'post' === $screen->base && $this->is_block_editor_active() ) {
			add_meta_box(
				'occ_titles_meta_box',
				__( 'OCC Titles Meta Box', 'oneclickcontent-titles' ),
				array( $this, 'render_meta_box_content' ),
				$screen->post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the content of the custom meta box.
	 *
	 * @since 1.0.0
	 */
	public function render_meta_box_content() {
		echo '';
	}

	/**
	 * Check if the Block Editor (Gutenberg) is active.
	 *
	 * @since 1.0.0
	 * @return bool True if Block Editor is active, false otherwise.
	 */
	private function is_block_editor_active() {
		if ( function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( get_post_type() ) ) {
			return true;
		}
		return false;
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		$screen              = get_current_screen();
		$selected_post_types = (array) get_option( 'occ_titles_post_types', array() );

		if ( 'post' === $screen->base && in_array( $screen->post_type, $selected_post_types, true ) && ! wp_should_load_block_editor_scripts_and_styles() ) {
			wp_enqueue_style(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'css/occ-titles-admin.css',
				array(),
				$this->version,
				'all'
			);
		} elseif ( in_array( $screen->base, array( 'settings_page_occ_titles-settings', 'settings_page_occ_titles-help' ), true ) ) {
			wp_enqueue_style(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'css/occ-titles-admin.css',
				array(),
				$this->version,
				'all'
			);
		}
	}

	/**
	 * Enqueue admin scripts for the plugin.
	 *
	 * Loads the settings script on all admin pages and conditionally enqueues
	 * additional scripts on selected post type edit pages or the plugin's
	 * settings page. Localized data is provided for AJAX functionality.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$screen              = get_current_screen(); // Get current screen object.
		$selected_post_types = (array) get_option( 'occ_titles_post_types', array() );
		$post_id             = 0;

		if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
			$post_id = (int) $GLOBALS['post']->ID;
		}

		$post_slug      = $post_id ? get_post_field( 'post_name', $post_id ) : '';
		$post_permalink = $post_id ? get_permalink( $post_id ) : '';

		// Enqueue the settings script on all admin pages.
		wp_enqueue_script(
			'occ-titles-settings',
			plugin_dir_url( __FILE__ ) . 'js/occ-titles-settings.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		$localization = array(
			'ajax_url'              => admin_url( 'admin-ajax.php' ),
			'occ_titles_ajax_nonce' => wp_create_nonce( 'occ_titles_ajax_nonce' ),
			'selected_post_types'   => $selected_post_types,
			'current_post_type'     => isset( $screen->post_type ) ? $screen->post_type : '',
			'svg_url'               => plugin_dir_url( __DIR__ ) . 'img/ai-sparkle.svg',
			'now'                   => current_time( 'mysql' ),
			'settings_url'          => admin_url( 'options-general.php?page=occ_titles-settings' ),
			'post_id'               => $post_id,
			'post_slug'             => $post_slug,
			'post_permalink'        => $post_permalink,
			'strings'               => array(
				'badge_valid'          => __( 'Valid', 'oneclickcontent-titles' ),
				'badge_invalid'        => __( 'Invalid', 'oneclickcontent-titles' ),
				'badge_unknown'        => __( 'Not validated', 'oneclickcontent-titles' ),
				'badge_not_checked'    => __( 'Not checked yet.', 'oneclickcontent-titles' ),
				/* translators: %s: date/time of last API key validation. */
				'badge_last_checked'   => __( 'Last checked: %s', 'oneclickcontent-titles' ),
				'results_title'        => __( 'Title Recommendations', 'oneclickcontent-titles' ),
				'results_empty'        => __( 'Generate titles to see results.', 'oneclickcontent-titles' ),
				'results_last'         => __( 'Last generated:', 'oneclickcontent-titles' ),
				'results_provider'     => __( 'Provider:', 'oneclickcontent-titles' ),
				'results_top_picks'    => __( 'Top picks', 'oneclickcontent-titles' ),
				'results_more_options' => __( 'More options', 'oneclickcontent-titles' ),
				'results_summary'      => __( 'Start with the strongest options below. Open the full breakdown only if you want the deeper score math.', 'oneclickcontent-titles' ),
				'score_current'        => __( 'Score Current Title', 'oneclickcontent-titles' ),
				'copy_all'             => __( 'Copy All', 'oneclickcontent-titles' ),
				'download_csv'         => __( 'Download CSV', 'oneclickcontent-titles' ),
				'collapse_results'     => __( 'Collapse results', 'oneclickcontent-titles' ),
				'show_results'         => __( 'Show results', 'oneclickcontent-titles' ),
				'open_breakdown'       => __( 'Open full breakdown', 'oneclickcontent-titles' ),
				'breakdown_label'      => __( 'Detailed scoring, previews, exports, and keyword notes', 'oneclickcontent-titles' ),
				'pick_best_for'        => __( 'Best for', 'oneclickcontent-titles' ),
				'pick_current'         => __( 'Current title', 'oneclickcontent-titles' ),
				'pick_apply'           => __( 'Apply this title', 'oneclickcontent-titles' ),
				'pick_why'             => __( 'Why it works', 'oneclickcontent-titles' ),
				'pick_pixel'           => __( 'Pixel width', 'oneclickcontent-titles' ),
				'pick_length'          => __( 'Length', 'oneclickcontent-titles' ),
				'pick_keywords'        => __( 'Keyword fit', 'oneclickcontent-titles' ),
				'pick_readability'     => __( 'Readability', 'oneclickcontent-titles' ),
				'controls_kicker'      => __( 'Optimize before you generate', 'oneclickcontent-titles' ),
				'controls_title'       => __( 'Generation Controls', 'oneclickcontent-titles' ),
				'controls_intro'       => __( 'Choose the outcome you want, then generate a fresh batch.', 'oneclickcontent-titles' ),
				'controls_help'        => __( 'Set goal, style, and optional keyword targets before generating.', 'oneclickcontent-titles' ),
				'generate_titles'      => __( 'Generate Titles', 'oneclickcontent-titles' ),
				'revert_title'         => __( 'Revert to Original Title', 'oneclickcontent-titles' ),
				'collapse_controls'    => __( 'Collapse controls', 'oneclickcontent-titles' ),
				'show_controls'        => __( 'Show controls', 'oneclickcontent-titles' ),
			),
		);

		wp_localize_script( 'occ-titles-settings', 'occ_titles_admin_vars', $localization );

		// Enqueue scripts on the selected post type edit pages.
		if ( 'post' === $screen->base && in_array( $screen->post_type, $selected_post_types, true ) ) {
			wp_enqueue_script(
				'occ-titles-admin',
				plugin_dir_url( __FILE__ ) . 'js/occ-titles-admin.js',
				array( 'jquery', 'occ-titles-settings' ),
				$this->version,
				true
			);

			wp_localize_script( 'occ-titles-admin', 'occ_titles_admin_vars', $localization );
		} elseif ( 'settings_page_occ_titles-settings' === $screen->base ) {
			wp_enqueue_script(
				'occ-titles-admin-post',
				plugin_dir_url( __FILE__ ) . 'js/occ-titles-admin.js',
				array( 'jquery', 'occ-titles-settings' ),
				$this->version,
				true
			);

			wp_localize_script( 'occ-titles-admin-post', 'occ_titles_admin_vars', $localization );
		}
	}
	/**
	 * Enqueue block editor styles.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function occ_titles_enqueue_block_editor_assets() {
		wp_enqueue_style(
			'occ-titles-editor-css',
			plugin_dir_url( __FILE__ ) . 'css/occ-titles-admin.css',
			array(),
			$this->version
		);
	}



	/**
	 * Handle the AJAX request to generate titles.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function generate_titles() {
		$request_id = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'occ_titles_', true );

		Occ_Titles_Logger::get_instance()->info(
			'Title generation request received.',
			array( 'request_id' => $request_id )
		);

		// Check nonce for security.
		if ( ! check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce', false ) ) {
			Occ_Titles_Logger::get_instance()->warning(
				'Title generation failed nonce verification.',
				array(
					'action'     => 'occ_titles_generate_titles',
					'request_id' => $request_id,
				)
			);
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'oneclickcontent-titles' ) ) );
		}

		// Verify the user has permission.
		if ( ! current_user_can( 'edit_posts' ) ) {
			Occ_Titles_Logger::get_instance()->warning(
				'Title generation denied due to insufficient permissions.',
				array(
					'capability' => 'edit_posts',
					'request_id' => $request_id,
				)
			);
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oneclickcontent-titles' ) ) );
		}

		// Get and sanitize incoming data.
		$content      = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
		$style        = isset( $_POST['style'] ) ? sanitize_text_field( wp_unslash( $_POST['style'] ) ) : '';
		$seed_title   = isset( $_POST['seed_title'] ) ? sanitize_text_field( wp_unslash( $_POST['seed_title'] ) ) : '';
		$variation    = isset( $_POST['variation'] ) ? sanitize_text_field( wp_unslash( $_POST['variation'] ) ) : '';
		$keyword      = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		$count        = isset( $_POST['count'] ) ? absint( $_POST['count'] ) : 5;
		$intent       = isset( $_POST['intent'] ) ? sanitize_text_field( wp_unslash( $_POST['intent'] ) ) : '';
		$ellipsis     = isset( $_POST['ellipsis'] ) ? absint( $_POST['ellipsis'] ) : 0;
		$raw_keywords = array();
		if ( isset( $_POST['keywords'] ) ) {
			$raw_keywords = is_array( $_POST['keywords'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['keywords'] ) )
				: sanitize_text_field( wp_unslash( $_POST['keywords'] ) );
		}
		$keywords = array();
		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
			Occ_Titles_Logger::get_instance()->warning(
				'Title generation denied for post due to insufficient permissions.',
				array(
					'post_id'    => $post_id,
					'capability' => 'edit_post',
					'request_id' => $request_id,
				)
			);
			wp_send_json_error( array( 'message' => __( 'Permission denied for this post.', 'oneclickcontent-titles' ) ) );
		}

		if ( is_string( $raw_keywords ) ) {
			$keywords = array_filter( array_map( 'sanitize_text_field', explode( ',', $raw_keywords ) ) );
		} elseif ( is_array( $raw_keywords ) ) {
			$keywords = array_filter( array_map( 'sanitize_text_field', $raw_keywords ) );
		}

		if ( $count < 1 ) {
			$count = 1;
		} elseif ( $count > 5 ) {
			$count = 5;
		}

		Occ_Titles_Logger::get_instance()->info(
			'Title generation payload sanitized.',
			array(
				'request_id'     => $request_id,
				'content_length' => strlen( $content ),
				'style'          => $style,
				'seed_title'     => $seed_title,
				'variation'      => $variation,
				'count'          => $count,
				'intent'         => $intent,
				'ellipsis'       => $ellipsis ? 'yes' : 'no',
				'keywords'       => $keywords,
			)
		);

		if ( empty( $content ) ) {
			Occ_Titles_Logger::get_instance()->warning(
				'Title generation request missing content.',
				array(
					'provider'   => get_option( 'occ_titles_ai_provider', 'openai' ),
					'request_id' => $request_id,
				)
			);
			wp_send_json_error( array( 'message' => __( 'Missing content.', 'oneclickcontent-titles' ) ) );
		}

		// Determine which AI provider to use.
		$provider      = get_option( 'occ_titles_ai_provider', 'openai' );
		$voice_profile = get_option( 'occ_titles_voice_profile', array() );
		$voice_samples = get_option( 'occ_titles_voice_samples', array() );
		$voice_samples = is_array( $voice_samples ) ? $voice_samples : array();

		if ( 'openai' === $provider ) {
			$api_key = get_option( 'occ_titles_openai_api_key' );
			if ( empty( $api_key ) ) {
				Occ_Titles_Logger::get_instance()->warning(
					'OpenAI API key missing for title generation.',
					array( 'provider' => 'openai' )
				);
				wp_send_json_error( array( 'message' => __( 'Missing OpenAI API key.', 'oneclickcontent-titles' ) ) );
			}

			$this->enforce_generation_rate_limit( $post_id, $request_id );

			$helper = new Occ_Titles_OpenAI_Helper();
			$result = $helper->generate_titles_openai( $api_key, $content, $style, $request_id, $count, $seed_title, $variation, $keyword, $voice_profile, $voice_samples, $intent, $keywords, $ellipsis );
		} elseif ( 'google' === $provider ) {
			$api_key = get_option( 'occ_titles_google_api_key' );
			if ( empty( $api_key ) ) {
				Occ_Titles_Logger::get_instance()->warning(
					'Google API key missing for title generation.',
					array( 'provider' => 'google' )
				);
				wp_send_json_error( array( 'message' => __( 'Missing Google Gemini API key.', 'oneclickcontent-titles' ) ) );
			}

			$this->enforce_generation_rate_limit( $post_id, $request_id );

			$helper = new Occ_Titles_Google_Helper();
			$result = $helper->generate_titles_google( $api_key, $content, $style, $request_id, $count, $seed_title, $variation, $keyword, $voice_profile, $voice_samples, $intent, $keywords, $ellipsis );
		} else {
			Occ_Titles_Logger::get_instance()->error(
				'Unknown AI provider configured.',
				array(
					'provider'   => $provider,
					'request_id' => $request_id,
				)
			);
			wp_send_json_error( array( 'message' => __( 'Unknown AI provider.', 'oneclickcontent-titles' ) ) );
		}

		if ( is_array( $result ) ) {
			Occ_Titles_Logger::get_instance()->info(
				'Title generation succeeded.',
				array(
					'provider'   => $provider,
					'request_id' => $request_id,
					'count'      => count( $result ),
				)
			);
			wp_send_json_success(
				array(
					'titles'       => $result,
					'provider'     => $provider,
					'intent'       => $intent,
					'ellipsis'     => $ellipsis ? 1 : 0,
					'keywords'     => $keywords,
					'generated_at' => current_time( 'mysql' ),
				)
			);
		} else {
			Occ_Titles_Logger::get_instance()->error(
				'Title generation failed.',
				array(
					'provider'   => $provider,
					'request_id' => $request_id,
					'message'    => is_string( $result ) ? $result : 'unknown error',
				)
			);
			wp_send_json_error( array( 'message' => $result ) );
		}
	}

	/**
	 * Save generated title results for a post.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function save_generated_results() {
		if ( ! check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'oneclickcontent-titles' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'oneclickcontent-titles' ) ) );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied for this post.', 'oneclickcontent-titles' ) ) );
		}

		$raw_results = isset( $_POST['results'] ) ? sanitize_textarea_field( wp_unslash( $_POST['results'] ) ) : '';
		if ( empty( $raw_results ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing results payload.', 'oneclickcontent-titles' ) ) );
		}

		$decoded = json_decode( $raw_results, true );
		if ( ! is_array( $decoded ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid results format.', 'oneclickcontent-titles' ) ) );
		}

		$decoded = $this->sanitize_results_payload( $decoded );

		update_post_meta( $post_id, '_occ_titles_results', $decoded );

		Occ_Titles_Logger::get_instance()->info(
			'Saved title generation results.',
			array(
				'post_id' => $post_id,
				'count'   => isset( $decoded['titles'] ) && is_array( $decoded['titles'] ) ? count( $decoded['titles'] ) : 0,
			)
		);

		wp_send_json_success( array( 'message' => __( 'Results saved.', 'oneclickcontent-titles' ) ) );
	}

	/**
	 * Retrieve saved title results for a post.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function get_saved_results() {
		if ( ! check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'oneclickcontent-titles' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'oneclickcontent-titles' ) ) );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied for this post.', 'oneclickcontent-titles' ) ) );
		}

		$results = get_post_meta( $post_id, '_occ_titles_results', true );
		if ( empty( $results ) || ! is_array( $results ) ) {
			wp_send_json_success( array( 'results' => array() ) );
		}

		wp_send_json_success( array( 'results' => $results ) );
	}

	/**
	 * Sanitize a generated results payload before saving it.
	 *
	 * @since 2.1.0
	 * @param mixed $payload Results payload value.
	 * @return mixed
	 */
	private function sanitize_results_payload( $payload ) {
		if ( is_array( $payload ) ) {
			$sanitized_payload = array();

			foreach ( $payload as $key => $value ) {
				$sanitized_key                       = is_string( $key ) ? sanitize_key( $key ) : $key;
				$sanitized_payload[ $sanitized_key ] = $this->sanitize_results_payload( $value );
			}

			return $sanitized_payload;
		}

		if ( is_string( $payload ) ) {
			return sanitize_text_field( $payload );
		}

		if ( is_bool( $payload ) || is_int( $payload ) || is_float( $payload ) || null === $payload ) {
			return $payload;
		}

		return sanitize_text_field( (string) $payload );
	}

	/**
	 * Save a voice sample from an applied title.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function save_voice_sample() {
		if ( ! check_ajax_referer( 'occ_titles_ajax_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'oneclickcontent-titles' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oneclickcontent-titles' ) ) );
		}

		$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( $post_id && ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied for this post.', 'oneclickcontent-titles' ) ) );
		}

		if ( '' === $title ) {
			wp_send_json_error( array( 'message' => __( 'Missing title.', 'oneclickcontent-titles' ) ) );
		}

		$samples = get_option( 'occ_titles_voice_samples', array() );
		$samples = is_array( $samples ) ? $samples : array();

		array_unshift( $samples, $title );
		$samples = array_values( array_unique( $samples ) );
		$samples = array_slice( $samples, 0, 20 );

		update_option( 'occ_titles_voice_samples', $samples );

		Occ_Titles_Logger::get_instance()->info(
			'Saved voice sample.',
			array(
				'title' => $title,
				'count' => count( $samples ),
			)
		);

		wp_send_json_success( array( 'message' => __( 'Voice sample saved.', 'oneclickcontent-titles' ) ) );
	}

	/**
	 * Enforce a short cooldown between title generation requests per user/post pair.
	 *
	 * @since 1.1.2
	 * @param int    $post_id    Current post ID.
	 * @param string $request_id Request identifier for logs.
	 * @return void
	 */
	private function enforce_generation_rate_limit( $post_id, $request_id ) {
		$user_id          = get_current_user_id();
		$cooldown_seconds = (int) apply_filters( 'occ_titles_generation_cooldown_seconds', 8, $post_id, $user_id );

		if ( $cooldown_seconds < 1 ) {
			$cooldown_seconds = 1;
		}

		$rate_limit_key = 'occ_titles_gen_' . md5( $user_id . '|' . $post_id );
		$is_limited     = (bool) get_transient( $rate_limit_key );

		if ( $is_limited ) {
			Occ_Titles_Logger::get_instance()->warning(
				'Title generation rate limited.',
				array(
					'request_id'       => $request_id,
					'user_id'          => $user_id,
					'post_id'          => $post_id,
					'cooldown_seconds' => $cooldown_seconds,
				)
			);

			wp_send_json_error(
				array(
					/* translators: %d: cooldown in seconds before retrying generation. */
					'message' => sprintf( __( 'Please wait %d seconds before generating titles again.', 'oneclickcontent-titles' ), $cooldown_seconds ),
				)
			);
		}

		set_transient( $rate_limit_key, 1, $cooldown_seconds );
	}
}
