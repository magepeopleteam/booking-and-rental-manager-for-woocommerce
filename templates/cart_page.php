<?php

global $rbfw;
$rbfw_rent_type 	= get_post_meta( $rbfw_id, 'rbfw_item_type', true );

$rbfw_enable_start_end_date  = get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) : 'yes';

?>
<?php

do_action( 'rbfw_before_cart_item_display', $cart_item, $rbfw_id );

$security_deposit_amount 	= $cart_item['security_deposit_amount'] ? $cart_item['security_deposit_amount'] : '';


?>

<?php /* Type: Resort */ ?>
<?php if($rbfw_rent_type == 'resort'){

    $start_datetime    = $cart_item['rbfw_start_datetime'] ? $cart_item['rbfw_start_datetime'] : '';
    $end_datetime 		= $cart_item['rbfw_end_datetime'] ? $cart_item['rbfw_end_datetime'] : '';

    $origin              = date_create( $start_datetime );
    $target              = date_create( $end_datetime );
    $interval            = date_diff( $origin, $target );
    $total_days          = $interval->format( '%a' );
    $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
    if ($rbfw_count_extra_day_enable == 'on') {
        $total_days++;
    }


    $rbfw_room_price_category 	= $cart_item['rbfw_room_price_category'] ? $cart_item['rbfw_room_price_category'] : '';

    $rbfw_room_info 			= $cart_item['rbfw_room_info'] ? $cart_item['rbfw_room_info'] : [];

    $rbfw_type_info 			= $cart_item['rbfw_type_info'] ? $cart_item['rbfw_type_info'] : [];



    $rbfw_resort_room_data 		= get_post_meta( $rbfw_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_resort_room_data', true ) : array();

    $rbfw_resort_ticket_info 	= $cart_item['rbfw_ticket_info'] ? $cart_item['rbfw_ticket_info'] : [];
    $rbfw_room_price 	= $cart_item['rbfw_room_price'] ? $cart_item['rbfw_room_price'] : [];

    $rbfw_item_quantity = 1;
    if($rbfw_room_price_category == 'daynight'):
        $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daynight_rate','room_type');
    elseif($rbfw_room_price_category == 'daylong'):
        $room_types = array_column($rbfw_resort_room_data,'rbfw_room_daylong_rate','room_type');
    else:
        $room_types = array();
    endif;

    //echo '<pre>';print_r($room_types);echo '<pre>';exit;

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
    $discount_amount 	= $cart_item['discount_amount'] ? $cart_item['discount_amount'] : '0';

    ?>
    <table class="rbfw_room_cart_table">
        <?php if ( ! empty( $start_datetime ) ): ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_checkin_date', 'rbfw_basic_translation_settings', __('Check-In Date','booking-and-rental-manager-for-woocommerce')));?>:</th>
                <td><?php echo esc_html(rbfw_date_format($start_datetime)); ?></td>
            </tr>
        <?php endif; ?>

        <?php if ( ! empty( $end_datetime ) ): ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_checkout_date', 'rbfw_basic_translation_settings', __('Check-Out Date','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                <td><?php echo esc_html(rbfw_date_format($end_datetime)); ?></td>
            </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_room_price_category ) ): ?>
            <tr class="rbfw-package">
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_package', 'rbfw_basic_translation_settings', __('Package','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                <td><?php echo esc_html($rbfw_room_price_category); ?></td>
            </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_room_info ) ):
            foreach ($rbfw_room_info as $key => $value):
                $room_type = $key; //Type
                if(array_key_exists($room_type, $room_types)){ // if Type exist in array
                    $room_price = $rbfw_room_price[$room_type]; // get type price from array
                    $room_qty = $value;
                    $total_price = (float)$room_price * (float)$room_qty;
                    $room_description = $room_desc[$room_type]; // get type description from array
                    ?>
                    <tr>
                        <th>
                            <?php echo esc_html($room_type); ?>:
                            <span><?php echo wp_kses($room_description,rbfw_allowed_html()); ?></span>
                        </th>
                        <td>(<?php echo wp_kses(wc_price($room_price),rbfw_allowed_html()); ?> x <?php echo esc_html($room_qty); ?>) = <?php echo wp_kses(wc_price($total_price),rbfw_allowed_html()); ?></td>
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
                            <?php echo esc_html($service_name); ?>:
                        </th>
                        <td>(<?php echo wp_kses(wc_price($service_price),rbfw_allowed_html()); ?> x <?php echo esc_html($service_qty); ?>) = <?php echo wp_kses(wc_price($total_service_price),rbfw_allowed_html()); ?></td>
                    </tr>
                    <?php
                }

            endforeach;

        endif; ?>

        <?php if ( ! empty( $rbfw_room_duration_price ) ): ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                <td><?php echo wp_kses(wc_price($rbfw_room_duration_price),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>

        <?php if ( ! empty( $rbfw_room_service_price ) ): ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                <td><?php echo wp_kses(wc_price($rbfw_room_service_price),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>

        <?php if ( $discount_amount  ): ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                <td><?php echo wp_kses(wc_price($discount_amount),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>
        <?php if ( ! empty( $security_deposit_amount ) ): ?>
            <tr>
                <th><?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit')); ?>:</th>
                <td><?php echo wp_kses(wc_price($security_deposit_amount),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>

    </table>
<?php } ?>

<?php if($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment'){

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

    $rbfw_pickup_point  = isset($cart_item['rbfw_pickup_point']) ? $cart_item['rbfw_pickup_point'] : '';
    $rbfw_dropoff_point = isset($cart_item['rbfw_dropoff_point']) ? $cart_item['rbfw_dropoff_point'] : '';

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

    ?>
    <table class="rbfw_bikecarsd_cart_table rbfw_room_cart_table">

        <?php if ( ! empty( $rbfw_pickup_point ) ){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_point', 'rbfw_basic_translation_settings', __('Pickup Point','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo esc_html($rbfw_pickup_point); ?></td>
            </tr>
        <?php } ?>

        <?php if ( ! empty( $rbfw_dropoff_point ) ){ ?>
                <tr>
                    <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_point', 'rbfw_basic_translation_settings', __('Drop-off Point','booking-and-rental-manager-for-woocommerce')));  ?></th>
                    <td><?php echo esc_html($rbfw_dropoff_point); ?></td>
                </tr>

        <?php } ?>


        <?php if ( ! empty( $start_datetime )): ?>
            <tr>
                <th>
                    <?php if(($start_time)){ ?>
                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings', __('Start Date and Time','booking-and-rental-manager-for-woocommerce')));?>:
                    <?php } else{ ?>
                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_start_date', 'rbfw_basic_translation_settings', __('Start Date','booking-and-rental-manager-for-woocommerce')));?>:
                    <?php } ?>
                </th>
                <td>
                    <?php echo esc_html(rbfw_date_format($start_datetime)) ; ?>
                    <?php if(($start_time)){
                        echo ' @'.esc_html(gmdate(get_option('time_format'), strtotime($start_time)));
                    } ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php

        foreach ( $rbfw_bikecarsd_data as $key => $value ){
            $rent_type = $value['rent_type'];
            if ( array_key_exists( $rent_type, $rbfw_type_info ) ) {

                if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) ) {
                    $rbfw_sp_prices = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data_sp', true );
                    if ( isset( $rbfw_sp_prices ) && $rbfw_sp_prices  ) {
                        $sp_price = check_seasonal_price_sd( $start_datetime, $rbfw_sp_prices, $rent_type );
                    }
                }
                $type_price = (isset($sp_price) and $sp_price)?$sp_price:$value['price'];

                ?>
                <tr>
                    <th>
                        <?php echo esc_html($rent_type); ?>:
                        <span><?php echo esc_html($value['short_desc']); ?></span>
                    </th>
                    <td>(<?php echo wp_kses(wc_price($type_price),rbfw_allowed_html()); ?> x <?php echo esc_html($rbfw_type_info[ $rent_type ]); ?>) = <?php echo wp_kses(wc_price($rbfw_type_info[ $rent_type ] * $type_price),rbfw_allowed_html()); ?></td>
                </tr>
                <?php
            }
        }
        ?>


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
                            <?php echo esc_html($service_name); ?>:
                        </th>
                        <td>(<?php echo wp_kses(wc_price($service_price),rbfw_allowed_html()); ?> x <?php echo esc_html($service_qty); ?>) = <?php echo wp_kses(wc_price($total_service_price),rbfw_allowed_html()); ?></td>
                    </tr>
                    <?php
                }

            endforeach;

        endif; ?>

        <?php  if ( ! empty( $rbfw_bikecarsd_duration_price ) ): ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                <td><?php echo wp_kses(wc_price($rbfw_bikecarsd_duration_price),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>



        <?php if ( ! empty( $rbfw_bikecarsd_service_price ) ): ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                <td><?php echo wp_kses(wc_price($rbfw_bikecarsd_service_price),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>
        <?php if ( ! empty( $security_deposit_amount ) ): ?>
            <tr>
                <th><?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit')); ?>:</th>
                <td><?php echo wp_kses(wc_price($security_deposit_amount),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>

    </table>
<?php } ?>


<?php if($rbfw_rent_type == 'bike_car_md' || $rbfw_rent_type == 'dress' || $rbfw_rent_type == 'equipment' || $rbfw_rent_type == 'others'){


    $start_datetime     = $cart_item['rbfw_start_datetime'] ? $cart_item['rbfw_start_datetime'] : '';
    $end_datetime       = $cart_item['rbfw_end_datetime'] ? $cart_item['rbfw_end_datetime'] : '';

    $start_date         = $cart_item['rbfw_start_date'] ? $cart_item['rbfw_start_date'] : '';
    $start_time         = $cart_item['rbfw_start_time'] ? $cart_item['rbfw_start_time'] : '';
    $end_date           = $cart_item['rbfw_end_date'] ? $cart_item['rbfw_end_date'] : '';
    $end_time           = $cart_item['rbfw_end_time'] ? $cart_item['rbfw_end_time'] : '';
    $rbfw_pickup_point  = $cart_item['rbfw_pickup_point'] ? $cart_item['rbfw_pickup_point'] : '';
    $rbfw_dropoff_point = $cart_item['rbfw_dropoff_point'] ? $cart_item['rbfw_dropoff_point'] : '';
    $rbfw_duration_md = $cart_item['rbfw_duration_md'] ? $cart_item['rbfw_duration_md'] : '';

    $rbfw_duration_price_individual = isset($cart_item['rbfw_duration_price_individual'] )? $cart_item['rbfw_duration_price_individual'] : 0;

    $rbfw_duration_price = $cart_item['rbfw_duration_price'] ? $cart_item['rbfw_duration_price'] : 0;


    $rbfw_item_quantity = $cart_item['rbfw_item_quantity'] ? $cart_item['rbfw_item_quantity'] : 1;
    $rbfw_service_price = $cart_item['rbfw_service_price'] ? $cart_item['rbfw_service_price'] : '';
    $rbfw_service_info 	= $cart_item['rbfw_service_info'] ? $cart_item['rbfw_service_info'] : [];
    $rbfw_service_infos 	= $cart_item['rbfw_service_infos'] ? $cart_item['rbfw_service_infos'] : [];
    $rbfw_ticket_info = $cart_item['rbfw_ticket_info'] ? $cart_item['rbfw_ticket_info'] : [];
    $variation_info = $cart_item['rbfw_variation_info'] ? $cart_item['rbfw_variation_info'] : [];
    $total_days = $cart_item['total_days'];


    $rbfw_extra_service_data = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();


    if(! empty($rbfw_extra_service_data)):
        $extra_services = array_column($rbfw_extra_service_data,'service_price','service_name');
    else:
        $extra_services = array();
    endif;

    $discount_type 	= $cart_item['discount_type'] ? $cart_item['discount_type'] : '';
    $discount_amount 	= $cart_item['discount_amount'] ? $cart_item['discount_amount'] : '';

    $rbfw_enable_extra_service_qty = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) : 'no';

    ?>

    <table class="rbfw_room_cart_table">

        <?php if ( ! empty( $rbfw_pickup_point ) ){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_point', 'rbfw_basic_translation_settings', __('Pickup Point','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo esc_html($rbfw_pickup_point); ?></td>
            </tr>
        <?php } ?>

        <?php if ( ! empty( $rbfw_dropoff_point ) ){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_point', 'rbfw_basic_translation_settings', __('Drop-off Point','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo  esc_html($rbfw_dropoff_point); ?></td>
            </tr>
        <?php } ?>

        <?php if ( !empty($start_datetime) && !empty($start_time)){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings', __('Pickup Date & Time','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo esc_html(rbfw_get_datetime( $start_datetime, 'date-time-text' )); ?></td>
            </tr>
        <?php }else{ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date', 'rbfw_basic_translation_settings', __('Pickup Date','booking-and-rental-manager-for-woocommerce'))) ?></th>
                <td><?php echo esc_html(rbfw_get_datetime( $start_datetime, 'date-text' )); ?></td>
            </tr>
        <?php } ?>



        <?php if (!empty($end_datetime) && !empty($end_time)){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_date_time', 'rbfw_basic_translation_settings', __('Drop-off Date & Time','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo esc_html(rbfw_get_datetime( $end_datetime, 'date-time-text' )); ?></td>
            </tr>
        <?php }else{ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_date', 'rbfw_basic_translation_settings', __('Drop-off Date','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo esc_html(rbfw_get_datetime( $end_datetime, 'date-text' )); ?></td>
            </tr>
        <?php } ?>

        <?php if(!empty($variation_info)){ ?>
            <?php foreach ($variation_info as $key => $value) { ?>
                <tr>
                    <th><?php echo esc_html($value['field_label']);  ?></th>
                    <td><?php echo esc_html($value['field_value']); ?></td>
                </tr>
            <?php } ?>
        <?php }  ?>

        <?php if ( ! empty( $rbfw_item_quantity ) ){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo esc_html($rbfw_item_quantity); ?></td>
            </tr>
        <?php } ?>


        <?php if ( ! empty( $start_datetime ) && ! empty( $end_datetime ) && $rbfw_duration_price_individual ){ ?>
            <tr>
                <th>
                    <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost:','booking-and-rental-manager-for-woocommerce'))); ?>
                </th>
                <td>
                    <?php echo wp_kses('('.wc_price((float)$rbfw_duration_price_individual) .' x '.$rbfw_item_quantity.')'. ' = '.wc_price((float)$rbfw_duration_price_individual * $rbfw_item_quantity),rbfw_allowed_html());?>
                </td>
            </tr>
        <?php } ?>


        <tr>
            <th>
                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration', 'rbfw_basic_translation_settings', __('Duration','booking-and-rental-manager-for-woocommerce'))); ?>
            </th>
            <td>
                <?php echo esc_html($rbfw_duration_md); ?>
            </td>
        </tr>

        <?php  if ( ! empty( $rbfw_service_infos ) ){ ?>
            <?php foreach ($rbfw_service_infos as $key => $value){ ?>
                    <?php if(count($value)){ ?>
                        <tr>
                            <th><?php echo esc_html($key); ?> </th>
                            <td>
                                <table>
                                    <?php foreach ($value as $key1=>$item){ ?>
                                        <tr>
                                            <td><?php echo esc_html($item['name']); ?></td>
                                            <td><?php
                                                if($item['service_price_type']=='day_wise'){
                                                    $rbfw_service_price =  (float)$rbfw_service_price+(float)$item['price']*(int)$item['quantity']*(int)$total_days;
                                                    echo '('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) . 'x' .esc_html($total_days) .'='.wp_kses(wc_price($item['price']*(int)$item['quantity']*$total_days),rbfw_allowed_html()).')';
                                                }else{
                                                    echo ('('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) .'='.wp_kses(wc_price($item['price']*$item['quantity']),rbfw_allowed_html())).')';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php } ?>




            <?php if ( ! empty( $rbfw_service_info ) ){ ?>
                <?php
                foreach ($rbfw_service_info as $key => $value){
                    $service_name = $key; //service name
                    if(array_key_exists($service_name, $extra_services)){ // if service name exist in array
                        $service_price = $extra_services[$service_name]; // get type price from array
                        $service_qty = $value;
                        if($rbfw_item_quantity > 1 && $service_qty == 1 && $rbfw_enable_extra_service_qty != 'yes'){
                            $service_qty = $rbfw_item_quantity;
                        }
                        $total_service_price = (float)$service_price * (int)$service_qty;
                        ?>
                        <tr>
                            <th>
                                <?php echo esc_html($service_name); ?>:
                            </th>
                            <td>
                                (<?php echo wp_kses(wc_price($service_price),rbfw_allowed_html()); ?> x <?php echo esc_html($service_qty); ?>) = <?php echo wp_kses(wc_price($total_service_price),rbfw_allowed_html()); ?>
                            </td>
                        </tr>
                        <?php
                    }
                } ?>
            <?php } ?>

            <?php if (  $discount_amount ): ?>
                <tr>
                    <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_discount', 'rbfw_basic_translation_settings', __('Discount','booking-and-rental-manager-for-woocommerce'))); ?>:</th>
                    <td><?php echo wp_kses(wc_price($discount_amount),rbfw_allowed_html()); ?></td>
                </tr>
            <?php endif; ?>


            <?php if ( ! empty( $security_deposit_amount ) ): ?>
                <tr>
                    <th><?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit')); ?>:</th>
                    <td><?php echo wp_kses(wc_price($security_deposit_amount),rbfw_allowed_html()); ?></td>
                </tr>
            <?php endif; ?>


        </table>

<?php }  ?>


<?php if($rbfw_rent_type == 'multiple_items'){


    $start_datetime = $cart_item['rbfw_start_date'] ? $cart_item['rbfw_start_date'] : '';
    $start_time = $cart_item['rbfw_start_time'] ? $cart_item['rbfw_start_time'] : '';


    $end_datetime = $cart_item['rbfw_end_date'] ? $cart_item['rbfw_end_date'] : '';
    $end_time = $cart_item['rbfw_end_time'] ? $cart_item['rbfw_end_time'] : '';
    $rbfw_start_datetime = $cart_item['rbfw_start_datetime'] ? $cart_item['rbfw_start_datetime'] : '';
    $rbfw_end_datetime = $cart_item['rbfw_end_datetime'] ? $cart_item['rbfw_end_datetime'] : '';


    $rbfw_service_info 	= $cart_item['rbfw_service_info'] ? $cart_item['rbfw_service_info'] : [];

    $rbfw_service_infos 	= $cart_item['rbfw_service_infos'] ? $cart_item['rbfw_service_infos'] : [];


    $multiple_items_info = get_post_meta( $rbfw_id, 'multiple_items_info', true ) ? get_post_meta( $rbfw_id, 'multiple_items_info', true ) : array();

    $rbfw_pickup_point  = isset($cart_item['rbfw_pickup_point']) ? $cart_item['rbfw_pickup_point'] : '';
    $rbfw_dropoff_point = isset($cart_item['rbfw_dropoff_point']) ? $cart_item['rbfw_dropoff_point'] : '';

    $rbfw_item_quantity = 1;

    $duration_type  = isset($cart_item['duration_type']) ? $cart_item['duration_type'] : '';
    $duration_qty  = isset($cart_item['duration_qty']) ? $cart_item['duration_qty'] : '';

   // echo '<pre>'; print_r($cart_item);echo '<pre>';exit;

    $pricing_type = ($duration_type == 'hourly' ? 'hourly_price' : ($duration_type == 'daily' ? 'daily_price' : ($duration_type == 'weekly' ? 'weekly_price':'monthly_price')));

    $total_days = $cart_item['total_days'];

    if(! empty($multiple_items_info)):
        $all_services = array_column($multiple_items_info,$pricing_type,'item_name');
    else:
        $all_services = array();
    endif;


    ?>
    <table class="rbfw_bikecarsd_cart_table rbfw_room_cart_table">

        <?php if ( ! empty( $rbfw_pickup_point ) ){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_point', 'rbfw_basic_translation_settings', __('Pickup Point','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <td><?php echo esc_html($rbfw_pickup_point); ?></td>
            </tr>
        <?php } ?>

        <?php if ( ! empty( $rbfw_dropoff_point ) ){ ?>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_point', 'rbfw_basic_translation_settings', __('Drop-off Point','booking-and-rental-manager-for-woocommerce')));  ?></th>
                <td><?php echo esc_html($rbfw_dropoff_point); ?></td>
            </tr>

        <?php } ?>


        <?php if ( ! empty( $start_datetime )): ?>
            <tr>
                <th>
                    <?php if(($start_time)){ ?>
                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings', __('Start Date and Time','booking-and-rental-manager-for-woocommerce')));?>:
                    <?php } else{ ?>
                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_start_date', 'rbfw_basic_translation_settings', __('Start Date','booking-and-rental-manager-for-woocommerce')));?>:
                    <?php } ?>
                </th>
                <td>
                    <?php echo esc_html(rbfw_date_format($start_datetime)) ; ?>
                    <?php if(($start_time)){
                        echo ' @'.esc_html(gmdate(get_option('time_format'), strtotime($start_time)));
                    } ?>
                </td>
            </tr>
        <?php endif; ?>




        <?php if ( ! empty( $rbfw_service_info ) ):
            foreach ($rbfw_service_info as $key => $value):
                $service_name = $key; //service name

                if(array_key_exists($service_name, $all_services)){ // if service name exist in array
                    $service_price = $all_services[$service_name]; // get type price from array
                    $service_qty = $value;
                    $total_service_price = (float)$service_price * (float)$service_qty * $duration_qty;
                    ?>
                    <tr>
                        <th>
                            <?php echo esc_html($service_name); ?>:
                        </th>
                        <td>(<?php echo wp_kses(wc_price($service_price),rbfw_allowed_html()); ?> x <?php echo esc_html($service_qty); ?> x <?php echo esc_html($duration_qty); ?>) = <?php echo wp_kses(wc_price($total_service_price),rbfw_allowed_html()); ?></td>
                    </tr>
                    <?php
                }

            endforeach;

        endif; ?>

        <?php  if ( ! empty( $rbfw_service_infos ) ){ ?>
            <?php foreach ($rbfw_service_infos as $key => $value){ ?>
                <?php if(count($value)){ ?>
                    <tr>
                        <th><?php echo esc_html($key); ?> </th>
                        <td>
                            <table>
                                <?php foreach ($value as $key1=>$item){ ?>
                                    <tr>
                                        <td><?php echo esc_html($item['name']); ?></td>
                                        <td><?php
                                            if($item['service_price_type']=='day_wise'){
                                                echo '('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) . 'x' .esc_html($total_days) .'='.wp_kses(wc_price($item['price']*(int)$item['quantity']*$total_days),rbfw_allowed_html()).')';
                                            }else{
                                                echo ('('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) .'='.wp_kses(wc_price($item['price']*$item['quantity']),rbfw_allowed_html())).')';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>



        <?php if ( ! empty( $security_deposit_amount ) ): ?>
            <tr>
                <th><?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : 'Security Deposit')); ?>:</th>
                <td><?php echo wp_kses(wc_price($security_deposit_amount),rbfw_allowed_html()); ?></td>
            </tr>
        <?php endif; ?>

    </table>

<?php }  ?>


<?php do_action( 'rbfw_after_cart_item_display', $cart_item ); ?>
