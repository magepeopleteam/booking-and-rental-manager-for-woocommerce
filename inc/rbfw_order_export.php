<?php
/**
 * RBFW Order List — Export (CSV / PDF)
 *
 * Streams the rental order data shown on the Order List page as either a CSV
 * spreadsheet or a PDF document. Supports item-wise and month-range filtering.
 *
 * A real PDF is produced with mPDF when the bundled "magepeople-pdf-support"
 * companion plugin is active (same library the Pro PDF tickets use). When that
 * library is not available the handler degrades gracefully to a print-optimised
 * HTML page that the browser can "Save as PDF", so the free plugin keeps working
 * stand-alone.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_rbfw_export_orders', 'rbfw_export_orders_handler' );

/**
 * Entry point for the export download request.
 *
 * Security: verifies the dedicated nonce and the booking-management capability
 * ( rbfw_bookings_capability(), default manage_options ) before reading any data.
 * All output is plain text / library-rendered PDF, so
 * there is no HTML echoed into wp-admin from user input.
 */
function rbfw_export_orders_handler() {

	if ( ! current_user_can( rbfw_bookings_capability() ) ) {
		wp_die( esc_html__( 'You are not allowed to export orders.', 'booking-and-rental-manager-for-woocommerce' ), 403 );
	}

	$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'rbfw_export_orders_action' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the Order List page and try again.', 'booking-and-rental-manager-for-woocommerce' ), 403 );
	}

	// --- Sanitise filter inputs -------------------------------------------------
	$format = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : 'csv';
	if ( ! in_array( $format, array( 'csv', 'pdf' ), true ) ) {
		$format = 'csv';
	}

	$item_id    = isset( $_GET['item_id'] ) ? absint( wp_unslash( $_GET['item_id'] ) ) : 0;
	$status     = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
	$from_month = isset( $_GET['from_month'] ) ? sanitize_text_field( wp_unslash( $_GET['from_month'] ) ) : '';
	$to_month   = isset( $_GET['to_month'] ) ? sanitize_text_field( wp_unslash( $_GET['to_month'] ) ) : '';

	// Normalise the YYYY-MM month inputs; ignore anything that is not a valid month.
	$from_month = preg_match( '/^\d{4}-\d{2}$/', $from_month ) ? $from_month : '';
	$to_month   = preg_match( '/^\d{4}-\d{2}$/', $to_month ) ? $to_month : '';
	// Allow the user to enter the range either way round.
	if ( $from_month && $to_month && $from_month > $to_month ) {
		$tmp        = $from_month;
		$from_month = $to_month;
		$to_month   = $tmp;
	}

	$filters = array(
		'item_id'    => $item_id,
		'status'     => $status,
		'from_month' => $from_month,
		'to_month'   => $to_month,
	);

	$rows = rbfw_collect_export_rows( $filters );

	if ( 'pdf' === $format ) {
		rbfw_export_orders_pdf( $rows, $filters );
	} else {
		rbfw_export_orders_csv( $rows, $filters );
	}
	exit;
}

/**
 * Column definitions for the export — single source of truth for both CSV and PDF.
 *
 * @return array<string,string> machine key => human label.
 */
function rbfw_export_columns() {
	return array(
		'order_no'         => __( 'Order #', 'booking-and-rental-manager-for-woocommerce' ),
		'booking_id'       => __( 'Booking ID', 'booking-and-rental-manager-for-woocommerce' ),
		'customer'         => __( 'Customer', 'booking-and-rental-manager-for-woocommerce' ),
		'email'            => __( 'Email', 'booking-and-rental-manager-for-woocommerce' ),
		'phone'            => __( 'Phone', 'booking-and-rental-manager-for-woocommerce' ),
		'item'             => __( 'Item', 'booking-and-rental-manager-for-woocommerce' ),
		'item_type'        => __( 'Item Type', 'booking-and-rental-manager-for-woocommerce' ),
		'qty'              => __( 'Qty', 'booking-and-rental-manager-for-woocommerce' ),
		'booking_start'    => __( 'Booking Start', 'booking-and-rental-manager-for-woocommerce' ),
		'booking_end'      => __( 'Booking End', 'booking-and-rental-manager-for-woocommerce' ),
		'total_days'       => __( 'Days', 'booking-and-rental-manager-for-woocommerce' ),
		'duration_cost'    => __( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ),
		'service_cost'     => __( 'Service Cost', 'booking-and-rental-manager-for-woocommerce' ),
		'discount'         => __( 'Discount', 'booking-and-rental-manager-for-woocommerce' ),
		'security_deposit' => __( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ),
		'status'           => __( 'Status', 'booking-and-rental-manager-for-woocommerce' ),
		'order_total'      => __( 'Order Total', 'booking-and-rental-manager-for-woocommerce' ),
		'order_created'    => __( 'Order Created', 'booking-and-rental-manager-for-woocommerce' ),
	);
}

