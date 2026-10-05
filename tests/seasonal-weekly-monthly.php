<?php
/**
 * Seasonal WEEKLY / MONTHLY rates on multi-day items (rbfw_sp_price_w / rbfw_sp_price_m).
 *
 * Run on a development site with WooCommerce, this plugin and the "Booking and Rental
 * Manager Addon: Seasonal Pricing" active (and "Count Extra Day" left at its default, on):
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/seasonal-weekly-monthly.php
 *
 * Before the fix a booking long enough to bill a whole week/month took the weekly/monthly
 * early-return path in rbfw_md_duration_price_calculation(), which never consulted seasonal
 * prices, and the season row had no weekly/monthly field to set anyway.
 *
 * Rule under test: each billed week/month is charged the seasonal rate of the season its
 * FIRST day falls in; leftover days use the seasonal daily rate; a season that leaves a rate
 * blank falls back to the item's regular rate (so seasons saved before this feature price
 * exactly as they always did).
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

if ( ! is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) ) {
    WP_CLI::error( 'Activate the Seasonal Pricing addon first.' );
}

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();
if ( 'on' !== $GLOBALS['rbfw']->get_option_trans( 'rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on' ) ) {
    WP_CLI::error( 'Turn "Count Extra Day" on: the expected totals below include the return day.' );
}

$ids = array();

$make_item = static function ( $meta, $status = 'publish' ) use ( &$ids ) {
    $id = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => $status, 'post_title' => 'Seasonal weekly/monthly fixture' ) );
    $meta = array_merge( array(
        'rbfw_item_type'           => 'bike_car_md',
        'rbfw_enable_daily_rate'   => 'yes',
        'rbfw_daily_rate'          => 100,
        'rbfw_enable_hourly_rate'  => 'no',
        'rbfw_enable_half_day_rate' => 'no',
        'rbfw_enable_time_picker'  => 'no',
        'rbfw_enable_weekly_rate'  => 'no',
        'rbfw_enable_monthly_rate' => 'no',
        'rbfw_item_stock_quantity' => 1000,
    ), $meta );
    foreach ( $meta as $key => $value ) {
        update_post_meta( $id, $key, $value );
    }
    $ids[] = $id;
    return $id;
};
$season = static function ( $start, $end, $d, $w, $m ) {
    return array(
        'rbfw_sp_start_date' => $start,
        'rbfw_sp_end_date'   => $end,
        'rbfw_sp_price_h'    => '',
        'rbfw_sp_price_hd'   => '',
        'rbfw_sp_price_d'    => $d,
        'rbfw_sp_price_w'    => $w,
        'rbfw_sp_price_m'    => $m,
    );
};
$price = static function ( $id, $start, $end ) {
    $info = rbfw_md_duration_price_calculation( $id, $start . ' 00:00', $end . ' 00:00', $start, $end, '', '', 'no' );
    return (float) $info['duration_price'];
};

try {
    // S1 has weekly+daily; S2 deliberately has NO weekly (a season saved before this feature).
    $s1 = $season( '2030-03-01', '2030-03-31', '80', '500', '' );
    $s2 = $season( '2030-06-01', '2030-06-30', '90', '', '' );

    /* ── Weekly only ─────────────────────────────────────────────── */
    $weekly_item = $make_item( array(
        'rbfw_enable_weekly_rate' => 'yes',
        'rbfw_weekly_rate'        => 600,
        'rbfw_seasonal_prices'    => array( $s1, $s2 ),
    ) );

    $assert( $near( $price( $weekly_item, '2030-03-01', '2030-03-07' ), 500 ), 'one in-season week bills the seasonal weekly rate (500, not 600)' );
    $assert( $near( $price( $weekly_item, '2030-03-01', '2030-03-09' ), 500 + 2 * 80 ), 'week + 2 in-season leftover days = 500 + 2 x seasonal daily 80' );
    $assert( $near( $price( $weekly_item, '2030-02-22', '2030-03-02' ), 600 + 2 * 80 ), 'week starting before the season keeps the regular 600; its in-season leftover days use 80' );
    $assert( $near( $price( $weekly_item, '2030-05-01', '2030-05-09' ), 600 + 2 * 100 ), 'out-of-season booking prices exactly as before (600 + 2 x 100)' );
    $assert( $near( $price( $weekly_item, '2030-06-01', '2030-06-07' ), 600 ), 'season with a blank weekly rate falls back to the regular weekly rate' );
    $assert( $near( $price( $weekly_item, '2030-06-01', '2030-06-09' ), 600 + 2 * 90 ), 'blank-weekly season still applies its seasonal daily rate to leftover days' );
    $assert( $near( $price( $weekly_item, '2030-03-01', '2030-03-03' ), 3 * 80 ), 'booking shorter than a week keeps the per-day seasonal path (3 x 80)' );

    /* ── Weekly day-threshold (extra week once leftover days reach it) ── */
    $threshold_item = $make_item( array(
        'rbfw_enable_weekly_rate'            => 'yes',
        'rbfw_weekly_rate'                   => 600,
        'rbfw_enable_day_threshold_for_weekly' => 'yes',
        'rbfw_day_threshold_for_weekly'      => 5,
        'rbfw_seasonal_prices'               => array( $s1 ),
    ) );
    $assert( $near( $price( $threshold_item, '2030-03-01', '2030-03-12' ), 2 * 500 ), 'threshold-rounded extra week is priced seasonally too (2 x 500)' );
    $assert( $near( $price( $threshold_item, '2030-03-25', '2030-04-07' ), 500 + 600 ), 'two weeks straddling the season end: week 1 (03-25) seasonal 500, week 2 (04-01) regular 600' );

    /* ── Monthly + weekly + daily ────────────────────────────────── */
    $s3 = $season( '2030-07-01', '2030-07-31', '80', '500', '1800' );
    $monthly_item = $make_item( array(
        'rbfw_enable_weekly_rate'  => 'yes',
        'rbfw_weekly_rate'         => 600,
        'rbfw_enable_monthly_rate' => 'yes',
        'rbfw_monthly_rate'        => 2000,
        'rbfw_seasonal_prices'     => array( $s3 ),
    ) );
    $assert( $near( $price( $monthly_item, '2030-07-01', '2030-07-31' ), 1800 ), 'one in-season month bills the seasonal monthly rate (1800, not 2000)' );
    $assert( $near( $price( $monthly_item, '2030-07-01', '2030-08-09' ), 1800 + 600 + 2 * 100 ), 'month in season, following week and days past the season end at regular rates' );
    $assert( $near( $price( $monthly_item, '2030-06-25', '2030-08-03' ), 2000 + 500 + 3 * 100 ), 'month before the season is regular, but the week that follows it (07-25) is seasonal: week offset counts the months' );

    /* ── Helpers ─────────────────────────────────────────────────── */
    $assert( $near( rbfw_md_period_blocks_price( '', 'rbfw_sp_price_w', 600, '2030-03-01', 3, 'week' ), 1800 ), 'no seasonal prices: blocks price is the plain rate x count' );
    $overlap = array( $season( '2030-08-01', '2030-08-31', '', '', '' ), $season( '2030-08-10', '2030-08-20', '', '400', '' ) );
    $assert( null === rbfw_md_seasonal_period_rate( $overlap, '2030-08-15', 'rbfw_sp_price_w' ), 'overlapping seasons: the first listed wins, even when it leaves the rate blank' );
    $assert( 500.0 === rbfw_md_seasonal_period_rate( array( $s1 ), '2030-03-01', 'rbfw_sp_price_w' ), 'season start date is inclusive' );
    $assert( 500.0 === rbfw_md_seasonal_period_rate( array( $s1 ), '2030-03-31', 'rbfw_sp_price_w' ), 'season end date is inclusive' );
    $assert( null === rbfw_md_seasonal_period_rate( array( $s1 ), '2030-04-01', 'rbfw_sp_price_w' ), 'day after the season is outside it' );

    /* ── Default call (no seasonal argument) stays unchanged ────────── */
    $assert( $near( rbfw_daywise_days_sum( $weekly_item, '2030-03-01', 0, 2, 100 ), 200 ), 'rbfw_daywise_days_sum without seasonal prices is unchanged (2 x 100)' );

    /* ── Admin: row markup + save through the addon's own hooks ───── */
    $admin_item = $make_item( array() );
    $_POST = array(
        'rbfw_sp_start_date'      => array( '2030-03-01', '2030-06-01', '' ),
        'rbfw_sp_end_date'        => array( '2030-03-31', '2030-06-30', '' ),
        'rbfw_sp_ticket_price_d'  => array( '80', '90', '' ),
        'rbfw_sp_ticket_price_w'  => array( '500', '', '' ),
        'rbfw_sp_ticket_price_m'  => array( '1800', '', '' ),
    );
    do_action( 'rbfw_modern_editor_save', $admin_item );
    $_POST = array();
    $saved = get_post_meta( $admin_item, 'rbfw_seasonal_prices', true );
    $assert( is_array( $saved ) && 2 === count( $saved ), 'save keeps the two complete season rows and drops the blank one' );
    $assert( '500' === (string) $saved[0]['rbfw_sp_price_w'] && '1800' === (string) $saved[0]['rbfw_sp_price_m'], 'weekly and monthly seasonal rates are persisted' );
    $assert( '' === $saved[1]['rbfw_sp_price_w'] && '' === $saved[1]['rbfw_sp_price_m'], 'blank weekly/monthly stay blank (fall back to the regular rate)' );

    ob_start();
    do_action( 'rbfw_after_week_price_table', $admin_item );
    $html = ob_get_clean();
    $assert( false !== strpos( $html, 'name="rbfw_sp_ticket_price_w[]"' ) && false !== strpos( $html, 'name="rbfw_sp_ticket_price_m[]"' ), 'admin season row renders Weekly and Monthly rate inputs' );
    $assert( false !== strpos( $html, 'value="500"' ) && false !== strpos( $html, 'value="1800"' ), 'saved weekly/monthly values are shown back in the row' );

    WP_CLI::success( $checks . ' checks passed.' );
} finally {
    $_POST = array();
    foreach ( $ids as $id ) {
        wp_delete_post( $id, true );
    }
    $GLOBALS['rbfw'] = $saved_rbfw;
}
