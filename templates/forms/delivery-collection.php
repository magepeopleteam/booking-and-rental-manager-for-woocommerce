<?php
/**
 * Delivery & Collection block for the booking forms.
 *
 * Included by each registration template right after the pick-up / drop-off location
 * fields. Renders nothing at all unless the shop offers delivery AND this item allows it,
 * so a shop that never turns the feature on sees no change whatsoever.
 *
 * WHAT IT POSTS
 *   rbfw_delivery_wanted     yes|''   customer wants it delivered
 *   rbfw_collection_wanted   yes|''   customer wants it collected
 *   rbfw_delivery_distance   float    the chosen zone's distance
 *   rbfw_delivery_address    string   where to deliver
 *
 * WHY A ZONE PICKER, NOT A KM BOX
 * There is no geolocation here — the shop prices manually. Asking a customer to type "how
 * many km away are you" makes them guess a number they do not know, and a wrong guess is a
 * wrong price the shop then has to argue about. So the configured bands are offered as
 * named CHOICES ("Up to 5 km — free", "5–15 km — 15.00") and the customer picks the one
 * that describes them. The value submitted is a distance inside the chosen band, so the
 * server prices it through exactly the same band table with no special-casing.
 *
 * It deliberately does NOT post a price. The figure shown here is for the customer's
 * benefit; the amount charged is recomputed server-side on both the WooCommerce and the
 * standalone path.
 *
 * Expects $rbfw_id (rental item id) in scope.
 *
 * @package booking-and-rental-manager-for-woocommerce
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$rbfw_delivery_item_id = isset( $rbfw_id ) ? absint( $rbfw_id ) : get_the_ID();

if ( ! function_exists( 'rbfw_delivery_enabled_for_item' ) || ! rbfw_delivery_enabled_for_item( $rbfw_delivery_item_id ) ) {
	return;
}

/*
 * Render once per request.
 *
 * Item types differ in where the booking UI is built: the step / timely flow renders its
 * panel over AJAX (template_segment/single_day_info.php) while the other flows render it
 * inline in the form. Both include this partial so every flow gets delivery, which means
 * one page could otherwise print two sets of delivery inputs with the same field names —
 * and the second would silently win on submit.
 *
 * The AJAX segment is a fresh request, so it always renders; only a genuine double-include
 * within one page render is suppressed.
 */
if ( ! empty( $GLOBALS['rbfw_delivery_block_rendered'] ) ) {
	return;
}
$GLOBALS['rbfw_delivery_block_rendered'] = true;

