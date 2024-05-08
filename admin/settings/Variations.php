<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Variations')) {
        class RBFW_Variations{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu($rbfw_id) {
                $rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';
	            $rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
	
            ?>
                <li  data-target-tabs="#rbfw_variations" ><i class="fa-solid fa-table-cells-large"></i><?php esc_html_e('Variations', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Template Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure template Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
                <?php
            }


			public function panel_header($title,$description){
                ?>
                    <section class="bg-light mt-5">
                        <div>
                            <label>
                                <?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $title ); ?>
                            </label>
                            <span><?php echo sprintf(__("%s",'booking-and-rental-manager-for-woocommerce'), $description ); ?></span>
                        </div>
                    </section>
                <?php
            }
            public function add_tabs_content( $post_id ) {
                $rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : '';
                $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
                $rbfw_variations_data = get_post_meta( $post_id, 'rbfw_variations_data', true ) ? get_post_meta( $post_id, 'rbfw_variations_data', true ) : [];
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_variations" data-tab-item="#rbfw_variations" <?php if($rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
                    <div>
                        <h2 class="h5 text-white bg-primary mb-1 rounded-top"><?php esc_html_e( 'Variations Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                        <section class="component d-flex justify-content-between align-items-center mb-2" data-row="rbfw_time_slot_switch">
                            <label scope="row" class="w-50"><?php esc_html_e( 'Item Variations', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span><?php esc_html_e( 'Enable/Disable Variations. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></i>										</label>
                                                        
                            <div class="d-flex flex-column w-50">
                                <div class="rbfw_switch rbfw_switch_variations">
                                    <label for="rbfw_enable_variations_on" class="<?php if ( $rbfw_enable_variations == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_variations" class="rbfw_enable_variations" value="yes" id="rbfw_enable_variations_on" <?php if ( $rbfw_enable_variations == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_variations_off" class="<?php if ( $rbfw_enable_variations != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_variations" class="rbfw_enable_variations" value="no" id="rbfw_enable_variations_off" <?php if ( $rbfw_enable_variations != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
                                </div>
                            </div>
                        </section>

                        <div class="rbfw_variations_table_wrap component mb-2" <?php if($rbfw_enable_variations == 'no'){ echo 'style="display:none"'; }?>>
                            <table class="form-table rbfw_variations_table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Value(s)', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="rbfw_variations_table_body ui-sortable">
                                <?php
                                if(! empty($rbfw_variations_data)) :
                                $i = 0;
                                foreach ($rbfw_variations_data as $key => $value):
                                    $selected_value = !empty($value['selected_value']) ? $value['selected_value'] : '';
                                ?>
                                    <tr class="rbfw_variations_table_row" data-key="<?php echo esc_attr($i); ?>">

                                        <td>
                                            <input type="text" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][field_label]" value="<?php echo esc_attr( $value['field_label'] ); ?>" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                            <input type="hidden" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][field_id]" value="rbfw_variation_id_<?php echo esc_attr($i); ?>">
                                        </td>
                                        <td>
                                            <table class="rbfw_variations_value_table">
                                                <thead>
                                                    <th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                    <th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                    <th>
                                                        <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
                                                            <span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                        </div>
                                                    </th>
                                                    <th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                </thead>
                                                <tbody class="rbfw_variations_value_table_tbody">
                                                    <?php
                                                    $c = 0;
                                                    foreach ($rbfw_variations_data[$i]['value'] as $key => $value):
                                                    ?>
                                                    <tr class="rbfw_variations_value_table_row" data-key="<?php echo esc_attr($c); ?>">
                                                        <td>
                                                            <input type="text" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][value][<?php echo esc_attr($c); ?>][name]" value="<?php echo esc_attr($value['name']); ?>" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][value][<?php echo esc_attr($c); ?>][quantity]" value="<?php echo esc_attr($value['quantity']); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][selected_value]" value="<?php echo esc_attr($value['name']); ?>" class="rbfw_variation_selected_value" <?php if($value['name'] == $selected_value){ echo 'checked'; } ?>>
                                                        </td>
                                                        <td>
                                                        <div class="mp_event_remove_move">
                                                            <button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
                                                            <div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div>
                                                        </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $c++;
                                                    endforeach;
                                                    ?>
                                                </tbody>
                                            </table>
                                            <hr>
                                            <button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                        </td>
                                        <td>
                                            <div class="mp_event_remove_move">
                                                <button class="button remove-rbfw_variations_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
                                                <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                $i++;
                                endforeach;
                                else:
                                ?>
                                    <tr class="rbfw_variations_table_row" data-key="0">
                                        <td>
                                            <input type="text" name="rbfw_variations_data[0][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                            <input type="hidden" name="rbfw_variations_data[0][field_id]" value="rbfw_variation_id_0">
                                        </td>
                                        <td>
                                            <table class="rbfw_variations_value_table">
                                                <thead>
                                                    <th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                    <th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                    <th>
                                                        <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?>
                                                        <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i>
                                                            <span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                                                        </div>
                                                    </th>
                                                    <th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
                                                </thead>
                                                <tbody class="rbfw_variations_value_table_tbody">
                                                    <tr class="rbfw_variations_value_table_row" data-key="0">
                                                        <td>
                                                            <input type="text" name="rbfw_variations_data[0][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="rbfw_variations_data[0][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" name="rbfw_variations_data[0][selected_value]"  class="rbfw_variation_selected_value">
                                                        </td>
                                                        <td>
                                                        <div class="mp_event_remove_move">
                                                            <button class="button remove-rbfw_variations_value_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
                                                            <div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div>
                                                        </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <hr>
                                            <button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                        </td>
                                        <td>
                                            <div class="mp_event_remove_move">
                                                <button class="button remove-rbfw_variations_table_row" type="button"><span class="dashicons dashicons-trash" ></span></button>
                                                <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>

                            <button id="add-new-variation" class="ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Variation', 'booking-and-rental-manager-for-woocommerce' ); ?></button>

                        </div>
                    </div>
                </div>
                <script>
                    jQuery(document).ready(function(){

                        jQuery('#add-new-variation').click(function (e) {
                            e.preventDefault();

                            if(jQuery('.rbfw_variations_table .rbfw_variations_table_row').length > 0){
                                let rbfw_variations_table_last_row = jQuery('.rbfw_variations_table .rbfw_variations_table_row:last-child()');
                                let rbfw_variations_table_last_data_key = parseInt(rbfw_variations_table_last_row.attr('data-key'));
                                let rbfw_variations_table_new_data_key = rbfw_variations_table_last_data_key + 1;
                                let rbfw_variations_table_row = '<tr class="rbfw_variations_table_row" data-key="'+rbfw_variations_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"><input type="hidden" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_id]" value="rbfw_variation_id_'+rbfw_variations_table_new_data_key+'"></td><td><table class="rbfw_variations_value_table"><thead><th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th> <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?> <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i><span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></th><th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th></thead><tbody class="rbfw_variations_value_table_tbody"><tr class="rbfw_variations_value_table_row" data-key="0"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr></tbody></table><hr><button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
                                let rbfw_variations_table_add_new_row = jQuery('.rbfw_variations_table').append(rbfw_variations_table_row);
                            }
                            else{
                                let rbfw_variations_table_new_data_key = 0;
                                let rbfw_variations_table_row = '<tr class="rbfw_variations_table_row" data-key="'+rbfw_variations_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"><input type="hidden" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_id]" value="rbfw_variation_id_'+rbfw_variations_table_new_data_key+'"></td><td><table class="rbfw_variations_value_table"><thead><th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th><th> <?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?> <div class="rbfw_tooltip"><i class="fa-solid fa-circle-info"></i><span class="rbfw_tooltiptext"><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div></th><th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th></thead><tbody class="rbfw_variations_value_table_tbody"><tr class="rbfw_variations_value_table_row" data-key="0"><td><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr></tbody></table><hr><button class="add-new-variation-value ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
                                let rbfw_variations_table_add_new_row = jQuery('.rbfw_variations_table').append(rbfw_variations_table_row);
                            }

                            rbfw_variation_table_action_btns_func();
                            rbfw_add_new_variation_value();
                            rbfw_variation_selected_value_func();
                        });

                        /* Start: Add New Variation Value */

                        rbfw_add_new_variation_value();
                        function rbfw_add_new_variation_value(){

                            jQuery('.add-new-variation-value').click(function (e) {
                                let this_btn = jQuery(this);
                                e.preventDefault();
                                e.stopImmediatePropagation();


                                let c = jQuery('.rbfw_variations_table .rbfw_variations_table_row:last-child()');
                                c = parseInt(c.attr('data-key'));


                                if(jQuery(this_btn).siblings('.rbfw_variations_value_table').find('.rbfw_variations_value_table_row').length > 0){

                                    let rbfw_variations_value_table_last_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').find('.rbfw_variations_value_table_row:last-child()');
                                    let rbfw_variations_value_table_last_data_key = parseInt(rbfw_variations_value_table_last_row.attr('data-key'));
                                    let rbfw_variations_value_table_new_data_key = rbfw_variations_value_table_last_data_key + 1;
                                    let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="'+rbfw_variations_value_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+c+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
                                    let rbfw_variations_value_table_add_new_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').append(rbfw_variations_value_table_row);

                                }else{

                                    let rbfw_variations_value_table_new_data_key = 0;
                                    let rbfw_variations_value_table_row = '<tr class="rbfw_variations_value_table_row" data-key="'+rbfw_variations_value_table_new_data_key+'"><td><input type="text" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][name]" placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>" class="rbfw_variation_value"></td><td><input type="number" name="rbfw_variations_data['+c+'][value]['+rbfw_variations_value_table_new_data_key+'][quantity]" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="checkbox" name="rbfw_variations_data['+c+'][selected_value]" class="rbfw_variation_selected_value"></td><td><div class="mp_event_remove_move"><button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
                                    let rbfw_variations_value_table_add_new_row = jQuery(this_btn).siblings('.rbfw_variations_value_table').append(rbfw_variations_value_table_row);
                                }

                                rbfw_variation_table_action_btns_func();

                                rbfw_variation_selected_value_func();
                            });
                        }

                        /* End: Add New Variation Value */

                        /* Start: Variation Default Value: Note: It works for frontend select box */
                        rbfw_variation_selected_value_func();
                        function rbfw_variation_selected_value_func(){

                            jQuery('.rbfw_variation_selected_value').on('change', function() {
                                jQuery(this).parents('.rbfw_variations_value_table_tbody').find('.rbfw_variation_selected_value').not(this).prop('checked', false);
                            });

                            jQuery('.rbfw_variation_value').keyup(function() {
                                let	this_field = jQuery(this);
                                let	this_val = jQuery(this).val();
                                jQuery(this_field).parent('td').siblings('td').find('.rbfw_variation_selected_value').val(this_val);
                            });
                        }
                        /* End: Variation Default Value */

                        /* Start: variation table action buttons function */
                        rbfw_variation_table_action_btns_func();
                        function rbfw_variation_table_action_btns_func(){
                            jQuery('.remove-rbfw_variations_table_row').on('click', function (e) {
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                                    jQuery(this).parents('tr').remove();
                                } else {
                                    return false;
                                }
                            });

                            jQuery('.remove-rbfw_variations_value_table_row').on('click', function (e) {
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                                    jQuery(this).parents('tr.rbfw_variations_value_table_row').remove();
                                } else {
                                    return false;
                                }
                            });

                            jQuery( ".rbfw_variations_table_body" ).sortable({ handle: ".mp_event_type_sortable_button" });
                            jQuery( ".rbfw_variations_value_table_tbody" ).sortable({ handle: ".rbfw_variations_value_table_row_sortable" });
                        }
                        /* End: variation table action buttons function */

                    });
                    </script>
            <?php
            }
        }

        new RBFW_Variations();
    }