<?php

$counts = wp_count_posts('rbfw_item');
// Prepare the count data
$post_counts = array(
    'publish' => isset($counts->publish) ? $counts->publish : 0,
    'draft'   => isset($counts->draft) ? $counts->draft : 0,
    'trash'   => isset($counts->trash) ? $counts->trash : 0,
);



$total_event = $post_counts['publish'] + $post_counts['draft']  + $post_counts['trash'] ;
//$statuses = ['publish', 'draft', 'trash'];
$statuses = ['publish', 'draft'];
$posts = get_posts(array(
    'post_type'   => 'rbfw_item',
    'post_status' => $statuses,
    'numberposts' => -1
));
$post_type = 'rbfw_item';

$add_new_link = admin_url('post-new.php?post_type=' . $post_type);
$trash_url = admin_url('edit.php?post_status=trash&post_type=rbfw_item');

function render_mep_events_by_status( $posts ) {
    ob_start();
    if (!empty($posts)) {
        foreach ($posts as $post) {
            $rental_id    = $post->ID;
            $title = get_the_title( $rental_id );
            $rbfw_rent_type = get_post_meta( $rental_id, 'rbfw_item_type', true );

            $single_template = get_post_meta( $rental_id, 'rbfw_single_template', true );
            $rental_shortcode = "[rent-add-to-cart id=$rental_id]";
            $featured_image_url = get_the_post_thumbnail_url( $rental_id, 'medium' );

            $item_type = RBFW_Function::rbfw_rent_types();

            $price_type = $item_type[$rbfw_rent_type];


            $rbfw_categories = get_post_meta( $rental_id, 'rbfw_categories', true ) ? maybe_unserialize( get_post_meta( $rental_id, 'rbfw_categories', true ) ) : [];
            $rbfw_categories_items = implode(',', $rbfw_categories);
            $status = get_post_status( $rental_id );
            $edit_link   = get_edit_post_link( $rental_id );
            $delete_link = get_delete_post_link( $rental_id ); // Moves to Trash
            $view_link   = get_permalink( $rental_id );

            ?>
                <tr class="rbfw_rental_list"
                    data-rental-status="<?php echo esc_attr( $status );?>"
                    data-title_search="<?php echo esc_attr( $title );?>"
                >
                    <td><img class="rbfw_rental_feature_image" src="<?php echo esc_attr( $featured_image_url);?>" ></td>
                    <td>
                        <div class="rbfw_rental_lists_item-title-wrapper">
                            <a href="#" class="rbfw_rental_lists_item-title"><?php echo esc_attr( $title )?></a>

                            <?php if( $status === 'publish'){?>
                            <span class="rbfw_rental_lists_status-badge rbfw_rental_lists_live" title="Live">
                                <span class="rbfw_rental_lists_status-icon">🟢</span>
                                <span>Live</span>
                            </span>
                            <?php }else{?>

                            <span class="status-badge draft" title="Draft">
                                <span class="status-icon">🟡</span>
                                <span>Draft</span>
                            </span>
                            <?php }?>
                        </div>
                    </td>
                    <td><?php echo esc_attr( $single_template );?></td>
                    <td><?php echo esc_attr( $rental_shortcode );?></td>
                    <td class="rbfw_rental_lists_price-type"><?php echo esc_attr( $price_type )?></td>

                    <td>
                        <div class="rbfw_rental_lists_actions-cell">
                            <a href="<?php echo esc_url( $view_link )?>"><button class="rbfw_rental_lists_action-btn rbfw_rental_lists_view" title="View Item">👁️</button></a>
                            <a href="<?php echo esc_url( $edit_link )?>"><button class="rbfw_rental_lists_action-btn rbfw_rental_lists_edit" title="Edit Item">✏️</button></a>

                            <a title="<?php echo esc_attr__('Duplicate Item ', 'booking-and-rental-manager-for-woocommerce') . ' : ' . get_the_title($rental_id); ?>"  href="<?php echo wp_nonce_url(
                                admin_url('admin.php?action=rbfw_duplicate_post&post_id=' . $rental_id),
                                'rbfw_duplicate_post_' . $rental_id
                            ); ?>"><button class="rbfw_rental_lists_action-btn rbfw_rental_lists_duplicate" title="Duplicate Item">📋</button></a>
                            <a href="<?php echo esc_url( $delete_link )?>"><button class="rbfw_rental_lists_action-btn rbfw_rental_lists_delete" title="Delete Item">🗑️</button></a>
                        </div>
                    </td>
                </tr>
        <?php   }
    } else {
        echo '<p>No posts found.</p>';
    }

    return ob_get_clean(); // return the entire buffered content
}


