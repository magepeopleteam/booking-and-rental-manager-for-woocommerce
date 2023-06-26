<?php
/*
* Author 	:	MagePeople Team
* Copyright	: 	mage-people.com
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes_rbfw_order', 'rbfw_order_meta_box' );

function rbfw_order_meta_box() {
    add_meta_box(
        'rbfw-order-meta-box',
        __( 'Order Details', 'booking-and-rental-manager-for-woocommerce' ),
        'rbfw_order_meta_box_callback'
    );
    add_meta_box(
        'rbfw-order-meta-box-sidebar',
        __( 'Order Status Update', 'booking-and-rental-manager-for-woocommerce' ),
        'rbfw_order_meta_box_sidebar_callback',
        '',
        'side',
        'core'
        );
}

function rbfw_order_meta_box_callback(){
    global $post;
    global $rbfw;
    $post_id = $post->ID;
    $order_id = $post_id;

    $status = get_post_meta($order_id, 'rbfw_order_status', true);
    $billing_name = get_post_meta($order_id, 'rbfw_billing_name', true);
    $billing_email = get_post_meta($order_id, 'rbfw_billing_email', true);
    $payment_method = get_post_meta($order_id, 'rbfw_payment_method', true);
    $payment_id = get_post_meta($order_id, 'rbfw_payment_id', true);
    
    $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

    if($rbfw_payment_system == 'wps'){
        $order_no = get_post_meta($order_id, 'rbfw_order_id', true);
    }else{
        $order_no = $post_id;
    }
    $mps_tax_switch = $rbfw->get_option('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
    $mps_tax_format = $rbfw->get_option('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');
    ?>
    <div class="rbfw_order_meta_box_wrap">
        <div class="rbfw_order_meta_box_head">
            <h1><?php esc_html_e( 'Order #' .$order_no. ' Details', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
        </div>
        <div class="rbfw_order_meta_box_body">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th colspan="2"><?php rbfw_string('rbfw_text_general_information',__('General Information','booking-and-rental-manager-for-woocommerce')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_status',__('Status','booking-and-rental-manager-for-woocommerce')); ?></strong></td>
                        <td>
                            <select name="rbfw_order_status">
                                <option value="pending" <?php if($status == 'pending'){ echo 'selected'; } ?>><?php esc_html_e( 'Pending payment', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="processing" <?php if($status == 'processing'){ echo 'selected'; } ?>><?php esc_html_e( 'Processing', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="on-hold" <?php if($status == 'on-hold'){ echo 'selected'; } ?>><?php esc_html_e( 'On hold', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="completed" <?php if($status == 'completed'){ echo 'selected'; } ?>><?php esc_html_e( 'Completed', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="cancelled" <?php if($status == 'cancelled'){ echo 'selected'; } ?>><?php esc_html_e( 'Cancelled', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="refunded" <?php if($status == 'refunded'){ echo 'selected'; } ?>><?php esc_html_e( 'Refunded', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_order_created_date',__('Order created date','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html(get_the_date( 'F j, Y' )).' '.esc_html(get_the_time()); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_payment_method',__('Payment method','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($payment_method); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_payment_id',__('Payment ID','booking-and-rental-manager-for-woocommerce')); ; echo ':';  ?></strong></td>
                        <td><?php echo esc_html($payment_id); ?></td>
                    </tr>                     
                </tbody>
            </table>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th colspan="2"><?php rbfw_string('rbfw_text_billing_information',__('Billing Information','booking-and-rental-manager-for-woocommerce')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_name',__('Name','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($billing_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_email',__('Email','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($billing_email); ?></td>
                    </tr>
                </tbody>
            </table>

            <?php 
                /* Loop Ticket Info */
                $ticket_infos = !empty(get_post_meta($order_id,'rbfw_ticket_info',true)) ? get_post_meta($order_id,'rbfw_ticket_info',true) : [];
                 foreach ($ticket_infos as $ticket_info) {
                
            
                $item_name = !empty($ticket_info['ticket_name']) ? $ticket_info['ticket_name'] : '';
                $rbfw_id = $ticket_info['rbfw_id'];
                $item_id = $rbfw_id;
                $rent_type = $ticket_info['rbfw_rent_type'];

                $rbfw_start_datetime = rbfw_get_datetime($ticket_info['rbfw_start_datetime'], 'date-time-text');
                $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-time-text');
                $rbfw_start_time = !empty($ticket_info['rbfw_start_time']) ? $ticket_info['rbfw_start_time'] : '';
                $rbfw_end_time =  !empty($ticket_info['rbfw_end_time']) ? $ticket_info['rbfw_end_time'] : '';

                if($rent_type == 'resort' || (empty($rbfw_start_time) && empty($rbfw_end_time))){

                    $rbfw_start_datetime = rbfw_get_datetime($ticket_info['rbfw_start_datetime'], 'date-text');
                    $rbfw_end_datetime = rbfw_get_datetime($ticket_info['rbfw_end_datetime'], 'date-text');
                }

                $tax = !empty($ticket_info['rbfw_mps_tax']) ? $ticket_info['rbfw_mps_tax'] : 0;
                $mps_tax_percentage = !empty(get_post_meta($rbfw_id, 'rbfw_mps_tax_percentage', true)) ? strip_tags(get_post_meta($rbfw_id, 'rbfw_mps_tax_percentage', true)) : '';
                $tax_status = '';

                if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && $mps_tax_format == 'including_tax'){
                    $tax_status = '('.rbfw_string_return('rbfw_text_includes',__('Includes','booking-and-rental-manager-for-woocommerce')).' '.rbfw_mps_price($tax).' '.rbfw_string_return('rbfw_text_tax',__('Tax','booking-and-rental-manager-for-woocommerce')).')';
                }

                if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){
                    $BikeCarSdClass = new RBFW_BikeCarSd_Function();
                    $rent_info = !empty($ticket_info['rbfw_type_info']) ? $ticket_info['rbfw_type_info'] : [];
                    $service_info = !empty($ticket_info['rbfw_service_info']) ? $ticket_info['rbfw_service_info'] : [];
                    $rent_info = $BikeCarSdClass->rbfw_get_bikecarsd_rent_info($item_id, $rent_info);
                    $service_info = $BikeCarSdClass->rbfw_get_bikecarsd_service_info($item_id, $service_info);

                }elseif($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){
                    $BikeCarMdClass = new RBFW_BikeCarMd_Function();
                    $service_info = !empty($ticket_info['rbfw_service_info']) ? $ticket_info['rbfw_service_info'] : [];
                    $service_info = $BikeCarMdClass->rbfw_get_bikecarmd_service_info($item_id, $service_info);
                    $item_quantity = !empty($ticket_info['rbfw_item_quantity']) ? $ticket_info['rbfw_item_quantity'] : 1;
                    $pickup_point = !empty($ticket_info['rbfw_pickup_point']) ? $ticket_info['rbfw_pickup_point'] : '';
                    $dropoff_point = !empty($ticket_info['rbfw_dropoff_point']) ? $ticket_info['rbfw_dropoff_point'] : '';

                }elseif($rent_type == 'resort'){
                    $ResortClass = new RBFW_Resort_Function();
                    $package = $ticket_info['rbfw_resort_package'];
                    $rent_info = !empty($ticket_info['rbfw_type_info']) ? $ticket_info['rbfw_type_info'] : [];
                    $rent_info  = $ResortClass->rbfw_get_resort_room_info($item_id, $rent_info, $package);
                    $service_info = !empty($ticket_info['rbfw_service_info']) ? $ticket_info['rbfw_service_info'] : [];
                    $service_info = $ResortClass->rbfw_get_resort_service_info($item_id, $service_info);

                }else{
                    $rent_info = '';
                    $service_info = '';
                }

                $variation_info = !empty($ticket_info['rbfw_variation_info']) ? $ticket_info['rbfw_variation_info'] : [];

                $duration_cost = rbfw_mps_price($ticket_info['duration_cost']);
                $service_cost = rbfw_mps_price($ticket_info['service_cost']);
                $total_cost = rbfw_mps_price($ticket_info['ticket_price']);
                $discount_amount = !empty($ticket_info['discount_amount']) ? (float)$ticket_info['discount_amount'] : 0;
                $discount_amount = rbfw_mps_price($discount_amount);
                $discount_type = !empty($ticket_info['discount_type']) ? $ticket_info['discount_type'] : '';
                $rbfw_regf_info = !empty($ticket_info['rbfw_regf_info']) ? $ticket_info['rbfw_regf_info'] : [];

                /* End  loop*/
            ?>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th colspan="2"><?php rbfw_string('rbfw_text_item_information',__('Item Information','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_item_name',__('Item Name','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($item_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_item_type',__('Item Type','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo rbfw_get_type_label($rent_type); ?></td>
                    </tr>
                    <?php if($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){ ?>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_pickup_location',__('Pickup Location','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($pickup_point); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_dropoff_location',__('Drop-off Location','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($dropoff_point); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if($rent_type == 'resort'){ ?>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_package',__('Package','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($package); ?></td>
                    </tr>
                    <?php } ?>

                    <?php if(!empty($discount_type)){ ?>
                    <tr>
                        <td><strong><?php echo $rbfw->get_option('rbfw_text_discount_type', 'rbfw_basic_translation_settings', __('Discount Type','booking-and-rental-manager-for-woocommerce')); ?>:</strong></td>
                        <td><?php echo $discount_type; ?></td>
                    </tr>
                    <?php } ?>

                    <?php if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){ ?>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_rent_information',__('Rent Information','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td>
                            <table class="wp-list-table widefat fixed striped table-view-list">                     
                            <?php 
                                if(!empty($rent_info)){
                                    foreach ($rent_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value;?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if($rent_type == 'resort'){ ?>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_room_information',__('Room Information','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td>
                            <table class="wp-list-table widefat fixed striped table-view-list">                     
                            <?php 
                                if(!empty($rent_info)){
                                    foreach ($rent_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_extra_service_information',__('Extra Service Information','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td>
                            <table class="wp-list-table widefat fixed striped table-view-list">                     
                            <?php 
                            if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){
                                if(!empty($service_info)){
                                    foreach ($service_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $key; ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            elseif($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){
                                if(!empty($service_info)){
                                    foreach ($service_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            elseif($rent_type == 'resort'){
                                if(!empty($service_info)){
                                    foreach ($service_info as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><strong><?php esc_html_e($key); ?></strong></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <?php if(!empty($rbfw_regf_info)){ ?>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_customer_information',__('Customer Information','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td>
                            <table class="wp-list-table widefat fixed striped table-view-list">
                            <?php
                            foreach ($rbfw_regf_info as $info) {

                                $label = $info['label'];
                                $value = $info['value'];

                                if(filter_var($value, FILTER_VALIDATE_URL)){

                                    $value = '<a href="'.esc_url($value).'" target="_blank" style="text-decoration:underline">'.esc_html__('View File','booking-and-rental-manager-for-woocommerce').'</a>';
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo $label; ?></strong></td>
                                    <td><?php echo $value; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_start_date_and_time',__('Start Date and Time','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($rbfw_start_datetime); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_end_date_and_time',__('End Date and Time','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo esc_html($rbfw_end_datetime); ?></td>
                    </tr>

                    <?php if(!empty($variation_info)){ 
                    foreach ($variation_info as $key => $value) { 
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($value['field_label']); ?></strong></td>
                        <td><?php echo esc_html($value['field_value']); ?></td>
                    </tr>
                    <?php } } ?>

                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_duration_cost',__('Duration Cost','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo $duration_cost; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_resource_cost',__('Resource Cost','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo $service_cost; ?></td>
                    </tr>
                    <?php if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($tax)){ ?>
                    <tr>
                        <td><strong><?php echo $rbfw->get_option('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')); ?></strong></td>
                        <td><?php echo rbfw_mps_price($tax); ?></td>
                    </tr>
                    <?php } ?>

                    <?php if(!empty($discount_amount)){ ?>
                    <tr>
                        <td><strong><?php echo $rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')); ?>:</strong></td>
                        <td><?php echo $discount_amount; ?></td>
                    </tr>
                    <?php } ?>

                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_total_cost',__('Total Cost','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo $total_cost.' '.$tax_status; ?></td>
                    </tr>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </div>
    <script>
        jQuery(document).ready(function(){
            jQuery('#rbfw-order-meta-box .handle-actions').remove();
            jQuery('#rbfw-order-meta-box .postbox-header').hide();
        });        
    </script>
    
    <?php
}

function rbfw_order_meta_box_sidebar_callback(){
    global $post;
    $post_id = $post->ID;
    $notice = get_post_meta( $post_id, 'rbfw_order_status_revision', true );
    if(!empty($notice)){
        foreach ($notice as $value) {
            ?>
            <div class="mps_alert_warning"><?php echo $value; ?></div>
            <?php
        }
    }
}

/* Save Order Meta Data */
add_action( 'save_post', 'save_rbfw_order_meta_box' );

function save_rbfw_order_meta_box( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'rbfw_order' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }
    
    if ( isset( $_POST['rbfw_order_status'] ) ) {
        update_post_meta( $post_id, 'rbfw_order_status', $_POST['rbfw_order_status'] );

        $current_user = wp_get_current_user();
        $username = $current_user->user_login;
        $modified_date =  current_datetime()->format('F j, Y h:i a');

        $status = 'Status changed to <strong>'.$_POST['rbfw_order_status'].'</strong> by '.$username.' on '.$modified_date;
        $current_status_update = get_post_meta( $post_id, 'rbfw_order_status_revision', true );


        $current_status = get_post_meta( $post_id, 'rbfw_order_status', true );
        
        global $rbfw;
        $rbfw_payment_system = $rbfw->get_option('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

        if($rbfw_payment_system == 'wps'){
            $rbfw_link_order_id = get_post_meta( $post_id, 'rbfw_link_order_id', true );
        }else {
            $rbfw_link_order_id = get_post_meta( $post_id, 'rbfw_status_id', true );
        }

        if(empty($current_status_update)){
            $all_status_update = array();  
            $all_status_update[] = $status;
        }else{
            $all_status_update = $current_status_update;
            $all_status_update[] = $status;
        }

        update_post_meta( $post_id, 'rbfw_order_status_revision', $all_status_update );
        rbfw_update_reports_status($rbfw_link_order_id, $current_status);
        rbfw_update_inventory($rbfw_link_order_id, $current_status);
    }

}

function rbfw_update_reports_status($id,$status){

    if(empty($id) || empty($status)){
        return;
    }

    $args = array(
        'post_type' => 'rbfw_order_meta',
        'posts_per_page' => -1,
        'meta_query' => array(
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


    $the_query = new WP_Query($args);

    if(!empty($the_query)){
		foreach ($the_query->posts as $result) {
			$post_id = $result->ID;
            update_post_meta($post_id,'rbfw_order_status',$status);
		}
	}
}