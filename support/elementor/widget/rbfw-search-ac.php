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

		/* -------------------------------------------------------------------
		 * Content tab — field visibility & label overrides.
		 * These map 1:1 to the [rbfw_search_ac] shortcode attributes so the
		 * widget stays in sync with the shortcode / Front-end Display settings.
		 * ---------------------------------------------------------------- */
		$this->start_controls_section(
			'rbfw_search_ac_content_section',
			[
				'label' => esc_html__( 'Search Fields', 'booking-and-rental-manager-for-woocommerce' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'rbfw_search_ac_hide_location',
			[
				'label'        => esc_html__( 'Hide Pickup Location', 'booking-and-rental-manager-for-woocommerce' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Hide', 'booking-and-rental-manager-for-woocommerce' ),
				'label_off'    => esc_html__( 'Show', 'booking-and-rental-manager-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Remove the Pickup Location dropdown from the search form.', 'booking-and-rental-manager-for-woocommerce' ),
			]
		);

		$this->add_control(
			'rbfw_search_ac_type_label',
			[
				'label'       => esc_html__( 'Rental Type Label', 'booking-and-rental-manager-for-woocommerce' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'e.g. Service', 'booking-and-rental-manager-for-woocommerce' ),
				'description' => esc_html__( 'Override the "Rental Type" dropdown placeholder. Leave blank to use the Global (Front-end Display) setting.', 'booking-and-rental-manager-for-woocommerce' ),
			]
		);

		$this->add_control(
			'rbfw_search_ac_location_label',
			[
				'label'       => esc_html__( 'Pickup Location Label', 'booking-and-rental-manager-for-woocommerce' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'e.g. Pickup Location', 'booking-and-rental-manager-for-woocommerce' ),
				'condition'   => [ 'rbfw_search_ac_hide_location!' => 'yes' ],
				'description' => esc_html__( 'Override the "Pickup Location" dropdown placeholder. Leave blank to use the Global (Front-end Display) setting.', 'booking-and-rental-manager-for-woocommerce' ),
			]
		);

		$this->end_controls_section();

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
        $settings = $this->get_settings_for_display();

        // Map widget controls to the shortcode attributes. Only pass an
        // attribute when the user set it, so blank labels fall back to the
        // Front-end Display (Global Settings) values inside the shortcode.
        $atts = array();

        if ( ! empty( $settings['rbfw_search_ac_hide_location'] ) && 'yes' === $settings['rbfw_search_ac_hide_location'] ) {
            $atts['hide_location'] = 'yes';
        }

        if ( ! empty( $settings['rbfw_search_ac_type_label'] ) ) {
            $atts['type_label'] = sanitize_text_field( $settings['rbfw_search_ac_type_label'] );
        }

        if ( empty( $atts['hide_location'] ) && ! empty( $settings['rbfw_search_ac_location_label'] ) ) {
            $atts['location_label'] = sanitize_text_field( $settings['rbfw_search_ac_location_label'] );
        }
        ?>
        <div class="rbfw-search-ac-widget">
            <?php
            // Call the shortcode callback directly with an attribute array so
            // label text containing spaces/special chars is passed safely
            // ( building a shortcode string would be fragile to escape ). The
            // callback returns already-escaped markup.
            if ( function_exists( 'rbfw_rent_search_ac_shortcode' ) ) {
                echo \rbfw_rent_search_ac_shortcode( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is escaped internally (esc_*/wp_kses).
            } else {
                echo do_shortcode( '[rbfw_search_ac]' );
            }
            ?>
        </div>
        <?php
    }
}