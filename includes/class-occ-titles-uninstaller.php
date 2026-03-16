<?php
/**
 * Uninstall routines for OneClickContent - Titles.
 *
 * @link       https://oneclickcontent.com
 * @since      2.0.1
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin cleanup during uninstall.
 *
 * @since 2.0.1
 */
class Occ_Titles_Uninstaller {

	/**
	 * Remove plugin data from the current site or network sites.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public static function uninstall() {
		if ( is_multisite() ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);

			foreach ( $site_ids as $site_id ) {
				switch_to_blog( (int) $site_id );
				self::cleanup_site();
				restore_current_blog();
			}
		} else {
			self::cleanup_site();
		}

		self::cleanup_log_artifacts();
	}

	/**
	 * Remove plugin-owned settings and post metadata for the current site.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	private static function cleanup_site() {
		foreach ( self::get_option_keys() as $option_name ) {
			delete_option( $option_name );
		}

		delete_post_meta_by_key( '_occ_titles_results' );
	}

	/**
	 * Return plugin-owned option keys.
	 *
	 * @since 2.0.1
	 * @return array
	 */
	private static function get_option_keys() {
		return array(
			'occ_titles_ai_provider',
			'occ_titles_assistant_id',
			'occ_titles_google_api_key',
			'occ_titles_google_api_key_status',
			'occ_titles_google_model',
			'occ_titles_logging_enabled',
			'occ_titles_openai_api_key',
			'occ_titles_openai_api_key_status',
			'occ_titles_openai_model',
			'occ_titles_post_types',
			'occ_titles_voice_profile',
			'occ_titles_voice_samples',
		);
	}

	/**
	 * Remove log files and their protection files.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	private static function cleanup_log_artifacts() {
		$log_file = self::get_log_file_path();
		if ( '' === $log_file ) {
			return;
		}

		$filesystem = self::get_filesystem();
		if ( ! $filesystem ) {
			return;
		}

		$directory = dirname( $log_file );
		$files     = array(
			$log_file,
			trailingslashit( $directory ) . 'index.php',
			trailingslashit( $directory ) . '.htaccess',
			plugin_dir_path( __DIR__ ) . 'occ-titles.log',
			plugin_dir_path( __DIR__ ) . 'plugin-error.log',
		);

		foreach ( array_unique( $files ) as $file ) {
			if ( $filesystem->exists( $file ) ) {
				$filesystem->delete( $file );
			}
		}

		if ( $filesystem->is_dir( $directory ) ) {
			$filesystem->rmdir( $directory );
		}
	}

	/**
	 * Determine the log path used by the plugin.
	 *
	 * @since 2.0.1
	 * @return string
	 */
	private static function get_log_file_path() {
		$default = plugin_dir_path( __DIR__ ) . 'occ-titles.log';

		if ( function_exists( 'wp_upload_dir' ) ) {
			$upload_dir = wp_upload_dir( null, false );
			if ( ! empty( $upload_dir['basedir'] ) ) {
				$default = trailingslashit( $upload_dir['basedir'] ) . 'occ-titles-logs/occ-titles.log';
			}
		}

		return (string) apply_filters( 'occ_titles_log_file_path', $default );
	}

	/**
	 * Retrieve the filesystem instance used for cleanup.
	 *
	 * @since 2.0.1
	 * @return WP_Filesystem_Base|false
	 */
	private static function get_filesystem() {
		$filesystem = apply_filters( 'occ_titles_filesystem', null );
		if ( class_exists( 'WP_Filesystem_Base' ) && $filesystem instanceof WP_Filesystem_Base ) {
			return $filesystem;
		}

		global $wp_filesystem;

		if ( class_exists( 'WP_Filesystem_Base' ) && $wp_filesystem instanceof WP_Filesystem_Base ) {
			return $wp_filesystem;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
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
