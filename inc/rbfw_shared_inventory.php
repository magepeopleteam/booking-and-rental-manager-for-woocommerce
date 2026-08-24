<?php
/**
 * Shared inventory for the "Multiple day for multiple items" rent type.
 *
 * A Multiple Items rental normally carries its own private stock counter per
 * sub-item row (`multiple_items_info[i]['available_qty']`) that has no relation
 * to anything else in the catalogue. This file adds the WooCommerce Product
 * Bundles behaviour the same way a bundled product shares its component's
 * stock: a row can instead be LINKED to an existing rental item, and then both
 * the bundle and the individual rental draw down one single pool — additionally
 * scoped to the selected rental date and time.
 *
 * Row schema additions (both optional; absent === legacy private stock):
 *   - inventory_source : 'own' (default) | 'shared'
 *   - source_id        : rbfw_item post id the row draws its stock from
 *
 * Reads are the only thing that changed: nothing new is written to any
 * `rbfw_inventory` record beyond the `source_id` carried on the booked line, so
 * every existing cancel / trash / refund / booking-edit path keeps working
 * untouched and a freed bundle booking immediately frees the source item again.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Reverse index option: source item id => list of Multiple Items parent ids. */
if ( ! defined( 'RBFW_MI_SOURCE_MAP_OPTION' ) ) {
	define( 'RBFW_MI_SOURCE_MAP_OPTION', 'rbfw_mi_shared_inventory_map' );
}

if ( ! function_exists( 'rbfw_mi_shared_inventory_enabled' ) ) {
	/**
	 * Master switch for the whole feature (Settings -> General).
	 *
	 * Read straight from the stored settings array rather than through
	 * rbfw_get_option(), which deliberately returns nothing under WP-CLI — this
	 * value has to be reliable in cron/CLI/REST contexts too.
	 *
	 * @return bool
	 */
	function rbfw_mi_shared_inventory_enabled() {
		$settings = get_option( 'rbfw_basic_gen_settings', array() );
		$value    = ( is_array( $settings ) && isset( $settings['rbfw_mi_shared_inventory'] ) )
			? $settings['rbfw_mi_shared_inventory']
			: 'yes';

		return 'no' !== $value;
	}
}

if ( ! function_exists( 'rbfw_mi_source_supported_types' ) ) {
	/**
	 * Rent types that may act as a shared-inventory source.
	 *
	 * Only types backed by ONE unit pool and a datetime-overlap availability
	 * model can be shared with a bundle, because a bundle books whole units for
	 * a window — it has no notion of a room type, a session or a per-rate row.
	 * Resort / appointment / multiple_items and date-based single-day items
	 * (whose stock lives per rate row) are therefore never offered.
	 *
	 * @return string[]
	 */
	function rbfw_mi_source_supported_types() {
		return apply_filters(
			'rbfw_mi_source_supported_types',
			array( 'bike_car_md', 'dress', 'equipment', 'others', 'bike_car_sd' )
		);
	}
}

if ( ! function_exists( 'rbfw_mi_is_valid_source' ) ) {
	/**
	 * Whether a post id can be used as a shared-inventory source.
	 *
	 * @param int $source_id Candidate rbfw_item id.
	 * @return bool
	 */
	function rbfw_mi_is_valid_source( $source_id ) {
		// Single source of truth: eligibility is "there is no reason not to".
		// Keeping the test and the explanation in one place stops the dropdown
		// from ever disagreeing with the message next to it.
		return ( '' === rbfw_mi_source_ineligible_reason( $source_id ) );
	}
}

if ( ! function_exists( 'rbfw_mi_row_source_id' ) ) {
	/**
	 * Resolve the source item a Multiple Items row draws its stock from.
	 *
	 * Returns 0 for every legacy row, every row left on "own inventory", and
	 * whenever the master switch is off — so callers can branch on a single
	 * truthy check and keep the original private-stock behaviour otherwise.
	 *
	 * Accepts both shapes this key travels in: a stored configuration row, which
	 * always carries the explicit `inventory_source` flag, and a booked/cart line,
	 * which carries only `source_id`. An explicit flag always wins.
	 *
	 * @param array $row One multiple_items_info row or booked line.
	 * @return int Source rbfw_item id, or 0.
	 */
	function rbfw_mi_row_source_id( $row ) {
		if ( ! is_array( $row ) || ! rbfw_mi_shared_inventory_enabled() ) {
			return 0;
		}
		if ( isset( $row['inventory_source'] ) && 'shared' !== (string) $row['inventory_source'] ) {
			return 0;
		}
		$source_id = isset( $row['source_id'] ) ? absint( $row['source_id'] ) : 0;

		return rbfw_mi_is_valid_source( $source_id ) ? $source_id : 0;
	}
}

