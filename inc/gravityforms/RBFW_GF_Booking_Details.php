<?php
	/**
	 * Show the customer's Gravity Forms answers on the Bookings screen.
	 *
	 * The free Bookings listing is a flat table — reference, customer, item, total,
	 * status, date — and its per-booking "view" action is Pro-locked, so there is
	 * no detail screen where answers could otherwise appear. This adds a
	 * full-width disclosure row directly beneath each booking that has answers,
	 * via the `rbfw_booking_list_after_row` action.
	 *
	 * A native <details> element is used rather than custom show/hide scripting:
	 * it collapses by default so the table stays scannable, works with no
	 * JavaScript, and is keyboard accessible for free.
	 *
	 * Both booking sources are handled. Standalone bookings keep the answers in
	 * post meta on the booking; WooCommerce bookings keep them on the order's line
	 * items, mirrored onto the booking record by inc/rbfw_order_meta.php.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'RBFW_GF_Booking_Details' ) ) {

		class RBFW_GF_Booking_Details {

			public function __construct() {
				add_action( 'rbfw_booking_list_after_row', array( $this, 'render_row' ) );
			}

			/**
			 * @param array $row Normalised booking row from RBFW_Booking_Normalizer.
			 */
			public function render_row( $row ): void {
				if ( ! is_array( $row ) || empty( $row['id'] ) ) {
					return;
				}

				$booking_id = (int) $row['id'];
				$rows       = $this->answers_for( $booking_id, $row );

				if ( ! $rows ) {
					return;
				}

				$colspan  = class_exists( 'RBFW_Booking_List_Table' ) && defined( 'RBFW_Booking_List_Table::COLUMN_COUNT' )
					? (int) RBFW_Booking_List_Table::COLUMN_COUNT
					: 7;
				$entry_id = (int) get_post_meta( $booking_id, '_rbfw_gf_entry_id', true );
				?>
				<tr class="rbfw-gf-answers-row">
					<td colspan="<?php echo esc_attr( (string) $colspan ); ?>">
						<details class="rbfw-gf-answers">
							<summary>
								<span class="dashicons dashicons-feedback"></span>
								<?php
									printf(
										/* translators: %d: number of answered questions. */
										esc_html( _n( 'Booking questions (%d answer)', 'Booking questions (%d answers)', count( $rows ), 'booking-and-rental-manager-for-woocommerce' ) ),
										count( $rows )
									);
								?>
								<?php if ( $entry_id > 0 && $this->entry_link( $entry_id ) ) : ?>
									<a class="rbfw-gf-entry-link" href="<?php echo esc_url( $this->entry_link( $entry_id ) ); ?>">
										<?php
											/* translators: %d: Gravity Forms entry ID. */
											printf( esc_html__( 'Entry #%d', 'booking-and-rental-manager-for-woocommerce' ), $entry_id );
										?>
									</a>
								<?php endif; ?>
							</summary>

							<table class="rbfw-gf-answers-table">
								<tbody>
								<?php foreach ( $rows as $answer ) : ?>
									<tr>
										<th scope="row"><?php echo esc_html( $answer['label'] ); ?></th>
										<td><?php echo nl2br( esc_html( $answer['value'] ) ); ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</details>
					</td>
				</tr>
				<?php
			}

			/**
			 * Flat label/value answers for one booking.
			 *
			 * Reads the same `rbfw_regf_info` structure the rest of the plugin uses,
			 * so answers written by either checkout path render identically. Falls
			 * back to the per-unit attendee list when the flat key is absent.
			 *
			 * @return array<int,array{label:string,value:string}>
			 */
			private function answers_for( int $booking_id, array $row ): array {
				$raw = get_post_meta( $booking_id, 'rbfw_regf_info', true );

				if ( ! is_array( $raw ) || ! $raw ) {
					$attendees = get_post_meta( $booking_id, 'rbfw_regf_attendees', true );
					if ( is_array( $attendees ) && isset( $attendees[0] ) && is_array( $attendees[0] ) ) {
						$raw = $attendees[0];
					}
				}

				// WooCommerce bookings may only carry the answers on the order line
				// item, when the booking mirror predates this module.
				if ( ( ! is_array( $raw ) || ! $raw ) && ! empty( $row['source'] ) && 'woocommerce' === $row['source'] ) {
					$raw = $this->answers_from_order( $booking_id );
				}

				if ( ! is_array( $raw ) || ! $raw ) {
					return array();
				}

				$out = array();
				foreach ( $raw as $key => $field ) {
					if ( is_array( $field ) ) {
						$label = isset( $field['label'] ) ? (string) $field['label'] : (string) $key;
						$value = isset( $field['value'] ) ? $field['value'] : '';
					} else {
						$label = (string) $key;
						$value = $field;
					}

					if ( is_array( $value ) ) {
						$value = implode( ', ', array_filter( array_map( 'strval', $value ) ) );
					}

					$value = trim( (string) $value );
					if ( '' === $value || '' === trim( $label ) ) {
						continue;
					}

					$out[] = array( 'label' => $label, 'value' => $value );
				}

				return $out;
			}

			/**
			 * Line-item fallback for WooCommerce-sourced rows.
			 *
			 * @return array
			 */
			private function answers_from_order( int $order_id ): array {
				if ( ! function_exists( 'wc_get_order' ) ) {
					return array();
				}

				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					return array();
				}

				foreach ( $order->get_items() as $item ) {
					$entry_id = (int) $item->get_meta( '_rbfw_gf_entry_id', true );
					if ( $entry_id <= 0 ) {
						continue;
					}

					$rows = array();
					foreach ( $item->get_meta_data() as $meta ) {
						$key = isset( $meta->key ) ? (string) $meta->key : '';
						// Hidden/internal meta is underscore-prefixed; the customer's
						// answers were stored with their human label as the key.
						if ( '' === $key || '_' === $key[0] ) {
							continue;
						}
						$rows[ $key ] = array( 'label' => $key, 'value' => $meta->value );
					}

					if ( $rows ) {
						return $rows;
					}
				}

				return array();
			}

			/**
			 * Admin link to the Gravity Forms entry, when the form is still known.
			 */
			private function entry_link( int $entry_id ): string {
				if ( ! class_exists( 'GFAPI' ) ) {
					return '';
				}

				$entry = GFAPI::get_entry( $entry_id );
				if ( is_wp_error( $entry ) || empty( $entry['form_id'] ) ) {
					return '';
				}

				return admin_url(
					'admin.php?page=gf_entries&view=entry&id=' . (int) $entry['form_id'] . '&lid=' . $entry_id
				);
			}
		}
	}
