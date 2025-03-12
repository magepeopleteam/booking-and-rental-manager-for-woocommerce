<?php
/**
 * Block Registration and Functionality for Booking and Rental Manager
 *
 * This file handles the registration and functionality of all blocks for the Booking and Rental Manager plugin.
 */

if (!defined('ABSPATH')) {
    die; // Cannot access pages directly.
}

/**
 * Register all blocks for the Booking and Rental Manager plugin
 */
function rbfw_register_blocks() {
    // Only register blocks if Gutenberg is active
    if (!function_exists('register_block_type')) {
        return;
    }

    // Register block scripts and styles
    wp_register_script(
        'rbfw-blocks-editor-script',
        RBFW_PLUGIN_URL . '/support/blocks/js/blocks.build.js',
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(RBFW_PLUGIN_DIR . '/support/blocks/js/blocks.build.js'),
        true
    );

    wp_register_style(
        'rbfw-blocks-editor-style',
        RBFW_PLUGIN_URL . '/support/blocks/css/blocks.editor.css',
        array('wp-edit-blocks'),
        filemtime(RBFW_PLUGIN_DIR . '/support/blocks/css/blocks.editor.css')
    );

    wp_register_style(
        'rbfw-blocks-style',
        RBFW_PLUGIN_URL . '/support/blocks/css/blocks.style.css',
        array(),
        filemtime(RBFW_PLUGIN_DIR . '/support/blocks/css/blocks.style.css')
    );

    // Register Rent List Block
    register_block_type('rbfw/rent-list', array(
        'editor_script' => 'rbfw-blocks-editor-script',
        'editor_style' => 'rbfw-blocks-editor-style',
        'style' => 'rbfw-blocks-style',
        'attributes' => array(
            'style' => array(
                'type' => 'string',
                'default' => 'grid',
            ),
            'show' => array(
                'type' => 'number',
                'default' => -1,
            ),
            'order' => array(
                'type' => 'string',
                'default' => 'DESC',
            ),
            'orderby' => array(
                'type' => 'string',
                'default' => '',
            ),
            'meta_key' => array(
                'type' => 'string',
                'default' => '',
            ),
            'type' => array(
                'type' => 'string',
                'default' => '',
            ),
            'location' => array(
                'type' => 'string',
                'default' => '',
            ),
            'category' => array(
                'type' => 'string',
                'default' => '',
            ),
            'cat_ids' => array(
                'type' => 'string',
                'default' => '',
            ),
            'columns' => array(
                'type' => 'number',
                'default' => 3,
            ),
            'left-filter' => array(
                'type' => 'string',
                'default' => '',
            ),
            'left-title-filter' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'left-price-filter' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'left-location-filter' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'left-category-filter' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'left-type-filter' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'left-feature-filter' => array(
                'type' => 'string',
                'default' => 'on',
            ),
        ),
        'render_callback' => 'rbfw_rent_list_block_render',
    ));

    // Register Rent Search Block
    register_block_type('rbfw/rent-search', array(
        'editor_script' => 'rbfw-blocks-editor-script',
        'editor_style' => 'rbfw-blocks-editor-style',
        'style' => 'rbfw-blocks-style',
        'render_callback' => 'rbfw_rent_search_block_render',
    ));

    // Register Rent Filter Block
    register_block_type('rbfw/rent-filter', array(
        'editor_script' => 'rbfw-blocks-editor-script',
        'editor_style' => 'rbfw-blocks-editor-style',
        'style' => 'rbfw-blocks-style',
        'attributes' => array(
            'title_filter_shown' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'price_filter_shown' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'location_filter_shown' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'category_filter_shown' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'type_filter_shown' => array(
                'type' => 'string',
                'default' => 'on',
            ),
            'feature_filter_shown' => array(
                'type' => 'string',
                'default' => 'on',
            ),
        ),
        'render_callback' => 'rbfw_rent_filter_block_render',
    ));

    // Register Add to Cart Block
    register_block_type('rbfw/add-to-cart', array(
        'editor_script' => 'rbfw-blocks-editor-script',
        'editor_style' => 'rbfw-blocks-editor-style',
        'style' => 'rbfw-blocks-style',
        'attributes' => array(
            'id' => array(
                'type' => 'number',
                'default' => 0,
            ),
        ),
        'render_callback' => 'rbfw_add_to_cart_block_render',
    ));

    // Register Search Results Block
    register_block_type('rbfw/search-result', array(
        'editor_script' => 'rbfw-blocks-editor-script',
        'editor_style' => 'rbfw-blocks-editor-style',
        'style' => 'rbfw-blocks-style',
        'render_callback' => 'rbfw_search_result_block_render',
    ));
}
add_action('init', 'rbfw_register_blocks');

/**
 * Render callback for the Rent List Block
 */
function rbfw_rent_list_block_render($attributes) {
    // Convert block attributes to shortcode attributes
    $shortcode_atts = array(
        'style' => $attributes['style'],
        'show' => $attributes['show'],
        'order' => $attributes['order'],
        'orderby' => $attributes['orderby'],
        'meta_key' => $attributes['meta_key'],
        'type' => $attributes['type'],
        'location' => $attributes['location'],
        'category' => $attributes['category'],
        'cat_ids' => $attributes['cat_ids'],
        'columns' => $attributes['columns'],
        'left-filter' => $attributes['left-filter'],
        'left-title-filter' => $attributes['left-title-filter'],
        'left-price-filter' => $attributes['left-price-filter'],
        'left-location-filter' => $attributes['left-location-filter'],
        'left-category-filter' => $attributes['left-category-filter'],
        'left-type-filter' => $attributes['left-type-filter'],
        'left-feature-filter' => $attributes['left-feature-filter'],
    );

    // Build shortcode string
    $shortcode = '[rent-list';
    foreach ($shortcode_atts as $key => $value) {
        if (!empty($value)) {
            $shortcode .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
    }
    $shortcode .= ']';

    // Return shortcode output
    return do_shortcode($shortcode);
}

/**
 * Render callback for the Rent Search Block
 */
function rbfw_rent_search_block_render($attributes) {
    return do_shortcode('[rbfw_search]');
}

/**
 * Render callback for the Rent Filter Block
 */
function rbfw_rent_filter_block_render($attributes) {
    // Convert block attributes to shortcode attributes
    $shortcode_atts = array(
        'title_filter_shown' => $attributes['title_filter_shown'],
        'price_filter_shown' => $attributes['price_filter_shown'],
        'location_filter_shown' => $attributes['location_filter_shown'],
        'category_filter_shown' => $attributes['category_filter_shown'],
        'type_filter_shown' => $attributes['type_filter_shown'],
        'feature_filter_shown' => $attributes['feature_filter_shown'],
    );

    // Build shortcode string
    $shortcode = '[rbfw_left_filter';
    foreach ($shortcode_atts as $key => $value) {
        if (!empty($value)) {
            $shortcode .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
    }
    $shortcode .= ']';

    // Return shortcode output
    return do_shortcode($shortcode);
}

/**
 * Render callback for the Add to Cart Block
 */
function rbfw_add_to_cart_block_render($attributes) {
    $id = !empty($attributes['id']) ? $attributes['id'] : 0;
    return do_shortcode('[rent-add-to-cart id="' . esc_attr($id) . '"]');
}

/**
 * Render callback for the Search Results Block
 */
function rbfw_search_result_block_render($attributes) {
    return do_shortcode('[search-result]');
}