if ( ! function_exists( 'rbfw_mi_normalize_rows' ) ) {
	/**
	 * Normalise submitted Multiple Items rows before they are stored.
	 *
	 * Turns the single posted `source_id` into the explicit (source_id,
	 * inventory_source) pair every reader branches on, and gives a linked row the
	 * source item's title when the admin left the name blank — that name is what
	 * the customer sees and what legacy bookings are matched by.
	 *
	 * @param array $rows Raw multiple_items_info rows.
	 * @return array
	 */
	function rbfw_mi_normalize_rows( $rows ) {
		if ( ! is_array( $rows ) ) {
			return array();
		}

		foreach ( $rows as $key => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$source_id                       = isset( $row['source_id'] ) ? absint( $row['source_id'] ) : 0;
			$rows[ $key ]['source_id']        = $source_id;
			$rows[ $key ]['inventory_source'] = $source_id ? 'shared' : 'own';

			if ( $source_id && ( ! isset( $row['item_name'] ) || '' === trim( (string) $row['item_name'] ) ) ) {
				$title                     = rbfw_mi_source_title( $source_id );
				$rows[ $key ]['item_name'] = ( '' !== $title ) ? $title : '#' . $source_id;
			}
		}

		return $rows;
	}
}

if ( ! function_exists( 'rbfw_mi_source_stock' ) ) {
	/**
	 * Total unit pool configured on a source item.
	 *
	 * @param int $source_id rbfw_item id.
	 * @return int
	 */
	function rbfw_mi_source_stock( $source_id ) {
		$source_id = absint( $source_id );
		if ( ! $source_id ) {
			return 0;
		}
		$type = get_post_meta( $source_id, 'rbfw_item_type', true );

		if ( 'bike_car_sd' === $type ) {
			return max( 0, (int) get_post_meta( $source_id, 'rbfw_item_stock_quantity_timely', true ) );
		}

		if ( get_post_meta( $source_id, 'rbfw_enable_variations', true ) === 'yes' && function_exists( 'rbfw_get_variations_stock' ) ) {
			return max( 0, (int) rbfw_get_variations_stock( $source_id ) );
		}

		return function_exists( 'rbfw_get_effective_item_stock' )
			? (int) rbfw_get_effective_item_stock( $source_id )
			: max( 0, (int) get_post_meta( $source_id, 'rbfw_item_stock_quantity', true ) );
	}
}

if ( ! function_exists( 'rbfw_mi_source_ineligible_reason' ) ) {
	/**
	 * Why an item cannot be used as a shared-inventory source, in plain words.
	 *
	 * Returned to the editor so an admin never has to guess why one of their
	 * rentals is missing from the dropdown.
	 *
	 * @param int $source_id Candidate rbfw_item id.
	 * @return string Empty string when the item IS eligible.
	 */
	function rbfw_mi_source_ineligible_reason( $source_id ) {
		$source_id = absint( $source_id );
		if ( ! $source_id || get_post_type( $source_id ) !== RBFW_Function::get_cpt_name() ) {
			return __( 'not a rental item', 'booking-and-rental-manager-for-woocommerce' );
		}

		$type = get_post_meta( $source_id, 'rbfw_item_type', true );

		if ( 'multiple_items' === $type ) {
			return __( 'a Multiple Items package has no stock of its own to share', 'booking-and-rental-manager-for-woocommerce' );
		}
		if ( 'resort' === $type ) {
			return __( 'Resort stock is held per room type, not as one pool', 'booking-and-rental-manager-for-woocommerce' );
		}
		if ( 'appointment' === $type ) {
			return __( 'Appointment stock is held per session, not as one pool', 'booking-and-rental-manager-for-woocommerce' );
		}
		if ( 'bike_car_sd' === $type && 'on' !== get_post_meta( $source_id, 'manage_inventory_as_timely', true ) ) {
			return __( 'switch on "Manage inventory as timely" on this item to share it', 'booking-and-rental-manager-for-woocommerce' );
		}
		/* Item Variations hold stock per value (Red 5 / Blue 5), and a package row
		   books whole units without naming a value. Allowing it would let a package
		   drain the pool while each value still reported its own full stock to the
		   item's own booking form — the same "not one pool" problem as Resort rooms
		   and per-rate single-day rows. */
		if ( 'yes' === get_post_meta( $source_id, 'rbfw_enable_variations', true ) ) {
			return __( 'Item Variations hold stock per value, not as one pool', 'booking-and-rental-manager-for-woocommerce' );
		}
		if ( ! in_array( $type, rbfw_mi_source_supported_types(), true ) ) {
			return __( 'this rental type has no single stock pool', 'booking-and-rental-manager-for-woocommerce' );
		}

		return '';
	}
}

