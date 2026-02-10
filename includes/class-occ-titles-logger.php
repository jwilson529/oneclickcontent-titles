<?php
/**
 * Logger class for OneClickContent - Titles.
 *
 * @link       https://oneclickcontent.com
 * @since      1.1.0
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provides a reusable logging system for the plugin.
 *
 * @since 1.1.0
 */
class Occ_Titles_Logger {

	/**
	 * The singleton instance.
	 *
	 * @since 1.1.0
	 * @var Occ_Titles_Logger|null
	 */
	private static $instance = null;

	/**
	 * Log file path.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	private $log_file;

	/**
	 * Create the logger instance.
	 *
	 * @since 1.1.0
	 */
	private function __construct() {
		$this->log_file = $this->get_log_file_path();
	}

	/**
	 * Retrieve the singleton instance.
	 *
	 * @since 1.1.0
	 * @return Occ_Titles_Logger
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Log a debug message.
	 *
	 * @since 1.1.0
	 * @param string $message Log message.
	 * @param array  $context Optional context data.
	 * @return bool True when logged, false otherwise.
	 */
	public function debug( $message, $context = array() ) {
		return $this->log( 'debug', $message, $context );
	}

	/**
	 * Log an info message.
	 *
	 * @since 1.1.0
	 * @param string $message Log message.
	 * @param array  $context Optional context data.
	 * @return bool True when logged, false otherwise.
	 */
	public function info( $message, $context = array() ) {
		return $this->log( 'info', $message, $context );
	}

	/**
	 * Log a warning message.
	 *
	 * @since 1.1.0
	 * @param string $message Log message.
	 * @param array  $context Optional context data.
	 * @return bool True when logged, false otherwise.
	 */
	public function warning( $message, $context = array() ) {
		return $this->log( 'warning', $message, $context );
	}

	/**
	 * Log an error message.
	 *
	 * @since 1.1.0
	 * @param string $message Log message.
	 * @param array  $context Optional context data.
	 * @return bool True when logged, false otherwise.
	 */
	public function error( $message, $context = array() ) {
		return $this->log( 'error', $message, $context );
	}

	/**
	 * Write a log entry.
	 *
	 * @since 1.1.0
	 * @param string $level   Log level.
	 * @param string $message Log message.
	 * @param array  $context Optional context data.
	 * @return bool True when logged, false otherwise.
	 */
	public function log( $level, $message, $context = array() ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$level  = strtolower( (string) $level );
		$levels = array( 'debug', 'info', 'warning', 'error' );

		if ( empty( $message ) || ! in_array( $level, $levels, true ) ) {
			return false;
		}

		if ( ! $this->is_writable() ) {
			return false;
		}

		$timestamp = current_time( 'mysql' );
		$context   = is_array( $context ) ? $context : array( 'context' => $context );
		$context   = $this->sanitize_context( $context );
		$encoded   = ! empty( $context ) ? wp_json_encode( $context ) : '';
		$line      = sprintf(
			'[%1$s] %2$s: %3$s%4$s' . PHP_EOL,
			$timestamp,
			strtoupper( $level ),
			$message,
			$encoded ? ' | context: ' . $encoded : ''
		);

		$filesystem = $this->get_filesystem();
		if ( ! $filesystem ) {
			return false;
		}

		$existing = '';
		if ( $filesystem->exists( $this->log_file ) ) {
			$existing = (string) $filesystem->get_contents( $this->log_file );
		}

		$mode = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;

		return (bool) $filesystem->put_contents( $this->log_file, $existing . $line, $mode );
	}

	/**
	 * Check if logging is enabled.
	 *
	 * @since 1.1.0
	 * @return bool True if enabled.
	 */
	private function is_enabled() {
		$enabled = (bool) get_option( 'occ_titles_logging_enabled', 1 );

		/**
		 * Filter whether logging is enabled.
		 *
		 * @since 1.1.0
		 *
		 * @param bool $enabled Whether logging is enabled.
		 */
		return (bool) apply_filters( 'occ_titles_logging_enabled', $enabled );
	}

