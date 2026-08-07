<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

if ( wp_is_block_theme() ) {
    /*
     * Block themes build their layout CSS *while the blocks render*: every
     * `wp:group`/`wp:navigation` with a layout gets a generated
     * `.wp-container-core-…` rule pushed into the style engine's `block-supports`
     * store. Core flushes that store in `wp_enqueue_stored_styles()`, which for a
     * block theme prints during `wp_enqueue_scripts` — i.e. inside wp_head() — and
     * deliberately bails out on `wp_footer` (see wp-includes/script-loader.php).
     *
     * Rendering the header/footer template parts *after* wp_head() therefore emitted
     * markup whose layout rules were never printed at all: flex/justification was
     * lost, so the navigation collapsed to the left, header items wrapped, and the
     * footer lost its alignment. Core's own template-canvas.php avoids this by
     * building the template HTML before <head> for exactly this reason.
     *
     * So: render both template parts up front, buffer the markup, and echo it in
     * place further down. Nothing about the output changes — only when it is built.
     */
    ob_start();
    block_header_area();
    $rbfw_block_header_html = ob_get_clean();

    ob_start();
    block_footer_area();
    $rbfw_block_footer_html = ob_get_clean();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div class="wp-site-blocks">
        <header class="wp-block-template-part site-header">
            <?php echo $rbfw_block_header_html; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- rendered block markup. ?>
        </header>
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
$submit_name = 'add-to-cart';

set_transient("pricing_applied", "No", 3600);


/* $rbfw_inventory = get_post_meta($post_id,'rbfw_inventory',true);
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
    RBFW_Frontend::load_template($post_id);
}
do_action('rbfw_single_page_after_wrapper');
do_action('rbfw_single_page_footer',$post_id);

if ( wp_is_block_theme() ) {
// Code for block themes goes here.
    ?>
    <footer class="wp-block-template-part">
        <?php echo $rbfw_block_footer_html; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- rendered block markup, built before wp_head(). ?>
    </footer>
    </div><!-- /.wp-site-blocks -->
    <?php wp_footer(); ?>
    </body>
    </html>
    <?php
} else {
    get_footer();
}
