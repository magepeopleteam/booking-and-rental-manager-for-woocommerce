<?php
/**
 * Item variations: how the booking form sets the quantity, deleting values, and the
 * Single Day shared-stock field (support ticket #8502, ruedasmiami.com).
 *
 * Run on a development site with WooCommerce and this plugin active:
 * wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/variation-quantity-mode.php
 *
 * The front-end stepper behaviour (rbfw_script.js / sd_script.js / md_script.js) is
 * driven by the data-qty-mode attribute checked here.
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

$created = array();
$make    = static function ( $type, $meta = array() ) use ( &$created ) {
	$id = wp_insert_post(
		array(
			'post_type'   => 'rbfw_item',
			'post_status' => 'publish',
			'post_title'  => 'Variation quantity mode test',
		)
	);
	update_post_meta( $id, 'rbfw_item_type', $type );
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	$created[] = $id;
	return $id;
};
$variations = array(
	array(
		'field_label' => 'Model',
		'field_id'    => 'rbfw_variation_id_0',
		'value'       => array(
			array( 'name' => 'Aventon Black', 'quantity' => '1' ),
			array( 'name' => 'Ride1Up (pedal-assist)', 'quantity' => '1' ),
		),
	),
);

try {
	/* ── Default mode per item ── */
	$sd = $make( 'bike_car_sd', array( 'rbfw_enable_variations' => 'yes', 'rbfw_variations_data' => $variations ) );
	$assert( 'variations' === rbfw_variation_qty_mode( $sd ), 'Single Day defaults to "Pick variations" (the pre-2.8.1 behaviour)' );

	$md_dropdown = $make( 'bike_car_md', array( 'rbfw_enable_md_type_item_qty' => 'yes' ) );
	$assert( 'quantity' === rbfw_variation_qty_mode( $md_dropdown ), 'Multiple Day with the Quantity dropdown keeps "Quantity first" (2.8.1)' );

	foreach ( array( 'dress', 'equipment', 'others' ) as $md_type ) {
		$id = $make( $md_type, array( 'rbfw_enable_md_type_item_qty' => 'yes' ) );
		$assert( 'quantity' === rbfw_variation_qty_mode( $id ), "{$md_type} with the Quantity dropdown keeps \"Quantity first\"" );
	}

	$md_plain = $make( 'bike_car_md' );
	$assert( 'variations' === rbfw_variation_qty_mode( $md_plain ), 'Multiple Day without the dropdown lets the steppers set the quantity' );

	/* ── Saved value wins, junk is ignored ── */
	update_post_meta( $sd, 'rbfw_variation_qty_mode', 'quantity' );
	$assert( 'quantity' === rbfw_variation_qty_mode( $sd ), 'a saved "quantity" overrides the Single Day default' );
	update_post_meta( $md_dropdown, 'rbfw_variation_qty_mode', 'variations' );
	$assert( 'variations' === rbfw_variation_qty_mode( $md_dropdown ), 'a saved "variations" overrides the Multiple Day default' );
	update_post_meta( $sd, 'rbfw_variation_qty_mode', '<script>' );
	$assert( 'variations' === rbfw_variation_qty_mode( $sd ), 'an unknown stored value falls back to the default' );
	delete_post_meta( $sd, 'rbfw_variation_qty_mode' );

	$filter = static function () {
		return 'nonsense';
	};
	add_filter( 'rbfw_variation_qty_mode', $filter );
	$assert( 'variations' === rbfw_variation_qty_mode( $md_dropdown ), 'the filter result is normalised to a known mode' );
	remove_filter( 'rbfw_variation_qty_mode', $filter );

	/* ── Saving the setting ── */
	$_POST = array( 'rbfw_variation_qty_mode' => 'quantity' );
	RBFW_Inventory::save_variation_qty_mode( $sd );
	$assert( 'quantity' === get_post_meta( $sd, 'rbfw_variation_qty_mode', true ), 'save stores a valid mode' );
	$_POST = array( 'rbfw_variation_qty_mode' => 'drop table' );
	RBFW_Inventory::save_variation_qty_mode( $sd );
	$assert( 'quantity' === get_post_meta( $sd, 'rbfw_variation_qty_mode', true ), 'save ignores an invalid mode' );
	$_POST = array();
	RBFW_Inventory::save_variation_qty_mode( $sd );
	$assert( 'quantity' === get_post_meta( $sd, 'rbfw_variation_qty_mode', true ), 'a form without the field leaves the saved mode alone' );
	delete_post_meta( $sd, 'rbfw_variation_qty_mode' );

	/* ── Booking-form markup the steppers read ── */
	$html = rbfw_render_sd_variation_field( $sd, $variations );
	$assert( false !== strpos( $html, 'data-qty-mode="variations"' ), 'the variations wrapper tells the script the mode' );
	$assert( false !== strpos( $html, 'data-locked-hint="Choose a quantity first"' ), 'the locked hint is translatable text with no direction' );
	$assert( false === strpos( $html, 'above' ), 'the hint no longer says "above" (Single Day puts Quantity below)' );
	update_post_meta( $sd, 'rbfw_variation_qty_mode', 'quantity' );
	$assert( false !== strpos( rbfw_render_sd_variation_field( $sd, $variations ), 'data-qty-mode="quantity"' ), 'a "Quantity first" item says so' );
	delete_post_meta( $sd, 'rbfw_variation_qty_mode' );

	/* ── Inventory editor ── */
	$renderer = ( new ReflectionClass( 'RBFW_Inventory' ) )->newInstanceWithoutConstructor();
	$render   = static function ( $method, $post_id ) use ( $renderer ) {
		ob_start();
		$renderer->$method( $post_id );
		return ob_get_clean();
	};

	$fresh = $make( 'bike_car_sd' );
	$assert( false !== strpos( $render( 'variation_settings', $fresh ), 'value="Red"' ), 'a brand-new item still starts with example values' );
	$emptied = $make( 'bike_car_sd', array( 'rbfw_enable_variations' => 'yes', 'rbfw_variations_data' => array() ) );
	$emptied_html = $render( 'variation_settings', $emptied );
	$assert( false === strpos( $emptied_html, 'value="Red"' ), 'an item whose values were all deleted does not get the examples back' );
	$assert( false !== strpos( $emptied_html, 'rbfw-var-add-form' ), 'the emptied list still offers "Add New Value"' );

	$mode_html = $render( 'variation_qty_mode_setting', $md_dropdown );
	$assert( false !== strpos( $mode_html, 'name="rbfw_variation_qty_mode"' ), 'the editor renders the quantity-mode setting' );
	$assert( 1 === preg_match( '/value="variations"\s+selected/', $mode_html ), 'the setting shows the saved/effective mode' );

	$timely = $make(
		'bike_car_sd',
		array(
			'manage_inventory_as_timely' => 'on',
			'rbfw_enable_variations'     => 'yes',
			'rbfw_variations_data'       => $variations,
		)
	);
	$stock_html = $render( 'stock_settings', $timely );
	$assert( 1 === preg_match( '/<input[^>]*id="rbfw_item_stock_quantity_timely"[^>]*>/', $stock_html, $timely_input ), 'the Single Day shared stock field renders' );
	$assert( false === strpos( $timely_input[0], 'disabled' ), 'shared stock stays editable with Item variation on (it was disabled while the save required it)' );
	$assert( false !== strpos( $timely_input[0], 'required' ), 'shared stock is required with Item variation on' );
	$assert( 1 === preg_match( '/<input[^>]*id="rbfw_item_stock_quantity"[^>]*>/', $stock_html, $md_input ) && false !== strpos( $md_input[0], 'disabled' ), 'the Multiple Day stock field is still disabled with variations on' );

	$pricing = new RBFW_Pricing();
	$errors  = $pricing->get_pricing_validation_errors(
		'bike_car_sd',
		array(
			'manage_inventory_as_timely'      => 'on',
			'rbfw_enable_time_picker'         => 'yes',
			'rbfw_item_stock_quantity_timely' => '3',
			'rbfw_bike_car_sd_data'           => array( array( 'rent_type' => 'Up to 2 hours', 'price' => '40', 'duration' => '2', 'd_type' => 'Hours' ) ),
		)
	);
	$assert( empty( $errors ), 'a filled shared stock passes the publish check with variations on' );
} finally {
	$_POST = array();
	foreach ( $created as $id ) {
		wp_delete_post( $id, true );
	}
}

WP_CLI::success( "All {$checks} checks passed." );
