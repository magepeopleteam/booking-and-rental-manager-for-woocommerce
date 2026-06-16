<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}
$rbfw_id = $post_id ??0;

global $submit_name;

$cart_backend = $cart_backend??'';

if($cart_backend){
    $submit_name = 'admin-purchase';
}else{
    $submit_name = 'add-to-cart';
}




$rbfw_monthly_rate = get_post_meta($rbfw_id, 'rbfw_monthly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_monthly_rate', true) : 0;
$rbfw_weekly_rate = get_post_meta($rbfw_id, 'rbfw_weekly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_weekly_rate', true) : 0;
$daily_rate = get_post_meta($rbfw_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_daily_rate', true) : 0;
$hourly_rate = get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) : 0;

$rbfw_enable_monthly_rate           = get_post_meta( $rbfw_id, 'rbfw_enable_monthly_rate', true ) ;
$rbfw_enable_weekly_rate           = get_post_meta( $rbfw_id, 'rbfw_enable_weekly_rate', true );
$enable_daily_rate = get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) : 'yes';
$enable_hourly_rate = get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) : 'no';
$rbfw_enable_daywise_price = get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) : 'no';

//$availabe_time = rbfw_get_available_times($rbfw_id);
$availabe_time = get_post_meta($rbfw_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rdfw_available_time', true)) : [];

$off_dates_list = get_post_meta($rbfw_id, 'rbfw_off_dates', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_off_dates', true)) : [];

$location_switch = !empty(get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true)) ? get_post_meta($rbfw_id, 'rbfw_enable_pick_point', true) : '';
$pickup_location = get_post_meta($rbfw_id, 'rbfw_pickup_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_pickup_data', true)) : [];
$dropoff_location = get_post_meta($rbfw_id, 'rbfw_dropoff_data', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rbfw_dropoff_data', true)) : [];

$extra_service_list = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : [];

$enable_service_price =  get_post_meta($post_id, 'rbfw_enable_category_service_price', true) ? get_post_meta($post_id, 'rbfw_enable_category_service_price', true) : 'off';

$current_day = date_i18n('D');

$current_date = date_i18n('Y-m-d');


global $rbfw;


$rbfw_enable_md_type_item_qty = get_post_meta($rbfw_id, 'rbfw_enable_md_type_item_qty', true) ? get_post_meta($rbfw_id, 'rbfw_enable_md_type_item_qty', true) : 'no';

//echo $rbfw_enable_md_type_item_qty;exit;

$rbfw_enable_extra_service_qty = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) : 'no';

$rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
$rbfw_variations_data = get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_variations_data', true ) : [];

//echo '<pre>';print_r($rbfw_variations_data);echo '<pre>';exit;

$input_stock_quantity = '';
if($rbfw_enable_variations == 'yes'){
    $item_stock_quantity = rbfw_get_variations_stock($rbfw_id);
} else {
    $item_stock_quantity = !empty(get_post_meta($rbfw_id,'rbfw_item_stock_quantity',true)) ? get_post_meta($rbfw_id,'rbfw_item_stock_quantity',true) : 0;
    if(empty($item_stock_quantity)){
        $input_stock_quantity = 'no_has_value';
    }
}


$rbfw_enable_start_end_date  = get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) : 'yes';
$rbfw_event_start_date  = get_post_meta( $rbfw_id, 'rbfw_event_start_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_start_date', true ) : '';
$rbfw_event_start_time  = get_post_meta( $rbfw_id, 'rbfw_event_start_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_start_time', true ) : '';
$rbfw_event_start_time  = gmdate('h:i a', strtotime($rbfw_event_start_time));
$rbfw_event_end_date  = get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) : '';
$rbfw_event_end_time  = get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) : '';
$rbfw_event_end_time  = gmdate('h:i a', strtotime($rbfw_event_end_time));
$rbfw_event_last_date = strtotime(date_i18n('Y-m-d h:i a', strtotime($rbfw_event_end_date.' '.$rbfw_event_end_time)));
$rbfw_todays_date = strtotime(date_i18n('Y-m-d h:i a'));
$referal_page = '';

$rbfw_enable_time_picker = get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) ? get_post_meta($rbfw_id, 'rbfw_enable_time_picker', true) : 'no';

if ( isset( $_GET['rbfw_start_date'], $_GET['rbfw_end_date'] ) ) {
    $rbfw_start_date = sanitize_text_field( wp_unslash( $_GET['rbfw_start_date'] ) );
    $rbfw_end_date   = sanitize_text_field( wp_unslash( $_GET['rbfw_end_date'] ) );

    if ( $rbfw_start_date && $rbfw_end_date ) {
        $rbfw_enable_time_picker = 'no';
        $referal_page = 'search';
    }
}



