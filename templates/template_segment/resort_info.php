<?php
global $rbfw;

check_ajax_referer( 'rbfw_check_resort_availibility_action', 'nonce' );

$start_date = isset( $_POST['checkin_date'] ) ? sanitize_text_field( wp_unslash( $_POST['checkin_date'] ) ) : '';
$end_date   = isset( $_POST['checkout_date'] ) ? sanitize_text_field( wp_unslash( $_POST['checkout_date'] ) ) : '';
$post_id    = isset( $_POST['post_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : '';
$origin     = date_create( $start_date );
$target     = date_create( $end_date );
$interval   = date_diff( $origin, $target );
$total_days = $interval->format( '%a' );


if ($total_days ) {
    $active_tab = 'daynight';
} else {
    $active_tab = 'daylong';
}

if(isset($post_id) && isset($active_tab)){

    $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
    if ($rbfw_count_extra_day_enable == 'on' || $total_days==0) {
        $total_days = $total_days + 1;
    }
    $rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
    $rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
    $rbfw_product_id = get_post_meta( $post_id, "link_wc_product", true ) ? get_post_meta( $post_id, "link_wc_product", true ) : $post_id;

    $currency_symbol = get_woocommerce_currency_symbol();

    $rbfw_payment_system = 'wps_enabled';

    $available_qty_info_switch = get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($post_id, 'rbfw_available_qty_info_switch', true) : 'no';

    ?>

    <div class="rbfw-single-right-heading" style="margin-top: 10px;margin-bottom:0;text-align:center;">
        <?php if($active_tab=='daylong'){ ?>
            <p><?php echo esc_html($total_days) ?> <?php esc_html_e('Daylong only day stay','booking-and-rental-manager-for-woocommerce'); ?> </p>
        <?php } if($active_tab=='daynight'){  ?>
            <p><?php echo esc_html($total_days) ?> <?php esc_html_e('Day night stay','booking-and-rental-manager-for-woocommerce'); ?></p>
        <?php } ?>
    </div>

    <input type="hidden" name="rbfw_room_price_category" value="<?php echo esc_attr($active_tab); ?>"/>
    <input type="hidden" name="resort_total_days" id="resort_total_days" value="<?php echo esc_attr($total_days); ?>"/>

    <div class="rbfw_resort_rt_price_table_container">
        <table class="rbfw_room_price_table rbfw_resort_rt_price_table">
            <thead>
            <tr>
                <th><?php echo esc_html__( 'Room Type','booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th><?php echo esc_html__( 'Image','booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th><?php echo esc_html__( 'Price','booking-and-rental-manager-for-woocommerce' ); ?></th>
                <th class="w_30_pc"> <?php echo esc_html__( 'Quantity','booking-and-rental-manager-for-woocommerce' ); ?></th>
            </tr>
            </thead>
            <tbody>

            <?php
            $i = 0;

            foreach ($rbfw_resort_room_data as $key => $value) {
                $img_url    = wp_get_attachment_url($value['rbfw_room_image']);
                $uniq_id    = wp_rand();
                if($img_url) {
                    $img = '<a href="#rbfw_room_img_' . $uniq_id . '" rel="mage_modal:open"><img src="' . esc_url($img_url) . '"/></a>';
                    $img .= '<div id="rbfw_room_img_' . $uniq_id . '" class="mage_modal"><img src="' . esc_url($img_url) . '"/></div>';
                }else{
                    $img = '';
                }

                if($active_tab == 'daylong') {
                    $price = $value['rbfw_room_daylong_rate'];
                }elseif($active_tab == 'daynight') {
                    $price = $value['rbfw_room_daynight_rate'];
                }


                if($value['rbfw_room_available_qty'] > 0) {
                    $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $start_date, $end_date, $value['room_type'], '', '');
                    $max_available_qty = $max_available_qty['remaining_stock'];
                    $max_available_qty = ($max_available_qty < 0) ? 0 : $max_available_qty;
                }

                ?>

                <tr>
                    <td>
                        <span class="room_type_title"><?php echo esc_html($value['room_type']) ?></span>
                        <input type="hidden" name="rbfw_room_info[<?php echo esc_attr($i); ?>][room_type]" value="<?php echo esc_attr($value['room_type']); ?>"/>
                        <?php if($value['rbfw_room_desc']) { ?>
                            <small class="rbfw_room_desc">
                                <?php echo esc_html($value['rbfw_room_desc']); ?>
                            </small>
                            <?php if ($available_qty_info_switch == 'yes') {  ?>
                                <small class="rbfw_available_qty_notice"><?php echo esc_html__( 'Available:', 'booking-and-rental-manager-for-woocommerce' ) . esc_html($max_available_qty); ?></small>
                            <?php  } ?>
                            <input type="hidden" name="rbfw_room_info[<?php echo  esc_attr($i); ?>][room_desc]" value="<?php echo  esc_attr($value['rbfw_room_desc']); ?>"/>
                        <?php  } ?>
                    </td>
                    <td>
                        <?php echo wp_kses($img , rbfw_allowed_html()); ?>
                    </td>
                    <?php if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) || is_plugin_active( 'multi-day-price-saver-addon-for-wprently/additional-day-price.php' ) ) {  ?>

                        <?php
                        $rbfw_resort_data_mds = get_post_meta($post_id, 'rbfw_resort_data_mds', true) ? get_post_meta($post_id, 'rbfw_resort_data_mds', true) : [];
                        $rbfw_resort_data_sp = get_post_meta($post_id, 'rbfw_resort_data_sp', true) ? get_post_meta($post_id, 'rbfw_resort_data_sp', true) : [];




                        if(is_plugin_active( 'multi-day-price-saver-addon-for-wprently/additional-day-price.php' ) && !empty($rbfw_resort_data_mds)){  ?>
                            <td>
                                <?php
                                $rbfw_resort_data_mds = get_post_meta($post_id, 'rbfw_resort_data_mds', true) ? get_post_meta($post_id, 'rbfw_resort_data_mds', true) : [];
                                $room_price = 0;
                                if (($sp_price = check_seasonal_price_resort_mds($total_days, $rbfw_resort_data_mds, $value['room_type'], $active_tab)) != 0) {
                                    $room_price = (float)$sp_price;
                                }else{
                                    $room_price = (float)$price;
                                }
                                ?>
                                <?php echo wp_kses(wc_price($room_price) , rbfw_allowed_html()); ?>
                            </td>

                        <?php }elseif(is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) && !empty($rbfw_resort_data_sp)){ ?>

                            <td>
                                <?php
                                $book_dates = getAllDates( $start_date, $end_date );
                                $total_room_price =0;
                                for($d = 0; $d < $total_days; $d++) {
                                    if (($sp_price = check_seasonal_price_resort($book_dates[$d], $rbfw_resort_data_sp, $value['room_type'], $active_tab)) != 'not_found') {
                                        $total_room_price += (float)$sp_price;
                                    } else {
                                        $total_room_price += (float)$price;
                                    }
                                }
                                $room_price = $total_room_price/$total_days;
                                ?>
                                <p><?php esc_html_e('Avg price','booking-and-rental-manager-for-woocommerce'); ?>: <?php echo wp_kses(wc_price($total_room_price/$total_days) , rbfw_allowed_html()); ?></p>
                                <a class="rbfw_see_resort_datewise_price" data-checkin_date="<?php echo esc_attr($start_date) ?>" data-checkout_date="<?php echo esc_attr($end_date) ?>" data-total_days="<?php echo esc_attr($total_days) ?>" data-price="<?php echo esc_attr($price) ?>" data-post_id="<?php echo esc_attr( $post_id ); ?>" data-room_type="<?php echo esc_attr($value['room_type']) ?>" data-active_tab="<?php echo esc_attr($active_tab) ?>" href="#">
                                    <?php esc_html_e('See Details','booking-and-rental-manager-for-woocommerce'); ?>
                                </a>
                            </td>

                        <?php }else{ ?>
                            <td>
                                <?php echo wp_kses(wc_price($price) , rbfw_allowed_html()); ?>
                                <input type="hidden" name="rbfw_room_info[<?php echo esc_attr($i); ?>][room_price]" value="<?php echo esc_attr($price); ?>"/>
                            </td>
                        <?php } ?>


                    <?php }else{ ?>
                        <td>
                            <?php echo wp_kses(wc_price($price) , rbfw_allowed_html()); ?>
                            <input type="hidden" name="rbfw_room_info[<?php echo esc_attr($i); ?>][room_price]" value="<?php echo esc_attr($price); ?>"/>
                        </td>
                    <?php } ?>
                    <?php $price = isset($room_price)?$room_price:$price ?>
                    <td>
                        <?php if($max_available_qty){ ?>
                            <div class="rbfw_service_price_wrap">
                                <input type="hidden" value="<?php echo esc_attr($price); ?>" name="rbfw_room_info[<?php echo esc_attr($i); ?>][room_price]"/>
                                <div class="rbfw_qty_input">
                                    <a class="rbfw_qty_minus rbfw_room_qty_minus"><i class="fas fa-minus"></i></a>
                                    <input type="number" min="0" max="<?php echo esc_attr($max_available_qty) ?>" value="0" name="rbfw_room_info[<?php echo esc_attr($i); ?>][room_qty]" class="rbfw_room_qty" data-price="<?php echo esc_attr($price); ?>" data-type="<?php echo esc_attr($value['room_type']); ?>" data-active_tab="<?php echo esc_attr($active_tab); ?>" data-cat="room"/>
                                    <a class="rbfw_qty_plus rbfw_room_qty_plus"><i class="fas fa-plus"></i></a>
                                </div>
                            </div>
                        <?php }else{ ?>
                            <?php esc_html_e('Sold Out','booking-and-rental-manager-for-woocommerce'); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                $i++;
            }
            ?>
            </tbody>
        </table>
    </div>

    <?php if(!empty($rbfw_extra_service_data)) {  ?>
        <div class="rbfw_resort_es_price_table">
            <div class="rbfw-single-right-heading">
                <?php esc_html_e('Additional Services You may like.','booking-and-rental-manager-for-woocommerce'); ?>
            </div>
            <table class="rbfw_room_price_table">
                <thead>
                <tr>
                    <th><?php echo esc_html__( 'Service Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                    <th><?php echo esc_html__( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                    <th><?php echo esc_html__( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                    <th class="w_30_pc"><?php echo esc_html__( 'Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                </tr>
                </thead>
                <tbody>

                <?php

                $c = 0;
                foreach ($rbfw_extra_service_data as $key => $value) {
                    $max_es_available_qty = rbfw_get_multiple_date_es_available_qty($post_id, $start_date, $end_date, $value['service_name']);
                    $img_url = isset($value['service_img'])?wp_get_attachment_url($value['service_img']):'';
                    $uniq_id = wp_rand();
                    if ($img_url) {
                        $img = '<a href="#rbfw_room_img_' . $uniq_id . '" rel="mage_modal:open"><img src="' . esc_url($img_url) . '"/></a>';
                        $img .= '<div id="rbfw_room_img_' . $uniq_id . '" class="mage_modal"><img src="' . esc_url($img_url) . '"/></div>';
                    }
                    else {
                        $img = '';
                    }

                    if ($value['service_qty'] > 0) {  ?>
                        <tr>
                            <td>
                                <?php echo esc_html($value['service_name']); ?>
                                <input type="hidden" name="rbfw_service_info[<?php echo  esc_attr($c); ?>][service_name]" value="<?php echo esc_attr($value['service_name']); ?>"/>
                                <?php if (isset($value['service_desc']) && $value['service_desc']) { ?>
                                    <small class="rbfw_room_desc">
                                        <?php echo esc_html($value['service_desc']); ?>
                                    </small>
                                <?php } ?>
                                <?php if ($available_qty_info_switch == 'yes') { ?>
                                    <small class="rbfw_available_qty_notice"><?php echo esc_html__( 'Available:', 'booking-and-rental-manager-for-woocommerce' ) . esc_html($max_es_available_qty); ?></small>
                                <?php } ?>
                                <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_desc]" value="<?php echo esc_attr(isset($value['service_desc'])?$value['service_desc']:''); ?>"/>
                            </td>
                            <td>
                                <?php echo wp_kses($img , rbfw_allowed_html()); ?>
                            </td>
                            <td>
                                <?php echo wp_kses(wc_price($value['service_price']) , rbfw_allowed_html()); ?>
                                <input type="hidden" name="rbfw_service_info[<?php echo  esc_attr($c); ?>][service_price]" value="<?php echo esc_attr($value['service_price']); ?>"/>
                            </td>
                            <td>
                                <div class="rbfw_service_price_wrap">
                                    <div class="rbfw_qty_input">
                                        <a class="rbfw_qty_minus rbfw_service_qty_minus"><i class="fas fa-minus"></i></a>
                                        <input type="number" min="0" max="<?php echo esc_attr($max_es_available_qty) ?>" value="0" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_qty]" class="rbfw_service_qty_resort" data-price="<?php echo esc_attr($value['service_price']); ?>" data-type="<?php echo esc_attr($value['service_name']); ?>" data-cat="service"/>
                                        <a class="rbfw_qty_plus rbfw_service_qty_plus"><i class="fas fa-plus"></i></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    $c++;
                }
                ?>

                </tbody>
            </table>

        </div>

        <?php
    }
    ?>


    <?php
    $rbfw_fee_data = get_post_meta( $post_id, 'rbfw_fee_data', true );

    //echo '<pre>';print_r($rbfw_fee_data);

    ?>

    <?php if(!empty($rbfw_fee_data)){ ?>
        <div class="item rbfw_resort_es_price_table">
            <div class="rbfw-single-right-heading">
                <?php esc_html_e('Fee Management','booking-and-rental-manager-for-woocommerce'); ?>
            </div>
            <div class="item-content rbfw-resource">
                <table class="rbfw_bikecarmd_es_table">
                    <tbody>
                    <?php
                    $c = 0;
                    //echo '<pre>';print_r($rbfw_fee_data);echo '<pre>';
                    $rbfw_management_price = 0;
                    foreach ($rbfw_fee_data as $key=>$fee) { ?>
                        <?php if(isset($fee['label'])){ ?>
                            <tr>
                                <td class="w_20 rbfw_bikecarmd_es_hidden_input_box">
                                    <div class="label rbfw-checkbox">
                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][label]" value="<?php echo esc_attr($fee['label']); ?>">
                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][is_checked]" class="rbfw-management-qty" value="<?php echo (esc_attr($fee['priority'])=='required')?'yes':'' ?>">
                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][amount]"  value="<?php echo esc_attr($fee['amount']); ?>">
                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][calculation_type]"  value="<?php echo esc_attr($fee['calculation_type']); ?>">
                                        <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][frequency]"  value="<?php echo esc_attr($fee['frequency']); ?>">
                                        <label class="switch">
                                            <input type="checkbox" <?php echo (esc_attr($fee['priority'])=='required')?'checked':'' ?>   class="rbfw-management-price-resort rbfw-resource-price-multiple-qty key_value_<?php echo esc_attr($key+1); ?>"   data-price="<?php echo esc_attr($fee['amount']); ?>" data-name="<?php echo esc_attr($fee['label']); ?>" data-price_type="<?php echo esc_attr($fee['calculation_type']); ?>" data-frequency="<?php echo esc_attr($fee['frequency']); ?>">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </td>
                                <td class="resource-title-qty">
                                    <?php echo esc_html($fee['label']); ?>
                                    <?php
                                    if($fee['frequency']=='one-time'){
                                        echo 'One Time';
                                    }else{
                                        echo 'Day Wise';
                                    }
                                    ?>
                                </td>
                                <td class="w_20">
                                    <?php if($fee['calculation_type']=='fixed'){
                                        echo wp_kses(wc_price($fee['amount']),rbfw_allowed_html());
                                    }else{
                                        echo $fee['amount'].'%';
                                    }
                                    ?>
                                </td>
                                <?php
                                if(esc_attr($fee['priority'])=='required'){
                                    $rbfw_management_price +=  $fee['amount'];
                                }
                                ?>
                            </tr>
                        <?php } ?>
                        <?php $c++; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>




