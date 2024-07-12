<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
    <div class="rbfw_nebula_template">
        <!-- nebula slider template -->
        <div class="rbfw-nebula-slider">
            <div class="rbfw-swiper">
                <div class="swiper-wrapper">
                    <?php 
                        $gallery_images = RBFW_Frontend::get_slider_images($post_id);
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
                $rbfw_feature_category = RBFW_Frontend::get_feature_categories($post_id);
                if ( $rbfw_feature_category ) :
                    foreach ( $rbfw_feature_category as $value ) :
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
            <div class="start-booking">
                <h2 class="title"><?php _e('Book online','booking-and-rental-manager-for-woocommerce'); ?></h2>
                <div class="devider"></div>
                <?php do_action('rbfw_booking_form'); ?>
            </div>
        </div>
        <!-- Related Product -->
        <div class="rbfw-nebula-related">
            <?php do_action('rbfw_template_view_related'); ?>
        </div>
        <!-- Related items -->
    </div>

