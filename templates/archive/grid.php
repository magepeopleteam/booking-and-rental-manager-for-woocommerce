<?php
/*********************************
 * Rent List Shortcode Grid Style
 *********************************/
global $rbfw;
$post_id            = get_the_id();
$post_title         = get_the_title();
$post_featured_img  = !empty(get_the_post_thumbnail_url( $post_id, 'full' )) ? get_the_post_thumbnail_url( $post_id, 'full' ) : RBFW_PLUGIN_URL. '/assets/images/no_image.png';
$post_link          = get_the_permalink();
$book_now_label     = $rbfw->get_option_trans('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce'));

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

$enabled_sun = get_post_meta($post_id, 'rbfw_enable_sun_day', true) ? get_post_meta($post_id, 'rbfw_enable_sun_day', true) : 'yes';
$enabled_mon = get_post_meta($post_id, 'rbfw_enable_mon_day', true) ? get_post_meta($post_id, 'rbfw_enable_mon_day', true) : 'yes';
$enabled_tue = get_post_meta($post_id, 'rbfw_enable_tue_day', true) ? get_post_meta($post_id, 'rbfw_enable_tue_day', true) : 'yes';
$enabled_wed = get_post_meta($post_id, 'rbfw_enable_wed_day', true) ? get_post_meta($post_id, 'rbfw_enable_wed_day', true) : 'yes';
$enabled_thu = get_post_meta($post_id, 'rbfw_enable_thu_day', true) ? get_post_meta($post_id, 'rbfw_enable_thu_day', true) : 'yes';
$enabled_fri = get_post_meta($post_id, 'rbfw_enable_fri_day', true) ? get_post_meta($post_id, 'rbfw_enable_fri_day', true) : 'yes';
$enabled_sat = get_post_meta($post_id, 'rbfw_enable_sat_day', true) ? get_post_meta($post_id, 'rbfw_enable_sat_day', true) : 'yes';

$current_day = gmdate('D');

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

$current_date = gmdate('Y-m-d');
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

            if($rbfw_enable_hourly_rate == 'yes'){

                $price = (float)$sp_arr['sp_hourly_rate'];

            } else {

                $price = (float)$sp_arr['sp_daily_rate'];
            }
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
?>

<div class="rbfw_rent_list_col rbfw_grid_list_col_<?php echo esc_attr($d); ?>">
    <div class="rbfw_rent_list_inner_wrapper">

        <div class="rbfw_rent_list_featured_img_wrap">
            <a href="<?php echo esc_url($post_link); ?>">
                <div class="rbfw_rent_list_featured_img">
                    <img src="<?php echo esc_url($post_featured_img); ?>" alt="<?php echo esc_attr($post_title); ?>">
                </div>
            </a>
        </div>

        <div class="rbfw_rent_list_content">

            <?php if( !isset($rbfw_hide_price) || $rbfw_hide_price !== 'yes' ): ?>
            <div class="rbfw_rent_list_price_wrap">
                <?php if($rbfw_rent_type != 'resort' && $rbfw_rent_type != 'bike_car_sd' && $rbfw_rent_type != 'appointment'): ?>
                    <div class="rbfw_rent_list_price_badge">
                        <span class="rbfw_rent_list_price_badge_label"><?php echo esc_html($the_price_label); ?></span>
                        <span class="rbfw_rent_list_price_badge_price"><?php echo wp_kses(wc_price($price),rbfw_allowed_html()); ?></span>
                    </div>
                <?php endif; ?>

                <?php if($rbfw_rent_type == 'resort' && !empty($rbfw_room_data)): ?>
                    <div class="rbfw_rent_list_price_badge">
                        <span class="rbfw_rent_list_price_badge_label"><?php echo esc_html($prices_start_at); ?></span>
                        <span class="rbfw_rent_list_price_badge_price"><?php echo wp_kses(wc_price($price),rbfw_allowed_html()); ?></span>
                    </div>
                <?php endif; ?>

                <?php if(($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment') && !empty($rbfw_bike_car_sd_data)): ?>
                    <div class="rbfw_rent_list_price_badge">
                        <span class="rbfw_rent_list_price_badge_label"><?php echo esc_html($prices_start_at); ?></span>
                        <span class="rbfw_rent_list_price_badge_price"> <?php echo wp_kses(wc_price($price),rbfw_allowed_html()); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="rbfw_rent_list_title_wrap">
                <a href="<?php echo esc_url($post_link); ?>"><?php echo esc_html($post_title); ?></a>
            </div>

            <div class="rbfw_muff_highlighted_features rbfw_rent_list_highlighted_features">
                <?php if ( $rbfw_feature_category ) :
                    $n = 1;
                foreach ( $rbfw_feature_category as $value ) :
                    $cat_title = $value['cat_title'];
                $cat_features = $value['cat_features'] ? $value['cat_features'] : [];

                if($n == 1){
                    ?>
                    <ul>
                        <?php
                        if(!empty($cat_features)){
                            $i = 1;
                            foreach ($cat_features as $features) {
                                if($i<=5){
                                    $icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
                                    $title = $features['title'];
                                    $rand_number = wp_rand();
                                    if($title) {
                                        ?>
                                        <li class="title <?php echo esc_attr($rand_number); ?>">
                                            <i class="<?php echo esc_attr($icon); ?>"></i>
                                            <?php echo esc_html($title); ?>
                                        </li>
                                        <?php
                                    }
                                }
                                $i++;
                            }
                        }
                        ?>
                    </ul>
                    <?php
                }
                $n++;
                endforeach;
                endif;
                ?>
            </div>
        </div>

        <div class="rbfw_rent_list_footer">
            <div class="rbfw_rent_list_button_wrap">
                <a href="<?php echo esc_url($post_link); ?>" class="rbfw_rent_list_btn"><?php echo esc_html($book_now_label); ?></a>
            </div>
        </div>
    </div>
</div>