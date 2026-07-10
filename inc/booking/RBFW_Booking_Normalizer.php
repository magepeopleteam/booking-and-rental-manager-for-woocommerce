<?php
/**
 * RBFW_Booking_Normalizer — the single source of truth that lets one admin table
 * list BOTH kinds of rental booking the plugin can create:
 *
 *   - Native / custom bookings  → the `rbfw_booking` CPT (status in `rbfw_status` meta).
 *   - WooCommerce bookings       → the `rbfw_order` CPT, each linked to a real WC order
 *                                  via `rbfw_order_id` meta (status lives on the WC order,
 *                                  mirrored into `rbfw_order_status` meta).
 *
 * Design goals (see the feature brief):
 *   - SOURCE is detected cheaply from the post type — no per-row heuristics.
 *   - A single status map (label + CSS class) covers native AND WooCommerce statuses,
 *     tolerating a "wc-" prefix, so the renderers never hardcode status strings.
 *   - The real WooCommerce order object is resolved ONLY for the rows rendered on the
 *     current page (see hydrate()), never for the whole dataset — avoiding an N+1.
 *   - Every WooCommerce call is guarded with function_exists(), so the whole feature
 *     keeps working with WooCommerce inactive.
 *
 * This lives in the FREE plugin so both the free teaser list and the Pro "Bookings"
 * page consume the exact same normalization (no duplicated logic).
 *
 * @package booking-and-rental-manager-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_Normalizer' ) ) {

	class RBFW_Booking_Normalizer {

		/** Native / custom booking CPT. */
		const CPT_CUSTOM = 'rbfw_booking';

		/** WooCommerce-linked booking CPT (mirror of a real WC order). */
		const CPT_WOO = 'rbfw_order';

		/** Source identifiers. */
		const SOURCE_CUSTOM = 'custom';
		const SOURCE_WOO    = 'woocommerce';

		/**
		 * Per-request cache of resolved WC orders, keyed by the rbfw_order post id.
		 * Values are WC_Order objects or false (order missing / WooCommerce off).
		 *
		 * @var array<int,mixed>
		 */
		private static $wc_order_cache = array();

		/* ================================================================== *
		 * Source detection
		 * ================================================================== */

		/**
		 * Whether WooCommerce is actually active in this request.
		 *
		 * NOTE: do NOT test function_exists( 'wc_get_order' ) here — the free plugin ships
		 * WooCommerce fallback shims (inc/rbfw_wc_fallbacks.php) that define wc_get_order()
		 * (returning false) when WooCommerce is inactive, so that test is always true. The
		 * canonical, shim-proof check is rbfw_has_woocommerce() / class_exists( 'WooCommerce' ).
		 *
		 * @return bool
		 */
		public static function wc_active() {
			if ( function_exists( 'rbfw_has_woocommerce' ) ) {
				return rbfw_has_woocommerce();
			}
			return class_exists( 'WooCommerce' );
		}

		/**
		 * Detect a row's source purely from its post type.
		 *
		 * @param int|WP_Post $post Post id or object.
		 * @return string self::SOURCE_WOO | self::SOURCE_CUSTOM
		 */
		public static function detect_source( $post ) {
			$type = get_post_type( $post );
			return self::CPT_WOO === $type ? self::SOURCE_WOO : self::SOURCE_CUSTOM;
		}

		/**
		 * Human label for a source (badge text).
		 *
		 * @param string $source
		 * @return string
		 */
		public static function source_label( $source ) {
			return self::SOURCE_WOO === $source
				? esc_html__( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' )
				: esc_html__( 'Custom', 'booking-and-rental-manager-for-woocommerce' );
		}

		/* ================================================================== *
		 * Status map (native + WooCommerce)
		 * ================================================================== */

		/**
		 * Normalize a raw status string: lowercased, "wc-" prefix stripped, and the
		 * American "canceled" folded onto "cancelled".
		 *
		 * @param string $raw
		 * @return string
		 */
		public static function normalize_status( $raw ) {
			$s = strtolower( trim( (string) $raw ) );
			$s = preg_replace( '/^wc-/', '', $s );
			if ( 'canceled' === $s ) {
				$s = 'cancelled';
			}
			return $s ? $s : 'pending';
		}

		/**
		 * Unified status map covering both native and WooCommerce statuses.
		 *
		 * @return array<string,array{label:string,class:string}> normalized slug => meta
		 */
		public static function status_map() {
			return array(
				'pending'        => array( 'label' => esc_html__( 'Pending', 'booking-and-rental-manager-for-woocommerce' ),        'class' => 'rbfw-status-pending' ),
				'processing'     => array( 'label' => esc_html__( 'Processing', 'booking-and-rental-manager-for-woocommerce' ),     'class' => 'rbfw-status-processing' ),
				'confirmed'      => array( 'label' => esc_html__( 'Confirmed', 'booking-and-rental-manager-for-woocommerce' ),      'class' => 'rbfw-status-processing' ),
				'on-hold'        => array( 'label' => esc_html__( 'On hold', 'booking-and-rental-manager-for-woocommerce' ),        'class' => 'rbfw-status-on-hold' ),
				'completed'      => array( 'label' => esc_html__( 'Completed', 'booking-and-rental-manager-for-woocommerce' ),      'class' => 'rbfw-status-completed' ),
				'cancelled'      => array( 'label' => esc_html__( 'Cancelled', 'booking-and-rental-manager-for-woocommerce' ),      'class' => 'rbfw-status-cancelled' ),
				'refunded'       => array( 'label' => esc_html__( 'Refunded', 'booking-and-rental-manager-for-woocommerce' ),       'class' => 'rbfw-status-refunded' ),
				'failed'         => array( 'label' => esc_html__( 'Failed', 'booking-and-rental-manager-for-woocommerce' ),         'class' => 'rbfw-status-failed' ),
				'partially-paid' => array( 'label' => esc_html__( 'Partially Paid', 'booking-and-rental-manager-for-woocommerce' ), 'class' => 'rbfw-status-partial' ),
			);
		}

		/**
		 * Display label for a (possibly wc-prefixed) status.
		 *
		 * @param string $raw
		 * @return string
		 */
		public static function status_label( $raw ) {
			$slug = self::normalize_status( $raw );
			$map  = self::status_map();
			return isset( $map[ $slug ] ) ? $map[ $slug ]['label'] : ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
		}

		/**
		 * CSS class for a (possibly wc-prefixed) status pill.
		 *
		 * @param string $raw
		 * @return string
		 */
		public static function status_class( $raw ) {
			$slug = self::normalize_status( $raw );
			$map  = self::status_map();
			return isset( $map[ $slug ] ) ? $map[ $slug ]['class'] : 'rbfw-status-' . sanitize_html_class( $slug );
		}

		/**
		 * Whether a booking status counts as "ticket ready" — the point at which the
		 * customer's ticket/invoice should become downloadable and get attached to
		 * confirmation emails.
		 *
		 * Driven by the existing "Inventory Managed Order Status" setting
		 * (General Settings → rbfw_basic_gen_settings[inventory_managed_order_status]),
		 * the same setting that decides which statuses hold inventory. Reusing it keeps
		 * "is this booking reserved" and "can the customer get their ticket" in lockstep
		 * everywhere a ticket can be obtained: the My Account download button, the
		 * confirmation email's PDF attachment, and the booking confirmation page.
		 *
		 * @param string $raw_status
		 * @return bool
		 */
		public static function is_ticket_ready( $raw_status ) {
			$slug    = self::normalize_status( $raw_status );
			$managed = get_option( 'rbfw_basic_gen_settings', array() );
			$managed = ( is_array( $managed ) && isset( $managed['inventory_managed_order_status'] ) && is_array( $managed['inventory_managed_order_status'] ) )
				? $managed['inventory_managed_order_status']
				: array( 'processing' => 'processing', 'completed' => 'completed' ); // matches the field's own default.
			return isset( $managed[ $slug ] );
		}

		/* ================================================================== *
		 * WooCommerce order resolution (slice-only, cached, guarded)
		 * ================================================================== */

		/**
		 * Resolve the real WooCommerce order behind an rbfw_order mirror post.
		 * Cached per request and guarded so it never fatals with WooCommerce off.
		 *
		 * @param int $woo_post_id rbfw_order post id.
		 * @return WC_Order|false
		 */
		public static function resolve_wc_order( $woo_post_id ) {
			$woo_post_id = absint( $woo_post_id );
			if ( array_key_exists( $woo_post_id, self::$wc_order_cache ) ) {
				return self::$wc_order_cache[ $woo_post_id ];
			}
			$order = false;
			if ( self::wc_active() ) {
				$wc_order_id = get_post_meta( $woo_post_id, 'rbfw_order_id', true );
				if ( $wc_order_id ) {
					$maybe = wc_get_order( $wc_order_id );
					$order = $maybe ? $maybe : false;
				}
			}
			self::$wc_order_cache[ $woo_post_id ] = $order;
			return $order;
		}

		/**
		 * HPOS-aware admin edit URL for the real WooCommerce order behind a mirror post.
		 *
		 * @param int $woo_post_id rbfw_order post id.
		 * @return string Empty string when WooCommerce is off or no order exists.
		 */
		public static function wc_order_edit_url( $woo_post_id ) {
			$order = self::resolve_wc_order( $woo_post_id );
			if ( ! $order ) {
				return '';
			}
			// get_edit_order_url() is HPOS-aware (custom-order-tables vs legacy post edit).
			if ( method_exists( $order, 'get_edit_order_url' ) ) {
				return $order->get_edit_order_url();
			}
			return admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );
		}

		/* ================================================================== *
		 * Formatting
		 * ================================================================== */

		/**
		 * Format a monetary amount, using WooCommerce's formatter (or the plugin's
		 * currency-shim fallback when WooCommerce is inactive).
		 *
		 * @param float $amount
		 * @return string HTML.
		 */
		public static function format_price( $amount ) {
			if ( function_exists( 'wc_price' ) ) {
				return wc_price( (float) $amount );
			}
			$symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
			return $symbol . number_format( (float) $amount, 2 );
		}

		/**
		 * Build a "start → end" period string from date/time parts.
		 *
		 * @return string
		 */
		private static function format_period( $start_date, $start_time, $end_date, $end_time ) {
			$start = trim( (string) $start_date . ' ' . (string) $start_time );
			$end   = trim( (string) $end_date . ' ' . (string) $end_time );
			if ( $start && $end && $start !== $end ) {
				return $start . ' → ' . $end;
			}
			return $start ? $start : ( $end ? $end : '—' );
		}

		/* ================================================================== *
		 * Cheap per-source descriptor builders (no WC object resolution)
		 * ================================================================== */

		/**
		 * Lightweight descriptor for a native rbfw_booking row, from flat post meta.
		 *
		 * @param int $id
		 * @return array
		 */
		private static function describe_custom( $id ) {
			$item_id   = (int) get_post_meta( $id, 'rbfw_item_id', true );
			$item_name = (string) get_post_meta( $id, 'rbfw_item_name', true );
			if ( '' === $item_name ) {
				$item_name = $item_id ? get_the_title( $item_id ) : '—';
			}
			$reference = (string) get_post_meta( $id, 'rbfw_reference', true );
			$name      = (string) get_post_meta( $id, 'rbfw_customer_name', true );
			$email     = (string) get_post_meta( $id, 'rbfw_customer_email', true );
			$phone     = (string) get_post_meta( $id, 'rbfw_customer_phone', true );
			$gateway   = (string) get_post_meta( $id, 'rbfw_payment_method', true );

			return array(
				'id'             => $id,
				'source'         => self::SOURCE_CUSTOM,
				'wc_order_id'    => 0,
				'timestamp'      => (int) get_post_time( 'U', true, $id ),
				'raw_status'     => self::normalize_status( get_post_meta( $id, 'rbfw_status', true ) ),
				'item_id'        => $item_id,
				'item_name'      => $item_name,
				'reference'      => $reference ? $reference : ( '#' . $id ),
				'customer_name'  => $name,
				'customer_email' => $email,
				'customer_phone' => $phone,
				'pin'            => '',
				'gateway'        => $gateway ? $gateway : 'custom',
				'total_meta'     => (float) get_post_meta( $id, 'rbfw_total', true ),
			);
		}

		/**
		 * Lightweight descriptor for a WooCommerce rbfw_order mirror row.
		 *
		 * Uses only flat post meta + the serialized ticket snapshot — it does NOT
		 * resolve the WC order object (that happens later, on the page slice, in
		 * hydrate()). Status here is the mirrored `rbfw_order_status` meta.
		 *
		 * @param int $id
		 * @return array
		 */
		private static function describe_woo( $id ) {
			$wc_order_id = (int) get_post_meta( $id, 'rbfw_order_id', true );
			$name        = (string) get_post_meta( $id, 'rbfw_billing_name', true );
			$email       = (string) get_post_meta( $id, 'rbfw_billing_email', true );
			$phone       = (string) get_post_meta( $id, 'rbfw_billing_phone', true );
			$pin         = (string) get_post_meta( $id, 'rbfw_pin', true );

			$ticket = self::parse_woo_ticket( $id );

			return array(
				'id'             => $id,
				'source'         => self::SOURCE_WOO,
				'wc_order_id'    => $wc_order_id,
				'timestamp'      => (int) get_post_time( 'U', true, $id ),
				'raw_status'     => self::normalize_status( get_post_meta( $id, 'rbfw_order_status', true ) ),
				'item_id'        => $ticket['item_id'],
				'item_name'      => $ticket['item_name'],
				'reference'      => $wc_order_id ? ( '#' . $wc_order_id ) : ( '#' . $id ),
				'customer_name'  => $name,
				'customer_email' => $email,
				'customer_phone' => $phone,
				'pin'            => $pin,
				'gateway'        => (string) get_post_meta( $id, 'rbfw_payment_method_title', true ),
				'total_meta'     => (float) get_post_meta( $id, 'rbfw_ticket_total_price', true ),
				'_period'        => $ticket['period'],
				'_quantity'      => $ticket['quantity'],
			);
		}

		/**
		 * Extract item name / id / period / quantity from an rbfw_order's serialized
		 * `rbfw_ticket_info` snapshot without touching WooCommerce. Untrusted serialized
		 * meta is decoded with object instantiation disabled.
		 *
		 * @param int $id rbfw_order post id.
		 * @return array{item_id:int,item_name:string,period:string,quantity:int}
		 */
		private static function parse_woo_ticket( $id ) {
			$out = array( 'item_id' => 0, 'item_name' => '—', 'period' => '—', 'quantity' => 1 );

			$raw = get_post_meta( $id, 'rbfw_ticket_info', true );
			if ( is_string( $raw ) && '' !== $raw ) {
				$raw = maybe_unserialize( $raw );
			}
			if ( ! is_array( $raw ) || empty( $raw ) ) {
				return $out;
			}
			$first = reset( $raw );
			if ( ! is_array( $first ) ) {
				return $out;
			}

			$out['item_id'] = isset( $first['rbfw_id'] ) ? absint( $first['rbfw_id'] ) : 0;
			$name           = isset( $first['ticket_name'] ) ? (string) $first['ticket_name'] : '';
			if ( '' === $name && $out['item_id'] ) {
				$name = get_the_title( $out['item_id'] );
			}
			$out['item_name'] = $name ? $name : '—';

			if ( isset( $first['rbfw_item_quantity'] ) ) {
				$out['quantity'] = max( 1, absint( $first['rbfw_item_quantity'] ) );
			}

			$out['period'] = self::format_period(
				isset( $first['rbfw_start_date'] ) ? $first['rbfw_start_date'] : '',
				isset( $first['rbfw_start_time'] ) ? $first['rbfw_start_time'] : '',
				isset( $first['rbfw_end_date'] ) ? $first['rbfw_end_date'] : '',
				isset( $first['rbfw_end_time'] ) ? $first['rbfw_end_time'] : ''
			);

			return $out;
		}

		/**
		 * Public accessor for a WooCommerce mirror's ticket snapshot (item id/name, booking
		 * period, quantity) — used by the Pro detail view to show a WooCommerce order's
		 * booking specifics in-plugin without re-parsing the serialized meta itself.
		 *
		 * @param int $id rbfw_order post id.
		 * @return array{item_id:int,item_name:string,period:string,quantity:int}
		 */
		public static function woo_ticket_summary( $id ) {
			return self::parse_woo_ticket( absint( $id ) );
		}

		/* ================================================================== *
		 * Query index (cheap) → filter/search/sort → slice → hydrate (WC only here)
		 * ================================================================== */

		/**
		 * Default, sanitized filter set.
		 *
		 * @param array $filters
		 * @return array
		 */
		public static function default_filters( $filters = array() ) {
			return wp_parse_args( $filters, array(
				'search'    => '',
				'source'    => '',   // '' | custom | woocommerce
				'status'    => '',   // normalized status slug
				'item_id'   => 0,
				'gateway'   => '',
				'date_from' => '',
				'date_to'   => '',
			) );
		}

		/**
		 * Build the full, ordered index of matching bookings across BOTH CPTs, applying
		 * every filter that can be evaluated from cheap post meta. WooCommerce order
		 * objects are NOT resolved here — only in hydrate() for the rendered slice.
		 *
		 * @param array $filters See default_filters().
		 * @return array<int,array> Descriptor rows, newest first.
		 */
		public static function query_index( $filters = array() ) {
			$filters = self::default_filters( $filters );
			$rows    = array();

			$want_custom = ( '' === $filters['source'] || self::SOURCE_CUSTOM === $filters['source'] );
			$want_woo    = ( '' === $filters['source'] || self::SOURCE_WOO === $filters['source'] );

			$date_query = self::build_date_query( $filters );

			if ( $want_custom ) {
				foreach ( self::query_ids( self::CPT_CUSTOM, $filters, $date_query, 'rbfw_status' ) as $id ) {
					$rows[] = self::describe_custom( $id );
				}
			}
			if ( $want_woo ) {
				foreach ( self::query_ids( self::CPT_WOO, $filters, $date_query, 'rbfw_order_status' ) as $id ) {
					$rows[] = self::describe_woo( $id );
				}
			}

			$rows = self::apply_php_filters( $rows, $filters );

			usort( $rows, static function ( $a, $b ) {
				return $b['timestamp'] <=> $a['timestamp'];
			} );

			return $rows;
		}

		/**
		 * Run a per-CPT id query. Uses `post_status => 'any'` (which excludes trash) so
		 * pending/draft bookings are never silently dropped, and the status filter maps
		 * onto that CPT's own status meta key.
		 *
		 * @param string     $cpt
		 * @param array      $filters
		 * @param array|null $date_query
		 * @param string     $status_meta_key rbfw_status | rbfw_order_status
		 * @return int[]
		 */
		private static function query_ids( $cpt, $filters, $date_query, $status_meta_key ) {
			$args = array(
				'post_type'      => $cpt,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			);

			$meta_query = array();
			if ( $filters['status'] ) {
				$meta_query[] = array( 'key' => $status_meta_key, 'value' => sanitize_key( $filters['status'] ) );
			}
			// item_id / gateway are only reliable meta on native rows.
			if ( self::CPT_CUSTOM === $cpt ) {
				if ( $filters['item_id'] ) {
					$meta_query[] = array( 'key' => 'rbfw_item_id', 'value' => absint( $filters['item_id'] ) );
				}
				if ( $filters['gateway'] ) {
					$meta_query[] = array( 'key' => 'rbfw_payment_method', 'value' => sanitize_text_field( $filters['gateway'] ) );
				}
			}
			if ( $meta_query ) {
				$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}
			if ( $date_query ) {
				$args['date_query'] = $date_query;
			}

			$q = new WP_Query( $args );
			return array_map( 'absint', $q->posts );
		}

		/**
		 * Translate date_from / date_to into a WP_Query date_query.
		 *
		 * @param array $filters
		 * @return array|null
		 */
		private static function build_date_query( $filters ) {
			$dq = array();
			if ( $filters['date_from'] ) {
				$dq['after'] = array(
					'year'  => (int) substr( $filters['date_from'], 0, 4 ),
					'month' => (int) substr( $filters['date_from'], 5, 2 ),
					'day'   => (int) substr( $filters['date_from'], 8, 2 ),
				);
			}
			if ( $filters['date_to'] ) {
				$dq['before'] = array(
					'year'  => (int) substr( $filters['date_to'], 0, 4 ),
					'month' => (int) substr( $filters['date_to'], 5, 2 ),
					'day'   => (int) substr( $filters['date_to'], 8, 2 ),
				);
			}
			if ( $dq ) {
				$dq['inclusive'] = true;
				return $dq;
			}
			return null;
		}

		/**
		 * Apply the filters that can't be expressed as a single per-CPT meta_query:
		 * a free-text search across reference / WC order id / booking id / name / email
		 * / phone / PIN, plus the item filter for WooCommerce rows.
		 *
		 * @param array $rows
		 * @param array $filters
		 * @return array
		 */
		private static function apply_php_filters( $rows, $filters ) {
			$search  = strtolower( trim( (string) $filters['search'] ) );
			$item_id = absint( $filters['item_id'] );

			if ( '' === $search && ! $item_id ) {
				return $rows;
			}

			return array_values( array_filter( $rows, static function ( $row ) use ( $search, $item_id ) {
				// item filter for WooCommerce rows (native rows already filtered in SQL).
				if ( $item_id && RBFW_Booking_Normalizer::SOURCE_WOO === $row['source'] && (int) $row['item_id'] !== $item_id ) {
					return false;
				}
				if ( '' === $search ) {
					return true;
				}
				$haystack = strtolower( implode( ' ', array(
					$row['id'],
					$row['wc_order_id'],
					$row['reference'],
					$row['customer_name'],
					$row['customer_email'],
					$row['customer_phone'],
					$row['pin'],
					$row['item_name'],
				) ) );
				return false !== strpos( $haystack, $search );
			} ) );
		}

		/**
		 * Hydrate a slice of descriptors into full, render-ready rows. This is the ONLY
		 * place the real WooCommerce order is resolved, so the cost is bounded to the
		 * rows actually shown on the current page.
		 *
		 * @param array $slice Descriptor rows from query_index().
		 * @return array<int,array> Render-ready rows.
		 */
		public static function hydrate( $slice ) {
			$out = array();
			foreach ( $slice as $row ) {
				$out[] = self::SOURCE_WOO === $row['source']
					? self::hydrate_woo( $row )
					: self::hydrate_custom( $row );
			}
			return $out;
		}

		/**
		 * Finalize a native row — everything is already in flat meta, no WC needed.
		 *
		 * @param array $row Descriptor.
		 * @return array
		 */
		private static function hydrate_custom( $row ) {
			$id     = $row['id'];
			$total  = $row['total_meta'];
			$period = self::format_period(
				get_post_meta( $id, 'rbfw_start_date', true ),
				get_post_meta( $id, 'rbfw_start_time', true ),
				get_post_meta( $id, 'rbfw_end_date', true ),
				get_post_meta( $id, 'rbfw_end_time', true )
			);

			return array_merge( $row, array(
				'period'        => $period,
				'quantity'      => max( 1, absint( get_post_meta( $id, 'rbfw_quantity', true ) ) ),
				'total_raw'     => (float) $total,
				'total'         => self::format_price( $total ),
				'status_label'  => self::status_label( $row['raw_status'] ),
				'status_class'  => self::status_class( $row['raw_status'] ),
				'payment_label' => $row['gateway'] && 'custom' !== $row['gateway'] ? ucwords( str_replace( array( '-', '_' ), ' ', $row['gateway'] ) ) : esc_html__( 'Custom', 'booking-and-rental-manager-for-woocommerce' ),
				'edit_url'      => '',
				'date'          => get_the_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $id ),
				'source_label'  => self::source_label( self::SOURCE_CUSTOM ),
			) );
		}

		/**
		 * Finalize a WooCommerce row by resolving the real order for the authoritative
		 * status, total, payment title and edit URL. Falls back to the mirrored meta
		 * when the order (or WooCommerce itself) is gone, so the row still renders.
		 *
		 * @param array $row Descriptor.
		 * @return array
		 */
		private static function hydrate_woo( $row ) {
			$order = self::resolve_wc_order( $row['id'] );

			$status  = $row['raw_status'];
			$total   = $row['total_meta'];
			$payment = $row['gateway'];
			$edit    = '';

			if ( $order ) {
				$status  = self::normalize_status( $order->get_status() );
				$total   = (float) $order->get_total();
				$payment = $order->get_payment_method_title();
				$edit    = self::wc_order_edit_url( $row['id'] );
			}

			return array_merge( $row, array(
				'raw_status'    => $status,
				'period'        => isset( $row['_period'] ) ? $row['_period'] : '—',
				'quantity'      => isset( $row['_quantity'] ) ? $row['_quantity'] : 1,
				'total_raw'     => (float) $total,
				'total'         => self::format_price( $total ),
				'status_label'  => self::status_label( $status ),
				'status_class'  => self::status_class( $status ),
				'payment_label' => $payment ? $payment : esc_html__( 'WooCommerce', 'booking-and-rental-manager-for-woocommerce' ),
				'edit_url'      => $edit,
				'order_missing' => ! $order,
				'date'          => get_the_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row['id'] ),
				'source_label'  => self::source_label( self::SOURCE_WOO ),
			) );
		}

		/* ================================================================== *
		 * Status write-back (source-aware)
		 * ================================================================== */

		/**
		 * Change a booking's status, writing to the right place for its source:
		 *   - WooCommerce rows → update the REAL WC order (fires WC emails/hooks) and
		 *     mirror the new status into `rbfw_order_status`.
		 *   - Native rows      → update `rbfw_status` meta and run the shared transition
		 *     side effects via RBFW_Booking_Actions.
		 *
		 * @param int    $post_id    Mirror/booking post id.
		 * @param string $new_status Normalized status slug.
		 * @return true|WP_Error
		 */
		public static function update_status( $post_id, $new_status ) {
			$post_id    = absint( $post_id );
			$new_status = self::normalize_status( $new_status );
			$source     = self::detect_source( $post_id );

			if ( self::SOURCE_WOO === $source ) {
				if ( ! self::wc_active() ) {
					return new WP_Error( 'rbfw_no_wc', esc_html__( 'WooCommerce is not active.', 'booking-and-rental-manager-for-woocommerce' ) );
				}
				$order = self::resolve_wc_order( $post_id );
				if ( ! $order ) {
					return new WP_Error( 'rbfw_no_order', esc_html__( 'The WooCommerce order could not be found.', 'booking-and-rental-manager-for-woocommerce' ) );
				}
				if ( $order->get_status() !== $new_status ) {
					$order->update_status( $new_status, '', true );
				}
				update_post_meta( $post_id, 'rbfw_order_status', $new_status );
				return true;
			}

			$old = get_post_meta( $post_id, 'rbfw_status', true );
			update_post_meta( $post_id, 'rbfw_status', $new_status );
			if ( class_exists( 'RBFW_Booking_Actions' ) ) {
				RBFW_Booking_Actions::apply_transition( $post_id, $new_status, $old );
			}
			return true;
		}
	}
}
