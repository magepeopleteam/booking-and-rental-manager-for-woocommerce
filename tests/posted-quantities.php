<?php
/** Run on a development site: wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/posted-quantities.php */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}
$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
    ++$checks;
    WP_CLI::log( 'PASS ' . $message );
};

// 1. The parser: whole numbers only, everything else is null (never coerced).
foreach ( array( '0' => 0, '' => 0, ' 3 ' => 3, '12' => 12, '3.0' => 3, '007' => 7, '999999999' => 999999999 ) as $in => $expected ) {
    $assert( $expected === RBFW_Function::parse_posted_quantity( (string) $in ), 'Accepts whole number ' . var_export( (string) $in, true ) );
}
foreach ( array( '-5', '-0', '0.9', '0.001', '2.5', '1e2', '1e309', '-1e309', 'abc', '0x1A', '1,5', '9999999999', '99999999999999999999', 'NaN', 'INF' ) as $in ) {
    $assert( null === RBFW_Function::parse_posted_quantity( $in ), 'Rejects ' . var_export( $in, true ) );
}
$assert( null === RBFW_Function::parse_posted_quantity( array( '1' ) ), 'Rejects an array posted as a quantity' );
$assert( null === RBFW_Function::parse_posted_quantity( -1 ), 'Rejects a negative integer' );
$assert( 4 === RBFW_Function::parse_posted_quantity( 4 ), 'Accepts a positive integer' );
$assert( 0 === RBFW_Function::parse_posted_quantity( null ), 'An absent value is 0 (not selected)' );

