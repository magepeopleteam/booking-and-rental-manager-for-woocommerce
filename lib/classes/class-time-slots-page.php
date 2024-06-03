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

if (!class_exists('RBFW_Timeslots_Page')) {
    class RBFW_Timeslots_Page
    {
        public function __construct(){
            add_action('admin_footer', array($this, 'rbfw_time_slots_script'));
            add_action('wp_ajax_rbfw_insert_time_slot', array($this, 'rbfw_insert_time_slot'));
            add_action('wp_ajax_rbfw_delete_time_slot', array($this, 'rbfw_delete_time_slot'));
            add_action('wp_ajax_rbfw_update_time_slot', array($this, 'rbfw_update_time_slot'));
            
        }

        public function rbfw_time_slots_page(){
            ?>
            <div class="rbfw_time_slots_page_wrap wrap">
                <h1><?php esc_html_e('Time Slots','booking-and-rental-manager-for-woocommerce'); ?></h1>
                <?php
                $this->rbfw_time_slots_form();
                $this->rbfw_time_slots_table();
                ?>
            </div>
            <?php
        }

        public function rbfw_time_slots_form(){
            ?>
            <div class="rbfw_time_slot_page_form">
                <div class="rbfw_time_slot_form_input_group">
                    <label><?php esc_html_e('Slot Label','booking-and-rental-manager-for-woocommerce'); ?></label>
                    <input type="text" class="rbfw_time_slot_label" placeholder="Enter slot label here"/>
                </div>
                <div class="rbfw_time_slot_form_input_group">
                    <label><?php esc_html_e('Slot Time','booking-and-rental-manager-for-woocommerce'); ?></label>
                    <input type="time" class="rbfw_time_slot_time" placeholder="10:00 AM"/>
                </div>
                <div class="rbfw_time_slot_form_input_group">
                    <label></label>
                    <button class="rbfw_time_slot_add_btn"><?php esc_html_e('Add Time Slot','booking-and-rental-manager-for-woocommerce'); ?></button>
                </div>
                <div class="rbfw_time_slot_form_input_group">
                    <label></label>
                    <button class="rbfw_time_slot_reset_btn"><?php esc_html_e('Reset Form','booking-and-rental-manager-for-woocommerce'); ?></button>
                </div>
                <div class="rbfw_time_slot_form_input_group">
                    <label></label>
                    <button class="rbfw_time_slot_refresh_btn"><?php esc_html_e('Refresh Page','booking-and-rental-manager-for-woocommerce'); ?></button>
                </div>
            </div>
            <?php
        }

        public function rbfw_format_time_slot($time_slots_arr){

            $arr = [];

            if(empty($time_slots_arr)){

                return $arr;
            }

            foreach ($time_slots_arr as $key => $value) {

                $arr[$key] = date('H:i', strtotime($value));
            }

            return $arr;
        }

        public function rbfw_time_slots_table(){

            $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
            ?>
            <table class="rbfw_time_slots_page_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Slot Label','booking-and-rental-manager-for-woocommerce'); ?></th>
                        <th><?php esc_html_e('Slot Time','booking-and-rental-manager-for-woocommerce'); ?></th>
                        <th style="text-align:right"><?php esc_html_e('Action','booking-and-rental-manager-for-woocommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(!empty($rbfw_time_slots)){

                        $rbfw_time_slots = $this->rbfw_format_time_slot($rbfw_time_slots);

                        asort($rbfw_time_slots);

                        foreach ($rbfw_time_slots as $key => $value) {
                            ?>
                            <tr>
                                <td><?php echo $key; ?></td>
                                <td><?php echo date('h:i A', strtotime($value)); ?></td>
                                <td style="text-align:right">
                                    <a href="#" class="rbfw_time_slot_edit_btn" data-label="<?php echo esc_attr($key); ?>"><i class="fa-solid fa-pen-to-square"></i> <?php esc_html_e('Edit','booking-and-rental-manager-for-woocommerce'); ?></a>
                                    <a href="#" class="rbfw_time_slot_remove_btn" data-time="<?php echo esc_attr($value); ?>" data-label="<?php echo esc_attr($key); ?>"><i class="fa-solid fa-trash-can"></i></a>
                                </td>
                            </tr>
                            <?php 
                        }

                    } else {
                       ?>
                        <tr>
                            <td colspan="3"><?php esc_html_e('Sorry, no data found!','booking-and-rental-manager-for-woocommerce'); ?></td>
                        </tr>                       
                       <?php
                    }
                    ?>
                </tbody>
            </table>
            <div class="rbfw_time_slot_edit_form">
                <h3><?php esc_html_e('Edit Time Slot','booking-and-rental-manager-for-woocommerce'); ?></h3>
                <hr>
                <div class="rbfw_time_slot_edit_form_group first_child">
                    <label><?php esc_html_e('Slot Label','booking-and-rental-manager-for-woocommerce'); ?></label>
                    <input type="text" class="rbfw_time_slot_edit_slot_label"/>
                </div>
                <div class="rbfw_time_slot_edit_form_group">
                    <button class="rbfw_time_slot_edit_form_save"><?php esc_html_e('Save','booking-and-rental-manager-for-woocommerce'); ?></button>
                    <input type="hidden" class="rbfw_time_slot_edit_slot_label_current_value"/>
                </div>
            </div>
            <?php
        }

        public function rbfw_insert_time_slot(){
            
            if(isset($_POST['ts_label']) && isset($_POST['ts_time'])){

                $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
                $ts_label = $_POST['ts_label'];
                $ts_time = $_POST['ts_time'];
                $ts_time = date('h:i A', strtotime($ts_time));

                if( ! array_key_exists($ts_label, $rbfw_time_slots) ){

                    $rbfw_time_slots[$ts_label] = $ts_time;
                    update_option('rbfw_time_slots', $rbfw_time_slots);
                    $status = 'inserted';

                } else {

                    $status = 'exist';
                }

                echo json_encode( array(
                    'status'   => $status,
                ) );
                
            }
            
            wp_die();
        }

        public function rbfw_delete_time_slot(){

            if(isset($_POST['ts_time']) && isset($_POST['ts_label'])){

                $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
                $ts_time = $_POST['ts_time'];
                $ts_label = $_POST['ts_label'];
                $status = '';

                if ( array_key_exists($ts_label, $rbfw_time_slots) ) {
                    unset($rbfw_time_slots[$ts_label]);
                    update_option('rbfw_time_slots', $rbfw_time_slots);
                    $status = 'deleted';
                }

                echo json_encode( array(
                    'status'   => $status,
                ) );
            }

            wp_die();
        }

        public function rbfw_replace_key($arr, $oldkey, $newkey) {
            if(array_key_exists( $oldkey, $arr)) {
                $keys = array_keys($arr);
                $keys[array_search($oldkey, $keys)] = $newkey;
                return array_combine($keys, $arr);	
            }
            return $arr;    
        }

        public function rbfw_update_time_slot(){

            if(isset($_POST['new_ts_label']) && isset($_POST['current_ts_label'])){

                $rbfw_time_slots = !empty(get_option('rbfw_time_slots')) ? get_option('rbfw_time_slots') : [];
                $new_ts_label = $_POST['new_ts_label'];
                $current_ts_label = $_POST['current_ts_label'];
                $status = '';

                if( array_key_exists($current_ts_label, $rbfw_time_slots) ){

                    $rbfw_time_slots = $this->rbfw_replace_key($rbfw_time_slots, $current_ts_label, $new_ts_label);
                    update_option('rbfw_time_slots', $rbfw_time_slots);
                    $status = 'updated';

                    echo json_encode( array(
                        'status'   => $status,
                    ) );
                }
            }

            wp_die();
        }

        public function rbfw_time_slots_script(){
            ?>
            <script>
                jQuery(document).ready(function(){
                    jQuery('.rbfw_time_slot_add_btn').click(function (e) { 
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        let ts_label = jQuery('.rbfw_time_slot_label').val();
                        let ts_time = jQuery('.rbfw_time_slot_time').val();

                        if(ts_label == '' || ts_time == ''){

                            return;
                        }

                        jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax_url,
                            data: {
                                'action' : 'rbfw_insert_time_slot',
                                'ts_label' : ts_label,
                                'ts_time' : ts_time
                            },
                            beforeSend: function() {
                                jQuery('.rbfw_time_slot_add_btn').append('<i class="fas fa-spinner fa-spin"></i>');
                            },		
                            success: function (response) {
                                jQuery('.rbfw_time_slot_add_btn i').remove();
                                var response = JSON.parse(response); 

                                if(response.status == 'inserted'){

                                    jQuery('.rbfw_time_slot_label').val('');
                                    jQuery('.rbfw_time_slot_time').val('');
                                    alert('Good job! Time slot added!');
                                    window.location.reload();

                                }
                                else if(response.status == 'exist'){
                                    alert('Sorry! Time slot label exist!');
                                }
                                
                            }
                        });

                    });

                    jQuery('.rbfw_time_slot_refresh_btn').click(function (e) {

                        window.location.reload();
                    });

                    jQuery('.rbfw_time_slot_reset_btn').click(function (e) {

                        jQuery('.rbfw_time_slot_label').val('');
                        jQuery('.rbfw_time_slot_time').val('');
                        
                    });

                    jQuery('.rbfw_time_slot_remove_btn').click(function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        let ts_time = jQuery(this).attr('data-time');
                        let ts_label = jQuery(this).attr('data-label');
                        let this_btn = jQuery(this);
                        if (confirm('Are you sure? You won\'t be able to revert this!')) {
                            jQuery.ajax({
                                type: 'POST',
                                url: rbfw_ajax_url,
                                data: {
                                    'action' : 'rbfw_delete_time_slot',
                                    'ts_time' : ts_time,
                                    'ts_label' : ts_label
                                },
                                beforeSend: function() {
                                    this_btn.append('<i class="fas fa-spinner fa-spin"></i>');
                                },
                                success: function (response) {

                                    jQuery('.rbfw_time_slot_remove_btn i.fa-spinner').remove();

                                    var response = JSON.parse(response);

                                    if(response.status == 'deleted'){
                                        alert('Done! Time slot deleted!');
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    });

                    jQuery('.rbfw_time_slot_edit_btn').click(function (e) { 
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        let ts_label = jQuery(this).attr('data-label');
                        let this_btn = jQuery(this);
                        jQuery('.rbfw_time_slot_edit_slot_label').val(ts_label);
                        jQuery('.rbfw_time_slot_edit_slot_label_current_value').val(ts_label);

                        jQuery(".rbfw_time_slot_edit_form").mage_modal({
							escapeClose: false,
							clickClose: false,
							showClose: true
						});
                    });

                    jQuery('.rbfw_time_slot_edit_form_save').click(function (e) { 
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        let current_ts_label = jQuery('.rbfw_time_slot_edit_slot_label_current_value').val();
                        let new_ts_label = jQuery('.rbfw_time_slot_edit_slot_label').val();

                        if(new_ts_label == '' || current_ts_label == ''){

                            return;
                        }

                        jQuery.ajax({
                            type: 'POST',
                            url: rbfw_ajax_url,
                            data: {
                                'action' : 'rbfw_update_time_slot',
                                'new_ts_label' : new_ts_label,
                                'current_ts_label' : current_ts_label
                            },
                            beforeSend: function() {
                                jQuery('.rbfw_time_slot_edit_form_save').append('<i class="fas fa-spinner fa-spin"></i>');
                            },		
                            success: function (response) {
                                jQuery('.rbfw_time_slot_edit_form_save i').remove();
                                var response = JSON.parse(response); 

                                if(response.status == 'updated'){

                                    jQuery('.rbfw_time_slot_edit_form').append('<p class="rbfw_alert_success">Time slot updated! redirecting...</p>');
                                    window.location.reload();
                                }
                            }
                        });
                    });
                });
            </script>
            <?php
        }
    }
    new RBFW_Timeslots_Page();
}