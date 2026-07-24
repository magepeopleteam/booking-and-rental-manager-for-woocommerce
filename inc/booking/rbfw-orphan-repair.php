<?php
/**
 * One-off repair for reservation records orphaned by a deleted WooCommerce order.
 *
 * Symptom this fixes: the Booking Calendar keeps showing bookings that the Bookings
 * (Booking Orders) page no longer lists, and the item's availability is understated,
 * because the booking's `rbfw_order_meta` reservation record and its entry in the
 * item's `rbfw_inventory` map were never retired when the order was deleted.
 *
 * Root cause is fixed going forward by RBFW_Reservation_Sync (hooks + sweep). This
 * script only repairs records orphaned BEFORE that class was in place.
 *
 * Run it with WP-CLI from the WordPress root:
 *
 *     wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/inc/booking/rbfw-orphan-repair.php
 *
 * Add --dry-run to preview without writing:
 *
 *     wp eval-file .../rbfw-orphan-repair.php dry
 *
 * Safe to re-run: it is idempotent, and every status it changes is stashed in
 * `_rbfw_status_before_trash` so a restore from Trash puts the booking back.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$rbfw_repair_dry_run = ( isset( $args[0] ) && 'dry' === $args[0] );

if ( ! ( function_exists( 'rbfw_has_woocommerce' ) ? rbfw_has_woocommerce() : class_exists( 'WooCommerce' ) ) ) {
	echo "WooCommerce is not active — cannot tell a deleted order from an unavailable order store. Aborting.\n";
	return;
}

/**
 * Has the booking behind this order id ceased to exist? True when the order is
 * trashed, permanently deleted, or its rbfw_order mirror sits in the Trash.
 */
$rbfw_order_is_gone = static function ( $order_id ) {
	$order_id = absint( $order_id );
	if ( ! $order_id ) {
		return false;
	}

	$mirror_trashed = static function ( $oid ) {
		$q = new WP_Query( array(
			'post_type'      => 'rbfw_order',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation' => 'OR',
				array( 'key' => 'rbfw_link_order_id', 'value' => $oid ),
				array( 'key' => 'rbfw_order_id', 'value' => $oid ),
			),
		) );
		if ( ! $q->posts ) {
			return false;
		}
		foreach ( $q->posts as $pid ) {
			if ( 'trash' !== get_post_status( $pid ) ) {
				return false;
			}
		}
		return true;
	};

	$order = wc_get_order( $order_id );
	if ( $order && is_a( $order, 'WC_Order' ) ) {
		return ( 'trash' === $order->get_status() ) ? true : $mirror_trashed( $order_id );
	}
	$post = get_post( $order_id );
	if ( $post ) {
		return 'trash' === $post->post_status;
	}
	return true; // permanently deleted
};

$excluded = apply_filters( 'rbfw_calendar_excluded_statuses', array( 'cancelled', 'failed', 'refunded', 'trash' ) );

$reservations = new WP_Query( array(
	'post_type'      => 'rbfw_order_meta',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'no_found_rows'  => true,
	'meta_query'     => array(
		array( 'key' => 'rbfw_order_status', 'value' => $excluded, 'compare' => 'NOT IN' ),
	),
) );

$gone_orders = array();
$hit_records = array();

foreach ( $reservations->posts as $pid ) {
	$oid = (int) get_post_meta( $pid, 'rbfw_link_order_id', true );
	if ( ! $oid ) {
		$oid = (int) get_post_meta( $pid, 'rbfw_order_id', true );
	}
	if ( ! $oid ) {
		continue; // unlinked / native booking — not ours to judge.
	}
	if ( ! isset( $gone_orders[ $oid ] ) ) {
		$gone_orders[ $oid ] = $rbfw_order_is_gone( $oid );
	}
	if ( $gone_orders[ $oid ] ) {
		$hit_records[] = array( 'post' => $pid, 'order' => $oid, 'status' => get_post_meta( $pid, 'rbfw_order_status', true ) );
	}
}

echo sprintf(
	"Reservation records still shown as live: %d\nOf those, orphaned (order trashed/deleted or mirror trashed): %d\n",
	count( $reservations->posts ),
	count( $hit_records )
);

foreach ( $hit_records as $r ) {
	echo sprintf( "  reservation #%d  order #%d  status '%s' -> trash\n", $r['post'], $r['order'], $r['status'] );
}

/* ---- inventory entries held by orders that no longer exist ---- */

$items = new WP_Query( array(
	'post_type'      => 'rbfw_item',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'no_found_rows'  => true,
	'meta_query'     => array( array( 'key' => 'rbfw_inventory', 'compare' => 'EXISTS' ) ),
) );

$inventory_hits = 0;
$inventory_plan = array();

foreach ( $items->posts as $item_id ) {
	$inventory = get_post_meta( $item_id, 'rbfw_inventory', true );
	if ( ! is_array( $inventory ) ) {
		continue;
	}
	foreach ( $inventory as $oid => $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}
		$status = isset( $entry['rbfw_order_status'] ) ? (string) $entry['rbfw_order_status'] : '';
		if ( 'trash' === $status ) {
			continue;
		}
		if ( ! isset( $gone_orders[ $oid ] ) ) {
			$gone_orders[ $oid ] = $rbfw_order_is_gone( $oid );
		}
		if ( $gone_orders[ $oid ] ) {
			$inventory_plan[ $item_id ][ $oid ] = $status;
			$inventory_hits++;
		}
	}
}

echo sprintf( "Inventory entries held by orders that no longer exist: %d\n", $inventory_hits );

if ( $rbfw_repair_dry_run ) {
	echo "\nDRY RUN — nothing written. Re-run without 'dry' to apply.\n";
	return;
}

foreach ( $hit_records as $r ) {
	if ( '' !== $r['status'] ) {
		update_post_meta( $r['post'], '_rbfw_status_before_trash', $r['status'] );
	}
	update_post_meta( $r['post'], 'rbfw_order_status', 'trash' );
}

foreach ( $inventory_plan as $item_id => $orders ) {
	$inventory = get_post_meta( $item_id, 'rbfw_inventory', true );
	if ( ! is_array( $inventory ) ) {
		continue;
	}
	foreach ( $orders as $oid => $prev ) {
		if ( ! isset( $inventory[ $oid ] ) || ! is_array( $inventory[ $oid ] ) ) {
			continue;
		}
		if ( '' !== $prev ) {
			$inventory[ $oid ]['_rbfw_status_before_trash'] = $prev;
		}
		$inventory[ $oid ]['rbfw_order_status'] = 'trash';
	}
	update_post_meta( $item_id, 'rbfw_inventory', $inventory );
}

echo sprintf(
	"\nDone. Retired %d reservation record(s) and released %d inventory entr(ies).\n",
	count( $hit_records ),
	$inventory_hits
);