$rbfw_delivery_cfg = rbfw_delivery_settings();
?>
<div class="item rbfw-delivery-block"
	data-item-id="<?php echo esc_attr( $rbfw_delivery_item_id ); ?>"
	data-base-fee="<?php echo esc_attr( $rbfw_delivery_cfg['base_fee'] ); ?>"
	data-free-radius="<?php echo esc_attr( $rbfw_delivery_cfg['free_radius'] ); ?>"
	data-max-distance="<?php echo esc_attr( $rbfw_delivery_cfg['max_distance'] ); ?>"
	data-collection-mode="<?php echo esc_attr( $rbfw_delivery_cfg['collection_mode'] ); ?>"
	data-bands="<?php echo esc_attr( wp_json_encode( $rbfw_delivery_cfg['bands'] ) ); ?>"
	data-collection-bands="<?php echo esc_attr( wp_json_encode( $rbfw_delivery_cfg['collection_band_rows'] ) ); ?>">

	<div class="rbfw-single-right-heading">
		<?php esc_html_e( 'Delivery &amp; Collection', 'booking-and-rental-manager-for-woocommerce' ); ?>
	</div>

	<div class="item-content rbfw-delivery-content">

		<div class="rbfw-delivery-options">
			<?php if ( $rbfw_delivery_cfg['enabled'] ) : ?>
				<label class="rbfw-delivery-option">
					<input type="checkbox" name="rbfw_delivery_wanted" value="yes" class="rbfw-delivery-toggle">
					<span class="rbfw-delivery-option-label"><?php echo esc_html( $rbfw_delivery_cfg['delivery_label'] ); ?></span>
					<span class="rbfw-delivery-option-hint"><?php esc_html_e( 'We bring it to you', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</label>
			<?php endif; ?>

			<?php if ( $rbfw_delivery_cfg['collection_enabled'] ) : ?>
				<label class="rbfw-delivery-option">
					<input type="checkbox" name="rbfw_collection_wanted" value="yes" class="rbfw-delivery-toggle">
					<span class="rbfw-delivery-option-label"><?php echo esc_html( $rbfw_delivery_cfg['collection_label'] ); ?></span>
					<span class="rbfw-delivery-option-hint"><?php esc_html_e( 'We pick it up afterwards', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</label>
			<?php endif; ?>
		</div>

		<div class="rbfw-delivery-fields" style="display:none;">
			<div class="rbfw-delivery-field">
				<label for="rbfw-delivery-address-<?php echo esc_attr( $rbfw_delivery_item_id ); ?>">
					<?php esc_html_e( 'Delivery address', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<?php if ( $rbfw_delivery_cfg['require_address'] ) : ?><span class="rbfw-required">*</span><?php endif; ?>
				</label>
				<input type="text"
					id="rbfw-delivery-address-<?php echo esc_attr( $rbfw_delivery_item_id ); ?>"
					name="rbfw_delivery_address"
					class="rbfw-input rbfw-delivery-address"
					autocomplete="street-address"
					placeholder="<?php esc_attr_e( 'Street, town, postcode', 'booking-and-rental-manager-for-woocommerce' ); ?>"
					<?php echo $rbfw_delivery_cfg['require_address'] ? 'data-required="1"' : ''; ?>>
			</div>

			<?php
			/*
			 * Zone choices, built from the configured bands.
			 *
			 * The posted value is the band's MIDPOINT rather than its edge. Bands are
			 * inclusive at both ends, so neighbouring bands touch (0-5 and 5-15 both contain
			 * 5) and submitting an edge would let the "first match wins" rule resolve to the
			 * cheaper neighbour — the customer would pick the 5-15 zone and be charged the
			 * 0-5 price. A midpoint can only ever fall in its own band.
			 */
			$rbfw_delivery_zones = array();

			if ( $rbfw_delivery_cfg['free_radius'] > 0 ) {
				$rbfw_delivery_zones[] = array(
					'value' => min( $rbfw_delivery_cfg['free_radius'], max( 0.1, $rbfw_delivery_cfg['free_radius'] / 2 ) ),
					'label' => sprintf(
						/* translators: %s: free radius in km. */
						__( 'Within %s km', 'booking-and-rental-manager-for-woocommerce' ),
						rbfw_delivery_format_km( $rbfw_delivery_cfg['free_radius'] )
					),
					'note'  => __( 'Free', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}

			foreach ( $rbfw_delivery_cfg['bands'] as $rbfw_band ) {
				$rbfw_from = $rbfw_band['from'];
				$rbfw_to   = $rbfw_band['to'];

				if ( $rbfw_delivery_cfg['free_radius'] > 0 ) {
					// Entirely inside the free radius — already covered by the free row above.
					if ( $rbfw_to <= $rbfw_delivery_cfg['free_radius'] ) {
						continue;
					}
					/*
					 * Straddles the free radius (free 0-3, band 0-5): only the part ABOVE the
					 * radius actually costs anything, so the option must describe 3-5, not 0-5.
					 * Leaving it as 0-5 put its midpoint inside the free zone, which quoted a
					 * 4 km customer at nothing — the shop delivering for free by accident.
					 */
					if ( $rbfw_from < $rbfw_delivery_cfg['free_radius'] ) {
						$rbfw_from = $rbfw_delivery_cfg['free_radius'];
					}
				}

				// Midpoint of the chargeable span. Bands are inclusive at both ends, so
				// neighbours touch; a midpoint can only ever resolve to its own band.
				$rbfw_mid   = $rbfw_from + ( ( $rbfw_to - $rbfw_from ) / 2 );
				$rbfw_quote = rbfw_delivery_quote( $rbfw_delivery_item_id, $rbfw_mid, true, false );

				$rbfw_delivery_zones[] = array(
					'value' => $rbfw_mid,
					'label' => sprintf(
						/* translators: 1: band lower bound, 2: band upper bound. */
						__( '%1$s – %2$s km', 'booking-and-rental-manager-for-woocommerce' ),
						rbfw_delivery_format_km( $rbfw_from ),
						rbfw_delivery_format_km( $rbfw_to )
					),
					'note'  => $rbfw_quote['delivery'] > 0
						? wp_strip_all_tags( rbfw_delivery_price_html( $rbfw_quote['delivery'] ) )
						: __( 'Free', 'booking-and-rental-manager-for-woocommerce' ),
				);
			}
			?>
			<div class="rbfw-delivery-field rbfw-delivery-field-distance">
				<label for="rbfw-delivery-distance-<?php echo esc_attr( $rbfw_delivery_item_id ); ?>">
					<?php esc_html_e( 'How far are you from us?', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<span class="rbfw-required">*</span>
				</label>
				<select id="rbfw-delivery-distance-<?php echo esc_attr( $rbfw_delivery_item_id ); ?>"
					name="rbfw_delivery_distance"
					class="rbfw-select rbfw-delivery-distance">
					<option value=""><?php esc_html_e( 'Choose your area…', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
					<?php foreach ( $rbfw_delivery_zones as $rbfw_zone ) : ?>
						<option value="<?php echo esc_attr( $rbfw_zone['value'] ); ?>">
							<?php
							printf(
								/* translators: 1: distance zone, 2: price for that zone. */
								esc_html__( '%1$s — %2$s', 'booking-and-rental-manager-for-woocommerce' ),
								esc_html( $rbfw_zone['label'] ),
								esc_html( $rbfw_zone['note'] )
							);
							?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php if ( $rbfw_delivery_cfg['max_distance'] > 0 ) : ?>
					<small class="rbfw-delivery-range">
						<?php
						printf(
							/* translators: %s: maximum delivery distance in km. */
							esc_html__( 'We deliver up to %s km. Further away? Get in touch.', 'booking-and-rental-manager-for-woocommerce' ),
							esc_html( rbfw_delivery_format_km( $rbfw_delivery_cfg['max_distance'] ) )
						);
						?>
					</small>
				<?php endif; ?>
			</div>

			<div class="rbfw-delivery-quote" aria-live="polite"></div>

			<?php if ( '' !== trim( (string) $rbfw_delivery_cfg['help_text'] ) ) : ?>
				<p class="rbfw-delivery-help"><?php echo esc_html( $rbfw_delivery_cfg['help_text'] ); ?></p>
			<?php endif; ?>
		</div>

	</div>
</div>
