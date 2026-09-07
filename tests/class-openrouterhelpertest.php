<?php
/**
 * OpenRouter request and settings contract regressions.
 *
 * @package Occ_Titles
 * @since 2.2.0
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once __DIR__ . '/class-wp-error.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-openai-helper.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-openrouter-helper.php';

/**
 * Exercise provider boundaries without sending paid requests.
 */
class OpenRouterHelperTest extends Occ_Titles_Test_Case {
	/**
	 * Test options.
	 *
	 * @var array
	 */
	private $options = array();
	/**
	 * Test transients.
	 *
	 * @var array
	 */
	private $cache = array();

	/**
	 * Isolate WordPress persistence and HTTP response helpers.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
			define( 'HOUR_IN_SECONDS', 3600 );
		}
		Functions\when( '__' )->returnArg();
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( wp_strip_all_tags( $value ) );
			}
		);
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
		Functions\when( 'wp_salt' )->justReturn( 'test-only-salt' );
		Functions\when( 'is_wp_error' )->alias(
			function ( $value ) {
				return $value instanceof WP_Error;
			}
		);
		Functions\when( 'get_option' )->alias(
			function ( $key, $fallback = false ) {
				return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $fallback;
			}
		);
		Functions\when( 'update_option' )->alias(
			function ( $key, $value ) {
				$this->options[ $key ] = $value;
				return true;
			}
		);
		Functions\when( 'get_transient' )->alias(
			function ( $key ) {
				return isset( $this->cache[ $key ] ) ? $this->cache[ $key ] : false;
			}
		);
		Functions\when( 'set_transient' )->alias(
			function ( $key, $value ) {
				$this->cache[ $key ] = $value;
				return true;
			}
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function ( $response ) {
				return $response['body'];
			}
		);
		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function ( $response ) {
				return $response['response']['code'];
			}
		);
	}

	/**
	 * Return one complete model result.
	 *
	 * @param string $finish Completion reason.
	 * @param int    $count Number of titles.
	 * @return array
	 */
	private function response( $finish = 'stop', $count = 3 ) {
		$title  = array(
			'text'      => '<b>Library hours</b>',
			'style'     => 'How-To',
			'sentiment' => 'Neutral',
			'keywords'  => array( 'library' ),
		);
		$choice = array(
			'finish_reason' => $finish,
			'message'       => array( 'content' => wp_json_encode( array_fill( 0, $count, $title ) ) ),
		);
		return array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode(
				array(
					'model'   => 'test/text-model',
					'choices' => array( $choice ),
				)
			),
		);
	}

	/**
	 * Accept explicit model IDs and reject URL/whitespace input.
	 *
	 * @return void
	 */
	public function test_model_ids_and_invalid_option_preserve_previous_selection() {
		foreach ( array( 'anthropic/claude-haiku-4.5', 'openai/gpt-6-astra', 'provider/model:free' ) as $model ) {
			$this->assertSame( $model, Occ_Titles_OpenRouter_Helper::sanitize_model( $model ) );
		}
		foreach ( array( '', 'model', 'https://host/model', 'provider/model?key=x', 'provider/model with spaces', array() ) as $model ) {
			$this->assertSame( '', Occ_Titles_OpenRouter_Helper::sanitize_model( $model ) );
		}
		$this->options['occ_titles_openrouter_model'] = 'test/previous';
		Functions\expect( 'add_settings_error' )->once();
		$this->assertSame( 'test/previous', Occ_Titles_OpenRouter_Helper::sanitize_model_option( 'invalid' ) );
	}

	/**
	 * Use the fixed endpoint, header secret, selected model, and shared controls.
	 *
	 * @return void
	 */
	public function test_request_preserves_controls_and_normalizes_complete_titles() {
		Functions\expect( 'wp_remote_post' )->once()->with(
			'https://openrouter.ai/api/v1/chat/completions',
			\Mockery::on(
				function ( $args ) {
					$this->assertSame( 'Bearer test-key', $args['headers']['Authorization'] );
					$body = json_decode( $args['body'], true );
					$this->assertSame( 'test/text-model', $body['model'] );
					$this->assertSame( 'Article source.', $body['messages'][1]['content'] );
					$this->assertStringContainsString( 'library', $body['messages'][0]['content'] );
					$this->assertStringContainsString( 'Original title', $body['messages'][0]['content'] );
					$this->assertArrayNotHasKey( 'temperature', $body );
					$this->assertArrayNotHasKey( 'response_format', $body );
					return true;
				}
			)
		)->andReturn( $this->response() );
		$result = Occ_Titles_OpenRouter_Helper::request_titles( 'Article source.', 'test-key', 'test/text-model', 'How-To', '', 3, 'Original title', 'shorter', 'library' );
		$this->assertCount( 3, $result['titles'] );
		$this->assertSame( 'Library hours', $result['titles'][0]['text'] );
		$this->assertSame( 3, $result['titles'][2]['index'] );
	}

	/**
	 * Incomplete and malformed output must not replace existing editor results.
	 *
	 * @return void
	 */
	public function test_incomplete_and_malformed_results_are_rejected() {
		Functions\when( 'wp_remote_post' )->justReturn( $this->response( 'length' ) );
		$result = Occ_Titles_OpenRouter_Helper::request_titles( 'Source', 'test-key', 'test/model' );
		$this->assertSame( 'openrouter_incomplete', $result->get_error_code() );
		foreach ( array( null, 'not json', '[]', '[{"text":"Missing metadata"}]', '[{"text":"","style":"","sentiment":"","keywords":[]}]' ) as $text ) {
			$this->assertInstanceOf( WP_Error::class, Occ_Titles_OpenRouter_Helper::parse_titles( $text, 1 ) );
		}
		$response = json_decode( $this->response()['body'], true );
		$valid    = $response['choices'][0]['message']['content'];
		$fence    = str_repeat( chr( 96 ), 3 );
		$this->assertCount( 3, Occ_Titles_OpenRouter_Helper::parse_titles( $fence . "json\n" . $valid . "\n" . $fence, 3 ) );
		$this->assertInstanceOf( WP_Error::class, Occ_Titles_OpenRouter_Helper::parse_titles( $valid, 5 ) );
	}

	/**
	 * Remote failures never expose response bodies or credentials.
	 *
	 * @return void
	 */
	public function test_http_and_network_errors_are_safe() {
		foreach ( array( 200, 400, 401, 402, 403, 404, 429, 502, 503 ) as $status ) {
			Functions\when( 'wp_remote_post' )->justReturn(
				array(
					'response' => array( 'code' => $status ),
					'body'     => '{"error":{"message":"<script>private-test-key</script>"}}',
				)
			);
			$result = Occ_Titles_OpenRouter_Helper::request_titles( 'Source', 'private-test-key', 'test/model' );
			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertStringNotContainsString( 'private-test-key', $result->get_error_message() );
			$this->assertStringNotContainsString( '<script>', $result->get_error_message() );
		}
		Functions\when( 'wp_remote_post' )->justReturn( new WP_Error( 'network', 'private-test-key' ) );
		$this->assertSame( 'openrouter_network', Occ_Titles_OpenRouter_Helper::request_titles( 'Source', 'private-test-key', 'test/model' )->get_error_code() );
	}

	/**
	 * Test results expire and cannot validate a different key or model.
	 *
	 * @return void
	 */
	public function test_test_status_is_bound_to_key_model_and_expiry() {
		Functions\when( 'wp_remote_post' )->justReturn( $this->response() );
		Occ_Titles_OpenRouter_Helper::test_model( 'test-key', 'test/model' );
		$this->assertSame( 'passed', Occ_Titles_OpenRouter_Helper::get_test_status( 'test-key', 'test/model' )['state'] );
		$this->assertSame( 'untested', Occ_Titles_OpenRouter_Helper::get_test_status( 'other-key', 'test/model' )['state'] );
		$this->assertSame( 'untested', Occ_Titles_OpenRouter_Helper::get_test_status( 'test-key', 'test/other' )['state'] );
		$this->assertStringNotContainsString( 'test-key', wp_json_encode( $this->options['occ_titles_openrouter_test'] ) );
		$this->options['occ_titles_openrouter_test']['time'] = time() - 8 * DAY_IN_SECONDS;
		$this->assertSame( 'untested', Occ_Titles_OpenRouter_Helper::get_test_status( 'test-key', 'test/model' )['state'] );
	}

	/**
	 * Public catalogs exclude image-only output and are cached without a key.
	 *
	 * @return void
	 */
	public function test_catalog_filters_and_caches_public_text_models() {
		Functions\expect( 'wp_remote_get' )->once()->with( 'https://openrouter.ai/api/v1/models', array( 'timeout' => 20 ) )->andReturn(
			array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode(
					array(
						'data' => array(
							array(
								'id'           => 'test/text',
								'name'         => '<b>Text model</b>',
								'architecture' => array(
									'input_modalities'  => array( 'text', 'image' ),
									'output_modalities' => array( 'text' ),
								),
							),
							array(
								'id'           => 'test/image',
								'architecture' => array(
									'input_modalities'  => array( 'text' ),
									'output_modalities' => array( 'image' ),
								),
							),
						),
					)
				),
			)
		);
		$models = Occ_Titles_OpenRouter_Helper::get_models();
		$this->assertCount( 1, $models );
		$this->assertSame( 'Text model', $models[0]['name'] );
		$this->assertSame( $models, Occ_Titles_OpenRouter_Helper::get_models() );
	}

	/**
	 * Privileged provider actions stop before any HTTP work for non-admins.
	 *
	 * @return void
	 */
	public function test_ajax_requires_nonce_and_administrator_capability() {
		Functions\expect( 'check_ajax_referer' )->once()->with( 'occ_titles_ajax_nonce', 'nonce' );
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_send_json_error' )->once()->with( array( 'message' => 'Permission denied.' ), 403 );
		Functions\expect( 'wp_remote_get' )->never();
		Functions\expect( 'wp_remote_post' )->never();
		( new Occ_Titles_OpenRouter_Helper() )->ajax_action();
		$this->assertTrue( true );
	}
}
