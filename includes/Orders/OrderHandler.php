<?php

namespace FilterShippingRates\Orders;

class OrderHandler
{

	public function __construct()
	{

	}

	public function get_cancelled_orders( $user ) {
		$orders = [];

		if( empty($user['status']) )
			return $orders;

		if( $user['id'] ) {
			$orders = wc_get_orders( array(
				'customer' => $user['id'],
				'status' => $user['status'],
			));
		} else if( isset($user['email']) && isset($user['phone']) ) {
			if( $user['email'] == '' || $user['phone'] == '' )
				return false;

			$orders = wc_get_orders( array(
				'status' => $user['status'],
				'billing_email' => $user['email'],
			) );

			if( empty( $orders ) ) {
				$orders = wc_get_orders( array(
					'status' => $user['status'],
					'billing_phone' => $user['phone'],
				) );
			}
		}

		return $orders;
	}

}
