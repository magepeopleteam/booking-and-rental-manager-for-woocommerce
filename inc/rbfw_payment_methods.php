<?php
/**
 * Accounting payment methods (card / cheque / cash / bank transfer).
 *
 * A rental shop takes money in ways WooCommerce never sees: a cheque handed over at the
 * counter, cash on pickup, a bank transfer that lands days later. For the books, what
 * matters is HOW the customer actually paid — not which gateway object processed the row.
 *
 * This is the registry for those methods. It backs three things:
 *   1. the options a customer picks from at the standalone (non-WooCommerce) checkout,
 *   2. the method an admin records on ANY booking after the fact,
 *   3. the filter / column / per-method totals used for reconciliation.
 *
 * IMPORTANT — WooCommerce bookings.
 * For a WooCommerce order the gateway that processed it is a matter of record and must
 * stay truthful, so the accounting method is written to its OWN meta key and never
 * overwrites `_payment_method` / `_payment_method_title`. A shop that takes a card payment
 * in person against an order placed as "Cash on delivery" gets correct books without
 * corrupting WooCommerce's own reporting.
 *
 * @package booking-and-rental-manager-for-woocommerce
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Where the editable method list lives (inside the existing payment settings option). */
if ( ! defined( 'RBFW_PAYMENT_METHODS_OPTION' ) ) {
	define( 'RBFW_PAYMENT_METHODS_OPTION', 'rbfw_payment_settings' );
}

/** Meta key holding the accounting method, on both booking post types and on WC orders. */
if ( ! defined( 'RBFW_ACCOUNTING_METHOD_META' ) ) {
	define( 'RBFW_ACCOUNTING_METHOD_META', '_rbfw_accounting_method' );
}

/**
 * The four methods a rental shop starts with. Editable in Settings → Payments.
 *
 * @return array<string,array{label:string,icon:string,instructions:string,enabled:bool}>
 */
function rbfw_payment_methods_defaults() {
	return array(
		'card'          => array(
			'label'        => __( 'Card', 'booking-and-rental-manager-for-woocommerce' ),
			'icon'         => 'dashicons-cart',
			'instructions' => '',
			'enabled'      => true,
		),
		'cheque'        => array(
			'label'        => __( 'Cheque', 'booking-and-rental-manager-for-woocommerce' ),
			'icon'         => 'dashicons-media-document',
			'instructions' => '',
			'enabled'      => true,
		),
		'cash'          => array(
			'label'        => __( 'Cash', 'booking-and-rental-manager-for-woocommerce' ),
			'icon'         => 'dashicons-money-alt',
			'instructions' => '',
			'enabled'      => true,
		),
		'bank_transfer' => array(
			'label'        => __( 'Bank Transfer', 'booking-and-rental-manager-for-woocommerce' ),
			'icon'         => 'dashicons-bank',
			'instructions' => '',
			'enabled'      => true,
		),
	);
}

/**
 * Normalize one stored method row.
 *
 * @param string $slug
 * @param mixed  $row
 * @return array{label:string,icon:string,instructions:string,enabled:bool}|null
 */
function rbfw_payment_method_normalize( $slug, $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}
	$label = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
	if ( '' === $label ) {
		// A method with no label cannot be shown or reconciled — drop it rather than
		// render a blank radio button the customer can still select.
		return null;
	}

	return array(
		'label'        => $label,
		'icon'         => isset( $row['icon'] ) ? sanitize_html_class( (string) $row['icon'] ) : '',
		'instructions' => isset( $row['instructions'] ) ? wp_kses_post( (string) $row['instructions'] ) : '',
		'enabled'      => ! isset( $row['enabled'] ) || (bool) $row['enabled'],
	);
}

