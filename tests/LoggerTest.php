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

if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
	/**
	 * Minimal filesystem base for tests.
	 *
	 * @since 1.1.0
	 */
	abstract class WP_Filesystem_Base {
	}
}

/**
 * Minimal filesystem shim for logger tests.
 *
 * @since 1.1.0
 */
class Occ_Titles_Test_Filesystem extends WP_Filesystem_Base {

	/**
	 * Check if a path exists.
	 *
	 * @since 1.1.0
	 * @param string $path Path to check.
	 * @return bool
	 */
	public function exists( $path ) {
		return file_exists( $path );
	}

	/**
	 * Read file contents.
	 *
	 * @since 1.1.0
	 * @param string $path File path.
	 * @return string
	 */
	public function get_contents( $path ) {
		return (string) file_get_contents( $path );
	}

	/**
	 * Write file contents.
	 *
	 * @since 1.1.0
	 * @param string $path    File path.
	 * @param string $content Content.
	 * @param int    $mode    Mode.
	 * @return bool
	 */
	public function put_contents( $path, $content, $mode = 0644 ) {
		$written = file_put_contents( $path, $content );
		if ( false !== $written ) {
			chmod( $path, $mode );
			return true;
		}
		return false;
	}

	/**
	 * Check if path is writable.
	 *
	 * @since 1.1.0
	 * @param string $path Path.
	 * @return bool
	 */
	public function is_writable( $path ) {
		return is_writable( $path );
	}

	/**
	 * Check if directory.
	 *
	 * @since 1.1.0
	 * @param string $path Path.
	 * @return bool
	 */
	public function is_dir( $path ) {
		return is_dir( $path );
	}

	/**
	 * Create directory.
	 *
	 * @since 1.1.0
	 * @param string $path Path.
	 * @return bool
	 */
	public function mkdir( $path ) {
		if ( is_dir( $path ) ) {
			return true;
		}
		return mkdir( $path, 0755, true );
	}
}

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

		$log_file = sys_get_temp_dir() . '/occ-titles-test.log';

		if ( file_exists( $log_file ) ) {
			unlink( $log_file );
		}

		global $wp_filesystem;
		$wp_filesystem = new Occ_Titles_Test_Filesystem();

		Functions\when( 'get_option' )->alias(
			function ( $name, $default = null ) {
				if ( 'occ_titles_logging_enabled' === $name ) {
					return 1;
				}
				return $default;
			}
		);

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $log_file ) {
				if ( 'occ_titles_log_file_path' === $tag ) {
					return $log_file;
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
		Functions\when( 'wp_mkdir_p' )->alias(
			function ( $dir ) {
				if ( ! is_dir( $dir ) ) {
					mkdir( $dir, 0755, true );
				}
				return true;
			}
		);

		$logger = Occ_Titles_Logger::get_instance();

		$this->assertTrue( $logger->info( 'Test message', array( 'foo' => 'bar' ) ) );
		$this->assertFileExists( $log_file );

		$contents = file_get_contents( $log_file );
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

		$log_file = sys_get_temp_dir() . '/occ-titles-test-disabled.log';

		if ( file_exists( $log_file ) ) {
			unlink( $log_file );
		}

		global $wp_filesystem;
		$wp_filesystem = new Occ_Titles_Test_Filesystem();

		Functions\when( 'get_option' )->alias(
			function ( $name, $default = null ) {
				if ( 'occ_titles_logging_enabled' === $name ) {
					return 0;
				}
				return $default;
			}
		);

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $log_file ) {
				if ( 'occ_titles_log_file_path' === $tag ) {
					return $log_file;
				}
				if ( 'occ_titles_logging_enabled' === $tag ) {
					return false;
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
		Functions\when( 'wp_mkdir_p' )->alias(
			function ( $dir ) {
				if ( ! is_dir( $dir ) ) {
					mkdir( $dir, 0755, true );
				}
				return true;
			}
		);

		$logger = Occ_Titles_Logger::get_instance();

		$this->assertFalse( $logger->info( 'Should not log' ) );
		$this->assertFileDoesNotExist( $log_file );
	}
}
