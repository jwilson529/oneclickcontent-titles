<?php
/**
 * Tests for the loader class.
 *
 * @package Occ_Titles
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/includes/class-occ-titles-loader.php';
require_once __DIR__ . '/class-occ-titles-test-component.php';

/**
 * Loader tests.
 *
 * @since 1.1.0
 */
class LoaderTest extends Occ_Titles_Test_Case {

	/**
	 * Ensure hooks are registered when run is called.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function test_run_registers_hooks() {
		$loader = new Occ_Titles_Loader();

		$component = new Occ_Titles_Test_Component();

		$loader->add_action( 'init', $component, 'action_callback', 10, 1 );
		$loader->add_filter( 'the_content', $component, 'filter_callback', 12, 2 );

		Functions\expect( 'add_action' )
			->once()
			->with( 'init', array( $component, 'action_callback' ), 10, 1 );

		Functions\expect( 'add_filter' )
			->once()
			->with( 'the_content', array( $component, 'filter_callback' ), 12, 2 );

		$loader->run();

		$this->assertTrue( true );
	}
}
