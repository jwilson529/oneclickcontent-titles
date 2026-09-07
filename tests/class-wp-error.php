<?php
/**
 * Minimal WordPress error fixture for isolated provider tests.
 *
 * @package Occ_Titles
 */

defined( 'ABSPATH' ) || exit;

/**
 * WordPress-compatible error data used by the isolated harness.
 */
class WP_Error {
	/**
	 * Error code.
	 *
	 * @var string
	 */
	private $code;
	/**
	 * Error message.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Store a code and safe message.
	 *
	 * @param string $code Error code.
	 * @param string $message Error message.
	 */
	public function __construct( $code, $message ) {
		$this->code    = $code;
		$this->message = $message;
	}

	/**
	 * Return the code.
	 *
	 * @return string
	 */
	public function get_error_code() {
		return $this->code;
	}

	/**
	 * Return the message.
	 *
	 * @return string
	 */
	public function get_error_message() {
		return $this->message;
	}
}
