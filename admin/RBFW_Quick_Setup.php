<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('admin_init','rbfw_quick_setup_exit',99);
function rbfw_quick_setup_exit(){
    if(isset($_REQUEST['rbfw_skip_quick_setup'])){
        update_option('rbfw_quick_setup_done', 'exit');
        exit(wp_redirect(admin_url('index.php')));
    }
}

if (!class_exists('TTBM_Quick_Setup')) {
    class TTBM_Quick_Setup {
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
                add_submenu_page('edit.php?post_type=rbfw_tour', __('Quick Setup', 'tour-booking-manager'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'tour-booking-manager') . '</span>', 'manage_options', 'rbfw_quick_setup', array($this, 'quick_setup'));
                add_submenu_page('rbfw_tour', esc_html__('Quick Setup', 'tour-booking-manager'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'tour-booking-manager') . '</span>', 'manage_options', 'rbfw_quick_setup', array($this, 'quick_setup'));
            }
            else {
                add_menu_page( __('Quick Setup', 'booking-and-rental-manager-for-woocommerce'), __('Quick Setup', 'booking-and-rental-manager-for-woocommerce'), 'manage_options', 'rbfw_quick_setup', array($this, 'quick_setup'));
            }
        }
        public function quick_setup() {



            $woo_status = rbfw_woo_install_check();


            if (isset($_POST['rbfw_quick_setup']) && wp_verify_nonce($_POST['rbfw_quick_setup'], 'rbfw_quick_setup_nonce'))
            {
                if (isset($_POST['active_woo_btn'])) {
                    ?>
                    <script>
                        dLoaderBody();
                    </script>
                    <?php
                    activate_plugin('woocommerce/woocommerce.php');
                    TTBM_Woocommerce_Plugin::on_activation_page_create();
                    ?>
                    <script>
                        (function ($) {
                            "use strict";
                            $(document).ready(function () {
                                let ttbm_admin_location = window.location.href;
                                ttbm_admin_location = ttbm_admin_location.replace('admin.php?post_type=ttbm_tour&page=rbfw_quick_setup', 'edit.php?post_type=ttbm_tour&page=rbfw_quick_setup');
                                ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=ttbm_tour', 'edit.php?post_type=ttbm_tour&page=rbfw_quick_setup');
                                ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=rbfw_quick_setup', 'edit.php?post_type=ttbm_tour&page=rbfw_quick_setup');
                                window.location.href = ttbm_admin_location;
                            });
                        }(jQuery));
                    </script>
                    <?php
                }
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
                    TTBM_Woocommerce_Plugin::on_activation_page_create();
                    echo '</div>';
                    ?>
                    <script>
                        (function ($) {
                            "use strict";
                            $(document).ready(function () {
                                let ttbm_admin_location = window.location.href;
                                ttbm_admin_location = ttbm_admin_location.replace('admin.php?post_type=ttbm_tour&page=rbfw_quick_setup', 'edit.php?post_type=ttbm_tour&page=rbfw_quick_setup');
                                ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=ttbm_tour', 'edit.php?post_type=ttbm_tour&page=rbfw_quick_setup');
                                ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=rbfw_quick_setup', 'edit.php?post_type=ttbm_tour&page=rbfw_quick_setup');
                                window.location.href = ttbm_admin_location;
                            });
                        }(jQuery));
                    </script>
                    <?php
                }
                if (isset($_POST['finish_quick_setup'])) {
                    $label = isset($_POST['ttbm_travel_label']) ? sanitize_text_field($_POST['ttbm_travel_label']) : 'Tour';
                    $slug = isset($_POST['ttbm_travel_slug']) ? sanitize_text_field($_POST['ttbm_travel_slug']) : 'Tour';
                    $general_settings_data = get_option('ttbm_basic_gen_settings');
                    $update_general_settings_arr = [
                        'ttbm_travel_label' => $label,
                        'ttbm_travel_slug' => $slug
                    ];
                    $new_general_settings_data = is_array($general_settings_data) ? array_replace($general_settings_data, $update_general_settings_arr) : $update_general_settings_arr;



                    update_option('ttbm_basic_gen_settings', $new_general_settings_data);
                    update_option('ttbm_quick_setup_done', 'yes');
                    wp_redirect(admin_url('edit.php?post_type=ttbm_tour'));
                }
            }

            ?>
            <div class="mpStyle">
                <div class=_dShadow_6_adminLayout">
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
                                    <h6 class="circleTitle" data-class><?php esc_html_e('Welcome', 'tour-booking-manager'); ?></h6>
                                </div>
                                <div data-tabs-target-next="#ttbm_qs_general" class="tabItemNext" data-open-text="2" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                    <h4 class="circleIcon" data-class>
                                        <span class="mp_zero" data-icon></span>
                                        <span class="mp_zero" data-text>2</span>
                                    </h4>
                                    <h6 class="circleTitle" data-class><?php esc_html_e('General', 'tour-booking-manager'); ?></h6>
                                </div>
                                <div data-tabs-target-next="#ttbm_qs_done" class="tabItemNext" data-open-text="3" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                    <h4 class="circleIcon" data-class>
                                        <span class="mp_zero" data-icon></span>
                                        <span class="mp_zero" data-text>3</span>
                                    </h4>
                                    <h6 class="circleTitle" data-class><?php esc_html_e('Done', 'tour-booking-manager'); ?></h6>
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
                                        <span>&longleftarrow;<?php esc_html_e('Previous', 'tour-booking-manager'); ?></span>
                                    </button>
                                    <div></div>
                                    <button type="button" class="themeButton nextTab_next">
                                        <span><?php esc_html_e('Next', 'tour-booking-manager'); ?>&longrightarrow;</span>
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
            $woo_status = rbfw_woo_install_check();
            ?>
            <div data-tabs-next="#ttbm_qs_welcome">
                <h2><?php esc_html_e('Tour Booking Manager For Woocommerce Plugin', 'tour-booking-manager'); ?></h2>
                <p class="mTB_xs"><?php esc_html_e('Thanks for choosing Tour Booking Manager Plugin for WooCommerce for your site, Please go step by step and choose some options to get started.', 'tour-booking-manager'); ?></p>
                <div class="_dLayout_mT_alignCenter justifyBetween">
                    <h5>
                        <?php if ($woo_status == 'Yes') {
                            esc_html_e('Woocommerce already installed and activated', 'tour-booking-manager');
                        }
                        elseif ($woo_status == 'No') {
                            esc_html_e('Woocommerce need to install and active', 'tour-booking-manager');
                        }
                        else {
                            esc_html_e('Woocommerce already install , please activate it', 'tour-booking-manager');
                        } ?>
                    </h5>
                    <?php if ($woo_status == 'Yes') { ?>
                        <h5>
                            <span class="fas fa-check-circle textSuccess"></span>
                        </h5>
                    <?php } elseif ($woo_status == 'No') { ?>
                        <button class="warningButton" type="submit" name="install_and_active_woo_btn"><?php esc_html_e('Install & Active Now', 'tour-booking-manager'); ?></button>
                    <?php } else { ?>
                        <button class="themeButton" type="submit" name="active_woo_btn"><?php esc_html_e('Active Now', 'tour-booking-manager'); ?></button>
                    <?php } ?>
                </div>
                <?php if ($woo_status != 'Yes') { ?>
                    <div class='mep_seup_exit_sec'>
                        <button style='margin:10px auto;' class="warningButton" type="submit" name="ttbm_skip_quick_setup"><?php _e('Skip, Go to Dashboard') ?></button>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
        public function setup_general_content() {
            $label = 'Rent Item';
            $slug = 'rbfw_item';
            ?>
            <div data-tabs-next="#ttbm_qs_general">
                <div class="section">
                    <h2><?php esc_html_e('General settings', 'tour-booking-manager'); ?></h2>
                    <p class="mTB_xs"><?php esc_html_e('Choose some general option.', 'tour-booking-manager'); ?></p>
                    <div class="_dLayout_mT">
                        <label class="fullWidth">
                            <span class="min_200"><?php esc_html_e('Tour Label:', 'tour-booking-manager'); ?></span>
                            <input type="text" class="formControl" name="ttbm_travel_label" value='<?php echo esc_attr($label); ?>'/>
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e('It will change the Tour post type label on the entire plugin.', 'tour-booking-manager'); ?>
                        </i>
                        <div class="divider"></div>
                        <label class="fullWidth">
                            <span class="min_200"><?php esc_html_e('Tour Slug:', 'tour-booking-manager'); ?></span>
                            <input type="text" class="formControl" name="ttbm_travel_slug" value='<?php echo esc_attr($slug); ?>'/>
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e('It will change the Tour slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button', 'tour-booking-manager'); ?>
                        </i>
                    </div>
                </div>
            </div>
            <?php
        }
        public function setup_content_done() {
            ?>
            <div data-tabs-next="#ttbm_qs_done">
                <h2><?php esc_html_e('Finalize Setup', 'tour-booking-manager'); ?></h2>
                <p class="mTB_xs"><?php esc_html_e('You are about to Finish & Save Tour Booking Manager For Woocommerce Plugin setup process', 'tour-booking-manager'); ?></p>
                <div class="mT allCenter">
                    <button type="submit" name="finish_quick_setup" class="themeButton"><?php esc_html_e('Finish & Save', 'tour-booking-manager'); ?></button>
                </div>
            </div>
            <?php
        }




    }
    new TTBM_Quick_Setup();
}