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
 
if ( ! class_exists( 'Mage_Rating' ) ) {

	class Mage_Rating {

        private $plugin_name;
        private $text_domain;
        private $plugin_logo;
        private $rating_url;
        private $support_url;
        private $priority = 10;
        private $duplication          = false;
		private $never_show_triggered = false;
        private $days;
        private $current_screen = false;

        public function __construct(){

            add_action('admin_init', array($this,'plugin_name'));
            add_action('admin_init', array($this,'text_domain'));
            add_action('admin_init', array($this,'plugin_logo'));
            add_action('admin_init', array($this,'plugin_url'));
            add_action('admin_init', array($this,'rating_url'));
            add_action('admin_init', array($this,'support_url'));
            add_action('admin_init', array($this,'set_first_appear_day'));
            add_action('current_screen', array($this,'current_screen'));
            add_action('admin_notices', array( $this, 'display_message_box'),20);
            add_action('admin_footer', array( $this, 'scripts_and_styles' ), 9999 );
            add_action('wp_ajax_mage_rating_never_show_message', array( $this, 'never_show_message'));
			add_action('wp_ajax_mage_rating_ask_me_later_message', array( $this, 'ask_me_later_message'));
        }

        public function plugin_url() {

            return RBFW_Rent_Manager::get_plugin_data('PluginURI');
        }

        public function rating_url() {

            $this->rating_url = 'https://wordpress.org/support/plugin/'.$this->text_domain.'/reviews/#new-post';
            return $this;
        }

        public function support_url() {

            $this->support_url = $this->plugin_url().'/support-desk';
            return $this;
        }

        public function plugin_logo() {

            $this->plugin_logo = RBFW_PLUGIN_URL .'/css/images/logo.png';
            return $this;
        }

        public function plugin_name() {

            $this->plugin_name = 'Booking and Rental Manager';
            return $this;
        }

        public function text_domain() {

            $this->text_domain = RBFW_Rent_Manager::get_plugin_data('booking-and-rental-manager-for-woocommerce');
            return $this;
    
        }

		public function set_first_appear_day() {

			$this->days = 15;
			return $this;
		}

        public function set_installation_date() {
			add_option( $this->text_domain . '_install_date', gmdate( 'Y-m-d h:i:s' ) );
		}

		public function is_installation_date_exists() {
			return ( get_option( $this->text_domain . '_install_date' ) == false ) ? false : true;
		}

		public function get_installation_date() {
			return get_option( $this->text_domain . '_install_date' );
		}

		public function get_days( $from_date, $to_date ) {
			return round( ( $to_date->format( 'U' ) - $from_date->format( 'U' ) ) / ( 60 * 60 * 24 ) );
		}

        public function current_screen() {

            $current_screen = get_current_screen();
                
            if ( ! in_array( $current_screen->id, array( 'dashboard', 'plugins' ) ) ) {

                return $this->current_screen = false;

            } else {

                return $this->current_screen = true;
            }  
		}

        public function get_remaining_days() {

			$install_date  = get_option( $this->text_domain . '_install_date' );
			$display_date  = gmdate( 'Y-m-d h:i:s' );
			$datetime1     = new DateTime( $install_date );
			$datetime2     = new DateTime( $display_date );
			$diff_interval = $this->get_days( $datetime1, $datetime2 );
			return abs( $diff_interval );
		}

        public function never_show_message(){

			if( empty( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mage_rating' ) ){
				return false;
			}

			$plugin_name = isset($_POST['plugin_name']) ? sanitize_key( $_POST['plugin_name'] ) : '';
			add_option( $plugin_name . '_never_show', 'yes' );
        }

        public function ask_me_later_message(){

			if( empty( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mage_rating' ) ){
				return false;
			}

			$plugin_name = isset($_POST['plugin_name']) ? sanitize_key( $_POST['plugin_name'] ) : '';
			if ( get_option( $plugin_name . '_ask_me_later' ) == false ) {
				add_option( $plugin_name . '_ask_me_later', 'yes' );
			} else {
				add_option( $plugin_name . '_never_show', 'yes' );
			}
        }

        public function display_message_box() {

			if ( current_user_can( 'update_plugins' ) ) {
               
                if(! $this->current_screen){
                    return;
                }

                if ( ! $this->is_installation_date_exists() ) {
                    $this->set_installation_date();
                }
                
                if ( get_option( $this->text_domain . '_never_show' ) == 'yes' ) {
                    return;
                }

                if ( get_option( $this->text_domain . '_ask_me_later' ) == 'yes' ) {
                    
                    $this->days                 = 30;
                    $this->never_show_triggered = true;
                }
			
                $install_date  = get_option( $this->text_domain . '_install_date' );
                $display_date  = gmdate( 'Y-m-d h:i:s' );
                $datetime1     = new DateTime( $install_date );
                $datetime2     = new DateTime( $display_date );
                $diff_interval = $this->get_days( $datetime1, $datetime2 );

                if ( abs( $diff_interval ) >= $this->days ) {

                $not_good_enough_btn_id = ( $this->never_show_triggered ) ? '_btn_never_show' : '_btn_not_good';

                $message  = '<div id="'.esc_attr($this->text_domain).'-plugin_rating_msg_used_in_day" class="mage_rating_notice_wrap notice notice-info  '.$this->text_domain.'-plugin_rating_msg_used_in_day">';
                $message .= "<img src='".esc_url($this->plugin_logo)."'/>";
                $message .= '<div>';
                $message .= "<p>Hello! Seems like you have used <strong>".esc_html($this->plugin_name)."</strong> to this website — Thanks a lot! <br>
                As you are happy with this plugin's services, Could you please give it a <b>5 Star</b> review on wordpress.org?
                This would boost our motivation and help other users make a comfortable decision while choosing the <strong>".esc_html($this->plugin_name)."</strong>.</p>";
                $message .='<div class="mage-rating-button-container">
                            <a id="'.esc_attr($this->text_domain).'_btn_deserved" href="'.esc_url($this->rating_url).'" class="mage-rating-notice-button button-primary" target="_blank">
                                '.esc_html__('Ok, you deserved it','booking-and-rental-manager-for-woocommerce').'
                            </a>
                            
                            <a id="'.esc_attr($this->text_domain).'_btn_already_did" href="#" class="mage-rating-notice-button button-default">
                                <i class="fa-regular fa-face-smile"></i>
                                '.esc_html__('I already did','booking-and-rental-manager-for-woocommerce').'
                            </a>
                            
                            <a id="#" href="'.esc_url($this->support_url).'" class="mage-rating-notice-button button-default" target="_blank">
                                <i class="fa-solid fa-headset"></i>
                                '.esc_html__('I need support','booking-and-rental-manager-for-woocommerce').'
                            </a>
                            
                            <a id="'.esc_attr($this->text_domain).$not_good_enough_btn_id.'" href="#" class="mage-rating-notice-button button-default">
                                <i class="fa-regular fa-thumbs-down"></i>
                                '.esc_html__('No, not good enough','booking-and-rental-manager-for-woocommerce').'
                            </a>
                        </div>';
                $message .= '</div>';
                $message .= '</div>';

                echo wp_kses_post($message);
                }
            }
        }

		public function scripts_and_styles() {
        
			echo "
                <script>
                jQuery(document).ready(function ($) {
                    
                    $( '#" . esc_js( $this->text_domain ) . "_btn_already_did' ).on( 'click', function() {

                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action 	: 'mage_rating_never_show_message',
                                plugin_name : '" . esc_js( $this->text_domain ) . "',
								nonce : '" . esc_js( wp_create_nonce( 'mage_rating' ) ) . "'

                            },
                            success:function(response){
                                $('#" . esc_js( $this->text_domain ) . "-plugin_rating_msg_used_in_day').remove();

                            }
                        });

                    });

                    $('#" . esc_js( $this->text_domain ) . "_btn_deserved').click(function(){
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action 	: 'mage_rating_never_show_message',
                                plugin_name : '" . esc_js( $this->text_domain ) . "',
								nonce : '" . esc_js( wp_create_nonce( 'mage_rating' ) ) . "'
                            },
                            success:function(response){
                                $('#" . esc_js( $this->text_domain ) . "-plugin_rating_msg_used_in_day').remove();

                            }
                        });
                    });

                    $('#" . esc_js( $this->text_domain ) . "_btn_not_good').click(function(){
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action 	: 'mage_rating_ask_me_later_message',
                                plugin_name : '" . esc_js( $this->text_domain ) . "',
								nonce : '" . esc_js( wp_create_nonce( 'mage_rating' ) ) . "'
                            },
                            success:function(response){
                                $('#" . esc_js( $this->text_domain ) . "-plugin_rating_msg_used_in_day').remove();

                            }
                        });
                    });
                    
                    $('#" . esc_js( $this->text_domain ) . "_btn_never_show').click(function(){
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action 	: 'mage_rating_never_show_message',
                                plugin_name : '" . esc_js( $this->text_domain ) . "',
								nonce : '" . esc_js( wp_create_nonce( 'mage_rating' ) ) . "'
                            },
                            success:function(response){
                                $('#" . esc_js( $this->text_domain ) . "-plugin_rating_msg_used_in_day').remove();

                            }
                        });
                    });

                });
                </script>
		    ";

            echo "<style>
            .mage_rating_notice_wrap {
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex;
                border-left-color: #f99f1b;
            }
            #booking-and-rental-manager-for-woocommerce_btn_deserved{
                background: #f99f1b;
                border-color: #f99f1b;
                color: #09482d;
                font-weight: bold;
            }
            .mage-rating-notice-button {
                text-decoration: none;
                display: inline-block;
                margin: 0 5px 0px 5px;
            }
            .mage_rating_notice_wrap img{
                width: auto;
                height: 80px;
                height: 85px;
                margin: 5px 10px 0 0;
            }
            .mage-rating-button-container{
                margin-bottom: 10px;
            }
            </style>";
		}        
    }
    new Mage_Rating();
}