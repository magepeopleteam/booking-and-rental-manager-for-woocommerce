<?php
/** Run on a development site: wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/pricing-display.php */
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
$items = array(
    array( 'item_name' => 'A', 'hourly_price' => 8, 'daily_price' => 50 ),
    array( 'item_name' => 'B', 'hourly_price' => 5, 'daily_price' => 40 ),
);
$assert( 0.0 === findMinimumPrice( array() )['price'], 'Empty rate lists never expose a sentinel price' );
$assert( null === findMinimumPrice( array( array( 'daily_price' => '' ) ) )['price_type'], 'Blank rates have no duration label' );
$assert( 40.0 === findMinimumPrice( $items, 'daily' )['price'], 'Preferred daily duration uses the lowest daily rate' );
$assert( 5.0 === findMinimumPrice( $items, 'monthly' )['price'], 'Missing preferred duration falls back to a configured rate' );
$assert( 0.0 === findMinimumPrice( array( array( 'daily_price' => '0' ), array( 'daily_price' => 40 ) ), 'daily' )['price'], 'An explicit zero rate remains valid' );
$assert( null === findMinimumPrice( array( null, array( 'daily_price' => -1 ), array( 'daily_price' => 'bad' ), array( 'daily_price' => INF ) ) )['price_type'], 'Malformed and negative rates are ignored' );
$id = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'draft', 'post_title' => 'Pricing display regression fixture' ) );
try {
    update_post_meta( $id, 'rbfw_item_type', 'equipment' );
    update_post_meta( $id, 'rbfw_enable_daily_rate', 'yes' );
    update_post_meta( $id, 'rbfw_enable_hourly_rate', 'no' );
    $renderer = ( new ReflectionClass( 'RBFW_Inventory' ) )->newInstanceWithoutConstructor();
    $render = static function ( $value ) use ( $renderer, $id ) {
        ob_start();
        $renderer->variation_price_cell( $id, 0, 0, $value );
        return ob_get_clean();
    };
    $html = $render( array( 'price' => '', 'prices' => array( 'daily' => 0, 'weekly' => 500 ) ) );
    $assert( false === strpos( $html, 'Any duration' ) && 1 === substr_count( $html, 'type="number"' ), 'Daily-only variations expose only the daily price input' );
    $assert( false !== strpos( $html, '[prices][weekly]" value="500"' ), 'Disabled duration prices survive subsequent saves' );
    $assert( false !== strpos( $render( array( 'price' => 25 ) ), 'Legacy flat price' ), 'Existing flat charges remain visible and editable' );
    update_post_meta( $id, 'rbfw_enable_daily_rate', 'no' );
    $assert( 1 === substr_count( $render( array() ), 'type="number"' ), 'Items with no durations retain the flat price field' );
    update_post_meta( $id, 'rbfw_enable_daily_rate', 'yes' );
    update_post_meta( $id, 'rbfw_variations_data', array( array( 'field_label' => 'Size', 'field_id' => 'size', 'value' => array(
        array( 'name' => 'Small', 'price' => '', 'prices' => array( 'daily' => 0 ) ),
        array( 'name' => 'Large', 'price' => '', 'prices' => array( 'daily' => 500 ) ),
        array( 'name' => 'Legacy', 'price' => 25 ),
    ) ) ) );
    $assert( 0.0 == rbfw_calc_variation_surcharge( $id, 'Small', array( 'daily' => 2 ) ), 'Zero surcharge does not affect the base rental rate' );
    $assert( 1000.0 == rbfw_calc_variation_surcharge( $id, 'Large', array( 'daily' => 2 ) ), 'Daily surcharge still multiplies by the billed duration' );
    $assert( 25.0 == rbfw_calc_variation_surcharge( $id, 'Legacy', array( 'daily' => 2 ) ), 'Legacy flat surcharges retain their original billing behavior' );
    // Exercise both real catalog templates with an isolated rental query.
    $only_fixture = static function ( $query ) use ( $id ) {
        if ( 'rbfw_item' === $query->get( 'post_type' ) ) {
            $query->set( 'p', $id );
            $query->set( 'post_status', 'draft' );
        }
    };
    update_post_meta( $id, 'rbfw_daily_rate', 100 );
    $saved_rbfw = $GLOBALS['rbfw'] ?? null;
    $GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();
    add_action( 'pre_get_posts', $only_fixture );
    try {
        foreach ( array( 'equipment', 'dress', 'others' ) as $type ) {
            update_post_meta( $id, 'rbfw_item_type', $type );
            foreach ( array( 'list', 'grid' ) as $style ) {
                $html = rbfw_rent_list_shortcode_func( array( 'style' => $style ) );
                $assert( false !== strpos( wp_strip_all_tags( $html ), wp_strip_all_tags( wc_price( 100 ) ) ) && false === strpos( $html, '9,223,372' ), $type . ' ' . $style . ' catalog uses the base rental price' );
            }
        }
    } finally {
        remove_action( 'pre_get_posts', $only_fixture );
        $GLOBALS['rbfw'] = $saved_rbfw;
    }
    // Rendering must use the requested item, even outside the classic post loop.
    update_post_meta( $id, 'rbfw_item_type', 'resort' );
    update_post_meta( $id, 'rbfw_enable_resort_daylong_price', 'yes' );
    $pricing = ( new ReflectionClass( 'RBFW_Pricing' ) )->newInstanceWithoutConstructor();
    ob_start();
    $pricing->resort_price_config( $id );
    $html = ob_get_clean();
    $assert( (bool) preg_match( '/name="rbfw_enable_resort_daylong_price"[^>]+checked/', $html ), 'Resort toggle reads the requested item outside the post loop' );
} finally {
    $product = (int) get_post_meta( $id, 'link_wc_product', true );
    wp_delete_post( $id, true );
    if ( $product && $product !== $id ) wp_delete_post( $product, true );
}
WP_CLI::success( $checks . ' pricing display regression checks passed.' );