/**
 * Export columns arranged into accordion groups for the settings UI.
 *
 * @return array<string,array{label:string,columns:string[]}>
 */
function rbfw_export_column_groups() {
	return array(
		'order'    => array(
			'label'   => __( 'Order', 'booking-and-rental-manager-for-woocommerce' ),
			'columns' => array( 'order_no', 'booking_id', 'status', 'order_total', 'order_created' ),
		),
		'customer' => array(
			'label'   => __( 'Customer', 'booking-and-rental-manager-for-woocommerce' ),
			'columns' => array( 'customer', 'email', 'phone' ),
		),
		'booking'  => array(
			'label'   => __( 'Booking', 'booking-and-rental-manager-for-woocommerce' ),
			'columns' => array( 'item', 'item_type', 'qty', 'booking_start', 'booking_end', 'total_days' ),
		),
		'pricing'  => array(
			'label'   => __( 'Pricing', 'booking-and-rental-manager-for-woocommerce' ),
			'columns' => array( 'duration_cost', 'service_cost', 'discount', 'security_deposit' ),
		),
	);
}

/**
 * Option name holding the per-column export toggles.
 *
 * @return string
 */
function rbfw_export_settings_option_name() {
	return 'rbfw_order_export_settings';
}

/**
 * Enabled/disabled map for every export column.
 *
 * Columns default to ON — a column is only hidden when it has an explicit 'off'
 * saved — so the export is unchanged until the admin customises it.
 *
 * @return array<string,bool>
 */
