<?php
/**
 * Run on a development site: wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/md-legacy-extra-services.php
 *
 * Multiple Day items (bike_car_md / equipment / dress / others) set up before 2.7.2 keep their
 * extra services in rbfw_extra_service_data. 2.7.2 stopped rendering them on the booking form
 * and hid their table in the editor, while pricing still read them: the services "vanished".
 */
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
$render_form = static function ( $id ) {
    $html = (string) rbfw_add_to_cart_shortcode_func( array( 'id' => $id ) );
    // The MD form enqueues its own scripts and may echo inline data; keep only what the shortcode returned.
    return $html;
};
$render_editor = static function ( $id ) {
    $GLOBALS['rbfw_modern_editor_rendering'] = true;
    ob_start();
    RBFW_Extra_Service::render_for_modern_editor( $id );
    $html = ob_get_clean();
    unset( $GLOBALS['rbfw_modern_editor_rendering'] );
    preg_match( '/<div class="rbfw_es_price_config_wrapper"[^>]*>/', $html, $m );
    return $m ? $m[0] : '';
};

// Index 1 has no name (the classic editor keeps such a row when only a price was typed):
// the form must skip it without shifting Bag onto Lock's stock.
$legacy = array(
    array( 'service_name' => 'Lock', 'service_price' => '20', 'service_qty' => '5' ),
    array( 'service_price' => '5' ),
    array( 'service_name' => 'Bag', 'service_price' => '10', 'service_qty' => '' ),
);
$base_meta = array(
    'rbfw_enable_daily_rate'   => 'yes',
    'rbfw_daily_rate'          => 100,
    'rbfw_enable_hourly_rate'  => 'no',
    'rbfw_item_stock_quantity' => 10,
);

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

try {
    $md   = $make( 'Legacy ES md', 'bike_car_md', $base_meta + array( 'rbfw_extra_service_data' => $legacy ) );
    $eq   = $make( 'Legacy ES equipment', 'equipment', $base_meta + array( 'rbfw_extra_service_data' => $legacy ) );
    $none = $make( 'No legacy ES md', 'bike_car_md', $base_meta );

    // 1. Booking form.
    foreach ( array( 'bike_car_md' => $md, 'equipment' => $eq ) as $type => $id ) {
        $html = $render_form( $id );
        $assert( false !== strpos( $html, 'Optional Add-ons' ), "$type form lists the legacy extra services" );
        $assert( (bool) preg_match( '/name="rbfw_service_info\[0\]\[service_name\]" value="Lock"/', $html ), "$type form posts Lock under its stored index 0" );
        $assert( (bool) preg_match( '/name="rbfw_service_info\[2\]\[service_name\]" value="Bag"/', $html ), "$type form posts Bag under its stored index 2" );
        $assert( false === strpos( $html, 'rbfw_service_info[1]' ), "$type form skips the unnamed row" );
        $assert( (bool) preg_match( '/class="rbfw_bikecarmd_es_qty" data-es-key="2"[^>]*data-name="Bag"/', $html ), "$type form tags Bag's quantity with its stock key" );
    }
    $assert( false === strpos( $render_form( $none ), 'Optional Add-ons' ), 'an item without legacy services gets no empty add-ons block' );

    // 2. The posted shape the form produces is still priced from stored meta.
    $builder = new RBFW_Woocommerce( false );
    $start   = gmdate( 'Y-m-d', strtotime( '+10 days' ) );
    $end     = gmdate( 'Y-m-d', strtotime( '+14 days' ) );
    $build   = static function ( $extra ) use ( $builder, $md, $start, $end ) {
        $payload = array_merge( array(
            'rbfw_post_id'           => $md,
            'rbfw_pickup_start_date' => $start,
            'rbfw_pickup_end_date'   => $end,
            'rbfw_pickup_start_time' => '10:00',
            'rbfw_pickup_end_time'   => '10:00',
            'rbfw_enable_time_slot'  => 'off',
            'rbfw_item_quantity'     => '1',
            'nonce'                  => wp_create_nonce( 'rbfw_ajax_action' ),
        ), $extra );
        $data = $builder->rbfw_add_cart_item_func( array(), $md, $payload );
        return is_array( $data ) ? (float) $data['rbfw_tp'] : null;
    };
    $control = $build( array() );
    $assert( $control > 0, 'control booking prices above zero (' . $control . ')' );
    $with = $build( array( 'rbfw_service_info' => array(
        0 => array( 'service_name' => 'Lock', 'service_price' => '20', 'service_qty' => '2' ),
        2 => array( 'service_name' => 'Bag', 'service_price' => '10', 'service_qty' => '0' ),
    ) ) );
    $assert( null !== $with && abs( $with - ( $control + 40 ) ) < 0.01, 'Lock x2 adds 2 x 20, unselected Bag adds nothing (' . $with . ')' );
    $tampered = $build( array( 'rbfw_service_info' => array(
        0 => array( 'service_name' => 'Lock', 'service_price' => '0.01', 'service_qty' => '1' ),
    ) ) );
    $assert( null !== $tampered && abs( $tampered - ( $control + 20 ) ) < 0.01, 'a posted service_price is ignored; the stored price is charged' );

    // 3. Editor: the legacy table is no longer hidden when the item has rows in it.
    $assert( false === strpos( $render_editor( $md ), 'display:none' ), 'modern editor shows the Extra Services table for an MD item with legacy rows' );
    $assert( false === strpos( $render_editor( $eq ), 'display:none' ), 'modern editor shows the Extra Services table for an equipment item with legacy rows' );
    $assert( false !== strpos( $render_editor( $none ), 'display:none' ), 'modern editor still hides it for an MD item without legacy rows' );
    $assert( false !== strpos( $render_editor( $md ), 'data-has-legacy-data="1"' ), 'the table tells the editor JS it holds legacy rows' );
} finally {
    foreach ( $ids as $id ) {
        $product = (int) get_post_meta( $id, 'link_wc_product', true );
        if ( $product ) {
            wp_delete_post( $product, true );
        }
        wp_delete_post( $id, true );
    }
    $GLOBALS['rbfw'] = $saved_rbfw;
}
WP_CLI::success( "$checks checks passed" );
