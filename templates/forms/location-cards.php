<?php
/**
 * Pickup-location chooser cards — Location Inventory & Price feature.
 *
 * Included by every booking form template (single-day, multi-day,
 * multi-items, resort). Renders nothing when the item has the feature off,
 * so including it is always safe. Expects $post_id in scope.
 *
 * Selecting a card writes the location into the form's rbfw_pickup_point
 * field (the classic dropdown is hidden while cards are active), caps the
 * quantity input to the location's remaining stock and un-gates the rest of
 * the form (see rbfw_script.js).
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$rbfw_loc_inv_conf = function_exists( 'rbfw_get_location_inventory' ) ? rbfw_get_location_inventory( $post_id ) : array();
if ( ! empty( $rbfw_loc_inv_conf ) ) :
	/* Dates-first flow: availability is date-wise, so the cards start
	   disabled ("Select dates first") and only activate — with exact
	   remaining stock for the chosen range — once the customer picks the
	   booking dates (rbfw_location_stock_info AJAX in rbfw_script.js). */
	?>
	<div
		class="rbfw_loc_cards_wrap rbfw_loc_pending rbfw_loc_waitdates"
		id="rbfw_loc_cards_wrap"
		data-post-id="<?php echo esc_attr( $post_id ); ?>"
		data-txt-available="<?php esc_attr_e( '%d unit(s) available', 'booking-and-rental-manager-for-woocommerce' ); ?>"
		data-txt-soldout="<?php esc_attr_e( 'Sold out', 'booking-and-rental-manager-for-woocommerce' ); ?>"
		data-txt-note-choose="<?php esc_attr_e( 'Please choose a location to continue your booking.', 'booking-and-rental-manager-for-woocommerce' ); ?>"
	>
		<div class="rbfw_loc_cards_head">
			<span class="rbfw_loc_cards_title"><?php esc_html_e( 'Choose Your Location', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
			<span class="rbfw_loc_cards_required"><?php esc_html_e( 'Required', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
		</div>
		<div class="rbfw_loc_cards">
			<?php
			foreach ( $rbfw_loc_inv_conf as $rbfw_loc_slug => $rbfw_loc_row ) :
				$rbfw_loc_term = get_term_by( 'slug', $rbfw_loc_slug, 'rbfw_item_location' );
				$rbfw_loc_name = $rbfw_loc_term && ! is_wp_error( $rbfw_loc_term ) ? $rbfw_loc_term->name : ucwords( str_replace( '-', ' ', $rbfw_loc_slug ) );
				/* Only a configured stock of 0 is a hard sell-out up front —
				   real availability is date-wise and filled in once dates are picked. */
				$rbfw_soldout = $rbfw_loc_row['stock'] <= 0;
				?>
				<button
					type="button"
					class="rbfw_loc_card<?php echo $rbfw_soldout ? ' rbfw_loc_card_soldout' : ''; ?>"
					data-loc="<?php echo esc_attr( $rbfw_loc_slug ); ?>"
					data-price="<?php echo esc_attr( $rbfw_loc_row['price'] ); ?>"
					data-stock=""
					disabled
				>
					<span class="rbfw_loc_card_pin" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg>
					</span>
					<span class="rbfw_loc_card_body">
						<span class="rbfw_loc_card_name"><?php echo esc_html( $rbfw_loc_name ); ?></span>
						<span class="rbfw_loc_card_stock">
							<?php
							if ( $rbfw_soldout ) {
								esc_html_e( 'Sold out', 'booking-and-rental-manager-for-woocommerce' );
							} else {
								esc_html_e( 'Select dates first', 'booking-and-rental-manager-for-woocommerce' );
							}
							?>
						</span>
					</span>
					<span class="rbfw_loc_card_price<?php echo $rbfw_loc_row['price'] > 0 ? '' : ' rbfw_loc_card_price_free'; ?>">
						<?php
						if ( $rbfw_loc_row['price'] > 0 ) {
							echo wp_kses_post( '+ ' . wc_price( $rbfw_loc_row['price'] ) );
						} else {
							esc_html_e( 'Free', 'booking-and-rental-manager-for-woocommerce' );
						}
						?>
					</span>
					<span class="rbfw_loc_card_check" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
					</span>
				</button>
			<?php endforeach; ?>
		</div>
		<p class="rbfw_loc_cards_note"><?php esc_html_e( 'Please select your booking dates first — availability is date-wise.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
	</div>
<?php endif; ?>
