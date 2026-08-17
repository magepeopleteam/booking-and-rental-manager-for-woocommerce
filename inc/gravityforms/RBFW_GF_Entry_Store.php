<?php
	/**
	 * Ownership of a Gravity Forms entry.
	 *
	 * A Gravity Forms entry id is a small sequential integer, so it is trivially
	 * guessable. Accepting one from POST without proof would let any visitor
	 * attach another customer's answers — including their name, phone and address
	 * — to their own booking. Every entry this bridge creates therefore gets a
	 * random claim token stored as entry meta; the booking form must present the
	 * matching token before the entry is trusted.
	 *
	 * The token is delivered to the browser inside the Gravity Forms confirmation
	 * markup rather than in a cookie or session. That keeps this working on
	 * page-cached sites, on sites running the rental plugin in standalone mode
	 * without WooCommerce, and for logged-out visitors.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'RBFW_GF_Entry_Store' ) ) {

		class RBFW_GF_Entry_Store {

			const META_TOKEN    = 'rbfw_gf_token';
			const META_CLAIMED  = 'rbfw_gf_claimed';
			const META_ORDER    = 'rbfw_gf_order_id';
			const META_BOOKING  = 'rbfw_gf_booking_id';
			const META_ITEM     = 'rbfw_gf_item_id';

			public function __construct() {
				// Note the un-suffixed hook names: these fire for every form, and
				// the handlers decide relevance from the saved settings. A
				// `gform_after_submission_7` style hook would bind the bridge to
				// one form, which is exactly what this plugin must not do.
				add_action( 'gform_after_submission', array( $this, 'issue_token' ), 10, 2 );
				add_filter( 'gform_confirmation', array( $this, 'append_claim_payload' ), 10, 4 );
			}

			/**
			 * Mint a claim token for any submission of a form that some rental
			 * item actually uses.
			 *
			 * @param array $entry
			 * @param array $form
			 */
			public function issue_token( $entry, $form ): void {
				if ( empty( $entry['id'] ) || empty( $form['id'] ) ) {
					return;
				}
				if ( ! in_array( (int) $form['id'], rbfw_gf_forms_in_use(), true ) ) {
					return;
				}

				gform_update_meta( (int) $entry['id'], self::META_TOKEN, wp_generate_password( 32, false, false ) );

				// Remember which rental the visitor came from, when the embed
				// passed it through. Used to reject an entry replayed against a
				// different rental item.
				$item_id = isset( $_POST['rbfw_gf_item_id'] ) ? absint( wp_unslash( $_POST['rbfw_gf_item_id'] ) ) : 0;
				if ( $item_id > 0 ) {
					gform_update_meta( (int) $entry['id'], self::META_ITEM, $item_id );
				}
			}

			/**
			 * Append the entry id and its token to the confirmation output so the
			 * page script can hand them to the booking form.
			 *
			 * Gravity Forms passes a string for message confirmations and an array
			 * for redirects; only the string case is ours to touch.
			 *
			 * @param string|array $confirmation
			 * @param array        $form
			 * @param array        $entry
			 * @param bool         $ajax
			 *
			 * @return string|array
			 */
			public function append_claim_payload( $confirmation, $form, $entry, $ajax ) {
				if ( ! is_string( $confirmation ) ) {
					return $confirmation;
				}
				if ( empty( $entry['id'] ) || empty( $form['id'] ) ) {
					return $confirmation;
				}
				if ( ! in_array( (int) $form['id'], rbfw_gf_forms_in_use(), true ) ) {
					return $confirmation;
				}

				$token = self::get_token( (int) $entry['id'] );
				if ( '' === $token ) {
					return $confirmation;
				}

				$confirmation .= sprintf(
					'<div class="rbfw-gf-claim" data-entry-id="%1$s" data-token="%2$s" data-form-id="%3$s" hidden></div>',
					esc_attr( (string) (int) $entry['id'] ),
					esc_attr( $token ),
					esc_attr( (string) (int) $form['id'] )
				);

				return $confirmation;
			}

			public static function get_token( int $entry_id ): string {
				if ( $entry_id <= 0 || ! function_exists( 'gform_get_meta' ) ) {
					return '';
				}

				return (string) gform_get_meta( $entry_id, self::META_TOKEN );
			}

			/**
			 * The single gate every consumer of a posted entry id must pass through.
			 *
			 * Verifies, in order: the entry exists; it is not spam or trashed; the
			 * presented token matches the stored one in constant time; the entry
			 * belongs to the form this rental is configured to use; and the entry
			 * has not already been consumed by an earlier booking.
			 *
			 * @return array|null The entry on success, null on any failure.
			 */
			public static function verify( int $entry_id, string $token, int $item_id ): ?array {
				if ( $entry_id <= 0 || '' === $token ) {
					return null;
				}

				$stored = self::get_token( $entry_id );
				if ( '' === $stored || ! hash_equals( $stored, $token ) ) {
					return null;
				}

				$entry = GFAPI::get_entry( $entry_id );
				if ( is_wp_error( $entry ) || empty( $entry['id'] ) ) {
					return null;
				}

				// Spam and trashed entries must never reach an order.
				if ( ! empty( $entry['status'] ) && 'active' !== $entry['status'] ) {
					return null;
				}

				// The entry has to come from the form this rental is set to use,
				// otherwise a valid token for form A could be replayed to attach
				// unrelated answers to a rental configured for form B.
				$expected_form = rbfw_gf_form_id_for_item( $item_id );
				if ( $expected_form <= 0 || (int) $entry['form_id'] !== $expected_form ) {
					return null;
				}

				// One entry, one booking. Prevents a browser refresh from reusing
				// the same answers across two separate bookings.
				$claimed = (string) gform_get_meta( $entry_id, self::META_CLAIMED );
				if ( '' !== $claimed ) {
					return null;
				}

				return $entry;
			}

			/**
			 * Mark an entry as spent. Called once the answers are safely inside
			 * the cart item, so a resubmitted add-to-cart cannot reuse them.
			 */
			public static function mark_claimed( int $entry_id ): void {
				if ( $entry_id > 0 ) {
					gform_update_meta( $entry_id, self::META_CLAIMED, current_time( 'mysql' ) );
				}
			}

			/**
			 * Link the entry back to the order it produced, so an entry opened in
			 * the Gravity Forms admin can be traced to its booking.
			 */
			public static function link_order( int $entry_id, int $order_id ): void {
				if ( $entry_id > 0 && $order_id > 0 ) {
					gform_update_meta( $entry_id, self::META_ORDER, $order_id );
				}
			}

			/**
			 * Link the entry to the booking post it produced. This is the custom
			 * (standalone) mode counterpart of link_order() — without WooCommerce
			 * there is no order, and the booking post is the record.
			 */
			public static function link_booking( int $entry_id, int $booking_id ): void {
				if ( $entry_id > 0 && $booking_id > 0 ) {
					gform_update_meta( $entry_id, self::META_BOOKING, $booking_id );
				}
			}
		}
	}
