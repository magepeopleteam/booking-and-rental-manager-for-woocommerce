<?php
/**
 * Delivery & Collection mileage pricing.
 *
 * Prices a "we bring the bike to you / we pick it up again" service from the customer's
 * distance, using admin-defined distance bands plus an optional flat base fee and a free
 * radius. There is no external API and no API key: the customer states their address and
 * how far away they are, and the band table turns that into a price.
 *
 * WHY IT PLUGS INTO THE FEE BUCKET
 * The charge is emitted as a row in `rbfw_management_info` / `rbfw_management_price` — the
 * same bucket rbfw_apply_location_charge() uses. That bucket already flows through the cart
 * summary, the order meta, the PDF ticket, the Pro booking detail view and the Booking
 * Editor's "Fees" line, so delivery appears everywhere with no changes to any of them.
 *
 * SECURITY
 * The browser posts only the CHOICE — delivery yes/no, collection yes/no, an address and a
 * distance. Every amount is resolved here, server-side, from the stored settings. This
 * mirrors how optional fees already work in RBFW_Woocommerse (the form says WHICH fee was
 * ticked; the server decides what it costs), so a tampered price in the request is ignored.
 *
 * @package booking-and-rental-manager-for-woocommerce
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Settings section id, shared by the settings screen and every reader below. */
if ( ! defined( 'RBFW_DELIVERY_SECTION' ) ) {
	define( 'RBFW_DELIVERY_SECTION', 'rbfw_delivery_settings' );
}

/**
 * Default distance bands, used until the shop configures its own.
 *
 * @return array<int,array{from:float,to:float,price:float}>
 */
function rbfw_delivery_default_bands() {
	return array(
		array( 'from' => 0,  'to' => 5,  'price' => 0 ),
		array( 'from' => 5,  'to' => 15, 'price' => 10 ),
		array( 'from' => 15, 'to' => 30, 'price' => 20 ),
	);
}

/**
 * Normalize one stored band row.
 *
 * @param mixed $row
 * @return array{from:float,to:float,price:float}|null Null when the row is unusable.
 */
function rbfw_delivery_normalize_band( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}
	$from  = isset( $row['from'] ) ? (float) $row['from'] : 0;
	$to    = isset( $row['to'] ) ? (float) $row['to'] : 0;
	$price = isset( $row['price'] ) ? (float) $row['price'] : 0;

	// A band that ends before it starts can never match; drop it rather than let it
	// silently swallow a distance.
	if ( $to <= $from ) {
		return null;
	}

	return array(
		'from'  => max( 0, $from ),
		'to'    => max( 0, $to ),
		'price' => max( 0, $price ),
	);
}

/**
 * Sanitize + sort a raw band list. Sorted ascending by `from` so the first match wins
 * deterministically no matter what order the admin entered the rows in.
 *
 * @param mixed $raw
 * @return array<int,array{from:float,to:float,price:float}>
 */
function rbfw_delivery_sanitize_bands( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$bands = array();
	foreach ( $raw as $row ) {
		$band = rbfw_delivery_normalize_band( $row );
		if ( $band ) {
			$bands[] = $band;
		}
	}

	usort( $bands, static function ( $a, $b ) {
		return $a['from'] <=> $b['from'];
	} );

	return $bands;
}

/**
 * The shop's delivery configuration, fully normalized.
 *
 * Read directly from the stored option rather than through rbfw_get_option(), because this
 * runs on pricing paths that also execute under WP-CLI and in cron, where rbfw_get_option()
 * deliberately returns nothing (see inc/rbfw_functions.php:497).
 *
 * @return array
 */
