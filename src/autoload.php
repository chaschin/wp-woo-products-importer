<?php
/**
 * Anonymous function that registers a custom autoloader
 *
 * @package WooProductsImporter
 */

$prefix   = 'class';
$base_dir = WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'src/';

spl_autoload_register(
	function ( $class ) use ( $prefix, $base_dir ) {
		$file = str_replace( '\\', '/', $class );
		$parts = explode( '/', $file );
		if ( in_array( 'Traits', $parts ) ) {
			$prefix = 'trait';
		} else {
			$prefix = 'class';
		}
		$parts[ count( $parts ) - 1 ] = $prefix . '-' . str_replace( '_', '', $parts[ count( $parts ) - 1 ] ) . '.php';
		$file = implode( '/', $parts );
		$file = str_replace( '_', '-', strtolower( $file ) );
		if ( file_exists( $base_dir . $file ) ) {
			require $base_dir . $file;
		}
	}
);
