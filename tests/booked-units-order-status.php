<?php
/**
 * Run on a development site: wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/booked-units-order-status.php
 *
 * A rented unit must stay booked for the whole time its order holds it, not only while the
 * order is Processing/Completed.
 *
 * At checkout the booking is written with status "processing", so the unit is blocked. The
 * order's next status change then overwrites that copy: On hold (bank transfer, cheque),
 * Pending (customer back on the thank-you page before a PayPal/redirect payment confirms).
 * The availability engines counted only Processing/Completed/Picked, so the unit showed as
 * free again on the calendar and in the time picker. For Single Day items the booking gate
 * reuses those engines, so a second customer could book the same unit. The Multiple Day gate
 * already used rbfw_get_blocking_order_statuses() (refused the booking, but still showed the
 * unit as free). Native bookings stay "pending" in inventory and were never counted.
 */
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

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();
add_filter( 'pre_wp_mail', '__return_false' ); // order status changes send emails.

$items  = array();
$orders = array();
$extra  = array(); // native bookings + mirror/reservation posts.

$day     = gmdate( 'Y-m-d', strtotime( '+12 days' ) );
$md_end  = gmdate( 'Y-m-d', strtotime( $day . ' +2 days' ) );
$ym      = array( (int) gmdate( 'Y', strtotime( $day ) ), (int) gmdate( 'n', strtotime( $day ) ) );

$make_item = static function ( $title, $meta ) use ( &$items ) {
	$id = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => $title ) );
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	$items[] = $id;
	return $id;
};

$sd_timely_meta = array(
	'rbfw_item_type'                  => 'bike_car_sd',
	'manage_inventory_as_timely'      => 'on',
	'enable_specific_duration'        => 'off',
	'rbfw_item_stock_quantity_timely' => 1,
	'rbfw_bike_car_sd_data'           => array( array( 'rent_type' => '2 Hours', 'price' => 20, 'qty' => 1, 'duration' => 2, 'd_type' => 'Hours' ) ),
);
$sd_timely = $make_item( 'Status test SD timely', $sd_timely_meta );
$sd_option = $make_item( 'Status test SD option', array(
	'rbfw_item_type'             => 'bike_car_sd',
	'manage_inventory_as_timely' => 'off',
	'rbfw_bike_car_sd_data'      => array( array( 'rent_type' => 'Full Day', 'price' => 40, 'qty' => 1, 'duration' => 1, 'd_type' => 'Days' ) ),
) );
$md = $make_item( 'Status test MD', array(
	'rbfw_item_type'              => 'bike_car_md',
	'rbfw_item_stock_quantity'    => 1,
	'stock_manage_on_return_date' => 'yes',
) );

$tickets = array(
	$sd_timely => array( 'rbfw_start_date' => $day, 'rbfw_start_time' => '10:00', 'rbfw_end_date' => $day, 'rbfw_end_time' => '12:00:00', 'rbfw_type_info' => array( '2 Hours' => 1 ) ),
	$sd_option => array( 'rbfw_start_date' => $day, 'rbfw_start_time' => '10:00', 'rbfw_end_date' => $day, 'rbfw_end_time' => '', 'rbfw_type_info' => array( 'Full Day' => 1 ) ),
	$md        => array( 'rbfw_start_date' => $day, 'rbfw_start_time' => '', 'rbfw_end_date' => $md_end, 'rbfw_end_time' => '', 'rbfw_type_info' => array() ),
);
$names = array( $sd_timely => 'Single Day timely', $sd_option => 'Single Day option', $md => 'Multiple Day' );

// What the next customer is shown for the same period (the booking form's engine).
$shown = static function ( $item ) use ( $tickets, $sd_timely, $sd_option ) {
	$t = $tickets[ $item ];
	if ( $item === $sd_timely ) {
		return (int) rbfw_timely_available_quantity_updated( $item, $t['rbfw_start_date'], '10:00', $t['rbfw_end_date'], '12:00:00' );
	}
	if ( $item === $sd_option ) {
		return (int) rbfw_get_bike_car_sd_available_qty( $item, $t['rbfw_start_date'], 'Full Day', '10:00' );
	}
	$r = rbfw_get_multiple_date_available_qty( $item, $t['rbfw_start_date'], $t['rbfw_end_date'], null, $t['rbfw_start_date'] . ' 00:00', $t['rbfw_end_date'] . ' 23:59' );
	return (int) $r['remaining_stock'];
};