function rbfw_delivery_settings( $force_refresh = false ) {
	static $cache = null;
	if ( null !== $cache && ! $force_refresh ) {
		return $cache;
	}

	$opts = get_option( RBFW_DELIVERY_SECTION, array() );
	$opts = is_array( $opts ) ? $opts : array();

	$get = static function ( $key, $default = '' ) use ( $opts ) {
		return isset( $opts[ $key ] ) && '' !== $opts[ $key ] ? $opts[ $key ] : $default;
	};

	$bands = rbfw_delivery_sanitize_bands( $get( 'rbfw_delivery_bands', array() ) );
	if ( empty( $bands ) ) {
		$bands = rbfw_delivery_default_bands();
	}

	$collection_bands = rbfw_delivery_sanitize_bands( $get( 'rbfw_collection_bands', array() ) );

	$delivery_require_mode = $get( 'rbfw_delivery_require_mode', '' );
	if ( ! in_array( $delivery_require_mode, array( 'off', 'any', 'both' ), true ) ) {
		// Migrate the superseded boolean. It was documented as "delivery and/or collection",
		// but a shop that ticks "mandatory" and can still book a lone leg reads that as
		// broken — so the honest upgrade is the stricter mode.
		$delivery_require_mode = ( 'on' === $get( 'rbfw_delivery_require_choice', 'off' ) ) ? 'both' : 'off';
	}

	$cache = array(
		'enabled'            => 'on' === $get( 'rbfw_delivery_enable', 'off' ),
		'collection_enabled' => 'on' === $get( 'rbfw_collection_enable', 'off' ),
		// same | own | free
		'collection_mode'    => in_array( $get( 'rbfw_collection_mode', 'same' ), array( 'same', 'own', 'free' ), true )
			? $get( 'rbfw_collection_mode', 'same' )
			: 'same',
		'base_fee'           => max( 0, (float) $get( 'rbfw_delivery_base_fee', 0 ) ),
		'free_radius'        => max( 0, (float) $get( 'rbfw_delivery_free_radius', 0 ) ),
		'max_distance'       => max( 0, (float) $get( 'rbfw_delivery_max_distance', 0 ) ),
		'bands'              => $bands,
		'collection_band_rows' => $collection_bands,
		/*
		 * What the customer must complete. Enforced on the form AND on the server.
		 *
		 * require_mode: off | any | both.
		 *   off  — delivery is entirely optional; collecting in store is a valid answer.
		 *   any  — at least one leg.
		 *   both — delivery AND collection, for a shop that will not leave a rental at an
		 *          address it is not coming back to.
		 *
		 * The old boolean `rbfw_delivery_require_choice` migrates to `both`, which is what
		 * shops that turned it on actually meant: ticking it and still being able to book
		 * one leg read as the setting not working.
		 */
		'require_mode'       => $delivery_require_mode,
		'require_address'    => 'off' !== $get( 'rbfw_delivery_require_address', 'on' ),
		'require_phone'      => 'on' === $get( 'rbfw_delivery_require_phone', 'off' ),
		'require_note'       => 'on' === $get( 'rbfw_delivery_require_note', 'off' ),
		'delivery_label'     => (string) $get( 'rbfw_delivery_label', __( 'Delivery', 'booking-and-rental-manager-for-woocommerce' ) ),
		'collection_label'   => (string) $get( 'rbfw_collection_label', __( 'Collection', 'booking-and-rental-manager-for-woocommerce' ) ),
		'help_text'          => (string) $get( 'rbfw_delivery_help_text', '' ),
	);

	/**
	 * Filter the resolved delivery configuration.
	 *
	 * @since 2.8.0
	 * @param array $cache Normalized settings.
	 */
	$cache = apply_filters( 'rbfw_delivery_settings', $cache );

	return $cache;
}

/**
 * Drop the memoized settings so the next read reflects a just-saved option.
 *
 * Hooked to the option write below, because the settings screen saves and then re-renders
 * inside the SAME request — without this the admin would be shown the values from before
 * their save.
 *
 * @return void
 */
function rbfw_delivery_flush_settings_cache() {
	rbfw_delivery_settings( true );
}
add_action( 'update_option_' . RBFW_DELIVERY_SECTION, 'rbfw_delivery_flush_settings_cache', 10, 0 );
add_action( 'add_option_' . RBFW_DELIVERY_SECTION, 'rbfw_delivery_flush_settings_cache', 10, 0 );

/**
 * Whether delivery is offered at all (shop-wide switch).
 *
 * @return bool
 */
function rbfw_delivery_is_enabled() {
	$cfg = rbfw_delivery_settings();
	return ! empty( $cfg['enabled'] ) || ! empty( $cfg['collection_enabled'] );
}

/**
 * Whether delivery is offered for one rental item.
 *
 * The shop-wide switch is the master; each item may then opt OUT. A brand new item has no
 * `rbfw_enable_delivery` meta at all, and that must mean "follow the shop", not "disabled" —
 * otherwise turning delivery on would appear to do nothing until every item was re-saved.
 *
 * @param int $item_id rbfw_item post id.
 * @return bool
 */
