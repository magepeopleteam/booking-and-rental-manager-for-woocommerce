<?php
/**
 * Frontend coupon field.
 *
 * Placement differs by mode, because the two modes have different "carts":
 *  - Standalone: one booking = one item, so the field lives INSIDE the booking form. It is
 *    rendered on the shared `rbfw_ticket_feature_info` hook (present inside the <form> of all
 *    four registration templates, just after the totals) — no template edits required, so theme
 *    overrides keep working.
 *  - WooCommerce: a real multi-item cart exists, so the field lives on the cart/checkout page
 *    where the coupon can be validated against actual cart contents.
 *
 * The field NEVER rewrites the `.total` element: rbfw_native_checkout.js reads that element and
 * posts it as `rbfw_total`, so discounting it here would make the server subtract twice (and the
 * pricing scripts rewrite it on every input change anyway). The discount is shown in its own
 * summary row instead.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_Frontend' ) ) {
	class RBFW_Coupon_Frontend {

		public function __construct() {
			// Coupon entry points, by mode:
			//  - Standalone: the field lives INSIDE the native checkout modal (templates/layout/
			//    native_checkout_modal.php), right by the Total + Confirm, handled by
			//    rbfw_native_checkout.js. We deliberately do NOT also render it on the item-page
			//    booking form — two fields would double-submit the code.
			//  - WooCommerce: the native WC coupon field (classic cart/checkout AND the Blocks)
			//    accepts our codes via RBFW_Coupon_WC's virtual-coupon bridge.

			// Keep the "any automatic rules?" cache honest whenever a coupon changes.
			add_action( 'save_post_' . RBFW_Coupon_Post_Type::POST_TYPE, array( 'RBFW_Coupon', 'flush_auto_cache' ) );
			add_action( 'deleted_post', array( 'RBFW_Coupon', 'flush_auto_cache' ), 10, 2 );
		}

		/** @deprecated internal alias — the cache lives on RBFW_Coupon. */
		public static function flush_auto_cache() {
			RBFW_Coupon::flush_auto_cache();
		}

		public static function enabled() {
			return class_exists( 'RBFW_Coupon_Engine' )
				&& RBFW_Coupon_Engine::is_enabled()
				&& 'no' !== RBFW_Coupon_Engine::setting( 'rbfw_coupon_show_field', 'yes' );
		}

		/** Are there any active automatic rules? Lets the JS skip a needless preview request. */
		public static function has_auto_coupons() {
			return RBFW_Coupon::has_auto_coupons();
		}

		private function label() {
			return RBFW_Coupon_Engine::setting( 'rbfw_coupon_label', __( 'Have a coupon?', 'booking-and-rental-manager-for-woocommerce' ) );
		}

		private function placeholder() {
			return RBFW_Coupon_Engine::setting( 'rbfw_coupon_placeholder', __( 'Enter coupon code', 'booking-and-rental-manager-for-woocommerce' ) );
		}

		/** Standalone: inside the booking form. */
		public function render_form_field() {
			if ( ! self::enabled() || RBFW_Function::use_wc() ) {
				return;
			}
			$this->markup( 'native' );
		}

		/** WooCommerce: on the cart / checkout page (a real cart exists there). */
		public function render_wc_field() {
			if ( ! self::enabled() || ! RBFW_Function::use_wc() ) {
				return;
			}
			if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
				return;
			}
			$this->markup( 'wc' );
		}

		/**
		 * The field itself. The submit button is type="button" so it can never submit the
		 * surrounding booking form; only the hidden input carries the code into the POST.
		 */
		private function markup( $mode ) {
			$applied_code = '';
			if ( 'wc' === $mode && class_exists( 'RBFW_Coupon_WC' ) ) {
				// Static read — constructing RBFW_Coupon_WC here would re-register its hooks.
				$applied_code = RBFW_Coupon_WC::get_session_code();
			}
			?>
			<div class="rbfw-coupon" data-rbfw-coupon data-rbfw-coupon-mode="<?php echo esc_attr( $mode ); ?>">
				<label class="rbfw-coupon__label" for="rbfw-coupon-input-<?php echo esc_attr( $mode ); ?>">
					<?php echo esc_html( $this->label() ); ?>
				</label>
				<div class="rbfw-coupon__row">
					<input type="text"
						   id="rbfw-coupon-input-<?php echo esc_attr( $mode ); ?>"
						   class="rbfw-coupon__input"
						   autocomplete="off"
						   value="<?php echo esc_attr( $applied_code ); ?>"
						   placeholder="<?php echo esc_attr( $this->placeholder() ); ?>">
					<button type="button" class="rbfw-coupon__apply">
						<?php esc_html_e( 'Apply', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>
				</div>

				<?php // Serialized with the booking form in standalone mode. ?>
				<input type="hidden" name="rbfw_coupon_code" class="rbfw-coupon__code" value="<?php echo esc_attr( $applied_code ); ?>">

				<div class="rbfw-coupon__msg" role="status" aria-live="polite"></div>

				<div class="rbfw-coupon__summary"<?php echo $applied_code ? '' : ' hidden'; ?>>
					<span class="rbfw-coupon__applied"></span>
					<button type="button" class="rbfw-coupon__remove">
						<?php esc_html_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>
				</div>
			</div>
			<?php
		}
	}

	new RBFW_Coupon_Frontend();
}
