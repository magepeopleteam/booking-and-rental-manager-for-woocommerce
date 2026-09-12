<?php
/**
 * Post-activation "Welcome" onboarding screen — shown once, right after the
 * plugin is activated, instead of dropping straight into the (empty) rental
 * items list. Modelled on the same pattern popular onboarding wizards use
 * (Eventin, WooCommerce Setup Wizard, etc.): a normal wp-admin page whose
 * chrome (menu, admin bar, notices) is hidden via CSS scoped to this screen
 * only, so the page reads as a full-screen welcome modal rather than a
 * regular settings screen.
 *
 * Registered as a parent-less submenu page (`add_submenu_page('', ...)`), so
 * it never appears in the admin menu — it's only ever reached via the
 * activation redirect (RBFW_Woo_Installer::handle_activation_redirect()) or
 * by visiting its URL directly.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Onboarding' ) ) {

	class RBFW_Onboarding {

		const PAGE_SLUG = 'rbfw_onboarding';

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_page' ) );
		}

		/**
		 * Hidden page — parent '' means it never shows up in any admin menu.
		 */
		public function register_page() {
			add_submenu_page(
				'',
				__( 'Welcome', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'Welcome', 'booking-and-rental-manager-for-woocommerce' ),
				'manage_options',
				self::PAGE_SLUG,
				array( $this, 'render_page' )
			);
		}

		/**
		 * URL of this screen, for the activation redirect.
		 */
		public static function url() {
			return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		}

		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Both destinations already exist elsewhere in the plugin — this screen
			// is just a friendlier front door to them, not a new setup flow.
			// (admin/RBFW_Quick_Setup.php looks like the obvious fit for the CTA,
			// but it's dead code — deliberately dropped from the load order by
			// commit 12df7c0a ("Remove old Quick Setup loading and keep automatic
			// default page creation") in favour of the current dummy-import-popup
			// flow, so it's never wired up. Not reviving it here.)
			$setup_url   = admin_url( 'post-new.php?post_type=rbfw_item' );
			$explore_url = menu_page_url( 'rbfw_welcome', false );
			$items_url   = admin_url( 'edit.php?post_type=rbfw_item' );

			if ( ! $explore_url ) {
				$explore_url = $items_url;
			}
			?>
			<style>
				/* Hide the normal wp-admin chrome on this one screen only, so the
				   centered card below reads as a full-screen welcome moment instead
				   of "a settings page". body.admin_page_<slug> is the standard core
				   body class WordPress adds for a parent-less admin.php subpage. */
				body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> #adminmenumain,
				body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> #wpadminbar,
				body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> #wpfooter,
				body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> .notice,
				body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> .update-nag {
					display: none !important;
				}
				body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> #wpcontent,
				body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> #wpbody-content {
					margin-left: 0 !important;
					padding: 0 !important;
				}
				html.wp-toolbar body.admin_page_<?php echo esc_attr( self::PAGE_SLUG ); ?> {
					padding-top: 0 !important;
				}

				.rbfw-ob-screen {
					min-height: 100vh;
					box-sizing: border-box;
					display: flex;
					align-items: center;
					justify-content: center;
					padding: 40px 20px;
					background: linear-gradient(135deg, #fdf2f8 0%, #eef2ff 50%, #ecfeff 100%);
				}
				.rbfw-ob-card {
					width: 100%;
					max-width: 640px;
					box-sizing: border-box;
					background: #fff;
					border-radius: 20px;
					box-shadow: 0 24px 60px rgba(15, 23, 42, .12);
					padding: 40px 44px 36px;
					text-align: center;
				}
				.rbfw-ob-logo {
					display: inline-flex;
					align-items: center;
					gap: 9px;
					margin-bottom: 22px;
				}
				.rbfw-ob-logo svg { flex: 0 0 auto; }
				.rbfw-ob-logo span {
					font-size: 19px;
					font-weight: 700;
					color: #111827;
					letter-spacing: -.2px;
				}
				.rbfw-ob-title {
					margin: 0 0 8px;
					font-size: 26px;
					font-weight: 700;
					color: #111827;
					line-height: 1.3;
				}
				.rbfw-ob-subtitle {
					margin: 0 0 26px;
					font-size: 14.5px;
					color: #6b7280;
				}
				.rbfw-ob-features {
					display: grid;
					grid-template-columns: repeat(3, 1fr);
					gap: 12px;
					margin-bottom: 24px;
				}
				.rbfw-ob-feature {
					background: #fafafa;
					border: 1px solid #f1f1f4;
					border-radius: 12px;
					padding: 18px 12px;
				}
				.rbfw-ob-feature-icon {
					display: inline-flex;
					align-items: center;
					justify-content: center;
					width: 40px;
					height: 40px;
					border-radius: 10px;
					background: rgba(255, 55, 38, .1);
					color: var(--rbfw_color_primary, #ff3726);
					margin-bottom: 12px;
				}
				.rbfw-ob-feature-title {
					margin: 0 0 3px;
					font-size: 13.5px;
					font-weight: 700;
					color: #111827;
				}
				.rbfw-ob-feature-desc {
					margin: 0;
					font-size: 12px;
					color: #6b7280;
					line-height: 1.4;
				}
				.rbfw-ob-links {
					margin: 0 0 26px;
					font-size: 12.5px;
					color: #6b7280;
				}
				.rbfw-ob-links a {
					color: var(--rbfw_color_primary, #ff3726);
					font-weight: 600;
					text-decoration: none;
				}
				.rbfw-ob-links a:hover { text-decoration: underline; }
				.rbfw-ob-actions {
					display: flex;
					align-items: center;
					justify-content: center;
					gap: 12px;
					flex-wrap: wrap;
				}
				.rbfw-ob-btn {
					display: inline-flex;
					align-items: center;
					justify-content: center;
					gap: 6px;
					padding: 11px 22px;
					border-radius: 8px;
					font-size: 13.5px;
					font-weight: 700;
					text-decoration: none;
					line-height: 1;
					border: 1px solid transparent;
					transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
				}
				.rbfw-ob-btn:hover { transform: translateY(-1px); }
				.rbfw-ob-btn--secondary {
					background: #f3f4f6;
					color: #334155;
				}
				.rbfw-ob-btn--secondary:hover { background: #e5e7eb; color: #334155; }
				.rbfw-ob-btn--primary {
					background: var(--rbfw_color_primary, #ff3726);
					color: #fff;
					box-shadow: 0 4px 14px rgba(255, 55, 38, .3);
				}
				.rbfw-ob-btn--primary:hover {
					background: #e42e20;
					color: #fff;
					box-shadow: 0 6px 18px rgba(255, 55, 38, .38);
				}
				@media (max-width: 560px) {
					.rbfw-ob-card { padding: 30px 22px 26px; }
					.rbfw-ob-features { grid-template-columns: 1fr; }
					.rbfw-ob-actions { flex-direction: column-reverse; width: 100%; }
					.rbfw-ob-btn { width: 100%; }
				}
			</style>

			<div class="rbfw-ob-screen">
				<div class="rbfw-ob-card">
					<div class="rbfw-ob-logo">
						<svg width="30" height="30" viewBox="0 0 24 24" fill="none" aria-hidden="true">
							<rect width="24" height="24" rx="7" fill="#ff3726"/>
							<path d="M7 14.5V10a1 1 0 01.4-.8l4-3a1 1 0 011.2 0l4 3a1 1 0 01.4.8v4.5" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M9.2 14.5V12a.8.8 0 01.8-.8h4a.8.8 0 01.8.8v2.5" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M6.2 14.5h11.6" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
						</svg>
						<span><?php esc_html_e( 'Rental Manager', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>

					<h1 class="rbfw-ob-title"><?php esc_html_e( 'Set up your rentals in minutes', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
					<p class="rbfw-ob-subtitle"><?php esc_html_e( 'Everything you need to list, book, and get paid for rentals.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>

					<div class="rbfw-ob-features">
						<div class="rbfw-ob-feature">
							<span class="rbfw-ob-feature-icon" aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<p class="rbfw-ob-feature-title"><?php esc_html_e( 'Quick Item Setup', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							<p class="rbfw-ob-feature-desc"><?php esc_html_e( 'Get your first rental live in minutes', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<div class="rbfw-ob-feature">
							<span class="rbfw-ob-feature-icon" aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M3 12h18M12 3c2.5 2.5 3.8 5.5 3.8 9s-1.3 6.5-3.8 9c-2.5-2.5-3.8-5.5-3.8-9S9.5 5.5 12 3z" stroke="currentColor" stroke-width="1.8"/></svg>
							</span>
							<p class="rbfw-ob-feature-title"><?php esc_html_e( 'WooCommerce or Standalone', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							<p class="rbfw-ob-feature-desc"><?php esc_html_e( 'Accept bookings with or without WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
						<div class="rbfw-ob-feature">
							<span class="rbfw-ob-feature-icon" aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 10h18M7 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
							</span>
							<p class="rbfw-ob-feature-title"><?php esc_html_e( 'Flexible Pricing & Payments', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
							<p class="rbfw-ob-feature-desc"><?php esc_html_e( 'Hourly, daily or multi-day rates, card, PayPal & more', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						</div>
					</div>

					<p class="rbfw-ob-links">
						<?php
						printf(
							/* translators: 1: opening link tag to the reference/demo site, 2: closing link tag. */
							esc_html__( 'New here? %1$sSee a live reference site & docs%2$s', 'booking-and-rental-manager-for-woocommerce' ),
							'<a href="' . esc_url( $explore_url ) . '">',
							'</a>'
						);
						?>
					</p>

					<div class="rbfw-ob-actions">
						<a href="<?php echo esc_url( $explore_url ); ?>" class="rbfw-ob-btn rbfw-ob-btn--secondary">
							<?php esc_html_e( 'Skip & Explore', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</a>
						<a href="<?php echo esc_url( $setup_url ); ?>" class="rbfw-ob-btn rbfw-ob-btn--primary">
							<?php esc_html_e( "Let's set up your first rental item", 'booking-and-rental-manager-for-woocommerce' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php
		}
	}

	new RBFW_Onboarding();
}
