<?php
/**
 * Tests for saving generated results.
 *
 * @package Occ_Titles
 * @since 1.1.1
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/admin/class-occ-titles-openai-helper.php';
require_once dirname( __DIR__ ) . '/admin/class-occ-titles-admin.php';

/**
 * Admin results tests.
 *
 * @since 1.1.1
 */
class AdminResultsTest extends Occ_Titles_Test_Case {

	/**
	 * Ensure editor detection does not rely on store availability.
	 *
	 * Gutenberg Code Editor includes a `.wp-editor-area` element, so that class
	 * cannot distinguish it from the Classic Editor.
	 *
	 * @since 2.1.5
	 * @return void
	 */
	public function test_editor_mode_detection_uses_block_editor_signals() {
		$script = file_get_contents( dirname( __DIR__ ) . '/admin/js/occ-titles-admin.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local test fixture.

		$this->assertIsString( $script );
		$bridge = file_get_contents( dirname( __DIR__ ) . '/admin/js/occ-titles-editor-bridge.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local test fixture.

		$this->assertIsString( $bridge );
		$this->assertStringContainsString( "document.body.classList.contains( 'block-editor-page' )", $bridge );
		$this->assertStringNotContainsString( "isBlockEditor = !! window.wp.data.select( 'core/editor' )", $bridge );
		$this->assertStringNotContainsString( "document.querySelector( '.wp-editor-area' ) !== null", $script );
	}

	/**
	 * Ensure saved results load once and detailed analysis is not nested.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function test_editor_results_have_stable_single_click_interactions() {
		$script = file_get_contents( dirname( __DIR__ ) . '/admin/js/occ-titles-admin.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local test fixture.

		$this->assertIsString( $script );
		$this->assertStringContainsString( 'if ( savedResultsRequested )', $script );
		$this->assertStringContainsString( '$top_picks.append( $more_picks, $breakdown );', $script );
		$this->assertStringNotContainsString( '$more_picks_body.append( $breakdown );', $script );
	}

	/**
	 * Ensure each editor has a deterministic launcher anchor.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function test_editor_launchers_cover_classic_visual_and_code_modes() {
		$script = file_get_contents( dirname( __DIR__ ) . '/admin/js/occ-titles-admin.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local test fixture.

		$this->assertIsString( $script );
		$this->assertStringContainsString( "var \$title_wrap = \$( '#titlewrap' );", $script );
		$this->assertStringContainsString( "\$title_wrap.append( '<button id=\"occ_titles_generate_button\"", $script );
		$this->assertStringNotContainsString( "\$title_input.after( '<button id=\"occ_titles_generate_button\"", $script );
		$this->assertStringContainsString( "'h1.wp-block-post-title'", $script );
		$this->assertStringContainsString( "\$( '.editor-header__settings' ).first()", $script );
		$this->assertStringContainsString( "button_class: 'occ_titles_header_button'", $script );
	}

	/**
	 * Ensure compact editor controls are localized with the admin script.
	 *
	 * @since 2.1.5
	 * @return void
	 */
	public function test_enqueue_scripts_localizes_compact_editor_controls() {
		unset( $_GET['post'], $GLOBALS['post'] );

		Functions\when( 'get_current_screen' )->justReturn(
			(object) array(
				'base'      => 'post',
				'post_type' => 'post',
			)
		);
		Functions\when( 'get_option' )->justReturn( array( 'post', 'page' ) );
		Functions\when( 'admin_url' )->alias(
			function ( $path ) {
				return 'https://example.test/wp-admin/' . $path;
			}
		);
		Functions\when( 'wp_create_nonce' )->justReturn( 'test-nonce' );
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.test/wp-content/plugins/oneclickcontent-titles/admin/' );
		Functions\when( 'current_time' )->justReturn( '2026-07-21 12:00:00' );
		Functions\when( '__' )->returnArg();
		$enqueued = array();
		Functions\when( 'wp_enqueue_script' )->alias(
			function ( $handle, $source, $dependencies ) use ( &$enqueued ) {
				$enqueued[ $handle ] = array(
					'source'       => $source,
					'dependencies' => $dependencies,
				);
				return true;
			}
		);

		$localized = array();
		Functions\when( 'wp_localize_script' )->alias(
			function ( $handle, $object_name, $data ) use ( &$localized ) {
				$localized = array( $handle, $object_name, $data );
				return true;
			}
		);

		$admin = new Occ_Titles_Admin( 'oneclickcontent-titles', '2.1.5' );
		$admin->enqueue_scripts();

		$this->assertSame( 'occ-titles-admin', $localized[0] );
		$this->assertSame( 'occ_titles_admin_vars', $localized[1] );
		$this->assertSame( 'Title Assistant', $localized[2]['strings']['results_title'] );
		$this->assertSame( 'Regenerate', $localized[2]['strings']['regenerate_titles'] );
		$this->assertSame( 'Options', $localized[2]['strings']['show_controls'] );
		$this->assertSame( 'Undo', $localized[2]['strings']['undo_title'] );
		$this->assertSame( 'Details', $localized[2]['strings']['pick_details'] );
		$this->assertSame( 'The editor title could not be updated. Reload the editor and try again.', $localized[2]['strings']['title_update_failed'] );
		$this->assertArrayHasKey( 'occ-titles-editor-bridge', $enqueued );
		$this->assertSame( array(), $enqueued['occ-titles-editor-bridge']['dependencies'] );
		$this->assertSame( array( 'jquery', 'occ-titles-editor-bridge' ), $enqueued['occ-titles-admin']['dependencies'] );
	}

	/**
	 * Ensure saving results updates post meta.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function test_save_results_updates_meta() {
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );

		$_POST = array(
			'post_id' => 123,
			'results' => wp_json_encode(
				array(
					'titles'       => array(
						array( 'text' => 'Test title' ),
					),
					'generated_at' => '2026-01-28 12:00:00',
				)
			),
		);

		Functions\when( 'check_ajax_referer' )->alias(
			function () {
				return true;
			}
		);

		Functions\when( 'current_user_can' )->alias(
			function () {
				return true;
			}
		);

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return $value;
			}
		);

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( preg_replace( '/<[^>]*>/', '', (string) $value ) );
			}
		);

		Functions\when( 'sanitize_key' )->alias(
			function ( $value ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) );
			}
		);

		Functions\when( 'sanitize_textarea_field' )->alias(
			function ( $value ) {
				return trim( preg_replace( '/<[^>]*>/', '', (string) $value ) );
			}
		);

		Functions\when( 'plugin_dir_path' )->alias(
			function () {
				return dirname( __DIR__ ) . '/';
			}
		);

		Functions\when( 'get_option' )->alias(
			function () {
				return 0;
			}
		);

		Functions\when( '__' )->alias(
			function ( $text ) {
				return $text;
			}
		);

		$captured = array();
		Functions\when( 'update_post_meta' )->alias(
			function ( $post_id, $key, $value ) use ( &$captured ) {
				$captured = array( $post_id, $key, $value );
				return true;
			}
		);

		Functions\when( 'wp_send_json_success' )->alias(
			function () {
				throw new RuntimeException( 'done' );
			}
		);

		$admin = new Occ_Titles_Admin( 'oneclickcontent-titles', '1.1.1' );

		try {
			$admin->save_generated_results();
		} catch ( RuntimeException $exception ) {
			$this->assertSame( 'done', $exception->getMessage() );
		}

		$this->assertSame( 123, $captured[0] );
		$this->assertSame( '_occ_titles_results', $captured[1] );
		$this->assertIsArray( $captured[2] );
		$this->assertArrayHasKey( 'titles', $captured[2] );
	}

	/**
	 * Ensure saving results sanitizes nested payload values.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function test_save_results_sanitizes_nested_payload_values() {
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );

		$_POST = array(
			'post_id' => 123,
			'results' => wp_json_encode(
				array(
					'titles' => array(
						array(
							'text'  => '<strong>Clean title</strong>',
							'note'  => '<em>Helpful</em>',
							'score' => 92,
						),
					),
				)
			),
		);

		Functions\when( 'check_ajax_referer' )->alias(
			function () {
				return true;
			}
		);

		Functions\when( 'current_user_can' )->alias(
			function () {
				return true;
			}
		);

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return $value;
			}
		);

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( preg_replace( '/<[^>]*>/', '', (string) $value ) );
			}
		);

		Functions\when( 'sanitize_key' )->alias(
			function ( $value ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) );
			}
		);

		Functions\when( 'sanitize_textarea_field' )->alias(
			function ( $value ) {
				return trim( preg_replace( '/<[^>]*>/', '', (string) $value ) );
			}
		);

		Functions\when( 'plugin_dir_path' )->alias(
			function () {
				return dirname( __DIR__ ) . '/';
			}
		);

		Functions\when( 'get_option' )->alias(
			function () {
				return 0;
			}
		);

		Functions\when( '__' )->alias(
			function ( $text ) {
				return $text;
			}
		);

		$captured = array();
		Functions\when( 'update_post_meta' )->alias(
			function ( $post_id, $key, $value ) use ( &$captured ) {
				$captured = array( $post_id, $key, $value );
				return true;
			}
		);

		Functions\when( 'wp_send_json_success' )->alias(
			function () {
				throw new RuntimeException( 'done' );
			}
		);

		$admin = new Occ_Titles_Admin( 'oneclickcontent-titles', '1.1.1' );

		try {
			$admin->save_generated_results();
		} catch ( RuntimeException $exception ) {
			$this->assertSame( 'done', $exception->getMessage() );
		}

		$this->assertSame( 'Clean title', $captured[2]['titles'][0]['text'] );
		$this->assertSame( 'Helpful', $captured[2]['titles'][0]['note'] );
		$this->assertSame( 92, $captured[2]['titles'][0]['score'] );
	}
}
