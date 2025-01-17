<?php
global $rbfw;

if (!(isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rbfw_ajax_action'))) {
    return;
}

if(isset($_POST['post_id'])){
    $id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';

    $selected_time = isset($_POST['selected_time']) ? sanitize_text_field(wp_unslash($_POST['selected_time'])) : '';

    $is_muffin_template = isset($_POST['is_muffin_template']) ? sanitize_text_field(wp_unslash($_POST['is_muffin_template'])) : '';

    $rbfw_bike_car_sd_data = get_post_meta($id, 'rbfw_bike_car_sd_data', true) ? get_post_meta($id, 'rbfw_bike_car_sd_data', true) : [];
    $rbfw_extra_service_data = get_post_meta($id, 'rbfw_extra_service_data', true) ? get_post_meta($id, 'rbfw_extra_service_data', true) : [];



    $rbfw_product_id = get_post_meta($id, 'link_wc_product', true) ? get_post_meta($id, 'link_wc_product', true) : $id;

    $selected_date = isset($_POST['selected_date']) ? sanitize_text_field(wp_unslash($_POST['selected_date'])) : '';

    $available_times = rbfw_get_available_times($id);

    $default_timezone = wp_timezone_string();
    $date = new DateTime("now", new DateTimeZone($default_timezone));
    $nowTime  = $date->format('H:i');
    $nowDate  = $date->format('Y-m-d');

    $date_to_string = new DateTime($selected_date);
    $result = $date_to_string->format(get_option('date_format'));

    $available_qty_info_switch = get_post_meta($id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($id, 'rbfw_available_qty_info_switch', true) : 'no';
    $enable_service_price_sd = get_post_meta($id, 'rbfw_enable_category_service_price_sd', true) ? get_post_meta($id, 'rbfw_enable_category_service_price_sd', true) : 'off';
?>


<div class="rbfw_bikecarsd_pricing_table_container rbfw-bikecarsd-step" data-step="3">
    <a class="rbfw_back_step_btn" back-step="2" data-step="3"><i class="fas fa-circle-left"></i>
       <?php echo esc_html(rbfw_string_return('rbfw_text_back_to_previous_step',__('Back to Previous Step','booking-and-rental-manager-for-woocommerce')));  ?>
    </a>

    <?php if($is_muffin_template == 0){ ?>
        <div class="rbfw_step_selected_date" data-time="<?php echo esc_html($selected_time); ?>">
            <i class="fas fa-calendar-check"></i>
            <?php echo esc_html(rbfw_string_return('rbfw_text_you_selected',__('You selected','booking-and-rental-manager-for-woocommerce'))) ?>: <?php echo esc_html($result.' '.$selected_time); ?>
        </div>
    <?php } ?>

    <?php if($is_muffin_template == 1){ ?>
        <div class="rbfw_step_selected_date rbfw_muff_selected_date" step="3">
            <div class="rbfw_muff_selected_date_col">
                <label><i class="fa-regular fa-calendar-days"></i> <?php echo esc_html(rbfw_string_return('rbfw_text_date',__('Date','booking-and-rental-manager-for-woocommerce'))); ?></label>
                <span class="rbfw_muff_selected_date_value"><?php echo esc_html($result); ?></span>
            </div>
            <div class="rbfw_muff_selected_date_col">
                <label><i class="fa-regular fa-clock"></i> <?php echo esc_html(rbfw_string_return('rbfw_text_time',__('Time','booking-and-rental-manager-for-woocommerce'))); ?></label>
                <span class="rbfw_muff_selected_date_value"><?php echo esc_html(gmdate(get_option('time_format'), strtotime($selected_time)));  ?></span>
            </div>
        </div>
    <?php } ?>




    <div class="">
       <?php

            if(($enable_service_price_sd=='on')){

                $option_value  = get_post_meta($id, 'rbfw_service_category_price_sd', true);
                $option_value  = is_serialized($option_value) ? unserialize($option_value) : $option_value;
                if(!empty($option_value)){  ?>
                    <div class="multi-service-category-section">
                        <div class="rbfw-single-right-heading">
                            <?php esc_html_e( 'Category wise service price', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </div>

                        <div class="tab">
                            <?php foreach ($option_value as $cat=>$item){ ?>
                                <?php if($item['cat_title']){ ?>
                                    <button class="tablinks" onclick="openCity(event, 'service_item_<?php echo esc_html($cat); ?>')"><?php echo esc_html($item['cat_title']); ?></button>
                                    <input type="hidden" name="rbfw_service_price_data[<?php echo esc_html($cat); ?>][cat_title]" value="<?php echo esc_html($item['cat_title']); ?>">
                                    <?php
                                }
                            }
                            ?>
                        </div>

                                <?php foreach ($option_value as $cat=>$item){ ?>
                                    <?php if($item['cat_title']){ ?>
                                        <div id="service_item_<?php echo esc_html($cat); ?>" class="tabcontent">
                                            <?php foreach ($item['cat_services'] as $serkey=>$service){ ?>
                                                <?php if($service['title']){ ?>

                                                        <div class="rbfw_multi_services">
                                                            <div class="service-price-item" style="display: flex;gap:20px">
                                                                <div style="display: none" class="rbfw-sold-out">
                                                                    Sold Out
                                                                </div>

                                                                <div class="rbfw-checkbox">
                                                                    <label class="switch">
                                                                        <input type="checkbox" class="rbfw_service_price_data_sd item_<?php echo esc_html($cat.$serkey); ?>" name="rbfw_service_price_data[<?php echo esc_html($cat); ?>][<?php echo esc_html($serkey); ?>][main_cat_name]" data-service_price_type="<?php echo esc_html($service['service_price_type']); ?>" data-price="<?php echo esc_html($service['price']); ?>" data-quantity="1" data-rbfw_enable_md_type_item_qty="<?php echo esc_html($rbfw_enable_extra_service_qty); ?>" data-item="<?php echo esc_html($cat.$serkey); ?>">
                                                                        <span class="slider round"></span>
                                                                    </label>
                                                                    <input type="hidden" name="rbfw_service_price_data[<?php echo esc_html($cat); ?>][<?php echo esc_html($serkey); ?>][name]" value="<?php echo esc_html($service['title']); ?>">
                                                                    <input type="hidden" name="rbfw_service_price_data[<?php echo esc_html($cat); ?>][<?php echo esc_html($serkey); ?>][service_price_type]" value="<?php echo esc_html($service['service_price_type']); ?>">
                                                                    <input type="hidden" name="rbfw_service_price_data[<?php echo esc_html($cat); ?>][<?php echo esc_html($serkey); ?>][price]" value="<?php echo esc_html($service['price']); ?>">
                                                                </div>

                                                                <div class="title">
                                                                    <?php echo esc_html($service['title']); ?> <span class="remaining_stock"></span>
                                                                </div>
                                                                <div  class="rbfw_qty_input rbfw_service_quantity item_<?php echo esc_html($cat.$serkey); ?>">
                                                                    <a class="rbfw_service_quantity_minus_sd" data-item="<?php echo esc_html($cat.$serkey); ?>"><i class="fas fa-minus"></i></a>
                                                                    <input type="number"  name="rbfw_service_price_data[<?php echo esc_html($cat); ?>][<?php echo esc_html($serkey); ?>][quantity]" min="0"  value="1" class="rbfw_servicesd_qty rbfw_service_info_stock" data-cat="service" data-price="20" data-item="<?php echo esc_html($cat.$serkey); ?>" data-name="ddd" autocomplete="off">
                                                                    <a class="rbfw_service_quantity_plus_sd" data-item="<?php echo esc_html($cat.$serkey); ?>"><i class="fas fa-plus"></i></a>
                                                                </div>
                                                                <div class="title"><?php echo wp_kses(wc_price($service['price']),rbfw_allowed_html()); ?></div>
                                                            </div>
                                                        </div>

                                                <?php }
                                             } ?>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                    </div>

                <?php }
            }else{ ?>



                    <table class="rbfw_bikecarsd_price_table rbfw_bikecarsd_rt_price_table">
                        <thead>
                        <tr>
                            <th class="w_50_pc">
                                <?php echo esc_html(rbfw_string_return('rbfw_text_rent_type',__('Type','booking-and-rental-manager-for-woocommerce'))); ?>
                            </th>
                            <th class="w_30_pc">
                                <?php echo esc_html(rbfw_string_return('rbfw_text_price',__('Price','booking-and-rental-manager-for-woocommerce'))); ?>
                            </th>
                            <th data-booked_message="<?php echo esc_attr__( 'Available Quantity is ', 'booking-and-rental-manager-for-woocommerce' ) ?>" class="w_20_pc">
                                <?php echo esc_html(rbfw_string_return('rbfw_text_quantity',__('Quantity','booking-and-rental-manager-for-woocommerce'))); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php

                        $i = 1;
                        foreach ($rbfw_bike_car_sd_data as $value) {
                            $max_available_qty = rbfw_get_bike_car_sd_available_qty($id, $selected_date, $value['rent_type'], $selected_time);
                            if($value['qty'] > 0){
                                ?>
                                <tr>
                                    <td class="w_50_pc">
                                        <span class="rbfw_bikecarsd_type_title"><?php echo esc_html($value['rent_type']); ?></span>
                                        <small class="rbfw_bikecarsd_type_desc"><?php echo esc_html($value['short_desc']); ?></small>
                                        <?php  if($available_qty_info_switch == 'yes'){ ?>
                                            <small class="rbfw_available_qty_notice">(<?php echo esc_html(rbfw_string_return('rbfw_text_available',__('Available:','booking-and-rental-manager-for-woocommerce'))).esc_html($max_available_qty); ?>)</small>
                                        <?php } ?>
                                        <input type="hidden" name="rbfw_bikecarsd_info[<?php echo esc_html($i); ?>][rent_type]" value="<?php echo esc_html($value['rent_type']); ?>"/>
                                        <input type="hidden" name="rbfw_bikecarsd_info[<?php echo esc_html($i); ?>][short_desc]" value="<?php echo esc_html($value['short_desc']); ?>"/>
                                    </td>

                                    <td class="w_30_pc">
                                        <span class="rbfw_bikecarsd_type_price"><?php echo wp_kses(rbfw_mps_price($value['price']),rbfw_allowed_html()); ?></span>
                                    </td>
                                    <input type="hidden" name="rbfw_bikecarsd_info[<?php echo esc_html($i); ?>][price]" value="<?php echo esc_html($value['price']); ?>"/>
                                    <td class="w_20_pc">
                                        <div class="rbfw_service_price_wrap">
                                            <div class="rbfw_qty_input">
                                                <?php if($max_available_qty){ ?>
                                                    <a class="rbfw_qty_minus rbfw_bikecarsd_qty_minus"><i class="fas fa-minus"></i></a>
                                                    <input type="number" min="0" max="<?php echo esc_html($max_available_qty); ?>" value="0" name="rbfw_bikecarsd_info[<?php echo esc_html($i) ?>][qty]" class="rbfw_bikecarsd_qty" data-price="<?php echo esc_html($value['price']); ?>" data-type="<?php echo esc_html($value['rent_type']); ?>" data-cat="bikecarsd" />
                                                    <a class="rbfw_qty_plus rbfw_bikecarsd_qty_plus"><i class="fas fa-plus"></i></a>
                                                <?php }else{ ?>
                                                    <div style="width: 120px">Sold Out</div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php $i++;
                            }
                        } ?>
                        </tbody>
                    </table>



                <?php
            }
            ?>


        <?php if(!empty($rbfw_extra_service_data)){ ?>

                <div class="rbfw_bikecarsd_es_price_table">
                    <div class="rbfw-single-right-heading">
                        <?php esc_html_e('Additional Services You may like.','booking-and-rental-manager-for-woocommerce'); ?>
                    </div>
                    <table class="rbfw_bikecarsd_price_table">
                        <thead>
                        <tr>
                            <th class="w_50_pc"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_service_name', 'rbfw_basic_translation_settings', __('Service Name','booking-and-rental-manager-for-woocommerce'))); ?></th>
                            <th class="w_30_pc"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Price','booking-and-rental-manager-for-woocommerce'))); ?></th>
                            <th class="w_20_pc"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce'))); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $c = 0;
                        foreach ($rbfw_extra_service_data as $value) {
                            $img_url = !empty($value['service_img']) ? wp_get_attachment_url($value['service_img']) : '';
                            $uniq_id = wp_rand();
                            if ($img_url) {
                                $img = '<a href="#rbfw_service_img_<?php echo $uniq_id ?>" rel="mage_modal:open"><img src="' . esc_url($img_url) . '"/></a>';
                                $img .= '<div id="rbfw_service_img_' . $uniq_id . '" class="mage_modal"><img src="<?php echo esc_url($img_url) ?>"/></div>';
                            }else{
                                $img = '';
                            }

                            $max_es_available_qty = rbfw_get_bike_car_sd_es_available_qty($id, $selected_date, $value['service_name']);

                            if($value['service_qty'] > 0){
                                ?>
                                <tr>
                                    <td class="w_50_pc">
                                        <div>
                                            <?php echo wp_kses($img,rbfw_allowed_html()); ?>
                                        </div>
                                        <div>
                                            <span class="rbfw_bikecarsd_type_title"><?php echo esc_html($value['service_name']); ?></span>
                                            <?php if(!empty($value['service_desc'])){ ?>
                                                <small class="rbfw_bikecarsd_type_desc"><?php echo esc_html($value['service_desc']); ?></small>
                                            <?php } ?>
                                            <?php if($available_qty_info_switch == 'yes'){ ?>
                                                <small class="rbfw_available_qty_notice">(<?php echo esc_html(rbfw_string_return('rbfw_text_available',__('Available:','booking-and-rental-manager-for-woocommerce'))).esc_html($max_es_available_qty); ?>)</small>
                                            <?php } ?>
                                            <input type="hidden" name="rbfw_service_info[<?php echo esc_html($c) ?>][service_name]" value="<?php echo esc_html($value['service_name']); ?>"/>
                                        </div>
                                    </td>
                                    <td class="w_30_pc">
                                        <?php echo wp_kses(rbfw_mps_price($value['service_price']),rbfw_allowed_html()); ?>
                                    </td>
                                    <td class="w_20_pc">
                                        <div class="rbfw_service_price_wrap">
                                            <input type="hidden" name="rbfw_service_info[<?php echo esc_html($c) ?>][service_price]" value="<?php echo esc_html($value['service_price']); ?>"/>
                                            <div class="rbfw_qty_input">
                                                <?php if($max_es_available_qty){ ?>
                                                    <a class="rbfw_qty_minus rbfw_servicesd_qty_minus"><i class="fas fa-minus"></i></a>
                                                    <input type="number" min="0" max="<?php echo esc_attr($max_es_available_qty) ?>" value="0" name="rbfw_service_info[<?php echo esc_html($c) ?>][service_qty]" class="rbfw_servicesd_qty" data-price="<?php echo esc_html($value['service_price']); ?>" data-type="<?php echo esc_html($value['service_name']); ?>" data-cat="service"/>
                                                    <a class="rbfw_qty_plus rbfw_servicesd_qty_plus"><i class="fas fa-plus"></i></a>
                                                <?php }else{ ?>
                                                    <div style="width: 120px"><?php echo esc_html('Sold Out','booking-and-rental-manager-for-woocommerce'); ?></div>
                                                <?php } ?>
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
        <div class="item rbfw_bikecarsd_price_summary">
            <div class="item-content rbfw-costing">
                <ul class="rbfw-ul">
                    <li class="duration-costing rbfw-cond">
                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                        <?php echo wp_kses(wc_price(0),rbfw_allowed_html()); ?>
                    </li>
                    <?php if(!empty($rbfw_extra_service_data)){ ?>
                        <li class="resource-costing rbfw-cond">
                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                            <?php echo wp_kses(wc_price(0),rbfw_allowed_html()); ?>
                        </li>
                    <?php } ?>
                    <li class="subtotal">
                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce'))); ?>
                        <?php echo wp_kses(wc_price(0),rbfw_allowed_html()); ?>
                    </li>
                    <li class="total">
                        <strong><?php echo esc_html($rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce'))); ?></strong>
                        <?php echo wp_kses(wc_price(0),rbfw_allowed_html()); ?>
                    </li>
                </ul>
                <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
            </div>
        </div>
    </div>

    <?php

    /* Include Custom Registration Form */
    if(class_exists('Rbfw_Reg_Form')){
        $reg_form = new Rbfw_Reg_Form();
        echo wp_kses($reg_form->rbfw_generate_regf_fields($id),rbfw_allowed_html());
    }
    /* End: Include Custom Registration Form */

    ?>
</div>

<?php

}

?>