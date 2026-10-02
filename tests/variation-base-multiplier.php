<?php
/**
 * "Charge base price per variation unit" (rbfw_variation_multiply_base) on multi-day items.
 *
 * Run on a development site with WooCommerce, this plugin and the Multi Day Price Saver add-on active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/variation-base-multiplier.php
 *
 * Fixture mirrors the report: $25/day base, multi-day saver $22/day from 3 days, Size variations
 * Small (no price) / Medium (+$2 per day) / Large (+$5 per day).
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

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();
$builder         = new RBFW_Woocommerce( false );
$ajax            = new RBFW_BikeCarMd_Function();

$start = gmdate( 'Y-m-d', strtotime( '+10 days' ) );
$end   = gmdate( 'Y-m-d', strtotime( '+12 days' ) ); // 3 booked days (return day counts).
$ids   = array();

$make_item = static function ( $variations_on, $multiply ) use ( &$ids ) {
    $id = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => 'Variation multiplier fixture' ) );
    $meta = array(
        'rbfw_item_type'              => 'bike_car_md',
        'rbfw_enable_daily_rate'      => 'yes',
        'rbfw_daily_rate'             => 25,
        'rbfw_enable_hourly_rate'     => 'no',
        'rbfw_item_stock_quantity'    => 1000,
        'rbfw_md_data_mds'            => array( array( 'rbfw_start_day' => 3, 'rbfw_end_day' => 10, 'rbfw_daily_price' => 22, 'rbfw_hourly_price' => 0 ) ),
        'rbfw_enable_variations'      => $variations_on ? 'yes' : 'no',
        'rbfw_variation_multiply_base' => $multiply ? 'yes' : 'no',
        'rbfw_variations_data'        => array( array(
            'field_label'    => 'Size',
            'field_id'       => 'rbfw_variation_id_0',
            'selected_value' => '',
            'value'          => array(
                array( 'name' => 'Small', 'quantity' => '5' ),
                array( 'name' => 'Medium', 'quantity' => '5', 'prices' => array( 'daily' => '2' ) ),
                array( 'name' => 'Large', 'quantity' => '5', 'prices' => array( 'daily' => '5' ) ),
            ),
        ) ),
    );
    foreach ( $meta as $key => $value ) {
        update_post_meta( $id, $key, $value );
    }
    $ids[] = $id;
    return $id;
};
$payload = static function ( $qty_map, $extra = array() ) use ( $start, $end ) {
    return array_merge( array(
        'rbfw_pickup_start_date' => $start,
        'rbfw_pickup_end_date'   => $end,
        'rbfw_pickup_start_time' => '',
        'rbfw_pickup_end_time'   => '',
        'rbfw_enable_time_slot'  => 'no',
        'rbfw_variation_qty'     => array( 'rbfw_variation_id_0' => $qty_map ),
        'nonce'                  => wp_create_nonce( 'rbfw_ajax_action' ),
    ), $extra );
};
$cart = static function ( $id, $data ) use ( $builder ) {
    $built = $builder->rbfw_add_cart_item_func( array(), $id, $data );
    if ( ! is_array( $built ) ) {
        return array( 'rejected' => true );
    }
    return array(
        'rejected' => false,
        'tp'       => (float) $built['rbfw_tp'],
        'qty'      => (int) $built['rbfw_item_quantity'],
        'duration' => (float) $built['rbfw_duration_price'],
        'surcharge' => (float) $built['rbfw_variation_surcharge'],
    );
};
// The live-total AJAX handler echoes JSON and dies; turn the die into an exception to read the reply.
$live = static function ( $id, $qty_map, $extra = array() ) use ( $ajax, $start, $end ) {
    $_POST = array_merge( array(
        'nonce'                 => wp_create_nonce( 'rbfw_bikecarmd_ajax_price_calculation_action' ),
        'post_id'               => $id,
        'pickup_date'           => $start,
        'dropoff_date'          => $end,
        'pickup_time'           => '',
        'dropoff_time'          => '',
        'rbfw_enable_time_slot' => 'no',
        'item_quantity'         => 0,
        'rbfw_variation_qty'    => array( 'rbfw_variation_id_0' => $qty_map ),
    ), $extra );
    $_REQUEST = $_POST; // check_ajax_referer() reads the nonce from $_REQUEST, which CLI does not mirror from $_POST.
    $die = static function () {
        return static function () {
            throw new RuntimeException( 'ajax-die' );
        };
    };
    add_filter( 'wp_die_ajax_handler', $die );
    add_filter( 'wp_doing_ajax', '__return_true' );
    ob_start();
    try {
        $ajax->rbfw_md_duration_price_calculation_ajax();
    } catch ( RuntimeException $e ) {
        // expected: wp_die() was reached.
    }
    $out = ob_get_clean();
    remove_filter( 'wp_die_ajax_handler', $die );
    remove_filter( 'wp_doing_ajax', '__return_true' );
    $json = json_decode( $out, true );
    if ( ! is_array( $json ) ) {
        throw new RuntimeException( 'AJAX reply was not JSON: ' . substr( $out, 0, 200 ) );
    }
    return $json;
};

try {
    // ---- 0. Helpers -------------------------------------------------------------------------------
    $off = $make_item( true, false );
    $on  = $make_item( true, true );
    $assert( false === rbfw_variations_multiply_base( $off ), 'Helper: setting off -> false' );
    $assert( true === rbfw_variations_multiply_base( $on ), 'Helper: setting on + variations on -> true' );
    $assert( false === rbfw_variations_multiply_base( $make_item( false, true ) ), 'Helper: setting on but variations off -> false' );
    $assert( 3 === rbfw_variation_units( $on, array( 'rbfw_variation_id_0' => array( 'Small' => '1', 'Medium' => '2', 'Large' => '0' ) ) ), 'Units: 1 Small + 2 Medium = 3' );
    $assert( 1 === rbfw_variation_units( $on, array( 'rbfw_variation_id_0' => array( 'Small' => '1', 'Ghost' => '50' ) ) ), 'Units: a value that is not configured is ignored' );
    $assert( 0 === rbfw_variation_units( $on, array( 'rbfw_variation_id_9' => array( 'Small' => '4' ) ) ), 'Units: an unknown field id is ignored' );
    $assert( 0 === rbfw_variation_units( $on, array( 'rbfw_variation_id_0' => array( 'Small' => '-4' ) ) ), 'Units: a negative quantity never lowers the count below zero' );
    $assert( 0 === rbfw_variation_units( $on, 'junk' ), 'Units: a non-array payload is 0' );

    // ---- 1. Setting OFF: reproduces the reported figures exactly (nothing changes) --------------
    $c = $cart( $off, $payload( array( 'Small' => 1, 'Medium' => 2 ) ) );
    $assert( ! $c['rejected'], 'OFF: cart accepts the booking' );
    $assert( $near( $c['duration'], 66 ), 'OFF: duration cost is $66 (3 days at the $22 saver rate, billed once)' );
    $assert( $near( $c['surcharge'], 12 ), 'OFF: variations are $12 (2 Medium x $2 x 3 days)' );
    $assert( $near( $c['tp'], 78 ), 'OFF: total is $78, as reported' );
    $assert( 1 === $c['qty'], 'OFF: item quantity stays 1' );

    // ---- 2. Setting ON: base is billed per unit ---------------------------------------------------
    $c = $cart( $on, $payload( array( 'Small' => 1, 'Medium' => 2 ) ) );
    $assert( ! $c['rejected'], 'ON: cart accepts the booking' );
    $assert( 3 === $c['qty'], 'ON: item quantity is the 3 variation units' );
    $assert( $near( $c['duration'], 198 ), 'ON: duration cost is 3 x $66 = $198 (saver rate still applies)' );
    $assert( $near( $c['surcharge'], 12 ), 'ON: variation surcharge is unchanged ($12) - not double counted' );
    $assert( $near( $c['tp'], 210 ), 'ON: total is $210' );

    $c = $cart( $on, $payload( array( 'Small' => 1 ) ) );
    $assert( 1 === $c['qty'] && $near( $c['tp'], 66 ), 'ON: a single Small bills one unit ($66)' );
    $c = $cart( $on, $payload( array( 'Large' => 2 ) ) );
    $assert( $near( $c['duration'], 132 ) && $near( $c['surcharge'], 30 ) && $near( $c['tp'], 162 ), 'ON: 2 Large = 2 x $66 + 2 x $5 x 3 days = $162' );

    // Browser cannot lower or raise it: a posted rbfw_item_quantity is ignored when units exist.
    $c = $cart( $on, $payload( array( 'Small' => 1, 'Medium' => 2 ), array( 'rbfw_item_quantity' => '1' ) ) );
    $assert( 3 === $c['qty'] && $near( $c['tp'], 210 ), 'ON: a posted rbfw_item_quantity of 1 cannot lower the derived 3 units' );
    $c = $cart( $on, $payload( array( 'Small' => 1, 'Medium' => 2 ), array( 'rbfw_item_quantity' => '9' ) ) );
    $assert( 3 === $c['qty'] && $near( $c['tp'], 210 ), 'ON: a posted rbfw_item_quantity of 9 cannot raise it' );
    $c = $cart( $on, $payload( array( 'Small' => 1, 'Ghost' => 40 ) ) );
    $assert( 1 === $c['qty'] && $near( $c['tp'], 66 ), 'ON: an unconfigured variation key cannot inflate the units' );

    // ---- 3. Setting ON, but variations switched off: no effect ------------------------------------
    $stale = $make_item( false, true );
    $c     = $cart( $stale, $payload( array( 'Small' => 3 ) ) );
    $assert( 1 === $c['qty'], 'ON + variations off: quantity is not derived from steppers' );

    // ---- 4. Live total must equal the cart total (preview and cart cannot disagree) --------------
    foreach ( array( array( 'Small' => 1, 'Medium' => 2 ), array( 'Large' => 2 ), array( 'Small' => 1 ), array( 'Medium' => 3, 'Large' => 1 ) ) as $map ) {
        foreach ( array( 'off' => $off, 'on' => $on ) as $label => $item_id ) {
            $reply = $live( $item_id, $map );
            $built = $cart( $item_id, $payload( $map ) );
            $assert( $near( $reply['total_price'], $built['tp'] ), 'Live == cart [' . $label . '] ' . wp_json_encode( $map ) . ' => ' . $built['tp'] );
        }
    }

    // ---- 5. The "what's going on" notes ---------------------------------------------------------------
    $reply = $live( $on, array( 'Small' => 1, 'Medium' => 2 ) );
    $assert( 3 === (int) $reply['ticket_item_quantity'], 'Live ON: reply carries the 3 billed units' );
    $assert( false !== strpos( $reply['duration_note'], '3 units' ) && false !== strpos( $reply['duration_note'], '$66.00' ), 'Live ON: duration note says "3 units x $66.00 each" -> ' . $reply['duration_note'] );
    $assert( false !== strpos( $reply['variation_note'], 'Medium: 2 × $6.00' ), 'Live: variation note explains the $12 -> ' . $reply['variation_note'] );
    $assert( false === strpos( $reply['variation_note'], 'Small' ), 'Live: a $0 value is left out of the variation note' );
    $reply = $live( $off, array( 'Small' => 1, 'Medium' => 2 ) );
    $assert( '' === $reply['duration_note'], 'Live OFF: no duration note (base is charged once)' );
    $assert( false !== strpos( $reply['variation_note'], 'Medium: 2 × $6.00' ), 'Live OFF: variation note still explains the $12' );
    $reply = $live( $on, array( 'Small' => 1 ) );
    $assert( '' === $reply['duration_note'], 'Live ON: no duration note for a single unit' );

    // ---- 6. Admin save wiring -------------------------------------------------------------------------
    $admin = $make_item( true, false );
    wp_set_current_user( 1 );
    $_POST = array(
        'rbfw_ticket_type_nonce'       => wp_create_nonce( 'rbfw_ticket_type_nonce' ),
        'rbfw_enable_variations'       => 'yes',
        'rbfw_variation_multiply_base' => 'yes',
        'rbfw_variations_data'         => get_post_meta( $admin, 'rbfw_variations_data', true ),
    );
    ( new RBFW_Inventory() )->settings_save( $admin );
    $assert( 'yes' === get_post_meta( $admin, 'rbfw_variation_multiply_base', true ), 'Classic save: ticked toggle stores yes' );
    unset( $_POST['rbfw_variation_multiply_base'] );
    ( new RBFW_Inventory() )->settings_save( $admin );
    $assert( 'no' === get_post_meta( $admin, 'rbfw_variation_multiply_base', true ), 'Classic save: unticked toggle stores no' );
    $_POST['rbfw_variation_multiply_base'] = '<script>';
    ( new RBFW_Inventory() )->settings_save( $admin );
    $assert( 'no' === get_post_meta( $admin, 'rbfw_variation_multiply_base', true ), 'Classic save: any value other than yes stores no' );

    ob_start();
    RBFW_Inventory::render_for_modern_editor( $admin );
    $html = ob_get_clean();
    $assert( false !== strpos( $html, 'name="rbfw_variation_multiply_base"' ), 'Modern editor renders the toggle' );

    WP_CLI::success( "All {$checks} checks passed." );
} finally {
    foreach ( $ids as $id ) {
        wp_delete_post( $id, true );
    }
    $_POST    = array();
    $_REQUEST = array();
    if ( null !== $saved_rbfw ) {
        $GLOBALS['rbfw'] = $saved_rbfw;
    }
}
