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
			array( 'sanitize_callback' => 'sanitize_text_field' )
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

		// Add the settings section.
		add_settings_section(
			'occ_titles_settings_section',
			__( 'OneClickContent - Titles Settings', 'oneclickcontent-titles' ),
			array( $this, 'occ_titles_settings_section_callback' ),
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

		// Retrieve the selected provider (default to "openai").
		$provider = get_option( 'occ_titles_ai_provider', 'openai' );

		// Conditionally register and add the provider-specific API key and model fields.
		if ( 'openai' === $provider ) {
			// Register OpenAI settings.
			register_setting(
				'occ_titles_settings',
				'occ_titles_openai_api_key',
				array( 'sanitize_callback' => 'sanitize_text_field' )
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
				array( 'sanitize_callback' => 'sanitize_text_field' )
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
	 * Display admin notices for settings.
	 */
	public function display_admin_notices() {
		settings_errors();
	}
}
