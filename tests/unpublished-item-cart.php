<?php
/**
 * Run on a development site with WooCommerce active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/unpublished-item-cart.php
 *
 * A rental's backing WooCommerce product stays published when the rental item is set to
 * draft, pending, private or trashed. The add-to-cart validator only checked the public
 * booking nonce, so a visitor who kept the product ID could still book the unpublished
 * item through WooCommerce. Expected: refused on every add-to-cart path, and a cart line
 * added while the item was public is dropped once it is unpublished.
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

// WooCommerce mode, read-only for this run.
$mode_filter = static function () {
	return array( RBFW_Function::MODE_KEY => 'woocommerce' );
};
add_filter( 'pre_option_' . RBFW_Function::MODE_OPTION, $mode_filter );

$posts = array();

try {
	$item = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => 'RBFW unpublished-cart test' ) );
	$posts[] = $item;
	foreach ( array(
		'rbfw_item_type'             => 'bike_car_md',
		'rbfw_enable_daily_rate'     => 'yes',
		'rbfw_daily_rate'            => 60,
		'rbfw_enable_hourly_rate'    => 'no',
		'rbfw_item_stock_quantity'   => 2,
		'rbfw_enable_start_end_date' => 'yes',
		'rbfw_enable_time_picker'    => 'no',
	) as $key => $value ) {
		update_post_meta( $item, $key, $value );
	}
	$product = ( new RBFW_Hidden_Product() )->ensure_hidden_product( $item );
	$posts[] = $product;
	$assert( $product > 0 && 'publish' === get_post_status( $product ), 'The item has a published backing product' );

	$plain   = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'RBFW plain product' ) );
	$posts[] = $plain;
	update_post_meta( $plain, '_price', 5 );
	update_post_meta( $plain, '_regular_price', 5 );

	wp_set_current_user( 0 ); // a guest, as in the report
	wc_load_cart();

	$start = gmdate( 'Y-m-d', strtotime( '+40 days' ) );
	$end   = gmdate( 'Y-m-d', strtotime( '+41 days' ) );
	$form  = static function () use ( $item, $start, $end ) {
		return array(
			'rbfw_post_id'           => $item,
			'rbfw_pickup_start_date' => $start,
			'rbfw_pickup_end_date'   => $end,
			'rbfw_pickup_start_time' => '10:00',
			'rbfw_pickup_end_time'   => '10:00',
			'rbfw_enable_time_slot'  => 'no',
			'rbfw_item_quantity'     => '1',
			'nonce'                  => wp_create_nonce( 'rbfw_ajax_action' ),
		);
	};
	$errors = static function () {
		return implode( ' ', wp_list_pluck( wc_get_notices( 'error' ), 'notice' ) );
	};
	$reset = static function () {
		WC()->cart->empty_cart();
		wc_clear_notices();
	};

	// Classic form post (WC_Form_Handler), the path the Book Now button uses.
	$form_post = static function () use ( $form, $product ) {
		$_POST                = $form();
		$_POST['add-to-cart'] = $product;
		$_REQUEST             = $_POST;
		WC_Form_Handler::add_to_cart_action();
		$_POST    = array();
		$_REQUEST = array();
		return WC()->cart->get_cart_contents_count();
	};

	// ?wc-ajax=add_to_cart, the path in the report.
	$die = static function () {
		return static function () {
			throw new RuntimeException( 'rbfw-test-die' );
		};
	};
	add_filter( 'wp_die_ajax_handler', $die );
	add_filter( 'wp_doing_ajax', '__return_true' );
	$ajax_add = static function () use ( $form, $product ) {
		$_POST               = $form();
		$_POST['product_id'] = $product;
		$_POST['quantity']   = 1;
		$_REQUEST            = $_POST;
		ob_start();
		try {
			WC_AJAX::add_to_cart();
		} catch ( RuntimeException $e ) {
			unset( $e ); // wp_send_json() ends the request.
		}
		$out      = (string) ob_get_clean();
		$_POST    = array();
		$_REQUEST = array();
		return array( WC()->cart->get_cart_contents_count(), $out );
	};

	// Direct WC()->cart->add_to_cart(): WooCommerce 11 runs no validation filter here.
	$direct = static function () use ( $form, $product ) {
		$_POST    = $form();
		$_REQUEST = $_POST;
		try {
			$key = WC()->cart->add_to_cart( $product, 1 );
		} catch ( Exception $e ) {
			$key = false;
		}
		$_POST    = array();
		$_REQUEST = array();
		return $key;
	};

	/* ── Published: unchanged ─────────────────────────────────────────── */
	$reset();
	$assert( 1 === $form_post(), 'Published item: Book Now adds it to the cart' );
	$reset();
	list( $count ) = $ajax_add();
	$assert( 1 === $count, 'Published item: wc-ajax add_to_cart adds it' );
	$assert( wc_get_product( $product )->is_purchasable(), 'Published item: backing product is purchasable' );

	/* ── Unpublished: refused on every path ───────────────────────────── */
	foreach ( array( 'draft', 'pending', 'private', 'trash' ) as $status ) {
		wp_update_post( array( 'ID' => $item, 'post_status' => $status ) );
		clean_post_cache( $item );
		$assert( 'publish' === get_post_status( $product ), "$status: the backing product itself is still published" );

		$reset();
		$assert( 0 === $form_post(), "$status: Book Now form post is refused" );
		$assert( false !== strpos( $errors(), 'not available' ), "$status: the visitor is told the item is not available" );

		$reset();
		list( $count, $out ) = $ajax_add();
		$assert( 0 === $count && false !== strpos( $out, '"error":true' ), "$status: wc-ajax add_to_cart is refused" );

		$reset();
		$assert( false === $direct(), "$status: a direct cart add is refused (product not purchasable)" );
		$assert( ! wc_get_product( $product )->is_purchasable(), "$status: backing product is not purchasable" );
	}

	/* ── Editors previewing a draft keep working ──────────────────────── */
	wp_update_post( array( 'ID' => $item, 'post_status' => 'draft' ) );
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	wp_set_current_user( (int) $admins[0] );
	$reset();
	$assert( 1 === $form_post(), 'Draft item: an administrator can still add it (preview)' );
	wp_set_current_user( 0 );

	/* ── Line added while public, item unpublished afterwards ─────────── */
	wp_update_post( array( 'ID' => $item, 'post_status' => 'publish' ) );
	clean_post_cache( $item );
	$reset();
	$assert( 1 === $form_post(), 'Cart holds the rental while it is published' );
	WC()->cart->set_session();
	wp_update_post( array( 'ID' => $item, 'post_status' => 'draft' ) );
	clean_post_cache( $item );
	wc_clear_notices();
	// A new request starts with an empty in-memory cart and restores it from the session.
	WC()->cart->set_cart_contents( array() );
	( new WC_Cart_Session( WC()->cart ) )->get_cart_from_session();
	$assert( 0 === WC()->cart->get_cart_contents_count(), 'Unpublishing the item drops the line when the cart is loaded again' );
	$assert( false !== strpos( $errors(), 'can no longer be purchased' ), '…and WooCommerce tells the customer why' );
	$assert( empty( WC()->session->get( 'cart' ) ), '…and the stored cart no longer holds it' );

	/* ── Ordinary products are untouched ──────────────────────────────── */
	$reset();
	$assert( false !== WC()->cart->add_to_cart( $plain, 1 ), 'A non-rental product is still purchasable' );

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