function rbfw_delivery_enabled_for_item( $item_id ) {
	if ( ! rbfw_delivery_is_enabled() ) {
		return false;
	}

	$item_id = absint( $item_id );
	if ( ! $item_id ) {
		return false;
	}

	$per_item = get_post_meta( $item_id, 'rbfw_enable_delivery', true );

	// '' (never saved) inherits the shop-wide setting; only an explicit 'no' opts out.
	$enabled = ( 'no' !== $per_item );

	/**
	 * Filter whether one item can be delivered.
	 *
	 * @since 2.8.0
	 * @param bool $enabled
	 * @param int  $item_id
	 */
	return (bool) apply_filters( 'rbfw_delivery_enabled_for_item', $enabled, $item_id );
}

/**
 * Price for a distance from a band table.
 *
 * Bands are treated as [from, to] with an INCLUSIVE upper bound, so a table of 0–5 / 5–15
 * has no gap at exactly 5 km. The first matching band wins (they are sorted ascending), so
 * an overlapping table degrades to "the lowest band that covers this distance" instead of
 * throwing or picking arbitrarily.
 *
 * @param float $km
 * @param array $bands
 * @return float|null Null when no band covers the distance.
 */
function rbfw_delivery_band_price( $km, $bands ) {
	$km = (float) $km;
	foreach ( $bands as $band ) {
		if ( $km >= $band['from'] && $km <= $band['to'] ) {
			return (float) $band['price'];
		}
	}
	return null;
}

/**
 * Human label for the band a distance falls into ("5 – 15 km").
 *
 * @param float $km
 * @param array $bands
 * @return string
 */
function rbfw_delivery_band_label( $km, $bands ) {
	$km = (float) $km;
	foreach ( $bands as $band ) {
		if ( $km >= $band['from'] && $km <= $band['to'] ) {
			return sprintf(
				/* translators: 1: band lower bound, 2: band upper bound. */
				__( '%1$s – %2$s km', 'booking-and-rental-manager-for-woocommerce' ),
				rbfw_delivery_format_km( $band['from'] ),
				rbfw_delivery_format_km( $band['to'] )
			);
		}
	}
	return '';
}

/**
 * Format a distance without a pointless trailing ".0".
 *
 * @param float $km
 * @return string
 */
function rbfw_delivery_format_km( $km ) {
	$km = (float) $km;
	return ( $km == (int) $km ) ? (string) (int) $km : rtrim( rtrim( number_format( $km, 2, '.', '' ), '0' ), '.' );
}

/**
 * Quote delivery and/or collection for one booking.
 *
 * @param int   $item_id        rbfw_item post id.
 * @param float $km             Distance the customer stated.
 * @param bool  $want_delivery  Customer wants the bike delivered.
 * @param bool  $want_collection Customer wants it collected again.
 * `applied_delivery` / `applied_collection` say whether the service was GRANTED, which is
 * not the same as costing money — inside the free radius a delivery is applied at 0.00. Any
 * caller asking "was this booking delivered?" must read those, never the amounts.
 *
 * @return array{
 *     delivery:float, collection:float, total:float, band:string, distance:float,
 *     applied_delivery:bool, applied_collection:bool, error:string, error_code:string
 * }
 */
