<?php
/**
 * Native checkout modal (standalone mode).
 *
 * Output once in the footer on rental item pages when Booking Mode = Standalone. The
 * "Book Now" button in the booking form opens this modal (md_script.js / sd_script.js);
 * on submit it POSTs the booking form plus these billing fields to wp_ajax_rbfw_native_checkout.
 *
 * Phase 1: collects contact details only — no payment fields (those land with the payment phase).
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>
<style>
	.rbfw-native-modal{position:fixed;inset:0;z-index:99999;}
	.rbfw-native-modal__overlay{position:absolute;inset:0;background:rgba(0,0,0,.55);}
	.rbfw-native-modal__dialog{position:relative;max-width:440px;margin:8vh auto 0;background:#fff;border-radius:10px;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,.25);}
	.rbfw-native-modal__close{position:absolute;top:10px;right:14px;border:0;background:none;font-size:26px;line-height:1;cursor:pointer;color:#666;}
	.rbfw-native-modal__title{margin:0 0 14px;font-size:20px;}
	.rbfw-native-modal__summary{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;margin-bottom:16px;background:#f6f7f9;border-radius:8px;font-weight:600;}
	.rbfw-native-field{margin:0 0 12px;}
	.rbfw-native-field label{display:block;margin-bottom:4px;font-size:13px;font-weight:600;}
	.rbfw-native-field input{width:100%;padding:9px 11px;border:1px solid #cfd4da;border-radius:6px;box-sizing:border-box;}
	.rbfw-native-modal__message{min-height:18px;margin:0 0 10px;font-size:13px;}
	.rbfw-native-modal__message.error{color:#b32d2e;}
	.rbfw-native-modal__message.success{color:#1a7f37;}
	.rbfw-native-modal__submit{width:100%;padding:11px 16px;font-size:15px;cursor:pointer;}
	.rbfw-native-modal__submit.is-loading{opacity:.6;cursor:progress;}
	.rbfw-native-modal__note{margin:10px 0 0;font-size:12px;color:#777;text-align:center;}
</style>
<div id="rbfw-native-checkout-modal" class="rbfw-native-modal" aria-hidden="true" style="display:none;">
	<div class="rbfw-native-modal__overlay" data-rbfw-native-close></div>
	<div class="rbfw-native-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rbfw-native-modal-title">
		<button type="button" class="rbfw-native-modal__close" data-rbfw-native-close aria-label="<?php echo esc_attr__( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
		<h3 id="rbfw-native-modal-title" class="rbfw-native-modal__title"><?php echo esc_html__( 'Complete your booking', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>

		<div class="rbfw-native-modal__summary">
			<span class="rbfw-native-modal__total-label"><?php echo esc_html__( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?>:</span>
			<span class="rbfw-native-modal__total-value" data-rbfw-native-total></span>
		</div>

		<div class="rbfw-native-modal__fields">
			<p class="rbfw-native-field">
				<label for="rbfw_billing_name"><?php echo esc_html__( 'Full name', 'booking-and-rental-manager-for-woocommerce' ); ?> <span class="required">*</span></label>
				<input type="text" id="rbfw_billing_name" name="rbfw_billing_name" required>
			</p>
			<p class="rbfw-native-field">
				<label for="rbfw_billing_email"><?php echo esc_html__( 'Email address', 'booking-and-rental-manager-for-woocommerce' ); ?> <span class="required">*</span></label>
				<input type="email" id="rbfw_billing_email" name="rbfw_billing_email" required>
			</p>
			<p class="rbfw-native-field">
				<label for="rbfw_billing_phone"><?php echo esc_html__( 'Phone', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
				<input type="text" id="rbfw_billing_phone" name="rbfw_billing_phone">
			</p>
		</div>

		<div class="rbfw-native-modal__message" data-rbfw-native-message aria-live="polite"></div>

		<button type="button" class="rbfw-native-modal__submit button" data-rbfw-native-submit>
			<?php echo esc_html__( 'Confirm booking', 'booking-and-rental-manager-for-woocommerce' ); ?>
		</button>
		<p class="rbfw-native-modal__note"><?php echo esc_html__( 'Payment will be arranged after your booking is received.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
	</div>
</div>
