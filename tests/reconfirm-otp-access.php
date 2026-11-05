<?php
/**
 * Run on a development site:
 *   wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/reconfirm-otp-access.php
 *
 * Guards the two self-service reconfirmation AJAX endpoints
 * (inc/booking/RBFW_Booking_Reconfirm.php):
 *
 *   1. Authorization. The per-booking nonce is CSRF protection only — for a nopriv endpoint
 *      every logged-out visitor computes the same value and it is printed on the public
 *      confirmation page. Before the fix, a valid nonce was the ONLY gate, so any visitor who
 *      knew a booking reference could request and verify an OTP for someone else's booking.
 *      These checks assert that a request with a good nonce but no ownership signal (not the
 *      logged-in owner, no capability cookie) is refused, while the booking's own customer —
 *      logged-in owner, account-email owner, or holder of the grant_access() cookie — is let
 *      through.
 *
 *   2. Attempt ceiling. Five wrong guesses per emailed code, enforced by a single atomic
 *      claim so the limit holds. This test covers the serial semantics (the 6th guess is
 *      refused, the counter message counts down); the concurrency property that the claim is
 *      atomic is proven separately with overlapping HTTP requests.
 *
 * Red on <= 2.8.4 (no ownership gate), green with the fix.
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

// $rbfw is normally the plugin's main object; the endpoints only use it for send_email(),
// which we neutralise below, so an uninitialised instance is enough under CLI.
$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

// Make the feature "on" regardless of saved settings, and swallow outgoing mail.
$on = static function () {
	return 'on';
};
add_filter( 'option_rbfw_payment_settings', static function ( $v ) {
	$v = is_array( $v ) ? $v : array();
	$v['rbfw_offline_reconfirm_enable'] = 'on';
	return $v;
} );
add_filter( 'default_option_rbfw_payment_settings', static function ( $v ) {
	$v = is_array( $v ) ? $v : array();
	$v['rbfw_offline_reconfirm_enable'] = 'on';
	return $v;
} );
$captured_mail = array();
add_filter( 'pre_wp_mail', static function ( $null, $atts ) use ( &$captured_mail ) {
	$captured_mail[] = $atts;
	return true;
}, 1, 2 );

// Capture wp_send_json_* (which calls wp_die) as a thrown result instead of exiting.
add_filter( 'wp_doing_ajax', '__return_true' );
add_filter( 'wp_die_ajax_handler', static function () {
	return static function () {
		throw new Exception( 'wp_die' );
	};
} );

$posts = array();
$users = array();

$make_user = static function ( $role, $email ) use ( &$users ) {
	$id      = wp_insert_user( array(
		'user_login' => 'rbfw_rc_' . $role . '_' . wp_generate_password( 6, false ),
		'user_pass'  => wp_generate_password(),
		'user_email' => $email,
		'role'       => $role,
	) );
	$users[] = $id;
	return $id;
};

/**
 * Invoke one reconfirm endpoint and return the decoded JSON response.
 *
 * @param string $action 'send' | 'verify'
 * @param array  $post   POST fields (booking_id/nonce/otp)
 * @param int    $viewer user id to act as (0 = logged out)
 * @param array  $cookie $_COOKIE contents for the request
 */
$call = static function ( $action, $post, $viewer, $cookie ) {
	wp_set_current_user( $viewer );
	// Build the nonce for whoever is acting: wp_verify_nonce() is user-scoped, so this keeps
	// the (orthogonal, CSRF-only) nonce valid and leaves ownership as the sole variable.
	if ( isset( $post['booking_id'] ) && ! isset( $post['nonce'] ) ) {
		$post['nonce'] = wp_create_nonce( RBFW_Booking_Reconfirm::nonce_action( $post['booking_id'] ) );
	}
	$_POST    = $post;
	$_REQUEST = $post; // check_ajax_referer()/wp_verify_nonce read $_REQUEST under CLI.
	$_COOKIE  = $cookie;

	$handler = new RBFW_Booking_Reconfirm();
	$method  = 'send' === $action ? 'ajax_send_otp' : 'ajax_verify_otp';

	ob_start();
	try {
		$handler->$method();
	} catch ( Exception $e ) {
		// wp_die from wp_send_json_* — expected.
	}
	$out = ob_get_clean();

	$_POST = $_REQUEST = $_COOKIE = array();
	wp_set_current_user( 0 );

	return json_decode( $out, true );
};

