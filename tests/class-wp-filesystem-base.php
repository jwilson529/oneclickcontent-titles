<?php
/**
 * Minimal filesystem base class for tests.
 *
 * @package Occ_Titles
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
	/**
	 * Minimal filesystem base for tests.
	 *
	 * @since 1.1.0
	 */
	abstract class WP_Filesystem_Base {
	}
}
