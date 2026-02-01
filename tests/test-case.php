<?php
/**
 * Base test case for plugin tests.
 *
 * @package Occ_Titles
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * Base test case with Brain Monkey setup.
 *
 * @since 1.1.0
 */
abstract class Occ_Titles_Test_Case extends TestCase {

	/**
	 * Set up Brain Monkey for each test.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Tear down Brain Monkey after each test.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