if ( ! function_exists( 'rbfw_mi_source_title' ) ) {
	/**
	 * Plain-text title of a rental item, safe to escape once.
	 *
	 * get_the_title() runs the `the_title` filters, and wptexturize() emits HTML
	 * ENTITIES rather than characters — a plain "-" between words comes back as
	 * "&#8211;". Escaping that again turns the ampersand into "&amp;", so the
	 * browser prints the literal text "&#8211;" instead of an en dash. Decoding
	 * first gives a real UTF-8 string that esc_html()/esc_attr() leave alone, and
	 * that is also correct to store as an item name or send through JSON.
	 *
	 * @param int $post_id rbfw_item id.
	 * @return string
	 */
	function rbfw_mi_source_title( $post_id ) {
		$title = get_the_title( $post_id );
		if ( '' === $title || null === $title ) {
			return '';
		}

		return trim( html_entity_decode( (string) $title, ENT_QUOTES, 'UTF-8' ) );
	}
}

if ( ! function_exists( 'rbfw_mi_get_source_options' ) ) {
	/**
	 * Rental items offered in the admin "Existing Rental Item" dropdown.
	 *
	 * @param int $exclude_id Item currently being edited (never links to itself).
	 * @return array<int,string> id => title.
	 */
	function rbfw_mi_get_source_options( $exclude_id = 0 ) {
		static $cache = array();

		$exclude_id = absint( $exclude_id );
		if ( isset( $cache[ $exclude_id ] ) ) {
			return $cache[ $exclude_id ];
		}

		$items = get_posts(
			array(
				'post_type'        => RBFW_Function::get_cpt_name(),
				'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page'   => -1,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'fields'           => 'ids',
				'suppress_filters' => false,
				'no_found_rows'    => true,
			)
		);
		$items = array_map( 'absint', (array) $items );

		/* 'fields' => 'ids' skips WP's meta priming, and every candidate below is
		   qualified by reading two meta keys — without this the dropdown costs one
		   query per rental item on a page that renders for every item type. */
		if ( ! empty( $items ) ) {
			update_meta_cache( 'post', $items );
		}

		$options = array();
		foreach ( $items as $item_id ) {
			if ( $item_id === $exclude_id ) {
				continue;
			}
			if ( ! rbfw_mi_is_valid_source( $item_id ) ) {
				continue;
			}
			$options[ $item_id ] = rbfw_mi_source_title( $item_id );
		}

		$cache[ $exclude_id ] = $options;

		return $options;
	}
}

if ( ! function_exists( 'rbfw_mi_get_ineligible_sources' ) ) {
	/**
	 * Rental items that CANNOT be linked, each with the reason why.
	 *
	 * Listed in the editor as disabled options so an admin can see their item is
	 * known about and what to change, instead of a silently short dropdown.
	 *
	 * @param int $exclude_id Item currently being edited.
	 * @return array<int,string> id => "Title — reason".
	 */
	function rbfw_mi_get_ineligible_sources( $exclude_id = 0 ) {
		static $cache = array();

		$exclude_id = absint( $exclude_id );
		if ( isset( $cache[ $exclude_id ] ) ) {
			return $cache[ $exclude_id ];
		}

		$items = get_posts(
			array(
				'post_type'        => RBFW_Function::get_cpt_name(),
				'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page'   => -1,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'fields'           => 'ids',
				'suppress_filters' => false,
				'no_found_rows'    => true,
			)
		);
		$items = array_map( 'absint', (array) $items );
		if ( ! empty( $items ) ) {
			update_meta_cache( 'post', $items );
		}

		$out = array();
		foreach ( $items as $item_id ) {
			if ( $item_id === $exclude_id ) {
				continue;
			}
			$reason = rbfw_mi_source_ineligible_reason( $item_id );
			if ( '' === $reason ) {
				continue;
			}
			$out[ $item_id ] = sprintf(
				/* translators: 1: rental item name, 2: why it cannot share its stock. */
				__( '%1$s — %2$s', 'booking-and-rental-manager-for-woocommerce' ),
				rbfw_mi_source_title( $item_id ),
				$reason
			);
		}

		$cache[ $exclude_id ] = $out;

		return $out;
	}
}

/* -------------------------------------------------------------------------
 * Reverse index: which Multiple Items rentals consume a given source item.
 * ---------------------------------------------------------------------- */

