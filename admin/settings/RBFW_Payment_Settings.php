<?php
	/**
	 * Payment settings tab for the Rental global settings page.
	 *
	 * Replicates the Event plugin (mage-eventpress) "Payment" settings panel
	 * (admin/settings/global/admin_setting_panel.php) adapted to the rbfw_/RBFW_
	 * naming convention and the rental plugin's WeDevs Settings API filter pattern.
	 *
	 * - Registers a new "Payments" tab via rbfw_settings_sec_reg.
	 * - Adds the sub-tabbed UI (WooCommerce / Custom Payment), WooCommerce fields,
	 *   and the PayPal / Stripe / Offline gateway cards via rbfw_settings_field.
	 * - Injects the gateway Configure modals + the WooCommerce install/activate
	 *   modal + the tab-switching script on admin_footer (raw HTML, so the SVG /
	 *   button / input markup is not stripped by the html field's wp_kses pass).
	 *
	 * Gateway credentials are stored in the rbfw_payment_settings option and are
	 * saved in real time over AJAX from their own modals, so they are protected
	 * from being wiped when the Settings API saves the rest of the form.
	 *
	 * PayPal & Stripe Configure are gated behind the Pro plugin (rbfw_check_pro_active);
	 * the free version shows a PRO badge. Offline payment is fully functional in free.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'RBFW_Payment_Settings' ) ) :
		class RBFW_Payment_Settings {

			const OPTION  = 'rbfw_payment_settings';
			const SCREEN  = 'rbfw_item_page_rbfw_settings_page';

			public function __construct() {
				add_filter( 'rbfw_settings_sec_reg', array( $this, 'register_section' ), 15 );
				add_filter( 'rbfw_settings_field', array( $this, 'register_fields' ), 15 );

				add_action( 'admin_footer', array( $this, 'render_wc_warning_modal' ) );
				add_action( 'admin_footer', array( $this, 'render_gateway_modals' ) );
				add_action( 'admin_footer', array( $this, 'payment_tabs_script' ) );

				add_action( 'wp_ajax_rbfw_save_gateway_settings', array( $this, 'ajax_save_gateway_settings' ) );
				add_action( 'wp_ajax_rbfw_save_booking_mode', array( $this, 'ajax_save_booking_mode' ) );
				add_action( 'wp_ajax_rbfw_install_activate_wc', array( $this, 'ajax_install_activate_wc' ) );
				// Live "is a booking payable right now?" snapshot, so enabling a gateway
				// updates the editor in place instead of needing a page reload.
				add_action( 'wp_ajax_rbfw_payment_status', array( $this, 'ajax_payment_status' ) );

				// "Payment Method" status card in the modern rental-item editor sidebar,
				// plus the slim "no payment method configured" banner under its step bar.
				// Mirrors the taxi plugin (MPTBM_Payment_Settings), where the same pair
				// lives on the transportation edit screen.
				add_action( 'rbfw_modern_editor_sidebar_top', array( $this, 'render_payment_sidebar_card' ) );
				add_action( 'rbfw_modern_editor_notices', array( $this, 'render_editor_payment_notice' ) );

				// The popup those two open. Rendered in the footer — NOT inside the
				// editor markup — because RBFW_Modern_Editor's collectFormData() saves
				// every named control inside .rbfw-me-wrap as rental-item meta, and this
				// popup is full of booking-mode / gateway fields that must never go there.
				// Registered after payment_tabs_script() so its script (which owns
				// window.rbfwApplyPaymentMode on this screen) is the last one bound.
				add_action( 'admin_footer', array( $this, 'render_payment_config_modal' ), 11 );
				add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_editor_payment_assets' ) );

				// Gateway keys are managed by their own AJAX modals and never travel with
				// the settings form, so preserve them when the Settings API saves the rest.
				add_filter( 'pre_update_option_' . self::OPTION, array( $this, 'preserve_gateway_keys' ), 10, 2 );
			}

			/** Is this the rental settings screen? */
			private function is_settings_screen() {
				$screen = get_current_screen();
				return $screen && ( $screen->id === self::SCREEN || strpos( $screen->id, 'rbfw_settings_page' ) !== false );
			}

			/** Is this the modern rental-item editor page (admin.php?…&page=rbfw_modern_editor)? */
			private function is_modern_editor_screen() {
				if ( ! class_exists( 'RBFW_Modern_Editor' ) || ! is_admin() ) {
					return false;
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, no state change.
				$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

				return RBFW_Modern_Editor::PAGE_SLUG === $page;
			}

			/**
			 * The gateway Configure modals (render_gateway_modals()), the WooCommerce
			 * install modal (render_wc_warning_modal()) and the `.gateway-card` styling
			 * (payment_tabs_script()) are needed by the modern editor's Payment Method
			 * popup too, not just the real Payments tab.
			 */
			private function is_settings_or_editor_screen() {
				return $this->is_settings_screen() || $this->is_modern_editor_screen();
			}

			private function has_woo() {
				return function_exists( 'rbfw_has_woocommerce' ) ? rbfw_has_woocommerce() : class_exists( 'WooCommerce' );
			}

			private function is_pro() {
				return function_exists( 'rbfw_check_pro_active' ) ? rbfw_check_pro_active() : false;
			}

			private function opt( $key, $default = '' ) {
				$o = get_option( self::OPTION, array() );
				return isset( $o[ $key ] ) ? $o[ $key ] : $default;
			}

			/* ─────────────────────────────────────────────────────────────────
			 * Modern rental-item editor: Payment Method card, notice + popup
			 * ───────────────────────────────────────────────────────────────── */

			/** Shared status checker instance (hook-free, safe to build on demand). */
			private function checker() {
				static $checker = null;
				if ( null === $checker && class_exists( 'RBFW_Payment_Status_Checker' ) ) {
					$checker = new RBFW_Payment_Status_Checker();
				}
				return $checker;
			}

			/**
			 * Does the booking flow currently in effect have a payment method that can
			 * actually complete a booking? Fails OPEN when the checker is unavailable so
			 * a missing class can never nag the admin about a problem that may not exist.
			 */
			private function has_gateway_for_active_mode() {
				$checker = $this->checker();
				return $checker ? $checker->has_gateway_for_active_mode() : true;
			}

			/** The active booking flow: 'woocommerce' | 'standalone'. */
			private function active_mode() {
				if ( class_exists( 'RBFW_Function' ) ) {
					return RBFW_Function::booking_mode();
				}
				$checker = $this->checker();
				return $checker ? $checker->active_mode() : 'woocommerce';
			}

			/** Human-readable label for the active booking flow. */
			private function get_booking_mode_label() {
				return ( 'woocommerce' === $this->active_mode() )
					? __( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' )
					: __( 'Custom Payment', 'booking-and-rental-manager-for-woocommerce' );
			}

			/**
			 * Names of the payment method(s) enabled for the active booking flow.
			 *
			 * Read entirely through RBFW_Payment_Status_Checker — the same source
			 * has_gateway_for_active_mode() counts — so the card can never list a method
			 * while also warning that none is configured. In particular the Pro gateways
			 * come from the Pro-gated `rbfw_pro_enabled_payment_methods` filter rather
			 * than from raw enable toggles, which survive Pro being deactivated.
			 *
			 * @return string[]
			 */
			private function get_active_gateway_names() {
				$checker = $this->checker();
				if ( ! $checker ) {
					return array();
				}

				if ( 'woocommerce' === $this->active_mode() ) {
					$names = array();
					foreach ( $checker->get_enabled_woocommerce_gateways() as $gateway ) {
						if ( ! is_object( $gateway ) || ! method_exists( $gateway, 'get_title' ) ) {
							continue;
						}
						$title   = method_exists( $gateway, 'get_method_title' ) ? $gateway->get_method_title() : '';
						$names[] = $title ? $title : $gateway->get_title();
					}
					return $names;
				}

				// Standalone: any Pro gateway, plus the free Offline method.
				//
				// Pro's `rbfw_pro_enabled_payment_methods` payload is keyed by method id and
				// ALREADY carries an 'offline' entry (under the admin's own label) whenever
				// Offline is on — so only add the free plugin's own entry when Pro has not
				// contributed one, otherwise the card reads "Pay on Pickup, Offline Payment".
				$pro   = (array) $checker->get_enabled_pro_payment_methods();
				$names = array_values( array_map( 'strval', $pro ) );
				if ( $checker->offline_payment_enabled() && ! array_key_exists( 'offline', $pro ) ) {
					$label   = trim( (string) $this->opt( 'rbfw_offline_label', '' ) );
					$names[] = $label ? $label : __( 'Offline Payment', 'booking-and-rental-manager-for-woocommerce' );
				}
				return $names;
			}

			/**
			 * Compact "Payment Method" card for the modern editor sidebar — shows the
			 * live booking flow + enabled method(s) and opens the payment popup.
			 *
			 * @param int $post_id Unused; the payment setup is site-wide, not per item.
			 */
			public function render_payment_sidebar_card( $post_id = 0 ) {
				unset( $post_id );
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				$this->editor_payment_styles();

				$gateway_names = $this->get_active_gateway_names();
				$has_gateway   = $this->has_gateway_for_active_mode();
				?>
				<div class="rbfw-me-card rbfw-me-card--sidebar rbfw-me-payment-card<?php echo $has_gateway ? '' : ' is-warning'; ?>">
					<div class="rbfw-me-card__head">
						<h3>
							<span class="dashicons dashicons-money-alt" aria-hidden="true"></span>
							<?php esc_html_e( 'Payment Method', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</h3>
					</div>
					<div class="rbfw-me-card__body">
						<?php // data-field, not row order: the live refresh targets these by name. ?>
						<div class="rbfw-me-payment-row" data-field="mode">
							<span><?php esc_html_e( 'Active Method', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<strong><?php echo esc_html( $this->get_booking_mode_label() ); ?></strong>
						</div>
						<div class="rbfw-me-payment-row" data-field="gateway">
							<span><?php esc_html_e( 'Active Gateway', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<strong><?php echo esc_html( $gateway_names ? implode( ', ', $gateway_names ) : __( 'None', 'booking-and-rental-manager-for-woocommerce' ) ); ?></strong>
						</div>

						<?php
							// Both rows are always emitted, with the inactive one hidden: the
							// refresh after a gateway is enabled only toggles visibility, and
							// jQuery cannot show an element that was never rendered.
						?>
						<p class="rbfw-me-payment-link"<?php echo $gateway_names ? '' : ' style="display:none;"'; ?>>
							<a href="#" data-rbfw-payment-modal-open><?php esc_html_e( 'Payment Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
						</p>
						<p class="rbfw-me-payment-warning"<?php echo $has_gateway ? ' style="display:none;"' : ''; ?>>
							<a href="#" data-rbfw-payment-modal-open><?php esc_html_e( 'Configure payment method', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
						</p>
					</div>
				</div>
				<?php
			}

			/**
			 * Slim banner under the editor's step bar, shown only while the active
			 * booking flow has no usable payment method — the item being edited can't be
			 * booked until that is fixed. Silent once a method is live.
			 *
			 * @param int $post_id Unused; the payment setup is site-wide, not per item.
			 */
			public function render_editor_payment_notice( $post_id = 0 ) {
				unset( $post_id );
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				$this->editor_payment_styles();

				// Always emitted, hidden while payment is set up. Rendering it only when
				// broken would mean the banner could never appear (or disappear) in
				// response to the live refresh — jQuery cannot slide an element that is
				// not in the DOM, and a reload here would discard unsaved item edits.
				$hidden = $this->has_gateway_for_active_mode();
				?>
				<div class="rbfw-me-payment-notice" id="rbfw-me-payment-notice"<?php echo $hidden ? ' style="display:none;"' : ''; ?>>
					<span class="rbfw-me-payment-notice__icon" aria-hidden="true">
						<span class="dashicons dashicons-warning"></span>
					</span>
					<span class="rbfw-me-payment-notice__text">
						<?php esc_html_e( 'No payment method is currently configured.', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</span>
					<a href="#" class="rbfw-me-payment-notice-link" data-rbfw-payment-modal-open>
						<?php esc_html_e( 'Please configure a payment method to accept bookings.', 'booking-and-rental-manager-for-woocommerce' ); ?>
						<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
					</a>
				</div>
				<?php
			}

			/**
			 * The popup opened by the sidebar card / banner links — lets the admin flip
			 * the booking flow and enable a gateway without leaving the editor. Reuses
			 * the exact same self-contained, AJAX-saving pieces the real Payments tab
			 * uses (flow selector, WooCommerce Payment Methods manager, Custom Payment
			 * gateway cards + their Configure modals); nothing is re-implemented.
			 *
			 * Deliberately leaves out the Settings-API-only controls (Require Account
			 * Login, Booking Confirmation Page, WooCommerce Additional Settings), which
			 * only persist on the real form's submit — the popup links out for those.
			 */
			public function render_payment_config_modal() {
				if ( ! $this->is_modern_editor_screen() || ! current_user_can( 'manage_options' ) ) {
					return;
				}
				$this->editor_payment_styles();

				$is_wc        = ( 'woocommerce' === $this->active_mode() );
				$settings_url = admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_settings_page#rbfw_payment_settings' );
				$mode_labels  = array(
					'woocommerce' => __( 'WooCommerce Checkout', 'booking-and-rental-manager-for-woocommerce' ),
					'standalone'  => __( 'Custom Payment (Standalone)', 'booking-and-rental-manager-for-woocommerce' ),
				);
				?>
				<div class="rbfw-payment-modal" id="rbfw-payment-modal" style="display:none;">
					<div class="rbfw-payment-modal-box">
						<div class="rbfw-payment-modal-head">
							<h2><?php esc_html_e( 'Payment Method', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<button type="button" class="rbfw-payment-modal-close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
						</div>
						<div class="rbfw-payment-modal-body">
							<?php $this->render_mode_selector(); ?>

							<div class="rbfw-payment-modal-section" data-mode-section="woocommerce"<?php echo $is_wc ? '' : ' style="display:none;"'; ?>>
								<?php if ( $this->has_woo() ) : ?>
									<?php $this->render_wc_payment_manager(); ?>
								<?php else : ?>
									<?php // Without WooCommerce the manager renders nothing; say so rather than reveal a blank panel. The card above carries the Install & Activate button. ?>
									<p class="rbfw-payment-modal-empty">
										<?php esc_html_e( 'WooCommerce is not active, so there are no WooCommerce payment methods to configure yet. Use the Install & Activate button on the WooCommerce Checkout card above, then reopen this popup.', 'booking-and-rental-manager-for-woocommerce' ); ?>
									</p>
								<?php endif; ?>
							</div>
							<div class="rbfw-payment-modal-section" data-mode-section="standalone"<?php echo $is_wc ? ' style="display:none;"' : ''; ?>>
								<?php $this->render_gateway_cards_list(); ?>
							</div>

							<p class="rbfw-payment-modal-more">
								<a href="<?php echo esc_url( $settings_url ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'More payment settings (login requirement, confirmation page, checkout options)', 'booking-and-rental-manager-for-woocommerce' ); ?>
								</a>
							</p>
						</div>
					</div>
				</div>
				<script>
				jQuery(function($){
					var $modal = $('#rbfw-payment-modal');
					if (!$modal.length) { return; }
					// Out of any wrapper that could clip it (and out of .rbfw-me-wrap, whose
					// inputs the editor serializes into item meta on save).
					$modal.appendTo('body');

					var modeLabels = <?php echo wp_json_encode( $mode_labels ); ?>;

					function applyModalMode(mode){
						var isWc = (mode !== 'standalone');
						$modal.find('[data-mode-section="woocommerce"]').toggle(isWc);
						$modal.find('[data-mode-section="standalone"]').toggle(!isWc);

						// The selector's "You're configuring: <flow>" banner is normally kept in
						// sync by payment_tabs_script()'s applyModeVisibility(), which stands down
						// on this screen — so update it here or it keeps naming the old flow.
						var $ctx = $modal.find('.rbfw-bm-context');
						if ($ctx.length) {
							var key = isWc ? 'woocommerce' : 'standalone';
							$ctx.attr('data-mode', key);
							$ctx.find('.rbfw-bm-context-icon')
								.removeClass('dashicons-cart dashicons-money-alt')
								.addClass(isWc ? 'dashicons-cart' : 'dashicons-money-alt');
							$ctx.find('.rbfw-bm-context-mode').text(modeLabels[key]);
						}
					}

					// The flow selector persists the choice over AJAX and calls
					// window.rbfwApplyPaymentMode() to switch the view (including on a
					// rollback after a failed save). payment_tabs_script() only claims that
					// hook on the settings page, so chain onto whatever is already there.
					var prevApply = window.rbfwApplyPaymentMode;
					window.rbfwApplyPaymentMode = function(mode){
						if (typeof prevApply === 'function') { prevApply(mode); }
						applyModalMode(mode);
					};

					// Switch the view on click as well, so the section follows the card even
					// before the save round-trip resolves. Bound to EVERY card, including a
					// disabled one: the flow selector refuses to *save* an unavailable flow,
					// but revealing its settings is how the admin unlocks it (enable the free
					// Offline gateway, or install WooCommerce from the card's own CTA).
					// Without this the popup dead-ends on exactly the misconfigured sites the
					// banner is complaining about.
					$modal.on('click', '.rbfw-bm-card', function(){
						applyModalMode($(this).data('mode'));
					});

					$(document).on('click', '[data-rbfw-payment-modal-open]', function(e){
						e.preventDefault();
						$modal.css('display', 'flex');
					});
					$modal.on('click', '.rbfw-payment-modal-close', function(){ $modal.hide(); });
					$modal.on('click', function(e){
						if (e.target === this) { $modal.hide(); }
					});
					$(document).on('keydown', function(e){
						if ((e.key === 'Escape' || e.keyCode === 27) && $modal.is(':visible')) {
							$modal.hide();
						}
					});

					// ── Live refresh ────────────────────────────────────────────────
					// Enabling a gateway used to leave the card, the banner, the
					// no-gateway warning and the locked Custom Payment card all showing
					// their page-load state until the admin reloaded. Reloading is not an
					// option here (it would discard unsaved rental-item edits), so pull a
					// fresh snapshot and repaint in place. Fired by both save paths: the
					// custom gateway modals and the WooCommerce toggle.
					var statusNonce = <?php echo wp_json_encode( wp_create_nonce( 'rbfw_payment_status' ) ); ?>;

					$(document).on('rbfw:payment-updated', function(){
						$.post(ajaxurl, { action: 'rbfw_payment_status', nonce: statusNonce }).done(function(res){
							if (!res || !res.success || !res.data) { return; }
							var d = res.data;

							// Sidebar Payment Method card.
							var $card = $('.rbfw-me-payment-card');
							$card.toggleClass('is-warning', !d.has_gateway);
							$card.find('.rbfw-me-payment-row[data-field="mode"] strong').text(d.mode_label);
							$card.find('.rbfw-me-payment-row[data-field="gateway"] strong').text(d.gateway_names);
							$card.find('.rbfw-me-payment-link').toggle(!!d.has_names);
							$card.find('.rbfw-me-payment-warning').toggle(!d.has_gateway);

							// Banner under the step bar.
							var $notice = $('#rbfw-me-payment-notice');
							if ($notice.length) {
								if (d.has_gateway) { $notice.slideUp(160); }
								else if (!$notice.is(':visible')) { $notice.slideDown(160); }
							}

							// The popup's own "no gateway enabled" warning.
							var $slot = $modal.find('.rbfw-bm-gateway-warning-slot').empty();
							if (d.warning_text) {
								$slot.append(
									$('<div class="rbfw-bm-gateway-warning rbfw-blink-soft"><span class="dashicons dashicons-warning"></span><p></p></div>')
										.find('p').text(d.warning_text).end()
								);
							}

							// Enabling the free Offline gateway unlocks the Custom Payment
							// flow, so drop the disabled state and its "enable Offline
							// below" hint rather than leaving a card that can't be picked.
							if (d.custom_available) {
								var $std = $modal.find('.rbfw-bm-card[data-mode="standalone"]');
								$std.removeClass('is-disabled').removeAttr('aria-disabled');
								$std.find('input[type=radio]').prop('disabled', false);
								$std.find('.rbfw-bm-card-cta--hint').remove();
							}
						});
					});
				});
				</script>
				<?php
			}

			/**
			 * Current payment state as JSON, for repainting server-rendered UI after a
			 * gateway is enabled/disabled without reloading the page (which on the editor
			 * would throw away the admin's unsaved rental-item edits).
			 *
			 * Everything here comes from the same helpers that rendered the markup in the
			 * first place, so the refreshed view can't drift from a freshly loaded one.
			 */
			public function ajax_payment_status() {
				check_ajax_referer( 'rbfw_payment_status', 'nonce' );
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( __( 'Permission denied.', 'booking-and-rental-manager-for-woocommerce' ), 403 );
				}

				$names       = $this->get_active_gateway_names();
				$has_gateway = $this->has_gateway_for_active_mode();
				$is_wc       = ( 'woocommerce' === $this->active_mode() );

				$warning = '';
				if ( ! $has_gateway ) {
					$warning = $is_wc
						? __( 'WooCommerce mode is selected, but no WooCommerce payment gateway is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' )
						: __( 'Custom Payment mode is selected, but no gateway (PayPal, Stripe, or Offline) is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' );
				}

				wp_send_json_success(
					array(
						'mode'          => $this->active_mode(),
						'mode_label'    => $this->get_booking_mode_label(),
						'has_gateway'   => (bool) $has_gateway,
						'has_names'     => ! empty( $names ),
						'gateway_names' => $names ? implode( ', ', $names ) : __( 'None', 'booking-and-rental-manager-for-woocommerce' ),
						'warning_text'  => $warning,
						// Whether the Custom Payment flow can be chosen at all — enabling the
						// free Offline gateway unlocks the card that was rendered disabled.
						'custom_available' => (bool) ( $this->is_pro() || ( class_exists( 'RBFW_Function' ) && RBFW_Function::offline_payment_enabled() ) ),
					)
				);
			}

			/**
			 * WooCommerce admin assets the popup's native gateway forms rely on, plus the
			 * dashicons the card/banner use. Only on the modern editor — the settings page
			 * already gets these from RBFW_WC_Payment_Manager::enqueue_assets().
			 */
			public function maybe_enqueue_editor_payment_assets() {
				if ( ! $this->is_modern_editor_screen() || ! current_user_can( 'manage_options' ) ) {
					return;
				}
				wp_enqueue_style( 'dashicons' );
				if ( $this->has_woo() ) {
					wp_enqueue_style( 'woocommerce_admin_styles' );
					wp_enqueue_script( 'wc-enhanced-select' );
					wp_enqueue_script( 'wc-jquery-tiptip' );
				}
			}

			/** Styles for the editor card / banner / popup. Printed once per request. */
			private function editor_payment_styles() {
				static $printed = false;
				if ( $printed ) {
					return;
				}
				$printed = true;
				?>
				<style id="rbfw-editor-payment-styles">
				/* Payment Method sidebar card — inherits .rbfw-me-card--sidebar chrome. */
				.rbfw-me-payment-card.is-warning{border-color:rgba(220,38,38,.30);}
				/* PALETTE — the editor's own tokens (see .rbfw-me-wrap in
				   admin/css/rbfw-modern-editor.css), never ad-hoc colours:
				     primary   --me-primary #1a56db / --me-primary-dk #1347b8 / --me-primary-soft #eef3ff
				     secondary --me-text-secondary #334155, --me-muted #64748b, --me-border-subtle #f1f5f9
				     alert     --me-danger #dc2626
				   Brand pink (#F12971) is deliberately NOT used here — rbfw_global_settings.css
				   reserves it for the Save button and the Pro card (same note as
				   booking_mode_styles()), so payment UI reading pink made it look like an
				   unrelated system.
				   Every var() carries a literal fallback because the popup is appended to
				   <body>, outside .rbfw-me-wrap, where these tokens do not resolve.
				   rgba() tints are hand-matched to #1a56db / #dc2626. */
				.rbfw-me-payment-card .rbfw-me-card__head h3{display:flex;align-items:center;gap:7px;}
				.rbfw-me-payment-card .rbfw-me-card__head .dashicons{font-size:17px;width:17px;height:17px;line-height:1;color:var(--me-primary,#1a56db);}
				.rbfw-me-payment-row{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px;padding:7px 0;}
				.rbfw-me-payment-row + .rbfw-me-payment-row{border-top:1px solid var(--me-border-subtle,#f1f5f9);}
				.rbfw-me-payment-row span{color:var(--me-muted,#64748b);font-weight:500;}
				.rbfw-me-payment-row strong{color:var(--me-text,#0f172a);font-weight:700;text-align:right;overflow-wrap:anywhere;}
				.rbfw-me-payment-link,.rbfw-me-payment-warning{margin:8px 0 0;padding-top:10px;border-top:1px solid var(--me-border-subtle,#f1f5f9);font-size:12.5px;}
				.rbfw-me-payment-link a{color:var(--me-primary,#1a56db);font-weight:600;text-decoration:none;}
				.rbfw-me-payment-warning a{color:var(--me-danger,#dc2626);font-weight:600;text-decoration:none;}
				.rbfw-me-payment-link a:hover,.rbfw-me-payment-warning a:hover{text-decoration:underline;}

				/* Slim "no payment method" banner under the step bar. Hidden along with the
				   rest of the editor while its loading skeleton is up, so it doesn't flash
				   on its own above an otherwise blank page.
				   Deliberately kept to the SAME footprint as the flat strip it replaces:
				   full bleed, ~38px tall. 8px block padding + a 22px tall control is the
				   budget — don't grow the icon chip or the CTA past that or the step bar
				   and the editor body below start getting pushed down.
				   Colour split: the chip is --me-danger because it flags a real blocker,
				   the CTA is --me-primary because it is the primary action on this screen. */
				.rbfw-me-wrap.is-loading .rbfw-me-payment-notice{opacity:0;pointer-events:none;}
				.rbfw-me-payment-notice{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:9px;width:100%;box-sizing:border-box;text-align:center;padding:8px 22px;margin:0;background:linear-gradient(90deg,var(--me-primary-soft,#eef3ff) 0%,#f6f9ff 100%);border-top:1px solid rgba(26,86,219,.16);border-bottom:1px solid rgba(26,86,219,.16);font-size:13px;font-weight:600;color:var(--me-text-secondary,#334155);line-height:1.4;}
				.rbfw-me-payment-notice__icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--me-danger,#dc2626);box-shadow:0 2px 6px rgba(220,38,38,.28);}
				.rbfw-me-payment-notice__icon .dashicons{font-size:13px;width:13px;height:13px;line-height:1;color:#fff;}
				.rbfw-me-payment-notice__text{color:var(--me-text-secondary,#334155);}
				.rbfw-me-payment-notice-link{display:inline-flex;align-items:center;gap:4px;padding:2px 12px;border-radius:20px;background:#fff;border:1px solid var(--me-primary,#1a56db);color:var(--me-primary,#1a56db);font-size:12.5px;font-weight:700;text-decoration:none;cursor:pointer;box-shadow:0 1px 2px rgba(15,23,42,.05);transition:background .15s ease,border-color .15s ease,color .15s ease,box-shadow .15s ease;}
				.rbfw-me-payment-notice-link .dashicons{font-size:13px;width:13px;height:13px;line-height:1;color:inherit;transition:transform .15s ease;}
				.rbfw-me-payment-notice-link:hover{background:var(--me-primary,#1a56db);border-color:var(--me-primary-dk,#1347b8);color:#fff;box-shadow:0 3px 10px rgba(26,86,219,.28);}
				.rbfw-me-payment-notice-link:hover .dashicons{transform:translateX(2px);}
				@media (prefers-reduced-motion:reduce){.rbfw-me-payment-notice-link,.rbfw-me-payment-notice-link .dashicons{transition:none;}.rbfw-me-payment-notice-link:hover .dashicons{transform:none;}}

				/* Payment Method popup. */
				.rbfw-payment-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:100001;align-items:center;justify-content:center;padding:20px;}
				.rbfw-payment-modal-box{background:#fff;border-radius:12px;max-width:860px;width:100%;max-height:88vh;overflow-y:auto;box-shadow:0 24px 60px rgba(15,23,42,.35);}
				.rbfw-payment-modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid var(--me-border,#e2e8f0);position:sticky;top:0;background:#fff;z-index:1;}
				.rbfw-payment-modal-head h2{margin:0;font-size:17px;font-weight:700;color:var(--me-text,#0f172a);}
				.rbfw-payment-modal-close{border:none;background:transparent;font-size:22px;line-height:1;cursor:pointer;color:var(--me-muted,#64748b);padding:4px 8px;}
				.rbfw-payment-modal-close:hover{color:var(--me-text,#0f172a);}
				.rbfw-payment-modal-body{padding:20px 24px 28px;}
				.rbfw-payment-modal-section{margin-top:16px;}
				/* The settings page's "How payments work here" strip and its step list are
				   page-level onboarding; inside a focused popup they only add height. */
				.rbfw-payment-modal .rbfw-pay-intro{display:none;}
				.rbfw-payment-modal-empty{margin:0;padding:14px 16px;border:1px dashed var(--me-border,#e2e8f0);border-radius:10px;background:#f9fafb;color:var(--me-muted,#64748b);font-size:12.5px;line-height:1.6;}
				.rbfw-payment-modal-more{margin:18px 0 0;padding-top:14px;border-top:1px solid var(--me-border,#e2e8f0);font-size:12.5px;}
				.rbfw-payment-modal-more a{color:var(--me-primary,#1a56db);font-weight:600;text-decoration:none;}
				.rbfw-payment-modal-more a:hover{text-decoration:underline;}
				@media (max-width:680px){.rbfw-payment-modal-box{max-height:92vh;}}
				</style>
				<?php
			}

			/** Add the "Payments" tab to the settings navigation. */
			public function register_section( $sections ) {
				$sections[] = array(
					'id'    => self::OPTION,
					'title' => '<i class="fas fa-credit-card"></i>' . esc_html__( 'Payments', 'booking-and-rental-manager-for-woocommerce' ),
				);

				return $sections;
			}

			/** Register the fields that make up the Payments tab. */
			public function register_fields( $settings_fields ) {
				$settings_fields[ self::OPTION ] = array(
					array(
						'name'     => 'rbfw_booking_mode_selector',
						'label'    => '',
						'callback' => array( $this, 'render_mode_selector' ),
					),
					array(
						'name'     => 'rbfw_wc_payment_gateways_manager',
						'label'    => '',
						'class'    => 'woocommerce-field wc-payment-methods-field',
						'callback' => array( $this, 'render_wc_payment_manager' ),
					),
					array(
						'name'    => 'rbfw_wc_add_to_cart_redirect',
						'label'   => __( 'After Adding to Cart, Redirect to', 'booking-and-rental-manager-for-woocommerce' ),
						'desc'    => __( 'Select where to redirect after adding an item to the cart.', 'booking-and-rental-manager-for-woocommerce' ),
						'type'    => 'select',
						'default' => 'checkout',
						'options' => array(
							'cart'     => __( 'Cart', 'booking-and-rental-manager-for-woocommerce' ),
							'checkout' => __( 'Checkout', 'booking-and-rental-manager-for-woocommerce' ),
						),
						'class'   => 'woocommerce-field wc-additional-field',
					),
					array(
						'name'    => 'rbfw_wc_require_login',
						'label'   => __( 'Require Account Login', 'booking-and-rental-manager-for-woocommerce' ),
						'desc'    => __( 'Require login to complete a booking.', 'booking-and-rental-manager-for-woocommerce' ),
						'type'    => 'checkbox',
						'default' => '',
						'class'   => 'woocommerce-field wc-additional-field',
					),
					array(
						'name'    => 'rbfw_wc_show_billing_info',
						'label'   => __( 'Show Billing Info', 'booking-and-rental-manager-for-woocommerce' ),
						'desc'    => __( 'Show billing info on the WooCommerce checkout page.', 'booking-and-rental-manager-for-woocommerce' ),
						'type'    => 'checkbox',
						'default' => '',
						'class'   => 'woocommerce-field wc-additional-field',
					),
					array(
						'name'    => 'rbfw_wc_confirm_status',
						'label'   => __( 'Confirm Booking Based on Payment Status', 'booking-and-rental-manager-for-woocommerce' ),
						'desc'    => __( 'Select the order statuses that will confirm a booking.', 'booking-and-rental-manager-for-woocommerce' ),
						'type'    => 'multicheck',
						'default' => array( 'processing' => 'processing', 'completed' => 'completed' ),
						'options' => array(
							'pending'    => __( 'Pending payment', 'booking-and-rental-manager-for-woocommerce' ),
							'processing' => __( 'Processing', 'booking-and-rental-manager-for-woocommerce' ),
							'on-hold'    => __( 'On hold', 'booking-and-rental-manager-for-woocommerce' ),
							'completed'  => __( 'Completed', 'booking-and-rental-manager-for-woocommerce' ),
						),
						'class'   => 'woocommerce-field wc-additional-field',
					),
					array(
						'name'     => 'rbfw_payment_gateways_ui',
						'label'    => '',
						'class'    => 'no-woocommerce-field payment-gateways-container',
						'callback' => array( $this, 'render_gateway_cards' ),
					),
				);

				return $settings_fields;
			}

			/* ============================================================== *
			 * Accounting payment methods (card / cheque / cash / transfer)
			 * ============================================================== */

			/**
			 * Sanitize the posted method rows.
			 *
			 * Slugs are carried on the row rather than regenerated from the label, so renaming
			 * "Cheque" to "Chèque" does not orphan every booking recorded against it. Two rows
			 * resolving to the same slug are made unique instead of silently overwriting.
			 *
			 * @param mixed $raw
			 * @return array
			 */
			public static function sanitize_payment_methods( $raw ) {
				if ( ! is_array( $raw ) ) {
					return array();
				}

				$out = array();
				foreach ( $raw as $row ) {
					if ( ! is_array( $row ) ) {
						continue;
					}

					$label = isset( $row['label'] ) ? sanitize_text_field( wp_unslash( $row['label'] ) ) : '';
					if ( '' === trim( $label ) ) {
						// Unlabelled methods cannot be shown or reconciled.
						continue;
					}

					$slug = isset( $row['slug'] ) ? sanitize_key( $row['slug'] ) : '';
					if ( '' === $slug ) {
						$slug = sanitize_key( sanitize_title( $label ) );
					}
					if ( '' === $slug ) {
						continue;
					}

					$base = $slug;
					$i    = 2;
					while ( isset( $out[ $slug ] ) ) {
						$slug = $base . '_' . $i;
						$i++;
					}

					$out[ $slug ] = array(
						'label'        => $label,
						'icon'         => isset( $row['icon'] ) ? sanitize_html_class( $row['icon'] ) : '',
						'instructions' => isset( $row['instructions'] ) ? wp_kses_post( wp_unslash( $row['instructions'] ) ) : '',
						'enabled'      => ! empty( $row['enabled'] ),
					);
				}

				return $out;
			}

			/**
			 * The offline payment types, rendered INSIDE the Offline gateway's Configure modal.
			 *
			 * This is where they belong: the Offline gateway is literally "the customer pays
			 * you directly", and card / cheque / cash / bank transfer are the ways that
			 * happens. Listing them anywhere else on this tab reads as a second, competing
			 * set of payment settings sitting next to the gateway that already describes them.
			 *
			 * The same list is what an admin can later record against ANY booking (including
			 * WooCommerce ones) and what the "Revenue by payment method" totals bucket into.
			 *
			 * Rows carry no `name` attributes — the modal saves over AJAX, not with the
			 * settings form, and the collector below reads them positionally.
			 *
			 * @return void
			 */
			public function render_offline_methods() {
				if ( ! function_exists( 'rbfw_payment_methods' ) ) {
					return;
				}
				$methods = rbfw_payment_methods();
				?>
				<div class="rbfw-gw-field rbfw-pm-repeater">
					<label class="rbfw-gw-label"><?php esc_html_e( 'Payment Types', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
					<p class="rbfw-pm-intro">
						<?php esc_html_e( 'The ways customers can pay you directly. Shown as choices at the standalone checkout, recordable against any booking afterwards (including WooCommerce ones, for your books), and the buckets the "Revenue by payment method" totals reconcile into.', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</p>

					<div class="rbfw-pm-body">
						<?php foreach ( $methods as $slug => $m ) : ?>
							<div class="rbfw-pm-row" data-slug="<?php echo esc_attr( $slug ); ?>">
								<label class="rbfw-pm-active" title="<?php esc_attr_e( 'Offer this at checkout', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									<input type="checkbox" class="rbfw-pm-enabled" <?php checked( ! empty( $m['enabled'] ) ); ?>>
								</label>
								<input type="text" class="rbfw-pm-label" value="<?php echo esc_attr( $m['label'] ); ?>" placeholder="<?php esc_attr_e( 'Label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
								<input type="text" class="rbfw-pm-instructions" value="<?php echo esc_attr( $m['instructions'] ); ?>" placeholder="<?php esc_attr_e( 'Instructions shown at checkout (optional)', 'booking-and-rental-manager-for-woocommerce' ); ?>">
								<button type="button" class="rbfw-pm-remove" aria-label="<?php esc_attr_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>

					<button type="button" class="rbfw-pm-add"><?php esc_html_e( '+ Add payment type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
					<p style="margin:10px 0 0;font-size:12px;color:#6b7280;">
						<?php esc_html_e( 'Removing a type here does not change bookings already recorded against it — those keep showing what they were paid with.', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</p>
				</div>

				<style>
					/* Scoped to the Offline modal so it cannot leak into the settings form. */
					.rbfw-gw-modal .rbfw-pm-intro {
						margin: 0 0 12px; font-size: 12px; color: #6b7280; line-height: 1.55;
					}
					.rbfw-gw-modal .rbfw-pm-row {
						display: grid;
						grid-template-columns: 26px minmax(0, 1fr) minmax(0, 1.4fr) 26px;
						align-items: center; gap: 8px; margin-bottom: 8px;
					}
					.rbfw-gw-modal .rbfw-pm-row input[type="text"] {
						width: 100%; min-width: 0; margin: 0;
					}
					.rbfw-gw-modal .rbfw-pm-active { display: flex; justify-content: center; margin: 0; }
					.rbfw-gw-modal .rbfw-pm-active input { margin: 0; }
					.rbfw-gw-modal .rbfw-pm-remove {
						background: none; border: 0; cursor: pointer; padding: 0;
						color: #b32d2e; font-size: 18px; line-height: 1;
					}
					.rbfw-gw-modal .rbfw-pm-remove:hover { color: #8a2424; }
					.rbfw-gw-modal .rbfw-pm-add {
						background: none; border: 1px dashed #cbd5e1; border-radius: 7px;
						padding: 7px 14px; cursor: pointer; font-size: 12.5px;
						color: #0f766e; font-weight: 600; width: 100%;
					}
					.rbfw-gw-modal .rbfw-pm-add:hover { border-color: #0f766e; background: #f0fdfa; }
					@media screen and (max-width: 600px) {
						.rbfw-gw-modal .rbfw-pm-row {
							grid-template-columns: 26px minmax(0, 1fr) 26px;
						}
						.rbfw-gw-modal .rbfw-pm-instructions { grid-column: 2 / 4; }
					}
				</style>
				<script>
				( function ( $ ) {
					$( document ).on( 'click', '.rbfw-gw-modal .rbfw-pm-add', function () {
						// A blank slug tells the server to derive one from the label, which is
						// what gives a brand new type a stable, readable key.
						$( this ).closest( '.rbfw-pm-repeater' ).find( '.rbfw-pm-body' ).append(
							'<div class="rbfw-pm-row" data-slug="">' +
								'<label class="rbfw-pm-active"><input type="checkbox" class="rbfw-pm-enabled" checked></label>' +
								'<input type="text" class="rbfw-pm-label" value="">' +
								'<input type="text" class="rbfw-pm-instructions" value="">' +
								'<button type="button" class="rbfw-pm-remove">&times;</button>' +
							'</div>'
						);
					} );
					$( document ).on( 'click', '.rbfw-gw-modal .rbfw-pm-remove', function () {
						$( this ).closest( '.rbfw-pm-row' ).remove();
					} );
				} )( jQuery );
				</script>
				<?php
			}

			/**
			 * The "Booking Mode" selector — now the SINGLE, self-explanatory switch that
			 * decides whether WooCommerce or the standalone Custom Payment flow processes
			 * bookings AND which settings show below it. (The old duplicate "sub-tab" pill
			 * bar that also toggled the two sides was removed — it looked identical to this
			 * switch and confused admins into thinking there were two separate choices.)
			 *
			 * It saves in real time over its own AJAX handler (never through the main form),
			 * so its radios are named rbfw_booking_mode_radio, NOT the option key — the real
			 * value is written by RBFW_Function::set_booking_mode(). When only one system is
			 * available the mode is auto-resolved, so this shows an explanatory note instead of
			 * a choice. Modelled on ecab-taxi-booking-manager's MPTBM_Payment_Settings.
			 */
			public function render_mode_selector() {
				if ( ! class_exists( 'RBFW_Function' ) ) {
					return;
				}
				$availability = RBFW_Function::mode_availability();
				$mode         = RBFW_Function::booking_mode();

				// A short, plain-language "how this works" strip shown in every state, so the
				// page reads as a guided setup rather than a wall of controls.
				$this->render_mode_intro( $mode, ( 'both' === $availability ) );

				// The two flow cards are ALWAYS shown as the modern switcher. A flow that
				// isn't available right now — WooCommerce inactive, or Custom Payment with
				// no gateway on a free/no-Pro site — renders DISABLED with a CTA to unlock
				// it, instead of collapsing the whole switcher to a plain note.
				$woo_available    = $this->has_woo();
				$custom_available = ( $this->is_pro() || RBFW_Function::offline_payment_enabled() );
				$is_wc            = ( 'woocommerce' === $mode );
				$is_custom        = ( 'standalone' === $mode );
				$checker          = class_exists( 'RBFW_Payment_Status_Checker' ) ? new RBFW_Payment_Status_Checker() : null;
				$has_gateway      = $checker ? $checker->has_gateway_for_active_mode() : true;
				?>
				<div class="rbfw-bm-wrap<?php echo ( 'both' === $availability ) ? '' : ' rbfw-bm-single'; ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'rbfw_save_booking_mode' ) ); ?>"<?php echo RBFW_Function::needs_mode_selection() ? ' data-unconfirmed="1"' : ''; ?>>
					<div class="rbfw-bm-head">
						<h3><?php esc_html_e( 'Step 1 — Choose your booking flow', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Pick exactly one flow to process bookings. This single switch decides everything below, so WooCommerce and Custom Payment never both try to handle the same booking. Your choice is saved instantly, and only the matching settings are shown.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>

					<div class="rbfw-bm-cards">
						<label class="rbfw-bm-card<?php echo $is_wc ? ' is-selected' : ''; echo $woo_available ? '' : ' is-disabled'; ?>" data-mode="woocommerce"<?php echo $woo_available ? '' : ' aria-disabled="true"'; ?>>
							<input type="radio" name="rbfw_booking_mode_radio" value="woocommerce" <?php checked( $is_wc ); ?> <?php disabled( ! $woo_available ); ?>>
							<span class="rbfw-bm-card-icon dashicons dashicons-cart"></span>
							<span class="rbfw-bm-card-body">
								<span class="rbfw-bm-card-title-row">
									<strong><?php esc_html_e( 'WooCommerce Checkout', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
									<span class="rbfw-bm-card-badge"><span class="rbfw-bm-dot rbfw-blink"></span><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								</span>
								<span class="rbfw-bm-card-desc"><?php esc_html_e( 'Bookings go through the WooCommerce cart, checkout, and orders.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<?php if ( ! $woo_available ) : ?>
									<span class="rbfw-bm-card-cta"><?php echo $this->wc_install_button(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts. ?></span>
								<?php endif; ?>
							</span>
						</label>
						<label class="rbfw-bm-card<?php echo $is_custom ? ' is-selected' : ''; echo $custom_available ? '' : ' is-disabled'; ?>" data-mode="standalone"<?php echo $custom_available ? '' : ' aria-disabled="true"'; ?>>
							<input type="radio" name="rbfw_booking_mode_radio" value="standalone" <?php checked( $is_custom ); ?> <?php disabled( ! $custom_available ); ?>>
							<span class="rbfw-bm-card-icon dashicons dashicons-money-alt"></span>
							<span class="rbfw-bm-card-body">
								<span class="rbfw-bm-card-title-row">
									<strong><?php esc_html_e( 'Custom Payment (Standalone)', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
									<span class="rbfw-bm-card-badge"><span class="rbfw-bm-dot rbfw-blink"></span><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								</span>
								<span class="rbfw-bm-card-desc"><?php esc_html_e( 'Bookings are taken directly via PayPal, Stripe, or Offline payment — no WooCommerce.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<?php if ( ! $custom_available ) : ?>
									<span class="rbfw-bm-card-cta rbfw-bm-card-cta--hint"><?php esc_html_e( 'Enable the free Offline gateway below (or upgrade to PRO for PayPal & Stripe).', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<?php endif; ?>
							</span>
						</label>
					</div>

					<div class="rbfw-bm-gateway-warning-slot">
						<?php if ( ! $has_gateway ) : ?>
							<div class="rbfw-bm-gateway-warning rbfw-blink-soft">
								<span class="dashicons dashicons-warning"></span>
								<p>
									<?php if ( $is_wc ) : ?>
										<?php esc_html_e( 'WooCommerce mode is selected, but no WooCommerce payment gateway is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' ); ?>
									<?php else : ?>
										<?php esc_html_e( 'Custom Payment mode is selected, but no gateway (PayPal, Stripe, or Offline) is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' ); ?>
									<?php endif; ?>
								</p>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php
				$this->render_mode_context_banner( $mode );
				$this->booking_mode_styles();
				?>
				<script>
				jQuery(function($){
					var $wrap = $('.rbfw-bm-wrap');
					if (!$wrap.length) { return; }
					var nonce = $wrap.data('nonce');
					var i18n = {
						savingTitle: <?php echo wp_json_encode( __( 'Switching booking flow…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						savingSub:   <?php echo wp_json_encode( __( 'Saving your choice, please wait.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						savedTitle:  <?php echo wp_json_encode( __( 'Booking flow saved', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						/* translators: %s: the selected booking flow name. */
						savedSub:    <?php echo wp_json_encode( __( 'Bookings now go through %s.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						errTitle:    <?php echo wp_json_encode( __( 'Couldn\'t save the change', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						errSub:      <?php echo wp_json_encode( __( 'Something went wrong — your previous booking flow was restored. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						dismiss:     <?php echo wp_json_encode( __( 'Dismiss', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						wcWarn: <?php echo wp_json_encode( __( 'WooCommerce mode is selected, but no WooCommerce payment gateway is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						customWarn: <?php echo wp_json_encode( __( 'Custom Payment mode is selected, but no gateway (PayPal, Stripe, or Offline) is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
					};

					// --- Toast notification: one reusable element, kept in the aria-live tree. ---
					var $toast = null, toastTimer = null;
					function ensureToast(){
						if ($toast) { return $toast; }
						$toast = $('<div class="rbfw-toast" role="status" aria-live="polite">'+
							'<span class="rbfw-toast-ico" aria-hidden="true"></span>'+
							'<div class="rbfw-toast-body"><span class="rbfw-toast-title"></span><span class="rbfw-toast-sub"></span></div>'+
							'<button type="button" class="rbfw-toast-x" aria-label="'+i18n.dismiss+'">&times;</button>'+
						'</div>').appendTo(document.body);
						$toast.on('click', '.rbfw-toast-x', hideToast);
						return $toast;
					}
					function showToast(state, title, sub){
						ensureToast();
						clearTimeout(toastTimer);
						var ico = (state === 'loading') ? '<span class="rbfw-spin"></span>'
							: (state === 'success') ? '<span class="dashicons dashicons-yes"></span>'
							: '<span class="dashicons dashicons-warning"></span>';
						$toast.removeClass('is-loading is-success is-error').addClass('is-'+state);
						$toast.find('.rbfw-toast-ico').html(ico);
						$toast.find('.rbfw-toast-title').text(title);
						$toast.find('.rbfw-toast-sub').text(sub || '');
						$toast.find('.rbfw-toast-x').toggle(state !== 'loading');
						// Force reflow so the entry transition always plays.
						void $toast[0].offsetWidth;
						$toast.addClass('is-visible');
						if (state !== 'loading') {
							toastTimer = setTimeout(hideToast, state === 'error' ? 6000 : 3400);
						}
					}
					function hideToast(){ if ($toast) { clearTimeout(toastTimer); $toast.removeClass('is-visible'); } }

					var saving = false;
					$wrap.on('click', '.rbfw-bm-card:not(.is-disabled)', function(){
						var $card = $(this), mode = $card.data('mode');
						// Clicking the already-selected card is normally a no-op — EXCEPT while no
						// choice has ever been stored (data-unconfirmed), when the card shown as
						// selected is only the auto-resolved default. Clicking it then IS the admin
						// confirming that default and must be persisted; otherwise the install stays
						// "mode never chosen" forever and keeps being prompted for it.
						if (saving || ($card.hasClass('is-selected') && !$wrap.data('unconfirmed'))) { return; }

						// Remember the current selection so we can roll back on failure.
						var $prev     = $wrap.find('.rbfw-bm-card.is-selected');
						var prevMode  = $prev.data('mode') || (mode === 'woocommerce' ? 'standalone' : 'woocommerce');
						var modeLabel = $.trim($card.find('.rbfw-bm-card-title-row strong').text());

						saving = true;
						$wrap.addClass('is-saving');

						// Optimistic switch: reflect the choice immediately (settings + banner).
						$wrap.find('.rbfw-bm-card').removeClass('is-selected');
						$card.addClass('is-selected').find('input[type=radio]').prop('checked', true);
						if (typeof window.rbfwApplyPaymentMode === 'function') { window.rbfwApplyPaymentMode(mode); }

						showToast('loading', i18n.savingTitle, i18n.savingSub);

						function rollback(){
							$wrap.find('.rbfw-bm-card').removeClass('is-selected');
							$prev.addClass('is-selected').find('input[type=radio]').prop('checked', true);
							if (typeof window.rbfwApplyPaymentMode === 'function') { window.rbfwApplyPaymentMode(prevMode); }
						}

						$.post(ajaxurl, { action:'rbfw_save_booking_mode', nonce:nonce, mode:mode })
							.done(function(res){
								if (res && res.success) {
									// The choice is stored now, so the selected card goes back to
									// being a no-op on click.
									$wrap.removeAttr('data-unconfirmed').removeData('unconfirmed');
									showToast('success', i18n.savedTitle, i18n.savedSub.replace('%s', modeLabel));

									// Refresh the "no gateway enabled" warning for the newly active mode.
									var $slot = $wrap.find('.rbfw-bm-gateway-warning-slot').empty();
									if (res.data && res.data.has_gateway === false) {
										var msg = (mode === 'woocommerce') ? i18n.wcWarn : i18n.customWarn;
										$slot.append('<div class="rbfw-bm-gateway-warning rbfw-blink-soft"><span class="dashicons dashicons-warning"></span><p>'+msg+'</p></div>');
									}
								} else {
									rollback();
									showToast('error', i18n.errTitle, (res && res.data) ? res.data : i18n.errSub);
								}
							})
							.fail(function(){
								rollback();
								showToast('error', i18n.errTitle, i18n.errSub);
							})
							.always(function(){
								saving = false;
								$wrap.removeClass('is-saving');
							});
					});
				});
				</script>
				<?php
			}

			/** Short, plain-language "how this works" strip printed above the mode chooser. */
			private function render_mode_intro( $mode, $has_choice ) {
				?>
				<div class="rbfw-pay-intro">
					<div class="rbfw-pay-intro-title">
						<span class="dashicons dashicons-info-outline"></span>
						<?php esc_html_e( 'How payments work here', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</div>
					<ol class="rbfw-pay-steps">
						<li><span class="rbfw-pay-step-n">1</span><?php echo $has_choice
							? esc_html__( 'Choose one booking flow below — WooCommerce Checkout or Custom Payment.', 'booking-and-rental-manager-for-woocommerce' )
							: esc_html__( 'Your booking flow is set automatically (only one is available right now).', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
						<li><span class="rbfw-pay-step-n">2</span><?php esc_html_e( 'Enable and configure the payment methods for that flow — only its settings are shown.', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
						<li><span class="rbfw-pay-step-n">3</span><?php esc_html_e( 'That\'s it — customers can now pay. You can switch flows anytime; the change is saved instantly.', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
					</ol>
				</div>
				<?php
			}

			/**
			 * The live "You're configuring: <flow>" banner that sits directly above the
			 * settings. It replaces the removed pill bar as the single, unmistakable label
			 * for which flow the settings below belong to; JS keeps it in sync on switch.
			 */
			private function render_mode_context_banner( $mode ) {
				$is_wc = ( 'woocommerce' === $mode );
				?>
				<div class="rbfw-bm-context" data-mode="<?php echo esc_attr( $mode ); ?>">
					<span class="rbfw-bm-context-dot rbfw-blink"></span>
					<span class="rbfw-bm-context-label"><?php esc_html_e( 'You\'re configuring:', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					<span class="rbfw-bm-context-icon dashicons <?php echo $is_wc ? 'dashicons-cart' : 'dashicons-money-alt'; ?>"></span>
					<span class="rbfw-bm-context-mode"><?php echo esc_html( $is_wc
						? __( 'WooCommerce Checkout', 'booking-and-rental-manager-for-woocommerce' )
						: __( 'Custom Payment (Standalone)', 'booking-and-rental-manager-for-woocommerce' ) ); ?></span>
				</div>
				<?php
			}

			/** Markup for the "Install / Activate WooCommerce" button (opens the footer modal). */
			private function wc_install_button() {
				$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
				$btn_text     = $is_installed
					? __( 'Activate WooCommerce Now', 'booking-and-rental-manager-for-woocommerce' )
					: __( 'Install &amp; Activate Now', 'booking-and-rental-manager-for-woocommerce' );
				return '<button type="button" class="button button-primary rbfw-install-wc-trigger" style="white-space:nowrap;">' . wp_kses_post( $btn_text ) . '</button>';
			}

			/** Styles for the Booking Mode selector + its auto-detected notices. Printed once. */
			private function booking_mode_styles() {
				static $printed = false;
				if ( $printed ) {
					return;
				}
				$printed = true;
				?>
				<style>
				/* This tab's accent follows the settings shell's --rbfw-gs-accent (the blue
				   used by the active nav tab, the page header and the doc links) so the
				   Payments content reads as part of the same page. The brand pink stays
				   reserved for what rbfw_global_settings.css reserves it for — the Save
				   button and the Pro card. The literal is only a fallback for contexts that
				   don't load that stylesheet; rgba() tints below are hand-matched to it. */
				:root{--rbfw-pay-accent:#2271B1;}
				.rbfw_global_settings{--rbfw-pay-accent:var(--rbfw-gs-accent,#2271B1);}
				/* Render the selector row full width. Its cell is spanned across both table
				   columns via JS (colspan=2); a display:block hack here would break that span
				   and let 2-column setting rows squeeze it into the narrow label column. */
				#rbfw_payment_settings tr.rbfw_booking_mode_selector > th{display:none;}
				#rbfw_payment_settings tr.rbfw_booking_mode_selector > td{padding-left:0 !important;}
				.rbfw-bm-wrap,.rbfw-bm-wrap *,.rbfw-bm-auto-note,.rbfw-bm-auto-note *{box-sizing:border-box;}
				.rbfw-bm-wrap{margin:2px 0 18px;max-width:100%;}
				.rbfw-bm-head h3{margin:0 0 2px;font-size:15px;font-weight:700;color:#1d2327;}
				.rbfw-bm-head p{margin:0 0 12px;font-size:12.5px;color:#6b7280;max-width:680px;line-height:1.55;}
				.rbfw-bm-cards{display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:100%;}
				.rbfw-bm-card{position:relative;display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border:1.5px solid #e5e7eb;border-radius:12px;background:#fafafb;cursor:pointer;transition:border-color .15s,box-shadow .15s,background .15s;min-width:0;}
				.rbfw-bm-card:hover{border-color:#a8c8e4;box-shadow:0 4px 14px rgba(16,24,40,0.06);}
				.rbfw-bm-card.is-selected{border-color:var(--rbfw-pay-accent);background:#fff;box-shadow:0 6px 18px rgba(34,113,177,0.12);}
				.rbfw-bm-card input[type=radio]{position:absolute;opacity:0;width:0;height:0;}
				.rbfw-bm-card-icon{flex:0 0 auto;width:36px;height:36px;border-radius:9px;background:rgba(34,113,177,0.1);color:var(--rbfw-pay-accent);display:flex !important;align-items:center !important;justify-content:center !important;font-size:18px;}
				.rbfw-bm-card-body{display:block !important;flex:1;min-width:0;white-space:normal !important;}
				.rbfw-bm-card-title-row{display:flex !important;align-items:center;justify-content:space-between;gap:8px;margin:0 0 4px;width:100%;}
				.rbfw-bm-card-body strong{display:inline-block !important;font-size:14px;line-height:1.3;color:#1d2327;}
				.rbfw-bm-card-desc{display:block !important;font-size:12px;color:#6b7280;line-height:1.5;overflow-wrap:break-word;}
				.rbfw-bm-card-badge{flex:0 0 auto;display:none !important;align-items:center;gap:5px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;background:#dcfce7;color:#166534;padding:2px 9px;border-radius:20px;}
				.rbfw-bm-card.is-selected .rbfw-bm-card-badge{display:inline-flex !important;}
				.rbfw-bm-dot{width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;flex:0 0 auto;}
				.rbfw-bm-wrap.is-saving .rbfw-bm-card:not(.is-disabled){cursor:progress;}
				/* Unavailable flow: shown but not selectable, with a CTA to unlock it. */
				.rbfw-bm-card.is-disabled{cursor:default;background:#f6f7f9;border-style:dashed;}
				.rbfw-bm-card.is-disabled:hover{border-color:#e5e7eb;box-shadow:none;}
				.rbfw-bm-card.is-disabled .rbfw-bm-card-icon{background:#eef0f3;color:#9ca3af;}
				.rbfw-bm-card.is-disabled .rbfw-bm-card-body strong{color:#6b7280;}
				.rbfw-bm-card-cta{display:block;margin-top:10px;}
				.rbfw-bm-card-cta .button{white-space:nowrap;}
				.rbfw-bm-card-cta--hint{font-size:11.5px;color:#9a3412;background:#fff7ed;border:1px solid #fed7aa;border-radius:7px;padding:6px 9px;line-height:1.45;}
				.rbfw-bm-gateway-warning{display:flex;align-items:flex-start;gap:8px;margin-top:10px;padding:9px 12px;border-radius:8px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:12px;}
				.rbfw-bm-gateway-warning p{margin:0;}
				.rbfw-bm-auto-note{display:flex;align-items:flex-start;gap:10px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a8a;border-radius:10px;padding:12px 16px;margin:4px 0 14px;font-size:12.5px;}
				.rbfw-bm-auto-note--warn{background:#fef2f2;border-color:#fecaca;color:#991b1b;}
				.rbfw-bm-auto-note .dashicons{flex:0 0 auto;}
				.rbfw-bm-auto-note p{margin:0 0 2px;line-height:1.55;}
				.rbfw-bm-auto-note-cta{margin-top:10px !important;}

				/* "How payments work here" intro strip */
				.rbfw-pay-intro{background:linear-gradient(135deg,#f4f9fd 0%,#fdfdff 100%);border:1px solid #cfe1f2;border-radius:12px;padding:14px 18px;margin:2px 0 16px;}
				.rbfw-pay-intro-title{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:700;color:#1E3A5F;margin-bottom:9px;}
				.rbfw-pay-intro-title .dashicons{color:var(--rbfw-pay-accent);}
				.rbfw-pay-steps{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
				.rbfw-pay-steps li{display:flex;align-items:flex-start;gap:9px;font-size:12.5px;color:#4b5563;line-height:1.5;}
				.rbfw-pay-step-n{flex:0 0 auto;width:22px;height:22px;border-radius:50%;background:var(--rbfw-pay-accent);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;}
				@media (max-width:782px){.rbfw-pay-steps{grid-template-columns:1fr;}}

				/* Live "You're configuring: <flow>" context banner (replaces the old pill bar) */
				.rbfw-bm-context{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:2px 0 4px;padding:11px 16px;border-radius:10px;background:#eff6fc;border:1px solid #c3ddf1;border-left:4px solid var(--rbfw-pay-accent);}
				.rbfw-bm-context-dot{width:9px;height:9px;border-radius:50%;background:var(--rbfw-pay-accent);flex:0 0 auto;box-shadow:0 0 0 4px rgba(34,113,177,0.18);}
				.rbfw-bm-context-label{font-size:12.5px;font-weight:600;color:#6b7280;}
				.rbfw-bm-context-icon{color:var(--rbfw-pay-accent);}
				.rbfw-bm-context-mode{font-size:14px;font-weight:700;color:#1E3A5F;}

				/* Attention "blink" — a gentle pulse, disabled for reduced-motion users */
				@keyframes rbfwBlink{0%,100%{opacity:1;}50%{opacity:.25;}}
				@keyframes rbfwBlinkSoft{0%,100%{box-shadow:0 0 0 0 rgba(234,88,12,0);}50%{box-shadow:0 0 0 3px rgba(234,88,12,0.18);}}
				.rbfw-blink{animation:rbfwBlink 1.1s ease-in-out infinite;}
				.rbfw-blink-soft{animation:rbfwBlinkSoft 1.4s ease-in-out infinite;}
				@media (prefers-reduced-motion:reduce){.rbfw-blink,.rbfw-blink-soft{animation:none !important;}}

				/* Toast notification (booking-flow switch → saving / saved / failed) */
				.rbfw-toast{position:fixed;top:52px;right:24px;z-index:100001;display:flex;align-items:flex-start;gap:12px;width:344px;max-width:calc(100vw - 32px);padding:14px 16px;background:#fff;border:1px solid #e5e7eb;border-left:4px solid #9ca3af;border-radius:12px;box-shadow:0 14px 38px rgba(16,24,40,0.20);pointer-events:none;opacity:0;transform:translateX(120%);transition:transform .34s cubic-bezier(.16,1,.3,1),opacity .34s;}
				.rbfw-toast.is-visible{opacity:1;transform:translateX(0);pointer-events:auto;}
				.rbfw-toast.is-loading{border-left-color:var(--rbfw-pay-accent);}
				.rbfw-toast.is-success{border-left-color:#16a34a;}
				.rbfw-toast.is-error{border-left-color:#dc2626;}
				.rbfw-toast-ico{flex:0 0 auto;width:24px;height:24px;display:flex;align-items:center;justify-content:center;margin-top:1px;}
				.rbfw-toast-ico .dashicons{font-size:22px;width:22px;height:22px;}
				.rbfw-toast.is-success .rbfw-toast-ico .dashicons{color:#16a34a;}
				.rbfw-toast.is-error .rbfw-toast-ico .dashicons{color:#dc2626;}
				.rbfw-toast-body{flex:1 1 auto;min-width:0;display:flex;flex-direction:column;gap:2px;}
				.rbfw-toast-title{font-size:13.5px;font-weight:700;color:#111827;line-height:1.35;}
				.rbfw-toast-sub{font-size:12px;color:#6b7280;line-height:1.45;overflow-wrap:break-word;}
				.rbfw-toast-x{flex:0 0 auto;background:none;border:none;cursor:pointer;font-size:18px;line-height:1;color:#9ca3af;padding:0 0 0 4px;}
				.rbfw-toast-x:hover{color:#4b5563;}
				.rbfw-spin{width:18px;height:18px;border:2px solid #d3e4f2;border-top-color:var(--rbfw-pay-accent);border-radius:50%;animation:rbfwSpin .7s linear infinite;}
				@keyframes rbfwSpin{to{transform:rotate(360deg);}}
				@media (prefers-reduced-motion:reduce){.rbfw-toast{transition:opacity .2s;transform:none;}.rbfw-spin{animation-duration:1.4s;}}

				@media (max-width:680px){.rbfw-bm-cards{grid-template-columns:1fr;}.rbfw-toast{right:12px;left:12px;width:auto;}}
				</style>
				<?php
			}

			/** PayPal / Stripe / Offline gateway cards + booking confirmation page. */
			public function render_gateway_cards() {
				$this->render_gateway_cards_list();

				$conf_page = absint( $this->opt( 'rbfw_confirmation_page_id', 0 ) );
				?>
				<!-- Booking Confirmation Page -->
				<?php $req_login = $this->opt( 'rbfw_require_login', 'on' ) !== 'off'; ?>
				<div class="rbfw-conf-page">
					<div class="rbfw-conf-page-label">
						<label><?php esc_html_e( 'Require Account Login', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php esc_html_e( 'Require customers to log in or register before booking. When on, guests see an inline Login / Register panel; when off, guest checkout is allowed and customers can track a booking with their email and reference.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<div class="rbfw-conf-page-field">
						<input type="hidden" name="<?php echo esc_attr( self::OPTION ); ?>[rbfw_require_login]" value="off">
						<label class="rbfw-gw-switch"><input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[rbfw_require_login]" value="on" <?php checked( $req_login ); ?>><span class="rbfw-gw-slider"></span></label>
					</div>
				</div>

				<div class="rbfw-conf-page">
					<div class="rbfw-conf-page-label">
						<label><?php esc_html_e( 'Booking Confirmation Page', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php esc_html_e( 'In Standalone / Custom Payment mode, customers are shown a confirmation after booking. Optionally choose a dedicated page here.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<div class="rbfw-conf-page-field">
						<?php
							wp_dropdown_pages( array(
								'name'              => self::OPTION . '[rbfw_confirmation_page_id]',
								'id'                => 'rbfw_confirmation_page_id',
								'selected'          => $conf_page,
								'show_option_none'  => __( '— Default —', 'booking-and-rental-manager-for-woocommerce' ),
								'option_none_value' => '0',
							) );
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * The PayPal / Stripe / Offline gateway cards on their own, without the
			 * Require Account Login / Booking Confirmation Page controls below them.
			 *
			 * Those two are classic Settings-API fields tied to the real Payments form
			 * and only save on its submit, so they are deliberately left out of the
			 * modern editor's Payment Method popup — which links out to the full tab
			 * for them instead. Shared by render_gateway_cards() above and
			 * render_payment_config_modal().
			 */
			public function render_gateway_cards_list() {
				$is_pro      = $this->is_pro();
				$pp_enabled  = $this->opt( 'rbfw_paypal_enable' ) === 'on';
				$st_enabled  = $this->opt( 'rbfw_stripe_enable' ) === 'on';
				$off_enabled = $this->opt( 'rbfw_offline_enable' ) === 'on';

				$enabled_txt  = __( 'Enabled', 'booking-and-rental-manager-for-woocommerce' );
				$disabled_txt = __( 'Disabled', 'booking-and-rental-manager-for-woocommerce' );
				$pro_badge    = '<span class="rbfw-gw-pro-badge" title="' . esc_attr__( 'Available in Pro version', 'booking-and-rental-manager-for-woocommerce' ) . '">PRO</span>';
				?>
				<div class="rbfw-gw-intro">
					<h3><?php esc_html_e( 'Custom Payment Gateways', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
					<p><?php esc_html_e( 'Accept payments directly without WooCommerce. Configure a gateway below, then enable it for the Standalone / Custom Payment checkout.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>

				<div class="rbfw-gw-grid">
					<!-- PayPal Card -->
					<div class="gateway-card paypal-card">
						<div class="gateway-top">
							<span class="gateway-icon">
								<svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z" fill="#fff"/>
								</svg>
							</span>
							<?php if ( $is_pro ) : ?>
								<span class="gateway-status <?php echo $pp_enabled ? 'active' : ''; ?>"><?php echo esc_html( $pp_enabled ? $enabled_txt : $disabled_txt ); ?></span>
							<?php else : ?>
								<?php echo wp_kses_post( $pro_badge ); ?>
							<?php endif; ?>
						</div>
						<span class="gateway-meta">
							<span class="gateway-name"><?php esc_html_e( 'PayPal', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="gateway-sub"><?php esc_html_e( 'Cards & PayPal balance', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</span>
						<?php if ( $is_pro ) : ?>
							<div class="gateway-actions">
								<button type="button" class="gateway-configure-btn" id="rbfw-paypal-configure-btn"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e( 'Configure', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							</div>
						<?php endif; ?>
					</div>

					<!-- Stripe Card -->
					<div class="gateway-card stripe-card">
						<div class="gateway-top">
							<span class="gateway-icon">
								<svg width="26" height="26" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
									<path fill="#fff" d="M14.07 15.11c-1.85-.43-2.61-.79-2.61-1.63 0-.79.75-1.33 1.95-1.33 1.34 0 2.87.41 4.31 1.09V8.65c-1.39-.56-2.93-.84-4.52-.84-3.8 0-6.66 1.96-6.66 5.25 0 3.73 3.32 4.96 6.03 5.61 2.05.49 2.8.92 2.8 1.8 0 .86-.87 1.48-2.3 1.48-1.57 0-3.37-.53-5.06-1.54v4.75c1.67.75 3.59 1.13 5.51 1.13 4.13 0 7-2 7-5.34-.01-3.6-3.6-4.41-6.45-5.84z"/>
								</svg>
							</span>
							<?php if ( $is_pro ) : ?>
								<span class="gateway-status <?php echo $st_enabled ? 'active' : ''; ?>"><?php echo esc_html( $st_enabled ? $enabled_txt : $disabled_txt ); ?></span>
							<?php else : ?>
								<?php echo wp_kses_post( $pro_badge ); ?>
							<?php endif; ?>
						</div>
						<span class="gateway-meta">
							<span class="gateway-name"><?php esc_html_e( 'Stripe', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="gateway-sub"><?php esc_html_e( 'Credit & debit cards', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</span>
						<?php if ( $is_pro ) : ?>
							<div class="gateway-actions">
								<button type="button" class="gateway-configure-btn" id="rbfw-stripe-configure-btn"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e( 'Configure', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							</div>
						<?php endif; ?>
					</div>

					<!-- Offline Payment Card -->
					<div class="gateway-card offline-card">
						<div class="gateway-top">
							<span class="gateway-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3 19h18a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/>
									<path d="M2 10h20M6 14h4" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
								</svg>
							</span>
							<?php // Offline Payment is FREE — always show its live status + Configure (PayPal/Stripe stay Pro-gated above). ?>
							<span class="gateway-status <?php echo $off_enabled ? 'active' : ''; ?>"><?php echo esc_html( $off_enabled ? $enabled_txt : $disabled_txt ); ?></span>
						</div>
						<span class="gateway-meta">
							<span class="gateway-name"><?php esc_html_e( 'Offline Payment', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							<span class="gateway-sub"><?php esc_html_e( 'Bank transfer, cash, pay on pickup', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</span>
						<div class="gateway-actions">
							<button type="button" class="gateway-configure-btn" id="rbfw-offline-configure-btn"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e( 'Configure', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</div>
					</div>
				</div>
				<?php
			}

			/** WooCommerce native payment-methods manager (inside the Payment Methods accordion). */
			public function render_wc_payment_manager() {
				if ( class_exists( 'WooCommerce' ) && class_exists( 'RBFW_WC_Payment_Manager' ) ) {
					RBFW_WC_Payment_Manager::instance()->render();
				}
			}

			/** WooCommerce install / activate modal (footer). */
			public function render_wc_warning_modal() {
				// Also needed on the modern editor: the Payment Method popup embeds the
				// same booking-flow selector, whose WooCommerce card carries the
				// `.rbfw-install-wc-trigger` button that opens this modal.
				if ( ! $this->is_settings_or_editor_screen() || $this->has_woo() ) {
					return;
				}
				$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
				$modal_desc   = $is_installed
					? __( 'WooCommerce is already installed but not active. Click the button below to activate it now.', 'booking-and-rental-manager-for-woocommerce' )
					: __( 'WooCommerce is required to process payments through the cart/checkout flow. We will securely download, install, and activate it for you now.', 'booking-and-rental-manager-for-woocommerce' );
				$modal_btn    = $is_installed
					? __( 'Activate WooCommerce Now', 'booking-and-rental-manager-for-woocommerce' )
					: __( 'Install &amp; Activate Now', 'booking-and-rental-manager-for-woocommerce' );
				?>
				<div id="rbfw-wc-install-modal" style="display:none;position:fixed;z-index:999999;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
					<div style="background:#fff;border-radius:12px;width:520px;max-width:92vw;box-shadow:0 10px 40px rgba(0,0,0,0.35);overflow:hidden;">
						<div style="padding:18px 24px;border-bottom:1px solid #e2e4e7;display:flex;justify-content:space-between;align-items:center;background:#f8f9fa;">
							<h3 style="margin:0;font-size:17px;color:#2c3338;display:flex;align-items:center;gap:8px;">
								<span class="dashicons dashicons-plugins-checked" style="font-size:20px;color:#2271b1;"></span>
								<?php esc_html_e( 'Set Up WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</h3>
							<button type="button" id="rbfw-wc-install-modal-close" style="background:none;border:none;font-size:24px;line-height:1;cursor:pointer;color:#666;padding:0;">&times;</button>
						</div>
						<div style="padding:24px;">
							<div id="rbfw-wc-modal-info">
								<p style="margin:0 0 18px;font-size:14px;color:#3c434a;line-height:1.6;"><?php echo esc_html( $modal_desc ); ?></p>
								<button type="button" id="rbfw-wc-modal-action-btn" class="button button-primary" style="white-space:nowrap;padding:6px 18px;"><?php echo wp_kses_post( $modal_btn ); ?></button>
							</div>
							<div id="rbfw-wc-modal-progress" style="display:none;">
								<div style="width:100%;height:8px;background:#f0f0f1;border-radius:100px;overflow:hidden;margin-bottom:10px;">
									<div id="rbfw-wc-modal-progress-fill" style="height:100%;width:0%;border-radius:100px;background:linear-gradient(90deg,#7b5ea7,#9b72cf);transition:width 0.5s cubic-bezier(0.16,1,0.3,1);"></div>
								</div>
								<p id="rbfw-wc-modal-status-text" style="font-size:13px;color:#50575e;margin:0;text-align:center;min-height:20px;"></p>
							</div>
						</div>
					</div>
				</div>
				<script>
				jQuery(function($){
					var rbfwWcIsInstalled = <?php echo $is_installed ? 'true' : 'false'; ?>;
					var rbfwWcNonce       = '<?php echo esc_js( wp_create_nonce( 'rbfw_install_wc' ) ); ?>';

					$(document).on('click', '.rbfw-install-wc-trigger', function(e){
						e.preventDefault();
						$('#rbfw-wc-install-modal').css('display','flex').hide().fadeIn(200);
					});
					$('#rbfw-wc-install-modal-close').on('click', function(){ $('#rbfw-wc-install-modal').fadeOut(200); });
					$(document).on('click', '#rbfw-wc-install-modal', function(e){
						if ($(e.target).is('#rbfw-wc-install-modal')) { $(this).fadeOut(200); }
					});

					$('#rbfw-wc-modal-action-btn').on('click', function(){
						var $info=$('#rbfw-wc-modal-info'), $progress=$('#rbfw-wc-modal-progress'),
						    $fill=$('#rbfw-wc-modal-progress-fill'), $status=$('#rbfw-wc-modal-status-text');
						$info.hide(); $fill.css('width','0%'); $progress.fadeIn(200);
						var texts = rbfwWcIsInstalled
							? [<?php echo implode( ',', array_map( 'wp_json_encode', array(
								__( 'Activating WooCommerce...', 'booking-and-rental-manager-for-woocommerce' ),
								__( 'Configuring settings...', 'booking-and-rental-manager-for-woocommerce' ),
								__( 'Finalizing setup...', 'booking-and-rental-manager-for-woocommerce' ),
							) ) ); ?>]
							: [<?php echo implode( ',', array_map( 'wp_json_encode', array(
								__( 'Downloading WooCommerce...', 'booking-and-rental-manager-for-woocommerce' ),
								__( 'Installing WooCommerce...', 'booking-and-rental-manager-for-woocommerce' ),
								__( 'Activating WooCommerce...', 'booking-and-rental-manager-for-woocommerce' ),
								__( 'Configuring settings...', 'booking-and-rental-manager-for-woocommerce' ),
								__( 'Finalizing...', 'booking-and-rental-manager-for-woocommerce' ),
							) ) ); ?>];
						var duration=rbfwWcIsInstalled?3000:15000, startTime=Date.now(), isDone=false, frameId;
						$status.text(texts[0]);
						function animateBar(){
							if(isDone) return;
							var raw=Math.min((Date.now()-startTime)/duration,1), pct=raw*(2-raw)*95;
							$fill.css('width',pct+'%');
							var idx=Math.min(Math.floor((pct/95)*texts.length),texts.length-1);
							$status.text(texts[idx]+' '+Math.round(pct)+'%');
							if(pct<95) frameId=requestAnimationFrame(animateBar);
						}
						frameId=requestAnimationFrame(animateBar);
						$.ajax({
							url: ajaxurl, type:'POST',
							data:{ action:'rbfw_install_activate_wc', nonce:rbfwWcNonce },
							success: function(response){
								var minWait=rbfwWcIsInstalled?1500:3000, leftover=Math.max(0,minWait-(Date.now()-startTime));
								setTimeout(function(){
									isDone=true; cancelAnimationFrame(frameId); $fill.css('width','100%');
									if(response.success){
										$status.css('color','#039855').text(<?php echo wp_json_encode( __( 'Successfully Activated! 100%', 'booking-and-rental-manager-for-woocommerce' ) ); ?>);
										setTimeout(function(){ location.reload(); }, 1200);
									} else {
										$status.css('color','#d92d20').text(<?php echo wp_json_encode( __( 'Error: ', 'booking-and-rental-manager-for-woocommerce' ) ); ?> + (response.data||'Unknown error'));
										setTimeout(function(){ $progress.hide(); $info.show(); }, 5000);
									}
								}, leftover);
							},
							error: function(){
								isDone=true; cancelAnimationFrame(frameId); $fill.css('width','100%');
								$status.css('color','#d92d20').text(<?php echo wp_json_encode( __( 'A network error occurred. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>);
								setTimeout(function(){ $progress.hide(); $info.show(); }, 5000);
							}
						});
					});
				});
				</script>
				<?php
			}

			/** PayPal / Stripe / Offline Configure modals (footer). Pro-only for PayPal/Stripe. */
			public function render_gateway_modals() {
				// Also needed on the modern editor: the Payment Method popup embeds the
				// same gateway cards (render_gateway_cards_list()), whose Configure
				// buttons open these very modals.
				if ( ! $this->is_settings_or_editor_screen() ) {
					return;
				}
				$pp_enabled  = $this->opt( 'rbfw_paypal_enable' ) === 'on';
				$pp_sandbox  = $this->opt( 'rbfw_paypal_sandbox' ) === 'on';
				$pp_client   = esc_attr( $this->opt( 'rbfw_paypal_client_id' ) );
				$pp_secret   = esc_attr( $this->opt( 'rbfw_paypal_secret' ) );
				$st_enabled  = $this->opt( 'rbfw_stripe_enable' ) === 'on';
				$st_sandbox  = $this->opt( 'rbfw_stripe_sandbox' ) === 'on';
				$st_test_pub = esc_attr( $this->opt( 'rbfw_stripe_test_pub' ) );
				$st_test_sec = esc_attr( $this->opt( 'rbfw_stripe_test_sec' ) );
				$st_live_pub = esc_attr( $this->opt( 'rbfw_stripe_live_pub' ) );
				$st_live_sec = esc_attr( $this->opt( 'rbfw_stripe_live_sec' ) );
				$off_enabled = $this->opt( 'rbfw_offline_enable' ) === 'on';
				$off_label   = esc_attr( $this->opt( 'rbfw_offline_label', __( 'Offline Payment', 'booking-and-rental-manager-for-woocommerce' ) ) );
				$nonce       = wp_create_nonce( 'rbfw_save_gateway' );
				$is_pro      = $this->is_pro();
				?>
				<style>
				.rbfw-gw-modal{display:none;position:fixed;inset:0;z-index:999999;background:rgba(10,10,30,0.65);align-items:center;justify-content:center;backdrop-filter:blur(3px);}
				.rbfw-gw-modal-box{background:#fff;border-radius:16px;width:540px;max-width:94vw;max-height:92vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,0.3);}
				.rbfw-gw-modal-header{padding:22px 26px;display:flex;align-items:center;justify-content:space-between;border-radius:16px 16px 0 0;}
				.rbfw-gw-modal-header h2{margin:0;font-size:19px;font-weight:700;color:#fff;display:flex;align-items:center;gap:12px;}
				.rbfw-gw-modal-close{background:rgba(255,255,255,0.2);border:none;border-radius:50%;width:34px;height:34px;font-size:20px;line-height:1;cursor:pointer;color:#fff;display:flex;align-items:center;justify-content:center;}
				.rbfw-gw-modal-body{padding:26px 26px 10px;}
				.rbfw-gw-field{margin-bottom:20px;}
				.rbfw-gw-field label.rbfw-gw-label{display:block;font-weight:600;font-size:13px;color:#374151;margin-bottom:7px;}
				.rbfw-gw-field input[type="text"],.rbfw-gw-field input[type="password"]{width:100%;padding:10px 14px;border:1.5px solid #d1d5db;border-radius:8px;font-size:14px;color:#111;background:#f9fafb;box-sizing:border-box;}
				.rbfw-gw-field input[type="text"]:focus,.rbfw-gw-field input[type="password"]:focus{border-color:var(--rbfw-pay-accent);box-shadow:0 0 0 3px rgba(34,113,177,0.12);outline:none;background:#fff;}
				.rbfw-gw-toggle-row{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:#f9fafb;border-radius:10px;margin-bottom:20px;border:1.5px solid #e5e7eb;}
				.rbfw-gw-toggle-label{font-weight:600;font-size:14px;color:#111827;}
				.rbfw-gw-toggle-sub{font-size:12px;color:#6b7280;margin-top:2px;}
				.rbfw-gw-divider{border:none;border-top:1px solid #e5e7eb;margin:4px 0 20px;}
				.rbfw-gw-section-title{font-size:12px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px;}
				.rbfw-gw-modal-footer{padding:16px 26px 22px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
				.rbfw-gw-save-btn{padding:11px 28px;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;color:#fff;flex-shrink:0;}
				.rbfw-gw-save-msg{display:none;padding:9px 14px;border-radius:7px;font-size:13px;font-weight:500;flex:1;}
				.rbfw-gw-switch{position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0;}
				.rbfw-gw-switch input{opacity:0;width:0;height:0;}
				.rbfw-gw-slider{position:absolute;cursor:pointer;inset:0;background:#d1d5db;border-radius:26px;transition:0.3s;}
				.rbfw-gw-slider:before{content:"";position:absolute;height:20px;width:20px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:0.3s;box-shadow:0 1px 3px rgba(0,0,0,0.2);}
				.rbfw-gw-switch input:checked + .rbfw-gw-slider{background:#22c55e;}
				.rbfw-gw-switch input:checked + .rbfw-gw-slider:before{transform:translateX(22px);}
				</style>

				<?php if ( $is_pro ) : ?>
				<!-- PayPal Config Modal -->
				<div id="rbfw-paypal-modal" class="rbfw-gw-modal">
					<div class="rbfw-gw-modal-box">
						<div class="rbfw-gw-modal-header" style="background:linear-gradient(135deg,#003087 0%,#0079C1 100%);">
							<h2><?php esc_html_e( 'PayPal Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<button type="button" class="rbfw-gw-modal-close">&times;</button>
						</div>
						<div class="rbfw-gw-modal-body">
							<div class="rbfw-gw-toggle-row">
								<div>
									<div class="rbfw-gw-toggle-label"><?php esc_html_e( 'Enable PayPal', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
									<div class="rbfw-gw-toggle-sub"><?php esc_html_e( 'Accept payments via PayPal', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
								</div>
								<label class="rbfw-gw-switch"><input type="checkbox" data-field="rbfw_paypal_enable" <?php checked( $pp_enabled ); ?>><span class="rbfw-gw-slider"></span></label>
							</div>
							<div class="rbfw-gw-toggle-row">
								<div>
									<div class="rbfw-gw-toggle-label"><?php esc_html_e( 'Sandbox / Test Mode', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
									<div class="rbfw-gw-toggle-sub"><?php esc_html_e( 'Use sandbox credentials for testing', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
								</div>
								<label class="rbfw-gw-switch"><input type="checkbox" data-field="rbfw_paypal_sandbox" <?php checked( $pp_sandbox ); ?>><span class="rbfw-gw-slider"></span></label>
							</div>
							<hr class="rbfw-gw-divider">
							<p class="rbfw-gw-section-title"><?php esc_html_e( 'API Credentials', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							<div class="rbfw-gw-field">
								<label class="rbfw-gw-label"><?php esc_html_e( 'PayPal Client ID', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="text" data-field="rbfw_paypal_client_id" value="<?php echo $pp_client; ?>" placeholder="<?php esc_attr_e( 'Enter your PayPal Client ID', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							</div>
							<div class="rbfw-gw-field">
								<label class="rbfw-gw-label"><?php esc_html_e( 'PayPal Secret Key', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="password" data-field="rbfw_paypal_secret" value="<?php echo $pp_secret; ?>" placeholder="<?php esc_attr_e( 'Enter your PayPal Secret Key', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							</div>
						</div>
						<div class="rbfw-gw-modal-footer">
							<button type="button" class="rbfw-gw-save-btn" data-gateway="paypal" style="background:linear-gradient(135deg,#003087,#0079C1);"><?php esc_html_e( 'Save PayPal Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<span class="rbfw-gw-save-msg"></span>
						</div>
					</div>
				</div>

				<!-- Stripe Config Modal -->
				<div id="rbfw-stripe-modal" class="rbfw-gw-modal">
					<div class="rbfw-gw-modal-box">
						<div class="rbfw-gw-modal-header" style="background:linear-gradient(135deg,#635bff 0%,#3f36c5 100%);">
							<h2><?php esc_html_e( 'Stripe Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<button type="button" class="rbfw-gw-modal-close">&times;</button>
						</div>
						<div class="rbfw-gw-modal-body">
							<div class="rbfw-gw-toggle-row">
								<div>
									<div class="rbfw-gw-toggle-label"><?php esc_html_e( 'Enable Stripe', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
									<div class="rbfw-gw-toggle-sub"><?php esc_html_e( 'Accept payments via Stripe', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
								</div>
								<label class="rbfw-gw-switch"><input type="checkbox" data-field="rbfw_stripe_enable" <?php checked( $st_enabled ); ?>><span class="rbfw-gw-slider"></span></label>
							</div>
							<div class="rbfw-gw-toggle-row">
								<div>
									<div class="rbfw-gw-toggle-label"><?php esc_html_e( 'Sandbox / Test Mode', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
									<div class="rbfw-gw-toggle-sub"><?php esc_html_e( 'Use test keys instead of live keys', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
								</div>
								<label class="rbfw-gw-switch"><input type="checkbox" data-field="rbfw_stripe_sandbox" <?php checked( $st_sandbox ); ?>><span class="rbfw-gw-slider"></span></label>
							</div>
							<hr class="rbfw-gw-divider">
							<p class="rbfw-gw-section-title"><?php esc_html_e( 'Test / Sandbox Keys', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							<div class="rbfw-gw-field">
								<label class="rbfw-gw-label"><?php esc_html_e( 'Test Publishable Key', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="text" data-field="rbfw_stripe_test_pub" value="<?php echo $st_test_pub; ?>" placeholder="pk_test_...">
							</div>
							<div class="rbfw-gw-field">
								<label class="rbfw-gw-label"><?php esc_html_e( 'Test Secret Key', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="password" data-field="rbfw_stripe_test_sec" value="<?php echo $st_test_sec; ?>" placeholder="sk_test_...">
							</div>
							<hr class="rbfw-gw-divider">
							<p class="rbfw-gw-section-title"><?php esc_html_e( 'Live Keys', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							<div class="rbfw-gw-field">
								<label class="rbfw-gw-label"><?php esc_html_e( 'Live Publishable Key', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="text" data-field="rbfw_stripe_live_pub" value="<?php echo $st_live_pub; ?>" placeholder="pk_live_...">
							</div>
							<div class="rbfw-gw-field">
								<label class="rbfw-gw-label"><?php esc_html_e( 'Live Secret Key', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="password" data-field="rbfw_stripe_live_sec" value="<?php echo $st_live_sec; ?>" placeholder="sk_live_...">
							</div>
						</div>
						<div class="rbfw-gw-modal-footer">
							<button type="button" class="rbfw-gw-save-btn" data-gateway="stripe" style="background:linear-gradient(135deg,#635bff,#3f36c5);"><?php esc_html_e( 'Save Stripe Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<span class="rbfw-gw-save-msg"></span>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Offline Payment Config Modal -->
				<div id="rbfw-offline-modal" class="rbfw-gw-modal">
					<div class="rbfw-gw-modal-box">
						<div class="rbfw-gw-modal-header" style="background:linear-gradient(135deg,#0f766e 0%,#115e59 100%);">
							<h2><?php esc_html_e( 'Offline Payment Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
							<button type="button" class="rbfw-gw-modal-close">&times;</button>
						</div>
						<div class="rbfw-gw-modal-body">
							<div class="rbfw-gw-toggle-row">
								<div>
									<div class="rbfw-gw-toggle-label"><?php esc_html_e( 'Enable Offline Payment', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
									<div class="rbfw-gw-toggle-sub"><?php esc_html_e( 'Let customers pay offline (bank transfer, cash, pay on pickup).', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
								</div>
								<label class="rbfw-gw-switch"><input type="checkbox" data-field="rbfw_offline_enable" <?php checked( $off_enabled ); ?>><span class="rbfw-gw-slider"></span></label>
							</div>
							<hr class="rbfw-gw-divider">
							<div class="rbfw-gw-field">
								<label class="rbfw-gw-label"><?php esc_html_e( 'Heading', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="text" data-field="rbfw_offline_label" value="<?php echo $off_label; ?>" placeholder="<?php esc_attr_e( 'e.g. Pay on Pickup / Bank Transfer', 'booking-and-rental-manager-for-woocommerce' ); ?>">
								<p style="margin:8px 0 0;font-size:12px;color:#6b7280;"><?php esc_html_e( 'Shown above the payment choices on the frontend payment step.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							</div>
							<hr class="rbfw-gw-divider">
							<?php $this->render_offline_methods(); ?>
						</div>
						<div class="rbfw-gw-modal-footer">
							<button type="button" class="rbfw-gw-save-btn" data-gateway="offline" style="background:linear-gradient(135deg,#0f766e,#115e59);"><?php esc_html_e( 'Save Offline Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<span class="rbfw-gw-save-msg"></span>
						</div>
					</div>
				</div>

				<script>
				var rbfwGateway = <?php echo wp_json_encode( array(
					'nonce'    => $nonce,
					'enabled'  => __( 'Enabled', 'booking-and-rental-manager-for-woocommerce' ),
					'disabled' => __( 'Disabled', 'booking-and-rental-manager-for-woocommerce' ),
				) ); ?>;
				jQuery(function($){
					$(document).on('click', '#rbfw-paypal-configure-btn', function(e){ e.preventDefault(); $('#rbfw-paypal-modal').css('display','flex').hide().fadeIn(220); });
					$(document).on('click', '#rbfw-stripe-configure-btn', function(e){ e.preventDefault(); $('#rbfw-stripe-modal').css('display','flex').hide().fadeIn(220); });
					$(document).on('click', '#rbfw-offline-configure-btn', function(e){ e.preventDefault(); $('#rbfw-offline-modal').css('display','flex').hide().fadeIn(220); });
					$(document).on('click', '.rbfw-gw-modal-close', function(){ $('.rbfw-gw-modal').fadeOut(200); });
					$(document).on('click', '.rbfw-gw-modal', function(e){ if ($(e.target).hasClass('rbfw-gw-modal')) $(this).fadeOut(200); });

					$(document).on('click', '.rbfw-gw-save-btn', function(e){
						e.preventDefault();
						var $btn=$(this), $box=$btn.closest('.rbfw-gw-modal-box'), gateway=$btn.data('gateway'),
						    $msg=$box.find('.rbfw-gw-save-msg'), fields={};
						$box.find('input[data-field]').each(function(){
							var key=$(this).data('field');
							fields[key]=($(this).attr('type')==='checkbox') ? ($(this).is(':checked')?'on':'off') : $(this).val();
						});

						// Offline payment types are a repeater, not flat data-field inputs, so
						// they travel alongside `fields` rather than inside it.
						var methods=[];
						$box.find('.rbfw-pm-row').each(function(){
							var $row=$(this);
							var label=$.trim($row.find('.rbfw-pm-label').val()||'');
							if(!label){ return; }   // unlabelled rows cannot be shown or reconciled
							methods.push({
								slug: $row.attr('data-slug')||'',
								label: label,
								instructions: $row.find('.rbfw-pm-instructions').val()||'',
								enabled: $row.find('.rbfw-pm-enabled').is(':checked') ? 1 : 0
							});
						});

						$btn.prop('disabled',true).css('opacity','0.7'); $msg.hide();
						$.ajax({
							url: ajaxurl, type:'POST',
							data:{ action:'rbfw_save_gateway_settings', nonce:rbfwGateway.nonce, gateway:gateway, fields:fields, methods:methods },
							success: function(res){
								if(res.success){
									$msg.css({'color':'#0f5132','background':'#d1e7dd','border':'1px solid #badbcc'}).text(res.data).fadeIn(200);
									setTimeout(function(){ $msg.fadeOut(400); }, 1200);
									var $badge=$('.'+gateway+'-card .gateway-status');
									if($badge.length){
										var isEnabled = fields['rbfw_'+gateway+'_enable']==='on';
										$badge.text(isEnabled?rbfwGateway.enabled:rbfwGateway.disabled).toggleClass('active',isEnabled);
									}
									// Everything else that answers "is a booking payable right
									// now?" (the editor's Payment Method card, its banner, the
									// no-gateway warning, the locked Custom Payment card) was
									// rendered server-side and would stay stale until reload.
									$(document).trigger('rbfw:payment-updated');
								} else {
									$msg.css({'color':'#842029','background':'#f8d7da','border':'1px solid #f5c2c7'}).text(res.data).fadeIn(200);
									setTimeout(function(){ $msg.fadeOut(400); }, 1500);
								}
							},
							error: function(){
								$msg.css({'color':'#842029','background':'#f8d7da','border':'1px solid #f5c2c7'}).text('A network error occurred.').fadeIn(200);
								setTimeout(function(){ $msg.fadeOut(400); }, 1500);
							},
							complete: function(){ $btn.prop('disabled',false).css('opacity','1'); }
						});
					});
				});
				</script>
				<?php
			}

			/** Mode-driven field visibility + gateway card styling (footer). */
			public function payment_tabs_script() {
				// Also needed on the modern editor so the popup's gateway cards get the
				// same `.gateway-card` / `.rbfw-gw-*` styling defined below. The script
				// further down early-returns as soon as it fails to find the settings
				// table's own `tr.rbfw_booking_mode_selector` row, so the accordion and
				// row-visibility logic never runs there.
				if ( ! $this->is_settings_or_editor_screen() ) {
					return;
				}
				$wc_active = $this->has_woo() ? 'true' : 'false';
				$mode      = class_exists( 'RBFW_Function' ) ? RBFW_Function::booking_mode() : 'woocommerce';
				?>
				<style>
				/* Same accent contract as booking_mode_styles() — see the note there. */
				:root{--rbfw-pay-accent:#2271B1;}
				.rbfw_global_settings{--rbfw-pay-accent:var(--rbfw-gs-accent,#2271B1);}

				/* Custom Payment intro */
				.rbfw-gw-intro{margin:4px 0 18px;}
				.rbfw-gw-intro h3{margin:0 0 4px;font-size:16px;font-weight:700;color:#1d2327;}
				.rbfw-gw-intro p{margin:0;font-size:13px;color:#6b7280;max-width:680px;line-height:1.6;}

				/* Gateway cards (Custom Payment) — modern responsive card grid.
				   Each card exposes its brand colour via --gw / --gw2 custom
				   properties so the accent strip, icon badge, and Configure button
				   all share one palette per gateway. */
				.payment-gateways-container th{display:none;}
				.payment-gateways-container td{padding:0 !important;}
				.rbfw-gw-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;margin-bottom:22px;}
				.gateway-card{--gw:var(--rbfw-pay-accent);--gw2:var(--rbfw-pay-accent);position:relative;display:flex;flex-direction:column;gap:14px;background:#fff;border:1px solid #eceef2;border-radius:16px;padding:22px 20px 18px;box-shadow:0 4px 14px rgba(16,24,40,0.06);overflow:hidden;box-sizing:border-box;transition:transform 0.18s ease,box-shadow 0.18s ease,border-color 0.18s ease;}
				.gateway-card:before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--gw),var(--gw2));}
				.gateway-card:hover{transform:translateY(-3px);box-shadow:0 16px 32px rgba(16,24,40,0.13);border-color:var(--gw);}
				.gateway-card.paypal-card{--gw:#0079C1;--gw2:#003087;}
				.gateway-card.stripe-card{--gw:#635bff;--gw2:#3f36c5;}
				.gateway-card.offline-card{--gw:#0f766e;--gw2:#115e59;}
				.gateway-card .gateway-top{display:flex;align-items:center;justify-content:space-between;gap:10px;}
				.gateway-card .gateway-icon{flex:0 0 auto;width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,var(--gw),var(--gw2));display:flex;align-items:center;justify-content:center;box-shadow:0 8px 18px -6px var(--gw);}
				.gateway-card .gateway-meta{display:flex;flex-direction:column;gap:3px;min-width:0;}
				.gateway-card .gateway-name{font-size:16px;font-weight:700;color:#1d2327;line-height:1.3;}
				.gateway-card .gateway-sub{font-size:12.5px;color:#6b7280;line-height:1.45;}
				.gateway-card .gateway-status{display:inline-flex;align-items:center;gap:6px;font-size:10.5px;text-transform:uppercase;letter-spacing:0.5px;padding:5px 11px;border-radius:20px;background:#f3f4f6;color:#6b7280;font-weight:700;white-space:nowrap;}
				.gateway-card .gateway-status:before{content:"";width:6px;height:6px;border-radius:50%;background:#9ca3af;}
				.gateway-card .gateway-status.active{background:#dcfce7;color:#166534;}
				.gateway-card .gateway-status.active:before{background:#22c55e;}
				.gateway-card .gateway-actions{display:flex;margin-top:auto;}
				.gateway-card .gateway-configure-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:100%;cursor:pointer;color:var(--gw) !important;background:#fff !important;border:1.5px solid var(--gw) !important;font-weight:700 !important;font-size:13.5px !important;border-radius:10px !important;padding:9px 16px !important;line-height:1.4 !important;transition:color 0.16s ease,background 0.16s ease,box-shadow 0.16s ease;}
				.gateway-card .gateway-configure-btn .dashicons{font-size:16px;width:16px;height:16px;line-height:1;}
				.gateway-card .gateway-configure-btn:hover{color:#fff !important;background:linear-gradient(135deg,var(--gw),var(--gw2)) !important;box-shadow:0 8px 18px -6px var(--gw) !important;}
				.rbfw-gw-pro-badge{background:linear-gradient(135deg,#f6d365 0%,#fda085 100%);color:#fff;padding:5px 12px;border-radius:20px;font-weight:bold;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;box-shadow:0 2px 6px rgba(253,160,133,0.4);}

				/* Booking confirmation page */
				.rbfw-conf-page{margin-top:8px;padding:20px 22px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;background:#fafafb;border:1px solid #ececf0;border-radius:14px;}
				.rbfw-conf-page-label{flex:1 1 260px;}
				.rbfw-conf-page-label label{display:block;font-weight:700;font-size:14px;color:#1d2327;margin:0 0 4px;}
				.rbfw-conf-page-label span{display:block;font-size:12px;color:#6b7280;line-height:1.6;}
				.rbfw-conf-page-field{flex:0 0 auto;}
				.rbfw-conf-page-field select{width:100%;max-width:320px;border:1px solid #d1d5db;border-radius:8px;padding:7px 12px;font-size:13px;background:#fff;}

				/* WooCommerce sub-tab accordions */
				tr.rbfw-acc-header > td.rbfw-acc-header-cell{padding:0 !important;}
				tr.rbfw-acc-header .rbfw-acc-bar{display:flex;align-items:center;justify-content:space-between;gap:10px;cursor:pointer;user-select:none;background:#fff;border:1px solid #e7e8ec;border-radius:10px;padding:13px 16px;margin:14px 0 4px;transition:background 0.2s ease,border-color 0.2s ease,box-shadow 0.2s ease;}
				tr.rbfw-acc-header .rbfw-acc-bar:hover{border-color:#a8c8e4;box-shadow:0 2px 8px rgba(16,24,40,0.06);}
				tr.rbfw-acc-header.open .rbfw-acc-bar{background:#eff6fc;border-color:var(--rbfw-pay-accent);}
				tr.rbfw-acc-header .rbfw-acc-title{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#1d2327;margin:0;}
				tr.rbfw-acc-header.open .rbfw-acc-title{color:var(--rbfw-pay-accent);}
				tr.rbfw-acc-header .rbfw-acc-arrow{transition:transform 0.2s ease;color:#50575e;line-height:1;}
				tr.rbfw-acc-header.open .rbfw-acc-arrow{transform:rotate(180deg);color:var(--rbfw-pay-accent);}
				/* The accordion header already shows the title; hide the manager's own duplicate heading but keep its bar (it holds the "Open in WooCommerce" link). */
				tr.wc-payment-methods-field .rbfw-wc-pm-heading{display:none;}
				tr.wc-payment-methods-field .rbfw-wc-payment-manager{margin-top:4px;padding:6px 2px;}
				/* WooCommerce enable toggle row + additional fields: lighter rows */
				tr.woocommerce-field td, tr.no-woocommerce-field td{vertical-align:middle;}

				/* --- Align with the modern Global Settings shell ---
				   The gateway cards / sub-tabs / accordions are the visual layer on
				   this tab, so neutralise the generic form-table "card" (border,
				   shadow, row striping + hover) that the shell applies to every tab,
				   otherwise a striped box sits behind the cards. */
				#rbfw_payment_settings table.form-table{background:transparent !important;border:none !important;box-shadow:none !important;border-radius:0 !important;margin-bottom:0 !important;}
				#rbfw_payment_settings table.form-table tr{background:transparent !important;border-bottom:none !important;}
				#rbfw_payment_settings table.form-table tr:hover{background:transparent !important;}
				#rbfw_payment_settings table.form-table > tbody > tr > th{padding-left:0 !important;}

				/* Mobile: gateway card header wraps to two rows (icon/name/sub on
				   its own line, status + action below) instead of squeezing three
				   flex items — icon, status pill, and Configure button — onto one
				   narrow line. */
				@media (max-width: 480px) {
					.gateway-card .gateway-header{flex-wrap:wrap;row-gap:10px;}
					.gateway-card .gateway-id{flex:1 1 100%;}
					.gateway-card .gateway-status{flex:0 0 auto;}
					.gateway-card .gateway-actions{flex:0 0 auto;justify-content:flex-start;margin-left:auto;}
				}
				</style>
				<script>
				jQuery(function($){
					// Only run on the Payments tab (identified by the Booking Mode selector row).
					if ($('tr.rbfw_booking_mode_selector').length === 0) { return; }

					// The Booking Mode selector row carries full-width UI (intro, mode cards,
					// context banner). Once WooCommerce mode shows its 2-column "Additional
					// Settings" rows, an un-spanned single cell gets squeezed into the narrow
					// label column — so drop the empty label cell and span it across both columns.
					$('tr.rbfw_booking_mode_selector').children('th').remove();
					$('tr.rbfw_booking_mode_selector').children('td').attr('colspan', 2);

					var wcActive   = <?php echo $wc_active; ?>;
					var activeMode = <?php echo wp_json_encode( $mode ); ?>;
					var modeLabels = {
						woocommerce: <?php echo wp_json_encode( __( 'WooCommerce Checkout', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						standalone:  <?php echo wp_json_encode( __( 'Custom Payment (Standalone)', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
					};

					// --- WooCommerce settings accordions: Payment Methods (open) + Additional Settings (collapsed) ---
					var $methodsRows      = $('tr.wc-payment-methods-field');
					var $additionalRows   = $('tr.wc-additional-field');
					var $methodsHeader    = $();
					var $additionalHeader = $();

					function buildAccordionHeader(extraClass, title, isOpen){
						return $(
							'<tr class="woocommerce-field rbfw-acc-header '+extraClass+(isOpen?' open':'')+'">'+
								'<td colspan="2" class="rbfw-acc-header-cell">'+
									'<div class="rbfw-acc-bar">'+
										'<span class="rbfw-acc-title">'+title+'</span>'+
										'<span class="rbfw-acc-arrow dashicons dashicons-arrow-down-alt2"></span>'+
									'</div>'+
								'</td>'+
							'</tr>'
						);
					}

					function refreshAccordions(){
						if (!$methodsHeader.length) { return; }
						if ($methodsHeader.hasClass('open')) { $methodsRows.show(); } else { $methodsRows.hide(); }
						if ($additionalHeader.hasClass('open')) { $additionalRows.show(); } else { $additionalRows.hide(); }
					}

					if ($methodsRows.length || $additionalRows.length) {
						// Anchor the accordion headers directly on the Booking Mode selector row —
						// the single switch that now decides which settings show (the old sub-tab
						// pill bar that used to anchor them was removed as a confusing duplicate).
						var $anchorRow = $('tr.rbfw_booking_mode_selector');
						$methodsHeader    = buildAccordionHeader('rbfw-acc-methods', <?php echo wp_json_encode( __( 'WooCommerce Payment Methods', 'booking-and-rental-manager-for-woocommerce' ) ); ?>, true);
						$additionalHeader = buildAccordionHeader('rbfw-acc-additional', <?php echo wp_json_encode( __( 'Additional Settings', 'booking-and-rental-manager-for-woocommerce' ) ); ?>, false);

						// Make the payment-methods row span the full table width (drop the empty
						// label cell so the shared column widths don't squeeze sibling rows).
						$methodsRows.each(function(){
							var $r = $(this);
							$r.children('th').remove();
							$r.children('td').attr('colspan', 2);
						});

						// Re-order: mode selector -> [Methods header + rows] -> [Additional header + rows].
						$methodsRows.detach();
						$additionalRows.detach();
						$anchorRow.after($methodsHeader);
						$methodsHeader.after($methodsRows);
						$methodsRows.last().after($additionalHeader);
						$additionalHeader.after($additionalRows);

						// Exclusive toggle: opening one closes the other.
						$methodsHeader.find('.rbfw-acc-bar').on('click', function(){
							var willOpen = !$methodsHeader.hasClass('open');
							$methodsHeader.toggleClass('open', willOpen);
							if (willOpen) { $additionalHeader.removeClass('open'); }
							refreshAccordions();
						});
						$additionalHeader.find('.rbfw-acc-bar').on('click', function(){
							var willOpen = !$additionalHeader.hasClass('open');
							$additionalHeader.toggleClass('open', willOpen);
							if (willOpen) { $methodsHeader.removeClass('open'); }
							refreshAccordions();
						});
					}

					// Show only the settings that belong to the active booking flow. Called on
					// load and whenever a Booking Mode card is clicked (window.rbfwApplyPaymentMode).
					function applyModeVisibility(mode){
						activeMode = (mode === 'standalone') ? 'standalone' : 'woocommerce';
						$('tr.woocommerce-field, tr.no-woocommerce-field').hide();
						$('.rbfw_settings_panel .submit').show();
						if (activeMode === 'woocommerce') {
							if (wcActive) { $('tr.woocommerce-field').stop(true,true).show(); refreshAccordions(); }
						} else {
							$('tr.no-woocommerce-field').show();
						}

						// Keep the "You're configuring: <flow>" banner in sync with the choice.
						var $ctx = $('.rbfw-bm-context');
						if ($ctx.length) {
							$ctx.attr('data-mode', activeMode);
							$ctx.find('.rbfw-bm-context-icon')
								.removeClass('dashicons-cart dashicons-money-alt')
								.addClass(activeMode === 'woocommerce' ? 'dashicons-cart' : 'dashicons-money-alt');
							$ctx.find('.rbfw-bm-context-mode').text(modeLabels[activeMode]);
						}
					}
					window.rbfwApplyPaymentMode = applyModeVisibility;
					applyModeVisibility(activeMode);
				});
				</script>
				<?php
			}

			/** AJAX: save a single gateway's settings (real-time from its modal). */
			public function ajax_save_gateway_settings() {
				check_ajax_referer( 'rbfw_save_gateway', 'nonce' );
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( __( 'Permission denied.', 'booking-and-rental-manager-for-woocommerce' ) );
				}

				$gateway  = isset( $_POST['gateway'] ) ? sanitize_key( wp_unslash( $_POST['gateway'] ) ) : '';
				$fields   = isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : array();
				$existing = get_option( self::OPTION, array() );
				if ( ! is_array( $existing ) ) {
					$existing = array();
				}

				$allowed = array(
					'paypal'  => array( 'rbfw_paypal_enable', 'rbfw_paypal_sandbox', 'rbfw_paypal_client_id', 'rbfw_paypal_secret' ),
					'stripe'  => array( 'rbfw_stripe_enable', 'rbfw_stripe_sandbox', 'rbfw_stripe_test_pub', 'rbfw_stripe_test_sec', 'rbfw_stripe_live_pub', 'rbfw_stripe_live_sec' ),
					'offline' => array( 'rbfw_offline_enable', 'rbfw_offline_label' ),
				);

				if ( ! isset( $allowed[ $gateway ] ) ) {
					wp_send_json_error( __( 'Invalid gateway.', 'booking-and-rental-manager-for-woocommerce' ) );
				}

				// PayPal & Stripe are Pro-only; never persist their keys from the free build.
				if ( ( 'paypal' === $gateway || 'stripe' === $gateway ) && ! $this->is_pro() ) {
					wp_send_json_error( __( 'This gateway is available in the Pro version.', 'booking-and-rental-manager-for-woocommerce' ) );
				}

				// Offline payment types (the repeater in that gateway's modal). Only accepted
				// for the offline gateway, and only when the repeater was actually posted —
				// an absent key must leave the stored list alone rather than wipe it.
				if ( 'offline' === $gateway && isset( $_POST['methods'] ) ) {
					$raw = wp_unslash( $_POST['methods'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized field-by-field in sanitize_payment_methods().
					$existing['rbfw_payment_methods'] = self::sanitize_payment_methods( is_array( $raw ) ? $raw : array() );
				}

				$toggles = array( 'rbfw_paypal_enable', 'rbfw_paypal_sandbox', 'rbfw_stripe_enable', 'rbfw_stripe_sandbox', 'rbfw_offline_enable' );
				foreach ( $allowed[ $gateway ] as $key ) {
					if ( in_array( $key, $toggles, true ) ) {
						// An unchecked checkbox posts nothing, so absent genuinely means "off".
						$val              = isset( $fields[ $key ] ) ? $fields[ $key ] : 'off';
						$existing[ $key ] = ( 'on' === $val ) ? 'on' : 'off';
						continue;
					}

					// Text fields are a different story: absent means "not submitted", NOT
					// "empty". This previously fell through to the same 'off' default and
					// wrote the literal string "off" into the field — which is how a partial
					// save could turn the Offline heading into "off", and would just as
					// happily have overwritten a Stripe or PayPal API key with it. Leave a
					// field that was not posted exactly as it was stored.
					if ( ! isset( $fields[ $key ] ) ) {
						continue;
					}
					$existing[ $key ] = sanitize_text_field( $fields[ $key ] );
				}

				update_option( self::OPTION, $existing );
				wp_send_json_success( __( 'Settings saved successfully!', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			/** AJAX: persist the Booking Mode immediately when the card selection changes. */
			public function ajax_save_booking_mode() {
				check_ajax_referer( 'rbfw_save_booking_mode', 'nonce' );
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( __( 'Permission denied.', 'booking-and-rental-manager-for-woocommerce' ) );
				}

				$mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';
				if ( ! in_array( $mode, array( 'woocommerce', 'standalone' ), true ) ) {
					wp_send_json_error( __( 'Invalid booking mode.', 'booking-and-rental-manager-for-woocommerce' ) );
				}

				// The choice is only meaningful when both systems are available; otherwise the
				// mode is auto-resolved and shouldn't be overridden.
				if ( class_exists( 'RBFW_Function' ) && 'both' !== RBFW_Function::mode_availability() ) {
					wp_send_json_error( __( 'Booking mode can only be changed when both WooCommerce and the Pro custom gateways are available.', 'booking-and-rental-manager-for-woocommerce' ) );
				}

				RBFW_Function::set_booking_mode( $mode );

				$checker     = class_exists( 'RBFW_Payment_Status_Checker' ) ? new RBFW_Payment_Status_Checker() : null;
				$has_gateway = $checker ? $checker->has_gateway_for_active_mode() : true;

				wp_send_json_success( array(
					'mode'        => $mode,
					'message'     => __( 'Booking mode saved.', 'booking-and-rental-manager-for-woocommerce' ),
					'has_gateway' => $has_gateway,
				) );
			}

			/** AJAX: install &/or activate WooCommerce. */
			public function ajax_install_activate_wc() {
				check_ajax_referer( 'rbfw_install_wc', 'nonce' );
				if ( ! current_user_can( 'install_plugins' ) ) {
					wp_send_json_error( __( 'Permission denied.', 'booking-and-rental-manager-for-woocommerce' ) );
				}

				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/misc.php';

				$plugin_file = 'woocommerce/woocommerce.php';

				if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
					$api = plugins_api( 'plugin_information', array(
						'slug'   => 'woocommerce',
						'fields' => array( 'sections' => false ),
					) );
					if ( is_wp_error( $api ) ) {
						wp_send_json_error( $api->get_error_message() );
					}
					$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
					$result   = $upgrader->install( $api->download_link );
					if ( is_wp_error( $result ) ) {
						wp_send_json_error( $result->get_error_message() );
					} elseif ( ! $result ) {
						wp_send_json_error( __( 'Installation failed. Please try manually.', 'booking-and-rental-manager-for-woocommerce' ) );
					}
				}

				// Activate via the options table to avoid loading woocommerce.php into this
				// process (which would clash with the wc_price()/WC() fallback shims).
				$active = get_option( 'active_plugins', array() );
				if ( ! in_array( $plugin_file, $active, true ) ) {
					$active[] = $plugin_file;
					sort( $active );
					update_option( 'active_plugins', $active );
				}
				do_action( 'activate_' . $plugin_file );
				do_action( 'activated_plugin', $plugin_file, false );

				wp_send_json_success( __( 'WooCommerce activated successfully!', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			/**
			 * Keep gateway credentials when the Settings API saves the rest of the form.
			 * Only restores a key when it is ABSENT from the incoming value, so a gateway
			 * modal's own AJAX save (which carries new values) is never clobbered.
			 */
			public function preserve_gateway_keys( $new_value, $old_value ) {
				$protected = array(
					'rbfw_paypal_enable', 'rbfw_paypal_sandbox', 'rbfw_paypal_client_id', 'rbfw_paypal_secret',
					'rbfw_stripe_enable', 'rbfw_stripe_sandbox', 'rbfw_stripe_test_pub', 'rbfw_stripe_test_sec',
					'rbfw_stripe_live_pub', 'rbfw_stripe_live_sec',
					'rbfw_offline_enable', 'rbfw_offline_label',
					// Offline payment types live in that gateway's modal and save over AJAX,
					// so they never travel with the settings form — without this, saving any
					// other field on the Payments tab would silently wipe the whole list.
					'rbfw_payment_methods',
				);
				if ( ! is_array( $new_value ) ) {
					return $new_value;
				}
				if ( is_array( $old_value ) ) {
					foreach ( $protected as $key ) {
						if ( ! isset( $new_value[ $key ] ) && isset( $old_value[ $key ] ) ) {
							$new_value[ $key ] = $old_value[ $key ];
						}
					}
				}

				// The Booking Mode card only renders when both systems are available; on any
				// other save keep the previously stored choice rather than dropping it.
				if ( ! isset( $new_value['rbfw_booking_mode'] ) && is_array( $old_value ) && isset( $old_value['rbfw_booking_mode'] ) ) {
					$new_value['rbfw_booking_mode'] = $old_value['rbfw_booking_mode'];
				}
				// Keep the legacy "Enable WooCommerce Payment" mirror in lock-step with the mode
				// so any older code still reading that flag agrees with booking_mode().
				if ( isset( $new_value['rbfw_booking_mode'] ) && in_array( $new_value['rbfw_booking_mode'], array( 'woocommerce', 'standalone' ), true ) ) {
					$new_value['rbfw_enable_wc_payment'] = ( 'woocommerce' === $new_value['rbfw_booking_mode'] ) ? 'on' : 'off';
				}

				return $new_value;
			}
		}

		new RBFW_Payment_Settings();
	endif;
