<?php
/*********************************
 * Rent List Shortcode List Style
 *********************************/
global $rbfw;
$post_id            = get_the_id();
$post_title         = get_the_title();
$post_featured_img  = !empty(get_the_post_thumbnail_url( $post_id, 'full' )) ? get_the_post_thumbnail_url( $post_id, 'full' ) : RBFW_PLUGIN_URL. '/assets/images/no_image.png';

$post_link          = get_the_permalink();
$book_now_label     = $rbfw->get_option_trans('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce'));
$post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating() : '';

$post_content       = $the_content;
$post_content       = strlen($post_content) >= 40 ? substr($post_content, 0, 100) . '...' : $post_content;

$hourly_rate_label = $rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly rate','booking-and-rental-manager-for-woocommerce'));
$daily_rate_label = $rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings', __('Daily rate','booking-and-rental-manager-for-woocommerce'));
$rbfw_enable_hourly_rate = get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) : 'no';
$rbfw_enable_daily_rate  = get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) : 'no';

if($rbfw_enable_hourly_rate == 'no'){
    $the_price_label = $daily_rate_label;
} else {
    $the_price_label = $hourly_rate_label;
}

$prices_start_at = $rbfw->get_option_trans('rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __('Prices start at','booking-and-rental-manager-for-woocommerce'));
$rbfw_rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );

if($rbfw_enable_hourly_rate == 'yes'){

    $price = get_post_meta($post_id, 'rbfw_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_hourly_rate', true) : 0;
    $price_sun = get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) : 0;
    $price_mon = get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) : 0;
    $price_tue = get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) : 0;
    $price_wed = get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) : 0;
    $price_thu = get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) : 0;
    $price_fri = get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) : 0;
    $price_sat = get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) : 0;

} else {

    $price = get_post_meta($post_id, 'rbfw_daily_rate', true) ? get_post_meta($post_id, 'rbfw_daily_rate', true) : 0;
    $price_sun = get_post_meta($post_id, 'rbfw_sun_daily_rate', true) ? get_post_meta($post_id, 'rbfw_sun_daily_rate', true) : 0;
    $price_mon = get_post_meta($post_id, 'rbfw_mon_daily_rate', true) ? get_post_meta($post_id, 'rbfw_mon_daily_rate', true) : 0;
    $price_tue = get_post_meta($post_id, 'rbfw_tue_daily_rate', true) ? get_post_meta($post_id, 'rbfw_tue_daily_rate', true) : 0;
    $price_wed = get_post_meta($post_id, 'rbfw_wed_daily_rate', true) ? get_post_meta($post_id, 'rbfw_wed_daily_rate', true) : 0;
    $price_thu = get_post_meta($post_id, 'rbfw_thu_daily_rate', true) ? get_post_meta($post_id, 'rbfw_thu_daily_rate', true) : 0;
    $price_fri = get_post_meta($post_id, 'rbfw_fri_daily_rate', true) ? get_post_meta($post_id, 'rbfw_fri_daily_rate', true) : 0;
    $price_sat = get_post_meta($post_id, 'rbfw_sat_daily_rate', true) ? get_post_meta($post_id, 'rbfw_sat_daily_rate', true) : 0;
}

$price = (float)$price;

// sunday rate
$price_sun = get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sun_hourly_rate', true) : 0;
$enabled_sun = get_post_meta($post_id, 'rbfw_enable_sun_day', true) ? get_post_meta($post_id, 'rbfw_enable_sun_day', true) : 'yes';

// monday rate
$price_mon = get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_mon_hourly_rate', true) : 0;
$enabled_mon = get_post_meta($post_id, 'rbfw_enable_mon_day', true) ? get_post_meta($post_id, 'rbfw_enable_mon_day', true) : 'yes';

// tuesday rate
$price_tue = get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_tue_hourly_rate', true) : 0;
$enabled_tue = get_post_meta($post_id, 'rbfw_enable_tue_day', true) ? get_post_meta($post_id, 'rbfw_enable_tue_day', true) : 'yes';

