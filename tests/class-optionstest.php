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
require_once dirname( __DIR__ ) . '/includes/class-occ-titles-activator.php';

/**
 * Settings sanitization tests.
 *
 * @since 1.1.0
 */
class OptionsTest extends Occ_Titles_Test_Case {

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
		$this->assertSame( 'gpt-5.5', $updated_options['occ_titles_openai_model'] );
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
