<?php
/**
 * Run on a development site with WooCommerce active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/listing-price-appointment.php
 *
 * An Appointment ("service") item priced per rental option (55 / 85 / 122 / 140)
 * was advertised on the [rbfw_booking_search] results list as "10.00 per day".
 * Two faults stacked: the search card fell back to rbfw_daily_rate for every
 * item type, and saving an Appointment / Single-day item writes that meta with
 * the multi-day panel's placeholder (10) even though the item never charges it.
 * Expected: each item type advertises the price its own booking engine charges —
 * cheapest rental option for Appointment / Single-day, room rate for Resort,
 * cheapest item row for bundles, day/hour/week/month rate for multi-day — and
 * saving a non multi-day item no longer creates the phantom daily rate.
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

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

$mode_filter = static function () {
	return array( RBFW_Function::MODE_KEY => 'woocommerce' );
};
add_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );

/** Build a published item with the given meta. */
$make_item = static function ( $title, $meta ) {
	$id = wp_insert_post(
		array(
			'post_type'    => 'rbfw_item',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => 'Listing price test item.',
		)
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}

	return $id;
};

$start = gmdate( 'Y-m-d', strtotime( '+1 day' ) );
$end   = gmdate( 'Y-m-d', strtotime( '+2 days' ) );
$posts = array();

