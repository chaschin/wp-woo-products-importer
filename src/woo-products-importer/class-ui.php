<?php
/**
 * Plugin Name: WP Woo Products Importer
 *
 * @package WooProductsImporter
 */

namespace Woo_Products_Importer;

use Woo_Products_Importer\Traits\Singleton;
use Woo_Products_Importer;

/**
 * UI class
 */
class UI {

	use Singleton;

	/**
	 * Initialization
	 */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	public function init() {
		wp_deregister_script( 'jquery' );
		wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, null, true );

		wp_register_script( 'woo_products_importer', WOO_PRODUCT_IMPORTER__PLUGIN_URL . 'js/admin.js', array( 'jquery' ), WOO_PRODUCT_IMPORTER__PLUGIN_VERSION, true );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'woo_products_importer' );

		wp_localize_script(
			'woo_products_importer',
			'woo_products_importer',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	public function menu() {
		$has_full_access = 'add_users';
		add_menu_page(
			'Woo Products Importer',
			'Woo Products Importer',
			$has_full_access,
			'woo-products-importer',
			array( $this, 'woo_products_importer' ),
			'dashicons-universal-access'
		);
	}

	public function woo_products_importer() {
		$template = Woo_Products_Importer::$twig->load( 'admin-interface.html' );
		$data = array(
			'title'                 => __( 'Woo Products Importer', 'woo_products_importer' ),
			'update_meta_btn_title' => __( 'Update Products Meta', 'woo_products_importer' ),
		);
		$content = $template->render( $data );
		echo $content;
	}
}
