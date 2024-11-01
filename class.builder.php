<?php

// Plugin Builder Class
class woo_shop_hacker_builder {


	// Test Plugin Settings
	static function is_configured() {
		$mid = get_option( 'woo_shop_hacker_merchantid' );
		$apikey = get_option( 'woo_shop_hacker_apikey' );
		$apisecret = get_option( 'woo_shop_hacker_apisecret' );
		return ( $mid && $apikey && $apisecret );
	}


	// Section Contents
	static function print_search_form() {

		// Ensure Plugin Is Configured
		if( ! self::is_configured() ) { return []; }

		// Get Search Results
		$query = isset( $_REQUEST['query'] ) ? sanitize_text_field( $_REQUEST['query'] ) : '';
		$page = isset( $_REQUEST['paginate'] ) ? intval( $_REQUEST['paginate'] ) : 1;
		if( $query ) {
			$response = woo_shop_hacker_api::get_search_results( $query, $page );
			$products = isset( $response->products ) ? $response->products : [];
		}

		// Get Meta Data
		$meta = isset( $response->meta ) ? $response->meta : '';
		$total_pages = isset( $meta->total_pages ) ? intval( $meta->total_pages ) : 0;
		$total_records = isset( $meta->total_records ) ? intval( $meta->total_records ) : 0;

		// Get Pagination
		$pagination = [ sprintf( 'Page: %d of %d', $page, $total_pages ) ];
		if( $page > 2 ) {
			$pagination[] = sprintf(
				'<a href="admin.php?page=wc-settings&tab=settings_tab_shop_hacker&paginate=%d&query=%s">%s</a>',
				1, $query, '&laquo; ' . __( 'First Page', 'woo-shop-hacker' )
			);
		}
		if( $page > 1 ) {
			$pagination[] = sprintf(
				'<a href="admin.php?page=wc-settings&tab=settings_tab_shop_hacker&paginate=%d&query=%s">%s</a>',
				$page - 1, $query, '&laquo; ' . __( 'Previous Page', 'woo-shop-hacker' )
			);
		}
		if( $page < $total_pages ) {
			$pagination[] = sprintf(
				'<a href="admin.php?page=wc-settings&tab=settings_tab_shop_hacker&paginate=%d&query=%s">%s</a>',
				$page + 1, $query, __( 'Next Page', 'woo-shop-hacker' ) . '  &raquo;'
			);
		}
		if( $page < $total_pages - 1 ) {
			$pagination[] = sprintf(
				'<a href="admin.php?page=wc-settings&tab=settings_tab_shop_hacker&paginate=%d&query=%s">%s</a>',
				$total_pages, $query, __( 'Last Page', 'woo-shop-hacker' ) .  ' &raquo;'
			);
		}

		// Search Form And Results
		include( 'view.inventory.html.php' );

		// Return Settings
		return [];	
	}


	// Product HTML
	static function print_product( $product ) {
		global $wpdb;

		// Get Description
		$description = '';
		if( $product->product_line_items ) {
			foreach( $product->product_line_items as $val ) {
				$description = $val->description;
			}
		}

		// Get Preexistence Status
		$product_match = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_sku' AND meta_value = 'SH-%d' LIMIT 1",
				$product->id
			)
		);
		$disabled = $product_match ? 'disabled="disabled"' : '';
		$title = $product_match ? __( 'This product exists in your store', 'woo-shop-hacker' ) : '';

		// Output Row
		echo sprintf(
			'
				<dt>
					<label>
						<input type="checkbox" name="add_product[]" value="%d" title="%s" %s /> <strong>%s</strong>
						<a href="#" onclick="jQuery( \'#pop%d\' ).toggle( \'slow\' ); return false;"
							class="dashicons dashicons-sort"></a><br />
					</label>
				</dt>
				<dd>
					<p>
						<small>%s: $%s – %s</small>
					</p>
					<div id="pop%d" class="shop_hacker_product_details">%s</div>
				</dd>
			',
			$product->id,
			$title,
			$disabled,
			trim( $product->name ),
			$product->id,
			__( 'Cost', 'woo-shop-hacker' ),
			number_format( $product->bundle_pricing, 2 ),
			$product->bundle_headline,
			$product->id,
			$description
		);
	}


	// Add Product Into Woo
	static function add_products() {

		// Verify Required Data
		$add_product = isset( $_REQUEST['add_product'] ) ? $_REQUEST['add_product'] : [];
		if( ! is_array( $add_product ) ) { return false; }

		// Loop Products Being Added
		foreach( $add_product as $product_id ) {

			// Get Product
			$response = woo_shop_hacker_api::get_product( $product_id );
			$bundle_headline = isset( $response->product->bundle_headline ) ? $response->product->bundle_headline : '';
			$bundle_pricing = isset( $response->product->bundle_pricing ) ? $response->product->bundle_pricing : '';
			$name = isset( $response->product->name ) ? $response->product->name : '';
			$product_line_items = isset( $response->product->product_line_items ) ? $response->product->product_line_items : [];
			$square_img_url = isset( $response->product->square_img_url ) ? $response->product->square_img_url : '';

			// Get Description
			$description = '';
			if( $response->product->product_line_items ) {
				foreach( $response->product->product_line_items as $val ) {
					$description = $val->description;
				}
			}

			// Add Into Woo
			$data = [
				'description' => $description,
				'images' => [ [ 'src' => $square_img_url, 'position' => 0 ] ],
				'name' => $name,
				'regular_price' => number_format( $bundle_pricing, 2 ),
				'short_description' => $bundle_headline,
				'sku' => 'SH-' . $product_id,
				'status' => 'draft',
				'type' => 'simple',
				'virtual' => true,
			];
			$request = new WP_REST_Request( 'POST' );
			$request->set_body_params( $data );
			$products_controller = new WC_REST_Products_Controller;
			$response = $products_controller->create_item( $request );

			// Handle Bad Response
			if( ! isset( $response->data['id'] ) ) {
				print_r( $response );
				return false;
			}

			// Success Message
			echo sprintf(
				'<div class="notice notice-success"><p><strong><a href="post.php?post=%d&action=edit">%s</a></strong> %s</p></div>',
				intval( $response->data['id'] ),
				$name,
				__( 'was added to your store in Draft status. Please review and publish.', 'woo-shop-hacker' )
			);
		}
	}


// End Plugin Builder Class
}
