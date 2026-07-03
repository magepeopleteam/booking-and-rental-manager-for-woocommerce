<?php
function rbfw_woo_install_check() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        return 'Yes';
    } elseif ( is_dir( $plugin_dir ) ) {
        return 'Installed But Not Active';
    } else {
        return 'No';
    }
}

/**
 * Global wrappers around the capability helpers on RBFW_Function.
 *
 * These exist for templates and any code that may run before RBFW_Function is
 * loaded (e.g. WP-CLI), mirroring the WP-CLI fallback pattern used elsewhere.
 * They are the single source of truth for runtime WooCommerce branching.
 */
if ( ! function_exists( 'rbfw_has_woocommerce' ) ) {
    function rbfw_has_woocommerce() {
        if ( class_exists( 'RBFW_Function' ) ) {
            return RBFW_Function::has_woocommerce();
        }
        return class_exists( 'WooCommerce' );
    }
}

if ( ! function_exists( 'rbfw_booking_mode' ) ) {
    function rbfw_booking_mode() {
        if ( class_exists( 'RBFW_Function' ) ) {
            return RBFW_Function::booking_mode();
        }
        if ( ! rbfw_has_woocommerce() ) {
            return 'standalone';
        }
        $options = get_option( 'rbfw_basic_payment_settings' );
        $mode    = isset( $options['rbfw_booking_mode'] ) ? $options['rbfw_booking_mode'] : 'woocommerce';
        return ( $mode === 'standalone' ) ? 'standalone' : 'woocommerce';
    }
}

if ( ! function_exists( 'rbfw_use_wc' ) ) {
    function rbfw_use_wc() {
        if ( class_exists( 'RBFW_Function' ) ) {
            return RBFW_Function::use_wc();
        }
        return rbfw_has_woocommerce() && rbfw_booking_mode() === 'woocommerce';
    }
}

/**
 * Helpful, dismissible admin notice shown when WooCommerce is inactive, explaining that the
 * plugin has switched to Standalone booking mode. Replaces the old forced "install WooCommerce"
 * error now that WooCommerce is optional.
 */
add_action( 'admin_init', 'rbfw_handle_standalone_notice_dismiss' );
function rbfw_handle_standalone_notice_dismiss() {
    if ( isset( $_GET['rbfw_dismiss_standalone'] ) && $_GET['rbfw_dismiss_standalone'] === '1' ) {
        if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'rbfw_dismiss_standalone' ) && current_user_can( 'manage_options' ) ) {
            update_option( 'rbfw_standalone_dismissed', 'yes' );
        }
    }
}

