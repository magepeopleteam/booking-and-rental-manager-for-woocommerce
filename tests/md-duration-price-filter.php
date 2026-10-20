<?php
/**
 * The `rbfw_md_duration_price_info` filter on rbfw_md_duration_price_calculation().
 *
 * Run on a development site with WooCommerce and this plugin active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/md-duration-price-filter.php
 *
 * Rules under test: with no listener the result is exactly the core calculation
 * (rbfw_md_duration_price_calculation_raw()); a listener receives the item id and
 * the booking dates, and what it returns is what every caller gets.
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

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

$item = wp_insert_post( array(
    'post_type'   => 'rbfw_item',
    'post_status' => 'publish',
    'post_title'  => 'Duration price filter test',
    'meta_input'  => array(
        'rbfw_item_type'           => 'bike_car_md',
        'rbfw_enable_daily_rate'   => 'yes',
        'rbfw_daily_rate'          => 20,
        'rbfw_enable_hourly_rate'  => 'no',
        'rbfw_enable_weekly_rate'  => 'no',
        'rbfw_enable_monthly_rate' => 'no',
    ),
) );

try {
    $args = array( $item, '2026-11-02 00:00', '2026-11-05 00:00', '2026-11-02', '2026-11-05', '', '', 'no' );

    $raw      = rbfw_md_duration_price_calculation_raw( ...$args );
    $filtered = rbfw_md_duration_price_calculation( ...$args );
    $assert( $raw == $filtered, 'no listener: result equals the core calculation' );

    $seen     = array();
    $listener = static function ( $info, $post_id, $context ) use ( &$seen ) {
        $seen                   = array( $post_id, $context );
        $info['duration_price'] = 1.5;
        return $info;
    };
    add_filter( 'rbfw_md_duration_price_info', $listener, 10, 3 );
    $changed = rbfw_md_duration_price_calculation( ...$args );
    remove_filter( 'rbfw_md_duration_price_info', $listener, 10 );

    $assert( 1.5 === $changed['duration_price'], 'listener result is returned' );
    $assert( $raw['total_days'] === $changed['total_days'], 'other keys untouched' );
    $assert( (int) $seen[0] === (int) $item, 'listener gets the item id' );
    $assert( '2026-11-02' === $seen[1]['start_date'] && '2026-11-05 00:00' === $seen[1]['dropoff_datetime'] && 'no' === $seen[1]['enable_time_slot'], 'listener gets the booking dates' );
} finally {
    wp_delete_post( $item, true );
    $GLOBALS['rbfw'] = $saved_rbfw;
}

WP_CLI::success( "$checks checks passed." );
