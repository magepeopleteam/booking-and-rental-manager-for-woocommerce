<?php
// Template Name: Muffin Bike-car-sd Theme

if ( ! defined( 'ABSPATH' ) ) exit; 

?>

    <div class="rbfw_nebula_template">
        <header> 
            <?php do_action('rbfw_template_pricing','pricing'); ?>
        </header>  
        <!-- nebula slider template -->
        <div class="rbfw-nebula-slider">
            <?php do_action('rbfw_template_slider','slider'); ?>
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

