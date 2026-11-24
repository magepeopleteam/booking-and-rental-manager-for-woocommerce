<?php
/**
 * Test suite for:
 * 1. Time-slot Stored XSS Prevention (CVE/Patchstack 40918)
 * 2. Inverted Date Access Control & Stock Overlap Normalization (CVE/Patchstack 40883)
 *
 * Run with:
 *   wp --path=/var/www/html/magepeople eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/timeslot-xss-and-reversed-dates.php
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	++$checks;
	WP_CLI::log( 'PASS: ' . $message );
};

$posts = array();
$users = array();

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

$make_user = static function ( $role, $email ) use ( &$users ) {
	$id = wp_insert_user( array(
		'user_login' => 'rbfw_test_' . $role . '_' . wp_generate_password( 6, false ),
		'user_pass'  => wp_generate_password(),
		'user_email' => $email,
		'role'       => $role,
	) );
	$users[] = $id;
	return $id;
};

if ( ! class_exists( 'RBFW_Booking_Reconfirm' ) && defined( 'RBFW_PLUGIN_DIR' ) ) {
	require_once RBFW_PLUGIN_DIR . '/inc/booking/RBFW_Booking_Reconfirm.php';
}

// Capture wp_send_json_* (which calls wp_die) as a thrown result instead of exiting
add_filter( 'wp_doing_ajax', '__return_true' );
add_filter( 'pre_wp_mail', '__return_true' );
add_filter( 'wp_die_ajax_handler', static function () {
	return static function () {
		throw new Exception( 'wp_die' );
	};
} );

try {
	// =========================================================================
	// PART 1: Time Slot Stored XSS & Validation
	// =========================================================================
	WP_CLI::log( '--- Testing Time Slot XSS Sanitization & Strict Time Matching ---' );

	// 1. Strict time slot format validator
	$assert( RBFW_Function::is_valid_time_slot_string( '09:00 AM' ), 'Accepts 09:00 AM' );
	$assert( RBFW_Function::is_valid_time_slot_string( '9:00 AM' ), 'Accepts 9:00 AM' );
	$assert( RBFW_Function::is_valid_time_slot_string( '12:00 PM' ), 'Accepts 12:00 PM' );
	$assert( RBFW_Function::is_valid_time_slot_string( '08:30 pm' ), 'Accepts lowercase am/pm' );
	$assert( RBFW_Function::is_valid_time_slot_string( '14:30' ), 'Accepts 24-hour time 14:30' );
	$assert( RBFW_Function::is_valid_time_slot_string( '9:15' ), 'Accepts 9:15' );
	$assert( RBFW_Function::is_valid_time_slot_string( '09:00:00' ), 'Accepts H:i:s time 09:00:00' );
	$assert( RBFW_Function::is_valid_time_slot_string( '23:59:59' ), 'Accepts 23:59:59' );

	// XSS and invalid format injections
	$assert( ! RBFW_Function::is_valid_time_slot_string( 'x"><img src=x onerror=alert(document.domain)>' ), 'Rejects HTML image onerror tag injection' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( '&lt;img src=x onerror=alert(1)&gt;' ), 'Rejects entity-encoded HTML tag' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( '<script>alert(1)</script>' ), 'Rejects script tag' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( '09:00<script>' ), 'Rejects trailing tag injection' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( '25:00' ), 'Rejects invalid hour 25:00' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( '12:60' ), 'Rejects invalid minute 12:60' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( '09:00 XM' ), 'Rejects invalid AM/PM designator XM' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( '09:00 AM" autofocus onfocus="alert(1)' ), 'Rejects attribute breakout injection' );
	$assert( ! RBFW_Function::is_valid_time_slot_string( "' or '1'='1" ), 'Rejects SQL/prose injection' );

	// 2. sanitize_time_slots()
	$raw_slots = array(
		array( 'time' => '09:00 AM', 'status' => 'enabled' ),
		array( 'time' => 'x"><img src=x onerror=alert(1)>', 'status' => 'enabled' ),
		array( 'time' => '11:00 AM', 'status' => 'disabled' ),
		array( 'time' => '&lt;script&gt;alert(1)&lt;/script&gt;', 'status' => 'bad_status' ),
	);
	$clean_slots = RBFW_Function::sanitize_time_slots( $raw_slots );
	$assert( count( $clean_slots ) === 2, 'sanitize_time_slots filters out invalid/malicious slots' );
	$assert( $clean_slots[0]['time'] === '09:00 AM' && $clean_slots[0]['status'] === 'enabled', 'Keeps valid first slot' );
	$assert( $clean_slots[1]['time'] === '11:00 AM' && $clean_slots[1]['status'] === 'disabled', 'Keeps valid second slot' );

	// Flat slot times array
	$flat_raw = array( '09:00 AM', 'x"><img src=x onerror=alert(1)>', '10:30' );
	$flat_clean = RBFW_Function::sanitize_time_slots( $flat_raw );
	$assert( count( $flat_clean ) === 2, 'Flat slot times array sanitized properly' );
	$assert( $flat_clean[0] === '09:00 AM' && $flat_clean[1] === '10:30', 'Flat valid slots retained' );

	// 3. sanitize_particulars_data()
	$raw_particulars = array(
		array(
			'start_date'     => '2026-12-01',
			'end_date'       => '2026-12-05',
			'available_time' => array(
				array( 'time' => '10:00 AM', 'status' => 'enabled' ),
				array( 'time' => '"><script>alert(1)</script>', 'status' => 'enabled' ),
			),
		),
		array(
			'start_date'     => '<script>alert(1)</script>',
			'end_date'       => '2026-12-05',
			'available_time' => array(
				array( 'time' => '10:00 AM', 'status' => 'enabled' ),
			),
		),
	);
	$clean_particulars = RBFW_Function::sanitize_particulars_data( $raw_particulars );
	$assert( count( $clean_particulars ) === 1, 'Particular rows with invalid dates are discarded' );
	$assert( count( $clean_particulars[0]['available_time'] ) === 1, 'Malicious slot times in particulars are discarded' );
	$assert( $clean_particulars[0]['available_time'][0]['time'] === '10:00 AM', 'Valid slot time in particular row retained' );

	// =========================================================================
	// PART 2: Inverted Date Access Control & Overlap Normalization
	// =========================================================================
	WP_CLI::log( '--- Testing Inverted Date Rejection & Inventory Math Normalization ---' );

	// Create test rental item
	$item_id = wp_insert_post( array(
		'post_type'   => 'rbfw_item',
		'post_status' => 'publish',
		'post_title'  => 'Test Security Rental Item',
	) );
	$posts[] = $item_id;
	update_post_meta( $item_id, 'rbfw_item_type', 'bike_car_md' );
	update_post_meta( $item_id, 'rbfw_item_stock_quantity', '1' );

	// 4. Inverted date check in Add to Cart (RBFW_Woocommerce)
	$wc_calc = new RBFW_Woocommerce( false );

	$inv_payload = array(
		'rbfw_id'                => $item_id,
		'rbfw_item_quantity'     => 1,
		'rbfw_pickup_start_date' => '2026-12-10',
		'rbfw_pickup_end_date'   => '2026-12-05',
		'rbfw_pickup_start_time' => '10:00',
		'rbfw_pickup_end_time'   => '10:00',
		'nonce'                  => wp_create_nonce( 'rbfw_ajax_action' ),
	);
	$res_inv = $wc_calc->rbfw_add_cart_item_func( array(), $item_id, $inv_payload );
	$assert( is_wp_error( $res_inv ) && $res_inv->get_error_code() === 'rbfw_invalid_dates', 'Add to cart rejects inverted dates with rbfw_invalid_dates error' );

	// Normal dates succeed
	$norm_payload = array(
		'rbfw_id'                => $item_id,
		'rbfw_item_quantity'     => 1,
		'rbfw_pickup_start_date' => '2026-12-05',
		'rbfw_pickup_end_date'   => '2026-12-10',
		'rbfw_pickup_start_time' => '10:00',
		'rbfw_pickup_end_time'   => '10:00',
		'nonce'                  => wp_create_nonce( 'rbfw_ajax_action' ),
	);
	$res_norm = $wc_calc->rbfw_add_cart_item_func( array(), $item_id, $norm_payload );
	$assert( ! is_wp_error( $res_norm ) && is_array( $res_norm ), 'Add to cart accepts valid chronological dates' );

	// 5. Inverted date check in Resort items
	$resort_item = wp_insert_post( array(
		'post_type'   => 'rbfw_item',
		'post_status' => 'publish',
		'post_title'  => 'Test Resort Item',
	) );
	$posts[] = $resort_item;
	update_post_meta( $resort_item, 'rbfw_item_type', 'resort' );
	update_post_meta( $resort_item, 'rbfw_resort_room_data', array(
		array( 'room_type' => 'Deluxe', 'rbfw_room_available_qty' => 5, 'room_price' => 100 ),
	) );

	$resort_inv_payload = array(
		'rbfw_id'             => $resort_item,
		'rbfw_start_datetime' => '2026-12-10 14:00',
		'rbfw_end_datetime'   => '2026-12-05 10:00',
		'rbfw_room_info'      => array(
			array( 'room_type' => 'Deluxe', 'room_qty' => 1, 'room_price' => 100 ),
		),
		'nonce'               => wp_create_nonce( 'rbfw_ajax_action' ),
	);
	$res_resort_inv = $wc_calc->rbfw_add_cart_item_func( array(), $resort_item, $resort_inv_payload );
	$assert( is_wp_error( $res_resort_inv ) && $res_resort_inv->get_error_code() === 'rbfw_invalid_dates', 'Resort add-to-cart rejects inverted checkin/checkout dates' );

	// 6. RBFW_Native_Quote date ordering validation
	$invalid_cart_data = array(
		'rbfw_start_date'     => '2026-12-10',
		'rbfw_end_date'       => '2026-12-05',
		'rbfw_start_datetime' => '2026-12-10 10:00',
		'rbfw_end_datetime'   => '2026-12-05 10:00',
		'rbfw_item_quantity'  => 1,
	);
	$assert( ! RBFW_Native_Quote::has_valid_selection( 'bike_car_md', $invalid_cart_data ), 'RBFW_Native_Quote::has_valid_selection rejects reversed start/end dates' );

	$valid_cart_data = array(
		'rbfw_start_date'     => '2026-12-05',
		'rbfw_end_date'       => '2026-12-10',
		'rbfw_start_datetime' => '2026-12-05 10:00',
		'rbfw_end_datetime'   => '2026-12-10 10:00',
		'rbfw_item_quantity'  => 1,
	);
	$assert( RBFW_Native_Quote::has_valid_selection( 'bike_car_md', $valid_cart_data ), 'RBFW_Native_Quote::has_valid_selection accepts valid chronological dates' );

	$quote_res = RBFW_Native_Quote::build( $item_id, $inv_payload );
	$assert( is_wp_error( $quote_res ), 'RBFW_Native_Quote::build rejects inverted dates' );

	// 7. Inventory overlap counting symmetry on inverted dates
	// Set up an inventory entry on $item_id for 2026-12-05 to 2026-12-10
	$inv = array(
		99991 => array(
			'rbfw_order_status'   => 'completed',
			'rbfw_item_quantity'  => 1,
			'rbfw_start_date_ymd' => '2026-12-05',
			'rbfw_end_date_ymd'   => '2026-12-10',
			'rbfw_start_time_24'  => '10:00',
			'rbfw_end_time_24'    => '10:00',
		),
	);
	update_post_meta( $item_id, 'rbfw_inventory', $inv );

	$cnt_normal = rbfw_count_overlapping_booked_qty( $item_id, '2026-12-06 00:00', '2026-12-08 00:00' );
	$cnt_inverted = rbfw_count_overlapping_booked_qty( $item_id, '2026-12-08 00:00', '2026-12-06 00:00' );
	$assert( $cnt_normal === 1, 'Overlapping booked qty counts 1 unit for normal range' );
	$assert( $cnt_inverted === 1, 'Overlapping booked qty counts 1 unit for inverted range (normalized, does not bypass to 0)' );

	$cart_lines = array(
		array(
			'rbfw_start_datetime' => '2026-12-05 10:00',
			'rbfw_end_datetime'   => '2026-12-10 10:00',
			'rbfw_item_quantity'  => 1,
		),
	);
	$cart_cnt_norm = rbfw_count_overlapping_cart_qty( $cart_lines, '2026-12-06 00:00', '2026-12-08 00:00' );
	$cart_cnt_inv  = rbfw_count_overlapping_cart_qty( $cart_lines, '2026-12-08 00:00', '2026-12-06 00:00' );
	$assert( $cart_cnt_norm === 1, 'Overlapping cart qty counts 1 unit for normal range' );
	$assert( $cart_cnt_inv === 1, 'Overlapping cart qty counts 1 unit for inverted range' );

	// 8. rbfw_check_rental_availability() rejects inverted dates
	$avail_check_inv = rbfw_check_rental_availability( $item_id, array(
		'rbfw_start_datetime' => '2026-12-10 10:00',
		'rbfw_end_datetime'   => '2026-12-05 10:00',
		'rbfw_item_quantity'  => 1,
	) );
	$assert( ! empty( $avail_check_inv ) && isset( $avail_check_inv[0]['ok'] ) && ! $avail_check_inv[0]['ok'], 'rbfw_check_rental_availability returns ok=false for inverted dates' );

	// =========================================================================
	// PART 3: Stock Re-check on Status Transition & Customer Reconfirmation
	// =========================================================================
	WP_CLI::log( '--- Testing Stock Re-check on Status Update & Customer OTP Reconfirm ---' );

	// Stock is 1 for $item_id. Inventory entry 99991 holds the 1 unit for 2026-12-05 to 2026-12-10.
	// Create Booking 2 for the same dates, starting in 'cancelled' status.
	$booking2 = wp_insert_post( array(
		'post_type'   => RBFW_Booking_Post_Type::POST_TYPE,
		'post_status' => 'publish',
		'post_title'  => 'Booking 2 - Competing',
	) );
	$posts[] = $booking2;
	update_post_meta( $booking2, 'rbfw_item_id', $item_id );
	update_post_meta( $booking2, 'rbfw_item_type', 'bike_car_md' );
	update_post_meta( $booking2, 'rbfw_start_date', '2026-12-05' );
	update_post_meta( $booking2, 'rbfw_end_date', '2026-12-10' );
	update_post_meta( $booking2, 'rbfw_start_time', '10:00' );
	update_post_meta( $booking2, 'rbfw_end_time', '10:00' );
	update_post_meta( $booking2, 'rbfw_quantity', 1 );
	update_post_meta( $booking2, 'rbfw_status', 'cancelled' );

	// Check availability for Booking 2: stock is 1, order 99991 takes 1 -> available is 0!
	$avail_res = RBFW_Booking_Normalizer::check_booking_availability( $booking2 );
	$assert( is_wp_error( $avail_res ) && $avail_res->get_error_code() === 'rbfw_out_of_stock', 'check_booking_availability detects stock exhaustion for competing booking' );

	// Admin transition: attempt to move Booking 2 from 'cancelled' to 'confirmed'
	$update_res = RBFW_Booking_Normalizer::update_status( $booking2, 'confirmed' );
	$assert( is_wp_error( $update_res ) && $update_res->get_error_code() === 'rbfw_out_of_stock', 'update_status refuses transition to confirmed when stock is exhausted' );
	$assert( get_post_meta( $booking2, 'rbfw_status', true ) === 'cancelled', 'Booking 2 status remains cancelled' );

	// Customer OTP Reconfirm: setup OTP for booking 2 (still pending/cancelled)
	update_post_meta( $booking2, 'rbfw_status', 'pending' );
	$test_email = 'customer.' . wp_generate_password( 5, false ) . '@example.com';
	$customer_id = $make_user( 'customer', $test_email );
	update_post_meta( $booking2, 'rbfw_customer_email', $test_email );
	update_post_meta( $booking2, 'rbfw_user_id', $customer_id );

	$raw_otp = '123456';
	update_post_meta( $booking2, 'rbfw_reconfirm_otp_hash', wp_hash_password( $raw_otp ) );
	update_post_meta( $booking2, 'rbfw_reconfirm_otp_expires', time() + 600 );
	update_post_meta( $booking2, 'rbfw_reconfirm_otp_attempts', 0 );

	// Customer attempts to verify OTP while inventory is exhausted by order 99991
	wp_set_current_user( $customer_id );
	$_POST = array(
		'booking_id' => $booking2,
		'otp'        => $raw_otp,
		'nonce'      => wp_create_nonce( RBFW_Booking_Reconfirm::nonce_action( $booking2 ) ),
	);
	$_REQUEST = $_POST;

	$reconfirm_handler = new RBFW_Booking_Reconfirm();
	ob_start();
	try {
		$reconfirm_handler->ajax_verify_otp();
	} catch ( Exception $e ) {
		// wp_die from wp_send_json_error
	}
	$otp_output = ob_get_clean();
	$_POST = $_REQUEST = array();
	wp_set_current_user( 0 );

	$otp_res = json_decode( $otp_output, true );
	$assert( isset( $otp_res['success'] ) && false === $otp_res['success'], 'Customer OTP reconfirmation is rejected when inventory is exhausted' );
	$assert( 'pending' === get_post_meta( $booking2, 'rbfw_status', true ), 'Booking 2 is not confirmed on exhausted inventory' );

	// Now free up stock by removing the blocking order 99991
	unset( $inv[99991] );
	update_post_meta( $item_id, 'rbfw_inventory', $inv );

	// With stock available, check_booking_availability succeeds
	$avail_free = RBFW_Booking_Normalizer::check_booking_availability( $booking2 );
	$assert( true === $avail_free, 'check_booking_availability succeeds when inventory is available' );

	// Admin transition to confirmed now succeeds
	$update_success = RBFW_Booking_Normalizer::update_status( $booking2, 'confirmed' );
	$assert( true === $update_success, 'update_status succeeds when stock is available' );
	$assert( get_post_meta( $booking2, 'rbfw_status', true ) === 'confirmed', 'Booking 2 status is now confirmed' );

	// Verify inventory entry was created/restored for booking 2
	$item_inv = get_post_meta( $item_id, 'rbfw_inventory', true );
	$assert( isset( $item_inv[ $booking2 ] ) && $item_inv[ $booking2 ]['rbfw_order_status'] === 'confirmed', 'Inventory entry properly restored and set to confirmed for Booking 2' );

	WP_CLI::success( "timeslot-xss-and-reversed-dates: all {$checks} checks passed." );
} finally {
	foreach ( $posts as $p ) {
		wp_delete_post( $p, true );
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	foreach ( $users as $u ) {
		wp_delete_user( $u );
	}
	$GLOBALS['rbfw'] = $saved_rbfw;
}
