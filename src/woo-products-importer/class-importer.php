<?php
/**
 * Plugin Name: WP Woo Products Importer
 *
 * @package WooProductsImporter
 */

namespace Woo_Products_Importer;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use WC_Product;

use Woo_Products_Importer\Base;
use Woo_Products_Importer\Traits\Singleton;


/**
 * Importer class
 */
class Importer {

	use Singleton;

	/**
	 * Data field specification
	 *
	 * @var array
	 */
	private $data_fields = array(
		'A' => 'sku',
		'B' => 'name',
		'C' => 'description',
		'D' => 'SEO Title',
		'E' => 'Meta Description',
		'F' => 'qty',
		'G' => 'Online Price',
		'H' => 'length',
		'I' => 'width',
		'J' => 'height',
		'K' => 'weight',
		'L' => 'main_image',
		'M' => 'additional_images',
		'N' => 'Shipping Operation',
		'O' => 'UPC',
		'P' => 'Brand',
		'Q' => 'item-group',
		'R' => 'Category',
		'S' => 'Supplier',
	);

	/**
	 * Initialization
	 */
	private function __construct() {
		// add_action( 'wp_loaded', array( $this, 'import_products' ) );
		add_action( 'wp_loaded', array( $this, 'import_products2' ) );
	}

	/**
	 * Import categories
	 *
	 * @return void
	 */
	public function import_categories() {
		$input_file_name = WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'samples/categories.xls';
		$spreadsheet     = IOFactory::load( $input_file_name );
		$sheet           = $spreadsheet->getActiveSheet();
		$row_count       = $sheet->getHighestDataRow( 'A' );
		$i               = 1;
		$sheet_data      = $sheet->toArray( null, true, true, true );
		$not_found       = array();
		$updated         = array();
		foreach ( $sheet_data as $row ) {
			if ( $i > 1 ) {
				if ( strlen( intval( $row['A'] ) ) === strlen( $row['A'] ) ) {
					set_time_limit( 5 );
					$category = get_term( intval( $row['A'] ), 'product_cat', ARRAY_A );
					if ( is_array( $category ) ) {
						$updated[] = $category['term_id'];
						$args = array(
							'cat_ID'               => $category['term_id'],
							'taxonomy'             => 'product_cat',
							'cat_name'             => $row['B'],
							'category_description' => $row['D'],
						);
						wp_insert_category( $args );
						update_term_meta( $category['term_id'], 'SKU', $row['C'] );
						update_term_meta( $category['term_id'], 'Supplier', $row['E'] );
					} else {
						$category = $this->get_category_by_name( $row['B'] );
						if ( is_array( $category ) ) {
							$updated[] = $category['term_id'];
							$args = array(
								'cat_ID'               => $category['term_id'],
								'taxonomy'             => 'product_cat',
								'cat_name'             => $row['B'],
								'category_description' => $row['D'],
							);
							wp_insert_category( $args );
							update_term_meta( $category['term_id'], 'SKU', $row['C'] );
							update_term_meta( $category['term_id'], 'Supplier', $row['E'] );
						} else {
							$not_found[] = $row['A'];
							echo '<br/>' . $row['B'];
						}
					}
				}
			}
			$i ++;
			if ( $i > $row_count ) {
				break;
			}
		}
		echo implode( ', ', $not_found );
		echo '<br/>';
		echo implode( ', ', $updated );
		die();
	}

	/**
	 * Import products
	 *
	 * @return void
	 */
	public function import_products2() {
		$input_file_name = WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'samples/categories.xls';
		$spreadsheet = IOFactory::load( $input_file_name );
		$sheet = $spreadsheet->getActiveSheet();
		$row_count = $sheet->getHighestDataRow( 'A' );
		$i = 1;
		$sheet_data = $sheet->toArray( null, true, true, true );
		foreach ( $sheet_data as $row ) {
			if ( $i > 1 ) {
				if ( strlen( intval( $row['A'] ) ) === strlen( $row['A'] ) ) {
					$data = array(
						'A' => $row['C'],
						'B' => $row['B'],
						'C' => $row['D'],
						'S' => $row['E'],
					);
					$product_id = $this->add_new_product( $data, intval( $row['A'] ) );
				}
			}
			$i ++;
			if ( $i > $row_count ) {
				break;
			}
		}
	}

