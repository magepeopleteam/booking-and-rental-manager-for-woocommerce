<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

/******************************
 * Rent List Shortcode
 ******************************/
add_shortcode('rent-list', 'rbfw_rent_list_shortcode_func');
function rbfw_rent_list_shortcode_func($atts = null) {

    $attributes = shortcode_atts( array(
        'style' => 'grid',
        'show'  => -1,
        'order' => 'DESC',
        'orderby' => '',
        'meta_key' => '',
        'type'  => '',
        'location' => '',
        'category' => '',
        'cat_ids' => '',
        'columns' => '',
    ), $atts );

    $style  = $attributes['style'];
    $show   = $attributes['show'];
    $order  = $attributes['order'];
    $orderby  = $attributes['orderby'];
    $meta_key  = $attributes['meta_key'];
    $type   = $attributes['type'];
    $location   = $attributes['location'];
    $category   = $attributes['category'];
    $cat_ids   = $attributes['cat_ids'];
    $columns   = $attributes['columns'];

    if(!$category){
        $category  = $cat_ids;
    }


    $rent_type = !empty($type) ? array(
        'key' => 'rbfw_item_type',
        'value' => $type,
        'compare' => '==',
    ) : '';
    $location_query = !empty($location) ? array(
        'key' => 'rbfw_pickup_data',
        'value' => $location,
        'compare' => 'LIKE'
    ) : '';

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'rbfw_item',
        'posts_per_page' => $show,
        'paged' => $paged,
        'meta_key' => $meta_key,
        'orderby' => $orderby,
        'order' => $order,
        'meta_query' => array(
            'relation' => 'OR',
            $rent_type,
            $location_query
        )
    );

    if(!empty($category)):
        $category = explode(',', $category);
        foreach ($category as $cat){
            $category_name=isset(get_term($cat)->name) ? get_term($cat)->name : '';
            $args['meta_query'][] = array(
                'key' => 'rbfw_categories',
                'value' => $category_name,
                'compare' => 'LIKE'
            );
        }
    endif;

    $query = new WP_Query($args);
    $total_posts = $query->found_posts;
    $post_count = $query->post_count;

    if(isset($_COOKIE['rbfw_rent_item_list_grid'])) {
        $rbfw_rent_item_list_grid = $_COOKIE['rbfw_rent_item_list_grid'];
//        error_log( print_r( ['$rbfw_rent_item_list_grid' => $rbfw_rent_item_list_grid], true ) );
    }else{
        $rbfw_rent_item_list_grid = '';
    }

    if( $rbfw_rent_item_list_grid === '' ){
        if( $style == 'grid' ){
            $image_holder = 'rbfw_rent_list_grid_view_top';
            $rent_item_info = 'rbfw_inner_details';
            $rent_item_list_info = 'rbfw_rent_list_info';
            $is_display = 'none';
            $style = 'grid';
            $is_grid_selected = 'selected_list_grid';
            $is_list_selected = '';
        }else{
            $image_holder = 'rbfw_rent_list_lists_images';
            $rent_item_info = 'rbfw_rent_list_lists_info';
            $rent_item_list_info = 'rbfw_rent_item_content_list_bottom';
            $is_display = 'grid';
            $style = 'list';
            $is_grid_selected = '';
            $is_list_selected = 'selected_list_grid';
        }
    }else{
        if( $rbfw_rent_item_list_grid == 'rbfw_rent_item_grid' ){
            $image_holder = 'rbfw_rent_list_grid_view_top';
            $rent_item_info = 'rbfw_inner_details';
            $rent_item_list_info = 'rbfw_rent_list_info';
            $is_display = 'none';
            $style = 'grid';
            $is_grid_selected = 'selected_list_grid';
            $is_list_selected = '';
        }else{
            $image_holder = 'rbfw_rent_list_lists_images';
            $rent_item_info = 'rbfw_rent_list_lists_info';
            $rent_item_list_info = 'rbfw_rent_item_content_list_bottom';
            $is_display = 'grid';
            $style = 'list';
            $is_grid_selected = '';
            $is_list_selected = 'selected_list_grid';
        }
    }

    ob_start();
