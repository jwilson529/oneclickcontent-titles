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
		} elseif ( 'settings_page_occ_titles-settings' === $screen->base ) {
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
			'strings'               => array(
				'badge_valid'        => __( 'Valid', 'oneclickcontent-titles' ),
				'badge_invalid'      => __( 'Invalid', 'oneclickcontent-titles' ),
				'badge_unknown'      => __( 'Not validated', 'oneclickcontent-titles' ),
				'badge_not_checked'  => __( 'Not checked yet.', 'oneclickcontent-titles' ),
				/* translators: %s: date/time of last API key validation. */
				'badge_last_checked' => __( 'Last checked: %s', 'oneclickcontent-titles' ),
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
		$content    = isset( $_POST['content'] ) ? sanitize_text_field( wp_unslash( $_POST['content'] ) ) : '';
		$style      = isset( $_POST['style'] ) ? sanitize_text_field( wp_unslash( $_POST['style'] ) ) : '';
		$seed_title = isset( $_POST['seed_title'] ) ? sanitize_text_field( wp_unslash( $_POST['seed_title'] ) ) : '';
		$variation  = isset( $_POST['variation'] ) ? sanitize_text_field( wp_unslash( $_POST['variation'] ) ) : '';
		$keyword    = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		$count      = isset( $_POST['count'] ) ? absint( $_POST['count'] ) : 5;

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
		$provider = get_option( 'occ_titles_ai_provider', 'openai' );

		if ( 'openai' === $provider ) {
			$api_key = get_option( 'occ_titles_openai_api_key' );
			if ( empty( $api_key ) ) {
				Occ_Titles_Logger::get_instance()->warning(
					'OpenAI API key missing for title generation.',
					array( 'provider' => 'openai' )
				);
				wp_send_json_error( array( 'message' => __( 'Missing OpenAI API key.', 'oneclickcontent-titles' ) ) );
			}
			$helper = new Occ_Titles_OpenAI_Helper();
			$result = $helper->generate_titles_openai( $api_key, $content, $style, $request_id, $count, $seed_title, $variation, $keyword );
		} elseif ( 'google' === $provider ) {
			$api_key = get_option( 'occ_titles_google_api_key' );
			if ( empty( $api_key ) ) {
				Occ_Titles_Logger::get_instance()->warning(
					'Google API key missing for title generation.',
					array( 'provider' => 'google' )
				);
				wp_send_json_error( array( 'message' => __( 'Missing Google Gemini API key.', 'oneclickcontent-titles' ) ) );
			}
			// Occ_Titles_Google_Helper should be implemented similarly.
			$helper = new Occ_Titles_Google_Helper();
			$result = $helper->generate_titles_google( $api_key, $content, $style, $request_id, $count, $seed_title, $variation, $keyword );
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

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oneclickcontent-titles' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'oneclickcontent-titles' ) ) );
		}

		$raw_results = isset( $_POST['results'] ) ? wp_unslash( $_POST['results'] ) : '';
		if ( empty( $raw_results ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing results payload.', 'oneclickcontent-titles' ) ) );
		}

		$decoded = json_decode( $raw_results, true );
		if ( ! is_array( $decoded ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid results format.', 'oneclickcontent-titles' ) ) );
		}

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

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oneclickcontent-titles' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'oneclickcontent-titles' ) ) );
		}

		$results = get_post_meta( $post_id, '_occ_titles_results', true );
		if ( empty( $results ) || ! is_array( $results ) ) {
			wp_send_json_success( array( 'results' => array() ) );
		}

		wp_send_json_success( array( 'results' => $results ) );
	}
}
