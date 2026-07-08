<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	/**
	 * @package RBFW_Plugin
	 */
	class RBFW_Welcome {
		public function __construct() {
			add_action( "rbfw_admin_menu_after_settings", array( $this, "RBFW_welcome_init" ) );
		}

		public function RBFW_welcome_init() {
			add_submenu_page(
				'edit.php?post_type=rbfw_item',
				__( 'Welcome', 'booking-and-rental-manager-for-woocommerce' ),
				'<span style="color:#13df13">' . __( 'Welcome', 'booking-and-rental-manager-for-woocommerce' ) . '</span>',
				'manage_options',
				'rbfw_welcome',
				array( $this, "RBFW_welcome_page_callback" )
			);
		}

		public function RBFW_welcome_page_callback() {

            $arr = array( 'strong' => array() );
			$shortcodes = $this->rbfw_welcome_shortcodes();

			?>
            <div class="wrap"></div>
            <div class="rbfw_welcome">

                <!-- Hero -->
                <div class="rbfw_welcome_hero">
                    <div class="rbfw_welcome_hero_badge">
                        <span class="dashicons dashicons-smiley"></span>
                        <?php esc_html_e( 'Welcome', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    </div>
                    <h1><?php esc_html_e( 'Booking and Rental Manager for WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
                    <p><?php esc_html_e( 'A complete rental & booking solution for your business. It is perfect to offer all types of rental and booking services — bikes, cars, resorts, equipment, dresses and more.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    <div class="rbfw_welcome_hero_actions">
                        <a href="<?php echo esc_url( 'https://booking.mage-people.com/' ); ?>" class="rbfw_welcome_btn rbfw_welcome_btn_primary" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e( 'View Demo', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </a>
                        <a href="<?php echo esc_url( 'https://docs.mage-people.com/rent-and-booking-manager/' ); ?>" class="rbfw_welcome_btn rbfw_welcome_btn_ghost_light" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-book"></span>
                            <?php esc_html_e( 'Documentation', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </a>
                        <a href="https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/" target="_blank" rel="noopener" class="rbfw_welcome_btn rbfw_welcome_btn_outline_light">
                            <span class="dashicons dashicons-cart"></span>
                            <?php esc_html_e( 'Buy Pro', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </a>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="rbfw_welcome_tabs_wrap">
                    <ul class="rbfw_welcome_tabs">
                        <li class="rbfw_welcome_tab_link current" data-tab="tab-welcome">
                            <span class="dashicons dashicons-admin-home"></span>
                            <?php esc_html_e( 'Welcome', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </li>
                        <li class="rbfw_welcome_tab_link" data-tab="tab-shortcodes">
                            <span class="dashicons dashicons-shortcode"></span>
                            <?php esc_html_e( 'Shortcodes', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </li>
                    </ul>

                    <!-- Welcome Tab -->
                    <div id="tab-welcome" class="rbfw_welcome_tab_content current">

                        <div class="rbfw_welcome_section">
                            <h2><?php esc_html_e( 'Get Started', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                            <div class="rbfw_welcome_card_grid">

                                <div class="rbfw_welcome_card">
                                    <div class="rbfw_welcome_card_icon"><span class="dashicons dashicons-welcome-learn-more"></span></div>
                                    <h3><?php esc_html_e( '1. Read the Docs', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                                    <p><?php esc_html_e( 'Step-by-step setup guide covering installation, configuration and your first booking item.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                                    <a href="<?php echo esc_url( 'https://docs.mage-people.com/rent-and-booking-manager/' ); ?>" target="_blank" rel="noopener" class="rbfw_welcome_card_link">
                                        <?php esc_html_e( 'Open Documentation', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        <span class="dashicons dashicons-arrow-right-alt"></span>
                                    </a>
                                </div>

                                <div class="rbfw_welcome_card">
                                    <div class="rbfw_welcome_card_icon"><span class="dashicons dashicons-video-alt3"></span></div>
                                    <h3><?php esc_html_e( '2. Watch Tutorials', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                                    <p><?php esc_html_e( 'Video walkthroughs showing how to create a bike/car/resort booking item from scratch.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                                    <span class="rbfw_welcome_card_meta">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php esc_html_e( 'Video tutorials coming soon', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    </span>
                                </div>

                                <div class="rbfw_welcome_card">
                                    <div class="rbfw_welcome_card_icon"><span class="dashicons dashicons-admin-customizer"></span></div>
                                    <h3><?php esc_html_e( '3. Configure & Launch', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                                    <p><?php esc_html_e( 'Use the built-in settings panel to set pricing, inventory, labels and WooCommerce integration.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_settings' ) ); ?>" class="rbfw_welcome_card_link">
                                        <?php esc_html_e( 'Open Settings', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        <span class="dashicons dashicons-arrow-right-alt"></span>
                                    </a>
                                </div>

                                <div class="rbfw_welcome_card">
                                    <div class="rbfw_welcome_card_icon"><span class="dashicons dashicons-sos"></span></div>
                                    <h3><?php esc_html_e( '4. Need Help?', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
                                    <p><?php esc_html_e( 'Stuck on something? Reach the MagePeople support team and we will help you get it working.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                                    <a href="<?php echo esc_url( 'https://mage-people.com/contact/' ); ?>" target="_blank" rel="noopener" class="rbfw_welcome_card_link">
                                        <?php esc_html_e( 'Contact Support', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        <span class="dashicons dashicons-arrow-right-alt"></span>
                                    </a>
                                </div>

                            </div>
                        </div>

                        <div class="rbfw_welcome_section">
                            <h2><?php esc_html_e( 'Video Tutorials', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>

                            <div class="rbfw_welcome_video_grid">
                                <div class="rbfw_welcome_video_card">
                                    <div class="rbfw_welcome_video_thumb">
                                        <span class="dashicons dashicons-format-video"></span>
                                    </div>
                                    <div class="rbfw_welcome_video_body">
                                        <h3><?php echo wp_kses( 'How to create a <b>Bike/Car</b> booking item (Single Day)', array( 'b' => array() ) ); ?></h3>
                                        <p class="rbfw_welcome_alert">
                                            <span class="dashicons dashicons-info"></span>
                                            <?php esc_html_e( 'Video tutorial coming soon. Please follow the online documentation in the meantime.', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="rbfw_welcome_video_card">
                                    <div class="rbfw_welcome_video_thumb">
                                        <span class="dashicons dashicons-format-video"></span>
                                    </div>
                                    <div class="rbfw_welcome_video_body">
                                        <h3><?php echo wp_kses( 'How to create a <b>Resort</b> booking item', array( 'b' => array() ) ); ?></h3>
                                        <p class="rbfw_welcome_alert">
                                            <span class="dashicons dashicons-info"></span>
                                            <?php esc_html_e( 'Video tutorial coming soon. Please follow the online documentation in the meantime.', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Shortcodes Tab -->
                    <div id="tab-shortcodes" class="rbfw_welcome_tab_content">

                        <div class="rbfw_welcome_section" style="margin-top:0;">
                            <h2><?php esc_html_e( 'All Shortcodes', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                            <p class="rbfw_welcome_section_lead"><?php esc_html_e( 'Drop these shortcodes into any page, post or template to display your rental items.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>

                            <div class="rbfw_welcome_shortcode_grid">
                                <?php foreach ( $shortcodes as $sc ) : ?>
                                    <div class="rbfw_welcome_shortcode_card">
                                        <div class="rbfw_welcome_shortcode_head">
                                            <span class="dashicons <?php echo esc_attr( $sc['icon'] ); ?>"></span>
                                            <h3><?php echo esc_html( $sc['title'] ); ?></h3>
                                        </div>
                                        <div class="rbfw_welcome_shortcode_code">
                                            <code><?php echo esc_html( $sc['shortcode'] ); ?></code>
                                            <button type="button" class="rbfw_welcome_copy_btn" data-copy="<?php echo esc_attr( $sc['shortcode'] ); ?>" aria-label="<?php esc_attr_e( 'Copy shortcode', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                <span class="dashicons dashicons-clipboard"></span>
                                            </button>
                                        </div>
                                        <div class="rbfw_welcome_shortcode_params">
                                            <?php foreach ( $sc['params'] as $param => $desc ) : ?>
                                                <p><code><?php echo esc_html( $param ); ?></code><?php echo esc_html( $desc ); ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if ( ! empty( $sc['demo'] ) ) : ?>
                                            <a href="<?php echo esc_url( $sc['demo'] ); ?>" target="_blank" rel="noopener" class="rbfw_welcome_shortcode_demo">
                                                <span class="dashicons dashicons-external"></span>
                                                <?php esc_html_e( 'View Demo', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>

				<?php if ( ! is_plugin_active( 'booking-and-rental-manager-for-woocommerce/rent-pro.php' ) ) { ?>
                    <div class="rbfw_welcome_cta">
                        <div class="rbfw_welcome_cta_icon"><span class="dashicons dashicons-star-filled"></span></div>
                        <div class="rbfw_welcome_cta_body">
                            <h2><?php esc_html_e( 'Ready to unlock the full feature set?', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                            <p><?php esc_html_e( 'Get Pro and the available addons to enable PayPal/Stripe checkout, branded PDF receipts, custom forms, reporting and more.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <a href="https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/" target="_blank" rel="noopener" class="rbfw_welcome_btn rbfw_welcome_btn_primary">
                            <span class="dashicons dashicons-cart"></span>
                            <?php esc_html_e( 'Buy Pro', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </a>
                    </div>
				<?php } ?>
            </div>
            <style>
				.rbfw_welcome, .rbfw_welcome * { box-sizing: border-box; }
				.rbfw_welcome {
					--rbfw-w-accent1: #3F13A4;
					--rbfw-w-accent2: #2271B1;
					--rbfw-w-primary: #F12971;
					--rbfw-w-heading: #1D2327;
					--rbfw-w-text: #4A5568;
					--rbfw-w-border: #E4E9F2;
					--rbfw-w-bg: #F4F6FA;
					font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
					color: var(--rbfw-w-heading);
					margin: 16px 20px 40px 0;
				}

				/* Hero */
				.rbfw_welcome_hero {
					background: linear-gradient(135deg, var(--rbfw-w-accent1) 0%, var(--rbfw-w-accent2) 100%);
					border-radius: 14px;
					padding: 44px 40px;
					color: #fff;
					box-shadow: 0 10px 30px rgba(63,19,164,.18);
					position: relative;
					overflow: hidden;
				}
				.rbfw_welcome_hero::after {
					content: '';
					position: absolute;
					top: -80px;
					right: -80px;
					width: 280px;
					height: 280px;
					border-radius: 50%;
					background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
					pointer-events: none;
				}
				.rbfw_welcome_hero_badge {
					display: inline-flex;
					align-items: center;
					gap: 6px;
					background: rgba(255,255,255,.16);
					border: 1px solid rgba(255,255,255,.3);
					padding: 5px 14px;
					border-radius: 20px;
					font-size: 12px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: .6px;
					margin-bottom: 14px;
				}
				.rbfw_welcome_hero_badge .dashicons { font-size: 16px; width: 16px; height: 16px; line-height: 1; }
				.rbfw_welcome_hero h1 {
					margin: 0 0 12px;
					padding: 0;
					border: none;
					font-size: 30px;
					font-weight: 800;
					line-height: 1.2;
					color: #fff;
					max-width: 760px;
				}
				.rbfw_welcome_hero p {
					margin: 0 0 26px;
					font-size: 14.5px;
					line-height: 1.65;
					color: rgba(255,255,255,.88);
					max-width: 680px;
				}
				.rbfw_welcome_hero_actions { display: flex; flex-wrap: wrap; gap: 12px; position: relative; z-index: 1; }

				/* Buttons (shared with pro page visual language) */
				.rbfw_welcome_btn {
					display: inline-flex;
					align-items: center;
					gap: 6px;
					justify-content: center;
					height: 42px;
					padding: 0 20px;
					border-radius: 8px;
					font-size: 13.5px;
					font-weight: 700;
					text-decoration: none !important;
					transition: opacity .18s, background .18s, transform .12s;
				}
				.rbfw_welcome_btn:active { transform: scale(.98); }
				.rbfw_welcome_btn .dashicons { font-size: 17px; width: 17px; height: 17px; line-height: 17px; flex-shrink: 0; }
				.rbfw_welcome_btn_primary {
					background: var(--rbfw-w-primary);
					color: #fff !important;
					box-shadow: 0 6px 16px rgba(241,41,113,.3);
				}
				.rbfw_welcome_btn_primary:hover { opacity: .9; color: #fff !important; }
				.rbfw_welcome_btn_ghost_light {
					background: rgba(255,255,255,.18);
					color: #fff !important;
					border: 1px solid rgba(255,255,255,.5);
				}
				.rbfw_welcome_btn_ghost_light:hover { background: rgba(255,255,255,.28); color: #fff !important; }
				.rbfw_welcome_btn_outline_light {
					background: rgba(255,255,255,.08);
					color: #fff !important;
					border: 1px solid rgba(255,255,255,.7);
				}
				.rbfw_welcome_btn_outline_light:hover { background: rgba(255,255,255,.2); color: #fff !important; }

				/* Tabs */
				.rbfw_welcome_tabs_wrap {
					margin-top: 28px;
					background: #fff;
					border: 1px solid var(--rbfw-w-border);
					border-radius: 14px;
					box-shadow: 0 2px 10px rgba(15,23,42,.05);
					overflow: hidden;
				}
				.rbfw_welcome_tabs {
					display: flex;
					gap: 4px;
					margin: 0;
					padding: 8px 8px 0;
					list-style: none;
					background: var(--rbfw-w-bg);
					border-bottom: 1px solid var(--rbfw-w-border);
				}
				.rbfw_welcome_tab_link {
					display: inline-flex;
					align-items: center;
					gap: 6px;
					padding: 10px 16px;
					font-size: 13px;
					font-weight: 600;
					color: var(--rbfw-w-text);
					cursor: pointer;
					border-radius: 8px 8px 0 0;
					transition: background .15s, color .15s;
					user-select: none;
				}
				.rbfw_welcome_tab_link:hover { color: var(--rbfw-w-heading); background: #fff; }
				.rbfw_welcome_tab_link .dashicons { font-size: 16px; width: 16px; height: 16px; }
				.rbfw_welcome_tab_link.current {
					background: #fff;
					color: var(--rbfw-w-accent2);
					box-shadow: 0 -1px 0 var(--rbfw-w-accent2) inset;
				}

				.rbfw_welcome_tab_content {
					display: none;
					padding: 28px 30px 30px;
				}
				.rbfw_welcome_tab_content.current { display: block; }

				/* Section heading */
				.rbfw_welcome_section { margin-top: 8px; }
				.rbfw_welcome_section h2 {
					font-size: 18px;
					font-weight: 800;
					margin: 0 0 6px;
					padding: 0;
					border: none;
					color: var(--rbfw-w-heading);
				}
				.rbfw_welcome_section_lead {
					margin: 0 0 18px;
					font-size: 13.5px;
					color: var(--rbfw-w-text);
				}

				/* Get started cards */
				.rbfw_welcome_card_grid {
					display: grid;
					grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr));
					gap: 16px;
					margin-top: 18px;
				}
				.rbfw_welcome_card {
					background: #fff;
					border: 1px solid var(--rbfw-w-border);
					border-radius: 12px;
					padding: 20px;
					display: flex;
					flex-direction: column;
					transition: transform .18s, box-shadow .18s, border-color .18s;
				}
				.rbfw_welcome_card:hover {
					transform: translateY(-2px);
					border-color: var(--rbfw-w-accent2);
					box-shadow: 0 8px 22px rgba(15,23,42,.08);
				}
				.rbfw_welcome_card_icon {
					width: 42px;
					height: 42px;
					border-radius: 10px;
					background: linear-gradient(135deg, var(--rbfw-w-accent1) 0%, var(--rbfw-w-accent2) 100%);
					display: flex;
					align-items: center;
					justify-content: center;
					margin-bottom: 14px;
					color: #fff;
				}
				.rbfw_welcome_card_icon .dashicons { color: #fff; font-size: 20px; width: 20px; height: 20px; line-height: 1; }
				.rbfw_welcome_card h3 {
					margin: 0 0 8px;
					padding: 0;
					border: none;
					font-size: 15px;
					font-weight: 700;
					color: var(--rbfw-w-heading);
				}
				.rbfw_welcome_card p {
					margin: 0 0 14px;
					font-size: 13px;
					line-height: 1.55;
					color: var(--rbfw-w-text);
					flex: 1;
				}
				.rbfw_welcome_card_link {
					display: inline-flex;
					align-items: center;
					gap: 4px;
					font-size: 13px;
					font-weight: 700;
					color: var(--rbfw-w-accent2) !important;
					text-decoration: none !important;
				}
				.rbfw_welcome_card_link .dashicons { font-size: 14px; width: 14px; height: 14px; transition: transform .18s; }
				.rbfw_welcome_card_link:hover .dashicons { transform: translateX(2px); }
				.rbfw_welcome_card_meta {
					display: inline-flex;
					align-items: center;
					gap: 6px;
					font-size: 12px;
					color: var(--rbfw-w-text);
				}
				.rbfw_welcome_card_meta .dashicons { color: #F59E0B; font-size: 14px; width: 14px; height: 14px; }

				/* Video tutorials */
				.rbfw_welcome_video_grid {
					display: grid;
					grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
					gap: 16px;
					margin-top: 18px;
				}
				.rbfw_welcome_video_card {
					display: flex;
					gap: 16px;
					background: #fff;
					border: 1px solid var(--rbfw-w-border);
					border-radius: 12px;
					padding: 18px;
					transition: transform .18s, box-shadow .18s;
				}
				.rbfw_welcome_video_card:hover {
					transform: translateY(-2px);
					box-shadow: 0 8px 22px rgba(15,23,42,.08);
				}
				.rbfw_welcome_video_thumb {
					flex-shrink: 0;
					width: 86px;
					height: 86px;
					border-radius: 12px;
					background: linear-gradient(135deg, var(--rbfw-w-accent1) 0%, var(--rbfw-w-accent2) 100%);
					display: flex;
					align-items: center;
					justify-content: center;
					color: #fff;
				}
				.rbfw_welcome_video_thumb .dashicons { font-size: 32px; width: 32px; height: 32px; line-height: 1; color: #fff; }
				.rbfw_welcome_video_body { flex: 1; min-width: 0; }
				.rbfw_welcome_video_body h3 {
					margin: 0 0 8px;
					padding: 0;
					border: none;
					font-size: 14px;
					font-weight: 700;
					color: var(--rbfw-w-heading);
					line-height: 1.4;
				}
				.rbfw_welcome_alert {
					display: flex;
					align-items: flex-start;
					gap: 8px;
					margin: 0;
					padding: 10px 12px;
					background: #FFFBEB;
					border: 1px solid #FDE68A;
					border-radius: 8px;
					color: #92400E;
					font-size: 12.5px;
					line-height: 1.5;
				}
				.rbfw_welcome_alert .dashicons { color: #D97706; font-size: 16px; width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

				/* Shortcodes */
				.rbfw_welcome_shortcode_grid {
					display: grid;
					grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr));
					gap: 16px;
					margin-top: 18px;
				}
				.rbfw_welcome_shortcode_card {
					background: #fff;
					border: 1px solid var(--rbfw-w-border);
					border-radius: 12px;
					padding: 18px;
					transition: border-color .18s, transform .18s;
				}
				.rbfw_welcome_shortcode_card:hover {
					border-color: var(--rbfw-w-accent2);
					transform: translateY(-2px);
				}
				.rbfw_welcome_shortcode_head {
					display: flex;
					align-items: center;
					gap: 10px;
					margin-bottom: 12px;
				}
				.rbfw_welcome_shortcode_head .dashicons {
					width: 36px;
					height: 36px;
					font-size: 18px;
					border-radius: 8px;
					background: var(--rbfw-w-bg);
					color: var(--rbfw-w-accent2);
					flex-shrink: 0;
				}
				.rbfw_welcome_shortcode_head h3 {
					margin: 0;
					padding: 0;
					border: none;
					font-size: 14px;
					font-weight: 700;
					color: var(--rbfw-w-heading);
				}
				.rbfw_welcome_shortcode_code {
					position: relative;
					background: var(--rbfw-w-bg);
					border: 1px solid var(--rbfw-w-border);
					border-radius: 8px;
					padding: 10px 44px 10px 12px;
					margin-bottom: 12px;
				}
				.rbfw_welcome_shortcode_code code {
					background: transparent;
					padding: 0;
					color: var(--rbfw-w-accent1);
					font-size: 13px;
					font-weight: 600;
					word-break: break-all;
				}
				.rbfw_welcome_copy_btn {
					position: absolute;
					top: 6px;
					right: 6px;
					width: 30px;
					height: 30px;
					border: none;
					border-radius: 6px;
					background: #fff;
					color: var(--rbfw-w-text);
					cursor: pointer;
					display: flex;
					align-items: center;
					justify-content: center;
					transition: background .15s, color .15s;
				}
				.rbfw_welcome_copy_btn:hover { background: var(--rbfw-w-accent2); color: #fff; }
				.rbfw_welcome_copy_btn.copied { background: #16A34A; color: #fff; }
				.rbfw_welcome_copy_btn .dashicons { font-size: 16px; width: 16px; height: 16px; }
				.rbfw_welcome_shortcode_params { margin: 0 0 12px; }
				.rbfw_welcome_shortcode_params p {
					margin: 0 0 6px;
					font-size: 12.5px;
					line-height: 1.55;
					color: var(--rbfw-w-text);
				}
				.rbfw_welcome_shortcode_params p:last-child { margin-bottom: 0; }
				.rbfw_welcome_shortcode_params code {
					background: #fff;
					border: 1px solid var(--rbfw-w-border);
					padding: 1px 6px;
					border-radius: 4px;
					color: var(--rbfw-w-accent1);
					font-size: 12px;
					font-weight: 600;
					margin-right: 6px;
				}
				.rbfw_welcome_shortcode_demo {
					display: inline-flex;
					align-items: center;
					gap: 6px;
					font-size: 12.5px;
					font-weight: 700;
					color: var(--rbfw-w-accent2) !important;
					text-decoration: none !important;
					padding: 6px 12px;
					border: 1px solid var(--rbfw-w-border);
					border-radius: 6px;
					transition: background .15s, color .15s, border-color .15s;
				}
				.rbfw_welcome_shortcode_demo:hover {
					background: var(--rbfw-w-accent2);
					color: #fff !important;
					border-color: var(--rbfw-w-accent2);
				}
				.rbfw_welcome_shortcode_demo .dashicons { font-size: 14px; width: 14px; height: 14px; }

				/* Final CTA */
				.rbfw_welcome_cta {
					margin-top: 28px;
					display: flex;
					align-items: center;
					gap: 20px;
					padding: 26px 30px;
					background: linear-gradient(135deg, var(--rbfw-w-bg) 0%, #fff 100%);
					border: 1px solid var(--rbfw-w-border);
					border-radius: 14px;
					box-shadow: 0 2px 10px rgba(15,23,42,.05);
				}
				.rbfw_welcome_cta_icon {
					width: 52px;
					height: 52px;
					border-radius: 12px;
					background: linear-gradient(135deg, var(--rbfw-w-accent1) 0%, var(--rbfw-w-accent2) 100%);
					display: flex;
					align-items: center;
					justify-content: center;
					flex-shrink: 0;
					color: #fff;
				}
				.rbfw_welcome_cta_icon .dashicons { color: #fff; font-size: 24px; width: 24px; height: 24px; line-height: 1; }
				.rbfw_welcome_cta_body { flex: 1; min-width: 0; }
				.rbfw_welcome_cta_body h2 {
					margin: 0 0 4px;
					padding: 0;
					border: none;
					font-size: 17px;
					font-weight: 800;
					color: var(--rbfw-w-heading);
				}
				.rbfw_welcome_cta_body p {
					margin: 0;
					font-size: 13px;
					color: var(--rbfw-w-text);
					line-height: 1.5;
				}

				@media (max-width: 782px) {
					.rbfw_welcome { margin-right: 10px; }
					.rbfw_welcome_hero { padding: 30px 22px; }
					.rbfw_welcome_hero h1 { font-size: 22px; }
					.rbfw_welcome_tab_content { padding: 22px 18px 24px; }
					.rbfw_welcome_cta { flex-wrap: wrap; }
				}
            </style>
            <script>
                jQuery(document).ready(function ($) {
                    $('.rbfw_welcome_tab_link').on('click', function () {
                        var tab_id = $(this).data('tab');
                        $('.rbfw_welcome_tab_link').removeClass('current');
                        $('.rbfw_welcome_tab_content').removeClass('current');
                        $(this).addClass('current');
                        $('#' + tab_id).addClass('current');
                    });

                    $('.rbfw_welcome_copy_btn').on('click', function () {
                        var $btn = $(this);
                        var text = $btn.data('copy');
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(text).then(function () {
                                $btn.addClass('copied');
                                setTimeout(function () { $btn.removeClass('copied'); }, 1500);
                            });
                        } else {
                            var $tmp = $('<textarea>').val(text).css({position:'fixed',opacity:0}).appendTo('body');
                            $tmp[0].select();
                            try { document.execCommand('copy'); $btn.addClass('copied'); setTimeout(function () { $btn.removeClass('copied'); }, 1500); } catch (e) {}
                            $tmp.remove();
                        }
                    });
                });
            </script>
			<?php
		}

		/**
		 * Shortcodes shown on the Welcome page.
		 *
		 * Kept as data so adding/listing them does not require editing the
		 * markup. icon = a core Dashicons slug; demo = optional external link.
		 *
		 * @return array[]
		 */
		private function rbfw_welcome_shortcodes() {
			return array(
				array(
					'icon'      => 'list-view',
					'title'     => __( 'Rents – List Style', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rent-list style="list" show="8"]',
					'params'    => array(
						'style' => __( 'grid or list — Default: grid', 'booking-and-rental-manager-for-woocommerce' ),
						'show'  => __( 'Number of items to show (integer). Default: -1 (all).', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => 'https://booking.mage-people.com/rents-list-style/',
				),
				array(
					'icon'      => 'grid-view',
					'title'     => __( 'Rents – Grid Style', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rent-list style="grid" show="8"]',
					'params'    => array(
						'style' => __( 'grid or list — Default: grid', 'booking-and-rental-manager-for-woocommerce' ),
						'show'  => __( 'Number of items to show (integer). Default: -1 (all).', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => 'https://booking.mage-people.com/rents-grid-style/',
				),
				array(
					'icon'      => 'dashboard',
					'title'     => __( 'Bike List – Grid Style', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rent-list style="grid" type="bike_car_sd"]',
					'params'    => array(
						'style' => __( 'grid or list — Default: grid', 'booking-and-rental-manager-for-woocommerce' ),
						'type'  => __( 'bike_car_sd, bike_car_md, resort, equipment, dress, others. Default: show all.', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => 'https://booking.mage-people.com/',
				),
				array(
					'icon'      => 'admin-generic',
					'title'     => __( 'Rent List — All Parameters', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rent-list]',
					'params'    => array(
						'style'                 => __( 'grid or list — Default: grid', 'booking-and-rental-manager-for-woocommerce' ),
						'show'                  => __( 'Number of items to show (integer). Default: -1 (all).', 'booking-and-rental-manager-for-woocommerce' ),
						'order'                 => __( 'ASC or DESC. Default: DESC', 'booking-and-rental-manager-for-woocommerce' ),
						'orderby'               => __( 'WP_Query orderby field, e.g. date, title, menu_order. Default: date', 'booking-and-rental-manager-for-woocommerce' ),
						'type'                  => __( 'Filter by rent type — bike_car_sd, bike_car_md, resort, equipment, dress, others. Default: show all', 'booking-and-rental-manager-for-woocommerce' ),
						'location'              => __( 'Filter by pickup location name. Default: show all', 'booking-and-rental-manager-for-woocommerce' ),
						'category / cat_ids'    => __( 'Comma-separated category IDs or names to filter by. Default: show all', 'booking-and-rental-manager-for-woocommerce' ),
						'columns'               => __( 'Grid columns, 1–5. Default: 3', 'booking-and-rental-manager-for-woocommerce' ),
						'hide-price'            => __( 'yes or no — hide the price on each item. Default: no', 'booking-and-rental-manager-for-woocommerce' ),
						'pagination'            => __( 'yes or no — show pagination links. Default: no', 'booking-and-rental-manager-for-woocommerce' ),
						'left-filter'           => __( 'yes or on — show the sidebar filter panel. Default: off', 'booking-and-rental-manager-for-woocommerce' ),
						'left-*-filter'         => __( 'title/price/location/category/type/feature — on or off, toggles that sidebar filter section (only when left-filter is on). Default: on', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => '',
				),
				array(
					'icon'      => 'search',
					'title'     => __( 'Search Form', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rbfw_search]',
					'params'    => array(
						'search-type'       => __( 'item to show a single-item + pickup/dropoff date search box, or leave empty for the location + category + pickup date search box. Default: empty', 'booking-and-rental-manager-for-woocommerce' ),
						'hide_pickup_date'  => __( 'yes or no — hide the pickup date field (default search box only). Default: no', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => '',
				),
				array(
					'icon'      => 'menu-alt3',
					'title'     => __( 'Search Results', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[search-result]',
					'params'    => array(
						'(none)' => __( 'Reads the submitted search from the URL (requires a valid nonce from the [rbfw_search] form). Add this to the page configured as your Search Results page.', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => '',
				),
				array(
					'icon'      => 'controls-repeat',
					'title'     => __( 'Search Form (Results Page)', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rbfw_search_ac]',
					'params'    => array(
						'search-type'       => __( 'Same options as [rbfw_search]. Pre-fills the fields from the current search (location, type, pickup date read from the URL).', 'booking-and-rental-manager-for-woocommerce' ),
						'hide_pickup_date'  => __( 'yes or no — hide the pickup date field. Default: no', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => '',
				),
				array(
					'icon'      => 'cart',
					'title'     => __( 'Add to Cart / Booking Form', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rent-add-to-cart id="123"]',
					'params'    => array(
						'id'      => __( 'Required. The Rent Item post ID to render the booking/add-to-cart form for.', 'booking-and-rental-manager-for-woocommerce' ),
						'backend' => __( 'Internal use — renders without the WooCommerce single-product wrapper hooks. Default: not set.', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => '',
				),
				array(
					'icon'      => 'image-filter',
					'title'     => __( 'Left Filter Sidebar', 'booking-and-rental-manager-for-woocommerce' ),
					'shortcode' => '[rbfw_left_filter]',
					'params'    => array(
						'title-filter'    => __( 'on or off — show the title/keyword search box. Default: on', 'booking-and-rental-manager-for-woocommerce' ),
						'price-filter'    => __( 'on or off — show the price range filter. Default: on', 'booking-and-rental-manager-for-woocommerce' ),
						'location-filter' => __( 'on or off — show the location filter. Default: on', 'booking-and-rental-manager-for-woocommerce' ),
						'category-filter' => __( 'on or off — show the category filter. Default: on', 'booking-and-rental-manager-for-woocommerce' ),
						'type-filter'     => __( 'on or off — show the rent type filter. Default: on', 'booking-and-rental-manager-for-woocommerce' ),
						'feature-filter'  => __( 'on or off — show the item feature filter. Default: on', 'booking-and-rental-manager-for-woocommerce' ),
					),
					'demo'      => '',
				),
			);
		}
	}
	new RBFW_Welcome();
