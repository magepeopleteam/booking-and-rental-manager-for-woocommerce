<?php
namespace RBFW\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class RBFWAddToCartWidget extends Widget_Base {

	public function get_name() {
		return 'rent-add-to-cart';
	}

	public function get_title() {
		return __( 'Rent Add To Cart', 'booking-and-rental-manager-for-woocommerce' );
	}

	public function get_icon() {
		return 'eicon-cart-medium';
	}

	public function get_categories() {
		return [ 'RBFW-elements' ];
	}

    public function get_rbfw_item_list(){
        $args = array(
            'post_type'=> 'rbfw_item',
            'order'    => 'ASC',
			'posts_per_page' => -1
        );              
        $arr = [];

        $the_query = new \WP_Query($args);
        if($the_query->have_posts() ) : 
            while ( $the_query->have_posts() ) : 
               $the_query->the_post(); 
        
               $arr[get_the_ID()] = get_the_title();
            endwhile; 
            wp_reset_postdata(); 
        else: 
        endif;

        return $arr;
    }
    protected function register_controls() {

		$this->start_controls_section(
			'rbfw_rent_add_to_cart_settings',
			[
				'label' => __( 'Settings', 'booking-and-rental-manager-for-woocommerce' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'rbfw_rent_add_to_cart_item_id',
			[
				'label' => __( 'Item','booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_rbfw_item_list(),
			]
		);																		
        $this->end_controls_section();	                
	}

    protected function render() {

        $settings = $this->get_settings_for_display();
        $post_id = $settings['rbfw_rent_add_to_cart_item_id'];
        ?>
        <div class="rbfw-rent-add-to-cart-widget">
            <?php echo do_shortcode('[rent-add-to-cart id="'.$post_id.'"]'); ?>
        </div>
        <?php
    }
}