function rbfw_export_column_enabled_map() {
	$saved = get_option( rbfw_export_settings_option_name(), array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$map = array();
	foreach ( rbfw_export_columns() as $key => $label ) {
		$map[ $key ] = ! ( isset( $saved[ $key ] ) && 'off' === $saved[ $key ] );
	}
	return $map;
}

/**
 * The export columns that are currently enabled, in display order.
 *
 * Falls back to all columns if every toggle was switched off, so the export
 * file is never completely empty of columns.
 *
 * @return array<string,string> key => label.
 */
function rbfw_export_enabled_columns() {
	$all     = rbfw_export_columns();
	$map     = rbfw_export_column_enabled_map();
	$enabled = array();
	foreach ( $all as $key => $label ) {
		if ( ! empty( $map[ $key ] ) ) {
			$enabled[ $key ] = $label;
		}
	}
	return $enabled ? $enabled : $all;
}

add_action( 'wp_ajax_rbfw_save_export_settings', 'rbfw_save_export_settings_callback' );
/**
 * AJAX: persist the export column toggles from the Order List accordion.
 */
function rbfw_save_export_settings_callback() {
	check_ajax_referer( 'rbfw_save_export_settings_action', 'nonce' );

	if ( ! current_user_can( rbfw_bookings_capability() ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized access.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
	}

	$posted = ( isset( $_POST['columns'] ) && is_array( $_POST['columns'] ) ) ? wp_unslash( $_POST['columns'] ) : array();

	$settings = array();
	foreach ( rbfw_export_columns() as $key => $label ) {
		$val               = isset( $posted[ $key ] ) ? sanitize_text_field( $posted[ $key ] ) : '0';
		$settings[ $key ]  = in_array( $val, array( '1', 'on', 'true' ), true ) ? 'on' : 'off';
	}

	update_option( rbfw_export_settings_option_name(), $settings );

	wp_send_json_success( array( 'message' => esc_html__( 'Export settings saved.', 'booking-and-rental-manager-for-woocommerce' ) ) );
}

/**
 * Build the export dataset — one row per booking line item (ticket).
 *
 * Producing a row per ticket (rather than per order) is what makes the
 * item-wise filter meaningful: a multi-item order yields one row per item,
 * each carrying its parent order number so rows can still be grouped.
 *
 * @param array $filters item_id, status, from_month, to_month.
 * @return array list of associative rows keyed by rbfw_export_columns().
 */
function rbfw_collect_export_rows( $filters ) {
	$rows = array();

	$query = new WP_Query(
		array(
			'post_type'      => 'rbfw_order',
			'order'          => 'DESC',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future', 'inherit' ),
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return $rows;
	}

	$item_id    = isset( $filters['item_id'] ) ? (int) $filters['item_id'] : 0;
	$status     = isset( $filters['status'] ) ? $filters['status'] : '';
	$from_month = isset( $filters['from_month'] ) ? $filters['from_month'] : '';
	$to_month   = isset( $filters['to_month'] ) ? $filters['to_month'] : '';

	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id     = get_the_ID();
		$wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
		$order       = $wc_order_id ? wc_get_order( $wc_order_id ) : false;

		if ( ! $order ) {
			continue;
		}

		$order_status = $order->get_status();
		if ( 'trash' === $order_status || 'wc-trash' === $order_status ) {
			continue;
		}

		// Status filter (statuses are stored without the wc- prefix in the UI).
		if ( '' !== $status && str_replace( 'wc-', '', $order_status ) !== $status ) {
			continue;
		}

		$order_created = get_the_date( 'Y-m-d H:i', $post_id );
		$order_total   = (float) $order->get_total();
		$billing_name  = get_post_meta( $post_id, 'rbfw_billing_name', true );
		if ( '' === $billing_name ) {
			$billing_name = trim( $order->get_formatted_billing_full_name() );
		}
		$billing_phone = $order->get_billing_phone();
		$billing_email = $order->get_billing_email();

		$ticket_info_array = maybe_unserialize( get_post_meta( $post_id, 'rbfw_ticket_info', true ) );
		if ( empty( $ticket_info_array ) || ! is_array( $ticket_info_array ) ) {
			continue;
		}

		// Re-index in case the meta is an associative/nested structure.
		if ( isset( $ticket_info_array['rbfw_id'] ) ) {
			$ticket_info_array = array( $ticket_info_array );
		}

		$first_ticket_row_for_order = true;

		foreach ( $ticket_info_array as $ticket ) {
			if ( ! is_array( $ticket ) ) {
				continue;
			}

			$ticket_item_id = isset( $ticket['rbfw_id'] ) ? (int) $ticket['rbfw_id'] : 0;

			// Item-wise filter.
			if ( $item_id > 0 && $ticket_item_id !== $item_id ) {
				continue;
			}

			$start_raw = isset( $ticket['rbfw_start_datetime'] ) ? $ticket['rbfw_start_datetime'] : '';
			$end_raw   = isset( $ticket['rbfw_end_datetime'] ) ? $ticket['rbfw_end_datetime'] : '';

			// Month-range filter on booking start, falling back to order-created
			// date when the booking has no start (keeps every record placeable).
			$range_basis = $start_raw ? strtotime( $start_raw ) : strtotime( $order_created );
			if ( ( $from_month || $to_month ) && $range_basis ) {
				$row_month = gmdate( 'Y-m', $range_basis );
				if ( $from_month && $row_month < $from_month ) {
					continue;
				}
				if ( $to_month && $row_month > $to_month ) {
					continue;
				}
			}

			$qty = 1;
			if ( ! empty( $ticket['rbfw_item_quantity'] ) ) {
				$qty = (int) $ticket['rbfw_item_quantity'];
			} elseif ( ! empty( $ticket['ticket_qty'] ) ) {
				$qty = (int) $ticket['ticket_qty'];
			}

			$rows[] = array(
				'order_no'         => $wc_order_id,
				'booking_id'       => $post_id,
				'customer'         => html_entity_decode( (string) $billing_name, ENT_QUOTES, 'UTF-8' ),
				'email'            => $billing_email,
				'phone'            => $billing_phone,
				'item'             => isset( $ticket['ticket_name'] ) ? html_entity_decode( (string) $ticket['ticket_name'], ENT_QUOTES, 'UTF-8' ) : '',
				'item_type'        => isset( $ticket['rbfw_rent_type'] ) ? rbfw_export_type_label( $ticket['rbfw_rent_type'] ) : '',
				'qty'              => $qty,
				'booking_start'    => rbfw_export_format_datetime( $start_raw ),
				'booking_end'      => rbfw_export_format_datetime( $end_raw ),
				'total_days'       => isset( $ticket['total_days'] ) && $ticket['total_days'] ? (int) $ticket['total_days'] : '',
				'duration_cost'    => isset( $ticket['duration_cost'] ) ? (float) $ticket['duration_cost'] : 0,
				'service_cost'     => isset( $ticket['service_cost'] ) ? (float) $ticket['service_cost'] : 0,
				'discount'         => isset( $ticket['discount_amount'] ) ? (float) $ticket['discount_amount'] : 0,
				'security_deposit' => isset( $ticket['security_deposit_amount'] ) ? (float) $ticket['security_deposit_amount'] : 0,
				'status'           => ucfirst( str_replace( 'wc-', '', $order_status ) ),
				// The WC order total belongs to the whole order; only attribute it
				// to the first line so the column never double-counts a multi-item
				// order when summed in a spreadsheet.
				'order_total'      => $first_ticket_row_for_order ? $order_total : '',
				'order_created'    => $order_created,
			);

			$first_ticket_row_for_order = false;
		}
	}

	wp_reset_postdata();

	return $rows;
}

/**
 * Resolve a rent-type slug to its human label, reusing the plugin helper when present.
 *
 * @param string $type rent type slug.
 * @return string
 */
function rbfw_export_type_label( $type ) {
	if ( function_exists( 'rbfw_get_type_label' ) ) {
		$label = rbfw_get_type_label( $type );
		if ( $label ) {
			return $label;
		}
	}
	return ucwords( str_replace( array( '_', '-' ), ' ', (string) $type ) );
}

/**
 * Format a stored booking datetime string to a stable, sortable export value.
 *
 * @param string $value raw datetime string from ticket meta.
 * @return string formatted "Y-m-d H:i" or empty string.
 */
function rbfw_export_format_datetime( $value ) {
	if ( empty( $value ) ) {
		return '';
	}
	$ts = strtotime( $value );
	return $ts ? gmdate( 'Y-m-d H:i', $ts ) : (string) $value;
}

/**
 * Plain-text money formatter (no HTML) for the PDF report.
 *
 * The PDF is rendered with mPDF's default Latin font (DejaVu / FreeSans), which
 * covers ASCII, the Latin-1 currency characters and the Unicode "Currency
 * Symbols" block (€, ₹, ₦ …) but NOT script-specific symbols such as the
 * Bengali Taka sign ৳ (U+09F3) — those render as empty "tofu" boxes. So we keep
 * the currency symbol when every character is in a font-safe range, and fall
 * back to the ISO currency code (e.g. "BDT") otherwise, which always renders.
 *
 * @param float $amount value.
 * @return string e.g. "$1,250.00" or "BDT 1,250.00".
 */
function rbfw_export_money( $amount ) {
	$decimals   = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;
	$symbol     = function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ) : '';
	$amount_str = number_format( (float) $amount, $decimals, '.', ',' );

	if ( '' !== $symbol && rbfw_export_symbol_is_font_safe( $symbol ) ) {
		// Most currency symbols sit before the amount; this matches WooCommerce defaults closely enough for a report.
		return $symbol . $amount_str;
	}

	$code = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';
	return ( $code ? $code . ' ' : '' ) . $amount_str;
}

/**
 * Whether a currency symbol is renderable by the PDF's default Latin font.
 *
 * Font-safe ranges (all covered by DejaVu / FreeSans): ASCII (0x20–0x7E),
 * Latin-1 Supplement + Latin Extended-A/B (0xA0–0x24F, covers ¢ £ ¥ and symbols
 * built from Latin letters such as zł, Kč) and the Currency Symbols block
 * (0x20A0–0x20BF, covers € ₹ ₦ …). Anything outside — e.g. Bengali ৳ (U+09F3) or
 * Thai ฿ (U+0E3F) — is treated as unsafe so we fall back to the ISO code.
 *
 * @param string $symbol currency symbol.
 * @return bool
 */
function rbfw_export_symbol_is_font_safe( $symbol ) {
	$len = function_exists( 'mb_strlen' ) ? mb_strlen( $symbol, 'UTF-8' ) : strlen( $symbol );
	for ( $i = 0; $i < $len; $i++ ) {
		$char = function_exists( 'mb_substr' ) ? mb_substr( $symbol, $i, 1, 'UTF-8' ) : substr( $symbol, $i, 1 );
		$cp   = rbfw_export_uniord( $char );
		$safe = ( $cp >= 0x20 && $cp <= 0x7E )
			|| ( $cp >= 0xA0 && $cp <= 0x24F )
			|| ( $cp >= 0x20A0 && $cp <= 0x20BF );
		if ( ! $safe ) {
			return false;
		}
	}
	return true;
}

/**
 * Unicode code point of a single (possibly multibyte) UTF-8 character.
 *
 * @param string $char one character.
 * @return int code point, or 0 on failure.
 */
function rbfw_export_uniord( $char ) {
	if ( function_exists( 'mb_convert_encoding' ) ) {
		$encoded = mb_convert_encoding( $char, 'UTF-32BE', 'UTF-8' );
		if ( false !== $encoded && strlen( $encoded ) >= 4 ) {
			$parsed = unpack( 'N', $encoded );
			return $parsed ? (int) $parsed[1] : 0;
		}
	}
	return ord( $char[0] );
}

/**
 * Neutralise CSV formula injection.
 *
 * A spreadsheet treats a cell beginning with = + - @ (or a leading tab/CR) as a
 * formula, so an attacker-controlled value such as "=HYPERLINK(...)" could run
 * on open. We prefix such values with an apostrophe to force text — but only
 * when the value is not a genuine number, so formatted amounts and numeric
 * phone fields keep parsing correctly.
 *
 * @param mixed $value cell value.
 * @return string
 */
function rbfw_export_csv_safe( $value ) {
	$value = (string) $value;
	if ( '' === $value ) {
		return $value;
	}
	$first = $value[0];
	if ( in_array( $first, array( '=', '+', '-', '@' ), true ) && ! is_numeric( $value ) ) {
		return "'" . $value;
	}
	if ( "\t" === $first || "\r" === $first ) {
		return "'" . $value;
	}
	return $value;
}

/**
 * Raw numeric money for CSV (no symbol, no thousands separator) so spreadsheets parse it.
 *
 * @param float $amount value.
 * @return string
 */
function rbfw_export_money_raw( $amount ) {
	$decimals = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;
	return number_format( (float) $amount, $decimals, '.', '' );
}

/**
 * Human-readable summary of the applied filters, for the file body / PDF header.
 *
 * @param array $filters applied filters.
 * @return string
 */
function rbfw_export_filter_summary( $filters ) {
	$parts = array();

	if ( ! empty( $filters['item_id'] ) ) {
		$title   = get_the_title( (int) $filters['item_id'] );
		$parts[] = __( 'Item', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . ( $title ? $title : '#' . (int) $filters['item_id'] );
	} else {
		$parts[] = __( 'Item', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . __( 'All Items', 'booking-and-rental-manager-for-woocommerce' );
	}

	if ( ! empty( $filters['status'] ) ) {
		$parts[] = __( 'Status', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . ucfirst( $filters['status'] );
	}

	if ( ! empty( $filters['from_month'] ) || ! empty( $filters['to_month'] ) ) {
		$from    = ! empty( $filters['from_month'] ) ? date_i18n( 'M Y', strtotime( $filters['from_month'] . '-01' ) ) : __( 'Beginning', 'booking-and-rental-manager-for-woocommerce' );
		$to      = ! empty( $filters['to_month'] ) ? date_i18n( 'M Y', strtotime( $filters['to_month'] . '-01' ) ) : __( 'Now', 'booking-and-rental-manager-for-woocommerce' );
		$parts[] = __( 'Period', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . $from . ' – ' . $to;
	} else {
		$parts[] = __( 'Period', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . __( 'All time', 'booking-and-rental-manager-for-woocommerce' );
	}

	return implode( '   |   ', $parts );
}

/**
 * Sum of distinct order totals in the dataset (avoids double-counting multi-item orders).
 *
 * @param array $rows export rows.
 * @return float
 */
function rbfw_export_grand_total( $rows ) {
	$total = 0;
	foreach ( $rows as $row ) {
		if ( '' !== $row['order_total'] ) {
			$total += (float) $row['order_total'];
		}
	}
	return $total;
}

/**
 * Build the export filename stem (without extension).
 *
 * @return string
 */
function rbfw_export_filename_stem() {
	return 'rbfw-orders-export-' . gmdate( 'Ymd-His' );
}

/**
 * Stream the dataset as a CSV download.
 *
 * @param array $rows    export rows.
 * @param array $filters applied filters.
 */
function rbfw_export_orders_csv( $rows, $filters ) {
	$columns  = rbfw_export_enabled_columns();
	$filename = rbfw_export_filename_stem() . '.csv';

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	$out = fopen( 'php://output', 'w' );

	// UTF-8 BOM so Excel renders accented characters and the currency symbol correctly.
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

	// Context lines so the file is self-describing.
	fputcsv( $out, array( get_bloginfo( 'name' ) . ' — ' . __( 'Orders Export', 'booking-and-rental-manager-for-woocommerce' ) ) );
	fputcsv( $out, array( rbfw_export_filter_summary( $filters ) ) );
	fputcsv( $out, array( __( 'Generated', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . date_i18n( 'Y-m-d H:i' ) ) );
	fputcsv( $out, array() );

	// Header row.
	fputcsv( $out, array_values( $columns ) );

	$money_keys = array( 'duration_cost', 'service_cost', 'discount', 'security_deposit', 'order_total' );

	foreach ( $rows as $row ) {
		$line = array();
		foreach ( $columns as $key => $label ) {
			$value = isset( $row[ $key ] ) ? $row[ $key ] : '';
			if ( in_array( $key, $money_keys, true ) && '' !== $value ) {
				$value = rbfw_export_money_raw( $value );
			}
			$line[] = rbfw_export_csv_safe( $value );
		}
		fputcsv( $out, $line );
	}

	// Totals line.
	fputcsv( $out, array() );
	fputcsv(
		$out,
		array(
			__( 'Total Orders', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . rbfw_export_count_orders( $rows ),
			__( 'Total Line Items', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . count( $rows ),
			__( 'Grand Total', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . rbfw_export_money_raw( rbfw_export_grand_total( $rows ) ),
		)
	);

	fclose( $out );
}

/**
 * Count distinct orders represented in the dataset.
 *
 * @param array $rows export rows.
 * @return int
 */
function rbfw_export_count_orders( $rows ) {
	$ids = array();
	foreach ( $rows as $row ) {
		$ids[ (string) $row['order_no'] ] = true;
	}
	return count( $ids );
}

/**
 * Render the dataset as a PDF (mPDF) download, or an auto-print HTML fallback.
 *
 * @param array $rows    export rows.
 * @param array $filters applied filters.
 */
function rbfw_export_orders_pdf( $rows, $filters ) {
	$body_html = rbfw_export_pdf_body_html( $rows, $filters );

	// Use mPDF when the companion PDF plugin is active (true downloadable PDF).
	if ( class_exists( '\Mpdf\Mpdf' ) ) {
		try {
			$upload  = wp_upload_dir();
			$tmp_dir = ( isset( $upload['basedir'] ) ? $upload['basedir'] : sys_get_temp_dir() ) . '/rbfw-mpdf-tmp';
			if ( ! file_exists( $tmp_dir ) ) {
				wp_mkdir_p( $tmp_dir );
			}

			$mpdf = new \Mpdf\Mpdf(
				array(
					'mode'          => 'utf-8',
					'format'        => 'A4-L',
					'margin_top'    => 30,
					'margin_bottom' => 16,
					'margin_left'   => 8,
					'margin_right'  => 8,
					'tempDir'       => is_writable( $tmp_dir ) ? $tmp_dir : null,
				)
			);
			$mpdf->SetTitle( get_bloginfo( 'name' ) . ' — ' . __( 'Orders Export', 'booking-and-rental-manager-for-woocommerce' ) );
			$mpdf->SetHTMLHeader( rbfw_export_pdf_header_html( $filters ) );
			$mpdf->SetHTMLFooter( rbfw_export_pdf_footer_html() );
			$mpdf->WriteHTML( $body_html );
			$mpdf->Output( rbfw_export_filename_stem() . '.pdf', 'D' );
			return;
		} catch ( \Exception $e ) {
			// Fall through to the print fallback on any rendering error.
			$body_html .= '';
		}
	}

	// Fallback: a print-optimised HTML page that the browser can "Save as PDF".
	rbfw_export_pdf_print_fallback( $rows, $filters );
}

/**
 * Shared inline CSS for the PDF table (mPDF supports a CSS subset).
 *
 * @return string
 */
function rbfw_export_pdf_css() {
	return '
		body { font-family: sans-serif; color: #1f2430; font-size: 8.5px; }
		.rbfw_exp_meta { color: #5a6072; font-size: 9px; margin: 0 0 6px; }
		table.rbfw_exp { width: 100%; border-collapse: collapse; }
		table.rbfw_exp th { background: #4F6EF7; color: #fff; font-size: 8px; text-align: left; padding: 5px 4px; }
		table.rbfw_exp td { border-bottom: 0.5px solid #e3e7ef; padding: 4px; font-size: 8px; vertical-align: top; }
		table.rbfw_exp tr:nth-child(even) td { background: #f6f8fc; }
		.rbfw_exp_num { text-align: right; }
		.rbfw_exp_tot td { font-weight: bold; background: #eef2ff; border-top: 1px solid #4F6EF7; }
		.rbfw_exp_empty { padding: 16px; text-align: center; color: #8a90a2; }
	';
}

/**
 * mPDF running page header.
 *
 * @param array $filters applied filters.
 * @return string
 */
function rbfw_export_pdf_header_html( $filters ) {
	$title   = esc_html( get_bloginfo( 'name' ) ) . ' — ' . esc_html__( 'Orders Export', 'booking-and-rental-manager-for-woocommerce' );
	$summary = esc_html( rbfw_export_filter_summary( $filters ) );
	$gen     = esc_html__( 'Generated', 'booking-and-rental-manager-for-woocommerce' ) . ': ' . esc_html( date_i18n( 'Y-m-d H:i' ) );

	// A two-cell table keeps the title (left) and the generated date (right)
	// reliably separated in mPDF, where CSS float in a page header is flaky.
	return '<table style="width:100%;font-family:sans-serif;border-bottom:1px solid #4F6EF7;">'
		. '<tr>'
		. '<td style="font-size:12px;font-weight:bold;color:#1f2430;padding:0 12px 5px 0;vertical-align:bottom;">' . $title . '</td>'
		. '<td style="font-size:8px;color:#5a6072;text-align:right;padding:0 0 5px 12px;vertical-align:bottom;white-space:nowrap;">' . $gen . '</td>'
		. '</tr>'
		. '<tr><td colspan="2" style="font-size:8px;color:#5a6072;padding:0 0 5px;">' . $summary . '</td></tr>'
		. '</table>';
}

/**
 * mPDF running page footer with page numbers.
 *
 * @return string
 */
function rbfw_export_pdf_footer_html() {
	return '<div style="font-family:sans-serif;font-size:7.5px;color:#8a90a2;border-top:0.5px solid #e3e7ef;padding-top:3px;text-align:right;">'
		. esc_html__( 'Page', 'booking-and-rental-manager-for-woocommerce' ) . ' {PAGENO} / {nbpg}</div>';
}

/**
 * Build the PDF body table HTML.
 *
 * @param array $rows    export rows.
 * @param array $filters applied filters.
 * @return string
 */
function rbfw_export_pdf_body_html( $rows, $filters ) {
	$columns    = rbfw_export_enabled_columns();
	$money_keys = array( 'duration_cost', 'service_cost', 'discount', 'security_deposit', 'order_total' );
	$num_keys   = array( 'qty', 'total_days' );

	$html  = '<style>' . rbfw_export_pdf_css() . '</style>';
	$html .= '<table class="rbfw_exp"><thead><tr>';
	foreach ( $columns as $label ) {
		$html .= '<th>' . esc_html( $label ) . '</th>';
	}
	$html .= '</tr></thead><tbody>';

	if ( empty( $rows ) ) {
		$html .= '<tr><td class="rbfw_exp_empty" colspan="' . count( $columns ) . '">'
			. esc_html__( 'No orders match the selected filters.', 'booking-and-rental-manager-for-woocommerce' )
			. '</td></tr>';
	} else {
		foreach ( $rows as $row ) {
			$html .= '<tr>';
			foreach ( $columns as $key => $label ) {
				$value = isset( $row[ $key ] ) ? $row[ $key ] : '';
				$class = '';
				if ( in_array( $key, $money_keys, true ) ) {
					$value = ( '' === $value ) ? '' : rbfw_export_money( $value );
					$class = ' class="rbfw_exp_num"';
				} elseif ( in_array( $key, $num_keys, true ) ) {
					$class = ' class="rbfw_exp_num"';
				}
				$html .= '<td' . $class . '>' . esc_html( $value ) . '</td>';
			}
			$html .= '</tr>';
		}

		// Totals row — aligned to the order-total column when it is shown, and
		// robust to that column being toggled off or positioned first/last.
		$keys      = array_keys( $columns );
		$col_count = count( $columns );
		$total_pos = array_search( 'order_total', $keys, true );
		$label     = esc_html__( 'Grand Total', 'booking-and-rental-manager-for-woocommerce' )
			. ' (' . esc_html( rbfw_export_count_orders( $rows ) ) . ' '
			. esc_html__( 'orders', 'booking-and-rental-manager-for-woocommerce' ) . ')';
		$amount    = esc_html( rbfw_export_money( rbfw_export_grand_total( $rows ) ) );

		$html .= '<tr class="rbfw_exp_tot">';
		if ( false === $total_pos ) {
			// No order-total column: a single full-width labelled row.
			$html .= '<td colspan="' . $col_count . '">' . $label . '</td>';
		} else {
			$lead  = (int) $total_pos;                 // cells before the total column.
			$trail = $col_count - $total_pos - 1;       // cells after it.
			if ( $lead > 0 ) {
				$html .= '<td colspan="' . $lead . '">' . $label . '</td>';
				$html .= '<td class="rbfw_exp_num">' . $amount . '</td>';
			} else {
				// order_total is the first column.
				$html .= '<td class="rbfw_exp_num">' . $amount . '</td>';
			}
			if ( $trail > 0 ) {
				$cell = ( 0 === $lead ) ? $label : '';
				$html .= '<td colspan="' . $trail . '">' . $cell . '</td>';
			} elseif ( 0 === $lead ) {
				// Total is first and only-ish: ensure the label is still shown.
				$html .= '<td>' . $label . '</td>';
			}
		}
		$html .= '</tr>';
	}

	$html .= '</tbody></table>';

	return $html;
}

/**
 * Print-optimised HTML page used when mPDF is not available.
 *
 * Sends a normal HTML document that triggers window.print() on load, letting the
 * user choose "Save as PDF". This keeps the PDF option functional in the free
 * plugin without bundling a PDF library.
 *
 * @param array $rows    export rows.
 * @param array $filters applied filters.
 */
function rbfw_export_pdf_print_fallback( $rows, $filters ) {
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );

	$title = get_bloginfo( 'name' ) . ' — ' . __( 'Orders Export', 'booking-and-rental-manager-for-woocommerce' );

	echo '<!doctype html><html><head><meta charset="utf-8"><title>' . esc_html( $title ) . '</title>';
	echo '<style>'
		. 'body{font-family:Arial,Helvetica,sans-serif;color:#1f2430;margin:20px;}'
		. 'h1{font-size:18px;margin:0 0 4px;}'
		. '.rbfw_exp_meta{color:#5a6072;font-size:12px;margin:0 0 14px;}'
		. 'table{width:100%;border-collapse:collapse;font-size:11px;}'
		. 'th{background:#4F6EF7;color:#fff;text-align:left;padding:6px 5px;}'
		. 'td{border-bottom:1px solid #e3e7ef;padding:5px;}'
		. 'tr:nth-child(even) td{background:#f6f8fc;}'
		. '.rbfw_exp_num{text-align:right;}'
		. '.rbfw_exp_tot td{font-weight:bold;background:#eef2ff;}'
		. '@media print{.rbfw_exp_noprint{display:none;}}'
		. '.rbfw_exp_btn{display:inline-block;margin:0 0 16px;padding:8px 16px;background:#4F6EF7;color:#fff;border:0;border-radius:6px;cursor:pointer;font-size:13px;}'
		. rbfw_export_pdf_css() // Static, trusted CSS string — not user input.
		. '</style></head><body>';

	echo '<button class="rbfw_exp_btn rbfw_exp_noprint" onclick="window.print()">'
		. esc_html__( 'Print / Save as PDF', 'booking-and-rental-manager-for-woocommerce' ) . '</button>';
	echo '<h1>' . esc_html( $title ) . '</h1>';
	echo '<p class="rbfw_exp_meta">' . esc_html( rbfw_export_filter_summary( $filters ) )
		. '<br>' . esc_html__( 'Generated', 'booking-and-rental-manager-for-woocommerce' ) . ': '
		. esc_html( date_i18n( 'Y-m-d H:i' ) ) . '</p>';

	// Reuse the same table markup as the mPDF body. Every dynamic value inside
	// rbfw_export_pdf_body_html() is passed through esc_html(); the surrounding
	// markup is static, so this builder output is safe to emit directly.
	echo rbfw_export_pdf_body_html( $rows, $filters ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped builder output.

	echo '<script>window.addEventListener("load",function(){setTimeout(function(){window.print();},250);});</script>';
	echo '</body></html>';
}