function rbfw_delivery_quote( $item_id, $km, $want_delivery = false, $want_collection = false ) {
	$empty = array(
		'delivery'           => 0.0,
		'collection'         => 0.0,
		'total'              => 0.0,
		'band'               => '',
		'distance'           => 0.0,
		'applied_delivery'   => false,
		'applied_collection' => false,
		'error'              => '',
		'error_code'         => '',
	);

	if ( ! $want_delivery && ! $want_collection ) {
		return $empty;
	}
	if ( ! rbfw_delivery_enabled_for_item( $item_id ) ) {
		return $empty;
	}

	$cfg = rbfw_delivery_settings();

	// Honour the two independent switches: a shop may deliver but not collect.
	$want_delivery   = $want_delivery && ! empty( $cfg['enabled'] );
	$want_collection = $want_collection && ! empty( $cfg['collection_enabled'] );
	if ( ! $want_delivery && ! $want_collection ) {
		return $empty;
	}

	$km = (float) $km;
	if ( $km < 0 ) {
		$km = 0;
	}

	// Out of range is a hard refusal, not a silent free delivery: quoting 0 for a 200 km
	// journey would let the booking through at a price the shop never agreed to.
	if ( $cfg['max_distance'] > 0 && $km > $cfg['max_distance'] ) {
		return array_merge( $empty, array(
			'distance'   => $km,
			'error_code' => 'out_of_range',
			'error'      => sprintf(
				/* translators: %s: maximum distance in km. */
				__( 'Sorry, we only deliver within %s km. Please contact us to arrange something.', 'booking-and-rental-manager-for-woocommerce' ),
				rbfw_delivery_format_km( $cfg['max_distance'] )
			),
		) );
	}

	// Inside the free radius nothing is charged — but the service is still APPLIED, so the
	// booking is correctly recorded as a delivery and shows as one on the calendar.
	if ( $cfg['free_radius'] > 0 && $km <= $cfg['free_radius'] ) {
		return array_merge( $empty, array(
			'distance'           => $km,
			'band'               => __( 'Free delivery zone', 'booking-and-rental-manager-for-woocommerce' ),
			'applied_delivery'   => $want_delivery,
			'applied_collection' => $want_collection,
		) );
	}

	$band_price = rbfw_delivery_band_price( $km, $cfg['bands'] );
	if ( null === $band_price ) {
		return array_merge( $empty, array(
			'distance'   => $km,
			'error_code' => 'no_band',
			'error'      => __( 'We could not work out a delivery price for that distance. Please contact us.', 'booking-and-rental-manager-for-woocommerce' ),
		) );
	}

	$delivery = 0.0;
	if ( $want_delivery ) {
		$delivery = $cfg['base_fee'] + $band_price;
	}

	$collection = 0.0;
	if ( $want_collection ) {
		switch ( $cfg['collection_mode'] ) {
			case 'free':
				$collection = 0.0;
				break;

			case 'own':
				$own = rbfw_delivery_band_price( $km, $cfg['collection_band_rows'] );
				// An "own bands" table that does not cover this distance falls back to the
				// delivery price rather than quoting 0 — under-charging is the worse failure.
				$collection = ( null === $own ) ? ( $cfg['base_fee'] + $band_price ) : ( $cfg['base_fee'] + $own );
				break;

			case 'same':
			default:
				$collection = $cfg['base_fee'] + $band_price;
				break;
		}
	}

	$quote = array(
		'delivery'           => round( max( 0, $delivery ), 2 ),
		'collection'         => round( max( 0, $collection ), 2 ),
		'total'              => round( max( 0, $delivery + $collection ), 2 ),
		'band'               => rbfw_delivery_band_label( $km, $cfg['bands'] ),
		'distance'           => $km,
		'applied_delivery'   => $want_delivery,
		'applied_collection' => $want_collection,
		'error'              => '',
		'error_code'         => '',
	);

	/**
	 * Filter a delivery quote before it is charged.
	 *
	 * @since 2.8.0
	 * @param array $quote
	 * @param int   $item_id
	 * @param float $km
	 */
	return apply_filters( 'rbfw_delivery_quote', $quote, $item_id, $km );
}

/**
 * Read the delivery choice out of a submitted booking form.
 *
 * Only the CHOICE is taken from the request — never a price.
 *
 * @param array $input Sanitized form payload.
 * @return array{delivery:bool,collection:bool,distance:float,address:string}
 */
function rbfw_delivery_input_from_form( $input ) {
	$input = is_array( $input ) ? $input : array();

	$truthy = static function ( $value ) {
		return in_array( (string) $value, array( '1', 'yes', 'on', 'true' ), true );
	};

	$distance = isset( $input['rbfw_delivery_distance'] ) ? (float) str_replace( ',', '.', (string) $input['rbfw_delivery_distance'] ) : 0.0;

	return array(
		'delivery'   => isset( $input['rbfw_delivery_wanted'] ) && $truthy( $input['rbfw_delivery_wanted'] ),
		'collection' => isset( $input['rbfw_collection_wanted'] ) && $truthy( $input['rbfw_collection_wanted'] ),
		'distance'   => max( 0, $distance ),
		'address'    => isset( $input['rbfw_delivery_address'] ) ? sanitize_text_field( (string) $input['rbfw_delivery_address'] ) : '',
		'phone'      => isset( $input['rbfw_delivery_phone'] ) ? sanitize_text_field( (string) $input['rbfw_delivery_phone'] ) : '',
		'note'       => isset( $input['rbfw_delivery_note'] ) ? sanitize_textarea_field( (string) $input['rbfw_delivery_note'] ) : '',
	);
}