try {
	/* ---------- 1. Appointment item exactly like the reported one ---------- */
	$appointment = $make_item(
		'RBFW appointment listing price test',
		array(
			'rbfw_item_type'         => 'appointment',
			'rbfw_bike_car_sd_data'  => array(
				array( 'rent_type' => '1 Hour lesson', 'price' => '55', 'qty' => '20' ),
				array( 'rent_type' => '2 Hours lessons', 'price' => '85', 'qty' => '20' ),
				array( 'rent_type' => '3 Hours lessons', 'price' => '122', 'qty' => '20' ),
				array( 'rent_type' => '4 Hours lessons', 'price' => '140', 'qty' => '20' ),
			),
			// Left behind by earlier saves of the hidden multi-day panel.
			'rbfw_enable_daily_rate' => 'yes',
			'rbfw_daily_rate'        => 10,
		)
	);
	$posts[]     = $appointment;

	$starting = rbfw_get_item_starting_price( $appointment );
	$assert( 55.0 === (float) $starting['price'], 'Appointment starting price is the cheapest rental option (55), not the stale daily rate' );
	$assert( 'starting price' === $starting['label'], 'Appointment starting price carries the "starting price" note' );
	$assert( 'rental_options' === $starting['source'], 'Appointment price comes from the rental options' );

	$card = rbfw_booking_search_card_price( $appointment, $start, $end, 2 );
	$assert( false !== strpos( $card['html'], '55' ), 'Search result card shows the 55 starting price' );
	$assert( false === strpos( $card['html'], '>10<' ), 'Search result card no longer shows the 10 placeholder' );
	$assert( 'starting price' === $card['note'], 'Search result card notes that 55 is the starting price' );

	/* ---------- 2. Same rows on a Single-day item ---------- */
	update_post_meta( $appointment, 'rbfw_item_type', 'bike_car_sd' );
	$single_day = rbfw_get_item_starting_price( $appointment );
	$assert( 55.0 === (float) $single_day['price'], 'Single-day item also prices from its rental options' );


	/* ---------- 3. Multi-day item keeps its own rate ---------- */
	$multi_day = $make_item(
		'RBFW multi day listing price test',
		array(
			'rbfw_item_type'           => 'bike_car_md',
			'rbfw_enable_daily_rate'   => 'yes',
			'rbfw_daily_rate'          => 100,
			'rbfw_enable_hourly_rate'  => 'no',
			'rbfw_item_stock_quantity' => 5,
		)
	);
	$posts[]   = $multi_day;

	$md_price = rbfw_get_item_starting_price( $multi_day, array( 'display' => 'daily' ) );
	$assert( 100.0 === (float) $md_price['price'], 'Multi-day item advertises its daily rate' );
	$assert( 'per day' === $md_price['label'], 'Multi-day daily rate is labelled per day' );

	$md_card = rbfw_booking_search_card_price( $multi_day, $start, $end, 2 );
	$assert( '' !== $md_card['html'], 'Multi-day item still shows a price on the search card' );

	/* Hourly-only multi-day item: daily switched off, time picker on. */
	update_post_meta( $multi_day, 'rbfw_enable_daily_rate', 'no' );
	update_post_meta( $multi_day, 'rbfw_enable_hourly_rate', 'yes' );
	update_post_meta( $multi_day, 'rbfw_enable_time_picker', 'yes' );
	update_post_meta( $multi_day, 'rbfw_hourly_rate', 15 );
	$hourly_price = rbfw_get_item_starting_price( $multi_day, array( 'display' => 'daily' ) );
	$assert( 15.0 === (float) $hourly_price['price'], 'Hourly-only multi-day item falls back to its hourly rate' );
	$assert( 'per hour' === $hourly_price['label'], 'Hourly rate is labelled per hour' );

	/* ---------- 4. Resort item prices from its rooms ---------- */
	$resort = $make_item(
		'RBFW resort listing price test',
		array(
			'rbfw_item_type'        => 'resort',
			'rbfw_resort_room_data' => array(
				array( 'room_type' => 'Double', 'rbfw_room_daynight_rate' => '180', 'rbfw_room_available_qty' => '4' ),
				array( 'room_type' => 'Single', 'rbfw_room_daynight_rate' => '120', 'rbfw_room_daylong_rate' => '90', 'rbfw_room_available_qty' => '4' ),
			),
			// A daily rate on a Resort item is never charged either.
			'rbfw_daily_rate'       => 10,
		)
	);
	$posts[] = $resort;

	$resort_price = rbfw_get_item_starting_price( $resort );
	$assert( 90.0 === (float) $resort_price['price'], 'Resort item advertises the cheapest room rate' );
	$assert( 'resort_rooms' === $resort_price['source'], 'Resort price comes from its rooms' );

	/* ---------- 5. Bundle item prices from its item rows ---------- */
	$bundle = $make_item(
		'RBFW multiple items listing price test',
		array(
			'rbfw_item_type'      => 'multiple_items',
			'multiple_items_info' => array(
				array( 'item_name' => 'Skis', 'available_qty' => '5', 'hourly_price' => '20', 'daily_price' => '90', 'weekly_price' => '', 'monthly_price' => '' ),
				array( 'item_name' => 'Boots', 'available_qty' => '5', 'hourly_price' => '8', 'daily_price' => '30', 'weekly_price' => '', 'monthly_price' => '' ),
			),
			'rbfw_daily_rate'     => 10,
		)
	);
	$posts[] = $bundle;

	$bundle_daily = rbfw_get_item_starting_price( $bundle, array( 'display' => 'daily' ) );
	$assert( 30.0 === (float) $bundle_daily['price'], 'Bundle advertises the cheapest item row for the preferred duration' );
	$assert( 'per day' === $bundle_daily['label'], 'Bundle price is labelled per day' );

	$bundle_hourly = rbfw_get_item_starting_price( $bundle, array( 'display' => 'hourly' ) );
	$assert( 8.0 === (float) $bundle_hourly['price'], 'Bundle follows the preferred hourly duration' );

	/* ---------- 6. Item without any price shows nothing ---------- */
	$empty   = $make_item( 'RBFW no price listing test', array( 'rbfw_item_type' => 'appointment' ) );
	$posts[] = $empty;

	$empty_starting = rbfw_get_item_starting_price( $empty );
	$assert( 0.0 === (float) $empty_starting['price'], 'Item without prices reports no starting price' );

	$empty_card = rbfw_booking_search_card_price( $empty, $start, $end, 2 );
	$assert( '' === $empty_card['html'], 'Item without prices keeps the card price block empty' );

	$cards_html = rbfw_booking_search_render_cards(
		array(
			array(
				'id'          => $empty,
				'title'       => 'No price card',
				'permalink'   => get_permalink( $empty ),
				'thumb'       => '',
				'type_label'  => 'Appointment',
				'is_quick'    => false,
				'remaining'   => null,
				'price_html'  => $empty_card['html'],
				'price_note'  => $empty_card['note'],
				'loc_charge'  => 0,
				'qty_enabled' => false,
				'excerpt'     => '',
			),
		),
		$start,
		$end,
		'',
		'Add to booking',
		true,
		'list'
	);
	$assert( false === strpos( $cards_html, 'rbfw_bsearch_price' ), 'Empty price block is not rendered at all' );

	/* ---------- 7. Site-level override of the card price ---------- */
	$override = static function ( $display, $item_id ) use ( $appointment ) {
		if ( (int) $item_id === (int) $appointment ) {
			return array(
				'html' => '<span>Ask us</span>',
				'note' => 'price on request',
			);
		}

		return $display;
	};
	add_filter( 'rbfw_booking_search_card_price', $override, 10, 2 );
	$filtered = rbfw_booking_search_card_price( $appointment, $start, $end, 2 );
	remove_filter( 'rbfw_booking_search_card_price', $override, 10 );
	$assert( '<span>Ask us</span>' === $filtered['html'] && 'price on request' === $filtered['note'], 'rbfw_booking_search_card_price can replace or hide the shown price' );

	/* ---------- 8. show_price="no" reaches the card renderer ---------- */
	$shortcode_html = do_shortcode( '[rbfw_booking_search show_price="no"]' );
	$assert( false !== strpos( $shortcode_html, 'data-show-price="no"' ), '[rbfw_booking_search show_price="no"] hides the auto price' );
	$shortcode_html = do_shortcode( '[rbfw_booking_search]' );
	$assert( false !== strpos( $shortcode_html, 'data-show-price="yes"' ), 'Prices stay on by default' );

	/* ---------- 9. Saving an Appointment item no longer stores a daily rate ---------- */
	require_once RBFW_PLUGIN_DIR . '/admin/settings/Pricing.php';
	wp_set_current_user( 1 );
	$admin_user = get_user_by( 'id', 1 );
	if ( ! $admin_user || ! user_can( $admin_user, 'edit_post', $appointment ) ) {
		WP_CLI::warning( 'Skipped the Pricing save check: user 1 cannot edit posts.' );
	} elseif ( ! class_exists( 'RBFW_Pricing' ) ) {
		WP_CLI::warning( 'Skipped the Pricing save check: RBFW_Pricing did not load.' );
	} else {
		$fresh   = $make_item(
			'RBFW appointment save test',
			array(
				'rbfw_item_type'        => 'appointment',
				'rbfw_bike_car_sd_data' => array(
					array( 'rent_type' => '1 Hour lesson', 'price' => '55', 'qty' => '20' ),
				),
			)
		);
		$posts[] = $fresh;

		$pricing    = new RBFW_Pricing();
		$saved_post = $_POST;
		$saved_req  = $_REQUEST;
		$_POST      = array(
			'rbfw_ticket_type_nonce'  => wp_create_nonce( 'rbfw_ticket_type_nonce' ),
			'rbfw_item_type'          => 'appointment',
			'rbfw_bike_car_sd_data'   => array(
				array( 'rent_type' => '2 Hours lessons', 'price' => '85', 'qty' => '20' ),
			),
			// The hidden multi-day panel still posts these on every save.
			'rbfw_enable_daily_rate'  => 'yes',
			'rbfw_daily_rate'         => '10',
			'rbfw_enable_hourly_rate' => 'yes',
			'rbfw_hourly_rate'        => '12',
		);
		$_REQUEST   = $_POST;
		$pricing->settings_save( $fresh );
		$_POST    = $saved_post;
		$_REQUEST = $saved_req;

		$saved_rows = get_post_meta( $fresh, 'rbfw_bike_car_sd_data', true );
		$assert(
			is_array( $saved_rows ) && isset( $saved_rows[0]['price'] ) && '85' === (string) $saved_rows[0]['price'],
			'Pricing save ran and stored the posted rental options'
		);
		$assert( '' === (string) get_post_meta( $fresh, 'rbfw_daily_rate', true ), 'Saving an Appointment item no longer creates a phantom daily rate' );
		$assert( '' === (string) get_post_meta( $fresh, 'rbfw_hourly_rate', true ), 'Saving an Appointment item no longer creates a phantom hourly rate' );
	}

	WP_CLI::success( $checks . ' checks passed.' );
} catch ( Throwable $e ) {
	WP_CLI::error( $e->getMessage() );
} finally {
	remove_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );
	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	$GLOBALS['rbfw'] = $saved_rbfw;
}

