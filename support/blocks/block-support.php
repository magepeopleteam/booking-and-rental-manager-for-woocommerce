<?php
/**
 * Block Support for Booking and Rental Manager
 *
 * This file initializes block support for the Booking and Rental Manager plugin.
 */

if (!defined('ABSPATH')) {
    die; // Cannot access pages directly.
}

// Include the blocks registration file
require_once RBFW_PLUGIN_DIR . '/support/blocks/blocks.php';

/**
 * Add a custom block category for Booking and Rental Manager blocks
 */
function rbfw_block_category($categories, $post) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'rbfw-blocks',
                'title' => __('Booking & Rental Manager', 'booking-and-rental-manager-for-woocommerce'),
                'icon'  => 'calendar-alt',
            ),
        )
    );
}
add_filter('block_categories_all', 'rbfw_block_category', 10, 2);

/**
 * Enqueue block assets for the editor
 */
function rbfw_enqueue_block_editor_assets() {
    // Enqueue any additional assets needed for the block editor
    wp_enqueue_style(
        'rbfw-admin-style',
        RBFW_PLUGIN_URL . '/admin/css/admin_style.css',
        array(),
        filemtime(RBFW_PLUGIN_DIR . '/admin/css/admin_style.css')
    );
}
add_action('enqueue_block_editor_assets', 'rbfw_enqueue_block_editor_assets');