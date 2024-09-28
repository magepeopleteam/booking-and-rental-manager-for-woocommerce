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

$rbfw_offday_range  = get_post_meta( get_the_id(), 'rbfw_offday_range', true ) ? get_post_meta( get_the_id(), 'rbfw_offday_range', true ) : 'no';

$continue = false;
if( $rbfw_offday_range !== 'no' && !empty( $pickup_date ) ){
    foreach ( $rbfw_offday_range as $date_rang ){
        $start_date = $date_rang['from_date'];
        $end_date = $date_rang['to_date'];
        $check_date = $pickup_date;

        $startDateTime = DateTime::createFromFormat('d-m-Y', $start_date);
        $endDateTime = DateTime::createFromFormat('d-m-Y', $end_date);
        $checkDateTime = DateTime::createFromFormat('d-m-Y', $check_date);

        if ($checkDateTime >= $startDateTime && $checkDateTime <= $endDateTime) {
            error_log( print_r( ['$continue' => $continue], true ) );
            $continue = true ;
        }
    }
}



if( !$continue ){
    $hourly_rate_label = $rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly rate','booking-and-rental-manager-for-woocommerce'));
    $daily_rate_label = $rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings', __('Daily rate','booking-and-rental-manager-for-woocommerce'));
    $rbfw_enable_hourly_rate = get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) : 'no';
    $rbfw_enable_daily_rate  = get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_daily_rate', true ) : 'no';
    $post_content       = $the_content;

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

    if( $rbfw_rent_type != 'resort' && $rbfw_rent_type != 'bike_car_sd' && $rbfw_rent_type != 'appointment' ){
        $price_level = $the_price_label;
    }elseif( $rbfw_rent_type == 'resort' && !empty( $rbfw_room_data ) ){
        $price_level = $prices_start_at;
    }else{
        $price_level = $prices_start_at;
    }

    ?>
    <div class="rbfw_rent_list_col rbfw_grid_list_col_<?php echo $d; ?>">
        <div class="rbfw_rent_list_inner_wrapper">
            <div class="<?php echo esc_attr( $image_holder )?>">
                <a class="rbfw_rent_list_grid_view_top_img" href="<?php echo esc_url($post_link); ?>">
                    <img src="<?php echo esc_url($post_featured_img); ?>" alt="Catalog Image">
                </a>
            </div>
            <div class="<?php echo esc_attr( $rent_item_info )?>">
                <div class="rbfw_rent_list_content">
                    <div class="rbfw_rent_list_grid_title_wrapper">
                        <h2 class="rbfw_rent_list_grid_title">
                            <a href="<?php echo esc_url($post_link); ?>"><?php echo esc_html($post_title); ?></a>
                        </h2>
                        <div class="rbfw_rent_list_grid_row rbfw_pricing-box">
                            <p class="rbfw_rent_list_row_price">
                                <span class="prc currency_left"><?php echo rbfw_mps_price($price); ?></span>
                            </p>
                            <span class="rbfw_rent_list_row_price_level">/ <?php echo esc_html($price_level); ?></span>
                        </div>
                    </div>
                    <div class="rbfw_rent_item_description" id="rbfw_rent_item_description">
                        <p class="rbfw_rent_item_description_text" style="display: <?php echo esc_attr( $is_display )?>">
                            <?php echo esc_html( $post_content )?>
                        </p>
                    </div>
                </div>

                <?php if ( $rbfw_feature_category ) :
                    $n = 1;
                    foreach ( $rbfw_feature_category as $value ) :
                        $cat_title = $value['cat_title'];
                        $cat_features = $value['cat_features'] ? $value['cat_features'] : [];

                        if($n == 1){
                            ?>
                            <ul class="<?php echo esc_attr( $rent_item_list_info )?>">
                                <?php
                                if(!empty($cat_features)){
                                    $i = 1;
                                    foreach ($cat_features as $features) {
                                        if($i<=5){
                                            $icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
                                            $title = $features['title'];
                                            $rand_number = rand();
                                            if($title) {
                                                ?>
                                                <li class=" bfw_rent_list_items title <?php echo $rand_number ?>"> <span class="bfw_rent_list_items_icon"><i class="<?php echo mep_esc_html($icon) ?>"></i></span> <?php echo $title ?></li>
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

                <div class="rbfw_rent_list_btn_holder">
                    <a class="rbfw_rent_list_link rbfw_rent_list_btn btn" href="<?php echo esc_url($post_link); ?>">
                        <span> <?php echo esc_html($book_now_label); ?> </span>
                        <span>
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.75 9H14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M9 3.75L14.25 9L9 14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>

                    </a>
                </div>

            </div>
        </div>
    </div>

<?php }?>