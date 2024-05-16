<?php
namespace FilterShippingRates\Rest;

use FilterShippingRates\Rest\Controllers\SettingsController;

/**
 * Register all routes for REST API
 */
class Api {

    /**
     * API namespace
     *
     * @since 1.0.0
     *
     * @var string
     */
    protected $namespace  = 'filter-shipping-rates/v1';

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     */
    public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register all routes
     *
     * @since 1.0.0
     *
     * @uses "rest_api_init" action
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
     * @return void
     */
    public function register_routes() {
        $controller = new SettingsController();
        register_rest_route( $this->namespace, '/settings', [
			[
				'methods' => 'GET',
				'callback' => [ $controller, 'get' ],
				'permission_callback' => function() {
					return current_user_can('manage_options');
				}
			],
			[
				'methods' => 'POST',
				'callback' => [ $controller, 'update' ],
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				}
			],
		] );
    }
}
