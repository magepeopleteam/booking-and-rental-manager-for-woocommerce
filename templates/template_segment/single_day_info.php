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


    $default_timezone = wp_timezone_string();
    $date = new DateTime("now", new DateTimeZone($default_timezone));
    $nowTime  = $date->format('H:i');
    $nowDate  = $date->format('Y-m-d');

    $date_to_string = new DateTime($selected_date);
    $result = $date_to_string->format(get_option('date_format'));

    $available_qty_info_switch = get_post_meta($id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($id, 'rbfw_available_qty_info_switch', true) : 'no';
    $enable_service_price_sd = get_post_meta($id, 'rbfw_enable_category_service_price_sd', true) ? get_post_meta($id, 'rbfw_enable_category_service_price_sd', true) : 'off';

    $rbfw_enable_extra_service_qty = get_post_meta( $id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $id, 'rbfw_enable_extra_service_qty', true ) : 'no';

    $manage_inventory_as_timely =  get_post_meta($id, 'manage_inventory_as_timely', true) ? get_post_meta($id, 'manage_inventory_as_timely', true) : 'off';
    $enable_specific_duration =  get_post_meta($id, 'enable_specific_duration', true) ? get_post_meta($id, 'enable_specific_duration', true) : 'off';
    $rbfw_time_slot_switch = !empty(get_post_meta($id,'rbfw_time_slot_switch',true)) ? get_post_meta($id,'rbfw_time_slot_switch',true) : 'off';
    $available_times = get_post_meta($id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($id, 'rdfw_available_time', true)) : [];


    if($rbfw_time_slot_switch == 'on' && !empty($available_times) && ($manage_inventory_as_timely=='on' && $enable_specific_duration =='off') ){
        update_post_meta($id,'rbfw_enable_time_picker','yes');
    }

    ?>


    <div class="rbfw_bikecarsd_pricing_table_container rbfw-bikecarsd-step" data-step="3">
        <a class="rbfw_back_step_btn" back-step="2" data-step="3"><i class="fas fa-circle-left"></i>
           <?php echo esc_html__( 'Back to Previous Step','booking-and-rental-manager-for-woocommerce' );  ?>
        </a>

        <?php if($is_muffin_template == 0){ ?>
            <div class="rbfw_step_selected_date" data-time="<?php echo esc_attr($selected_time); ?>">
                <i class="fas fa-calendar-check"></i>
                <?php echo esc_html__( 'You selected','booking-and-rental-manager-for-woocommerce' ) ?>: <?php echo esc_html($result.' '.$selected_time); ?>
            </div>
        <?php } ?>

        <?php if($is_muffin_template == 1){ ?>
            <div class="rbfw_step_selected_date rbfw_muff_selected_date" step="3">
                <div class="rbfw_muff_selected_date_col">
                    <label><i class="fa-regular fa-calendar-days"></i> <?php echo esc_html__( 'Date','booking-and-rental-manager-for-woocommerce' ); ?></label>
                    <span class="rbfw_muff_selected_date_value"><?php echo esc_html($result); ?></span>
                </div>
                <div class="rbfw_muff_selected_date_col">
                    <label><i class="fa-regular fa-clock"></i> <?php echo esc_html__( 'Time','booking-and-rental-manager-for-woocommerce' ); ?></label>
                    <span class="rbfw_muff_selected_date_value"><?php echo esc_html(gmdate(get_option('time_format'), strtotime($selected_time)));  ?></span>
                </div>
            </div>
        <?php } ?>


        <div class="">
            <table class="rbfw_bikecarsd_price_table rbfw_bikecarsd_rt_price_table">
                            <thead>
                            <tr>
                                <th class="w_50_pc">
                                    <?php echo esc_html__( 'Type','booking-and-rental-manager-for-woocommerce' ); ?>
                                </th>
                                <th class="w_30_pc">
                                    <?php echo esc_html__( 'Price','booking-and-rental-manager-for-woocommerce' ); ?>
                                </th>

                                <?php if($rbfw_enable_extra_service_qty=='yes'){ ?>
                                    <th data-booked_message="<?php echo esc_attr__( 'Available Quantity is ', 'booking-and-rental-manager-for-woocommerce' ) ?>" class="w_20_pc">
                                        <?php echo esc_html__( 'Quantity','booking-and-rental-manager-for-woocommerce' ); ?>
                                    </th>
                                <?php }else{ ?>
                                    <th data-booked_message="<?php echo esc_attr__( 'Available Quantity is ', 'booking-and-rental-manager-for-woocommerce' ) ?>" class="w_20_pc">
                                        <?php echo esc_html_e('Enable/Disable','booking-and-rental-manager-for-woocommerce'); ?>
                                    </th>
                                <?php } ?>
                            </tr>
                            </thead>
                            <tbody>

                            <?php

                            $i = 1;
                            foreach ($rbfw_bike_car_sd_data as $value) {
                                $max_available_qty = rbfw_get_bike_car_sd_available_qty($id, $selected_date, $value['rent_type'], $selected_time);
                                if($value['qty'] > 0){

                                    if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) ) {
                                        $rbfw_sp_prices = get_post_meta( $id, 'rbfw_bike_car_sd_data_sp', true );
                                        if ( isset( $rbfw_sp_prices ) && $rbfw_sp_prices  ) {
                                            $sp_price = check_seasonal_price_sd( $selected_date, $rbfw_sp_prices, $value['rent_type'] );
                                        }
                                    }
                                    $type_price = (isset($sp_price) and $sp_price)?$sp_price:$value['price'];


                                    ?>
                                    <tr>
                                        <td class="w_50_pc">
                                            <span class="rbfw_bikecarsd_type_title"><?php echo esc_html($value['rent_type']); ?></span>
                                            <small class="rbfw_bikecarsd_type_desc"><?php echo esc_html($value['short_desc']); ?></small>
                                            <?php  if($available_qty_info_switch == 'yes'){ ?>
                                                <small class="rbfw_available_qty_notice"><?php echo esc_html__( 'Available:','booking-and-rental-manager-for-woocommerce' ) . esc_html($max_available_qty); ?></small>
                                            <?php } ?>
                                            <input type="hidden" name="rbfw_bikecarsd_info[<?php echo esc_attr($i); ?>][rent_type]" value="<?php echo esc_attr($value['rent_type']); ?>"/>
                                            <input type="hidden" name="rbfw_bikecarsd_info[<?php echo esc_attr($i); ?>][short_desc]" value="<?php echo esc_attr($value['short_desc']); ?>"/>
                                        </td>

                                        <td class="w_30_pc">
                                            <span class="rbfw_bikecarsd_type_price"><?php echo wp_kses(wc_price($type_price),rbfw_allowed_html()); ?></span>
                                        </td>

                                        <input type="hidden" name="rbfw_bikecarsd_info[<?php echo esc_html($i); ?>][price]" value="<?php echo esc_attr($type_price); ?>"/>

                                        <td class="w_20_pc">
                                            <div class="rbfw_service_price_wrap">
                                                <div class="rbfw_qty_input">
                                                    <?php if($max_available_qty){ ?>
                                                        <?php if($rbfw_enable_extra_service_qty=='yes'){ ?>
                                                            <a class="rbfw_qty_minus rbfw_bikecarsd_qty_minus"><i class="fas fa-minus"></i></a>
                                                            <input type="number" min="0" max="<?php echo esc_attr($max_available_qty); ?>" value="0" name="rbfw_bikecarsd_info[<?php echo esc_attr($i) ?>][qty]" class="rbfw_bikecarsd_qty" data-price="<?php echo esc_attr($type_price); ?>" data-type="<?php echo esc_attr($value['rent_type']); ?>" data-cat="bikecarsd" />
                                                            <a class="rbfw_qty_plus rbfw_bikecarsd_qty_plus"><i class="fas fa-plus"></i></a>
                                                        <?php }else{ ?>
                                                            <label class="switch">
                                                                <input type="checkbox" class="rbfw_bikecarsd_checkbox" data-quantity_fixed="checkbox">
                                                                <span class="slider round"></span>
                                                                <input style="display:none" type="number" min="0" max="<?php echo esc_attr($max_available_qty); ?>" value="0" name="rbfw_bikecarsd_info[<?php echo esc_attr($i) ?>][qty]" class="rbfw_bikecarsd_qty" data-price="<?php echo esc_attr($type_price); ?>" data-type="<?php echo esc_attr($value['rent_type']); ?>" data-cat="bikecarsd" />
                                                            </label>
                                                        <?php } ?>
                                                    <?php }else{ ?>
                                                        <div class="rbfw_sold_out" style="width: 120px"><?php esc_html_e('Sold Out','booking-and-rental-manager-for-woocommerce'); ?></div>
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





            <?php if(!empty($rbfw_extra_service_data)){ ?>

                    <div class="rbfw_bikecarsd_es_price_table">
                        <div class="rbfw-single-right-heading">
                            <?php esc_html_e('Optional Add-ons','booking-and-rental-manager-for-woocommerce'); ?>
                        </div>
                        <table class="rbfw_bikecarsd_price_table">
                            <thead>
                            <tr>
                                <th class="w_50_pc"><?php echo esc_html__( 'Service Name','booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <th class="w_30_pc"><?php echo esc_html__( 'Price','booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <?php if($rbfw_enable_extra_service_qty=='yes'){ ?>
                                    <th class="w_20_pc"><?php echo esc_html__( 'Quantity','booking-and-rental-manager-for-woocommerce' ); ?></th>
                                <?php }else{ ?>
                                    <th class="w_20_pc"><?php esc_html_e('Enable/Disable','booking-and-rental-manager-for-woocommerce'); ?></th>
                                <?php } ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $c = 0;
                            foreach ($rbfw_extra_service_data as $value) {
                                $img_url = !empty($value['service_img']) ? wp_get_attachment_url($value['service_img']) : '';
                                $uniq_id = wp_rand();
                                if ($img_url) {
                                    $img = '<a href="#rbfw_service_img_'.$uniq_id.'" rel="mage_modal:open"><img src="' . esc_url($img_url) . '"/></a>';
                                    $img .= '<div id="rbfw_service_img_' . $uniq_id . '" class="mage_modal"><img src="<?php echo esc_url($img_url) ?>"/></div>';
                                }else{
                                    $img = '';
                                }

                                $max_es_available_qty = rbfw_get_bike_car_sd_es_available_qty($id, $selected_date, $value['service_name']);

                                if(isset($value['service_qty']) && ($value['service_qty'] > 0)){
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
                                                    <small class="rbfw_available_qty_notice"><?php echo esc_html__( 'Available:','booking-and-rental-manager-for-woocommerce' ) . esc_html($max_es_available_qty); ?></small>
                                                <?php } ?>
                                                <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c) ?>][service_name]" value="<?php echo esc_attr($value['service_name']); ?>"/>
                                            </div>
                                        </td>
                                        <td class="w_30_pc">
                                            <?php echo wp_kses(wc_price($value['service_price']),rbfw_allowed_html()); ?>
                                        </td>
                                        <td class="w_20_pc">
                                            <div class="rbfw_service_price_wrap">
                                                <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c) ?>][service_price]" value="<?php echo esc_attr($value['service_price']); ?>"/>
                                                <div class="rbfw_qty_input">
                                                    <?php if($max_es_available_qty){ ?>
                                                        <?php if($rbfw_enable_extra_service_qty=='yes'){ ?>
                                                            <a class="rbfw_qty_minus rbfw_servicesd_qty_minus"><i class="fas fa-minus"></i></a>
                                                            <input type="number" min="0" max="<?php echo esc_attr($max_es_available_qty) ?>" value="0" name="rbfw_service_info[<?php echo esc_attr($c) ?>][service_qty]" class="rbfw_servicesd_qty" data-price="<?php echo esc_attr($value['service_price']); ?>" data-type="<?php echo esc_attr($value['service_name']); ?>" data-cat="service"/>
                                                            <a class="rbfw_qty_plus rbfw_servicesd_qty_plus"><i class="fas fa-plus"></i></a>
                                                        <?php }else{ ?>
                                                            <label class="switch">
                                                                <input type="checkbox" class="rbfw_extra_service_sd_checkbox"  data-quantity_fixed="checkbox">
                                                                <span class="slider round"></span>
                                                                <input style="display: none" type="number" min="0" max="<?php echo esc_attr($max_es_available_qty) ?>" value="0" name="rbfw_service_info[<?php echo esc_attr($c)?>][service_qty]" class="rbfw_servicesd_qty" data-price="<?php echo esc_attr($value['service_price']); ?>" data-type="<?php echo esc_attr($value['service_name']); ?>" data-cat="service"/>
                                                            </label>
                                                        <?php } ?>
                                                    <?php }else{ ?>
                                                        <div class="rbfw_sold_out" style="width: 120px"><?php esc_html_e('Sold Out','booking-and-rental-manager-for-woocommerce'); ?></div>
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
                            <?php echo esc_html__( 'Duration Cost','booking-and-rental-manager-for-woocommerce' ); ?>
                            <?php echo wp_kses(wc_price(0),rbfw_allowed_html()); ?>
                        </li>
                        <?php if(!empty($rbfw_extra_service_data)){ ?>
                            <li class="extra_service_cost rbfw-cond">
                                <?php echo esc_html__( 'Resource Cost','booking-and-rental-manager-for-woocommerce' ); ?>
                                <?php echo wp_kses(wc_price(0),rbfw_allowed_html()); ?>
                            </li>
                        <?php } ?>
                        <li class="subtotal">
                            <?php echo esc_html__( 'Subtotal','booking-and-rental-manager-for-woocommerce' ); ?>
                            <?php echo wp_kses(wc_price(0),rbfw_allowed_html()); ?>
                        </li>
                        <li class="total">
                            <strong><?php echo esc_html__( 'Total','booking-and-rental-manager-for-woocommerce' ); ?></strong>
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
<?php } ?>