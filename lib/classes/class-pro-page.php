<?php
/*
* Author 	:	MagePeople Team
* Copyright	: 	mage-people.com
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('RBFWProPage')) {

	class RBFWProPage{

		/**
		 * Feature categories shown on the Get PRO page.
		 *
		 * Kept as data (rather than hard-coded markup) so the list is easy to
		 * keep in sync with what the Pro plugin actually ships — each entry
		 * reflects a real, current Pro module, not a historical/aspirational
		 * one. icon = a core Dashicon name (no extra asset dependency).
		 *
		 * @return array[]
		 */
		private function rbfw_gopro_feature_categories() {
			return array(
				array(
					'icon'  => 'cart',
					'title' => __( 'Payments & Checkout', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Built-in PayPal & Stripe checkout — accept cards and PayPal directly, no separate gateway setup.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Dedicated booking confirmation page with the booking reference and status.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Offline / manual payment option — reserve now, pay later.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'icon'  => 'calendar-alt',
					'title' => __( 'Booking Management', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Modern Booking Orders dashboard — live search, filters, stats and pagination.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'One-click status updates from the order list or detail view.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Visual booking calendar to spot availability and conflicts at a glance.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Edit stock quantities directly from the Inventory page, without opening each item.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'icon'  => 'media-document',
					'title' => __( 'PDF & Receipts', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Branded PDF booking receipts — your logo, colours, address and terms.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Thermal / POS receipt printing (80mm & 58mm) for front-desk checkouts.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Receipts attach automatically to confirmation emails.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Guided one-click setup for the PDF engine — no manual configuration.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'icon'  => 'welcome-write-blog',
					'title' => __( 'Custom Booking Forms', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Drag-and-drop form builder — no code required.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Conditional logic — show or hide fields based on earlier answers.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Build multiple forms and reuse them across different rental items.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'File uploads in forms — collect IDs, licences or waivers.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'icon'  => 'star-filled',
					'title' => __( 'Reviews & Social Proof', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Customer star ratings and written reviews on rental items.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Review moderation queue with a pending-count badge.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'icon'  => 'chart-bar',
					'title' => __( 'Reporting & Export', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Choose exactly which columns appear in your booking reports.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'One-click CSV export for accounting or offline analysis.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'icon'  => 'share',
					'title' => __( 'Calendar & Integrations', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Google Calendar sync — keep staff, vehicles or rooms scheduled outside WordPress.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Sync every existing booking in bulk, or automatically as new ones come in.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'icon'  => 'email-alt',
					'title' => __( 'Notifications', 'booking-and-rental-manager-for-woocommerce' ),
					'items' => array(
						__( 'Fully editable confirmation email — subject, body, sender name and address.', 'booking-and-rental-manager-for-woocommerce' ),
						__( 'Choose exactly which order statuses trigger an email.', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
			);
		}

		public function rbfw_go_pro_page(){
			$categories = $this->rbfw_gopro_feature_categories();
			?>
			<div class="wrap"></div>
			<div class="rbfw_gopro">

				<!-- Hero -->
				<div class="rbfw_gopro_hero">
					<div class="rbfw_gopro_hero_badge"><?php esc_html_e( 'Upgrade', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
					<h1><?php esc_html_e( 'Booking and Rental Manager for WooCommerce — Pro', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
					<p><?php esc_html_e( 'Everything the free plugin gives you to build the booking flow, plus the back office to actually run the business: payments, receipts, custom forms, reporting and calendar sync.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					<div class="rbfw_gopro_hero_actions">
						<a href="<?php echo esc_url( 'https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/' ); ?>" class="rbfw_gopro_btn rbfw_gopro_btn_primary" target="_blank" rel="noopener"><?php esc_html_e( 'Buy Pro', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
						<a href="<?php echo esc_url( 'https://booking.mage-people.com/' ); ?>" class="rbfw_gopro_btn rbfw_gopro_btn_ghost_light" target="_blank" rel="noopener"><?php esc_html_e( 'View Demo', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
						<a href="<?php echo esc_url( 'https://docs.mage-people.com/rent-and-booking-manager/' ); ?>" class="rbfw_gopro_btn rbfw_gopro_btn_ghost_light" target="_blank" rel="noopener"><?php esc_html_e( 'Documentation', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
					</div>
				</div>

				<!-- Feature categories -->
				<div class="rbfw_gopro_section">
					<h2><?php esc_html_e( "What Pro Adds", 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					<div class="rbfw_gopro_cat_grid">
						<?php foreach ( $categories as $cat ) : ?>
							<div class="rbfw_gopro_cat_card">
								<div class="rbfw_gopro_cat_icon"><span class="dashicons dashicons-<?php echo esc_attr( $cat['icon'] ); ?>"></span></div>
								<h3><?php echo esc_html( $cat['title'] ); ?></h3>
								<ul>
									<?php foreach ( $cat['items'] as $item ) : ?>
										<li><span class="dashicons dashicons-yes-alt"></span><span><?php echo esc_html( $item ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Addon cross-sell -->
				<div class="rbfw_gopro_addon">
					<div class="rbfw_gopro_addon_icon"><span class="dashicons dashicons-tag"></span></div>
					<div class="rbfw_gopro_addon_body">
						<h3><?php esc_html_e( 'Addon: Seasonal Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Extends the date-wise pricing engine — set different rates for peak season, weekends or holidays.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<a href="<?php echo esc_url( 'https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-addon-seasonal-pricing/' ); ?>" class="rbfw_gopro_btn rbfw_gopro_btn_outline" target="_blank" rel="noopener"><?php esc_html_e( 'Buy Addon', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
				</div>

				<!-- Testimonial -->
				<div class="rbfw_gopro_section">
					<h2><?php esc_html_e( 'What Users Say', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					<div class="rbfw_gopro_review">
						<div class="rbfw_gopro_review_stars">
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
						</div>
						<p class="rbfw_gopro_review_text">&ldquo;<?php esc_html_e( 'This is the best booking and rental plugin. Found all things in one place. This plugin meets my business requirements.', 'booking-and-rental-manager-for-woocommerce' ); ?>&rdquo;</p>
						<div class="rbfw_gopro_review_author">
							<strong><?php esc_html_e( 'alalvenzard', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Member, WordPress.org', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
					</div>
				</div>

				<!-- Final CTA -->
				<div class="rbfw_gopro_cta">
					<h2><?php esc_html_e( 'Ready to run bookings like a business?', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					<a href="<?php echo esc_url( 'https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/' ); ?>" class="rbfw_gopro_btn rbfw_gopro_btn_primary" target="_blank" rel="noopener"><?php esc_html_e( 'Download PRO Version Now', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
				</div>

			</div>
			<style>
				.rbfw_gopro, .rbfw_gopro * { box-sizing: border-box; }
				.rbfw_gopro {
					--rbfw-gp-accent1: #3F13A4;
					--rbfw-gp-accent2: #2271B1;
					--rbfw-gp-primary: #F12971;
					--rbfw-gp-heading: #1D2327;
					--rbfw-gp-text: #4A5568;
					--rbfw-gp-border: #E4E9F2;
					--rbfw-gp-bg: #F4F6FA;
					font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
					color: var(--rbfw-gp-heading);
					/* No outer max-width: fill the available admin content
					   width instead (like the rest of wp-admin) so this
					   doesn't leave a large empty gap on wide screens. The
					   card grid and hero copy already cap their own
					   readable widths (grid via minmax(), hero <p> via its
					   own max-width), so nothing gets uncomfortably wide
					   even at full bleed. */
					margin: 16px 20px 40px 0;
				}

				/* Hero */
				.rbfw_gopro_hero {
					background: linear-gradient(135deg, var(--rbfw-gp-accent1) 0%, var(--rbfw-gp-accent2) 100%);
					border-radius: 14px;
					padding: 44px 40px;
					color: #fff;
					box-shadow: 0 10px 30px rgba(63,19,164,.18);
				}
				.rbfw_gopro_hero_badge {
					display: inline-block;
					background: rgba(255,255,255,.16);
					border: 1px solid rgba(255,255,255,.3);
					padding: 4px 14px;
					border-radius: 20px;
					font-size: 11px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: .6px;
					margin-bottom: 14px;
				}
				.rbfw_gopro_hero h1 { margin: 0 0 12px; padding: 0; border: none; font-size: 28px; font-weight: 800; line-height: 1.25; color: #fff; max-width: 720px; }
				.rbfw_gopro_hero p { margin: 0 0 26px; font-size: 14.5px; line-height: 1.65; color: rgba(255,255,255,.88); max-width: 640px; }
				.rbfw_gopro_hero_actions { display: flex; flex-wrap: wrap; gap: 12px; }

				.rbfw_gopro_btn {
					display: inline-flex; align-items: center; justify-content: center;
					height: 42px; padding: 0 22px; border-radius: 8px;
					font-size: 13.5px; font-weight: 700; text-decoration: none !important;
					transition: opacity .18s, background .18s, transform .12s;
				}
				.rbfw_gopro_btn:active { transform: scale(.98); }
				.rbfw_gopro_btn_primary { background: var(--rbfw-gp-primary); color: #fff !important; box-shadow: 0 6px 16px rgba(241,41,113,.3); }
				.rbfw_gopro_btn_primary:hover { opacity: .9; }
				.rbfw_gopro_btn_ghost_light { background: rgba(255,255,255,.12); color: #fff !important; border: 1px solid rgba(255,255,255,.35); }
				.rbfw_gopro_btn_ghost_light:hover { background: rgba(255,255,255,.2); }
				.rbfw_gopro_btn_outline { background: #fff; color: var(--rbfw-gp-accent2) !important; border: 1px solid var(--rbfw-gp-accent2); flex-shrink: 0; }
				.rbfw_gopro_btn_outline:hover { background: var(--rbfw-gp-accent2); color: #fff !important; }

				/* Section heading */
				.rbfw_gopro_section { margin-top: 40px; }
				.rbfw_gopro_section h2 { font-size: 20px; font-weight: 800; margin: 0 0 20px; padding: 0; border: none; color: var(--rbfw-gp-heading); }

				/* Category grid */
				.rbfw_gopro_cat_grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr)); gap: 16px; }
				.rbfw_gopro_cat_card {
					background: #fff; border: 1px solid var(--rbfw-gp-border); border-radius: 12px;
					padding: 22px; box-shadow: 0 2px 10px rgba(15,23,42,.05);
				}
				.rbfw_gopro_cat_icon {
					width: 42px; height: 42px; border-radius: 10px;
					background: linear-gradient(135deg, var(--rbfw-gp-accent1) 0%, var(--rbfw-gp-accent2) 100%);
					display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
				}
				.rbfw_gopro_cat_icon .dashicons { color: #fff; font-size: 20px; width: 20px; height: 20px; }
				.rbfw_gopro_cat_card h3 { margin: 0 0 12px; padding: 0; border: none; font-size: 15px; font-weight: 700; color: var(--rbfw-gp-heading); }
				.rbfw_gopro_cat_card ul { margin: 0; padding: 0; list-style: none; }
				.rbfw_gopro_cat_card li { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 10px; font-size: 13px; line-height: 1.55; color: var(--rbfw-gp-text); }
				.rbfw_gopro_cat_card li:last-child { margin-bottom: 0; }
				.rbfw_gopro_cat_card li .dashicons { color: #16A34A; font-size: 16px; width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px; }

				/* Addon cross-sell */
				.rbfw_gopro_addon {
					margin-top: 32px; display: flex; align-items: center; gap: 18px;
					background: #fff; border: 1px solid var(--rbfw-gp-border); border-radius: 12px;
					padding: 20px 24px; box-shadow: 0 2px 10px rgba(15,23,42,.05);
				}
				.rbfw_gopro_addon_icon {
					width: 46px; height: 46px; border-radius: 10px; flex-shrink: 0;
					background: var(--rbfw-gp-bg); display: flex; align-items: center; justify-content: center;
				}
				.rbfw_gopro_addon_icon .dashicons { color: var(--rbfw-gp-accent2); font-size: 22px; width: 22px; height: 22px; }
				.rbfw_gopro_addon_body { flex: 1; min-width: 0; }
				.rbfw_gopro_addon_body h3 { margin: 0 0 4px; padding: 0; border: none; font-size: 15px; font-weight: 700; }
				.rbfw_gopro_addon_body p { margin: 0; font-size: 13px; color: var(--rbfw-gp-text); }

				/* Testimonial — centred with a decorative quote mark; a single
				   review left-aligned in a full-width section left a large,
				   unbalanced empty gap on wide screens once the outer max-
				   width cap was removed elsewhere on this page. */
				.rbfw_gopro_review {
					position: relative;
					background: #fff; border: 1px solid var(--rbfw-gp-border); border-radius: 14px;
					padding: 36px 40px; box-shadow: 0 2px 10px rgba(15,23,42,.05);
					max-width: 640px; margin: 0 auto; text-align: center;
				}
				.rbfw_gopro_review::before {
					content: '\201C';
					position: absolute; top: 6px; left: 24px;
					font-size: 64px; font-weight: 800; line-height: 1;
					color: var(--rbfw-gp-accent2); opacity: .12;
					font-family: Georgia, serif;
				}
				.rbfw_gopro_review_stars { display: flex; justify-content: center; gap: 2px; }
				.rbfw_gopro_review_stars .dashicons { color: #F59E0B; font-size: 16px; width: 16px; height: 16px; }
				.rbfw_gopro_review_text { font-size: 15.5px; font-style: italic; line-height: 1.65; color: var(--rbfw-gp-heading); margin: 14px 0 18px; position: relative; }
				.rbfw_gopro_review_author strong { display: block; font-size: 13px; }
				.rbfw_gopro_review_author span { font-size: 12px; color: var(--rbfw-gp-text); }

				/* Final CTA */
				.rbfw_gopro_cta {
					margin-top: 40px; text-align: center; padding: 36px 20px;
					background: var(--rbfw-gp-bg); border-radius: 14px;
				}
				.rbfw_gopro_cta h2 { margin: 0 0 18px; padding: 0; border: none; font-size: 20px; }

				@media (max-width: 782px) {
					.rbfw_gopro { margin-right: 10px; }
					.rbfw_gopro_hero { padding: 30px 24px; }
					.rbfw_gopro_hero h1 { font-size: 22px; }
					.rbfw_gopro_addon { flex-wrap: wrap; }
				}
			</style>
			<?php
			}
	}
}
