<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 * @package RBFW_Plugin
 */

if(!class_exists('RBFW_Quick_Setup')){

    class RBFW_Quick_Setup
    {
        public function __construct(){
            add_action("admin_head", array($this,"rbfw_quick_setup_admin_head"));
            add_action("rbfw_admin_menu_after_settings", array($this,"rbfw_quick_setup_func"));
            add_action("admin_init", array($this,"rbfw_quick_setup_init_func"));
        }

        public function rbfw_quick_setup_func(){
            add_submenu_page(
                'edit.php?post_type=rbfw_item',
                __( 'Setup Wizard', 'booking-and-rental-manager-for-woocommerce' ),
                '<span style="color:#13df13">'.__( 'Quick Setup', 'booking-and-rental-manager-for-woocommerce' ).'</span>',
                'manage_options',
                'rbfw_quick_setup',
                array($this,"rbfw_setup_page_callback")
            );
        }

        public function rbfw_setup_page_callback(){
            $sz_logo = RBFW_PLUGIN_URL .'/css/images/welcome-logo.png';
            global $rbfw;
            $cpt_label        = $rbfw->get_name();
            $cpt_slug         = $rbfw->get_slug();
            ?>
            <div class="rbfw-sz-wrapper">
                <header class="rbfw-sz-header"><img src="<?php echo esc_url($sz_logo); ?>"/></header>
                <main class="rbfw-sz-main">
                <form action="" method="POST">
                    <div id="rbfw-smartwizard">
                        <ul class="nav">
                            <li>
                            <a class="nav-link" href="#step-1">
                                <div class="num">1</div>
                                <?php esc_html_e('Welcome', 'booking-and-rental-manager-for-woocommerce'); ?>
                            </a>
                            </li>
                            <li>
                            <a class="nav-link" href="#step-2">
                                <span class="num">2</span>
                                <?php esc_html_e('Basic Settings', 'booking-and-rental-manager-for-woocommerce'); ?>
                            </a>
                            </li>
                            <li>
                            <a class="nav-link" href="#step-3">
                                <span class="num">3</span>
                                <?php esc_html_e('Payment System', 'booking-and-rental-manager-for-woocommerce'); ?>
                            </a>
                            </li>
                            <li>
                            <a class="nav-link " href="#step-4">
                                <span class="num">4</span>
                                <?php esc_html_e('Finish', 'booking-and-rental-manager-for-woocommerce'); ?>
                            </a>
                            </li>
                        </ul>

                        <div class="tab-content" style="margin-top: -5px;">

                            <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
                                <h2 class="title"><?php esc_html_e('Welcome to the Booking and Rental Manager Setup Wizard!', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                                <p class="subtitle"><?php esc_html_e('We\'ll guide you through each step needed to get Booking and Rental Manager fully set up on your site.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
                            </div>

                            <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
                                <h2 class="title rbfw-sz-text-left"><?php esc_html_e('Configure Basic Settings', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                                <p class="subtitle rbfw-sz-text-left"><?php esc_html_e('Below, we\'ll show you the post type settings.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
                                <hr class="rbfw-sz-hr">
                                <div class="rbfw-sz-form-group rbfw-sz-mb-20">
                                    <label class="rbfw-sz-form-label" for="rbfw_sz_cpt_label">
                                        <?php esc_html_e('CPT Label', 'booking-and-rental-manager-for-woocommerce'); ?>
                                        <small><?php esc_html_e('It will change the rent post type label on the entire plugin.', 'booking-and-rental-manager-for-woocommerce'); ?></small>
                                    </label>
                                    <div class="rbfw-sz-form-input"><input type="text" name="rbfw_sz_cpt_label" id="rbfw_sz_cpt_label" value="<?php echo $cpt_label; ?>"/></div>
                                </div>
                                <div class="rbfw-sz-form-group">
                                    <label class="rbfw-sz-form-label" for="rbfw_sz_cpt_slug">
                                        <?php esc_html_e('CPT Slug', 'booking-and-rental-manager-for-woocommerce'); ?>
                                        <small><?php esc_html_e('It will change the rent slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button', 'booking-and-rental-manager-for-woocommerce'); ?></small>
                                    </label>
                                    <div class="rbfw-sz-form-input"><input type="text" name="rbfw_sz_cpt_slug" id="rbfw_sz_cpt_slug" value="<?php echo $cpt_slug; ?>"/></div>
                                </div>
                            </div>

                            <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                                <h2 class="title rbfw-sz-text-left"><?php esc_html_e('Configure Payment System', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                                <p class="subtitle rbfw-sz-text-left"><?php esc_html_e('Below, we\'ll show you the payment system settings.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
                                <hr class="rbfw-sz-hr">
                                <div class="rbfw-sz-form-group">
                                    <label class="rbfw-sz-form-label" for="rbfw_sz_payment_system"><?php esc_html_e('Payment System', 'booking-and-rental-manager-for-woocommerce'); ?></label>
                                    <div class="rbfw-sz-form-input">
                                        <select id="rbfw_sz_payment_system" name="rbfw_sz_payment_system">
                                            <option value="wps"><?php esc_html_e('WooCommerce Payment System', 'booking-and-rental-manager-for-woocommerce'); ?></option>
                                            <option value="mps"><?php esc_html_e('Mage Payment System', 'booking-and-rental-manager-for-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
                                <h2 class="title"><?php esc_html_e('Thank you for choosing the settings!', 'booking-and-rental-manager-for-woocommerce'); ?></h2>
                                <p class="subtitle"><?php esc_html_e('You are about to Finish & Save the Rental and Booking Manager plugin setup process.', 'booking-and-rental-manager-for-woocommerce'); ?></p>
                                <div class="rbfw-sz-btn-wrap"><button type="submit" id="rbfw-sz-btn"><?php esc_html_e('Finish Setup', 'booking-and-rental-manager-for-woocommerce'); ?></button></div>
                            </div>

                        </div>

                        <!-- Include optional progressbar HTML -->
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="rbfw-sz-footer"><a href="<?php echo get_admin_url().'edit.php?post_type=rbfw_item'; ?>" class="rbfw-sz-footer-link"><?php esc_html_e('Close and exit the Setup Wizard', 'booking-and-rental-manager-for-woocommerce'); ?></a></div>
                    <input type="hidden" name="rbfw_sz_form_submit"/>
                    </form>
                </main>
            </div>

            <?php
        }

        public function rbfw_quick_setup_init_func(){

            remove_submenu_page( 'edit.php?post_type=rbfw_item', 'rbfw_quick_setup' );

            if(isset($_POST['rbfw_sz_cpt_label']) && !empty($_POST['rbfw_sz_cpt_label'])){
                $rbfw_basic_gen_settings = get_option('rbfw_basic_gen_settings',true);
                $rbfw_basic_gen_settings['rbfw_rent_label'] =  sanitize_text_field($_POST['rbfw_sz_cpt_label']);
                update_option('rbfw_basic_gen_settings', $rbfw_basic_gen_settings);
            }

            if(isset($_POST['rbfw_sz_cpt_slug']) && !empty($_POST['rbfw_sz_cpt_slug'])){
                $rbfw_basic_gen_settings = get_option('rbfw_basic_gen_settings',true);
                $rbfw_basic_gen_settings['rbfw_rent_slug'] = sanitize_text_field($_POST['rbfw_sz_cpt_slug']);
                update_option('rbfw_basic_gen_settings', $rbfw_basic_gen_settings);
            }

            if(isset($_POST['rbfw_sz_payment_system'])  && !empty($_POST['rbfw_sz_payment_system'])){
                $rbfw_basic_payment_settings = get_option('rbfw_basic_payment_settings',true);
                $rbfw_basic_payment_settings['rbfw_payment_system'] = sanitize_text_field($_POST['rbfw_sz_payment_system']);
                update_option('rbfw_basic_payment_settings', $rbfw_basic_payment_settings);

                if($_POST['rbfw_sz_payment_system'] == 'wps' && rbfw_free_chk_plugin_folder_exist('woocommerce') == true && !is_plugin_active('woocommerce/woocommerce.php')){
                    activate_plugin( 'woocommerce/woocommerce.php' );
                }

                if($_POST['rbfw_sz_payment_system'] == 'wps' && rbfw_free_chk_plugin_folder_exist('woocommerce') == false){
					echo '<div style="display:none">';
					include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
					$plugin = 'woocommerce';
					$api    = plugins_api( 'plugin_information', array(
						'slug'   => $plugin,
						'fields' => array(
							'short_description' => false,
							'sections'          => false,
							'requires'          => false,
							'rating'            => false,
							'ratings'           => false,
							'downloaded'        => false,
							'last_updated'      => false,
							'added'             => false,
							'tags'              => false,
							'compatibility'     => false,
							'homepage'          => false,
							'donate_link'       => false,
						),
					) );
					//includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
					include_once( ABSPATH . 'wp-admin/includes/file.php' );
					include_once( ABSPATH . 'wp-admin/includes/misc.php' );
					include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
                    $title = '';
                    $url = '';
                    $nonce = '';
					$woocommerce_plugin = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
					$woocommerce_plugin->install( $api->download_link );
					activate_plugin( 'woocommerce/woocommerce.php' );
					echo '</div>';

                }
                ?>
                <script>
                    window.location.replace("<?php echo get_admin_url().'edit.php?post_type=rbfw_item'; ?>");
                </script>
                <?php
            }

            if(isset($_POST['rbfw_sz_form_submit']) && get_option('rbfw_sz_form_submit') === false){

                update_option('rbfw_sz_form_submit', '1');
            }

        }

        public function rbfw_quick_setup_admin_head(){
            if(isset($_GET['post_type']) && $_GET['post_type'] == 'rbfw_item' && isset($_GET['page']) && $_GET['page'] == 'rbfw_quick_setup'){
                ?>
                <script>
                    jQuery(document).ready(function(){
                        jQuery('#rbfw-smartwizard').smartWizard({
                            selected: 0,
                            theme: 'square', // basic, arrows, square, round, dots
                            transition: {
                                animation:'fade' // none|fade|slideHorizontal|slideVertical|slideSwing|css
                            },
                            toolbar: {
                                showNextButton: true, // show/hide a Next button
                                showPreviousButton: true, // show/hide a Previous button
                                position: 'bottom', // none/ top/ both bottom
                            }
                        });
                    });
                </script>
                <style>
                #rbfw-sz-btn{
                    background-color: #e27730;
                    color: #fff;
                    font-weight: 500;
                    border-radius: 3px;
                    border: none;
                    cursor: pointer;
                    display: inline-block;
                    font-size: 16px;
                    line-height: 19px;
                    padding: 15px 30px;
                    text-decoration: none;
                }
                .rbfw-sz-footer{
                    margin-top: 30px;
                }
                .rbfw-sz-footer-link{
                    color: #999;
                    text-decoration: none;
                    font-size: 14px;
                }
                .rbfw-sz-form-label small{
                    display: block;
                    font-weight: normal;
                    color: #777777;
                }
                .rbfw-sz-form-input select{
                    display: block;
                    width: 100%;
                    font-size: 16px;
                    padding: 5px;
                    color: #444;
                    border: 1px solid #b6b6b6;
                    border-radius: 3px;
                    margin: 0 0 15px;
                    max-width: 100%;
                }
                .rbfw-sz-form-input input{
                    display: block;
                    width: 100%;
                    height: 40px;
                    font-size: 16px;
                    padding: 10px;
                    color: #444;
                    border: 1px solid #b6b6b6;
                    border-radius: 3px;
                    margin: 0 0 15px;
                }
                .rbfw-sz-mb-20{
                    margin-bottom: 20px;
                }
                .rbfw-sz-form-label{
                    font-size: 18px;
                    line-height: 22px;
                    color: #444444;
                    margin-bottom: 10px;
                    display:block;
                    font-weight: 500;
                }
                .rbfw-sz-form-group,
                .rbfw-sz-form-group label,
                .rbfw-sz-form-group .rbfw-sz-form-input{
                    text-align: left;
                }
                .rbfw-sz-hr{
                    margin-top: 20px;
                    margin-bottom: 20px;
                }
                .rbfw-sz-text-left{
                    text-align:left;
                }
                .sw > .tab-content > .tab-pane{
                    padding:20px;
                }
                .sw > .tab-content > .tab-pane h2.title{
                    color: #444;
                    font-size: 24px;
                    font-weight: 500;
                    margin: 0 0 12px;
                    line-height: 30px;
                }
                .sw > .tab-content > .tab-pane p.subtitle{
                    padding-left:0;
                    color: #777777;
                    font-size: 16px;
                    line-height: 24px;
                    margin-bottom: 10px;
                }
                .rbfw-sz-header img{
                    max-width: 664px;
                }
                .rbfw_item_page_rbfw_quick_setup #adminmenumain{
                    display: none;
                }
                .rbfw_item_page_rbfw_quick_setup #wpadminbar{
                    display: none;
                }
                .rbfw_item_page_rbfw_quick_setup .update-nag,
                .rbfw_item_page_rbfw_quick_setup div.updated,
                .rbfw_item_page_rbfw_quick_setup .error,
                .rbfw_item_page_rbfw_quick_setup .is-dismissible,
                .rbfw_item_page_rbfw_quick_setup .notice { display: none !important; }

                .rbfw_item_page_rbfw_quick_setup #wpfooter {
                    display: none;
                }
                .rbfw_item_page_rbfw_quick_setup #wpcontent {
                    margin-left: 0 !important;
                    padding: 0;
                }
                .rbfw-sz-wrapper{
                    text-align: center;
                }
                .sw-theme-square > .nav .nav-link > .num{
                    top: -32px;
                }

                .sw-theme-square > .nav .nav-link:active,
                .sw-theme-square > .nav .nav-link:focus,
                .sw-theme-square > .nav .nav-link:visited  {
                    outline: 0;
                    border: none;
                    -moz-outline-style: none;
                    box-shadow: none;
                }
                .sw > .progress{
                    position: fixed;
                    top: 0;
                }
                .rbfw-sz-header{
                    margin-top: 100px;
                }
                .sw > .tab-content{
                    margin: auto;
                    width: 646px;
                    background: #fff;
                    border: 1px solid #d3d3d3;
                    border-radius: 0px;
                    border-top: 0;
                }
                .sw .toolbar{
                    margin: auto;
                    width: 646px;
                    background: #fff;
                    border: 1px solid #d3d3d3;
                    border-radius: 0px;
                    border-top: 0;
                }
                .sw-theme-square > .nav{
                    position: fixed;
                    top: 6px;
                    width: 100%;
                    background: #fff;
                    padding-top: 20px;
                    z-index: 1;
                }
                .sw-theme-square > .nav::before{
                    top: 38px;
                }
                </style>
                <script>
                jQuery(document).ready(function(){

                    jQuery('body').addClass('rbfw-setup-wizard-fullscreen');

                });
                </script>
                <?php
            }
        }
    }

    new RBFW_Quick_Setup();
}
