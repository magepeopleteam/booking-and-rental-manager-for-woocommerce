<?php

namespace RBFW;

class RBFWElementor {
	
	private static $_instance = null;
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	
	public function add_widget_categories( $elements_manager ) {
		
		$elements_manager->add_category(
			'RBFW-elements',
			[
				'title' => __( 'Booking and Rental Manager', 'booking-and-rental-manager-for-woocommerce'),
				'icon'  => 'fa fa-plug',
			]
		);
		
	}
	
	private function include_widgets_files() {
		require_once( __DIR__ . '/widget/rbfw-rent-add-to-cart.php' );	
		require_once( __DIR__ . '/widget/rbfw-search.php' );
	}
	
	public function register_widgets() {
		
		// Its is now safe to include Widgets files
		$this->include_widgets_files();
		
		// Register Widgets		
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\RBFWAddToCartWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\RBFWSearchWidget() );
	}
	
	public function __construct() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( \is_plugin_active( 'elementor/elementor.php' ) ) {
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_categories' ] );
        }
	}
}


// Instantiate Plugin Class
RBFWElementor::instance();