//echo '<pre>';print_r($query);echo '</pre>';
    $grid_class = 'rbfw-w-33';

    if($columns){
        $grid_class = ($columns==1 || $columns==2)?'rbfw-w-50':(($columns==3)?'rbfw-w-33':(($columns==4)?'rbfw-w-25':(($columns==5)?'rbfw-w-20':'rbfw-w-20')));
    }

    $shoe_result =  $total_posts. ' results. Showing '.$post_count. ' of '. $total_posts. ' of total';
    ?>
    <div class="rbfw_rent_show_result_list_grid_icon_holder">
        <div class="shoe_result_text">
            <span> <?php echo esc_attr( $shoe_result );?></span>
        </div>
        <div class="rbfw_rent_list_grid_icon_holder">
            <div class="rbfw_rent_items_list_grid rbfw_rent_items_grid <?php echo esc_attr( $is_grid_selected )?>" id="rbfw_rent_items_grid">Grid</div>
            <div class="rbfw_rent_items_list_grid rbfw_rent_items_list <?php echo esc_attr( $is_list_selected )?>" id="rbfw_rent_items_list">List</div>
        </div>
    </div>
    <div class="rbfw_rent_list_wrapper <?php echo $grid_class ?> rbfw_rent_list_style_<?php echo esc_attr($style); ?>" id="rbfw_rent_list_wrapper">

        <?php
        $d = 1;
        if($query->have_posts()): while ( $query->have_posts() ) : $query->the_post();
            $the_content = get_the_content();

            $rbfw_id = get_the_id();

            $expire = 'no';
            $rbfw_enable_start_end_date  = get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_start_end_date', true ) : 'yes';

            if($rbfw_enable_start_end_date=='no'){
                $rbfw_event_end_date  = get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_date', true ) : '';
                $rbfw_event_end_time  = get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) ? get_post_meta( $rbfw_id, 'rbfw_event_end_time', true ) : '';
                $rbfw_event_end_time  = date('h:i a', strtotime($rbfw_event_end_time));
                $rbfw_event_end_time  = date('h:i a', strtotime($rbfw_event_end_time));
                $rbfw_event_last_date = strtotime(date_i18n('Y-m-d h:i a', strtotime($rbfw_event_end_date.' '.$rbfw_event_end_time)));
                $rbfw_todays_date = strtotime(date_i18n('Y-m-d h:i a'));
                if($rbfw_event_last_date<$rbfw_todays_date){
                    $expire = 'yes';
                }
            }
            // load c
            if($expire == 'no'){
//                $grid=RBFW_Function::get_template_path('archive/grid.php');
                $grid=RBFW_Function::get_template_path('archive/grid_new.php');
//                $list=RBFW_Function::get_template_path('archive/list.php');
                $list=RBFW_Function::get_template_path('archive/list_new.php');

                if($style == 'grid'){
                    include($grid);
                }
                elseif($style == 'list'){
                    include($list);
                }
                else{
                    include( $list );
                }
            }
            $d++;
        endwhile;
        else:
            ?>
            <div class="rbfw-lsn-new-message-box">
                <div class="rbfw-lsn-new-message-box-info">
                    <div class="rbfw-lsn-info-tab rbfw-lsn-tip-icon-info" title="error"><i></i></div>
                    <div class="rbfw-lsn-tip-box-info">
                        <p><?php rbfw_string('rbfw_text_nodatafound',__('Sorry, no data found!','booking-and-rental-manager-for-woocommerce')); ?></p>
                    </div>
                </div>
            </div>
        <?php
        endif;

        wp_reset_query();
        ?>
    </div>
    <?php
    $content = ob_get_clean();

    if( isset( $atts['pagination'] ) && $atts['pagination'] == 'yes') {
        $content .= '<div class="pagination rbfw_pagination">';
        $content .= paginate_links(array(
            'total' => $query->max_num_pages,
            'prev_text' => __('« '), // Optional: Add previous and next text
            'next_text' => __(' »'),
        ));
        $content .= '</div>';
    }
    wp_reset_postdata();

    return $content;
}

