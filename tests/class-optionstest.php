<?php
/**
 * Tests for settings sanitization and options.
 *
 * @package Occ_Titles
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/admin/class-occ-titles-settings.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-openai-helper.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-google-helper.php';
require_once dirname( __DIR__ ) . '/includes/class-occ-titles-activator.php';

/**
 * Settings sanitization tests.
 *
 * @since 1.1.0
 */
class OptionsTest extends Occ_Titles_Test_Case {

	/**
	 * Ensure help remains routable without a duplicate Settings menu item.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function test_register_options_page_hides_help_submenu() {
		Functions\when( '__' )->alias(
			function ( $text ) {
				return $text;
			}
		);

		Functions\expect( 'add_options_page' )->once();
		Functions\expect( 'add_submenu_page' )->once();
		Functions\expect( 'remove_submenu_page' )
			->once()
			->with( 'options-general.php', 'occ_titles-help' );

		$settings = new Occ_Titles_Settings();
		$settings->occ_titles_register_options_page();

		$this->addToAssertionCount( 1 );
	}

	/**
	 * Ensure post types sanitize to an array.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function test_sanitize_post_types_requires_array() {
		Functions\when( 'update_option' )->justReturn( true );

		$result = Occ_Titles_Settings::occ_titles_sanitize_post_types( 'post' );

		$this->assertSame( array(), $result );
	}

	/**
	 * Ensure post types are sanitized.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function test_sanitize_post_types_sanitizes_values() {
		Functions\when( 'update_option' )->justReturn( true );

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( $value );
			}
		);

		$result = Occ_Titles_Settings::occ_titles_sanitize_post_types( array( ' post ', 'page' ) );

		$this->assertSame( array( 'post', 'page' ), $result );
	}

	/**
	 * Ensure editor locations render as full-card checkbox controls.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function test_post_types_render_as_selectable_cards() {
		Functions\when( 'get_option' )->justReturn( array( 'post' ) );
		Functions\when( 'get_post_types' )->justReturn(
			array(
				'post' => (object) array(
					'labels' => (object) array( 'singular_name' => 'Post' ),
				),
			)
		);
		Functions\when( 'esc_attr' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_html' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_html__' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'checked' )->justReturn( ' checked="checked"' );

		$settings = new Occ_Titles_Settings();
		ob_start();
		$settings->occ_titles_post_types_callback();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'class="occ_titles-post-type-card-surface"', $output );
		$this->assertStringContainsString( 'class="occ_titles-post-type-card-indicator" aria-hidden="true"', $output );
		$this->assertStringContainsString( 'type="checkbox"', $output );
		$this->assertStringNotContainsString( 'occ_titles-post-type-card-state', $output );
	}

	/**
	 * Ensure activation enables posts and pages by default.
	 *
	 * @since 2.1.1
	 * @return void
	 */
	public function test_activator_defaults_to_posts_and_pages() {
		$updated_options = array();

		Functions\when( 'get_option' )->alias(
			function () {
				return false;
			}
		);

		Functions\when( 'update_option' )->alias(
			function ( $option_name, $option_value ) use ( &$updated_options ) {
				$updated_options[ $option_name ] = $option_value;
				return true;
			}
		);

		Occ_Titles_Activator::activate();

		$this->assertSame( array( 'post', 'page' ), $updated_options['occ_titles_post_types'] );
		$this->assertSame( 0, $updated_options['occ_titles_post_types_customized'] );
		$this->assertSame( 'auto', $updated_options['occ_titles_openai_model'] );
		$this->assertSame( 0, $updated_options['occ_titles_logging_enabled'] );
	}

