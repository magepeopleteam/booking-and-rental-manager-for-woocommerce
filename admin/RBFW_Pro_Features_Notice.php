<?php
/**
 * "Buy Pro Version" row + its Pro feature list, inside the modern rental-item editor's
 * "Resources & Addons" sidebar card.
 *
 * History: this was a wp-admin-wide `admin_notices` banner that fired on every plugin
 * screen AND the main Plugins list, with a "Maybe later" dismissal. The taxi plugin has
 * no such banner — its Pro upsell is a card on the edit screen only
 * (MPTBM_Right_Side_Content_Settings::mptbm_right_pro_features_card()) — so this moved
 * into the editor sidebar to match. It then lived as its own card, which duplicated the
 * "Buy Pro Version" link already in Resources & Addons; both are now this single row.
 *
 * The row replaces that previously hard-coded link (same label, same destination) and
 * expands on click to reveal the feature list, so nothing in the sidebar is listed twice.
 * Being non-intrusive it needs no dismiss control, same as the taxi plugin's card. The
 * old `rbfw_pro_promo_dismissed` option is simply no longer read; nothing is deleted, so
 * downgrading restores the previous behaviour intact.
 *
 * The highlighted features mirror the Get PRO page categories — Offline is deliberately
 * excluded because it is a free payment method (see RBFW_Function::offline_payment_enabled()).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Pro_Features_Notice' ) ) {

	class RBFW_Pro_Features_Notice {

		/** Where "Buy Pro Version" goes — unchanged from the row this replaces. */
		const PRO_URL = 'https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-pro/';

		public function __construct() {
			add_action( 'rbfw_modern_editor_pro_links', array( $this, 'render' ) );
		}

		private function is_pro() {
			return function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active();
		}

		/**
		 * The Pro-only highlights (label + core Dashicon), mirroring the Get PRO page
		 * feature categories. Offline is a free method, so it is not listed here.
		 */
		private function features() {
			return array(
				array( 'icon' => 'cart',               'label' => __( 'PayPal & Stripe checkout', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'calendar-alt',       'label' => __( 'Booking calendar & orders dashboard', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'car',                'label' => __( 'Delivery & collection by distance', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'media-document',     'label' => __( 'Branded PDF & POS receipts', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'welcome-write-blog', 'label' => __( 'Drag-and-drop booking forms', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'star-filled',        'label' => __( 'Reviews & ratings', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'chart-bar',          'label' => __( 'Reports & CSV export', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'share',              'label' => __( 'Google Calendar sync', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'icon' => 'email-alt',          'label' => __( 'Editable notification emails', 'booking-and-rental-manager-for-woocommerce' ) ),
			);
		}

		/**
		 * @param int $post_id Unused; the upsell is site-wide, not per item.
		 */
		public function render( $post_id = 0 ) {
			unset( $post_id );

			// Nothing to upsell once Pro is active — the row disappears with its list,
			// leaving the addon links below it untouched.
			if ( $this->is_pro() ) {
				return;
			}

			$this->print_styles();
			?>
			<div class="rbfw-me-pro-row">
				<?php
					// A button, not a link: its job is to disclose the list. The real
					// navigation is the CTA at the end of that list, pointing at the same
					// URL the plain link used to.
				?>
				<button type="button" class="rbfw-me-help-link rbfw-me-help-link--pro rbfw-me-pro-toggle" aria-expanded="false" aria-controls="rbfw-me-pro-features">
					<span class="rbfw-me-help-link__icon dashicons dashicons-star-filled"></span>
					<span class="rbfw-me-help-link__text">
						<strong><?php esc_html_e( 'Buy Pro Version', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
						<span><?php esc_html_e( 'Unlock all premium features', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</span>
					<span class="dashicons dashicons-arrow-right-alt2 rbfw-me-help-link__arrow"></span>
				</button>

				<div class="rbfw-me-pro-features" id="rbfw-me-pro-features" hidden>
					<ul class="rbfw-me-pro-features__list">
						<?php foreach ( $this->features() as $f ) : ?>
							<li>
								<span class="dashicons dashicons-<?php echo esc_attr( $f['icon'] ); ?>" aria-hidden="true"></span>
								<?php echo esc_html( $f['label'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<a class="rbfw-me-pro-features__cta" href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank" rel="noopener">
						<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
						<?php esc_html_e( 'Upgrade to Pro', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</a>
				</div>
			</div>
			<?php
			// Printed in the footer, not inline here: the editor enqueues jQuery as a
			// footer dependency, so a <script> in the middle of the body can't assume
			// jQuery exists yet. Registered from render() so the script only ships on
			// requests that actually drew the row.
			add_action( 'admin_footer', array( $this, 'print_script' ) );
		}

		/** Disclosure behaviour for the Pro row. */
		public function print_script() {
			?>
			<script>
			jQuery(function($){
				$(document).on('click', '.rbfw-me-pro-toggle', function(){
					var $btn   = $(this);
					var open   = $btn.attr('aria-expanded') !== 'true';
					var $panel = $btn.siblings('.rbfw-me-pro-features');

					$btn.attr('aria-expanded', open ? 'true' : 'false');

					// prop(), not attr(): `hidden` is a boolean attribute. It must come
					// off BEFORE the slide, and the panel has to actually be display:none
					// for slideDown to animate rather than snap — hence the .hide().
					if (open) {
						$panel.prop('hidden', false).hide().stop(true, true).slideDown(160);
					} else {
						$panel.stop(true, true).slideUp(160, function(){
							$(this).prop('hidden', true);
						});
					}
				});
			});
			</script>
			<?php
		}

		/** Row + list styling, matching the surrounding .rbfw-me-help-link rows. Printed once. */
		private function print_styles() {
			static $printed = false;
			if ( $printed ) {
				return;
			}
			$printed = true;
			?>
			<style id="rbfw-me-pro-row-styles">
				/* The sibling rows are <a>; neutralise the button chrome so this one
				   sits in the list as if it were another link. */
				.rbfw-me-pro-toggle{width:100%;box-sizing:border-box;background:none;border:none;border-left:3px solid transparent;font:inherit;text-align:left;cursor:pointer;padding-top:0;padding-bottom:0;}
				.rbfw-me-pro-toggle:focus-visible{outline:2px solid var(--me-primary,#1a56db);outline-offset:-2px;}
				/* The arrow is hover-only on the plain rows; while this one is open it
				   must stay visible, and it turns to point down. */
				.rbfw-me-pro-toggle .rbfw-me-help-link__arrow{transition:opacity .12s,transform .16s;}
				.rbfw-me-pro-toggle[aria-expanded="true"]{background:#f0f6fc;border-left-color:var(--me-primary,#1a56db);}
				.rbfw-me-pro-toggle[aria-expanded="true"] .rbfw-me-help-link__arrow{opacity:1;transform:rotate(90deg);}

				.rbfw-me-pro-features{padding:4px 14px 12px 30px;}
				.rbfw-me-pro-features__list{list-style:none;margin:0 0 10px;padding:0;display:flex;flex-direction:column;gap:6px;}
				.rbfw-me-pro-features__list li{display:flex;align-items:flex-start;gap:8px;font-size:11.5px;color:var(--me-text-secondary,#334155);line-height:1.45;}
				.rbfw-me-pro-features__list .dashicons{flex:0 0 auto;font-size:14px;width:14px;height:14px;line-height:1.3;color:#F12971;}
				.rbfw-me-pro-features__cta{display:flex;align-items:center;justify-content:center;gap:6px;padding:7px 12px;border-radius:8px;background:linear-gradient(135deg,#F12971 0%,#7b2ff7 100%);color:#fff !important;font-size:12px;font-weight:700;text-decoration:none;box-shadow:0 3px 10px rgba(241,41,113,.26);transition:transform .15s ease,box-shadow .15s ease;}
				.rbfw-me-pro-features__cta:hover{transform:translateY(-1px);box-shadow:0 7px 16px rgba(241,41,113,.32);color:#fff !important;text-decoration:none;}
				.rbfw-me-pro-features__cta .dashicons{font-size:14px;width:14px;height:14px;line-height:1;}
			</style>
			<?php
		}
	}

	new RBFW_Pro_Features_Notice();
}