// 2. The cart builder, one fixture per rental type. Control booking first: it must still price.
$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();
$builder         = new RBFW_Woocommerce( false );
$build   = static function ( $id, $payload ) use ( $builder ) {
    $payload['nonce'] = wp_create_nonce( 'rbfw_ajax_action' );
    // A refused request comes back as a WP_Error: callers already treat any non-array as "no booking".
    $data = $builder->rbfw_add_cart_item_func( array(), $id, $payload );
    if ( ! is_array( $data ) ) {
        return array( 'rejected' => true, 'message' => is_wp_error( $data ) ? $data->get_error_message() : '' );
    }
    return array( 'rejected' => false, 'data' => $data, 'tp' => isset( $data['rbfw_tp'] ) ? (float) $data['rbfw_tp'] : null );
};
$start = gmdate( 'Y-m-d', strtotime( '+10 days' ) );
$end   = gmdate( 'Y-m-d', strtotime( '+14 days' ) );
$ids   = array();
$make  = static function ( $title, $type, $meta ) use ( &$ids ) {
    $id = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'draft', 'post_title' => $title ) );
    update_post_meta( $id, 'rbfw_item_type', $type );
    foreach ( $meta as $key => $value ) {
        update_post_meta( $id, $key, $value );
    }
    $ids[] = $id;
    return $id;
};
$services = array( array( 'service_name' => 'Lock', 'service_price' => 20, 'service_qty' => 5 ) );
try {
    // Multi-day (bike/car) with a categorised service.
    $md = $make( 'Quantity fixture md', 'bike_car_md', array(
        'rbfw_enable_daily_rate'      => 'yes',
        'rbfw_daily_rate'             => 100,
        'rbfw_enable_hourly_rate'     => 'no',
        'rbfw_item_stock_quantity'    => 10,
        'rbfw_extra_service_data'     => $services,
        'rbfw_service_category_price' => array( array( 'cat_title' => 'Gear', 'cat_services' => array( array( 'title' => 'Helmet', 'price' => 50, 'service_price_type' => 'one_time' ) ) ) ),
    ) );
    $md_form = static function ( $extra = array() ) use ( $md, $start, $end ) {
        return array_merge( array(
            'rbfw_post_id'            => $md,
            'rbfw_pickup_start_date'  => $start,
            'rbfw_pickup_end_date'    => $end,
            'rbfw_pickup_start_time'  => '10:00',
            'rbfw_pickup_end_time'    => '10:00',
            'rbfw_enable_time_slot'   => 'off',
            'rbfw_item_quantity'      => '1',
        ), $extra );
    };
    $service_row = static function ( $qty ) {
        return array( 'Gear' => array( 'cat_title' => 'Gear', 0 => array( 'name' => 'Helmet', 'quantity' => $qty ) ) );
    };
    $control = $build( $md, $md_form() );
    $assert( ! $control['rejected'] && $control['tp'] > 0, 'md control booking prices above zero (' . $control['tp'] . ')' );
    $two = $build( $md, $md_form( array( 'rbfw_item_quantity' => '2' ) ) );
    $assert( ! $two['rejected'] && abs( $two['tp'] - 2 * $control['tp'] ) < 0.01, 'md quantity 2 still doubles the duration price' );
    $assert( ! $build( $md, $md_form( array( 'rbfw_item_quantity' => '2.0' ) ) )['rejected'], 'md accepts a whole number written with a trailing .0' );
    foreach ( array( '-5', '0', '', '0.9', '1e309', 'abc', '2.5' ) as $bad ) {
        $assert( $build( $md, $md_form( array( 'rbfw_item_quantity' => $bad ) ) )['rejected'], 'md rejects rbfw_item_quantity=' . var_export( $bad, true ) );
    }
    $assert( 'Please enter a valid quantity.' === $build( $md, $md_form( array( 'rbfw_item_quantity' => '-5' ) ) )['message'], 'The refusal carries a customer-facing message' );
    $assert( $build( $md, $md_form( array( 'rbfw_item_quantity' => array( '1' ) ) ) )['rejected'], 'md rejects an array posted as rbfw_item_quantity' );
    $absent = $md_form();
    unset( $absent['rbfw_item_quantity'] );
    $assert( ! $build( $md, $absent )['rejected'], 'md without a posted quantity still defaults to 1' );
    $with_service = $build( $md, $md_form( array( 'rbfw_service_price_data' => $service_row( '2' ) ) ) );
    $assert( ! $with_service['rejected'] && abs( $with_service['tp'] - ( $control['tp'] + 100 ) ) < 0.01, 'md categorised service quantity 2 adds 2 x 50' );
    $assert( ! $build( $md, $md_form( array( 'rbfw_service_price_data' => $service_row( '0' ) ) ) )['rejected'], 'md service quantity 0 means not selected' );
    foreach ( array( '0.001', '-1', '1e309', 'x' ) as $bad ) {
        $assert( $build( $md, $md_form( array( 'rbfw_service_price_data' => $service_row( $bad ) ) ) )['rejected'], 'md rejects categorised service quantity ' . var_export( $bad, true ) );
    }
    $svc = static function ( $qty ) {
        return array( 1 => array( 'service_name' => 'Lock', 'service_price' => '20', 'service_qty' => $qty ) );
    };
    foreach ( array( '0.002', '-3', '1e309' ) as $bad ) {
        $assert( $build( $md, $md_form( array( 'rbfw_service_info' => $svc( $bad ) ) ) )['rejected'], 'md rejects extra service quantity ' . var_export( $bad, true ) );
    }
    $extra = $build( $md, $md_form( array( 'rbfw_service_info' => $svc( '3' ) ) ) );
    $assert( ! $extra['rejected'] && $extra['tp'] > $control['tp'], 'md extra service quantity 3 is still charged' );

    // Single day, rent types by quantity.
    $sd = $make( 'Quantity fixture sd', 'bike_car_sd', array(
        'rbfw_bike_car_sd_data'   => array( array( 'rent_type' => 'Hourly', 'price' => 100, 'duration' => 1, 'd_type' => 'Hours', 'short_desc' => '' ) ),
        'rbfw_extra_service_data' => $services,
        'rbfw_item_stock_quantity' => 10,
    ) );
    $sd_form = static function ( $qty, $extra = array() ) use ( $sd, $start ) {
        return array_merge( array(
            'rbfw_post_id'                 => $sd,
            'rbfw_bikecarsd_selected_date' => $start,
            'rbfw_start_time'              => '10:00',
            'rbfw_bikecarsd_info'          => array( 1 => array( 'rent_type' => 'Hourly', 'qty' => $qty, 'price' => '100' ) ),
        ), $extra );
    };
    $sd_control = $build( $sd, $sd_form( '2' ) );
    $assert( ! $sd_control['rejected'] && abs( $sd_control['tp'] - 200 ) < 0.01, 'sd control: 2 x 100 prices at 200 (' . $sd_control['tp'] . ')' );
    foreach ( array( '0.001', '0.5', '-2', '1e309', 'abc' ) as $bad ) {
        $assert( $build( $sd, $sd_form( $bad ) )['rejected'], 'sd rejects rent type quantity ' . var_export( $bad, true ) );
    }
    $assert( ! $build( $sd, $sd_form( '2', array( 'rbfw_service_info' => $svc( '0' ) ) ) )['rejected'], 'sd service quantity 0 means not selected' );
    foreach ( array( '0.002', '-3', '1e309' ) as $bad ) {
        $assert( $build( $sd, $sd_form( '2', array( 'rbfw_service_info' => $svc( $bad ) ) ) )['rejected'], 'sd rejects extra service quantity ' . var_export( $bad, true ) );
    }
    $sd_extra = $build( $sd, $sd_form( '2', array( 'rbfw_service_info' => $svc( '2' ) ) ) );
    $assert( ! $sd_extra['rejected'] && abs( $sd_extra['tp'] - 240 ) < 0.01, 'sd extra service quantity 2 adds 2 x 20' );

    // Appointment / timely: the chosen service is booked rbfw_item_quantity times.
    $ap = $make( 'Quantity fixture appointment', 'appointment', array(
        'rbfw_bike_car_sd_data'    => array( array( 'rent_type' => 'Hourly', 'price' => 100, 'duration' => 1, 'd_type' => 'Hours', 'short_desc' => '' ) ),
        'rbfw_item_stock_quantity' => 10,
    ) );
    $ap_form = static function ( $qty ) use ( $ap, $start ) {
        return array( 'rbfw_post_id' => $ap, 'rbfw_bikecarsd_selected_date' => $start, 'rbfw_start_time' => '10:00', 'service_type' => 'Hourly', 'rbfw_item_quantity' => $qty );
    };
    $ap_control = $build( $ap, $ap_form( '2' ) );
    $assert( ! $ap_control['rejected'] && abs( $ap_control['tp'] - 200 ) < 0.01, 'appointment control: 2 x 100 prices at 200 (' . $ap_control['tp'] . ')' );
    foreach ( array( '0', '-2', '0.9', '1e309', 'abc' ) as $bad ) {
        $assert( $build( $ap, $ap_form( $bad ) )['rejected'], 'appointment rejects rbfw_item_quantity=' . var_export( $bad, true ) );
    }

    // Resort rooms.
    $rs = $make( 'Quantity fixture resort', 'resort', array(
        'rbfw_resort_room_data' => array( array( 'room_type' => 'Deluxe', 'rbfw_room_daynight_rate' => 100, 'rbfw_room_daylong_rate' => 80, 'rbfw_room_available_qty' => 5, 'rbfw_room_desc' => '' ) ),
    ) );
    $rs_form = static function ( $qty, $extra = array() ) use ( $rs, $start, $end ) {
        return array_merge( array(
            'rbfw_post_id'              => $rs,
            'rbfw_start_datetime'       => $start,
            'rbfw_end_datetime'         => $end,
            'rbfw_room_price_category'  => 'daynight',
            'rbfw_room_info'            => array( array( 'room_type' => 'Deluxe', 'room_price' => '100', 'room_qty' => $qty ) ),
        ), $extra );
    };
    $rs_control = $build( $rs, $rs_form( '2' ) );
    $assert( ! $rs_control['rejected'] && $rs_control['tp'] > 0, 'resort control: 2 rooms prices above zero (' . $rs_control['tp'] . ')' );
    $rs_one = $build( $rs, $rs_form( '1' ) );
    $assert( abs( $rs_control['tp'] - 2 * $rs_one['tp'] ) < 0.01, 'resort room quantity 2 still doubles the room price' );
    foreach ( array( '-3', '0.001', '0.5', '1e309', 'abc' ) as $bad ) {
        $assert( $build( $rs, $rs_form( $bad ) )['rejected'], 'resort rejects room quantity ' . var_export( $bad, true ) );
    }
    $rs_zero_row = $rs_form( '2' );
    $rs_zero_row['rbfw_room_info'][] = array( 'room_type' => 'Suite', 'room_price' => '200', 'room_qty' => '0' );
    $assert( ! $build( $rs, $rs_zero_row )['rejected'], 'resort: an unselected room row (quantity 0) beside a selected one is fine' );
    foreach ( array( '0.002', '-3', '1e309' ) as $bad ) {
        $assert( $build( $rs, $rs_form( '2', array( 'rbfw_service_info' => $svc( $bad ) ) ) )['rejected'], 'resort rejects extra service quantity ' . var_export( $bad, true ) );
    }

    // 3. The standalone checkout quote shares the builder: same inputs, same refusal.
    $quote = RBFW_Native_Quote::build( $md, $md_form( array( 'rbfw_item_quantity' => '2' ) ) );
    $assert( ! is_wp_error( $quote ) && $quote['subtotal'] > 0, 'standalone quote prices a valid multi-day booking' );
    foreach ( array( '-5', '0', '0.9' ) as $bad ) {
        $assert( is_wp_error( RBFW_Native_Quote::build( $md, $md_form( array( 'rbfw_item_quantity' => $bad ) ) ) ), 'standalone quote refuses rbfw_item_quantity=' . var_export( $bad, true ) );
    }
    $assert( is_wp_error( RBFW_Native_Quote::build( $sd, $sd_form( '0.001' ) ) ), 'standalone quote refuses a fractional rent type quantity' );
    $assert( is_wp_error( RBFW_Native_Quote::build( $rs, $rs_form( '-3' ) ) ), 'standalone quote refuses a negative room quantity' );
} finally {
    foreach ( $ids as $fixture_id ) {
        wp_delete_post( $fixture_id, true );
    }
    if ( null === $saved_rbfw ) {
        unset( $GLOBALS['rbfw'] );
    } else {
        $GLOBALS['rbfw'] = $saved_rbfw;
    }
}
WP_CLI::success( $checks . ' checks passed.' );
