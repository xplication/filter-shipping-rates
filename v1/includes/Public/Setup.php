<?php

namespace FilterShippingRates\Public;

use FilterShippingRates\Orders\OrderHandler;
use FilterShippingRates\Rest\Controllers\SettingsController;

/**
 * FilterShippingRates Setup Class for public
 */
class Setup
{
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public $settings = [];

	public function __construct() {
		$this->initialize_settings();

		if( isset( $this->settings->active ) && $this->settings->active ) {
			add_action( 'woocommerce_checkout_process', [$this, 'validate_cash_on_delivery_order'] );
			add_filter( 'woocommerce_available_payment_gateways', [$this, 'hide_cod_for_users_with_cancelled_orders'] );
		}
	}

	private function initialize_settings(): void
	{
		$controller = new SettingsController();
		$option = get_option( $controller->option_key );
		$this->settings = json_decode( $option );
	}


	public function validate_cash_on_delivery_order() {
		// Check if the payment method is "Cash on Delivery"
		if ( WC()->session->get( 'chosen_payment_method' ) === 'cod' ) {
			$user_id = get_current_user_id();
			$email = isset( $_POST['billing_email'] ) ? sanitize_email( $_POST['billing_email'] ) : '';
			$phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';
			$status = json_decode($this->settings->orderStatus);

			$user = array(
				'id' => $user_id,
				'phone' => $phone,
				'email' => $email,
				'status' => (count($status) > 0) ? $status : 'cancelled'
			);
			// Check if the user has cancelled orders
			$order_handler = new OrderHandler();
			$cancelled_orders = $order_handler->get_cancelled_orders($user);

			if ( ! empty( $cancelled_orders ) ) {
				wc_add_notice( __( 'Nu puteți utiliza metoda de plată Ramburs la livrare deoarece ați anulat comenzi.', 'filter-shipping-rates' ), 'error' );
				remove_action( 'woocommerce_checkout_order_processed', 'wc_reduce_stock_levels' );
			}
		}
	}

	function hide_cod_for_users_with_cancelled_orders( $gateways ) {
		// Verifică dacă utilizatorul are comenzi anulate
		$user['id'] = get_current_user_id();
		$status = [];

		if( isset($this->settings->orderStatus) )
			$status = json_decode($this->settings->orderStatus);

		$user['status'] = (count($status) > 0) ? $status : 'cancelled';

		$order_handler = new OrderHandler();
		$cancelled_orders = $order_handler->get_cancelled_orders($user);

		// Dacă utilizatorul are comenzi anulate, ascunde metoda de plată "Cash on Delivery"
		if ( ! empty( $cancelled_orders ) && isset( $gateways['cod'] ) ) {
			unset( $gateways['cod'] );
		}

		return $gateways;
	}
}
