<?php
/**
 * Plugin Name: WP Woo Products Importer
 *
 * @package WooProductsImporter
 */

namespace Woo_Products_Importer\Traits;

/**
 * Trait Singleton
 */
trait Singleton {
	/**
	 * Class instance
	 *
	 * @var [type]
	 */
	public static $instance = null;

	/**
	 * Get Instance
	 *
	 * @return self
	 */
	public static function get_instance() {
		$class = __CLASS__;
		self::$instance = is_null( self::$instance ) ? new $class() : self::$instance;

		return self::$instance;
	}

	/**
	 * Construct
	 */
	private function __construct() {
	}

	/**
	 * Clone
	 *
	 * @return void
	 */
	public function __clone() {
	}

	/**
	 * Wake Up
	 */
	public function __wakeup() {
	}
}
