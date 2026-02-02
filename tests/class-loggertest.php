<?php
/**
 * Tests for the logger class.
 *
 * @package Occ_Titles
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/includes/class-occ-titles-logger.php';
require_once __DIR__ . '/class-occ-titles-test-filesystem.php';

/**
 * Logger tests.
 *
 * @since 1.1.0
 */
class LoggerTest extends Occ_Titles_Test_Case {

	/**
	 * Reset singleton between tests.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	private function reset_logger_instance() {
		$reflection = new ReflectionProperty( 'Occ_Titles_Logger', 'instance' );
		$reflection->setAccessible( true );
		$reflection->setValue( null, null );
	}

	/**
	 * Logger writes when enabled.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function test_logger_writes_when_enabled() {
		$this->reset_logger_instance();

		$log_file   = '/occ-titles-test.log';
		$filesystem = new Occ_Titles_Test_Filesystem();

		Functions\when( 'get_option' )->alias(
			function ( $name, $fallback = null ) {
				if ( 'occ_titles_logging_enabled' === $name ) {
					return 1;
				}
				return $fallback;
			}
		);

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value = null ) use ( $log_file, $filesystem ) {
				if ( 'occ_titles_log_file_path' === $tag ) {
					return $log_file;
				}
				if ( 'occ_titles_filesystem' === $tag ) {
					return $filesystem;
				}
				return $value;
			}
		);

		Functions\when( 'current_time' )->alias(
			function () {
				return '2026-01-28 00:00:00';
			}
		);

		Functions\when( 'plugin_dir_path' )->alias(
			function () {
				return dirname( __DIR__ ) . '/';
			}
		);

		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );

		$logger = Occ_Titles_Logger::get_instance();

		$this->assertTrue( $logger->info( 'Test message', array( 'foo' => 'bar' ) ) );

		$contents = $filesystem->get_contents( $log_file );
		$this->assertStringContainsString( 'INFO', $contents );
		$this->assertStringContainsString( 'Test message', $contents );
	}

	/**
	 * Logger skips when disabled.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function test_logger_skips_when_disabled() {
		$this->reset_logger_instance();

		$log_file   = '/occ-titles-test-disabled.log';
		$filesystem = new Occ_Titles_Test_Filesystem();

		Functions\when( 'get_option' )->alias(
			function ( $name, $fallback = null ) {
				if ( 'occ_titles_logging_enabled' === $name ) {
					return 0;
				}
				return $fallback;
			}
		);

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value = null ) use ( $log_file, $filesystem ) {
				if ( 'occ_titles_log_file_path' === $tag ) {
					return $log_file;
				}
				if ( 'occ_titles_logging_enabled' === $tag ) {
					return false;
				}
				if ( 'occ_titles_filesystem' === $tag ) {
					return $filesystem;
				}
				return $value;
			}
		);

		Functions\when( 'current_time' )->alias(
			function () {
				return '2026-01-28 00:00:00';
			}
		);

		Functions\when( 'plugin_dir_path' )->alias(
			function () {
				return dirname( __DIR__ ) . '/';
			}
		);

		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );

		$logger = Occ_Titles_Logger::get_instance();

		$this->assertFalse( $logger->info( 'Should not log' ) );
		$this->assertSame( '', $filesystem->get_contents( $log_file ) );
	}
}
