<?php

// Plugin Order Actions Class
class woo_shop_hacker_order_actions {


	// Add Custom Order Action
	static function woocommerce_order_actions( $actions ) {
		global $theorder;

		// Conditions
		if (

			// Only If Paid
			! $theorder->is_paid()

			// Only If Not Previously Sent
			|| get_post_meta( $theorder->get_id(), '_shop_hacker_sent', true )
		) {
			return $actions;
		}

		// Only If Order Contains Shop Hacker Product
		$found_sh = false;
		$items = $theorder->get_items();
		foreach( $items as $item ) {
			$product = $theorder->get_product_from_item( $item );
			$sku = $product->get_sku();
			if( ! strstr( $sku, 'SH-' ) ) { continue; }
			$found_sh = true;
		}
		if( ! $found_sh ) { return $actions; }

		// Add Custom Order Action
		$actions['shop_hacker_send'] = __( 'Send to Shop Hacker fulfillment', 'woo-shop-hacker' );
		return $actions;
	}


	// Handle Order Send Request
	static function woocommerce_order_action( $order ) {

		// Get Matching Products From Order
		$items = $order->get_items();
		foreach( $items as $item ) {
			$product = $order->get_product_from_item( $item );
			$sku = $product->get_sku();
			if( ! strstr( $sku, 'SH-' ) ) { continue; }
			$product_id = str_replace( 'SH-', '', $sku );

			// Send To Shop Hacker
			$response = woo_shop_hacker_api::save_sale(
				$product_id,
				sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() ),
				$order->get_billing_email()
			);

			// Handle Bad Response
			if( ! intval( $response ) ) {
				$message =
					sprintf(
						__( 'Sorry, there was an error sending your order of %s to Shop Hacker for fulfillment. Please try again later or contact support for assistance.', 'woo-shop-hacker' ),
						$product->get_title()
					) . "\n\n" . $response;
				$order->add_order_note( $message );
				continue;
			}

			// Order Note
			$message = sprintf(
				__( 'Sent order for %s to Shop Hacker for fulfillment.', 'woo-shop-hacker' ),
				$product->get_title()
			);
			$order->add_order_note( $message );

			// Mark Order As Transmitted
			update_post_meta( $order->get_id(), '_shop_hacker_sent', 'yes' );
		}
	}


} // End Plugin Order Actions Class
