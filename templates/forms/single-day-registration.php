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

    /* Item Variations (e.g. Size S/M/L/XL) — supported on Single Day items. */
    $rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
    $rbfw_variations_data   = get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) : [];




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


                <?php
                // --- Flexible Rate box ---
                $_rbfw_sd_raw   = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true );
                $_rbfw_min_h    = 0;
                $_rbfw_start    = 0;
                if ( ! empty( $_rbfw_sd_raw ) ) {
                    $_h_list = array_filter( array_column( $_rbfw_sd_raw, 'hours' ) );
                    if ( ! empty( $_h_list ) ) { $_rbfw_min_h = (int) min( $_h_list ); }
                    $_p_list = array_filter( array_column( $_rbfw_sd_raw, 'price' ) );
                    if ( ! empty( $_p_list ) ) { $_rbfw_start = (float) min( $_p_list ); }
                }
                if ( ! $_rbfw_start ) {
                    $_rbfw_start = (float) rbfw_get_bike_car_md_hourly_daily_price( $rbfw_id, 'hourly' );
                }
                ?>
                <div class="rbfw-sd-rate-box">
                    <?php rbfw_fd_summary_badges(); ?>
                    <?php rbfw_fd_summary_title(); ?>
                    <?php rbfw_fd_summary_desc(); ?>
                    <?php if ( $_rbfw_start > 0 ) : ?>
                    <div class="rbfw-sd-rate-box-price-row">
                        <span class="rbfw-sd-rate-box-label"><?php esc_html_e( 'Starting from', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        <div class="rbfw-sd-rate-box-price">
                            <?php echo wp_kses( wc_price( $_rbfw_start ), rbfw_allowed_html() ); ?>
                        </div>
                        <?php if ( $_rbfw_min_h > 1 ) : ?>
                        <p class="rbfw-sd-rate-box-note">
                            <?php printf( esc_html__( 'Minimum %d-hour booking applies', 'booking-and-rental-manager-for-woocommerce' ), $_rbfw_min_h ); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="rbfw-sd-trust-grid">
                        <div class="rbfw-sd-trust-item">
                            <i class="far fa-check-circle"></i>
                            <span><?php esc_html_e( 'Instant confirmation', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw-sd-trust-item">
                            <i class="fas fa-lock"></i>
                            <span><?php esc_html_e( 'Secure payment', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <div class="rbfw-sd-trust-item">
                            <i class="far fa-calendar-times"></i>
                            <span><?php esc_html_e( 'Free cancellation', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                    </div>
                </div>

                <?php
                /* Item Variations (e.g. Size S/M/L/XL) — Single Day only.
                   For the standard (non-timely) flow the size selector is rendered under
                   the rate price table inside single_day_info.php (the AJAX step-3 content).
                   Timely-inventory mode does not use that AJAX table, so render the selector
                   here for timely items. Either way the choice is posted with "Book Now" and
                   per-size stock is enforced at add-to-cart by rbfw_check_rental_availability(). */
                if ( $rbfw_rent_type == 'bike_car_sd' && $manage_inventory_as_timely == 'on' && $rbfw_enable_variations == 'yes' && ! empty( $rbfw_variations_data ) ) { ?>
                    <div class="rbfw-variations-content-wrapper">
                        <?php foreach ( $rbfw_variations_data as $data_arr_one ) {
                            // Some saved/legacy variation rows may omit one or more keys; default
                            // them so the template never raises "Undefined array key" notices.
                            $field_label    = isset( $data_arr_one['field_label'] ) ? $data_arr_one['field_label'] : '';
                            $field_id       = isset( $data_arr_one['field_id'] ) ? $data_arr_one['field_id'] : '';
                            $field_values   = ! empty( $data_arr_one['value'] ) && is_array( $data_arr_one['value'] ) ? $data_arr_one['value'] : array();
                            $selected_value = ! empty( $data_arr_one['selected_value'] ) ? $data_arr_one['selected_value'] : '';
                            ?>
                            <div class="item">
                                <div class="rbfw-single-right-heading"><?php echo esc_html( $field_label ); ?></div>
                                <div class="item-content rbfw-p-relative">
                                    <?php if ( ! empty( $field_values ) ) { ?>
                                        <select class="rbfw-select rbfw_variation_field" required name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>" data-field="<?php echo esc_attr( $field_label ); ?>">
                                            <?php if ( empty( $selected_value ) ) { ?>
                                                <option value=""><?php echo esc_html( __( 'Choose', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $field_label ); ?></option>
                                            <?php } ?>
                                            <?php foreach ( $field_values as $data_arr_two ) {
                                                $variant_name = isset( $data_arr_two['name'] ) ? $data_arr_two['name'] : '';
                                                ?>
                                                <option class="rbfw_variant" value="<?php echo esc_attr( $variant_name ); ?>" <?php if ( $variant_name !== '' && $variant_name == $selected_value ) { echo 'selected'; } ?>><?php echo esc_html( $variant_name ); ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php  if($manage_inventory_as_timely !='on' || $rbfw_rent_type =='appointment'){ ?>
                    <div class="item rbfw-bikecarsd-step" data-step="1">
                        <div id="rbfw-bikecarsd-calendar" class="rbfw-bikecarsd-calendar">
                        </div>
                        <div class="rbfw-bikecarsd-calendar-footer">
                            <i class="fas fa-circle-info"></i>
                            <?php rbfw_string('rbfw_text_click_date_to_browse_availability',__('Click a date to browse availability','booking-and-rental-manager-for-woocommerce')); ?>
                        </div>
                    </div>

                    <?php include RBFW_TEMPLATE_PATH . 'forms/location-cards.php'; ?>

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

                    <?php
                    /* Timely-inventory mode has its own Rental Start Date field
                       above; the location cards follow it (dates → location). */
                    include RBFW_TEMPLATE_PATH . 'forms/location-cards.php';
                    ?>

                    <label class="rbfw-single-right-heading">
                        <?php esc_html_e('Rental Duration', 'booking-and-rental-manager-for-woocommerce'); ?>
                    </label>

                    <div class="rbfw_service_type rbfw_service_type_timely">
                        <?php foreach ($rbfw_bike_car_sd_data as $value) { ?>
                            <div title="<?php echo esc_attr($value['short_desc']); ?>" data-text="<?php echo esc_attr($value['rent_type']); ?>" data-available_quantity="<?php echo esc_attr( max( 0, (int) $rbfw_item_stock_quantity_timely ) ); ?>" data-duration="<?php echo esc_attr($value['duration']); ?>" data-price="<?php echo esc_attr($value['price']); ?>" data-d_type="<?php echo esc_attr($value['d_type']); ?>" data-start_time="<?php echo esc_attr($value['start_time']) ?? '' ?>" data-end_time="<?php echo esc_attr($value['end_time']) ?? '' ?>" class="radio-button single-type-timely ">
                                <label for="">
                                    <input type="radio" name="option" class="radio-input">
                                    <span class="rent-type"><?php echo esc_html($value['rent_type']); ?></span>
                                    <?php if($enable_specific_duration=='on'): ?>
                                        <div class="rbfw_time">
                                            <?php echo esc_html(  gmdate(get_option('time_format'), strtotime($value['start_time']))).' - '.esc_html(  gmdate(get_option('time_format'), strtotime($value['end_time']))); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="rbfw_time">
                                            <?php echo esc_html($value['duration']." ".$value['d_type']); ?>
                                        </div>
                                    <?php endif; ?>
                                </label>
                                <div class="price"><?php echo wp_kses(wc_price($value['price']) , rbfw_allowed_html()); ?></div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="rbfw_bikecarsd_pricing_table_container rbfw_quantiry_area_sd" style="display: none">
                        <div class="rbfw_bikecarsd_price_table timely_quqntity_table">
                            <span class="rbfw_bikecarsd_type_title">
                                <?php esc_html_e('Quantity','booking-and-rental-manager-for-woocommerce'); ?>
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

                    <div class="item rbfw-duration rbfw-duration-sd">
                        <div class="rbfw-single-right-heading">
                            <?php esc_html_e('Duration','booking-and-rental-manager-for-woocommerce'); ?>
                            <div class="item-content"></div>
                        </div>

                        <div class="rbfw-duration-date rbfw-duration-start-date">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Start Date','booking-and-rental-manager-for-woocommerce'); ?>
                                <div class="item-content"></div>
                            </div>
                        </div>

                        <div class="rbfw-duration-date rbfw-duration-end-date">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('End Date','booking-and-rental-manager-for-woocommerce'); ?>
                                <div class="item-content"></div>
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
                                        // Available qty defaults to the configured stock (no date is selected at
                                        // initial render); avoids an undefined-variable warning in the notice below.
                                        $max_es_available_qty = isset($value['service_qty']) ? $value['service_qty'] : 0;
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
                            $rbfw_fee_data = get_post_meta( $post_id, 'rbfw_fee_data', true );
                            $fee_management_cost_enable = false;
                            ?>

                            <?php if(!empty($rbfw_fee_data)){ ?>
                                <div class="item rbfw_resourse_md">
                                    <div class="rbfw-single-right-heading">
                                        <?php esc_html_e('Fee Management','booking-and-rental-manager-for-woocommerce'); ?>
                                    </div>
                                    <div class="item-content rbfw-resource">
                                        <table class="rbfw_bikecarmd_es_table">
                                            <tbody>
                                            <?php
                                            $c = 0;
                                            $rbfw_management_price = 0;
                                            foreach ($rbfw_fee_data as $key=>$fee) { $fee_management_cost_enable = true;  ?>
                                                <?php if(isset($fee['label'])){ ?>
                                                    <tr>
                                                        <td class="w_20 rbfw_bikecarmd_es_hidden_input_box">
                                                            <div class="label rbfw-checkbox">
                                                                <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][label]" value="<?php echo esc_attr($fee['label']); ?>">
                                                                <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][is_checked]" class="rbfw-management-qty" value="<?php echo (esc_attr($fee['priority'])=='required')?'yes':'' ?>">
                                                                <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][amount]"  value="<?php echo esc_attr($fee['amount']); ?>">
                                                                <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][calculation_type]"  value="<?php echo esc_attr($fee['calculation_type']); ?>">
                                                                <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][frequency]"  value="<?php echo esc_attr($fee['frequency']); ?>">
                                                                <input type="hidden" name="rbfw_management_info[<?php echo esc_attr($c); ?>][refundable]"  value="<?php echo esc_attr($fee['refundable']); ?>">
                                                                <label class="switch">
                                                                    <input type="checkbox" <?php echo (esc_attr($fee['priority'])=='required')?'checked':'' ?>   class="rbfw-management-price <?php echo (esc_attr($fee['priority'])=='required')?'rbfw-fee-required':'' ?> rbfw-resource-price-multiple-qty key_value_<?php echo esc_attr($key+1); ?>"   data-price="<?php echo esc_attr($fee['amount']); ?>" data-name="<?php echo esc_attr($fee['label']); ?>" data-price_type="<?php echo esc_attr($fee['calculation_type']); ?>" data-frequency="<?php echo esc_attr($fee['frequency']); ?>">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td class="resource-title-qty">
                                                            <?php echo esc_html($fee['label']); ?>
                                                            <span class="rbfw-refundable">
                                                                <?php
                                                                if($fee['refundable']=='yes'){
                                                                    esc_html_e('Refundable','booking-and-rental-manager-for-woocommerce');
                                                                }else{
                                                                    esc_html_e('Non refundable','booking-and-rental-manager-for-woocommerce');
                                                                }
                                                                ?>
                                                            </span>
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



                            <?php
                            /* Include Custom Registration Form */
                            if(class_exists('Rbfw_Reg_Form')){
                                $reg_form = new Rbfw_Reg_Form();
                                echo wp_kses($reg_form->rbfw_generate_regf_fields($rbfw_id),  rbfw_allowed_html());
                            }
                            /* End: Include Custom Registration Form */
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
                                            <li class="resource-costing extra_service_cost rbfw-cond" style="display: none">
                                                <?php esc_html_e('Resource Cost','booking-and-rental-manager-for-woocommerce'); ?>
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
                                        <?php if($fee_management_cost_enable){ ?>
                                            <li class="management-costing rbfw-cond">
                                                <?php esc_html_e('Management Cost','booking-and-rental-manager-for-woocommerce'); ?>
                                                <span class="price-figure" data-price="">
                                                </span>
                                            </li>
                                        <?php } ?>

                                        <li class="security_deposit" style="display:none;">
                                            <?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : __('Security Deposit','booking-and-rental-manager-for-woocommerce'))); ?>
                                            <span></span>
                                        </li>

                                        <li class="total">
                                            <strong><?php esc_html_e('Total','booking-and-rental-manager-for-woocommerce'); ?></strong>
                                            <span class="price-figure">
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

                <?php do_action('rbfw_add_term_condition',$rbfw_id) ?>

				<div class="item rbfw_bikecarsd_book_now_btn_wrap">
					<?php if ( ! rbfw_is_booking_available() ) { ?>
						<p class="rbfw_booking_unavailable_msg" style="background:#fff3cd;color:#856404;padding:10px 14px;border-left:4px solid #ffc107;border-radius:4px;margin:0 0 10px;font-size:13px;"><i class="fas fa-exclamation-triangle" style="margin-right:6px;color:#e0a800;"></i><?php esc_html_e( 'Booking currently not possible. Please contact us directly to complete your booking.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<button type="button" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_disabled_button" disabled style="opacity:.55;cursor:not-allowed;" title="<?php esc_attr_e( 'Booking is currently not possible. Please contact us directly.', 'booking-and-rental-manager-for-woocommerce' ); ?>">
							<?php esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce'); ?>
						</button>
					<?php } else { ?>
						<button type="submit" name="add-to-cart" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarsd_book_now_btn rbfw_disabled_button" disabled>
						<?php
							esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce');
						?>
						</button>
					<?php } ?>
				</div>

                <?php

                $time_slot_switch = !empty(get_post_meta($post_id, 'rbfw_time_slot_switch', true)) ? get_post_meta($post_id, 'rbfw_time_slot_switch', true) : 'on';
                $available_times = get_post_meta($post_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($post_id, 'rdfw_available_time', true)) : [];


              //  echo '<pre>';print_r($available_times);echo '<pre>';

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
                <input type="hidden" id="rbfw_block_offday_booking" value="<?php echo esc_attr(rbfw_block_offday_range_booking($post_id)); ?>">
                <input type="hidden" id="rbfw_month_wise_inventory" value='<?php echo esc_attr( wp_json_encode( rbfw_sd_sold_out_dates( $post_id ) ) ); ?>'>

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
