<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

if ( wp_is_block_theme() ) {  ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <?php
        $block_content = do_blocks( '
		<!-- wp:group {"layout":{"type":"constrained"}} -->
		<div class="wp-block-group">
		<!-- wp:post-content /-->
		</div>
		<!-- /wp:group -->'
        );
        wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div class="wp-site-blocks">
        <header class="wp-block-template-part site-header">
            <?php block_header_area(); ?>
        </header>

    </div>
    <?php
} else {
    get_header();
    the_post();

    if ( function_exists( 'blocksy_output_hero_section' ) ) {
        if (apply_filters('blocksy:single:has-default-hero', true)) {
            echo blocksy_output_hero_section([
                'type' => 'type-2'
            ]);
        }
    }


}


$post_id = get_the_id();
$frontend = 'yes';
$submit_name = 'add-to-cart';




/*$rbfw_inventory = get_post_meta($post_id,'rbfw_inventory',true);
echo '<pre>';
print_r($rbfw_inventory);
echo '<pre>';
exit;*/


do_action('rbfw_single_page_before_wrapper');
if ( post_password_required() ) {
    echo wp_kses(get_the_password_form(),rbfw_allowed_html()); // WPCS: XSS ok.
} else {
    do_action( 'woocommerce_before_single_product' );
    //include_once( RBFW_Function::get_template($post_id) );
    $today_booking_enable = rbfw_get_option('today_booking_enable','rbfw_basic_gen_settings');
    ?>
    <input type="hidden" class="rbfw_today_booking_enable" value="<?php echo esc_attr($today_booking_enable); ?>">
    <?php
    RBFW_Frontend::load_template($post_id);

}
do_action('rbfw_single_page_after_wrapper');
do_action('rbfw_single_page_footer',$post_id);

if ( wp_is_block_theme() ) {
// Code for block themes goes here.
    ?>
    <footer class="wp-block-template-part">
        <?php block_footer_area(); ?>
    </footer>
    <?php wp_footer(); ?>
    </body>
    <?php
} else {
    get_footer();
}
