<?php
/**
 * Tests for provider helper hardening.
 *
 * @package Occ_Titles
 * @since 2.0.1
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/includes/class-occ-titles-logger.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-google-helper.php';

/**
 * Provider helper tests.
 *
 * @since 2.0.1
 */
class ProviderHelperTest extends Occ_Titles_Test_Case {

	/**
	 * Reset logger singleton between tests.
	 *
	 * @since 2.0.1
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
	 * @since 2.0.1
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
				if ( 'occ_titles_google_model' === $name ) {
					return 'gemini-2.5-flash';
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
		Functions\when( 'get_transient' )->alias(
			function () {
				return false;
			}
		);
		Functions\when( 'set_transient' )->alias(
			function () {
				return true;
			}
		);
		Functions\when( 'is_wp_error' )->alias(
			function () {
				return false;
			}
		);
	}

	/**
	 * Google Gemini errors are sanitized before being returned to the UI.
	 *
	 * @since 2.0.1
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
	 * @since 2.0.1
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
	 * Google title generation requests structured JSON output.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public function test_google_generation_requests_structured_json_output() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		$captured = array();

		Functions\when( 'wp_remote_post' )->alias(
			function ( $endpoint, $args ) use ( &$captured ) {
				$captured = json_decode( $args['body'], true );

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
		$helper->generate_titles_google( 'secret-key', 'Body copy for testing.' );

		$this->assertSame( 'application/json', $captured['generationConfig']['responseMimeType'] );
		$this->assertSame( 'array', $captured['generationConfig']['responseJsonSchema']['type'] );
		$this->assertSame( 'object', $captured['generationConfig']['responseJsonSchema']['items']['type'] );
	}

	/**
	 * Google title generation can recover when Gemini wraps the JSON in extra prose.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public function test_google_generation_parses_wrapped_json_payload() {
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
						'candidates' => array(
							array(
								'content' => array(
									'parts' => array(
										array(
											'text' => "Here are your title ideas:\n[{\"index\":1,\"text\":\"Wrapped title\",\"style\":\"How-To\",\"sentiment\":\"Positive\",\"keywords\":[\"alpha\"]}]",
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
		$this->assertSame( 'Wrapped title', $result[0]['text'] );
	}

	/**
	 * Google model list is fetched dynamically and filtered to text generation models.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public function test_google_model_list_is_loaded_from_api() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		Functions\when( 'wp_remote_get' )->alias(
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
						'models' => array(
							array(
								'baseModelId' => 'gemini-2.5-flash',
								'displayName' => 'Gemini 2.5 Flash',
								'supportedGenerationMethods' => array( 'generateContent' ),
							),
							array(
								'baseModelId' => 'gemini-2.5-pro',
								'displayName' => 'Gemini 2.5 Pro',
								'supportedGenerationMethods' => array( 'generateContent' ),
							),
							array(
								'baseModelId' => 'gemini-2.5-flash-preview-09-2025',
								'displayName' => 'Gemini 2.5 Flash Preview',
								'supportedGenerationMethods' => array( 'generateContent' ),
							),
							array(
								'baseModelId' => 'gemini-2.5-flash-image',
								'displayName' => 'Gemini 2.5 Flash Image',
								'supportedGenerationMethods' => array( 'generateContent' ),
							),
							array(
								'baseModelId' => 'text-embedding-004',
								'displayName' => 'Text Embedding 004',
								'supportedGenerationMethods' => array( 'embedContent' ),
							),
						),
					)
				);
			}
		);

		$models = Occ_Titles_Google_Helper::get_available_google_models( 'secret-key' );

		$this->assertIsArray( $models );
		$this->assertArrayHasKey( 'gemini-2.5-flash', $models );
		$this->assertArrayHasKey( 'gemini-2.5-pro', $models );
		$this->assertArrayHasKey( 'gemini-2.5-flash-preview-09-2025', $models );
		$this->assertArrayNotHasKey( 'gemini-2.5-flash-image', $models );
		$this->assertArrayNotHasKey( 'text-embedding-004', $models );
	}

	/**
	 * Google model list uses a cached result when available.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public function test_google_model_list_uses_cache_when_available() {
		$this->reset_logger_instance();
		$this->prime_helper_environment();

		Functions\when( 'get_transient' )->alias(
			function () {
				return array(
					'gemini-2.5-flash' => 'Gemini 2.5 Flash',
				);
			}
		);

		Functions\when( 'wp_remote_get' )->alias(
			function () {
				throw new RuntimeException( 'remote call should not happen' );
			}
		);

		$models = Occ_Titles_Google_Helper::get_available_google_models( 'secret-key' );

		$this->assertSame(
			array(
				'gemini-2.5-flash' => 'Gemini 2.5 Flash',
			),
			$models
		);
	}

	/**
	 * Google validation sends the API key in headers, not the URL.
	 *
	 * @since 2.0.1
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