	/**
	 * Determine the log file path.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	private function get_log_file_path() {
		$default = plugin_dir_path( __DIR__ ) . 'occ-titles.log';

		if ( function_exists( 'wp_upload_dir' ) ) {
			$upload_dir = wp_upload_dir( null, false );
			if ( ! empty( $upload_dir['basedir'] ) ) {
				$default = trailingslashit( $upload_dir['basedir'] ) . 'occ-titles-logs/occ-titles.log';
			}
		}

		/**
		 * Filter the log file path.
		 *
		 * @since 1.1.0
		 *
		 * @param string $default Log file path.
		 */
		return (string) apply_filters( 'occ_titles_log_file_path', $default );
	}

	/**
	 * Check whether the log file path is writable.
	 *
	 * @since 1.1.0
	 * @return bool True when writable.
	 */
	private function is_writable() {
		$filesystem = $this->get_filesystem();
		if ( ! $filesystem ) {
			return false;
		}

		$directory = dirname( $this->log_file );

		if ( ! $filesystem->is_dir( $directory ) ) {
			$filesystem->mkdir( $directory );
		}

		$this->protect_log_directory( $filesystem, $directory );

		if ( $filesystem->exists( $this->log_file ) ) {
			return $filesystem->is_writable( $this->log_file );
		}

		return $filesystem->is_writable( $directory );
	}

	/**
	 * Protect the log directory from direct access.
	 *
	 * @since 1.1.1
	 * @param WP_Filesystem_Base $filesystem Filesystem instance.
	 * @param string             $directory  Directory path.
	 * @return void
	 */
	private function protect_log_directory( $filesystem, $directory ) {
		if ( ! $filesystem->is_dir( $directory ) ) {
			return;
		}

		$mode             = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
		$index_file       = trailingslashit( $directory ) . 'index.php';
		$htaccess_file    = trailingslashit( $directory ) . '.htaccess';
		$index_contents   = "<?php\n// Silence is golden.\n";
		$htaccess_content = "Deny from all\n";

		if ( ! $filesystem->exists( $index_file ) ) {
			$filesystem->put_contents( $index_file, $index_contents, $mode );
		}

		if ( ! $filesystem->exists( $htaccess_file ) ) {
			$filesystem->put_contents( $htaccess_file, $htaccess_content, $mode );
		}
	}

	/**
	 * Remove sensitive values from log context.
	 *
	 * @since 1.1.1
	 * @param array $context Log context.
	 * @return array
	 */
	private function sanitize_context( $context ) {
		$sensitive_terms = array( 'api_key', 'authorization', 'token', 'secret', 'password' );
		$sanitized       = array();

		foreach ( $context as $key => $value ) {
			$normalized_key = strtolower( (string) $key );
			$is_sensitive   = false;

			foreach ( $sensitive_terms as $term ) {
				if ( false !== strpos( $normalized_key, $term ) ) {
					$is_sensitive = true;
					break;
				}
			}

			if ( $is_sensitive ) {
				$sanitized[ $key ] = '[redacted]';
			} elseif ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize_context( $value );
			} else {
				$sanitized[ $key ] = $value;
			}
		}

		return $sanitized;
	}

	/**
	 * Retrieve the WP_Filesystem instance.
	 *
	 * @since 1.1.0
	 * @return WP_Filesystem_Base|false
	 */
	private function get_filesystem() {
		/**
		 * Filter the filesystem instance used by the logger.
		 *
		 * @since 1.1.1
		 *
		 * @param WP_Filesystem_Base|null $filesystem Filesystem instance.
		 */
		$filesystem = apply_filters( 'occ_titles_filesystem', null );
		if ( class_exists( 'WP_Filesystem_Base' ) && $filesystem instanceof WP_Filesystem_Base ) {
			return $filesystem;
		}

		global $wp_filesystem;

		if ( class_exists( 'WP_Filesystem_Base' ) && $wp_filesystem instanceof WP_Filesystem_Base ) {
			return $wp_filesystem;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			if ( defined( 'ABSPATH' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
		}

		if ( function_exists( 'WP_Filesystem' ) ) {
			WP_Filesystem();
		}

		if ( class_exists( 'WP_Filesystem_Base' ) && $wp_filesystem instanceof WP_Filesystem_Base ) {
			return $wp_filesystem;
		}

		return false;
	}
}
