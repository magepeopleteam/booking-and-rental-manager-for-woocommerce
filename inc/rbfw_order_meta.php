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
    add_meta_box('rbfw-order-meta-box', __( 'Order Details', 'booking-and-rental-manager-for-woocommerce' ), 'rbfw_order_meta_box_callback');
    add_meta_box('rbfw-order-meta-box-sidebar', __( 'Order Status Update', 'booking-and-rental-manager-for-woocommerce' ), 'rbfw_order_meta_box_sidebar_callback', '', 'side', 'core');
}


add_action('wp_ajax_fetch_order_details', 'fetch_order_details_callback');

function fetch_order_details_callback() {
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        $billing_name = get_post_meta($post_id, 'rbfw_billing_name', true);
        $billing_email = get_post_meta($post_id, 'rbfw_billing_email', true);
        $payment_method = get_post_meta($post_id, 'rbfw_payment_method', true);
        $payment_id = get_post_meta($post_id, 'rbfw_payment_id', true);
        $rbfw_pickup_note = get_post_meta($post_id, 'rbfw_pickup_note', true);
        $rbfw_return_note = get_post_meta($post_id, 'rbfw_return_note', true);
        $rbfw_return_security_deposit_amount = get_post_meta($post_id, 'rbfw_return_security_deposit_amount', true);
        $ticket_infos = maybe_unserialize(get_post_meta($post_id, 'rbfw_ticket_info', true));
        ob_start();
        ?>
        <div class="rbfw_order_meta_box_wrap">
            <div class="rbfw_order_meta_box_body">
                <h2><?php esc_html_e('Order Details', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                <div class="export-print-buttons">
                    <button id="export-to-excel" class="button button-primary"><?php esc_html_e('Export to Excel', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                    <button id="print-order-details" class="button button-secondary"><?php esc_html_e('Print Order Details', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                </div>

                <table class="wp-list-table widefat fixed striped table-view-list">
                    <tbody>
                        <?php if ($billing_name) : ?>
                            <tr>
                                <td><strong><?php esc_html_e('Billing Name:', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                <td><?php echo esc_html($billing_name); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($billing_email) : ?>
                            <tr>
                                <td><strong><?php esc_html_e('Billing Email:', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                <td><?php echo esc_html($billing_email); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($payment_method) : ?>
                            <tr>
                                <td><strong><?php esc_html_e('Payment Method:', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                <td><?php echo esc_html($payment_method); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($payment_id) : ?>
                            <tr>
                                <td><strong><?php esc_html_e('Payment ID:', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                <td><?php echo esc_html($payment_id); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($rbfw_pickup_note) : ?>
                            <tr>
                                <td><strong><?php esc_html_e('Pickup Note:', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                <td><?php echo esc_html($rbfw_pickup_note); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($rbfw_return_note) : ?>
                            <tr>
                                <td><strong><?php esc_html_e('Return Note:', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                <td><?php echo esc_html($rbfw_return_note); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($rbfw_return_security_deposit_amount) : ?>
                            <tr>
                                <td><strong><?php esc_html_e('Return Security Deposit:', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                <td><?php echo esc_html($rbfw_return_security_deposit_amount); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if (!empty($ticket_infos) && is_array($ticket_infos)) : ?>
                    <h2><?php esc_html_e('Service Information', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                    <?php
                    $unique_services = [];
                    foreach ($ticket_infos as $ticket_info) {
                        if (!empty($ticket_info['rbfw_service_info']) && is_array($ticket_info['rbfw_service_info'])) {
                            foreach ($ticket_info['rbfw_service_info'] as $service_key => $service_value) {
                                if (!isset($unique_services[$service_key])) {
                                    $unique_services[$service_key] = $service_value;
                                }
                            }
                        }
                    }
                    if (!empty($unique_services)) {
                        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
                        echo '<thead><tr><th>' . esc_html__('Service Name', 'booking-and-rental-manager-for-woocommerce') . '</th><th>' . esc_html__('Quantity', 'booking-and-rental-manager-for-woocommerce') . '</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($unique_services as $service_key => $service_value) {
                            echo '<tr>';
                            echo '<td>' . esc_html($service_key) . '</td>';
                            echo '<td>' . esc_html($service_value) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    }
                    ?>

                    <h2><?php esc_html_e('Ticket Information', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                    <table class="wp-list-table widefat fixed striped table-view-list">
                        <thead>
                            <tr>
                                <?php
                                $show_columns = [ 'item_name' => false, 'pickup_location' => false, 'dropoff_location' => false, 'package' => false, 'discount_type' => false, 'rent_info' => false, 'duration_cost' => false, 'resource_cost' => false, 'tax' => false, 'discount' => false, 'security_deposit' => false, 'total_cost' => false, 'rent_type' => false, ];
                                foreach ($ticket_infos as $ticket_info) {
                                    $show_columns['item_name'] = $show_columns['item_name'] || !empty($ticket_info['ticket_name']);
                                    $show_columns['pickup_location'] = $show_columns['pickup_location'] || !empty($ticket_info['rbfw_pickup_point']);
                                    $show_columns['dropoff_location'] = $show_columns['dropoff_location'] || !empty($ticket_info['rbfw_dropoff_point']);
                                    $show_columns['package'] = $show_columns['package'] || !empty($ticket_info['rbfw_resort_package']);
                                    $show_columns['discount_type'] = $show_columns['discount_type'] || !empty($ticket_info['discount_type']);
                                    $show_columns['rent_info'] = $show_columns['rent_info'] || !empty($ticket_info['rbfw_type_info']);
                                    $show_columns['duration_cost'] = $show_columns['duration_cost'] || isset($ticket_info['duration_cost']);
                                    $show_columns['resource_cost'] = $show_columns['resource_cost'] || isset($ticket_info['service_cost']) && $ticket_info['service_cost'] > 0; // Check for resource cost
                                    $show_columns['tax'] = $show_columns['tax'] || isset($ticket_info['rbfw_mps_tax']) && $ticket_info['rbfw_mps_tax'] > 0; // Check for tax
                                    $show_columns['discount'] = $show_columns['discount'] || isset($ticket_info['discount_amount']) && $ticket_info['discount_amount'] > 0; // Check for discount
                                    $show_columns['security_deposit'] = $show_columns['security_deposit'] || isset($ticket_info['security_deposit_amount']) && $ticket_info['security_deposit_amount'] > 0; // Check for security deposit
                                    $show_columns['total_cost'] = $show_columns['total_cost'] || isset($ticket_info['ticket_price']); // Total cost is always shown
                                    $show_columns['rent_type'] = $show_columns['rent_type'] || !empty($ticket_info['rbfw_rent_type']); // Check for rent type
                                }
                                foreach ($show_columns as $column_key => $show) {
                                    if ($show) {
                                        echo '<th>' . esc_html__(ucwords(str_replace('_', ' ', $column_key)), 'booking-and-rental-manager-for-woocommerce') . '</th>';
                                    }
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $unique_tickets = [];
                            foreach ($ticket_infos as $ticket_info) {
                                $ticket_identifier = $ticket_info['ticket_name'] . '|' . 
                                                     $ticket_info['rbfw_pickup_point'] . '|' . 
                                                     $ticket_info['rbfw_dropoff_point'] . '|' . 
                                                     $ticket_info['rbfw_resort_package'] . '|' . 
                                                     $ticket_info['discount_type'] . '|' . 
                                                     $ticket_info['duration_cost'] . '|' . 
                                                     $ticket_info['service_cost'] . '|' . 
                                                     $ticket_info['rbfw_mps_tax'] . '|' . 
                                                     $ticket_info['discount_amount'] . '|' . 
                                                     $ticket_info['security_deposit_amount'] . '|' . 
                                                     $ticket_info['ticket_price'] . '|' . 
                                                     $ticket_info['rbfw_rent_type'];
                                if (!isset($unique_tickets[$ticket_identifier])) {
                                    $unique_tickets[$ticket_identifier] = $ticket_info; 
                                }
                            }
                            foreach ($unique_tickets as $unique_ticket) {
                                echo '<tr>';
                                if ($show_columns['item_name']) echo '<td>' . esc_html($unique_ticket['ticket_name'] ?? 'N/A') . '</td>';
                                if ($show_columns['pickup_location']) echo '<td>' . esc_html($unique_ticket['rbfw_pickup_point'] ?? 'N/A') . '</td>';
                                if ($show_columns['dropoff_location']) echo '<td>' . esc_html($unique_ticket['rbfw_dropoff_point'] ?? 'N/A') . '</td>';
                                if ($show_columns['package']) echo '<td>' . esc_html($unique_ticket['rbfw_resort_package'] ?? 'N/A') . '</td>';
                                if ($show_columns['discount_type']) echo '<td>' . esc_html($unique_ticket['discount_type'] ?? 'N/A') . '</td>';
                                if ($show_columns['rent_info']) {
                                    $rent_info = $unique_ticket['rbfw_type_info'] ?? 'N/A';
                                    if (is_array($rent_info)) {
                                        $rent_info_string = '';
                                        foreach ($rent_info as $type => $quantity) {
                                            $rent_info_string .= esc_html($type) . ': ' . esc_html($quantity) . '<br>';
                                        }
                                        echo '<td>' . $rent_info_string . '</td>';
                                    } else {
                                        echo '<td>' . esc_html($rent_info) . '</td>';
                                    }
                                }
                                if ($show_columns['duration_cost']) echo '<td>' . wc_price($unique_ticket['duration_cost'] ?? 0) . '</td>';
                                if ($show_columns['resource_cost']) echo '<td>' . wc_price($unique_ticket['service_cost'] ?? 0) . '</td>';
                                if ($show_columns['tax']) echo '<td>' . wc_price($unique_ticket['rbfw_mps_tax'] ?? 0) . '</td>';
                                if ($show_columns['discount']) echo '<td>' . wc_price($unique_ticket['discount_amount'] ?? 0) . '</td>';
                                if ($show_columns['security_deposit']) echo '<td>' . wc_price($unique_ticket['security_deposit_amount'] ?? 0) . '</td>';
                                if ($show_columns['total_cost']) echo '<td>' . wc_price($unique_ticket['ticket_price'] ?? 0) . '</td>';
                                if ($show_columns['rent_type']) echo '<td>' . esc_html($unique_ticket['rbfw_rent_type'] ?? 'N/A') . '</td>'; 
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $output = ob_get_clean(); 

        echo $output;
    }
    wp_die(); 
}

function rbfw_order_meta_box_callback(){
    global $post;
    global $rbfw;
    $post_id = $post->ID;
    $order_id = $post_id;

    $status = get_post_meta($order_id, 'rbfw_order_status', true);
    $rbfw_pickup_note = get_post_meta($order_id, 'rbfw_pickup_note', true);
    $rbfw_return_note = get_post_meta($order_id, 'rbfw_return_note', true);
    $rbfw_return_security_deposit_amount = get_post_meta($order_id, 'rbfw_return_security_deposit_amount', true);
    $billing_name = get_post_meta($order_id, 'rbfw_billing_name', true);
    $billing_email = get_post_meta($order_id, 'rbfw_billing_email', true);
    $payment_method = get_post_meta($order_id, 'rbfw_payment_method', true);
    $payment_id = get_post_meta($order_id, 'rbfw_payment_id', true);
    
    $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

    if($rbfw_payment_system == 'wps'){
        $order_no = get_post_meta($order_id, 'rbfw_order_id', true);
    }else{
        $order_no = $post_id;
    }
    $mps_tax_switch = $rbfw->get_option_trans('rbfw_mps_tax_switch', 'rbfw_basic_payment_settings', 'off');
    $mps_tax_format = $rbfw->get_option_trans('rbfw_mps_tax_format', 'rbfw_basic_payment_settings', 'excluding_tax');

    $grand_total = !empty(get_post_meta($order_id,'rbfw_ticket_total_price',true)) ? rbfw_mps_price(get_post_meta($order_id,'rbfw_ticket_total_price',true)) : '';
    $rbfw_order_tax = !empty(get_post_meta($order_id,'rbfw_order_tax',true)) ? rbfw_mps_price(get_post_meta($order_id,'rbfw_order_tax',true)) : '';
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
                                <option value="picked" <?php if($status == 'picked'){ echo 'selected'; } ?>><?php esc_html_e( 'Picked', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                                <option value="returned" <?php if($status == 'returned'){ echo 'selected'; } ?>><?php esc_html_e( 'Returned', 'booking-and-rental-manager-for-woocommerce' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr class="rbfw_pickup_note" style="display: none">
                        <td><strong><?php esc_html_e( 'Pick Up Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><textarea name="rbfw_pickup_note" placeholder="Pickup Note" cols="50" rows="3"><?php echo $rbfw_pickup_note ?></textarea></td>
                    </tr>
                    <tr class="rbfw_return_note" style="display: none">
                        <td><strong><?php esc_html_e( 'Return Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><textarea name="rbfw_return_note" placeholder="Return Note" cols="50" rows="3"><?php echo $rbfw_return_note ?></textarea></td>
                    </tr>
                    <tr class="rbfw_return_security_deposit_amount" style="display: none">
                        <td><strong><?php esc_html_e( 'Return Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><input type="number" value="<?php echo $rbfw_return_security_deposit_amount ?>" name="rbfw_return_security_deposit_amount" placeholder="Return Security Deposit"></td>
                    </tr>

                    <?php if($rbfw_pickup_note){ ?>
                        <tr>
                            <td><strong><?php esc_html_e( 'Pick Up Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo $rbfw_pickup_note ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Pick Up Date', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo get_post_meta($order_id, 'rbfw_pickup_date', true) ?></td>
                        </tr>
                    <?php } if($rbfw_return_note){ ?>
                        <tr>
                            <td><strong><?php esc_html_e( 'Return Note', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo $rbfw_return_note ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Return Date', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                            <td><?php echo get_post_meta($order_id, 'rbfw_return_date', true) ?></td>
                        </tr>
                    <?php } if($rbfw_return_security_deposit_amount){ ?>
                    <tr>
                        <td><strong><?php esc_html_e( 'Return Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
                        <td><?php echo $rbfw_return_security_deposit_amount ?></td>
                    </tr>
                    <?php } ?>

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


                $subtotal = 0;

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
                        $pickup_point = !empty($ticket_info['rbfw_pickup_point']) ? $ticket_info['rbfw_pickup_point'] : '';
                        $dropoff_point = !empty($ticket_info['rbfw_dropoff_point']) ? $ticket_info['rbfw_dropoff_point'] : '';

                    }elseif($rent_type == 'bike_car_md' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){
                        $BikeCarMdClass = new RBFW_BikeCarMd_Function();

                        $service_info = !empty($ticket_info['rbfw_service_info']) ? $ticket_info['rbfw_service_info'] : [];
                        $service_info = $BikeCarMdClass->rbfw_get_bikecarmd_service_info($item_id, $service_info);

                        $service_infos = !empty($ticket_info['rbfw_service_infos']) ? $ticket_info['rbfw_service_infos'] : [];


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
                        $service_infos = '';
                    }

                    $variation_info = !empty($ticket_info['rbfw_variation_info']) ? $ticket_info['rbfw_variation_info'] : [];
                    $duration_cost = $ticket_info['duration_cost'];
                    $service_cost = $ticket_info['service_cost'];
                    $subtotal += $ticket_info['ticket_price'];
                    $total_cost = $ticket_info['ticket_price'];
                    $discount_amount = !empty($ticket_info['discount_amount']) ? (float)$ticket_info['discount_amount'] : 0;

                    $security_deposit_amount = !empty($ticket_info['security_deposit_amount']) ? (float)$ticket_info['security_deposit_amount'] : 0;
                    $security_deposit_amount = $security_deposit_amount;

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
                            <?php if($rent_type == 'bike_car_md' || $rent_type == 'bike_car_sd' || $rent_type == 'dress' || $rent_type == 'equipment' || $rent_type == 'others'){ ?>
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

                            <?php if($discount_type){ ?>
                                <tr>
                                    <td><strong><?php echo $rbfw->get_option_trans('rbfw_text_discount_type', 'rbfw_basic_translation_settings', __('Discount Type','booking-and-rental-manager-for-woocommerce')); ?>:</strong></td>
                                    <td><?php echo $discount_type; ?></td>
                                </tr>
                            <?php } ?>

                            <?php if($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){ ?>
                                <tr>
                                    <td>
                                        <strong><?php rbfw_string('rbfw_text_rent_information',__('Rent Information','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong>
                                    </td>
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

                            <?php if ( ! empty( $service_info ) ){ ?>
                                <tr>
                                    <td>
                                        <strong><?php rbfw_string('rbfw_text_extra_service_information',__('Extra Service Information','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong>
                                    </td>
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
                                                $total_days = $ticket_info['total_days'];
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
                                            } elseif($rent_type == 'resort'){
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
                            <?php } ?>


                            <?php if ( ! empty( $service_infos ) ){ ?>
                                <tr>
                                    <td>
                                        <?php esc_html_e( 'Service Information:', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                    </td>
                                    <td>
                                        <?php foreach ($service_infos as $key => $value){ 
                                            
                                            ?>
                                            
                                            <?php if(count($value)){ ?>
                                                <table>
                                                    <tr>
                                                        <td><?php echo $key; ?></td>
                                                    </tr>
                                                    <?php foreach ($value as $key1=>$item){ ?>
                                                        
                                                        <tr>
                                                            <td><?php echo $item['name'] ?></td>
                                                            <td>
                                                                <?php
                                                                if($item['service_price_type']=='day_wise'){
                                                                    echo '('.wc_price($item['price']). 'x'. $item['quantity'] . 'x' .$total_days .'='.wc_price($item['price']*$item['quantity']*$total_days).')';
                                                                }else{
                                                                    echo '('.wc_price($item['price']). 'x'. $item['quantity'] .'='.wc_price($item['price']*$item['quantity']).')';
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
                                <td><?php echo wc_price($duration_cost); ?></td>
                            </tr>
                            <?php if($service_cost){ ?>
                                <tr>
                                    <td><strong><?php rbfw_string('rbfw_text_resource_cost',__('Resource Cost','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                                    <td><?php echo wc_price($service_cost); ?></td>
                                </tr>
                            <?php } ?>

                            <?php if($rbfw_payment_system == 'mps' && $mps_tax_switch == 'on' && !empty($tax)){ ?>
                            <tr>
                                <td><strong><?php echo $rbfw->get_option_trans('rbfw_text_tax', 'rbfw_basic_translation_settings', __('Tax','booking-and-rental-manager-for-woocommerce')); ?></strong></td>
                                <td><?php echo wc_price($tax); ?></td>
                            </tr>
                            <?php } ?>



                            <?php if($discount_amount){ ?>
                                <tr>
                                    <td><strong><?php echo $rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')); ?>:</strong></td>
                                    <td><?php echo wc_price($discount_amount); ?></td>
                                </tr>
                            <?php } ?>


                            <?php if($security_deposit_amount){ ?>
                                <tr>
                                    <td><strong><?php echo (!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit'); ?>:</strong></td>
                                    <td><?php echo wc_price($security_deposit_amount); ?></td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <td><strong><?php rbfw_string('rbfw_text_total_cost',__('Total Cost','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                                <td><?php echo wc_price($total_cost).' '.$tax_status; ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php } ?>

            <?php

            $is_tax_inclusive = get_option('woocommerce_prices_include_tax', true);

            if($is_tax_inclusive == 'yes'){
                $wps_order_tax = !empty(get_post_meta($order_id,'rbfw_order_tax',true)) ? get_post_meta($order_id,'rbfw_order_tax',true) : '';
                $subtotal = (float)$subtotal - (float)$wps_order_tax;
                $subtotal = rbfw_mps_price($subtotal).'(ex. tax)';
            } else{
                $subtotal = rbfw_mps_price($subtotal);
            }
            ?>

            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th colspan="2"><?php rbfw_string('rbfw_text_total',__('Summary','booking-and-rental-manager-for-woocommerce')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_summary',__('Subtotal','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo $subtotal; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_tax',__('Tax','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo $rbfw_order_tax; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php rbfw_string('rbfw_text_total_cost',__('Total Cost','booking-and-rental-manager-for-woocommerce')); echo ':'; ?></strong></td>
                        <td><?php echo wc_price($grand_total); ?></td>
                    </tr>
                </tbody>
            </table>
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
    if ( isset( $_POST['rbfw_pickup_note'] ) ) {
        update_post_meta($post_id, 'rbfw_pickup_note', $_POST['rbfw_pickup_note']);
        update_post_meta($post_id, 'rbfw_pickup_date', date('Y-m-d H:i'));
    }
    if ( isset( $_POST['rbfw_return_note'] ) ) {
        update_post_meta($post_id, 'rbfw_return_note', $_POST['rbfw_return_note']);
        update_post_meta($post_id, 'rbfw_return_date', date('Y-m-d H:i'));
    }
    if ( isset( $_POST['rbfw_return_security_deposit_amount'] ) ) {
        update_post_meta($post_id, 'rbfw_return_security_deposit_amount', $_POST['rbfw_return_security_deposit_amount']);
    }


    if ( isset( $_POST['rbfw_order_status'] ) ) {
        update_post_meta( $post_id, 'rbfw_order_status', $_POST['rbfw_order_status'] );
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;
        $modified_date =  current_datetime()->format('F j, Y h:i a');

        $status = 'Status changed to <strong>'.$_POST['rbfw_order_status'].'</strong> by '.$username.' on '.$modified_date;
        $current_status_update = get_post_meta( $post_id, 'rbfw_order_status_revision', true );

        $current_status = get_post_meta( $post_id, 'rbfw_order_status', true );

        $current_status_wc = $current_status;

        if($current_status=='picked'){
            $current_status_wc = 'processing';
        }
        if($current_status=='returned'){
            $current_status_wc = 'completed';
        }

        global $rbfw;
        $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

        if($rbfw_payment_system == 'wps'){
            $rbfw_link_order_id = get_post_meta( $post_id, 'rbfw_link_order_id', true );
            $orderDetail = new WC_Order( $rbfw_link_order_id );

            $orderDetail->update_status("wc-".$current_status_wc, $current_status_wc, TRUE);

            update_post_meta( $post_id, 'rbfw_order_status', $_POST['rbfw_order_status'] );

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