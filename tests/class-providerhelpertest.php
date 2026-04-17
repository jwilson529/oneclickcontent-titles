<?php
/**
 * Tests for provider helper hardening.
 *
 * @package Occ_Titles
 * @since 1.1.2
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/includes/class-occ-titles-logger.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-openai-helper.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-google-helper.php';

/**
 * Provider helper tests.
 *
 * @since 1.1.2
 */
class ProviderHelperTest extends Occ_Titles_Test_Case {

	/**
	 * Reset logger singleton between tests.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	private function reset_logger_instance() {
		$reflection = new ReflectionProperty( 'Occ_Titles_Logger', 'instance' );
		$reflection->setAccessible( true );
		$reflection->setValue( null, null );
	}

	/**
	 * Register common function mocks for helper tests.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	private function prime_helper_environment() {
		Functions\when( 'plugin_dir_path' )->alias(
			function () {
				return dirname( __DIR__ ) . '/';
			}
		);

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value = null ) {
				return $value;
			}
		);

		Functions\when( 'get_option' )->alias(
			function ( $name, $fallback = null ) {
				if ( 'occ_titles_openai_model' === $name ) {
					return 'gpt-4o-mini';
				}

				if ( 'occ_titles_google_model' === $name ) {
					return 'gemini-1.5-flash';
				}

				if ( 'occ_titles_logging_enabled' === $name ) {
					return 0;
				}

				return $fallback;
			}
		);

		Functions\when( '__' )->alias(
			function ( $text ) {
				return $text;
			}
		);

		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
		Functions\when( 'is_wp_error' )->alias(
			function () {
				return false;
			}
		);
	}

	/**
	 * OpenAI errors are sanitized before being returned to the UI.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function test_openai_generation_sanitizes_provider_error_message() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		Functions\when( 'wp_remote_post' )->alias(
			function () {
				return array( 'response' => array( 'code' => 429 ) );
			}
		);

		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function () {
				return 429;
			}
		);

		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function () {
				return wp_json_encode(
					array(
						'error' => array(
							'message' => '<strong>Quota exceeded</strong><script>alert("x")</script>',
						),
					)
				);
			}
		);

		$helper = new Occ_Titles_OpenAI_Helper();
		$result = $helper->generate_titles_openai( 'secret-key', 'Body copy for testing.' );

		$this->assertSame( 'Quota exceeded', $result );
	}

	/**
	 * OpenAI malformed responses do not expose raw provider output.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function test_openai_generation_returns_generic_message_for_invalid_json_output() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		Functions\when( 'wp_remote_post' )->alias(
			function () {
				return array( 'response' => array( 'code' => 200 ) );
			}
		);

		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function () {
				return 200;
			}
		);

		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function () {
				return wp_json_encode(
					array(
						'output' => array(
							array(
								'content' => array(
									array(
										'type' => 'output_text',
										'text' => '<div>not valid json</div>',
									),
								),
							),
						),
					)
				);
			}
		);

		$helper = new Occ_Titles_OpenAI_Helper();
		$result = $helper->generate_titles_openai( 'secret-key', 'Body copy for testing.' );

		$this->assertSame( 'Unable to parse the response from OpenAI.', $result );
	}

	/**
	 * Google Gemini errors are sanitized before being returned to the UI.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function test_google_generation_sanitizes_provider_error_message() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		Functions\when( 'wp_remote_post' )->alias(
			function () {
				return array( 'response' => array( 'code' => 400 ) );
			}
		);

		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function () {
				return 400;
			}
		);

		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function () {
				return wp_json_encode(
					array(
						'error' => array(
							'message' => '<em>Bad request</em><style>body{display:none}</style>',
						),
					)
				);
			}
		);

		$helper = new Occ_Titles_Google_Helper();
		$result = $helper->generate_titles_google( 'secret-key', 'Body copy for testing.' );

		$this->assertSame( 'Bad request', $result );
	}

	/**
	 * Google title generation sends the API key in headers, not the URL.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function test_google_generation_sends_api_key_in_headers() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		$captured = array();

		Functions\when( 'wp_remote_post' )->alias(
			function ( $endpoint, $args ) use ( &$captured ) {
				$captured = array(
					'endpoint' => $endpoint,
					'args'     => $args,
				);

				return array( 'response' => array( 'code' => 200 ) );
			}
		);

		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function () {
				return 200;
			}
		);

		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function () {
				return wp_json_encode(
					array(
						'candidates' => array(
							array(
								'content' => array(
									'parts' => array(
										array(
											'text' => '[{"index":1,"text":"Test title","style":"How-To","sentiment":"Positive","keywords":["alpha"]}]',
										),
									),
								),
							),
						),
					)
				);
			}
		);

		$helper = new Occ_Titles_Google_Helper();
		$result = $helper->generate_titles_google( 'secret-key', 'Body copy for testing.' );

		$this->assertIsArray( $result );
		$this->assertStringNotContainsString( 'secret-key', $captured['endpoint'] );
		$this->assertSame( 'secret-key', $captured['args']['headers']['x-goog-api-key'] );
	}

	/**
	 * Google validation sends the API key in headers, not the URL.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function test_google_validation_sends_api_key_in_headers() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		$captured = array();

		Functions\when( 'wp_remote_post' )->alias(
			function ( $endpoint, $args ) use ( &$captured ) {
				$captured = array(
					'endpoint' => $endpoint,
					'args'     => $args,
				);

				return array( 'response' => array( 'code' => 200 ) );
			}
		);

		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function () {
				return 200;
			}
		);

		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function () {
				return wp_json_encode(
					array(
						'candidates' => array(
							array(
								'content' => array(
									'parts' => array(
										array(
											'text' => 'Hello, Gemini!',
										),
									),
								),
							),
						),
					)
				);
			}
		);

		$result = Occ_Titles_Google_Helper::validate_google_api_key( 'secret-key' );

		$this->assertTrue( $result );
		$this->assertStringNotContainsString( 'secret-key', $captured['endpoint'] );
		$this->assertSame( 'secret-key', $captured['args']['headers']['x-goog-api-key'] );
	}
}
