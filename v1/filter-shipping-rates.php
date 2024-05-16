<?php
/**
 * Plugin Name: Filter Shipping Rates
 * Version: 0.1.0
 * Author: Xplication by Iftodi Petru
 * Author URI: https://xplication.com
 * Text Domain: filter-shipping-rates
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package extension
 */

use FilterShippingRates\Admin\Setup;
use FilterShippingRates\Rest\Api;
use FilterShippingRates\Public\Setup as PublicSetup;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MAIN_PLUGIN_FILE' ) ) {
	define( 'MAIN_PLUGIN_FILE', __FILE__ );
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload_packages.php';

// phpcs:disable WordPress.Files.FileName

/**
 * WooCommerce fallback notice.
 *
 * @since 0.1.0
 */
function filter_shipping_rates_missing_wc_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Filter Shipping Rates requires WooCommerce to be installed and active. You can download %s here.', 'filter_shipping_rates' ), '<a href="https://woo.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

register_activation_hook( __FILE__, 'filter_shipping_rates_activate' );

/**
 * Activation hook.
 *
 * @since 0.1.0
 */
function filter_shipping_rates_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'filter_shipping_rates_missing_wc_notice' );
		return;
	}
}

if ( ! class_exists( 'filter_shipping_rates' ) ) :
	/**
	 * The filter_shipping_rates class.
	 */
	class filter_shipping_rates {
		/**
		 * This class instance.
		 *
		 * @var \filter_shipping_rates single instance of this class.
		 */
		private static $instance;

		/**
		 * Constructor.
		 */
		public function __construct() {

			new Api();

			if ( is_admin() ) {
				new Setup();
			} else {
				new PublicSetup();
			}


		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'filter_shipping_rates' ), $this->version );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'filter_shipping_rates' ), $this->version );
		}

		/**
		 * Gets the main instance.
		 *
		 * Ensures only one instance can be loaded.
		 *
		 * @return \filter_shipping_rates
		 */
		public static function instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;

add_action( 'plugins_loaded', 'filter_shipping_rates_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function filter_shipping_rates_init() {
	load_plugin_textdomain( 'filter_shipping_rates', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'filter_shipping_rates_missing_wc_notice' );
		return;
	}

	filter_shipping_rates::instance();

}