// Whether the add-to-cart / checkout gate lets the next customer book it.
$bookable = static function ( $item ) use ( $tickets ) {
	$t      = $tickets[ $item ];
	$values = $t + array(
		'rbfw_item_quantity'  => 1,
		'rbfw_start_datetime' => $t['rbfw_start_date'] . ' ' . ( $t['rbfw_start_time'] ? $t['rbfw_start_time'] : '00:00' ),
		'rbfw_end_datetime'   => $t['rbfw_end_date'] . ' ' . ( $t['rbfw_end_time'] ? $t['rbfw_end_time'] : '23:59' ),
	);
	foreach ( rbfw_check_rental_availability( $item, $values ) as $check ) {
		if ( empty( $check['ok'] ) ) {
			return false;
		}
	}
	return true;
};

// Units left on the booking day in the calendar ("N left" / sold-out day).
$calendar_left = static function ( $item ) use ( $day, $ym ) {
	$map = rbfw_calendar_day_stock_map( $item, $ym[0], $ym[1] );
	return isset( $map[ $day ]['n'] ) ? (int) $map[ $day ]['n'] : null;
};

$expect_booked = static function ( $label ) use ( $assert, $names, $shown, $bookable, $calendar_left ) {
	foreach ( $names as $item => $name ) {
		$assert( 0 === $shown( $item ), "$name: booking form shows no unit left ($label)" );
		$assert( ! $bookable( $item ), "$name: checkout refuses a second booking ($label)" );
		$left = $calendar_left( $item );
		$assert( null === $left || 0 === $left, "$name: calendar shows the day full ($label)" );
	}
};
$expect_free = static function ( $label ) use ( $assert, $names, $shown, $bookable ) {
	foreach ( $names as $item => $name ) {
		$assert( 1 === $shown( $item ), "$name: unit is free again ($label)" );
		$assert( $bookable( $item ), "$name: checkout accepts a new booking ($label)" );
	}
};
$set_status = static function ( $status ) use ( &$orders ) {
	foreach ( $orders as $order_id ) {
		wc_get_order( $order_id )->update_status( $status );
	}
};

