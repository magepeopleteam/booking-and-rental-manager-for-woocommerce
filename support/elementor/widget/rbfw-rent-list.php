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

class RBFWRentListWidget extends Widget_Base {

	public function get_name() {
		return 'rbfw-rent-list';
	}

	public function get_title() {
		return __( 'Rent List', 'booking-and-rental-manager-for-woocommerce' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'RBFW-elements' ];
	}

    protected function register_controls() {

		$this->start_controls_section(
			'rbfw_rent_list_settings',
			[
				'label' => __( 'Settings', 'booking-and-rental-manager-for-woocommerce' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		
		$this->add_control(
			'style',
			[
				'label' => __( 'Style', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid' => __( 'Grid', 'booking-and-rental-manager-for-woocommerce' ),
					'list' => __( 'List', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
		$this->add_control(
			'show',
			[
				'label' => __( 'Items to Show', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::NUMBER,
				'default' => -1,
				'description' => __( 'Enter -1 to show all items', 'booking-and-rental-manager-for-woocommerce' ),
			]
		);
		
		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC' => __( 'Ascending', 'booking-and-rental-manager-for-woocommerce' ),
					'DESC' => __( 'Descending', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
		$this->add_control(
			'columns',
			[
				'label' => __( 'Columns', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'options' => [
					'1' => __( '1', 'booking-and-rental-manager-for-woocommerce' ),
					'2' => __( '2', 'booking-and-rental-manager-for-woocommerce' ),
					'3' => __( '3', 'booking-and-rental-manager-for-woocommerce' ),
					'4' => __( '4', 'booking-and-rental-manager-for-woocommerce' ),
					'5' => __( '5', 'booking-and-rental-manager-for-woocommerce' ),
				],
				'condition' => [
					'style' => 'grid',
				],
			]
		);
		
		$this->add_control(
			'type',
			[
				'label' => __( 'Type', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'description' => __( 'Filter by type', 'booking-and-rental-manager-for-woocommerce' ),
			]
		);
		
		$this->add_control(
			'location',
			[
				'label' => __( 'Location', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'description' => __( 'Filter by location', 'booking-and-rental-manager-for-woocommerce' ),
			]
		);
		
		$this->add_control(
			'category',
			[
				'label' => __( 'Category', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'description' => __( 'Filter by category (comma separated IDs)', 'booking-and-rental-manager-for-woocommerce' ),
			]
		);
		
		$this->add_control(
			'left_filter',
			[
				'label' => __( 'Left Filter', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'No', 'booking-and-rental-manager-for-woocommerce' ),
					'on' => __( 'Yes', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
        $this->end_controls_section();	                
	}

    protected function render() {

        $settings = $this->get_settings_for_display();
        
        $shortcode_attributes = [
            'style' => $settings['style'],
            'show' => $settings['show'],
            'order' => $settings['order'],
            'columns' => $settings['columns'],
            'type' => $settings['type'],
            'location' => $settings['location'],
            'category' => $settings['category'],
            'left-filter' => $settings['left_filter'],
        ];
        
        $shortcode_string = '[rent-list';
        
        foreach ($shortcode_attributes as $key => $value) {
            if (!empty($value)) {
                $shortcode_string .= ' ' . $key . '="' . $value . '"';
            }
        }
        
        $shortcode_string .= ']';
        
        ?>
        <div class="rbfw-rent-list-widget">
            <?php echo do_shortcode($shortcode_string); ?>
        </div>
        <?php
    }
}