if ( ! function_exists( 'rbfw_mi_build_source_map' ) ) {
	/**
	 * Rebuild the source => parents index from every Multiple Items rental.
	 *
	 * @return array<int,int[]>
	 */
	function rbfw_mi_build_source_map() {
		$parents = get_posts(
			array(
				'post_type'        => RBFW_Function::get_cpt_name(),
				/* Trashed packages are indexed on purpose: their existing orders are
				   still real bookings that hold units of the linked item. 'any' would
				   drop them and quietly free stock that is not actually free. */
				'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'suppress_filters' => false,
				'no_found_rows'    => true,
				'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'rbfw_item_type',
						'value'   => 'multiple_items',
						'compare' => '=',
					),
				),
			)
		);

		$parents = array_map( 'absint', (array) $parents );
		if ( ! empty( $parents ) ) {
			// 'fields' => 'ids' skips meta priming; one query beats one per parent.
			update_meta_cache( 'post', $parents );
		}

		$map = array();
		foreach ( $parents as $parent_id ) {
			$rows = get_post_meta( $parent_id, 'multiple_items_info', true );
			if ( ! is_array( $rows ) ) {
				continue;
			}
			foreach ( $rows as $row ) {
				$source_id = ( is_array( $row ) && isset( $row['source_id'] ) ) ? absint( $row['source_id'] ) : 0;
				if ( ! $source_id ) {
					continue;
				}
				if ( ! isset( $map[ $source_id ] ) ) {
					$map[ $source_id ] = array();
				}
				if ( ! in_array( (int) $parent_id, $map[ $source_id ], true ) ) {
					$map[ $source_id ][] = (int) $parent_id;
				}
			}
		}

		update_option( RBFW_MI_SOURCE_MAP_OPTION, $map, false );

		return $map;
	}
}

if ( ! function_exists( 'rbfw_mi_get_source_parents' ) ) {
	/**
	 * Multiple Items rentals that (may) consume a source item.
	 *
	 * The index is only a hint — every caller re-reads the parent's rows before
	 * counting anything, so a stale entry can never inflate a booked figure.
	 *
	 * @param int $source_id rbfw_item id.
	 * @return int[]
	 */
	function rbfw_mi_get_source_parents( $source_id ) {
		$source_id = absint( $source_id );
		if ( ! $source_id ) {
			return array();
		}

		$map = get_option( RBFW_MI_SOURCE_MAP_OPTION, null );
		if ( ! is_array( $map ) ) {
			$map = rbfw_mi_build_source_map();
		}

		return isset( $map[ $source_id ] ) ? array_map( 'absint', (array) $map[ $source_id ] ) : array();
	}
}

if ( ! function_exists( 'rbfw_mi_sync_source_map' ) ) {
	/**
	 * Fold one parent's current rows into the index after a save.
	 *
	 * @param int   $parent_id Multiple Items rental id.
	 * @param array $rows      Its saved multiple_items_info rows.
	 * @return void
	 */
	function rbfw_mi_sync_source_map( $parent_id, $rows ) {
		$parent_id = absint( $parent_id );
		if ( ! $parent_id ) {
			return;
		}

		$map = get_option( RBFW_MI_SOURCE_MAP_OPTION, null );
		if ( ! is_array( $map ) ) {
			rbfw_mi_build_source_map();

			return;
		}

		// Drop this parent everywhere, then re-add it for its current sources.
		foreach ( $map as $source_id => $parent_ids ) {
			$filtered = array_values( array_diff( array_map( 'absint', (array) $parent_ids ), array( $parent_id ) ) );
			if ( empty( $filtered ) ) {
				unset( $map[ $source_id ] );
			} else {
				$map[ $source_id ] = $filtered;
			}
		}

		foreach ( (array) $rows as $row ) {
			$source_id = ( is_array( $row ) && isset( $row['source_id'] ) ) ? absint( $row['source_id'] ) : 0;
			if ( ! $source_id ) {
				continue;
			}
			if ( ! isset( $map[ $source_id ] ) ) {
				$map[ $source_id ] = array();
			}
			if ( ! in_array( $parent_id, $map[ $source_id ], true ) ) {
				$map[ $source_id ][] = $parent_id;
			}
		}

		update_option( RBFW_MI_SOURCE_MAP_OPTION, $map, false );
	}
}

/* -------------------------------------------------------------------------
 * Counting.
 * ---------------------------------------------------------------------- */

if ( ! function_exists( 'rbfw_mi_windows_overlap' ) ) {
	/**
	 * Overlap test used by every shared-inventory count.
	 *
	 * Windows are treated as half-open — an item handed back at 15:00 is free
	 * for the next customer at 15:00 — which is the same rule the timely
	 * (hourly) engine uses and the model these bundles are booked under. Bookings
	 * stored without a time collapse to midnight, so a whole-day booking still
	 * overlaps any request on that day.
	 *
	 * @param DateTime $a_start Window A start.
	 * @param DateTime $a_end   Window A end.
	 * @param DateTime $b_start Window B start.
	 * @param DateTime $b_end   Window B end.
	 * @return bool
	 */
	function rbfw_mi_windows_overlap( $a_start, $a_end, $b_start, $b_end ) {
		if ( $a_start == $a_end || $b_start == $b_end ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- DateTime value comparison.
			// Zero-length (date-only) window: fall back to inclusive containment.
			return ( $a_start <= $b_end && $b_start <= $a_end );
		}

		return ( $a_start < $b_end && $b_start < $a_end );
	}
}

