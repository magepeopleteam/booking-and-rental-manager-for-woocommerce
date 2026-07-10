<?php
/**
 * Native checkout modal — billing + payment fields (standalone mode).
 *
 * Extracted from native_checkout_modal.php so the exact same markup can be rendered
 * twice: (1) inline in the modal on the initial page load, and (2) via AJAX
 * (RBFW_Native_Checkout::render_fields_ajax) right after a guest logs in from the
 * inline auth panel — swapped into the modal in place so the item/quantity already
 * selected in the surrounding booking form is never touched by a page reload.
 *
 * @var int $rbfw_native_fields_item_id Optional. Set by the includer when there is no
 *          queried object to fall back on (e.g. the AJAX render). Falls back to
 *          get_queried_object_id() when unset (the normal inline-render case).
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$rbfw_native_item_id = isset( $rbfw_native_fields_item_id ) ? absint( $rbfw_native_fields_item_id ) : get_queried_object_id();

$rbfw_native_name = $rbfw_native_email = $rbfw_native_phone = '';
if ( is_user_logged_in() ) {
	$rbfw_native_user  = wp_get_current_user();
	$rbfw_native_name  = $rbfw_native_user->display_name;
	$rbfw_native_email = $rbfw_native_user->user_email;
	$rbfw_native_phone = get_user_meta( $rbfw_native_user->ID, 'user_phone', true );
}
?>
<div class="rbfw-native-modal__fields">
	<p class="rbfw-native-field">
		<label for="rbfw_billing_name"><?php echo esc_html__( 'Full name', 'booking-and-rental-manager-for-woocommerce' ); ?> <span class="required">*</span></label>
		<input type="text" id="rbfw_billing_name" name="rbfw_billing_name" value="<?php echo esc_attr( $rbfw_native_name ); ?>" required>
	</p>
	<p class="rbfw-native-field">
		<label for="rbfw_billing_email"><?php echo esc_html__( 'Email address', 'booking-and-rental-manager-for-woocommerce' ); ?> <span class="required">*</span></label>
		<input type="email" id="rbfw_billing_email" name="rbfw_billing_email" value="<?php echo esc_attr( $rbfw_native_email ); ?>" required>
	</p>
	<p class="rbfw-native-field">
		<label for="rbfw_billing_phone"><?php echo esc_html__( 'Phone', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
		<input type="text" id="rbfw_billing_phone" name="rbfw_billing_phone" value="<?php echo esc_attr( $rbfw_native_phone ); ?>">
	</p>
	<?php
	/**
	 * Payment gateway selector / fields. The Pro plugin hooks this to render the
	 * PayPal / Stripe / Offline method picker; with no gateway configured nothing
	 * is rendered and the booking stays pending (free behaviour).
	 *
	 * @param int $item_id The rental item id.
	 */
	do_action( 'rbfw_native_checkout_payment_fields', $rbfw_native_item_id );
	?>
</div>
