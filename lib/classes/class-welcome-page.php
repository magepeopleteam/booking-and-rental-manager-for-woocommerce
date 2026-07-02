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

		/**
		 * Shortcodes documented on the Shortcodes tab.
		 *
		 * Kept as data (same pattern as the Get PRO page's feature list) so
		 * rows stay easy to add without touching markup. Every entry mirrors
		 * what the shortcode actually accepts in inc/rbfw_shortcodes.php
		 * (shortcode_atts) — keep the two in sync when attributes change.
		 *
		 * Fields: name, desc, code (copyable example), params (array of
		 * [attribute, description]), optional note, optional demo URL.
		 *
		 * @return array[]
		 */
		private function rbfw_welcome_shortcodes() {
			return array(
				array(
					'name'   => __( 'Rent Item List', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'   => __( 'Displays your rental items in a grid or list layout, with optional filtering by type, location and category, plus an optional filter sidebar.', 'booking-and-rental-manager-for-woocommerce' ),
					'code'   => '[rent-list style="grid" show="8" columns="3"]',
					'params' => array(
						array( 'style', __( 'Layout: grid or list. Default: grid', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'show', __( 'Items per page (integer). Default: -1 (show all)', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'columns', __( 'Grid columns: 2, 3, 4 or 5. Default: 3', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'type', __( 'Show only one item type: bike_car_sd, bike_car_md, resort, equipment, dress or others. Default: all types', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'location', __( 'Show only items with this pickup location (location name)', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'category', __( 'Show only these categories — comma-separated category IDs, e.g. category="12,15"', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'cat_ids', __( 'Legacy alias of category — used only when category is empty', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'order', __( 'Sort direction: ASC or DESC. Default: DESC', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'orderby', __( 'Sort field: date, title, menu_order, rand, meta_value or meta_value_num. Default: date', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'meta_key', __( 'Custom field key to sort by — use together with orderby="meta_value" or "meta_value_num"', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'left-filter', __( 'Show the filter sidebar next to the list: yes (or on) to enable. Default: off', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'left-*-filter', __( 'Show/hide individual sidebar blocks — left-title-filter, left-price-filter, left-location-filter, left-category-filter, left-type-filter, left-feature-filter. Each accepts on or off. Default: on', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'hide-price', __( 'Hide item prices in the list: yes or no. Default: no', 'booking-and-rental-manager-for-woocommerce' ) ),
					),
					'demo'   => 'https://booking.mage-people.com/rents-grid-style/',
				),
				array(
					'name'   => __( 'Search Form', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'   => __( 'Front-end search form. The default mode searches by rental type, pickup location and pickup date; the item mode searches a specific item with pickup and drop-off dates.', 'booking-and-rental-manager-for-woocommerce' ),
					'code'   => '[rbfw_search]',
					'params' => array(
						array( 'search-type', __( 'Form mode: leave empty for the type / location / date form, or item for the specific-item + pickup & drop-off dates form', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'hide_pickup_date', __( 'Hide the pickup date field: yes or no. Default: no', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'hide_location', __( 'Hide the pickup location dropdown: yes or no. Default: no', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'type_label', __( 'Custom placeholder label for the rental type dropdown', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'location_label', __( 'Custom placeholder label for the pickup location dropdown', 'booking-and-rental-manager-for-woocommerce' ) ),
					),
					'note'   => __( 'Pair it with [search-result] placed on your search results page.', 'booking-and-rental-manager-for-woocommerce' ),
					'demo'   => 'https://booking.mage-people.com/',
				),
				array(
					'name'   => __( 'Search Results', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'   => __( 'Displays the rental items matching the visitor\'s search. Place it on the page that receives the search form submission — it reads the search automatically.', 'booking-and-rental-manager-for-woocommerce' ),
					'code'   => '[search-result]',
					'params' => array(),
					'note'   => __( 'No attributes needed — it inherits everything from the submitted search.', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'   => __( 'Search Form (Keeps Selection)', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'   => __( 'Same search form, but it keeps the visitor\'s previous type, location and date pre-selected. Use it on the results page above [search-result] so visitors can refine their search.', 'booking-and-rental-manager-for-woocommerce' ),
					'code'   => '[rbfw_search_ac]',
					'params' => array(),
					'note'   => __( 'No attributes needed — it re-fills itself from the submitted search.', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'   => __( 'Single Item Booking Form', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'   => __( 'Embeds the full booking / add-to-cart form of one rental item on any page — the form matches the item type (single day, multiple day, resort, etc.).', 'booking-and-rental-manager-for-woocommerce' ),
					'code'   => '[rent-add-to-cart id="123"]',
					'params' => array(
						array( 'id', __( 'The rental item ID (required). Find it in Rent Item → All Items', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'backend', __( 'Internal — used for admin-side order creation. Leave empty on the front end', 'booking-and-rental-manager-for-woocommerce' ) ),
					),
				),
				array(
					'name'   => __( 'Filter Sidebar', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'   => __( 'Standalone filter sidebar — the same one [rent-list] embeds when left-filter="yes". Each block can be switched off individually.', 'booking-and-rental-manager-for-woocommerce' ),
					'code'   => '[rbfw_left_filter]',
					'params' => array(
						array( 'title-filter', __( 'Show the title search block: on or off. Default: on', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'price-filter', __( 'Show the price range block: on or off. Default: on', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'location-filter', __( 'Show the location block: on or off. Default: on', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'category-filter', __( 'Show the category block: on or off. Default: on', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'type-filter', __( 'Show the rent type block: on or off. Default: on', 'booking-and-rental-manager-for-woocommerce' ) ),
						array( 'feature-filter', __( 'Show the features block: on or off. Default: on', 'booking-and-rental-manager-for-woocommerce' ) ),
					),
				),
				array(
					'name'   => __( 'Thank You / Booking Confirmation', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'   => __( 'Shows the booking confirmation details after checkout. The plugin creates a "Thank You" page with this shortcode automatically — add it manually only on a custom confirmation page.', 'booking-and-rental-manager-for-woocommerce' ),
					'code'   => '[rbfw_thankyou]',
					'params' => array(),
					'note'   => __( 'No attributes needed.', 'booking-and-rental-manager-for-woocommerce' ),
				),
			);
		}

		public function RBFW_welcome_page_callback() {

			$shortcodes  = $this->rbfw_welcome_shortcodes();
			$quick_links = array(
				array(
					'icon'  => 'plus-alt',
					'title' => __( 'Create a Rental Item', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'  => __( 'Add your first bike, car, resort, dress or equipment item.', 'booking-and-rental-manager-for-woocommerce' ),
					'url'   => admin_url( 'post-new.php?post_type=rbfw_item' ),
				),
				array(
					'icon'  => 'admin-generic',
					'title' => __( 'Configure Settings', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'  => __( 'Currency, labels, emails, inventory rules and more.', 'booking-and-rental-manager-for-woocommerce' ),
					'url'   => admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_settings_page' ),
				),
				array(
					'icon'  => 'chart-bar',
					'title' => __( 'Track Your Inventory', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'  => __( 'See day-by-day stock, sold quantities and extra services.', 'booking-and-rental-manager-for-woocommerce' ),
					'url'   => admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_inventory' ),
				),
				array(
					'icon'  => 'list-view',
					'title' => __( 'Manage Orders', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'  => __( 'Review bookings and keep every rental on schedule.', 'booking-and-rental-manager-for-woocommerce' ),
					'url'   => admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_order' ),
				),
			);
			$tutorials = array(
				__( 'How to create a Bike/Car for Single Day booking or rental item?', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'How to create a Resort booking or rental item?', 'booking-and-rental-manager-for-woocommerce' ),
			);
			?>
            <div class="wrap"></div>
            <div class="rbfw_wlc">
				<?php settings_errors(); ?>

                <!-- Hero -->
                <div class="rbfw_wlc_hero">
                    <div class="rbfw_wlc_hero_badge"><?php esc_html_e( 'Welcome', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                    <h1><?php esc_html_e( 'Welcome to Booking and Rental Manager', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
                    <p><?php esc_html_e( 'A complete rental & booking solution for your business. It is perfect to offer all types of rental and booking services.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                    <div class="rbfw_wlc_hero_actions">
                        <a href="<?php echo esc_url( 'https://booking.mage-people.com/' ); ?>" class="rbfw_wlc_btn rbfw_wlc_btn_primary" target="_blank" rel="noopener"><?php esc_html_e( 'View Demo', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                        <a href="<?php echo esc_url( 'https://docs.mage-people.com/rent-and-booking-manager/' ); ?>" class="rbfw_wlc_btn rbfw_wlc_btn_ghost" target="_blank" rel="noopener"><?php esc_html_e( 'Documentation', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="rbfw_wlc_tabs" role="tablist">
                    <button type="button" class="rbfw_wlc_tab current" data-tab="rbfw_wlc_tab_start"><span class="dashicons dashicons-flag"></span><?php esc_html_e( 'Getting Started', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                    <button type="button" class="rbfw_wlc_tab" data-tab="rbfw_wlc_tab_shortcodes"><span class="dashicons dashicons-shortcode"></span><?php esc_html_e( 'Shortcodes', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                </div>

                <!-- Getting Started tab -->
                <div id="rbfw_wlc_tab_start" class="rbfw_wlc_panel current">

                    <div class="rbfw_wlc_section_title"><?php esc_html_e( 'Quick Start', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                    <div class="rbfw_wlc_quick_grid">
						<?php foreach ( $quick_links as $link ) : ?>
                            <a class="rbfw_wlc_quick_card" href="<?php echo esc_url( $link['url'] ); ?>">
                                <span class="rbfw_wlc_quick_icon"><span class="dashicons dashicons-<?php echo esc_attr( $link['icon'] ); ?>"></span></span>
                                <span class="rbfw_wlc_quick_body">
                                    <strong><?php echo esc_html( $link['title'] ); ?></strong>
                                    <span><?php echo esc_html( $link['desc'] ); ?></span>
                                </span>
                                <span class="rbfw_wlc_quick_arrow dashicons dashicons-arrow-right-alt2"></span>
                            </a>
						<?php endforeach; ?>
                    </div>

                    <div class="rbfw_wlc_section_title"><?php esc_html_e( 'Video Tutorials', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                    <div class="rbfw_wlc_tut_list">
						<?php foreach ( $tutorials as $tutorial ) : ?>
                            <div class="rbfw_wlc_tut_card">
                                <span class="rbfw_wlc_tut_icon"><span class="dashicons dashicons-video-alt3"></span></span>
                                <div class="rbfw_wlc_tut_body">
                                    <strong><?php echo esc_html( $tutorial ); ?></strong>
                                    <p><?php esc_html_e( 'Video tutorial coming soon. Meanwhile, please follow the online documentation.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                                </div>
                                <a class="rbfw_wlc_btn rbfw_wlc_btn_outline" href="<?php echo esc_url( 'https://docs.mage-people.com/rent-and-booking-manager/' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Read Docs', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>

                <!-- Shortcodes tab -->
                <div id="rbfw_wlc_tab_shortcodes" class="rbfw_wlc_panel">
                    <div class="rbfw_wlc_section_title"><?php esc_html_e( 'All Shortcodes', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                    <div class="rbfw_wlc_sc_list">
						<?php foreach ( $shortcodes as $sc ) : ?>
                            <div class="rbfw_wlc_sc_card">
                                <div class="rbfw_wlc_sc_head">
                                    <div class="rbfw_wlc_sc_title">
                                        <strong class="rbfw_wlc_sc_name"><?php echo esc_html( $sc['name'] ); ?></strong>
                                        <p class="rbfw_wlc_sc_desc"><?php echo esc_html( $sc['desc'] ); ?></p>
                                    </div>
									<?php if ( ! empty( $sc['demo'] ) ) : ?>
                                        <a class="rbfw_wlc_btn rbfw_wlc_btn_outline rbfw_wlc_btn_sm" href="<?php echo esc_url( $sc['demo'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View Demo', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
									<?php endif; ?>
                                </div>
                                <div class="rbfw_wlc_sc_code_row">
                                    <code class="rbfw_wlc_sc_code"><?php echo esc_html( $sc['code'] ); ?></code>
                                    <button type="button" class="rbfw_wlc_sc_copy" data-code="<?php echo esc_attr( $sc['code'] ); ?>">
                                        <span class="dashicons dashicons-clipboard"></span><span class="rbfw_wlc_sc_copy_label"><?php esc_html_e( 'Copy', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                    </button>
                                </div>
								<?php if ( ! empty( $sc['params'] ) ) : ?>
                                    <div class="rbfw_wlc_sc_params_label"><?php esc_html_e( 'Attributes', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
                                    <ul class="rbfw_wlc_sc_params">
										<?php foreach ( $sc['params'] as $param ) : ?>
                                            <li><code><?php echo esc_html( $param[0] ); ?></code><span><?php echo esc_html( $param[1] ); ?></span></li>
										<?php endforeach; ?>
                                    </ul>
								<?php endif; ?>
								<?php if ( ! empty( $sc['note'] ) ) : ?>
                                    <p class="rbfw_wlc_sc_note"><span class="dashicons dashicons-info-outline"></span><?php echo esc_html( $sc['note'] ); ?></p>
								<?php endif; ?>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>

				<?php if ( ! is_plugin_active( 'booking-and-rental-manager-for-woocommerce/rent-pro.php' ) ) { ?>
                    <div class="rbfw_wlc_cta">
                        <div class="rbfw_wlc_cta_body">
                            <h2><?php esc_html_e( 'Unlock every feature with Pro', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                            <p><?php esc_html_e( 'Get Pro and other available addons to get all features.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        </div>
                        <a href="<?php echo esc_url( 'https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/' ); ?>" target="_blank" rel="noopener" class="rbfw_wlc_btn rbfw_wlc_btn_primary"><?php esc_html_e( 'Buy Pro', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
                    </div>
				<?php } ?>
            </div>
            <style>
				.rbfw_wlc, .rbfw_wlc * { box-sizing: border-box; }
				.rbfw_wlc {
					--rbfw-wlc-accent1: #3F13A4;
					--rbfw-wlc-accent2: #2271B1;
					--rbfw-wlc-primary: #F12971;
					--rbfw-wlc-heading: #1D2327;
					--rbfw-wlc-text: #4A5568;
					--rbfw-wlc-border: #E4E9F2;
					--rbfw-wlc-bg: #F4F6FA;
					font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
					color: var(--rbfw-wlc-heading);
					margin: 16px 20px 40px 0;
				}

				/* Hero */
				.rbfw_wlc_hero {
					background: linear-gradient(135deg, var(--rbfw-wlc-accent1) 0%, var(--rbfw-wlc-accent2) 100%);
					border-radius: 14px;
					padding: 44px 40px;
					color: #fff;
					box-shadow: 0 10px 30px rgba(63,19,164,.18);
				}
				.rbfw_wlc_hero_badge {
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
				.rbfw_wlc_hero h1 { margin: 0 0 12px; padding: 0; border: none; font-size: 28px; font-weight: 800; line-height: 1.25; color: #fff; max-width: 720px; }
				.rbfw_wlc_hero p { margin: 0 0 26px; font-size: 14.5px; line-height: 1.65; color: rgba(255,255,255,.88); max-width: 640px; }
				.rbfw_wlc_hero_actions { display: flex; flex-wrap: wrap; gap: 12px; }

				/* Buttons */
				.rbfw_wlc_btn {
					display: inline-flex; align-items: center; justify-content: center;
					height: 42px; padding: 0 22px; border-radius: 8px;
					font-size: 13.5px; font-weight: 700; text-decoration: none !important;
					transition: opacity .18s, background .18s, transform .12s;
					cursor: pointer;
				}
				.rbfw_wlc_btn:active { transform: scale(.98); }
				.rbfw_wlc_btn_primary { background: var(--rbfw-wlc-primary); color: #fff !important; box-shadow: 0 6px 16px rgba(241,41,113,.3); }
				.rbfw_wlc_btn_primary:hover { opacity: .9; }
				.rbfw_wlc_btn_ghost { background: rgba(255,255,255,.12); color: #fff !important; border: 1px solid rgba(255,255,255,.35); }
				.rbfw_wlc_btn_ghost:hover { background: rgba(255,255,255,.2); }
				.rbfw_wlc_btn_outline { background: #fff; color: var(--rbfw-wlc-accent2) !important; border: 1px solid var(--rbfw-wlc-accent2); flex-shrink: 0; }
				.rbfw_wlc_btn_outline:hover { background: var(--rbfw-wlc-accent2); color: #fff !important; }
				.rbfw_wlc_btn_sm { height: 32px; padding: 0 14px; font-size: 12.5px; border-radius: 7px; }

				/* Tabs */
				.rbfw_wlc_tabs {
					display: inline-flex; gap: 4px; margin-top: 28px;
					background: #fff; border: 1px solid var(--rbfw-wlc-border);
					border-radius: 10px; padding: 4px;
					box-shadow: 0 2px 10px rgba(15,23,42,.05);
				}
				.rbfw_wlc_tab {
					display: inline-flex; align-items: center; gap: 7px;
					border: none; background: transparent; cursor: pointer;
					padding: 9px 18px; border-radius: 7px;
					font-family: inherit; font-size: 13px; font-weight: 700;
					color: var(--rbfw-wlc-text);
					transition: background .15s, color .15s;
				}
				.rbfw_wlc_tab .dashicons { font-size: 16px; width: 16px; height: 16px; }
				.rbfw_wlc_tab:hover { color: var(--rbfw-wlc-heading); }
				.rbfw_wlc_tab.current {
					background: linear-gradient(135deg, var(--rbfw-wlc-accent1) 0%, var(--rbfw-wlc-accent2) 100%);
					color: #fff;
				}

				/* Panels */
				.rbfw_wlc_panel { display: none; }
				.rbfw_wlc_panel.current { display: block; }
				.rbfw_wlc_section_title { font-size: 17px; font-weight: 800; margin: 30px 0 16px; color: var(--rbfw-wlc-heading); }

				/* Quick start grid */
				.rbfw_wlc_quick_grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr)); gap: 14px; }
				.rbfw_wlc_quick_card {
					display: flex; align-items: center; gap: 14px;
					background: #fff; border: 1px solid var(--rbfw-wlc-border); border-radius: 12px;
					padding: 18px; text-decoration: none; color: inherit;
					box-shadow: 0 2px 10px rgba(15,23,42,.05);
					transition: border-color .15s, box-shadow .15s, transform .12s;
				}
				.rbfw_wlc_quick_card:hover {
					border-color: var(--rbfw-wlc-accent2);
					box-shadow: 0 6px 18px rgba(34,113,177,.14);
					transform: translateY(-1px);
					color: inherit;
				}
				.rbfw_wlc_quick_icon {
					width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
					background: linear-gradient(135deg, var(--rbfw-wlc-accent1) 0%, var(--rbfw-wlc-accent2) 100%);
					display: flex; align-items: center; justify-content: center;
				}
				.rbfw_wlc_quick_icon .dashicons { color: #fff; font-size: 20px; width: 20px; height: 20px; }
				.rbfw_wlc_quick_body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
				.rbfw_wlc_quick_body strong { font-size: 14px; font-weight: 700; color: var(--rbfw-wlc-heading); }
				.rbfw_wlc_quick_body span { font-size: 12.5px; line-height: 1.5; color: var(--rbfw-wlc-text); }
				.rbfw_wlc_quick_arrow { color: var(--rbfw-wlc-accent2); flex-shrink: 0; }

				/* Tutorial cards */
				.rbfw_wlc_tut_list { display: flex; flex-direction: column; gap: 14px; }
				.rbfw_wlc_tut_card {
					display: flex; align-items: center; gap: 16px;
					background: #fff; border: 1px solid var(--rbfw-wlc-border); border-radius: 12px;
					padding: 18px 20px; box-shadow: 0 2px 10px rgba(15,23,42,.05);
				}
				.rbfw_wlc_tut_icon {
					width: 46px; height: 46px; border-radius: 10px; flex-shrink: 0;
					background: var(--rbfw-wlc-bg);
					display: flex; align-items: center; justify-content: center;
				}
				.rbfw_wlc_tut_icon .dashicons { color: var(--rbfw-wlc-accent2); font-size: 22px; width: 22px; height: 22px; }
				.rbfw_wlc_tut_body { flex: 1; min-width: 0; }
				.rbfw_wlc_tut_body strong { display: block; font-size: 14px; font-weight: 700; margin-bottom: 3px; }
				.rbfw_wlc_tut_body p { margin: 0; font-size: 12.5px; line-height: 1.55; color: var(--rbfw-wlc-text); }

				/* Shortcode cards — one per row: the attribute lists are the
				   content here, so give them the full readable width. */
				.rbfw_wlc_sc_list { display: flex; flex-direction: column; gap: 14px; }
				.rbfw_wlc_sc_card {
					background: #fff; border: 1px solid var(--rbfw-wlc-border); border-radius: 12px;
					padding: 22px 24px; box-shadow: 0 2px 10px rgba(15,23,42,.05);
					display: flex; flex-direction: column; gap: 14px;
				}
				.rbfw_wlc_sc_head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
				.rbfw_wlc_sc_title { flex: 1; min-width: 0; }
				.rbfw_wlc_sc_name { display: block; font-size: 15px; font-weight: 700; margin-bottom: 4px; }
				.rbfw_wlc_sc_desc { margin: 0; font-size: 12.5px; line-height: 1.6; color: var(--rbfw-wlc-text); max-width: 760px; }
				.rbfw_wlc_sc_params_label {
					font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px;
					color: var(--rbfw-wlc-text); margin-top: 2px;
				}
				.rbfw_wlc_sc_note {
					display: flex; align-items: center; gap: 7px; margin: 0;
					background: var(--rbfw-wlc-bg); border-radius: 8px; padding: 9px 12px;
					font-size: 12.5px; color: var(--rbfw-wlc-text);
				}
				.rbfw_wlc_sc_note .dashicons { font-size: 15px; width: 15px; height: 15px; color: var(--rbfw-wlc-accent2); flex-shrink: 0; }
				.rbfw_wlc_sc_code_row { display: flex; align-items: stretch; gap: 8px; }
				.rbfw_wlc_sc_code {
					flex: 1; min-width: 0; display: flex; align-items: center;
					background: var(--rbfw-wlc-bg); border: 1px solid var(--rbfw-wlc-border);
					border-radius: 8px; padding: 9px 12px;
					font-size: 12.5px; overflow-x: auto; white-space: nowrap;
					color: var(--rbfw-wlc-accent1);
				}
				.rbfw_wlc_sc_copy {
					display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
					background: #fff; border: 1px solid var(--rbfw-wlc-border); border-radius: 8px;
					padding: 0 12px; cursor: pointer;
					font-family: inherit; font-size: 12px; font-weight: 700; color: var(--rbfw-wlc-text);
					transition: border-color .15s, color .15s;
				}
				.rbfw_wlc_sc_copy:hover { border-color: var(--rbfw-wlc-accent2); color: var(--rbfw-wlc-accent2); }
				.rbfw_wlc_sc_copy .dashicons { font-size: 15px; width: 15px; height: 15px; }
				.rbfw_wlc_sc_copy.copied { border-color: #16A34A; color: #16A34A; }
				.rbfw_wlc_sc_params { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; }
				.rbfw_wlc_sc_params li {
					display: flex; align-items: baseline; gap: 12px;
					font-size: 12.5px; line-height: 1.55; color: var(--rbfw-wlc-text);
					padding: 8px 0; border-bottom: 1px dashed var(--rbfw-wlc-border);
				}
				.rbfw_wlc_sc_params li:last-child { border-bottom: none; padding-bottom: 0; }
				.rbfw_wlc_sc_params li:first-child { padding-top: 0; }
				.rbfw_wlc_sc_params li code {
					background: var(--rbfw-wlc-bg); border-radius: 5px; padding: 2px 8px;
					font-size: 12px; color: var(--rbfw-wlc-accent1); flex-shrink: 0;
					min-width: 130px; text-align: left;
				}
				@media (max-width: 600px) {
					.rbfw_wlc_sc_params li { flex-direction: column; gap: 4px; }
					.rbfw_wlc_sc_params li code { min-width: 0; align-self: flex-start; }
					.rbfw_wlc_sc_head { flex-direction: column; }
				}

				/* Pro CTA */
				.rbfw_wlc_cta {
					margin-top: 36px; display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
					background: linear-gradient(135deg, var(--rbfw-wlc-accent1) 0%, var(--rbfw-wlc-accent2) 100%);
					border-radius: 14px; padding: 28px 32px; color: #fff;
					box-shadow: 0 10px 30px rgba(63,19,164,.18);
				}
				.rbfw_wlc_cta_body { flex: 1; min-width: 220px; }
				.rbfw_wlc_cta h2 { margin: 0 0 6px; padding: 0; border: none; font-size: 19px; font-weight: 800; color: #fff; }
				.rbfw_wlc_cta p { margin: 0; font-size: 13.5px; color: rgba(255,255,255,.85); }

				@media (max-width: 782px) {
					.rbfw_wlc { margin-right: 10px; }
					.rbfw_wlc_hero { padding: 30px 24px; }
					.rbfw_wlc_hero h1 { font-size: 22px; }
					.rbfw_wlc_tabs { display: flex; }
					.rbfw_wlc_tab { flex: 1; justify-content: center; }
					.rbfw_wlc_tut_card { flex-wrap: wrap; }
				}
            </style>
            <script>
                jQuery(document).ready(function ($) {
                    $('.rbfw_wlc_tab').on('click', function () {
                        var tab_id = $(this).attr('data-tab');
                        $('.rbfw_wlc_tab').removeClass('current');
                        $('.rbfw_wlc_panel').removeClass('current');
                        $(this).addClass('current');
                        $('#' + tab_id).addClass('current');
                    });
                    $('.rbfw_wlc_sc_copy').on('click', function () {
                        var $btn = $(this);
                        var code = $btn.attr('data-code');
                        var done = function () {
                            $btn.addClass('copied');
                            $btn.find('.rbfw_wlc_sc_copy_label').text('<?php echo esc_js( __( 'Copied!', 'booking-and-rental-manager-for-woocommerce' ) ); ?>');
                            setTimeout(function () {
                                $btn.removeClass('copied');
                                $btn.find('.rbfw_wlc_sc_copy_label').text('<?php echo esc_js( __( 'Copy', 'booking-and-rental-manager-for-woocommerce' ) ); ?>');
                            }, 1600);
                        };
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(code).then(done);
                        } else {
                            var $tmp = $('<textarea>').val(code).appendTo('body').select();
                            document.execCommand('copy');
                            $tmp.remove();
                            done();
                        }
                    });
                });
            </script>
			<?php
		}
	}
	new RBFW_Welcome();
