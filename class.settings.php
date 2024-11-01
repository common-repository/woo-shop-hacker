<?php

// Plugin Settings Class
class woo_shop_hacker_settings {


	// Plugin Page Settings Link
	static function plugin_action_links( $links ) {
		return array_merge( [
			sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wc-settings&tab=settings_tab_shop_hacker' ),
				__( 'Settings', 'woo-shop-hacker' )
			)
		], $links );
	}


	// Bootstraps the class and hooks required actions & filters
	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_settings_tab_shop_hacker', __CLASS__ . '::settings_tab' );
		add_action( 'woocommerce_update_options_settings_tab_shop_hacker', __CLASS__ . '::update_settings' );
	}


	// Add a new settings tab to the WooCommerce settings tabs array
	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs['settings_tab_shop_hacker'] = __( 'Shop Hacker', 'woo-shop-hacker' );
		return $settings_tabs;
	}


	// Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function
	public static function settings_tab() {
		do_action( 'woo_shop_hacker_settings_before' );
		woocommerce_admin_fields( self::get_settings() );
		do_action( 'woo_shop_hacker_settings_after' );
	}


	// Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function
	public static function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}


	// Get all the settings for this plugin for @see woocommerce_admin_fields() function
	public static function get_settings() {
		$settings = [
			'section_title' => [
				'name' => __( 'Shop Hacker API Credentials', 'woo-shop-hacker' ),
				'type' => 'title',
				'id' => 'woo_shop_hacker_api'
			],
			'merchantid' => [
				'name' => __( 'Merchant ID', 'woo-shop-hacker' ),
				'type' => 'number',
				'id' => 'woo_shop_hacker_merchantid'
			],
			'apikey' => [
				'name' => __( 'API Key', 'woo-shop-hacker' ),
				'type' => 'text',
				'id' => 'woo_shop_hacker_apikey'
			],
			'apisecret' => [
				'name' => __( 'API Secret', 'woo-shop-hacker' ),
				'type' => 'text',
				'id' => 'woo_shop_hacker_apisecret'
			],
			'section_end' => [
				 'type' => 'sectionend',
				 'id' => 'woo_shop_hacker_end'
			]
		];
		return apply_filters( 'wc_settings_tab_shop_hacker_settings', $settings );
	}


} // End Plugin Settings Class


// Initialize Class
woo_shop_hacker_settings::init();