/******************************
 * Single Add to Cart Shortcode
 ******************************/
add_shortcode('rent-add-to-cart', 'rbfw_add_to_cart_shortcode_func');

function rbfw_add_to_cart_shortcode_func($atts){

   // echo print_r($atts);exit;



    $attributes = shortcode_atts( array(
        'id' => '',
        'backend' => ''
    ), $atts );



    $post_id = $attributes['id'];
    $backend = $attributes['backend']??0;





    define("add_to_cart_id", $post_id);


    if(empty($post_id)){
        return;
    }

    $rbfw_item_type = get_post_meta($post_id, 'rbfw_item_type', true);


    if(!$backend){
        ob_start();
        do_action( 'woocommerce_before_single_product' );
    }


    if($rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){

        include( RBFW_TEMPLATE_PATH . 'forms/single-day-registration.php' );

        $BikeCarSdclass = new RBFW_BikeCarSd_Function();
        $BikeCarSdclass->rbfw_bike_car_sd_frontend_scripts($post_id);

    }
    elseif($rbfw_item_type == 'bike_car_md' || $rbfw_item_type == 'equipment' || $rbfw_item_type == 'dress' || $rbfw_item_type == 'others'){


        include(  RBFW_TEMPLATE_PATH . 'forms/multi-day-registration.php' );

        $BikeCarMdclass = new RBFW_BikeCarMd_Function();
        $BikeCarMdclass->rbfw_bike_car_md_frontend_scripts($post_id);

    }
    elseif($rbfw_item_type == 'resort'){

        include(  RBFW_TEMPLATE_PATH . 'forms/resort-registration.php' );

        $Resortclass = new RBFW_Resort_Function();
        $Resortclass->rbfw_resort_frontend_scripts($post_id);
    }

    if(!$backend){
        $content = ob_get_clean();
        return $content;
    }

}

/******************************
 * Rent Filter Form Shortcode
 ******************************/
