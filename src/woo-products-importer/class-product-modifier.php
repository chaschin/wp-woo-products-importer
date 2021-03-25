<?php
/**
 * Plugin Name: WP Woo Products Importer
 *
 * @package WooProductsImporter
 */

namespace Woo_Products_Importer;

use \WP_Post;

use Woo_Products_Importer\Traits\Singleton;

/**
 * Product Modifier class
 */
class Product_Modifier {

	use Singleton;

	/**
	 * Initialization
	 */
	private function __construct() {
		add_action( 'wp_ajax_update_all_products', array( $this, 'update_all_products' ) );
		add_action( 'wp_ajax_get_all_products', array( $this, 'get_all_products_ajax' ) );
		add_action( 'wp_ajax_update_product', array( $this, 'update_product_ajax' ) );
	}

	private function get_all_products() {
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
		);
		$products = get_posts( $args );
		return $products;
	}

	public function get_all_products_ajax() {
		global $wpdb;
		$products = $wpdb->get_results( "SELECT ID as id FROM $wpdb->posts WHERE post_type='product' AND post_status='publish'" );
		echo json_encode( $products );
		wp_die();
	}

	public function update_product_ajax() {
		$product_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$status = 0;
		$result = array();
		if ( $product_id > 0 ) {
			$post_object = get_post( $product_id );
			$updated_title = update_post_meta( $post_object->ID, '_yoast_wpseo_title', strip_tags( $post_object->post_title ) );
			$updated_desc  = update_post_meta( $post_object->ID, '_yoast_wpseo_metadesc', strip_tags( $post_object->post_content ) );
			if ( $updated_title && $updated_desc ) {
				$status = 1;
			}
			$result['title'] = $post_object->post_title;
		}
		$result['status'] = $status;
		echo json_encode( $result );
		wp_die();
	}

	/**
	 * Update Yoast Meta title and description
	 *
	 * @param WP_Post $post_object WP_Post object.
	 * @return bool
	 */
	public function update_product_meta_data( WP_Post $post_object ) : bool {
		$result = false;

		$updated_title = update_post_meta( $post_object->ID, '_yoast_wpseo_title', strip_tags( $post_object->post_title ) );
		$updated_desc  = update_post_meta( $post_object->ID, '_yoast_wpseo_metadesc', strip_tags( $post_object->post_content ) );

		if ( $updated_title && $updated_desc ) {
			$result = true;
		}
		return $result;
	}

	/**
	 * Update all products
	 *
	 * @return void
	 */
	public function update_all_products() {
		$products = $this->get_all_products();
		foreach ( $products as $k => $product ) {
			set_time_limit( 5 );
			$result = array(
				'k'     => $k,
				'title' => $product->post_title
			);
			// $result = $this->update_product_meta_data( $product );
			echo $result;

			echo json_encode( $d ) . PHP_EOL;

			// PUSH the data out by all FORCE POSSIBLE.
			ob_flush();
			flush();


			sleep( 1 );
			if ( $k > 4 ) {
				break;
			}
		}
		die();
	}

}