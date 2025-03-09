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

class RBFWSearchAcWidget extends Widget_Base {

	public function get_name() {
		return 'rbfw-search-ac';
	}

	public function get_title() {
		return __( 'Rent Search Autocomplete', 'booking-and-rental-manager-for-woocommerce' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'RBFW-elements' ];
	}

    protected function register_controls() {
		$this->start_controls_section(
			'rbfw_search_ac_style_section',
			[
				'label' => esc_html__( 'Style', 'booking-and-rental-manager-for-woocommerce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'rbfw_search_ac_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'booking-and-rental-manager-for-woocommerce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rbfw_search_ac_wrapper' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
            'rbfw_search_ac_text_color',
            [
                'label' => esc_html__( 'Text Color', 'booking-and-rental-manager-for-woocommerce' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rbfw_search_ac_wrapper' => 'color: {{VALUE}}',
                ],
            ]
		);

		$this->end_controls_section();
	}

    protected function render() {
        ?>
        <div class="rbfw-search-ac-widget">
            <?php echo do_shortcode('[rbfw_search_ac]'); ?>
        </div>
        <?php
    }
}