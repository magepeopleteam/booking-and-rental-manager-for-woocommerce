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

class RBFWLeftFilterWidget extends Widget_Base {

	public function get_name() {
		return 'rbfw-left-filter';
	}

	public function get_title() {
		return __( 'Rent Left Filter', 'booking-and-rental-manager-for-woocommerce' );
	}

	public function get_icon() {
		return 'eicon-sidebar';
	}

	public function get_categories() {
		return [ 'RBFW-elements' ];
	}

    protected function register_controls() {
		$this->start_controls_section(
			'rbfw_left_filter_settings',
			[
				'label' => __( 'Settings', 'booking-and-rental-manager-for-woocommerce' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		
		$this->add_control(
			'title_filter',
			[
				'label' => __( 'Title Filter', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'on',
				'options' => [
					'on' => __( 'Show', 'booking-and-rental-manager-for-woocommerce' ),
					'off' => __( 'Hide', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
		$this->add_control(
			'price_filter',
			[
				'label' => __( 'Price Filter', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'on',
				'options' => [
					'on' => __( 'Show', 'booking-and-rental-manager-for-woocommerce' ),
					'off' => __( 'Hide', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
		$this->add_control(
			'location_filter',
			[
				'label' => __( 'Location Filter', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'on',
				'options' => [
					'on' => __( 'Show', 'booking-and-rental-manager-for-woocommerce' ),
					'off' => __( 'Hide', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
		$this->add_control(
			'category_filter',
			[
				'label' => __( 'Category Filter', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'on',
				'options' => [
					'on' => __( 'Show', 'booking-and-rental-manager-for-woocommerce' ),
					'off' => __( 'Hide', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
		$this->add_control(
			'type_filter',
			[
				'label' => __( 'Type Filter', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'on',
				'options' => [
					'on' => __( 'Show', 'booking-and-rental-manager-for-woocommerce' ),
					'off' => __( 'Hide', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
		$this->add_control(
			'feature_filter',
			[
				'label' => __( 'Feature Filter', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'on',
				'options' => [
					'on' => __( 'Show', 'booking-and-rental-manager-for-woocommerce' ),
					'off' => __( 'Hide', 'booking-and-rental-manager-for-woocommerce' ),
				],
			]
		);
		
        $this->end_controls_section();
		
		$this->start_controls_section(
			'rbfw_left_filter_style_section',
			[
				'label' => esc_html__( 'Style', 'booking-and-rental-manager-for-woocommerce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'rbfw_left_filter_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rbfw_left_filter_wrapper' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
            'rbfw_left_filter_text_color',
            [
                'label' => esc_html__( 'Text Color', 'booking-and-rental-manager-for-woocommerce' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rbfw_left_filter_wrapper' => 'color: {{VALUE}}',
                ],
            ]
		);

		$this->end_controls_section();
	}

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_attributes = [
            'title-filter' => $settings['title_filter'],
            'price-filter' => $settings['price_filter'],
            'location-filter' => $settings['location_filter'],
            'category-filter' => $settings['category_filter'],
            'type-filter' => $settings['type_filter'],
            'feature-filter' => $settings['feature_filter'],
        ];
        
        $shortcode_string = '[rbfw_left_filter';
        
        foreach ($shortcode_attributes as $key => $value) {
            if (!empty($value)) {
                $shortcode_string .= ' ' . $key . '="' . $value . '"';
            }
        }
        
        $shortcode_string .= ']';
        
        ?>
        <div class="rbfw-left-filter-widget">
            <?php echo do_shortcode($shortcode_string); ?>
        </div>
        <?php
    }
}