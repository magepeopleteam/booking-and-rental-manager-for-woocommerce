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

if (!class_exists('RBFWInventoryPage')) {

	class RBFWInventoryPage{

        public function __construct(){
            add_action('wp_ajax_rbfw_get_stock_details', array($this, 'rbfw_get_stock_details'));
            add_action('wp_ajax_rbfw_get_stock_by_filter', array($this, 'rbfw_get_stock_by_filter'));
            add_action('admin_footer', array($this, 'rbfw_inventory_script'));
        }

        public function rbfw_inventory_page(){
            $args = array(
                'post_type' => 'rbfw_item',
                'order' => 'DESC',
                'posts_per_page' => -1
            );
            $query = new WP_Query( $args );
            $total_items = $query->found_posts;
            ?>
            <div class="rbfw_inventory_page_wrap wrap">
                <h1><?php esc_html_e('Inventory','booking-and-rental-manager-for-woocommerce'); ?></h1>
                <div class="rbfw_inventory_page_filter">
                    <div class="rbfw_inventory_filter_input_group">
                        <label><?php esc_html_e('Date','booking-and-rental-manager-for-woocommerce'); ?></label>
                        <input type="text" class="rbfw_inventory_filter_date" placeholder="dd-mm-yyyy"/>
                    </div>
                    <div class="rbfw_inventory_filter_input_group">
                      <div class="w-50 ms-5 d-flex justify-content-between align-items-center">
                        <label for="">Start Time:</label>
                        <div class=" d-flex justify-content-between align-items-center">
                        <input type="time"  id="rbfw_inventory_event_start_time" value="">
                        </div>
						</div>
                    </div>
                    <div class="rbfw_inventory_filter_input_group">
                        <div class="w-50 d-flex justify-content-between align-items-center">
									<label for="">End Time:</label>
									<div class=" d-flex justify-content-between align-items-center">
										<input type="time" id="rbfw_inventory_event_end_time" value="">
									</div>
								</div>
                    </div>
                    <div class="rbfw_inventory_filter_input_group">
                        <label></label>
                        <button class="rbfw_inventory_filter_btn"><?php esc_html_e('Filter','booking-and-rental-manager-for-woocommerce'); ?></button>
                    </div>
                    <div class="rbfw_inventory_filter_input_group">
                        <label></label>
                        <button class="rbfw_inventory_reset_btn"><?php esc_html_e('Reset Filter','booking-and-rental-manager-for-woocommerce'); ?></button>
                    </div>
                    <div class="rbfw_inventory_filter_input_group">
                        <label></label>
                        <button class="rbfw_inventory_refresh_btn"><?php esc_html_e('Refresh Page','booking-and-rental-manager-for-woocommerce'); ?></button>
                    </div>
                </div>
                <div class="rbfw_inventory_page_table_wrap">
                    <?php echo $this->rbfw_inventory_page_table($query); ?>
                </div>
            </div>
            <div id="rbfw_stock_view_result_wrap">
                <div id="rbfw_stock_view_result_inner_wrap"></div>
            </div>
            <div class="rbfw-inventory-page-ph">
                <div class="rbfw-ph-item">
                    <div class="rbfw-ph-col-12">
                        <div class="rbfw-ph-row">
                            <div class="rbfw-ph-col-12 big"></div>
                        </div>
                        <div class="rbfw-ph-row">
                            <?php for ($i=0; $i < $total_items; $i++) { ?>
                                <div class="rbfw-ph-col-12"></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }


        public function rbfw_inventory_page_table($query, $date = null, $start_time = null, $end_time = null){

            ob_start();
            $inventory_based_on_return = rbfw_get_option('inventory_based_on_pickup_return','rbfw_basic_gen_settings');
            ?>
                <table class="rbfw_inventory_page_table">
                    <thead  class="rbfw_inventory_page_table_head">
                        <tr>
                            <th><?php esc_html_e('Date','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Item Name','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th class="rbfw_text_center"><?php esc_html_e('Item Stock','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th class="rbfw_text_center"><?php esc_html_e('Item Sold Qty','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th class="rbfw_text_center"><?php esc_html_e('Extra Service Stock','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th class="rbfw_text_center"><?php esc_html_e('Extra Service Sold Qty','booking-and-rental-manager-for-woocommerce'); ?></th>                            
                            <th class="rbfw_text_center"><?php esc_html_e('Category Service','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th class="rbfw_text_center"><?php esc_html_e('Category Service Sold Qty','booking-and-rental-manager-for-woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    if ( $query->have_posts() ) { 
                        while ( $query->have_posts() ) {
                        $query->the_post();
                        global $post;
                        $post_id = $post->ID;

                        $rent_type = !empty(get_post_meta($post_id, 'rbfw_item_type', true)) ? get_post_meta($post_id, 'rbfw_item_type', true) : '';
                        
                        $rbfw_variations_data = !empty(get_post_meta($post_id, 'rbfw_variations_data', true)) ? get_post_meta($post_id, 'rbfw_variations_data', true) : [];
                        $rbfw_resort_room_data = !empty(get_post_meta($post_id, 'rbfw_resort_room_data', true)) ? get_post_meta($post_id, 'rbfw_resort_room_data', true) : [];
                        $rbfw_bike_car_sd_data = !empty(get_post_meta($post_id, 'rbfw_bike_car_sd_data', true)) ? get_post_meta($post_id, 'rbfw_bike_car_sd_data', true) : [];

                        $rbfw_extra_service_data = !empty(get_post_meta($post_id, 'rbfw_extra_service_data', true)) ? get_post_meta($post_id, 'rbfw_extra_service_data', true) : [];
                        $total_es_qty = 0;
                        foreach ($rbfw_extra_service_data as $value) {
                            $total_es_qty += !empty($value['service_qty']) ? $value['service_qty'] : 0;
                        }

                        $rbfw_item_stock_quantity = 0;

                        if ($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){
                           
                            foreach ($rbfw_bike_car_sd_data as $key => $bike_car_sd_data) {

                                $rbfw_item_stock_quantity += !empty($bike_car_sd_data['qty']) ? $bike_car_sd_data['qty'] : 0;
                            }

                        } elseif ($rent_type == 'resort'){

                            foreach ($rbfw_resort_room_data as $key => $resort_room_data) {

                                $rbfw_item_stock_quantity += !empty($resort_room_data['rbfw_room_available_qty']) ? $resort_room_data['rbfw_room_available_qty'] : 0;
                            }

                        } else {

                            $rbfw_item_stock_quantity = !empty(get_post_meta($post_id, 'rbfw_item_stock_quantity', true)) ? get_post_meta($post_id, 'rbfw_item_stock_quantity', true) : 0;
                        }

                        if ( !empty($date) ){

                            $current_date = $date;

                        } else {
                            
                            $current_date = date_i18n('d-m-Y');
                        }
                        
                        $rbfw_inventory = !empty(get_post_meta($post_id, 'rbfw_inventory', true)) ? get_post_meta($post_id, 'rbfw_inventory', true) : [];

                        $inventory_based_on_return = rbfw_get_option('inventory_based_on_return','rbfw_basic_gen_settings');

                        $remaining_item_stock = $rbfw_item_stock_quantity;
                        $remaining_es_stock = $total_es_qty;
                        $sold_item_qty = 0;
                        $sold_es_qty = 0;



                        if(!empty($rbfw_inventory)){
                            
                            foreach ($rbfw_inventory as $key => $inventory) {
                                $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];
                                if ( in_array($current_date, $booked_dates) && ($inventory['rbfw_order_status'] == 'completed' || $inventory['rbfw_order_status'] == 'processing' || $inventory['rbfw_order_status'] == 'picked' || (($inventory_based_on_return=='yes')?$inventory['rbfw_order_status'] == 'returned':'')) ){

                                    $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];
                                    $rbfw_variation_info = !empty($inventory['rbfw_variation_info']) ? $inventory['rbfw_variation_info'] : [];
                                    $rbfw_service_info = !empty($inventory['rbfw_service_info']) ? $inventory['rbfw_service_info'] : [];
                                    $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;
                                    
                                    if($rent_type == 'bike_car_sd' || $rent_type == 'appointment' || $rent_type == 'resort') {
                                        if (!empty($rbfw_type_info)) {
                                            foreach ($rbfw_type_info as $key => $type_info) {
                                                $sold_item_qty += $type_info;
                                            }
                                        }
                                        if (!empty($rbfw_service_info)) {
                                        foreach ($rbfw_service_info as $key => $service_info) {
                                            $sold_es_qty += $service_info;
                                        }
                                    }
                                    }else {
                                        $inventory_start_date = $booked_dates[0];
                                        $inventory_end_date = end($booked_dates);
                                        $inventory_start_time = $inventory['rbfw_start_time'];
                                        $inventory_end_time = $inventory['rbfw_end_time'];
                                        $inventory_start_datetime = strtotime($inventory_start_date . ' ' . $inventory_start_time);
                                        $inventory_end_datetime =  strtotime($inventory_end_date . ' ' . $inventory_end_time);
                                        if($start_time && $end_time){
                                            $pickup_datetime = strtotime($date . ' ' . $start_time);
                                            $dropoff_datetime = strtotime($date . ' ' . $end_time);
                                            if(!(($inventory_start_datetime>$pickup_datetime && $inventory_start_datetime>$dropoff_datetime) || ($inventory_end_datetime<$pickup_datetime && $inventory_end_datetime<$dropoff_datetime))){
                                                $sold_item_qty += $rbfw_item_quantity;
                                                if (!empty($rbfw_service_info)) {
                                                    foreach ($rbfw_service_info as $key => $service_info) {
                                                        $sold_es_qty += $service_info;
                                                    }
                                                }
                                            }
                                        }else{
                                            $sold_item_qty += $rbfw_item_quantity;
                                            if (!empty($rbfw_service_info)) {
                                                foreach ($rbfw_service_info as $key => $service_info) {
                                                    $sold_es_qty += $service_info;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $remaining_item_stock = $rbfw_item_stock_quantity - (int)$sold_item_qty;
                            $remaining_es_stock = $total_es_qty - $sold_es_qty;
                        }


                            $rbfw_service_category_price = get_post_meta($post_id, 'rbfw_service_category_price', true);
                            $service_quantity = [];
                            $service_stock = [];
                            if (!empty($rbfw_service_category_price)) {
                                foreach($rbfw_service_category_price as $key=>$item1){
                                    $cat_title = $item1['cat_title'];
                                    $service_q = [];
                                    foreach ($item1['cat_services'] as $key1=>$single){
                                        if($single['title']){
                                            $service_quantity[] = $single['stock_quantity'];
                                            $service_q[] = array('date'=>$date,$single['title']=>total_service_quantity($cat_title,$single['title'],$date,$rbfw_inventory,$inventory_based_on_return,$start_time , $end_time ));
                                            $service_stock[] = $single['stock_quantity'] - max(array_column($service_q, $single['title']));
                                        }
                                    }
                                }
                            }

                        
                    ?>
                        <tr>
                            <td><?php echo date(get_option('date_format'),strtotime($current_date)); ?></td>

                            <td><a href="<?php echo esc_url(admin_url('post.php?post='.$post_id.'&action=edit')); ?>" class="rbfw_item_title"><?php echo esc_html(get_the_title()); ?></a></td>
                            
                            <td class="rbfw_text_center"><span class="rbfw_s_qty_span"><?php echo $remaining_item_stock; ?>/<?php echo $rbfw_item_stock_quantity; ?></span> <a class="rbfw_stock_view_details" data-request="closing" data-date="<?php echo $current_date; ?>" data-id="<?php echo get_the_ID(); ?>"><?php esc_attr_e('View Details','booking-and-rental-manager-for-woocommerce'); ?></a></td>
                            
                            <td class="rbfw_text_center"><?php  echo $sold_item_qty; ?></td>
                            <td class="rbfw_text_center"><?php echo $remaining_es_stock; ?>/<?php echo $total_es_qty; ?></td>
                            <td class="rbfw_text_center"><?php echo $sold_es_qty; ?></td>
                            <td class="rbfw_text_center"><?php echo array_sum($service_stock); ?>/<?php echo array_sum($service_quantity); ?></td>
                            <td class="rbfw_text_center"><?php echo array_sum($service_quantity)-array_sum($service_stock); ?></td>
                        </tr>
                    <?php
                        }
                    }else{
                        ?>
                        <tr>
                            <td colspan="20"><?php esc_html_e( 'Sorry, No data found!', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                        </tr>
                        <?php
                    }
                    wp_reset_postdata();
                    ?>    
                    </tbody>
                </table>
            <?php
            $content = ob_get_clean();
            return $content;
        }

        public function rbfw_get_stock_by_filter(){

            $selected_date = strip_tags($_POST['selected_date']);
            $start_date = strip_tags($_POST['start_date']);
            $end_date = strip_tags($_POST['end_date']);

            $args = array(
                'post_type' => 'rbfw_item',
                'order' => 'DESC',
                'posts_per_page' => -1
            );

            $query = new WP_Query( $args );

            $content = $this->rbfw_inventory_page_table($query, $selected_date,$start_date,$end_date);

            echo $content;

            wp_die();
        }

        public function rbfw_get_stock_details(){

            $data_request = strip_tags($_POST['data_request']);
            $data_date = strip_tags($_POST['data_date']);
            $data_id = strip_tags($_POST['data_id']);
            $inventory_based_on_return = rbfw_get_option('inventory_based_on_pickup_return','rbfw_basic_gen_settings');
            $rent_type = !empty(get_post_meta($data_id, 'rbfw_item_type', true)) ? get_post_meta($data_id, 'rbfw_item_type', true) : ''; 
            $rbfw_enable_variations = !empty(get_post_meta($data_id, 'rbfw_enable_variations', true)) ? get_post_meta($data_id, 'rbfw_enable_variations', true) : 'no';      
            $rbfw_variations_data = !empty(get_post_meta($data_id, 'rbfw_variations_data', true)) ? get_post_meta($data_id, 'rbfw_variations_data', true) : [];
            $rbfw_resort_room_data = !empty(get_post_meta($data_id, 'rbfw_resort_room_data', true)) ? get_post_meta($data_id, 'rbfw_resort_room_data', true) : [];
            $rbfw_bike_car_sd_data = !empty(get_post_meta($data_id, 'rbfw_bike_car_sd_data', true)) ? get_post_meta($data_id, 'rbfw_bike_car_sd_data', true) : [];
            $rbfw_extra_service_data = !empty(get_post_meta($data_id, 'rbfw_extra_service_data', true)) ? get_post_meta($data_id, 'rbfw_extra_service_data', true) : [];
            $total_es_qty = 0;


            foreach ($rbfw_extra_service_data as $key => $extra_service_data) {

                $total_es_qty += !empty($extra_service_data['service_qty']) ? $extra_service_data['service_qty'] : 0;
            }    

            $rbfw_item_stock_quantity = 0;

            if ($rent_type == 'bike_car_sd' || $rent_type == 'appointment'){
               
                foreach ($rbfw_bike_car_sd_data as $key => $bike_car_sd_data) {

                    $rbfw_item_stock_quantity += !empty($bike_car_sd_data['qty']) ? $bike_car_sd_data['qty'] : 0;
                }

            } elseif ($rent_type == 'resort'){

                foreach ($rbfw_resort_room_data as $key => $resort_room_data) {

                    $rbfw_item_stock_quantity += !empty($resort_room_data['rbfw_room_available_qty']) ? $resort_room_data['rbfw_room_available_qty'] : 0;
                }

            } else {

                $rbfw_item_stock_quantity = !empty(get_post_meta($data_id, 'rbfw_item_stock_quantity', true)) ? get_post_meta($data_id, 'rbfw_item_stock_quantity', true) : '';
            }

            $remaining_item_stock = $rbfw_item_stock_quantity;
            $sold_item_qty = 0;

            if($data_request == 'closing'){
            
                $rbfw_inventory =  get_post_meta($data_id, 'rbfw_inventory', true);

                if(!empty($rbfw_inventory)){

                    $rbfw_resort_room_data_closing = $rbfw_resort_room_data;
                    $rbfw_bike_car_sd_data_closing = $rbfw_bike_car_sd_data;
                    $rbfw_extra_service_data_closing = $rbfw_extra_service_data;
                    $rbfw_variations_data_closing = $rbfw_variations_data;
                    
                    foreach ($rbfw_inventory as $key => $inventory) {

                        if ( in_array($data_date, $inventory['booked_dates']) && ($inventory['rbfw_order_status'] == 'completed' || $inventory['rbfw_order_status'] == 'processing' || $inventory['rbfw_order_status'] == 'picked') ){

                            $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];
                            $rbfw_variation_info = !empty($inventory['rbfw_variation_info']) ? $inventory['rbfw_variation_info'] : [];
                            $rbfw_service_info = !empty($inventory['rbfw_service_info']) ? $inventory['rbfw_service_info'] : [];
                            $rbfw_item_quantity = !empty($inventory['rbfw_item_quantity']) ? $inventory['rbfw_item_quantity'] : 0;
                        
                            if($rent_type == 'bike_car_sd' || $rent_type == 'appointment' || $rent_type == 'resort') {
                                if (!empty($rbfw_type_info)) {
                                    foreach ($rbfw_type_info as $name => $qty) {
                                        $sold_item_qty += $qty;
                                    }
                                }
                                $i = 0;
                                foreach ($rbfw_resort_room_data_closing as $key => $resort_room_data) {
                                    $type_name = $rbfw_resort_room_data_closing[$i]['room_type'];
                                    $type_qty =$rbfw_resort_room_data_closing[$i]['rbfw_room_available_qty'];
                                    if (!empty($rbfw_type_info)) {
                                        foreach ($rbfw_type_info as $name => $qty) {
                                            if ($name == $type_name) {
                                                $rbfw_resort_room_data_closing[$i]['rbfw_room_available_qty'] = $type_qty - $qty;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                                $c = 0;
                                foreach ($rbfw_bike_car_sd_data_closing as $key => $bike_car_sd_data) {
                                    $type_name = $rbfw_bike_car_sd_data_closing[$c]['rent_type'];
                                    $type_qty =$rbfw_bike_car_sd_data_closing[$c]['qty'];
                                    if (!empty($rbfw_type_info)) {
                                        foreach ($rbfw_type_info as $name => $qty) {
                                            if ($name == $type_name) {
                                                $rbfw_bike_car_sd_data_closing[$c]['qty'] = $type_qty - $qty;
                                            }
                                        }
                                    }
                                    $c++;
                                }
                            } else {

                                $sold_item_qty += $rbfw_item_quantity;
                                $f = 0;
                                foreach ($rbfw_variations_data_closing as $key => $v_data) {
                                    $field_id = $rbfw_variations_data_closing[$f]['field_id'];
                                    $field_label = $rbfw_variations_data_closing[$f]['field_label'];
                                    $field_value = $rbfw_variations_data_closing[$f]['value'];
                                    
                                    if(!empty($rbfw_variation_info)){
                                        foreach ($rbfw_variation_info as $key => $v_info) {
                                            $s_field_id = $v_info['field_id'];
                                            $s_field_label = $v_info['field_label'];
                                            $s_field_value = $v_info['field_value'];
                                            if($s_field_id == $field_id){
                                                $g = 0;
                                                foreach ($field_value as $key => $f_value) {
                                                    
                                                    $fv_name = $f_value['name'];
                                                    $fv_qty = $f_value['quantity'];
    
                                                    if ($s_field_value == $fv_name) {
                                                        $rbfw_variations_data_closing[$f]['value'][$g]['quantity'] = $fv_qty - $rbfw_item_quantity;
                                                    }
                                                    $g++;
                                                }
                                            }
                                        }
                                    }
                                    $f++;
                                }
                            }
                            $d = 0;
                            foreach ($rbfw_extra_service_data_closing as $key => $extra_service_data) {
                                $es_name = $rbfw_extra_service_data_closing[$d]['service_name'];
                                $es_qty =$rbfw_extra_service_data_closing[$d]['service_qty'];
                                if (!empty($rbfw_service_info)) {
                                    foreach ($rbfw_service_info as $name => $qty) {
                                        if ($name == $es_name) {
                                            $rbfw_extra_service_data_closing[$d]['service_qty'] = $es_qty - $qty;
                                        }
                                    }
                                }
                                $d++;
                            }
                        }
                    }



                    $remaining_item_stock = (float)$rbfw_item_stock_quantity - (float)$sold_item_qty;
                    $rbfw_resort_room_data = $rbfw_resort_room_data_closing;
                    $rbfw_bike_car_sd_data = $rbfw_bike_car_sd_data_closing;
                    $rbfw_extra_service_data = $rbfw_extra_service_data_closing;
                    $rbfw_variations_data = $rbfw_variations_data_closing;
                }


            }

            ?>
            <table class="rbfw_inventory_page_inner_table">
                <thead>
                    <tr>
                        <td class="rbfw_inventory_vf_label"><?php esc_html_e('Available Quantity:','booking-and-rental-manager-for-woocommerce'); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td <?php if(empty($remaining_item_stock) || $remaining_item_stock <= 0){ echo "data-status=empty"; } ?>><?php echo esc_html($remaining_item_stock); ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if(!empty($rbfw_resort_room_data) && $rent_type == 'resort'){ ?>
                <div class="rbfw_inventory_vf_label"><?php esc_html_e('Room Info:','booking-and-rental-manager-for-woocommerce'); ?></div>
                <table class="rbfw_inventory_page_inner_table">
                    <thead>
                        <tr>
                            <th class="rbfw_inventory_vf_label"><?php esc_html_e('Room Type','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th class="rbfw_inventory_vf_label"><?php esc_html_e('Available Quantity','booking-and-rental-manager-for-woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rbfw_resort_room_data as $resort_room_data) { ?>
                        <tr>
                            <td><?php echo $resort_room_data['room_type']; ?></td>
                            <td><?php echo $resort_room_data['rbfw_room_available_qty']; ?></td>
                        </tr>
                        <?php } ?>                        
                    </tbody>
                </table>
            <?php } ?> 
            
            <?php if(!empty($rbfw_bike_car_sd_data) && ($rent_type == 'bike_car_sd' || $rent_type == 'appointment')){ ?>
                <div class="rbfw_inventory_vf_label"><?php esc_html_e('Rent Info:','booking-and-rental-manager-for-woocommerce'); ?></div>
                <table class="rbfw_inventory_page_inner_table">
                    <thead>
                        <tr>
                            <th class="rbfw_inventory_vf_label"><?php esc_html_e('Rent Type','booking-and-rental-manager-for-woocommerce'); ?>gggg</th>
                            <th class="rbfw_inventory_vf_label"><?php esc_html_e('Available Quantity','booking-and-rental-manager-for-woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rbfw_bike_car_sd_data as $bike_car_sd_data) { ?>
                        <tr>
                            <td><?php echo $bike_car_sd_data['rent_type']; ?></td>
                            <td><?php echo $bike_car_sd_data['qty']; ?></td>
                        </tr>
                        <?php } ?>                        
                    </tbody>
                </table>

           <?php } ?>
<?php

if($rbfw_enable_variations == 'yes' && !empty($rbfw_variations_data) && $rent_type != 'resort' && $rent_type != 'bike_car_sd' && $rent_type != 'appointment'){

    ?>
            <table class="rbfw_inventory_page_inner_table">
                <thead>
                    <tr>
                        <td class="rbfw_inventory_vf_label"><?php esc_html_e('Variation Stock:','booking-and-rental-manager-for-woocommerce'); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>

                           <table class="rbfw_inventory_page_inner_table rbfw_border_none">


                              <?php foreach ($rbfw_variations_data as $_variations_data) {   ?>

                                       <tr>
                                            <th class="rbfw_inventory_page_inner_vf_th">
                                                <div class="rbfw_inventory_vf_label">
                                                   <?php echo $_variations_data['field_label'].':' ?>
                                                </div>
                                                <?php if(!empty($_variations_data['value'])){ ?>
                                                    <table class="rbfw_inventory_page_inner_table">
                                                        <thead>
                                                           <tr>
                                                                <th class="rbfw_inventory_vf_label">
                                                                    <?php esc_html_e('Name','booking-and-rental-manager-for-woocommerce'); ?>
                                                                </th>
                                                                <th class="rbfw_inventory_vf_label">
                                                                    <?php esc_html_e('Available Quantity','booking-and-rental-manager-for-woocommerce'); ?>
                                                               </th>
                                                            </tr>
                                                       </thead>

                                                    <?php foreach ($_variations_data['value'] as $value) { ?>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                   <?php echo $value['name']; ?>
                                                                </td>
                                                                <td data-status="<?php if(empty($value['quantity']) || $value['quantity'] <= 0){ echo "empty"; }?>">
                                                                    <?php echo $value['quantity']; ?>
                                                               </td>
                                                            </tr>
                                                        </tbody>
                                                   <?php } ?>
                                                    </table>
                                                <?php } ?>
                                            </th>
                                        </tr>
                                    <?php } ?>
                                </table>

                        </td>
                    </tr>
                </tbody>
            </table>
            <?php } ?> 
            
            <?php if(!empty($rbfw_extra_service_data)){ ?>
                <div class="rbfw_inventory_vf_label"><?php esc_html_e('Extra Services:','booking-and-rental-manager-for-woocommerce'); ?></div>
                <table class="rbfw_inventory_page_inner_table">
                    <thead>
                        <tr>
                            <th class="rbfw_inventory_vf_label"><?php esc_html_e('Service Name','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th class="rbfw_inventory_vf_label"><?php esc_html_e('Available Quantity','booking-and-rental-manager-for-woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rbfw_extra_service_data as $extra_service_data) { ?>
                        <tr>
                            <td><?php echo $extra_service_data['service_name']; ?></td>
                            <td><?php echo $extra_service_data['service_qty']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>

            <?php

            $rbfw_service_category_price = get_post_meta($data_id, 'rbfw_service_category_price', true);
            $service_stock = [];
            if (!empty($rbfw_service_category_price)) { ?>
                <div class="rbfw_inventory_vf_label"><?php esc_html_e('Category wise service:','booking-and-rental-manager-for-woocommerce'); ?></div>
                <table class="rbfw_inventory_page_inner_table">
                <?php
                foreach($rbfw_service_category_price as $key=>$item1){
                    $cat_title = $item1['cat_title'];
                    ?>
                    <tr><th colspan="2" class="rbfw_inventory_vf_label"> <?php echo $cat_title; ?></th></tr>

                    <?php
                    $service_q = [];
                    foreach ($item1['cat_services'] as $key1=>$single){
                        if($single['title']){
                            ?>
                            <tr>
                            <td><?php echo $single['title']; ?></td>
                            <?php
                            $service_q[] = array('date'=>$data_date,$single['title']=>total_service_quantity($cat_title,$single['title'],$data_date,$rbfw_inventory,$inventory_based_on_return));
                            ?>
                            <td>
                            <?php echo $single['stock_quantity'] - max(array_column($service_q, $single['title'])); ?>
                            </td>
                            </tr>
                           <?php
                        }
                    }
                    ?>

                    <?php
                }
                ?>
                <table>
                <?php
            }
            ?>


            <?php
            wp_die();
        }

        public function rbfw_inventory_script(){
            ?>
            <script>
            jQuery(document).ready(function(){
                rbfw_stock_view_details_func();
                function rbfw_stock_view_details_func(){
                    jQuery('.rbfw_stock_view_details').click(function (e) { 
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        jQuery("#rbfw_stock_view_result_wrap").mage_modal({
                                escapeClose: false,
                                clickClose: false,
                                showClose: true
                        });

                        let data_request = jQuery(this).attr('data-request');
                        let data_date = jQuery(this).attr('data-date');
                        let data_id = jQuery(this).attr('data-id');

                        jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax_url,
                            data: {
                                'action' : 'rbfw_get_stock_details',
                                'data_request' : data_request,
                                'data_date' : data_date,
                                'data_id' : data_id,
                            },
                            beforeSend: function() {
                                jQuery('#rbfw_stock_view_result_inner_wrap').empty();
                                jQuery('#rbfw_stock_view_result_inner_wrap').html('<i class="fas fa-spinner fa-spin rbfw_rp_loader"></i>');
                            },		
                            success: function (response) {
                                jQuery('#rbfw_stock_view_result_inner_wrap').html(response);
                            }
                        });
                    });
                }


                jQuery('.rbfw_inventory_filter_btn').click(function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    let selected_date = jQuery('.rbfw_inventory_filter_date').val();
                    let start_date = jQuery('#rbfw_inventory_event_start_time').val();
                    let end_date = jQuery('#rbfw_inventory_event_end_time').val();
                    let placeholder_loader = jQuery('.rbfw-inventory-page-ph').clone();
          
                    if(selected_date == ''){
                        alert('Please select the date');
                        return;
                    }
                    if(start_date && !end_date){
                        alert('Please select the end time');
                        return;
                    }

                    jQuery.ajax({
                        type: 'POST',
                        url: rbfw_ajax_url,
                        data: {
                            'action' : 'rbfw_get_stock_by_filter',
                            'selected_date' : selected_date,
                            'start_date' : start_date,
                            'end_date' : end_date,
                        },
                        beforeSend: function() {
                            jQuery('.rbfw_inventory_page_table_wrap').empty();
                            jQuery('.rbfw_inventory_page_table_wrap').html(placeholder_loader);
                            jQuery('.rbfw_inventory_page_table_wrap .rbfw-inventory-page-ph').show();
                        },		
                        success: function (response) {
                            jQuery('.rbfw_inventory_page_table_wrap').html(response);
                            rbfw_stock_view_details_func();
                        }
                    });
                });

                jQuery('.rbfw_inventory_reset_btn').click(function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    jQuery('.rbfw_inventory_filter_date').val('');
                    jQuery('#rbfw_inventory_event_start_time').val('');
                    jQuery('#rbfw_inventory_event_end_time').val('');
                    let selected_date = '';
                    let placeholder_loader = jQuery('.rbfw-inventory-page-ph').clone();

                    jQuery.ajax({
                        type: 'POST',
                        url: rbfw_ajax_url,
                        data: {
                            'action' : 'rbfw_get_stock_by_filter',
                            'selected_date' : selected_date,
                        },
                        beforeSend: function() {
                            jQuery('.rbfw_inventory_page_table_wrap').empty();
                            jQuery('.rbfw_inventory_page_table_wrap').html(placeholder_loader);
                            jQuery('.rbfw_inventory_page_table_wrap .rbfw-inventory-page-ph').show();
                        },		
                        success: function (response) {
                            jQuery('.rbfw_inventory_page_table_wrap').html(response);
                            rbfw_stock_view_details_func();
                        }
                    });
                });

                jQuery('.rbfw_inventory_refresh_btn').click(function (e) { 
                    window.location.reload();
                    
                });
            });
            </script>
            <?php
        }        
    }
    new RBFWInventoryPage();
}