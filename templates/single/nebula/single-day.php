<?php
// Template Name: Muffin Bike-car-sd Theme
if ( ! defined( 'ABSPATH' ) ) exit; ?>

    <div class="rbfw_nebula_template">
        <header> 
            <h2>
                <span>$100</span>/<?php esc_html_e('day',''); ?> | <span>$10</span>/<?php esc_html_e('hr',''); ?>
            </h2>
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
        <h2 class="title"><?php the_title(); ?></h2>
        <div class="devider"></div>
        <!-- Description -->
        <div class="content"><?php the_content(); ?></div>
        <!-- Feature icon -->
        <div class="feature-lists">
            <?php if ( $rbfw_feature_category ) :
                foreach ( $rbfw_feature_category as $value ) :
                    $cat_title = $value['cat_title'];
                    $cat_features = $value['cat_features'] ? $value['cat_features'] : [];
                ?>
                <h2 class="feature-title"><?php echo esc_html($cat_title); ?></h2>
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
        <!-- Start booking -->
        <div class="start-booking">
            <h2 class="title"><?php _e('Book online','booking-and-rental-manager-for-woocommerce'); ?></h2>
            <div class="devider"></div>
            <?php include(  RBFW_TEMPLATE_PATH . 'forms/nebula/bike-car-sd-registration.php' ); ?>
        </div>
        <!-- Related items -->
    </div>

