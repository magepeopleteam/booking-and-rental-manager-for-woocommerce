<?php
/**
 * Run on a development site with WooCommerce active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/booking-rule-gaps.php
 *
 * Booking rules that the forms apply must hold on the server too:
 *  - item variations: every group describes the same units (never added together), at
 *    least one choice is required, quantities are whole numbers;
 *  - Off Days / off-date ranges / "Appointment days" / the range-blocking toggle;
 *  - Location Inventory counts the units actually booked, other cart lines, and the
 *    Custom Payment (standalone) checkout;
 *  - a Custom Payment booking records the same stock data a WooCommerce order does.
 *
 * RBFW_TEST_SOFT=1 logs every failure and keeps going.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
if ( ! function_exists( 'WC' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$checks = 0;
$failed = 0;
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

$mode        = 'woocommerce';
$mode_filter = static function () use ( &$mode ) {
	return 'woocommerce' === $mode
		? array( RBFW_Function::MODE_KEY => 'woocommerce' )
		: array( RBFW_Function::MODE_KEY => 'standalone', 'rbfw_offline_enable' => 'on', 'rbfw_require_login' => 'off' );
};
add_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );

$posts   = array();
$builder = new RBFW_Woocommerce( false );
$build   = static function ( $id, $payload ) use ( $builder ) {
	$payload['nonce'] = wp_create_nonce( 'rbfw_ajax_action' );
	try {
		$data = $builder->rbfw_add_cart_item_func( array(), $id, $payload );
	} catch ( Throwable $e ) {
		return array( 'rejected' => false, 'code' => 'fatal: ' . $e->getMessage(), 'data' => array(), 'tp' => null );
	}
	if ( ! is_array( $data ) ) {
		return array( 'rejected' => true, 'code' => is_wp_error( $data ) ? $data->get_error_code() : '', 'data' => array(), 'tp' => null );
	}
	return array( 'rejected' => false, 'code' => '', 'data' => $data, 'tp' => isset( $data['rbfw_tp'] ) ? (float) $data['rbfw_tp'] : null );
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
	return array_merge( array( 'rent_type' => $name, 'price' => $price, 'duration' => 2, 'd_type' => 'Hours', 'qty' => 10, 'short_desc' => '' ), $extra );
};
$all_ok = static function ( $results ) {
	foreach ( $results as $r ) {
		if ( empty( $r['ok'] ) ) {
			return false;
		}
	}
	return true;
};
$messages = static function ( $results ) {
	return implode( ' | ', array_filter( wp_list_pluck( $results, 'message' ) ) );
};

// A date 5..11 weeks out that falls on a Wednesday, plus the days around it.
$base = strtotime( '+5 weeks' );
while ( 'wednesday' !== strtolower( gmdate( 'l', $base ) ) ) {
	$base += DAY_IN_SECONDS;
}
$wed = gmdate( 'Y-m-d', $base );
$thu = gmdate( 'Y-m-d', $base + DAY_IN_SECONDS );
$fri = gmdate( 'Y-m-d', $base + 2 * DAY_IN_SECONDS );
$sat = gmdate( 'Y-m-d', $base + 3 * DAY_IN_SECONDS );
$mon = gmdate( 'Y-m-d', $base + 5 * DAY_IN_SECONDS );
$tue = gmdate( 'Y-m-d', $base + 6 * DAY_IN_SECONDS );

$groups = array(
	array( 'field_label' => 'Size', 'field_id' => 'rbfw_variation_id_0', 'selected_value' => '', 'value' => array( array( 'name' => 'S', 'quantity' => 10 ), array( 'name' => 'M', 'quantity' => 10 ) ) ),
	array( 'field_label' => 'Colour', 'field_id' => 'rbfw_variation_id_1', 'selected_value' => '', 'value' => array( array( 'name' => 'Red', 'quantity' => 10 ), array( 'name' => 'Blue', 'quantity' => 10 ) ) ),
);

try {
	/* ── 1. Variations: groups describe the same units ───────────────── */
	$timely_var = $make( 'GAP timely variations', array(
		'rbfw_item_type'                  => 'bike_car_sd',
		'manage_inventory_as_timely'      => 'on',
		'rbfw_item_stock_quantity_timely' => 10,
		'rbfw_enable_time_picker'         => 'yes',
		'rbfw_enable_variations'          => 'yes',
		'rbfw_variations_data'            => $groups,
		'rbfw_bike_car_sd_data'           => array( $option( '2 Hours', 40 ) ),
	) );
	$tv_form = static function ( $variation_qty, $extra = array() ) use ( $timely_var, $wed ) {
		return array_merge( array(
			'rbfw_post_id'                 => $timely_var,
			'rbfw_bikecarsd_selected_date' => $wed,
			'rbfw_start_time'              => '09:00',
			'rbfw_item_quantity'           => '2',
			'service_type'                 => '2 Hours',
			'rbfw_variation_qty'           => $variation_qty,
		), $extra );
	};
	$two = $build( $timely_var, $tv_form( array( 'rbfw_variation_id_0' => array( 'M' => '2' ), 'rbfw_variation_id_1' => array( 'Red' => '2' ) ) ) );
	$assert( ! $two['rejected'] && 2 === (int) $two['data']['rbfw_item_quantity'] && abs( $two['tp'] - 80 ) < 0.01, 'Size M x2 + Colour Red x2 is 2 units (80.00), not 4' );
	$split = $build( $timely_var, $tv_form( array( 'rbfw_variation_id_0' => array( 'S' => '1', 'M' => '1' ), 'rbfw_variation_id_1' => array( 'Red' => '1', 'Blue' => '1' ) ) ) );
	$assert( ! $split['rejected'] && 2 === (int) $split['data']['rbfw_item_quantity'], 'Split choices (S+M, Red+Blue) are still 2 units' );
	$assert( 'rbfw_variation_choice' === $build( $timely_var, $tv_form( array( 'rbfw_variation_id_0' => array( 'M' => '2' ), 'rbfw_variation_id_1' => array( 'Red' => '1' ) ) ) )['code'], 'Groups that disagree (2 sizes, 1 colour) are refused' );
	$assert( 'rbfw_variation_choice' === $build( $timely_var, $tv_form( array( 'rbfw_variation_id_0' => array( 'M' => '2' ) ) ) )['code'], 'A group left empty is refused' );
	$assert( 'rbfw_variation_choice' === $build( $timely_var, $tv_form( array() ) )['code'], 'No variation chosen at all is refused' );
	$assert( 'rbfw_invalid_quantity' === $build( $timely_var, $tv_form( array( 'rbfw_variation_id_0' => array( 'M' => '1.5' ), 'rbfw_variation_id_1' => array( 'Red' => '1.5' ) ) ) )['code'], 'A fractional variation quantity is refused' );
	$assert( 'rbfw_variation_choice' === $build( $timely_var, $tv_form( array( 'rbfw_variation_id_0' => array( 'XXL' => '2' ), 'rbfw_variation_id_1' => array( 'Red' => '2' ) ) ) )['code'], 'A size the item does not have counts for nothing' );
	$assert( 2 === rbfw_variation_units( $timely_var, array( 'rbfw_variation_id_0' => array( 'M' => '2' ), 'rbfw_variation_id_1' => array( 'Red' => '2' ) ) ), 'rbfw_variation_units() counts one group, not the sum of groups' );

	$rows_var = $make( 'GAP rows variations', array(
		'rbfw_item_type'         => 'bike_car_sd',
		'rbfw_enable_variations' => 'yes',
		'rbfw_variations_data'   => array( $groups[0] ),
		'rbfw_bike_car_sd_data'  => array( $option( '2 Hours', 40 ) ),
	) );
	$rv_form = static function ( $rows_qty, $size_qty ) use ( $rows_var, $wed ) {
		return array( 'rbfw_post_id' => $rows_var, 'rbfw_bikecarsd_selected_date' => $wed, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => (string) $rows_qty ) ), 'rbfw_variation_qty' => array( 'rbfw_variation_id_0' => array( 'M' => (string) $size_qty ) ) );
	};
	$rv = $build( $rows_var, $rv_form( 2, 2 ) );
	$assert( ! $rv['rejected'] && 2 === (int) $rv['data']['rbfw_item_quantity'] && abs( $rv['tp'] - 80 ) < 0.01, 'Rows: 2 units with 2 sizes chosen is accepted (80.00, quantity 2)' );
	$assert( 'rbfw_variation_choice' === $build( $rows_var, $rv_form( 2, 1 ) )['code'], 'Rows: 2 units but only 1 size chosen is refused' );

	$placeholder = $make( 'GAP placeholder variations', array(
		'rbfw_item_type'         => 'bike_car_sd',
		'rbfw_enable_variations' => 'yes',
		'rbfw_variations_data'   => array( array( 'field_label' => '', 'field_id' => 'rbfw_variation_id_0', 'value' => array( array( 'name' => '', 'quantity' => '' ) ), 'selected_value' => '' ) ),
		'rbfw_bike_car_sd_data'  => array( $option( '2 Hours', 40 ) ),
	) );
	$ph = $build( $placeholder, array( 'rbfw_post_id' => $placeholder, 'rbfw_bikecarsd_selected_date' => $wed, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) );
	$assert( ! $ph['rejected'], 'Variations switched on with only an empty placeholder row: nothing to choose, booking accepted' );

	$md_var = $make( 'GAP multi-day variations', array(
		'rbfw_item_type'               => 'bike_car_md',
		'rbfw_enable_daily_rate'       => 'yes',
		'rbfw_daily_rate'              => 25,
		'rbfw_enable_hourly_rate'      => 'no',
		'rbfw_item_stock_quantity'     => 50,
		'rbfw_enable_variations'       => 'yes',
		'rbfw_variation_multiply_base' => 'yes',
		'rbfw_variations_data'         => $groups,
	) );
	$md_form = static function ( $variation_qty ) use ( $md_var, $wed, $fri ) {
		return array( 'rbfw_post_id' => $md_var, 'rbfw_pickup_start_date' => $wed, 'rbfw_pickup_end_date' => $fri, 'rbfw_pickup_start_time' => '10:00', 'rbfw_pickup_end_time' => '10:00', 'rbfw_enable_time_slot' => 'off', 'rbfw_item_quantity' => '1', 'rbfw_variation_qty' => $variation_qty );
	};
	$md2 = $build( $md_var, $md_form( array( 'rbfw_variation_id_0' => array( 'S' => '2' ), 'rbfw_variation_id_1' => array( 'Blue' => '2' ) ) ) );
	$assert( ! $md2['rejected'] && 2 === (int) $md2['data']['rbfw_item_quantity'], 'Multi-day, base per unit: Size x2 + Colour x2 bills 2 units, not 4' );
	$assert( 'rbfw_variation_choice' === $build( $md_var, $md_form( array( 'rbfw_variation_id_0' => array( 'S' => '2' ), 'rbfw_variation_id_1' => array( 'Blue' => '1' ) ) ) )['code'], 'Multi-day: groups that disagree are refused' );

	/* ── 2. Off Days, off-date ranges, Appointment days ──────────────── */
	$offday = $make( 'GAP off days', array(
		'rbfw_item_type'        => 'bike_car_sd',
		'rbfw_off_days'         => 'thursday',
		'rbfw_offday_range'     => array( array( 'from_date' => $mon, 'to_date' => $mon ) ),
		'rbfw_bike_car_sd_data' => array( $option( '2 Hours', 40 ) ),
	) );
	$sd_line = static function ( $id, $day ) use ( $build ) {
		return $build( $id, array( 'rbfw_post_id' => $id, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) )['data'];
	};
	$assert( $all_ok( rbfw_check_rental_availability( $offday, $sd_line( $offday, $wed ) ) ), 'Single Day on an open Wednesday: bookable' );
	$thu_check = rbfw_check_rental_availability( $offday, $sd_line( $offday, $thu ) );
	$assert( ! $all_ok( $thu_check ) && false !== strpos( $messages( $thu_check ), 'not available' ), 'Single Day on the weekly off day (Thursday): refused with a reason' );
	$assert( ! $all_ok( rbfw_check_rental_availability( $offday, $sd_line( $offday, $mon ) ) ), 'Single Day inside an off-date range: refused' );
	update_post_meta( $offday, 'rbfw_enable_start_end_date', 'no' );
	$assert( $all_ok( rbfw_check_rental_availability( $offday, $sd_line( $offday, $thu ) ) ), 'Fixed rental period (start/end date off): admin dates are not second-guessed' );
	delete_post_meta( $offday, 'rbfw_enable_start_end_date' );

	$appointment = $make( 'GAP appointment days', array(
		'rbfw_item_type'             => 'appointment',
		'rbfw_sd_appointment_ondays' => array( 'Monday', 'Wednesday' ),
		'rbfw_bike_car_sd_data'      => array( $option( 'Consultation', 50, array( 'duration' => 1 ) ) ),
	) );
	$ap_line = static function ( $day ) use ( $build, $appointment ) {
		return $build( $appointment, array( 'rbfw_post_id' => $appointment, 'rbfw_bikecarsd_selected_date' => $day, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => 'Consultation', 'qty' => '1' ) ) ) )['data'];
	};
	$assert( $all_ok( rbfw_check_rental_availability( $appointment, $ap_line( $wed ) ) ), 'Appointment on one of its Appointment days (Wednesday): bookable' );
	$ap_tue = rbfw_check_rental_availability( $appointment, $ap_line( $tue ) );
	$assert( ! $all_ok( $ap_tue ) && false !== strpos( $messages( $ap_tue ), 'Appointments are not available' ), 'Appointment on a Tuesday (not an Appointment day): refused' );
	update_post_meta( $appointment, 'rbfw_sd_appointment_ondays', array() );
	$assert( $all_ok( rbfw_check_rental_availability( $appointment, $ap_line( $tue ) ) ), 'Appointment with no Appointment days set: every day open, as before' );

	$md_off = $make( 'GAP multi-day off days', array(
		'rbfw_item_type'           => 'bike_car_md',
		'rbfw_enable_daily_rate'   => 'yes',
		'rbfw_daily_rate'          => 25,
		'rbfw_enable_hourly_rate'  => 'no',
		'rbfw_item_stock_quantity' => 10,
		'rbfw_off_days'            => 'thursday',
	) );
	$md_line = static function ( $from, $to ) use ( $build, $md_off ) {
		return $build( $md_off, array( 'rbfw_post_id' => $md_off, 'rbfw_pickup_start_date' => $from, 'rbfw_pickup_end_date' => $to, 'rbfw_pickup_start_time' => '10:00', 'rbfw_pickup_end_time' => '10:00', 'rbfw_enable_time_slot' => 'off', 'rbfw_item_quantity' => '1' ) )['data'];
	};
	$assert( ! $all_ok( rbfw_check_rental_availability( $md_off, $md_line( $wed, $thu ) ) ), 'Multi-day returning on the off day: refused' );
	$assert( $all_ok( rbfw_check_rental_availability( $md_off, $md_line( $wed, $fri ) ) ), 'Multi-day spanning an off day, range blocking off: allowed (as the calendar)' );
	update_post_meta( $md_off, 'rbfw_block_offday_range_booking', 'on' );
	$assert( ! $all_ok( rbfw_check_rental_availability( $md_off, $md_line( $wed, $fri ) ) ), 'Multi-day spanning an off day, range blocking on: refused' );
	$assert( $all_ok( rbfw_check_rental_availability( $md_off, $md_line( $fri, $sat ) ) ), 'Multi-day clear of off days, range blocking on: allowed' );

	/* ── 3. The real WooCommerce add-to-cart on an off day ───────────── */
	wp_set_current_user( 0 );
	wc_load_cart();
	$errors    = static function () {
		return implode( ' ', wp_list_pluck( wc_get_notices( 'error' ), 'notice' ) );
	};
	$form_post = static function ( $id, $form, $keep_cart = false ) {
		if ( ! $keep_cart ) {
			WC()->cart->empty_cart();
		}
		wc_clear_notices();
		$product              = ( new RBFW_Hidden_Product() )->ensure_hidden_product( $id );
		$_POST                = $form;
		$_POST['nonce']       = wp_create_nonce( 'rbfw_ajax_action' );
		$_POST['add-to-cart'] = $product;
		$_REQUEST             = $_POST;
		WC_Form_Handler::add_to_cart_action();
		$_POST    = array();
		$_REQUEST = array();
		return array( $product, count( WC()->cart->get_cart() ) );
	};
	list( $p1, $n ) = $form_post( $offday, array( 'rbfw_post_id' => $offday, 'rbfw_bikecarsd_selected_date' => $thu, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) );
	$posts[] = $p1;
	$assert( 0 === $n && false !== strpos( $errors(), 'not available' ), 'Add-to-cart on the weekly off day: refused with a notice' );
	list( , $n ) = $form_post( $offday, array( 'rbfw_post_id' => $offday, 'rbfw_bikecarsd_selected_date' => $wed, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) );
	$assert( 1 === $n, 'Add-to-cart on an open day: accepted' );

	/* ── 4. Location Inventory ───────────────────────────────────────── */
	$located = $make( 'GAP location stock', array(
		'rbfw_item_type'                 => 'bike_car_sd',
		'rbfw_enable_location_inventory' => 'on',
		'rbfw_location_inventory'        => array( 'north' => array( 'stock' => 2, 'price' => 0 ) ),
		'rbfw_bike_car_sd_data'          => array( $option( '2 Hours', 40 ) ),
	) );
	$loc_form = static function ( $rows_qty, $extra = array() ) use ( $located, $wed ) {
		return array_merge( array( 'rbfw_post_id' => $located, 'rbfw_bikecarsd_selected_date' => $wed, 'rbfw_start_time' => '10:00', 'rbfw_pickup_point' => 'north', 'rbfw_item_quantity' => '1', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => (string) $rows_qty ) ) ), $extra );
	};
	$built3 = $build( $located, $loc_form( 3 ) );
	$assert( ! $built3['rejected'] && 3 === (int) $built3['data']['rbfw_item_quantity'], 'Rows booking: the stored unit count is the 3 units priced, not the posted 1' );
	list( $p2, $n ) = $form_post( $located, $loc_form( 3 ) );
	$posts[] = $p2;
	$assert( 0 === $n && false !== strpos( $errors(), 'unit(s) are available' ), 'Location with 2 left: 3 units booked by rows (posted quantity 1) are refused' );
	list( , $n ) = $form_post( $located, $loc_form( 2 ) );
	$assert( 1 === $n, 'Location with 2 left: 2 units are accepted' );
	// "Allow the same rental in the cart more than once": read-only for this run.
	$dup_filter = static function ( $settings ) {
		return array_merge( is_array( $settings ) ? $settings : array(), array( 'rbfw_allow_duplicate_rental_cart_item' => 'yes' ) );
	};
	add_filter( 'option_rbfw_basic_gen_settings', $dup_filter );
	add_filter( 'default_option_rbfw_basic_gen_settings', $dup_filter );
	$assert( rbfw_allow_duplicate_rental_cart_items(), 'Duplicate rental cart lines allowed for this part' );
	list( , $n ) = $form_post( $located, $loc_form( 1 ), true );
	$assert( 1 === $n && false !== strpos( $errors(), 'unit(s) are available' ), 'A second cart line at the same location and day is counted: refused' );
	$assert( function_exists( 'rbfw_location_stock_error' ), 'Shared location gate rbfw_location_stock_error() exists' );
	if ( function_exists( 'rbfw_location_stock_error' ) ) {
		$assert( '' === rbfw_location_stock_error( $located, 'north', $wed, $wed, 2 ), 'Shared gate: 2 of 2 left is fine' );
		$assert( '' !== rbfw_location_stock_error( $located, 'south', $wed, $wed, 1 ), 'Shared gate: a location the item does not have is refused' );
		$assert( '' !== rbfw_location_stock_error( $located, 'north', $wed, $wed, 1, array( array( 'rbfw_id' => $located, 'rbfw_pickup_point' => 'north', 'rbfw_start_date' => $wed, 'rbfw_end_date' => $wed, 'rbfw_item_quantity' => 2 ) ) ), 'Shared gate: other lines for the same day use up the stock' );
		$assert( '' === rbfw_location_stock_error( $located, 'north', $wed, $wed, 2, array( array( 'rbfw_id' => $located, 'rbfw_pickup_point' => 'north', 'rbfw_start_date' => $thu, 'rbfw_end_date' => $thu, 'rbfw_item_quantity' => 2 ) ) ), 'Shared gate: lines on another day do not count' );
	}
	WC()->cart->empty_cart();
	remove_filter( 'option_rbfw_basic_gen_settings', $dup_filter );
	remove_filter( 'default_option_rbfw_basic_gen_settings', $dup_filter );

	/* ── 5. Custom Payment (standalone) checkout ─────────────────────── */
	$mode = 'standalone';
	$assert( ! RBFW_Function::use_wc() && RBFW_Function::is_booking_available() && ! rbfw_login_required(), 'Standalone mode with Offline payment and guest booking for this part' );
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
	$checkout = static function ( $form ) use ( &$posts ) {
		$_POST    = array_merge( $form, array(
			'action'             => 'rbfw_native_checkout',
			'nonce'              => wp_create_nonce( 'rbfw_native_checkout_action' ),
			'rbfw_billing_name'  => 'GAP Guest',
			'rbfw_billing_email' => 'gap-guest@example.test',
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
		$out      = is_array( $out ) ? $out : array();
		if ( ! empty( $out['data']['booking_id'] ) ) {
			$posts[] = (int) $out['data']['booking_id'];
		}
		return $out;
	};
	$res = $checkout( $loc_form( 3 ) );
	$assert( isset( $res['success'] ) && false === $res['success'] && false !== strpos( (string) ( $res['data']['message'] ?? '' ), 'unit(s) are available' ), 'Standalone: 3 units at a location with 2 left are refused' );
	$res = $checkout( $loc_form( 2 ) );
	$assert( ! empty( $res['success'] ), 'Standalone: 2 units at that location are booked' );
	$entries = (array) get_post_meta( $located, 'rbfw_inventory', true );
	$entry   = end( $entries );
	$assert( is_array( $entry ) && 'north' === ( $entry['rbfw_pickup_point'] ?? '' ) && array( '2 Hours' => 2 ) === ( $entry['rbfw_type_info'] ?? null ) && 2 === (int) ( $entry['rbfw_item_quantity'] ?? 0 ), 'Standalone booking records the location, the option and the 2 units in inventory' );
	$assert( 2 === rbfw_location_sold_qty( $located, 'north', $wed, $wed ), 'Location stock now counts the standalone booking' );
	$res = $checkout( $loc_form( 1 ) );
	$assert( isset( $res['success'] ) && false === $res['success'], 'Standalone: the location is now full, a further unit is refused' );

	$one_left = $make( 'GAP standalone option stock', array(
		'rbfw_item_type'        => 'bike_car_sd',
		'rbfw_bike_car_sd_data' => array( $option( '2 Hours', 40, array( 'qty' => 1 ) ) ),
	) );
	$ol_form = array( 'rbfw_post_id' => $one_left, 'rbfw_bikecarsd_selected_date' => $wed, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) );
	$assert( ! empty( $checkout( $ol_form )['success'] ), 'Standalone: the only unit of an option is booked' );
	$second = $checkout( $ol_form );
	$assert( isset( $second['success'] ) && false === $second['success'], 'Standalone: the option is now sold out for a second booking' );
	$res = $checkout( array( 'rbfw_post_id' => $offday, 'rbfw_bikecarsd_selected_date' => $thu, 'rbfw_start_time' => '10:00', 'rbfw_bikecarsd_info' => array( 1 => array( 'rent_type' => '2 Hours', 'qty' => '1' ) ) ) );
	$assert( isset( $res['success'] ) && false === $res['success'], 'Standalone: a booking on the off day is refused' );
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