if ( ! function_exists( 'rbfw_mi_entry_units_for_source' ) ) {
	/**
	 * Units of one source item held by a single stored inventory record.
	 *
	 * Bookings taken after this feature shipped carry `source_id` on the line,
	 * which survives renaming the row. Older records are matched by item name
	 * against the parent's current rows so historic bundles still hold stock.
	 *
	 * @param array    $entry      One rbfw_inventory record on a Multiple Items rental.
	 * @param int      $source_id  Source rbfw_item id.
	 * @param string[] $row_names  Row names currently bound to that source on the parent.
	 * @return int
	 */
	function rbfw_mi_entry_units_for_source( $entry, $source_id, $row_names ) {
		$lines = ( isset( $entry['rbfw_service_info'] ) && is_array( $entry['rbfw_service_info'] ) )
			? $entry['rbfw_service_info']
			: array();

		$units = 0;
		foreach ( $lines as $line ) {
			if ( ! is_array( $line ) ) {
				continue;
			}
			$line_source = isset( $line['source_id'] ) ? absint( $line['source_id'] ) : 0;
			if ( $line_source ) {
				$matches = ( $line_source === (int) $source_id );
			} else {
				$name    = isset( $line['item_name'] ) ? (string) $line['item_name'] : '';
				$matches = ( '' !== $name && in_array( $name, $row_names, true ) );
			}
			if ( $matches ) {
				$units += isset( $line['item_qty'] ) ? max( 0, (int) $line['item_qty'] ) : 0;
			}
		}

		return $units;
	}
}

if ( ! function_exists( 'rbfw_mi_bundle_booked_units' ) ) {
	/**
	 * Units of a source item already committed through Multiple Items bookings.
	 *
	 * Walks every Multiple Items rental that links to the source and sums the
	 * quantities of its stock-holding orders whose window overlaps the request.
	 *
	 * @param int    $source_id        Source rbfw_item id.
	 * @param string $start_dt         Requested pickup ("Y-m-d H:i").
	 * @param string $end_dt           Requested dropoff.
	 * @param array  $args             'exclude_order_id' => int, 'exclude_parent_order' => array(parent_id => order_id).
	 * @return int
	 */
	function rbfw_mi_bundle_booked_units( $source_id, $start_dt, $end_dt, $args = array() ) {
		$source_id = absint( $source_id );
		if ( ! $source_id || ! rbfw_mi_shared_inventory_enabled() ) {
			return 0;
		}

		$parents = rbfw_mi_get_source_parents( $source_id );
		if ( empty( $parents ) ) {
			return 0;
		}

		try {
			$req_start = new DateTime( $start_dt );
			$req_end   = new DateTime( $end_dt );
		} catch ( Exception $e ) {
			return 0;
		}

		$exclude_order_id = isset( $args['exclude_order_id'] ) ? (int) $args['exclude_order_id'] : 0;
		$blocking         = rbfw_get_blocking_order_statuses();
		$deposit_mode     = get_option( 'mepp_reduce_stock', 'full' );

		$total = 0;
		foreach ( $parents as $parent_id ) {
			$rows = get_post_meta( $parent_id, 'multiple_items_info', true );
			if ( ! is_array( $rows ) ) {
				continue;
			}

			// Row names currently bound to this source — the fallback matcher for
			// bookings taken before source_id was stored on the line.
			$row_names = array();
			foreach ( $rows as $row ) {
				if ( is_array( $row ) && isset( $row['source_id'] ) && absint( $row['source_id'] ) === $source_id && ! empty( $row['item_name'] ) ) {
					$row_names[] = (string) $row['item_name'];
				}
			}

			$inventory = get_post_meta( $parent_id, 'rbfw_inventory', true );
			if ( empty( $inventory ) || ! is_array( $inventory ) ) {
				continue;
			}

			foreach ( $inventory as $order_id => $entry ) {
				if ( ! is_array( $entry ) ) {
					continue;
				}
				if ( $exclude_order_id && (int) $order_id === $exclude_order_id ) {
					continue;
				}

				$status = isset( $entry['rbfw_order_status'] ) ? $entry['rbfw_order_status'] : '';
				if ( ! in_array( $status, $blocking, true ) ) {
					continue;
				}
				if ( 'partially-paid' === $status && 'deposit' === $deposit_mode ) {
					continue;
				}

				$inv_start_date = isset( $entry['rbfw_start_date_ymd'] ) ? $entry['rbfw_start_date_ymd'] : '';
				$inv_end_date   = isset( $entry['rbfw_end_date_ymd'] ) ? $entry['rbfw_end_date_ymd'] : '';
				if ( empty( $inv_start_date ) || empty( $inv_end_date ) ) {
					continue;
				}
				$inv_start_time = isset( $entry['rbfw_start_time_24'] ) ? $entry['rbfw_start_time_24'] : '';
				$inv_end_time   = isset( $entry['rbfw_end_time_24'] ) ? $entry['rbfw_end_time_24'] : '';
				if ( false !== strpos( $inv_start_date . $inv_end_date . $inv_start_time . $inv_end_time, 'NaN' ) ) {
					continue;
				}

				try {
					$inv_start = new DateTime( trim( $inv_start_date . ' ' . $inv_start_time ) );
					$inv_end   = new DateTime( trim( $inv_end_date . ' ' . $inv_end_time ) );
				} catch ( Exception $e ) {
					continue;
				}

				if ( ! rbfw_mi_windows_overlap( $inv_start, $inv_end, $req_start, $req_end ) ) {
					continue;
				}

				$total += rbfw_mi_entry_units_for_source( $entry, $source_id, $row_names );
			}
		}

		return $total;
	}
}

