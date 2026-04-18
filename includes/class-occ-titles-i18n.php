<?php
/**
 * Define the internationalization compatibility functionality.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Occ_Titles
 * @subpackage Occ_Titles/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define the internationalization functionality.
 *
 * WordPress.org-hosted plugins load translations automatically via
 * just-in-time loading, so this class remains as a lightweight
 * compatibility wrapper.
 *
 * @since      1.0.0
 * @package    Occ_Titles
 * @subpackage Occ_Titles/includes
 * @author     James Wilson <info@oneclickcontent.com>
 */
class Occ_Titles_I18n {


	/**
	 * Retained for compatibility with the plugin bootstrap.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function load_plugin_textdomain() {
		// WordPress.org loads this plugin's translations automatically.
	}
}
