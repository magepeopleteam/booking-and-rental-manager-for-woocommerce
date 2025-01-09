<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 * @package RBFW_Plugin
 */

class RBFW_Welcome
{
  public function __construct(){
    add_action("rbfw_admin_menu_after_settings",array($this,"RBFW_welcome_init"));
  }
  
  public function RBFW_welcome_init(){
    add_submenu_page(
        'edit.php?post_type=rbfw_item',
        __( 'Welcome', 'booking-and-rental-manager-for-woocommerce ' ),
        '<span style="color:#13df13">'.__( 'Welcome', 'booking-and-rental-manager-for-woocommerce ' ).'</span>',
        'manage_options',
        'rbfw_welcome',
        array($this,"RBFW_welcome_page_callback")
    );
  } 

  public function RBFW_welcome_page_callback(){
    $pro_badge = '<span class="badge">'.__( "PRO", "booking-and-rental-manager-for-woocommerce " ).'</span>';
    $arr = array( 'strong' => array() );
    if($_GET['page'] == 'rbfw_import'){
        echo '<script>jQuery(document).ready(function(){

            jQuery(".tab-link").removeClass("current");
            jQuery(".tab-content").removeClass("current");
            jQuery(".tab-link[data-tab=tab-2]").addClass("current");
            jQuery("#tab-2").addClass("current");
          
          });</script>';
    }
    ?>
    <div class="wrap rbfw_welcome_wrap">
    <?php settings_errors(); ?>
        <h1><?php _e( 'Welcome to Booking and Rental Manager', 'booking-and-rental-manager-for-woocommerce ' ); ?></h1>
            <ul class="tabs">
                <li class="tab-link current" data-tab="tab-1"><?php _e( 'Welcome', 'booking-and-rental-manager-for-woocommerce ' ); ?></li>
                <li class="tab-link" data-tab="tab-3"><?php _e( 'Shortcodes', 'booking-and-rental-manager-for-woocommerce ' ); ?></li>
            </ul>
            <!-- Start Tab One Content -->
            <div id="tab-1" class="tab-content current">
            <h1><?php _e( 'Welcome to Booking and Rental Manager Plugin Guideline', 'booking-and-rental-manager-for-woocommerce ' ); ?></h1>
            <p><?php echo wp_kses('A complete rental & booking solution for your business. It is perfect to offer all types of rental and booking services.', $arr); ?></p>
            <a href="<?php echo esc_url('https://booking.mage-people.com/'); ?>" class="rbfw_go_pro_btn2"><?php esc_html_e('View Demo','booking-and-rental-manager-for-woocommerce'); ?></a>
            <a href="<?php echo esc_url('https://docs.mage-people.com/rent-and-booking-manager/'); ?>" class="rbfw_go_pro_btn3"><?php esc_html_e('Documentation','booking-and-rental-manager-for-woocommerce'); ?></a>

            <h2><?php _e( 'Video Tutorial: How to create a <b>Bike/Car for Single Day</b> booking or rental item?', 'booking-and-rental-manager-for-woocommerce ' ); ?></h2>
            <p class="rbfw_alert_success"><i class="fa-solid fa-circle-info"></i> <?php _e( 'Video tutorial comming soon. Please follow the online <a href="https://docs.mage-people.com/rent-and-booking-manager/" target="_blank">documentation</a>.', 'booking-and-rental-manager-for-woocommerce ' ); ?></p>

            <h2><?php _e( 'Video Tutorial: How to create a <b>resort</b> booking or rental item?', 'booking-and-rental-manager-for-woocommerce ' ); ?></h2>
            <p class="rbfw_alert_success"><i class="fa-solid fa-circle-info"></i> <?php _e( 'Video tutorial comming soon. Please follow the online <a href="https://docs.mage-people.com/rent-and-booking-manager/" target="_blank">documentation</a>.', 'booking-and-rental-manager-for-woocommerce ' ); ?></p>

            </div>
             <!-- End Start Tab One Content -->

             <!-- Start Tab Three Content --> 
            <div id="tab-3" class="tab-content">
            <h1><?php _e( 'All Shortcode list', 'booking-and-rental-manager-for-woocommerce ' ); ?></h1>
            <div class="rbfw_shortcode_table_wrapper">
                <table class="rbfw_shortcode_table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Name', 'booking-and-rental-manager-for-woocommerce ' ); ?></th>
                            <th><?php _e( 'Shortcode', 'booking-and-rental-manager-for-woocommerce ' ); ?></th>
                            <th><?php _e( 'Parameter Description', 'booking-and-rental-manager-for-woocommerce ' ); ?></th>
                            <th><?php _e( 'Demo', 'booking-and-rental-manager-for-woocommerce ' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e( 'Rents – List Style', 'booking-and-rental-manager-for-woocommerce ' ); ?></td>
                            <td><code>[rent-list style="list" show="8"]</code></td>
                            <td><p><code>style</code>grid or list | Default: <strong>grid</strong> <code>show</code>Number of items show  (integer number only)   |  Default:  <strong>-1</strong> to show all</p></td>
                            <td><a href="https://booking.mage-people.com/rents-list-style/" target="_blank" class="rbfw_go_pro_btn3"><?php _e( 'View Demo', 'booking-and-rental-manager-for-woocommerce ' ); ?></a></td>
                        </tr>

                        <tr>
                            <td><?php _e( 'Rents – Grid Style', 'booking-and-rental-manager-for-woocommerce ' ); ?></td>
                            <td><code>[rent-list style="grid" show="8"]</code></td>
                            <td><p><code>style</code>grid or list | Default: <strong>grid</strong> <code>show</code>Number of items show  (integer number only)   |  Default:  <strong>-1</strong> to show all</p></td>
                            <td><a href="https://booking.mage-people.com/rents-grid-style/" target="_blank" class="rbfw_go_pro_btn3"><?php _e( 'View Demo', 'booking-and-rental-manager-for-woocommerce ' ); ?></a></td>
                        </tr>

                        <tr>
                            <td><?php _e( 'Bike List – Grid Style', 'booking-and-rental-manager-for-woocommerce ' ); ?></td>
                            <td><code>[rent-list style="grid" type="bike_car_sd"]</code></td>
                            <td><p><code>style</code>grid or list | Default: <strong>grid</strong> <code>type</code><strong>bike_car_sd</strong> or <strong>bike_car_md</strong> or <strong>resort</strong> or <strong>equipment</strong> or <strong>dress</strong> or <strong>others</strong> |  Default: show all</p></td>
                            <td><a href="https://booking.mage-people.com/" target="_blank" class="rbfw_go_pro_btn3"><?php _e( 'View Demo', 'booking-and-rental-manager-for-woocommerce ' ); ?></a></td>
                        </tr>                 
                    </tbody>
                </table>
            </div>
            </div>
            <!-- End Tab Three Content -->
            <?php if(!is_plugin_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php')){ ?>
            <div class="rbfw_welcome_footer">
                <div class="rbfw_welcome_footer_col"><?php _e( 'Get Pro and Other Available Addons to get all features.', 'booking-and-rental-manager-for-woocommerce ' ); ?> <a href="https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-pro/" target="_blank" class="rbfw_go_pro_btn1"><?php _e( 'Buy Pro', 'booking-and-rental-manager-for-woocommerce ' ); ?></a></div>
            </div>
            <?php } ?>
    </div>
    <style>
        .rbfw_welcome_footer{
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            align-content: center;
            align-items: center;
            background-color: gold;
            padding: 20px;
        }
        .rbfw_welcome_footer_col{
            width: 100%;
            font-size: 20px;
            text-align: center;
        }
        .rbfw_welcome_footer_col .rbfw_go_pro_btn1{
            padding: 10px 10px;
            border-radius: 5px;
            background: #000;
        }
        .rbfw_import_dummy_wrapper{
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            text-align: left;

            align-content: center;
        }
        .rbfw_welcome_wrap h1{
            margin-bottom: 10px;           
        }
        .rbfw_welcome_wrap #tab-1 h1{
            display:block !important;
        }
        .rbfw_welcome_wrap ul.tabs{
			margin: 0px;
			padding: 0px;
			list-style: none;
		}
		.rbfw_welcome_wrap ul.tabs li{
			background: #3BB70F;
			color: #fff;
			display: inline-block;
			padding: 10px 15px;
			cursor: pointer;
		}

		.rbfw_welcome_wrap ul.tabs li.current{
			background: #fff;
			color: #222;
		}

		.rbfw_welcome_wrap .tab-content{
			display: none;
			background: #fff;
			padding: 15px;
            position: relative;
		}

		.rbfw_welcome_wrap .tab-content.current{
			display: inherit;
		}
        .rbfw_welcome_wrap .rbfw-d-btn{
            color: #fff;
            background-color: #337ab7;
            border-color: #2e6da4;
            border-radius: 3px;            
            font-family: inherit;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-block;
            line-height: 1.125rem;
            padding: .5rem 1rem;
            margin: 0;
            height: auto;
            border: 1px solid transparent;
            vertical-align: middle;
            -webkit-appearance: none;
            text-decoration:none;
            margin-right:10px;
        }
        .rbfw_welcome_wrap .rbfw-top-pro-btn{
            color: #fff;
            background: #f95656;
            border-color: #2e6da4;
            border-radius: 3px;            
            font-family: inherit;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-block;
            line-height: 1.125rem;
            padding: .5rem 1rem;
            margin: 0;
            height: auto;
            border: 1px solid transparent;
            vertical-align: middle;
            -webkit-appearance: none;
            text-decoration:none;      
        }
        @media only screen and (min-width: 768px) {
            .rbfw_welcome_wrap .rbfw-top-pro-btn{
                position: absolute;
                right: 20px;
                top: 20px;
            }
            .rbfw_idw_col {
                width: 33%;
            }
        }  
        .rbfw_idw_col{
            padding: 10px;
            border: 1px solid #d3d3d3;
            margin: 10px;
            border-radius: 5px;
        }      
        .rbfw_welcome_wrap .rbfw-d-btn:hover, .rbfw_welcome_wrap .rbfw-top-pro-btn:hover{
            color:#fff;
        }
        .rbfw_welcome_wrap p{
            margin-top:0;
        }
        .rbfw_welcome_wrap .badge{
            background: #f95656;
            padding: 0px 5px 0px 5px;
            border-radius: 5px;
            margin-left: 5px;
        }
        .rbfw_welcome_wrap .mt-10{
            margin-top: 10px;
        }

        .rbfw_welcome_wrap ul{
            padding-left: 25px;
        }
        .rbfw_welcome_wrap ul li{
            list-style-type: disc;
        }
        .rbfw_welcome_wrap .tab-content h2{
            background: #eaf2d6;
            border-bottom: 2px solid;
            padding: 5px;
            margin-top: 20px;
            font-weight: normal;
        }
        span.rbfw_idw_import_step {
            color: #fff;
            width: 75px;
            height: 75px;
            border-radius: 50%;
            text-align: center;
            vertical-align: middle;
            display: table-cell;
            font-size: 16px;
            text-transform: uppercase;
            font-weight: 400;
            background-image: linear-gradient(180deg, #00e31d, #00c0ce);
            box-shadow: 0 15px 30px 0 rgba(0, 101, 6, .08);
        }
        .rbfw_welcome_wrap .tab-content h4{
            margin-bottom: 10px;
            font-size: 16px;
            color: #0d930d;
        }
        .rbfw_welcome_wrap table.rbfw_shortcode_table {
            width: 100%;
            border-collapse: collapse;
        }

        .rbfw_welcome_wrap table.rbfw_shortcode_table th,
        .rbfw_welcome_wrap table.rbfw_shortcode_table td {
            padding: 8px;
            text-align: left;
        }

        .rbfw_welcome_wrap table.rbfw_shortcode_table thead {
            background-color: #f1f9ff;
        }

        .rbfw_welcome_wrap table.rbfw_shortcode_table th:last-child {
            width: 15%;
        }

        .rbfw_welcome_wrap table.rbfw_shortcode_table p {
            color: #273b5e;
        }

        .rbfw_welcome_wrap table.rbfw_shortcode_table code {
            background-color: #e3e3e3;
            padding: 5px;
            font-weight: 400;
            border-radius: 5px;
            font-size: 16px;
            display: block;
        }

        .rbfw_shortcode_table_wrapper {
            overflow-x: scroll;
            overflow-y: hidden;
            margin-top: 0px;
            margin-bottom: 30px;
        }

        .rbfw_shortcode_wrapper {
            width: 100%;
        }
        .rbfw_welcome_wrap table.rbfw_shortcode_table th, .rbfw_welcome_wrap table.rbfw_shortcode_table td {
            border: 1px solid #d3d3d3;
        }
    </style>
    <script>
    jQuery(document).ready(function(){
	
        jQuery('.rbfw_welcome_wrap ul.tabs li').click(function(){
		var tab_id = jQuery(this).attr('data-tab');

		jQuery('.rbfw_welcome_wrap ul.tabs li').removeClass('current');
		jQuery('.rbfw_welcome_wrap .tab-content').removeClass('current');

		jQuery(this).addClass('current');
		jQuery("#"+tab_id).addClass('current');
	    });

        jQuery('.rbfw_welcome_wrap ul.accordion .toggle').click(function(e) {
            e.preventDefault();
        
            var $this = jQuery(this);
        
            if ($this.next().hasClass('show')) {
                $this.next().removeClass('show');
                $this.removeClass('active');
                $this.next().slideUp(350);
            } else {
                $this.parent().parent().find('li .inner').removeClass('show');
                $this.parent().parent().find('li a').removeClass('active');
                $this.parent().parent().find('li .inner').slideUp(350);
                $this.next().toggleClass('show');
                $this.toggleClass('active');
                $this.next().slideToggle(350);
            }
        });   
    });
    </script>
    <?php
  }
}
new RBFW_Welcome();