add_action( 'admin_notices', 'rbfw_standalone_mode_notice' );
function rbfw_standalone_mode_notice() {
    if ( rbfw_has_woocommerce() ) {
        return;
    }
    if ( get_option( 'rbfw_standalone_dismissed' ) === 'yes' ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $dismiss_url = wp_nonce_url( add_query_arg( 'rbfw_dismiss_standalone', '1' ), 'rbfw_dismiss_standalone' );
    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong><?php esc_html_e( 'Booking & Rental Manager is running in Standalone mode.', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
            <?php esc_html_e( 'WooCommerce is not active, so bookings are handled internally. Activate WooCommerce any time to use its cart, checkout and order flow.', 'booking-and-rental-manager-for-woocommerce' ); ?>
            <a href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Continue without WooCommerce', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
        </p>
    </div>
    <?php
}

/**
 * Detect dangerous serialized payloads (objects/custom classes).
 *
 * @param string $value Serialized string.
 *
 * @return bool
 */
function rbfw_feature_category_has_disallowed_types( $value ) {
    return (bool) preg_match( '/(^|;)(O|C)\:\d+\:\"/i', $value );
}

/**
 * Recursively sanitize feature category arrays without depending on plugin classes.
 *
 * @param array $value
 *
 * @return array
 */
function rbfw_sanitize_feature_category_array( $value ) {
    if ( class_exists( 'RBFW_Function' ) ) {
        return RBFW_Function::data_sanitize( $value );
    }

    array_walk_recursive(
        $value,
        static function ( &$item ) {
            if ( is_scalar( $item ) ) {
                $item = sanitize_text_field( $item );
            } else {
                $item = '';
            }
        }
    );

    return $value;
}

/**
 * Convert raw meta into a safe, sanitized feature category array.
 *
 * @param mixed $value
 *
 * @return array
 */
function rbfw_prepare_feature_category_meta_value( $value ) {
    if ( empty( $value ) ) {
        return array();
    }

    if ( is_string( $value ) ) {
        $value = wp_unslash( $value );

        if ( ! is_serialized( $value ) ) {
            return array();
        }

        if ( rbfw_feature_category_has_disallowed_types( $value ) ) {
            return array();
        }

        if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
            $value = @unserialize( $value, array( 'allowed_classes' => false ) );
        } else {
            $value = @unserialize( $value );
            if ( is_object( $value ) ) {
                return array();
            }
        }
    } elseif ( is_array( $value ) ) {
        $value = wp_unslash( $value );
    } else {
        return array();
    }

    if ( ! is_array( $value ) ) {
        return array();
    }

    return rbfw_sanitize_feature_category_array( $value );
}

/**
 * Flatten posted rent-type values into a list of names.
 *
 * @param mixed $raw Posted rbfw_categories value (array or string).
 * @return string[]
 */
function rbfw_normalize_rent_type_input( $raw ) {
    $names = array();

    if ( is_string( $raw ) ) {
        $raw = array( $raw );
    }

    if ( ! is_array( $raw ) ) {
        return array();
    }

    foreach ( $raw as $item ) {
        if ( is_array( $item ) ) {
            $names = array_merge( $names, rbfw_normalize_rent_type_input( $item ) );
            continue;
        }

        $item = sanitize_text_field( (string) $item );
        if ( '' === $item ) {
            continue;
        }

        foreach ( explode( ',', $item ) as $part ) {
            $part = trim( $part );
            if ( '' !== $part ) {
                $names[] = $part;
            }
        }
    }

    return $names;
}

/**
 * Map of lowercase rent-type name => canonical taxonomy term name.
 *
 * @return array<string, string>
 */
function rbfw_get_valid_rent_type_names() {
    $terms = get_terms(
        array(
            'taxonomy'   => 'rbfw_item_caregory',
            'hide_empty' => false,
        )
    );

    $valid = array();
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $valid[ strtolower( $term->name ) ] = $term->name;
        }
    }

    return $valid;
}

/**
 * Keep only rent types that exist in rbfw_item_caregory.
 *
 * @param mixed $raw Posted rbfw_categories value.
 * @return string[]
 */
function rbfw_sanitize_rent_type_categories( $raw ) {
    $names = rbfw_normalize_rent_type_input( $raw );
    $valid = rbfw_get_valid_rent_type_names();
    $out   = array();

    foreach ( $names as $name ) {
        $key = strtolower( $name );
        if ( isset( $valid[ $key ] ) ) {
            $out[] = $valid[ $key ];
        }
    }

    return array_values( array_unique( $out ) );
}

/**
 * Helper to fetch the sanitized feature category meta.
 *
 * @param int $post_id
 *
 * @return array
 */
function rbfw_get_feature_category_meta( $post_id ) {
    $raw_value = get_post_meta( $post_id, 'rbfw_feature_category', true );

    return rbfw_prepare_feature_category_meta_value( $raw_value );
}

/**
 * Sanitize feature category meta on save regardless of the source.
 *
 * @param mixed  $meta_value
 * @param string $meta_key
 * @param string $meta_type
 *
 * @return array
 */
function rbfw_filter_feature_category_meta( $meta_value, $meta_key, $meta_type ) {
    return rbfw_prepare_feature_category_meta_value( $meta_value );
}
add_filter( 'sanitize_post_meta_rbfw_feature_category', 'rbfw_filter_feature_category_meta', 10, 3 );


function want_loco_translate()
{
   return rbfw_get_option('want_loco_translate', 'rbfw_basic_gen_settings');
}