	/**
	 * Import products 2
	 *
	 * @return void
	 */
	public function import_products() {
		$input_file_name = WOO_PRODUCT_IMPORTER__PLUGIN_DIR . 'samples/products.xls';
		$spreadsheet = IOFactory::load( $input_file_name );
		$sheet = $spreadsheet->getActiveSheet();
		$row_count = $sheet->getHighestDataRow( 'A' );
		$i = 1;
		$sheet_data = $sheet->toArray( null, true, true, true );
		foreach ( $sheet_data as $row ) {
			if ( $i > 1 ) {
				$product_id = $this->add_new_product( $row );
				if ( 0 === $product_id ) {
					break;
				}
			}
			$i ++;
			if ( $i > $row_count ) {
				break;
			}
		}
	}

	/**
	 * Add new product
	 *
	 * @param array $product_data Product data.
	 * @param int   $product_id   Product ID.
	 *
	 * @return integer|WP_Error
	 */
	public function add_new_product( array $product_data, $product_id = 0 ): int {
		set_time_limit( 30 );
		if ( 0 === $product_id ) {
			$product_id = $this->get_product_by_sku( $product_data['A'] );
			if ( is_null( $product_id ) ) {
				$product_id = wp_insert_post(
					array(
						'post_title'   => $product_data['B'],
						'post_type'    => 'product',
						'post_status'  => 'publish',
						'post_content' => $product_data['C'],
					)
				);
			}
		}
		$product = wc_get_product( $product_id );
		if ( $product ) {
			foreach ( $this->data_fields as $key => $field_name ) {
				switch ( $key ) {
					case 'A':
						$product->set_sku( $product_data[ $key ] );
						break;
					case 'B':
						$product->set_name( $product_data[ $key ] );
						break;
					case 'C':
						$product->set_description( $product_data[ $key ] );
						break;
					case 'F':
						$product->set_stock_quantity( floatval( $product_data[ $key ] ) );
						$product->set_stock_status( 'instock' );
						break;
					case 'G':
						$product->set_price( $product_data[ $key ] );
						$product->set_regular_price( $product_data[ $key ] );
						break;
					case 'H':
						$product->set_length( floatval( $product_data[ $key ] ) );
						break;
					case 'I':
						$product->set_width( floatval( $product_data[ $key ] ) );
						break;
					case 'J':
						$product->set_height( floatval( $product_data[ $key ] ) );
						break;
					case 'K':
						$product->set_weight( floatval( $product_data[ $key ] ) );
						break;
					case 'L':
						if ( ! empty( $product_data[ $key ] ) ) {
							$image    = $this->upload_image_from_url( $product_data[ $key ] );
							$image_id = intval( $image['attachment_id'] );
							if ( $image_id > 0 ) {
								$product->set_image_id( intval( $image_id ) );
							}
						}
						break;
					case 'M':
						if ( ! empty( $product_data[ $key ] ) ) {
							$urls = explode( ',', $product_data[ $key ] );
							$images = array();
							foreach ( $urls as $url ) {
								$image = $this->upload_image_from_url( $url );
								$image_id = intval( $image['attachment_id'] );
								if ( $image_id > 0 ) {
									$images[] = $image_id;
								}
							}
							$product->set_gallery_image_ids( $images );
						}
						break;
					case 'N':
						if ( 'Free' === $product_data[ $key ] ) {
							$product->set_tax_status( 'none' );
						}
						break;
					case 'O':
					case 'P':
					case 'Q':
					case 'S':
						update_post_meta( $product_id, strtolower( $field_name ), $product_data[ $key ] );
						break;
					case 'R':
						if ( ! empty( $product_data[ $key ] ) ) {
							$categories_ids = array();
							$categories = explode( '>', $product_data[ $key ] );
							$parent_category = null;
							foreach ( $categories as $category_name ) {
								$category = $this->get_category_by_name( $category_name );
								if ( is_array( $category ) ) {
									$categories_ids[] = $category['term_id'];
									$parent_category  = $category['term_id'];
								} else {
									$args = array(
										'taxonomy'             => 'product_cat',
										'cat_name'             => $category_name,
										'category_parent'      => is_null( $parent_category ) ? '' : $parent_category,
									);
									$category_id = wp_insert_category( $args, true );
									if ( $category_id > 0 ) {
										$parent_category = $category_id;
									}
								}
							}
							if ( count( $categories_ids ) > 0 ) {
								$product->set_category_ids( $categories_ids );
							}
						}
						break;
					default:
						break;
				}
			}
			$product->save();

			return $product_id;
		}
		return 0;
	}

