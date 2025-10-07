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
add_action( 'wp_ajax_fetch_order_details', 'fetch_order_details_callback' );
function fetch_order_details_callback() {

 /*   if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) {
        wp_send_json_error( 'Invalid request.' );
        wp_die();
    }*/
    check_ajax_referer( 'rbfw_fetch_order_details_action', 'nonce' );


    global $rbfw;
    if ( isset( $_POST['post_id'] ) ) {
        $rbfw_order_id = intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) );
        $wc_order_id   = get_post_meta( $rbfw_order_id, 'rbfw_order_id', true );
        $wc_order_details = wc_get_order( $wc_order_id );
        ?>
        <div class="rbfw_order_meta_box_wrap">
            <div class="rbfw_order_meta_box_head">
                <h1>
                    <?php esc_html_e( 'Order', 'booking-and-rental-manager-for-woocommerce' ); ?>
                    <?php echo '#' . esc_html( $wc_order_id ); ?>
                    <?php esc_html_e( 'Details', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </h1>
            </div>
            <div class="rbfw_order_meta_box_body">
                <?php
                $total_security_deposit_amount = 0;
                foreach ( $wc_order_details->get_items() as $item_id => $item ) {
                    $ticket_info = $item->get_meta( '_rbfw_ticket_info', true );
                    $ticket_info = $ticket_info[0];

                    $item_name = ! empty( $ticket_info['ticket_name'] ) ? $ticket_info['ticket_name'] : '';
                    $rbfw_id   = $ticket_info['rbfw_id'];
                    $item_id   = $rbfw_id;
                    $rent_type = $ticket_info['rbfw_rent_type'];
                    $rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-time-text' );
                    $rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-time-text' );
                    $rbfw_start_time     = ! empty( $ticket_info['rbfw_start_time'] ) ? $ticket_info['rbfw_start_time'] : '';
                    $rbfw_end_time       = ! empty( $ticket_info['rbfw_end_time'] ) ? $ticket_info['rbfw_end_time'] : '';
                    if ( $rent_type == 'resort' || ( empty( $rbfw_start_time ) && empty( $rbfw_end_time ) ) ) {
                        $rbfw_start_datetime = rbfw_get_datetime( $ticket_info['rbfw_start_datetime'], 'date-text' );
                        $rbfw_end_datetime   = rbfw_get_datetime( $ticket_info['rbfw_end_datetime'], 'date-text' );
                    }
                    $tax = ! empty( $ticket_info['rbfw_mps_tax'] ) ? $ticket_info['rbfw_mps_tax'] : 0;
                    if ( $rent_type == 'bike_car_sd' || $rent_type == 'appointment' ) {
                        $BikeCarSdClass = new RBFW_BikeCarSd_Function();
                        $rent_info      = ! empty( $ticket_info['rbfw_type_info'] ) ? $ticket_info['rbfw_type_info'] : [];
                        if($ticket_info['rbfw_end_time']){
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
                               <?php rbfw_string( 'rbfw_text_item_information', __( 'Item Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) { ?>
                            <tr>
                                <td>
                                    <strong><?php rbfw_string( 'rbfw_text_item_name', __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) ); echo ':'; ?>
                                    </strong>
                                </td>
                                <td><?php echo esc_html( $item_name ) . ' * ' . esc_html( $item_quantity ); ?></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_item_name', __( 'Item Name', 'booking-and-rental-manager-for-woocommerce' ) ); echo ':'; ?>
                                    </strong>
                                </td>
                                <td><?php echo esc_html( $item_name ); ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_item_type', __( 'Item Type', 'booking-and-rental-manager-for-woocommerce' ) ); echo ':'; ?>
                                </strong>
                            </td>
                            <td><?php echo esc_html( rbfw_get_type_label( $rent_type ) ); ?></td>
                        </tr>
                        <?php if ( $rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others' ) { ?>
                            <?php if ( $pickup_point ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_pickup_location', __( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ) );
                                            echo ':'; ?></strong></td>
                                    <td><?php echo esc_html( $pickup_point ); ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ( $dropoff_point ) { ?>
                                <tr>
                                    <td><strong><?php rbfw_string( 'rbfw_text_dropoff_location', __( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ) );
                                            echo ':'; ?></strong></td>
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
                                        <?php rbfw_string( 'rbfw_text_room_information', __( 'Room Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
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
                                                            <?php echo esc_html($value['item_name']); ?>:
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


                        <?php if ( ! empty( $rent_type == 'multiple_items' ) ) { ?>
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

                        <?php if ( ! empty( $rbfw_regf_info ) ) { ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php rbfw_string( 'rbfw_text_customer_information', __( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
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
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $value['field_label'] ); ?></strong></td>
                                    <td><?php echo esc_html( $value['field_value'] ); ?></td>
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
                                <td><strong><?php echo esc_html__( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?>:</strong></td>
                                <td><?php echo wp_kses( wc_price( $security_deposit_amount ), rbfw_allowed_html() ); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    
                    <!-- Rental Details Table - Right Side -->
                    <table class="wp-list-table widefat fixed striped table-view-list rental-details-table">
                        <thead>
                        <tr>
                            <th colspan="2"><?php rbfw_string( 'rbfw_text_rental_details', __( 'Rental Details', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_start_date_and_time', __( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                                </strong>
                            </td>
                            <td><?php echo esc_html( $rbfw_start_datetime ); ?></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_end_date_and_time', __( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                                </strong>
                            </td>
                            <td><?php echo esc_html( $rbfw_end_datetime ); ?></td>
                        </tr>
                        <?php if($ticket_info['duration_cost']){ ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_duration_cost', __( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $duration_cost ), rbfw_allowed_html() ); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ( $ticket_info['service_cost'] ) { ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php rbfw_string( 'rbfw_text_resource_cost', __( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $ticket_info['service_cost'] ), rbfw_allowed_html() ); ?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                    <tr>
                        <th colspan="2"><?php rbfw_string( 'rbfw_text_total', __( 'Summary', 'booking-and-rental-manager-for-woocommerce' ) ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <strong>
                                <?php rbfw_string( 'rbfw_text_summary', __( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                            </strong>
                        </td>
                        <td><?php echo wp_kses( wc_price( $wc_order_details->get_total() - $total_security_deposit_amount - $wc_order_details->get_total_tax() ), rbfw_allowed_html() ); ?></td>
                    </tr>
                    <?php if ( $wc_order_details->get_total_tax() ) { ?>
                        <tr>
                            <td>
                                <strong><?php rbfw_string( 'rbfw_text_tax', __( 'Tax', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $wc_order_details->get_total_tax() ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ( $total_security_deposit_amount ) { ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html__( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?>:
                                </strong>
                            </td>
                            <td><?php echo wp_kses( wc_price( $total_security_deposit_amount ), rbfw_allowed_html() ); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <strong>
                                <?php rbfw_string( 'rbfw_text_total_cost', __( 'Total Cost', 'booking-and-rental-manager-for-woocommerce' ) ); ?>:
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
    $status                              = get_post_meta( $order_id, 'rbfw_order_status', true );
    $rbfw_pickup_note                    = get_post_meta( $order_id, 'rbfw_pickup_note', true );
    $rbfw_return_note                    = get_post_meta( $order_id, 'rbfw_return_note', true );
    $rbfw_return_security_deposit_amount = get_post_meta( $order_id, 'rbfw_return_security_deposit_amount', true );
    $billing_name                        = get_post_meta( $order_id, 'rbfw_billing_name', true );
    $billing_email                       = get_post_meta( $order_id, 'rbfw_billing_email', true );
    $payment_method                      = get_post_meta( $order_id, 'rbfw_payment_method', true );
    $payment_id                          = get_post_meta( $order_id, 'rbfw_payment_id', true );
    $order_no       = get_post_meta( $order_id, 'rbfw_order_id', true );
    $grand_total    = ! empty( get_post_meta( $order_id, 'rbfw_ticket_total_price', true ) ) ? wc_price( get_post_meta( $order_id, 'rbfw_ticket_total_price', true ) ) : '';
    $rbfw_order_tax = ! empty( get_post_meta( $order_id, 'rbfw_order_tax', true ) ) ? wc_price( get_post_meta( $order_id, 'rbfw_order_tax', true ) ) : '';

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

                    $rent_info    = $ResortClass->rbfw_get_resort_room_info( $item_id, $rent_info, $package );
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
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html( $value['field_label'] ); ?></strong></td>
                                <td><?php echo esc_html( $value['field_value'] ); ?></td>
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
                $wps_order_tax = ! empty( get_post_meta( $order_id, 'rbfw_order_tax', true ) ) ? get_post_meta( $order_id, 'rbfw_order_tax', true ) : '';
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
            $orderDetail        = new WC_Order( $rbfw_link_order_id );
            $orderDetail->update_status( "wc-" . $current_status_wc, $current_status_wc, true );
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
