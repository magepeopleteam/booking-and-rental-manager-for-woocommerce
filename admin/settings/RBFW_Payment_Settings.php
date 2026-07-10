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

				// Gateway keys are managed by their own AJAX modals and never travel with
				// the settings form, so preserve them when the Settings API saves the rest.
				add_filter( 'pre_update_option_' . self::OPTION, array( $this, 'preserve_gateway_keys' ), 10, 2 );
			}

			/** Is this the rental settings screen? */
			private function is_settings_screen() {
				$screen = get_current_screen();
				return $screen && ( $screen->id === self::SCREEN || strpos( $screen->id, 'rbfw_settings_page' ) !== false );
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
						'name'     => 'rbfw_payment_tabs_html',
						'label'    => '',
						'callback' => array( $this, 'render_sub_tabs' ),
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

			/**
			 * The "Booking Mode" selector — the single, explicit switch that decides whether
			 * WooCommerce or the standalone Custom Payment flow processes bookings.
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

				if ( 'none' === $availability ) {
					?>
					<div class="rbfw-bm-auto-note rbfw-bm-auto-note--warn">
						<span class="dashicons dashicons-warning"></span>
						<p><?php esc_html_e( 'No booking flow is available yet: WooCommerce is not active and the Pro plugin is not active. Activate WooCommerce or the Pro plugin to start taking bookings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<?php
					$this->booking_mode_styles();
					return;
				}

				if ( 'woocommerce_only' === $availability ) {
					?>
					<div class="rbfw-bm-auto-note">
						<span class="dashicons dashicons-yes-alt"></span>
						<p><?php esc_html_e( 'Bookings are automatically processed through WooCommerce — it\'s the only booking flow available right now. Activate the Pro plugin to unlock the standalone Custom Payment flow (and a mode switch here).', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<?php
					$this->booking_mode_styles();
					return;
				}

				if ( 'custom_only' === $availability ) {
					?>
					<div class="rbfw-bm-auto-note">
						<span class="dashicons dashicons-yes-alt"></span>
						<p><?php esc_html_e( 'Bookings are automatically processed through the Custom Payment flow — WooCommerce is not active. Activate WooCommerce to unlock the WooCommerce checkout flow (and a mode switch here).', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<?php
					$this->booking_mode_styles();
					return;
				}

				// $availability === 'both': a real choice.
				$mode        = RBFW_Function::booking_mode();
				$is_wc       = ( 'woocommerce' === $mode );
				$is_custom   = ( 'standalone' === $mode );
				$checker     = class_exists( 'RBFW_Payment_Status_Checker' ) ? new RBFW_Payment_Status_Checker() : null;
				$has_gateway = $checker ? $checker->has_gateway_for_active_mode() : true;
				?>
				<div class="rbfw-bm-wrap" data-nonce="<?php echo esc_attr( wp_create_nonce( 'rbfw_save_booking_mode' ) ); ?>">
					<div class="rbfw-bm-head">
						<h3><?php esc_html_e( 'Booking Mode', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Choose exactly one flow to process bookings. This single switch decides everything below, so WooCommerce and Custom Payment never both try to handle the same booking. Your choice is saved instantly.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>

					<div class="rbfw-bm-cards">
						<label class="rbfw-bm-card<?php echo $is_wc ? ' is-selected' : ''; ?>" data-mode="woocommerce">
							<input type="radio" name="rbfw_booking_mode_radio" value="woocommerce" <?php checked( $is_wc ); ?>>
							<span class="rbfw-bm-card-icon dashicons dashicons-cart"></span>
							<span class="rbfw-bm-card-body">
								<span class="rbfw-bm-card-title-row">
									<strong><?php esc_html_e( 'WooCommerce Checkout', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
									<span class="rbfw-bm-card-badge"><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								</span>
								<span class="rbfw-bm-card-desc"><?php esc_html_e( 'Bookings go through the WooCommerce cart, checkout, and orders.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</span>
						</label>
						<label class="rbfw-bm-card<?php echo $is_custom ? ' is-selected' : ''; ?>" data-mode="standalone">
							<input type="radio" name="rbfw_booking_mode_radio" value="standalone" <?php checked( $is_custom ); ?>>
							<span class="rbfw-bm-card-icon dashicons dashicons-money-alt"></span>
							<span class="rbfw-bm-card-body">
								<span class="rbfw-bm-card-title-row">
									<strong><?php esc_html_e( 'Custom Payment (Standalone)', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
									<span class="rbfw-bm-card-badge"><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								</span>
								<span class="rbfw-bm-card-desc"><?php esc_html_e( 'Bookings are taken directly via PayPal, Stripe, or Offline payment — no WooCommerce.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</span>
						</label>
					</div>

					<p class="rbfw-bm-status" role="status" aria-live="polite"></p>

					<div class="rbfw-bm-gateway-warning-slot">
						<?php if ( ! $has_gateway ) : ?>
							<div class="rbfw-bm-gateway-warning">
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

				<?php $this->booking_mode_styles(); ?>
				<script>
				jQuery(function($){
					var $wrap = $('.rbfw-bm-wrap');
					if (!$wrap.length) { return; }
					var nonce = $wrap.data('nonce');
					var i18n = {
						saving: <?php echo wp_json_encode( __( 'Saving…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						saved:  <?php echo wp_json_encode( __( 'Booking mode saved.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						error:  <?php echo wp_json_encode( __( 'Could not save. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						wcWarn: <?php echo wp_json_encode( __( 'WooCommerce mode is selected, but no WooCommerce payment gateway is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>,
						customWarn: <?php echo wp_json_encode( __( 'Custom Payment mode is selected, but no gateway (PayPal, Stripe, or Offline) is enabled yet. Customers won\'t be able to complete a booking until you enable one below.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
					};

					$wrap.on('click', '.rbfw-bm-card', function(){
						var $card = $(this), mode = $card.data('mode');
						if ($card.hasClass('is-selected')) { return; }

						$wrap.find('.rbfw-bm-card').removeClass('is-selected');
						$card.addClass('is-selected').find('input[type=radio]').prop('checked', true);
						var $status = $wrap.find('.rbfw-bm-status').show().text(i18n.saving).css('color','#6b7280');

						$.post(ajaxurl, { action:'rbfw_save_booking_mode', nonce:nonce, mode:mode })
							.done(function(res){
								if (res && res.success) {
									$status.text(i18n.saved).css('color','#0a7c2f');
									setTimeout(function(){ $status.fadeOut(400, function(){ $(this).text('').show(); }); }, 1800);

									// Refresh the "Active" badge on the sub-tab bar.
									$('.rbfw-pay-subtab-badge').hide();
									$('.rbfw-pay-subtab-badge[data-badge-for="'+mode+'"]').show();

									// Jump to the matching sub-tab so it can be configured right away.
									var targetHref = (mode === 'standalone') ? '#no-woocommerce-field' : '#woocommerce-field';
									$('.payment-sub-tabs .nav-tab[href="'+targetHref+'"]').trigger('click');

									// Refresh the "no gateway enabled" warning for the newly active mode.
									var $slot = $wrap.find('.rbfw-bm-gateway-warning-slot').empty();
									if (res.data && res.data.has_gateway === false) {
										var msg = (mode === 'woocommerce') ? i18n.wcWarn : i18n.customWarn;
										$slot.append('<div class="rbfw-bm-gateway-warning"><span class="dashicons dashicons-warning"></span><p>'+msg+'</p></div>');
									}
								} else {
									$status.show().text((res && res.data) ? res.data : i18n.error).css('color','#d63638');
								}
							})
							.fail(function(){ $status.show().text(i18n.error).css('color','#d63638'); });
					});
				});
				</script>
				<?php
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
				/* Render the selector row full width (its settings-table label cell is empty). */
				#rbfw_payment_settings tr.rbfw_booking_mode_selector > th{display:none;}
				#rbfw_payment_settings tr.rbfw_booking_mode_selector > td{padding-left:0 !important;display:block;width:100%;}
				.rbfw-bm-wrap,.rbfw-bm-wrap *,.rbfw-bm-auto-note,.rbfw-bm-auto-note *{box-sizing:border-box;}
				.rbfw-bm-wrap{margin:2px 0 18px;max-width:100%;}
				.rbfw-bm-head h3{margin:0 0 2px;font-size:15px;font-weight:700;color:#1d2327;}
				.rbfw-bm-head p{margin:0 0 12px;font-size:12.5px;color:#6b7280;max-width:680px;line-height:1.55;}
				.rbfw-bm-cards{display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:100%;}
				.rbfw-bm-card{position:relative;display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border:1.5px solid #e5e7eb;border-radius:12px;background:#fafafb;cursor:pointer;transition:border-color .15s,box-shadow .15s,background .15s;min-width:0;}
				.rbfw-bm-card:hover{border-color:#d4b3c3;box-shadow:0 4px 14px rgba(16,24,40,0.06);}
				.rbfw-bm-card.is-selected{border-color:#F12971;background:#fff;box-shadow:0 6px 18px rgba(241,41,113,0.12);}
				.rbfw-bm-card input[type=radio]{position:absolute;opacity:0;width:0;height:0;}
				.rbfw-bm-card-icon{flex:0 0 auto;width:36px;height:36px;border-radius:9px;background:rgba(241,41,113,0.1);color:#F12971;display:flex !important;align-items:center !important;justify-content:center !important;font-size:18px;}
				.rbfw-bm-card-body{display:block !important;flex:1;min-width:0;white-space:normal !important;}
				.rbfw-bm-card-title-row{display:flex !important;align-items:center;justify-content:space-between;gap:8px;margin:0 0 4px;width:100%;}
				.rbfw-bm-card-body strong{display:inline-block !important;font-size:14px;line-height:1.3;color:#1d2327;}
				.rbfw-bm-card-desc{display:block !important;font-size:12px;color:#6b7280;line-height:1.5;overflow-wrap:break-word;}
				.rbfw-bm-card-badge{flex:0 0 auto;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;background:#dcfce7;color:#166534;padding:1px 8px;border-radius:20px;display:none !important;}
				.rbfw-bm-card.is-selected .rbfw-bm-card-badge{display:inline-block !important;}
				.rbfw-bm-status{min-height:16px;margin:8px 2px 0;font-size:12px;font-weight:600;}
				.rbfw-bm-gateway-warning{display:flex;align-items:flex-start;gap:8px;margin-top:10px;padding:9px 12px;border-radius:8px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:12px;}
				.rbfw-bm-gateway-warning p{margin:0;}
				.rbfw-bm-auto-note{display:flex;align-items:flex-start;gap:10px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a8a;border-radius:10px;padding:10px 14px;margin:4px 0 14px;font-size:12.5px;}
				.rbfw-bm-auto-note--warn{background:#fef2f2;border-color:#fecaca;color:#991b1b;}
				.rbfw-bm-auto-note p{margin:0;}
				.rbfw-pay-subtab-badge{margin-left:6px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;background:rgba(255,255,255,0.9);color:#166534;padding:1px 7px;border-radius:20px;vertical-align:middle;}
				@media (max-width:680px){.rbfw-bm-cards{grid-template-columns:1fr;}}
				</style>
				<?php
			}

			/** Sub-tab bar (WooCommerce / Custom Payment) + WC-inactive warning. */
			public function render_sub_tabs() {
				$wc_active    = $this->has_woo();
				$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
				$btn_text     = $is_installed
					? __( 'Activate WooCommerce Now', 'booking-and-rental-manager-for-woocommerce' )
					: __( 'Install &amp; Activate Now', 'booking-and-rental-manager-for-woocommerce' );

				// Default the active sub-tab to whichever flow currently owns bookings, so the
				// Custom Payment gateways aren't the first thing shown when WooCommerce is the mode.
				$mode           = class_exists( 'RBFW_Function' ) ? RBFW_Function::booking_mode() : 'woocommerce';
				$custom_is_mode = ( 'standalone' === $mode );
				?>
				<div class="payment-sub-tabs-wrapper">
					<h2 class="nav-tab-wrapper payment-sub-tabs">
						<a href="#woocommerce-field" class="nav-tab<?php echo $custom_is_mode ? '' : ' nav-tab-active'; ?>">
							<?php esc_html_e( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?>
							<span class="rbfw-pay-subtab-badge" data-badge-for="woocommerce"<?php echo $custom_is_mode ? ' style="display:none;"' : ''; ?>><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</a>
						<a href="#no-woocommerce-field" class="nav-tab<?php echo $custom_is_mode ? ' nav-tab-active' : ''; ?>">
							<?php esc_html_e( 'Custom Payment', 'booking-and-rental-manager-for-woocommerce' ); ?>
							<span class="rbfw-pay-subtab-badge" data-badge-for="standalone"<?php echo $custom_is_mode ? '' : ' style="display:none;"'; ?>><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</a>
					</h2>
					<?php if ( ! $wc_active ) : ?>
						<div class="woocommerce-field">
							<div class="rbfw-woo-warning-notice" style="background:#fff3cd;color:#856404;padding:15px;border-left:4px solid #ffeeba;border-radius:6px;margin:15px 0 10px;">
								<div style="display:flex;flex-direction:column;align-items:flex-start;gap:15px;">
									<div style="width:100%;">
										<strong style="display:block;font-size:14px;margin-bottom:5px;"><i class="fas fa-exclamation-triangle" style="margin-right:5px;"></i><?php esc_html_e( 'Notice: WooCommerce is Not Activated', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
										<span style="font-size:13px;display:block;"><?php esc_html_e( 'To process bookings through the WooCommerce cart/checkout flow, you must install and activate WooCommerce. Otherwise, use the Custom Payment tab.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
									</div>
									<div>
										<button type="button" class="button button-primary rbfw-install-wc-trigger" style="white-space:nowrap;"><?php echo wp_kses_post( $btn_text ); ?></button>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php
			}

			/** PayPal / Stripe / Offline gateway cards + booking confirmation page. */
			public function render_gateway_cards() {
				$is_pro      = $this->is_pro();
				$pp_enabled  = $this->opt( 'rbfw_paypal_enable' ) === 'on';
				$st_enabled  = $this->opt( 'rbfw_stripe_enable' ) === 'on';
				$off_enabled = $this->opt( 'rbfw_offline_enable' ) === 'on';
				$conf_page   = absint( $this->opt( 'rbfw_confirmation_page_id', 0 ) );

				$enabled_txt  = __( 'Enabled', 'booking-and-rental-manager-for-woocommerce' );
				$disabled_txt = __( 'Disabled', 'booking-and-rental-manager-for-woocommerce' );
				$pro_badge    = '<span class="rbfw-gw-pro-badge" title="' . esc_attr__( 'Available in Pro version', 'booking-and-rental-manager-for-woocommerce' ) . '">PRO</span>';
				?>
				<div class="rbfw-gw-intro">
					<h3><?php esc_html_e( 'Custom Payment Gateways', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
					<p><?php esc_html_e( 'Accept payments directly without WooCommerce. Configure a gateway below, then enable it for the Standalone / Custom Payment checkout.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>

				<!-- PayPal Card -->
				<div class="gateway-card paypal-card">
					<div class="gateway-header">
						<div class="gateway-id">
							<span class="gateway-icon">
								<svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z" fill="#fff"/>
								</svg>
							</span>
							<span class="gateway-meta">
								<span class="gateway-name"><?php esc_html_e( 'PayPal', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<span class="gateway-sub"><?php esc_html_e( 'Cards & PayPal balance', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</span>
						</div>
						<?php if ( $is_pro ) : ?>
							<span class="gateway-status <?php echo $pp_enabled ? 'active' : ''; ?>"><?php echo esc_html( $pp_enabled ? $enabled_txt : $disabled_txt ); ?></span>
						<?php endif; ?>
						<div class="gateway-actions">
							<?php if ( $is_pro ) : ?>
								<button type="button" class="gateway-configure-btn" id="rbfw-paypal-configure-btn"><?php esc_html_e( 'Configure', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<?php else : ?>
								<?php echo wp_kses_post( $pro_badge ); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Stripe Card -->
				<div class="gateway-card stripe-card">
					<div class="gateway-header">
						<div class="gateway-id">
							<span class="gateway-icon">
								<svg width="26" height="26" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
									<path fill="#fff" d="M14.07 15.11c-1.85-.43-2.61-.79-2.61-1.63 0-.79.75-1.33 1.95-1.33 1.34 0 2.87.41 4.31 1.09V8.65c-1.39-.56-2.93-.84-4.52-.84-3.8 0-6.66 1.96-6.66 5.25 0 3.73 3.32 4.96 6.03 5.61 2.05.49 2.8.92 2.8 1.8 0 .86-.87 1.48-2.3 1.48-1.57 0-3.37-.53-5.06-1.54v4.75c1.67.75 3.59 1.13 5.51 1.13 4.13 0 7-2 7-5.34-.01-3.6-3.6-4.41-6.45-5.84z"/>
								</svg>
							</span>
							<span class="gateway-meta">
								<span class="gateway-name"><?php esc_html_e( 'Stripe', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<span class="gateway-sub"><?php esc_html_e( 'Credit & debit cards', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</span>
						</div>
						<?php if ( $is_pro ) : ?>
							<span class="gateway-status <?php echo $st_enabled ? 'active' : ''; ?>"><?php echo esc_html( $st_enabled ? $enabled_txt : $disabled_txt ); ?></span>
						<?php endif; ?>
						<div class="gateway-actions">
							<?php if ( $is_pro ) : ?>
								<button type="button" class="gateway-configure-btn" id="rbfw-stripe-configure-btn"><?php esc_html_e( 'Configure', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<?php else : ?>
								<?php echo wp_kses_post( $pro_badge ); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Offline Payment Card -->
				<div class="gateway-card offline-card">
					<div class="gateway-header">
						<div class="gateway-id">
							<span class="gateway-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3 19h18a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/>
									<path d="M2 10h20M6 14h4" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
								</svg>
							</span>
							<span class="gateway-meta">
								<span class="gateway-name"><?php esc_html_e( 'Offline Payment', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
								<span class="gateway-sub"><?php esc_html_e( 'Bank transfer, cash, pay on pickup', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</span>
						</div>
						<span class="gateway-status <?php echo $off_enabled ? 'active' : ''; ?>"><?php echo esc_html( $off_enabled ? $enabled_txt : $disabled_txt ); ?></span>
						<div class="gateway-actions">
							<button type="button" class="gateway-configure-btn" id="rbfw-offline-configure-btn"><?php esc_html_e( 'Configure', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</div>
					</div>
				</div>

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

			/** WooCommerce native payment-methods manager (inside the Payment Methods accordion). */
			public function render_wc_payment_manager() {
				if ( class_exists( 'WooCommerce' ) && class_exists( 'RBFW_WC_Payment_Manager' ) ) {
					RBFW_WC_Payment_Manager::instance()->render();
				}
			}

			/** WooCommerce install / activate modal (footer). */
			public function render_wc_warning_modal() {
				if ( ! $this->is_settings_screen() || $this->has_woo() ) {
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
				if ( ! $this->is_settings_screen() ) {
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
				.rbfw-gw-field input[type="text"]:focus,.rbfw-gw-field input[type="password"]:focus{border-color:#F12971;box-shadow:0 0 0 3px rgba(241,41,113,0.12);outline:none;background:#fff;}
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
								<label class="rbfw-gw-label"><?php esc_html_e( 'Payment Label', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<input type="text" data-field="rbfw_offline_label" value="<?php echo $off_label; ?>" placeholder="<?php esc_attr_e( 'e.g. Pay on Pickup / Bank Transfer', 'booking-and-rental-manager-for-woocommerce' ); ?>">
								<p style="margin:8px 0 0;font-size:12px;color:#6b7280;"><?php esc_html_e( 'This label is shown to customers on the frontend payment step.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							</div>
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
						$btn.prop('disabled',true).css('opacity','0.7'); $msg.hide();
						$.ajax({
							url: ajaxurl, type:'POST',
							data:{ action:'rbfw_save_gateway_settings', nonce:rbfwGateway.nonce, gateway:gateway, fields:fields },
							success: function(res){
								if(res.success){
									$msg.css({'color':'#0f5132','background':'#d1e7dd','border':'1px solid #badbcc'}).text(res.data).fadeIn(200);
									setTimeout(function(){ $msg.fadeOut(400); }, 1200);
									var $badge=$('.'+gateway+'-card .gateway-status');
									if($badge.length){
										var isEnabled = fields['rbfw_'+gateway+'_enable']==='on';
										$badge.text(isEnabled?rbfwGateway.enabled:rbfwGateway.disabled).toggleClass('active',isEnabled);
									}
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

			/** Sub-tab switching + gateway card styling (footer). */
			public function payment_tabs_script() {
				if ( ! $this->is_settings_screen() ) {
					return;
				}
				$wc_active = $this->has_woo() ? 'true' : 'false';
				?>
				<style>
				:root{--rbfw-pay-accent:#F12971;}
				/* Sub-tab bar */
				.payment-sub-tabs-wrapper{margin:6px 0 22px;background:#fff;padding:6px;border-radius:12px;border:1px solid #e7e8ec;box-shadow:0 1px 2px rgba(16,24,40,0.04);display:inline-block;}
				.payment-sub-tabs.nav-tab-wrapper{border-bottom:none !important;padding:0 !important;margin:0 !important;display:flex;gap:6px;}
				.payment-sub-tabs .nav-tab{background:transparent;border:1px solid transparent;border-radius:8px;padding:9px 20px;font-size:14px;font-weight:600;color:#50575e !important;text-decoration:none;margin:0;transition:all 0.18s ease;}
				.payment-sub-tabs .nav-tab:hover{background:#fbeaf1;color:var(--rbfw-pay-accent) !important;}
				.payment-sub-tabs .nav-tab-active,.payment-sub-tabs .nav-tab-active:hover{background:var(--rbfw-pay-accent);color:#fff !important;box-shadow:0 4px 12px rgba(241,41,113,0.28);}

				/* Custom Payment intro */
				.rbfw-gw-intro{margin:4px 0 18px;}
				.rbfw-gw-intro h3{margin:0 0 4px;font-size:16px;font-weight:700;color:#1d2327;}
				.rbfw-gw-intro p{margin:0;font-size:13px;color:#6b7280;max-width:680px;line-height:1.6;}

				/* Gateway cards (Custom Payment) */
				.payment-gateways-container th{display:none;}
				.payment-gateways-container td{padding:0 !important;}
				.gateway-card{border:none;border-radius:14px;margin-bottom:14px;box-shadow:0 6px 18px rgba(16,24,40,0.10);width:100%;box-sizing:border-box;color:#fff;overflow:hidden;transition:transform 0.18s ease,box-shadow 0.18s ease;}
				.gateway-card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(16,24,40,0.16);}
				.gateway-card .gateway-header{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:18px 22px;}
				.gateway-card .gateway-id{display:flex;align-items:center;gap:14px;min-width:0;flex:1 1 0;}
				.gateway-card .gateway-icon{flex:0 0 auto;width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,0.16);display:flex;align-items:center;justify-content:center;}
				.gateway-card .gateway-meta{display:flex;flex-direction:column;min-width:0;}
				.gateway-card .gateway-name{font-size:16px;font-weight:700;color:#fff;line-height:1.3;}
				.gateway-card .gateway-sub{font-size:12px;color:rgba(255,255,255,0.82);line-height:1.4;}
				.gateway-card .gateway-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px;flex:1 1 0;}
				.gateway-card .gateway-status{display:inline-block;min-width:78px;text-align:center;font-size:11px;text-transform:uppercase;letter-spacing:0.4px;padding:4px 11px;border-radius:20px;background:rgba(255,255,255,0.2);color:#fff;font-weight:700;}
				.gateway-card .gateway-status.active{background:#fff;}
				.gateway-card.paypal-card{background:linear-gradient(135deg,#003087 0%,#0079C1 100%);}
				.gateway-card.paypal-card .gateway-status.active{color:#003087;}
				.gateway-card.stripe-card{background:linear-gradient(135deg,#635bff 0%,#3f36c5 100%);}
				.gateway-card.stripe-card .gateway-status.active{color:#635bff;}
				.gateway-card.offline-card{background:linear-gradient(135deg,#0f766e 0%,#115e59 100%);}
				.gateway-card.offline-card .gateway-status.active{color:#0f766e;}
				.gateway-card .gateway-configure-btn{cursor:pointer;color:#1d2327 !important;background:#fff !important;border:none !important;font-weight:600 !important;font-size:13px !important;border-radius:8px !important;padding:7px 16px !important;line-height:1.4 !important;box-shadow:0 2px 6px rgba(0,0,0,0.18) !important;transition:opacity 0.15s ease;}
				.gateway-card .gateway-configure-btn:hover{opacity:0.9;}
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
				tr.rbfw-acc-header .rbfw-acc-bar:hover{border-color:#d4b3c3;box-shadow:0 2px 8px rgba(16,24,40,0.06);}
				tr.rbfw-acc-header.open .rbfw-acc-bar{background:#fdf2f7;border-color:var(--rbfw-pay-accent);}
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
				   narrow line. Sub-tab pills wrap instead of overflowing. */
				@media (max-width: 480px) {
					.payment-sub-tabs.nav-tab-wrapper{flex-wrap:wrap;}
					.gateway-card .gateway-header{flex-wrap:wrap;row-gap:10px;}
					.gateway-card .gateway-id{flex:1 1 100%;}
					.gateway-card .gateway-status{flex:0 0 auto;}
					.gateway-card .gateway-actions{flex:0 0 auto;justify-content:flex-start;margin-left:auto;}
				}
				</style>
				<script>
				jQuery(function($){
					var wcActive = <?php echo $wc_active; ?>;
					if ($('.payment-sub-tabs').length === 0) { return; }

					// --- WooCommerce sub-tab accordions: Payment Methods (open) + Additional Settings (collapsed) ---
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
						// Anchor the accordion headers on the sub-tab row (the "Enable WooCommerce
						// Payment" toggle that used to anchor them was removed — the Booking Mode
						// card replaces it). Captured before the sub-tab wrapper is relocated below.
						var $toggleRow = $('.payment-sub-tabs-wrapper').closest('tr');
						$methodsHeader    = buildAccordionHeader('rbfw-acc-methods', <?php echo wp_json_encode( __( 'WooCommerce Payment Methods', 'booking-and-rental-manager-for-woocommerce' ) ); ?>, true);
						$additionalHeader = buildAccordionHeader('rbfw-acc-additional', <?php echo wp_json_encode( __( 'Additional Settings', 'booking-and-rental-manager-for-woocommerce' ) ); ?>, false);

						// Make the payment-methods row span the full table width (drop the empty
						// label cell so the shared column widths don't squeeze sibling rows).
						$methodsRows.each(function(){
							var $r = $(this);
							$r.children('th').remove();
							$r.children('td').attr('colspan', 2);
						});

						// Re-order: toggle -> [Methods header + rows] -> [Additional header + rows].
						$methodsRows.detach();
						$additionalRows.detach();
						$toggleRow.after($methodsHeader);
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

					function updateTabs(){
						var activeTabId = $('.payment-sub-tabs .nav-tab-active').attr('href').replace('#','');
						$('tr.woocommerce-field, div.woocommerce-field, tr.no-woocommerce-field').hide();
						$('.rbfw_settings_panel .submit').show();
						if (activeTabId === 'woocommerce-field') {
							$('div.woocommerce-field').show();
							if (wcActive) { $('tr.woocommerce-field').stop(true,true).show(); refreshAccordions(); }
						} else {
							$('tr.' + activeTabId).show();
						}
					}
					$('.payment-sub-tabs .nav-tab').on('click', function(e){
						e.preventDefault();
						$('.payment-sub-tabs .nav-tab').removeClass('nav-tab-active');
						$(this).addClass('nav-tab-active');
						updateTabs();
					});

					// Move the tab bar above the settings table so it spans full width.
					var $tabContainer = $('.payment-sub-tabs-wrapper');
					var $table = $tabContainer.closest('table.form-table');
					if ($table.length) {
						$tabContainer.insertBefore($table);
						$table.find('tr').each(function(){
							if ($(this).find('.payment-sub-tabs-wrapper').length === 0 && $(this).text().trim() === '') { $(this).hide(); }
						});
					}
					updateTabs();
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

				$toggles = array( 'rbfw_paypal_enable', 'rbfw_paypal_sandbox', 'rbfw_stripe_enable', 'rbfw_stripe_sandbox', 'rbfw_offline_enable' );
				foreach ( $allowed[ $gateway ] as $key ) {
					$val = isset( $fields[ $key ] ) ? $fields[ $key ] : 'off';
					if ( in_array( $key, $toggles, true ) ) {
						$existing[ $key ] = ( 'on' === $val ) ? 'on' : 'off';
					} else {
						$existing[ $key ] = sanitize_text_field( $val );
					}
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
