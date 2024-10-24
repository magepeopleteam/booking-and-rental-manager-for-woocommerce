<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('wp', 'rbfw_hide_hidden_product_from_single', 90);

if (!function_exists('rbfw_hide_hidden_product_from_single')) {
    function rbfw_hide_hidden_product_from_single() {
        global $post, $wp_query;
        if (is_product()) {
            $post_id = $post->ID;
            $visibility = get_the_terms($post_id, 'product_visibility') ? get_the_terms($post_id, 'product_visibility') : [0];
            if (is_object($visibility[0])) {
                if ($visibility[0]->name == 'exclude-from-catalog') {
                    $check_event_hidden = get_post_meta($post_id, 'link_rbfw_id', true) ? get_post_meta($post_id, 'link_rbfw_id', true) : 0;
                    if ($check_event_hidden > 0) {
                        $wp_query->set_404();
                        status_header(404);
                        get_template_part(404);
                        exit();
                    }
                }
            }
        }
    }
}


// Language Load
add_action( 'init', 'rbfw_language_load' );
function rbfw_language_load() {
    $plugin_dir = basename( dirname( __DIR__ ) ) . "/languages/";
    load_plugin_textdomain( 'booking-and-rental-manager-for-woocommerce', false, $plugin_dir );
}
function rbfw_array_strip( $array_or_string ) {
    if ( is_string( $array_or_string ) ) {
        $array_or_string = sanitize_text_field( $array_or_string );
    } elseif ( is_array( $array_or_string ) ) {
        foreach ( $array_or_string as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = rbfw_array_strip( $value );
            } else {
                $value = sanitize_text_field( $value );
            }
        }
    }

    return $array_or_string;
}



function rbfw_get_location_arr() {
    $terms = get_terms( array(
        'taxonomy'   => 'rbfw_item_location',
        'hide_empty' => false,
    ) );
    $arr   = array(
        '' => rbfw_string_return('rbfw_text_pls_select_location',__('Please Select a Location','booking-and-rental-manager-for-woocommerce'))
    );
    foreach ( $terms as $_terms ) {
        $arr[ $_terms->name ] = $_terms->name;
    }

    return $arr;
}

function rbfw_get_category_arr() {
    $terms = get_terms( array(
        'taxonomy'   => 'rbfw_item_caregory',
        'hide_empty' => false,
    ) );
    $arr   = array(
        '' => rbfw_string_return('rbfw_text_pls_select_category',__('Please Select a Category','booking-and-rental-manager-for-woocommerce'))
    );
    foreach ( $terms as $_terms ) {
        $arr[ $_terms->term_id ] = $_terms->name;
    }

    return $arr;
}

function rbfw_get_location_dropdown( $name, $saved_value = '', $class = '' ) {
    $location_arr = rbfw_get_location_arr();
    echo "<select name=$name class=$class>";
    foreach ( $location_arr as $key => $value ) {
        $selected_text = ! empty( $saved_value ) && $saved_value == $key ? 'Selected' : '';
        echo "<option value='$key' $selected_text>" . esc_html( $value ) . "</option>";
    }
    echo "</select>";
}


function rbfw_get_category_dropdown( $name, $saved_value = '', $class = '' ) {
    $category_arr = rbfw_get_category_arr();
    echo "<select name=$name class=$class>";
    foreach ( $category_arr as $key => $value ) {
        $selected_text = ! empty( $saved_value ) && $saved_value == $key ? 'Selected' : '';
        echo "<option value='$key' $selected_text>" . esc_html( $value ) . "</option>";
    }
    echo "</select>";
}

function rbfw_get_option( $option, $section, $default = '' ) {
    global $rbfw;

    return $rbfw->get_option_trans( $option, $section, $default );
}

function rbfw_end_time(){
    global $rbfw;
    $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
    if($rbfw_count_extra_day_enable=='on'){
        return '24:00:00';
    }else{
        return '00:00:00';
    }
}


function rbfw_get_string( $option_name, $default_string ) {
    return rbfw_get_option( $option_name, 'rbfw_basic_translation_settings', $default_string );
}
function rbfw_string( $option_name, $default_string ) {
    echo rbfw_get_option( $option_name, 'rbfw_basic_translation_settings', $default_string );
}
function rbfw_string_return( $option_name, $default_string ) {
    return rbfw_get_option( $option_name, 'rbfw_basic_translation_settings', $default_string );
}


function rbfw_get_datetime( $date, $type = 'date-time-text' ) {
    global $rbfw;
    return $rbfw->get_datetime( $date, $type );
}

function rbfw_get_order_item_meta( $item_id, $key ) {
    global $rbfw;

    return $rbfw->get_order_meta( $item_id, $key );
}

function rbfw_check_product_exists( $id ) {
    return is_string( get_post_status( $id ) );
}

function rbfw_get_event_extra_price_by_name( $name, $event_id ) {
    $ticket_type_arr = get_post_meta( $event_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $event_id, 'rbfw_extra_service_data', true ) : [];
    foreach ( $ticket_type_arr as $price ) {
        if ( $price['service_name'] === $name ) {
            $p = $price['service_price'];
        }
    }

    return $p;
}
function rbfw_get_extra_price_arr( $ticket_type, $rbfw_id ) {
    $price = [];
    //print_r($ticket_type);
    foreach ( $ticket_type as $ticket ) {
        // print_r($ticket);
        $exp_qty = explode( "*", $ticket );
        //$price[] = rbfw_get_event_extra_price_by_name( $exp_qty[1], $rbfw_id );
        $price[] = rbfw_get_event_extra_price_by_name( $ticket, $rbfw_id );
    }

    return $price;
}


if ( ! function_exists( 'mep_get_date_diff' ) ) {
    function mep_get_date_diff( $start_datetime, $end_datetime ) {

        $current   = date( 'Y-m-d H:i', strtotime( $start_datetime ) );
        $newformat = date( 'Y-m-d H:i', strtotime( $end_datetime ) );
        $datetime1 = new DateTime( $newformat );
        $datetime2 = new DateTime( $current );
        $interval  = date_diff( $datetime2, $datetime1 );

        $arr = [];
        if($start_datetime == $end_datetime){
            $days = 1;
        } else {
            $days = $interval->days;
        }
        if(!empty($interval->h)){
            $hours = $interval->h;
        } else {
            $hours = 0;
        }
        if(!empty($interval->i)){
            $minutes = $interval->i;
        } else {
            $minutes = 0;
        }

        return [ $days, $hours, $minutes ];

    }
}
// Getting event exprie date & time
if ( ! function_exists( 'rbfw_day_diff_status' ) ) {
    function rbfw_day_diff_status( $start_datetime, $end_datetime ) {
        $current   = date( 'Y-m-d H:i', strtotime( $start_datetime ) );
        $newformat = date( 'Y-m-d H:i', strtotime( $end_datetime ) );
        $datetime1 = new DateTime( $newformat );
        $datetime2 = new DateTime( $current );
        $interval  = date_diff( $datetime2, $datetime1 );

        if ( current_time( 'Y-m-d H:i' ) > $newformat ) {
            return "<span class=err>Expired</span>";
        } else {

            $days    = $interval->days;
            $hours   = $interval->h;
            $minutes = $interval->i;
            if ( $days > 0 ) {
                global $rbfw;
                $dd = $days .' '. esc_html__( 'Days', 'booking-and-rental-manager-for-woocommerce' );
            } else {
                $dd = "";
            }
            if ( $hours > 0 ) {
                $hh = $hours .' '. esc_html__( 'hours', 'booking-and-rental-manager-for-woocommerce' );
            } else {
                $hh = "";
            }
            if ( $minutes > 0 ) {
                $mm = $minutes .' '. esc_html__( 'minutes', 'booking-and-rental-manager-for-woocommerce' );
            } else {
                $mm = "";
            }
            return "<span class='active'>" . esc_html( $dd ) . " " . esc_html( $hh ) . " " . esc_html( $mm ) . "</span>";

        }
    }
}

add_action( 'rbfw_availabe_label', 'rbfw_show_availabe_label', 10, 2 );
function rbfw_show_availabe_label( $availabe_type_seat, $rbfw_id ) {
    $stock_status = get_post_meta( $rbfw_id, 'rbfw_inventory_manage', true ) ? get_post_meta( $rbfw_id, 'rbfw_inventory_manage', true ) : 'yes';
    if ( $stock_status == 'yes' ) {
        ?>
        <p class='rbfw_availabe_seat_label'><?php echo esc_html( $availabe_type_seat ) . ' ';
            rbfw_string( 'rbfw_string_availabe', __( 'Availabe', 'booking-and-rental-manager-for-woocommerce' ) ); ?></p>
        <?php
    }
}
function rbfw_woo_install_check() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        return 'Yes';
    } elseif ( is_dir( $plugin_dir ) ) {
        return 'Installed But Not Active';
    } else {
        return 'No';
    }
}
add_filter( 'manage_rbfw_item_posts_columns', 'rbfw_item_col_mod_head' );
function rbfw_item_col_mod_head( $columns ) {

    // unset( $columns['taxonomy-rbfw_item_cat'] );
    // unset( $columns['taxonomy-rbfw_item_org'] );
    unset( $columns['taxonomy-rbfw_item_location'] );
    // $columns['mep_event_date'] = __( 'Event Start Date', 'mage-eventpress' );
    return $columns;
}
function rbfw_create_tag_taxonomy() {
    $labels = array(
        'name'              => _x( 'Tags', 'dora' ),
        'singular_name'     => _x( 'Tags', 'booking-and-rental-manager-for-woocommerce' ),
        'search_items'      => __( 'Search Tags' ),
        'all_items'         => __( 'All Tags' ),
        'parent_item'       => __( 'Parent Tag' ),
        'parent_item_colon' => __( 'Parent Tag:' ),
        'edit_item'         => __( 'Edit Tag' ),
        'update_item'       => __( 'Update Tag' ),
        'add_new_item'      => __( 'Add New Tag' ),
        'new_item_name'     => __( 'New Tag Name' ),
        'menu_name'         => __( 'Tags' ),
    );
// 		register_taxonomy( 'rbfw_item_tag', array( 'rbfw_item' ), array(
// 			'hierarchical'      => false,
// 			'labels'            => $labels,
// 			'show_ui'           => true,
// 			'show_in_rest'      => true,
// 			'show_admin_column' => true,
// 			'query_var'         => true,
// 			'rewrite'           => array( 'slug' => 'rbfw_item_tag' ),
// 		) );
}
add_action( 'init', 'rbfw_create_tag_taxonomy', 0 );
if ( ! function_exists( 'mep_esc_html' ) ) {
    function mep_esc_html( $string ) {
        $allow_attr = array(
            'input'    => array(
                'br'                 => [],
                'type'               => [],
                'class'              => [],
                'id'                 => [],
                'name'               => [],
                'value'              => [],
                'size'               => [],
                'placeholder'        => [],
                'min'                => [],
                'max'                => [],
                'checked'            => [],
                'required'           => [],
                'disabled'           => [],
                'readonly'           => [],
                'step'               => [],
                'data-default-color' => [],
            ),
            'p'        => [
                'class' => []
            ],
            'img'      => [
                'class' => [],
                'id'    => [],
                'src'   => [],
                'alt'   => [],
            ],
            'fieldset' => [
                'class' => []
            ],
            'label'    => [
                'for'   => [],
                'class' => []
            ],
            'select'   => [
                'class' => [],
                'name'  => [],
                'id'    => [],
            ],
            'option'   => [
                'class'    => [],
                'value'    => [],
                'id'       => [],
                'selected' => [],
            ],
            'textarea' => [
                'class' => [],
                'rows'  => [],
                'id'    => [],
                'cols'  => [],
                'name'  => [],
            ],
            'h2'       => [ 'class' => [], 'id' => [], ],
            'a'        => [ 'class' => [], 'id' => [], 'href' => [], ],
            'div'      => [ 'class' => [], 'id' => [], 'data' => [], ],
            'span'     => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'i'        => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'table'    => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'tr'       => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'td'       => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'thead'    => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'tbody'    => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'th'       => [
                'class' => [],
                'id'    => [],
                'data'  => [],
            ],
            'svg'      => [
                'class'   => [],
                'id'      => [],
                'width'   => [],
                'height'  => [],
                'viewBox' => [],
                'xmlns'   => [],
            ],
            'g'        => [
                'fill' => [],
            ],
            'path'     => [
                'd' => [],
            ],
            'br'       => array(),
            'em'       => array(),
            'strong'   => array(),
        );

        return wp_kses( $string, $allow_attr );
    }
}
if ( ! function_exists( 'mep_trim_string' ) ) {
    function mep_trim_string($text, $length = 20){
        $trimmed_text = substr($text, 0, $length);
        if (strlen($text) > $length) {
            $trimmed_text .= '...';
        }
        return $trimmed_text;
    }
}
if ( ! function_exists( 'rbfw_field_generator' ) ) {
    function rbfw_field_generator( $type, $option ) {

        $FormFieldsGenerator = new RbfwFormFieldsGenerator();
        if ( $type === 'text' ) {
            return $FormFieldsGenerator->field_text( $option );
        } elseif ( $type === 'text_multi' ) {
            return $FormFieldsGenerator->field_text_multi( $option );
        } elseif ( $type === 'textarea' ) {
            return $FormFieldsGenerator->field_textarea( $option );
        } elseif ( $type === 'checkbox' ) {
            return $FormFieldsGenerator->field_checkbox( $option );
        } elseif ( $type === 'checkbox_multi' ) {
            return $FormFieldsGenerator->field_checkbox_multi( $option );
        } elseif ( $type === 'radio' ) {
            return $FormFieldsGenerator->field_radio( $option );
        } elseif ( $type === 'select' ) {
            return $FormFieldsGenerator->field_select( $option );
        } elseif ( $type === 'range' ) {
            return $FormFieldsGenerator->field_range( $option );
        } elseif ( $type === 'range_input' ) {
            return $FormFieldsGenerator->field_range_input( $option );
        } elseif ( $type === 'switch' ) {
            return $FormFieldsGenerator->field_switch( $option );
        } elseif ( $type === 'switch_multi' ) {
            return $FormFieldsGenerator->field_switch_multi( $option );
        } elseif ( $type === 'switch_img' ) {
            return $FormFieldsGenerator->field_switch_img( $option );
        } elseif ( $type === 'time_format' ) {
            return $FormFieldsGenerator->field_time_format( $option );
        } elseif ( $type === 'date_format' ) {
            return $FormFieldsGenerator->field_date_format( $option );
        } elseif ( $type === 'datepicker' ) {
            return $FormFieldsGenerator->field_datepicker( $option );
        } elseif ( $type === 'color_sets' ) {
            return $FormFieldsGenerator->field_color_sets( $option );
        } elseif ( $type === 'colorpicker' ) {
            return $FormFieldsGenerator->field_colorpicker( $option );
        } elseif ( $type === 'colorpicker_multi' ) {
            return $FormFieldsGenerator->field_colorpicker_multi( $option );
        } elseif ( $type === 'link_color' ) {
            return $FormFieldsGenerator->field_link_color( $option );
        } elseif ( $type === 'icon' ) {
            return $FormFieldsGenerator->field_icon( $option );
        } elseif ( $type === 'icon_multi' ) {
            return $FormFieldsGenerator->field_icon_multi( $option );
        } elseif ( $type === 'dimensions' ) {
            return $FormFieldsGenerator->field_dimensions( $option );
        } elseif ( $type === 'wp_editor' ) {
            return $FormFieldsGenerator->field_wp_editor( $option );
        } elseif ( $type === 'select2' ) {
            return $FormFieldsGenerator->field_select2( $option );
        } elseif ( $type === 'faq' ) {
            return $FormFieldsGenerator->field_faq( $option );
        } elseif ( $type === 'grid' ) {
            return $FormFieldsGenerator->field_grid( $option );
        } elseif ( $type === 'color_palette' ) {
            return $FormFieldsGenerator->field_color_palette( $option );
        } elseif ( $type === 'color_palette_multi' ) {
            return $FormFieldsGenerator->field_color_palette_multi( $option );
        } elseif ( $type === 'media' ) {
            return $FormFieldsGenerator->field_media( $option );
        } elseif ( $type === 'media_multi' ) {
            return $FormFieldsGenerator->field_media_multi( $option );
        } elseif ( $type === 'repeatable' ) {
            return $FormFieldsGenerator->field_repeatable( $option );
        } elseif ( $type === 'user' ) {
            return $FormFieldsGenerator->field_user( $option );
        } elseif ( $type === 'margin' ) {
            return $FormFieldsGenerator->field_margin( $option );
        } elseif ( $type === 'padding' ) {
            return $FormFieldsGenerator->field_padding( $option );
        } elseif ( $type === 'border' ) {
            return $FormFieldsGenerator->field_border( $option );
        } elseif ( $type === 'switcher' ) {
            return $FormFieldsGenerator->field_switcher( $option );
        } elseif ( $type === 'password' ) {
            return $FormFieldsGenerator->field_password( $option );
        } elseif ( $type === 'post_objects' ) {
            return $FormFieldsGenerator->field_post_objects( $option );
        } elseif ( $type === 'google_map' ) {
            return $FormFieldsGenerator->field_google_map( $option );
        } elseif ( $type === 'image_link' ) {
            return $FormFieldsGenerator->field_image_link( $option );
        } elseif ( $type === 'number' ) {
            return $FormFieldsGenerator->field_number( $option );
        }elseif ( $type === 'time_slot' ) {
            return $FormFieldsGenerator->field_time_slot( $option );
        }elseif (  $type === 'add_to_cart_shortcode' ) {
            return $FormFieldsGenerator->field_add_to_cart_shortcode( $option );
        }elseif (  $type === 'rbfw_add_category' ) {
            return $FormFieldsGenerator->field_rbfw_add_category( $option );
        }elseif ( $type === 'md_service_category_price' ) {
            return $FormFieldsGenerator->field_service_price( $option );
        }
        elseif ( $type === 'time_slot' ) {
            return $FormFieldsGenerator->field_time_slot( $option );
        } else {
            return '';
        }
    }
}