if ( ! function_exists( 'rbfw_mi_cart_units_for_source' ) ) {
	/**
	 * Units of a source item held by cart lines that are not yet an order.
	 *
	 * Counts both plain rentals of the source itself and Multiple Items lines
	 * whose rows point at it, so a customer cannot combine an individual booking
	 * and a bundle to oversubscribe the same pool inside one checkout.
	 *
	 * @param int    $source_id Source rbfw_item id.
	 * @param array  $lines     Cart line value arrays to consider.
	 * @param string $start_dt  Requested pickup.
	 * @param string $end_dt    Requested dropoff.
	 * @param array  $args      'bundles_only' => true to skip plain lines of the source
	 *                          itself, for callers that already count those as siblings.
	 * @return int
	 */
	function rbfw_mi_cart_units_for_source( $source_id, $lines, $start_dt, $end_dt, $args = array() ) {
		$source_id = absint( $source_id );
		if ( ! $source_id || empty( $lines ) || ! is_array( $lines ) ) {
			return 0;
		}

		try {
			$req_start = new DateTime( $start_dt );
			$req_end   = new DateTime( $end_dt );
		} catch ( Exception $e ) {
			return 0;
		}

		$total = 0;
		foreach ( $lines as $line ) {
			if ( ! is_array( $line ) ) {
				continue;
			}
			$line_start = ! empty( $line['rbfw_start_datetime'] ) ? $line['rbfw_start_datetime'] : '';
			$line_end   = ! empty( $line['rbfw_end_datetime'] ) ? $line['rbfw_end_datetime'] : '';
			if ( '' === $line_start || '' === $line_end ) {
				continue;
			}
			try {
				$ls = new DateTime( $line_start );
				$le = new DateTime( $line_end );
			} catch ( Exception $e ) {
				continue;
			}
			if ( ! rbfw_mi_windows_overlap( $ls, $le, $req_start, $req_end ) ) {
				continue;
			}

			$line_item_id = isset( $line['rbfw_id'] ) ? absint( $line['rbfw_id'] ) : 0;

			if ( $line_item_id === $source_id ) {
				if ( empty( $args['bundles_only'] ) ) {
					$total += isset( $line['rbfw_item_quantity'] ) ? max( 1, (int) $line['rbfw_item_quantity'] ) : 1;
				}
				continue;
			}

			$mi_lines = ( isset( $line['multiple_items_info'] ) && is_array( $line['multiple_items_info'] ) )
				? $line['multiple_items_info']
				: array();
			foreach ( $mi_lines as $mi ) {
				if ( is_array( $mi ) && isset( $mi['source_id'] ) && absint( $mi['source_id'] ) === $source_id ) {
					$total += isset( $mi['item_qty'] ) ? max( 0, (int) $mi['item_qty'] ) : 0;
				}
			}
		}

		return $total;
	}
}

if ( ! function_exists( 'rbfw_mi_cart_bundle_units' ) ) {
	/**
	 * Units of an item held by Multiple Items cart lines only.
	 *
	 * Used when validating the individual rental, whose own cart lines are
	 * already counted as siblings — counting them again here would double-book
	 * the customer against themselves.
	 *
	 * @param int    $source_id Source rbfw_item id.
	 * @param array  $lines     Cart line value arrays.
	 * @param string $start_dt  Requested pickup.
	 * @param string $end_dt    Requested dropoff.
	 * @return int
	 */
	function rbfw_mi_cart_bundle_units( $source_id, $lines, $start_dt, $end_dt ) {
		if ( ! is_array( $lines ) ) {
			return 0;
		}

		return rbfw_mi_cart_units_for_source( $source_id, $lines, $start_dt, $end_dt, array( 'bundles_only' => true ) );
	}
}