$expire = 'no';
if($rbfw_enable_start_end_date=='no'){
    if($rbfw_event_last_date<$rbfw_todays_date){
        $expire = 'yes';
    }
}
$available_qty_info_switch = get_post_meta($rbfw_id, 'rbfw_available_qty_info_switch', true) ? get_post_meta($rbfw_id, 'rbfw_available_qty_info_switch', true) : 'no';

$pricing_types           = get_post_meta( $post_id, 'pricing_types', true ) ? get_post_meta( $post_id, 'pricing_types', true ) : [];
$multiple_items_info           = get_post_meta( $post_id, 'multiple_items_info', true ) ? get_post_meta( $post_id, 'multiple_items_info', true ) : [];
$enabled_pricing_types = [];
foreach ( [ 'hourly', 'daily', 'weekly', 'monthly' ] as $pricing_type ) {
    if ( isset( $pricing_types[ $pricing_type ] ) && $pricing_types[ $pricing_type ] == 'on' ) {
        $enabled_pricing_types[] = $pricing_type;
    }
}
$auto_selected_pricing_type = ! empty( $enabled_pricing_types ) ? current( $enabled_pricing_types ) : '';

$rbfw_enable_security_deposit = get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) ? get_post_meta($rbfw_id, 'rbfw_enable_security_deposit', true) : 'no';
$rbfw_security_deposit_type = get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_type', true) : 'percentage';
$rbfw_security_deposit_amount = get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_amount', true) : 0;
$rbfw_mi_hourly_to_half_day_pivot = get_post_meta($rbfw_id, 'rbfw_mi_hourly_to_half_day_pivot', true) ? get_post_meta($rbfw_id, 'rbfw_mi_hourly_to_half_day_pivot', true) : '';
$rbfw_mi_half_day_to_daily_pivot = get_post_meta($rbfw_id, 'rbfw_mi_half_day_to_daily_pivot', true) ? get_post_meta($rbfw_id, 'rbfw_mi_half_day_to_daily_pivot', true) : '';
$rbfw_mi_daily_to_weekly_pivot = get_post_meta($rbfw_id, 'rbfw_mi_daily_to_weekly_pivot', true) ? get_post_meta($rbfw_id, 'rbfw_mi_daily_to_weekly_pivot', true) : '';
$rbfw_mi_weekly_to_monthly_pivot = get_post_meta($rbfw_id, 'rbfw_mi_weekly_to_monthly_pivot', true) ? get_post_meta($rbfw_id, 'rbfw_mi_weekly_to_monthly_pivot', true) : '';

