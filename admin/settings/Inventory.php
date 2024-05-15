<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Inventory')) {
        class RBFW_Inventory{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu($rbfw_id) {
                $rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';
	            $rbfw_enable_variations = get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_variations', true ) : 'no';
	
            ?>
                <li class="<?php echo $rbfw_enable_variations ?>" data-target-tabs="#rbfw_variations" <?php if( $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display:none"'; }?>><i class="fa-solid fa-table-cells-large"></i><?php esc_html_e('Inventory', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Inventory Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure Inventory Settings.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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

            public function variation_settings($post_id){
                $rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
                $rbfw_variations_data = get_post_meta( $post_id, 'rbfw_variations_data', true ) ? get_post_meta( $post_id, 'rbfw_variations_data', true ) : [];
            ?>
                <section class="rbfw_variations_table_wrap <?php echo esc_attr(($rbfw_enable_variations == 'yes')? 'show':'hide'); ?>">
                    <div class="form-table rbfw_variations_table">

                        <tbody class="rbfw_variations_table_body ui-sortable">
                        <?php
                        if(! empty($rbfw_variations_data)) :
                        $i = 0;
                        foreach ($rbfw_variations_data as $key => $value):
                            $selected_value = !empty($value['selected_value']) ? $value['selected_value'] : '';
                        ?>
                            <div class="rbfw_variations_table_row" data-key="<?php echo esc_attr($i); ?>">

                                <header>
                                    <label for="">Field Label</label>
                                    <div>
                                        <input type="text" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][field_label]" value="<?php echo esc_attr( $value['field_label'] ); ?>" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                        <input type="hidden" name="rbfw_variations_data[<?php echo esc_attr($i); ?>][field_id]" value="rbfw_variation_id_<?php echo esc_attr($i); ?>">
                                    </div>
                                </header>
                                <div class=variations-inner-table>
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
                                                    <button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button>
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
                                </div>
                                <div class="mp_event_remove_move">
                                    <button class="remove-rbfw_variations_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button>
                                    <!-- <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div> -->
                                </div>
                            </div>
                        <?php
                        $i++;
                        endforeach;
                        else:
                        ?>
                            <div class="rbfw_variations_table_row" data-key="0">
                                <header>
                                    <label for="">Field Label</label>
                                    <div>
                                        <input type="text" name="rbfw_variations_data[0][field_label]" placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>">
                                        <input type="hidden" name="rbfw_variations_data[0][field_id]" value="rbfw_variation_id_0">
                                    </div>
                                </header>
                                <div class="variations-inner-table">
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
                                                    <button class="button remove-rbfw_variations_value_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button>
                                                    <div class="button rbfw_variations_value_table_row_sortable"><i class="fas fa-arrows-alt"></i></div>
                                                </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table> 
                                    <button class="add-new-variation-value ppof-button mt-2"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                                </div>
                                <div class="mp_event_remove_move">
                                    <button class="remove-rbfw_variations_table_row" type="button"><i class="fa-solid fa-trash-can"></i></button>
                                    <!-- <div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div> -->
                                </div>
                            </div>
                        <?php endif; ?>
                        </tbody>
                    </div>
                    <button id="add-new-variation" class="ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Variation', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
                </section>
            <?php
            }

            public function stock_settings($post_id){
				$rbfw_item_stock_quantity  = get_post_meta( $post_id, 'rbfw_item_stock_quantity', true ) ? get_post_meta( $post_id, 'rbfw_item_stock_quantity', true ) : '';
            ?>
                <section>
                    <div>
                        <label>
                            <?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>
                        </label>
                        <span><?php esc_html_e( 'Add stock quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                    </div>
                    <div>
                        <input type="number" name="rbfw_item_stock_quantity" id="rbfw_item_stock_quantity" value="<?php echo esc_attr($rbfw_item_stock_quantity); ?>">
                    </div>
                </section>
            <?php
            }


            public function quantity_box_toggle($post_id){
				$rbfw_enable_md_type_item_qty = get_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', true ) : 'no';

                ?>
                    <section >
                        <div>
                            <label><?php _e( 'Enable Multiple Item Quantity Box Display in Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
                            <span><?php  _e( 'It enables the multiple item quantity selection option. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_md_type_item_qty" value="<?php echo esc_attr($rbfw_enable_md_type_item_qty); ?>" <?php echo esc_attr(($rbfw_enable_md_type_item_qty=='yes')?'checked':''); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>
                <?php
            }
            public function variation_table_switch_on_off($post_id){
				$rbfw_enable_variations = get_post_meta( $post_id, 'rbfw_enable_variations', true ) ? get_post_meta( $post_id, 'rbfw_enable_variations', true ) : 'no';
            ?>
                
                <section >
					<div>
						<label><?php _e( 'Item variation', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span><?php  _e( 'Enable/Disable Variations. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.','booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<label class="switch">
						<input type="checkbox" name="rbfw_enable_variations" value="<?php echo esc_attr($rbfw_enable_variations); ?>" <?php echo esc_attr(($rbfw_enable_variations=='yes')?'checked':''); ?>>
						<span class="slider round"></span>
					</label>
				</section>
            <?php
            }

            public function add_tabs_content( $post_id ) {
                $rbfw_item_type = get_post_meta( $post_id, 'rbfw_item_type', true ) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : '';
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_variations" data-tab-item="#rbfw_variations" <?php if($rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
                    <?php $this->section_header(); ?>
					<?php $this->panel_header('Inventory Settings','Inventory Settings'); ?>
                    <?php $this->stock_settings($post_id); ?>
                    <?php $this->quantity_box_toggle($post_id); ?>
                    <?php $this->variation_table_switch_on_off($post_id); ?>
                    <?php $this->variation_settings($post_id); ?>
                </div>
                <script>

                    jQuery('input[name=rbfw_enable_variations]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
                            jQuery('.rbfw_variations_table_wrap').slideUp().removeClass('show').addClass('hide');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
                            jQuery('.rbfw_variations_table_wrap').slideDown().removeClass('hide').addClass('show');
						}
					});

                    jQuery('input[name=rbfw_enable_md_type_item_qty]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
						}
					});

                    jQuery(document).ready(function(){
                        jQuery('#add-new-variation').click(function (e) {
                            e.preventDefault();
                            if(jQuery('.rbfw_variations_table .rbfw_variations_table_row').length > 0){
                                let rbfw_variations_table_last_row = jQuery('.rbfw_variations_table .rbfw_variations_table_row:last-child()');
                                let rbfw_variations_table_last_data_key = parseInt(rbfw_variations_table_last_row.attr('data-key'));
                                let rbfw_variations_table_new_data_key = rbfw_variations_table_last_data_key + 1;
                                let rbfw_variations_table_row = '<div class=rbfw_variations_table_row data-key="'+rbfw_variations_table_new_data_key+'"><header><label for="">Field Label</label><div><input type="text" name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_label]"placeholder="<?php esc_attr_e( 'Field Label', 'booking-and-rental-manager-for-woocommerce' ); ?>"> <input name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][field_id]"type=hidden value="rbfw_variation_id_'+rbfw_variations_table_new_data_key+'"></div></header><div class=variations-inner-table><table class=rbfw_variations_value_table><thead><th><?php esc_html_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?><th><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?><th><?php esc_html_e( 'Is Default ', 'booking-and-rental-manager-for-woocommerce' ); ?><div class=rbfw_tooltip><i class="fa-solid fa-circle-info"></i><span class=rbfw_tooltiptext><?php esc_html_e( 'The selected value will be set as a default value in the front end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></div><th><?php esc_html_e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?><tbody class=rbfw_variations_value_table_tbody><tr class=rbfw_variations_value_table_row data-key=0><td><input name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][name]"placeholder="<?php esc_attr_e( 'Value Name', 'booking-and-rental-manager-for-woocommerce' ); ?>"class=rbfw_variation_value><td><input name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][value][0][quantity]"placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"type=number><td><input name="rbfw_variations_data['+rbfw_variations_table_new_data_key+'][selected_value]"type=checkbox class=rbfw_variation_selected_value><td><div class=mp_event_remove_move><button class="button remove-rbfw_variations_value_table_row"type=button><i class="fa-solid fa-trash-can"></i></button><div class="button rbfw_variations_value_table_row_sortable"><i class="fa-arrows-alt fas"></i></div></div></table><button class="add-new-variation-value mt-2 ppof-button"><i class="fa-solid fa-circle-plus"></i><?php esc_html_e( 'Add New Value', 'booking-and-rental-manager-for-woocommerce' ); ?></button></div><div class=mp_event_remove_move><button class=remove-rbfw_variations_table_row type=button><i class="fa-solid fa-trash-can"></i></button></div></div>';
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
                                    jQuery(this).parent().parent().remove();
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

            public function settings_save($post_id) {
                
                if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( $_POST['rbfw_ticket_type_nonce'], 'rbfw_ticket_type_nonce' ) ) {
                    return;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return;
                }
                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return;
                }
                if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$rbfw_enable_variations = isset( $_POST['rbfw_enable_variations'] ) ? $_POST['rbfw_enable_variations']  : 'no';
					$rbfw_item_stock_quantity = isset( $_POST['rbfw_item_stock_quantity'] ) ? $_POST['rbfw_item_stock_quantity']  : '';
					$rbfw_enable_md_type_item_qty  = isset( $_POST['rbfw_enable_md_type_item_qty'] ) ? $_POST['rbfw_enable_md_type_item_qty'] : 'no';
                    $rbfw_variations_data 	 = isset( $_POST['rbfw_variations_data'] ) ? rbfw_array_strip( $_POST['rbfw_variations_data'] ) : [];
					
                    update_post_meta( $post_id, 'rbfw_enable_md_type_item_qty', $rbfw_enable_md_type_item_qty );				
					update_post_meta( $post_id, 'rbfw_enable_variations', $rbfw_enable_variations );
					update_post_meta( $post_id, 'rbfw_item_stock_quantity', $rbfw_item_stock_quantity );
                    update_post_meta( $post_id, 'rbfw_variations_data', $rbfw_variations_data );

					
                }
            }
        }

        new RBFW_Inventory();
    }