<?php
/**
 * Plugin Name: Woo Shop Hacker
 * Plugin URI: https://codedcommerce.com/product/woo-shop-hacker/
 * Description: Connects WooCommerce with Shop Hacker for syncing your products.
 * Version: 1.0
 * Author: Coded Commerce, LLC
 * Author URI: https://codedcommerce.com
 * Developer: Sean Conklin
 * Developer URI: https://seanconklin.wordpress.com
 * Text Domain: woo-shop-hacker
 * Domain Path: /languages
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.0.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit If Accessed Directly
if( ! defined( 'ABSPATH' ) ) { exit; }

// Make Sure WooCommerce Is Activated
if(
	in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
) {

	// Include Files
	require_once( 'class.api.php' );
	require_once( 'class.builder.php' );
	require_once( 'class.order-actions.php' );
	require_once( 'class.settings.php' );

	// Plugin Hooks
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [ 'woo_shop_hacker_settings', 'plugin_action_links' ] );
	add_action( 'woo_shop_hacker_settings_before', [ 'woo_shop_hacker_builder', 'add_products' ] );
	add_action( 'woo_shop_hacker_settings_before', [ 'woo_shop_hacker_builder', 'print_search_form' ] );
	add_action( 'woocommerce_order_actions', [ 'woo_shop_hacker_order_actions', 'woocommerce_order_actions' ] );
	add_action( 'woocommerce_order_action_shop_hacker_send', [ 'woo_shop_hacker_order_actions', 'woocommerce_order_action' ] );

}
