<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rbfw_show_cart_item', 'rbfw_show_cart_item_func', 10, 2 );
function rbfw_show_cart_item_func( $cart_item, $rbfw_id ) {
    global $rbfw;
    $rbfw_rent_type 	= get_post_meta( $rbfw_id, 'rbfw_item_type', true );

    if($rbfw_rent_type == 'resort'):
        $start_datetime    = $cart_item['rbfw_start_datetime'] ? $cart_item['rbfw_start_datetime'] : '';
        $end_datetime 		= $cart_item['rbfw_end_datetime'] ? $cart_item['rbfw_end_datetime'] : '';
        $rbfw_room_price_category 	= $cart_item['rbfw_room_price_category'] ? $cart_item['rbfw_room_price_category'] : '';	
        
        $rbfw_room_info 			= $cart_item['rbfw_room_info'] ? $cart_item['rbfw_room_info'] : [];
        $rbfw_type_info 			= $cart_item['rbfw_type_info'] ? $cart_item['rbfw_type_info'] : [];		
        $rbfw_resort_room_data 		= get_post_meta( $rbfw_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_resort_room_data', true ) : array();
        $rbfw_resort_ticket_info 	= $cart_item['rbfw_ticket_info'] ? $cart_item['rbfw_ticket_info'] : [];
        $rbfw_item_quantity = 1;
        if($rbfw_room_price_category == 'daynight'):
            $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daynight_rate','room_type');
        elseif($rbfw_room_price_category == 'daylong'):
            $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daylong_rate','room_type');
        else:
            $room_types = array();
        endif;

        $room_desc = array_column($rbfw_resort_room_data,'rbfw_room_desc','room_type');

        $rbfw_service_info 			= $cart_item['rbfw_service_info'] ? $cart_item['rbfw_service_info'] : [];
        $rbfw_extra_service_data 	= get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();
        
        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        $rbfw_room_duration_price 	= $cart_item['rbfw_room_duration_price'] ? $cart_item['rbfw_room_duration_price'] : '';
        $rbfw_room_service_price 	= $cart_item['rbfw_room_service_price'] ? $cart_item['rbfw_room_service_price'] : '';

        $discount_type 	= $cart_item['discount_type'] ? $cart_item['discount_type'] : '';
        $discount_amount 	= $cart_item['discount_amount'] ? $cart_item['discount_amount'] : '';

    elseif($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment'):
        $start_datetime = $cart_item['rbfw_start_date'] ? $cart_item['rbfw_start_date'] : '';
        $start_time = $cart_item['rbfw_start_time'] ? $cart_item['rbfw_start_time'] : '';
        $end_datetime = $cart_item['rbfw_end_date'] ? $cart_item['rbfw_end_date'] : '';
        $end_time = $cart_item['rbfw_end_time'] ? $cart_item['rbfw_end_time'] : '';
        $rbfw_start_datetime = $cart_item['rbfw_start_datetime'] ? $cart_item['rbfw_start_datetime'] : '';
        $rbfw_end_datetime = $cart_item['rbfw_end_datetime'] ? $cart_item['rbfw_end_datetime'] : '';
        $rbfw_type_info = $cart_item['rbfw_type_info'] ? $cart_item['rbfw_type_info'] : [];
        $rbfw_service_info 	= $cart_item['rbfw_service_info'] ? $cart_item['rbfw_service_info'] : [];
        $rbfw_bikecarsd_ticket_info = $cart_item['rbfw_ticket_info'] ? $cart_item['rbfw_ticket_info'] : [];

        $rbfw_bikecarsd_data = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) : array();
        $rbfw_extra_service_data = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();
        $rbfw_item_quantity = 1;

        if(!empty($rbfw_bikecarsd_data)):
            $rent_types = array_column($rbfw_bikecarsd_data,'price','rent_type'); 
        else:
            $rent_types = array();
        endif;

        $rent_desc = array_column($rbfw_bikecarsd_data,'short_desc','rent_type');
   
        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        $rbfw_bikecarsd_duration_price 	= $cart_item['rbfw_bikecarsd_duration_price'] ? $cart_item['rbfw_bikecarsd_duration_price'] : '';
        $rbfw_bikecarsd_service_price 	= $cart_item['rbfw_bikecarsd_service_price'] ? $cart_item['rbfw_bikecarsd_service_price'] : '';			
    else:
        
        $start_datetime     = $cart_item['rbfw_start_datetime'] ? $cart_item['rbfw_start_datetime'] : '';
        $end_datetime       = $cart_item['rbfw_end_datetime'] ? $cart_item['rbfw_end_datetime'] : '';
        $start_date         = $cart_item['rbfw_start_date'] ? $cart_item['rbfw_start_date'] : '';
        $start_time         = $cart_item['rbfw_start_time'] ? $cart_item['rbfw_start_time'] : '';
        $end_date           = $cart_item['rbfw_end_date'] ? $cart_item['rbfw_end_date'] : '';
        $end_time           = $cart_item['rbfw_end_time'] ? $cart_item['rbfw_end_time'] : '';
        $rbfw_pickup_point  = $cart_item['rbfw_pickup_point'] ? $cart_item['rbfw_pickup_point'] : '';
        $rbfw_dropoff_point = $cart_item['rbfw_dropoff_point'] ? $cart_item['rbfw_dropoff_point'] : '';

        $rbfw_item_quantity = $cart_item['rbfw_item_quantity'] ? $cart_item['rbfw_item_quantity'] : 1;
        $rbfw_duration_price = $cart_item['rbfw_duration_price'] ? $cart_item['rbfw_duration_price'] : '';
        $rbfw_service_price = $cart_item['rbfw_service_price'] ? $cart_item['rbfw_service_price'] : '';
        $rbfw_service_info 	= $cart_item['rbfw_service_info'] ? $cart_item['rbfw_service_info'] : [];
        $rbfw_ticket_info = $cart_item['rbfw_ticket_info'] ? $cart_item['rbfw_ticket_info'] : [];
        $variation_info = $cart_item['rbfw_variation_info'] ? $cart_item['rbfw_variation_info'] : [];
        $rbfw_extra_service_data = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();

        if(! empty($rbfw_extra_service_data)):
            $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
        else:
            $extra_services = array();
        endif;

        $discount_type 	= $cart_item['discount_type'] ? $cart_item['discount_type'] : '';
        $discount_amount 	= $cart_item['discount_amount'] ? $cart_item['discount_amount'] : '';

    endif;

    
    ?>
    <?php do_action( 'rbfw_before_cart_item_display', $cart_item, $rbfw_id ); ?>

    <?php /* Type: Resort */ ?>
    <?php if($rbfw_rent_type == 'resort'){ ?>
        <table class="rbfw_room_cart_table">
        <?php if ( ! empty( $start_datetime ) ): ?>	
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_checkin_date', 'rbfw_basic_translation_settings', __('Check-In Date','booking-and-rental-manager-for-woocommerce'));?>:</th>
            <td><?php echo rbfw_date_format($start_datetime); ?></td>
        </tr>
        <?php endif; ?>
        
        <?php if ( ! empty( $end_datetime ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_checkout_date', 'rbfw_basic_translation_settings', __('Check-Out Date','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo rbfw_date_format($end_datetime); ?></td>	
        </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_room_price_category ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_package', 'rbfw_basic_translation_settings', __('Package','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo $rbfw_room_price_category; ?></td>
        </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_room_info ) ):

            foreach ($rbfw_room_info as $key => $value):
                $room_type = $key; //Type
                if(array_key_exists($room_type, $room_types)){ // if Type exist in array
                    $room_price = $room_types[$room_type]; // get type price from array
                    $room_qty = $value;
                    $total_price = (float)$room_price * (float)$room_qty;
                    $room_description = $room_desc[$room_type]; // get type description from array
                    ?>
                    <tr>
                        <th>
                            <?php echo $room_type; ?>:
                            <span><?php echo $room_description; ?></span>
                        </th>
                        <td>(<?php echo wc_price($room_price); ?> x <?php echo $room_qty; ?>) = <?php echo wc_price($total_price); ?></td>
                    </tr>
                    <?php
                }
         
            endforeach;				

        endif; ?>

        <?php if ( ! empty( $rbfw_service_info ) ):

            foreach ($rbfw_service_info as $key => $value):
                $service_name = $key; //service name
                if(array_key_exists($service_name, $extra_services)){ // if service name exist in array
                    $service_price = $extra_services[$service_name]; // get type price from array
                    $service_qty = $value;
                    $total_service_price = (float)$service_price * (float)$service_qty;
                    ?>
                    <tr>
                        <th>
                            <?php echo $service_name; ?>:
                        </th>
                        <td>(<?php echo wc_price($service_price); ?> x <?php echo $service_qty; ?>) = <?php echo wc_price($total_service_price); ?></td>
                    </tr>
                    <?php
                }
         
            endforeach;

        endif; ?>

        <?php if ( ! empty( $rbfw_room_duration_price ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo wc_price($rbfw_room_duration_price); ?></td>
        </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_room_service_price ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo wc_price($rbfw_room_service_price); ?></td>
        </tr>
        <?php endif; ?>

        <?php if ( ! empty( $discount_amount ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo wc_price($discount_amount); ?></td>
        </tr>
        <?php endif; ?>

        </table>
        <?php } ?>
    <?php /* End Type: Resort */ ?>

    <?php /* Type: Bikecarsd */ ?>
    <?php if($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment'){ ?>
        <table class="rbfw_bikecarsd_cart_table rbfw_room_cart_table">
        <?php if ( ! empty( $start_datetime ) && !empty($start_time)): ?>	
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings', __('Start Date and Time','booking-and-rental-manager-for-woocommerce'));?>:</th>
            <td><?php echo rbfw_date_format($start_datetime).' @'.$start_time; ?></td>
        </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_type_info ) ):
            foreach ($rbfw_type_info as $key => $value):
                $rent_type = $key; //Type
                if(array_key_exists($rent_type, $rent_types)){ // if Type exist in array
                    $rent_price = $rent_types[$rent_type]; // get type price from array
                    $rent_qty = $value;
                    $total_price = (float)$rent_price * (float)$rent_qty;
                    $rent_description = $rent_desc[$rent_type]; // get type description from array
                    ?>
                    <tr>
                        <th>
                            <?php echo $rent_type; ?>:
                            <span><?php echo $rent_description; ?></span>
                        </th>
                        <td>(<?php echo wc_price($rent_price); ?> x <?php echo $rent_qty; ?>) = <?php echo wc_price($total_price); ?></td>
                    </tr>
                    <?php
                }
         
            endforeach;				

        endif; ?>

        <?php if ( ! empty( $rbfw_service_info ) ):
            foreach ($rbfw_service_info as $key => $value):
                $service_name = $key; //service name
                if(array_key_exists($service_name, $extra_services)){ // if service name exist in array
                    $service_price = $extra_services[$service_name]; // get type price from array
                    $service_qty = $value;
                    $total_service_price = (float)$service_price * (float)$service_qty;
                    ?>
                    <tr>
                        <th>
                            <?php echo $service_name; ?>:
                            
                        </th>
                        <td>(<?php echo wc_price($service_price); ?> x <?php echo $service_qty; ?>) = <?php echo wc_price($total_service_price); ?></td>
                    </tr>
                    <?php
                }
         
            endforeach;

        endif; ?>

        <?php if ( ! empty( $rbfw_bikecarsd_duration_price ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo wc_price($rbfw_bikecarsd_duration_price); ?></td>
        </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_bikecarsd_service_price ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo wc_price($rbfw_bikecarsd_service_price); ?></td>
        </tr>
        <?php endif; ?>
        </table>
    <?php } ?>	
    <?php /* End Type: Bikecarsd */ ?>

    
    <?php /* Type: Bikecarmd */ ?>

    <?php if($rbfw_rent_type == 'bike_car_md' || $rbfw_rent_type == 'dress' || $rbfw_rent_type == 'equipment' || $rbfw_rent_type == 'others'){ ?>

    <ul>

    <?php if ( ! empty( $rbfw_pickup_point ) ): ?>
        <li><?php echo $rbfw->get_option('rbfw_text_pickup_point', 'rbfw_basic_translation_settings', __('Pickup Point','booking-and-rental-manager-for-woocommerce')); echo ': ' . $rbfw_pickup_point; ?></li>
    <?php endif; ?>

    <?php if ( ! empty( $rbfw_dropoff_point ) ): ?>
        <li><?php echo $rbfw->get_option('rbfw_text_dropoff_point', 'rbfw_basic_translation_settings', __('Drop-off Point','booking-and-rental-manager-for-woocommerce')); echo ': ' . $rbfw_dropoff_point; ?></li>
    <?php endif; ?>

    <?php if ( !empty($start_datetime) && !empty($start_time)): ?>
        <li>
            <?php echo $rbfw->get_option('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings', __('Pickup Date & Time','booking-and-rental-manager-for-woocommerce')); echo ': ' . rbfw_get_datetime( $start_datetime, 'date-time-text' ); ?>
        </li>
    <?php else: ?>
        <li><?php echo $rbfw->get_option('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings', __('Pickup Date & Time','booking-and-rental-manager-for-woocommerce')); echo ': ' . rbfw_get_datetime( $start_datetime, 'date-text' ); ?></li>        
    <?php endif; ?>

    <?php if (!empty($end_datetime) && !empty($end_time)): ?>
        <li><?php echo $rbfw->get_option('rbfw_text_dropoff_date_time', 'rbfw_basic_translation_settings', __('Drop-off Date & Time','booking-and-rental-manager-for-woocommerce'));  echo ': ' . rbfw_get_datetime( $end_datetime, 'date-time-text' ); ?></li>
    <?php else: ?>
        <li><?php echo $rbfw->get_option('rbfw_text_dropoff_date_time', 'rbfw_basic_translation_settings', __('Drop-off Date & Time','booking-and-rental-manager-for-woocommerce')); echo ': ' . rbfw_get_datetime( $end_datetime, 'date-text' ); ?></li>        
    <?php endif; ?>

    <?php if(!empty($variation_info)){ 
        foreach ($variation_info as $key => $value) { 
        ?>
        <li><?php echo esc_html($value['field_label']); echo ': '; echo esc_html($value['field_value']); ?></li>
    <?php } } ?>

    <?php if ( ! empty( $rbfw_item_quantity ) ): ?>
        <li><?php echo $rbfw->get_option('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce')); echo ': '.$rbfw_item_quantity; ?></li>
    <?php endif; ?>

    <table class="rbfw_room_cart_table">
        <?php if ( ! empty( $start_datetime ) && ! empty( $end_datetime ) ): ?>
        <tr>
            <th>
                <?php echo $rbfw->get_option('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost:','booking-and-rental-manager-for-woocommerce')); ?>
                <br>
                <span><?php echo rbfw_day_diff_status( $start_datetime, $end_datetime );?></span>
            </th>
            <td>
                <?php echo '('.wc_price(rbfw_price_calculation( $rbfw_id, $start_datetime, $end_datetime, $start_date )) .' x '.$rbfw_item_quantity.')'. ' = '.wc_price(rbfw_price_calculation( $rbfw_id, $start_datetime, $end_datetime, $start_date ) * $rbfw_item_quantity);?>
            </td>
        </tr>
        <?php endif; ?>    
        <?php if ( ! empty( $rbfw_service_info ) ): ?>
        <?php
        foreach ($rbfw_service_info as $key => $value):
            $service_name = $key; //service name
            if(array_key_exists($service_name, $extra_services)){ // if service name exist in array
                $service_price = $extra_services[$service_name]; // get type price from array
                $service_qty = $value;
                $total_service_price = (float)$service_price * (float)$service_qty;
                ?>
                <tr>
                    <th>
                        <?php echo $service_name; ?>:
                    </th>
                    <td>(<?php echo wc_price($service_price); ?> x <?php echo $service_qty; ?>) = <?php echo wc_price($total_service_price); ?></td>
                </tr>
                <?php
            }

        endforeach; ?>
        <?php endif; ?>

        <?php if ( ! empty( $discount_amount ) ): ?>
        <tr>
            <th><?php echo $rbfw->get_option('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce')); ?>:</th>
            <td><?php echo wc_price($discount_amount); ?></td>
        </tr>
        <?php endif; ?>
        
    </table>

    </ul>
    <?php } ?>
    <?php do_action( 'rbfw_after_cart_item_display', $cart_item ); ?>
    <?php

}