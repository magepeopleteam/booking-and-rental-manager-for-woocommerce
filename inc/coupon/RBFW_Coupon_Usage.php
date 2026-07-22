<?php
/**
 * Coupon usage tracking — counters + idempotent redemption recording.
 *
 * DESIGN: every usage counter lives on the COUPON post as meta, incremented atomically at
 * the DB level. This is deliberately independent of where the order/booking is stored, so it
 * is correct under WooCommerce HPOS (order meta lives in a custom table that WP_Query cannot
 * scan) and in Standalone mode alike:
 *
 *   rbfw_usage_count                 total redemptions        (cheap total-cap gate)
 *   rbfw_usage_user_<user_id>        redemptions by that user (per-user cap)
 *   rbfw_usage_email_<md5(email)>    redemptions by a guest   (per-user cap, logged-out)
 *   rbfw_usage_day_<Ymd GMT>         redemptions that day     (per-day cap)
 *   rbfw_usage_amount_total          aggregate money discounted (reporting, best-effort)
 *
 * Idempotency: recording is guarded by a `rbfw_coupon_recorded` flag written on the
 * order/booking object. WooCommerce fires its paid hooks more than once, so record() no-ops
 * if the flag is already set — the coupon counters are never double-incremented.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_Usage' ) ) {
	class RBFW_Coupon_Usage {

		/* -------------------------------------------------------------------------
		 * Recording
		 * ---------------------------------------------------------------------- */

		/**
		 * Record one redemption of a coupon against an order/booking. Idempotent.
		 *
		 * @param int    $coupon_id  rbfw_coupon post id.
		 * @param array  $object     [ 'type' => 'order'|'booking', 'id' => int ].
		 * @param int    $user_id    Redeeming user (0 for guest).
		 * @param string $email      Redeeming email (used when user_id is 0).
		 * @param float  $amount     Discount amount granted.
		 * @return bool  True if newly recorded, false if it was already recorded (or invalid).
		 */
		public static function record( $coupon_id, $object, $user_id = 0, $email = '', $amount = 0.0 ) {
			$coupon_id = absint( $coupon_id );
			$object_id = isset( $object['id'] ) ? absint( $object['id'] ) : 0;
			$type      = isset( $object['type'] ) ? $object['type'] : '';

			if ( ! $coupon_id || ! $object_id || ! in_array( $type, array( 'order', 'booking' ), true ) ) {
				return false;
			}

			// Idempotency guard — do not double count on hook re-fires. Keyed per COUPON so that
			// several stacked coupons on the same order each record exactly once.
			$guard = 'rbfw_coupon_recorded_' . $coupon_id;
			if ( self::object_meta_get( $type, $object_id, $guard ) ) {
				return false;
			}
			self::object_meta_set( $type, $object_id, $guard, '1' );

			$user_id = absint( $user_id );
			$email   = sanitize_email( $email );
			$amount  = (float) $amount;

			self::increment_meta( $coupon_id, 'rbfw_usage_count' );
			self::increment_meta( $coupon_id, self::user_key( $user_id, $email ) );
			self::increment_meta( $coupon_id, self::day_key( self::today_gmt() ) );

			if ( $amount > 0 ) {
				// Reporting aggregate — best-effort (not money-authoritative), so a plain RMW is fine.
				$current = (float) get_post_meta( $coupon_id, 'rbfw_usage_amount_total', true );
				update_post_meta( $coupon_id, 'rbfw_usage_amount_total', round( $current + $amount, wc_get_price_decimals() ) );
			}

			/**
			 * Fires after a coupon redemption is recorded.
			 *
			 * @param int   $coupon_id rbfw_coupon id.
			 * @param array $object    [type,id] of the order/booking.
			 * @param float $amount    Discount granted.
			 */
			do_action( 'rbfw_coupon_recorded', $coupon_id, $object, $amount );

			return true;
		}

		/* -------------------------------------------------------------------------
		 * Counting (all O(1) reads off the coupon post)
		 * ---------------------------------------------------------------------- */

		public static function count_total( $coupon_id ) {
			return (int) get_post_meta( absint( $coupon_id ), 'rbfw_usage_count', true );
		}

		public static function count_for_user( $coupon_id, $user_id, $email = '' ) {
			return (int) get_post_meta( absint( $coupon_id ), self::user_key( absint( $user_id ), $email ), true );
		}

		public static function count_for_day( $coupon_id, $date_ymd = '' ) {
			$date_ymd = $date_ymd ? $date_ymd : self::today_gmt();
			return (int) get_post_meta( absint( $coupon_id ), self::day_key( $date_ymd ), true );
		}

		/* -------------------------------------------------------------------------
		 * Keys
		 * ---------------------------------------------------------------------- */

		protected static function user_key( $user_id, $email ) {
			if ( $user_id > 0 ) {
				return 'rbfw_usage_user_' . $user_id;
			}
			$email = strtolower( sanitize_email( $email ) );
			return 'rbfw_usage_email_' . md5( $email );
		}

		protected static function day_key( $date_ymd ) {
			return 'rbfw_usage_day_' . preg_replace( '/[^0-9]/', '', $date_ymd );
		}

		protected static function today_gmt() {
			return gmdate( 'Ymd' );
		}

		/* -------------------------------------------------------------------------
		 * Atomic-ish counter increment on a coupon post meta row
		 * ---------------------------------------------------------------------- */

		protected static function increment_meta( $post_id, $key ) {
			global $wpdb;
			$post_id = absint( $post_id );

			// Atomic increment when the row already exists (avoids lost updates on concurrent
			// checkouts). meta_value is stored as text; MySQL casts it for the arithmetic.
			$updated = $wpdb->query( $wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
				$post_id,
				$key
			) );

			if ( ! $updated ) {
				// Row did not exist yet — create it. unique=true guards a concurrent create.
				add_post_meta( $post_id, $key, 1, true );
			}

			wp_cache_delete( $post_id, 'post_meta' );
		}

		/* -------------------------------------------------------------------------
		 * Object meta helpers (HPOS-safe for orders)
		 * ---------------------------------------------------------------------- */

		protected static function object_meta_get( $type, $object_id, $key ) {
			if ( 'order' === $type && function_exists( 'wc_get_order' ) ) {
				$order = wc_get_order( $object_id );
				return $order ? $order->get_meta( $key ) : '';
			}
			return get_post_meta( $object_id, $key, true );
		}

		protected static function object_meta_set( $type, $object_id, $key, $value ) {
			if ( 'order' === $type && function_exists( 'wc_get_order' ) ) {
				$order = wc_get_order( $object_id );
				if ( $order ) {
					$order->update_meta_data( $key, $value );
					$order->save();
				}
				return;
			}
			update_post_meta( $object_id, $key, $value );
		}
	}
}
