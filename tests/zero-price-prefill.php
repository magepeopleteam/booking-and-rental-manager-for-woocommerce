<?php
/**
 * Run on a development site with WooCommerce active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/zero-price-prefill.php
 *
 * Verifies:
 * 1. Brand-new items do not prefill example prices (10, 50, 90, 20/35, 80/120)
 *    and do not enable MD daily rate by default.
 * 2. Explicit 0 saved for rbfw_daily_rate is preserved when rendering Pricing admin.
 * 3. Disabled daily rate (rbfw_enable_daily_rate = 'no') does not charge a
 *    leftover rbfw_daily_rate in rbfw_md_duration_price_calculation_raw().
 * 4. Enabled daily rate at 0 charges 0, and enabled daily rate > 0 still charges normally.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
if ( ! function_exists( 'WC' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};

require_once WP_PLUGIN_DIR . '/booking-and-rental-manager-for-woocommerce/admin/settings/Pricing.php';
require_once WP_PLUGIN_DIR . '/booking-and-rental-manager-for-woocommerce/lib/classes/class-time-slots-page.php';

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: new MageRBFWClass();
if ( empty( $GLOBALS['RBFW_Timeslots_Page'] ) ) {
	$GLOBALS['RBFW_Timeslots_Page'] = new RBFW_Timeslots_Page();
}

$posts = array();

try {
	$pricing = new RBFW_Pricing();

	// 1. Brand-new MD item: daily rate defaults to 0 and disabled ('no').
	$md_new = wp_insert_post(
		array(
			'post_type'   => 'rbfw_item',
			'post_status' => 'auto-draft',
			'post_title'  => 'New MD Item',
		)
	);
	$posts[] = $md_new;
	update_post_meta( $md_new, 'rbfw_item_type', 'bike_car_md' );

	ob_start();
	$pricing->md_price_config( $md_new );
	$md_html = ob_get_clean();

	$assert(
		strpos( $md_html, 'id="daily-price-input"' ) !== false && preg_match( '/name="rbfw_daily_rate"[^>]*value="0"/', $md_html ) === 1,
		'new MD item prefills daily rate as 0, not 10'
	);
	$assert(
		preg_match( '/id="rbfw_enable_daily_rate"[^>]*value="no"/', $md_html ) === 1,
		'new MD item defaults rbfw_enable_daily_rate to no'
	);

	// 2. Explicit 0 saved for rbfw_daily_rate is preserved.
	update_post_meta( $md_new, 'rbfw_daily_rate', '0' );
	update_post_meta( $md_new, 'rbfw_enable_daily_rate', 'yes' );
	ob_start();
	$pricing->md_price_config( $md_new );
	$md_zero_html = ob_get_clean();
	$assert(
		preg_match( '/name="rbfw_daily_rate"[^>]*value="0"/', $md_zero_html ) === 1,
		'explicit 0 daily rate is preserved on re-render'
	);

	// 3. Brand-new SD item: no prefilled 10 / 50 prices.
	$sd_new = wp_insert_post(
		array(
			'post_type'   => 'rbfw_item',
			'post_status' => 'auto-draft',
			'post_title'  => 'New SD Item',
		)
	);
	$posts[] = $sd_new;
	update_post_meta( $sd_new, 'rbfw_item_type', 'bike_car_sd' );

	ob_start();
	$pricing->bike_car_single_day( $sd_new );
	$sd_html = ob_get_clean();
	$assert(
		strpos( $sd_html, 'name="rbfw_bike_car_sd_data[0][price]"' ) !== false
		&& preg_match( '/name="rbfw_bike_car_sd_data\[[0-9]+\]\[price\]"[^>]*value="(10|50)"/', $sd_html ) === 0,
		'new Single Day item does not prefill 10 or 50 in rental option prices'
	);

	// 4. Brand-new Appointment item: no prefilled 50 / 90 prices.
	$appt_new = wp_insert_post(
		array(
			'post_type'   => 'rbfw_item',
			'post_status' => 'auto-draft',
			'post_title'  => 'New Appointment Item',
		)
	);
	$posts[] = $appt_new;
	update_post_meta( $appt_new, 'rbfw_item_type', 'appointment' );

	ob_start();
	$pricing->bike_car_single_day( $appt_new );
	$appt_html = ob_get_clean();
	$assert(
		strpos( $appt_html, 'value="50"' ) === false && strpos( $appt_html, 'value="90"' ) === false,
		'new Appointment item does not prefill 50 or 90 in session prices'
	);

	// 5. Brand-new Resort item: no prefilled 80.00 / 120.00 / 50.00 prices.
	$resort_new = wp_insert_post(
		array(
			'post_type'   => 'rbfw_item',
			'post_status' => 'auto-draft',
			'post_title'  => 'New Resort Item',
		)
	);
	$posts[] = $resort_new;
	update_post_meta( $resort_new, 'rbfw_item_type', 'resort' );

	ob_start();
	$pricing->resort_price_config( $resort_new );
	$resort_html = ob_get_clean();
	$assert(
		strpos( $resort_html, 'value="80.00"' ) === false
		&& strpos( $resort_html, 'value="120.00"' ) === false
		&& strpos( $resort_html, 'value="50.00"' ) === false,
		'new Resort item does not prefill room example prices'
	);

	// 6. Brand-new Multiple Items: no prefilled 5 / 20 / 8 / 35 prices.
	$mi_new = wp_insert_post(
		array(
			'post_type'   => 'rbfw_item',
			'post_status' => 'auto-draft',
			'post_title'  => 'New MI Item',
		)
	);
	$posts[] = $mi_new;
	update_post_meta( $mi_new, 'rbfw_item_type', 'multiple_items' );

	ob_start();
	$pricing->multiple_items( $mi_new );
	$mi_html = ob_get_clean();
	$assert(
		preg_match( '/name="multiple_items_info\[\]\[daily_price\]"[^>]*value="(20|35)"/', $mi_html ) === 0
		&& preg_match( '/name="multiple_items_info\[\]\[hourly_price\]"[^>]*value="(5|8)"/', $mi_html ) === 0,
		'new Multiple Items product does not prefill hourly or daily example prices'
	);

	// 7. Frontend MD calculation: disabled daily rate does NOT leak leftover rbfw_daily_rate = 10.
	$md_calc = wp_insert_post(
		array(
			'post_type'   => 'rbfw_item',
			'post_status' => 'publish',
			'post_title'  => 'Disabled Daily MD Item',
		)
	);
	$posts[] = $md_calc;
	update_post_meta( $md_calc, 'rbfw_item_type', 'bike_car_md' );
	update_post_meta( $md_calc, 'rbfw_enable_daily_rate', 'no' );
	update_post_meta( $md_calc, 'rbfw_daily_rate', '10' );
	update_post_meta( $md_calc, 'rbfw_enable_hourly_rate', 'no' );
	update_post_meta( $md_calc, 'rbfw_enable_half_day_rate', 'no' );
	update_post_meta( $md_calc, 'rbfw_enable_weekly_rate', 'no' );
	update_post_meta( $md_calc, 'rbfw_enable_monthly_rate', 'no' );

	$start = gmdate( 'Y-m-d', strtotime( '+2 days' ) );
	$end   = gmdate( 'Y-m-d', strtotime( '+4 days' ) );

	$res_disabled = rbfw_md_duration_price_calculation_raw( $md_calc, $start . ' 00:00:00', $end . ' 00:00:00', $start, $end );
	$assert(
		(float) $res_disabled['duration_price'] === 0.0,
		'disabled daily rate returns 0 duration_price even when leftover rbfw_daily_rate is 10'
	);

	// 8. Enabled daily rate at 0 returns 0.
	update_post_meta( $md_calc, 'rbfw_enable_daily_rate', 'yes' );
	update_post_meta( $md_calc, 'rbfw_daily_rate', '0' );
	$res_zero = rbfw_md_duration_price_calculation_raw( $md_calc, $start . ' 00:00:00', $end . ' 00:00:00', $start, $end );
	$assert(
		(float) $res_zero['duration_price'] === 0.0,
		'enabled daily rate at 0 returns 0 duration_price'
	);

	// 9. Enabled daily rate at 25 still charges 25 * days.
	update_post_meta( $md_calc, 'rbfw_daily_rate', '25' );
	$res_paid = rbfw_md_duration_price_calculation_raw( $md_calc, $start . ' 00:00:00', $end . ' 00:00:00', $start, $end );
	$assert(
		(float) $res_paid['duration_price'] === ( 25.0 * (int) $res_paid['total_days'] ),
		'enabled daily rate at 25 charges 25 * total_days'
	);

	WP_CLI::success( "All {$checks} zero-price-prefill checks passed." );
} finally {
	foreach ( $posts as $pid ) {
		wp_delete_post( $pid, true );
	}
}
