<?php
	/**
	 * "Gravity form creates the booking" mode.
	 *
	 * When a rental item is set to this mode the Gravity form *is* the order form:
	 * the rental booking form is not shown at all, and submitting the Gravity form
	 * creates the booking record directly, so it appears on the Bookings screen
	 * with every answer the customer gave.
	 *
	 * Field mapping is by Gravity Forms field TYPE, never by field id — a `name`
	 * field is the customer name, `email` the address, `phone` the number, the
	 * first `date` field the start date, `total` the amount. Any form therefore
	 * works without configuration, and editing the form in Gravity Forms does not
	 * break the mapping.
	 *
	 * The trade-off, accepted when choosing this mode: a Gravity form carries no
	 * rental calendar, so the availability, inventory and off-day checks that gate
	 * the normal booking form cannot gate this one. Dates come from whatever the
	 * form asked for. Use `required` or `optional` mode instead where those
	 * guarantees matter.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'RBFW_GF_Booking_Creator' ) ) {

		class RBFW_GF_Booking_Creator {

			public function __construct() {
				// Priority 20: after RBFW_GF_Entry_Store::issue_token() at 10, so the
				// entry already carries its token and item id.
				add_action( 'gform_after_submission', array( $this, 'maybe_create_booking' ), 20, 2 );
			}

			/**
			 * @param array $entry
			 * @param array $form
			 */
			public function maybe_create_booking( $entry, $form ): void {
				if ( empty( $entry['id'] ) || empty( $form['id'] ) ) {
					return;
				}

				$entry_id = (int) $entry['id'];
				$item_id  = $this->item_id_for_entry( $entry_id );

				if ( $item_id <= 0 ) {
					return;
				}
				if ( 'booking' !== rbfw_gf_mode_for_item( $item_id ) ) {
					return;
				}
				// Confirm the form really is the one configured for this rental, so a
				// submission of some unrelated form can never open a booking.
				if ( (int) $form['id'] !== rbfw_gf_form_id_for_item( $item_id ) ) {
					return;
				}
				// Idempotence: this action can fire more than once for one entry
				// (notification retries, add-ons re-saving), and each run must not
				// produce another booking.
				if ( '' !== (string) gform_get_meta( $entry_id, RBFW_GF_Entry_Store::META_CLAIMED ) ) {
					return;
				}
				if ( ! class_exists( 'RBFW_Booking_Manager' ) ) {
					return;
				}

				$mapped = self::map_entry( $entry, $form );

				if ( '' === $mapped['email'] || ! is_email( $mapped['email'] ) ) {
					// Without a contact address the booking is unusable to the shop.
					// The entry is still stored, so nothing the customer typed is lost.
					return;
				}

				$item_type = (string) get_post_meta( $item_id, 'rbfw_item_type', true );
				$dates     = $mapped['dates'];
				$quantity  = $mapped['quantity'];

				$result = RBFW_Booking_Manager::create_booking(
					$item_id,
					array(
						'customer'    => array(
							'name'  => $mapped['name'],
							'email' => $mapped['email'],
							'phone' => $mapped['phone'],
						),
						'dates'       => $dates,
						'subtotal'    => $mapped['total'],
						'discount'    => 0,
						'coupon_code' => '',
						'total'       => $mapped['total'],
						'item_type'   => $item_type,
						'quantity'    => $quantity,
						'ticket_info' => array(
							'rbfw_start_date'     => $dates['start_date'],
							'rbfw_end_date'       => $dates['end_date'],
							'rbfw_start_time'     => $dates['start_time'],
							'rbfw_end_time'       => $dates['end_time'],
							'rbfw_item_quantity'  => $quantity,
							'rbfw_type_info'      => array(),
							'rbfw_variation_info' => array(),
							'rbfw_service_info'   => array(),
							'rbfw_service_infos'  => array(),
						),
						'raw'         => $mapped['raw'],
					)
				);

				if ( is_wp_error( $result ) || empty( $result['booking_id'] ) ) {
					return;
				}

				$booking_id = (int) $result['booking_id'];

				// The answers themselves, through the same path both other modes use.
				RBFW_GF_Standalone::write_answers( $booking_id, $entry );

				RBFW_GF_Entry_Store::mark_claimed( $entry_id );
				RBFW_GF_Entry_Store::link_booking( $entry_id, $booking_id );

				/**
				 * Fires after a booking has been opened from a Gravity Forms entry.
				 *
				 * @param int   $booking_id
				 * @param int   $item_id
				 * @param array $entry
				 */
				do_action( 'rbfw_gf_booking_created', $booking_id, $item_id, $entry );
			}

			/**
			 * The rental this entry belongs to.
			 *
			 * Prefers the entry meta written at submission time, because by the time
			 * later hooks run the POST may no longer be the submitting request.
			 */
			private function item_id_for_entry( int $entry_id ): int {
				$item_id = (int) gform_get_meta( $entry_id, RBFW_GF_Entry_Store::META_ITEM );

				if ( $item_id <= 0 && isset( $_POST['rbfw_gf_item_id'] ) ) {
					$item_id = absint( wp_unslash( $_POST['rbfw_gf_item_id'] ) );
				}

				return ( $item_id > 0 && rbfw_gf_cpt() === get_post_type( $item_id ) ) ? $item_id : 0;
			}

			/**
			 * Reduce a Gravity entry to the fields a booking needs.
			 *
			 * Everything is resolved from field types, so no form-specific
			 * configuration is involved. Each value is filterable for the rare form
			 * that needs an override.
			 *
			 * @return array{name:string,email:string,phone:string,total:float,quantity:int,dates:array,raw:array}
			 */
			public static function map_entry( array $entry, array $form ): array {
				$out = array(
					'name'     => '',
					'email'    => '',
					'phone'    => '',
					'total'    => 0.0,
					'quantity' => 1,
					'dates'    => array( 'start_date' => '', 'end_date' => '', 'start_time' => '', 'end_time' => '' ),
					'raw'      => array(),
				);

				$currency = ! empty( $entry['currency'] ) ? (string) $entry['currency'] : '';
				$dates    = array();

				foreach ( (array) ( $form['fields'] ?? array() ) as $field ) {
					$type = isset( $field->type ) ? (string) $field->type : '';
					$fid  = isset( $field->id ) ? (string) $field->id : '';
					if ( '' === $fid ) {
						continue;
					}

					$display = self::display_value( $field, $entry, $currency );

					switch ( $type ) {
						case 'name':
							if ( '' === $out['name'] ) {
								$out['name'] = $display;
							}
							break;

						case 'email':
							if ( '' === $out['email'] ) {
								$out['email'] = $display;
							}
							break;

						case 'phone':
							if ( '' === $out['phone'] ) {
								$out['phone'] = $display;
							}
							break;

						case 'date':
							if ( '' !== $display ) {
								$dates[] = $display;
							}
							break;

						case 'total':
							$out['total'] = self::to_number( $display, $currency );
							break;
					}

					// Whole sanitized answer set, stored verbatim on the booking the way
					// the native checkout stores its own posted payload.
					if ( '' !== $display ) {
						$out['raw'][ 'gf_' . $fid ] = $display;
					}
				}

				// A single date field describes a one-day booking; two describe a range.
				if ( isset( $dates[0] ) ) {
					$start = self::normalise_date( $dates[0] );
					$end   = isset( $dates[1] ) ? self::normalise_date( $dates[1] ) : $start;

					// Guard against the two being supplied the wrong way round.
					if ( $end < $start ) {
						$tmp   = $start;
						$start = $end;
						$end   = $tmp;
					}

					$out['dates']['start_date'] = $start;
					$out['dates']['end_date']   = $end;
				}

				// No Total field on the form: fall back to the entry's own payment total.
				if ( $out['total'] <= 0 && isset( $entry['payment_amount'] ) ) {
					$out['total'] = self::to_number( (string) $entry['payment_amount'], $currency );
				}

				if ( '' === $out['name'] && '' !== $out['email'] ) {
					$out['name'] = strtok( $out['email'], '@' );
				}

				return (array) apply_filters( 'rbfw_gf_mapped_booking', $out, $entry, $form );
			}

			/**
			 * One field's answer as plain text, using Gravity's own renderer so
			 * composite fields (Name, Address, checkbox groups) read the way they do
			 * in the entry view.
			 */
			private static function display_value( $field, array $entry, string $currency ): string {
				$fid = isset( $field->id ) ? (string) $field->id : '';

				if ( method_exists( 'GFFormsModel', 'get_lead_field_value' ) ) {
					$raw = GFFormsModel::get_lead_field_value( $entry, $field );
				} elseif ( class_exists( 'RGFormsModel' ) ) {
					$raw = RGFormsModel::get_lead_field_value( $entry, $field );
				} else {
					$raw = $entry[ $fid ] ?? '';
				}

				$value = GFCommon::get_lead_field_display( $field, $raw, $currency, false, 'text' );
				$value = is_array( $value ) ? implode( ', ', array_filter( (array) $value ) ) : (string) $value;

				return trim( wp_strip_all_tags( $value ) );
			}

			/**
			 * Money from a Gravity display string.
			 *
			 * Uses GFCommon::to_number() rather than floatval(): entry values are
			 * formatted for their currency, so a European "1.250,00 €" is read as
			 * 1250.00 instead of 1.25.
			 */
			private static function to_number( string $value, string $currency ): float {
				if ( '' === $value ) {
					return 0.0;
				}
				if ( method_exists( 'GFCommon', 'to_number' ) ) {
					$n = GFCommon::to_number( $value, $currency );
					if ( is_numeric( $n ) ) {
						return max( 0.0, (float) $n );
					}
				}

				return max( 0.0, (float) preg_replace( '/[^0-9.]/', '', $value ) );
			}

			/**
			 * Gravity date fields honour a per-field display format (mm/dd/yyyy and
			 * dd/mm/yyyy among them), while the booking record stores Y-m-d.
			 */
			private static function normalise_date( string $value ): string {
				$value = trim( $value );
				if ( '' === $value ) {
					return '';
				}

				// Already ISO.
				if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
					return $value;
				}

				$ts = strtotime( $value );

				return $ts ? gmdate( 'Y-m-d', $ts ) : '';
			}
		}
	}
