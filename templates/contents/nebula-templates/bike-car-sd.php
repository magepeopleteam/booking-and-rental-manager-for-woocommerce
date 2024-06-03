<?php
// Template Name: Muffin Bike-car-sd Theme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
?>
<?php

global $rbfw;
$post_id = get_the_id();
$rbfw_id = $post_id;
$post_title = get_the_title();
$post_content  = get_the_content();
$rbfw_feature_category = get_post_meta($post_id,'rbfw_feature_category',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_feature_category', true)) : [];
$rbfw_enable_faq_content  = get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) ? get_post_meta( $post_id, 'rbfw_enable_faq_content', true ) : 'no';
$slide_style = $rbfw->get_option('super_slider_style', 'super_slider_settings','');
$post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating($post_id,'muffin','style1') : '';
$currency_symbol = rbfw_mps_currency_symbol();
$get_hourly_price = rbfw_get_bike_car_md_hourly_daily_price($post_id, 'hourly');
$get_daily_price = rbfw_get_bike_car_md_hourly_daily_price($post_id, 'daily');
$enable_daily_rate = get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daily_rate', true) : 'yes';
$enable_hourly_rate = get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_enable_hourly_rate', true) : 'no';
$rbfw_enable_daywise_price = get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) ? get_post_meta($rbfw_id, 'rbfw_enable_daywise_price', true) : 'no';
$rbfw_related_post_arr = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize(get_post_meta( $post_id, 'rbfw_releted_rbfw', true )) : [];
$post_review_rating_style2 = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating($post_id,'muffin', 'style2') : '';
$post_review_average = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id) : '';

$post_review_average_hygenic = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'hygenic') : '';
$post_review_average_quality = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'quality') : '';
$post_review_average_cost_value = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'cost_value') : '';
$post_review_average_staff = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'staff') : '';
$post_review_average_facilities = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'facilities') : '';
$post_review_average_comfort = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($post_id, 'comfort') : '';

$post_review_value_round_hygenic = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_hygenic) : '';
$post_review_value_round_quality = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_quality) : '';
$post_review_value_round_cost_value = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_cost_value) : '';
$post_review_value_round_staff = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_staff) : '';
$post_review_value_round_facilities = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_facilities) : '';
$post_review_value_round_comfort = function_exists('rbfw_review_value_round') ? rbfw_review_value_round($post_review_average_comfort) : '';

$post_hygenic_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_hygenic) : '';
$post_quality_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_quality) : '';
$post_cost_value_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_cost_value) : '';
$post_staff_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_staff) : '';
$post_facilities_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_facilities) : '';
$post_comfort_progress_width = function_exists('rbfw_review_get_progress_bar_width') ? rbfw_review_get_progress_bar_width($post_review_average_comfort) : '';

$gallery_images_additional = rbfw_get_additional_gallary_images($post_id, 6);
$prices_start_at = $rbfw->get_option('rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __('Prices start at','booking-and-rental-manager-for-woocommerce'));

/* Single Day/Appointment Type */
$rbfw_rent_type = get_post_meta( $post_id, 'rbfw_item_type', true );
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
$review_system = rbfw_get_option('rbfw_review_system', 'rbfw_basic_review_settings', 'on');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <div class="rbfw_nebula_template">
        <header> 
            <h2>
                <span>$100</span>/<?php esc_html_e('day',''); ?> | <span>$10</span>/<?php esc_html_e('hr',''); ?>
            </h2>
        </header>
        <?php do_action( 'add_super_slider', $post_id ,'rbfw_gallery_images'); ?>
        <div class="nebula-slider">
        <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="swiper mySwiper2">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="https://swiperjs.com/demos/images/nature-1.jpg" />
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
        <div thumbsSlider="" class="swiper mySwiper">
            <div class="swiper-wrapper">
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-1.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-2.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-3.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-4.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-5.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-6.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-7.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-8.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-9.jpg" />
            </div>
            <div class="swiper-slide">
                <img src="https://swiperjs.com/demos/images/nature-10.jpg" />
            </div>
            </div>
        </div>
        </div>

        <!-- Swiper JS -->
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

        <!-- Initialize Swiper -->
        <script>
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
        });
        var swiper2 = new Swiper(".mySwiper2", {
            loop: true,
            spaceBetween: 10,
            navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
            },
            thumbs: {
            swiper: swiper,
            },
        });
        </script>
    </div>

