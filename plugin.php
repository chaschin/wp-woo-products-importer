<?php
/**
 * Plugin Name: WP Woo Products Importer
 * Plugin URI:
 * Description:
 * Version: 1.0.0
 * Author: Alexey Chaschin
 * Author URI: https://github.com/chaschin
 * Text Domain: woo_products_importer
 *
 * @package WooProductsImporter
 */

if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'WOO_PRODUCT_IMPORTER__PLUGIN_VERSION', '1.0.0' );
define( 'WOO_PRODUCT_IMPORTER__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOO_PRODUCT_IMPORTER__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once( ABSPATH . 'wp-config.php' );
require_once( ABSPATH . 'wp-includes/wp-db.php' );
require_once( ABSPATH . 'wp-includes/class-wp-query.php' );
require_once( ABSPATH . 'wp-admin/includes/taxonomy.php' );

require_once( WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'src/autoload.php' );
require_once( WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'vendor/autoload.php' );

add_action(
	'plugins_loaded',
	function() {
		Woo_Products_Importer::get_instance();
	}
);
