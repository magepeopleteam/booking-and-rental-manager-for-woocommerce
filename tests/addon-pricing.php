<?php
/**
 * Pricing addons on the booking forms and in the cart: Seasonal Pricing, Discount Over X Days,
 * Min & Max Booking Day.
 *
 * Run on a development site with WooCommerce, this plugin and the Seasonal Pricing, Discount
 * Over X Days and Min & Max Booking Day addons active ("Count Extra Day" left on):
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/addon-pricing.php
 *
 * Before the fix:
 * - the resort room table never read the seasonal rates (a removed $rbfw_resort_data_sp), so it
 *   showed and posted the base rate while the cart charged the season;
 * - the cart's resort seasonal loop carried one room type's total into the next;
 * - the resort summary never showed the discount, and the cart took it off the fees too;
 * - Multiple Items advertised Discount Over X Days but never applied it, and ignored its seasons;
 * - a Quick Edit wiped every seasonal price.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}
$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
    if ( ! $condition ) {
        throw new RuntimeException( 'FAIL ' . $message );
    }
    ++$checks;
    WP_CLI::log( 'PASS ' . $message );
};
$near = static function ( $a, $b ) {
    return abs( (float) $a - (float) $b ) < 0.005;
};

foreach ( array(
    'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php',
    'booking-and-rental-manager-discount-over-x-days/rent-discount-over-x-days.php',
    'booking-and-rental-manager-min-max-booking-day/rent-min-max-booking-day.php',
) as $addon ) {
    if ( ! is_plugin_active( $addon ) ) {
        WP_CLI::error( 'Activate ' . $addon . ' first.' );
    }
}

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();
if ( 'on' !== $GLOBALS['rbfw']->get_option_trans( 'rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on' ) ) {
    WP_CLI::error( 'Turn "Count Extra Day" on: the expected totals below include the return day.' );
}
$builder = new RBFW_Woocommerce( false );
$build   = static function ( $id, $payload ) use ( $builder ) {
    $payload['nonce'] = wp_create_nonce( 'rbfw_ajax_action' );
    $data             = $builder->rbfw_add_cart_item_func( array(), $id, $payload );
    return is_array( $data ) ? $data : null;
};
$ids  = array();
$make = static function ( $title, $type, $meta ) use ( &$ids ) {
    $id = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => $title ) );
    update_post_meta( $id, 'rbfw_item_type', $type );
    foreach ( $meta as $key => $value ) {
        update_post_meta( $id, $key, $value );
    }
    $ids[] = $id;
    return $id;
};
// Read a JSON-echoing admin-ajax handler: wp_die() becomes an exception.
$ajax = static function ( callable $handler, array $post ) {
    $_POST    = $post;
    $_REQUEST = $post;
    $die      = static function () {
        return static function () {
            throw new RuntimeException( 'rbfw-test-die' );
        };
    };
    add_filter( 'wp_die_handler', $die, PHP_INT_MAX );
    add_filter( 'wp_die_ajax_handler', $die, PHP_INT_MAX );
    ob_start();
    try {
        $handler();
    } catch ( RuntimeException $e ) {
        if ( 'rbfw-test-die' !== $e->getMessage() ) {
            ob_end_clean();
            throw $e;
        }
    } finally {
        remove_filter( 'wp_die_handler', $die, PHP_INT_MAX );
        remove_filter( 'wp_die_ajax_handler', $die, PHP_INT_MAX );
        $_POST    = array();
        $_REQUEST = array();
    }
    return ob_get_clean();
};

$prev_user = get_current_user_id();
$admins    = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( $admins ? (int) $admins[0] : 1 );

try {
    /* ── Resort: the reported setup (season Oct 20-30: Deluxe 300 / Standard 200 a night) ── */
    $rooms  = array(
        array( 'room_type' => 'Deluxe Room', 'rbfw_room_daylong_rate' => '80', 'rbfw_room_daynight_rate' => '120', 'rbfw_room_available_qty' => '5', 'rbfw_room_desc' => 'Spacious', 'rbfw_room_image' => '' ),
        array( 'room_type' => 'Standard Room', 'rbfw_room_daylong_rate' => '50', 'rbfw_room_daynight_rate' => '80', 'rbfw_room_available_qty' => '8', 'rbfw_room_desc' => 'Comfortable', 'rbfw_room_image' => '' ),
    );
    $season = array(
        array(
            'start_date' => '2030-10-20',
            'end_date'   => '2030-10-30',
            'room_price' => array(
                array( 'room_type' => 'Deluxe Room', 'day_long_price' => '150', 'price' => '300' ),
                array( 'room_type' => 'Standard Room', 'day_long_price' => '', 'price' => '200' ),
            ),
        ),
    );
    $rs = $make( 'Addon fixture resort', 'resort', array(
        'rbfw_resort_room_data'            => $rooms,
        'rbfw_resort_data_sp'              => $season,
        'rbfw_enable_resort_daylong_price' => 'yes',
        'rbfw_item_stock_quantity'         => 10,
    ) );

    $stay = rbfw_resort_room_stay_price( $rs, 'Deluxe Room', 'daynight', '2030-10-21', '2030-10-23' );
    $assert( 3 === $stay['days'] && $near( $stay['total'], 900 ) && $near( $stay['per_day'], 300 ) && 'sessional' === $stay['applied'], 'resort: 21-23 Oct in season = 3 nights x 300 (' . $stay['total'] . ')' );
    $edge = rbfw_resort_room_stay_price( $rs, 'Deluxe Room', 'daynight', '2030-10-18', '2030-10-21' );
    $assert( $near( $edge['total'], 120 * 2 + 300 * 2 ) && $edge['varies'], 'resort: a stay crossing into the season bills each night at its own rate (840, avg)' );
    $blank = rbfw_resort_room_stay_price( $rs, 'Standard Room', 'daylong', '2030-10-22', '2030-10-22' );
    $assert( 1 === $blank['days'] && $near( $blank['total'], 50 ), 'resort: a blank seasonal day-long rate falls back to the base rate, not a free day' );

    // Room table (AJAX) shows and posts the seasonal figures.
    $html = $ajax( array( new RBFW_Resort_Function(), 'rbfw_check_resort_availibility' ), array(
        'action'        => 'rbfw_check_resort_availibility',
        'post_id'       => $rs,
        'checkin_date'  => '2030-10-21',
        'checkout_date' => '2030-10-23',
        'nonce'         => wp_create_nonce( 'rbfw_check_resort_availibility_action' ),
    ) );
    $price_text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES );
    $assert( false !== strpos( $price_text, '300.00/night' ) && false !== strpos( $price_text, '900.00 · 3 nights' ), 'resort table: Deluxe shows $300/night and the $900 stay badge' );
    $assert( false !== strpos( $price_text, '200.00/night' ) && false !== strpos( $price_text, '600.00 · 3 nights' ), 'resort table: Standard shows $200/night and the $600 stay badge' );
    $assert( false !== strpos( $html, 'data-stay_total="900"' ) && false !== strpos( $html, 'rbfw_see_resort_datewise_price' ), 'resort table: qty input carries the exact stay total; See Details link present' );
    $assert( false !== strpos( $price_text, 'Sessional pricing applied' ), 'resort summary: the seasonal note comes from this stay' );
    $assert( false === strpos( $html, 'id="rbfw_resort_discount_type" value="percentage"' ), 'resort table: no discount tier while the item has none' );

    // Cart: two room types in season are priced independently.
    $rs_form = static function ( $rooms_qty, $extra = array() ) use ( $rs ) {
        $rows = array();
        foreach ( $rooms_qty as $type => $qty ) {
            $rows[] = array( 'room_type' => $type, 'room_price' => '1', 'room_qty' => (string) $qty );
        }
        return array_merge( array(
            'rbfw_post_id'             => $rs,
            'rbfw_start_datetime'      => '2030-10-21',
            'rbfw_end_datetime'        => '2030-10-23',
            'rbfw_room_price_category' => 'daynight',
            'rbfw_room_info'           => $rows,
        ), $extra );
    };
    $two = $build( $rs, $rs_form( array( 'Deluxe Room' => 1, 'Standard Room' => 2 ) ) );
    $assert( $two && $near( $two['rbfw_tp'], 900 + 2 * 600 ), 'resort cart: Deluxe x1 + Standard x2 in season = 2100 (' . ( $two ? $two['rbfw_tp'] : 'null' ) . ')' );
    $tamper = $build( $rs, $rs_form( array( 'Deluxe Room' => 1 ), array( 'rbfw_room_price_category' => 'daylong' ) ) );
    $assert( $tamper && $near( $tamper['rbfw_tp'], 900 ), 'resort cart: a posted "daylong" on a multi-night stay is ignored' );
    $sold_out_first = $rs_form( array( 'Standard Room' => 1 ) );
    array_unshift( $sold_out_first['rbfw_room_info'], array( 'room_type' => 'Deluxe Room' ) ); // sold out: no qty/price posted
    $after_sold_out = $build( $rs, $sold_out_first );
    $assert( $after_sold_out && $near( $after_sold_out['rbfw_tp'], 600 ), 'resort cart: a sold-out room row no longer drops the rooms after it' );

    // Discount Over X Days: 50% over 2 days.
    update_post_meta( $rs, 'rbfw_enable_discount', 'yes' );
    update_post_meta( $rs, 'rbfw_discount_array', array( array( 'rbfw_discount_over_days' => 2, 'rbfw_discount_type' => 'percentage', 'rbfw_discount_number' => 50 ) ) );
    $disc = $build( $rs, $rs_form( array( 'Deluxe Room' => 1 ) ) );
    $assert( $disc && $near( $disc['discount_amount'], 450 ) && $near( $disc['rbfw_tp'], 450 ), 'resort cart: 50% off a 900 stay = 450' );
    $ticket = reset( $disc['rbfw_ticket_info'] );
    $assert( $near( $ticket['discount_amount'], 450 ) && $near( $ticket['ticket_price'], 450 ), 'resort ticket: same discount and price as the cart line' );
    $html = $ajax( array( new RBFW_Resort_Function(), 'rbfw_check_resort_availibility' ), array(
        'post_id'       => $rs,
        'checkin_date'  => '2030-10-21',
        'checkout_date' => '2030-10-23',
        'nonce'         => wp_create_nonce( 'rbfw_check_resort_availibility_action' ),
    ) );
    $assert( false !== strpos( $html, 'id="rbfw_resort_discount_type" value="percentage"' ) && false !== strpos( $html, 'id="rbfw_resort_discount_number" value="50"' ) && false !== strpos( $html, '<li class="discount"' ), 'resort summary: discount tier and row are rendered for the JS' );
    $short = $build( $rs, $rs_form( array( 'Deluxe Room' => 1 ), array( 'rbfw_end_datetime' => '2030-10-21' ) ) );
    $assert( $short && empty( $short['discount_amount'] ), 'resort cart: a 1-day stay is not "over 2 days" — no discount' );
    update_post_meta( $rs, 'rbfw_discount_array', array( array( 'rbfw_discount_over_days' => 2, 'rbfw_discount_type' => 'fixed_amount', 'rbfw_discount_number' => 5000 ) ) );
    $clamp = $build( $rs, $rs_form( array( 'Deluxe Room' => 1 ) ) );
    $assert( $clamp && $near( $clamp['rbfw_tp'], 0 ) && $near( $clamp['discount_amount'], 900 ), 'resort cart: a fixed discount larger than the stay stops at zero' );
    update_post_meta( $rs, 'rbfw_enable_discount', 'no' );

    /* ── Multiple Items: seasons and discount ── */
    $mi = $make( 'Addon fixture multiple items', 'multiple_items', array(
        'pricing_types'       => array( 'hourly' => 'off', 'daily' => 'on', 'weekly' => 'off', 'monthly' => 'off' ),
        'multiple_items_info' => array( array( 'item_name' => 'Kayak', 'available_qty' => 5, 'hourly_price' => '', 'daily_price' => '40', 'weekly_price' => '', 'monthly_price' => '' ) ),
        'rbfw_multiple_items_sp' => array( array( 'start_date' => '2030-07-01', 'end_date' => '2030-07-31', 'item_prices' => array( array( 'item_name' => 'Kayak', 'hourly_price' => '', 'daily_price' => '60', 'weekly_price' => '', 'monthly_price' => '' ) ) ) ),
    ) );
    $mi_form = static function ( $date ) use ( $mi ) {
        return array(
            'rbfw_post_id'           => $mi,
            'rbfw_pickup_start_date' => $date,
            'rbfw_pickup_start_time' => '10:00',
            'durationType'           => 'daily',
            'durationQty'            => '3',
            'multiple_items_info'    => array( array( 'item_name' => 'Kayak', 'item_qty' => '2', 'item_price' => '0' ) ),
        );
    };
    $off = $build( $mi, $mi_form( '2030-06-10' ) );
    $assert( $off && $near( $off['rbfw_tp'], 40 * 2 * 3 ), 'multiple items: out of season 2 x 3 days x 40 = 240' );
    $in = $build( $mi, $mi_form( '2030-07-10' ) );
    $assert( $in && $near( $in['rbfw_tp'], 60 * 2 * 3 ), 'multiple items: in season the seasonal daily rate applies (360)' );
    update_post_meta( $mi, 'rbfw_enable_discount', 'yes' );
    update_post_meta( $mi, 'rbfw_discount_array', array( array( 'rbfw_discount_over_days' => 2, 'rbfw_discount_type' => 'fixed_amount', 'rbfw_discount_number' => 25 ) ) );
    $mi_disc = $build( $mi, $mi_form( '2030-07-10' ) );
    $assert( $mi_disc && $near( $mi_disc['discount_amount'], 25 ) && $near( $mi_disc['rbfw_tp'], 335 ), 'multiple items: the advertised discount is applied (360 - 25)' );
    $mi_ticket = reset( $mi_disc['rbfw_ticket_info'] );
    $assert( $near( $mi_ticket['discount_amount'], 25 ), 'multiple items ticket: carries the discount for the order rows' );
    $mi_json = json_decode( $ajax( array( new RBFW_BikeCarMd_Function(), 'rbfw_multi_items_ajax_price_calculation' ), array(
        'post_id'                     => $mi,
        'pickup_date'                 => '2030-07-10',
        'pickup_time'                 => '10:00',
        'durationType'                => 'daily',
        'durationQty'                 => '3',
        'rbfw_duration_price'         => '360',
        'rbfw_service_category_price' => '10',
        'nonce'                       => wp_create_nonce( 'rbfw_multi_items_ajax_price_calculation_action' ),
    ) ), true );
    $assert( is_array( $mi_json ) && $near( $mi_json['discount'], 25 ) && $near( $mi_json['total_price'], 360 + 10 - 25 ), 'multiple items summary AJAX: discount shown, add-ons included in the total (345)' );
    $assert( $near( $mi_json['mi_prices'][0]['daily'], 60 ), 'multiple items summary AJAX: returns the seasonal rate for the pick-up date' );

    /* ── Multi-day summary AJAX: the posted fee is not discounted nor counted twice ── */
    $md = $make( 'Addon fixture md', 'bike_car_md', array(
        'rbfw_enable_daily_rate'   => 'yes',
        'rbfw_daily_rate'          => 100,
        'rbfw_enable_hourly_rate'  => 'no',
        'rbfw_item_stock_quantity' => 10,
        'rbfw_enable_discount'     => 'yes',
        'rbfw_discount_array'      => array( array( 'rbfw_discount_over_days' => 2, 'rbfw_discount_type' => 'percentage', 'rbfw_discount_number' => 10 ) ),
    ) );
    $md_json = json_decode( $ajax( array( new RBFW_BikeCarMd_Function(), 'rbfw_md_duration_price_calculation_ajax' ), array(
        'post_id'               => $md,
        'pickup_date'           => '2030-03-01',
        'dropoff_date'          => '2030-03-03',
        'pickup_time'           => '',
        'dropoff_time'          => '',
        'item_quantity'         => '1',
        'rbfw_management_price' => '50',
        'rbfw_enable_time_slot' => 'no',
        'nonce'                 => wp_create_nonce( 'rbfw_bikecarmd_ajax_price_calculation_action' ),
    ) ), true );
    $assert( is_array( $md_json ) && $near( $md_json['discount'], 30 ) && $near( $md_json['total_price'], 300 - 30 ), 'multi-day summary AJAX: 10% of the 300 rental, fee left for the form to add once (270)' );
    $md_cart = $build( $md, array(
        'rbfw_post_id'           => $md,
        'rbfw_pickup_start_date' => '2030-03-01',
        'rbfw_pickup_end_date'   => '2030-03-03',
        'rbfw_pickup_start_time' => '',
        'rbfw_pickup_end_time'   => '',
        'rbfw_enable_time_slot'  => 'no',
        'rbfw_item_quantity'     => '1',
    ) );
    $assert( $md_cart && $near( $md_cart['discount_amount'], $md_json['discount'] ), 'multi-day: summary and cart agree on the discount' );

    /* ── Seasonal Pricing addon save ── */
    $sp_saver = new RBFW_Seasonal_Price();
    $_POST    = array(); // Quick Edit / status change: no editor form.
    $sp_saver->rbfw_after_week_price_table_seasonal_price_save( $rs );
    $assert( $season === get_post_meta( $rs, 'rbfw_resort_data_sp', true ), 'seasonal save: a request without the editor form keeps the seasons' );
    $_POST = array(
        'rbfw_item_type'      => 'resort',
        'rbfw_resort_data_sp' => array(
            array( 'start_date' => '2030-12-25', 'end_date' => '2030-12-25', 'room_price' => array(
                array( 'room_type' => 'Deluxe Room', 'day_long_price' => '', 'price' => '500' ),
                array( 'room_type' => 'Standard Room', 'day_long_price' => '', 'price' => '' ),
            ) ),
            array( 'start_date' => '2030-12-31', 'end_date' => '2030-12-01', 'room_price' => array( array( 'room_type' => 'Deluxe Room', 'price' => '9' ) ) ),
        ),
    );
    $sp_saver->rbfw_after_week_price_table_seasonal_price_save( $rs );
    $_POST = array();
    $saved = get_post_meta( $rs, 'rbfw_resort_data_sp', true );
    $assert( is_array( $saved ) && 1 === count( $saved ) && '2030-12-25' === $saved[0]['end_date'], 'seasonal save: a one-day season is kept, a reversed range dropped' );
    $assert( 1 === count( $saved[0]['room_price'] ) && 'Deluxe Room' === $saved[0]['room_price'][0]['room_type'], 'seasonal save: a room left blank keeps its normal rate instead of voiding the season' );
    $xmas = rbfw_resort_room_stay_price( $rs, 'Standard Room', 'daynight', '2030-12-25', '2030-12-26' );
    $assert( $near( $xmas['total'], 80 * 2 ), 'resort: the room left out of the season stays at its own rate' );

    /* ── Min & Max Booking Day ── */
    update_post_meta( $md, 'rbfw_minimum_booking_day', 3 );
    update_post_meta( $md, 'rbfw_maximum_booking_day', 5 );
    $md_days = static function ( $end ) {
        return array( 'rbfw_pickup_start_date' => '2030-03-01', 'rbfw_pickup_end_date' => $end );
    };
    $assert( '' !== rbfw_mmbd_booking_days_error( $md, $md_days( '2030-03-02' ) ), 'min-max: 1 day under a 3-day minimum is refused' );
    $assert( '' === rbfw_mmbd_booking_days_error( $md, $md_days( '2030-03-04' ) ), 'min-max: 3 days passes' );
    $assert( '' !== rbfw_mmbd_booking_days_error( $md, $md_days( '2030-03-08' ) ), 'min-max: 7 days over a 5-day maximum is refused' );
    $assert( 5 === has_action( 'wp_ajax_nopriv_rbfw_native_checkout', 'rbfw_mmbd_validate_native_checkout' ), 'min-max: the standalone checkout runs the same check' );
    update_post_meta( $mi, 'rbfw_minimum_booking_day', 9 );
    $assert( '' === rbfw_mmbd_booking_days_error( $mi, $md_days( '2030-03-02' ) ), 'min-max: no hidden limit on Multiple Items (no editor setting for it)' );
} finally {
    foreach ( $ids as $fixture_id ) {
        wp_delete_post( $fixture_id, true );
    }
    wp_set_current_user( $prev_user );
    if ( null === $saved_rbfw ) {
        unset( $GLOBALS['rbfw'] );
    } else {
        $GLOBALS['rbfw'] = $saved_rbfw;
    }
}
WP_CLI::success( $checks . ' checks passed.' );
