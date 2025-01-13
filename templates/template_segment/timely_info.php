<?php
global $rbfw;

if (isset($_POST['post_id']) && check_admin_referer('rbfw_nonce_action', 'rbfw_nonce_field')) { // Nonce verification
    $id = isset($_POST['post_id']) ? intval(wp_unslash($_POST['post_id'])) : 0;
    $service_price = isset($_POST['service_price']) ? sanitize_text_field(wp_unslash($_POST['service_price'])) : '';
    $start_date = isset($_POST['rbfw_bikecarsd_selected_date']) ? sanitize_text_field(wp_unslash($_POST['rbfw_bikecarsd_selected_date'])) : '';
    $start_time = isset($_POST['start_time']) ? sanitize_text_field(wp_unslash($_POST['start_time'])) : '';
    $service_type = isset($_POST['service_type']) ? sanitize_text_field(wp_unslash($_POST['service_type'])) : '';
    $duration = isset($_POST['duration']) ? intval(wp_unslash($_POST['duration'])) : 0;
    $d_type = isset($_POST['d_type']) ? sanitize_text_field(wp_unslash($_POST['d_type'])) : '';
    $available_quantity = isset($_POST['available_quantity']) ? intval(wp_unslash($_POST['available_quantity'])) : 0;
    $enable_specific_duration = isset($_POST['enable_specific_duration']) ? sanitize_text_field(wp_unslash($_POST['enable_specific_duration'])) : '';

    $start_date_time = new DateTime($start_date.' '.$start_time);

    if ($enable_specific_duration == 'on') {
        $end_date = $start_date;
        $end_time = isset($_POST['end_time']) ? sanitize_text_field(wp_unslash($_POST['end_time'])) : '';
    } else {
        $total_hours = ($d_type == 'Hours' ? $duration : ($d_type == 'Days' ? $duration * 24 : $duration * 24 * 7));
        $start_date_time->modify("+$total_hours hours");
        $end_date = $start_date_time->format('Y-m-d');
        $end_time = $start_date_time->format('H:i:s');
    }

    $duration_cost = $service_price;

    $rbfw_extra_service_data = get_post_meta($id, 'rbfw_extra_service_data', true) ? get_post_meta($id, 'rbfw_extra_service_data', true) : [];
    $available_qty_info_switch = get_post_meta($id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($id, 'rbfw_available_qty_info_switch', true) : 'no';

}

    ?>

    <div class="rbfw_bikecarsd_pricing_table_container">
        <div class="">
            <table class="rbfw_bikecarsd_price_table timely_quqntity_table" cellspacing="0" cellpadding="0" style="width: 100%">
                <tbody>
                <tr>
                    <td class="w_30_pc">
                        <div>
                            <span class="rbfw_bikecarsd_type_title">
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_quantity', 'rbfw_basic_translation_settings', __('Quantity','booking-and-rental-manager-for-woocommerce'))); ?>
                            </span>
                        </div>
                    </td>
                    <td class="w_30_pc">
                        <div class="rbfw_regf_group">
                            <select name="rbfw_item_quantity" id="rbfw_item_quantity">
                                <?php for ($qty = 1; $qty <= $available_quantity; $qty++) { ?>
                                    <option value="<?php echo esc_html($qty); ?>">
                                        <?php echo esc_html($qty); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo esc_html_e('x','booking-and-rental-manager-for-woocommerce') . esc_html(wc_price($service_price)); ?>
                        </div>
                    </td>

                </tr>

                </tbody>
            </table>
        </div>
    </div>


    <input type="hidden" name="end_date" value="<?php echo esc_html($end_date); ?>">
    <input type="hidden" name="end_time" value="<?php echo esc_html($end_time); ?>">
    <input type="hidden" name="service_type" value="<?php echo esc_attr($service_type) ?>">
    <div class="rbfw_bikecarsd_pricing_table_container rbfw-bikecarsd-step">
        <div class="">
            <?php if(!empty($rbfw_extra_service_data)){ ?>
                    <div class="rbfw-single-right-heading"><?php esc_html_e('Additional Services You may like.','booking-and-rental-manager-for-woocommerce'); ?></div>
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

                        $max_es_available_qty = rbfw_get_bike_car_sd_es_available_qty($id, $start_date, $value['service_name']);

                        if($value['service_qty'] > 0){
                            ?>
                            <tr>
                                <td class="w_50_pc">
                                    <div>
                                        <?php echo esc_html($img); ?>
                                    </div>
                                    <div>
                                        <span class="rbfw_bikecarsd_type_title"><?php echo esc_html($value['service_name']); ?></span>
                                        <?php if(!empty($value['service_desc'])){ ?>
                                            <small class="rbfw_bikecarsd_type_desc"><?php echo esc_html($value['service_desc']); ?></small>
                                        <?php } ?>
                                        <?php if($available_qty_info_switch == 'yes'){ ?>
                                            <small class="rbfw_available_qty_notice">(<?php echo esc_html(rbfw_string_return('rbfw_text_available',__('Available:','booking-and-rental-manager-for-woocommerce'))).esc_html($max_es_available_qty); ?>)</small>
                                        <?php } ?>
                                        <input type="hidden" name="rbfw_service_info[<?php echo esc_html($c); ?>][service_name]" value="<?php echo esc_html($value['service_name']); ?>"/>
                                    </div>
                                </td>
                                <td class="w_30_pc">
                                    <?php echo esc_html(rbfw_mps_price($value['service_price'])); ?>
                                </td>
                                <td class="w_20_pc">
                                    <div class="rbfw_service_price_wrap">
                                        <input type="hidden" name="rbfw_service_info[<?php echo esc_html($c); ?>][service_price]" value="<?php echo esc_html($value['service_price']); ?>"/>
                                        <div class="rbfw_qty_input">
                                            <?php if($max_es_available_qty){ ?>
                                                <a class="rbfw_qty_minus rbfw_timely_es_qty_minus"><i class="fa-solid fa-minus"></i></a>
                                                <input type="number" min="0" max="<?php echo esc_attr($max_es_available_qty) ?>" value="0" name="rbfw_service_info[<?php echo esc_html($c); ?>][service_qty]" class="rbfw_timely_es_qty" data-price="<?php echo esc_html($value['service_price']); ?>" data-type="<?php echo esc_html($value['service_name']); ?>" data-cat="service"/>
                                                <a class="rbfw_qty_plus rbfw_timely_es_qty_plus"><i class="fa-solid fa-plus"></i></a>
                                            <?php }else{ ?>
                                                <div style="width: 120px">Sold Out</div>
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
            <?php } ?>
            <div class="item rbfw_bikecarsd_price_summary_only">
                <div class="item-content rbfw-costing">
                    <ul class="rbfw-ul">
                        <li class="duration-costing rbfw-cond">
                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce'))) ?>
                            <?php echo esc_html(wc_price($duration_cost)); ?>
                        </li>
                        <?php if(!empty($rbfw_extra_service_data)){ ?>
                            <li class="resource-costing rbfw-cond">
                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                                <?php echo esc_html(wc_price(0)); ?>
                            </li>
                        <?php } ?>
                        <li class="subtotal">
                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce'))); ?>
                            <?php echo esc_html(wc_price($duration_cost)); ?>
                        </li>
                        <?php
                        $security_deposit = rbfw_security_deposit($id,$duration_cost);

                        if($security_deposit['security_deposit_desc']){ ?>
                            <li class="subtotal">
                                <?php echo esc_html((!empty(get_post_meta($id, 'rbfw_security_deposit_label', true)) ? get_post_meta($id, 'rbfw_security_deposit_label', true) : 'Security Deposit')); ?>
                                <?php echo esc_html(wc_price($security_deposit['security_deposit_amount'])); ?>
                            </li>
                        <?php }

                        $total_price = $duration_cost + $security_deposit['security_deposit_amount'];

                        ?>
                        <li class="total">
                            <strong><?php echo esc_html($rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce'))); ?></strong>
                            <?php echo esc_html(wc_price($total_price)); ?>
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
            echo esc_html($reg_form->rbfw_generate_regf_fields($id));
        }
        /* End: Include Custom Registration Form */
        ?>
    </div>
    
<?php } ?>