add_shortcode('rbfw-search', 'rbfw_rent_search_shortcode_func');
function rbfw_rent_search_shortcode_func() {

    $search_page_id = rbfw_get_option('rbfw_search_page','rbfw_basic_gen_settings');
    $search_page_link = get_page_link($search_page_id);
    $location_arr = rbfw_get_location_arr();
    $location = !empty($_GET['rbfw_search_location']) ? strip_tags($_GET['rbfw_search_location']) : '';
    ?>
    <div class="rbfw_search_form_wrap">
        <form class="rbfw_search_form" action="<?php echo esc_url($search_page_link); ?>" method="GET">
            <div class="rbfw_search_form_col">
                <label><?php rbfw_string('rbfw_text_pickup_location',__('Pickup Location','booking-and-rental-manager-for-woocommerce')); ?></label>
                <select name="rbfw_search_location">
                    <?php foreach ( $location_arr as $key => $value ) { ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php if($location == $key){ echo 'selected'; }?>><?php echo esc_html($value); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="rbfw_search_form_col">
                <label></label>
                <button type="submit" name="rbfw_search_submit" class="rbfw_search_submit"><?php rbfw_string('rbfw_text_search',__('Search','booking-and-rental-manager-for-woocommerce')); ?></button>
            </div>
        </form>
    </div>
    <?php
}

add_shortcode('rbfw_rent_search', 'rbfw_rent_search_shortcode' );
//[rbfw_search] bike_car_sd, appointment, bike_car_md, equipment, dress, resort, others
function rbfw_rent_search_shortcode( $attr ){
    $search_page_id = rbfw_get_option('rental-product','rbfw_basic_gen_settings');
    $search_page_id = rbfw_get_option('rental-product','rbfw_basic_gen_settings');
    $search_page_link = get_page_link($search_page_id);
    $location = !empty($_GET['rbfw_search_location']) ? strip_tags($_GET['rbfw_search_location']) : '';
    $type = !empty($_GET['rbfw_search_type']) ? strip_tags($_GET['rbfw_search_type']) : '';
    $pickup_date = !empty($_GET['rbfw-pickup-date']) ? strip_tags($_GET['rbfw-pickup-date']) : '';

    $location_arr = rbfw_get_location_arr();
    $all_types = array(
            'bike_car_sd'   => 'Bike Car SD',
            'appointment'   => 'Appointment',
            'bike_car_md'   => 'Bike Car MD',
            'equipment'     => 'Equipment',
            'dress'         => 'Dress',
            'resort'        => 'Resort',
            'others'        => 'Others',
    );

    ob_start();
    ?>

    <section class="rbfw_rent_item_search_elementor_section">
        <div class="rbfw_rent_item_search_elementor_container">
            <form class="rbfw_search_form_new" action="<?php echo esc_url($search_page_link); ?>" method="GET">
                <div class="rbfw_rent_item_search_container">
                    <div class="rbfwRentItemSearchTitleHolder">
                        <div class="rbfw_rent_item_Search_tiitle_text">
                            <div class="rbfw_rent_item_header"><?php rbfw_string('rbfw_text_quick_search',__('Quick search','booking-and-rental-manager-for-woocommerce')); ?></div>
                        </div>
                    </div>
                    <div class="rbfw_rent_item_searchContentHolder">
                        <div class="rbfw_rent_item_searchTypeLocationHolder">
                            <div class="rbfw_rent_item_search_item">
                                <label for="rbfw_rent_item_search_type"><?php rbfw_string('rbfw_text_type',__('Type','booking-and-rental-manager-for-woocommerce')); ?></label>
                                <select class="rbfw_rent_item_search_type_location" name="rbfw_search_type" id="rbfw_rent_item_search_type">
                                    <option value="all_type"><?php rbfw_string('rbfw_text_type',__('All Types','booking-and-rental-manager-for-woocommerce')); ?></option>
                                    <?php foreach ( $all_types as $key => $value ) { ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php if($type == $key){ echo 'selected'; }?>> <?php echo esc_html($value); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="rbfw_rent_item_search_item">
                                <label for="rbfw_rent_item_search_location"><?php rbfw_string('rbfw_text_pickup_location',__('Pickup Location','booking-and-rental-manager-for-woocommerce')); ?></label>
                                <select class="rbfw_rent_item_search_type_location" name="rbfw_search_location" id="rbfw_rent_item_search_location">
    <!--                                <option value="all_location">All Locations</option>-->
                                    <?php foreach ( $location_arr as $key => $value ) { ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php if($location == $key){ echo 'selected'; }?>><?php echo esc_html($value); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="rbfw_rent_item_search_dateButtonHolder">
                            <div class="rbfw_rent_item_search-item_date">
                                <div class="rbfw_rent_item_date_picker">
                                    <label for="rbfw_rent_item_search_pickup_date"><?php rbfw_string('rbfw_text_pickup_date',__('Pickup Date','booking-and-rental-manager-for-woocommerce')); ?></label>
                                    <div class="date-picker-wrapper">
                                        <input type="text" name="rbfw-pickup-date" id="rbfw_rent_item_search_pickup_date" value="<?php echo esc_attr( $pickup_date )?>">
                                        <i class="fa fa-calendar" id="rbfw_rent_item_search_calendar_icon"></i>
                                    </div>
                                </div>

                            </div>
                            <!-- Search Button -->
                            <div class="rbfw_rent_item_search_button_holder">
                                <div class="rbfw_rent_item_search_button">
                                    <button type="submit" class="rbfw_rent_item_search_submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </section>
<?php
    $search_content = ob_get_clean();

    return $search_content;
//    ob_get_clean(); }
}

?>