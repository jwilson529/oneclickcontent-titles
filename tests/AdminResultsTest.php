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
	 * Ensure saving results updates post meta.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function test_save_results_updates_meta() {
		$_POST = array(
			'post_id' => 123,
			'results' => json_encode(
				array(
					'titles' => array(
						array( 'text' => 'Test title' ),
					),
					'generated_at' => '2026-01-28 12:00:00',
				)
			),
		);

		Functions\when( 'check_ajax_referer' )->alias( function() {
			return true;
		} );

		Functions\when( 'current_user_can' )->alias( function() {
			return true;
		} );

		Functions\when( 'wp_unslash' )->alias( function( $value ) {
			return $value;
		} );

		Functions\when( 'plugin_dir_path' )->alias( function() {
			return dirname( __DIR__ ) . '/';
		} );

		Functions\when( 'get_option' )->alias( function() {
			return 0;
		} );

		Functions\when( '__' )->alias( function( $text ) {
			return $text;
		} );

		$captured = array();
		Functions\when( 'update_post_meta' )->alias( function( $post_id, $key, $value ) use ( &$captured ) {
			$captured = array( $post_id, $key, $value );
			return true;
		} );

		Functions\when( 'wp_send_json_success' )->alias( function() {
			throw new RuntimeException( 'done' );
		} );

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
}
