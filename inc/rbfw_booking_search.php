<?php
/**
 * [rbfw_booking_search] — date-range / location / rental-type search with
 * multi-item quick booking.
 *
 * Flow: customer picks a date range (+ optional location / rental type),
 * results render as cards with live price + remaining stock for those exact
 * dates, and each card can be added straight to the WooCommerce cart with a
 * quantity — several different rentals stack in one cart and check out as a
 * single order.
 *
 * Quick-add goes through WC()->cart->add_to_cart(), so every existing gate
 * (duplicate-cart rule, rbfw_check_rental_availability, location stock,
 * fees / security deposit / discount / location charge) runs unchanged.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_shortcode( 'rbfw_booking_search', 'rbfw_booking_search_shortcode' );
add_action( 'wp_ajax_rbfw_booking_search_results', 'rbfw_booking_search_results_ajax' );
add_action( 'wp_ajax_nopriv_rbfw_booking_search_results', 'rbfw_booking_search_results_ajax' );
add_action( 'wp_ajax_rbfw_booking_quick_add', 'rbfw_booking_quick_add_ajax' );
add_action( 'wp_ajax_nopriv_rbfw_booking_quick_add', 'rbfw_booking_quick_add_ajax' );
add_action( 'wp_ajax_rbfw_booking_search_item_form', 'rbfw_booking_search_item_form_ajax' );
add_action( 'wp_ajax_nopriv_rbfw_booking_search_item_form', 'rbfw_booking_search_item_form_ajax' );
add_action( 'wp_ajax_rbfw_booking_modal_add', 'rbfw_booking_modal_add_ajax' );
add_action( 'wp_ajax_nopriv_rbfw_booking_modal_add', 'rbfw_booking_modal_add_ajax' );
add_action( 'wp_ajax_rbfw_booking_checkout_form', 'rbfw_booking_checkout_form_ajax' );
add_action( 'wp_ajax_nopriv_rbfw_booking_checkout_form', 'rbfw_booking_checkout_form_ajax' );
add_action( 'wp_ajax_rbfw_booking_empty_cart', 'rbfw_booking_empty_cart_ajax' );
add_action( 'wp_ajax_nopriv_rbfw_booking_empty_cart', 'rbfw_booking_empty_cart_ajax' );
add_action( 'wp_ajax_rbfw_booking_bar_details', 'rbfw_booking_bar_details_ajax' );
add_action( 'wp_ajax_nopriv_rbfw_booking_bar_details', 'rbfw_booking_bar_details_ajax' );

/**
 * Rental types that support instant add-to-cart from a plain date range.
 * The other types need a time slot / room / sub-item choice, so their cards
 * link to the item page with the searched dates prefilled instead.
 */
function rbfw_booking_search_quick_types() {
	return apply_filters( 'rbfw_booking_search_quick_types', array( 'bike_car_md', 'equipment', 'dress', 'others' ) );
}

/**
 * Rental Type dropdown source — fully dynamic, same data the plugin's own
 * search uses: the rent-type categories admins assign to items
 * (rbfw_categories meta), displayed with the exact casing of the matching
 * rbfw_item_caregory term. Returns [stored value => display label].
 */
function rbfw_booking_search_rent_types() {
	$stored = function_exists( 'get_rbfw_post_categories_from_meta' ) ? get_rbfw_post_categories_from_meta() : array();
	$stored = is_array( $stored ) ? $stored : array();

	$term_map = array();
	$terms    = get_terms( array( 'taxonomy' => 'rbfw_item_caregory', 'hide_empty' => false ) );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$term_map[ strtolower( $term->name ) ] = $term->name;
		}
	}

	$out = array();
	foreach ( $stored as $value ) {
		$label         = isset( $term_map[ strtolower( (string) $value ) ] ) ? $term_map[ strtolower( (string) $value ) ] : $value;
		$out[ $value ] = $label;
	}

	return $out;
}

/** Card badge: the item's own rental-type categories, falling back to a readable item-type name. */
function rbfw_booking_search_item_type_label( $rbfw_id, $item_type ) {
	$cats = get_post_meta( $rbfw_id, 'rbfw_categories', true );
	if ( is_array( $cats ) ) {
		$cats = implode( ', ', array_filter( array_map( 'trim', $cats ) ) );
	}
	if ( is_string( $cats ) && '' !== trim( $cats ) ) {
		return trim( $cats );
	}

	return ucwords( str_replace( '_', ' ', (string) $item_type ) );
}

/**
 * Does the d-m-Y / Y-m-d range hit an off day for this item?
 * Endpoints (pickup / drop-off) are always enforced; interior days only when
 * the per-item "Block Booking If Date Range Contains Off Days" toggle is on
 * (meta empty = on, mirroring rbfw_block_offday_range_booking()).
 *
 * @return bool true when the range is blocked by off day/date rules.
 */
