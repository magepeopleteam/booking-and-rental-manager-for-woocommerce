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
 *   rbfw_delivery_distance   float    km, as stated by the customer
 *   rbfw_delivery_address    string   where to deliver
 *
 * It deliberately does NOT post a price. The figure shown here is an estimate rendered for
 * the customer's benefit; the amount actually charged is recomputed server-side from the
 * band table on both the WooCommerce and the standalone path.
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

			<div class="rbfw-delivery-field rbfw-delivery-field-distance">
				<label for="rbfw-delivery-distance-<?php echo esc_attr( $rbfw_delivery_item_id ); ?>">
					<?php esc_html_e( 'Distance from us (km)', 'booking-and-rental-manager-for-woocommerce' ); ?>
					<span class="rbfw-required">*</span>
				</label>
				<input type="number"
					id="rbfw-delivery-distance-<?php echo esc_attr( $rbfw_delivery_item_id ); ?>"
					name="rbfw_delivery_distance"
					class="rbfw-input rbfw-delivery-distance"
					min="0" step="0.1" inputmode="decimal"
					placeholder="0"
					<?php echo $rbfw_delivery_cfg['max_distance'] > 0 ? 'max="' . esc_attr( $rbfw_delivery_cfg['max_distance'] ) . '"' : ''; ?>>
			</div>

			<div class="rbfw-delivery-quote" aria-live="polite"></div>

			<?php if ( '' !== trim( (string) $rbfw_delivery_cfg['help_text'] ) ) : ?>
				<p class="rbfw-delivery-help"><?php echo esc_html( $rbfw_delivery_cfg['help_text'] ); ?></p>
			<?php endif; ?>
		</div>

	</div>
</div>