if ( ! function_exists( 'rbfw_mi_source_direct_booked' ) ) {
	/**
	 * Units of a source item booked on the item itself (not through a package).
	 *
	 * Deliberately measured with whichever rule governs that item's OWN booking
	 * form, so a package never shows a different figure from the individual
	 * rental page for the same window:
	 *
	 *  - timely single-day items use half-open windows (a boat handed back at
	 *    15:00 is bookable again at 15:00), mirroring
	 *    rbfw_timely_available_quantity_updated();
	 *  - multi-day items go through rbfw_count_overlapping_booked_qty(), which
	 *    also applies their buffer and return-date settings.
	 *
	 * @param int    $source_id        Source rbfw_item id.
	 * @param string $start_dt         Requested pickup.
	 * @param string $end_dt           Requested dropoff.
	 * @param int    $exclude_order_id Order to ignore (booking edit).
	 * @return int
	 */
	function rbfw_mi_source_direct_booked( $source_id, $start_dt, $end_dt, $exclude_order_id = 0 ) {
		$source_id = absint( $source_id );
		if ( ! $source_id ) {
			return 0;
		}

		if ( get_post_meta( $source_id, 'rbfw_item_type', true ) !== 'bike_car_sd' ) {
			return (int) rbfw_count_overlapping_booked_qty(
				$source_id,
				$start_dt,
				$end_dt,
				array(
					'exclude_order_id' => $exclude_order_id,
					'skip_shared'      => true, // packages are counted separately.
				)
			);
		}

		$inventory = get_post_meta( $source_id, 'rbfw_inventory', true );
		if ( empty( $inventory ) || ! is_array( $inventory ) ) {
			return 0;
		}

		try {
			$req_start = new DateTime( $start_dt );
			$req_end   = new DateTime( $end_dt );
		} catch ( Exception $e ) {
			return 0;
		}

		$blocking     = rbfw_get_blocking_order_statuses();
		$deposit_mode = get_option( 'mepp_reduce_stock', 'full' );
		$booked       = 0;

		foreach ( $inventory as $order_id => $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			if ( $exclude_order_id && (int) $order_id === $exclude_order_id ) {
				continue;
			}

			$status = isset( $entry['rbfw_order_status'] ) ? $entry['rbfw_order_status'] : '';
			if ( ! in_array( $status, $blocking, true ) ) {
				continue;
			}
			if ( 'partially-paid' === $status && 'deposit' === $deposit_mode ) {
				continue;
			}

			$inv_start_date = isset( $entry['rbfw_start_date_ymd'] ) ? $entry['rbfw_start_date_ymd'] : '';
			$inv_end_date   = isset( $entry['rbfw_end_date_ymd'] ) ? $entry['rbfw_end_date_ymd'] : '';
			$inv_start_time = isset( $entry['rbfw_start_time_24'] ) ? $entry['rbfw_start_time_24'] : '';
			$inv_end_time   = isset( $entry['rbfw_end_time_24'] ) ? $entry['rbfw_end_time_24'] : '';

			if ( empty( $inv_start_date ) || empty( $inv_end_date ) ) {
				// Pre-normalisation records only carry the booked-date list.
				$booked_dates = ( isset( $entry['booked_dates'] ) && is_array( $entry['booked_dates'] ) ) ? $entry['booked_dates'] : array();
				if ( empty( $booked_dates ) ) {
					continue;
				}
				$inv_start_date = gmdate( 'Y-m-d', strtotime( reset( $booked_dates ) ) );
				$inv_end_date   = gmdate( 'Y-m-d', strtotime( end( $booked_dates ) ) );
				$inv_start_time = isset( $entry['rbfw_start_time'] ) ? $entry['rbfw_start_time'] : '';
				$inv_end_time   = isset( $entry['rbfw_end_time'] ) ? $entry['rbfw_end_time'] : '';
			}

			if ( false !== strpos( $inv_start_date . $inv_end_date . $inv_start_time . $inv_end_time, 'NaN' ) ) {
				continue;
			}

			try {
				$inv_start = new DateTime( trim( $inv_start_date . ' ' . $inv_start_time ) );
				$inv_end   = new DateTime( trim( $inv_end_date . ' ' . $inv_end_time ) );
			} catch ( Exception $e ) {
				continue;
			}

			if ( rbfw_mi_windows_overlap( $inv_start, $inv_end, $req_start, $req_end ) ) {
				$booked += isset( $entry['rbfw_item_quantity'] ) ? max( 0, (int) $entry['rbfw_item_quantity'] ) : 0;
			}
		}

		return $booked;
	}
}