function rbfw_booking_search_range_blocked( $rbfw_id, $start_ts, $end_ts ) {
	$off_days_raw = get_post_meta( $rbfw_id, 'rbfw_off_days', true );
	$off_days     = array();
	if ( is_array( $off_days_raw ) ) {
		$off_days = array_map( 'strtolower', array_map( 'trim', $off_days_raw ) );
	} elseif ( is_string( $off_days_raw ) && '' !== $off_days_raw ) {
		$off_days = array_map( 'strtolower', array_map( 'trim', explode( ',', $off_days_raw ) ) );
	}

	$off_dates_raw = get_post_meta( $rbfw_id, 'rbfw_off_dates', true );
	$off_dates_raw = $off_dates_raw ? maybe_unserialize( $off_dates_raw ) : array();
	if ( is_string( $off_dates_raw ) ) {
		$off_dates_raw = array_map( 'trim', explode( ',', $off_dates_raw ) );
	}
	$off_dates = array();
	foreach ( (array) $off_dates_raw as $od ) {
		$od_ts = is_string( $od ) ? strtotime( str_replace( '/', '-', $od ) ) : false;
		if ( $od_ts ) {
			$off_dates[ gmdate( 'Y-m-d', $od_ts ) ] = true;
		}
	}

	if ( empty( $off_days ) && empty( $off_dates ) ) {
		return false;
	}

	$interior_blocking = function_exists( 'rbfw_block_offday_range_booking' )
		? ( rbfw_block_offday_range_booking( $rbfw_id ) !== 'off' )
		: true;

	$guard = 0;
	for ( $ts = $start_ts; $ts <= $end_ts && $guard < 1100; $ts += DAY_IN_SECONDS, $guard++ ) {
		$is_endpoint = ( $ts === $start_ts || $ts + DAY_IN_SECONDS > $end_ts );
		if ( ! $is_endpoint && ! $interior_blocking ) {
			continue;
		}
		if ( in_array( strtolower( gmdate( 'l', $ts ) ), $off_days, true ) || isset( $off_dates[ gmdate( 'Y-m-d', $ts ) ] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Remaining bookable units of an item for a date range (peak-day model).
 * Location-aware when a configured location slug is passed.
 *
 * @return int|null Remaining units, or null when the item has no managed stock.
 */
function rbfw_booking_search_remaining_stock( $rbfw_id, $start, $end, $location_slug = '' ) {
	if ( '' !== $location_slug && function_exists( 'rbfw_get_location_inventory' ) ) {
		$conf = rbfw_get_location_inventory( $rbfw_id );
		if ( ! empty( $conf ) && isset( $conf[ $location_slug ] ) ) {
			return rbfw_location_remaining_stock( $rbfw_id, $location_slug, $start, $end );
		}
	}

	if ( get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) === 'yes' && function_exists( 'rbfw_get_variations_stock' ) ) {
		$stock = (int) rbfw_get_variations_stock( $rbfw_id );
	} else {
		$stock_meta = get_post_meta( $rbfw_id, 'rbfw_item_stock_quantity', true );
		if ( '' === $stock_meta || null === $stock_meta ) {
			return null; // stock not managed for this item
		}
		$stock = (int) $stock_meta;
	}

	$booked = function_exists( 'rbfw_count_overlapping_booked_qty' )
		? (int) rbfw_count_overlapping_booked_qty( $rbfw_id, $start . ' 00:00', $end . ' 23:59' )
		: 0;

	return max( 0, $stock - $booked );
}

/** Locations dropdown source: [slug => name]. */
function rbfw_booking_search_locations() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'rbfw_item_location',
			'hide_empty' => false,
		)
	);
	$out   = array();
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$out[ $term->slug ] = $term->name;
		}
	}

	return $out;
}

/** Does this item serve the given location (new inventory, taxonomy, or legacy pickup data)? */
function rbfw_booking_search_item_in_location( $rbfw_id, $slug, $name ) {
	if ( function_exists( 'rbfw_get_location_inventory' ) ) {
		$conf = rbfw_get_location_inventory( $rbfw_id );
		if ( ! empty( $conf ) ) {
			return isset( $conf[ $slug ] );
		}
	}

	if ( has_term( $slug, 'rbfw_item_location', $rbfw_id ) ) {
		return true;
	}

	$pickup_data = get_post_meta( $rbfw_id, 'rbfw_pickup_data', true );
	$pickup_data = $pickup_data ? maybe_unserialize( $pickup_data ) : array();
	foreach ( (array) $pickup_data as $row ) {
		$loc = is_array( $row ) ? ( $row['location'] ?? ( $row['name'] ?? '' ) ) : $row;
		if ( is_string( $loc ) && '' !== $loc && ( sanitize_title( $loc ) === $slug || $loc === $name ) ) {
			return true;
		}
	}

	return false;
}

/******************************
 * Shortcode
 ******************************/