function fount_post_number_by_category(){

    global $wpdb;
    $results = $wpdb->get_results("
    SELECT post_id, meta_value 
    FROM {$wpdb->prefix}postmeta 
    WHERE meta_key = 'rbfw_categories'
");

    $category_counts = [];

    foreach ( $results as $row ) {
        $categories = maybe_unserialize( $row->meta_value );
        if ( is_array($categories) && count($categories) === 1 ) {
            $cat = strtolower(trim($categories[0]));

            if ( !empty($cat) ) {
                if ( !isset($category_counts[$cat]) ) {
                    $category_counts[$cat] = 0;
                }
                $category_counts[$cat]++;
            }
        }

    }

    return $category_counts;
}

$post_count_by_category = fount_post_number_by_category();
$bike_count = isset( $post_count_by_category['bike'] ) ? $post_count_by_category['bike'] : 0;
$boat_count = isset( $post_count_by_category['boat'] ) ? $post_count_by_category['boat'] : 0;
$car_count = isset( $post_count_by_category['car'] ) ? $post_count_by_category['car'] : 0;
?>

<div class="rbfw_rental_lists_container">
    <div class="rbfw_rental_lists_analytics-section">
        <h2 class="rbfw_rental_lists_analytics-title"><?php esc_attr_e( 'Rental Analytics', 'booking-and-rental-manager-for-woocommerce' );?></h2>
        <div class="rbfw_rental_lists_analytics-grid">
            <div class="rbfw_rental_lists_analytics-card">
                <div class="rbfw_rental_lists_analytics-number"><?php echo esc_attr( $total_event ); ?></div>
                <div class="rbfw_rental_lists_analytics-label"><?php esc_attr_e( 'Total Items', 'booking-and-rental-manager-for-woocommerce' );?></div>
            </div>
            <div class="rbfw_rental_lists_analytics-card cars">
                <div class="rbfw_rental_lists_analytics-number"><?php echo esc_attr( $car_count )?></div>
                <div class="rbfw_rental_lists_analytics-label"><?php esc_attr_e('Cars Available', 'booking-and-rental-manager-for-woocommerce' );?></div>
            </div>
            <div class="rbfw_rental_lists_analytics-card boats">
                <div class="rbfw_rental_lists_analytics-number"><?php echo esc_attr( $boat_count )?></div>
                <div class="arbfw_rental_lists_nalytics-label"><?php esc_attr_e( 'Boats Available', 'booking-and-rental-manager-for-woocommerce' );?></div>
            </div>
            <div class="rbfw_rental_lists_analytics-card sports">
                <div class="rbfw_rental_lists_analytics-number"><?php echo esc_attr( $bike_count )?></div>
                <div class="rbfw_rental_lists_analytics-label"><?php esc_attr_e( 'Bike Available', 'booking-and-rental-manager-for-woocommerce' );?></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="rbfw_rental_lists_main-content">
        <div class="rbfw_rental_lists_header">
            <div class="rbfw_rental_lists_header-left">
                <div class="rbfw_rental_lists_status-tabs">
                    <button data-by-filter="all" class="rbfw_rental_lists_status-tab active"><?php esc_attr_e( 'All ('.$total_event.')', 'booking-and-rental-manager-for-woocommerce' );?></button>
                    <button data-by-filter="publish" class="rbfw_rental_lists_status-tab"><?php esc_attr_e( 'Published ('.$post_counts['publish'].')', 'booking-and-rental-manager-for-woocommerce' );?></button>
                    <button data-by-filter="draft" class="rbfw_rental_lists_status-tab"><?php esc_attr_e( 'Draft ('.$post_counts['draft'].')', 'booking-and-rental-manager-for-woocommerce' );?></button>
                    <a href="<?php echo esc_url( $trash_url );?>"><button data-by-filter="trash" class="rbfw_rental_lists_status-tab"><?php esc_attr_e( 'Trash ('.$post_counts['trash'].')', 'booking-and-rental-manager-for-woocommerce' );?></button></a>
                </div>
            </div>
            <div class="rbfw_rental_lists_header-right">
                <div class="rbfw_rental_lists_controls">
                    <a href="<?php echo esc_url( $add_new_link );?>"><button class="rbfw_rental_lists_btn rbfw_rental_lists_btn-primary rbfw_rental_lists_add-new-btn"><?php esc_attr_e( '+ Add New', 'booking-and-rental-manager-for-woocommerce' );?></button></a>
                    <div class="rbfw_rental_lists_search-box">
                        <input type="text" class="rbfw_rental_lists_search-input" id="rbfw_rental_lists_search-input" placeholder="<?php esc_attr_e( 'Search Rent Item', 'booking-and-rental-manager-for-woocommerce' )?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="rbfw_rental_lists_table-container">
            <table>
                <thead>
                <tr>
                    <th class="rbfw_rental_image"><?php esc_attr_e( 'Image', 'booking-and-rental-manager-for-woocommerce' );?></th>
                    <th><?php esc_attr_e( 'Title', 'booking-and-rental-manager-for-woocommerce' );?></th>
                    <th><?php esc_attr_e( 'Template', 'booking-and-rental-manager-for-woocommerce' );?></th>
                    <th><?php esc_attr_e( 'Shortcode', 'booking-and-rental-manager-for-woocommerce' );?></th>
                    <th><?php esc_attr_e( 'Price Type', 'booking-and-rental-manager-for-woocommerce' );?></th>
                    <th width="120"><?php esc_attr_e( 'Actions', 'booking-and-rental-manager-for-woocommerce' );?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                echo wp_kses_post( render_mep_events_by_status( $posts ) );
                ?>
                </tbody>
            </table>
        </div>

        <!-- Load More -->
        <div class="rbfw_rental_lists_pagination">
            <div class="rbfw_rental_lists_pagination-info">
                <?php esc_attr_e( 'Showing', 'mage-eventpress' );?> <span id="visibleCount">0</span> of <span id="totalCount">0</span> <?php esc_attr_e( ' events', 'mage-eventpress' );?>
            </div>
            <button class="rbfw_rental_lists_load-more-btn" id="rbfw_loadMoreBtn">
                <span><?php esc_attr_e( 'Load More Events', 'mage-eventpress' );?></span>
                <span>↓</span>
            </button>
        </div>

    </div>
</div>
