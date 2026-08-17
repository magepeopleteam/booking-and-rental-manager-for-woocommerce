<?php
	/**
	 * Custom (standalone) mode — no WooCommerce.
	 *
	 * The plugin runs happily without WooCommerce: bookings are taken by
	 * RBFW_Native_Checkout over the `rbfw_native_checkout` AJAX action, persisted
	 * by RBFW_Standalone_Booking_Service::create_booking(), and paid offline. In
	 * that mode none of the `woocommerce_*` hooks the mapper uses exist, so
	 * RBFW_GF_Mapper never fires and the Gravity answers would be silently lost.
	 *
	 * This class is the same bridge for that path. It hooks the AJAX action ahead
	 * of the plugin's own handler to refuse a booking whose required answers are
	 * missing, then writes the answers onto the booking post once it exists.
	 *
	 * The booking post is where standalone display reads from — the same
	 * `rbfw_regf_info` / `rbfw_regf_attendees` keys the WooCommerce path mirrors
	 * onto the booking (inc/rbfw_order_meta.php:373), so the bookings list, the
	 * booking editor, the calendar, the PDF and the emails all pick these up with
	 * no further code.
	 *
	 * Because payment is offline in this mode, create_booking() always runs during
	 * the submit request — there is no gateway redirect that could defer it — so
	 * `rbfw_native_booking_created` is a reliable single attach point.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'RBFW_GF_Standalone' ) ) {

		class RBFW_GF_Standalone {

			public function __construct() {
				// Priority 1: ahead of RBFW_Native_Checkout::process(), which is
				// registered at the default 10, so a missing required form stops
				// the booking before anything is written.
				add_action( 'wp_ajax_rbfw_native_checkout', array( $this, 'validate' ), 1 );
				add_action( 'wp_ajax_nopriv_rbfw_native_checkout', array( $this, 'validate' ), 1 );

				add_action( 'rbfw_native_booking_created', array( $this, 'persist' ), 10, 5 );
			}

			/**
			 * Refuse the booking when this rental requires its form and no
			 * verified entry was presented.
			 *
			 * Deliberately does NOT re-verify the nonce: the plugin's own handler
			 * does that at priority 10 and its message is the better one for a
			 * stale page. This only adds a check, never weakens one — an invalid
			 * nonce still fails immediately afterwards.
			 */
			public function validate(): void {
				$item_id = rbfw_gf_item_id_from_post();
				if ( $item_id <= 0 ) {
					return;
				}

				if ( ! rbfw_gf_answers_required( $item_id ) ) {
					return;
				}

				if ( null === rbfw_gf_verified_entry_from_post( $item_id ) ) {
					wp_send_json_error( array(
						'message' => esc_html__( 'Please complete the booking questions for this rental before continuing.', 'booking-and-rental-manager-for-woocommerce' ),
					) );
				}
			}

			/**
			 * Write the answers onto the freshly created booking.
			 *
			 * @param int    $booking_id Booking post id.
			 * @param int    $item_id    Rental item id.
			 * @param float  $total      Booking total.
			 * @param string $status     Booking status.
			 * @param array  $payload    The create_booking() payload.
			 */
			public function persist( $booking_id, $item_id, $total = 0, $status = '', $payload = array() ): void {
				$booking_id = (int) $booking_id;
				$item_id    = (int) $item_id;

				if ( $booking_id <= 0 || $item_id <= 0 ) {
					return;
				}

				$entry = rbfw_gf_verified_entry_from_post( $item_id );
				if ( null === $entry ) {
					return;
				}

				if ( ! self::write_answers( $booking_id, $entry ) ) {
					return;
				}

				$entry_id = (int) $entry['id'];

				// Spend the entry, and record the booking on it so the Gravity
				// entry screen can point at this booking.
				RBFW_GF_Entry_Store::mark_claimed( $entry_id );
				RBFW_GF_Entry_Store::link_booking( $entry_id, $booking_id );
			}

			/**
			 * Write a Gravity entry's answers onto a booking post.
			 *
			 * Shared by this class and RBFW_GF_Booking_Creator so every mode stores
			 * the answers in exactly the same shape, and the Bookings screen has a
			 * single structure to read.
			 *
			 * @return bool Whether anything was written.
			 */
			public static function write_answers( int $booking_id, array $entry ): bool {
				if ( $booking_id <= 0 || empty( $entry['id'] ) ) {
					return false;
				}

				$rows = RBFW_GF_Mapper::entry_to_rows( $entry );
				if ( ! $rows ) {
					return false;
				}

				// Merge rather than overwrite: another module may already have
				// written registration rows for this booking.
				$existing = get_post_meta( $booking_id, 'rbfw_regf_info', true );
				$existing = is_array( $existing ) ? $existing : array();

				$merged = $existing + $rows;
				update_post_meta( $booking_id, 'rbfw_regf_info', $merged );

				$attendees = get_post_meta( $booking_id, 'rbfw_regf_attendees', true );
				if ( is_array( $attendees ) && $attendees ) {
					// One Gravity entry describes the booking as a whole, so the same
					// rows are merged into each booked unit rather than split across
					// them.
					foreach ( $attendees as $i => $attendee ) {
						if ( is_array( $attendee ) ) {
							$attendees[ $i ] = $attendee + $rows;
						}
					}
				} else {
					$attendees = array( $merged );
				}
				update_post_meta( $booking_id, 'rbfw_regf_attendees', $attendees );

				// Machine-readable link back to the entry.
				update_post_meta( $booking_id, '_rbfw_gf_entry_id', (int) $entry['id'] );

				return true;
			}
		}
	}
