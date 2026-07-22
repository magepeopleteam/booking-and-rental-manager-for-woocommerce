<?php
/**
 * Booking manager — façade that resolves the active booking service from the current mode.
 *
 * This is the single seam the booking flow (and future payment phase) goes through, so the
 * choice of WooCommerce vs Standalone lives in exactly one place
 * (RBFW_Function::booking_mode()).
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_Manager' ) ) {
	class RBFW_Booking_Manager {

		/** @var RBFW_Booking_Service_Interface|null */
		private static $service = null;

		/**
		 * Resolve the booking service for the active mode.
		 *
		 * @return RBFW_Booking_Service_Interface
		 */
		public static function service() {
			if ( self::$service instanceof RBFW_Booking_Service_Interface ) {
				return self::$service;
			}

			if ( RBFW_Function::use_wc() ) {
				self::$service = new RBFW_Woo_Booking_Service();
			} else {
				self::$service = new RBFW_Standalone_Booking_Service();
			}

			return apply_filters( 'rbfw_booking_service', self::$service );
		}

		/** Convenience wrapper around the active service's create_booking(). */
		public static function create_booking( $item_id, $payload ) {
			return self::service()->create_booking( $item_id, $payload );
		}

		public static function is_standalone() {
			return self::service()->get_mode() === 'standalone';
		}
	}
}
