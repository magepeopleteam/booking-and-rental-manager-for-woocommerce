<?php
global $rbfw;
if ( !isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action') ) {
    wp_die('Nonce verification failed.');
}


if ( !($post_id && $active_tab) ) {
    $post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
    $checkin_date = isset($_POST['checkin_date']) ? sanitize_text_field(wp_unslash($_POST['checkin_date'])) : '';
    $checkout_date = isset($_POST['checkout_date']) ? sanitize_text_field(wp_unslash($_POST['checkout_date'])) : '';
}
if(isset($post_id) && isset($active_tab)){


    $origin             = date_create($checkin_date);
    $target             = date_create($checkout_date);
    $interval           = date_diff($origin, $target);
    $total_days         = $interval->format('%a');
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

    <div class="rbfw_resort_rt_price_table_container">
        <table class="rbfw_room_price_table rbfw_resort_rt_price_table">
            <thead>
            <tr>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_type', 'rbfw_basic_translation_settings', __('Room Type','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_image', 'rbfw_basic_translation_settings', __('Image','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_price', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce'))); ?></th>
                <th class="w_30_pc"> <?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_qty', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce'))); ?></th>
            </tr>
            </thead>
            <tbody>

            <?php
            $i = 0;

            setcookie("pricing_applied", "No");

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
                    $max_available_qty = rbfw_get_multiple_date_available_qty($post_id, $checkin_date, $checkout_date, $value['room_type'], '', '');
                    $max_available_qty = $max_available_qty['remaining_stock'];
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
                                <small class="rbfw_available_qty_notice">(<?php echo  esc_attr(rbfw_string_return('rbfw_text_available', __('Available:', 'booking-and-rental-manager-for-woocommerce'))) . esc_html($max_available_qty); ?>)</small>
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
                                $book_dates = getAllDates( $checkin_date, $checkout_date );
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
                                <a class="rbfw_see_resort_datewise_price" data-checkin_date="<?php echo esc_attr($checkin_date) ?>" data-checkout_date="<?php echo esc_attr($checkout_date) ?>" data-total_days="<?php echo esc_attr($total_days) ?>" data-price="<?php echo esc_attr($price) ?>" data-post_id="<?php echo esc_attr( $post_id ); ?>" data-room_type="<?php echo esc_attr($value['room_type']) ?>" data-active_tab="<?php echo esc_attr($active_tab) ?>" href="#">
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
                        <div class="rbfw_service_price_wrap">
                            <input type="hidden" value="<?php echo esc_attr($price); ?>" name="rbfw_room_info[<?php echo esc_attr($i); ?>][room_price]"/>
                            <div class="rbfw_qty_input">
                                <a class="rbfw_qty_minus rbfw_room_qty_minus"><i class="fas fa-minus"></i></a>
                                <input type="number" min="0" max="<?php echo esc_attr($max_available_qty) ?>" value="0" name="rbfw_room_info[<?php echo esc_attr($i); ?>][room_qty]" class="rbfw_room_qty" data-price="<?php echo esc_attr($price); ?>" data-type="<?php echo esc_attr($value['room_type']); ?>" data-active_tab="<?php echo esc_attr($active_tab); ?>" data-cat="room"/>
                                <a class="rbfw_qty_plus rbfw_room_qty_plus"><i class="fas fa-plus"></i></a>
                            </div>
                        </div>
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
                <div class="rbfw-single-right-heading"><?php esc_html_e('Additional Services You may like.','booking-and-rental-manager-for-woocommerce'); ?></h5>
                <table class="rbfw_room_price_table">
                    <thead>
                    <tr>
                        <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_service_name', 'rbfw_basic_translation_settings', __('Service Name', 'booking-and-rental-manager-for-woocommerce'))); ?></th>
                        <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_image', 'rbfw_basic_translation_settings', __('Image', 'booking-and-rental-manager-for-woocommerce'))); ?></th>
                        <th><?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_service_price', 'rbfw_basic_translation_settings', __('Price', 'booking-and-rental-manager-for-woocommerce'))); ?></th>
                        <th class="w_30_pc"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_room_service_qty', 'rbfw_basic_translation_settings', __('Quantity', 'booking-and-rental-manager-for-woocommerce'))); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php

                    $c = 0;
                    foreach ($rbfw_extra_service_data as $key => $value) {
                        $max_es_available_qty = rbfw_get_multiple_date_es_available_qty($post_id, $checkin_date, $checkout_date, $value['service_name']);
                        $img_url = wp_get_attachment_url($value['service_img']);
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
                                        <small class="rbfw_available_qty_notice">(<?php echo esc_html(rbfw_string_return('rbfw_text_available', __('Available:', 'booking-and-rental-manager-for-woocommerce'))) . esc_html($max_es_available_qty); ?>)</small>
                                    <?php } ?>
                                    <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_desc]" value="<?php echo esc_attr($value['service_desc']); ?>"/>
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




<div class="item rbfw_room_price_summary">
    <div class="item-content rbfw-costing">
        <ul class="rbfw-ul">
            <li class="duration-costing rbfw-cond">
                <span>
                    <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                    <span class="rbfw_pricing_applied">
                        <?php if($_COOKIE['pricing_applied']=='sessional'){ ?>
                            (<?php esc_html_e( 'Sessional pricing applied', 'booking-and-rental-manager-for-woocommerce' ); ?>)
                        <?php }elseif ($_COOKIE['pricing_applied']=='mds'){ ?>
                            (<?php esc_html_e( 'Multi day pricing saver applied', 'booking-and-rental-manager-for-woocommerce' ); ?>)
                        <?php } ?>
                    </span>
                </span>
                <span><span class="price-figure" data-price="0"><?php echo esc_html($currency_symbol); ?>0</span></span>
            </li>
            <li class="resource-costing rbfw-cond"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce'))); ?>  <span><span class="price-figure" data-price="0"><?php echo wp_kses_post(wc_price(0)); ?></span></span></li>
            <li class="subtotal"> <?php echo esc_html($rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce'))); ?><span><span class="price-figure"><?php echo wp_kses_post(wc_price(0)); ?></span></span></li>
            <li class="total"><strong><?php echo esc_html($rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce'))); ?></strong> <span><span class="price-figure"><?php echo wp_kses_post(wc_price(0)); ?></span></span></li>
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

    <div class="item rbfw_text_book_now">
        <button type="submit" name="add-to-cart" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_resort_book_now_btn rbfw_disabled_button" disabled>
            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce'))); ?>
        </button>
    </div>
<?php }else{ ?>
    <div class="rbfw_alert_warning">
        <i class="fas fa-circle-info"></i>
        <?php echo esc_html__("Sorry, the day-night package is not available on the same check-in and check-out date.","booking-and-rental-manager-for-woocommerce") ?>
    </div>
<?php } ?>