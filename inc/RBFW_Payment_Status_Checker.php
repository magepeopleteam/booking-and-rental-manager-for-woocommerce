<?php
/**
 * Determines whether the booking system currently has at least one usable
 * payment method, across WooCommerce gateways and the optional Pro plugin's
 * custom gateways.
 *
 * Deliberately free of any WordPress hook registration so it can be
 * instantiated and unit tested in isolation. RBFW_Admin_Payment_Notice is the
 * only caller that wires it into `admin_notices`.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Payment_Status_Checker' ) ) {

	class RBFW_Payment_Status_Checker {

		/**
		 * Enabled WooCommerce payment gateways.
		 *
		 * Empty when WooCommerce is not active, when the "Enable WooCommerce Payment"
		 * toggle is off (WooCommerce no longer owns checkout, so its gateways are not
		 * a usable path), or when active with no gateway enabled.
		 *
		 * @return WC_Payment_Gateway[]
		 */
		public function get_enabled_woocommerce_gateways() {
			if ( ! $this->has_woocommerce() || ! $this->wc_payment_enabled() ) {
				return array();
			}

			if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
				return array();
			}

			return WC()->payment_gateways()->get_available_payment_gateways();
		}

		/**
		 * Enabled Pro custom payment methods.
		 *
		 * The free plugin never references Pro classes directly: when Pro is
		 * active it hooks the `rbfw_pro_enabled_payment_methods` filter and
		 * returns its own enabled gateways/offline method. Without Pro (or with
		 * an older Pro that doesn't hook the filter yet) this simply stays empty.
		 *
		 * @return array id => label of currently enabled Pro payment methods.
		 */
		public function get_enabled_pro_payment_methods() {
			if ( ! $this->has_pro() ) {
				return array();
			}

			return (array) apply_filters( 'rbfw_pro_enabled_payment_methods', array() );
		}

		/** Total number of payment methods available to customers right now. */
		public function count_available_payment_methods() {
			return count( $this->get_enabled_woocommerce_gateways() ) + count( $this->get_enabled_pro_payment_methods() );
		}

		/** Whether the booking system has at least one usable payment method. */
		public function has_available_payment_method() {
			return $this->count_available_payment_methods() > 0;
		}

		private function has_woocommerce() {
			return function_exists( 'rbfw_has_woocommerce' ) ? rbfw_has_woocommerce() : class_exists( 'WooCommerce' );
		}

		private function wc_payment_enabled() {
			return function_exists( 'rbfw_wc_payment_enabled' ) ? rbfw_wc_payment_enabled() : true;
		}

		private function has_pro() {
			return function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active();
		}
	}
}
