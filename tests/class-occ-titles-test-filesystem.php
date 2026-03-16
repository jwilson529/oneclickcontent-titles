<?php
/**
 * In-memory filesystem for logger tests.
 *
 * @package Occ_Titles
 * @since 1.1.1
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-wp-filesystem-base.php';

/**
 * In-memory filesystem shim for logger tests.
 *
 * @since 1.1.1
 */
class Occ_Titles_Test_Filesystem extends WP_Filesystem_Base {

	/**
	 * Stored file contents.
	 *
	 * @since 1.1.1
	 * @var array
	 */
	private $files = array();

	/**
	 * Stored directories.
	 *
	 * @since 1.1.1
	 * @var array
	 */
	private $directories = array();

	/**
	 * Create the in-memory filesystem.
	 *
	 * @since 1.1.1
	 */
	public function __construct() {
		$this->directories['/'] = true;
	}

	/**
	 * Check if a path exists.
	 *
	 * @since 1.1.1
	 * @param string $path Path to check.
	 * @return bool
	 */
	public function exists( $path ) {
		$path = $this->normalize_path( $path );
		return isset( $this->files[ $path ] ) || isset( $this->directories[ $path ] );
	}

	/**
	 * Read file contents.
	 *
	 * @since 1.1.1
	 * @param string $path File path.
	 * @return string
	 */
	public function get_contents( $path ) {
		$path = $this->normalize_path( $path );
		return isset( $this->files[ $path ] ) ? (string) $this->files[ $path ] : '';
	}

	/**
	 * Write file contents.
	 *
	 * @since 1.1.1
	 * @param string $path    File path.
	 * @param string $content Content.
	 * @param int    $mode    Mode.
	 * @return bool
	 */
	public function put_contents( $path, $content, $mode = 0644 ) {
		$path      = $this->normalize_path( $path );
		$directory = $this->normalize_path( dirname( $path ) );
		$this->mkdir( $directory );
		$this->files[ $path ] = (string) $content;
		return true;
	}

	/**
	 * Append file contents.
	 *
	 * @since 2.0.1
	 * @param string $path    File path.
	 * @param string $content Content.
	 * @param int    $mode    Mode.
	 * @return bool
	 */
	public function append_contents( $path, $content, $mode = 0644 ) {
		$existing = $this->get_contents( $path );
		return $this->put_contents( $path, $existing . $content, $mode );
	}

	/**
	 * Delete a file.
	 *
	 * @since 2.0.1
	 * @param string $path      Path.
	 * @param bool   $recursive Optional recursive flag.
	 * @param string $type      Optional type.
	 * @return bool
	 */
	public function delete( $path, $recursive = false, $type = false ) {
		unset( $recursive, $type );
		$path = $this->normalize_path( $path );
		unset( $this->files[ $path ] );
		return true;
	}

	/**
	 * Remove a directory.
	 *
	 * @since 2.0.1
	 * @param string $path      Path.
	 * @param bool   $recursive Optional recursive flag.
	 * @return bool
	 */
	public function rmdir( $path, $recursive = false ) {
		unset( $recursive );
		$path = $this->normalize_path( $path );
		unset( $this->directories[ $path ] );
		return true;
	}

	/**
	 * Check if path is writable.
	 *
	 * @since 1.1.1
	 * @param string $path Path.
	 * @return bool
	 */
	public function is_writable( $path ) {
		$path = $this->normalize_path( $path );
		if ( isset( $this->files[ $path ] ) ) {
			return true;
		}

		$directory = $this->normalize_path( dirname( $path ) );
		return isset( $this->directories[ $directory ] );
	}

	/**
	 * Check if directory.
	 *
	 * @since 1.1.1
	 * @param string $path Path.
	 * @return bool
	 */
	public function is_dir( $path ) {
		$path = $this->normalize_path( $path );
		return isset( $this->directories[ $path ] );
	}

	/**
	 * Create directory.
	 *
	 * @since 1.1.1
	 * @param string $path Path.
	 * @return bool
	 */
	public function mkdir( $path ) {
		$path = $this->normalize_path( $path );
		if ( isset( $this->directories[ $path ] ) ) {
			return true;
		}

		$segments = array_filter( explode( '/', trim( $path, '/' ) ) );
		$cursor   = '';

		foreach ( $segments as $segment ) {
			$cursor                      .= '/' . $segment;
			$this->directories[ $cursor ] = true;
		}

		return true;
	}

	/**
	 * Normalize a filesystem path.
	 *
	 * @since 1.1.1
	 * @param string $path Path to normalize.
	 * @return string
	 */
	private function normalize_path( $path ) {
		$path = str_replace( '\\', '/', (string) $path );
		if ( '' === $path ) {
			return '/';
		}

		if ( '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}

		$path = rtrim( $path, '/' );
		return '' === $path ? '/' : $path;
	}
}
