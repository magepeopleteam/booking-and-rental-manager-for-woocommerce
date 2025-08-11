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


?>

	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo esc_attr($rbfw_id); ?>">
        <!--    Right Side-->
		<div class="rbfw-single-right-container rbfw_bikecarsd_pricing_table_wrap">
			<form action="" method='post' class="mp_rbfw_ticket_form">
                <?php if ($location_switch == 'yes' && !empty($pickup_location)) : ?>
                    <div class="item">
                        <div class="rbfw-single-right-heading"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_location', 'rbfw_basic_translation_settings', __('Pickup Location','booking-and-rental-manager-for-woocommerce'))); ?></div>
                        <div class="item-content rbfw-location">
                            <select class="rbfw-select" name="rbfw_pickup_point" required>
                                <option value=""><?php echo esc_html($rbfw->get_option_trans('rbfw_text_choose_pickup_location', 'rbfw_basic_translation_settings', __('Choose pickup location','booking-and-rental-manager-for-woocommerce'))); ?></option>
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
                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_dropoff_location', 'rbfw_basic_translation_settings', __('Drop-off Location','booking-and-rental-manager-for-woocommerce'))); ?>
                        </div>
                        <div class="item-content rbfw-location">
                            <select class="rbfw-select" name="rbfw_dropoff_point" required>
                                <option value=""><?php echo esc_attr($rbfw->get_option_trans('rbfw_text_choose_dropoff_location', 'rbfw_basic_translation_settings', __('Choose drop-off location','booking-and-rental-manager-for-woocommerce'))); ?></option>
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
                            <div class="rbfw_bikecarsd_time_table_wrap">
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
                            <div class="date">
                                <label class="rbfw-single-right-heading"><?php _e('Rental Start Date','booking-and-rental-manager-for-woocommerce'); ?></label>
                                <div class="rbfw-p-relative">
                                    <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                    <input class="rbfw-input rbfw-time-price pickup_date_timely" type="text"   placeholder="<?php echo esc_attr($rbfw->get_option_trans('rbfw_text_pickup_date', 'rbfw_basic_translation_settings', __('Select Date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly="" style="background-position: 95% center">
                                    <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                </div>
                            </div>
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

                    <div class="rbfw_bikecarsd_pricing_table_container">
                        <div class="rbfw_bikecarsd_price_table timely_quqntity_table">
                            <span class="rbfw_bikecarsd_type_title">
                                Quantity
                            </span>
                            <div class="rbfw_regf_group">
                                <select name="rbfw_item_quantity" id="rbfw_item_quantity">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                                x
                                <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">€</span>&nbsp;10,00</span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="end_date" value="2025-08-07">
                    <input type="hidden" name="end_time" value="08:00:00">
                    <input type="hidden" name="service_type" value="type 1">

                    <div class="rbfw_bikecarsd_pricing_table_container rbfw-bikecarsd-step">
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

                                        $max_es_available_qty = rbfw_get_bike_car_sd_es_available_qty($id, $start_date, $value['service_name']);

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
                                                            <small class="rbfw_available_qty_notice">(<?php echo esc_html(rbfw_string_return('rbfw_text_available',__('Available:','booking-and-rental-manager-for-woocommerce'))).esc_html($max_es_available_qty); ?>)</small>
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
                                                            <?php if($max_es_available_qty){ ?>
                                                                <a class="rbfw_qty_minus rbfw_timely_es_qty_minus"><i class="fas fa-minus"></i></a>
                                                                <input type="number" min="0" max="<?php echo esc_attr($max_es_available_qty) ?>" value="0" name="rbfw_service_info[<?php echo esc_attr($c); ?>][service_qty]" class="rbfw_timely_es_qty" data-price="<?php echo esc_attr($value['service_price']); ?>" data-type="<?php echo esc_attr($value['service_name']); ?>" data-cat="service"/>
                                                                <a class="rbfw_qty_plus rbfw_timely_es_qty_plus"><i class="fas fa-plus"></i></a>
                                                            <?php }else{ ?>
                                                                <div style="width: 120px">Sold Out</div>
                                                            <?php } ?>
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

                            <div class="item rbfw_bikecarsd_price_summary">
                                <label class="rbfw-single-right-heading"><?php _e('Booking Summary','booking-and-rental-manager-for-woocommerce'); ?></label>
                                <div class="item-content rbfw-costing">
                                    <ul class="rbfw-ul">
                                        <li class="duration-costing rbfw-cond">
                                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                                            <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                        </li>
                                        <?php if(!empty($rbfw_extra_service_data)){ ?>
                                            <li class="resource-costing rbfw-cond">
                                                <?php echo esc_html($rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce'))); ?>
                                                <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                            </li>
                                        <?php } ?>
                                        <li class="subtotal">
                                            <?php echo esc_html($rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce'))); ?>
                                            <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                        </li>
                                        <li class="total">
                                            <strong><?php echo esc_html($rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce'))); ?></strong>
                                            <?php echo wp_kses(wc_price(0) , rbfw_allowed_html()); ?>
                                        </li>
                                    </ul>
                                    <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                 <!-- Header -->
				<div class="rbfw-bikecarsd-calendar-header">
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fas fa-clock"></i> <?php rbfw_string('rbfw_text_real_time_availability',__('Real-time availability','booking-and-rental-manager-for-woocommerce')); ?></div>
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fas fa-bolt"></i> <?php rbfw_string('rbfw_text_instant_confirmation',__('Instant confirmation','booking-and-rental-manager-for-woocommerce')); ?></div>
				</div>
				<!-- Button -->
				
				<div class="item rbfw_bikecarsd_book_now_btn_wrap">
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarsd_book_now_btn rbfw_disabled_button" disabled>
					<?php
						echo esc_html($rbfw->get_option_trans('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce')));
					?>
					</button>
				</div>

                <?php
                $rbfw_regf_info = [];
                if(class_exists('Rbfw_Reg_Form')){
                    $ClassRegForm = new Rbfw_Reg_Form();
                    $rbfw_regf_info = $ClassRegForm->rbfw_get_regf_all_fields_name($post_id);
                }
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
                <input type="hidden" name="rbfw_bikecarsd_selected_date" id="rbfw_bikecarsd_selected_date">
                <input type="hidden" name="enable_specific_duration" id="enable_specific_duration" value="<?php echo esc_attr($enable_specific_duration); ?>">
                <input type="hidden" name="rbfw_start_time" id="rbfw_start_time" value="00:00">
                <input type="hidden" name="rbfw_es_service_price" id="rbfw_es_service_price">
                <input type="hidden" name="manage_inventory_as_timely" id="manage_inventory_as_timely" value="<?php echo esc_attr($manage_inventory_as_timely); ?>">
                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="<?php echo esc_attr($rbfw_rent_type); ?>">
                <input type="hidden" name="rbfw_regf_info" id="rbfw_regf_info"  value='<?php echo wp_json_encode($rbfw_regf_info); ?>'>
                <input type="hidden" name="appointment_days" id="appointment_days"  value='<?php echo esc_attr($appointment_days); ?>'>
                <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo esc_attr(rbfw_off_days($post_id)); ?>'>
                <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range" class="llll"  value='<?php echo esc_attr(rbfw_off_dates($post_id)); ?>'>
                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id" class="rbfw_post_id"  value="<?php echo esc_attr($rbfw_id); ?>">

			</form>
		</div>
		<!--    Right Side END-->
	</div>
	<!--    Main Layout END-->