$rbfw_particular_switch = get_post_meta( $post_id, 'rbfw_particular_switch', true ) ? get_post_meta( $post_id, 'rbfw_particular_switch', true ) : 'off';
$particulars_data = get_post_meta( $rbfw_id, 'rbfw_particulars_data', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rbfw_particulars_data', true ) ) : [];
$rdfw_available_time = get_post_meta( $rbfw_id, 'rdfw_available_time', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rdfw_available_time', true ) ) : [];
$rbfw_buffer_time = get_post_meta( $rbfw_id, 'rbfw_buffer_time', true ) ? maybe_unserialize( get_post_meta( $rbfw_id, 'rbfw_buffer_time', true ) ) : 0;
$fee_management_cost_enable = false;


?>
<?php if($expire == 'yes'){ ?>
    <h3><?php esc_html_e( 'Date Expired !', 'booking-and-rental-manager-for-woocommerce' ); ?></h3>
    <?php die;  ?>
<?php } ?>

<?php
// Minimum starting price across all items and enabled pricing types for the rate box
$_rbfw_mi_price_pool = [];
if ( ! empty( $multiple_items_info ) && ! empty( $enabled_pricing_types ) ) {
    foreach ( $multiple_items_info as $_mi_item ) {
        foreach ( $enabled_pricing_types as $_mi_ptype ) {
            $_mi_price_key = $_mi_ptype . '_price';
            if ( ! empty( $_mi_item[ $_mi_price_key ] ) && (float) $_mi_item[ $_mi_price_key ] > 0 ) {
                $_rbfw_mi_price_pool[] = (float) $_mi_item[ $_mi_price_key ];
            }
        }
    }
}
$_rbfw_mi_min_price = ! empty( $_rbfw_mi_price_pool ) ? min( $_rbfw_mi_price_pool ) : 0;
$_rbfw_mi_unit_map  = [
    'hourly'  => __( 'Hour',  'booking-and-rental-manager-for-woocommerce' ),
    'daily'   => __( 'Day',   'booking-and-rental-manager-for-woocommerce' ),
    'weekly'  => __( 'Week',  'booking-and-rental-manager-for-woocommerce' ),
    'monthly' => __( 'Month', 'booking-and-rental-manager-for-woocommerce' ),
];
$_rbfw_mi_price_unit = ( ! empty( $auto_selected_pricing_type ) && isset( $_rbfw_mi_unit_map[ $auto_selected_pricing_type ] ) )
    ? $_rbfw_mi_unit_map[ $auto_selected_pricing_type ]
    : '';
?>

<div class="rbfw-single-container" data-service-id="<?php echo esc_attr($rbfw_id); ?>">
    <div class="rbfw-single-right-container">

        <div class="rbfw-sd-rate-box">
            <div class="rbfw-sd-rate-box-badges">
                <span class="rbfw-sd-badge rbfw-sd-badge--available">
                    <span class="rbfw-sd-badge-dot"></span>
                    <?php esc_html_e( 'Available Today', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </span>
                <span class="rbfw-sd-badge rbfw-sd-badge--seller">
                    <?php esc_html_e( 'Best Seller', 'booking-and-rental-manager-for-woocommerce' ); ?>
                </span>
            </div>
            <h3 class="rbfw-sd-rate-box-title">
                <?php esc_html_e( 'Instant Booking Summary', 'booking-and-rental-manager-for-woocommerce' ); ?>
            </h3>
            <p class="rbfw-sd-rate-box-desc">
                <?php esc_html_e( 'Select dates to see final price and availability in real time.', 'booking-and-rental-manager-for-woocommerce' ); ?>
            </p>
            <?php if ( $_rbfw_mi_min_price > 0 ) : ?>
            <div class="rbfw-sd-rate-box-price-row">
                <span class="rbfw-sd-rate-box-label"><?php esc_html_e( 'Starting from', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                <div class="rbfw-sd-rate-box-price">
                    <?php echo wp_kses( wc_price( $_rbfw_mi_min_price ), rbfw_allowed_html() ); ?>
                    <?php if ( $_rbfw_mi_price_unit ) : ?>
                        <span class="rbfw-sd-rate-per">/ <?php echo esc_html( $_rbfw_mi_price_unit ); ?></span>
                    <?php endif; ?>
                </div>
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

        <form action="" method='post' class="mp_rbfw_ticket_form">
            <div class="rbfw_bike_car_md_item_wrapper">
                <div class="rbfw_multi_items_wrapper_inner">
                    <?php do_action('rbfw_discount_ad', $rbfw_id); ?>
                    <div class="item pricing-content-container">
                        <?php do_action('rbfw_pricing_info_header'); ?>
                        <div class="price-item-container">
                            <span class="close-price-container"><i class="mi mi-x"></i></span>
                            <div class="mpStyle">
                                <div class="rbfw_day_wise_price">
                                    <table>
                                        <tbody>

                                        <tr>
                                            <td><strong><?php esc_html_e('Items', 'booking-and-rental-manager-for-woocommerce'); ?></strong></td>
                                            <?php if(isset($pricing_types['hourly']) && $pricing_types['hourly']=='on'){ ?>
                                                <td><?php esc_html_e('Hourly Price','booking-and-rental-manager-for-woocommerce'); ?> </td>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['daily']) && $pricing_types['daily']=='on'){ ?>
                                                <td><?php esc_html_e('Daily Price','booking-and-rental-manager-for-woocommerce'); ?> </td>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['weekly']) && $pricing_types['weekly']=='on'){ ?>
                                                <td><?php esc_html_e('Weekly Price','booking-and-rental-manager-for-woocommerce'); ?></td>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['monthly']) && $pricing_types['monthly']=='on'){ ?>
                                                <td><?php esc_html_e('Monthly Price','booking-and-rental-manager-for-woocommerce'); ?></td>
                                            <?php } ?>
                                        </tr>

                                        <?php foreach ($multiple_items_info as $key=>$item_price){   ?>

                                        <tr>
                                            <td><strong><?php echo esc_html($item_price['item_name']); ?></strong></td>
                                            <?php if(isset($pricing_types['hourly']) && $pricing_types['hourly']=='on'){ ?>
                                                <td><?php echo wc_price($item_price['hourly_price']) ?> / <?php esc_html_e('Hour','booking-and-rental-manager-for-woocommerce'); ?></td>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['daily']) && $pricing_types['daily']=='on'){ ?>
                                                <td><?php echo wc_price($item_price['daily_price']) ?> / <?php esc_html_e('Day','booking-and-rental-manager-for-woocommerce'); ?></td>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['weekly']) && $pricing_types['weekly']=='on'){ ?>
                                                <td><?php echo wc_price($item_price['weekly_price']) ?> / <?php esc_html_e('Week','booking-and-rental-manager-for-woocommerce'); ?></td>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['monthly']) && $pricing_types['monthly']=='on'){ ?>
                                                <td><?php echo wc_price($item_price['monthly_price']) ?> / <?php esc_html_e('Month','booking-and-rental-manager-for-woocommerce'); ?></td>
                                            <?php } ?>


                                        </tr>

                                        <?php } ?>



                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pickup_date "></div>

                    <?php if ($location_switch == 'yes' && !empty($pickup_location)) : ?>
                        <div class="item rbfw-mi-location-card">
                            <div class="rbfw-single-right-heading"><?php esc_html_e('Pickup Location','booking-and-rental-manager-for-woocommerce'); ?></div>
                            <div class="item-content rbfw-location">
                                <span class="location-icon"><i class="fas fa-location-dot"></i></span>
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
                        <div class="item rbfw-mi-location-card">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Drop-off Location','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
                            <div class="item-content rbfw-location">
                                <span class="location-icon"><i class="fas fa-location-dot"></i></span>
                                <select class="rbfw-select" name="rbfw_dropoff_point" required>
                                    <option value=""><?php esc_html_e('Choose drop-off location','booking-and-rental-manager-for-woocommerce'); ?></option>
                                    <?php foreach ($dropoff_location as $dropoff) : ?>
                                        <option value="<?php echo esc_attr($dropoff['loc_dropoff_name']); ?>"><?php echo esc_html($dropoff['loc_dropoff_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>


                    <input type="hidden" name="rbfw_off_days" id="rbfw_off_days"  value='<?php echo esc_attr(rbfw_off_days($post_id)); ?>'>
                    <input type="hidden" name="rbfw_offday_range" id="rbfw_offday_range"  value='<?php echo esc_attr(rbfw_off_dates($post_id)); ?>'>


                    <div class="rbfw_select_rental_period">
                        <div class="item">
                            <div class="item-content rbfw-datetime">
                                <div class="left rbfw-duration-type-field">
                                    <div class="rbfw-single-right-heading">
                                        <?php esc_html_e('Rental Duration Type','booking-and-rental-manager-for-woocommerce'); ?>
                                    </div>
                                    <div class="rbfw-p-relative">
                                        <select class="rbfw-select" name="durationType" id="durationType" required>
                                            <?php if ( empty( $auto_selected_pricing_type ) ) { ?>
                                                <option value=""><?php esc_html_e('Select duration type','booking-and-rental-manager-for-woocommerce'); ?></option>
                                            <?php } ?>

                                            <?php if(isset($pricing_types['hourly']) && $pricing_types['hourly']=='on'){ ?>
                                                <option value="hourly" <?php selected( $auto_selected_pricing_type, 'hourly' ); ?>><?php esc_html_e('Hourly','booking-and-rental-manager-for-woocommerce'); ?></option>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['daily']) && $pricing_types['daily']=='on'){ ?>
                                                <option value="daily" <?php selected( $auto_selected_pricing_type, 'daily' ); ?>><?php esc_html_e('Daily','booking-and-rental-manager-for-woocommerce'); ?></option>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['weekly']) && $pricing_types['weekly']=='on'){ ?>
                                                <option value="weekly" <?php selected( $auto_selected_pricing_type, 'weekly' ); ?>><?php esc_html_e('Weekly','booking-and-rental-manager-for-woocommerce'); ?></option>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['monthly']) && $pricing_types['monthly']=='on'){ ?>
                                                <option value="monthly" <?php selected( $auto_selected_pricing_type, 'monthly' ); ?>><?php esc_html_e('Monthly','booking-and-rental-manager-for-woocommerce'); ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="rbfw-mi-duration-tabs" aria-label="<?php esc_attr_e('Rental Duration Type','booking-and-rental-manager-for-woocommerce'); ?>">
                                            <?php if(isset($pricing_types['hourly']) && $pricing_types['hourly']=='on'){ ?>
                                                <button type="button" class="rbfw-mi-duration-tab" data-duration-type="hourly"><?php esc_html_e('Hourly','booking-and-rental-manager-for-woocommerce'); ?></button>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['daily']) && $pricing_types['daily']=='on'){ ?>
                                                <button type="button" class="rbfw-mi-duration-tab" data-duration-type="daily"><?php esc_html_e('Daily','booking-and-rental-manager-for-woocommerce'); ?></button>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['weekly']) && $pricing_types['weekly']=='on'){ ?>
                                                <button type="button" class="rbfw-mi-duration-tab" data-duration-type="weekly"><?php esc_html_e('Weekly','booking-and-rental-manager-for-woocommerce'); ?></button>
                                            <?php } ?>
                                            <?php if(isset($pricing_types['monthly']) && $pricing_types['monthly']=='on'){ ?>
                                                <button type="button" class="rbfw-mi-duration-tab" data-duration-type="monthly"><?php esc_html_e('Monthly','booking-and-rental-manager-for-woocommerce'); ?></button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="right time rbfw-duration-qty-field">
                                    <div class="rbfw-single-right-heading" id="qtyLabel" data-duration-label="<?php esc_attr_e('Rental Duration','booking-and-rental-manager-for-woocommerce'); ?>">
                                        <?php esc_html_e('Rental Duration','booking-and-rental-manager-for-woocommerce'); ?>
                                    </div>
                                    <div class="rbfw-p-relative">
                                        <span class="clock">
                                            <i class="fa-regular fa-clock"></i>
                                        </span>
                                        <select class="rbfw-select" id="rbfw_mi_duration_qty_select">
                                            <?php for ( $duration_qty = 1; $duration_qty <= 30; $duration_qty++ ) { ?>
                                                <option value="<?php echo esc_attr( $duration_qty ); ?>"><?php echo esc_html( $duration_qty ); ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                        <input type="hidden" id="durationQty" name="durationQty" class="qty-value" min="1" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="item-content rbfw-datetime rbfw-pickup-datetime" style="grid-template-columns: 1fr;">
                                <div class="<?php echo ($rbfw_enable_time_picker=='yes')?'left':'' ?> date">
                                    <div class="rbfw-single-right-heading">
                                        <?php esc_html_e('Pickup Date','booking-and-rental-manager-for-woocommerce'); ?>
                                    </div>
                                    <div class="rbfw-p-relative">
                                        <span class="calendar"><i class="fas fa-calendar-days"></i></span>
                                        <?php if($referal_page == 'search'){ ?>
                                            <input type="hidden" id="hidden_pickup_date" value="<?php echo esc_attr($rbfw_start_date)  ?>" name="rbfw_pickup_start_date">
                                            <input class="rbfw-input rbfw-time-price pickup_date" type="text" value="<?php echo esc_attr(rbfw_date_format($rbfw_start_date))  ?>"  id="pickup_date" placeholder="<?php esc_attr_e('Pickup Date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                        <?php }else{ ?>
                                            <input type="hidden" id="hidden_pickup_date" name="rbfw_pickup_start_date">
                                            <input class="rbfw-input rbfw-time-price pickup_date" type="text"  id="pickup_date" placeholder="<?php esc_attr_e('Pickup Date','booking-and-rental-manager-for-woocommerce'); ?>" required readonly="" <?php if($enable_hourly_rate == 'no'){ echo 'style="background-position: 95% center"'; }?>>
                                        <?php } ?>
                                        <span class="input-picker-icon"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </div>

                                <?php if($rbfw_enable_time_picker=='yes'){ ?>

                                    <div class="right time">
                                        <div class="rbfw-single-right-heading">
                                            <?php esc_html_e('Pickup Time','booking-and-rental-manager-for-woocommerce'); ?>
                                        </div>
                                        <div class="rbfw-p-relative">
                                        <span class="clock">
                                            <i class="fa-regular fa-clock"></i>
                                        </span>
                                            <select class="rbfw-select rbfw-time-price pickup_time" name="rbfw_pickup_start_time" id="pickup_time" required>
                                                <option value="" disabled selected><?php esc_html_e('Pickup Time','booking-and-rental-manager-for-woocommerce'); ?></option>
                                            </select>
                                            <span class="input-picker-icon"></span>
                                        </div>
                                    </div>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
    
                    <div class="item rbfw-duration">
                        <div class="rbfw-single-right-heading">
                            <?php esc_html_e('Duration','booking-and-rental-manager-for-woocommerce'); ?>
                            <div class="item-content"></div>
                        </div>

                        <div class="rbfw-duration-date rbfw-duration-start-date">
                            <div class="rbfw-single-right-heading">
                                <span class="rbfw-duration-start-label"><?php esc_html_e('Start Date','booking-and-rental-manager-for-woocommerce'); ?></span>
                                <div class="item-content"></div>
                            </div>
                        </div>

                        <div class="rbfw-duration-date rbfw-duration-end-date">
                            <div class="rbfw-single-right-heading">
                                <span class="rbfw-duration-end-label"><?php esc_html_e('End Date','booking-and-rental-manager-for-woocommerce'); ?></span>
                                <div class="item-content"></div>
                            </div>
                        </div>
                        
                        <input type="hidden" class="rbfw_duration_md" name="rbfw_duration_md">
                    </div>
                    
                    <?php  if(!empty($multiple_items_info)){ ?>

                        <div class="item rbfw_resourse_md" style="display: none">
                                <div class="rbfw-single-right-heading">
                                    <?php esc_html_e('Select Item to Rent','booking-and-rental-manager-for-woocommerce'); ?>
                                </div>
                                <div class="item-content rbfw-resource">

                                    <div class="rbfw_bikecarmd_es_table">
                                        <?php
                                        $c = 0;
                                        foreach ($multiple_items_info as $key=>$item) { ?>
                                            <?php if(isset($item['item_name']) && $item['available_qty'] > 0){ ?>
                                                <div class="rbfw-resource-item">
                                                    <div class="resource-title-qty">
                                                        <?php echo esc_html($item['item_name']); ?>
                                                        <div style="font-size: 12px" class="item-price">
                                                            <?php if(isset($pricing_types['hourly']) && $pricing_types['hourly']=='on'){ ?>
                                                                <span class="rbfw_hourly_price" style="display: none"><?php echo wc_price($item['hourly_price']) ?> / <?php esc_html_e('Hour','booking-and-rental-manager-for-woocommerce'); ?></span>
                                                            <?php } ?>
                                                            <?php if(isset($pricing_types['daily']) && $pricing_types['daily']=='on'){ ?>
                                                                <span class="rbfw_daily_price" style="display: none"><?php echo wc_price($item['daily_price']) ?> / <?php esc_html_e('Day','booking-and-rental-manager-for-woocommerce'); ?></span>
                                                            <?php } ?>
                                                            <?php if(isset($pricing_types['weekly']) && $pricing_types['weekly']=='on'){ ?>
                                                                <span class="rbfw_weekly_price" style="display: none"><?php echo wc_price($item['weekly_price']) ?> / <?php esc_html_e('Week','booking-and-rental-manager-for-woocommerce'); ?></span>
                                                            <?php } ?>
                                                            <?php if(isset($pricing_types['monthly']) && $pricing_types['monthly']=='on'){ ?>
                                                                <span class="rbfw_monthly_price" style="display: none"><?php echo wc_price($item['monthly_price']) ?> / <?php esc_html_e('Month','booking-and-rental-manager-for-woocommerce'); ?></span>
                                                            <?php } ?>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <?php  if($available_qty_info_switch == 'yes'){ ?>
                                                            <i class="resource-qty"><?php esc_html_e('Available Qty ','booking-and-rental-manager-for-woocommerce') ?><span class="es_stock"><?php echo '('.esc_html($item['available_qty']).')'; ?></span></i>
                                                        <?php } ?>
                                                    </div>


                                                    <div class="rbfw_multi_items_input_box">
                                                        <div class="rbfw_qty_input">
                                                            <a class="rbfw_qty_minus rbfw_multi_items_qty_minus" data-item="<?php echo esc_attr($key+1); ?>"><i class="fas fa-minus"></i></a>
                                                            <input type="hidden" name="multiple_items_info[<?php echo esc_attr($c); ?>][item_price]" class="rbfw_item_peice">
                                                            <input name="multiple_items_info[<?php echo esc_attr($c); ?>][item_qty]" type="number" min="0" max="<?php echo esc_html($item['available_qty']); ?>" value="0" class="rbfw_muiti_items_qty"  data-cat="service" data-item="<?php echo esc_attr($key+1); ?>" data-price-hourly="<?php echo esc_attr($item['hourly_price']); ?>" data-price-daily="<?php echo esc_attr($item['daily_price']); ?>" data-price-weekly="<?php echo esc_attr($item['weekly_price']); ?>" data-price-monthly="<?php echo esc_attr($item['monthly_price']); ?>" data-name="<?php echo esc_attr($item['item_name']); ?>"/>
                                                            <a class="rbfw_qty_plus rbfw_multi_items_qty_plus" data-item="<?php echo esc_attr($key+1); ?>"><i class="fas fa-plus"></i></a>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="multiple_items_info[<?php echo esc_attr($c); ?>][item_name]" value="<?php echo esc_attr($item['item_name']); ?>">
                                                </div>
                                            <?php } ?>
                                            <?php $c++; } ?>
                                    </div>
                                </div>


                            <?php

                            $rbfw_fee_data = get_post_meta( $post_id, 'rbfw_fee_data', true );
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
                                            foreach ($rbfw_fee_data as $key=>$fee) { ?>
                                                <?php if(isset($fee['label'])){ $fee_management_cost_enable = true; ?>
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
                        </div>
                    <?php } ?>
                </div>


                <div class="rbfw_rental_pricing_breakdown">
                    <div class="rbfw_bikecarmd_price_result">
                        
                        <div class="item-content rbfw-costing">
                            <div class="rbfw-single-right-heading">
                                <?php esc_html_e('Summary','booking-and-rental-manager-for-woocommerce'); ?>
                            </div>
                            
                            <ul class="rbfw-ul" id="rbfw-items-summary">

                            </ul>
                            
                            <ul class="rbfw-ul">

                                <li id="AddonsPrice" style="display: none">
                                    <?php esc_html_e('Add-ons Price','booking-and-rental-manager-for-woocommerce') ?> <span></span>
                                </li>


                                <li class="subtotal">
                                    <?php esc_html_e('Subtotal','booking-and-rental-manager-for-woocommerce'); ?>
                                    <span class="price-figure" data-price="">
                                    </span>
                                </li>

                                <?php if($fee_management_cost_enable){ ?>
                                    <li class="management-costing rbfw-cond">
                                        <?php esc_html_e('Management Cost','booking-and-rental-manager-for-woocommerce'); ?>
                                        <span class="price-figure" data-price="">
                                        </span>
                                    </li>
                                <?php } ?>

                                <li class="discount" style="display:none;">
                                    <?php esc_html_e('Discount','booking-and-rental-manager-for-woocommerce'); ?>
                                    <span></span>
                                </li>

                                <li class="security_deposit" style="display:none;">
                                    <?php echo esc_html((!empty(get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true)) ? get_post_meta($rbfw_id, 'rbfw_security_deposit_label', true) : __('Security Deposit','booking-and-rental-manager-for-woocommerce'))); ?>
                                    <span></span>
                                </li>

                                <li class="total">
                                    <?php esc_html_e('Total Price','booking-and-rental-manager-for-woocommerce'); ?>
                                    <span class="price-figure" data-price="">
                                    </span>
                                </li>

                            </ul>
                            <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
                        </div>
                    </div>
                </div>

                <?php


                $rbfw_service_category_price = get_post_meta($post_id, 'rbfw_service_category_price', true);
                if(!is_array($rbfw_service_category_price)){
                    $rbfw_service_category_price = json_decode($rbfw_service_category_price, true);
                }

                $option_value  = is_serialized($rbfw_service_category_price) ? unserialize($rbfw_service_category_price) : $rbfw_service_category_price;

                $rbfw_has_extra_services = false;
                if ( ! empty( $option_value ) && $enable_service_price === 'on' ) {
                    foreach ( $option_value as $_rbfw_cat => $_rbfw_cat_item ) {
                        if ( ! empty( $_rbfw_cat_item['cat_services'] ) ) {
                            foreach ( $_rbfw_cat_item['cat_services'] as $_rbfw_service ) {
                                if ( ! empty( $_rbfw_service['title'] ) ) {
                                    $rbfw_has_extra_services = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if ( $rbfw_has_extra_services ) {
                    ?>
                    <div class="multi-service-category-section" style="display: none">
                        <?php foreach ($option_value as $cat => $item) { ?>
                        <?php if (isset($item['cat_services']) && !empty($item['cat_services'])) { ?>
                            <div class="servise-item">
                                <div class="rbfw-single-right-heading"><?php esc_html_e('Optional Add-ons','booking-and-rental-manager-for-woocommerce'); ?></div>
                                <input type="hidden" name="rbfw_category_wise_info[<?php echo esc_attr($cat); ?>][cat_title]" value="<?php echo esc_attr($item['cat_title']); ?>">
                                <div class="item-content rbfw-resource">
                                    <div class="rbfw_bikecarmd_es_table">

                                            <?php foreach ($item['cat_services'] as $serkey => $service) { ?>
                                                <?php if (!empty($service['title'])) { ?>
                                                    <div class="service-price-item">
                                                        <div>
                                                            <div class="title">
                                                                <?php if($service['icon']){ ?>
                                                                    <i class="sc-icon <?php echo esc_attr($service['icon']); ?>"></i>
                                                                <?php } ?>
                                                                <?php echo esc_html($service['title']); ?>
                                                                <?php if($available_qty_info_switch == 'yes'){ ?>
                                                                    <i class="available-stock item_<?php echo esc_attr($cat . $serkey); ?>">
                                                                        <?php esc_html_e('Available Qty ', 'booking-and-rental-manager-for-woocommerce'); ?><span class="remaining_stock"></span>
                                                                    </i>
                                                                <?php } ?>
                                                            </div>
                                                            <div style="font-size: 12px;">
                                                                <span class="title"><?php echo wp_kses(wc_price($service['price']),rbfw_allowed_html()); ?></span>
                                                                <span class="day-time-wise"><?php echo (isset($service['service_price_type'] ) && $service['service_price_type'] === 'day_wise') ? esc_html__('Day Wise', 'booking-and-rental-manager-for-woocommerce') : esc_html__('One Time', 'booking-and-rental-manager-for-woocommerce'); ?></span>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" value="<?php echo $service['title'] ?>" name="rbfw_category_wise_info[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][name]"/>
                                                        <input type="hidden" value="<?php echo $service['service_price_type'] ?>" name="rbfw_category_wise_info[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][service_price_type]"/>
                                                        <input type="hidden" value="<?php echo $service['price'] ?>" name="rbfw_category_wise_info[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][price]"/>
                                                        <div class="item_<?php echo esc_attr($cat . $serkey); ?>">
                                                            <div class="rbfw_qty_input">
                                                                <a class="rbfw_additional_service_qty_minus rbfw_qty_minus" data-item="<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <i class="fas fa-minus"></i>
                                                                </a>
                                                                <input type="number" value="0" name="rbfw_category_wise_info[<?php echo esc_attr($cat); ?>][<?php echo esc_attr($serkey); ?>][quantity]" min="0" class="rbfw_muiti_items_additional_service_qty" data-price="<?php echo esc_attr($service['price']); ?>" data-service_price_type="<?php echo esc_attr($service['service_price_type']); ?>" data-item="<?php echo esc_attr($cat . $serkey); ?>" autocomplete="off"/>
                                                                <a class="rbfw_additional_service_qty_plus rbfw_qty_plus" data-item="<?php echo esc_attr($cat . $serkey); ?>">
                                                                    <i class="fas fa-plus"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>

                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>


                <?php wp_nonce_field('rbfw_ajax_action', 'nonce'); ?>

                <input type="hidden" name="rbfw_duration_price" id="rbfw_duration_price" value="0">
                <input type="hidden" name="rbfw_service_category_price" id="rbfw_service_category_price"  value="0">

                <input type="hidden" name="rbfw_management_price" id="rbfw_management_price"  value="0">

                <input type="hidden" name="rbfw_security_deposit_enable" id="rbfw_security_deposit_enable"  value="<?php echo esc_attr($rbfw_enable_security_deposit); ?>">
                <input type="hidden" name="rbfw_security_deposit_type" id="rbfw_security_deposit_type"  value="<?php echo esc_attr($rbfw_security_deposit_type); ?>">
                <input type="hidden" name="rbfw_security_deposit_amount" id="rbfw_security_deposit_amount"  value="<?php echo esc_attr($rbfw_security_deposit_amount); ?>">
                <input type="hidden" id="rbfw_mi_hourly_to_half_day_pivot" value="<?php echo esc_attr($rbfw_mi_hourly_to_half_day_pivot); ?>">
                <input type="hidden" id="rbfw_mi_half_day_to_daily_pivot" value="<?php echo esc_attr($rbfw_mi_half_day_to_daily_pivot); ?>">
                <input type="hidden" id="rbfw_mi_daily_to_weekly_pivot" value="<?php echo esc_attr($rbfw_mi_daily_to_weekly_pivot); ?>">
                <input type="hidden" id="rbfw_mi_weekly_to_monthly_pivot" value="<?php echo esc_attr($rbfw_mi_weekly_to_monthly_pivot); ?>">




                <input type="hidden" name="rbfw_rent_type" id="rbfw_rent_type"  value="multiple_items">
                <input type="hidden" name="rbfw_post_id" id="rbfw_post_id"  value="<?php echo esc_attr($rbfw_id); ?>">
                <input type="hidden" name="rbfw_enable_time_slot" id="rbfw_enable_time_slot"  value="<?php echo esc_attr($rbfw_enable_time_picker); ?>">
                <input type="hidden" name="total_days" id="rbfw_total_days">

                <input type="hidden" name="rbfw_particular_switch" id="rbfw_particular_switch"  value='<?php echo esc_attr($rbfw_particular_switch); ?>'>
                <input type="hidden" name="rbfw_particulars_data" id="rbfw_particulars_data"  value='<?php echo esc_attr(wp_json_encode($particulars_data)); ?>'>
                <input type="hidden" name="rdfw_available_time" id="rdfw_available_time"  value='<?php echo esc_attr(wp_json_encode($rdfw_available_time)); ?>'>
                <input type="hidden" name="rbfw_buffer_time" id="rbfw_buffer_time"  value='<?php echo esc_attr($rbfw_buffer_time); ?>'>





                <?php if(rbfw_chk_regf_fields_exist($rbfw_id) === true){ ?>
                    <div class="item">
                        <div class="rbfw_reg_form_rb" style="display: none">
                            <?php
                            $reg_form = new Rbfw_Reg_Form();
                            echo wp_kses($reg_form->rbfw_generate_regf_fields($post_id),rbfw_allowed_html());
                            ?>
                        </div>

                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                        <?php do_action('rbfw_ticket_feature_info'); ?>
                        <?php do_action('rbfw_add_term_condition',$rbfw_id) ?>
                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="rbfw_mps_book_now_btn_regf_____ mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn"  disabled >
                            <?php esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce'); ?>
                        </button>
                    </div>
                <?php } else{ ?>
                    <div class="item">
                        <?php $rbfw_product_id = get_post_meta( $rbfw_id, 'link_wc_product', true ) ? get_post_meta( $rbfw_id, 'link_wc_product', true ) : get_the_ID(); ?>
                        <?php do_action('rbfw_add_term_condition',$rbfw_id) ?>
                        <button type="submit" name="<?php echo esc_attr($submit_name); ?>" value="<?php echo esc_attr($rbfw_product_id); ?>" class="mp_rbfw_book_now_submit single_add_to_cart_button button alt btn-mep-event-cart rbfw-book-now-btn rbfw_bikecarmd_book_now_btn" disabled <?php if( $rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date ) { echo 'style="display:none"'; }?>>
                            <?php esc_html_e('Book Now','booking-and-rental-manager-for-woocommerce'); ?>
                        </button>
                    </div>
                <?php } ?>

                <?php if($rbfw_enable_start_end_date == 'no' && $rbfw_event_last_date < $rbfw_todays_date) {
                    echo '<div class="mps_alert_warning">'.esc_html__('Booking Time Expired!','booking-and-rental-manager-for-woocommerce').'</div>';
                } ?>
            </div>

            <div class="rbfw-bikecarmd-result-wrap">
                <div class="rbfw-bikecarmd-result-loader">
                </div>
                <div class="rbfw-bikecarmd-result">
                </div>
            </div>

        </form>
    </div>
</div>