/**
 * Add the delivery / collection charge to a booking's fee bucket.
 *
 * Deliberately mirrors rbfw_apply_location_charge()'s shape (inc/rbfw_functions.php) so the
 * two read alike at every call site.
 *
 * @param int   $item_id          rbfw_item post id.
 * @param array $input            Raw sanitized form payload.
 * @param array $management_info  Fee rows so far.
 * @param float $management_price Fee total so far.
 * @return array{0:array,1:float} [ $management_info, $management_price ]
 */
function rbfw_apply_delivery_charge( $item_id, $input, $management_info, $management_price ) {
	$choice = rbfw_delivery_input_from_form( $input );
	if ( ! $choice['delivery'] && ! $choice['collection'] ) {
		return array( $management_info, $management_price );
	}

	$quote = rbfw_delivery_quote( $item_id, $choice['distance'], $choice['delivery'], $choice['collection'] );
	if ( '' !== $quote['error'] ) {
		// Refused quotes add nothing. The checkout paths surface the message separately;
		// silently charging zero here is what must not happen.
		return array( $management_info, $management_price );
	}

	$cfg      = rbfw_delivery_settings();
	$distance = rbfw_delivery_format_km( $quote['distance'] );

	if ( $quote['delivery'] > 0 ) {
		$label = sprintf(
			/* translators: 1: "Delivery" label, 2: distance in km. */
			__( '%1$s (%2$s km)', 'booking-and-rental-manager-for-woocommerce' ),
			$cfg['delivery_label'],
			$distance
		);
		$management_info[ $label ] = array(
			'price'      => $quote['delivery'],
			'price_desc' => rbfw_delivery_price_html( $quote['delivery'] ),
			'refundable' => 'no',
		);
		$management_price += $quote['delivery'];
	}

	if ( $quote['collection'] > 0 ) {
		$label = sprintf(
			/* translators: 1: "Collection" label, 2: distance in km. */
			__( '%1$s (%2$s km)', 'booking-and-rental-manager-for-woocommerce' ),
			$cfg['collection_label'],
			$distance
		);
		$management_info[ $label ] = array(
			'price'      => $quote['collection'],
			'price_desc' => rbfw_delivery_price_html( $quote['collection'] ),
			'refundable' => 'no',
		);
		$management_price += $quote['collection'];
	}

	return array( $management_info, $management_price );
}

/**
 * Price markup for a fee row. WooCommerce may be inactive (Standalone mode), so wc_price()
 * is never called unguarded.
 *
 * @param float $amount
 * @return string
 */
function rbfw_delivery_price_html( $amount ) {
	if ( function_exists( 'wc_price' ) ) {
		return wc_price( (float) $amount );
	}
	$symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
	return $symbol . number_format( (float) $amount, 2 );
}

/**
 * Validate a submitted delivery choice against the shop's required-field settings.
 *
 * Runs on BOTH checkout paths. The booking form marks the same fields required, but that is
 * a convenience for the customer, not a control: anything enforced only in the browser can
 * be removed with the developer tools. This is the check that actually holds.
 *
 * @param int   $item_id rbfw_item post id.
 * @param array $input   Raw sanitized form payload.
 * @return true|WP_Error
 */