/**
 * Every configured accounting method, keyed by slug.
 *
 * Read straight from the option (not rbfw_get_option) so it is also correct under WP-CLI
 * and cron, where rbfw_get_option() intentionally returns nothing.
 *
 * MIGRATION: installs that predate this feature have a single free-text `rbfw_offline_label`
 * on the Offline gateway. That value becomes the first method so an existing shop keeps the
 * wording its customers already see, instead of silently switching to "Card".
 *
 * @param bool $enabled_only   Return only methods enabled for use.
 * @param bool $force_refresh  Rebuild the per-request cache (after a save).
 * @return array<string,array{label:string,icon:string,instructions:string,enabled:bool}>
 */
function rbfw_payment_methods( $enabled_only = false, $force_refresh = false ) {
	static $cache = null;

	if ( null === $cache || $force_refresh ) {
		$opts = get_option( RBFW_PAYMENT_METHODS_OPTION, array() );
		$opts = is_array( $opts ) ? $opts : array();

		$stored = isset( $opts['rbfw_payment_methods'] ) ? $opts['rbfw_payment_methods'] : null;

		if ( ! is_array( $stored ) || empty( $stored ) ) {
			$methods = rbfw_payment_methods_defaults();

			// Carry a pre-existing custom Offline label into the first method.
			$legacy = isset( $opts['rbfw_offline_label'] ) ? trim( (string) $opts['rbfw_offline_label'] ) : '';
			if ( '' !== $legacy ) {
				$methods = array_merge(
					array( 'offline' => array(
						'label'        => sanitize_text_field( $legacy ),
						'icon'         => 'dashicons-money-alt',
						'instructions' => '',
						'enabled'      => true,
					) ),
					$methods
				);
			}
		} else {
			$methods = array();
			foreach ( $stored as $slug => $row ) {
				$slug = sanitize_key( $slug );
				if ( '' === $slug ) {
					continue;
				}
				$norm = rbfw_payment_method_normalize( $slug, $row );
				if ( $norm ) {
					$methods[ $slug ] = $norm;
				}
			}
		}

		/**
		 * Filter the accounting payment methods.
		 *
		 * @since 2.8.0
		 * @param array $methods slug => [ label, icon, instructions, enabled ]
		 */
		$cache = apply_filters( 'rbfw_payment_methods', $methods );
	}

	if ( ! $enabled_only ) {
		return $cache;
	}

	return array_filter( $cache, static function ( $m ) {
		return ! empty( $m['enabled'] );
	} );
}

/**
 * Drop the memoized registry so a just-saved list is visible in the same request.
 *
 * The settings screen saves and re-renders within one request, so without this the admin
 * would be shown the method list from before their own save.
 *
 * @return void
 */
function rbfw_payment_methods_flush_cache() {
	rbfw_payment_methods( false, true );
}
add_action( 'update_option_' . RBFW_PAYMENT_METHODS_OPTION, 'rbfw_payment_methods_flush_cache', 10, 0 );
add_action( 'add_option_' . RBFW_PAYMENT_METHODS_OPTION, 'rbfw_payment_methods_flush_cache', 10, 0 );

/**
 * Whether a slug is a method this shop actually offers.
 *
 * @param string $slug
 * @return bool
 */
function rbfw_is_valid_payment_method( $slug ) {
	$slug = sanitize_key( (string) $slug );
	return '' !== $slug && array_key_exists( $slug, rbfw_payment_methods() );
}

/**
 * Display label for a method slug, falling back to a readable version of an unknown slug
 * (a method the shop has since deleted must still render on the bookings it was used on).
 *
 * @param string $slug
 * @return string
 */
function rbfw_payment_method_label( $slug ) {
	$slug = (string) $slug;
	if ( '' === $slug ) {
		return '';
	}
	$methods = rbfw_payment_methods();
	if ( isset( $methods[ $slug ] ) ) {
		return $methods[ $slug ]['label'];
	}
	return ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
}

/**
 * The accounting method recorded on a booking, or '' when none was.
 *
 * Falls back to the gateway that processed the booking, so an untouched WooCommerce order
 * still reports something useful ("PayPal") instead of a blank cell.
 *
 * @param int $booking_id rbfw_booking or rbfw_order post id.
 * @return array{slug:string,label:string,is_accounting:bool}
 */
