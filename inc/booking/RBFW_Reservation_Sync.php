<?php
/**
 * RBFW_Reservation_Sync — keeps the records a WooCommerce booking writes at checkout
 * in agreement for the whole life of the order.
 *
 * One WooCommerce booking is persisted in THREE places:
 *
 *   1. the real WooCommerce order            (source of truth)
 *   2. `rbfw_order`      — the mirror post    (what the admin Bookings list reads)
 *   3. `rbfw_order_meta` — the reservation    (what the Booking Calendar, the
 *                          attendee/report screens and the availability engine read,
 *                          plus an entry in the booked item's `rbfw_inventory` map)
 *
 * Deleting a booking updated (1) and (2) but could leave (3) untouched, so the calendar
 * kept rendering bookings the Bookings page had already dropped, and their inventory
 * entries kept holding stock no live order was using. The Pro calendar had a partial
 * sweep for this, but it only recognised an order sitting in the Trash — once
 * WordPress/WooCommerce purged the trash the order resolved to nothing at all, the
 * sweep's `'trash' === $order->get_status()` test never fired, and the reservation
 * became permanently un-sweepable.
 *
 * This class closes both halves:
 *
 *   - the cascade hooks, so trashing / untrashing / deleting an order or its mirror
 *     immediately propagates to the reservation records and the inventory map;
 *   - reconcile(), a bounded repair sweep for reservations orphaned before those hooks
 *     existed (or by any path that bypasses them) which — unlike the old sweep — also
 *     recognises an order that has been permanently deleted, and an order whose mirror
 *     is in the Trash.
 *
 * Retiring a record stashes the status it had, so untrashing an order or its mirror
 * puts the booking back exactly where it was rather than guessing.
 *
 * Everything is guarded on WooCommerce actually being active: with WooCommerce off we
 * cannot tell "order deleted" from "order store unavailable", so nothing is swept.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Reservation_Sync' ) ) {

	class RBFW_Reservation_Sync {

		/** Reservation record CPT (calendar / reports / availability source). */
		const CPT_RESERVATION = 'rbfw_order_meta';

		/** WooCommerce order mirror CPT (admin Bookings list source). */
		const CPT_MIRROR = 'rbfw_order';

		/** Status written onto records whose order is trashed or gone. */
		const STATUS_GONE = 'trash';

		/** Where a record's pre-retirement status is stashed, so untrash can restore it. */
		const META_PREV_STATUS = '_rbfw_status_before_trash';

		/** Transient guarding the reconcile sweep so it runs at most once per window. */
		const SWEEP_TRANSIENT = 'rbfw_reservation_sync_sweep';

		/** Reservation rows examined per sweep — bounded so large stores never time out. */
		const SWEEP_BATCH = 300;

		/** Re-entrancy guard: order ids currently being processed. */
		private static $in_cascade = array();

		public static function init() {
			// Mirror post lifecycle — covers the Pro Bookings page delete, the classic
			// post list, bulk actions, and anything else that trashes the mirror.
			add_action( 'trashed_post', array( __CLASS__, 'on_mirror_trashed' ) );
			add_action( 'before_delete_post', array( __CLASS__, 'on_mirror_trashed' ) );
			add_action( 'untrashed_post', array( __CLASS__, 'on_mirror_untrashed' ) );

			// WooCommerce order lifecycle (HPOS-aware hooks + legacy post storage).
			add_action( 'woocommerce_trash_order', array( __CLASS__, 'on_order_gone' ) );
			add_action( 'woocommerce_delete_order', array( __CLASS__, 'on_order_gone' ) );
			add_action( 'woocommerce_before_delete_order', array( __CLASS__, 'on_order_gone' ) );
			add_action( 'woocommerce_untrash_order', array( __CLASS__, 'on_order_restored' ) );
		}

		/* ================================================================== *
		 * Live cascade
		 * ================================================================== */

		/**
		 * An `rbfw_order` mirror was trashed or is about to be deleted: take its
		 * reservation records and inventory entries out of circulation too.
		 *
		 * Fires on every post trash/delete, so it bails cheaply on foreign post types.
		 *
		 * @param int $post_id
		 */
		public static function on_mirror_trashed( $post_id ) {
			$post_id = absint( $post_id );
			if ( ! $post_id || self::CPT_MIRROR !== get_post_type( $post_id ) ) {
				return;
			}
			$order_id = self::mirror_order_id( $post_id );
			if ( $order_id ) {
				self::retire( $order_id );
			}
		}

		/**
		 * An `rbfw_order` mirror came back out of the Trash: put its reservations back
		 * on the status they held before.
		 *
		 * @param int $post_id
		 */
		public static function on_mirror_untrashed( $post_id ) {
			$post_id = absint( $post_id );
			if ( ! $post_id || self::CPT_MIRROR !== get_post_type( $post_id ) ) {
				return;
			}
			$order_id = self::mirror_order_id( $post_id );
			if ( $order_id ) {
				self::restore( $order_id );
			}
		}

		/**
		 * The WooCommerce order itself was trashed or deleted.
		 *
		 * @param int $order_id
		 */
		public static function on_order_gone( $order_id ) {
			$order_id = absint( $order_id );
			if ( $order_id ) {
				self::retire( $order_id );
			}
		}

		/**
		 * The WooCommerce order was restored from the Trash.
		 *
		 * @param int $order_id
		 */
		public static function on_order_restored( $order_id ) {
			$order_id = absint( $order_id );
			if ( $order_id ) {
				self::restore( $order_id );
			}
		}

		/**
		 * Take every record tied to a WooCommerce order out of circulation: the
		 * reservation posts, the mirror post, and the order's entry in each booked
		 * item's `rbfw_inventory` map. The previous status is stashed first so
		 * restore() can put it back.
		 *
		 * @param int $order_id WooCommerce order id.
		 * @return int Number of reservation posts retired.
		 */
		public static function retire( $order_id ) {
			$order_id = absint( $order_id );
			if ( ! $order_id || isset( self::$in_cascade[ $order_id ] ) ) {
				return 0;
			}
			// before_delete_post and woocommerce_delete_order can both fire for the same
			// order in one request; do the work once.
			self::$in_cascade[ $order_id ] = true;

			$retired = 0;
			foreach ( array( self::CPT_RESERVATION, self::CPT_MIRROR ) as $cpt ) {
				foreach ( self::linked_posts( $cpt, $order_id ) as $pid ) {
					$current = (string) get_post_meta( $pid, 'rbfw_order_status', true );
					if ( self::STATUS_GONE === $current ) {
						continue;
					}
					if ( '' !== $current ) {
						update_post_meta( $pid, self::META_PREV_STATUS, $current );
					}
					update_post_meta( $pid, 'rbfw_order_status', self::STATUS_GONE );
					if ( self::CPT_RESERVATION === $cpt ) {
						$retired++;
					}
				}
			}
			self::sync_inventory( $order_id, self::STATUS_GONE );

			unset( self::$in_cascade[ $order_id ] );

			return $retired;
		}

		/**
		 * Undo retire(): put every record back on the status it held before, falling
		 * back to the live WooCommerce order's status.
		 *
		 * @param int $order_id
		 */
		public static function restore( $order_id ) {
			$order_id = absint( $order_id );
			if ( ! $order_id || isset( self::$in_cascade[ $order_id ] ) ) {
				return;
			}
			self::$in_cascade[ $order_id ] = true;

			$fallback = self::live_status( $order_id );

			foreach ( array( self::CPT_RESERVATION, self::CPT_MIRROR ) as $cpt ) {
				foreach ( self::linked_posts( $cpt, $order_id ) as $pid ) {
					if ( self::STATUS_GONE !== (string) get_post_meta( $pid, 'rbfw_order_status', true ) ) {
						continue; // never retired — leave it alone.
					}
					$prev = (string) get_post_meta( $pid, self::META_PREV_STATUS, true );
					update_post_meta( $pid, 'rbfw_order_status', $prev ? $prev : $fallback );
					delete_post_meta( $pid, self::META_PREV_STATUS );
				}
			}
			self::sync_inventory( $order_id, '' ); // '' = restore from the stashed value.

			unset( self::$in_cascade[ $order_id ] );
		}

		/**
		 * Point an order's entry in every item's `rbfw_inventory` map at $status, or —
		 * when $status is empty — back at the status stashed when it was retired.
		 *
		 * Deliberately does NOT go through rbfw_update_inventory(): that helper reads the
		 * order's line items to find the booked items, so it is a no-op exactly when it
		 * matters most, after the order has been deleted. This walks the item side
		 * instead, which survives the order's disappearance.
		 *
		 * @param int    $order_id
		 * @param string $status '' to restore the stashed status.
		 */
		private static function sync_inventory( $order_id, $status ) {
			$restoring = ( '' === $status );

			foreach ( self::items_with_inventory() as $item_id ) {
				$inventory = get_post_meta( $item_id, 'rbfw_inventory', true );
				if ( ! is_array( $inventory ) || ! isset( $inventory[ $order_id ] ) || ! is_array( $inventory[ $order_id ] ) ) {
					continue;
				}
				$entry   = $inventory[ $order_id ];
				$current = isset( $entry['rbfw_order_status'] ) ? (string) $entry['rbfw_order_status'] : '';

				if ( $restoring ) {
					if ( self::STATUS_GONE !== $current ) {
						continue;
					}
					$prev = isset( $entry[ self::META_PREV_STATUS ] ) ? (string) $entry[ self::META_PREV_STATUS ] : '';
					if ( '' === $prev ) {
						$prev = self::live_status( $order_id );
					}
					$entry['rbfw_order_status'] = $prev;
					unset( $entry[ self::META_PREV_STATUS ] );
				} else {
					if ( $current === $status ) {
						continue;
					}
					if ( '' !== $current ) {
						$entry[ self::META_PREV_STATUS ] = $current;
					}
					$entry['rbfw_order_status'] = $status;
				}

				$inventory[ $order_id ] = $entry;
				update_post_meta( $item_id, 'rbfw_inventory', $inventory );
			}
		}

		/* ================================================================== *
		 * Repair sweep
		 * ================================================================== */

		/**
		 * Bounded repair pass over reservation records that still look live.
		 *
		 * A reservation is retired when its WooCommerce order is trashed, has been
		 * permanently deleted, or when the order's `rbfw_order` mirror is in the Trash —
		 * i.e. whenever the admin Bookings list no longer shows the booking.
		 *
		 * @param bool $force Skip the once-per-window transient (used by manual repair).
		 * @return int Number of reservation records retired.
		 */
		public static function reconcile( $force = false ) {
			// With WooCommerce inactive an order cannot be resolved at all, so "missing"
			// would be indistinguishable from "deleted" and the sweep would wipe the
			// calendar. Do nothing instead.
			if ( ! self::wc_active() ) {
				return 0;
			}
			if ( ! $force && get_transient( self::SWEEP_TRANSIENT ) ) {
				return 0;
			}
			set_transient( self::SWEEP_TRANSIENT, 1, 5 * MINUTE_IN_SECONDS );

			$q = new WP_Query( array(
				'post_type'      => self::CPT_RESERVATION,
				'post_status'    => 'publish',
				'posts_per_page' => self::SWEEP_BATCH,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'rbfw_order_status',
						'value'   => self::excluded_statuses(),
						'compare' => 'NOT IN',
					),
				),
			) );

			$retired = 0;
			$seen    = array();
			foreach ( $q->posts as $pid ) {
				$order_id = self::reservation_order_id( $pid );
				if ( ! $order_id || isset( $seen[ $order_id ] ) ) {
					continue; // unlinked / native booking, or already handled this pass.
				}
				$seen[ $order_id ] = true;
				if ( self::order_is_gone( $order_id ) ) {
					$retired += self::retire( $order_id );
				}
			}

			return $retired;
		}

		/**
		 * Decide whether the booking behind an order id has ceased to exist, from the
		 * admin's point of view: the order is in the Trash, has been deleted outright,
		 * or its mirror post is trashed.
		 *
		 * Conservative by design — anything that still resolves to a live order with a
		 * live mirror, or to a non-trashed post, is left alone.
		 *
		 * @param int $order_id
		 * @return bool
		 */
		public static function order_is_gone( $order_id ) {
			$order_id = absint( $order_id );
			if ( ! $order_id ) {
				return false;
			}

			$order = wc_get_order( $order_id );
			if ( $order && is_a( $order, 'WC_Order' ) ) {
				if ( 'trash' === $order->get_status() ) {
					return true;
				}
				// Live order — but the admin may still have deleted the booking, which
				// trashes the mirror. Defer to the mirror in that case.
				return self::mirror_is_trashed( $order_id );
			}

			// No order object. Under HPOS a legacy post may still exist (or the id may
			// point at something else entirely) — only treat it as gone when nothing at
			// all is left, or what is left is itself trashed.
			$post = get_post( $order_id );
			if ( $post ) {
				return 'trash' === $post->post_status;
			}

			return true; // permanently deleted — the case the old sweep could not see.
		}

		/* ================================================================== *
		 * Helpers
		 * ================================================================== */

		/** Statuses the calendar / reports already treat as not-live. */
		private static function excluded_statuses() {
			$excluded = apply_filters( 'rbfw_calendar_excluded_statuses', array( 'cancelled', 'failed', 'refunded', 'trash' ) );
			return ( is_array( $excluded ) && $excluded ) ? $excluded : array( 'cancelled', 'failed', 'refunded', 'trash' );
		}

		/**
		 * Shim-proof WooCommerce detection — the free plugin defines wc_get_order()
		 * fallbacks, so function_exists() is not a reliable test here.
		 *
		 * @return bool
		 */
		private static function wc_active() {
			if ( function_exists( 'rbfw_has_woocommerce' ) ) {
				return rbfw_has_woocommerce();
			}
			return class_exists( 'WooCommerce' );
		}

		/**
		 * Posts of $cpt tied to a WooCommerce order. Both link meta keys are in use
		 * across the codebase, so match either.
		 *
		 * @param string $cpt
		 * @param int    $order_id
		 * @return int[]
		 */
		private static function linked_posts( $cpt, $order_id ) {
			$q = new WP_Query( array(
				'post_type'      => $cpt,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array( 'key' => 'rbfw_link_order_id', 'value' => $order_id ),
					array( 'key' => 'rbfw_order_id', 'value' => $order_id ),
				),
			) );
			return array_map( 'absint', $q->posts );
		}

		/**
		 * Rental items carrying an inventory map. The order's entry is looked up inside
		 * the (serialized) map itself — an order id cannot be matched in SQL reliably.
		 *
		 * @return int[]
		 */
		private static function items_with_inventory() {
			static $ids = null;
			if ( null === $ids ) {
				$q   = new WP_Query( array(
					'post_type'      => 'rbfw_item',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array( 'key' => 'rbfw_inventory', 'compare' => 'EXISTS' ),
					),
				) );
				$ids = array_map( 'absint', $q->posts );
			}
			return $ids;
		}

		/** WooCommerce order id behind a mirror post. */
		private static function mirror_order_id( $post_id ) {
			$id = (int) get_post_meta( $post_id, 'rbfw_order_id', true );
			if ( ! $id ) {
				$id = (int) get_post_meta( $post_id, 'rbfw_link_order_id', true );
			}
			return $id;
		}

		/** WooCommerce order id behind a reservation post. */
		private static function reservation_order_id( $post_id ) {
			$id = (int) get_post_meta( $post_id, 'rbfw_link_order_id', true );
			if ( ! $id ) {
				$id = (int) get_post_meta( $post_id, 'rbfw_order_id', true );
			}
			return $id;
		}

		/** True when the order has mirror posts and every one of them is trashed. */
		private static function mirror_is_trashed( $order_id ) {
			$mirrors = self::linked_posts( self::CPT_MIRROR, $order_id );
			if ( ! $mirrors ) {
				return false; // no mirror at all (legacy row) — not evidence of deletion.
			}
			foreach ( $mirrors as $pid ) {
				if ( 'trash' !== get_post_status( $pid ) ) {
					return false; // a live mirror means the booking still exists.
				}
			}
			return true;
		}

		/**
		 * Status to fall back on when no stashed value is available.
		 *
		 * @param int $order_id
		 * @return string
		 */
		private static function live_status( $order_id ) {
			if ( self::wc_active() ) {
				$order = wc_get_order( $order_id );
				if ( $order && is_a( $order, 'WC_Order' ) && 'trash' !== $order->get_status() ) {
					return $order->get_status();
				}
			}
			return 'pending';
		}
	}

	RBFW_Reservation_Sync::init();
}
