<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://oneclickcontent.com
 * @since             1.0.0
 * @package           Occ_Titles
 *
 * @wordpress-plugin
 * Plugin Name:       OneClickContent - Titles
 * Plugin URI:        https://oneclickcontent.com
 * Description:       Free AI title assistant for WordPress from the go-to bring-your-own-key AI plugin line. Use your own OpenAI or Google Gemini API key.
 * Version:           2.1.3
 * Author:            James Wilson
 * Author URI:        https://oneclickcontent.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       oneclickcontent-titles
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/jwilson529/oneclickcontent-titles
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

/**
 * Currently plugin version.
 * Use SemVer - https://semver.org
 */
define( 'OCC_TITLES_VERSION', '2.1.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-occ-titles-activator.php
 */
function occ_titles_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-occ-titles-activator.php';
	Occ_Titles_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-occ-titles-deactivator.php
 */
function occ_titles_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-occ-titles-deactivator.php';
	Occ_Titles_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'occ_titles_activate' );
register_deactivation_hook( __FILE__, 'occ_titles_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-occ-titles.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function occ_titles_run() {

	$plugin = new Occ_Titles();
	$plugin->run();
}
occ_titles_run();