function rbfw_update_settings(){
    $payment_settings = maybe_unserialize('a:6:{s:19:"rbfw_payment_system";s:3:"wps";s:17:"rbfw_mps_currency";s:3:"USD";s:26:"rbfw_mps_currency_position";s:4:"left";s:32:"rbfw_mps_currency_decimal_number";s:1:"2";s:25:"rbfw_mps_checkout_account";s:2:"on";s:24:"rbfw_mps_payment_gateway";a:1:{s:7:"offline";s:7:"offline";}}');

    if (get_option('rbfw_basic_payment_settings') === false) {

        update_option('rbfw_basic_payment_settings', $payment_settings);

    }

    $pdf_settings = maybe_unserialize('a:9:{s:13:"rbfw_send_pdf";s:3:"yes";s:13:"rbfw_pdf_logo";s:0:"";s:11:"rbfw_pdf_bg";s:0:"";s:16:"rbfw_pdf_address";s:0:"";s:14:"rbfw_pdf_phone";s:0:"";s:17:"rbfw_pdf_tc_title";s:0:"";s:16:"rbfw_pdf_tc_text";s:0:"";s:17:"rbfw_pdf_bg_color";s:0:"";s:19:"rbfw_pdf_text_color";s:0:"";}');

    if (get_option('rbfw_basic_pdf_settings') === false) {

        update_option('rbfw_basic_pdf_settings', $pdf_settings);

    }
}

function rbfw_exist_page_by_slug( $slug ) {

    $query = new WP_Query( array(
        'name'        => $slug,
        'post_type'   => 'page', // Specify 'page' if you're looking for pages
        'post_status' => 'publish', // Only look for published pages
    ) );

    $result = $query->post_count;

    if(  $result > 0 ) {
        return false;
    } else {
        return true;
    }
}

function rbfw_exist_page_by_title( $title ) {
    $query = new WP_Query( array(
        'title'        => $title,
        'post_type'   => 'page', // Specify 'page' if you're looking for pages
        'post_status' => 'publish', // Only look for published pages
    ) );

    $result = $query->post_count;

    if(  $result > 0 ) {
        return true;
    } else {
        return false;
    }
}

function rbfw_page_create() {
    $pages = [
        'rent-list' => [
            'title' => 'Rent List',
            'content' => "[rent-list style='list']"
        ],
        'rent-grid' => [
            'title' => 'Rent Grid',
            'content' => "[rent-list style='grid']"
        ],
        'search-item-list' => [
            'title' => 'Search Item List',
            'content' => '[rbfw_search_ac] [search-result]'
        ],
        'booking-search' => [
            'title' => 'Booking Search',
            'content' => '[rbfw_booking_search]'
        ]
    ];

    $created = false;

    foreach ($pages as $slug => $page) {
        if (get_page_by_path($slug) === null) {
            $page_id = wp_insert_post([
                'post_title'    => $page['title'],
                'post_content'  => $page['content'],
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => $slug,
                'post_author'   => 1 // Set admin as author
            ]);

            if (!is_wp_error($page_id)) {
                wp_cache_delete( $slug, 'posts' );
                $created = true;
                error_log("Page '{$page['title']}' created successfully with ID: $page_id");
            } else {
                error_log("Failed to create page '{$page['title']}': " . $page_id->get_error_message());
            }
        }
    }

    // Newly created pages need a rewrite flush or their permalinks 404 until
    // an admin manually re-saves Settings -> Permalinks.
    if ( $created ) {
        flush_rewrite_rules();
    }
}


add_action('woocommerce_cart_calculate_fees', 'custom_taxable_fee', 20);
function custom_taxable_fee() {
    $total_deposit_amount = 0;
    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item_key => $cart_item) {
        if(!empty($cart_item['rbfw_ticket_info'])){
            foreach ($cart_item['rbfw_ticket_info'] as $item_dep){
                $total_deposit_amount = $total_deposit_amount + $item_dep['security_deposit_amount'];
            }
        }
    }
    if($total_deposit_amount){
        WC()->cart->add_fee(__('Security Deposit', 'booking-and-rental-manager-for-woocommerce'), $total_deposit_amount, false); // 'true' makes it taxable
    }
}


add_action( 'rbfw_ticket_feature_info', 'rbfw_ticket_feature_info' );
function rbfw_ticket_feature_info(){

    $rbfw_real_time_availability_display = rbfw_get_option('rbfw_real_time_availability_display', 'rbfw_basic_gen_settings');

    if($rbfw_real_time_availability_display=='yes'){

    ?>
    <div class="rbfw-bikecarsd-calendar-header">
        <div class="rbfw-bikecarsd-calendar-header-feature">
            <i class="fas fa-clock"></i>
            <?php esc_html_e('Real-time availability','booking-and-rental-manager-for-woocommerce'); ?>
        </div>
        <div class="rbfw-bikecarsd-calendar-header-feature">
            <i class="fas fa-bolt"></i>
            <?php esc_html_e('Instant confirmation','booking-and-rental-manager-for-woocommerce'); ?>
        </div>
    </div>
    <?php
    }
}

