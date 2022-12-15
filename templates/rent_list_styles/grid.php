<?php
/*********************************
 * Rent List Shortcode Grid Style
 *********************************/
global $rbfw;
$post_id            = get_the_id();
$post_title         = get_the_title();
$post_featured_img  = !empty(get_the_post_thumbnail_url( $post_id, 'full' )) ? get_the_post_thumbnail_url( $post_id, 'full' ) : RBFW_PLUGIN_URL. '/assets/images/no_image.png';
$post_link          = get_the_permalink();
$book_now_label     = $rbfw->get_option('rbfw_text_book_now', 'rbfw_basic_translation_settings', __('Book Now','booking-and-rental-manager-for-woocommerce'));
$post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating() : '';
?>
<div class="rbfw_rent_list_col">
    <div class="rbfw_rent_list_inner_wrapper">
        
        <div class="rbfw_rent_list_featured_img_wrap">
            <a href="<?php echo esc_url($post_link); ?>">
                <div class="rbfw_rent_list_featured_img"><img src="<?php echo esc_url($post_featured_img); ?>" alt="<?php esc_attr_e('Featured Image','booking-and-rental-manager-for-woocommerce'); ?>"></div>
            </a>
        </div>
        
        <div class="rbfw_rent_list_content">
            <div class="rbfw_rent_list_title_wrap">
                <a href="<?php echo esc_url($post_link); ?>"><?php echo esc_html($post_title); ?></a>
            </div>
            <?php if(!empty($post_review_rating)): ?>
            <div class="rbfw_rent_list_average_rating">
                <?php echo $post_review_rating; ?>
            </div>
            <?php endif; ?>
            <div class="rbfw_rent_list_button_wrap">
                <a href="<?php echo esc_url($post_link); ?>" class="rbfw_rent_list_btn"><?php echo esc_html($book_now_label); ?></a>
            </div>
        </div>
    </div>
</div>