function rbfw_get_booking_payment_method( $booking_id ) {
	$booking_id = absint( $booking_id );
	$none       = array( 'slug' => '', 'label' => '', 'is_accounting' => false );
	if ( ! $booking_id ) {
		return $none;
	}

	$slug = (string) get_post_meta( $booking_id, RBFW_ACCOUNTING_METHOD_META, true );
	if ( '' !== $slug ) {
		return array(
			'slug'          => $slug,
			'label'         => rbfw_payment_method_label( $slug ),
			'is_accounting' => true,
		);
	}

	$post_type = get_post_type( $booking_id );

	// Native bookings store the chosen method directly.
	if ( 'rbfw_booking' === $post_type ) {
		$gateway = (string) get_post_meta( $booking_id, 'rbfw_payment_method', true );
		if ( '' !== $gateway && 'custom' !== $gateway ) {
			return array(
				'slug'          => rbfw_is_valid_payment_method( $gateway ) ? $gateway : '',
				'label'         => rbfw_payment_method_label( $gateway ),
				'is_accounting' => false,
			);
		}
		return $none;
	}

	// WooCommerce mirrors: the gateway title captured at checkout.
	$title = (string) get_post_meta( $booking_id, 'rbfw_payment_method_title', true );
	if ( '' !== $title ) {
		return array( 'slug' => '', 'label' => $title, 'is_accounting' => false );
	}

	return $none;
}

/**
 * Record how a booking was actually paid.
 *
 * Source-aware:
 *   - native `rbfw_booking` → writes the accounting meta AND `rbfw_payment_method`, because
 *     for a standalone booking there is no other record of how it was paid.
 *   - WooCommerce `rbfw_order` → writes the accounting meta on the mirror AND on the real
 *     order, and leaves the WooCommerce gateway fields untouched (see the file header).
 *
 * @param int    $booking_id rbfw_booking or rbfw_order post id.
 * @param string $slug       Method slug, or '' to clear.
 * @return true|WP_Error
 */
function rbfw_set_booking_payment_method( $booking_id, $slug ) {
	$booking_id = absint( $booking_id );
	$post_type  = $booking_id ? get_post_type( $booking_id ) : '';

	if ( ! in_array( $post_type, array( 'rbfw_booking', 'rbfw_order' ), true ) ) {
		return new WP_Error(
			'rbfw_invalid_booking',
			esc_html__( 'That booking could not be found.', 'booking-and-rental-manager-for-woocommerce' )
		);
	}

	$slug = sanitize_key( (string) $slug );

	if ( '' === $slug ) {
		delete_post_meta( $booking_id, RBFW_ACCOUNTING_METHOD_META );
	} elseif ( ! rbfw_is_valid_payment_method( $slug ) ) {
		return new WP_Error(
			'rbfw_invalid_method',
			esc_html__( 'That payment method is not one this shop offers.', 'booking-and-rental-manager-for-woocommerce' )
		);
	} else {
		update_post_meta( $booking_id, RBFW_ACCOUNTING_METHOD_META, $slug );
	}

	if ( 'rbfw_booking' === $post_type ) {
		// Standalone: the accounting method IS the payment record.
		if ( '' !== $slug ) {
			update_post_meta( $booking_id, 'rbfw_payment_method', $slug );
		}
		return true;
	}

	// WooCommerce: mirror it onto the real order for reporting/export, without ever
	// touching the gateway fields WooCommerce itself relies on.
	$wc_order_id = (int) get_post_meta( $booking_id, 'rbfw_order_id', true );
	if ( $wc_order_id && function_exists( 'wc_get_order' ) ) {
		$order = wc_get_order( $wc_order_id );
		if ( $order ) {
			if ( '' === $slug ) {
				$order->delete_meta_data( RBFW_ACCOUNTING_METHOD_META );
			} else {
				$order->update_meta_data( RBFW_ACCOUNTING_METHOD_META, $slug );
			}
			$order->save();
		}
	}

	return true;
}
