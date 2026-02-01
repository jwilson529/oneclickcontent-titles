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
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( $value );
			}
		);

		$result = Occ_Titles_Settings::occ_titles_sanitize_post_types( array( ' post ', 'page' ) );

		$this->assertSame( array( 'post', 'page' ), $result );
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
}