	/**
	 * Ensure model settings accept automatic and reject malformed values.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function test_model_sanitization_supports_automatic_selection() {
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( (string) $value );
			}
		);

		$this->assertSame( 'auto', Occ_Titles_Settings::occ_titles_sanitize_openai_model( 'auto' ) );
		$this->assertSame( 'gpt-5.6-terra', Occ_Titles_Settings::occ_titles_sanitize_openai_model( 'gpt-5.6-terra' ) );
		$this->assertSame( 'auto', Occ_Titles_Settings::occ_titles_sanitize_openai_model( '<bad model>' ) );
		$this->assertSame( 'auto', Occ_Titles_Settings::occ_titles_sanitize_google_model( 'auto' ) );
		$this->assertSame( 'gemini-2.5-flash', Occ_Titles_Settings::occ_titles_sanitize_google_model( 'gemini-2.5-flash' ) );
		$this->assertSame( 'auto', Occ_Titles_Settings::occ_titles_sanitize_google_model( 'not-gemini' ) );
	}

	/**
	 * Ensure AJAX autosave persists a sanitized OpenAI model selection.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function test_model_autosave_persists_sanitized_openai_selection() {
		$_POST = array(
			'field_name'  => 'occ_titles_openai_model',
			'field_value' => ' gpt-5.6-terra ',
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( (string) $value );
			}
		);

		$updated = array();
		Functions\when( 'update_option' )->alias(
			function ( $name, $value ) use ( &$updated ) {
				$updated = array( $name, $value );
				return true;
			}
		);
		Functions\when( '__' )->alias(
			function ( $text ) {
				return $text;
			}
		);
		Functions\when( 'wp_send_json_success' )->alias(
			function () {
				throw new RuntimeException( 'saved' );
			}
		);

		try {
			Occ_Titles_Settings::occ_titles_auto_save();
		} catch ( RuntimeException $exception ) {
			$this->assertSame( 'saved', $exception->getMessage() );
		} finally {
			$_POST = array();
		}

		$this->assertSame( array( 'occ_titles_openai_model', 'gpt-5.6-terra' ), $updated );
	}

	/**
	 * Ensure the main picker stays short while account models remain searchable.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function test_openai_model_picker_separates_simple_and_advanced_choices() {
		Functions\when( 'get_option' )->alias(
			function ( $option_name, $fallback = false ) {
				if ( 'occ_titles_openai_model' === $option_name ) {
					return 'gpt-saved-custom';
				}

				if ( 'occ_titles_openai_api_key' === $option_name ) {
					return 'secret-key';
				}

				return $fallback;
			}
		);
		Functions\when( 'wp_remote_get' )->justReturn( array( 'response' => array( 'code' => 200 ) ) );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn(
			wp_json_encode(
				array(
					'data' => array(
						array( 'id' => 'gpt-saved-custom' ),
						array( 'id' => 'gpt-brand-new' ),
					),
				)
			)
		);
		Functions\when( '__' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_html__' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_attr__' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_attr' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_html' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\when( 'selected' )->alias(
			function ( $selected, $current ) {
				return $selected === $current ? ' selected="selected"' : '';
			}
		);

		$settings = new Occ_Titles_Settings();
		ob_start();
		$settings->occ_titles_openai_model_callback();
		$output = ob_get_clean();

		$main_select = strstr( $output, '</select>', true );
		$this->assertStringContainsString( 'Automatic (Recommended)', $main_select );
		$this->assertStringContainsString( 'gpt-saved-custom (saved model)', $main_select );
		$this->assertStringNotContainsString( 'gpt-brand-new', $main_select );
		$this->assertStringContainsString( 'data-occ-model-search', $output );
		$this->assertStringContainsString( '<option value="gpt-brand-new">', $output );
	}

	/**
	 * Ensure legacy defaults expand to pages unless customized.
	 *
	 * @since 2.1.1
	 * @return void
	 */
	public function test_normalize_post_type_defaults_expands_legacy_default() {
		$updated_options = array();

		Functions\when( 'get_option' )->alias(
			function ( $option_name, $fallback = false ) {
				if ( 'occ_titles_post_types' === $option_name ) {
					return array( 'post' );
				}

				if ( 'occ_titles_post_types_customized' === $option_name ) {
					return 0;
				}

				return $fallback;
			}
		);

		Functions\when( 'update_option' )->alias(
			function ( $option_name, $option_value ) use ( &$updated_options ) {
				$updated_options[ $option_name ] = $option_value;
				return true;
			}
		);

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( (string) $value );
			}
		);

		Occ_Titles_Settings::maybe_normalize_post_type_defaults();

		$this->assertSame( array( 'post', 'page' ), $updated_options['occ_titles_post_types'] );
	}

	/**
	 * Ensure legacy defaults are not changed after customization.
	 *
	 * @since 2.1.1
	 * @return void
	 */
	public function test_normalize_post_type_defaults_respects_customization() {
		$updated_options = array();

		Functions\when( 'get_option' )->alias(
			function ( $option_name, $fallback = false ) {
				if ( 'occ_titles_post_types' === $option_name ) {
					return array( 'post' );
				}

				if ( 'occ_titles_post_types_customized' === $option_name ) {
					return 1;
				}

				return $fallback;
			}
		);

		Functions\when( 'update_option' )->alias(
			function ( $option_name, $option_value ) use ( &$updated_options ) {
				$updated_options[ $option_name ] = $option_value;
				return true;
			}
		);

		Occ_Titles_Settings::maybe_normalize_post_type_defaults();

		$this->assertArrayNotHasKey( 'occ_titles_post_types', $updated_options );
	}

	/**
	 * Ensure logging enabled sanitization returns 1/0.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function test_sanitize_logging_enabled() {
		Functions\when( 'absint' )->alias(
			function ( $value ) {
				return (int) $value;
			}
		);

		$this->assertSame( 1, Occ_Titles_Settings::occ_titles_sanitize_logging_enabled( '1' ) );
		$this->assertSame( 0, Occ_Titles_Settings::occ_titles_sanitize_logging_enabled( '0' ) );
	}

	/**
	 * Ensure AI provider sanitization accepts valid values.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function test_sanitize_ai_provider_accepts_known_provider() {
		unset( $_POST['option_page'] );

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( $value );
			}
		);

		$this->assertSame( 'google', Occ_Titles_Settings::occ_titles_sanitize_ai_provider( 'google' ) );
	}

	/**
	 * Ensure AI provider sanitization falls back on invalid values.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function test_sanitize_ai_provider_rejects_unknown_provider() {
		unset( $_POST['option_page'] );

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( $value );
			}
		);

		$this->assertSame( 'openai', Occ_Titles_Settings::occ_titles_sanitize_ai_provider( 'invalid' ) );
	}

	/**
	 * Ensure voice profile sanitization normalizes fields.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function test_sanitize_voice_profile() {
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( (string) $value );
			}
		);

		$input = array(
			'tone'            => ' casual ',
			'formality'       => 'formal',
			'sentence_length' => 'short',
			'cta_style'       => 'direct',
			'must_use'        => "alpha\nbeta",
			'avoid'           => 'spam, clickbait',
			'examples'        => array( 'Example one', 'Example two' ),
		);

		$result = Occ_Titles_Settings::occ_titles_sanitize_voice_profile( $input );

		$this->assertSame( 'casual', $result['tone'] );
		$this->assertSame( 'formal', $result['formality'] );
		$this->assertSame( array( 'alpha', 'beta' ), $result['must_use'] );
		$this->assertSame( array( 'spam', 'clickbait' ), $result['avoid'] );
		$this->assertSame( array( 'Example one', 'Example two' ), $result['examples'] );
	}

	/**
	 * Ensure voice profile sanitization handles arrays.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function test_sanitize_voice_profile_handles_arrays() {
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( (string) $value );
			}
		);

		$input = array(
			'must_use' => array( 'alpha', 'beta', 'alpha' ),
			'avoid'    => array( 'spam', 'spam', 'clickbait' ),
		);

		$result = Occ_Titles_Settings::occ_titles_sanitize_voice_profile( $input );

		$this->assertSame( array( 'alpha', 'beta' ), $result['must_use'] );
		$this->assertSame( array( 'spam', 'clickbait' ), $result['avoid'] );
	}

	/**
	 * Ensure bundled help assets resolve to local plugin URLs.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function test_get_help_asset_url_returns_local_plugin_asset() {
		Functions\when( 'sanitize_file_name' )->alias(
			function ( $value ) {
				return basename( (string) $value );
			}
		);

		Functions\when( 'plugin_dir_url' )->alias(
			function () {
				return 'https://example.com/wp-content/plugins/oneclickcontent-titles/';
			}
		);

		$result = Occ_Titles_Settings::get_help_asset_url( 'OneClickContentTitles-Block.png' );

		$this->assertSame(
			'https://example.com/wp-content/plugins/oneclickcontent-titles/assets/OneClickContentTitles-Block.png',
			$result
		);
	}
}
