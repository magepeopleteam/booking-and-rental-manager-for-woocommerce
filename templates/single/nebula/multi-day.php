<?php if ( ! defined( 'ABSPATH' ) ) exit;
global $rbfw;
$enable_faq_content = RBFW_Frontend::get_enable_faq_content();
$gallery_images = RBFW_Frontend::get_slider_images($post_id);
$feature_list = RBFW_Frontend::get_feature_list($post_id);
$get_hourly_price = rbfw_get_bike_car_md_hourly_daily_price($post_id, 'hourly');
$get_daily_price = rbfw_get_bike_car_md_hourly_daily_price($post_id, 'daily');
$rbfw_enable_daywise_price = RBFW_Frontend::get_enable_daywise_price();
$enable_daily_rate = RBFW_Frontend::get_enable_daily_rate();
$enable_hourly_rate = RBFW_Frontend::get_enable_hourly_rate();
?>
<div class="rbfw-nebula-template">
	<header>
		<?php if (($enable_daily_rate == 'yes' || $rbfw_enable_daywise_price == 'yes') && !empty($get_daily_price)) : ?>
			<strong class="daily"><?php echo rbfw_mps_price($get_daily_price); ?></strong> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_day', 'rbfw_basic_translation_settings', __('day','booking-and-rental-manager-for-woocommerce'))); ?>
		<?php endif; ?>
		<?php if (($enable_hourly_rate == 'yes'  || $rbfw_enable_daywise_price == 'yes') && !empty($get_hourly_price)) : ?>
			<strong class="hourly"><?php echo rbfw_mps_price($get_hourly_price); ?></strong> / <?php echo esc_html($rbfw->get_option_trans('rbfw_text_hour', 'rbfw_basic_translation_settings', __('hr','booking-and-rental-manager-for-woocommerce'))); ?>
		<?php endif; ?>
	</header>
    <!-- nebula slider template -->
    <div class="rbfw-nebula-slider">
        <div class="rbfw-swiper">
            <div class="swiper-wrapper">
                <?php 
                    foreach($gallery_images as $key => $value):?>
                    <div class="swiper-slide">
                        <img src="<?php  echo wp_get_attachment_url($value ); ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-navigation">
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
        <div class="rbfw-swiper-thumbnail">
            <div class="swiper-wrapper">
                <?php 
                    foreach($gallery_images as $key => $value):?>
                    <div class="swiper-slide">
                        <img src="<?php  echo wp_get_attachment_url($value ); ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- title -->
    <div class="rbfw-nebula-title">
        <h2 class="title"><?php the_title(); ?></h2>
        <div class="devider"></div>
    </div>
    <!-- Description -->
    <div class="rbfw-nebula-content">
        <div>
            <?php the_content(); ?>
        </div>
    </div>
    <!-- Feature icon -->
    <div class="rbfw-nebula-features">
        <div class="feature-lists">
            <?php 
            
            if ( $feature_list ) :
                foreach ( $feature_list as $value ) :
                    $cat_title = $value['cat_title'];
                    $cat_features = $value['cat_features'] ? $value['cat_features'] : [];
                ?>
                <h2 class="feature-title">
                    <?php echo esc_html($cat_title); ?>
                    <div class="devider"></div>
                </h2>
                <div class="feature-items">
                    <?php
                    if(!empty($cat_features)):
                        $i = 1;
                        foreach ($cat_features as $features):
                            $icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
                            $title = $features['title'];
                            if($title):?>
                                <div class="item">
                                    <i class="<?php echo esc_attr(mep_esc_html($icon)); ?>"></i>
                                    <h2><?php echo mep_esc_html($title); ?></h2>
                                </div>
                            <?php
                            endif;
                            $i++;
                        endforeach;
                    endif;
                    ?>
                </div>
            <?php  endforeach; endif; ?>
        </div>
    </div>
    <!-- Start booking -->
    <div class="rbfw-nebula-booking">
        <h2 class="title"><?php _e('Book online','booking-and-rental-manager-for-woocommerce'); ?></h2>
        <div class="devider"></div>
        <div class="booking-area">
            <?php include(  RBFW_TEMPLATE_PATH . 'forms/muffin/bike-registration.php' ); ?>
        </div>
    </div>
        <!-- FAQ Product -->
    <div class="rbfw-nebula-faq">
        <?php   if($enable_faq_content == 'yes') { ?>
            <div class="rbfw_muff_row_faq">
                <h2 class="title"><?php echo esc_html($rbfw->get_option_trans('rbfw_text_faq', 'rbfw_basic_translation_settings', __('Freequently Asked Questions','booking-and-rental-manager-for-woocommerce'))); ?></h2>
                <div class="devider"></div>
                <?php do_action( 'rbfw_the_faq_style_two', $post_id ); ?>
            </div>
        <?php } ?>
    </div>

    <!-- Related Product -->
    <div class="rbfw-nebula-related">
        <div class="rbfw-related-products-wrapper">
            <?php do_action( 'rbfw_related_products', $post_id ); ?>
        </div>
    </div>

</div>