try {
	$expect_free( 'no order yet' );

	foreach ( $tickets as $item => $ticket ) {
		$order = wc_create_order( array( 'status' => 'pending' ) );
		$order->set_billing_first_name( 'Status' );
		$order->set_billing_email( 'status-test@example.test' );
		$order->set_payment_method( 'bacs' );
		$line = new WC_Order_Item_Product();
		$line->set_name( get_the_title( $item ) );
		$line->set_quantity( 1 );
		$line->set_total( 10 );
		$line->add_meta_data( '_rbfw_id', $item );
		$line->add_meta_data( '_rbfw_ticket_info', array( array_merge( $ticket, array(
			'ticket_name'         => get_the_title( $item ),
			'ticket_price'        => 10,
			'ticket_qty'          => 1,
			'rbfw_id'             => $item,
			'rbfw_item_quantity'  => 1,
			'rbfw_start_datetime' => trim( $ticket['rbfw_start_date'] . ' ' . $ticket['rbfw_start_time'] ),
			'rbfw_end_datetime'   => trim( $ticket['rbfw_end_date'] . ' ' . $ticket['rbfw_end_time'] ),
		) ) ) );
		$order->add_item( $line );
		$order->calculate_totals();
		$order->save();
		$orders[ $item ] = $order->get_id();
		do_action( 'woocommerce_checkout_order_processed', $order->get_id() );
	}
	$expect_booked( 'order just placed' );

	$set_status( 'on-hold' );
	$expect_booked( 'order on hold, e.g. bank transfer' );

	$set_status( 'pending' );
	foreach ( $orders as $order_id ) {
		ob_start();
		do_action( 'woocommerce_thankyou', $order_id ); // copies the live (pending) status into inventory.
		ob_end_clean();
	}
	foreach ( $orders as $item => $order_id ) {
		$inv = get_post_meta( $item, 'rbfw_inventory', true );
		$assert( 'pending' === $inv[ $order_id ]['rbfw_order_status'], $names[ $item ] . ': inventory copy now reads pending' );
	}
	$expect_booked( 'pending, back on the thank-you page before the payment callback' );

	$windows = rbfw_get_inventory_booked_windows( $md );
	$assert( 1 === count( $windows ), 'Multiple Day time pickers grey out the pending booking' );

	// The one rule stays filterable: a shop that wants unpaid orders not to hold stock.
	$no_unpaid = static function ( $statuses ) {
		return array_values( array_diff( $statuses, array( 'pending', 'on-hold' ) ) );
	};
	add_filter( 'rbfw_blocking_order_statuses', $no_unpaid );
	$expect_free( 'pending, rbfw_blocking_order_statuses filter drops unpaid statuses' );
	remove_filter( 'rbfw_blocking_order_statuses', $no_unpaid );

	$set_status( 'processing' );
	$expect_booked( 'paid, processing' );

	$set_status( 'completed' );
	$expect_booked( 'completed' );

	$set_status( 'failed' );
	$expect_free( 'payment failed' );

	$set_status( 'processing' );
	$set_status( 'cancelled' );
	$expect_free( 'cancelled' );

	// Native (standalone) bookings are written to inventory as "pending" and stay that way.
	$native_item = $make_item( 'Status test native SD timely', $sd_timely_meta );
	$service     = new RBFW_Standalone_Booking_Service();
	$result      = $service->create_booking( $native_item, array(
		'customer'    => array( 'name' => 'Native Status', 'email' => 'native-status@example.test', 'phone' => '1' ),
		'dates'       => array( 'start_date' => $day, 'end_date' => $day, 'start_time' => '10:00', 'end_time' => '12:00:00' ),
		'total'       => 20,
		'quantity'    => 1,
		'ticket_info' => $tickets[ $sd_timely ] + array( 'rbfw_item_quantity' => 1 ),
	) );
	$assert( is_array( $result ) && ! empty( $result['booking_id'] ), 'Native booking created' );
	$extra[] = (int) $result['booking_id'];
	$assert( 0 === (int) rbfw_timely_available_quantity_updated( $native_item, $day, '10:00', $day, '12:00:00' ), 'Native pending booking holds its unit in the booking form' );
	$native_ok = true;
	$native_values = $tickets[ $sd_timely ] + array(
		'rbfw_item_quantity'  => 1,
		'rbfw_start_datetime' => $day . ' 10:00',
		'rbfw_end_datetime'   => $day . ' 12:00:00',
	);
	foreach ( rbfw_check_rental_availability( $native_item, $native_values ) as $check ) {
		if ( empty( $check['ok'] ) ) {
			$native_ok = false;
		}
	}
	$assert( ! $native_ok, 'Native pending booking: checkout refuses a second booking' );

	// Native bookings are keyed by their rbfw_booking id, not a WooCommerce order: the
	// Multiple Day windows and calendar skipped them because wc_get_order() found nothing.
	$native_md = $make_item( 'Status test native MD', array(
		'rbfw_item_type'              => 'bike_car_md',
		'rbfw_item_stock_quantity'    => 1,
		'stock_manage_on_return_date' => 'yes',
	) );
	$result    = $service->create_booking( $native_md, array(
		'customer'    => array( 'name' => 'Native MD', 'email' => 'native-md@example.test', 'phone' => '1' ),
		'dates'       => array( 'start_date' => $day, 'end_date' => $md_end, 'start_time' => '', 'end_time' => '' ),
		'total'       => 30,
		'quantity'    => 1,
		'ticket_info' => $tickets[ $md ] + array( 'rbfw_item_quantity' => 1 ),
	) );
	$assert( is_array( $result ) && ! empty( $result['booking_id'] ), 'Native Multiple Day booking created' );
	$extra[]   = (int) $result['booking_id'];
	$md_avail  = rbfw_get_multiple_date_available_qty( $native_md, $day, $md_end, null, $day . ' 00:00', $md_end . ' 23:59' );
	$assert( 0 === (int) $md_avail['remaining_stock'], 'Native Multiple Day booking holds its unit in the booking form' );
	$md_map    = rbfw_calendar_day_stock_map( $native_md, $ym[0], $ym[1] );
	$assert( isset( $md_map[ $day ]['n'] ) && 0 === (int) $md_map[ $day ]['n'], 'Native Multiple Day booking shows the day full in the calendar' );

	WP_CLI::success( "$checks checks passed." );
} finally {
	foreach ( $orders as $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->delete( true );
		}
	}
	foreach ( array( 'rbfw_order', 'rbfw_order_meta' ) as $post_type ) {
		foreach ( $orders as $order_id ) {
			$linked = get_posts( array(
				'post_type'   => $post_type,
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_key'    => 'rbfw_link_order_id', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => $order_id,           // phpcs:ignore WordPress.DB.SlowDBQuery
			) );
			foreach ( $linked as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		}
	}
	foreach ( $extra as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	foreach ( $items as $item ) {
		$product_id = (int) get_post_meta( $item, 'link_wc_product', true );
		if ( $product_id ) {
			wp_delete_post( $product_id, true );
		}
		wp_delete_post( $item, true );
	}
	remove_filter( 'pre_wp_mail', '__return_false' );
	$GLOBALS['rbfw'] = $saved_rbfw;
}
