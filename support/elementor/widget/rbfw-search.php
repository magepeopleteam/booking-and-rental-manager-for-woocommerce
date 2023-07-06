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

class RBFWSearchWidget extends Widget_Base {

	public function get_name() {
		return 'rbfw-search';
	}

	public function get_title() {
		return __( 'Rent Search', 'booking-and-rental-manager-for-woocommerce' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'RBFW-elements' ];
	}


    protected function register_controls() {

		$this->start_controls_section(
			'rbfw_search_style_section',
			[
				'label' => esc_html__( 'Style', 'textdomain' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'rbfw_search_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rbfw_search_form' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
            'rbfw_search_label_color',
            [
                'label' => esc_html__( 'Label Color', 'textdomain' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rbfw_search_form label' => 'color: {{VALUE}}',
                ],
            ]
		);

        $this->add_control(
            'rbfw_search_button_bg_color',
            [
                'label' => esc_html__( 'Button Background Color', 'textdomain' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rbfw_search_form button' => 'background-color: {{VALUE}}',
                ],
            ]
		);

        $this->add_control(
            'rbfw_search_button_text_color',
            [
                'label' => esc_html__( 'Button Text Color', 'textdomain' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rbfw_search_form button' => 'color: {{VALUE}}',
                ],
            ]
		);

		$this->end_controls_section();
	}

    protected function render() {

        $settings = $this->get_settings_for_display();

        ?>
        <div class="rbfw-search-widget">
            <?php echo do_shortcode('[rbfw-search]'); ?>
        </div>
        <?php
    }
}