function check_rbfw_tiered_pricing( $day_number, $rbfw_tiered_pricing) {
    $day_number = $day_number+1;
    foreach ( $rbfw_tiered_pricing as $item ) {
        $rbfw_start_day = $item['rbfw_start_day_tiered'];
        $rbfw_end_day   = $item['rbfw_end_day_tiered'];

        if ( $day_number >= $rbfw_start_day  &&  $day_number <= $rbfw_end_day) {
            return $item['rbfw_daily_price_tiered'];
        }
    }
    return '';
}

add_action( 'rbfw_pricing_info_header', 'rbfw_pricing_info_header' );
function rbfw_pricing_info_header(){
    $info_display = rbfw_get_option('rbfw_pricing_info_display', 'rbfw_basic_gen_settings');
    ?>
    <?php if ( $info_display == 'yes' ) : ?>
        <div class="rbfw-pricing-info-heading">
            <i class="fas fa-info-circle"></i> <?php esc_html_e('Pricing Info', 'booking-and-rental-manager-for-woocommerce'); ?>
            <span class="pricing-info-view" title="click to see">
			<?php esc_html_e('Click to view','booking-and-rental-manager-for-woocommerce') ?>
		</span>
        </div>
    <?php endif; ?>
    <?php
}

add_action( 'rbfw_add_term_condition', 'rbfw_add_term_condition_item', 10, 1 );
function rbfw_add_term_condition_item( $post_id ) {

    $check_condition = get_post_meta( $post_id, 'rbfw_enable_term_content', true );
    $check_condition = $check_condition ? $check_condition : 'yes';



    if ( $check_condition && $check_condition == 'yes' ) {
        $conditions = get_post_meta( $post_id, 'mep_event_term', true ) ? maybe_unserialize( get_post_meta( $post_id, 'mep_event_term', true ) ) : [];

        if ( sizeof( $conditions ) > 0 ) {
            foreach ( $conditions as $condition ) {

                $required = $condition['rbfw_term_required'] == 'on' ? 'required' : '';
                ?>
                <label class="rbfw-term-condition">
                    <input type="checkbox" name="accept_term[]" <?php echo $required; ?> />
                    <a href="<?php echo $condition['rbfw_term_url']; ?>" target="_blank"><?php echo $condition['rbfw_term_title']; ?></a>
                </label>
                <?php
            }
        }
    }
}

/**
 * One-time, self-healing migration: clear the bogus "1000" stock default.
 *
 * Older builds of the modern editor silently saved a blank Stock Quantity as
 * 1000, which made single-unit rentals effectively unlimited and allowed
 * double-booking. This converts any rental item still holding exactly 1000 back
 * to blank, so the server-side availability guard treats it as a single unit
 * (see rbfw_get_effective_item_stock()). Items the admin deliberately set to a
 * real number are untouched, and the migration runs only once per site (guarded
 * by an option flag) — so a value an admin later sets is never overwritten.
 *
 * Runs on admin_init so it self-applies on the next admin page load after
 * activation or an update, without a manual step.
 */
add_action( 'admin_init', 'rbfw_maybe_migrate_stock_1000_default' );
function rbfw_maybe_migrate_stock_1000_default() {

	if ( 'done' === get_option( 'rbfw_stock_1000_migrated' ) ) {
		return;
	}

	// Allow a site to opt out of the data migration entirely.
	if ( apply_filters( 'rbfw_skip_stock_1000_migration', false ) ) {
		update_option( 'rbfw_stock_1000_migrated', 'done' );
		return;
	}

	// What blank/1000 stock should become. Default '' (blank => treated as 1 unit).
	$target_value = apply_filters( 'rbfw_stock_1000_migration_target', '' );

	$item_ids = get_posts(
		array(
			'post_type'      => 'rbfw_item',
			'post_status'    => 'any',
			'numberposts'    => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => 'rbfw_item_stock_quantity',
					'value'   => '1000',
					'compare' => '=',
				),
			),
		)
	);

	if ( ! empty( $item_ids ) ) {
		foreach ( $item_ids as $item_id ) {
			update_post_meta( $item_id, 'rbfw_item_stock_quantity', $target_value );
		}
	}

	update_option( 'rbfw_stock_1000_migrated', 'done' );
}




