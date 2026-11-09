<?php
/** wp eval-file /absolute/path/to/tests/shared-stock-display.php */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
$checks = 0;
$failures = array();
$items = array();
$settings = get_option( 'rbfw_basic_gen_settings', array() );
$today_enabled = 'yes';
$filter_settings = static function () use ( $settings, &$today_enabled ) {
	$options = is_array( $settings ) ? $settings : array();
	$options['today_booking_enable'] = $today_enabled;
	$options['today_booking_cutoff_time'] = '';
	return $options;
};
add_filter( 'pre_option_rbfw_basic_gen_settings', $filter_settings );
$assert = static function ( $condition, $message ) use ( &$checks, &$failures ) {
	++$checks;
	if ( ! $condition ) {
		$failures[] = $message;
		WP_CLI::log( 'FAIL ' . $message );
	}
};
$make = static function ( $type ) use ( &$items ) {
	$id = wp_insert_post( array( 'post_type' => $type, 'post_status' => 'publish', 'post_title' => 'Shared stock display regression fixture' ) );
	$items[] = $id;
	return $id;
};
$today = current_time( 'Y-m-d' );
$year = (int) substr( $today, 0, 4 );
$month = (int) substr( $today, 5, 2 );
$has_badge = static function ( $id ) {
	ob_start();
	rbfw_fd_summary_badges( $id );
	return false !== strpos( ob_get_clean(), 'rbfw-sd-badge--available' );
};
try {
	$id = $make( 'rbfw_item' );
	foreach ( array(
		'rbfw_item_type' => 'bike_car_sd', 'manage_inventory_as_timely' => 'on',
		'rbfw_item_stock_quantity_timely' => 1,
		'rbfw_bike_car_sd_data' => array(
			array( 'rent_type' => '2 Hours', 'qty' => 3, 'price' => 10, 'duration' => 2, 'd_type' => 'Hours' ),
			array( 'rent_type' => '4 Hours', 'qty' => 3, 'price' => 20, 'duration' => 4, 'd_type' => 'Hours' ),
		),
	) as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	$stock = rbfw_calendar_day_stock_map( $id, $year, $month );
	$assert( 1 === $stock[ $today ]['n'], 'Timely calendar uses the shared physical quantity, not the sum of duration options' );
	$assert( $has_badge( $id ), 'Available item retains its badge' );
	$native = $make( 'rbfw_booking' );
	$entry = array(
		'rbfw_order_status' => 'pending', 'rbfw_start_date_ymd' => $today, 'rbfw_end_date_ymd' => gmdate( 'Y-m-d', strtotime( $today . ' +1 day' ) ),
		'rbfw_start_time_24' => '00:00', 'rbfw_end_time_24' => '00:00',
		'rbfw_type_info' => array( '2 Hours' => 1 ), 'rbfw_item_quantity' => 1,
	);
	foreach ( array( 'pending', 'on-hold', 'processing', 'completed' ) as $status ) {
		$entry['rbfw_order_status'] = $status;
		update_post_meta( $id, 'rbfw_inventory', array( $native => $entry ) );
		$stock = rbfw_calendar_day_stock_map( $id, $year, $month );
		$assert( 0 === $stock[ $today ]['n'] && '' === $stock[ $today ]['p'], "$status: every duration draws from the same occupied unit" );
		$assert( ! $has_badge( $id ), "$status: rented-out item does not say Available Today" );
	}
	$entry['rbfw_order_status'] = 'cancelled';
	update_post_meta( $id, 'rbfw_inventory', array( $native => $entry ) );
	$stock = rbfw_calendar_day_stock_map( $id, $year, $month );
	$assert( 1 === $stock[ $today ]['n'], 'Cancellation restores shared calendar stock' );
	$assert( $has_badge( $id ), 'Cancellation restores the availability badge' );
	$entry['rbfw_order_status'] = 'processing';
	$entry['rbfw_start_date_ymd'] = gmdate( 'Y-m-d', strtotime( $today . ' -1 day' ) );
	$entry['rbfw_end_date_ymd'] = $today;
	update_post_meta( $id, 'rbfw_inventory', array( $native => $entry ) );
	$stock = rbfw_calendar_day_stock_map( $id, $year, $month, (int) substr( $today, 8, 2 ) );
	$assert( 1 === count( $stock ) && 1 === $stock[ $today ]['n'], 'Midnight return frees the next day; summary computes only the requested day' );
	update_post_meta( $id, 'rbfw_buffer_time_after', 2 );
	$stock = rbfw_calendar_day_stock_map( $id, $year, $month );
	$assert( 0 === $stock[ $today ]['n'] && ! empty( $stock[ $today ]['p'] ), 'Buffer after return blocks the beginning of the next day' );
	$assert( $has_badge( $id ), 'Partly available days keep the badge' );
	update_post_meta( $id, 'rbfw_inventory', array() );
	update_post_meta( $id, 'rbfw_item_type', 'appointment' );
	$stock = rbfw_calendar_day_stock_map( $id, $year, $month );
	$assert( 1 === $stock[ $today ]['n'], 'Appointment timely calendar uses shared stock too' );
	update_post_meta( $id, 'manage_inventory_as_timely', 'off' );
	$stock = rbfw_calendar_day_stock_map( $id, $year, $month );
	$assert( 6 === $stock[ $today ]['n'], 'Independent-option inventory retains its separate quantities' );
	$today_enabled = 'no';
	$assert( ! $has_badge( $id ), 'Same-day booking disabled: no Available Today claim' );
} finally {
	foreach ( array_reverse( $items ) as $id ) {
		$product = (int) get_post_meta( $id, 'link_wc_product', true );
		wp_delete_post( $id, true );
		if ( $product ) { wp_delete_post( $product, true ); }
	}
	remove_filter( 'pre_option_rbfw_basic_gen_settings', $filter_settings );
}
if ( $failures ) { WP_CLI::error( count( $failures ) . " of $checks checks failed." ); }
WP_CLI::success( "$checks checks passed." );