function rbfw_delivery_validate_input( $item_id, $input ) {
	if ( ! rbfw_delivery_enabled_for_item( $item_id ) ) {
		return true;
	}

	$cfg    = rbfw_delivery_settings();
	$choice = rbfw_delivery_input_from_form( $input );
	$chosen = ( $choice['delivery'] || $choice['collection'] );

	/*
	 * A leg can only be required if the shop actually offers it. Requiring "both" while
	 * Collection is switched off would make every booking impossible, so each rule is
	 * narrowed to the legs on offer first.
	 */
	$needs_delivery   = ! empty( $cfg['enabled'] ) && 'both' === $cfg['require_mode'];
	$needs_collection = ! empty( $cfg['collection_enabled'] ) && 'both' === $cfg['require_mode'];

	if ( $needs_delivery && ! $choice['delivery'] ) {
		return new WP_Error(
			'rbfw_delivery_required',
			sprintf(
				/* translators: 1: delivery label, 2: collection label. */
				esc_html__( 'This rental is booked with %1$s and %2$s together — please select both.', 'booking-and-rental-manager-for-woocommerce' ),
				$cfg['delivery_label'],
				$cfg['collection_label']
			)
		);
	}

	if ( $needs_collection && ! $choice['collection'] ) {
		return new WP_Error(
			'rbfw_collection_required',
			sprintf(
				/* translators: 1: delivery label, 2: collection label. */
				esc_html__( 'This rental is booked with %1$s and %2$s together — please select both.', 'booking-and-rental-manager-for-woocommerce' ),
				$cfg['delivery_label'],
				$cfg['collection_label']
			)
		);
	}

	if ( ! $chosen ) {
		if ( 'any' === $cfg['require_mode'] ) {
			return new WP_Error(
				'rbfw_delivery_required',
				esc_html__( 'Please choose whether you would like delivery or collection.', 'booking-and-rental-manager-for-woocommerce' )
			);
		}
		// Nothing asked for and nothing required — collecting in store is a valid answer.
		return true;
	}

	if ( $choice['distance'] <= 0 ) {
		return new WP_Error(
			'rbfw_delivery_no_distance',
			esc_html__( 'Please choose how far you are from us.', 'booking-and-rental-manager-for-woocommerce' )
		);
	}

	// The quote itself is authoritative on range and coverage.
	$quote = rbfw_delivery_quote( $item_id, $choice['distance'], $choice['delivery'], $choice['collection'] );
	if ( '' !== $quote['error'] ) {
		return new WP_Error( 'rbfw_delivery_' . $quote['error_code'], $quote['error'] );
	}

	if ( ! empty( $cfg['require_address'] ) && '' === trim( $choice['address'] ) ) {
		return new WP_Error(
			'rbfw_delivery_no_address',
			esc_html__( 'Please enter the delivery address.', 'booking-and-rental-manager-for-woocommerce' )
		);
	}

	if ( ! empty( $cfg['require_phone'] ) && '' === trim( (string) ( $input['rbfw_delivery_phone'] ?? '' ) ) ) {
		return new WP_Error(
			'rbfw_delivery_no_phone',
			esc_html__( 'Please give us a contact number for the delivery.', 'booking-and-rental-manager-for-woocommerce' )
		);
	}

	if ( ! empty( $cfg['require_note'] ) && '' === trim( (string) ( $input['rbfw_delivery_note'] ?? '' ) ) ) {
		return new WP_Error(
			'rbfw_delivery_no_note',
			esc_html__( 'Please add delivery notes so we can find you.', 'booking-and-rental-manager-for-woocommerce' )
		);
	}

	return true;
}

/**
 * The distance zones a customer (or an admin) picks from, built from the configured bands.
 *
 * Shared by the public booking form and the admin booking editor so the two can never offer
 * different zones or different prices for the same shop.
 *
 * Each option's VALUE is a distance inside its band — the midpoint of the chargeable span,
 * not an edge. Bands are inclusive at both ends, so neighbours touch (0-5 and 5-15 both
 * contain 5) and submitting an edge would let "first match wins" resolve to the cheaper
 * neighbour: the customer picks the 5-15 zone and is charged the 0-5 price.
 *
 * Bands straddling the free radius are trimmed to their chargeable part. With free delivery
 * to 3 km and a 0-5 band, offering "0 - 5 km" puts its midpoint inside the free zone, so a
 * customer 4 km away is quoted nothing and the shop delivers free by accident.
 *
 * @param int $item_id rbfw_item post id.
 * @return array<int,array{value:float,label:string,note:string}>
 */
