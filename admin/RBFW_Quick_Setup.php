<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('admin_init','rbfw_quick_setup_exit',99);
function rbfw_quick_setup_exit() {
    // Check if the skip button was pressed
    if (isset($_REQUEST['rbfw_skip_quick_setup_nonce'])) {
        // Unslash and sanitize the nonce field
        $nonce = sanitize_text_field(wp_unslash($_REQUEST['rbfw_skip_quick_setup_nonce']));

        // Verify the nonce
        if (wp_verify_nonce($nonce, 'rbfw_skip_quick_setup_action')) {
            if (isset($_REQUEST['rbfw_skip_quick_setup'])) {
                update_option('rbfw_quick_setup_done', 'exit');
                $redirect_url = esc_url_raw(admin_url('index.php'));
                wp_redirect($redirect_url);
                exit;
            }
        }
    }
}




if (!class_exists('TTBM_Quick_Setup')) {
    class RBFW_Quick_Setup {

        public function __construct() {
            if (!class_exists('TTBM_Dependencies')) {
                add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'), 10, 1);
            }
            add_action('admin_menu', array($this, 'quick_setup_menu'));
        }
        public function add_admin_scripts() {

        }
        public function quick_setup_menu() {
            $status = rbfw_woo_install_check();;
            if ($status == 'Yes') {
                add_submenu_page('edit.php?post_type=rbfw_item', esc_html__('Quick Setup', 'booking-and-rental-manager-for-woocommerce'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'booking-and-rental-manager-for-woocommerce') . '</span>', 'manage_options', 'rbfw_quick_setup', array($this, 'quick_setup'));
            }
            else {
                add_menu_page( esc_html__('Rent Item', 'booking-and-rental-manager-for-woocommerce'), esc_html__('Rent Item', 'booking-and-rental-manager-for-woocommerce'), 'manage_options', 'rbfw_quick_setup', array($this, 'quick_setup'),'dashicons-clipboard',25);
            }
        }
        public function quick_setup() {
    $woo_status = rbfw_woo_install_check();

    // Check if the form is submitted and nonce is verified
    if (isset($_POST['rbfw_quick_setup']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rbfw_quick_setup'])), 'rbfw_quick_setup_nonce')) {

        // Handling WooCommerce activation button
        if (isset($_POST['active_woo_btn'])) {
            ?>
            <script>
                dLoaderBody();
            </script>
            <?php

            activate_plugin('woocommerce/woocommerce.php');

            ?>
            <script>
                (function ($) {
                    "use strict";
                    $(document).ready(function () {
                        let ttbm_admin_location = window.location.href;
                        ttbm_admin_location = ttbm_admin_location.replace('admin.php?post_type=rbfw_item&page=rbfw_quick_setup', 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup');
                        ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=rbfw_item', 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup');
                        ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=rbfw_quick_setup', 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup');
                        window.location.href = ttbm_admin_location;
                    });
                }(jQuery));
            </script>
            <?php
        }

        // Handling install and activate WooCommerce button
        if (isset($_POST['install_and_active_woo_btn'])) {
            echo '<div style="display:none">';
            include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
            include_once(ABSPATH . 'wp-admin/includes/file.php');
            include_once(ABSPATH . 'wp-admin/includes/misc.php');
            include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

            $plugin = 'woocommerce';
            $api = plugins_api('plugin_information', array(
                'slug' => $plugin,
                'fields' => array(
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false,
                ),
            ));
            $title = 'title';
            $url = 'url';
            $nonce = 'nonce';
            $woocommerce_plugin = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
            $woocommerce_plugin->install($api->download_link);
            activate_plugin('woocommerce/woocommerce.php');
            echo '</div>';

            ?>
            <script>
                (function ($) {
                    "use strict";
                    $(document).ready(function () {
                        let ttbm_admin_location = window.location.href;
                        ttbm_admin_location = ttbm_admin_location.replace('admin.php?post_type=rbfw_item&page=rbfw_quick_setup', 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup');
                        ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=rbfw_item', 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup');
                        ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=rbfw_quick_setup', 'edit.php?post_type=rbfw_item&page=rbfw_quick_setup');
                        window.location.href = ttbm_admin_location;
                    });
                }(jQuery));
            </script>
            <?php
        }

        // Handling finish quick setup button
        if (isset($_POST['finish_quick_setup'])) {

            // Ensure 'rbfw_rent_label' is unslashed and sanitized
            if (isset($_POST['rbfw_rent_label']) && !empty($_POST['rbfw_rent_label'])) {
                $rbfw_basic_gen_settings = get_option('rbfw_basic_gen_settings', true);
                $rbfw_basic_gen_settings = is_array($rbfw_basic_gen_settings) ? $rbfw_basic_gen_settings : [];
                $rbfw_basic_gen_settings['rbfw_rent_label'] = sanitize_text_field(wp_unslash($_POST['rbfw_rent_label']));
                $rbfw_basic_gen_settings['rbfw_gutenburg_switch'] = 'Off';
                update_option('rbfw_basic_gen_settings', $rbfw_basic_gen_settings);
            }

            // Ensure 'rbfw_rent_slug' is unslashed and sanitized
            if (isset($_POST['rbfw_rent_slug']) && !empty($_POST['rbfw_rent_slug'])) {
                $rbfw_basic_gen_settings = get_option('rbfw_basic_gen_settings', true);
                $rbfw_basic_gen_settings['rbfw_rent_slug'] = sanitize_text_field(wp_unslash($_POST['rbfw_rent_slug']));
                update_option('rbfw_basic_gen_settings', $rbfw_basic_gen_settings);
            }

            update_option('rbfw_quick_setup_done', 'yes');
            wp_redirect(admin_url('edit.php?post_type=rbfw_item'));
            exit;
        }
    }

    // Render quick setup form here
    ?>
            <div class="mpStyle">
                <div class="_dShadow_6_adminLayout">
                    <form method="post" action="">
                        <?php wp_nonce_field('rbfw_quick_setup_nonce', 'rbfw_quick_setup'); ?>

                        <?php wp_nonce_field('my_custom_action', 'my_nonce_field'); ?>



                        <div class="mpTabsNext">
                            <div class="tabListsNext _max_700_mAuto">
                                <div data-tabs-target-next="#ttbm_qs_welcome" class="tabItemNext" data-open-text="1" data-close-text=" " data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                    <h4 class="circleIcon" data-class>
                                        <span class="mp_zero" data-icon></span>
                                        <span class="mp_zero" data-text>1</span>
                                    </h4>
                                    <h6 class="circleTitle" data-class><?php esc_html_e('Welcome', 'booking-and-rental-manager-for-woocommerce'); ?></h6>
                                </div>
                                <div data-tabs-target-next="#ttbm_qs_general" class="tabItemNext" data-open-text="2" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                    <h4 class="circleIcon" data-class>
                                        <span class="mp_zero" data-icon></span>
                                        <span class="mp_zero" data-text>2</span>
                                    </h4>
                                    <h6 class="circleTitle" data-class><?php esc_html_e('General', 'booking-and-rental-manager-for-woocommerce'); ?></h6>
                                </div>
                                <div data-tabs-target-next="#ttbm_qs_done" class="tabItemNext" data-open-text="3" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                    <h4 class="circleIcon" data-class>
                                        <span class="mp_zero" data-icon></span>
                                        <span class="mp_zero" data-text>3</span>
                                    </h4>
                                    <h6 class="circleTitle" data-class><?php esc_html_e('Done', 'booking-and-rental-manager-for-woocommerce'); ?></h6>
                                </div>
                            </div>
                            <div class="tabsContentNext _infoLayout_mT">
                                <?php
                                $this->setup_welcome_content();
                                $this->setup_general_content();
                                $this->setup_content_done();
                                ?>
                            </div>
                            <?php if ($woo_status == 'Yes') { ?>
                                <div class="justifyBetween">
                                    <button type="button" class="mpBtn nextTab_prev">
                                        <span>&longleftarrow;<?php esc_html_e('Previous', 'booking-and-rental-manager-for-woocommerce'); ?></span>
                                    </button>
                                    <div></div>
                                    <button type="button" class="themeButton nextTab_next">
                                        <span><?php esc_html_e('Next', 'booking-and-rental-manager-for-woocommerce'); ?>&longrightarrow;</span>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>

            <?php

        }


        public function setup_welcome_content() {
    wp_nonce_field('rbfw_quick_setup_action', 'rbfw_quick_setup_nonce');
    $woo_status = rbfw_woo_install_check();
    ?>
    <div data-tabs-next="#ttbm_qs_welcome">
        <h2><?php esc_html_e('Booking and Rental Manager For Woocommerce Plugin', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
        <p class="mTB_xs"><?php esc_html_e('Thanks for choosing Booking and Rental Manager Manager Plugin for WooCommerce for your site, Please go step by step and choose some options to get started.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
        <div class="_dLayout_mT_alignCenter justifyBetween">
            <h5>
                <?php if ($woo_status == 'Yes') {
                    esc_html_e('Woocommerce already installed and activated', 'booking-and-rental-manager-for-woocommerce');
                }
                elseif ($woo_status == 'No') {
                    esc_html_e('Woocommerce need to install and active', 'booking-and-rental-manager-for-woocommerce');
                }
                else {
                    esc_html_e('Woocommerce already install , please activate it', 'booking-and-rental-manager-for-woocommerce');
                } ?>
            </h5>
            <?php if ($woo_status == 'Yes') { ?>
                <h5>
                    <span class="fas fa-check-circle textSuccess"></span>
                </h5>
            <?php } elseif ($woo_status == 'No') { ?>
                <button class="warningButton" type="submit" name="install_and_active_woo_btn"><?php esc_html_e('Install & Active Now', 'booking-and-rental-manager-for-woocommerce'); ?></button>
            <?php } else { ?>
                <button class="themeButton" type="submit" name="active_woo_btn"><?php esc_html_e('Active Now', 'booking-and-rental-manager-for-woocommerce'); ?></button>
            <?php } ?>
        </div>
        <?php if ($woo_status != 'Yes') { ?>
            <div class='mep_seup_exit_sec'>
                <!-- Add nonce for skipping setup -->
                <?php wp_nonce_field('rbfw_skip_quick_setup_action', 'rbfw_skip_quick_setup_nonce'); ?>
                <button style='margin:10px auto;' class="warningButton" type="submit" name="rbfw_skip_quick_setup"><?php esc_attr_e('Skip, Go to Dashboard','booking-and-rental-manager-for-woocommerce') ?></button>
            </div>
        <?php } ?>
    </div>
    <?php
}

        public function setup_general_content() {
            $rbfw_basic_gen_settings = get_option('rbfw_basic_gen_settings')?get_option('rbfw_basic_gen_settings'):[];

            $label = isset($rbfw_basic_gen_settings['rbfw_rent_label']) ? $rbfw_basic_gen_settings['rbfw_rent_label'] : 'Rent Item';
            $slug = isset($rbfw_basic_gen_settings['rbfw_rent_slug']) ? $rbfw_basic_gen_settings['rbfw_rent_slug'] : 'rbfw_item';
            ?>
            <div data-tabs-next="#ttbm_qs_general">
                <div class="section">
                    <h2><?php esc_html_e('General settings', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                    <p class="mTB_xs"><?php esc_html_e('Choose some general option.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
                    <div class="_dLayout_mT">
                        <label class="fullWidth">
                            <span class="min_200"><?php esc_html_e('Rent Label:', 'booking-and-rental-manager-for-woocommerce'); ?></span>
                            <input type="text" class="formControl" name="rbfw_rent_label" value='<?php echo esc_attr($label); ?>'/>
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e('It will change the Rent post type label on the entire plugin.', 'booking-and-rental-manager-for-woocommerce'); ?>
                        </i>
                        <div class="divider"></div>
                        <label class="fullWidth">
                            <span class="min_200"><?php esc_html_e('Rent Slug:', 'booking-and-rental-manager-for-woocommerce'); ?></span>
                            <input type="text" class="formControl" name="rbfw_rent_slug" value='<?php echo esc_attr($slug); ?>'/>
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e('It will change the Rent slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button', 'booking-and-rental-manager-for-woocommerce'); ?>
                        </i>
                    </div>
                </div>
            </div>
            <?php
        }
        public function setup_content_done() {
            ?>
            <div data-tabs-next="#ttbm_qs_done">
                <h2><?php esc_html_e('Finalize Setup', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                <p class="mTB_xs"><?php esc_html_e('You are about to Finish & Save Tour Booking Manager For Woocommerce Plugin setup process', 'booking-and-rental-manager-for-woocommerce'); ?></p>
                <div class="mT allCenter">
                    <button type="submit" name="finish_quick_setup" class="themeButton"><?php esc_html_e('Finish & Save', 'booking-and-rental-manager-for-woocommerce'); ?></button>
                </div>
            </div>
            <?php
        }




    }
    new RBFW_Quick_Setup();
}