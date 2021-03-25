<?php
/**
 * Plugin Name: WP Woo Products Importer
 *
 * @package WooProductsImporter
 */

use Twig;

use Woo_Products_Importer\Traits\Singleton;

use Woo_Products_Importer\UI;
use Woo_Products_Importer\Importer;
use Woo_Products_Importer\Product_Modifier;

/**
 * Woo_Products_Importer class
 */
class Woo_Products_Importer {

	use Singleton;

	/**
	 * Twig
	 *
	 * @var Twig\Environment|null
	 */
	public static $twig = null;

	/**
	 * Initialization
	 */
	private function __construct() {
		$this->init();
		UI::get_instance();
		Product_Modifier::get_instance();

		// if ( isset( $_GET['import_woo_products'] ) ) {
		// 	Importer::get_instance();
		// }
		// if ( isset( $_GET['mod_products_meta'] ) ) {
		// 	Product_Modifier::get_instance()->update_all_products();
		// }
	}

	private function init() {
		$loader     = new Twig\Loader\FilesystemLoader( WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'templates' );
		self::$twig = new Twig\Environment(
			$loader,
			array(
				'cache'       => WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'cache',
				'auto_reload' => true,
			)
		);

	}
}