if ( ! function_exists( 'rbfw_mi_source_remaining' ) ) {
	/**
	 * Units of a source item still free for a window, across every consumer.
	 *
	 * stock - booked directly on the item - booked through any bundle - held by
	 * competing cart lines.
	 *
	 * @param int    $source_id Source rbfw_item id.
	 * @param string $start_dt  Requested pickup.
	 * @param string $end_dt    Requested dropoff.
	 * @param array  $args      'exclude_order_id' => int, 'cart_lines' => array.
	 * @return int
	 */
	function rbfw_mi_source_remaining( $source_id, $start_dt, $end_dt, $args = array() ) {
		$source_id = absint( $source_id );
		if ( ! $source_id ) {
			return 0;
		}

		$exclude_order_id = isset( $args['exclude_order_id'] ) ? (int) $args['exclude_order_id'] : 0;
		$stock            = rbfw_mi_source_stock( $source_id );

		$direct = rbfw_mi_source_direct_booked( $source_id, $start_dt, $end_dt, $exclude_order_id );

		$bundled = rbfw_mi_bundle_booked_units( $source_id, $start_dt, $end_dt, array( 'exclude_order_id' => $exclude_order_id ) );

		$in_cart = isset( $args['cart_lines'] )
			? rbfw_mi_cart_units_for_source( $source_id, $args['cart_lines'], $start_dt, $end_dt )
			: 0;

		return max( 0, $stock - $direct - $bundled - $in_cart );
	}
}

if ( ! function_exists( 'rbfw_mi_row_available_qty' ) ) {
	/**
	 * Units of one Multiple Items row a customer may still select.
	 *
	 * For a linked row the configured `available_qty` stays meaningful as the
	 * per-bundle cap ("offer at most N of these through this package"), and the
	 * shared pool caps it further. Unlinked rows keep their original private
	 * counter untouched.
	 *
	 * @param int    $parent_id Multiple Items rental id.
	 * @param array  $row       The row.
	 * @param string $start_dt  Requested pickup.
	 * @param string $end_dt    Requested dropoff.
	 * @param array  $args      Forwarded to rbfw_mi_source_remaining().
	 * @return int|null Remaining units, or null when the row is not linked.
	 */
	function rbfw_mi_row_available_qty( $parent_id, $row, $start_dt, $end_dt, $args = array() ) {
		$source_id = rbfw_mi_row_source_id( $row );
		if ( ! $source_id ) {
			return null;
		}

		$remaining = rbfw_mi_source_remaining( $source_id, $start_dt, $end_dt, $args );
		$cap       = isset( $row['available_qty'] ) ? (int) $row['available_qty'] : 0;
		$available = ( $cap > 0 ) ? min( $cap, $remaining ) : $remaining;

		/**
		 * Filter the units of a linked Multiple Items row a customer may select.
		 *
		 * @param int    $available Remaining units after the shared pool and the row cap.
		 * @param int    $parent_id Multiple Items rental id.
		 * @param array  $row       The row (or booked line).
		 * @param string $start_dt  Requested pickup.
		 * @param string $end_dt    Requested dropoff.
		 */
		return (int) apply_filters( 'rbfw_mi_row_available_qty', $available, $parent_id, $row, $start_dt, $end_dt );
	}
}

if ( ! function_exists( 'rbfw_mi_row_display_stock' ) ) {
	/**
	 * Headline stock shown for a row before any date is chosen.
	 *
	 * @param array $row One multiple_items_info row.
	 * @return int
	 */
	function rbfw_mi_row_display_stock( $row ) {
		$source_id = rbfw_mi_row_source_id( $row );
		$cap       = isset( $row['available_qty'] ) ? (int) $row['available_qty'] : 0;
		if ( ! $source_id ) {
			return $cap;
		}
		$stock = rbfw_mi_source_stock( $source_id );

		return ( $cap > 0 ) ? min( $cap, $stock ) : $stock;
	}
}

if ( ! function_exists( 'rbfw_mi_bundle_units_on_date' ) ) {
	/**
	 * Units of a source item held by bundle bookings on one calendar day.
	 *
	 * Day granularity for the month calendar, which paints sold-out dates rather
	 * than windows.
	 *
	 * @param int    $source_id Source rbfw_item id.
	 * @param string $date_ymd  Date in "Y-m-d".
	 * @return int
	 */
	function rbfw_mi_bundle_units_on_date( $source_id, $date_ymd ) {
		return rbfw_mi_bundle_booked_units( $source_id, $date_ymd . ' 00:00', $date_ymd . ' 23:59' );
	}
}

if ( ! function_exists( 'rbfw_mi_flush_source_map' ) ) {
	/**
	 * Drop the index when a rental item disappears, so it is rebuilt on demand.
	 *
	 * The hook fires after the row is gone, so the post type is read from the
	 * passed object — get_post_type() on a deleted id returns false and the flush
	 * would never run.
	 *
	 * @param int          $post_id Deleted post id.
	 * @param WP_Post|null $post    The post that was deleted (WP 5.5+).
	 * @return void
	 */
	function rbfw_mi_flush_source_map( $post_id, $post = null ) {
		$post_type = ( $post instanceof WP_Post ) ? $post->post_type : get_post_type( $post_id );
		if ( $post_type !== RBFW_Function::get_cpt_name() ) {
			return;
		}
		delete_option( RBFW_MI_SOURCE_MAP_OPTION );
	}
}
add_action( 'deleted_post', 'rbfw_mi_flush_source_map', 10, 2 );
