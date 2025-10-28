<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	global $rbfw;
	$rbfw_id = $post_id ? $post_id : get_the_ID();
	$rbfw_rent_type = !empty(get_post_meta( $rbfw_id, 'rbfw_item_type', true )) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : 'bike_car_sd';
	$rbfw_product_id = get_post_meta( $rbfw_id, "link_wc_product", true ) ? get_post_meta( $rbfw_id, "link_wc_product", true ) : $rbfw_id;

    $location_switch = !empty(get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true)) ? get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true) : '';
    $pickup_location = get_post_meta($rbfw_id, 'rbfw_pickup_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_pickup_data', true)) : [];
    $dropoff_location = get_post_meta($rbfw_id, 'rbfw_dropoff_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_dropoff_data', true)) : [];

    $manage_inventory_as_timely =  get_post_meta($rbfw_id, 'manage_inventory_as_timely', true) ? get_post_meta($rbfw_id, 'manage_inventory_as_timely', true) : 'off';
    $enable_specific_duration =  get_post_meta($rbfw_id, 'enable_specific_duration', true) ? get_post_meta($rbfw_id, 'enable_specific_duration', true) : 'off';

    $rbfw_item_stock_quantity_timely = !empty(get_post_meta($rbfw_id,'rbfw_item_stock_quantity_timely',true)) ? get_post_meta($rbfw_id,'rbfw_item_stock_quantity_timely',true) : 0;

    $available_times = get_post_meta($rbfw_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rdfw_available_time', true)) : [];


    $rbfw_enable_time_picker = get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) ? get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) : 'no';

    $rbfw_extra_service_data = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : [];

    $available_qty_info_switch = get_post_meta($rbfw_id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($rbfw_id, 'rbfw_available_qty_info_switch', true) : 'no';

    $rbfw_enable_security_deposit = get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) ? get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) : 'no';
    $rbfw_security_deposit_type = get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) : 'percentage';
    $rbfw_security_deposit_amount = get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) : 0;

    $rbfw_particular_switch = get_post_meta( $post_id, 'rbfw_particular_switch', true ) ? get_post_meta( $post_id, 'rbfw_particular_switch', true ) : 'off';
    $particulars_data = get_post_meta( $rbfw_id, 'rbfw_particulars_data', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rbfw_particulars_data', true ) ) : [];
    $rdfw_available_time = get_post_meta( $rbfw_id, 'rdfw_available_time', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rdfw_available_time', true ) ) : [];
    $rbfw_buffer_time = get_post_meta( $rbfw_id, 'rbfw_buffer_time', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rbfw_buffer_time', true ) ) : 0;




?>

	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo esc_attr($rbfw_id); ?>">
        <!--    Right Side-->
		<div class="rbfw-single-right-container rbfw_bikecarsd_pricing_table_wrap">
			<form action="" method='post' class="mp_rbfw_ticket_form">
                <?php if ($location_switch == 'yes' && !empty($pickup_location)) : ?>
                    <div class="item">
                        <div class="rbfw-single-right-heading"><?php esc_html_e('Pickup Location','booking-and-rental-manager-for-woocommerce'); ?></div>
                        <div class="item-content rbfw-location">
                            <select class="rbfw-select" name="rbfw_pickup_point" required>
                                <option value=""><?php esc_html_e('Choose pickup location','booking-and-rental-manager-for-woocommerce'); ?></option>
                                <?php foreach ($pickup_location as $pickup) : ?>
                                    <option value="<?php echo esc_attr($pickup['loc_pickup_name']); ?>"><?php echo esc_html($pickup['loc_pickup_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($location_switch == 'yes' && !empty($dropoff_location)) : ?>
                    <div class="item">
                        <div class="rbfw-single-right-heading">
                            <?php esc_html_e('Drop-off Location','booking-and-rental-manager-for-woocommerce'); ?>
                        </div>
                        <div class="item-content rbfw-location">
                            <select class="rbfw-select" name="rbfw_dropoff_point" required>
                                <option value=""><?php esc_html_e('Choose drop-off location','booking-and-rental-manager-for-woocommerce'); ?></option>
                                <?php foreach ($dropoff_location as $dropoff) : ?>
                                    <option value="<?php echo esc_attr($dropoff['loc_dropoff_name']); ?>"><?php echo esc_html($dropoff['loc_dropoff_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>


                <?php  if($manage_inventory_as_timely !='on' || $rbfw_rent_type =='appointment'){ ?>
                    <div class="item rbfw-bikecarsd-step" data-step="1">
                        <div id="rbfw-bikecarsd-calendar" class="rbfw-bikecarsd-calendar">
                        </div>
                        <div class="rbfw-bikecarsd-calendar-footer">
                            <i class="fas fa-circle-info"></i>
                            <?php rbfw_string('rbfw_text_click_date_to_browse_availability',__('Click a date to browse availability','booking-and-rental-manager-for-woocommerce')); ?>
                        </div>
                    </div>

                    <div class="rbfw-bikecarsd-result-wrap">
                        <div class="rbfw-bikecarsd-result-loader"></div>
                        <div class="rbfw-bikecarsd-result">
                            <div class="rbfw_bikecarsd_time_table_wrap" id="rbfw_bikecarsd_time_table_wrap">
                            </div>
                        </div>
                        <div class="rbfw-bikecarsd-result-order-details">

                        </div>
                    </div>

                <?php } else{ ?>

                    <?php
                    $rbfw_bike_car_sd_data = get_post_meta($rbfw_id, 'rbfw_bike_car_sd_data', true) ? get_post_meta($rbfw_id, 'rbfw_bike_car_sd_data', true) : [];
                    ?>
                    <div class="item">
                        <div class="item-content rbfw-datetime">

                            <div class="<?php echo ($rbfw_enable_time_picker == 'yes' && $enable_specific_duration =='off')?'left':'' ?> date">
                                <label class="rbfw-single-right-heading">
                                    <?php _e('Rental Start Date','booking-and-rental-manager-for-woocommerce'); ?>
                                </label>
                                <div class="rbfw-p-relative">
                                    <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                    <input class="rbfw-input rbfw-time-price pickup_date_timely" type="text"   placeholder="<?php esc_attr_e('Select Date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="" style="background-position: 95% center">
                                    <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                </div>
                            </div>

                            <?php if($rbfw_enable_time_picker == 'yes' && $enable_specific_duration =='off'){ ?>
                                <div class="right time">
                                    <div class="rbfw-single-right-heading">
                                        <?php esc_html_e('Pickup Time','booking-and-rental-manager-for-woocommerce'); ?>
                                    </div>
                                    <div class="rbfw-p-relative">
                                        <span class="clock">
                                            <i class="fa-regular fa-clock"></i>
                                        </span>

                                        <select class="rbfw-select rbfw-time-price pickup_time" name="rbfw_start_time" id="pickup_time" required>
                                            <option value="" disabled selected><?php esc_html_e('Pickup Time','booking-and-rental-manager-for-woocommerce'); ?></option>
                                        </select>


                                        <span class="input-picker-icon"></span>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                    <label class="rbfw-single-right-heading">
                        <?php esc_html_e('Rental Duration', 'booking-and-rental-manager-for-woocommerce'); ?>
                    </label>

                    <div class="rbfw_service_type rbfw_service_type_timely">
                        <?php foreach ($rbfw_bike_car_sd_data as $value) { ?>
                            <div title="<?php echo esc_attr($value['short_desc']); ?>" data-text="<?php echo esc_attr($value['rent_type']); ?>" data-available_quantity="0" data-duration="<?php echo esc_attr($value['duration']); ?>" data-price="<?php echo esc_attr($value['price']); ?>" data-d_type="<?php echo esc_attr($value['d_type']); ?>" data-start_time="<?php echo esc_attr($value['start_time']) ?? '' ?>" data-end_time="<?php echo esc_attr($value['end_time']) ?? '' ?>" class="radio-button single-type-timely">
                                <label for="">
                                    <input type="radio" name="option" class="radio-input">
                                    <span class="rent-type"><?php echo esc_html($value['rent_type']); ?></span>
                                    <?php if($enable_specific_duration=='on'): ?>
                                        <div class="rbfw_time">
                                            <?php echo esc_html($value['start_time']).' - '.esc_html($value['end_time']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="rbfw_time">
                                            <?php echo esc_html($value['duration']." ".$value['d_type']); ?>
                                        </div>
                                    <?php endif; ?>
                                </label>
                                <div class="price"><?php echo esc_html(get_woocommerce_currency_symbol().$value['price']); ?></div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="rbfw_bikecarsd_pricing_table_container rbfw_quantiry_area_sd" style="display: none">
                        <div class="rbfw_bikecarsd_price_table timely_quqntity_table">
                            <span class="rbfw_bikecarsd_type_title">
                                Quantity
                            </span>
                            <div class="rbfw_regf_group">
                                <select name="rbfw_item_quantity" id="rbfw_item_quantity">
                                </select>
                                <input type="hidden" class="rbfw_sd_price_input">
                                x
                                <span class="rbfw_sd_price"></span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="service_type" id="rbfw_service_type_for_st" value="">

                    <div class="rbfw_bikecarsd_pricing_table_container rbfw-bikecarsd-step rbfw_extra_service_sd" style="display: none">
                        <div class="">
                            <?php if(!empty($rbfw_extra_service_data)){ ?>
                                <label class="rbfw-single-right-heading"><?php esc_html_e('Optional Add-ons','booking-and-rental-manager-for-woocommerce'); ?></label>
                                <div class="rbfw_bikecarsd_price_table">
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
                                        if($value['service_qty'] > 0){
                                            ?>
                                            <div class="rbfw-optional-add-ons">
                                                <div>
                                                    <div>
                                                        <?php echo wp_kses($img , rbfw_allowed_html()); ?>
                                                    </div>
                                                    <div>
                                                        <span class="rbfw_bikecarsd_type_title"><?php echo esc_html($value['service_name']); ?></span>
                                                        <?php if(!empty($value['service_desc'])){ ?>
                                                            <small class="rbfw_bikecarsd_type_desc"><?php echo esc_html($value['service_desc']); ?></small>
                                                        <?php } ?>
                                                        <?php if($available_qty_info_switch == 'yes'){ ?>
                                                            <small class="rbfw_available_qty_notice"><?php echo esc_html__('Available:','booking-and-rental-manager-for-woocommerce') . esc_html($max_es_available_qty); ?></small>
                                                        <?php } ?>
                                                        <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_name]" value="<?php echo esc_attr($value['service_name']); ?>"/>
                                                    </div>
                                                </div>
                                                <div>
                                                    <?php echo wp_kses(wc_price($value['service_price']) , rbfw_allowed_html()); ?>
                                                </div>
                                                <div>
                                                    <div class="rbfw_service_price_wrap">
                                                        <input type="hidden" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_price]" value="<?php echo esc_attr($value['service_price']); ?>"/>
                                                        <div class="rbfw_qty_input">
                                                            <a class="rbfw_qty_minus rbfw_timely_es_qty_minus"><i class="fas fa-minus"></i></a>
                                                            <input type="number" min="0" max="<?php echo esc_attr($value['service_qty']) ?>" value="0" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_qty]" class="rbfw_timely_es_qty" data-price="<?php echo esc_attr($value['service_price']); ?>" data-type="<?php echo esc_attr($value['service_name']); ?>" data-cat="service"/>
                                                            <a class="rbfw_qty_plus rbfw_timely_es_qty_plus"><i class="fas fa-plus"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        $c++;
                                    }
                                    ?>
                                </div>
                            <?php } ?>

                            <?php
                            /* Include Custom Registration Form */
                            if(class_exists('Rbfw_Reg_Form')){
                                $reg_form = new Rbfw_Reg_Form();
                                echo wp_kses($reg_form->rbfw_generate_regf_fields($rbfw_id),  rbfw_allowed_html());
                            }
                            /* End: Include Custom Registration Form */
                            ?>

                            <?php
                            /* Include WooCommerce Products */
                            $rbfw_enable_woocommerce_products = get_post_meta($rbfw_id, 'rbfw_enable_woocommerce_products', true) ? get_post_meta($rbfw_id, 'rbfw_enable_woocommerce_products', true) : 'no';
                            $rbfw_woocommerce_products = get_post_meta($rbfw_id, 'rbfw_woocommerce_products', true) ? get_post_meta($rbfw_id, 'rbfw_woocommerce_products', true) : [];
                            
                            if($rbfw_enable_woocommerce_products == 'yes' && !empty($rbfw_woocommerce_products)) {
                                ?>
                                <div class="rbfw_woocommerce_products_section">
                                    <label class="rbfw-single-right-heading"><?php esc_html_e('Additional Products','booking-and-rental-manager-for-woocommerce'); ?></label>
                                    <div class="rbfw_woocommerce_products_list">
                                        <?php
                                        foreach ($rbfw_woocommerce_products as $index => $wc_product_data) {
                                            if(empty($wc_product_data['product_id'])) continue;
                                            
                                            $product = wc_get_product($wc_product_data['product_id']);
                                            if(!$product) continue;
                                            
                                            $product_price = $product->get_price();
                                            if($wc_product_data['override_price'] == 'yes' && !empty($wc_product_data['custom_price'])) {
                                                $product_price = $wc_product_data['custom_price'];
                                            }
                                            
                                            $max_quantity = !empty($wc_product_data['max_quantity']) ? $wc_product_data['max_quantity'] : 10;
                                            ?>
                                            <div class="rbfw-wc-product-item">
                                                <div class="rbfw-wc-product-info">
                                                    <div class="rbfw-wc-product-name"><?php echo esc_html($product->get_name()); ?></div>
                                                    <div class="rbfw-wc-product-price"><?php echo wp_kses(wc_price($product_price), rbfw_allowed_html()); ?></div>
                                                </div>
                                                <div class="rbfw-wc-product-quantity">
                                                    <div class="rbfw_qty_input">
                                                        <a class="rbfw_qty_minus rbfw_wc_qty_minus" data-product-id="<?php echo esc_attr($wc_product_data['product_id']); ?>">
                                                            <i class="fas fa-minus"></i>
                                                        </a>
                                                        <input type="number" 
                                                               min="0" 
                                                               max="<?php echo esc_attr($max_quantity); ?>" 
                                                               value="0" 
                                                               name="rbfw_wc_products[<?php echo esc_attr($index); ?>][quantity]" 
                                                               class="rbfw_wc_qty" 
                                                               data-product-id="<?php echo esc_attr($wc_product_data['product_id']); ?>"
                                                               data-price="<?php echo esc_attr($product_price); ?>"
                                                               data-max="<?php echo esc_attr($max_quantity); ?>"/>
                                                        <a class="rbfw_qty_plus rbfw_wc_qty_plus" data-product-id="<?php echo esc_attr($wc_product_data['product_id']); ?>">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="rbfw_wc_products[<?php echo esc_attr($index); ?>][product_id]" value="<?php echo esc_attr($wc_product_data['product_id']); ?>">
                                                <input type="hidden" name="rbfw_wc_products[<?php echo esc_attr($index); ?>][price]" value="<?php echo esc_attr($product_price); ?>">
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>

                            <div class="item rbfw_bikecarsd_price_summary">
                                <label class="rbfw-single-right-heading"><?php _e('Booking Summary','booking-and-rental-manager-for-woocommerce'); ?></label>
                                <div class="item-content rbfw-costing">
                                    <ul class="rbfw-ul">
                                        <li class="duration-costing rbfw-cond">
                                            <?php esc_html_e('Duration Cost','booking-and-rental-manager-for-woocommerce'); ?>
                                            <span>
                                                <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                            </span>
                                        </li>
                                        <?php if(!empty($rbfw_extra_service_data)){ ?>
                                            <li class="resource-costing extra_service_cost rbfw-cond">
                                                <?php esc_html_e('Resource Cost','booking-and-rental-manager-for-woocommerce'); ?>
                                                <span>
                                                    <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                                </span>
                                            </li>
                                        <?php } ?>
                                        <?php if($rbfw_enable_woocommerce_products == 'yes' && !empty($rbfw_woocommerce_products)){ ?>
                                            <li class="woocommerce-products-costing wc_products_cost rbfw-cond">
                                                <?php esc_html_e('Additional Products','booking-and-rental-manager-for-woocommerce'); ?>
                                                <span>
                                                    <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                                </span>
                                            </li>
                                        <?php } ?>
                                        <li class="subtotal">
                                            <?php esc_html_e('Subtotal','booking-and-rental-manager-for-woocommerce'); ?>
                                            <span>
                                                <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                            </span>
                                        </li>
                                        <li class="security_deposit" style="display:none;">
                                            <?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : __('Security Deposit','booking-and-rental-manager-for-woocommerce'))); ?>
                                            <span></span>
                                        </li>
                                        <li class="total">
                                            <strong><?php esc_html_e('Total','booking-and-rental-manager-for-woocommerce'); ?></strong>
                                            <span>
                                                <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                            </span>
                                        </li>
                                    </ul>
                                    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php do_action('rbfw_ticket_feature_info'); ?>
				
				<div class="item rbfw_bikecarsd_book_now_btn_wrap">
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarsd_book_now_btn rbfw_disabled_button" disabled>
					<?php
						esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce');
					?>
					</button>
				</div>

                <?php

                $time_slot_switch = !empty(get_post_meta($post_id, 'rbfw_time_slot_switch', true)) ? get_post_meta($post_id, 'rbfw_time_slot_switch', true) : 'on';
                $available_times = get_post_meta($post_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($post_id, 'rdfw_available_time', true)) : [];


                if($time_slot_switch == 'on' && !empty($available_times)){
                    $time_slot_switch = 'on';
                }else{
                    $time_slot_switch = 'off';
                }

                $appointment_days = wp_json_encode(get_post_meta($post_id, 'rbfw_sd_appointment_ondays', true));
                ?>

                <?php wp_nonce_field('rbfw_ajax_action', 'nonce'); ?>

                <input type="hidden" name="rbfw_time_slot_switch" id="rbfw_time_slot_switch" value="<?php echo esc_attr($rbfw_enable_time_picker); ?>">

                <input type="hidden" name="enable_specific_duration" id="enable_specific_duration" value="<?php echo esc_attr($enable_specific_duration); ?>">

                <input type="hidden" name="rbfw_bikecarsd_selected_date" id="rbfw_bikecarsd_selected_date">
                <input type="hidden" name="rbfw_start_time" id="rbfw_start_time">
                <input type="hidden" name="rbfw_end_date" id="rbfw_end_date">
                <input type="hidden" name="rbfw_end_time" id="rbfw_end_time">

                <input type="hidden" name="rbfw_service_price" id="rbfw_service_price" value="0">
                <input type="hidden" name="rbfw_es_service_price" id="rbfw_es_service_price" value="0">

                <input type="hidden" name="rbfw_security_deposit_enable" id="rbfw_security_deposit_enable"  value="<?php echo esc_attr($rbfw_enable_security_deposit); ?>">
                <input type="hidden" name="rbfw_security_deposit_type" id="rbfw_security_deposit_type"  value="<?php echo esc_attr($rbfw_security_deposit_type); ?>">
                <input type="hidden" name="rbfw_security_deposit_amount" id="rbfw_security_deposit_amount"  value="<?php echo esc_attr($rbfw_security_deposit_amount); ?>">

                <input type="hidden" name="manage_inventory_as_timely" id="manage_inventory_as_timely" value="<?php echo esc_attr($manage_inventory_as_timely); ?>">
                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="<?php echo esc_attr($rbfw_rent_type); ?>">
                <input type="hidden" name="appointment_days" id="appointment_days"  value='<?php echo esc_attr($appointment_days); ?>'>
                <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo esc_attr(rbfw_off_days($post_id)); ?>'>
                <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range"  value='<?php echo esc_attr(rbfw_off_dates($post_id)); ?>'>

                <input type="hidden" name="rbfw_particular_switch" id="rbfw_particular_switch"  value='<?php echo esc_attr($rbfw_particular_switch); ?>'>
                <input type="hidden" name="rbfw_particulars_data" id="rbfw_particulars_data"  value='<?php echo esc_attr(wp_json_encode($particulars_data)); ?>'>
                <input type="hidden" name="rdfw_available_time" id="rdfw_available_time"  value='<?php echo esc_attr(wp_json_encode($rdfw_available_time)); ?>'>

                <input type="hidden" name="rbfw_buffer_time" id="rbfw_buffer_time"  value='<?php echo esc_attr($rbfw_buffer_time); ?>'>

                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id" class="rbfw_post_id"  value="<?php echo esc_attr($rbfw_id); ?>">



            </form>
		</div>
		<!--    Right Side END-->
	</div>
	<!--    Main Layout END-->

	<style>
	.rbfw-wc-product-item {
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		padding: 10px 0 !important;
		margin-bottom: 5px !important;
		border-bottom: 1px solid #eee !important;
	}
	.rbfw-wc-product-item:last-child {
		border-bottom: none !important;
	}
	.rbfw-wc-product-info {
		flex: 1 !important;
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
	}
	.rbfw-wc-product-name {
		font-weight: 500 !important;
		color: #333 !important;
		margin: 0 !important;
	}
	.rbfw-wc-product-price {
		color: #333 !important;
		font-weight: 500 !important;
		margin: 0 !important;
	}
	.rbfw-wc-product-quantity {
		margin-left: 15px !important;
	}
	.rbfw_qty_input {
		display: flex !important;
		align-items: center !important;
		border: 1px solid #ddd !important;
		border-radius: 4px !important;
		overflow: hidden !important;
	}
	.rbfw_qty_minus,
	.rbfw_qty_plus {
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		width: 30px !important;
		height: 30px !important;
		background-color: #f8f9fa !important;
		border: none !important;
		cursor: pointer !important;
		transition: background-color 0.3s ease !important;
		font-size: 12px !important;
	}
	.rbfw_qty_minus:hover,
	.rbfw_qty_plus:hover {
		background-color: #e9ecef !important;
	}
	.rbfw_wc_qty {
		width: 50px !important;
		height: 30px !important;
		border: none !important;
		text-align: center !important;
		font-weight: 500 !important;
		outline: none !important;
		font-size: 14px !important;
	}
	.rbfw_wc_qty:focus {
		background-color: #f8f9fa !important;
	}
	</style>

	<script>
	jQuery(document).ready(function($) {
		console.log('RBFW WooCommerce Products JS loaded inline');
		
		// WooCommerce Products Quantity Controls
		$(document).on('click', '.rbfw_wc_qty_plus', function(e) {
			e.preventDefault();
			e.stopPropagation();
			console.log('Plus button clicked');
			
			var $input = $(this).siblings('input.rbfw_wc_qty');
			console.log('Input found:', $input.length);
			
			if ($input.length === 0) {
				console.log('No input found, trying alternative selector');
				$input = $(this).closest('.rbfw_qty_input').find('input.rbfw_wc_qty');
				console.log('Alternative input found:', $input.length);
			}
			
			if ($input.length > 0) {
				var currentVal = parseInt($input.val()) || 0;
				var maxVal = parseInt($input.data('max')) || 10;
				
				console.log('Current val:', currentVal, 'Max val:', maxVal);
				
				if (currentVal < maxVal) {
					$input.val(currentVal + 1).trigger('change');
					console.log('Updated to:', currentVal + 1);
				}
			}
		});

		$(document).on('click', '.rbfw_wc_qty_minus', function(e) {
			e.preventDefault();
			e.stopPropagation();
			console.log('Minus button clicked');
			
			var $input = $(this).siblings('input.rbfw_wc_qty');
			console.log('Input found:', $input.length);
			
			if ($input.length === 0) {
				console.log('No input found, trying alternative selector');
				$input = $(this).closest('.rbfw_qty_input').find('input.rbfw_wc_qty');
				console.log('Alternative input found:', $input.length);
			}
			
			if ($input.length > 0) {
				var currentVal = parseInt($input.val()) || 0;
				
				console.log('Current val:', currentVal);
				
				if (currentVal > 0) {
					$input.val(currentVal - 1).trigger('change');
					console.log('Updated to:', currentVal - 1);
				}
			}
		});

		// Update WooCommerce Products Total in Booking Summary
		$(document).on('change', '.rbfw_wc_qty', function() {
			console.log('Quantity changed for:', $(this).val());
			updateWooCommerceProductsTotal();
		});

		function updateWooCommerceProductsTotal() {
			console.log('Updating WooCommerce products total');
			var total = 0;
			var hasProducts = false;
			
			$('.rbfw_wc_qty').each(function() {
				var quantity = parseInt($(this).val()) || 0;
				var price = parseFloat($(this).data('price')) || 0;
				
				console.log('Product qty:', quantity, 'price:', price);
				
				if (quantity > 0) {
					total += quantity * price;
					hasProducts = true;
				}
			});

			console.log('Total WC products cost:', total, 'Has products:', hasProducts);

			// Update the display
			var $wcProductsCost = $('.wc_products_cost');
			var $wcProductsSpan = $wcProductsCost.find('span');
			
			console.log('WC products cost element found:', $wcProductsCost.length);
			console.log('WC products span found:', $wcProductsSpan.length);
			
			if (hasProducts) {
				$wcProductsCost.removeClass('rbfw-cond').addClass('show');
				$wcProductsSpan.html('€' + total.toFixed(2));
			} else {
				$wcProductsCost.removeClass('show').addClass('rbfw-cond');
				$wcProductsSpan.html('€0.00');
			}

			// Update subtotal
			updateBookingSubtotal();
		}

		function updateBookingSubtotal() {
			var durationCost = parseFloat($('.duration-costing span').text().replace(/[^0-9.-]+/g, '')) || 0;
			var resourceCost = parseFloat($('.extra_service_cost span').text().replace(/[^0-9.-]+/g, '')) || 0;
			var wcProductsCost = parseFloat($('.wc_products_cost span').text().replace(/[^0-9.-]+/g, '')) || 0;
			
			var subtotal = durationCost + resourceCost + wcProductsCost;
			
			$('.subtotal span').html('€' + subtotal.toFixed(2));
			
			// Update total
			var securityDeposit = parseFloat($('.security_deposit span').text().replace(/[^0-9.-]+/g, '')) || 0;
			var total = subtotal + securityDeposit;
			
			$('.total span').html('€' + total.toFixed(2));
		}

		// Initialize on page load
		updateWooCommerceProductsTotal();
	});
	</script>