<?php
/**
 * Run on a development site with WooCommerce active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/rental-option-validation.php
 *
 * Single-day and appointment bookings name the chosen rental option in the request
 * (service_type, or rbfw_bikecarsd_info[n][rent_type]). Expected: unknown or missing
 * options are refused, the end date/time always comes from the item's own option,
 * malformed dates/times are refused, stock is enforced for appointments and variation
 * items, and the Custom Payment (standalone) checkout stores the period the price was
 * computed for.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
if ( ! function_exists( 'WC' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$checks = 0;
$failed = 0;
// RBFW_TEST_SOFT=1 logs every failure and keeps going (to list what an unpatched build gets wrong).
$soft   = (bool) getenv( 'RBFW_TEST_SOFT' );
$assert = static function ( $condition, $message ) use ( &$checks, &$failed, $soft ) {
	if ( ! $condition ) {
		if ( ! $soft ) {
			throw new RuntimeException( $message );
		}
		++$failed;
		WP_CLI::log( 'FAIL ' . $message );
		return;
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

// WooCommerce mode, read-only for this run.
$mode_filter = static function () {
	return array( RBFW_Function::MODE_KEY => 'woocommerce' );
};
add_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );

$posts   = array();
$builder = new RBFW_Woocommerce( false );
$build   = static function ( $id, $payload ) use ( $builder ) {
	$payload['nonce'] = wp_create_nonce( 'rbfw_ajax_action' );
	try {
		$data = $builder->rbfw_add_cart_item_func( array(), $id, $payload );
	} catch ( Throwable $e ) {
		// A crash is not a refusal: report it as an accepted request with no data.
		return array( 'rejected' => false, 'code' => 'fatal: ' . $e->getMessage(), 'data' => array(), 'tp' => null );
	}
	if ( ! is_array( $data ) ) {
		return array(
			'rejected' => true,
			'code'     => is_wp_error( $data ) ? $data->get_error_code() : '',
		);
	}
	return array(
		'rejected' => false,
		'code'     => '',
		'data'     => $data,
		'tp'       => isset( $data['rbfw_tp'] ) ? (float) $data['rbfw_tp'] : null,
	);
};
$make = static function ( $title, $meta ) use ( &$posts ) {
	$id      = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => $title ) );
	$posts[] = $id;
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	return $id;
};
$option = static function ( $name, $price, $extra = array() ) {
	return array_merge(
		array( 'rent_type' => $name, 'price' => $price, 'duration' => 2, 'd_type' => 'Hours', 'qty' => 5, 'short_desc' => '' ),
		$extra
	);
};

$day      = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
$far_end  = gmdate( 'Y-m-d', strtotime( '+60 days' ) );
$next_day = gmdate( 'Y-m-d', strtotime( '+31 days' ) );

try {
	/* ── Fixtures ─────────────────────────────────────────────────────── */
	// The report's item: Single Day, shared time-based stock of 2, one option "2 Hours" at 40.
	$timely = $make( 'ROV timely', array(
		'rbfw_item_type'                  => 'bike_car_sd',
		'manage_inventory_as_timely'      => 'on',
		'rbfw_item_stock_quantity_timely' => 2,
		'rbfw_enable_time_picker'         => 'yes',
		'enable_specific_duration'        => 'off',
		'rbfw_bike_car_sd_data'           => array( $option( '2 Hours', 40 ) ),
	) );
	$timely_form = static function ( $extra = array() ) use ( $timely, $day, $far_end ) {
		return array_merge( array(
			'rbfw_post_id'                 => $timely,
			'rbfw_bikecarsd_selected_date' => $day,
			'rbfw_start_time'              => '09:00',
			'rbfw_item_quantity'           => '2',
			'manage_inventory_as_timely'   => 'on',
			'rbfw_rent_type'               => 'bike_car_sd',
			'service_type'                 => '2 Hours',
			'rbfw_end_date'                => $far_end,
			'rbfw_end_time'                => '09:00',
		), $extra );
	};
	$plain = $make( 'ROV plain', array(
		'rbfw_item_type'        => 'bike_car_sd',
		'rbfw_bike_car_sd_data' => array( $option( '2 Hours', 40, array( 'qty' => 1 ) ), $option( 'Half  Day', 25, array( 'duration' => 4 ) ) ),
	) );
	$plain_form = static function ( $rows, $extra = array() ) use ( $plain, $day, $far_end ) {
		return array_merge( array(
			'rbfw_post_id'                 => $plain,
			'rbfw_bikecarsd_selected_date' => $day,
			'rbfw_start_time'              => '10:00',
			'rbfw_bikecarsd_info'          => $rows,
			'rbfw_end_date'                => $far_end,
			'rbfw_end_time'                => '10:00',
		), $extra );
	};
	$specific = $make( 'ROV specific', array(
		'rbfw_item_type'                  => 'bike_car_sd',
		'manage_inventory_as_timely'      => 'on',
		'rbfw_item_stock_quantity_timely' => 3,
		'enable_specific_duration'        => 'on',
		'rbfw_bike_car_sd_data'           => array(
			$option( 'Morning', 30, array( 'start_time' => '09:00', 'end_time' => '12:00' ) ),
			$option( 'Night', 30, array( 'start_time' => '22:00', 'end_time' => '02:00' ) ),
		),
	) );
	$appointment = $make( 'ROV appointment', array(
		'rbfw_item_type'        => 'appointment',
		'rbfw_bike_car_sd_data' => array( $option( 'Consultation', 50, array( 'duration' => 1, 'qty' => 1 ) ) ),
	) );
	$variation = $make( 'ROV variation', array(
		'rbfw_item_type'         => 'bike_car_sd',
		'rbfw_enable_variations' => 'yes',
		'rbfw_variations_data'   => array( array( 'field_label' => 'Size', 'field_id' => 'rbfw_variation_id_0', 'value' => array( array( 'name' => 'M', 'quantity' => 5 ) ), 'selected_value' => '' ) ),
		'rbfw_bike_car_sd_data'  => array( $option( '2 Hours', 40, array( 'qty' => 1 ) ) ),
	) );
	$free = $make( 'ROV free option', array(
		'rbfw_item_type'        => 'bike_car_sd',
		'rbfw_bike_car_sd_data' => array( $option( 'Trial', 0 ) ),
	) );
	$gap = $make( 'ROV pricing gap', array(
		'rbfw_item_type'        => 'bike_car_sd',
		'rbfw_bike_car_sd_data' => array( $option( 'Odd', 40 ), $option( 'Odd', -40 ) ),
	) );

	/* ── 1. The report: an option name the item does not have ─────────── */
	$ok = $build( $timely, $timely_form() );
	$assert( ! $ok['rejected'] && abs( $ok['tp'] - 80 ) < 0.01, 'Configured "2 Hours" x 2 prices at 80.00' );
	$assert( $day === $ok['data']['rbfw_end_date'] && '11:00:00' === $ok['data']['rbfw_end_time'], 'Configured option: the end is start + 2 hours, the posted end date is ignored' );
	$bad = $build( $timely, $timely_form( array( 'service_type' => '2 Hours-X' ) ) );
	$assert( $bad['rejected'] && 'rbfw_invalid_rental_option' === $bad['code'], 'service_type "2 Hours-X" is refused' );
	foreach ( array( '2 hours', '2Hours', '3 Hours', '', 'Consultation' ) as $name ) {
		$assert( $build( $timely, $timely_form( array( 'service_type' => $name ) ) )['rejected'], 'service_type ' . var_export( $name, true ) . ' is refused' );
	}
	$assert( $build( $timely, $timely_form( array( 'service_type' => array( '2 Hours' ) ) ) )['rejected'], 'service_type posted as an array is refused, not a fatal' );
	$tagged = $build( $timely, $timely_form( array( 'service_type' => '2 Hours<b>' ) ) );
	$assert( ! $tagged['rejected'] && array( '2 Hours' => 2 ) === $tagged['data']['rbfw_type_info'], 'A name that sanitizes to a configured one is stored under the configured name only' );
	$rows_bad = $build( $plain, $plain_form( array( 1 => array( 'rent_type' => '2 Hours-X', 'qty' => '1' ) ) ) );
	$assert( $rows_bad['rejected'] && 'rbfw_invalid_rental_option' === $rows_bad['code'], 'rbfw_bikecarsd_info[1][rent_type] "2 Hours-X" is refused' );
	$rows_mixed = $build( $plain, $plain_form( array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ), 2 => array( 'rent_type' => '2 Hours-X', 'qty' => '1' ) ) ) );
	$assert( $rows_mixed['rejected'], 'A valid row does not let an unknown row through' );
	$rows_zero = $build( $plain, $plain_form( array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ), 2 => array( 'rent_type' => 'Bogus', 'qty' => '0' ) ) ) );
	$assert( ! $rows_zero['rejected'] && abs( $rows_zero['tp'] - 40 ) < 0.01, 'An unselected row (qty 0) is ignored whatever its name' );
	$assert( $build( $plain, $plain_form( array( 0 => array( 'rent_type' => '2 Hours-X', 'qty' => '1' ) ) ) )['rejected'], 'An unknown row at index 0 is checked too' );
	$assert( $build( $plain, $plain_form( 'not-an-array' ) )['rejected'], 'rbfw_bikecarsd_info posted as a string is refused, not a warning' );

	$ap_ok = $build( $appointment, array( 'rbfw_post_id' => $appointment, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => 'Consultation', 'qty' => '1' ) ) ) );
	$assert( ! $ap_ok['rejected'] && abs( $ap_ok['tp'] - 50 ) < 0.01, 'Appointment: the configured option still books at 50.00' );
	$assert( $build( $appointment, array( 'rbfw_post_id' => $appointment, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_start_time' => '10:00', 'service_type' => 'consultation', 'rbfw_item_quantity' => '1' ) )['rejected'], 'Appointment: service_type "consultation" (wrong case) is refused' );

	/* ── 2. Nothing selected ──────────────────────────────────────────── */
	$no_sel_form = $timely_form();
	unset( $no_sel_form['service_type'] );
	$assert( 'rbfw_no_rental_option' === $build( $timely, $no_sel_form )['code'], 'No service_type and no rows is refused' );
	$assert( 'rbfw_no_rental_option' === $build( $plain, $plain_form( array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '0' ) ) ) )['code'], 'Every row at qty 0 is refused' );

	/* ── 3. End date/time is the server's ─────────────────────────────── */
	$pm = $build( $timely, $timely_form( array( 'rbfw_start_time' => '1:00 PM', 'rbfw_end_time' => '02:00' ) ) );
	$assert( ! $pm['rejected'] && '15:00:00' === $pm['data']['rbfw_end_time'] && '1:00 PM' === $pm['data']['rbfw_start_time'], '12-hour pickup "1:00 PM" + 2 h ends at 15:00, the start keeps its form' );
	$half = $build( $plain, $plain_form( array( 1 => array( 'rent_type' => 'Half Day', 'qty' => '1' ) ) ) );
	$assert( ! $half['rejected'] && abs( $half['tp'] - 25 ) < 0.01 && isset( $half['data']['rbfw_type_info']['Half  Day'] ), 'A configured name with a double space matches its sanitized post and is priced' );
	$assert( $day === $half['data']['rbfw_end_date'] && '14:00:00' === $half['data']['rbfw_end_time'], 'Non-timely duration option: end = start + 4 h, posted end ignored' );
	$morning = $build( $specific, array( 'rbfw_post_id' => $specific, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_start_time' => '03:00', 'service_type' => 'Morning', 'rbfw_item_quantity' => '1', 'rbfw_end_date' => $far_end, 'rbfw_end_time' => '23:59' ) );
	$assert( ! $morning['rejected'] && '09:00' === $morning['data']['rbfw_start_time'] && $day === $morning['data']['rbfw_end_date'] && '12:00' === $morning['data']['rbfw_end_time'], 'Specific duration: start and end come from the option (09:00-12:00), not the post' );
	$night = $build( $specific, array( 'rbfw_post_id' => $specific, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_start_time' => '22:00', 'service_type' => 'Night', 'rbfw_item_quantity' => '1' ) );
	$assert( ! $night['rejected'] && $next_day === $night['data']['rbfw_end_date'] && '02:00' === $night['data']['rbfw_end_time'], 'Specific duration 22:00-02:00 ends the next day' );

	/* ── 4. Malformed date/time ───────────────────────────────────────── */
	foreach ( array( 'abc', '25:00', '13:00 PM', '9:61', '10:00; DROP', '+5 hours' ) as $time ) {
		$assert( 'rbfw_invalid_time' === $build( $timely, $timely_form( array( 'rbfw_start_time' => $time ) ) )['code'], 'Pickup time ' . var_export( $time, true ) . ' is refused' );
	}
	$assert( ! $build( $plain, $plain_form( array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ), array( 'rbfw_start_time' => '' ) ) )['rejected'], 'No pickup time (time picker off) is still accepted' );
	foreach ( array( '2026-02-30', 'tomorrow', $day . ' +40 days', '30-12-2026', '' ) as $date ) {
		$assert( 'rbfw_invalid_date' === $build( $timely, $timely_form( array( 'rbfw_bikecarsd_selected_date' => $date ) ) )['code'], 'Booking date ' . var_export( $date, true ) . ' is refused' );
	}

	/* ── 5. Time-based stock counts the units it priced ───────────────── */
	$rows_form = $timely_form( array( 'rbfw_item_quantity' => '2', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) );
	unset( $rows_form['service_type'] );
	$rows_timely = $build( $timely, $rows_form );
	$assert( ! $rows_timely['rejected'] && 1 === (int) $rows_timely['data']['rbfw_item_quantity'] && abs( $rows_timely['tp'] - 40 ) < 0.01, 'Timely item booked by rows: the stock quantity is the 1 unit priced, not the posted 2' );

	/* ── 6. Free options stay free; a pricing gap is refused ──────────── */
	$trial = $build( $free, array( 'rbfw_post_id' => $free, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => 'Trial', 'qty' => '1' ) ) ) );
	$assert( ! $trial['rejected'] && 0.0 === (float) $trial['tp'], 'An option priced 0 still books at 0.00' );
	$assert( 'rbfw_unpriced_rental' === $build( $gap, array( 'rbfw_post_id' => $gap, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => 'Odd', 'qty' => '1' ) ) ) )['code'], 'A priced option that computes to 0.00 is refused (defence in depth)' );

	$numeric = $make( 'ROV numeric option', array(
		'rbfw_item_type'        => 'bike_car_sd',
		'rbfw_bike_car_sd_data' => array( $option( '10', 15 ) ),
	) );
	$ten = $build( $numeric, array( 'rbfw_post_id' => $numeric, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '10', 'qty' => '2' ) ) ) );
	$assert( ! $ten['rejected'] && abs( $ten['tp'] - 30 ) < 0.01, 'A numeric option name ("10") still matches and prices (2 x 15)' );
	if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) ) {
		$season = static function ( $price ) use ( $day ) {
			return array( array( 'start_date' => $day, 'end_date' => $day, 'type_price' => array( array( 'type' => '2 Hours', 'price' => $price ) ) ) );
		};
		update_post_meta( $plain, 'rbfw_bike_car_sd_data_sp', $season( '55' ) );
		$sp = $build( $plain, $plain_form( array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) );
		$assert( ! $sp['rejected'] && abs( $sp['tp'] - 55 ) < 0.01, 'Seasonal price 55 still applies to a configured option' );
		update_post_meta( $plain, 'rbfw_bike_car_sd_data_sp', $season( '0.00' ) );
		$sp0 = $build( $plain, $plain_form( array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) );
		$assert( ! $sp0['rejected'] && 0.0 === (float) $sp0['tp'], 'A seasonal price of 0.00 is a free season, not a pricing gap' );
		delete_post_meta( $plain, 'rbfw_bike_car_sd_data_sp' );
	} else {
		WP_CLI::warning( 'Seasonal Pricing addon inactive: seasonal checks skipped.' );
	}

	/* ── 7. The full WooCommerce add-to-cart, as in the report ────────── */
	wp_set_current_user( 0 );
	wc_load_cart();
	$product = ( new RBFW_Hidden_Product() )->ensure_hidden_product( $timely );
	$posts[] = $product;
	$errors  = static function () {
		return implode( ' ', wp_list_pluck( wc_get_notices( 'error' ), 'notice' ) );
	};
	$form_post = static function ( $form ) use ( $product ) {
		WC()->cart->empty_cart();
		wc_clear_notices();
		$_POST                = $form;
		$_POST['nonce']       = wp_create_nonce( 'rbfw_ajax_action' );
		$_POST['add-to-cart'] = $product;
		$_REQUEST             = $_POST;
		WC_Form_Handler::add_to_cart_action();
		$_POST    = array();
		$_REQUEST = array();
		return WC()->cart->get_cart();
	};
	$cart = $form_post( $timely_form( array( 'service_type' => '2 Hours-X' ) ) );
	$assert( 0 === count( $cart ) && false !== strpos( $errors(), 'not available for this item' ), 'Add-to-cart with an unknown option: refused with a notice, cart empty' );
	$cart = $form_post( $timely_form() );
	$line = reset( $cart );
	$assert( 1 === count( $cart ) && abs( (float) $line['rbfw_tp'] - 80 ) < 0.01, 'Guest add-to-cart with "2 Hours" x 2: one line at 80.00' );
	$assert( $day === $line['rbfw_end_date'] && '11:00:00' === $line['rbfw_end_time'], 'Cart line ends at start + 2 h despite the posted end 30 days later' );
	WC()->cart->empty_cart();

	/* ── 8. Stock: appointments and variation items ───────────────────── */
	$dmy = gmdate( 'd-m-Y', strtotime( $day ) );
	update_post_meta( $appointment, 'rbfw_inventory', array( 990001 => array( 'booked_dates' => array( $dmy ), 'rbfw_type_info' => array( 'Consultation' => 1 ), 'rbfw_start_time' => '10:00', 'rbfw_order_status' => 'processing' ) ) );
	$line_at = static function ( $id, $type, $time ) use ( $day ) {
		return array( 'rbfw_start_datetime' => $day, 'rbfw_end_datetime' => $day . ' ', 'rbfw_start_date' => $day, 'rbfw_start_time' => $time, 'rbfw_end_date' => $day, 'rbfw_end_time' => '', 'rbfw_type_info' => array( $type => 1 ) );
	};
	$all_ok = static function ( $results ) {
		foreach ( $results as $r ) {
			if ( empty( $r['ok'] ) ) {
				return false;
			}
		}
		return true;
	};
	$booked_session = rbfw_check_rental_availability( $appointment, $line_at( $appointment, 'Consultation', '10:00' ) );
	$assert( ! empty( $booked_session ) && ! $all_ok( $booked_session ), 'Appointment: a fully booked 10:00 session is refused' );
	$assert( $all_ok( rbfw_check_rental_availability( $appointment, $line_at( $appointment, 'Consultation', '11:00' ) ) ), 'Appointment: the free 11:00 session is still bookable' );
	delete_post_meta( $appointment, 'rbfw_inventory' );
	$sibling = array( $line_at( $appointment, 'Consultation', '10:00' ) );
	$assert( ! $all_ok( rbfw_check_rental_availability( $appointment, $line_at( $appointment, 'Consultation', '10:00' ), $sibling ) ), 'Appointment: a second cart line for the same 1-seat session is refused' );
	$assert( $all_ok( rbfw_check_rental_availability( $appointment, $line_at( $appointment, 'Consultation', '11:00' ), $sibling ) ), 'Appointment: a cart line in another session does not count against it' );

	update_post_meta( $variation, 'rbfw_inventory', array( 990002 => array( 'booked_dates' => array( $dmy ), 'rbfw_type_info' => array( '2 Hours' => 1 ), 'rbfw_start_time' => '10:00', 'rbfw_order_status' => 'processing' ) ) );
	$no_size = rbfw_check_rental_availability( $variation, $line_at( $variation, '2 Hours', '10:00' ) );
	$assert( ! empty( $no_size ) && ! $all_ok( $no_size ), 'Variation item, option sold out, no size sent: refused' );
	$with_size                        = $line_at( $variation, '2 Hours', '10:00' );
	$with_size['rbfw_variation_info'] = array( array( 'field_id' => 'rbfw_variation_id_0', 'field_value' => 'M', 'qty' => 1 ) );
	$assert( ! $all_ok( rbfw_check_rental_availability( $variation, $with_size ) ), 'Variation item, option sold out, size M in stock: still refused' );
	delete_post_meta( $variation, 'rbfw_inventory' );
	$assert( $all_ok( rbfw_check_rental_availability( $variation, $with_size ) ), 'Variation item with free stock: accepted' );

	update_post_meta( $plain, 'rbfw_inventory', array( 990003 => array( 'booked_dates' => array( $dmy ), 'rbfw_type_info' => array( '2 Hours' => 1 ), 'rbfw_start_time' => '10:00', 'rbfw_order_status' => 'processing' ) ) );
	$assert( ! $all_ok( rbfw_check_rental_availability( $plain, $line_at( $plain, '2 Hours', '10:00' ) ) ), 'Plain single-day item: sold-out option still refused (unchanged)' );
	delete_post_meta( $plain, 'rbfw_inventory' );

	/* ── 9. Custom Payment (standalone) checkout stores the priced period ── */
	$quote = RBFW_Native_Quote::build( $timely, $rows_form );
	$assert( ! is_wp_error( $quote ), 'Standalone quote builds for a valid request' );
	$has_helpers = method_exists( 'RBFW_Native_Quote', 'booking_dates' );
	$assert( $has_helpers, 'RBFW_Native_Quote exposes booking_dates() / booking_quantity()' );
	if ( $has_helpers && ! is_wp_error( $quote ) ) {
		$dates = RBFW_Native_Quote::booking_dates( $quote['cart_data'] );
		$assert( $day === $dates['start_date'] && $day === $dates['end_date'] && '11:00:00' === $dates['end_time'], 'Standalone booking dates are the quoted 2 hours, not the posted 30 days' );
		$assert( 1 === RBFW_Native_Quote::booking_quantity( $quote['cart_data'], 2 ), 'Standalone booking quantity is the quoted 1 unit, not the posted 2' );
		$assert( 3 === RBFW_Native_Quote::booking_quantity( array(), 3 ), 'Types without a quoted unit count keep the posted one' );
	}
	$assert( is_wp_error( RBFW_Native_Quote::build( $timely, $timely_form( array( 'service_type' => '2 Hours-X' ) ) ) ), 'Standalone quote refuses "2 Hours-X"' );

	// The Custom Payment checkout itself: Standalone mode with the Offline method, the way a
	// shop without WooCommerce checkout runs it. Its class loads only in that mode.
	remove_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );
	$mode_filter = static function () {
		return array( RBFW_Function::MODE_KEY => 'standalone', 'rbfw_offline_enable' => 'on', 'rbfw_require_login' => 'off' );
	};
	add_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );
	$assert( ! RBFW_Function::use_wc() && RBFW_Function::is_booking_available() && ! rbfw_login_required(), 'Standalone mode with Offline payment and guest booking is active for the checkout run' );
	if ( ! class_exists( 'RBFW_Native_Checkout' ) ) {
		require_once RBFW_PLUGIN_DIR . '/inc/booking/RBFW_Native_Checkout.php';
	}
	add_filter( 'wp_doing_ajax', '__return_true' );
	$die = static function () {
		return static function () {
			throw new RuntimeException( 'rbfw-test-die' );
		};
	};
	add_filter( 'wp_die_ajax_handler', $die );
	$checkout = static function ( $form ) {
		$_POST    = array_merge( $form, array(
			'action'             => 'rbfw_native_checkout',
			'nonce'              => wp_create_nonce( 'rbfw_native_checkout_action' ),
			'rbfw_billing_name'  => 'ROV Guest',
			'rbfw_billing_email' => 'rov-guest@example.test',
		) );
		$_REQUEST = $_POST;
		ob_start();
		try {
			( new ReflectionClass( 'RBFW_Native_Checkout' ) )->newInstanceWithoutConstructor()->process();
		} catch ( RuntimeException $e ) {
			unset( $e );
		}
		$out      = json_decode( (string) ob_get_clean(), true );
		$_POST    = array();
		$_REQUEST = array();
		return is_array( $out ) ? $out : array();
	};
	$res = $checkout( $timely_form( array( 'service_type' => '2 Hours-X' ) ) );
	$assert( isset( $res['success'] ) && false === $res['success'] && false !== strpos( (string) ( $res['data']['message'] ?? '' ), 'not available for this item' ), 'Standalone guest checkout with "2 Hours-X" is refused for the unknown option' );
	$res        = $checkout( $timely_form() );
	$booking_id = isset( $res['data']['booking_id'] ) ? (int) $res['data']['booking_id'] : 0;
	if ( $booking_id ) {
		$posts[] = $booking_id;
	}
	$assert( ! empty( $res['success'] ) && $booking_id > 0, 'Standalone checkout with "2 Hours" creates a booking' );
	$assert( $day === get_post_meta( $booking_id, 'rbfw_end_date', true ) && '11:00:00' === get_post_meta( $booking_id, 'rbfw_end_time', true ), 'Standalone booking ends at start + 2 h, not on the posted date 30 days later' );
	$inventory = get_post_meta( $timely, 'rbfw_inventory', true );
	$dates_held = array();
	foreach ( (array) $inventory as $entry ) {
		$dates_held = array_merge( $dates_held, isset( $entry['booked_dates'] ) ? (array) $entry['booked_dates'] : array() );
	}
	$assert( array( $dmy ) === array_values( array_unique( $dates_held ) ), 'Standalone stock hold covers the booked day only' );
} finally {
	remove_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );
	$_POST    = array();
	$_REQUEST = array();
	if ( function_exists( 'WC' ) && WC()->cart ) {
		WC()->cart->empty_cart();
	}
	foreach ( $posts as $id ) {
		wp_delete_post( $id, true );
	}
	$GLOBALS['rbfw'] = $saved_rbfw;
}

if ( $failed ) {
	WP_CLI::error( $failed . ' checks failed, ' . $checks . ' passed.' );
}
WP_CLI::success( $checks . ' checks passed.' );