function rbfw_booking_search_shortcode( $atts = null ) {
	$a = shortcode_atts(
		array(
			'type'          => '',      // csv of rental-type category names to search; empty = all
			'types'         => '',      // back-compat alias of "type"
			'location'      => '',      // fixed location slug (hides the dropdown)
			'hide_location' => 'no',
			'hide_type'     => 'no',
			'columns'       => '3',
			'show'          => '24',    // max results
			'button_text'   => '',
			'show_stock'    => 'yes',
			'show_qty'      => 'yes',
			'style'         => 'grid',  // initial results layout: grid | list
		),
		$atts
	);

	$rent_types    = rbfw_booking_search_rent_types();
	$type_csv      = '' !== trim( (string) $a['type'] ) ? $a['type'] : $a['types'];
	$allowed_types = array_filter( array_map( 'trim', explode( ',', (string) $type_csv ) ) );
	if ( ! empty( $allowed_types ) ) {
		// Case-insensitive match against the site's real rent-type categories.
		$lower_map     = array_change_key_case( array_combine( array_keys( $rent_types ), array_keys( $rent_types ) ), CASE_LOWER );
		$allowed_types = array_values(
			array_unique(
				array_filter(
					array_map(
						function ( $t ) use ( $lower_map ) {
							return isset( $lower_map[ strtolower( $t ) ] ) ? $lower_map[ strtolower( $t ) ] : '';
						},
						$allowed_types
					)
				)
			)
		);
	}
	$locations     = rbfw_booking_search_locations();
	$fixed_loc     = sanitize_title( $a['location'] );
	$columns       = max( 1, min( 4, absint( $a['columns'] ) ) );
	$button_text   = '' !== trim( $a['button_text'] )
		? sanitize_text_field( $a['button_text'] )
		: __( 'Add to booking', 'booking-and-rental-manager-for-woocommerce' );

	$cart_count = 0;
	$cart_total = '';
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$cart_count = WC()->cart->get_cart_contents_count();
		$cart_total = WC()->cart->get_cart_total();
	}

	$dropdown_types = empty( $allowed_types ) ? array_keys( $rent_types ) : $allowed_types;

	ob_start();
	?>
	<div class="rbfw_bsearch_wrap"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		data-show="<?php echo esc_attr( max( 1, absint( $a['show'] ) ) ); ?>"
		data-types="<?php echo esc_attr( implode( ',', $allowed_types ) ); ?>"
		data-location="<?php echo esc_attr( $fixed_loc ); ?>"
		data-show-stock="<?php echo esc_attr( $a['show_stock'] ); ?>"
		data-show-qty="<?php echo esc_attr( $a['show_qty'] ); ?>"
		data-button-text="<?php echo esc_attr( $button_text ); ?>"
		data-style="<?php echo esc_attr( 'list' === $a['style'] ? 'list' : 'grid' ); ?>">

		<section class="rbfw_rent_item_search_elementor_section rbfw_bsearch_bar">
			<div class="rbfw_rent_item_search_elementor_container">
				<form class="rbfw_search_form_new rbfw_bsearch_form" onsubmit="return false;">
					<div class="rbfw_search_container">
						<div class="rbfw_search_item rbfw_bsearch_field">
							<label class="rbfw_bsearch_label"><?php esc_html_e( 'Pickup date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<input type="text" class="rbfw_bsearch_start" readonly placeholder="<?php echo esc_attr__( 'Select date', 'booking-and-rental-manager-for-woocommerce' ); ?>">
						</div>
						<div class="rbfw_search_item rbfw_bsearch_field">
							<label class="rbfw_bsearch_label"><?php esc_html_e( 'Drop-off date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
							<input type="text" class="rbfw_bsearch_end" readonly placeholder="<?php echo esc_attr__( 'Select date', 'booking-and-rental-manager-for-woocommerce' ); ?>">
						</div>
						<?php if ( 'yes' !== $a['hide_location'] && '' === $fixed_loc && ! empty( $locations ) ) : ?>
							<div class="rbfw_search_item rbfw_bsearch_field">
								<label class="rbfw_bsearch_label"><?php esc_html_e( 'Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<select class="rbfw_bsearch_location rbfw_rent_item_search_type_location">
									<option value=""><?php esc_html_e( 'Any location', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
									<?php foreach ( $locations as $slug => $name ) : ?>
										<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>
						<?php if ( 'yes' !== $a['hide_type'] && count( $dropdown_types ) > 1 ) : ?>
							<div class="rbfw_search_item rbfw_bsearch_field">
								<label class="rbfw_bsearch_label"><?php esc_html_e( 'Rental Type', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<select class="rbfw_bsearch_type rbfw_rent_item_search_type_location">
									<option value=""><?php esc_html_e( 'All types', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
									<?php foreach ( $dropdown_types as $tkey ) : ?>
										<option value="<?php echo esc_attr( $tkey ); ?>"><?php echo esc_html( isset( $rent_types[ $tkey ] ) ? $rent_types[ $tkey ] : $tkey ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>
						<div class="rbfw_search_item rbfw_bsearch_field rbfw_bsearch_btn_field">
							<label class="rbfw_bsearch_label">&nbsp;</label>
							<button type="button" class="rbfw_rent_item_search_submit rbfw_bsearch_go"><?php esc_html_e( 'Search', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</div>
					</div>
				</form>
			</div>
		</section>

		<div class="rbfw_bsearch_results" aria-live="polite"></div>

		<div class="rbfw_bsearch_modal" hidden>
			<div class="rbfw_bsearch_modal_overlay"></div>
			<div class="rbfw_bsearch_modal_dialog" role="dialog" aria-modal="true">
				<div class="rbfw_bsearch_modal_head">
					<h3 class="rbfw_bsearch_modal_title"></h3>
					<button type="button" class="rbfw_bsearch_modal_close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
				</div>
				<div class="rbfw_bsearch_modal_msg" role="alert"></div>
				<div class="rbfw_bsearch_modal_body"></div>
			</div>
		</div>

		<div class="rbfw_bsearch_bar_float" style="<?php echo $cart_count > 0 ? '' : 'display:none;'; ?>">
			<div class="rbfw_bsearch_bar_details" hidden>
				<div class="rbfw_bsearch_bar_details_head">
					<span><?php esc_html_e( 'Your Booking', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					<button type="button" class="rbfw_bsearch_bar_details_close" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button>
				</div>
				<div class="rbfw_bsearch_bar_details_body"></div>
			</div>
			<div class="rbfw_bsearch_bar_info">
				<span class="rbfw_bsearch_bar_count"><?php echo esc_html( $cart_count ); ?></span>
				<span class="rbfw_bsearch_bar_label"><?php esc_html_e( 'item(s) in your booking', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				<span class="rbfw_bsearch_bar_total"><?php echo wp_kses_post( $cart_total ); ?></span>
			</div>
			<div class="rbfw_bsearch_bar_actions">
				<button type="button" class="rbfw_bsearch_bar_view" title="<?php esc_attr_e( 'View booking details', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'View booking details', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-expanded="false">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
				</button>
				<button type="button" class="rbfw_bsearch_bar_empty" title="<?php esc_attr_e( 'Empty booking', 'booking-and-rental-manager-for-woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Empty booking', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M5 7l1 13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1l1-13M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<a class="rbfw_bsearch_bar_cart" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#' ); ?>"><?php esc_html_e( 'View Cart', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
				<a class="rbfw_bsearch_bar_checkout" href="<?php echo esc_url( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '#' ); ?>"><?php esc_html_e( 'Checkout', 'booking-and-rental-manager-for-woocommerce' ); ?> &rarr;</a>
			</div>
		</div>
	</div>
	<?php

	return ob_get_clean();
}

/******************************
 * AJAX: search results
 ******************************/
function rbfw_booking_search_results_ajax() {
	check_ajax_referer( 'rbfw_booking_search_action', 'nonce' );

	$start    = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
	$end      = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : '';
	$location = isset( $_POST['location'] ) ? sanitize_title( wp_unslash( $_POST['location'] ) ) : '';
	$type     = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
	$types    = isset( $_POST['types'] ) ? sanitize_text_field( wp_unslash( $_POST['types'] ) ) : '';
	$show     = isset( $_POST['show'] ) ? max( 1, absint( $_POST['show'] ) ) : 24;
	$show     = min( $show, 60 );

	$start_ts = strtotime( $start );
	$end_ts   = strtotime( $end );
	if ( ! $start_ts || ! $end_ts || $end_ts < $start_ts ) {
		wp_send_json_error( array( 'message' => __( 'Please select a valid date range.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}
	$start = gmdate( 'Y-m-d', $start_ts );
	$end   = gmdate( 'Y-m-d', $end_ts );

	/*
	 * Rental Type filter = the dynamic rent-type categories (rbfw_categories
	 * meta), matched with the same clause builder the plugin's own search
	 * uses. A specific dropdown pick wins; otherwise the shortcode's type
	 * restriction (csv) applies; otherwise no category filter at all.
	 */
	$allowed_types = array_filter( array_map( 'trim', explode( ',', $types ) ) );
	if ( '' !== $type && ( empty( $allowed_types ) || in_array( strtolower( $type ), array_map( 'strtolower', $allowed_types ), true ) ) ) {
		$filter_categories = array( $type );
	} else {
		$filter_categories = $allowed_types;
	}
	if ( ! empty( $filter_categories ) && function_exists( 'rbfw_sanitize_rent_type_categories' ) ) {
		$filter_categories = rbfw_sanitize_rent_type_categories( $filter_categories );
	}

	$meta_query = array( 'relation' => 'AND' );
	if ( ! empty( $filter_categories ) && function_exists( 'rbfw_build_categories_meta_clause' ) ) {
		$category_clause = rbfw_build_categories_meta_clause( $filter_categories );
		if ( ! empty( $category_clause ) ) {
			$meta_query[] = $category_clause;
		}
	}

	$loc_term = '' !== $location ? get_term_by( 'slug', $location, 'rbfw_item_location' ) : false;
	$loc_name = $loc_term && ! is_wp_error( $loc_term ) ? $loc_term->name : '';

	$query = new WP_Query(
		array(
			'post_type'      => 'rbfw_item',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => $meta_query,
		)
	);

	$show_stock  = isset( $_POST['show_stock'] ) && 'no' === $_POST['show_stock'] ? false : true;
	$show_qty    = isset( $_POST['show_qty'] ) && 'no' === $_POST['show_qty'] ? false : true;
	$button_text = isset( $_POST['button_text'] ) && '' !== trim( sanitize_text_field( wp_unslash( $_POST['button_text'] ) ) )
		? sanitize_text_field( wp_unslash( $_POST['button_text'] ) )
		: __( 'Add to booking', 'booking-and-rental-manager-for-woocommerce' );

	$quick_types = rbfw_booking_search_quick_types();
	$total_days  = max( 1, (int) round( ( $end_ts - $start_ts ) / DAY_IN_SECONDS ) );
	$cards       = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() && count( $cards ) < $show ) {
			$query->the_post();
			$rbfw_id   = get_the_ID();
			$item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true );

			if ( '' !== $location && ! rbfw_booking_search_item_in_location( $rbfw_id, $location, $loc_name ) ) {
				continue;
			}
			if ( rbfw_booking_search_range_blocked( $rbfw_id, $start_ts, $end_ts ) ) {
				continue;
			}

			$is_quick  = in_array( $item_type, $quick_types, true );
			$remaining = $is_quick ? rbfw_booking_search_remaining_stock( $rbfw_id, $start, $end, $location ) : null;

			/* Price for the searched range (quick types share the md engine). */
			$price_html = '';
			$price_note = '';
			if ( $is_quick && function_exists( 'rbfw_md_duration_price_calculation' ) ) {
				$info = rbfw_md_duration_price_calculation(
					$rbfw_id,
					gmdate( 'Y-m-d H:i', strtotime( $start . ' 12:00 am' ) ),
					gmdate( 'Y-m-d H:i', strtotime( $end . ' 12:00 am' ) ),
					$start,
					$end,
					'12:00 am',
					'12:00 am',
					'off'
				);
				$range_price = is_array( $info ) && isset( $info['duration_price'] ) ? (float) $info['duration_price'] : 0;
				if ( $range_price > 0 ) {
					$price_html = wc_price( $range_price );
					/* translators: %d: number of days */
					$price_note = sprintf( _n( 'total for %d day', 'total for %d days', $total_days, 'booking-and-rental-manager-for-woocommerce' ), $total_days );
				}
			}
			if ( '' === $price_html ) {
				$daily = (float) get_post_meta( $rbfw_id, 'rbfw_daily_rate', true );
				if ( $daily > 0 ) {
					$price_html = wc_price( $daily );
					$price_note = __( 'per day', 'booking-and-rental-manager-for-woocommerce' );
				}
			}

			/* Location surcharge preview (charged as fee at cart level). */
			$loc_charge = 0;
			if ( '' !== $location && function_exists( 'rbfw_get_location_inventory' ) ) {
				$conf = rbfw_get_location_inventory( $rbfw_id );
				if ( isset( $conf[ $location ]['price'] ) ) {
					$loc_charge = (float) $conf[ $location ]['price'];
				}
			}

			$qty_enabled = ( get_post_meta( $rbfw_id, 'rbfw_enable_md_type_item_qty', true ) === 'yes' ) && $show_qty;

			$excerpt = get_post_field( 'post_excerpt', $rbfw_id );
			if ( '' === trim( (string) $excerpt ) ) {
				$excerpt = get_post_field( 'post_content', $rbfw_id );
			}
			$excerpt = wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) $excerpt ) ), 24 );

			$cards[] = array(
				'id'          => $rbfw_id,
				'title'       => get_the_title( $rbfw_id ),
				'permalink'   => get_permalink( $rbfw_id ),
				'thumb'       => get_the_post_thumbnail_url( $rbfw_id, 'medium' ),
				'type_label'  => rbfw_booking_search_item_type_label( $rbfw_id, $item_type ),
				'is_quick'    => $is_quick,
				'remaining'   => $remaining,
				'price_html'  => $price_html,
				'price_note'  => $price_note,
				'loc_charge'  => $loc_charge,
				'qty_enabled' => $qty_enabled,
				'excerpt'     => $excerpt,
			);
		}
	}
	wp_reset_postdata();

	$style = isset( $_POST['style'] ) && 'list' === $_POST['style'] ? 'list' : 'grid';
	$html  = rbfw_booking_search_render_cards( $cards, $start, $end, $location, $button_text, $show_stock, $style );

	wp_send_json_success(
		array(
			'html'  => $html,
			'count' => count( $cards ),
		)
	);
}

/** Render the result card grid/list (server-side, fully escaped). */
function rbfw_booking_search_render_cards( $cards, $start, $end, $location, $button_text, $show_stock, $style = 'grid' ) {
	if ( empty( $cards ) ) {
		return '<div class="rbfw_bsearch_empty">'
			. esc_html__( 'Sorry, nothing is available for the selected dates. Please try different dates or another location.', 'booking-and-rental-manager-for-woocommerce' )
			. '</div>';
	}

	$is_list = ( 'list' === $style );

	ob_start();
	?>
	<div class="rbfw_bsearch_results_head">
		<span class="rbfw_bsearch_results_count">
			<?php
			/* translators: %d: number of results */
			echo esc_html( sprintf( _n( '%d rental available', '%d rentals available', count( $cards ), 'booking-and-rental-manager-for-woocommerce' ), count( $cards ) ) );
			?>
		</span>
		<span class="rbfw_bsearch_viewtoggle" role="group" aria-label="<?php esc_attr_e( 'Results layout', 'booking-and-rental-manager-for-woocommerce' ); ?>">
			<button type="button" class="rbfw_bsearch_view_grid_btn<?php echo $is_list ? '' : ' rbfw_bsearch_viewtoggle_active'; ?>" title="<?php esc_attr_e( 'Grid view', 'booking-and-rental-manager-for-woocommerce' ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z" fill="currentColor"/></svg>
			</button>
			<button type="button" class="rbfw_bsearch_view_list_btn<?php echo $is_list ? ' rbfw_bsearch_viewtoggle_active' : ''; ?>" title="<?php esc_attr_e( 'List view', 'booking-and-rental-manager-for-woocommerce' ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 5h16v2.6H4V5zm0 5.7h16v2.6H4v-2.6zm0 5.7h16V19H4v-2.6z" fill="currentColor"/></svg>
			</button>
		</span>
	</div>
	<?php
	$head = ob_get_clean();
	ob_start();
	foreach ( $cards as $card ) {
		$sold_out = ( null !== $card['remaining'] && (int) $card['remaining'] <= 0 );
		$max_qty  = null !== $card['remaining'] ? max( 0, (int) $card['remaining'] ) : 10;

		$view_url = add_query_arg(
			array(
				'rbfw_start_date' => rawurlencode( $start ),
				'rbfw_end_date'   => rawurlencode( $end ),
			),
			$card['permalink']
		);
		?>
		<div class="rbfw_bsearch_card<?php echo $sold_out ? ' rbfw_bsearch_soldout' : ''; ?>" data-item="<?php echo esc_attr( $card['id'] ); ?>" data-max="<?php echo esc_attr( $max_qty ); ?>">
			<a class="rbfw_bsearch_thumb" href="<?php echo esc_url( $view_url ); ?>">
				<?php if ( $card['thumb'] ) : ?>
					<img src="<?php echo esc_url( $card['thumb'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" loading="lazy">
				<?php else : ?>
					<span class="rbfw_bsearch_thumb_ph"></span>
				<?php endif; ?>
				<span class="rbfw_bsearch_badge"><?php echo esc_html( $card['type_label'] ); ?></span>
			</a>
			<div class="rbfw_bsearch_body">
				<h4 class="rbfw_bsearch_title"><a href="<?php echo esc_url( $view_url ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h4>

				<div class="rbfw_bsearch_meta">
					<?php if ( $show_stock && null !== $card['remaining'] ) : ?>
						<?php if ( $sold_out ) : ?>
							<span class="rbfw_bsearch_stock rbfw_bsearch_stock_out"><?php esc_html_e( 'Sold out for these dates', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						<?php else : ?>
							<span class="rbfw_bsearch_stock rbfw_bsearch_stock_ok">
								<?php
								/* translators: %d: units available */
								echo esc_html( sprintf( _n( '%d unit available', '%d units available', (int) $card['remaining'], 'booking-and-rental-manager-for-woocommerce' ), (int) $card['remaining'] ) );
								?>
							</span>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<?php if ( '' !== trim( (string) $card['excerpt'] ) ) : ?>
					<p class="rbfw_bsearch_desc"><?php echo esc_html( $card['excerpt'] ); ?></p>
				<?php endif; ?>

				<div class="rbfw_bsearch_price">
					<?php if ( '' !== $card['price_html'] ) : ?>
						<span class="rbfw_bsearch_amount"><?php echo wp_kses_post( $card['price_html'] ); ?></span>
						<small><?php echo esc_html( $card['price_note'] ); ?></small>
					<?php endif; ?>
					<?php if ( $card['loc_charge'] > 0 ) : ?>
						<small class="rbfw_bsearch_loc_fee">+ <?php echo wp_kses_post( wc_price( $card['loc_charge'] ) ); ?> <?php esc_html_e( 'location charge', 'booking-and-rental-manager-for-woocommerce' ); ?></small>
					<?php endif; ?>
				</div>

				<?php if ( $card['is_quick'] && ! $sold_out ) : ?>
					<div class="rbfw_bsearch_actions">
						<?php if ( $card['qty_enabled'] && $max_qty > 1 ) : ?>
							<div class="rbfw_bsearch_qty">
								<button type="button" class="rbfw_bsearch_qty_dec" aria-label="<?php esc_attr_e( 'Decrease quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>">&minus;</button>
								<input type="number" class="rbfw_bsearch_qty_input" value="1" min="1" max="<?php echo esc_attr( $max_qty ); ?>" inputmode="numeric">
								<button type="button" class="rbfw_bsearch_qty_inc" aria-label="<?php esc_attr_e( 'Increase quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>">+</button>
							</div>
						<?php endif; ?>
						<button type="button" class="rbfw_bsearch_add"
							data-start="<?php echo esc_attr( $start ); ?>"
							data-end="<?php echo esc_attr( $end ); ?>"
							data-location="<?php echo esc_attr( $location ); ?>">
							<?php echo esc_html( $button_text ); ?>
						</button>
					</div>
				<?php elseif ( ! $card['is_quick'] ) : ?>
					<div class="rbfw_bsearch_actions">
						<a class="rbfw_bsearch_view rbfw_bsearch_openmodal" href="<?php echo esc_url( $view_url ); ?>"
							data-item="<?php echo esc_attr( $card['id'] ); ?>"
							data-start="<?php echo esc_attr( $start ); ?>"
							data-end="<?php echo esc_attr( $end ); ?>"
							data-location="<?php echo esc_attr( $location ); ?>"><?php esc_html_e( 'Select options', 'booking-and-rental-manager-for-woocommerce' ); ?> &rarr;</a>
					</div>
				<?php endif; ?>

				<div class="rbfw_bsearch_msg" role="alert"></div>
			</div>
		</div>
		<?php
	}

	return $head
		. '<div class="rbfw_bsearch_grid' . ( $is_list ? ' rbfw_bsearch_view_list' : '' ) . '">'
		. ob_get_clean()
		. '</div>';
}

/******************************
 * AJAX: quick add to cart
 ******************************/
function rbfw_booking_quick_add_ajax() {
	check_ajax_referer( 'rbfw_booking_search_action', 'nonce' );

	$rbfw_id  = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
	$qty      = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
	$start    = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
	$end      = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : '';
	$location = isset( $_POST['location'] ) ? sanitize_title( wp_unslash( $_POST['location'] ) ) : '';

	global $rbfw;
	if ( ! $rbfw_id || get_post_type( $rbfw_id ) !== $rbfw->get_cpt_name() ) {
		wp_send_json_error( array( 'message' => __( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	$item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true );
	if ( ! in_array( $item_type, rbfw_booking_search_quick_types(), true ) ) {
		wp_send_json_error( array( 'message' => __( 'This rental needs extra options — please book it from its page.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	$start_ts = strtotime( $start );
	$end_ts   = strtotime( $end );
	if ( ! $start_ts || ! $end_ts || $end_ts < $start_ts ) {
		wp_send_json_error( array( 'message' => __( 'Please select a valid date range.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}
	$start = gmdate( 'Y-m-d', $start_ts );
	$end   = gmdate( 'Y-m-d', $end_ts );

	if ( rbfw_booking_search_range_blocked( $rbfw_id, $start_ts, $end_ts ) ) {
		wp_send_json_error( array( 'message' => __( 'The selected dates include an off day for this rental.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'The cart is unavailable right now. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	/* Guests need a persistent session for the cart to survive the redirect. */
	if ( WC()->session && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
	}

	$product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? (int) get_post_meta( $rbfw_id, 'link_wc_product', true ) : $rbfw_id;

	/*
	 * Feed the standard booking pipeline: every validator and the cart-item
	 * builder read these exact POST fields, so quick-add behaves 1:1 like the
	 * single-item booking form (pricing, fees, deposit, location charge…).
	 */
	$_POST['nonce']                  = wp_create_nonce( 'rbfw_ajax_action' );
	$_POST['rbfw_pickup_start_date'] = $start;
	$_POST['rbfw_pickup_end_date']   = $end;
	$_POST['rbfw_pickup_start_time'] = '12:00 am';
	$_POST['rbfw_pickup_end_time']   = '12:00 am';
	$_POST['rbfw_item_quantity']     = $qty;
	$_POST['rbfw_enable_time_slot']  = 'off';
	if ( '' !== $location ) {
		$_POST['rbfw_pickup_point'] = $location;
	}

	/*
	 * WooCommerce applies woocommerce_add_to_cart_validation in the CALLER
	 * (form handler / wc-ajax / Store API), not inside WC_Cart::add_to_cart().
	 * Apply it here the same way so every plugin gate runs: duplicate-cart
	 * rule, rbfw_check_rental_availability and the location stock/selection
	 * validator.
	 */
	ob_start();
	$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $qty );
	$cart_item_key     = $passed_validation ? WC()->cart->add_to_cart( $product_id, $qty ) : false;
	ob_end_clean();

	if ( ! $cart_item_key ) {
		$messages = array();
		if ( function_exists( 'wc_get_notices' ) ) {
			foreach ( wc_get_notices( 'error' ) as $notice ) {
				$messages[] = wp_strip_all_tags( is_array( $notice ) ? $notice['notice'] : $notice );
			}
			wc_clear_notices();
		}
		wp_send_json_error(
			array(
				'message' => ! empty( $messages )
					? implode( ' ', $messages )
					: __( 'This rental could not be added. Please try again.', 'booking-and-rental-manager-for-woocommerce' ),
			)
		);
	}

	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices(); // drop the "added to cart" notice; the floating bar reports it
	}

	wp_send_json_success(
		array(
			'count'        => WC()->cart->get_cart_contents_count(),
			'total'        => WC()->cart->get_cart_total(),
			'cart_url'     => wc_get_cart_url(),
			'checkout_url' => wc_get_checkout_url(),
		)
	);
}

/******************************
 * AJAX: booking form for the "Select options" modal
 *
 * Returns the item's real booking form — the exact templates the single
 * page uses. The plugin's front-end scripts drive these forms with
 * delegated (document/body-level) handlers, so the injected form is fully
 * interactive: calendars, time slots, rooms, quantities and price updates
 * all work inside the popup.
 ******************************/
function rbfw_booking_search_item_form_ajax() {
	check_ajax_referer( 'rbfw_booking_search_action', 'nonce' );

	$rbfw_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
	$start   = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
	$end     = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : '';

	global $rbfw;
	if ( ! $rbfw_id || get_post_type( $rbfw_id ) !== $rbfw->get_cpt_name() ) {
		wp_send_json_error( array( 'message' => __( 'Invalid rental item.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	/* Prefill the searched dates where the form templates support GET prefill. */
	if ( '' !== $start && '' !== $end && strtotime( $start ) && strtotime( $end ) ) {
		$_GET['rbfw_start_date'] = $start;
		$_GET['rbfw_end_date']   = $end;
	}

	$html = rbfw_add_to_cart_shortcode_func( array( 'id' => $rbfw_id ) );

	wp_send_json_success(
		array(
			'html' => $html,
		)
	);
}

/******************************
 * AJAX: add-to-cart from the modal form
 *
 * The whole modal form is posted here (serialized), so $_POST carries the
 * exact fields the single-page booking submit would send — including the
 * form's own rbfw_ajax_action nonce. Our endpoint nonce travels separately
 * as "bsnonce" to avoid clashing with that field.
 ******************************/
function rbfw_booking_modal_add_ajax() {
	check_ajax_referer( 'rbfw_booking_search_action', 'bsnonce' );

	$product_id = isset( $_POST['rbfw_modal_product'] ) ? absint( $_POST['rbfw_modal_product'] ) : 0;
	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid booking submission.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'The cart is unavailable right now. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	if ( WC()->session && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
	}

	$qty = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;

	/* Same pattern as the quick-add: WooCommerce validation runs in the caller. */
	ob_start();
	$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $qty );
	$cart_item_key     = $passed_validation ? WC()->cart->add_to_cart( $product_id, $qty ) : false;
	ob_end_clean();

	if ( ! $cart_item_key ) {
		$messages = array();
		if ( function_exists( 'wc_get_notices' ) ) {
			foreach ( wc_get_notices( 'error' ) as $notice ) {
				$messages[] = wp_strip_all_tags( is_array( $notice ) ? $notice['notice'] : $notice );
			}
			wc_clear_notices();
		}
		wp_send_json_error(
			array(
				'message' => ! empty( $messages )
					? implode( ' ', $messages )
					: __( 'This rental could not be added. Please review your selections and try again.', 'booking-and-rental-manager-for-woocommerce' ),
			)
		);
	}

	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}

	wp_send_json_success(
		array(
			'count'        => WC()->cart->get_cart_contents_count(),
			'total'        => WC()->cart->get_cart_total(),
			'cart_url'     => wc_get_cart_url(),
			'checkout_url' => wc_get_checkout_url(),
		)
	);
}

/******************************
 * AJAX: checkout form for the "Checkout" modal
 *
 * Renders WooCommerce's own classic checkout form ([woocommerce_checkout]).
 * The JS posts it to WooCommerce's native wc-ajax=checkout endpoint, so the
 * real order pipeline runs unchanged — rental line meta (via
 * woocommerce_checkout_create_order_line_item), gateways, taxes and the
 * order-received redirect all behave exactly as on the checkout page. No
 * iframe/embed: the form markup is injected straight into the modal.
 ******************************/
function rbfw_booking_checkout_form_ajax() {
	check_ajax_referer( 'rbfw_booking_search_action', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'The cart is unavailable right now. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	/* Guests: make sure the session/cart cookie the browser holds is honoured. */
	if ( WC()->session && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
	}
	if ( WC()->cart->is_empty() ) {
		wp_send_json_error( array( 'message' => __( 'Your booking is empty. Add a rental first.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	/* WooCommerce normally sets this on the checkout page; some gateways and
	   the checkout template check it while rendering. */
	if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
		define( 'WOOCOMMERCE_CHECKOUT', true );
	}

	WC()->cart->calculate_totals();

	$html = do_shortcode( '[woocommerce_checkout]' );

	if ( false === strpos( $html, 'woocommerce-checkout' ) ) {
		wp_send_json_error( array( 'message' => __( 'Checkout is unavailable right now. Please open the full checkout page.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	wp_send_json_success( array( 'html' => $html ) );
}

/******************************
 * AJAX: empty the booking (clear the cart) from the floating bar.
 ******************************/
function rbfw_booking_empty_cart_ajax() {
	check_ajax_referer( 'rbfw_booking_search_action', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'The cart is unavailable right now. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	WC()->cart->empty_cart();

	wp_send_json_success(
		array(
			'count' => WC()->cart->get_cart_contents_count(),
			'total' => WC()->cart->get_cart_total(),
		)
	);
}

/******************************
 * AJAX: itemized booking details for the floating bar's "eye" popover.
 * Reuses wc_get_formatted_cart_item_data() — the same woocommerce_get_item_data
 * output the real cart page renders (this plugin's own hook embeds a
 * compact dates/pricing table per item there), trusted the same way via
 * wp_kses_post, so every rental type "just works" with no per-type parsing.
 ******************************/
function rbfw_booking_bar_details_ajax() {
	check_ajax_referer( 'rbfw_booking_search_action', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'The cart is unavailable right now. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ) );
	}

	$cart = WC()->cart->get_cart();
	if ( empty( $cart ) ) {
		wp_send_json_success(
			array(
				'html' => '<div class="rbfw_bsearch_bar_details_empty">' . esc_html__( 'Your booking is empty.', 'booking-and-rental-manager-for-woocommerce' ) . '</div>',
			)
		);
	}

	ob_start();
	foreach ( $cart as $cart_item ) {
		$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
		if ( ! $product ) {
			continue;
		}

		$qty        = (int) $cart_item['quantity'];
		$line_total = $cart_item['line_total'] + ( isset( $cart_item['line_tax'] ) ? (float) $cart_item['line_tax'] : 0 );
		$meta_html  = wc_get_formatted_cart_item_data( $cart_item );
		?>
		<div class="rbfw_bsearch_bar_detail_row">
			<div class="rbfw_bsearch_bar_detail_head">
				<div class="rbfw_bsearch_bar_detail_thumb"><?php echo wp_kses_post( $product->get_image( array( 52, 52 ) ) ); ?></div>
				<div class="rbfw_bsearch_bar_detail_title"><?php echo esc_html( $product->get_name() ); ?></div>
				<div class="rbfw_bsearch_bar_detail_qty">&times;<?php echo esc_html( $qty ); ?></div>
				<div class="rbfw_bsearch_bar_detail_price"><?php echo wp_kses_post( wc_price( $line_total ) ); ?></div>
			</div>
			<?php if ( '' !== trim( (string) $meta_html ) ) : ?>
				<div class="rbfw_bsearch_bar_detail_meta"><?php echo wp_kses_post( $meta_html ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
