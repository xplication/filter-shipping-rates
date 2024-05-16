<?php
namespace FilterShippingRates\Rest\Controllers;

class SettingsController {
	public $option_key = 'filter-shipping-rates';

    /**
     * Get settings via API
     *
     * @return array
     */
    public function get( $request ){
		if( ! current_user_can('manage_options') )
			return false;

		$option_name = $request->get_param( 'option_name' );
		if( $option_name == 'all' ) {
			$option_value = get_option( $this->option_key );
		} else {
			$option_value = get_option( $this->option_key );
		}

		return rest_ensure_response( json_decode($option_value) );
    }

    /**
     * Update settings via API
     *
     * @param \WP_REST_Request $request
     * @return false|WP_REST_Response|string
	 */
	public function update( $request ) {
		if( ! current_user_can('manage_options') )
			return false;

		$params = $request->get_json_params();

		if ( ! isset( $params['options'] ) || ! is_array( $params['options'] ) ) {
			return new WP_Error( 'invalid', esc_html__( 'Invalid parameters!' ), array( 'status' => 400 ) );
		}
		$options = $params['options'];

		foreach ( $options as $option ) {
			$new_setting_key = isset( $option['option_key'] ) ? $option['option_key'] : '';
			$new_setting_value = isset( $option['option_value'] ) ? $option['option_value'] : '';

			if ( $new_setting_key == '' || $new_setting_value == '' ) {
				return new WP_Error( 'invalid', esc_html__( 'Invalid parameters!' ), array( 'status' => 400 ) );
			}

			$existing_option = get_option( $this->option_key, false );

			if ( $existing_option === false ) {
				// Initialize a new array with your setting if the option doesn't exist
				$new_option = array( $new_setting_key => $new_setting_value );
			} else {
				// Decode the existing option value from JSON
				$new_option = json_decode( $existing_option, true );

				// Add or update the new setting key and value
				$new_option[ $new_setting_key ] = $new_setting_value;
			}

			// Encode the updated option value back to JSON
			$new_option = json_encode( $new_option );

			// Update the option with the new value
			update_option( $this->option_key, $new_option );
		}

		return true;
	}
}