// wednesday rate
$price_wed = get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_wed_hourly_rate', true) : 0;
$enabled_wed = get_post_meta($post_id, 'rbfw_enable_wed_day', true) ? get_post_meta($post_id, 'rbfw_enable_wed_day', true) : 'yes';

// thursday rate
$price_thu = get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_thu_hourly_rate', true) : 0;
$enabled_thu = get_post_meta($post_id, 'rbfw_enable_thu_day', true) ? get_post_meta($post_id, 'rbfw_enable_thu_day', true) : 'yes';

// friday rate
$price_fri = get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_fri_hourly_rate', true) : 0;
$enabled_fri = get_post_meta($post_id, 'rbfw_enable_fri_day', true) ? get_post_meta($post_id, 'rbfw_enable_fri_day', true) : 'yes';

// saturday rate
$price_sat = get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($post_id, 'rbfw_sat_hourly_rate', true) : 0;
$enabled_sat = get_post_meta($post_id, 'rbfw_enable_sat_day', true) ? get_post_meta($post_id, 'rbfw_enable_sat_day', true) : 'yes';

$current_day = date('D');

if($current_day == 'Sun' && $enabled_sun == 'yes'){
    $price = (float)$price_sun;
}elseif($current_day == 'Mon' && $enabled_mon == 'yes'){
    $price = (float)$price_mon;
}elseif($current_day == 'Tue' && $enabled_tue == 'yes'){
    $price = (float)$price_tue;
}elseif($current_day == 'Wed' && $enabled_wed == 'yes'){
    $price = (float)$price_wed;
}elseif($current_day == 'Thu' && $enabled_thu == 'yes'){
    $price = (float)$price_thu;
}elseif($current_day == 'Fri' && $enabled_fri == 'yes'){
    $price = (float)$price_fri;
}elseif($current_day == 'Sat' && $enabled_sat == 'yes'){
    $price = (float)$price_sat;
}else{
    $price = (float)$price;
}

$current_date = date('Y-m-d');
$rbfw_sp_prices = get_post_meta( $post_id, 'rbfw_seasonal_prices', true );
if(!empty($rbfw_sp_prices)){
    $sp_array = [];
    $i = 0;
    foreach ($rbfw_sp_prices as $value) {
        $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
        $rbfw_sp_end_date 	= $value['rbfw_sp_end_date'];
        $rbfw_sp_price_h 	= $value['rbfw_sp_price_h'];
        $rbfw_sp_price_d 	= $value['rbfw_sp_price_d'];
        $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
        $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
        $sp_array[$i]['sp_daily_rate']  = $rbfw_sp_price_d;
        $i++;
    }

    foreach ($sp_array as $sp_arr) {
        if (in_array($current_date,$sp_arr['sp_dates'])){
            $price = (float)$sp_arr['sp_hourly_rate'];
        }
    }
}


/* Resort Type */
$rbfw_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true );
if(!empty($rbfw_room_data) && $rbfw_rent_type == 'resort'):
    $rbfw_daylong_rate = [];
    $rbfw_daynight_rate = [];
    foreach ($rbfw_room_data as $key => $value) {

        if(!empty($value['rbfw_room_daylong_rate'])){
            $rbfw_daylong_rate[] =  $value['rbfw_room_daylong_rate'];
        }

        if(!empty($value['rbfw_room_daynight_rate'])){
            $rbfw_daynight_rate[] = $value['rbfw_room_daynight_rate'];
        }

    }
    $merged_arr = array_merge($rbfw_daylong_rate,$rbfw_daynight_rate);

    if(!empty($merged_arr)){
        $smallest_price = min($merged_arr);
        $smallest_price = (float)$smallest_price;
    } else {
        $smallest_price = 0;
    }
    $price = $smallest_price;
endif;

