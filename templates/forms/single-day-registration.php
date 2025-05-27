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
    $rbfw_time_slot_switch = !empty(get_post_meta($rbfw_id,'rbfw_time_slot_switch',true)) ? get_post_meta($rbfw_id,'rbfw_time_slot_switch',true) : 'off';

    $available_times = get_post_meta($rbfw_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rdfw_available_time', true)) : [];


    $rbfw_enable_time_picker = get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) ? get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) : 'no';


?>

	<!--    Main Layout-->
	<div class="rbfw-single-container" data-service-id="<?php echo esc_attr($rbfw_id); ?>">
        <!--    Right Side-->
		<div class="rbfw-single-right-container rbfw_bikecarsd_pricing_table_wrap">
			<form action="" method='post' class="mp_rbfw_ticket_form">
                <!-- Header -->
				<div class="rbfw-bikecarsd-calendar-header">
					<h3 class="rbfw-bikecarsd-calendar-header-title"><?php rbfw_string('rbfw_text_book_online',__('Book online','booking-and-rental-manager-for-woocommerce')); ?></h3>
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fas fa-clock"></i> <?php rbfw_string('rbfw_text_real_time_availability',__('Real-time availability','booking-and-rental-manager-for-woocommerce')); ?></div>
					<div class="rbfw-bikecarsd-calendar-header-feature"><i class="fas fa-bolt"></i> <?php rbfw_string('rbfw_text_instant_confirmation',__('Instant confirmation','booking-and-rental-manager-for-woocommerce')); ?></div>
				</div>

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
                        <div class="rbfw-bikecarsd-result"></div>
                        <div class="rbfw-bikecarsd-result-order-details">

                        </div>
                    </div>

                <?php } else{ ?>


                <?php

                $rbfw_bike_car_sd_data = get_post_meta($rbfw_id, 'rbfw_bike_car_sd_data', true) ? get_post_meta($rbfw_id, 'rbfw_bike_car_sd_data', true) : [];


                ?>
                    <div class="item">
                        <div class="item-content rbfw-datetime">
                            <div class="<?php echo ($rbfw_enable_time_picker == 'yes' )?'left':'' ?> date">
                                <div class="rbfw-single-right-heading">
                                    <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings')); ?>
                                </div>
                                <div class="rbfw-p-relative">
                                    <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                    <input class="rbfw-input rbfw-time-price pickup_date_timely" type="text"   placeholder="<?php echo esc_attr($rbfw->get_option_trans('rbfw_text_pickup_date', 'rbfw_basic_translation_settings', __('Pickup date','booking-and-rental-manager-for-woocommerce'))); ?>" required readonly="" style="background-position: 95% center">
                                    <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                </div>
                            </div>

                            <?php if($rbfw_enable_time_picker == 'yes'){ ?>
                                <div class="right time">
                                    <div class="rbfw-single-right-heading">
                                        <?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_date_time', 'rbfw_basic_translation_settings')); ?>
                                    </div>
                                    <div class="rbfw-p-relative">
                                        <span class="clock">
                                            <i class="fa-regular fa-clock"></i>
                                        </span>
                                        <select class="rbfw-select rbfw-time-price pickup_time" name="rbfw_start_time" id="pickup_time" required>
                                            <option value="" disabled selected><?php echo esc_html($rbfw->get_option_trans('rbfw_text_pickup_time', 'rbfw_basic_translation_settings', __('Pickup Time','booking-and-rental-manager-for-woocommerce'))); ?></option>
                                            <?php foreach ($available_times as $key => $time) : ?>
                                                <option value="<?php echo esc_attr($time); ?>"><?php echo esc_html(gmdate(get_option('time_format'), strtotime($time))); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="input-picker-icon"></span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="rbfw-single-right-heading">
                        <?php esc_html_e('Choose Duration', 'booking-and-rental-manager-for-woocommerce'); ?>
                    </div>

                    <div class="rbfw_service_type rbfw_service_type_timely">
                        <?php foreach ($rbfw_bike_car_sd_data as $value) {



                            ?>
                            <label>
                                <input type="radio" name="option" class="radio-input">
                                <span title="<?php echo esc_attr($value['short_desc']); ?>" data-duration="<?php echo esc_attr($value['duration']); ?>" data-price="<?php echo esc_attr($value['price']); ?>" data-d_type="<?php echo esc_attr($value['d_type']); ?>" data-start_time="<?php echo esc_attr($value['start_time']) ?? '' ?>" data-end_time="<?php echo esc_attr($value['end_time']) ?? '' ?>" class="radio-button single-type-timely"><?php echo esc_html($value['rent_type']); ?></span>
                            </label>
                        <?php } ?>
                    </div>


                    <div class="item rbfw_bikecarsd_price_summary">
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
                <?php } ?>


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