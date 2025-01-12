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

if (!class_exists('RBFWProPage')) {

	class RBFWProPage{

        public function rbfw_go_pro_page(){
            ?>
            <div class="wrap"></div>
            <div class="rbfw_go_pro_page_wrap">
                <div class="rbfw_go_pro_intro_sec">
                    <div class="rbfw_go_pro_intro_col_1">
                        <h1><?php esc_html_e('Booking and Rental Manager for WooCommerce Pro','booking-and-rental-manager-for-woocommerce'); ?></h1>
                        <h3><?php esc_html_e('Pro Version Plugin Features:','booking-and-rental-manager-for-woocommerce'); ?></h3>
                        <ul>
                            <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Download PDF receipt for customers.','booking-and-rental-manager-for-woocommerce'); ?></li>
                            <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Automatic Email Confirmation Message and Pdf Receipts Mailing Features.','booking-and-rental-manager-for-woocommerce'); ?></li>
                            <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Reports Display.','booking-and-rental-manager-for-woocommerce'); ?></li>
                            <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Export Reports as CSV Format.','booking-and-rental-manager-for-woocommerce'); ?></li>
                            <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Booking Calender.','booking-and-rental-manager-for-woocommerce'); ?></li>
                        </ul>
                        <a href="<?php echo esc_url('https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/'); ?>" class="rbfw_go_pro_btn1"><?php esc_html_e('Buy Pro','booking-and-rental-manager-for-woocommerce'); ?></a>
                        <a href="<?php echo esc_url('https://booking.mage-people.com/'); ?>" class="rbfw_go_pro_btn2"><?php esc_html_e('View Demo','booking-and-rental-manager-for-woocommerce'); ?></a>
                        <a href="<?php echo esc_url('https://docs.mage-people.com/rent-and-booking-manager/'); ?>" class="rbfw_go_pro_btn3"><?php esc_html_e('Documentation','booking-and-rental-manager-for-woocommerce'); ?></a>
                    </div>
                    <div class="rbfw_go_pro_intro_col_2">
                        <img src="<?php echo esc_url(RBFW_PLUGIN_URL . '/css/images/4529264-ai.png'); ?>" alt="<?php esc_attr_e('Booking and Rental Manager for WooCommerce Pro','booking-and-rental-manager-for-woocommerce'); ?>">
                    </div>

                </div>
                <div class="rbfw_go_pro_intro_sec rbfw_seasonal_price_addon">
                    <div class="rbfw_go_pro_intro_col_1">
                        <h1><?php esc_html_e('Booking and Rental Manager for WooCommerce <span>Addon: Seasonal Pricing</span>','booking-and-rental-manager-for-woocommerce'); ?></h1>
                        <p><?php esc_html_e('Addon: Seasonal Pricing extends the date-wise pricing features.','booking-and-rental-manager-for-woocommerce'); ?></p>
                        <a href="<?php echo esc_url('https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-addon-seasonal-pricing/'); ?>" class="rbfw_go_pro_btn1"><?php esc_html_e('Buy Seasonal Pricing Addon','booking-and-rental-manager-for-woocommerce'); ?></a>
                    </div>
                    <div class="rbfw_go_pro_intro_col_2">
                        <img src="<?php echo esc_url(RBFW_PLUGIN_URL . '/css/images/seasonal-pricing-img.jpg'); ?>" alt="<?php esc_attr_e('Booking and Rental Manager for WooCommerce Pro','booking-and-rental-manager-for-woocommerce'); ?>">
                    </div>
                </div> 
                <div class="rbfw_go_pro_review_sec">
                <h1><?php esc_html_e('Features You\'ll Love','booking-and-rental-manager-for-woocommerce'); ?>
                    <span class="rbfw_go_pro_divider"></span>
                </h1>
                    
                    <div class="rbfw_go_pro_review_row">
                        <img src="<?php echo esc_url(RBFW_PLUGIN_URL . '/css/images/rent-pro-features.png'); ?>" alt="<?php esc_attr_e('Booking and Rental Manager for WooCommerce Pro Features','booking-and-rental-manager-for-woocommerce'); ?>">
                    </div>
                    <h1><?php esc_html_e('What User Says About Our Plugin','booking-and-rental-manager-for-woocommerce'); ?><span class="rbfw_go_pro_divider"></span></h1>
                    <div class="rbfw_go_pro_review_row">
                        <div class="rbfw_go_pro_review">
                            <div class="rbfw_go_pro_review_stars">
                                <span class="dashicons dashicons-star-filled"></span>
                                <span class="dashicons dashicons-star-filled"></span>
                                <span class="dashicons dashicons-star-filled"></span>
                                <span class="dashicons dashicons-star-filled"></span>
                                <span class="dashicons dashicons-star-filled"></span>
                            </div>
                            <div class="rbfw_go_pro_review_text">
                                <p><?php esc_html_e('This is the best booking and rental plugin. Found all things in one place. This plugin meet my business requirements.','booking-and-rental-manager-for-woocommerce'); ?></p>
                            </div>
                            <div class="rbfw_go_pro_review_writer">
                                <div class="rbfw_go_pro_review_writer_name"><?php esc_html_e('alalvenzard','booking-and-rental-manager-for-woocommerce'); ?></div>
                                <div class="rbfw_go_pro_review_writer_designation"><?php esc_html_e('Member, WordPress.Org','booking-and-rental-manager-for-woocommerce'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="rbfw_go_pro_review_row">
                        <a href="<?php echo esc_url('https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/'); ?>" class="rbfw_go_pro_btn1"><?php esc_html_e('Download PRO Version Now','booking-and-rental-manager-for-woocommerce'); ?></a>
                    </div>
                   
                </div>
            </div>
            <?php
            }
    }
}