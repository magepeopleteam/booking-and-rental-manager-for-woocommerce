<?php
/**
 * Run on a development site with WooCommerce active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/md-service-quantity.php
 *
 * Multi-day "Service Type" rows each have their own quantity stepper. Booking 2 bikes with
 * 2 x Child Seat (3 per day) showed 6 on the form and in the cart breakdown, but the cart
 * charged 12: the service total was multiplied by the item quantity a second time. The
 * live price summary did the same after a date or quantity change.
 * Expected: services are charged price x service qty (x days for day-wise), independent of
 * the number of units booked.
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
$near = static function ( $a, $b ) {
	return abs( (float) $a - (float) $b ) < 0.001;
};

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

$mode_filter = static function () {
	return array( RBFW_Function::MODE_KEY => 'woocommerce' );
};
add_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );

$posts = array();

try {
	$item    = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => 'RBFW service qty test' ) );
	$posts[] = $item;
	foreach ( array(
		'rbfw_item_type'              => 'bike_car_md',
		'rbfw_enable_daily_rate'      => 'yes',
		'rbfw_daily_rate'             => 35,
		'rbfw_enable_hourly_rate'     => 'no',
		'rbfw_item_stock_quantity'    => 5,
		'rbfw_enable_start_end_date'  => 'yes',
		'rbfw_enable_time_picker'     => 'no',
		'rbfw_service_category_price' => array(
			array(
				'cat_title'    => 'Extras',
				'cat_services' => array(
					array( 'title' => 'Child Seat', 'price' => 3, 'service_price_type' => 'day_wise', 'stock_quantity' => 20 ),
					array( 'title' => 'Lock', 'price' => 2, 'service_price_type' => 'one_time', 'stock_quantity' => 20 ),
				),
			),
		),
	) as $key => $value ) {
		update_post_meta( $item, $key, $value );
	}
	$product = ( new RBFW_Hidden_Product() )->ensure_hidden_product( $item );
	$posts[] = $product;

	wp_set_current_user( 0 );
	wc_load_cart();

	$start = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
	$end   = gmdate( 'Y-m-d', strtotime( '+32 days' ) );

	// Add to cart through WooCommerce's own form handler (the Book Now path).
	$book = static function ( $units, $seats, $locks ) use ( $item, $product, $start, $end ) {
		WC()->cart->empty_cart();
		wc_clear_notices();
		$_POST    = array(
			'rbfw_post_id'            => $item,
			'rbfw_pickup_start_date'  => $start,
			'rbfw_pickup_end_date'    => $end,
			'rbfw_pickup_start_time'  => '10:00',
			'rbfw_pickup_end_time'    => '10:00',
			'rbfw_enable_time_slot'   => 'no',
			'rbfw_item_quantity'      => (string) $units,
			'nonce'                   => wp_create_nonce( 'rbfw_ajax_action' ),
			'add-to-cart'             => $product,
			'rbfw_service_price_data' => array(
				0 => array(
					'cat_title' => 'Extras',
					0           => array( 'name' => 'Child Seat', 'quantity' => (string) $seats, 'service_price_type' => 'day_wise', 'price' => '3' ),
					1           => array( 'name' => 'Lock', 'quantity' => (string) $locks, 'service_price_type' => 'one_time', 'price' => '2' ),
				),
			),
		);
		$_REQUEST = $_POST;
		WC_Form_Handler::add_to_cart_action();
		$_POST    = array();
		$_REQUEST = array();
		$lines    = array_values( WC()->cart->get_cart() );
		return $lines ? $lines[0] : null;
	};

	$one = $book( 1, 2, 1 );
	$assert( null !== $one, 'One unit with services reaches the cart' );
	$days = (float) $one['rbfw_duration_price'] / 35; // billed days for one unit
	$assert( $days >= 1, "Duration is priced ($days day(s))" );
	$expected_services = 3 * 2 * $days + 2 * 1;
	$assert( $near( $one['rbfw_service_price'], $expected_services ), "1 unit: services = 3 x 2 x $days + 2 x 1 = $expected_services (got {$one['rbfw_service_price']})" );

	$two = $book( 2, 2, 1 );
	$assert( null !== $two, 'Two units with the same services reach the cart' );
	$assert( $near( $two['rbfw_duration_price'], 35 * $days * 2 ), 'Duration is charged per unit (35 x days x 2)' );
	$assert( $near( $two['rbfw_service_price'], $expected_services ), "2 units: services are NOT doubled ($expected_services, got {$two['rbfw_service_price']})" );
	$assert( $near( $two['rbfw_tp'], 35 * $days * 2 + $expected_services ), 'Cart line total = duration + services once' );
	$assert( $near( $two['rbfw_ticket_info'][0]['service_cost'], $expected_services ), 'Stored ticket service cost matches' );

	// The live price summary on the booking form.
	$die = static function () {
		return static function () {
			throw new RuntimeException( 'rbfw-test-die' );
		};
	};
	add_filter( 'wp_die_ajax_handler', $die );
	add_filter( 'wp_doing_ajax', '__return_true' );
	$summary = static function ( $units, $posted_service_total ) use ( $item, $start, $end ) {
		$_POST    = array(
			'action'                => 'rbfw_bikecarmd_ajax_price_calculation',
			'nonce'                 => wp_create_nonce( 'rbfw_bikecarmd_ajax_price_calculation_action' ),
			'post_id'               => $item,
			'pickup_date'           => $start,
			'dropoff_date'          => $end,
			'pickup_time'           => '10:00',
			'dropoff_time'          => '10:00',
			'item_quantity'         => $units,
			'rbfw_service_price'    => $posted_service_total,
			'rbfw_es_service_price' => 0,
			'rbfw_enable_time_slot' => 'no',
		);
		$_REQUEST = $_POST;
		ob_start();
		try {
			do_action( 'wp_ajax_nopriv_rbfw_bikecarmd_ajax_price_calculation' );
		} catch ( RuntimeException $e ) {
			unset( $e );
		}
		$json     = json_decode( (string) ob_get_clean(), true );
		$_POST    = array();
		$_REQUEST = array();
		return is_array( $json ) ? $json : array();
	};
	$live = $summary( 2, $expected_services );
	$assert( isset( $live['service_cost'] ) && $near( $live['service_cost'], $expected_services ), "Live summary, 2 units: service cost stays $expected_services (got " . ( $live['service_cost'] ?? 'none' ) . ')' );

	WP_CLI::success( "$checks checks passed." );
} finally {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		WC()->cart->empty_cart();
	}
	foreach ( array_reverse( $posts ) as $id ) {
		if ( $id ) {
			wp_delete_post( $id, true );
		}
	}
	remove_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );
	$GLOBALS['rbfw'] = $saved_rbfw;
}
