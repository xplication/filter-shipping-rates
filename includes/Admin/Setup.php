<?php

namespace FilterShippingRates\Admin;

/**
 * FilterShippingRates Setup Class for admin
 */
class Setup {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * Load all necessary dependencies.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		if ( ! method_exists( 'Automattic\WooCommerce\Admin\PageController', 'is_admin_or_embed_page' ) ||
		! \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page()
		) {
			return;
		}

		wp_enqueue_script( 'wp-api' );

		wp_localize_script( 'wp-api', 'wpApiSettings', array(
			'root' => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );

		$script_path       = '/build/index.js';
		$script_asset_path = dirname( MAIN_PLUGIN_FILE ) . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
		? require $script_asset_path
		: array(
			'dependencies' => array(),
			'version'      => filemtime( $script_path ),
		);
		$script_url        = plugins_url( $script_path, MAIN_PLUGIN_FILE );

		wp_register_script(
			'filter-shipping-rates',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_register_style(
			'filter-shipping-rates',
			plugins_url( '/build/index.css', MAIN_PLUGIN_FILE ),
			// Add any dependencies styles may have, such as wp-components.
			array(),
			filemtime( dirname( MAIN_PLUGIN_FILE ) . '/build/index.css' )
		);

		wp_enqueue_script( 'filter-shipping-rates' );
		wp_enqueue_style( 'filter-shipping-rates' );
	}

	/**
	 * Register page in wc-admin.
	 *
	 * @since 1.0.0
	 */
	public function register_page() {

		if ( ! function_exists( 'wc_admin_register_page' ) ) {
			return;
		}

		wc_admin_register_page(
			array(
				'id'     => 'filter_shipping_rates-example-page',
				'title'  => __( 'Setări plată ramburs', 'filter_shipping_rates' ),
				'parent' => 'woocommerce',
				'path'   => '/filter-shipping-rates',
			)
		);
	}

 	function register_rest_routes() {
		register_rest_route( 'filter-shipping-rates/v1', '/add-option', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_option_value' ),
		) );
	}

	function get_option_value( ) {
		return 'Hello World!';
	}
}
