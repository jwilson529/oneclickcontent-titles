<?php
/**
 * Tests for the uninstaller class.
 *
 * @package Occ_Titles
 * @since 2.0.1
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/includes/class-occ-titles-uninstaller.php';
require_once __DIR__ . '/class-occ-titles-test-filesystem.php';

/**
 * Uninstaller tests.
 *
 * @since 2.0.1
 */
class UninstallerTest extends Occ_Titles_Test_Case {

	/**
	 * Uninstall removes plugin-owned options, meta, and log artifacts.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public function test_uninstall_removes_plugin_data() {
		$deleted_options = array();
		$deleted_meta    = array();
		$log_file        = '/uploads/occ-titles-logs/occ-titles.log';
		$filesystem      = new Occ_Titles_Test_Filesystem();

		$filesystem->put_contents( $log_file, 'test log' );
		$filesystem->put_contents( '/uploads/occ-titles-logs/index.php', '<?php' );
		$filesystem->put_contents( '/uploads/occ-titles-logs/.htaccess', 'Deny from all' );
		$filesystem->put_contents( dirname( __DIR__ ) . '/occ-titles.log', 'legacy log' );
		$filesystem->put_contents( dirname( __DIR__ ) . '/plugin-error.log', 'legacy error log' );

		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'delete_option' )->alias(
			function ( $name ) use ( &$deleted_options ) {
				$deleted_options[] = $name;
				return true;
			}
		);
		Functions\when( 'delete_post_meta_by_key' )->alias(
			function ( $key ) use ( &$deleted_meta ) {
				$deleted_meta[] = $key;
				return true;
			}
		);
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value = null ) use ( $filesystem, $log_file ) {
				if ( 'occ_titles_filesystem' === $tag ) {
					return $filesystem;
				}
				if ( 'occ_titles_log_file_path' === $tag ) {
					return $log_file;
				}
				return $value;
			}
		);
		Functions\when( 'plugin_dir_path' )->alias(
			function ( $path ) {
				return trailingslashit( dirname( $path ) );
			}
		);
		Functions\when( 'trailingslashit' )->alias(
			function ( $path ) {
				return rtrim( $path, '/' ) . '/';
			}
		);
		Functions\when( 'wp_upload_dir' )->justReturn(
			array(
				'basedir' => '/uploads',
			)
		);

		Occ_Titles_Uninstaller::uninstall();

		$this->assertContains( 'occ_titles_openai_api_key', $deleted_options );
		$this->assertContains( 'occ_titles_voice_samples', $deleted_options );
		$this->assertSame( array( '_occ_titles_results' ), $deleted_meta );
		$this->assertSame( '', $filesystem->get_contents( $log_file ) );
		$this->assertSame( '', $filesystem->get_contents( '/uploads/occ-titles-logs/index.php' ) );
		$this->assertSame( '', $filesystem->get_contents( '/uploads/occ-titles-logs/.htaccess' ) );
	}
}