if ( ! function_exists( 'mage_array_strip' ) ) {
    function mage_array_strip( $array_or_string ) {
        if ( is_string( $array_or_string ) ) {
            $array_or_string = sanitize_text_field( htmlentities( nl2br( $array_or_string ) ) );
        } elseif ( is_array( $array_or_string ) ) {
            foreach ( $array_or_string as $key => &$value ) {
                if ( is_array( $value ) ) {
                    $value = mage_array_strip( $value );
                } else {
                    $value = sanitize_text_field( htmlentities( nl2br( $value ) ) );
                }
            }
        }

        return $array_or_string;
    }
}

/************************
 * GET RENT FAQ's Content
 *************************/
add_action('rbfw_the_faq_only','rbfw_get_faq_func');
function rbfw_get_faq_func($post_id){

    if(empty($post_id)){
        return;
    }

    $rbfw_faq_arr 		= get_post_meta( $post_id, 'mep_event_faq', true );

    if(! empty($rbfw_faq_arr)){

        $rbfw_faq_title 	= array_column($rbfw_faq_arr, 'rbfw_faq_title');
        $rbfw_faq_img 		= array_column($rbfw_faq_arr, 'rbfw_faq_img');
        $rbfw_faq_content 	= array_column($rbfw_faq_arr, 'rbfw_faq_content');
        $count_faq_arr 		= count($rbfw_faq_arr);
        ?>
        <div id="rbfw_faq_accordion">
            <?php foreach ($rbfw_faq_arr as $faq) { ?>
                <div class="rbfw_faq_item">
                <?php if (!empty($faq['rbfw_faq_title'])): ?>
                    <h3 class="rbfw_faq_header"><?php echo esc_html($faq['rbfw_faq_title']); ?> <i class="fas fa-plus"></i></h3>
                <?php endif; ?>
                <div class="rbfw_faq_content_wrapper">
                    <div class="rbfw_faq_img">
                        <?php
                        if (!empty($faq['rbfw_faq_img'])):
                            $rbfw_img_id_arr = explode(",", $faq['rbfw_faq_img']);
                            foreach ($rbfw_img_id_arr as $attachment_id) {
                                $url = wp_get_attachment_url($attachment_id);
                                echo '<img src="' . esc_url($url) . '"/>';
                            }
                        endif;
                        ?>
                    </div>
                    <p class="rbfw_faq_desc">
                        <?php
                        if (!empty($faq['rbfw_faq_content'])):
                            echo esc_html($faq['rbfw_faq_content']);
                        endif;
                        ?>
                    </p>
                </div>
                </div>
            <?php } ?>
        </div>
        <script>
            jQuery(document).ready(function($){
                $('#rbfw_faq_accordion .rbfw_faq_content_wrapper').first().slideDown();
                 $('#rbfw_faq_accordion .rbfw_faq_header').first().find('i').removeClass('fa-plus').addClass('fa-minus');

                $('.rbfw_faq_header').click(function(e){
                    e.preventDefault();
                    $(this).next('.rbfw_faq_content_wrapper').slideToggle();
                    $(this).find('i').toggleClass('fa-plus fa-minus');
                });
            });
        </script>
        <?php
    }
}