try {
	if ( ! class_exists( 'RBFW_Booking_Reconfirm' ) ) {
		WP_CLI::warning( 'RBFW_Booking_Reconfirm not loaded — standalone booking code is inactive; skipping.' );
		return;
	}

	$owner_email = 'victim.' . wp_generate_password( 5, false ) . '@victim.example';
	$owner       = $make_user( 'customer', $owner_email );
	$attacker    = $make_user( 'subscriber', 'attacker.' . wp_generate_password( 5, false ) . '@evil.example' );

	// A pending native booking owned by $owner.
	$item    = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => 'Reconfirm test item' ) );
	$posts[] = $item;
	update_post_meta( $item, 'rbfw_item_type', 'bike_car_md' );

	$booking = wp_insert_post( array( 'post_type' => 'rbfw_booking', 'post_status' => 'publish', 'post_title' => 'RBFW-RECONFIRM-TEST' ) );
	$posts[] = $booking;
	update_post_meta( $booking, 'rbfw_reference', 'RBFW-RECONFIRM-TEST' );
	update_post_meta( $booking, 'rbfw_item_id', $item );
	update_post_meta( $booking, 'rbfw_customer_email', $owner_email );
	update_post_meta( $booking, 'rbfw_user_id', $owner );
	update_post_meta( $booking, 'rbfw_status', 'pending' );

	// The capability token grant_access() would set on the booking customer's browser. The
	// grant_access()/access_token() pair is the fix; on vulnerable builds it is absent, so the
	// cookie-holder path is skipped there and the test still fails earlier on the (unguarded)
	// anonymous-access assertions — which is exactly the red signal we want.
	$has_cookie_gate = method_exists( 'RBFW_Booking_Reconfirm', 'access_token' );
	$cookie_name     = 'rbfw_rc_' . $booking;
	$good_cookie     = array();
	if ( $has_cookie_gate ) {
		$ref = new ReflectionMethod( 'RBFW_Booking_Reconfirm', 'access_token' );
		$ref->setAccessible( true );
		$good_cookie = array( $cookie_name => $ref->invoke( null, $booking ) );
	}
	$no_cookie = array();

	$ok  = static function ( $res ) {
		return is_array( $res ) && ! empty( $res['success'] );
	};
	$err = static function ( $res ) {
		return is_array( $res ) && empty( $res['success'] );
	};

	// --- Authorization: the core of the vulnerability -----------------------------------

	// 1. Anonymous visitor with a valid nonce but no ownership signal: both endpoints refuse,
	//    and (crucially) no code is emailed to the victim. Red on 2.8.4.
	$captured_mail = array();
	$assert( $err( $call( 'send', array( 'booking_id' => $booking ), 0, $no_cookie ) ), 'anonymous send_otp (good nonce, no owner/cookie) is refused' );
	$assert( 0 === count( $captured_mail ), 'anonymous send_otp emails nobody' );
	$assert( $err( $call( 'verify', array( 'booking_id' => $booking, 'otp' => '123456' ), 0, $no_cookie ) ), 'anonymous verify_otp (good nonce, no owner/cookie) is refused' );

	// 2. A different logged-in user (not the owner) is refused.
	$assert( $err( $call( 'send', array( 'booking_id' => $booking ), $attacker, $no_cookie ) ), 'a non-owner logged-in user is refused' );

	// 3. A forged / empty capability cookie is refused.
	$assert( $err( $call( 'send', array( 'booking_id' => $booking ), 0, array( $cookie_name => 'deadbeef' ) ) ), 'a forged capability cookie is refused' );
	$assert( $err( $call( 'send', array( 'booking_id' => $booking ), 0, array( $cookie_name => '' ) ) ), 'an empty capability cookie is refused' );

	// 4. The booking is still pending — no unauthorised request changed it.
	$assert( 'pending' === get_post_meta( $booking, 'rbfw_status', true ), 'the booking is still pending after the unauthorised attempts' );

	// 5. The legitimate owner (logged in, matches rbfw_user_id) may request a code.
	$captured_mail = array();
	$assert( $ok( $call( 'send', array( 'booking_id' => $booking ), $owner, $no_cookie ) ), 'the logged-in owner may request a code' );
	$assert( 1 === count( $captured_mail ), 'the owner request emails exactly once' );

	// 6. A guest who holds the grant_access() cookie may request a code.
	update_post_meta( $booking, 'rbfw_reconfirm_otp_sent_at', 0 ); // clear cooldown for the next send
	$captured_mail = array();
	$res = $call( 'send', array( 'booking_id' => $booking ), 0, $good_cookie );
	$assert( $ok( $res ), 'a guest holding the capability cookie may request a code' );
	$assert( 1 === count( $captured_mail ), 'the cookie-holder request emails exactly once' );

	// Recover the code that was just emailed so we can drive the verify path.
	$emailed = '';
	if ( preg_match( '/code is: (\d{6})/', (string) $captured_mail[0]['message'], $m ) ) {
		$emailed = $m[1];
	}
	$assert( '' !== $emailed, 'the emailed message carries a 6-digit code' );

	// --- Attempt ceiling (serial) -------------------------------------------------------

	// Five wrong guesses are evaluated with a decreasing remaining-count, the sixth is refused
	// as "too many", and the code still cannot be used past the limit.
	update_post_meta( $booking, 'rbfw_reconfirm_otp_attempts', 0 );
	update_post_meta( $booking, 'rbfw_reconfirm_otp_expires', time() + 600 );
	$wrong = '000000' === $emailed ? '111111' : '000000';
	for ( $i = 1; $i <= 5; $i++ ) {
		$res = $call( 'verify', array( 'booking_id' => $booking, 'otp' => $wrong ), 0, $good_cookie );
		$assert( $err( $res ) && false !== strpos( (string) $res['data']['message'], 'Incorrect code' ), "wrong guess $i is rejected as incorrect" );
	}
	$res = $call( 'verify', array( 'booking_id' => $booking, 'otp' => $wrong ), 0, $good_cookie );
	$assert( $err( $res ) && false !== strpos( (string) $res['data']['message'], 'Too many' ), 'the sixth guess is refused: the attempt ceiling holds' );
	$assert( 5 === (int) get_post_meta( $booking, 'rbfw_reconfirm_otp_attempts', true ), 'the stored attempt counter never exceeds the ceiling' );
	$assert( 'pending' === get_post_meta( $booking, 'rbfw_status', true ), 'the booking is still pending after exhausting the attempts' );

	// --- Happy path: the owner confirms with the correct code ---------------------------

	update_post_meta( $booking, 'rbfw_reconfirm_otp_attempts', 0 );
	update_post_meta( $booking, 'rbfw_reconfirm_otp_expires', time() + 600 );
	$res = $call( 'verify', array( 'booking_id' => $booking, 'otp' => $emailed ), 0, $good_cookie );
	$assert( $ok( $res ), 'the correct code from a cookie-holder confirms the booking' );
	$assert( 'confirmed' === RBFW_Booking_Normalizer::normalize_status( get_post_meta( $booking, 'rbfw_status', true ) ), 'the booking is now confirmed' );
	$assert( 'customer_otp' === get_post_meta( $booking, 'rbfw_confirmed_by', true ), 'the confirmation is attributed to the customer' );

	WP_CLI::success( "reconfirm-otp-access: all {$checks} checks passed." );
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
