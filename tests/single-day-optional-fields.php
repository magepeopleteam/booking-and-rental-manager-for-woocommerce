<?php
/** wp eval-file /absolute/path/to/tests/single-day-optional-fields.php */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
$checks = 0;
$failure = null;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	++$checks;
};
$saved_post = $GLOBALS['post'] ?? null;
$saved_rbfw = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();
$template = dirname( __DIR__ ) . '/templates/forms/single-day-registration.php';
set_error_handler( static function ( $severity, $message, $file, $line ) use ( $template ) {
	if ( ( error_reporting() & $severity ) && $template === $file ) {
		throw new ErrorException( $message, 0, $severity, $file, $line );
	}
	return false;
} );
$id = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'draft', 'post_title' => 'Single day optional fields regression fixture' ) );
try {
	update_post_meta( $id, 'manage_inventory_as_timely', 'on' );
	update_post_meta( $id, 'enable_specific_duration', 'off' );
	update_post_meta( $id, 'rbfw_item_stock_quantity_timely', 1 );
	$base = array( 'rent_type' => '2 Hours', 'qty' => 1, 'price' => 20, 'duration' => 2, 'd_type' => 'Hours' );
	$variants = array(
		'missing' => array(),
		'null' => array( 'short_desc' => null, 'start_time' => null, 'end_time' => null ),
		'empty' => array( 'short_desc' => '', 'start_time' => '', 'end_time' => '' ),
		'populated' => array( 'short_desc' => 'Use "helmet" & lock', 'start_time' => '09:00', 'end_time' => '11:00' ),
	);
	foreach ( array( 'bike_car_sd', 'appointment' ) as $type ) {
		update_post_meta( $id, 'rbfw_item_type', $type );
		foreach ( $variants as $name => $optional ) {
			update_post_meta( $id, 'rbfw_bike_car_sd_data', array( $base + $optional ) );
			$level = ob_get_level();
			try {
				$html = rbfw_add_to_cart_shortcode_func( array( 'id' => $id ) );
			} finally {
				while ( ob_get_level() > $level ) { ob_end_clean(); }
			}
			$internal_errors = libxml_use_internal_errors( true );
			try {
				$dom = new DOMDocument();
				$dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
				$rows = ( new DOMXPath( $dom ) )->query( '//div[contains(concat(" ", normalize-space(@class), " "), " single-type-timely ")]' );
				if ( 'appointment' === $type ) {
					$assert( 0 === $rows->length && false !== strpos( $html, 'id="rbfw-bikecarsd-calendar"' ), "$type/$name: appointment retains its calendar layout" );
					continue;
				}
				$assert( 1 === $rows->length, "$type/$name: duration option rendered" );
				$row = $rows->item( 0 );
				foreach ( array( 'title' => 'short_desc', 'data-start_time' => 'start_time', 'data-end_time' => 'end_time' ) as $attribute => $key ) {
					$assert( ( $optional[ $key ] ?? '' ) === $row->getAttribute( $attribute ), "$type/$name: $key safely defaults or retains its value" );
				}
				$assert( '1' === $row->getAttribute( 'data-available_quantity' ) && '20' === $row->getAttribute( 'data-price' ), "$type/$name: stock and price preserved" );
			} finally {
				libxml_clear_errors();
				libxml_use_internal_errors( $internal_errors );
			}
		}
	}
	// The reported page-created messages should occur only when a page is absent.
	$pages = array();
	foreach ( array( 'rent-list', 'rent-grid', 'search-item-list', 'booking-search', 'rent-types' ) as $slug ) {
		$page = get_page_by_path( $slug );
		$assert( $page instanceof WP_Post, "$slug: required page exists" );
		$pages[ $slug ] = $page->to_array();
	}
	rbfw_page_create();
	rbfw_page_create();
	foreach ( $pages as $slug => $before ) {
		$assert( $before === get_page_by_path( $slug )->to_array(), "$slug: repeated initialization preserves the page and its content" );
	}
} catch ( Throwable $error ) {
	$failure = $error;
} finally {
	$product = (int) get_post_meta( $id, 'link_wc_product', true );
	wp_delete_post( $id, true );
	if ( $product && $product !== $id ) { wp_delete_post( $product, true ); }
	$GLOBALS['post'] = $saved_post;
	$GLOBALS['rbfw'] = $saved_rbfw;
	restore_error_handler();
}
if ( $failure ) { WP_CLI::error( $failure->getMessage() ); }
WP_CLI::success( "$checks single-day optional-field and page-idempotency checks passed." );