// Post Share meta function
add_action('rbfw_product_meta','rbfw_post_share_meta');
function rbfw_post_share_meta($post_id){
    // Get current post URL
    $rbfwURL = urlencode(get_permalink());

    // Get current post title
    $rbfwTitle = htmlspecialchars(urlencode(html_entity_decode(get_the_title(), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8');
    // $rbfwTitle = str_replace( ' ', '%20', get_the_title());

    // Get Post Thumbnail for pinterest
    $rbfwThumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );

    $twitter_username='';

    // sharing URL
    $twitterURL = 'https://twitter.com/intent/tweet?text='.$rbfwTitle.'&amp;url='.$rbfwURL.'&amp;via='.$twitter_username;
    $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u='.$rbfwURL;
    $linkedInURL = 'https://www.linkedin.com/shareArticle?mini=true&url='.$rbfwURL.'&amp;title='.$rbfwTitle;
    $pinterestURL = 'https://www.pinterest.com/pin/create/button/?url='.$rbfwURL.'&amp;media='; if($rbfwThumbnail){ $pinterestURL.= $rbfwThumbnail[0]; } $pinterestURL.='&amp;description='.$rbfwTitle;?>
    <div class="rbfw-post-sharing">
        <a href="<?php echo esc_url($facebookURL);?>">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="<?php echo esc_url($twitterURL);?>">
            <i class="fab fa-twitter"></i>
        </a>
        <a href="<?php echo esc_url($pinterestURL);?>">
            <i class="fab fa-pinterest-p"></i>
        </a>
        <a href="<?php echo esc_url($linkedInURL);?>">
            <i class="fab fa-linkedin"></i>
        </a>
    </div>
    <?php
}

// Related products function
add_action('rbfw_related_products','rbfw_related_products');
function rbfw_related_products($post_id){

    if(empty($post_id)){
        return;
    }

    global $rbfw;
    $rbfw_related_post_arr = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize(get_post_meta( $post_id, 'rbfw_releted_rbfw', true )) : array();
    $hourly_rate_label = $rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly rate','booking-and-rental-manager-for-woocommerce'));
    $prices_start_at = $rbfw->get_option_trans('rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __('Prices start at','booking-and-rental-manager-for-woocommerce'));

    if(isset($rbfw_related_post_arr) && ! empty($rbfw_related_post_arr)){
        ?>
        <h3 class="rbfw-related-product-heading">
            <?php rbfw_string('rbfw_text_related_items',__('Related Items','booking-and-rental-manager-for-woocommerce')); ?>
        </h3>

        <div class="owl-carousel rbfw-related-product">
            <?php foreach ($rbfw_related_post_arr as $rbfw_related_post_id) {
                $rbfw_rent_type = get_post_meta( $rbfw_related_post_id, 'rbfw_item_type', true );
                $gallery_images = get_post_meta($rbfw_related_post_id,'rbfw_gallery_images',true);

                if(isset($gallery_images)){
                    $gallery_image = wp_get_attachment_url($gallery_images[0]);
                }
                else{
                    $gallery_image = RBFW_PLUGIN_URL.'/assets/images/no_image.png';
                }

                $thumb_url  = !empty(get_the_post_thumbnail_url( $rbfw_related_post_id, 'full' )) ? get_the_post_thumbnail_url( $rbfw_related_post_id, 'full' ) : $gallery_image;
                $title = get_the_title($rbfw_related_post_id);

                $hourly_rate_label = $rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly rate','booking-and-rental-manager-for-woocommerce'));
                $daily_rate_label = $rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings', __('Daily rate','booking-and-rental-manager-for-woocommerce'));
                $rbfw_enable_hourly_rate = get_post_meta( $rbfw_related_post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $rbfw_related_post_id, 'rbfw_enable_hourly_rate', true ) : 'no';

                if($rbfw_enable_hourly_rate == 'no'){
                    $the_price_label = $daily_rate_label;
                } else {
                    $the_price_label = $hourly_rate_label;
                }

                $prices_start_at = $rbfw->get_option_trans('rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __('Prices start at','booking-and-rental-manager-for-woocommerce'));
                $rbfw_rent_type = get_post_meta( $rbfw_related_post_id, 'rbfw_item_type', true );

                if($rbfw_enable_hourly_rate == 'yes'){

                    $price = get_post_meta($rbfw_related_post_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_hourly_rate', true) : 0;
                    $price_sun = get_post_meta($rbfw_related_post_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sun_hourly_rate', true) : 0;
                    $price_mon = get_post_meta($rbfw_related_post_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_mon_hourly_rate', true) : 0;
                    $price_tue = get_post_meta($rbfw_related_post_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_tue_hourly_rate', true) : 0;
                    $price_wed = get_post_meta($rbfw_related_post_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_wed_hourly_rate', true) : 0;
                    $price_thu = get_post_meta($rbfw_related_post_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_thu_hourly_rate', true) : 0;
                    $price_fri = get_post_meta($rbfw_related_post_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_fri_hourly_rate', true) : 0;
                    $price_sat = get_post_meta($rbfw_related_post_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sat_hourly_rate', true) : 0;

                } else {

                    $price = get_post_meta($rbfw_related_post_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_daily_rate', true) : 0;
                    $price_sun = get_post_meta($rbfw_related_post_id, 'rbfw_sun_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sun_daily_rate', true) : 0;
                    $price_mon = get_post_meta($rbfw_related_post_id, 'rbfw_mon_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_mon_daily_rate', true) : 0;
                    $price_tue = get_post_meta($rbfw_related_post_id, 'rbfw_tue_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_tue_daily_rate', true) : 0;
                    $price_wed = get_post_meta($rbfw_related_post_id, 'rbfw_wed_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_wed_daily_rate', true) : 0;
                    $price_thu = get_post_meta($rbfw_related_post_id, 'rbfw_thu_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_thu_daily_rate', true) : 0;
                    $price_fri = get_post_meta($rbfw_related_post_id, 'rbfw_fri_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_fri_daily_rate', true) : 0;
                    $price_sat = get_post_meta($rbfw_related_post_id, 'rbfw_sat_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sat_daily_rate', true) : 0;
                }

                $price = (float)$price;

                $enabled_sun = get_post_meta($rbfw_related_post_id, 'rbfw_enable_sun_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_sun_day', true) : 'yes';
                $enabled_mon = get_post_meta($rbfw_related_post_id, 'rbfw_enable_mon_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_mon_day', true) : 'yes';
                $enabled_tue = get_post_meta($rbfw_related_post_id, 'rbfw_enable_tue_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_tue_day', true) : 'yes';
                $enabled_wed = get_post_meta($rbfw_related_post_id, 'rbfw_enable_wed_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_wed_day', true) : 'yes';
                $enabled_thu = get_post_meta($rbfw_related_post_id, 'rbfw_enable_thu_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_thu_day', true) : 'yes';
                $enabled_fri = get_post_meta($rbfw_related_post_id, 'rbfw_enable_fri_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_fri_day', true) : 'yes';
                $enabled_sat = get_post_meta($rbfw_related_post_id, 'rbfw_enable_sat_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_sat_day', true) : 'yes';

                $current_day = date('D');

                if($current_day == 'Sun' && $enabled_sun == 'yes'){
                    $price = (float)$price_sun;
                }elseif($current_day == 'Mon' && $enabled_mon == 'yes'){
                    $price = (float)$price_mon;
                }elseif($current_day == 'Tue' && $enabled_tue == 'yes'){
                    $price = (float)$price_tue;
                }elseif($current_day == 'Wed' && $enabled_wed == 'yes'){
                    $price = (float)$price_wed;
                }elseif($current_day == 'Thu' && $enabled_thu == 'yes'){
                    $price = (float)$price_thu;
                }elseif($current_day == 'Fri' && $enabled_fri == 'yes'){
                    $price = (float)$price_fri;
                }elseif($current_day == 'Sat' && $enabled_sat == 'yes'){
                    $price = (float)$price_sat;
                }else{
                    $price = (float)$price;
                }

                $current_date = date('Y-m-d');
                $rbfw_sp_prices = get_post_meta( $rbfw_related_post_id, 'rbfw_seasonal_prices', true );
                if(!empty($rbfw_sp_prices)){
                    $sp_array = [];
                    $i = 0;
                    foreach ($rbfw_sp_prices as $value) {
                        $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
                        $rbfw_sp_end_date 	= $value['rbfw_sp_end_date'];
                        $rbfw_sp_price_h 	= $value['rbfw_sp_price_h'];
                        $rbfw_sp_price_d 	= $value['rbfw_sp_price_d'];
                        $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
                        $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
                        $sp_array[$i]['sp_daily_rate']  = $rbfw_sp_price_d;
                        $i++;
                    }

                    foreach ($sp_array as $sp_arr) {

                        if (in_array($current_date,$sp_arr['sp_dates'])){

                            if($rbfw_enable_hourly_rate == 'yes'){

                                $price = (float)$sp_arr['sp_hourly_rate'];

                            } else {

                                $price = (float)$sp_arr['sp_daily_rate'];
                            }
                        }
                    }
                }
                
                $permalink = get_the_permalink($rbfw_related_post_id);


                /* Resort Type */
                $rbfw_room_data = get_post_meta( $rbfw_related_post_id, 'rbfw_resort_room_data', true );
                if(!empty($rbfw_room_data) && $rbfw_rent_type == 'resort'):
                    $rbfw_daylong_rate = [];
                    $rbfw_daynight_rate = [];
                    foreach ($rbfw_room_data as $key => $value) {

                        if(!empty($value['rbfw_room_daylong_rate'])){
                            $rbfw_daylong_rate[] =  $value['rbfw_room_daylong_rate'];
                        }

                        if(!empty($value['rbfw_room_daynight_rate'])){
                            $rbfw_daynight_rate[] = $value['rbfw_room_daynight_rate'];
                        }

                    }
                    $merged_arr = array_merge($rbfw_daylong_rate,$rbfw_daynight_rate);

                    if(!empty($merged_arr)){
                        $smallest_price = min($merged_arr);
                        $smallest_price = (float)$smallest_price;
                    } else {
                        $smallest_price = 0;
                    }
                    $price = $smallest_price;
                endif;

                /* Single Day/Appointment Type */
                $rbfw_bike_car_sd_data = get_post_meta( $rbfw_related_post_id, 'rbfw_bike_car_sd_data', true );
                if(!empty($rbfw_bike_car_sd_data) && ($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment')):
                    $rbfw_price_arr = [];

                    foreach ($rbfw_bike_car_sd_data as $key => $value) {

                        if(!empty($value['price'])){
                            $rbfw_price_arr[] =  $value['price'];
                        }

                    }

                    if(!empty($rbfw_price_arr)){
                        $smallest_price = min($rbfw_price_arr);
                        $smallest_price = (float)$smallest_price;
                    } else {
                        $smallest_price = 0;
                    }
                    $price = $smallest_price;
                endif;
                ?>

                <div class="item">
                    <div class="rbfw-related-product-inner">
                        <div class="rbfw-related-product-thumb-wrap">
                            <a href="<?php echo esc_url($permalink); ?>">
                                <div class="rbfw-related-product-thumb">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php esc_attr_e('Featured Image','booking-and-rental-manager-for-woocommerce'); ?>">
                                </div>
                            </a>
                        </div>

                        <div class="rbfw-related-product-content-wrapper">
                            <div class="rbfw-related-product-content-inner">
                                <h3 class="rbfw-related-product-title-wrap">
                                    <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                                </h3>

                                <?php if($rbfw_rent_type != 'resort' && $rbfw_rent_type != 'bike_car_sd' && $rbfw_rent_type != 'appointment'): ?>
                                    <div class="rbfw-related-product-price-wrap"><?php echo esc_html($prices_start_at); ?>: <?php echo rbfw_mps_price($price); ?></div>
                                <?php endif; ?>

                                <?php if($rbfw_rent_type == 'resort' && !empty($rbfw_room_data)): ?>
                                    <div class="rbfw-related-product-price-wrap"><?php echo esc_html($prices_start_at); ?>: <?php echo rbfw_mps_price($price); ?></div>
                                <?php endif; ?>

                                <?php if(($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment') && !empty($rbfw_bike_car_sd_data)): ?>
                                    <div class="rbfw-related-product-price-wrap"><?php echo esc_html($prices_start_at); ?>: <?php echo rbfw_mps_price($price); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="rbfw-related-product-btn-wrap">
                                <a href="<?php echo esc_url($permalink); ?>" class="rbfw-related-product-btn">
                                    <?php rbfw_string('rbfw_text_read_more',__('Read More','booking-and-rental-manager-for-woocommerce')); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <script>
            jQuery(document).ready(function(){
                jQuery(".owl-carousel.rbfw-related-product").owlCarousel({
                    loop:true,
                    margin:0,
                    responsiveClass:true,
                    responsive:{
                        0:{
                            items:1,
                            nav:true
                        },
                        600:{
                            items:3,
                            nav:false
                        },
                        1000:{
                            items:3,
                            nav:true,
                            loop:true
                        }
                    }
                });
            });
        </script>
        <?php
    }
}

add_action('wp_footer','rbfw_footer_scripts');
function rbfw_footer_scripts(){
    global $rbfw;
    global $post;

    $post_id = !empty($post->ID) ? $post->ID : '';

    if(empty($post_id)){
        return;
    }

    $post_type = get_post_type($post_id);

    if($post_type == 'rbfw_item'){
        ?>
        <script>
            jQuery(document).ready(function(){

                // tab tooltip
                let highlighted_features 	= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_hightlighted_features', 'rbfw_basic_translation_settings', __('Highlighted Features','booking-and-rental-manager-for-woocommerce'))); ?>";
                let description 			= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_description', 'rbfw_basic_translation_settings', __('Description','booking-and-rental-manager-for-woocommerce'))); ?>";
                let faq 					= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_faq', 'rbfw_basic_translation_settings', __('Frequently Asked Questions','booking-and-rental-manager-for-woocommerce'))); ?>";
                let reviews 				= "<?php echo esc_html($rbfw->get_option_trans('rbfw_text_reviews', 'rbfw_basic_translation_settings', __('Reviews','booking-and-rental-manager-for-woocommerce'))); ?>";
                // tippy('.rbfw-features', {content: highlighted_features,theme: 'blue',placement: 'right'});
                // tippy('.rbfw-description', {content: description,theme: 'blue',placement: 'right'});
                // tippy('.rbfw-faq', {content: faq,theme: 'blue',placement: 'right'});
                // tippy('.rbfw-review', {content: reviews,theme: 'blue',placement: 'right'});
                // end tab tooltip
            });
        </script>
        <?php
    }
}

/***************************************************
 * Transfer Highlighed features to feature category
 ***************************************************/
add_action('wp_loaded','rbfw_highlighted_features_func');

function rbfw_highlighted_features_func(){

    $args = array(
        'post_type' => 'rbfw_item',
        'posts_per_page' => -1
    );

    $the_query = new WP_Query($args);
    if(!empty($the_query)){
        foreach ($the_query->posts as $result) {
            $post_id = $result->ID;
            $highlights_features = get_post_meta($post_id,'rbfw_highlights_texts',true);
            $rbfw_feature_category = get_post_meta($post_id,'rbfw_feature_category',true);

            if(!empty($highlights_features)){
                $the_array = [];
                $label = rbfw_string_return( 'rbfw_text_hightlighted_features', 'Highlighted Features' );
                $the_array[0]['cat_title'] = $label;
                $c = 0;
                foreach ($highlights_features as $features) {
                    $icon = $features['icon'];
                    $title = $features['title'];
                    $the_array[0]['cat_features'][$c]['icon'] = $icon;
                    $the_array[0]['cat_features'][$c]['title'] = $title;
                    $c++;
                }
                update_post_meta($post_id,'rbfw_feature_category',$the_array);
                delete_post_meta($post_id,'rbfw_highlights_texts');
            }
        }
    }
}

add_action('admin_footer','rbfw_footer_admin_scripts');
function rbfw_footer_admin_scripts(){

    $icon_library = new rbfw_icon_library();
    $icon_library_list = $icon_library->rbfw_fontawesome_icons();
    ?>
    <script>
        jQuery(document).ready(function(){

            jQuery('.rbfw_load_more_icons').click(function (e) {
                e.preventDefault();
                let data_loaded = parseInt(jQuery('#rbfw_features_icon_list_wrapper').attr('data-loaded'));
                var data = {
                    'action': 'rbfw_load_more_icons',
                    'data_loaded': data_loaded
                };

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'rbfw_load_more_icons',
                        'data_loaded': data_loaded
                    },
                    beforeSend: function() {
                        jQuery('.rbfw_load_more_icons').append('<span class="rbfw_load_more_icons_loader"><i class="fas fa-spinner fa-spin"></i></span>');
                    },
                    success: function (response) {
                        console.log('response',response);
                        jQuery('.rbfw_load_more_icons_loader').remove();
                        jQuery('.rbfw_features_icon_list_body').append(response);
                        data_loaded = data_loaded + 100;
                        jQuery('#rbfw_features_icon_list_wrapper').attr('data-loaded', data_loaded);

                        if(response == ''){
                            jQuery('.rbfw_load_more_icons').hide();
                        }

                        // Selected Feature Icon Action
                        jQuery(document).on('click', '#rbfw_features_icon_list_wrapper label',function(e){
                            e.stopImmediatePropagation();
                            let selected_label 		= jQuery(this);
                            let selected_val 		= jQuery('input', this).val();
                            let selected_data_key 	= jQuery("#rbfw_features_icon_list_wrapper").attr('data-key');
                            let selected_data_cat 	= jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat');

                            jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');

                            jQuery('.rbfw_feature_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_feature_icon_preview[data-key="'+selected_data_key+'"]').empty();
                            jQuery(selected_label).addClass('selected');
                            jQuery('.rbfw_feature_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_feature_icon[data-key="'+selected_data_key+'"]').val(selected_val);
                            jQuery('.rbfw_feature_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_feature_icon_preview[data-key="'+selected_data_key+'"]').append('<i class="'+selected_val+'"></i>');
                        });
                    },
                    error: function(response){
                        console.log(response);
                    }
                });
            });
        });
    </script>
    <div id="rbfw_features_icon_list_wrapper" class="mage_modal ggggg" data-loaded="100">
        <div class="rbfw_features_icon_list_header">
            <div class="rbfw_features_icon_list_header_group">
                <a href="#rbfw_features_icon_list_wrapper" rel="mage_modal:close" class="rbfw_feature_icon_list_close_button"><?php esc_html_e('Close','booking-and-rental-manager-for-woocommerce'); ?></a>
            </div>
            <div class="rbfw_features_icon_list_header_group">
                <input type="text" id="rbfw_features_search_icon" placeholder="<?php esc_attr_e('Search Icon...','booking-and-rental-manager-for-woocommerce'); ?>">
            </div>
        </div>
        <hr>
        <div class="rbfw_features_icon_list_body">
            <?php
            $i = 1;
            foreach ($icon_library_list as $key => $value) {
                if($i <= 100){
                    $input_id = str_replace(' ', '', $key);
                    ?>
                    <label for="<?php echo $input_id; ?>" data-id="<?php echo $value; ?>">
                        <input type="radio" name="rbfw_icon" id="<?php echo $input_id; ?>" value="<?php echo $key; ?>">
                        <i class="<?php echo $key; ?>"></i>
                    </label>
                    <?php
                }
                $i++;
            }
            ?>
        </div>
        <a class="ppof-button rbfw_load_more_icons"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e('Load More Icon','booking-and-rental-manager-for-woocommerce'); ?></a>
    </div>
    <style>
        #rbfw_features_icon_list_wrapper.mage_modal{
            display: none;
        }
        .rbfw_load_more_icons_loader{
            margin-left: 5px;
        }
    </style>
    <?php
}

add_action( 'wp_ajax_rbfw_load_more_icons', 'rbfw_load_more_icons_func' );

function rbfw_load_more_icons_func() {
    $data_loaded = $_POST['data_loaded'];
    $icon_library = new rbfw_icon_library();
    $icon_library_list = $icon_library->rbfw_fontawesome_icons();

    ob_start();
    $i = 0;
    $target = $data_loaded + 100;
    foreach ($icon_library_list as $key => $value) {
        if(($i > $data_loaded) && ($i <= $target)){
            $input_id = str_replace(' ', '', $key);
            ?>
            <label for="<?php echo $input_id; ?>" data-id="<?php echo $value; ?>">
                <input type="radio" name="rbfw_icon" id="<?php echo $input_id; ?>" value="<?php echo $key; ?>">
                <i class="<?php echo $key; ?>"></i>
            </label>
            <?php
        }
        $i++;
    }

    $content = ob_get_clean();
    echo $content;

    wp_die();
}

/*******************************************
 * Remove the template meta box from sidebar
 *******************************************/
add_action( 'do_meta_boxes', 'rbfw_remove_template_meta_box' );
function rbfw_remove_template_meta_box()
{
    $custom_post_type = 'rbfw_item';

    remove_meta_box('rbfw_list_thumbnail_meta_boxes', $custom_post_type, 'side' );
}

/*******************************************
 * Get Between Dates function
 *******************************************/
function rbfw_getBetweenDates($startDate, $endDate)
{

    $rangArray = [];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    for ($currentDate = $startDate; $currentDate <= $endDate;

         $currentDate += (86400)) {

        $date = date('Y-m-d', $currentDate);

        $rangArray[] = $date;

    }
    return $rangArray;
}

// Date Format Converter
function rbfw_date_format($date){
    if(empty($date)){
        return;
    }
    $date_to_string = new DateTime($date);
    $result = $date_to_string->format(get_option('date_format'));
    return $result;
}

// Remove element for rbfw_order post type
add_action( 'admin_head', 'rbfw_post_type_css');
function rbfw_post_type_css(){
    $current_queried_post_type = get_post_type( get_queried_object_id() );
    if ('rbfw_order' == $current_queried_post_type ) {
        echo '<style>#minor-publishing{display:none;}</style>';
        echo '<script>jQuery(document).ready(function(){ jQuery("#minor-publishing").remove(); });</script>';
    }
}

add_action( 'admin_footer', 'rbfw_meta_admin_script_func');
function rbfw_meta_admin_script_func(){
    global $post;
    $post_id = !empty($post->ID) ? $post->ID : '';

    if(empty($post_id)){
        return;
    }

    $rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : '';
    $rbfw_time_slot_switch = get_post_meta( $post_id, 'rbfw_time_slot_switch', true ) ? get_post_meta( $post_id, 'rbfw_time_slot_switch', true ) : 'off';

    if( ($rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment') && $rbfw_time_slot_switch == 'off' ){
        echo '<script>jQuery(document).ready(function(){ jQuery("tr[data-row=rdfw_available_time]").hide(); });</script>';
    } else {
        echo '<script>jQuery(document).ready(function(){ jQuery("tr[data-row=rdfw_available_time]").show(); });</script>';
    }

}

// Get rent type label by slug
function rbfw_get_type_label($slug){
    switch ($slug) {
        case 'bike_car_sd':
            return 'Bike/Car for single day';
            break;
        case 'bike_car_md':
            return 'Bike/Car for multiple day';
            break;
        case 'resort':
            return 'Resort';
            break;
        case 'equipment':
            return 'Equipment';
            break;
        case 'dress':
            return 'Dress';
            break;
        case 'appointment':
            return 'Appointment';
            break;
        case 'others':
            return 'Others';
            break;
        default:
            return;
    }
}

// Check WooCommerce Integration addon is installed
function rbfw_payment_systems(){

    $ps = array(
        'mps' => 'Mage Payment System'
    );

    return apply_filters('rbfw_payment_systems', $ps);
}

// get payment gateways
function rbfw_get_payment_gateways(){

    $pg = array(
        'offline' => 'Offline Payment',
    );

    return apply_filters('rbfw_payment_gateways', $pg);
}

// global settings payment system css
add_action( 'admin_head', 'rbfw_payment_systems_css');
function rbfw_payment_systems_css(){
    $current_payment_system = rbfw_get_option( 'rbfw_payment_system', 'rbfw_basic_payment_settings');
    $mps_tax_switch = rbfw_get_option( 'rbfw_mps_tax_switch', 'rbfw_basic_payment_settings');

    if ('wps' == $current_payment_system ) {
        echo '<style>tr.rbfw_mps_currency,tr.rbfw_mps_currency_position,tr.rbfw_mps_currency_decimal_seperator,tr.rbfw_mps_currency_thousand_seperator,tr.rbfw_mps_currency_decimal_number,tr.rbfw_mps_checkout_account,tr.rbfw_mps_payment_gateway,tr.rbfw_mps_payment_gateway_environment,tr.rbfw_mps_paypal_heading,tr.rbfw_mps_paypal_account_email,tr.rbfw_mps_paypal_api_username,tr.rbfw_mps_paypal_api_password,tr.rbfw_mps_paypal_api_signature,tr.rbfw_mps_paypal_ipn_handler,tr.rbfw_mps_stripe_heading,tr.rbfw_mps_stripe_publishable_key,tr.rbfw_mps_stripe_secret_key,tr.rbfw_mps_stripe_webhook,tr.rbfw_mps_paypal_client_id,tr.rbfw_mps_paypal_secret_key,tr.rbfw_mps_stripe_postal_field,tr.rbfw_mps_tax_switch,tr.rbfw_mps_tax_format{display:none;}</style>';
    } else {
        echo '<style>tr.rbfw_wps_add_to_cart_redirect{display:none;}</style>';
    }

    if($mps_tax_switch != 'on'){
        ?>
        <script>jQuery(document).ready(function(){
                jQuery(".mp_tab_details .mp_tab_item[data-tab-item=#rbfw_tax_settings_meta_boxes] .form-table").remove();
                jQuery(".mp_tab_details .mp_tab_item[data-tab-item=#rbfw_tax_settings_meta_boxes] .description").html("<div class='rbfw_alert_info'><i class='fa-solid fa-circle-info'></i> To enable tax settings, go to Settings->Payment Settings and enable the tax switch.</div>");
            });
        </script>
        <?php
    }
}

// get page list array
function rbfw_get_pages_arr() {
    $pages = get_pages();
    $arr   = array(
        '' => __( 'Please Select a Page', 'booking-and-rental-manager-for-woocommerce' )
    );
    foreach ( $pages as $page ) {
        $arr[ $page->ID ] = $page->post_title;
    }

    return $arr;
}

add_filter( 'rbfw_settings_field', 'rbfw_payment_settings_fields', 10 );
function rbfw_payment_settings_fields($settings_fields){
    $settings_fields['rbfw_basic_payment_settings'] = array(

        array(
            'name' => 'rbfw_payment_system',
            'label' => __( 'Payment System', 'booking-and-rental-manager-for-woocommerce' ),
            'desc' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
            'class' => 'rbfw_payment_system',
            'type' => 'select',
            'default' => 'wps',
            'options' => rbfw_payment_systems(),
        ),
        array(
            'name'    => 'rbfw_mps_currency',
            'label'   => __( 'Currency', 'booking-and-rental-manager-for-woocommerce' ),
            'desc'    => __( 'Please choose the currency if mage payment system is enabled.', 'booking-and-rental-manager-for-woocommerce' ),
            'type' => 'select',
            'default' => 'USD',
            'options' => rbfw_mps_currency_list(),
        ),
        array(
            'name' => 'rbfw_mps_currency_position',
            'label' => __( 'Currency position', 'booking-and-rental-manager-for-woocommerce' ),
            'desc' => __( 'This controls the position of the currency symbol if mage payment system is enabled.', 'booking-and-rental-manager-for-woocommerce' ),
            'type' => 'select',
            'default' => 'left',
            'options' => array(
                'left' => 'Left',
                'right'  => 'Right',
                'left_space'  => 'Left with space',
                'right_space'  => 'Right with space'
            ),
        ),
        array(
            'name'    => 'rbfw_mps_currency_thousand_seperator',
            'label'   => __( 'Thousand separator', 'booking-and-rental-manager-for-woocommerce' ),
            'desc'    => __( 'This sets the thousand seperator of displayed prices.', 'booking-and-rental-manager-for-woocommerce' ),
            'type'    => 'text',
            'default' => ',',
            'maxlength' => '1',
            'size' => '1',
        ),
        array(
            'name'    => 'rbfw_mps_currency_decimal_seperator',
            'label'   => __( 'Decimal separator', 'booking-and-rental-manager-for-woocommerce' ),
            'desc'    => __( 'This sets the decimal seperator of displayed prices.', 'booking-and-rental-manager-for-woocommerce' ),
            'type'    => 'text',
            'default' => '.',
            'maxlength' => '1',
            'size' => '1',
        ),
        array(
            'name'    => 'rbfw_mps_currency_decimal_number',
            'label'   => __( 'Number of decimals', 'booking-and-rental-manager-for-woocommerce' ),
            'desc'    => __( 'This sets the number of decimal points shown in displayed prices. It will work if mage payment system is enabled.', 'booking-and-rental-manager-for-woocommerce' ),
            'type'    => 'number',
            'default' => '2',
        ),
        array(
            'name' => 'rbfw_mps_tax_switch',
            'label' => __( 'Enable taxes', 'booking-and-rental-manager-for-woocommerce' ),
            'desc' => __( ' Enable tax rates and calculations. Rates will be configurable and taxes will be calculated during checkout.', 'booking-and-rental-manager-for-woocommerce' ),
            'type' => 'checkbox',
            'default' => 'off',
        ),
        array(
            'name'      => 'rbfw_mps_tax_format',
            'label'   => __( 'Display prices during checkout', 'booking-and-rental-manager-for-woocommerce' ),
            'desc' => __( 'Please select the tax format.', 'booking-and-rental-manager-for-woocommerce' ),
            'type'    => 'select',
            'default' => 'excluding_tax',
            'options'    => array(
                'excluding_tax' => __( 'Excluding tax', 'booking-and-rental-manager-for-woocommerce' ),
                'including_tax' => __( 'Including tax', 'booking-and-rental-manager-for-woocommerce' ),

            ),
        ),
        array(
            'name' => 'rbfw_mps_checkout_account',
            'label' => __( 'Account creation', 'booking-and-rental-manager-for-woocommerce' ),
            'desc' => __( 'Allow customers to create an account during checkout.', 'booking-and-rental-manager-for-woocommerce' ),
            'type' => 'checkbox',
            'default' => 'on',
        ),
        array(
            'name' => 'rbfw_mps_payment_gateway',
            'label' => __( 'Payment Gateway', 'booking-and-rental-manager-for-woocommerce' ),
            'desc' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
            'type' => 'multicheck',
            'default' => 'offline',
            'options' => rbfw_get_payment_gateways()
        ),
        array(
            'name' => 'rbfw_wps_add_to_cart_redirect',
            'label' => __( 'Added to cart redirect to', 'booking-and-rental-manager-for-woocommerce' ),
            'desc' => __( '', 'booking-and-rental-manager-for-woocommerce' ),
            'type' => 'select',
            'default' => 'checkout',
            'options' => array(
                'checkout' => 'Checkout',
                'cart'  => 'Cart',
            ),
        ),
    );

    return apply_filters('rbfw_payment_settings_fields', $settings_fields);
}

// Update Settings On Register the Plugin
function rbfw_update_settings(){
    $payment_settings = maybe_unserialize('a:6:{s:19:"rbfw_payment_system";s:3:"wps";s:17:"rbfw_mps_currency";s:3:"USD";s:26:"rbfw_mps_currency_position";s:4:"left";s:32:"rbfw_mps_currency_decimal_number";s:1:"2";s:25:"rbfw_mps_checkout_account";s:2:"on";s:24:"rbfw_mps_payment_gateway";a:1:{s:7:"offline";s:7:"offline";}}');

    if (get_option('rbfw_basic_payment_settings') === false) {

        update_option('rbfw_basic_payment_settings', $payment_settings);

    }

    $pdf_settings = maybe_unserialize('a:9:{s:13:"rbfw_send_pdf";s:3:"yes";s:13:"rbfw_pdf_logo";s:0:"";s:11:"rbfw_pdf_bg";s:0:"";s:16:"rbfw_pdf_address";s:0:"";s:14:"rbfw_pdf_phone";s:0:"";s:17:"rbfw_pdf_tc_title";s:0:"";s:16:"rbfw_pdf_tc_text";s:0:"";s:17:"rbfw_pdf_bg_color";s:0:"";s:19:"rbfw_pdf_text_color";s:0:"";}');

    if (get_option('rbfw_basic_pdf_settings') === false) {

        update_option('rbfw_basic_pdf_settings', $pdf_settings);

    }
}

function rbfw_page_create()
{
    $page_obj = rbfw_exist_page_by_slug('rent-list');

    if($page_obj === false){
        $args = array(
            'post_title'    => 'Rent List',
            'post_content'  => "[rent-list style='list']",
            'post_status'   => 'publish',
            'post_type'     => 'page'
        );
        wp_insert_post( $args );
    }

    $page_obj = rbfw_exist_page_by_slug('rent-grid');

    if($page_obj === false){
        $args = array(
            'post_title'    => 'Rent Grid',
            'post_content'  => "[rent-list style='grid']",
            'post_status'   => 'publish',
            'post_type'     => 'page'
        );
        wp_insert_post( $args );
    }


    $page_slug = 'search-item-list';
    // Check if the page already exists
    $existing_page = get_page_by_path($page_slug);
    if (!$existing_page) {
        // Page doesn't exist, so create it
        $page_data = array(
            'post_title'    => 'Search Item List',
            'post_content'  => '[rbfw_search] [search-result]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => $page_slug,
        );

        // Insert the page into the database
        wp_insert_post($page_data);
    }


}


// Check pro plugin active
function rbfw_check_pro_active(){
    if (is_plugin_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php' ) ) {
        return true;
    }
    else{
        return false;
    }
}


// Hide wc hidden products
add_action('admin_head', 'rbfw_hide_date_from_order_page');
if (!function_exists('rbfw_hide_date_from_order_page')) {
    function rbfw_hide_date_from_order_page() {
        $product_id = [];
        $args = array(
            'post_type' => 'rbfw_item',
            'posts_per_page' => -1
        );
        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $product_id[] = get_post_meta($post_id, 'link_wc_product', true) ? '.woocommerce-page .post-' . get_post_meta($post_id, 'link_wc_product', true) . '.type-product' : '';
        }
        $product_id = array_filter($product_id);
        $parr = implode(', ', $product_id);
        echo '<style> ' . esc_html($parr) . '{display:none!important}' . ' </style>';
    }
}

add_action( 'pre_get_posts', 'rbfw_search_query_exlude_hidden_wc_fix' );
function rbfw_search_query_exlude_hidden_wc_fix( $query ) {
    if ($query->is_search && !is_admin() ) {
        $query -> set( 'tax_query', array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'exclude-from-search',
                'operator' => 'NOT IN',
            )
        ));
    }
    return $query;
}

/*****************************
 * Create Inventory Meta
 *****************************/


/******************************************
 * Inventory Remove: WP Trash Post
 *****************************************/
add_action( 'wp_trash_post', 'rbfw_trash_order' );
add_action( 'untrashed_post', 'wp_kama_untrashed_post_action', 10, 2 );

function rbfw_trash_order( $order_id = '' ) {

    $order = wc_get_order( $order_id );

    if($order){
        $order_status = $order->get_status();
        foreach ( $order->get_items() as $item_id => $item_values ) {
            $rbfw_id =  wc_get_order_item_meta( $item_id, '_rbfw_id', true );
            rbfw_update_inventory_extra( $rbfw_id, $order_id,'cancelled');
        }
        // Verify if is trashing multiple posts
        if ( isset( $_GET['post'] ) && is_array( $_GET['post'] ) ) {
            foreach ( $_GET['post'] as $post_id ) {
                rbfw_update_inventory( $post_id, 'cancelled' );
            }
        } else {
            rbfw_update_inventory( $order_id, 'cancelled' );
        }
    }

}

function wp_kama_untrashed_post_action( $order_id ,$previous_status ) {
    $order = wc_get_order( $order_id );

    if(!empty($order)){
        $order_status = str_replace("wc-","",$previous_status);
        foreach ( $order->get_items() as $item_id => $item_values ) {
            $rbfw_id =  wc_get_order_item_meta( $item_id, '_rbfw_id', true );
            rbfw_update_inventory_extra( $rbfw_id, $order_id,$order_status);
        }
    }

}





function rbfw_update_inventory_extra($rbfw_id, $order_id,$order_status){
    global $rbfw;
    $rbfw_payment_system = $rbfw->get_option_trans('rbfw_payment_system', 'rbfw_basic_payment_settings','mps');

    if($rbfw_payment_system == 'wps'){
        $inventory = get_post_meta($rbfw_id,'rbfw_inventory', true);
        if (!empty($inventory) && array_key_exists($order_id, $inventory)) {
            $inventory[$order_id]['rbfw_order_status'] = $order_status;
            update_post_meta($rbfw_id, 'rbfw_inventory', $inventory);
        }
    } else {
        $inventory = get_post_meta($rbfw_id,'rbfw_inventory', true);
        if (!empty($inventory) && array_key_exists($order_id, $inventory)){
            $inventory[$order_id]['rbfw_order_status'] = $order_status;
            update_post_meta($rbfw_id, 'rbfw_inventory', $inventory);
        }
    }
}


/******************************************
 * Single Day Type: Get Available Quantity
 *****************************************/
function rbfw_get_bike_car_sd_available_qty($post_id, $selected_date, $type, $selected_time = null){

    if (empty($post_id) || empty($selected_date) || empty($type)) {
        return;
    }

    $selected_date = date('d-m-Y', strtotime($selected_date));
    $total_qty = 0;
    $type_stock = 0;
    $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);
    $rbfw_bike_car_sd_data = get_post_meta($post_id, 'rbfw_bike_car_sd_data', true);

    $rbfw_rent_type = get_post_meta($post_id, 'rbfw_item_type', true);
    $appointment_max_qty_per_session = get_post_meta($post_id, 'rbfw_sd_appointment_max_qty_per_session', true);

    if (!empty($rbfw_inventory)) {

        foreach ($rbfw_inventory as $key => $inventory) {

            $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];
            $rbfw_type_info = !empty($inventory['rbfw_type_info']) ? $inventory['rbfw_type_info'] : [];
            $rbfw_start_time = !empty($inventory['rbfw_start_time']) ? $inventory['rbfw_start_time'] : '';

            if($rbfw_rent_type == 'appointment'){

                if ( in_array($selected_date, $booked_dates) && ($selected_time == $rbfw_start_time) && ($inventory['rbfw_order_status'] == 'completed' || $inventory['rbfw_order_status'] == 'processing' || $inventory['rbfw_order_status'] == 'picked') ) {

                    foreach ($rbfw_type_info as $type_name => $type_qty) {

                        if ($type_name == $type) {
                            $total_qty += $type_qty;
                        }
                    }
                }

            } else {

                if ( in_array($selected_date, $booked_dates) && ($inventory['rbfw_order_status'] == 'completed' || $inventory['rbfw_order_status'] == 'processing' || $inventory['rbfw_order_status'] == 'picked')) {

                    foreach ($rbfw_type_info as $type_name => $type_qty) {

                        if ($type_name == $type) {
                            $total_qty += $type_qty;
                        }
                    }
                }
            }
        }
    }

    if (!empty($rbfw_bike_car_sd_data)) {

        foreach ($rbfw_bike_car_sd_data as $key => $bike_car_sd_data) {

            if($bike_car_sd_data['rent_type'] == $type){

                if($rbfw_rent_type == 'appointment'){

                    $type_stock = $appointment_max_qty_per_session;

                } else {

                    $type_stock += !empty($bike_car_sd_data['qty']) ? $bike_car_sd_data['qty'] : 0;
                }
            }
        }
    }

    $remaining_stock = $type_stock - $total_qty;
    $remaining_stock = max(0, $remaining_stock);

    return $remaining_stock;
}

/******************************************
 * Extra Service: Get Available Quantity
 *****************************************/
function rbfw_get_bike_car_sd_es_available_qty($post_id, $selected_date, $name){

    if (empty($post_id) || empty($selected_date) || empty($name)) {
        return;
    }

    $selected_date = date('d-m-Y', strtotime($selected_date));
    $total_qty = 0;
    $service_stock = 0;

    $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);
    $rbfw_extra_service_data = get_post_meta($post_id, 'rbfw_extra_service_data', true);

    if (!empty($rbfw_inventory)) {

        foreach ($rbfw_inventory as $key => $inventory) {

            $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];
            $rbfw_service_info = !empty($inventory['rbfw_service_info']) ? $inventory['rbfw_service_info'] : [];

            if ( in_array($selected_date, $booked_dates) ) {

                foreach ($rbfw_service_info as $service_name => $service_qty) {

                    if ($service_name == $name) {

                        $total_qty += $service_qty;
                    }
                }
            }
        }
    }

    if (!empty($rbfw_extra_service_data)) {

        foreach ($rbfw_extra_service_data as $key => $extra_service_data) {

            if($extra_service_data['service_name'] == $name){

                $service_stock += !empty($extra_service_data['service_qty']) ? $extra_service_data['service_qty'] : 0;
            }
        }
    }

    $remaining_stock = $service_stock - $total_qty;
    $remaining_stock = max(0, $remaining_stock);

    return $remaining_stock;
}

/****************************************************
 * Resort/Multiple Rent:
 * Get Types Available Quantity
 ****************************************************/








/****************************************************
 * Resort/Multiple Rent:
 * Get Extra Service Available Quantity
 ****************************************************/
function rbfw_get_multiple_date_es_available_qty($post_id, $start_date, $end_date, $service){

    if (empty($post_id) || empty($start_date) || empty($end_date) || empty($service)) {
        return;
    }

    $service_stock = 0;
    $rbfw_inventory = get_post_meta($post_id, 'rbfw_inventory', true);

    // Start: Get Date Range
    $date_range = [];
    $start_date = strtotime($start_date);
    $end_date = strtotime($end_date);

    for ($currentDate = $start_date; $currentDate <= $end_date;

         $currentDate += (86400)) {

        $date = date('d-m-Y', $currentDate);

        $date_range[] = $date;

    }
    // End: Get Date Range

    // Loop For Extra Services
    $rbfw_extra_service_data = get_post_meta($post_id, 'rbfw_extra_service_data', true);

    if (!empty($rbfw_extra_service_data)) {

        foreach ($rbfw_extra_service_data as $key => $extra_service_data) {

            if($extra_service_data['service_name'] == $service){

                $service_stock += !empty($extra_service_data['service_qty']) ? $extra_service_data['service_qty'] : 0;
            }
        }
    }
    // End Loop For Extra Services

    if (!empty($rbfw_inventory)) {

        $total_qty = 0;
        $qty_array = [];

        foreach ($date_range as $key => $range_date) {

            foreach ($rbfw_inventory as $key => $inventory) {

                $booked_dates = !empty($inventory['booked_dates']) ? $inventory['booked_dates'] : [];
                $rbfw_service_info = !empty($inventory['rbfw_service_info']) ? $inventory['rbfw_service_info'] : [];

                if ( in_array($range_date, $booked_dates) && ($inventory['rbfw_order_status'] == 'completed' || $inventory['rbfw_order_status'] == 'processing' || $inventory['rbfw_order_status'] == 'picked') ) {

                    foreach ($rbfw_service_info as $service_name => $service_qty) {

                        if ($service_name == $service) {

                            $total_qty += $service_qty;
                        }
                    }
                }
            }
            $remaining_stock = $service_stock - $total_qty;
            $remaining_stock = max(0, $remaining_stock);
            $qty_array[] = $remaining_stock;
            $total_qty = 0;
        }
    }

    if (empty($qty_array)) {

        $remaining_stock = $service_stock;

    } else {

        $remaining_stock = min($qty_array);
    }

    return $remaining_stock;
}



/****************************************************
 * Multiple Rent:
 * Get Variation Total Stock
 ****************************************************/
function rbfw_get_variations_stock($post_id){

    $variation_stock = 0;

    // Loop For Extra variations
    $rbfw_variations_data = get_post_meta($post_id, 'rbfw_variations_data', true);


    $count = 1;

    if (!empty($rbfw_variations_data)) {

        $count = count($rbfw_variations_data);

        foreach ($rbfw_variations_data as $key => $data_arr_one) {

            foreach ($data_arr_one['value'] as $data_arr_two) {

                $variation_stock += (int)$data_arr_two['quantity'];

            }
        }
    }
    // End Loop For Extra variations

    $variation_stock = round($variation_stock / $count);

    return $variation_stock;

}



/****************************************************
 * Add to cart redirect:
 ****************************************************/
add_filter('woocommerce_add_to_cart_redirect', 'rbfw_add_to_cart_redirect');
function rbfw_add_to_cart_redirect() {
    $add_to_cart_redirect = rbfw_get_option( 'rbfw_wps_add_to_cart_redirect', 'rbfw_basic_payment_settings', 'checkout');

    if ($add_to_cart_redirect == 'checkout') {
        global $woocommerce;
        if ( class_exists( 'WooCommerce' ) ) {
            $rbfw_redirect_checkout = wc_get_checkout_url();
            return $rbfw_redirect_checkout;
        }
    }
}

add_filter('wc_add_to_cart_message_html', 'rbfw_remove_add_to_cart_message');
function rbfw_remove_add_to_cart_message($message){
    $add_to_cart_redirect = rbfw_get_option( 'rbfw_wps_add_to_cart_redirect', 'rbfw_basic_payment_settings', 'checkout');

    if ($add_to_cart_redirect == 'checkout') {

        return '';

    } else {

        return $message;
    }
}

/****************************************************
 * Available Time Slots:
 ****************************************************/
function rbfw_get_available_time_slots(){

    $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
    asort($rbfw_time_slots);

    return $rbfw_time_slots;
}

/****************************************************
 * Import Time Slots if option empty:
 ****************************************************/
add_action('admin_init', 'rbfw_import_dummy_time_slots');
function rbfw_import_dummy_time_slots(){

    $import_time_slot_array = array(

        '12:00 AM' => __( '12:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '12:30 AM' => __( '12:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '01:00 AM'  => __( '1:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '01:30 AM'  => __( '1:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '02:00 AM'  => __( '2:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '02:30 AM'  => __( '2:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '03:00 AM'  => __( '3:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '03:30 AM'  => __( '3:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '04:00 AM'  => __( '4:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '04:30 AM'  => __( '4:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '05:00 AM'  => __( '5:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '05:30 AM'  => __( '5:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '06:00 AM'  => __( '6:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '06:30 AM'  => __( '6:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '00:00 AM'  => __( '7:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '07:30 AM'  => __( '7:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '08:00 AM'  => __( '8:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '08:30 AM'  => __( '8:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '09:00 AM'  => __( '9:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '09:30 AM'  => __( '9:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '10:00 AM' => __( '10:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '10:30 AM' => __( '10:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '11:00 AM' => __( '11:00 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '11:30 AM' => __( '11:30 AM', 'booking-and-rental-manager-for-woocommerce' ),
        '12:00 PM' => __( '12:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '12:30 PM' => __( '12:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '01:00 PM'  => __( '1:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '01:30 PM'  => __( '1:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '02:00 PM'  => __( '2:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '02:30 PM'  => __( '2:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '03:00 PM'  => __( '3:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '03:30 PM'  => __( '3:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '04:00 PM'  => __( '4:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '04:30 PM'  => __( '4:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '05:00 PM'  => __( '5:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '05:30 PM'  => __( '5:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '06:00 PM'  => __( '6:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '06:30 PM'  => __( '6:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '07:00 PM'  => __( '7:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '07:30 PM'  => __( '7:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '08:00 PM'  => __( '8:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '08:30 PM'  => __( '8:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '09:00 PM'  => __( '9:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '09:30 PM'  => __( '9:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '10:00 PM' => __( '10:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '10:30 PM' => __( '10:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '11:00 PM' => __( '11:00 PM', 'booking-and-rental-manager-for-woocommerce' ),
        '11:30 PM' => __( '11:30 PM', 'booking-and-rental-manager-for-woocommerce' ),
    );

    if(get_option('rbfw_time_slots') === false){

        update_option('rbfw_time_slots', $import_time_slot_array);
    }
}

/****************************************************
 * Check Min-Max Booking Day Plugin Active
 ****************************************************/
function rbfw_check_min_max_booking_day_active(){
    if (is_plugin_active( 'booking-and-rental-manager-min-max-booking-day/rent-min-max-booking-day.php' ) ) {
        return true;
    }
    else{
        return false;
    }
}

/****************************************************
 * Check Discount Over Days Plugin Active
 ****************************************************/
function rbfw_check_discount_over_days_plugin_active(){
    if (is_plugin_active( 'booking-and-rental-manager-discount-over-x-days/rent-discount-over-x-days.php' ) ) {
        return true;
    }
    else{
        return false;
    }
}

/****************************************************
 * Get hourly/daily price for Bike/Car Multiple Day
 ****************************************************/
function rbfw_get_bike_car_md_hourly_daily_price($rbfw_id, $price_type){
    if(empty($rbfw_id) || empty($price_type)){
        return;
    }
    $daily_rate = get_post_meta($rbfw_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_daily_rate', true) : 0;
    $hourly_rate = get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_hourly_rate', true) : 0;

    // sunday rate
    $hourly_rate_sun = get_post_meta($rbfw_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sun_hourly_rate', true) : 0;
    $daily_rate_sun = get_post_meta($rbfw_id, 'rbfw_sun_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sun_daily_rate', true) : 0;
    $enabled_sun = get_post_meta($rbfw_id, 'rbfw_enable_sun_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_sun_day', true) : 'yes';

    // monday rate
    $hourly_rate_mon = get_post_meta($rbfw_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_mon_hourly_rate', true) : 0;
    $daily_rate_mon = get_post_meta($rbfw_id, 'rbfw_mon_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_mon_daily_rate', true) : 0;
    $enabled_mon = get_post_meta($rbfw_id, 'rbfw_enable_mon_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_mon_day', true) : 'yes';

    // tuesday rate
    $hourly_rate_tue = get_post_meta($rbfw_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_tue_hourly_rate', true) : 0;
    $daily_rate_tue = get_post_meta($rbfw_id, 'rbfw_tue_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_tue_daily_rate', true) : 0;
    $enabled_tue = get_post_meta($rbfw_id, 'rbfw_enable_tue_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_tue_day', true) : 'yes';

    // wednesday rate
    $hourly_rate_wed = get_post_meta($rbfw_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_wed_hourly_rate', true) : 0;
    $daily_rate_wed = get_post_meta($rbfw_id, 'rbfw_wed_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_wed_daily_rate', true) : 0;
    $enabled_wed = get_post_meta($rbfw_id, 'rbfw_enable_wed_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_wed_day', true) : 'yes';

    // thursday rate
    $hourly_rate_thu = get_post_meta($rbfw_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_thu_hourly_rate', true) : 0;
    $daily_rate_thu = get_post_meta($rbfw_id, 'rbfw_thu_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_thu_daily_rate', true) : 0;
    $enabled_thu = get_post_meta($rbfw_id, 'rbfw_enable_thu_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_thu_day', true) : 'yes';

    // friday rate
    $hourly_rate_fri = get_post_meta($rbfw_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_fri_hourly_rate', true) : 0;
    $daily_rate_fri = get_post_meta($rbfw_id, 'rbfw_fri_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_fri_daily_rate', true) : 0;
    $enabled_fri = get_post_meta($rbfw_id, 'rbfw_enable_fri_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_fri_day', true) : 'yes';

    // saturday rate
    $hourly_rate_sat = get_post_meta($rbfw_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sat_hourly_rate', true) : 0;
    $daily_rate_sat = get_post_meta($rbfw_id, 'rbfw_sat_daily_rate', true) ? get_post_meta($rbfw_id, 'rbfw_sat_daily_rate', true) : 0;
    $enabled_sat = get_post_meta($rbfw_id, 'rbfw_enable_sat_day', true) ? get_post_meta($rbfw_id, 'rbfw_enable_sat_day', true) : 'yes';

    $current_day = date('D');

    if($current_day == 'Sun' && $enabled_sun == 'yes'){
        $hourly_rate = $hourly_rate_sun;
        $daily_rate = $daily_rate_sun;
    }elseif($current_day == 'Mon' && $enabled_mon == 'yes'){
        $hourly_rate = $hourly_rate_mon;
        $daily_rate = $daily_rate_mon;
    }elseif($current_day == 'Tue' && $enabled_tue == 'yes'){
        $hourly_rate = $hourly_rate_tue;
        $daily_rate = $daily_rate_tue;
    }elseif($current_day == 'Wed' && $enabled_wed == 'yes'){
        $hourly_rate = $hourly_rate_wed;
        $daily_rate = $daily_rate_wed;
    }elseif($current_day == 'Thu' && $enabled_thu == 'yes'){
        $hourly_rate = $hourly_rate_thu;
        $daily_rate = $daily_rate_thu;
    }elseif($current_day == 'Fri' && $enabled_fri == 'yes'){
        $hourly_rate = $hourly_rate_fri;
        $daily_rate = $daily_rate_fri;
    }elseif($current_day == 'Sat' && $enabled_sat == 'yes'){
        $hourly_rate = $hourly_rate_sat;
        $daily_rate = $daily_rate_sat;
    }else{
        $hourly_rate = $hourly_rate;
        $daily_rate = $daily_rate;
    }

    $current_date = date('Y-m-d');
    $rbfw_sp_prices = get_post_meta( $rbfw_id, 'rbfw_seasonal_prices', true );
    if(!empty($rbfw_sp_prices)){
        $sp_array = [];
        $i = 0;
        foreach ($rbfw_sp_prices as $value) {
            $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
            $rbfw_sp_end_date 	= $value['rbfw_sp_end_date'];
            $rbfw_sp_price_h 	= $value['rbfw_sp_price_h'];
            $rbfw_sp_price_d 	= $value['rbfw_sp_price_d'];
            $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
            $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
            $sp_array[$i]['sp_daily_rate']  = $rbfw_sp_price_d;
            $i++;
        }

        foreach ($sp_array as $sp_arr) {
            if (in_array($current_date,$sp_arr['sp_dates'])){
                $hourly_rate = $sp_arr['sp_hourly_rate'];
                $daily_rate  = $sp_arr['sp_daily_rate'];
            }
        }
    }

    if($price_type == 'hourly'){

        return $hourly_rate;
    }

    if($price_type == 'daily'){

        return $daily_rate;
    }
}

// Related products function
add_action('rbfw_related_products_style_two','rbfw_related_products_style_two');
function rbfw_related_products_style_two($post_id){

    if(empty($post_id)){
        return;
    }

    global $rbfw;
    $rbfw_related_post_arr = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize(get_post_meta( $post_id, 'rbfw_releted_rbfw', true )) : array();
    $hourly_rate_label = $rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly rate','booking-and-rental-manager-for-woocommerce'));
    $prices_start_at = $rbfw->get_option_trans('rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __('Prices start at','booking-and-rental-manager-for-woocommerce'));

    if(!empty($rbfw_related_post_arr)){

        echo '<div class="owl-carousel owl-theme t_carousel">';

        foreach ($rbfw_related_post_arr as $rbfw_related_post_id) {

            $rbfw_rent_type = get_post_meta( $rbfw_related_post_id, 'rbfw_item_type', true );

            $gallery_images = get_post_meta($rbfw_related_post_id,'rbfw_gallery_images',true);

            if(isset($gallery_images)){
                $gallery_image = wp_get_attachment_url($gallery_images[0]);
            }
            else{
                $gallery_image = RBFW_PLUGIN_URL.'/assets/images/no_image.png';
            }

            $thumb_url  = !empty(get_the_post_thumbnail_url( $rbfw_related_post_id, 'full' )) ? get_the_post_thumbnail_url( $rbfw_related_post_id, 'full' ) : $gallery_image;
            $title = get_the_title($rbfw_related_post_id);

            $price = get_post_meta($rbfw_related_post_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_hourly_rate', true) : 0;
            $price = (float)$price;
            // sunday rate
            $price_sun = get_post_meta($rbfw_related_post_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sun_hourly_rate', true) : 0;
            $enabled_sun = get_post_meta($rbfw_related_post_id, 'rbfw_enable_sun_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_sun_day', true) : 'yes';

            // monday rate
            $price_mon = get_post_meta($rbfw_related_post_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_mon_hourly_rate', true) : 0;
            $enabled_mon = get_post_meta($rbfw_related_post_id, 'rbfw_enable_mon_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_mon_day', true) : 'yes';

            // tuesday rate
            $price_tue = get_post_meta($rbfw_related_post_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_tue_hourly_rate', true) : 0;
            $enabled_tue = get_post_meta($rbfw_related_post_id, 'rbfw_enable_tue_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_tue_day', true) : 'yes';

            // wednesday rate
            $price_wed = get_post_meta($rbfw_related_post_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_wed_hourly_rate', true) : 0;
            $enabled_wed = get_post_meta($rbfw_related_post_id, 'rbfw_enable_wed_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_wed_day', true) : 'yes';

            // thursday rate
            $price_thu = get_post_meta($rbfw_related_post_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_thu_hourly_rate', true) : 0;
            $enabled_thu = get_post_meta($rbfw_related_post_id, 'rbfw_enable_thu_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_thu_day', true) : 'yes';

            // friday rate
            $price_fri = get_post_meta($rbfw_related_post_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_fri_hourly_rate', true) : 0;
            $enabled_fri = get_post_meta($rbfw_related_post_id, 'rbfw_enable_fri_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_fri_day', true) : 'yes';

            // saturday rate
            $price_sat = get_post_meta($rbfw_related_post_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sat_hourly_rate', true) : 0;
            $enabled_sat = get_post_meta($rbfw_related_post_id, 'rbfw_enable_sat_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_sat_day', true) : 'yes';

            $current_day = date('D');

            if($current_day == 'Sun' && $enabled_sun == 'yes'){
                $price = (float)$price_sun;
            }elseif($current_day == 'Mon' && $enabled_mon == 'yes'){
                $price = (float)$price_mon;
            }elseif($current_day == 'Tue' && $enabled_tue == 'yes'){
                $price = (float)$price_tue;
            }elseif($current_day == 'Wed' && $enabled_wed == 'yes'){
                $price = (float)$price_wed;
            }elseif($current_day == 'Thu' && $enabled_thu == 'yes'){
                $price = (float)$price_thu;
            }elseif($current_day == 'Fri' && $enabled_fri == 'yes'){
                $price = (float)$price_fri;
            }elseif($current_day == 'Sat' && $enabled_sat == 'yes'){
                $price = (float)$price_sat;
            }else{
                $price = (float)$price;
            }

            $current_date = date('Y-m-d');
            $rbfw_sp_prices = get_post_meta( $rbfw_related_post_id, 'rbfw_seasonal_prices', true );
            if(!empty($rbfw_sp_prices)){
                $sp_array = [];
                $i = 0;
                foreach ($rbfw_sp_prices as $value) {
                    $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
                    $rbfw_sp_end_date 	= $value['rbfw_sp_end_date'];
                    $rbfw_sp_price_h 	= $value['rbfw_sp_price_h'];
                    $rbfw_sp_price_d 	= $value['rbfw_sp_price_d'];
                    $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
                    $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
                    $sp_array[$i]['sp_daily_rate']  = $rbfw_sp_price_d;
                    $i++;
                }

                foreach ($sp_array as $sp_arr) {
                    if (in_array($current_date,$sp_arr['sp_dates'])){
                        $price = (float)$sp_arr['sp_hourly_rate'];
                    }
                }
            }
            $permalink = get_the_permalink($rbfw_related_post_id);


            /* Resort Type */
            $rbfw_room_data = get_post_meta( $rbfw_related_post_id, 'rbfw_resort_room_data', true );
            if(!empty($rbfw_room_data) && $rbfw_rent_type == 'resort'):
                $rbfw_daylong_rate = [];
                $rbfw_daynight_rate = [];
                foreach ($rbfw_room_data as $key => $value) {

                    if(!empty($value['rbfw_room_daylong_rate'])){
                        $rbfw_daylong_rate[] =  $value['rbfw_room_daylong_rate'];
                    }

                    if(!empty($value['rbfw_room_daynight_rate'])){
                        $rbfw_daynight_rate[] = $value['rbfw_room_daynight_rate'];
                    }

                }
                $merged_arr = array_merge($rbfw_daylong_rate,$rbfw_daynight_rate);

                if(!empty($merged_arr)){
                    $smallest_price = min($merged_arr);
                    $smallest_price = (float)$smallest_price;
                } else {
                    $smallest_price = 0;
                }
                $price = $smallest_price;
            endif;

            /* Single Day/Appointment Type */
            $rbfw_bike_car_sd_data = get_post_meta( $rbfw_related_post_id, 'rbfw_bike_car_sd_data', true );
            if(!empty($rbfw_bike_car_sd_data) && ($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment')):
                $rbfw_price_arr = [];

                foreach ($rbfw_bike_car_sd_data as $key => $value) {

                    if(!empty($value['price'])){
                        $rbfw_price_arr[] =  $value['price'];
                    }

                }

                if(!empty($rbfw_price_arr)){
                    $smallest_price = min($rbfw_price_arr);
                    $smallest_price = (float)$smallest_price;
                } else {
                    $smallest_price = 0;
                }
                $price = $smallest_price;
            endif;

            $post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating($rbfw_related_post_id) : '';
            $highlited_features = get_post_meta($rbfw_related_post_id, 'rbfw_highlights_texts', true) ? maybe_unserialize(get_post_meta($rbfw_related_post_id, 'rbfw_highlights_texts', true)) : [];
            ?>
            <div class="item">
                <div class="rbfw-related-product-inner">

                    <div class="rbfw-related-product-thumb-wrap"><a href="<?php echo esc_url($permalink); ?>"><div class="rbfw-related-product-thumb" style="background-image:url(<?php echo esc_url($thumb_url); ?>)"></div></a></div>

                    <div class="rbfw-related-product-bottom-card">
                        <div class="rbfw-related-product-bottom-card-pricing-box">
                            <?php if($rbfw_rent_type != 'resort' && $rbfw_rent_type != 'bike_car_sd' && $rbfw_rent_type != 'appointment'): ?>
                                <div class="rbfw-related-product-price-wrap"><?php echo esc_html($hourly_rate_label); ?>: <?php echo rbfw_mps_price($price); ?></div>
                            <?php endif; ?>

                            <?php if($rbfw_rent_type == 'resort' && !empty($rbfw_room_data)): ?>
                                <div class="rbfw-related-product-price-wrap"><?php echo esc_html($prices_start_at); ?>: <?php echo rbfw_mps_price($price); ?></div>
                            <?php endif; ?>

                            <?php if(($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment') && !empty($rbfw_bike_car_sd_data)): ?>
                                <div class="rbfw-related-product-price-wrap"><?php echo esc_html($prices_start_at); ?>: <?php echo rbfw_mps_price($price); ?></div>
                            <?php endif; ?>
                        </div>

                        <h3 class="rbfw-related-product-title-wrap"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3>

                        <?php if(!empty($highlited_features)): ?>
                            <div class="rbfw-related-product-features">
                                <?php if ( $highlited_features ) : ?>
                                    <ul>
                                        <?php
                                        $i = 1;
                                        foreach ( $highlited_features as $feature ) :

                                            if($i <= 4){
                                                if($feature['icon']):
                                                    $icon = $feature['icon'];
                                                else:
                                                    $icon = 'fas fa-arrow-right';
                                                endif;

                                                if($feature['title']):
                                                    $rand_number = rand();
                                                    echo '<li class="title'.$rand_number.'"><i class="'.mep_esc_html($icon).'"></i></li>';
                                                    ?>
                                                    <script>
                                                        jQuery(document).ready(function(){
                                                            let content<?php echo $rand_number; ?> = '<?php echo $feature['title']; ?>';
                                                            tippy('.title'+<?php echo $rand_number; ?>, {content: content<?php echo $rand_number; ?>,theme: 'blue',placement: 'top'});
                                                        });
                                                    </script>
                                                <?php
                                                endif;
                                            }
                                            $i++;
                                        endforeach;
                                        ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="rbfw-related-product-btn-wrap"><a href="<?php echo esc_url($permalink); ?>" class="rbfw-related-product-btn"><?php rbfw_string('rbfw_text_book_it',__('Book It','booking-and-rental-manager-for-woocommerce')); ?></a></div>
                </div>
            </div>
            <?php
        }

        echo '</div>';
    }
    ?>
    <script>

        jQuery(document).ready(function(){
            jQuery(".owl-carousel.t_carousel").owlCarousel({
                loop:true,
                margin:15,
                responsiveClass:true,
                dots: true,
                responsive:{
                    0:{
                        items:1,
                        //nav:false,
                        dots: true
                    },
                    600:{
                        items:2,
                        //nav:false,
                        dots: true
                    },
                    1000:{
                        items:4,
                        //nav:false,
                        loop:true,
                        dots: true
                    }
                }
            });
        });

    </script>
    <?php
}

// Related products function
add_action('rbfw_related_products_style_three','rbfw_related_products_style_three');
function rbfw_related_products_style_three($post_id){

    if(empty($post_id)){
        return;
    }

    global $rbfw;
    $rbfw_related_post_arr = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize(get_post_meta( $post_id, 'rbfw_releted_rbfw', true )) : array();
    $hourly_rate_label = $rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly rate','booking-and-rental-manager-for-woocommerce'));
    $prices_start_at = $rbfw->get_option_trans('rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __('Prices start at','booking-and-rental-manager-for-woocommerce'));
    $reviews_label = $rbfw->get_option_trans('rbfw_text_reviews', 'rbfw_basic_translation_settings', __('Reviews','booking-and-rental-manager-for-woocommerce'));

    

    if(!empty($rbfw_related_post_arr)){

        echo '<div class="owl-carousel owl-theme t_carousel">';

        foreach ($rbfw_related_post_arr as $rbfw_related_post_id) {

            $gallery_images = get_post_meta( $rbfw_related_post_id,'rbfw_gallery_images',true);
            if(isset($gallery_images)){
                $gallery_image = wp_get_attachment_url($gallery_images[0]);
            }
            else{
                $gallery_image = RBFW_PLUGIN_URL.'/assets/images/no_image.png';
            }
            
            $rbfw_rent_type = get_post_meta( $rbfw_related_post_id, 'rbfw_item_type', true );

            $thumb_url  = !empty(get_the_post_thumbnail_url( $rbfw_related_post_id, 'full' )) ? get_the_post_thumbnail_url( $rbfw_related_post_id, 'full' ) : $gallery_image;
            $title = get_the_title($rbfw_related_post_id);

            $hourly_rate_label = $rbfw->get_option_trans('rbfw_text_hourly_rate', 'rbfw_basic_translation_settings', __('Hourly rate','booking-and-rental-manager-for-woocommerce'));
            $daily_rate_label = $rbfw->get_option_trans('rbfw_text_daily_rate', 'rbfw_basic_translation_settings', __('Daily rate','booking-and-rental-manager-for-woocommerce'));
            $rbfw_enable_hourly_rate = get_post_meta( $rbfw_related_post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $rbfw_related_post_id, 'rbfw_enable_hourly_rate', true ) : 'no';

            if($rbfw_enable_hourly_rate == 'no'){
                $the_price_label = $daily_rate_label;
            } else {
                $the_price_label = $hourly_rate_label;
            }

            $prices_start_at = $rbfw->get_option_trans('rbfw_text_prices_start_at', 'rbfw_basic_translation_settings', __('Prices start at','booking-and-rental-manager-for-woocommerce'));
            $rbfw_rent_type = get_post_meta( $rbfw_related_post_id, 'rbfw_item_type', true );

            if($rbfw_enable_hourly_rate == 'yes'){

                $price = get_post_meta($rbfw_related_post_id, 'rbfw_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_hourly_rate', true) : 0;
                $price_sun = get_post_meta($rbfw_related_post_id, 'rbfw_sun_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sun_hourly_rate', true) : 0;
                $price_mon = get_post_meta($rbfw_related_post_id, 'rbfw_mon_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_mon_hourly_rate', true) : 0;
                $price_tue = get_post_meta($rbfw_related_post_id, 'rbfw_tue_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_tue_hourly_rate', true) : 0;
                $price_wed = get_post_meta($rbfw_related_post_id, 'rbfw_wed_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_wed_hourly_rate', true) : 0;
                $price_thu = get_post_meta($rbfw_related_post_id, 'rbfw_thu_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_thu_hourly_rate', true) : 0;
                $price_fri = get_post_meta($rbfw_related_post_id, 'rbfw_fri_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_fri_hourly_rate', true) : 0;
                $price_sat = get_post_meta($rbfw_related_post_id, 'rbfw_sat_hourly_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sat_hourly_rate', true) : 0;

            } else {

                $price = get_post_meta($rbfw_related_post_id, 'rbfw_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_daily_rate', true) : 0;
                $price_sun = get_post_meta($rbfw_related_post_id, 'rbfw_sun_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sun_daily_rate', true) : 0;
                $price_mon = get_post_meta($rbfw_related_post_id, 'rbfw_mon_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_mon_daily_rate', true) : 0;
                $price_tue = get_post_meta($rbfw_related_post_id, 'rbfw_tue_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_tue_daily_rate', true) : 0;
                $price_wed = get_post_meta($rbfw_related_post_id, 'rbfw_wed_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_wed_daily_rate', true) : 0;
                $price_thu = get_post_meta($rbfw_related_post_id, 'rbfw_thu_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_thu_daily_rate', true) : 0;
                $price_fri = get_post_meta($rbfw_related_post_id, 'rbfw_fri_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_fri_daily_rate', true) : 0;
                $price_sat = get_post_meta($rbfw_related_post_id, 'rbfw_sat_daily_rate', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_sat_daily_rate', true) : 0;
            }

            $price = (float)$price;

            $enabled_sun = get_post_meta($rbfw_related_post_id, 'rbfw_enable_sun_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_sun_day', true) : 'yes';
            $enabled_mon = get_post_meta($rbfw_related_post_id, 'rbfw_enable_mon_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_mon_day', true) : 'yes';
            $enabled_tue = get_post_meta($rbfw_related_post_id, 'rbfw_enable_tue_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_tue_day', true) : 'yes';
            $enabled_wed = get_post_meta($rbfw_related_post_id, 'rbfw_enable_wed_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_wed_day', true) : 'yes';
            $enabled_thu = get_post_meta($rbfw_related_post_id, 'rbfw_enable_thu_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_thu_day', true) : 'yes';
            $enabled_fri = get_post_meta($rbfw_related_post_id, 'rbfw_enable_fri_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_fri_day', true) : 'yes';
            $enabled_sat = get_post_meta($rbfw_related_post_id, 'rbfw_enable_sat_day', true) ? get_post_meta($rbfw_related_post_id, 'rbfw_enable_sat_day', true) : 'yes';

            $current_day = date('D');

            if($current_day == 'Sun' && $enabled_sun == 'yes'){
                $price = (float)$price_sun;
            }elseif($current_day == 'Mon' && $enabled_mon == 'yes'){
                $price = (float)$price_mon;
            }elseif($current_day == 'Tue' && $enabled_tue == 'yes'){
                $price = (float)$price_tue;
            }elseif($current_day == 'Wed' && $enabled_wed == 'yes'){
                $price = (float)$price_wed;
            }elseif($current_day == 'Thu' && $enabled_thu == 'yes'){
                $price = (float)$price_thu;
            }elseif($current_day == 'Fri' && $enabled_fri == 'yes'){
                $price = (float)$price_fri;
            }elseif($current_day == 'Sat' && $enabled_sat == 'yes'){
                $price = (float)$price_sat;
            }else{
                $price = (float)$price;
            }

            $current_date = date('Y-m-d');
            $rbfw_sp_prices = get_post_meta( $rbfw_related_post_id, 'rbfw_seasonal_prices', true );
            if(!empty($rbfw_sp_prices)){
                $sp_array = [];
                $i = 0;
                foreach ($rbfw_sp_prices as $value) {
                    $rbfw_sp_start_date = $value['rbfw_sp_start_date'];
                    $rbfw_sp_end_date 	= $value['rbfw_sp_end_date'];
                    $rbfw_sp_price_h 	= $value['rbfw_sp_price_h'];
                    $rbfw_sp_price_d 	= $value['rbfw_sp_price_d'];
                    $sp_array[$i]['sp_dates'] = rbfw_getBetweenDates($rbfw_sp_start_date, $rbfw_sp_end_date);
                    $sp_array[$i]['sp_hourly_rate'] = $rbfw_sp_price_h;
                    $sp_array[$i]['sp_daily_rate']  = $rbfw_sp_price_d;
                    $i++;
                }

                foreach ($sp_array as $sp_arr) {

                    if (in_array($current_date,$sp_arr['sp_dates'])){

                        if($rbfw_enable_hourly_rate == 'yes'){

                            $price = (float)$sp_arr['sp_hourly_rate'];

                        } else {

                            $price = (float)$sp_arr['sp_daily_rate'];
                        }
                    }
                }
            }

            $permalink = get_the_permalink($rbfw_related_post_id);


            /* Resort Type */
            $rbfw_room_data = get_post_meta( $rbfw_related_post_id, 'rbfw_resort_room_data', true );
            if(!empty($rbfw_room_data) && $rbfw_rent_type == 'resort') {
                $rbfw_daylong_rate = [];
                $rbfw_daynight_rate = [];
                foreach ($rbfw_room_data as $key => $value) {
                    if (!empty($value['rbfw_room_daylong_rate'])) {
                        $rbfw_daylong_rate[] = $value['rbfw_room_daylong_rate'];
                    }
                    if (!empty($value['rbfw_room_daynight_rate'])) {
                        $rbfw_daynight_rate[] = $value['rbfw_room_daynight_rate'];
                    }
                }
                $merged_arr = array_merge($rbfw_daylong_rate, $rbfw_daynight_rate);
                if (!empty($merged_arr)) {
                    $smallest_price = min($merged_arr);
                    $smallest_price = (float)$smallest_price;
                } else {
                    $smallest_price = 0;
                }
                $price = $smallest_price;
            }
            /* Single Day/Appointment Type */
            $rbfw_bike_car_sd_data = get_post_meta( $rbfw_related_post_id, 'rbfw_bike_car_sd_data', true );

            if(!empty($rbfw_bike_car_sd_data) && ($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment')){
                $rbfw_price_arr = [];
                foreach ($rbfw_bike_car_sd_data as $key => $value) {
                    if(!empty($value['price'])){
                        $rbfw_price_arr[] =  $value['price'];
                    }
                }
                if(!empty($rbfw_price_arr)){
                    $smallest_price = min($rbfw_price_arr);
                    $smallest_price = (float)$smallest_price;
                } else {
                    $smallest_price = 0;
                }
                $price = $smallest_price;
            }

            $post_review_rating = function_exists('rbfw_review_display_average_rating') ? rbfw_review_display_average_rating($rbfw_related_post_id) : '';
            $highlited_features = get_post_meta($rbfw_related_post_id, 'rbfw_highlights_texts', true) ? maybe_unserialize(get_post_meta($rbfw_related_post_id, 'rbfw_highlights_texts', true)) : [];
            $review_count = function_exists('rbfw_review_count_comments_by_id') ? rbfw_review_count_comments_by_id($rbfw_related_post_id) : '';
            $average_review = function_exists('rbfw_review_get_average_by_id') ? rbfw_review_get_average_by_id($rbfw_related_post_id) : '';

            ?>
            <div class="item">
                <div class="rbfw-related-product-inner-item-wrap">
                    <div class="rbfw-related-product-thumb-wrap"><a href="<?php echo esc_url($permalink); ?>"><div class="rbfw-related-product-thumb" style="background-image:url(<?php echo esc_url($thumb_url); ?>)"></div></a></div>
                    <?php if($review_count > 0){ ?>
                        <div class="rbfw-related-product-review-badge-wrap">
                            <div class="rbfw-related-product-review-badge-1"><?php echo $review_count.' '.$reviews_label; ?></div>
                            <div class="rbfw-related-product-review-badge-2"><?php echo $average_review; ?></div>
                        </div>
                    <?php } ?>
                    <div class="rbfw-related-product-inner-content-wrap">
                        <h3 class="rbfw-related-product-title-wrap"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3>
                        <div class="rbfw-related-product-bottom-card">

                            <div class="rbfw-related-product-bottom-card-pricing-box">
                                <?php if($rbfw_rent_type != 'resort' && $rbfw_rent_type != 'bike_car_sd' && $rbfw_rent_type != 'appointment' && $price): ?>
                                    <div class="rbfw-related-product-price-wrap"><?php echo esc_html($the_price_label); ?>: <span class="rbfw-related-product-price-badge"><?php echo rbfw_mps_price($price); ?></span></div>
                                <?php endif; ?>

                                <?php if($rbfw_rent_type == 'resort' && !empty($rbfw_room_data) && $price): ?>
                                    <div class="rbfw-related-product-price-wrap"><?php echo esc_html($prices_start_at); ?>: <span class="rbfw-related-product-price-badge"><?php echo rbfw_mps_price($price); ?></span></div>
                                <?php endif; ?>

                                <?php if(($rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment') && !empty($rbfw_bike_car_sd_data) && $price): ?>
                                    <div class="rbfw-related-product-price-wrap"><?php echo esc_html($prices_start_at); ?>: <span class="rbfw-related-product-price-badge"><?php echo rbfw_mps_price($price); ?></span></div>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($highlited_features)): ?>
                                <div class="rbfw-related-product-features">
                                    <?php if ( $highlited_features ) : ?>
                                        <ul>
                                            <?php
                                            $i = 1;
                                            foreach ( $highlited_features as $feature ) :

                                                if($i <= 4){
                                                    if($feature['icon']):
                                                        $icon = $feature['icon'];
                                                    else:
                                                        $icon = 'fas fa-arrow-right';
                                                    endif;

                                                    if($feature['title']):
                                                        $rand_number = rand();
                                                        echo '<li class="title'.$rand_number.'"><i class="'.mep_esc_html($icon).'"></i></li>';
                                                        ?>
                                                        <script>
                                                            jQuery(document).ready(function(){
                                                                let content<?php echo $rand_number; ?> = '<?php echo $feature['title']; ?>';
                                                                tippy('.title'+<?php echo $rand_number; ?>, {content: content<?php echo $rand_number; ?>,theme: 'blue',placement: 'top'});
                                                            });
                                                        </script>
                                                    <?php
                                                    endif;
                                                }
                                                $i++;
                                            endforeach;
                                            ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        echo '</div>';
    }
    ?>
    <script>

        jQuery(document).ready(function(){
            jQuery(".owl-carousel.t_carousel").owlCarousel({
                loop:true,
                margin:15,
                responsiveClass:true,
                dots: true,
                responsive:{
                    0:{
                        items:1,
                        //nav:false,
                        dots: true
                    },
                    600:{
                        items:2,
                        //nav:false,
                        dots: true
                    },
                    1000:{
                        items:3,
                        //nav:false,
                        loop:true,
                        dots: true
                    }
                }
            });
        });

    </script>
    <?php
}

/************************
 * GET RENT FAQ's Content
 *************************/
add_action('rbfw_the_faq_style_two','rbfw_the_faq_style_two_func');
function rbfw_the_faq_style_two_func($post_id){

    if(empty($post_id)){
        return;
    }

    $rbfw_faq_arr 		= get_post_meta( $post_id, 'mep_event_faq', true );

    if(! empty($rbfw_faq_arr)){

        $rbfw_faq_title 	= array_column($rbfw_faq_arr, 'rbfw_faq_title');
        $rbfw_faq_img 		= array_column($rbfw_faq_arr, 'rbfw_faq_img');
        $rbfw_faq_content 	= array_column($rbfw_faq_arr, 'rbfw_faq_content');
        $count_faq_arr 		= count($rbfw_faq_arr);
        ?>
        <div id="rbfw_faq_accordion_style_two">

            <?php for ($x = 0; $x < $count_faq_arr; $x++) { ?>
                <div class="rbfw_faq_accordion_wrapper <?php if($x == 0){ echo 'active'; }?>">
                    <?php if(! empty($rbfw_faq_title[$x])): ?>
                        <div class="rbfw_faq_heading_wrapper">
                            <h3 class="rbfw_faq_heading">
                                <span class="rbfw_faq_heading_collapsed"><?php echo esc_html($rbfw_faq_title[$x]); ?></span>
                            </h3>
                        </div>
                    <?php endif; ?>
                    <div class="rbfw_faq_content_wrapper">
                        <div class="rbfw_faq_content">
                            <div class="rbfw_faq_img">
                                <?php
                                if(! empty($rbfw_faq_img[$x])):
                                    $rbfw_img_id_arr = explode (",", $rbfw_faq_img[$x]);
                                    foreach ($rbfw_img_id_arr as $attachment_id) {
                                        $url = wp_get_attachment_url( $attachment_id );
                                        echo '<img src="'.esc_url($url).'"/>';
                                    }
                                endif;
                                ?>
                            </div>
                            <p class="rbfw_faq_desc">

                                <?php
                                if(! empty($rbfw_faq_content[$x])):
                                    echo esc_html($rbfw_faq_content[$x]);
                                endif;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <script>
            jQuery(document).ready(function(){
                jQuery('#rbfw_faq_accordion_style_two .rbfw_faq_accordion_wrapper').click(function (e) {
                    jQuery(this).toggleClass("active");

                });
            });
        </script>
        <?php
    }
}

/************************
 * GET Donut Testimonial Content
 *************************/
add_action('rbfw_dt_testimonial', 'rbfw_dt_testimonial_func');

function rbfw_dt_testimonial_func($post_id){

    if(empty($post_id)){
        return;
    }

    $testimonials = get_post_meta($post_id, 'rbfw_dt_sidebar_testimonials', true);

    if(empty($testimonials)){
        return;
    }

    $testimonials = array_column($testimonials, 'rbfw_dt_sidebar_testimonial_text');
    ?>
    <div class="rbfw_dt_testimonial">
        <h4><?php rbfw_string('rbfw_text_testimonials',__('Testimonials','booking-and-rental-manager-for-woocommerce')); ?></h4>
        <div class="owl-carousel owl-theme">
            <?php
            foreach ($testimonials as $value) {
                echo '<div class="item">'.esc_html($value).'</div>';
            }
            ?>
        </div>
    </div>
    <script>
        jQuery(document).ready(function(){
            jQuery(".rbfw_dt_testimonial .owl-carousel").owlCarousel({
                loop:true,
                margin:10,
                responsiveClass:true,
                dots: true,
                autoplay: true,
                autoplaySpeed: 1000,
                items:1,
            });
        });
    </script>
    <?php
}

/*************************************************
 * Check Plugin Folder Exists
 **************************************************/

if(! function_exists('rbfw_free_chk_plugin_folder_exist')){

    function rbfw_free_chk_plugin_folder_exist($slug){

        $plugin_dir = ABSPATH . 'wp-content/plugins/'.$slug;

        if(is_dir($plugin_dir)){
            return true;
        }
        else{
            return false;
        }
    }
}

/*************************************************
 * Check Registration Form Exists
 **************************************************/
function rbfw_chk_regf_fields_exist($post_id){

    if(empty($post_id)){
        return;
    }

    if(class_exists('Rbfw_Reg_Form')){

        $reg_form = new Rbfw_Reg_Form();
        $reg_fields = $reg_form->rbfw_generate_regf_fields($post_id);



        if(!empty($reg_fields)){

            return true;

        } else {

            return false;
        }

    } else {

        return false;
    }
}

/*************************************************
 * Get Gallary Images
 **************************************************/
function rbfw_get_additional_gallary_images($post_id, $show = 4, $style = ''){

    if(empty($post_id)){
        return;
    }

    $gallery_images_ids = get_post_meta($post_id, 'rbfw_gallery_images_additional', true) ? get_post_meta($post_id, 'rbfw_gallery_images_additional', true) : '';

    if(empty($gallery_images_ids)){
        return;
    }

    ob_start();

    if(!empty($gallery_images_ids)){
        if($style == 'style2'){
            ?>
            <div class="rbfw_additional_image_gallary_wrap" data-style="style2">
                <div class="rbfw_additional_image_gallary_inner_col">
                    <?php
                    $i = 1;
                    foreach ($gallery_images_ids as $img_id) {
                        $image_url = wp_get_attachment_url($img_id);
                        if($i == 1){
                            ?>
                            <div class="rbfw_additional_image_gallary_col" <?php if($i > $show){ echo 'style="display:none;"'; } ?>>
                                <div class="rbfw_aig_img_wrap" onclick="rbfw_aig_openModal();rbfw_aig_currentSlide(<?php echo $i; ?>)" class="rbfw_aig_hover-shadow" style="background-image:url(<?php echo esc_url($image_url); ?>)"></div>
                                <?php if($i == $show){ ?>
                                    <a class="rbfw_aig_view_more_btn" onclick="rbfw_aig_openModal();rbfw_aig_currentSlide(<?php echo $i; ?>)"><i class="fa-regular fa-images"></i> <?php esc_html_e('View More', 'booking-and-rental-manager-for-woocommerce'); ?></a>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        $i++;
                    }
                    ?>
                </div>
                <div class="rbfw_additional_image_gallary_inner_col">
                    <?php
                    $d = 1;
                    foreach ($gallery_images_ids as $img_id) {
                        $image_url = wp_get_attachment_url($img_id);
                        if($d > 1){
                            ?>
                            <div class="rbfw_additional_image_gallary_col" <?php if($d > $show){ echo 'style="display:none;"'; } ?>>
                                <div class="rbfw_aig_img_wrap" onclick="rbfw_aig_openModal();rbfw_aig_currentSlide(<?php echo $d; ?>)" class="rbfw_aig_hover-shadow" style="background-image:url(<?php echo esc_url($image_url); ?>)"></div>
                                <?php if($d == $show){ ?>
                                    <a class="rbfw_aig_view_more_btn" onclick="rbfw_aig_openModal();rbfw_aig_currentSlide(<?php echo $d; ?>)"><i class="fa-regular fa-images"></i> <?php esc_html_e('View More', 'booking-and-rental-manager-for-woocommerce'); ?></a>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        $d++;
                    }
                    ?>
                </div>
            </div>

            <!-- The Modal/Lightbox -->
            <div id="rbfw_aig_Modal" class="rbfw_aig_modal"> <span class="rbfw_aig_close cursor" onclick="rbfw_aig_closeModal()">&times;</span>
                <div class="rbfw_aig_modal-content">
                    <?php
                    $c = 1;
                    $count_images = count($gallery_images_ids);
                    foreach ($gallery_images_ids as $img_id) {
                        $image_url = wp_get_attachment_url($img_id);
                        ?>
                        <div class="rbfw_aig_slides">
                            <div class="rbfw_aig_numbertext"><?php echo $c; ?> / <?php echo $count_images; ?></div>
                            <img src="<?php echo esc_url($image_url); ?>">
                        </div>
                        <?php
                        $c++;
                    }
                    ?>

                    <!-- Next/rbfw_aig_previous controls --><a class="rbfw_aig_prev" onclick="rbfw_aig_plusSlides(-1)">&#10094;</a> <a class="rbfw_aig_next" onclick="rbfw_aig_plusSlides(1)">&#10095;</a>
                    <!-- Caption text -->
                    <div class="rbfw_aig_caption-container">
                        <p id="rbfw_aig_caption-caption"></p>
                    </div>
                    <!-- Thumbnail image controls -->
                    <div class="rbfw_aig_column_wrap">
                        <?php
                        $d = 1;
                        foreach ($gallery_images_ids as $img_id) {
                            $image_url = wp_get_attachment_url($img_id);
                            ?>
                            <div class="rbfw_aig_column"> <img class="rbfw_aig_img_thumb" src="<?php echo esc_url($image_url); ?>" onclick="rbfw_aig_currentSlide(<?php echo $d; ?>)" alt="<?php echo $d; ?>"> </div>
                            <?php
                            $d++;
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="rbfw_additional_image_gallary_wrap">
                <?php
                $i = 1;
                foreach ($gallery_images_ids as $img_id) {
                    $image_url = wp_get_attachment_url($img_id);
                    ?>
                    <div class="rbfw_additional_image_gallary_col" <?php if($i > $show){ echo 'style="display:none;"'; } ?>>
                        <div class="rbfw_aig_img_wrap" onclick="rbfw_aig_openModal();rbfw_aig_currentSlide(<?php echo $i; ?>)" class="rbfw_aig_hover-shadow" style="background-image:url(<?php echo esc_url($image_url); ?>)"></div>
                        <?php if($i == $show){ ?>
                            <a class="rbfw_aig_view_more_btn" onclick="rbfw_aig_openModal();rbfw_aig_currentSlide(<?php echo $i; ?>)"><i class="fa-regular fa-images"></i> <?php esc_html_e('View More', 'booking-and-rental-manager-for-woocommerce'); ?></a>
                        <?php } ?>
                    </div>
                    <?php
                    $i++;
                }
                ?>
                <!-- The Modal/Lightbox -->
                <div id="rbfw_aig_Modal" class="rbfw_aig_modal"> <span class="rbfw_aig_close cursor" onclick="rbfw_aig_closeModal()">&times;</span>
                    <div class="rbfw_aig_modal-content">
                        <?php
                        $c = 1;
                        $count_images = count($gallery_images_ids);
                        foreach ($gallery_images_ids as $img_id) {
                            $image_url = wp_get_attachment_url($img_id);
                            ?>
                            <div class="rbfw_aig_slides">
                                <div class="rbfw_aig_numbertext"><?php echo $c; ?> / <?php echo $count_images; ?></div>
                                <img src="<?php echo esc_url($image_url); ?>">
                            </div>
                            <?php
                            $c++;
                        }
                        ?>

                        <!-- Next/rbfw_aig_previous controls --><a class="rbfw_aig_prev" onclick="rbfw_aig_plusSlides(-1)">&#10094;</a> <a class="rbfw_aig_next" onclick="rbfw_aig_plusSlides(1)">&#10095;</a>
                        <!-- Caption text -->
                        <div class="rbfw_aig_caption-container">
                            <p id="rbfw_aig_caption-caption"></p>
                        </div>
                        <!-- Thumbnail image controls -->
                        <div class="rbfw_aig_column_wrap">
                            <?php
                            $d = 1;
                            foreach ($gallery_images_ids as $img_id) {
                                $image_url = wp_get_attachment_url($img_id);
                                ?>
                                <div class="rbfw_aig_column"> <img class="rbfw_aig_img_thumb" src="<?php echo esc_url($image_url); ?>" onclick="rbfw_aig_currentSlide(<?php echo $d; ?>)" alt="<?php echo $d; ?>"> </div>
                                <?php
                                $d++;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>

        <?php

    }

    $content = ob_get_clean();
    return $content;
}

//* Function to convert Hex colors to RGBA
function rbfw_hex2rgba( $color, $opacity = false ) {

    $defaultColor = 'rgb(0,0,0)';

    // Return default color if no color provided
    if ( empty( $color ) ) {
        return $defaultColor;
    }

    // Ignore "#" if provided
    if ( $color[0] == '#' ) {
        $color = substr( $color, 1 );
    }

    // Check if color has 6 or 3 characters, get values
    if ( strlen($color) == 6 ) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return $defaultColor;
    }

    // Convert hex values to rgb values
    $rgb =  array_map( 'hexdec', $hex );

    // Check if opacity is set(rgba or rgb)
    if ( $opacity ) {
        if( abs( $opacity ) > 1 ) {
            $opacity = 1.0;
        }
        $output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode( ",", $rgb ) . ')';
    }

    // Return rgb(a) color string
    return $output;

}

function rbfw_exist_page_by_title( $title ) {
    global $wpdb;
    $posts_table = $wpdb->prefix.'posts';
    $return = $wpdb->get_row( "SELECT ID FROM $posts_table WHERE post_title = '" . $title . "' && post_status = 'publish' && post_type = 'page' ", 'ARRAY_N' );

    if( empty( $return ) ) {
        return false;
    } else {
        return true;
    }
}

function rbfw_exist_page_by_slug( $slug ) {
    global $wpdb;
    $posts_table = $wpdb->prefix.'posts';
    $return = $wpdb->get_row( "SELECT ID FROM $posts_table WHERE post_name = '" . $slug . "' && post_status = 'publish' && post_type = 'page' ", 'ARRAY_N' );

    if( empty( $return ) ) {
        return false;
    } else {
        return true;
    }
}

function rbfw_get_available_times($rbfw_id){
    $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
    $rdfw_available_time = get_post_meta($rbfw_id, 'rdfw_available_time', true) ? maybe_unserialize(get_post_meta($rbfw_id, 'rdfw_available_time', true)) : [];

    $the_array = [];

    foreach ($rbfw_time_slots as $rts_key => $rts_value) {
        foreach ($rdfw_available_time as $rat_key => $rat_value) {
            if($rts_value == $rat_value){
                $the_array[$rts_value] = $rts_key;
            }
        }
    }
    return $the_array;
}

/* UPDATE: Inventory order status */
add_action('wp_loaded', 'rbfw_update_inventory_order_status');

function rbfw_update_inventory_order_status(){

    $check_update = get_option( 'rbfw_old_inventory_updated', true );

    if($check_update === 'yes'){
        return;
    }

    $args = array(
        'post_type' => 'rbfw_item',
        'posts_per_page' => -1,
    );

    $the_query = new WP_Query($args);

    if ( $the_query->have_posts() ) {

        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $id = get_the_ID();
            $inventory = get_post_meta($id,'rbfw_inventory',true);

            if(!empty($inventory)){
                foreach ($inventory as $key => $value) {
                    $order_id = $key;
                    $order_status = rbfw_get_order_status_by_id($order_id);
                    $inventory[$order_id]['rbfw_order_status'] = $order_status;
                    $inventory[$order_id]['rbfw_order_status'] = $order_status;
                }
            }

            update_post_meta($id, 'rbfw_inventory', $inventory);
        }
        update_option( 'rbfw_old_inventory_updated', 'yes' );
    }
}

function rbfw_get_order_status_by_id($order_id){

    $args = array(
        'post_type' => 'rbfw_order',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'rbfw_order_id',
                'value' => $order_id,
            ),
            array(
                'key' => 'rbfw_status_id',
                'value' => $order_id,
            )
        )
    );

    $the_query = new WP_Query($args);

    if ( $the_query->have_posts() ) {

        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $id = get_the_ID();
            $order_status = get_post_meta($id,'rbfw_order_status',true);

            return $order_status;
        }

    } else {
        return false;
    }

}

add_action('woocommerce_thankyou','rbfw_update_order_status');

function rbfw_update_order_status($order_id){

    $order = wc_get_order( $order_id );
    $current_status = $order->get_status();
    $items = $order->get_items();

    foreach( $items as $item_id => $item ) {

        $rbfw_id = wc_get_order_item_meta( $item_id, '_rbfw_id', true );
        $inventory = get_post_meta($rbfw_id,'rbfw_inventory', true);

        if (!empty($inventory) && array_key_exists($order_id, $inventory)){

            $inventory[$order_id]['rbfw_order_status'] = $current_status;

            update_post_meta($rbfw_id, 'rbfw_inventory', $inventory);
        }

    }
}
/************************
 * Duplicate Rental Item
 *************************/
add_action('admin_init', 'rbfw_duplicate_post');
function rbfw_duplicate_post() {

    if(isset($_GET['rbfw_duplicate'])){
        $post_id = $_GET['rbfw_duplicate'];
        $title   = get_the_title($post_id);
        $oldpost = get_post($post_id);
        $post    = array(
            'post_title' => $title,
            'post_status' => 'draft',
            'post_type' => $oldpost->post_type,
        );
        $new_post_id = wp_insert_post($post);

        // Copy meta fields.
        $post_meta = get_post_custom( $post_id );
        if( $post_meta ) {
            foreach ( $post_meta as $meta_key => $meta_values ) {
                update_post_meta( $new_post_id, $meta_key,  maybe_unserialize($meta_values[0]) );
            }
            update_post_meta($new_post_id, 'rbfw_inventory', '');
        }
    }
}

function rbfw_off_days($post_id){
    $off_days = [];
    $all_days = get_post_meta($post_id, 'rbfw_off_days', true);
    $all_days = explode(',',$all_days);
    if(!empty($all_days)){
        foreach ($all_days as $all_day){
            $off_days[] = $all_day;
        }
    }
    return json_encode($off_days);
}

function rbfw_off_dates($post_id){
    $off_dates = [];
    $off_date_ranges = get_post_meta($post_id, 'rbfw_offday_range', true);

    if(!empty($off_date_ranges)){
        foreach ($off_date_ranges as $off_date_range){
            $format = 'd-m-Y';
            $current = strtotime($off_date_range['from_date']);
            $date2 = strtotime($off_date_range['to_date']);
            $stepVal = '+1 day';
            while( $current <= $date2 ) {
                $off_dates[] = date($format, $current);
                $current = strtotime($stepVal, $current);
            }
        }
    }
    return json_encode($off_dates);
}



function rbfw_md_duration_price_calculation($post_id=0,$pickup_datetime=0,$dropoff_datetime=0,$start_date='',$end_date='', $star_time='',$end_time='')

{

    $Book_dates_array = getAllDates($pickup_datetime, $dropoff_datetime);

    if (is_plugin_active('booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php')){
        $rbfw_sp_prices = get_post_meta( $post_id, 'rbfw_seasonal_prices', true );
    }
    $rbfw_enable_daily_rate = get_post_meta($post_id, 'rbfw_enable_daily_rate', true);
    $rbfw_enable_hourly_rate = get_post_meta($post_id, 'rbfw_enable_hourly_rate', true);

    $rbfw_hourly_rate = get_post_meta($post_id, 'rbfw_hourly_rate', true);
    $rbfw_daily_rate = get_post_meta($post_id, 'rbfw_daily_rate', true);

    $endday = strtolower(date('D', strtotime($end_date)));

    $duration_price = 0;
    $diff = date_diff(new DateTime($pickup_datetime), new DateTime($dropoff_datetime));

    if ($diff) {

        $total_days = $diff->days;
        $actual_days = $diff->days;
        $hours = $diff->h + ($diff->i / 60);
        if(($hours) || ($total_days  && $rbfw_enable_hourly_rate=='yes' && $rbfw_enable_daily_rate=='no')){
            $total_days = $total_days + 1;
        }
        for ($i = 0; $i < $total_days; $i++) {

            $day = strtolower(date('D', strtotime("+$i day", strtotime($start_date))));


            if($rbfw_enable_daily_rate=='no' && $rbfw_enable_hourly_rate=='yes'){
                if($i==0){
                    if($start_date == $end_date){
                        if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$f_hours))!='not_found'){
                            $duration_price = $sp_price + $duration_price;
                        }
                        if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                            $duration_price = get_post_meta($post_id, 'rbfw_'.$day.'_hourly_rate', true) * $hours + $duration_price;
                        } else {
                            $duration_price = ($rbfw_hourly_rate * $hours + $duration_price);
                        }
                    }elseif($total_days == 1) {
                        $first_diff = date_diff(new DateTime($pickup_datetime), new DateTime($start_date. ' ' . '24:00:00'));
                        $f_hours = $first_diff->h + ($first_diff->i / 60);;

                        if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$f_hours))!='not_found'){
                            $duration_price = $sp_price + $duration_price;
                        } else {
                            if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                                $duration_price = get_post_meta($post_id, 'rbfw_'.$day.'_hourly_rate', true) * $f_hours + $duration_price;
                            } else {
                                $duration_price = ($rbfw_hourly_rate * $f_hours + $duration_price);
                            }
                        }

                        $last_diff = date_diff(new DateTime($end_date. ' ' . '00:00:00'), new DateTime($dropoff_datetime));
                        $l_hours = $last_diff->h + ($last_diff->i / 60);



                        if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$l_hours))!='not_found'){
                            $duration_price = $sp_price + $duration_price;
                        } else {
                            if (get_post_meta($post_id, 'rbfw_enable_' . $endday . '_day', true) == 'yes') {
                                $duration_price = get_post_meta($post_id, 'rbfw_'.$endday.'_hourly_rate', true) * $l_hours + $duration_price;
                            } else {
                                $duration_price = ($rbfw_hourly_rate * $l_hours + $duration_price);
                            }
                        }
                    }else{

                        $first_diff = date_diff(new DateTime($pickup_datetime), new DateTime($start_date. ' ' . '24:00:00'));
                        $f_hours = $first_diff->h + ($first_diff->i / 60);

                        if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,24))!='not_found'){
                            $duration_price = $sp_price + $duration_price;
                        }else{
                            if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                                $duration_price = get_post_meta($post_id, 'rbfw_'.$day.'_hourly_rate', true) * $f_hours + $duration_price;
                            } else {
                                $duration_price = ($rbfw_hourly_rate * $f_hours + $duration_price);
                            }
                        }
                    }
                }elseif($i == ($total_days - 1)) {

                    $last_diff = date_diff(new DateTime($end_date. ' ' . '00:00:00'), new DateTime($dropoff_datetime));
                    $l_hours = $last_diff->h + ($last_diff->i / 60);

                    if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$f_hours))!='not_found'){
                        $duration_price = $sp_price + $duration_price;
                    }

                    if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                        $duration_price = get_post_meta($post_id, 'rbfw_'.$day.'_hourly_rate', true) * $l_hours + $duration_price;
                    } else {
                        $duration_price = ($rbfw_hourly_rate * $l_hours + $duration_price);
                    }
                } else {

                    if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$f_hours))!='not_found'){
                        $duration_price = $sp_price + $duration_price;
                    }
                    if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                        $duration_price = (float)get_post_meta($post_id, 'rbfw_'.$day.'_hourly_rate', true) * 24 + $duration_price;
                    } else {
                        $duration_price = $rbfw_hourly_rate*24 + $duration_price;
                    }
                }
            }elseif($rbfw_enable_daily_rate=='yes' && $rbfw_enable_hourly_rate=='no'){
                if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$hours,$rbfw_enable_daily_rate))!='not_found'){
                    $duration_price = $sp_price + $duration_price;
                }
                if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                    $duration_price = (float)get_post_meta($post_id, 'rbfw_'.$day.'_daily_rate', true) + $duration_price;
                } else {
                    $duration_price = $rbfw_daily_rate + $duration_price;
                }
            }else{

                if($i==0 && ($start_date != $end_date) && $total_days==1 && $hours){

                    $first_diff = date_diff(new DateTime($pickup_datetime), new DateTime($start_date. ' ' . '24:00:00'));
                    $f_hours = $first_diff->h + ($first_diff->i / 60);

                    if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$f_hours))!='not_found'){
                        $duration_price = $sp_price + $duration_price;
                    } else {
                        if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                            $duration_price = get_post_meta($post_id, 'rbfw_'.$day.'_hourly_rate', true) * $f_hours + $duration_price;
                        } else {
                            $duration_price = ($rbfw_hourly_rate * $f_hours + $duration_price);
                        }
                    }



                    $last_diff = date_diff(new DateTime($end_date. ' ' . '00:00:00'), new DateTime($dropoff_datetime));
                    $l_hours = $last_diff->h + ($last_diff->i / 60);


                    if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$l_hours))!='not_found'){
                        $duration_price = $sp_price + $duration_price;
                    } else {
                        if (get_post_meta($post_id, 'rbfw_enable_' . $endday . '_day', true) == 'yes') {
                            $duration_price = get_post_meta($post_id, 'rbfw_'.$endday.'_hourly_rate', true) * $l_hours + $duration_price;
                        } else {
                            $duration_price = ($rbfw_hourly_rate * $l_hours + $duration_price);
                        }
                    }
                } elseif (($i == ($total_days - 1)) && $hours) {
                    if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$hours,$rbfw_enable_daily_rate))!='not_found'){
                        $duration_price = $sp_price + $duration_price;
                    }
                    if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                        $duration_price = (float)get_post_meta($post_id, 'rbfw_'.$day.'_hourly_rate', true) * $hours + $duration_price;
                    } else {
                        $duration_price = ($rbfw_hourly_rate * $hours + $duration_price);
                    }
                } else {
                    if(isset($rbfw_sp_prices) && $rbfw_sp_prices && ($sp_price = check_seasonal_price($Book_dates_array[$i],$rbfw_sp_prices,$hours,$rbfw_enable_daily_rate))!='not_found'){
                        $duration_price = $sp_price + $duration_price;
                    }
                    if (get_post_meta($post_id, 'rbfw_enable_' . $day . '_day', true) == 'yes') {
                        $duration_price = (float)get_post_meta($post_id, 'rbfw_'.$day.'_daily_rate', true) + $duration_price;
                    } else {
                        $duration_price = $rbfw_daily_rate + $duration_price;
                    }
                }
            }
        }
    }
    return array('duration_price'=>$duration_price,'total_days'=>$total_days,'actual_days'=>$actual_days,'hours'=>$hours);
}



function getAllDates($startingDate, $endingDate)
{
    $datesArray = [];

    $startingDate = strtotime($startingDate);
    $endingDate = strtotime($endingDate);

    for ($currentDate = $startingDate; $currentDate <= $endingDate; $currentDate += (86400)) {
        $date = date('Y-m-d', $currentDate);
        $datesArray[] = $date;
    }

    return $datesArray;
}

function check_seasonal_price($Book_date,$rbfw_sp_prices,$hours='0',$rbfw_enable_daily_rate='0')
{
    foreach ($rbfw_sp_prices as $rbfw_sp_price) {
        $rbfw_sp_start_date = $rbfw_sp_price['rbfw_sp_start_date'];
        $rbfw_sp_end_date = $rbfw_sp_price['rbfw_sp_end_date'];
        $sp_dates_array = getAllDates($rbfw_sp_start_date, $rbfw_sp_end_date);
        if (in_array($Book_date, $sp_dates_array)) {
            if($hours){
                return $rbfw_sp_price['rbfw_sp_price_h'] * $hours;
            }else{
                if($rbfw_enable_daily_rate=='no'){
                    return $rbfw_sp_price['rbfw_sp_price_h'] * 24;
                }else{
                    return $rbfw_sp_price['rbfw_sp_price_d'];
                }
            }
        } else {
            return 'not_found';
        }
    }
}

function rbfw_security_deposit($post_id,$sub_total_price)
{
    $security_deposit_amount = 0;
    $security_deposit_desc = 0;
    $rbfw_enable_security_deposit = get_post_meta( $post_id, 'rbfw_enable_security_deposit', true ) ? get_post_meta( $post_id, 'rbfw_enable_security_deposit', true ) : 'no';
    if($rbfw_enable_security_deposit=='yes'){
        $rbfw_security_deposit_type = get_post_meta( $post_id, 'rbfw_security_deposit_type', true ) ? get_post_meta( $post_id, 'rbfw_security_deposit_type', true ) : 'percentage';
        $rbfw_security_deposit_amount = get_post_meta( $post_id, 'rbfw_security_deposit_amount', true ) ? get_post_meta( $post_id, 'rbfw_security_deposit_amount', true ) : '0';
        if($rbfw_security_deposit_type=='percentage'){
            $security_deposit_amount = $rbfw_security_deposit_amount * $sub_total_price/100;
            $security_deposit_desc = wc_price($security_deposit_amount);
        }else{
            $security_deposit_amount = $rbfw_security_deposit_amount;
            $security_deposit_desc = wc_price($security_deposit_amount);
        }
    }
    return array('security_deposit_amount'=>$security_deposit_amount,'security_deposit_desc'=>$security_deposit_desc);
}

/**
 * Get unique categories from post meta with key 'rbfw_categories' using WP_Query.
 *
 * @return array|false Array of unique categories or false on failure.
 */
function get_rbfw_post_categories_from_meta() {
    $args = array(
        'post_type'      => 'any',
        'meta_key'       => 'rbfw_categories',
        'meta_compare'   => 'EXISTS',
        'posts_per_page' => -1,
        'orderby'        => 'meta_id',
        'order'          => 'DESC',
    );

    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return false;
    }
    $all_categories = [];

    while ($query->have_posts()) {
        $query->the_post();
        $meta_value = get_post_meta(get_the_ID(), 'rbfw_categories', true);
        $meta_value = maybe_unserialize($meta_value);
        if (is_array($meta_value) && count($meta_value) > 0) {
            $all_categories = array_merge($all_categories, $meta_value);
        }
    }
    wp_reset_postdata();

    $all_categories = array_filter( $all_categories, function($value ) {
        return !empty($value);
    });

    return array_unique( $all_categories );
}


/**
 * Get location data from wp_postmeta table using meta_key 'rbfw_pickup_data'.
 *
 * @return array|false Array of location data or false on failure.
 */
function get_rbfw_pickup_data_wp_query() {
    $args = array(
        'post_type'      => 'any',
        'meta_key'       => 'rbfw_pickup_data',
        'meta_compare'   => 'EXISTS',
        'orderby'        => 'meta_id',
        'order'          => 'DESC',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return false;
    }

    $locations = [];

    while ($query->have_posts()) {
        $query->the_post();
        $pickup_data = get_post_meta(get_the_ID(), 'rbfw_pickup_data', true);
        $pickup_data = maybe_unserialize($pickup_data);
        if (!empty($pickup_data)) {
            $locations[] = $pickup_data;
        }
    }
    wp_reset_postdata();

    $all_locations = [];
    if( count( $locations ) > 0 ) {
        foreach ($locations as $locations_group) {
            foreach ($locations_group as $location) {
                if (!empty($location['loc_pickup_name'])) {
                    $all_locations[$location['loc_pickup_name']] = $location['loc_pickup_name'];
                }
            }
        }
        $all_locations = array_unique($all_locations);
    }

    return $all_locations;
}

function rbfw_get_dropdown_new( $name, $saved_value , $class , $dropdown_for ) {

    if( $dropdown_for === 'category' ){
        $title =  __( 'Rental Type', 'booking-and-rental-manager-for-woocommerce'  );
        $category_arr = get_rbfw_post_categories_from_meta();
    }elseif( $dropdown_for === 'location' ){
        $title =  __( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce'  );
        $category_arr = get_rbfw_pickup_data_wp_query();
    }else{
        $title = '';
        $category_arr = [];
    }

    $option = '';
    $option .= "<select name=$name class=$class>";
    $option .= "<option value='' >" . $title . "</option>";

    if( is_array( $category_arr ) && count( $category_arr ) > 0 ){
        foreach ( $category_arr as $key => $value ) {
            $selected_text = ! empty( $saved_value ) && $saved_value == $value ? 'Selected' : '';
            $option .= "<option value='$value' $selected_text>" . esc_html( $value ) . "</option>";
        }
    }
    $option .= "</select>";

    echo $option;
}

