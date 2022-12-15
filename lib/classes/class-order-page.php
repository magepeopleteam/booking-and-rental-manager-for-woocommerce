<?php
/*
* Author 	:	MagePeople Team
* Copyright	: 	mage-people.com
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('RBFWOrderPage')) {

	class RBFWOrderPage{

        public function rbfw_order_page(){
            $args = array(
                'post_type' => 'rbfw_order',
                'order' => 'DESC',
                'posts_per_page' => -1
            );
            $query = new WP_Query( $args );
            ?>
            <div class="rbfw_order_page_wrap wrap">
                <h1><?php esc_html_e('Order List','booking-and-rental-manager-for-woocommerce'); ?></h1>
                <table class="rbfw_order_page_table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Order','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Date','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Status','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Total','booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Action','booking-and-rental-manager-for-woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    if ( $query->have_posts() ) { 
                        while ( $query->have_posts() ) {
                        $query->the_post();
                        global $post;
                        $post_id = $post->ID;
                        $status = get_post_meta($post_id, 'rbfw_order_status', true);
                        $total_price = get_post_meta($post_id, 'rbfw_ticket_total_price', true);
                    ?>
                        <tr>
                            <td><a href="<?php echo esc_url(admin_url('post.php?post='.$post_id.'&action=edit')); ?>" class="rbfw_order_title"><?php echo esc_html(get_the_title()); ?></a></td>
                            <td><?php echo esc_html(get_the_date( 'F j, Y' )).' '.esc_html(get_the_time()); ?></td>
                            <td><span class="rbfw_order_status <?php echo $status; ?>"><?php echo esc_html($status); ?></span></td>
                            <td><?php echo rbfw_mps_price($total_price); ?></td>
                            <td><a href="<?php echo esc_url(admin_url('post.php?post='.$post_id.'&action=edit')); ?>" class="rbfw_order_edit_btn"><i class="fa-solid fa-pen-to-square"></i> <?php esc_html_e('Edit','booking-and-rental-manager-for-woocommerce'); ?></a></td>
                        </tr>
                    <?php
                        }
                    }else{
                        ?>
                        <tr>
                            <td colspan="20"><?php esc_html_e( 'Sorry, No data found!', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
                        </tr>
                        <?php
                    }
                    wp_reset_postdata();
                    ?>    
                    </tbody>
                </table>
            </div> 
            <?php
        }
    }
    new RBFWOrderPage();
}