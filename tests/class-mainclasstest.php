<?php
/**
 * Tests for the main plugin class.
 *
 * @package Occ_Titles
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/includes/class-occ-titles.php';

/**
 * Main class tests.
 *
 * @since 1.1.0
 */
class MainClassTest extends Occ_Titles_Test_Case {

	/**
	 * Ensure plugin name and version are set as expected.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function test_plugin_name_and_version() {
		$plugin_root = dirname( __DIR__ ) . '/';

		if ( ! defined( 'OCC_TITLES_VERSION' ) ) {
			define( 'OCC_TITLES_VERSION', '2.0.0' );
		}

		Functions\when( 'plugin_dir_path' )->alias(
			function () use ( $plugin_root ) {
				return $plugin_root;
			}
		);

		$plugin = new Occ_Titles();

		$this->assertSame( 'oneclickcontent-titles', $plugin->get_plugin_name() );
		$this->assertSame( '2.0.0', $plugin->get_version() );
	}
}