<div class="item rbfw_room_price_summary">
    <div class="item-content rbfw-costing">
        <ul class="rbfw-ul">
            <li class="duration-costing rbfw-cond">
                <span>
                    <?php echo esc_html__( 'Duration Cost','booking-and-rental-manager-for-woocommerce' ); ?>
                    <span class="rbfw_pricing_applied">
                        <?php if(get_transient("pricing_applied")=='sessional'){ ?>
                            (<?php esc_html_e( 'Sessional pricing applied', 'booking-and-rental-manager-for-woocommerce' ); ?>)
                        <?php }elseif (get_transient("pricing_applied")=='mds'){ ?>
                            (<?php esc_html_e( 'Multi day pricing saver applied', 'booking-and-rental-manager-for-woocommerce' ); ?>)
                        <?php } ?>
                    </span>
                </span>
                <span><span class="price-figure" data-price="0"><?php echo esc_html($currency_symbol); ?>0</span></span>
            </li>

            <li class="resource-costing rbfw-cond">
                <?php echo esc_html__( 'Resource Cost','booking-and-rental-manager-for-woocommerce' ); ?>
                <span class="price-figure" data-price="0">
                    <?php echo wp_kses_post(wc_price(0)); ?>
                </span>
            </li>

            <li class="subtotal">
                <?php echo esc_html__( 'Subtotal','booking-and-rental-manager-for-woocommerce' ); ?>
                <span class="price-figure">
                    <?php echo wp_kses_post(wc_price(0)); ?>
                </span>
            </li>

            <li class="management-costing rbfw-cond">
                <?php echo esc_html__( 'Management Fee','booking-and-rental-manager-for-woocommerce' ); ?>
                <span class="price-figure" data-price="0">
                    <?php echo wp_kses_post(wc_price(0)); ?>
                </span>
            </li>

            <li class="security_deposit" style="display:none;">
                <?php echo esc_html((!empty(get_post_meta($post_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($post_id, 'rbfw_security_deposit_label', true) : __('Security Deposit','booking-and-rental-manager-for-woocommerce'))); ?>
                <span></span>
            </li>

            <li class="total">
                <strong><?php echo esc_html__( 'Total','booking-and-rental-manager-for-woocommerce' ); ?></strong>
                <span class="price-figure"><?php echo wp_kses_post(wc_price(0)); ?>
                </span>
            </li>
        </ul>

        <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
    </div>
</div>
    <?php
/* Include Custom Registration Form */
if(class_exists('Rbfw_Reg_Form')){
    $reg_form = new Rbfw_Reg_Form();
    echo wp_kses($reg_form->rbfw_generate_regf_fields($post_id),rbfw_allowed_html());
}
?>

    <input type="hidden" name="rbfw_room_duration_price" id="rbfw_room_duration_price" value="0"/>
    <input type="hidden" name="rbfw_extra_service_price" id="rbfw_extra_service_price" value="0"/>
    <input type="hidden" name="rbfw_management_price_resort" id="rbfw_management_price_resort" value="0"/>

    <div class="item rbfw_text_book_now">
        <button type="submit" name="add-to-cart" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_resort_book_now_btn rbfw_disabled_button" disabled>
            <?php echo esc_html__( 'Book Now','booking-and-rental-manager-for-woocommerce' ); ?>
        </button>
    </div>
<?php }else{ ?>
    <div class="rbfw_alert_warning">
        <i class="fas fa-circle-info"></i>
        <?php echo esc_html__("Sorry, the day-night package is not available on the same check-in and check-out date.","booking-and-rental-manager-for-woocommerce") ?>
    </div>
<?php } ?>