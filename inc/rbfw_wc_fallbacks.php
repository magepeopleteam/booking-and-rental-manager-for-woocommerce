<?php
/**
 * WooCommerce fallback stubs.
 *
 * The Rental plugin uses WooCommerce helpers (wc_price(), currency helpers, WC(),
 * cart/checkout URLs, etc.) pervasively across templates and engines. When the plugin
 * runs in Standalone mode (WooCommerce inactive), these would be undefined and cause
 * fatal errors. This file defines lightweight shims for the read-only/formatting helpers
 * so the existing code keeps rendering without scattering class_exists() checks everywhere.
 *
 * Mirrors mage-eventpress: woocommerce-event-press.php::mpwem_define_woocommerce_fallbacks().
 *
 * Order/product WRITE paths (creating WC products/orders) are NOT stubbed here — those are
 * guarded at the call site / loaded only in WooCommerce mode.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Hook on plugins_loaded priority 1 so WooCommerce (if active or being activated this
// request) has loaded first, preventing redeclaration conflicts.
add_action( 'plugins_loaded', 'rbfw_define_woocommerce_fallbacks', 1 );

if ( ! function_exists( 'rbfw_define_woocommerce_fallbacks' ) ) {
	function rbfw_define_woocommerce_fallbacks() {
		if ( class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Avoid declaring stubs during a WooCommerce activation request — WooCommerce's own
		// functions will load later in the same request and would collide with our shims.
		if ( rbfw_is_woocommerce_activating() ) {
			return;
		}

		if ( ! class_exists( 'RBFW_WC_Cart_Fallback' ) ) {
			class RBFW_WC_Cart_Fallback {
				public function get_cart() { return array(); }
				public function get_cart_contents_count() { return 0; }
				public function is_empty() { return true; }
				public function empty_cart() {}
			}
		}
		if ( ! class_exists( 'RBFW_WC_Customer_Fallback' ) ) {
			class RBFW_WC_Customer_Fallback {
				public function get_is_vat_exempt() { return false; }
			}
		}
		if ( ! class_exists( 'RBFW_WC_Fallback' ) ) {
			class RBFW_WC_Fallback {
				public $cart;
				public $customer;
				public $version = '0.0.0';
				public function __construct() {
					$this->cart     = new RBFW_WC_Cart_Fallback();
					$this->customer = new RBFW_WC_Customer_Fallback();
				}
			}
		}
		if ( ! function_exists( 'WC' ) ) {
			function WC() {
				static $instance = null;
				if ( null === $instance ) {
					$instance = new RBFW_WC_Fallback();
				}
				return $instance;
			}
		}
		if ( ! function_exists( 'wc_get_orders' ) ) {
			function wc_get_orders( $args = array() ) { return array(); }
		}
		if ( ! function_exists( 'wc_get_order' ) ) {
			function wc_get_order( $order_id = 0 ) { return false; }
		}
		if ( ! function_exists( 'wc_get_product' ) ) {
			function wc_get_product( $product_id = 0 ) { return false; }
		}
		if ( ! function_exists( 'wc_get_order_item_meta' ) ) {
			function wc_get_order_item_meta( $item_id = 0, $key = '', $single = true ) { return $single ? '' : array(); }
		}
		if ( ! function_exists( 'wc_add_order_item_meta' ) ) {
			function wc_add_order_item_meta( $item_id = 0, $key = '', $value = '', $unique = false ) { return false; }
		}
		if ( ! function_exists( 'wc_price' ) ) {
			function wc_price( $price, $args = array() ) {
				$amount   = (float) $price;
				$symbol   = get_woocommerce_currency_symbol();
				$position = rbfw_standalone_currency_setting( 'position', 'left' );
				$dec_sep  = wc_get_price_decimal_separator();
				$thou_sep = wc_get_price_thousand_separator();
				$decimals = (int) rbfw_standalone_currency_setting( 'decimals', 2 );
				$number   = number_format( $amount, $decimals, $dec_sep, $thou_sep );
				$symbol   = esc_html( $symbol );
				switch ( $position ) {
					case 'right':       return '<span class="woocommerce-Price-amount amount">' . $number . '<span class="woocommerce-Price-currencySymbol">' . $symbol . '</span></span>';
					case 'left_space':  return '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' . $symbol . '</span>&nbsp;' . $number . '</span>';
					case 'right_space': return '<span class="woocommerce-Price-amount amount">' . $number . '&nbsp;<span class="woocommerce-Price-currencySymbol">' . $symbol . '</span></span>';
					default:            return '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' . $symbol . '</span>' . $number . '</span>';
				}
			}
		}
		if ( ! function_exists( 'get_woocommerce_currency' ) ) {
			function get_woocommerce_currency() {
				return rbfw_standalone_currency_setting( 'code', 'USD' );
			}
		}
		if ( ! function_exists( 'get_woocommerce_currency_symbol' ) ) {
			function get_woocommerce_currency_symbol( $currency = '' ) {
				$code    = $currency ? $currency : rbfw_standalone_currency_setting( 'code', 'USD' );
				$symbols = array(
					'USD' => '&#36;',  'EUR' => '&euro;',  'GBP' => '&pound;', 'JPY' => '&yen;',
					'BDT' => '&#2547;', 'INR' => '&#8377;', 'AUD' => '&#36;',   'CAD' => '&#36;',
				);
				return isset( $symbols[ $code ] ) ? $symbols[ $code ] : $code;
			}
		}
		if ( ! function_exists( 'wc_prices_include_tax' ) ) {
			function wc_prices_include_tax() { return false; }
		}
		if ( ! function_exists( 'wc_get_price_thousand_separator' ) ) {
			function wc_get_price_thousand_separator() {
				return (string) rbfw_standalone_currency_setting( 'thousand_sep', ',' );
			}
		}
		if ( ! function_exists( 'wc_get_price_decimal_separator' ) ) {
			function wc_get_price_decimal_separator() {
				return (string) rbfw_standalone_currency_setting( 'decimal_sep', '.' );
			}
		}
		if ( ! function_exists( 'wc_get_price_decimals' ) ) {
			function wc_get_price_decimals() {
				return (int) rbfw_standalone_currency_setting( 'decimals', 2 );
			}
		}
		if ( ! function_exists( 'is_woocommerce' ) ) {
			function is_woocommerce() { return false; }
		}
		if ( ! function_exists( 'is_product' ) ) {
			function is_product() { return false; }
		}
		if ( ! function_exists( 'is_cart' ) ) {
			function is_cart() { return false; }
		}
		if ( ! function_exists( 'is_checkout' ) ) {
			function is_checkout() { return false; }
		}
		if ( ! function_exists( 'wc_get_cart_url' ) ) {
			function wc_get_cart_url() { return ''; }
		}
		if ( ! function_exists( 'wc_get_checkout_url' ) ) {
			function wc_get_checkout_url() { return ''; }
		}
		if ( ! function_exists( 'wc_add_notice' ) ) {
			function wc_add_notice( $message, $notice_type = 'success', $data = array() ) {}
		}
		if ( ! function_exists( 'wc_print_notices' ) ) {
			function wc_print_notices() {}
		}
	}
}

if ( ! function_exists( 'rbfw_standalone_currency_setting' ) ) {
	/**
	 * Reads the standalone currency configuration from rbfw_basic_payment_settings,
	 * reusing the existing rbfw_mps_currency* keys where present.
	 *
	 * @param string $which   One of: code, symbol, position, decimals, decimal_sep, thousand_sep.
	 * @param mixed  $default Fallback value.
	 * @return mixed
	 */
	function rbfw_standalone_currency_setting( $which, $default = '' ) {
		$opts = get_option( 'rbfw_basic_payment_settings', array() );
		if ( ! is_array( $opts ) ) {
			$opts = array();
		}
		switch ( $which ) {
			case 'code':
				return ! empty( $opts['rbfw_mps_currency'] ) ? $opts['rbfw_mps_currency'] : $default;
			case 'position':
				return ! empty( $opts['rbfw_mps_currency_position'] ) ? $opts['rbfw_mps_currency_position'] : $default;
			case 'decimals':
				return isset( $opts['rbfw_mps_currency_decimal_number'] ) && $opts['rbfw_mps_currency_decimal_number'] !== ''
					? (int) $opts['rbfw_mps_currency_decimal_number'] : $default;
			case 'decimal_sep':
				return ! empty( $opts['rbfw_mps_currency_decimal_sep'] ) ? $opts['rbfw_mps_currency_decimal_sep'] : $default;
			case 'thousand_sep':
				return ! empty( $opts['rbfw_mps_currency_thousand_sep'] ) ? $opts['rbfw_mps_currency_thousand_sep'] : $default;
			default:
				return $default;
		}
	}
}

