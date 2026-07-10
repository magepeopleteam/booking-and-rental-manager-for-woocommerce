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
	.rbfw-native-modal__submit{
		position:relative;
		display:flex;
		align-items:center;
		justify-content:center;
		gap:8px;
		width:100%;
		margin:4px 0 0;
		padding:13px 18px;
		border:none;
		border-radius:8px;
		background:var(--rbfw_color_primary,#ff3726);
		color:#fff;
		font-size:15px;
		font-weight:700;
		line-height:1.2;
		letter-spacing:.2px;
		cursor:pointer;
		box-shadow:0 8px 20px -6px rgba(0,0,0,.35);
		transition:background-color .2s ease,box-shadow .2s ease,transform .05s ease;
	}
	.rbfw-native-modal__submit .dashicons{font-size:18px;width:18px;height:18px;}
	.rbfw-native-modal__submit:hover,
	.rbfw-native-modal__submit:focus-visible{
		background:var(--rbfw_single_page_secondary_color,#333);
		box-shadow:0 10px 24px -6px rgba(0,0,0,.4);
	}
	.rbfw-native-modal__submit:focus-visible{outline:2px solid var(--rbfw_color_primary,#ff3726);outline-offset:2px;}
	.rbfw-native-modal__submit:active{transform:translateY(1px);box-shadow:0 4px 10px -4px rgba(0,0,0,.35);}
	.rbfw-native-modal__submit:disabled{cursor:default;}
	.rbfw-native-modal__spinner{display:none;width:16px;height:16px;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:rbfw-native-spin .6s linear infinite;}
	.rbfw-native-modal__submit.is-loading{background:var(--rbfw_single_page_secondary_color,#333);}
	.rbfw-native-modal__submit.is-loading .rbfw-native-modal__spinner{display:inline-block;}
	.rbfw-native-modal__submit.is-loading .rbfw-native-modal__submit-icon{display:none;}
	@keyframes rbfw-native-spin{to{transform:rotate(360deg);}}
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

		<?php
		// Login gate (standalone mode): when login is required and the visitor is a guest,
		// show the inline Login / Register panel (Pro) instead of the booking form. On
		// success its JS swaps this auth panel for the real fields in place (see
		// RBFW_Native_Checkout::render_fields_ajax()) — it never reloads the page, so the
		// item/quantity already selected in the surrounding booking form is preserved.
		$rbfw_login_gate = function_exists( 'rbfw_login_required' ) && rbfw_login_required() && ! is_user_logged_in();
		if ( $rbfw_login_gate ) :
			?>
			<div class="rbfw-native-modal__auth">
				<?php
				/** Pro renders the inline login/register panel here. */
				do_action( 'rbfw_native_checkout_auth_panel', get_queried_object_id() );
				if ( ! has_action( 'rbfw_native_checkout_auth_panel' ) ) {
					printf(
						'<p style="text-align:center;">%s</p>',
						wp_kses_post( sprintf(
							/* translators: %s: login URL */
							__( 'Please <a href="%s">log in</a> to complete your booking.', 'booking-and-rental-manager-for-woocommerce' ),
							esc_url( wp_login_url( get_permalink() ) )
						) )
					);
				}
				?>
			</div>
		<?php else :
			$rbfw_fields_template = RBFW_Function::get_template_path( 'layout/native_checkout_fields.php' );
			if ( $rbfw_fields_template && file_exists( $rbfw_fields_template ) ) {
				include $rbfw_fields_template;
			}
		?>

		<div class="rbfw-native-modal__message" data-rbfw-native-message aria-live="polite"></div>

		<button type="button" class="rbfw-native-modal__submit" data-rbfw-native-submit>
			<span class="dashicons dashicons-yes-alt rbfw-native-modal__submit-icon" aria-hidden="true"></span>
			<span class="rbfw-native-modal__spinner" aria-hidden="true"></span>
			<span><?php echo esc_html__( 'Confirm booking', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
		</button>
		<p class="rbfw-native-modal__note"><?php echo esc_html__( 'Payment will be arranged after your booking is received.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
		<?php endif; ?>
	</div>
</div>
