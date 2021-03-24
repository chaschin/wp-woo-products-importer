<?php
/**
 * Plugin Name: WP Woo Products Importer
 *
 * @package WooProductsImporter
 */

namespace Woo_Products_Importer;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Woo_Products_Importer\UI;
use Woo_Products_Importer\Importer;

/**
 * WooProductImporter class
 */
class Base {

	/**
	 * Initialization
	 */
	public function __construct() {
		if ( isset( $_GET['import_woo_products'] ) ) {
			Importer::get_instance();
		}
	}
}
