<?php
/*
* Author 	:	MagePeople Team
* Copyright	: 	mage-people.com
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
add_action( 'add_meta_boxes_rbfw_order', 'rbfw_order_meta_box' );
function rbfw_order_meta_box() {
    add_meta_box( 'rbfw-order-meta-box', esc_html__( 'Order Details', 'booking-and-rental-manager-for-woocommerce' ), 'rbfw_order_meta_box_callback' );
    add_meta_box( 'rbfw-order-meta-box-sidebar', esc_html__( 'Order Status Update', 'booking-and-rental-manager-for-woocommerce' ), 'rbfw_order_meta_box_sidebar_callback', '', 'side', 'core' );
}
/**
 * AJAX: update a WooCommerce order status straight from the Order List popup.
 *
 * Mirrors the side effects of the classic "Order Status Update" meta-box save
 * (revision log + reports + inventory sync) so both flows stay consistent.
 */
add_action( 'wp_ajax_rbfw_update_order_status', 'rbfw_update_order_status_callback' );
function rbfw_update_order_status_callback() {

    check_ajax_referer( 'rbfw_update_order_status_action', 'nonce' );

    if ( ! current_user_can( rbfw_bookings_capability() ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized access.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
    }

    if ( ! function_exists( 'wc_get_order' ) || ! function_exists( 'wc_get_order_statuses' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'WooCommerce is not active.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    $post_id    = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
    $new_status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';

    if ( ! $post_id || '' === $new_status ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Missing required data.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    // Build an allow-list of valid statuses ( slug without the wc- prefix => label ).
    $allowed = array();
    foreach ( wc_get_order_statuses() as $key => $label ) {
        $allowed[ str_replace( 'wc-', '', $key ) ] = $label;
    }
    if ( ! isset( $allowed[ $new_status ] ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Invalid order status.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    $wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
    $order       = $wc_order_id ? wc_get_order( $wc_order_id ) : false;
    if ( ! $order ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Order not found.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    // Nothing to do if the status is unchanged.
    if ( $order->get_status() === $new_status ) {
        wp_send_json_success( array(
            'status'       => $new_status,
            'status_label' => ucfirst( $new_status ),
            'message'      => esc_html__( 'Status is already set.', 'booking-and-rental-manager-for-woocommerce' ),
        ) );
    }

    // Update the WooCommerce order. This fires woocommerce_order_status_changed,
    // which the plugin already hooks ( rbfw_wc_status_update ) for its own side effects.
    $order->update_status( $new_status, '', true );

    // Keep the booking CPT + reports + inventory in sync with the new status.
    update_post_meta( $post_id, 'rbfw_order_status', $new_status );

    $current_user  = wp_get_current_user();
    $username      = $current_user->user_login;
    $modified_date = current_datetime()->format( 'F j, Y h:i a' );
    $note          = 'Status changed to ' . wp_kses( '<strong>' . $allowed[ $new_status ] . '</strong>', rbfw_allowed_html() ) . ' by ' . $username . ' on ' . $modified_date;
    $revisions     = get_post_meta( $post_id, 'rbfw_order_status_revision', true );
    if ( empty( $revisions ) || ! is_array( $revisions ) ) {
        $revisions = array();
    }
    $revisions[] = $note;
    update_post_meta( $post_id, 'rbfw_order_status_revision', $revisions );

    if ( function_exists( 'rbfw_update_reports_status' ) ) {
        rbfw_update_reports_status( $wc_order_id, $new_status );
    }
    if ( function_exists( 'rbfw_update_inventory' ) ) {
        rbfw_update_inventory( $wc_order_id, $new_status );
    }

    wp_send_json_success( array(
        'status'       => $new_status,
        'status_label' => ucfirst( $new_status ),
        'message'      => esc_html__( 'Order status updated.', 'booking-and-rental-manager-for-woocommerce' ),
    ) );
}

/**
 * Recalculate the Order List dashboard stats ( counts + amounts per bucket ).
 *
 * Single source of truth used by the AJAX delete handler to return fresh
 * numbers. Keep the status→bucket mapping in sync with the inline calculation
 * in MageRBFWClass::rbfw_order_list().
 *
 * @return array
 */
function rbfw_calculate_order_list_stats() {
    $stats = array(
        'total_orders'      => 0, 'total_amount'      => 0,
        'completed_orders'  => 0, 'completed_amount'  => 0,
        'cancelled_orders'  => 0, 'cancelled_amount'  => 0,
        'pending_orders'    => 0, 'pending_amount'    => 0,
        'refunded_orders'   => 0, 'refunded_amount'   => 0,
        // Revenue summary ( "earned" money = completed + processing ).
        'processing_orders' => 0, 'processing_amount' => 0,
        'paid_orders'       => 0,
        'net_revenue'       => 0,
        'this_month_revenue'=> 0,
        'avg_order_value'   => 0,
    );

    if ( ! function_exists( 'wc_get_order' ) ) {
        return $stats;
    }

    $this_ym = function_exists( 'current_time' ) ? current_time( 'Y-m' ) : gmdate( 'Y-m' );

    $query = new WP_Query( array(
        'post_type'      => 'rbfw_order',
        'order'          => 'DESC',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future', 'inherit' ),
    ) );

    foreach ( $query->posts as $post_id ) {
        $wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
        $order       = wc_get_order( $wc_order_id );
        if ( ! $order ) {
            continue;
        }
        $status = $order->get_status();
        if ( 'trash' === $status || 'wc-trash' === $status ) {
            continue;
        }
        $order_total = $order->get_total();
        $stats['total_orders']++;
        $stats['total_amount'] += $order_total;

        $norm = str_replace( 'wc-', '', $status );

        switch ( $norm ) {
            case 'completed':
                $stats['completed_orders']++;
                $stats['completed_amount'] += $order_total;
                break;
            case 'cancelled':
            case 'canceled':
            case 'failed':
                $stats['cancelled_orders']++;
                $stats['cancelled_amount'] += $order_total;
                break;
            case 'refunded':
                $stats['refunded_orders']++;
                $stats['refunded_amount'] += $order_total;
                break;
            case 'pending':
            case 'on-hold':
            case 'processing':
                $stats['pending_orders']++;
                $stats['pending_amount'] += $order_total;
                break;
        }

        // Revenue: count completed + processing as earned/collected money.
        if ( 'completed' === $norm || 'processing' === $norm ) {
            $stats['net_revenue'] += $order_total;
            $stats['paid_orders']++;
            if ( 'processing' === $norm ) {
                $stats['processing_orders']++;
                $stats['processing_amount'] += $order_total;
            }
            $created = $order->get_date_created();
            if ( $created && $created->date( 'Y-m' ) === $this_ym ) {
                $stats['this_month_revenue'] += $order_total;
            }
        }
    }
    wp_reset_postdata();

    $stats['avg_order_value'] = $stats['paid_orders'] > 0 ? ( $stats['net_revenue'] / $stats['paid_orders'] ) : 0;

    return $stats;
}

/**
 * AJAX: move a booking + its linked WooCommerce order to Trash from the list.
 *
 * Both records are trashed together ( recoverable ). Returns freshly
 * recalculated dashboard stats so the list updates without a page reload.
 */
add_action( 'wp_ajax_rbfw_delete_order', 'rbfw_delete_order_callback' );
function rbfw_delete_order_callback() {

    check_ajax_referer( 'rbfw_delete_order_action', 'nonce' );

    if ( ! current_user_can( rbfw_bookings_capability() ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized access.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
    if ( ! $post_id || 'rbfw_order' !== get_post_type( $post_id ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Invalid order.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    // Trash the linked WooCommerce order ( HPOS-safe via the WC API ).
    $wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
    if ( $wc_order_id && function_exists( 'wc_get_order' ) ) {
        $order = wc_get_order( $wc_order_id );
        if ( $order ) {
            $order->delete( false ); // false = move to Trash, not permanent.
        }
    }

    // Trash the booking record.
    wp_trash_post( $post_id );

    $stats = rbfw_calculate_order_list_stats();
    $fmt   = static function ( $count, $amount ) {
        return array(
            'count'  => number_format_i18n( $count ),
            'amount' => wc_price( $amount ),
            'pos'    => $amount > 0,
        );
    };

    wp_send_json_success( array(
        'message' => esc_html__( 'Order moved to Trash.', 'booking-and-rental-manager-for-woocommerce' ),
        'header'  => sprintf(
            /* translators: %s: number of orders. */
            _n( '%s Order', '%s Orders', $stats['total_orders'], 'booking-and-rental-manager-for-woocommerce' ),
            number_format_i18n( $stats['total_orders'] )
        ),
        'stats'   => array(
            'total'     => $fmt( $stats['total_orders'], $stats['total_amount'] ),
            'cancelled' => $fmt( $stats['cancelled_orders'], $stats['cancelled_amount'] ),
            'completed' => $fmt( $stats['completed_orders'], $stats['completed_amount'] ),
            'pending'   => $fmt( $stats['pending_orders'], $stats['pending_amount'] ),
            'refunded'  => $fmt( $stats['refunded_orders'], $stats['refunded_amount'] ),
        ),
        'revenue' => array(
            'net'        => wc_price( $stats['net_revenue'] ),
            'completed'  => wc_price( $stats['completed_amount'] ),
            'processing' => wc_price( $stats['processing_amount'] ),
            'month'      => wc_price( $stats['this_month_revenue'] ),
            'avg'        => wc_price( $stats['avg_order_value'] ),
            'paid_label' => esc_html( sprintf(
                /* translators: %s: number of paid (completed + processing) orders. */
                _n( 'From %s paid order', 'From %s paid orders', $stats['paid_orders'], 'booking-and-rental-manager-for-woocommerce' ),
                number_format_i18n( $stats['paid_orders'] )
            ) ),
        ),
    ) );
}

/**
 * Helper: load the first ticket-info entry for a booking order, plus its key.
 *
 * @return array { 'all' => full ticket array, 'key' => first key, 'ticket' => first entry }
 */
function rbfw_get_order_first_ticket( $post_id ) {
    $all = maybe_unserialize( get_post_meta( $post_id, 'rbfw_ticket_info', true ) );
    if ( ! is_array( $all ) || empty( $all ) ) {
        return array( 'all' => array(), 'key' => null, 'ticket' => array() );
    }
    $keys = array_keys( $all );
    $key  = $keys[0];
    return array( 'all' => $all, 'key' => $key, 'ticket' => is_array( $all[ $key ] ) ? $all[ $key ] : array() );
}

/**
 * Helper: load an item's category-wise service catalog as a clean array.
 */
function rbfw_get_item_service_catalog( $rbfw_id ) {
    $cats = get_post_meta( $rbfw_id, 'rbfw_service_category_price', true );
    if ( ! is_array( $cats ) ) {
        $cats = json_decode( $cats, true );
    }
    return is_array( $cats ) ? $cats : array();
}

/**
 * Propagate an order edit into the Reports store ( rbfw_order_meta attendee posts ).
 *
 * The Reports dashboard reads flat post-meta copied from the ticket at checkout, so
 * an edit must be mirrored onto every attendee post linked to the WooCommerce order.
 */
function rbfw_sync_attendee_meta_from_edit( $wc_order_id, $t, $billing, $billing_fields, $grand_total, $order_total ) {
    if ( ! $wc_order_id ) {
        return;
    }
    $query = new WP_Query( array(
        'post_type'      => 'rbfw_order_meta',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'any',
        'meta_query'     => array(
            'relation' => 'OR',
            array( 'key' => 'rbfw_link_order_id', 'value' => $wc_order_id ),
            array( 'key' => 'rbfw_order_id', 'value' => $wc_order_id ),
        ),
    ) );
    if ( empty( $query->posts ) ) {
        return;
    }

    $start_dt = isset( $t['rbfw_start_datetime'] ) ? $t['rbfw_start_datetime'] : '';
    $end_dt   = isset( $t['rbfw_end_datetime'] ) ? $t['rbfw_end_datetime'] : '';
    $address  = trim( $billing_fields['address_1'] . ' ' . $billing_fields['address_2'] );

    // Day-wise bookings store no real time — keep the stored datetime date-only
    // so it never surfaces a meaningless 12:00 AM anywhere it is displayed. The
    // booking is "timed" only when it has a real START time; the end time can be
    // a duration artifact, so gate it on the start too.
    $rbfw_meta_is_timed = rbfw_booking_has_time( isset( $t['rbfw_start_time'] ) ? $t['rbfw_start_time'] : '' );
    $start_dt_fmt = $rbfw_meta_is_timed ? 'Y-m-d h:i A' : 'Y-m-d';
    $end_dt_fmt   = ( $rbfw_meta_is_timed && rbfw_booking_has_time( isset( $t['rbfw_end_time'] ) ? $t['rbfw_end_time'] : '' ) ) ? 'Y-m-d h:i A' : 'Y-m-d';

    $meta = array(
        'ticket_price'            => $grand_total,
        'rbfw_ticket_total_price' => $order_total,
        'duration_cost'           => isset( $t['duration_cost'] ) ? $t['duration_cost'] : 0,
        'service_cost'            => isset( $t['service_cost'] ) ? $t['service_cost'] : 0,
        'total_days'              => isset( $t['total_days'] ) ? $t['total_days'] : 1,
        'discount_amount'         => isset( $t['discount_amount'] ) ? $t['discount_amount'] : 0,
        'rbfw_management_price'   => isset( $t['rbfw_management_price'] ) ? $t['rbfw_management_price'] : 0,
        'security_deposit_amount' => isset( $t['security_deposit_amount'] ) ? $t['security_deposit_amount'] : 0,
        'rbfw_service_infos'      => isset( $t['rbfw_service_infos'] ) ? $t['rbfw_service_infos'] : array(),
        'rbfw_start_date'         => isset( $t['rbfw_start_date'] ) ? gmdate( 'Y-m-d', strtotime( $t['rbfw_start_date'] ) ) : '',
        'end_date'                => isset( $t['rbfw_end_date'] ) ? gmdate( 'Y-m-d', strtotime( $t['rbfw_end_date'] ) ) : '',
        'start_date'              => isset( $t['rbfw_start_date'] ) ? gmdate( 'Y-m-d', strtotime( $t['rbfw_start_date'] ) ) : '',
        'rbfw_end_date'           => isset( $t['rbfw_end_date'] ) ? gmdate( 'Y-m-d', strtotime( $t['rbfw_end_date'] ) ) : '',
        'rbfw_start_datetime'     => $start_dt ? gmdate( $start_dt_fmt, strtotime( $start_dt ) ) : '',
        'rbfw_end_datetime'       => $end_dt ? gmdate( $end_dt_fmt, strtotime( $end_dt ) ) : '',
        'rbfw_billing_phone'      => $billing_fields['phone'],
        'rbfw_billing_address'    => $address,
    );
    if ( '' !== $billing ) {
        $meta['rbfw_billing_name'] = $billing;
    }
    if ( '' !== $billing_fields['email'] ) {
        $meta['rbfw_billing_email'] = $billing_fields['email'];
    }
    if ( isset( $t['rbfw_regf_info'] ) ) {
        $meta['rbfw_regf_info'] = $t['rbfw_regf_info'];
    }

    foreach ( $query->posts as $attendee_id ) {
        foreach ( $meta as $key => $value ) {
            update_post_meta( $attendee_id, $key, $value );
        }
    }
}

/**
 * AJAX: return the "Edit Order" modal form HTML for a given booking order.
 */
add_action( 'wp_ajax_rbfw_get_order_edit_form', 'rbfw_get_order_edit_form_callback' );
function rbfw_get_order_edit_form_callback() {

    check_ajax_referer( 'rbfw_get_order_edit_form_action', 'nonce' );
    if ( ! current_user_can( rbfw_bookings_capability() ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized access.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
    if ( ! $post_id || 'rbfw_order' !== get_post_type( $post_id ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Invalid order.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    $data = rbfw_get_order_first_ticket( $post_id );
    $t    = $data['ticket'];
    if ( empty( $t ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'This order has no editable booking data.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    $rbfw_id      = isset( $t['rbfw_id'] ) ? $t['rbfw_id'] : 0;
    $catalog      = rbfw_get_item_service_catalog( $rbfw_id );

    // WooCommerce billing details (full address).
    $wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
    $wc_order    = ( $wc_order_id && function_exists( 'wc_get_order' ) ) ? wc_get_order( $wc_order_id ) : false;
    $billing     = array(
        'first_name' => $wc_order ? $wc_order->get_billing_first_name() : '',
        'last_name'  => $wc_order ? $wc_order->get_billing_last_name() : '',
        'company'    => $wc_order ? $wc_order->get_billing_company() : '',
        'email'      => $wc_order ? $wc_order->get_billing_email() : get_post_meta( $post_id, 'rbfw_billing_email', true ),
        'phone'      => $wc_order ? $wc_order->get_billing_phone() : '',
        'address_1'  => $wc_order ? $wc_order->get_billing_address_1() : '',
        'address_2'  => $wc_order ? $wc_order->get_billing_address_2() : '',
        'city'       => $wc_order ? $wc_order->get_billing_city() : '',
        'state'      => $wc_order ? $wc_order->get_billing_state() : '',
        'postcode'   => $wc_order ? $wc_order->get_billing_postcode() : '',
        'country'    => $wc_order ? $wc_order->get_billing_country() : '',
    );
    if ( '' === $billing['first_name'] && '' === $billing['last_name'] ) {
        $billing['first_name'] = get_post_meta( $post_id, 'rbfw_billing_name', true );
    }

    // Attendee / registration form values stored on the booking ( label => value per field ).
    $regf_info = ( ! empty( $t['rbfw_regf_info'] ) && is_array( $t['rbfw_regf_info'] ) ) ? $t['rbfw_regf_info'] : array();

    // Map existing selected quantities by service title.
    $existing_qty = array();
    if ( ! empty( $t['rbfw_service_infos'] ) && is_array( $t['rbfw_service_infos'] ) ) {
        foreach ( $t['rbfw_service_infos'] as $svcs ) {
            if ( is_array( $svcs ) ) {
                foreach ( $svcs as $s ) {
                    if ( ! empty( $s['name'] ) ) {
                        $existing_qty[ $s['name'] ] = (int) ( isset( $s['quantity'] ) ? $s['quantity'] : 0 );
                    }
                }
            }
        }
    }

    $get = static function ( $key, $default = '' ) use ( $t ) {
        return isset( $t[ $key ] ) ? $t[ $key ] : $default;
    };

    ob_start();
    ?>
    <input type="hidden" id="rbfw_eo_post_id" value="<?php echo esc_attr( $post_id ); ?>">
    <input type="hidden" id="rbfw_eo_total_days" value="<?php echo esc_attr( (int) $get( 'total_days', 1 ) ); ?>">

    <?php
    // Day-wise (non-hourly) bookings store no meaningful time. A booking is only
    // "timed" when it has a real START time — that is what gets set when the
    // customer actually picks a time/slot. The end time can be a mere duration
    // artifact (e.g. a "2 hour" rental stores 02:00 with no start), so it must
    // never be trusted on its own. When there is no start time, hide both inputs
    // so the Edit Order form reflects the real order data, not a bogus 12:00 am.
    $eo_start_time    = $get( 'rbfw_start_time' );
    $eo_end_time      = $get( 'rbfw_end_time' );
    $eo_booking_timed = rbfw_booking_has_time( $eo_start_time );
    $eo_has_st_time   = $eo_booking_timed;
    $eo_has_en_time   = $eo_booking_timed && rbfw_booking_has_time( $eo_end_time );
    ?>
    <div class="rbfw_eo_section_title"><?php esc_html_e( 'Booking Dates', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
    <div class="rbfw_eo_grid">
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Start Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_start_date" class="rbfw_eo_fp_date" value="<?php echo esc_attr( $get( 'rbfw_start_date' ) ); ?>" autocomplete="off">
        </div>
        <?php if ( $eo_has_st_time ) { ?>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Start Time', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_start_time" class="rbfw_eo_fp_time" value="<?php echo esc_attr( $eo_start_time ); ?>" autocomplete="off">
        </div>
        <?php } ?>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'End Date', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_end_date" class="rbfw_eo_fp_date" value="<?php echo esc_attr( $get( 'rbfw_end_date' ) ); ?>" autocomplete="off">
        </div>
        <?php if ( $eo_has_en_time ) { ?>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'End Time', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_end_time" class="rbfw_eo_fp_time" value="<?php echo esc_attr( $eo_end_time ); ?>" autocomplete="off">
        </div>
        <?php } ?>
    </div>
    <?php
    // Preserve the (empty/midnight) values on save without showing the inputs,
    // so saving a day-wise order never mutates its stored time data.
    if ( ! $eo_has_st_time ) {
        ?><input type="hidden" id="rbfw_eo_start_time" value="<?php echo esc_attr( $eo_start_time ); ?>"><?php
    }
    if ( ! $eo_has_en_time ) {
        ?><input type="hidden" id="rbfw_eo_end_time" value="<?php echo esc_attr( $eo_end_time ); ?>"><?php
    }
    ?>

    <div class="rbfw_eo_section_title"><?php esc_html_e( 'Billing Details', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
    <div class="rbfw_eo_grid">
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'First Name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_first" value="<?php echo esc_attr( $billing['first_name'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Last Name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_last" value="<?php echo esc_attr( $billing['last_name'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Email', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="email" id="rbfw_eo_b_email" value="<?php echo esc_attr( $billing['email'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Phone', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_phone" value="<?php echo esc_attr( $billing['phone'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Company', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_company" value="<?php echo esc_attr( $billing['company'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Country', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_country" value="<?php echo esc_attr( $billing['country'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. BD, US', 'booking-and-rental-manager-for-woocommerce' ); ?>">
        </div>
        <div class="rbfw_eo_field rbfw_eo_full">
            <label><?php esc_html_e( 'Address Line 1', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_addr1" value="<?php echo esc_attr( $billing['address_1'] ); ?>">
        </div>
        <div class="rbfw_eo_field rbfw_eo_full">
            <label><?php esc_html_e( 'Address Line 2', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_addr2" value="<?php echo esc_attr( $billing['address_2'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'City', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_city" value="<?php echo esc_attr( $billing['city'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'State', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_state" value="<?php echo esc_attr( $billing['state'] ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Postcode', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="text" id="rbfw_eo_b_postcode" value="<?php echo esc_attr( $billing['postcode'] ); ?>">
        </div>
    </div>

    <?php if ( ! empty( $regf_info ) ) { ?>
        <div class="rbfw_eo_section_title"><?php esc_html_e( 'Attendee / Customer Information', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
        <div class="rbfw_eo_grid">
            <?php foreach ( $regf_info as $regf_key => $regf_field ) {
                $regf_label = is_array( $regf_field ) && isset( $regf_field['label'] ) ? $regf_field['label'] : $regf_key;
                $regf_value = is_array( $regf_field ) && isset( $regf_field['value'] ) ? $regf_field['value'] : '';
                ?>
                <div class="rbfw_eo_field rbfw_eo_full">
                    <label><?php echo esc_html( $regf_label ); ?></label>
                    <input type="text" class="rbfw_eo_regf" data-key="<?php echo esc_attr( $regf_key ); ?>" value="<?php echo esc_attr( $regf_value ); ?>">
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ( ! empty( $catalog ) ) { ?>
        <div class="rbfw_eo_section_title"><?php esc_html_e( 'Service Add-ons', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
        <div class="rbfw_eo_services">
            <?php foreach ( $catalog as $ck => $cat ) {
                $cat_title    = isset( $cat['cat_title'] ) ? $cat['cat_title'] : '';
                $cat_services = ( ! empty( $cat['cat_services'] ) && is_array( $cat['cat_services'] ) ) ? $cat['cat_services'] : array();
                if ( empty( $cat_services ) ) {
                    continue;
                }
                ?>
                <div class="rbfw_eo_svc_cat">
                    <?php if ( '' !== (string) $cat_title ) { ?>
                        <div class="rbfw_eo_svc_cat_title"><?php echo esc_html( $cat_title ); ?></div>
                    <?php } ?>
                    <?php foreach ( $cat_services as $sk => $svc ) {
                        if ( empty( $svc['title'] ) ) {
                            continue;
                        }
                        $s_price = isset( $svc['price'] ) ? (float) $svc['price'] : 0;
                        $s_type  = isset( $svc['service_price_type'] ) ? $svc['service_price_type'] : '';
                        $s_qty   = isset( $existing_qty[ $svc['title'] ] ) ? (int) $existing_qty[ $svc['title'] ] : 0;
                        ?>
                        <div class="rbfw_eo_svc_row">
                            <div class="rbfw_eo_svc_meta">
                                <span class="rbfw_eo_svc_name"><?php echo esc_html( $svc['title'] ); ?></span>
                                <span class="rbfw_eo_svc_price"><?php echo wp_kses( wc_price( $s_price ), rbfw_allowed_html() ); ?>
                                    <em><?php echo ( 'day_wise' === $s_type ) ? esc_html__( 'Day Wise', 'booking-and-rental-manager-for-woocommerce' ) : esc_html__( 'One Time', 'booking-and-rental-manager-for-woocommerce' ); ?></em>
                                </span>
                            </div>
                            <input type="number" min="0" step="1"
                                   class="rbfw_eo_svc_qty"
                                   data-price="<?php echo esc_attr( $s_price ); ?>"
                                   data-type="<?php echo esc_attr( $s_type ); ?>"
                                   data-cat="<?php echo esc_attr( $ck ); ?>"
                                   data-svc="<?php echo esc_attr( $sk ); ?>"
                                   value="<?php echo esc_attr( $s_qty ); ?>">
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="rbfw_eo_grid rbfw_eo_costs">
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="number" min="0" step="0.01" id="rbfw_eo_duration" value="<?php echo esc_attr( (float) $get( 'duration_cost', 0 ) ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Fee / Management', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="number" min="0" step="0.01" id="rbfw_eo_management" value="<?php echo esc_attr( (float) $get( 'rbfw_management_price', 0 ) ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Discount', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="number" min="0" step="0.01" id="rbfw_eo_discount" value="<?php echo esc_attr( (float) $get( 'discount_amount', 0 ) ); ?>">
        </div>
        <div class="rbfw_eo_field">
            <label><?php esc_html_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
            <input type="number" min="0" step="0.01" id="rbfw_eo_deposit" value="<?php echo esc_attr( (float) $get( 'security_deposit_amount', 0 ) ); ?>">
        </div>
    </div>

    <div class="rbfw_eo_totals">
        <div class="rbfw_eo_total_row"><span><?php esc_html_e( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ); ?></span><strong id="rbfw_eo_resource">—</strong></div>
        <div class="rbfw_eo_total_row"><span><?php esc_html_e( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ); ?></span><strong id="rbfw_eo_subtotal">—</strong></div>
        <div class="rbfw_eo_total_row rbfw_eo_grand"><span><?php esc_html_e( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?></span><strong id="rbfw_eo_total">—</strong></div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success( array( 'html' => $html ) );
}

/**
 * AJAX: save the edited order — recompute prices server-side, update the booking
 * ticket meta and the linked WooCommerce order total.
 */
add_action( 'wp_ajax_rbfw_save_order_edit', 'rbfw_save_order_edit_callback' );
function rbfw_save_order_edit_callback() {

    check_ajax_referer( 'rbfw_save_order_edit_action', 'nonce' );
    if ( ! current_user_can( rbfw_bookings_capability() ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized access.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
    if ( ! $post_id || 'rbfw_order' !== get_post_type( $post_id ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Invalid order.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }

    $data = rbfw_get_order_first_ticket( $post_id );
    if ( null === $data['key'] || empty( $data['ticket'] ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'This order has no editable booking data.', 'booking-and-rental-manager-for-woocommerce' ) ) );
    }
    $all     = $data['all'];
    $tkey    = $data['key'];
    $t       = $data['ticket'];
    $rbfw_id = isset( $t['rbfw_id'] ) ? $t['rbfw_id'] : 0;

    // ── sanitize inputs ──
    $billing_fields = array();
    foreach ( array( 'first_name', 'last_name', 'company', 'phone', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country' ) as $bf ) {
        $billing_fields[ $bf ] = isset( $_POST[ 'billing_' . $bf ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_' . $bf ] ) ) : '';
    }
    $billing_fields['email'] = isset( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) ) : '';
    $billing = trim( $billing_fields['first_name'] . ' ' . $billing_fields['last_name'] );
    $regf_in = ( isset( $_POST['regf'] ) && is_array( $_POST['regf'] ) ) ? wp_unslash( $_POST['regf'] ) : array();
    $start_date    = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : ( isset( $t['rbfw_start_date'] ) ? $t['rbfw_start_date'] : '' );
    $start_time    = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : ( isset( $t['rbfw_start_time'] ) ? $t['rbfw_start_time'] : '' );
    $end_date      = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : ( isset( $t['rbfw_end_date'] ) ? $t['rbfw_end_date'] : '' );
    $end_time      = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : ( isset( $t['rbfw_end_time'] ) ? $t['rbfw_end_time'] : '' );
    $duration_cost = isset( $_POST['duration_cost'] ) ? max( 0, (float) wp_unslash( $_POST['duration_cost'] ) ) : 0;
    $management    = isset( $_POST['management_price'] ) ? max( 0, (float) wp_unslash( $_POST['management_price'] ) ) : 0;
    $discount      = isset( $_POST['discount_amount'] ) ? max( 0, (float) wp_unslash( $_POST['discount_amount'] ) ) : 0;
    $deposit       = isset( $_POST['security_deposit'] ) ? max( 0, (float) wp_unslash( $_POST['security_deposit'] ) ) : 0;
    $qty_in        = ( isset( $_POST['service_qty'] ) && is_array( $_POST['service_qty'] ) ) ? wp_unslash( $_POST['service_qty'] ) : array();

    // ── total days from the (new) date range ──
    $s_ts       = strtotime( $start_date );
    $e_ts       = strtotime( $end_date );
    $total_days = ( $s_ts && $e_ts && $e_ts > $s_ts ) ? max( 1, (int) round( ( $e_ts - $s_ts ) / DAY_IN_SECONDS ) ) : 1;

    // ── recompute services from the item catalog ( authoritative prices ) ──
    $catalog       = rbfw_get_item_service_catalog( $rbfw_id );
    $service_cost  = 0;
    $service_infos = array();
    foreach ( $catalog as $ck => $cat ) {
        $cat_title    = isset( $cat['cat_title'] ) ? $cat['cat_title'] : '';
        $cat_services = ( ! empty( $cat['cat_services'] ) && is_array( $cat['cat_services'] ) ) ? $cat['cat_services'] : array();
        foreach ( $cat_services as $sk => $svc ) {
            $qty = isset( $qty_in[ $ck ][ $sk ] ) ? max( 0, (int) $qty_in[ $ck ][ $sk ] ) : 0;
            if ( $qty <= 0 || empty( $svc['title'] ) ) {
                continue;
            }
            $price = isset( $svc['price'] ) ? (float) $svc['price'] : 0;
            $type  = isset( $svc['service_price_type'] ) ? $svc['service_price_type'] : '';
            $service_cost += ( 'day_wise' === $type ) ? $price * $qty * $total_days : $price * $qty;
            if ( ! isset( $service_infos[ $cat_title ] ) ) {
                $service_infos[ $cat_title ] = array();
            }
            $service_infos[ $cat_title ][] = array(
                'name'               => sanitize_text_field( $svc['title'] ),
                'price'              => $price,
                'quantity'           => $qty,
                'service_price_type' => $type,
            );
        }
    }

    $line_total  = max( 0, $duration_cost + $service_cost + $management - $discount );
    $grand_total = $line_total + $deposit;

    // ── persist the booking ticket meta ──
    $t['rbfw_start_date']         = $start_date;
    $t['rbfw_start_time']         = $start_time;
    $t['rbfw_end_date']           = $end_date;
    $t['rbfw_end_time']           = $end_time;
    $t['rbfw_start_datetime']     = gmdate( 'Y-m-d H:i', strtotime( $start_date . ' ' . $start_time ) );
    $t['rbfw_end_datetime']       = gmdate( 'Y-m-d H:i', strtotime( $end_date . ' ' . $end_time ) );
    $t['duration_cost']           = $duration_cost;
    $t['service_cost']            = $service_cost;
    $t['rbfw_service_infos']      = $service_infos;
    $t['total_days']              = $total_days;
    $t['rbfw_management_price']   = $management;
    $t['discount_amount']         = $discount;
    $t['security_deposit_amount'] = $deposit;
    $t['ticket_price']            = $grand_total;

    // ── attendee / registration form values ( update existing fields only ) ──
    if ( ! empty( $regf_in ) && ! empty( $t['rbfw_regf_info'] ) && is_array( $t['rbfw_regf_info'] ) ) {
        foreach ( $t['rbfw_regf_info'] as $regf_key => $regf_field ) {
            if ( is_array( $regf_field ) && array_key_exists( $regf_key, $regf_in ) ) {
                $t['rbfw_regf_info'][ $regf_key ]['value'] = sanitize_text_field( $regf_in[ $regf_key ] );
            }
        }
    }

    $all[ $tkey ] = $t;
    update_post_meta( $post_id, 'rbfw_ticket_info', $all );
    update_post_meta( $post_id, 'rbfw_ticket_total_price', $grand_total );

    // ── billing name + post title ──
    $wc_order_id = get_post_meta( $post_id, 'rbfw_order_id', true );
    if ( '' !== $billing ) {
        update_post_meta( $post_id, 'rbfw_billing_name', $billing );
        wp_update_post( array( 'ID' => $post_id, 'post_title' => '#' . $wc_order_id . ' ' . $billing ) );
    }
    if ( '' !== $billing_fields['email'] ) {
        update_post_meta( $post_id, 'rbfw_billing_email', $billing_fields['email'] );
    }

    // ── sync the linked WooCommerce order ( total + billing details ) ──
    $order       = ( $wc_order_id && function_exists( 'wc_get_order' ) ) ? wc_get_order( $wc_order_id ) : false;
    $order_total = $grand_total;
    if ( $order ) {
        foreach ( $order->get_items() as $item ) {
            if ( (string) $item->get_meta( '_rbfw_id', true ) === (string) $rbfw_id ) {
                $item->set_subtotal( $line_total );
                $item->set_total( $line_total );
                // Keep the line-item ticket snapshot in sync (read by the detail panel + PDF).
                $item->update_meta_data( '_rbfw_ticket_info', $all );
                $item->save();
                break;
            }
        }
        // Billing address.
        $order->set_billing_first_name( $billing_fields['first_name'] );
        $order->set_billing_last_name( $billing_fields['last_name'] );
        $order->set_billing_company( $billing_fields['company'] );
        if ( '' !== $billing_fields['email'] ) {
            $order->set_billing_email( $billing_fields['email'] );
        }
        $order->set_billing_phone( $billing_fields['phone'] );
        $order->set_billing_address_1( $billing_fields['address_1'] );
        $order->set_billing_address_2( $billing_fields['address_2'] );
        $order->set_billing_city( $billing_fields['city'] );
        $order->set_billing_state( $billing_fields['state'] );
        $order->set_billing_postcode( $billing_fields['postcode'] );
        $order->set_billing_country( $billing_fields['country'] );
        $order->calculate_totals( false );
        $order->save();
        $order_total = $order->get_total();
        $order->add_order_note( sprintf(
            /* translators: %s: formatted order total. */
            __( 'Order details edited from the Order List. New total: %s', 'booking-and-rental-manager-for-woocommerce' ),
            wp_strip_all_tags( wc_price( $order_total ) )
        ) );
    }

    // ── propagate to the Reports store ( rbfw_order_meta attendee posts ) ──
    rbfw_sync_attendee_meta_from_edit( $wc_order_id, $t, $billing, $billing_fields, $grand_total, $order_total );

    wp_send_json_success( array(
        'message'       => esc_html__( 'Order updated successfully.', 'booking-and-rental-manager-for-woocommerce' ),
        'order_total'   => (float) $order_total,
        'total_html'    => wc_price( $order_total ),
        'billing'       => $billing,
        'start_display' => $s_ts ? date_i18n( rbfw_booking_has_time( $start_time ) ? 'F j, Y g:i a' : 'F j, Y', strtotime( $t['rbfw_start_datetime'] ) ) : '',
        'end_display'   => $e_ts ? date_i18n( ( rbfw_booking_has_time( $start_time ) && rbfw_booking_has_time( $end_time ) ) ? 'F j, Y g:i a' : 'F j, Y', strtotime( $t['rbfw_end_datetime'] ) ) : '',
    ) );
}

add_action( 'wp_ajax_fetch_order_details', 'fetch_order_details_callback' );
function fetch_order_details_callback() {

 /*   if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) {
        wp_send_json_error( 'Invalid request.' );
        wp_die();
    }*/

    check_ajax_referer( 'rbfw_fetch_order_details_action', 'nonce' );

    if ( ! current_user_can( rbfw_bookings_capability() ) ) {
        wp_send_json_error( 'Unauthorized access', 403 );
    }


    global $rbfw;
    if ( isset( $_POST['post_id'] ) ) {
        $rbfw_order_id = intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) );
        $wc_order_id   = get_post_meta( $rbfw_order_id, 'rbfw_order_id', true );
        $wc_order_details = wc_get_order( $wc_order_id );
        ?>
        <div class="rbfw_order_meta_box_wrap">
            <div class="rbfw_order_meta_box_head">
                <div class="rbfw_ol_dhead">
                    <?php echo rbfw_inv_icon( 'receipt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
                    <span class="rbfw_ol_dhead_title"><?php
                        /* translators: %s: WooCommerce order number. */
                        echo esc_html( sprintf( __( 'Order #%s Details', 'booking-and-rental-manager-for-woocommerce' ), $wc_order_id ) );
                    ?></span>
                </div>
                <button type="button" class="rbfw_ol_edit_order_btn" data-post-id="<?php echo esc_attr( $rbfw_order_id ); ?>">
                    <?php echo rbfw_inv_icon( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
                    <span><?php esc_html_e( 'Edit Order', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                </button>
            </div>
            <div class="rbfw_order_meta_box_body">
                <?php
                $total_security_deposit_amount = 0;
                foreach ( $wc_order_details->get_items() as $item_id => $item ) {
                    $ticket_info = $item->get_meta( '_rbfw_ticket_info', true );
                    // Some line items have no rental ticket data (e.g. plain WooCommerce
                    // products or legacy/partial meta). Normalise to a single ticket array
                    // and skip the item entirely when none is present, instead of fataling.
                    if ( is_array( $ticket_info ) && isset( $ticket_info[0] ) && is_array( $ticket_info[0] ) ) {
                        $ticket_info = $ticket_info[0];
                    } elseif ( is_array( $ticket_info ) && ! empty( $ticket_info ) && is_array( reset( $ticket_info ) ) ) {
                        $ticket_info = reset( $ticket_info );
                    }
                    if ( ! is_array( $ticket_info ) || empty( $ticket_info ) ) {
                        continue;
                    }

                    $item_name = ! empty( $ticket_info['ticket_name'] ) ? $ticket_info['ticket_name'] : '';
                    $rbfw_id   = isset( $ticket_info['rbfw_id'] ) ? $ticket_info['rbfw_id'] : 0;
                    $item_id   = $rbfw_id;
                    $rent_type = isset( $ticket_info['rbfw_rent_type'] ) ? $ticket_info['rbfw_rent_type'] : '';
                    $rbfw_start_datetime = ! empty( $ticket_info['rbfw_start_datetime'] ) ? rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-time-text' ) : '';
                    $rbfw_end_datetime   = ! empty( $ticket_info['rbfw_end_datetime'] ) ? rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-time-text' ) : '';
                    $rbfw_start_time     = ! empty( $ticket_info['rbfw_start_time'] ) ? $ticket_info['rbfw_start_time'] : '';
                    $rbfw_end_time       = ! empty( $ticket_info['rbfw_end_time'] ) ? $ticket_info['rbfw_end_time'] : '';
                    $rbfw_management_info       = ! empty( $ticket_info['rbfw_management_info'] ) ? $ticket_info['rbfw_management_info'] : '';
                    $rbfw_management_price       = ! empty( $ticket_info['rbfw_management_price'] ) ? $ticket_info['rbfw_management_price'] : '';

                    if ( $rent_type == 'resort' || ! rbfw_booking_has_time( $rbfw_start_time ) ) {
                        $rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-text' );
                        $rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-text' );
                    }
                    $tax = ! empty( $ticket_info['rbfw_mps_tax'] ) ? $ticket_info['rbfw_mps_tax'] : 0;
                    if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
                        $BikeCarSdClass = new RBFW_BikeCarSd_Function();
                        $rent_info      = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];
                        if( rbfw_booking_has_time( $rbfw_start_time ) && rbfw_booking_has_time( $rbfw_end_time ) ){
                            $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-time-text');
                        }else{
                            $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-text');
                        }
                        $service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                        $rent_info      = $BikeCarSdClass->rbfw_get_bikecarsd_rent_info( $item_id, $rent_info , $ticket_info['rbfw_start_date']);
                        $service_info   = $BikeCarSdClass->rbfw_get_bikecarsd_service_info( $item_id, $service_info );
                    } elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
                        $BikeCarMdClass = new RBFW_BikeCarMd_Function();
                        $service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                        $service_info   = $BikeCarMdClass->rbfw_get_bikecarmd_service_info( $item_id, $service_info );
                        $item_quantity  = ! empty( $ticket_info['rbfw_item_quantity'] ) ? $ticket_info['rbfw_item_quantity'] : 1;
                        $total_days     = ! empty( $ticket_info['total_days'] ) ? $ticket_info['total_days'] : 1;
                        $pickup_point   = ! empty( $ticket_info['rbfw_pickup_point'] ) ? $ticket_info['rbfw_pickup_point'] : '';
                        $dropoff_point  = ! empty( $ticket_info['rbfw_dropoff_point'] ) ? $ticket_info['rbfw_dropoff_point'] : '';
                    } elseif ( $rent_type == 'multiple_items') {
                        $multiple_items_info   = ! empty( $ticket_info['multiple_items_info'] ) ? $ticket_info['multiple_items_info'] : [];
                        $rbfw_category_wise_info   = ! empty( $ticket_info['rbfw_category_wise_info'] ) ? $ticket_info['rbfw_category_wise_info'] : [];
                        $total_days     = ! empty( $ticket_info['total_days'] ) ? $ticket_info['total_days'] : 1;
                        $pickup_point   = ! empty( $ticket_info['rbfw_pickup_point'] ) ? $ticket_info['rbfw_pickup_point'] : '';
                        $dropoff_point  = ! empty( $ticket_info['rbfw_dropoff_point'] ) ? $ticket_info['rbfw_dropoff_point'] : '';
                    }elseif ( $rent_type == 'resort' ) {
                        $ResortClass  = new RBFW_Resort_Function();
                        $package      = $ticket_info['rbfw_resort_package'];
                        $rent_info    = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];
                        $rbfw_room_price   = ! empty( $ticket_info['rbfw_room_price'] ) ? $ticket_info['rbfw_room_price'] : [];
                        $rent_info    = $ResortClass->rbfw_get_resort_room_info( $item_id, $rent_info, $package , $rbfw_room_price);
                        $service_info = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                        $service_info = $ResortClass->rbfw_get_resort_service_info( $item_id, $service_info );
                    } else {
                        $rent_info    = '';
                        $service_info = '';
                    }
                    $variation_info = ! empty( $ticket_info['rbfw_variation_info'] ) ? $ticket_info['rbfw_variation_info'] : [];
                    // Category-wise extra services ( bike_car_md / dress / equipment / others ).
                    $service_infos     = ! empty( $ticket_info['rbfw_service_infos'] ) ? $ticket_info['rbfw_service_infos'] : [];
                    $has_service_infos = false;
                    if ( is_array( $service_infos ) ) {
                        foreach ( $service_infos as $sv_cat_services ) {
                            if ( is_array( $sv_cat_services ) && count( $sv_cat_services ) ) {
                                $has_service_infos = true;
                                break;
                            }
                        }
                    }
                    $duration_cost  =  $ticket_info['duration_cost'] ;
                    $discount_amount         = ! empty( $ticket_info['discount_amount'] ) ? (float) $ticket_info['discount_amount'] : 0;
                    $security_deposit_amount = ! empty( $ticket_info['security_deposit_amount'] ) ? (float) $ticket_info['security_deposit_amount'] : 0;
                    $discount_type  = ! empty( $ticket_info['discount_type'] ) ? $ticket_info['discount_type'] : '';
                    $rbfw_regf_info = ! empty( $ticket_info['rbfw_regf_info'] ) ? $ticket_info['rbfw_regf_info'] : [];
                    /* End  loop*/
                    ?>
                    <table class="wp-list-table widefat fixed striped table-view-list">
                        <thead>
                        <tr>
                            <th colspan="2">
                               <span class="rbfw_ol_sec_ico"><?php echo rbfw_inv_icon( 'box' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span><?php rbfw_string( 'rbfw_text_item_information', __( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                            </th>
                        </tr>
                        </thead>
                        <tr>
                        <?php if ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) { ?>
                            <tr>
                                <td>
                                    <strong><?php rbfw_string( 'rbfw_text_item_name', __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) );  ?>
                                    </strong>
                                </td>
                                <td><?php echo esc_html( $item_name ) . ' × ' . esc_html( $item_quantity ); ?></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_item_name', __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) );  ?>
                                    </strong>
                                </td>
                                <td><?php echo esc_html( $item_name ); ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_item_type', __( 'Item Type', 'booking-and-rental-manager-for-woocommerce' ) );  ?>
                                </strong>
                            </td>
                            <td><?php echo esc_html( rbfw_get_type_label( $rent_type ) ); ?></td>
                        </tr>
                        <?php if ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) { ?>
                            <?php if ( $pickup_point ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_pickup_location', __( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ) );
                                             ?></strong></td>
                                    <td><?php echo esc_html( $pickup_point ); ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ( $dropoff_point ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_dropoff_location', __( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ) );
                                             ?></strong></td>
                                    <td><?php echo esc_html( $dropoff_point ); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        <?php if ( $rent_type == 'resort' ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_package', __( 'Package', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                    </strong>
                                </td>
                                <td><?php echo esc_html( $package ); ?></td>
                            </tr>
                        <?php } ?>

                        <?php if ( ! empty( $discount_type ) ) { ?>
                            <tr>
                                <td><strong>
                                        <?php
                                        if($rbfw->get_option_trans('rbfw_text_discount_type', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                        echo esc_html($rbfw->get_option_trans('rbfw_text_discount_type', 'rbfw_basic_translation_settings'));
                                        }else{
                                        echo esc_html__('Discount Type','booking-and-rental-manager-for-woocommerce');
                                        }
                                        ?>
                                    </strong>
                                </td>
                                <td><?php echo esc_html( $discount_type ); ?></td>
                            </tr>
                        <?php } ?>

                        <?php if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_rent_information', __( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                    </strong>
                                </td>
                                <td>
                                    <table class="wp-list-table widefat fixed striped table-view-list">
                                        <?php
                                        if ( ! empty( $rent_info ) ) {
                                            foreach ( $rent_info as $key => $value ) {
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                    <td><?php echo wp_kses( $value, rbfw_allowed_html() ); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ( $rent_type == 'resort' ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_room_information', __( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                    </strong>
                                </td>
                                <td>
                                    <table class="wp-list-table widefat fixed striped table-view-list">
                                        <?php
                                        if ( ! empty( $rent_info ) ) {
                                            foreach ( $rent_info as $key => $value ) {
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                    <td><?php echo wp_kses( $value, rbfw_allowed_html() ); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ( ! empty( $service_info ) ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_extra_service_information', __( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                    </strong>
                                </td>
                                <td>
                                    <table class="wp-list-table widefat fixed striped table-view-list">
                                        <?php
                                        if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
                                            if ( ! empty( $service_info ) ) {
                                                foreach ( $service_info as $key => $value ) {
                                                    ?>
                                                    <tr>
                                                        <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                        <td><?php echo wp_kses( $value, rbfw_allowed_html() ); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                        } elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
                                            if ( ! empty( $service_info ) ) {
                                                foreach ( $service_info as $key => $value ) {
                                                    ?>
                                                    <tr>
                                                        <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                        <td><?php echo wp_kses( $value, rbfw_allowed_html() ); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                        } elseif ( $rent_type == 'resort' ) {
                                            if ( ! empty( $service_info ) ) {
                                                foreach ( $service_info as $key => $value ) {
                                                    ?>
                                                    <tr>
                                                        <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                        <td><?php echo wp_kses( $value, rbfw_allowed_html() ); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>


                        <?php if ( ! empty( $rbfw_management_info ) ){ ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo esc_html__('Fee Management Info','booking-and-rental-manager-for-woocommerce') ?>
                                    </strong>
                                </td>
                                <td>
                                    <table class="wp-list-table widefat fixed striped table-view-list">
                                        <?php foreach ($rbfw_management_info as $key => $value){
                                            $service_label = $key; //service name
                                            $service_price = $value['price'];
                                            $service_price_desc = $value['price_desc'];
                                            $refundable = ($value['refundable']=='yes')?'( Refundable )':'( Non refundable )';
                                            ?>
                                            <tr>
                                                <th>
                                                    <?php echo esc_html($service_label); ?> <?php echo esc_html($refundable); ?>
                                                </th>
                                                <td>
                                                    (<?php echo wp_kses($service_price_desc,rbfw_allowed_html()); ?>)  = <?php echo wp_kses(wc_price($service_price),rbfw_allowed_html()); ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>


                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_room_information', __( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                </strong>
                            </td>
                            <td>
                                <?php if ( ! empty( $rent_info ) ) { ?>
                                <table class="wp-list-table widefat fixed striped table-view-list">
                                    <?php
                                    foreach ( $rent_info as $key => $value ) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                            <td><?php echo wp_kses( $value, rbfw_allowed_html() ); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                                <?php } else { echo '&mdash;'; } ?>
                            </td>
                        </tr>

                        <?php if ( ! empty( $rent_type == 'multiple_items' ) ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php esc_html_e('Selected Items','booking-and-rental-manager-for-woocommerce'); ?>
                                    </strong>
                                </td>
                                <td>
                                    <table class="wp-list-table widefat fixed striped table-view-list">
                                        <?php

                                        if ( ! empty( $multiple_items_info ) ){
                                                foreach ($multiple_items_info as $key => $value){
                                                    ?>
                                                    <tr>
                                                        <th>
                                                            <?php echo esc_html($value['item_name']); ?>
                                                        </th>
                                                        <td>(<?php echo wp_kses(wc_price($value['item_price']),rbfw_allowed_html()); ?> x <?php echo esc_html($value['item_qty']); ?> x <?php echo esc_html($duration_qty); ?>) = <?php echo wp_kses(wc_price($value['item_price'] * $value['item_qty']),rbfw_allowed_html()); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }

                                        ?>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>


                        <?php if ( $rent_type === 'multiple_items' && ! empty( $rbfw_category_wise_info ) ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php esc_html_e('Optional Add-ons','booking-and-rental-manager-for-woocommerce'); ?>
                                    </strong>
                                </td>
                                <td>
                                    <table class="wp-list-table widefat fixed striped table-view-list">
                                        <?php

                                        if ( ! empty( $rbfw_category_wise_info ) ){ ?>
                                            <?php foreach ($rbfw_category_wise_info as $key => $value){ ?>
                                                <tr>
                                                    <th><?php echo esc_html($value['cat_title']); ?> </th>
                                                    <td>
                                                        <table>
                                                            <?php foreach ($value as $item){ ?>
                                                                <?php if(isset($item['name'])){ ?>
                                                                    <tr>
                                                                        <td><?php echo esc_html($item['name']); ?></td>
                                                                        <td>
                                                                            <?php
                                                                            if($item['service_price_type']=='day_wise'){
                                                                                echo '('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) . 'x' .esc_html($total_days) .'='.wp_kses(wc_price($item['price']*(int)$item['quantity']*$total_days),rbfw_allowed_html()).')';
                                                                            }else{
                                                                                echo ('('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) .'='.wp_kses(wc_price($item['price']*$item['quantity']),rbfw_allowed_html())).')';
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </table>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ( $has_service_infos ) {
                            $sv_total_days = isset( $total_days ) ? (int) $total_days : 1;
                            ?>
                            <tr class="rbfw_ol_svc_tr">
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_service_information', __( 'Service Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div class="rbfw_ol_svc">
                                        <?php foreach ( $service_infos as $sv_cat_title => $sv_cat_services ) {
                                            if ( empty( $sv_cat_services ) || ! is_array( $sv_cat_services ) ) {
                                                continue; // skip categories with no selected service
                                            }
                                            ?>
                                            <div class="rbfw_ol_svc_cat">
                                                <?php if ( '' !== (string) $sv_cat_title ) { ?>
                                                    <div class="rbfw_ol_svc_cat_title"><?php echo esc_html( $sv_cat_title ); ?></div>
                                                <?php } ?>
                                                <?php foreach ( $sv_cat_services as $sv_item ) {
                                                    if ( empty( $sv_item['name'] ) ) {
                                                        continue;
                                                    }
                                                    $sv_price = isset( $sv_item['price'] ) ? (float) $sv_item['price'] : 0;
                                                    $sv_qty   = isset( $sv_item['quantity'] ) ? (int) $sv_item['quantity'] : 0;
                                                    $sv_type  = isset( $sv_item['service_price_type'] ) ? $sv_item['service_price_type'] : '';
                                                    if ( 'day_wise' === $sv_type ) {
                                                        $sv_calc = wc_price( $sv_price ) . ' &times; ' . $sv_qty . ' &times; ' . $sv_total_days . ' = <strong>' . wc_price( $sv_price * $sv_qty * $sv_total_days ) . '</strong>';
                                                    } else {
                                                        $sv_calc = wc_price( $sv_price ) . ' &times; ' . $sv_qty . ' = <strong>' . wc_price( $sv_price * $sv_qty ) . '</strong>';
                                                    }
                                                    ?>
                                                    <div class="rbfw_ol_svc_row">
                                                        <span class="rbfw_ol_svc_name"><?php echo esc_html( $sv_item['name'] ); ?></span>
                                                        <span class="rbfw_ol_svc_calc"><?php echo wp_kses( $sv_calc, rbfw_allowed_html() ); ?></span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ( ! empty( $rbfw_regf_info ) ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_customer_information', __( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                    </strong>
                                </td>
                                <td>
                                    <table class="wp-list-table widefat fixed striped table-view-list">
                                        <?php
                                        foreach ( $rbfw_regf_info as $info ) {
                                            $label = $info['label'];
                                            $value = $info['value'];
                                            if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
                                                $value = '<a href="' . esc_url( $value ) . '" target="_blank" style="text-decoration:underline">' . esc_html__( 'View File', 'booking-and-rental-manager-for-woocommerce' ) . '</a>';
                                            }
                                            ?>
                                            <tr>
                                                <td><strong><?php echo esc_html( $label ); ?></strong></td>
                                                <td><?php echo esc_html( $value ); ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ( ! empty( $variation_info ) ) {
                            foreach ( $variation_info as $key => $value ) {
                                $vi_qty   = isset( $value['qty'] ) ? (int) $value['qty'] : 0;
                                $vi_price = isset( $value['price'] ) ? (float) $value['price'] : 0;
                                $vi_text  = esc_html( $value['field_value'] ?? '' );
                                if ( $vi_qty > 0 ) {
                                    $vi_text .= ' × ' . esc_html( $vi_qty );
                                }
                                if ( $vi_price > 0 ) {
                                    $vi_text .= ' <span class="rbfw_variation_surcharge">(+' . wp_kses_post( wc_price( $vi_price ) ) . ')</span>';
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $value['field_label'] ?? '' ); ?></strong></td>
                                    <td><?php echo wp_kses_post( $vi_text ); ?></td>
                                </tr>
                            <?php }
                        } ?>


                        <?php if ( ! empty( $discount_amount ) ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php
                                        if($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                            echo esc_html($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings'));
                                        }else{
                                            echo esc_html__('Discount','booking-and-rental-manager-for-woocommerce') . ' :';
                                        }
                                        ?>
                                    </strong>
                                </td>
                                <td><?php echo wp_kses( wc_price( $discount_amount ), rbfw_allowed_html() ); ?></td>
                            </tr>
                        <?php } ?>


                        <?php $total_security_deposit_amount = $total_security_deposit_amount + $security_deposit_amount;
                        if ( ! empty( $security_deposit_amount ) ) { ?>
                            <tr>
                                <td><strong><?php echo esc_html__( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                                <td><?php echo wp_kses( wc_price( $security_deposit_amount ), rbfw_allowed_html() ); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    
                    <!-- Rental Details Table - Right Side -->
                    <table class="wp-list-table widefat fixed striped table-view-list rental-details-table">
                        <thead>
                        <tr>
                            <th colspan="2"><span class="rbfw_ol_sec_ico"><?php echo rbfw_inv_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span><?php rbfw_string( 'rbfw_text_rental_details', __( 'Rental Details', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_start_date_and_time', __( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                </strong>
                            </td>
                            <td><?php echo esc_html( $rbfw_start_datetime ); ?></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_end_date_and_time', __( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                </strong>
                            </td>
                            <td><?php echo esc_html( $rbfw_end_datetime ); ?></td>
                        </tr>
                        <?php if($ticket_info['duration_cost']){ ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_duration_cost', __( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $duration_cost ), rbfw_allowed_html() ); ?></td>
                        </tr>
                        <?php } ?>

                        <?php if ( $ticket_info['service_cost'] ) { ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_resource_cost', __( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $ticket_info['service_cost'] ), rbfw_allowed_html() ); ?></td>
                        </tr>
                        <?php } ?>

                        <?php if ( $rbfw_management_price ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_resource_cost', __( 'Fee Management Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                    </strong>
                                </td>
                                <td><?php echo wp_kses( wc_price( $rbfw_management_price ), rbfw_allowed_html() ); ?></td>
                            </tr>
                        <?php } ?>




                        </tbody>
                    </table>
                <?php } ?>
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                    <tr>
                        <th colspan="2"><span class="rbfw_ol_sec_ico"><?php echo rbfw_inv_icon( 'calculator' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span><?php rbfw_string( 'rbfw_text_total', __( 'Summary', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                    </tr>
                    </thead>
                    <?php
                    // Coupon discount is read natively from the WooCommerce order — this covers the
                    // unified coupon engine (our codes apply as real order coupons) and any other
                    // WooCommerce coupon. Empty on old orders / orders without a coupon.
                    $order_coupon_codes    = method_exists( $wc_order_details, 'get_coupon_codes' ) ? (array) $wc_order_details->get_coupon_codes() : array();
                    $order_coupon_discount = (float) $wc_order_details->get_discount_total();
                    $order_coupon_label    = $order_coupon_codes ? implode( ', ', array_map( 'strtoupper', $order_coupon_codes ) ) : '';
                    ?>
                    <tbody>
                    <tr>
                        <td>
                            <strong>
                                <?php rbfw_string( 'rbfw_text_summary', __( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                            </strong>
                        </td>
                        <td><?php echo wp_kses( wc_price( $wc_order_details->get_total() - $total_security_deposit_amount - $wc_order_details->get_total_tax() + $order_coupon_discount ), rbfw_allowed_html() ); ?></td>
                    </tr>
                    <?php if ( $order_coupon_discount > 0 ) { ?>
                        <tr>
                            <td>
                                <strong><?php
                                    echo esc_html( $rbfw->get_option( 'rbfw_text_coupon', 'rbfw_basic_translation_settings', __( 'Coupon', 'booking-and-rental-manager-for-woocommerce' ) ) );
                                    if ( $order_coupon_label ) { echo ' (' . esc_html( $order_coupon_label ) . ')'; }
                                    echo ':';
                                ?></strong>
                            </td>
                            <td>&minus;<?php echo wp_kses( wc_price( $order_coupon_discount ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ( $wc_order_details->get_total_tax() ) { ?>
                        <tr>
                            <td>
                                <strong><?php rbfw_string( 'rbfw_text_tax', __( 'Tax', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $wc_order_details->get_total_tax() ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ( $total_security_deposit_amount ) { ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html__( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $total_security_deposit_amount ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <strong>
                                <?php rbfw_string( 'rbfw_text_total_cost', __( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>
                            </strong>
                        </td>
                        <td><?php echo wp_kses( wc_price( $wc_order_details->get_total() ), rbfw_allowed_html() ); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    wp_die();
}
function rbfw_order_meta_box_callback() {
    global $post;
    global $rbfw;
    $post_id  = $post->ID;
    $order_id = $post_id;
    get_post_meta( $order_id );
    $status                              = get_post_meta( $order_id, 'rbfw_order_status', true );
    $rbfw_pickup_note                    = get_post_meta( $order_id, 'rbfw_pickup_note', true );
    $rbfw_return_note                    = get_post_meta( $order_id, 'rbfw_return_note', true );
    $rbfw_return_security_deposit_amount = get_post_meta( $order_id, 'rbfw_return_security_deposit_amount', true );
    $billing_name                        = get_post_meta( $order_id, 'rbfw_billing_name', true );
    $billing_email                       = get_post_meta( $order_id, 'rbfw_billing_email', true );
    $payment_method                      = get_post_meta( $order_id, 'rbfw_payment_method', true );
    $payment_id                          = get_post_meta( $order_id, 'rbfw_payment_id', true );
    $order_no             = get_post_meta( $order_id, 'rbfw_order_id', true );
    $ticket_total_price   = get_post_meta( $order_id, 'rbfw_ticket_total_price', true );
    $grand_total          = ! empty( $ticket_total_price ) ? wc_price( $ticket_total_price ) : '';
    $order_tax_raw        = get_post_meta( $order_id, 'rbfw_order_tax', true );
    $rbfw_order_tax       = ! empty( $order_tax_raw ) ? wc_price( $order_tax_raw ) : '';

    wp_nonce_field( 'rbfw_nonce_action', 'nonce' );

    ?>
    <div class="rbfw_order_meta_box_wrap">
        <div class="rbfw_order_meta_box_head">
            <h1><?php echo 'Order #' . esc_html( $order_no ) . ' Details'; ?></h1>
        </div>
        <div class="rbfw_order_meta_box_body">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                <tr>
                    <th colspan="2"><?php rbfw_string( 'rbfw_text_general_information', esc_html__( 'General Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_status', esc_html__( 'Status', 'booking-and-rental-manager-for-woocommerce' ) ); ?></strong></td>
                    <td>
                        <select name="rbfw_order_status">
                            <option value="pending" <?php echo selected( $status, 'pending', false ); ?>><?php esc_html_e( 'Pending payment', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            <option value="processing" <?php echo selected( $status, 'processing', false ); ?>><?php esc_html_e( 'Processing', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            <option value="on-hold" <?php echo selected( $status, 'on-hold', false ); ?>><?php esc_html_e( 'On hold', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            <option value="completed" <?php echo selected( $status, 'completed', false ); ?>><?php esc_html_e( 'Completed', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            <option value="cancelled" <?php echo selected( $status, 'cancelled', false ); ?>><?php esc_html_e( 'Cancelled', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            <option value="refunded" <?php echo selected( $status, 'refunded', false ); ?>><?php esc_html_e( 'Refunded', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            <option value="picked" <?php echo selected( $status, 'picked', false ); ?>><?php esc_html_e( 'Picked', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            <option value="returned" <?php echo selected( $status, 'returned', false ); ?>><?php esc_html_e( 'Returned', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr class="rbfw_pickup_note" style="display: none">
                    <td><strong><?php esc_html_e( 'Pick Up Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                    <td><textarea name="rbfw_pickup_note" placeholder="Pickup Note" cols="50" rows="3"><?php echo esc_textarea( $rbfw_pickup_note ); ?></textarea></td>
                </tr>
                <tr class="rbfw_return_note" style="display: none">
                    <td><strong><?php esc_html_e( 'Return Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                    <td><textarea name="rbfw_return_note" placeholder="Return Note" cols="50" rows="3"><?php echo esc_textarea( $rbfw_return_note ); ?></textarea></td>
                </tr>
                <tr class="rbfw_return_security_deposit_amount" style="display: none">
                    <td><strong><?php esc_html_e( 'Return Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                    <td><input type="number" value="<?php echo esc_attr( $rbfw_return_security_deposit_amount ); ?>" name="rbfw_return_security_deposit_amount" placeholder="Return Security Deposit"></td>
                </tr>
                <?php if ( $rbfw_pickup_note ) { ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Pick Up Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html( $rbfw_pickup_note ); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'Pick Up Date', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html( get_post_meta( $order_id, 'rbfw_pickup_date', true ) ); ?></td>
                    </tr>
                <?php } ?>

                <?php if ( $rbfw_return_note ) { ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Return Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html( $rbfw_return_note ); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'Return Date', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html( get_post_meta( $order_id, 'rbfw_return_date', true ) ); ?></td>
                    </tr>
                <?php } ?>

                <?php if ( $rbfw_return_security_deposit_amount ) { ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Return Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo esc_html( $rbfw_return_security_deposit_amount ); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_order_created_date', esc_html__( 'Order created date', 'booking-and-rental-manager-for-woocommerce' ) );
                            echo ':'; ?></strong></td>
                    <td><?php echo esc_html( get_the_date( 'F j, Y' ) ) . ' ' . esc_html( get_the_time() ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_payment_method', esc_html__( 'Payment method', 'booking-and-rental-manager-for-woocommerce' ) );
                            echo ':'; ?></strong></td>
                    <td><?php echo esc_html( $payment_method ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_payment_id', esc_html__( 'Payment ID', 'booking-and-rental-manager-for-woocommerce' ) );;
                            echo ':'; ?></strong></td>
                    <td><?php echo esc_html( $payment_id ); ?></td>
                </tr>
                </tbody>
            </table>
            <table class="wp-list-table widefat fixed striped table-view-list" style="display: none">
                <thead>
                <tr>
                    <th colspan="2"><?php rbfw_string( 'rbfw_text_billing_information', esc_html__( 'Billing Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_name', esc_html__( 'Name', 'booking-and-rental-manager-for-woocommerce' ) );
                            echo ':'; ?></strong></td>
                    <td><?php echo esc_html( $billing_name ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_email', esc_html__( 'Email', 'booking-and-rental-manager-for-woocommerce' ) );
                            echo ':'; ?></strong></td>
                    <td><?php echo esc_html( $billing_email ); ?></td>
                </tr>
                </tbody>
            </table>
            <?php
            /* Loop Ticket Info */
            $ticket_infos = ! empty( get_post_meta( $order_id, 'rbfw_ticket_info', true ) ) ? get_post_meta( $order_id, 'rbfw_ticket_info', true ) : [];

            $subtotal = 0;
            foreach ( $ticket_infos as $ticket_info ) {
                $item_name = ! empty( $ticket_info['ticket_name'] ) ? $ticket_info['ticket_name'] : '';
                $rbfw_id   = $ticket_info['rbfw_id'];
                $item_id   = $rbfw_id;
                $rent_type = $ticket_info['rbfw_rent_type'];
                $rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-time-text' );
                $rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'] ?? '', 'date-time-text' );
                // Day-wise bookings carry no real time (the end time may be a mere
                // duration artifact), so anchor on the start time and show date only.
                if ( ! rbfw_booking_has_time( isset( $ticket_info['rbfw_start_time'] ) ? $ticket_info['rbfw_start_time'] : '' ) ) {
                    $rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-text' );
                    $rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'] ?? '', 'date-text' );
                }
                $tax        = ! empty( $ticket_info['rbfw_mps_tax'] ) ? $ticket_info['rbfw_mps_tax'] : 0;
                $tax_status = '';
                if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
                    $BikeCarSdClass = new RBFW_BikeCarSd_Function();
                    $rent_info      = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];
                    $service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                    $rent_info      = $BikeCarSdClass->rbfw_get_bikecarsd_rent_info( $item_id, $rent_info );
                    $service_info   = $BikeCarSdClass->rbfw_get_bikecarsd_service_info( $item_id, $service_info );
                    $pickup_point   = ! empty( $ticket_info['rbfw_pickup_point'] ) ? $ticket_info['rbfw_pickup_point'] : '';
                    $dropoff_point  = ! empty( $ticket_info['rbfw_dropoff_point'] ) ? $ticket_info['rbfw_dropoff_point'] : '';
                } elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
                    $BikeCarMdClass = new RBFW_BikeCarMd_Function();
                    $service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                    $service_info   = $BikeCarMdClass->rbfw_get_bikecarmd_service_info( $item_id, $service_info );
                    $service_infos  = ! empty( $ticket_info['rbfw_service_infos'] ) ? $ticket_info['rbfw_service_infos'] : [];
                    $pickup_point   = ! empty( $ticket_info['rbfw_pickup_point'] ) ? $ticket_info['rbfw_pickup_point'] : '';
                    $dropoff_point  = ! empty( $ticket_info['rbfw_dropoff_point'] ) ? $ticket_info['rbfw_dropoff_point'] : '';
                }elseif ( $rent_type == 'multiple_items' ) {
                    $BikeCarMdClass = new RBFW_BikeCarMd_Function();
                    $service_info   = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                    $service_infos  = ! empty( $ticket_info['rbfw_service_infos'] ) ? $ticket_info['rbfw_service_infos'] : [];
                    $pickup_point   = ! empty( $ticket_info['rbfw_pickup_point'] ) ? $ticket_info['rbfw_pickup_point'] : '';
                    $dropoff_point  = ! empty( $ticket_info['rbfw_dropoff_point'] ) ? $ticket_info['rbfw_dropoff_point'] : '';
                } elseif ( $rent_type == 'resort' ) {
                    $ResortClass  = new RBFW_Resort_Function();
                    $package      = $ticket_info['rbfw_resort_package'];
                    $rent_info    = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];

                    $rent_info    = $ResortClass->rbfw_get_resort_room_info( $item_id, $rent_info, $package, $ticket_info['ticket_price'] );
                    $service_info = ! empty( $ticket_info['rbfw_service_info'] ) ? $ticket_info['rbfw_service_info'] : [];
                    $service_info = $ResortClass->rbfw_get_resort_service_info( $item_id, $service_info );
                } else {
                    $rent_info     = '';
                    $service_info  = '';
                    $service_infos = '';
                }
                $total_days              = $ticket_info['total_days'] ?? '';
                $variation_info          = ! empty( $ticket_info['rbfw_variation_info'] ) ? $ticket_info['rbfw_variation_info'] : [];
                $duration_cost           = $ticket_info['duration_cost'];
                $service_cost            = $ticket_info['service_cost'];
                $subtotal                += $ticket_info['ticket_price'];
                $total_cost              = $ticket_info['ticket_price'];
                $discount_amount         = ! empty( $ticket_info['discount_amount'] ) ? (float) $ticket_info['discount_amount'] : 0;
                $security_deposit_amount = ! empty( $ticket_info['security_deposit_amount'] ) ? (float) $ticket_info['security_deposit_amount'] : 0;
                $security_deposit_amount = $security_deposit_amount;
                $discount_type           = ! empty( $ticket_info['discount_type'] ) ? $ticket_info['discount_type'] : '';
                $rbfw_regf_info          = ! empty( $ticket_info['rbfw_regf_info'] ) ? $ticket_info['rbfw_regf_info'] : [];
                /* End  loop*/
                ?>
                <table class="wp-list-table widefat fixed striped table-view-list" style="display: none">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <?php rbfw_string( 'rbfw_text_item_information', esc_html__( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ) ); echo ':'; ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><strong><?php rbfw_string( 'rbfw_text_item_name', esc_html__( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) );
                                echo ':'; ?></strong></td>
                        <td><?php echo esc_html( $item_name ); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string( 'rbfw_text_item_type', esc_html__( 'Item Type', 'booking-and-rental-manager-for-woocommerce' ) );
                                echo ':'; ?></strong></td>
                        <td><?php echo esc_html( rbfw_get_type_label( $rent_type ) ); ?></td>
                    </tr>
                    <?php if ( $rent_type == 'bike_car_md' || $rent_type == 'bike_car_sd' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) { ?>
                        <?php if ( $pickup_point ) { ?>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_pickup_location', esc_html__( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ) );
                                        echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $pickup_point ); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ( $dropoff_point ) { ?>
                            <tr>
                                <td><strong><?php rbfw_string( 'rbfw_text_dropoff_location', esc_html__( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ) );
                                        echo ':'; ?></strong></td>
                                <td><?php echo esc_html( $dropoff_point ); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <?php if ( $rent_type == 'resort' ) { ?>
                        <tr>
                            <td><strong><?php rbfw_string( 'rbfw_text_package', esc_html__( 'Package', 'booking-and-rental-manager-for-woocommerce' ) );
                                    echo ':'; ?></strong></td>
                            <td><?php echo esc_html( $package ); ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ( $discount_type ) { ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php
                                    if($rbfw->get_option_trans('rbfw_text_discount_type', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                        echo esc_html($rbfw->get_option_trans('rbfw_text_discount_type', 'rbfw_basic_translation_settings'));
                                    }else{
                                        echo esc_html__('Discount Type','booking-and-rental-manager-for-woocommerce') . ' :';
                                    }
                                    ?>
                                </strong>
                            </td>
                            <td><?php echo esc_html( $discount_type ); ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) { ?>
                        <tr>
                            <td>
                                <strong><?php rbfw_string( 'rbfw_text_rent_information', esc_html__( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ) );
                                    echo ':'; ?></strong>
                            </td>
                            <td>
                                <table class="wp-list-table widefat fixed striped table-view-list">
                                    <?php
                                    if ( ! empty( $rent_info ) ) {
                                        foreach ( $rent_info as $key => $value ) {
                                            ?>
                                            <tr>
                                                <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                <td><?php echo wp_kses($value,rbfw_allowed_html());?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if ( $rent_type == 'resort' ) { ?>
                        <tr>
                            <td>
                                <strong><?php rbfw_string( 'rbfw_text_room_information', esc_html__( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ) );
                                    echo ':'; ?></strong>
                            </td>
                            <td>
                                <table class="wp-list-table widefat fixed striped table-view-list">
                                    <?php
                                    if ( ! empty( $rent_info ) ) {
                                        foreach ( $rent_info as $key => $value ) {
                                            ?>
                                            <tr>
                                                <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                <td><?php echo esc_html( $value ); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if ( ! empty( $service_info ) ) { ?>
                        <tr>
                            <td>
                                <strong><?php rbfw_string( 'rbfw_text_extra_service_information', esc_html__( 'Extra Service Information jj', 'booking-and-rental-manager-for-woocommerce' ) );
                                    echo ':'; ?></strong>
                            </td>
                            <td>
                                <table class="wp-list-table widefat fixed striped table-view-list">
                                    <?php
                                    if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
                                        if ( ! empty( $service_info ) ) {
                                            foreach ( $service_info as $key => $value ) {
                                                ?>
                                                <tr>
                                                    <td><strong><?php //echo esc_html( $key ); ?></strong></td>
                                                    <td><?php echo wp_kses( $value, rbfw_allowed_html() ); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                    } elseif ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) {
                                        if ( ! empty( $service_info ) ) {
                                            foreach ( $service_info as $key => $value ) {
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                    <td><?php echo esc_html( $value ); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                    } elseif ( $rent_type == 'resort' ) {
                                        if ( ! empty( $service_info ) ) {
                                            foreach ( $service_info as $key => $value ) {
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo esc_html( $key ); ?></strong></td>
                                                    <td><?php echo esc_html( $value ); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>


                    <?php if ( ! empty( $service_infos ) ) { ?>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Service Information:', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </td>
                            <td>
                                <?php foreach ( $service_infos as $key => $value ) {
                                    ?>
                                    <?php if ( count( $value ) ) { ?>
                                        <table>
                                            <tr>
                                                <td><?php echo esc_html( $key ); ?></td>
                                            </tr>
                                            <?php foreach ( $value as $key1 => $item ) { ?>
                                                <tr>
                                                    <td><?php echo esc_html( $item['name'] ); ?></td>
                                                    <td>
                                                        <?php
                                                        if ( $item['service_price_type'] == 'day_wise' ) {
                                                            echo '(' . wp_kses( wc_price( $item['price'] ), rbfw_allowed_html() ) . 'x' . esc_html( $item['quantity'] ) . 'x' . esc_html( $total_days ) . '=' . wp_kses( wc_price( $item['price'] * $item['quantity'] * $total_days ), rbfw_allowed_html() ) . ')';
                                                        } else {
                                                            echo '(' . wp_kses( wc_price( $item['price'] ), rbfw_allowed_html() ) . 'x' . esc_html( $item['quantity'] ) . '=' . wp_kses( wc_price( $item['price'] * $item['quantity'] ), rbfw_allowed_html() ) . ')';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if ( ! empty( $rbfw_regf_info ) ) { ?>
                        <tr>
                            <td>
                                <strong><?php rbfw_string( 'rbfw_text_customer_information', esc_html__( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ) );
                                    echo ':'; ?></strong>
                            </td>
                            <td>
                                <table class="wp-list-table widefat fixed striped table-view-list">
                                    <?php
                                    foreach ( $rbfw_regf_info as $info ) {
                                        $label = $info['label'];
                                        $value = $info['value'];
                                        if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
                                            $value = '<a href="' . esc_url( $value ) . '" target="_blank" style="text-decoration:underline">' . esc_html__( 'View File', 'booking-and-rental-manager-for-woocommerce' ) . '</a>';
                                        }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo esc_html( $label ); ?></strong></td>
                                            <td><?php echo esc_html( $value ); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><strong><?php rbfw_string( 'rbfw_text_start_date_and_time', esc_html__( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ) );
                                echo ':'; ?></strong></td>
                        <td><?php echo esc_html( $rbfw_start_datetime ); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string( 'rbfw_text_end_date_and_time', esc_html__( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ) );
                                echo ':'; ?></strong></td>
                        <td><?php echo esc_html( $rbfw_end_datetime ); ?></td>
                    </tr>
                    <?php if ( ! empty( $variation_info ) ) {
                        foreach ( $variation_info as $key => $value ) {
                            $vi_qty   = isset( $value['qty'] ) ? (int) $value['qty'] : 0;
                            $vi_price = isset( $value['price'] ) ? (float) $value['price'] : 0;
                            $vi_text  = esc_html( $value['field_value'] ?? '' );
                            if ( $vi_qty > 0 ) {
                                $vi_text .= ' × ' . esc_html( $vi_qty );
                            }
                            if ( $vi_price > 0 ) {
                                $vi_text .= ' <span class="rbfw_variation_surcharge">(+' . wp_kses_post( wc_price( $vi_price ) ) . ')</span>';
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html( $value['field_label'] ?? '' ); ?></strong></td>
                                <td><?php echo wp_kses_post( $vi_text ); ?></td>
                            </tr>
                        <?php }
                    } ?>
                    <?php if ( $ticket_info['duration_cost'] ) { ?>
                    <tr>
                        <td>
                            <strong>
                                <?php rbfw_string( 'rbfw_text_duration_cost', esc_html__( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                            </strong>
                        </td>
                        <td><?php echo wp_kses( wc_price( $duration_cost ), rbfw_allowed_html() ); ?></td>
                    </tr>
                    <?php } ?>

                    <?php if ( $service_cost ) { ?>
                        <tr>
                            <td><strong><?php rbfw_string( 'rbfw_text_resource_cost', esc_html__( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) );
                                    echo ':'; ?></strong></td>
                            <td><?php echo wp_kses( wc_price( $service_cost ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>



                    <?php if ( $discount_amount ) { ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php
                                    if($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings') && want_loco_translate()=='no'){
                                        echo esc_html($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings'));
                                    }else{
                                        echo esc_html__('Discount','booking-and-rental-manager-for-woocommerce') . ' :';
                                    }
                                    ?>
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $discount_amount ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>


                    <?php if ( $security_deposit_amount ) { ?>
                        <tr>
                            <td><strong><?php echo esc_html( ! empty( get_post_meta( $rbfw_id, 'rbfw_security_deposit_label', true ) ) ? get_post_meta( $rbfw_id, 'rbfw_security_deposit_label', true ) : 'Security Deposit' ); ?>:</strong></td>
                            <td><?php echo wp_kses( wc_price( $security_deposit_amount ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>

            <?php
            $is_tax_inclusive = get_option( 'woocommerce_prices_include_tax', true );
            if ( $is_tax_inclusive == 'yes' ) {
                $wps_order_tax = ! empty( $order_tax_raw ) ? $order_tax_raw : '';
                $subtotal      = (float) $subtotal - (float) $wps_order_tax;
                $subtotal      = wc_price( $subtotal ) . '(ex. tax)';
            } else {
                $subtotal = wc_price( $subtotal );
            }
            ?>
            <table class="wp-list-table widefat fixed striped table-view-list" style="display: none">
                <thead>
                <tr>
                    <th colspan="2"><?php rbfw_string( 'rbfw_text_total', esc_html__( 'Summary', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_summary', esc_html__( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ) );
                            echo ':'; ?></strong></td>
                    <td><?php echo esc_html( $subtotal ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_tax', esc_html__( 'Tax', 'booking-and-rental-manager-for-woocommerce' ) );
                            echo ':'; ?></strong></td>
                    <td><?php echo esc_html( $rbfw_order_tax ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php rbfw_string( 'rbfw_text_total_cost', esc_html__( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ) );
                            echo ':'; ?></strong></td>
                    <td><?php echo wp_kses( wc_price( $grand_total ), rbfw_allowed_html() ); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        jQuery(document).ready(function () {
            jQuery('#rbfw-order-meta-box .handle-actions').remove();
            jQuery('#rbfw-order-meta-box .postbox-header').hide();
        });
    </script>
    <?php
}
function rbfw_order_meta_box_sidebar_callback() {
    global $post;
    $post_id = $post->ID;
    $notice  = get_post_meta( $post_id, 'rbfw_order_status_revision', true );
    if ( ! empty( $notice ) ) {
        foreach ( $notice as $value ) {
            ?>
            <div class="mps_alert_warning"><?php echo esc_html( $value ); ?></div>
            <?php
        }
    }
}
/* Save Order Meta Data */
add_action( 'save_post', 'save_rbfw_order_meta_box' );
function save_rbfw_order_meta_box( $post_id ) {

    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_nonce_action' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['post_type'] ) && 'rbfw_order' == sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) {
        if ( isset( $_POST['rbfw_pickup_note'] ) ) {
            update_post_meta( $post_id, 'rbfw_pickup_note', sanitize_text_field( wp_unslash( $_POST['rbfw_pickup_note'] ) ) );
            update_post_meta( $post_id, 'rbfw_pickup_date', gmdate( 'Y-m-d H:i' ) );
        }
        if ( isset( $_POST['rbfw_return_note'] ) ) {
            update_post_meta( $post_id, 'rbfw_return_note', sanitize_text_field( wp_unslash( $_POST['rbfw_return_note'] ) ) );
            update_post_meta( $post_id, 'rbfw_return_date', gmdate( 'Y-m-d H:i' ) );
        }
        if ( isset( $_POST['rbfw_return_security_deposit_amount'] ) ) {
            update_post_meta( $post_id, 'rbfw_return_security_deposit_amount', sanitize_text_field( wp_unslash( $_POST['rbfw_return_security_deposit_amount'] ) ) );
        }
        if ( isset( $_POST['rbfw_order_status'] ) ) {
            update_post_meta( $post_id, 'rbfw_order_status', sanitize_text_field( wp_unslash( $_POST['rbfw_order_status'] ) ) );
            $current_user          = wp_get_current_user();
            $username              = $current_user->user_login;
            $modified_date         = current_datetime()->format( 'F j, Y h:i a' );
            $status                = 'Status changed to ' . wp_kses( '<strong>' . sanitize_text_field( wp_unslash( $_POST['rbfw_order_status'] ) ) . '</strong>', rbfw_allowed_html() ) . ' by ' . $username . ' on ' . $modified_date;
            $current_status_update = get_post_meta( $post_id, 'rbfw_order_status_revision', true );
            $current_status        = get_post_meta( $post_id, 'rbfw_order_status', true );
            $current_status_wc     = $current_status;
            if ( $current_status == 'picked' ) {
                $current_status_wc = 'processing';
            }
            if ( $current_status == 'returned' ) {
                $current_status_wc = 'completed';
            }


            $rbfw_link_order_id = get_post_meta( $post_id, 'rbfw_link_order_id', true );
            // Sync the linked WooCommerce order status only when WooCommerce is active.
            // Standalone bookings have no WC order to update.
            if ( rbfw_has_woocommerce() && class_exists( 'WC_Order' ) && $rbfw_link_order_id ) {
                $orderDetail = new WC_Order( $rbfw_link_order_id );
                if ( $orderDetail ) {
                    $orderDetail->update_status( "wc-" . $current_status_wc, $current_status_wc, true );
                }
            }
            update_post_meta( $post_id, 'rbfw_order_status', sanitize_text_field( wp_unslash( $_POST['rbfw_order_status'] ) ) );


            if ( empty( $current_status_update ) ) {
                $all_status_update   = array();
                $all_status_update[] = $status;
            } else {
                $all_status_update   = $current_status_update;
                $all_status_update[] = $status;
            }
            update_post_meta( $post_id, 'rbfw_order_status_revision', $all_status_update );
            rbfw_update_reports_status( $rbfw_link_order_id, $current_status );
            rbfw_update_inventory( $rbfw_link_order_id, $current_status );
        }
    }
}
function rbfw_update_reports_status( $id, $status ) {
    if ( empty( $id ) || empty( $status ) ) {
        return;
    }
    $args = array(
        'post_type'      => 'rbfw_order_meta',
        'posts_per_page' => - 1,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => 'rbfw_link_order_id',
                'value'   => $id,
                'compare' => '='
            ),
            array(
                'key'     => 'rbfw_status_id',
                'value'   => $id,
                'compare' => '='
            ),
        )
    );
    $the_query = new WP_Query( $args );
    if ( ! empty( $the_query ) ) {
        foreach ( $the_query->posts as $result ) {
            $post_id = $result->ID;
            update_post_meta( $post_id, 'rbfw_order_status', $status );
        }
    }
}
