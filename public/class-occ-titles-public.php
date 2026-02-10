<?php
/**
 * Public-facing functionality for the plugin.
 *
 * @link       https://oneclickcontent.com
 * @since      1.1.1
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/public
 */

defined( 'ABSPATH' ) || exit;

/**
 * Public-facing functionality for the plugin.
 *
 * @since 1.1.1
 */
class Occ_Titles_Public {

	/**
	 * Plugin slug.
	 *
	 * @since 1.1.1
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @since 1.1.1
	 * @var string
	 */
	private $version;

	/**
	 * Constructor.
	 *
	 * @since 1.1.1
	 * @param string $plugin_name Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register public styles.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name . '-public',
			plugin_dir_url( __FILE__ ) . 'css/occ-titles-public.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register public scripts.
	 *
	 * @since 1.1.1
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-public',
			plugin_dir_url( __FILE__ ) . 'js/occ-titles-public.js',
			array(),
			$this->version,
			false
		);
	}
}