function rbfw_delivery_zone_options( $item_id ) {
	$cfg   = rbfw_delivery_settings();
	$zones = array();

	if ( $cfg['free_radius'] > 0 ) {
		$zones[] = array(
			'value' => min( $cfg['free_radius'], max( 0.1, $cfg['free_radius'] / 2 ) ),
			'label' => sprintf(
				/* translators: %s: free radius in km. */
				__( 'Within %s km', 'booking-and-rental-manager-for-woocommerce' ),
				rbfw_delivery_format_km( $cfg['free_radius'] )
			),
			'note'  => __( 'Free', 'booking-and-rental-manager-for-woocommerce' ),
		);
	}

	foreach ( $cfg['bands'] as $band ) {
		$from = $band['from'];
		$to   = $band['to'];

		if ( $cfg['free_radius'] > 0 ) {
			if ( $to <= $cfg['free_radius'] ) {
				continue; // already covered by the free row above
			}
			if ( $from < $cfg['free_radius'] ) {
				$from = $cfg['free_radius'];
			}
		}

		$mid   = $from + ( ( $to - $from ) / 2 );
		$quote = rbfw_delivery_quote( $item_id, $mid, true, false );

		$zones[] = array(
			'value' => $mid,
			'label' => sprintf(
				/* translators: 1: band lower bound, 2: band upper bound. */
				__( '%1$s – %2$s km', 'booking-and-rental-manager-for-woocommerce' ),
				rbfw_delivery_format_km( $from ),
				rbfw_delivery_format_km( $to )
			),
			'note'  => $quote['delivery'] > 0
				? wp_strip_all_tags( rbfw_delivery_price_html( $quote['delivery'] ) )
				: __( 'Free', 'booking-and-rental-manager-for-woocommerce' ),
		);
	}

	return $zones;
}

/**
 * The zone option whose band contains a stored distance, so an existing booking reopens on
 * the zone it was actually booked with rather than falling back to "choose one".
 *
 * @param int   $item_id
 * @param float $distance
 * @return float|null The matching option value, or null when nothing covers it.
 */
function rbfw_delivery_zone_value_for( $item_id, $distance ) {
	$distance = (float) $distance;
	if ( $distance <= 0 ) {
		return null;
	}

	$cfg = rbfw_delivery_settings();

	// Inside the free radius the first option is the right one.
	if ( $cfg['free_radius'] > 0 && $distance <= $cfg['free_radius'] ) {
		$zones = rbfw_delivery_zone_options( $item_id );
		return isset( $zones[0] ) ? $zones[0]['value'] : null;
	}

	foreach ( $cfg['bands'] as $band ) {
		if ( $distance >= $band['from'] && $distance <= $band['to'] ) {
			$from = ( $cfg['free_radius'] > 0 && $band['from'] < $cfg['free_radius'] )
				? $cfg['free_radius']
				: $band['from'];
			return $from + ( ( $band['to'] - $from ) / 2 );
		}
	}

	return null;
}

/**
 * Flat delivery record for one booking, ready to travel as cart-item data.
 *
 * The fee bucket carries the MONEY, but not the address, the distance or the fact that a
 * free-radius delivery happened at all. This carries those, so the cart item → order item →
 * `rbfw_order` mirror chain ends with flat meta the list, calendar, PDF and emails can read
 * with a single get_post_meta().
 *
 * Returns an empty array when no delivery was asked for, so callers can merge unconditionally
 * without writing empty meta onto every ordinary booking.
 *
 * @param int   $item_id rbfw_item post id.
 * @param array $input   Raw sanitized form payload.
 * @return array<string,mixed>
 */
function rbfw_delivery_cart_data( $item_id, $input ) {
	$choice = rbfw_delivery_input_from_form( $input );
	if ( ! $choice['delivery'] && ! $choice['collection'] ) {
		return array();
	}

	$quote = rbfw_delivery_quote( $item_id, $choice['distance'], $choice['delivery'], $choice['collection'] );
	if ( '' !== $quote['error'] ) {
		return array();
	}

	return array(
		'rbfw_delivery_wanted'   => $quote['applied_delivery'] ? 'yes' : 'no',
		'rbfw_collection_wanted' => $quote['applied_collection'] ? 'yes' : 'no',
		'rbfw_delivery_distance' => $quote['distance'],
		'rbfw_delivery_address'  => $choice['address'],
		'rbfw_delivery_phone'    => $choice['phone'],
		'rbfw_delivery_note'     => $choice['note'],
		'rbfw_delivery_amount'   => $quote['total'],
		'rbfw_delivery_fee'      => $quote['delivery'],
		'rbfw_collection_fee'    => $quote['collection'],
		'rbfw_delivery_band'     => $quote['band'],
	);
}

/**
 * The meta keys the delivery record travels under, in one place so the cart, the order item
 * and the mirror can never drift apart.
 *
 * @return string[]
 */