if ( ! function_exists( 'rbfw_is_woocommerce_activating' ) ) {
	/**
	 * Detects whether WooCommerce is being activated in the current request, so the
	 * fallback shims are not declared (they would collide with WooCommerce's own
	 * functions loading later in the same request).
	 */
	function rbfw_is_woocommerce_activating() {
		// Single / bulk activation via the admin Plugins screen.
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'activate' ) {
			if ( isset( $_REQUEST['plugin'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ), 'woocommerce.php' ) !== false ) {
				return true;
			}
		}
		if ( isset( $_REQUEST['checked'] ) && is_array( $_REQUEST['checked'] ) ) {
			foreach ( wp_unslash( $_REQUEST['checked'] ) as $checked_plugin ) {
				if ( strpos( (string) $checked_plugin, 'woocommerce.php' ) !== false ) {
					return true;
				}
			}
		}
		// CLI activation, e.g. `wp plugin activate woocommerce`. We must require an
		// activation/install verb AND a woocommerce token — scanning argv for the bare word
		// "woocommerce" is too broad (it would match `wp eval`/`wp plugin list` commands that
		// merely mention WooCommerce and wrongly suppress the shims).
		if ( ( defined( 'WP_CLI' ) && WP_CLI ) && isset( $_SERVER['argv'] ) && is_array( $_SERVER['argv'] ) ) {
			$argv      = array_map( 'strval', $_SERVER['argv'] );
			$has_verb  = (bool) array_intersect( array( 'activate', 'install' ), $argv );
			$has_woo   = false;
			foreach ( $argv as $arg ) {
				if ( strpos( $arg, 'woocommerce' ) !== false ) {
					$has_woo = true;
					break;
				}
			}
			if ( $has_verb && $has_woo ) {
				return true;
			}
		}
		return false;
	}
}