	/**
	 * Get WooCommerce product category by name
	 *
	 * @param string $cat_name Category name.
	 * @return null|array
	 */
	public function get_category_by_name( string $cat_name ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT t.*, tt.* FROM $wpdb->terms AS t
				INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
				WHERE tt.taxonomy = 'product_cat' AND t.name = %s",
				$cat_name
			),
			ARRAY_A
		);
		return $result;
	}

	/**
	 * Get WooCommerce product by SKU
	 *
	 * @param [type] $sku SKU.
	 *
	 * @return void|WC_Product
	 */
	public function get_product_by_sku( $sku ) {
		global $wpdb;
		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $sku ) );
		return $product_id;
	}

	/**
	 * Upload image from URL.
	 * Retrieves an image from a URL and uploads it using ld_handle_upload_from_path. See that function for more details.
	 *
	 * @param string    $image_url Image URL.
	 * @param int       $attach_to_post Attach to post or no.
	 * @param bool|true $add_to_media Add to media library.
	 *
	 * @return array|bool
	 */
	public function upload_image_from_url( $image_url, $attach_to_post = 0, $add_to_media = true ) {
		$remote_image = fopen( $image_url, 'r' );

		if ( ! $remote_image ) {
			return false;
		}

		$meta = stream_get_meta_data( $remote_image );

		$image_meta = false;
		$image_filetype = false;

		if ( $meta && ! empty( $meta['wrapper_data'] ) ) {
			foreach ( $meta['wrapper_data'] as $v ) {
				if ( preg_match( '/Content\-Type: ?((image)\/?(jpe?g|png|gif|bmp))/i', $v, $matches ) ) {
					$image_meta = $matches[1];
					$image_filetype = $matches[3];
				}
			}
		}

		// Resource did not provide an image.
		if ( ! $image_meta ) {
			return false;
		}

		$v = basename( $image_url );
		if ( $v && strlen( $v ) > 6 ) {
			// Create a filename from the URL's file, if it is long enough.
			$path = $v;
		} else {
			// Short filenames should use the path from the URL (not domain).
			$url_parsed = parse_url( $image_url );
			$path = isset( $url_parsed['path'] ) ? $url_parsed['path'] : $image_url;
		}

		$path = preg_replace( '/(https?:|\/|www\.|\.[a-zA-Z]{2,4}$)/i', '', $path );
		$filename_no_ext = sanitize_title_with_dashes( $path, '', 'save' );

		$extension = $image_filetype;
		$filename = $filename_no_ext . '.' . $extension;

		// Simulate uploading a file through $_FILES. We need a temporary file for this.
		$stream_content = stream_get_contents( $remote_image );

		$tmp = tmpfile();
		$tmp_path = stream_get_meta_data( $tmp )['uri'];
		fwrite( $tmp, $stream_content );
		fseek( $tmp, 0 ); // If we don't do this, WordPress thinks the file is empty.

		$tmpfile = array(
			'name'     => $filename,
			'type'     => 'image/' . $extension,
			'tmp_name' => $tmp_path,
			'error'    => UPLOAD_ERR_OK,
			'size'     => strlen( $stream_content ),
		);

		// Trick is_uploaded_file() by adding it to the superglobal.
		$_FILES[ basename( $tmp_path ) ] = $tmpfile;

		// For wp_handle_upload to work.
		include_once ABSPATH . 'wp-admin/includes/media.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/image.php';

		$result = wp_handle_upload(
			$tmpfile,
			array(
				'test_form' => false,
				'action'    => 'local',
			)
		);

		fclose( $tmp ); // Close tmp file.
		@unlink( $tmp_path ); // Delete the tmp file. Closing it should also delete it, so hide any warnings with @.
		unset( $_FILES[ basename( $tmp_path ) ] ); // Clean up our $_FILES mess.

		fclose( $remote_image ); // Close the opened image resource.

		$result['attachment_id'] = 0;

		if ( empty( $result['error'] ) && $add_to_media ) {
			$args = array(
				'post_title'     => $filename_no_ext,
				'post_content'   => '',
				'post_status'    => 'publish',
				'post_mime_type' => $result['type'],
			);

			$result['attachment_id'] = wp_insert_attachment( $args, $result['file'], $attach_to_post );

			$attach_data = wp_generate_attachment_metadata( $result['attachment_id'], $result['file'] );
			wp_update_attachment_metadata( $result['attachment_id'], $attach_data );

			if ( is_wp_error( $result['attachment_id'] ) ) {
				$result['attachment_id'] = 0;
			}
		}

		return $result;
	}
}