function rbfw_delivery_meta_keys() {
	return array(
		'rbfw_delivery_wanted',
		'rbfw_collection_wanted',
		'rbfw_delivery_distance',
		'rbfw_delivery_address',
		'rbfw_delivery_phone',
		'rbfw_delivery_note',
		'rbfw_delivery_amount',
		// Each leg is kept separately as well as summed — they are billed as two services
		// and can be priced by different band tables, so a merged total cannot be split
		// back apart afterwards for an invoice or a refund.
		'rbfw_delivery_fee',
		'rbfw_collection_fee',
		'rbfw_delivery_band',
	);
}

/**
 * Human summary of a booking's delivery, for the PDF, emails and admin views.
 *
 * @param int $booking_id rbfw_booking or rbfw_order post id.
 * @return string '' when the booking has no delivery.
 */
function rbfw_delivery_summary( $booking_id ) {
	$booking_id = absint( $booking_id );
	if ( ! $booking_id ) {
		return '';
	}

	$delivery   = 'yes' === get_post_meta( $booking_id, 'rbfw_delivery_wanted', true );
	$collection = 'yes' === get_post_meta( $booking_id, 'rbfw_collection_wanted', true );
	if ( ! $delivery && ! $collection ) {
		return '';
	}

	$cfg   = rbfw_delivery_settings();
	$legs  = array();
	if ( $delivery ) {
		$legs[] = $cfg['delivery_label'];
	}
	if ( $collection ) {
		$legs[] = $cfg['collection_label'];
	}

	$summary  = implode( ' + ', $legs );
	$distance = (float) get_post_meta( $booking_id, 'rbfw_delivery_distance', true );
	if ( $distance > 0 ) {
		$summary .= sprintf(
			/* translators: %s: distance in km. */
			__( ' — %s km', 'booking-and-rental-manager-for-woocommerce' ),
			rbfw_delivery_format_km( $distance )
		);
	}

	$address = (string) get_post_meta( $booking_id, 'rbfw_delivery_address', true );
	if ( '' !== $address ) {
		$summary .= ' — ' . $address;
	}

	return $summary;
}

/**
 * Persist the delivery choice + resolved amounts on a booking.
 *
 * Written as flat meta on BOTH booking post types so the Bookings list, the calendar, the
 * editor and the emails can read it with one get_post_meta() and no unserializing.
 *
 * @param int   $booking_id rbfw_booking or rbfw_order post id.
 * @param int   $item_id    rbfw_item post id.
 * @param array $input      Raw sanitized form payload.
 * @return void
 */
function rbfw_delivery_save_booking_meta( $booking_id, $item_id, $input ) {
	$booking_id = absint( $booking_id );
	if ( ! $booking_id ) {
		return;
	}

	$choice = rbfw_delivery_input_from_form( $input );
	if ( ! $choice['delivery'] && ! $choice['collection'] ) {
		return;
	}

	$quote = rbfw_delivery_quote( $item_id, $choice['distance'], $choice['delivery'], $choice['collection'] );
	if ( '' !== $quote['error'] ) {
		return;
	}

	// Record what was GRANTED, not what cost money — a free-radius delivery is still a
	// delivery, and the calendar's "with / without delivery" badge depends on this.
	update_post_meta( $booking_id, 'rbfw_delivery_wanted', $quote['applied_delivery'] ? 'yes' : 'no' );
	update_post_meta( $booking_id, 'rbfw_collection_wanted', $quote['applied_collection'] ? 'yes' : 'no' );
	update_post_meta( $booking_id, 'rbfw_delivery_distance', $quote['distance'] );
	update_post_meta( $booking_id, 'rbfw_delivery_address', $choice['address'] );
	update_post_meta( $booking_id, 'rbfw_delivery_phone', $choice['phone'] );
	update_post_meta( $booking_id, 'rbfw_delivery_note', $choice['note'] );
	update_post_meta( $booking_id, 'rbfw_delivery_amount', $quote['total'] );
	update_post_meta( $booking_id, 'rbfw_delivery_fee', $quote['delivery'] );
	update_post_meta( $booking_id, 'rbfw_collection_fee', $quote['collection'] );
	update_post_meta( $booking_id, 'rbfw_delivery_band', $quote['band'] );
}