/* Single Day/Appointment Type */
$rbfw_bike_car_sd_data = get_post_meta( $post_id, 'rbfw_bike_car_sd_data', true );
if(!empty($rbfw_bike_car_sd_data) && ($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment')):
    $rbfw_price_arr = [];

    foreach ($rbfw_bike_car_sd_data as $key => $value) {

        if(!empty($value['price'])){
            $rbfw_price_arr[] =  $value['price'];
        }

    }

    if(!empty($rbfw_price_arr)){
        $smallest_price = min($rbfw_price_arr);
        $smallest_price = (float)$smallest_price;
    } else {
        $smallest_price = 0;
    }
    $price = $smallest_price;
endif;

$rbfw_feature_category = get_post_meta($post_id,'rbfw_feature_category',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_feature_category', true)) : [];

/*echo '<pre>';
print_r($rbfw_feature_category);
echo '<pre>';*/

?>
<div class="rbfw_rent_list_col">
    <div class="rbfw_rent_list_inner_wrapper">
        <div class="rbfw_rent_list_lists_view">
            <div class="rbfw_rent_list_lists_images">
                <a class="rbfw_rent_list_grid_view_top_img" href="<?php echo esc_url($post_link); ?>">
                    <img src="<?php echo esc_url($post_featured_img); ?>" alt="Catalog Image">
                </a>
            </div>
            <div class="rbfw_rent_list_lists_info">
                <div class="rbfw_rent_list_lists_left">
                    <div class="catalog-list-content-item__body catalog-list-content-item-body ">
                        <div class="catalog-list-content-item-body__top catalog-list-content-item-body-top"><a class="catalog-list-content-item-body-top__title" href="https://renity.tm-colors.info/shuffle/catalog/athletic-trainer-2/">Athletic trainer</a><div class="catalog-list-content-item-body-top__box catalog-list-content-item-body-top-box"><div class="catalog-list-content-item-body-top-box__inner">
                            <div class="catalog-list-content-item-body-top-box__text">Rent Per Day</div>
                            <div class="catalog-list-content-item-body-top-box__price"><span class="prc currency_left" data-symbol="$">230</span></div></div><div class="catalog-list-content-item-body-top-box__inner">
                            <div class="catalog-list-content-item-body-top-box__text">Rent Per Week</div>
                            <div class="catalog-list-content-item-body-top-box__price"><span class="prc currency_left" data-symbol="$">380</span></div></div><div class="catalog-list-content-item-body-top-box__inner">
                            <div class="catalog-list-content-item-body-top-box__text">Rent Per Month</div>
                            <div class="catalog-list-content-item-body-top-box__price"><span class="prc currency_left" data-symbol="$">650</span></div></div></div>
                        </div>
                        <div class="catalog-list-content-item-body__bottom catalog-list-content-item-body-bottom">
                            <p class="catalog-list-content-item-body-bottom__text">Platea pulvinar quam ut purus. Egestas ...</p>
                            <ul class="catalog-list-content-item-body-bottom__list">
                                <li class="catalog-list-content-item-body-bottom__list-item">
                                    <p class="catalog-list-content-item-body-bottom__list-text">
                                        Freq Range: 5 - 3000Hz
                                    </p>
                                </li>
                                <li class="catalog-list-content-item-body-bottom__list-item">
                                    <p class="catalog-list-content-item-body-bottom__list-text">
                                        Sensitivity: 104 dB
                                    </p>
                                </li>
                                <li class="catalog-list-content-item-body-bottom__list-item">
                                    <p class="catalog-list-content-item-body-bottom__list-text">
                                        Output BT Specs: Class 2
                                    </p>
                                </li>
                                <li class="catalog-list-content-item-body-bottom__list-item">
                                    <p class="catalog-list-content-item-body-bottom__list-text">
                                        Max Input Power: 20 mW
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="rbfw_rent_list_lists_right">
                    <div class="catalog-list-content-item__info catalog-list-content-item-info"><div class="catalog-list-content-item-info__box"><p class="catalog-list-content-item-info__box-text">total rental price Inc. Tax</p><div class="catalog-list-content-item-info__box-price"><span class="prc currency_left" data-symbol="$">230</span>/day</div></div><a class="catalog-list-content-item-info__btn btn" href="https://renity.tm-colors.info/shuffle/catalog/athletic-trainer-2/">
                        <span>
                            <svg width="20" height="20" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.75 9H14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M9 3.75L14.25 9L9 14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <span>book now</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>