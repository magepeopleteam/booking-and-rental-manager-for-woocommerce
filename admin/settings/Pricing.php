<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Pricing')) {
        class RBFW_Pricing{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#travel_pricing"><i class="fa-solid fa-pager"></i><?php esc_html_e('Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

			public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure price.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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


			public function rent_type($post_id){
				?>
				<?php $this->panel_header('Price Settings','Price Settings'); ?>
				<section>
					<div>
						<label for="">
							<?php _e('Rent Types', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</label>
						<span><?php _e('Price will be changed based on this type selection', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<?php  $rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd'; ?>
					<?php $item_type = [
						'bike_car_sd' => 'Bike/Car for single day',
						'bike_car_md' => 'Bike/Car for multiple day',
						'resort' => 'Resort',
						'equipment' => 'Equipment',
						'dress' => 'Dress',
						'appointment' => 'Appointment',
						'others' => 'Others',
					]; ?>
					<select name="rbfw_item_type" id="rbfw_item_type">
						<?php foreach($item_type as $kay => $value): ?>
							<option <?php echo ($kay==$rbfw_item_type)?'selected':'' ?> value="<?php echo $kay; ?>"> <?php echo $value; ?> </option>
						<?php endforeach; ?>
					</select>
				</section>
			<?php
			}

			public function field_service_price( $option ){
				$id 			= isset( $option['id'] ) ? $option['id'] : "";
				if(empty($id)) return;
	
				$field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
				$conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
				$placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
				$remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fa-solid fa-trash-can"></i>';
				$sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
				$default 	    = isset( $option['default'] ) ? $option['default'] : array();
	
				$values 	    = isset( $option['value'] ) ? $option['value'] : array();
				$values         = !empty($values) ? $values : $default;
				$limit 	        = !empty( $option['limit'] ) ? $option['limit'] : '';
	
	
	
				$field_id       = $id;
				$field_name     = !empty( $field_name ) ? $field_name : $id;
	
				ob_start();
				?>
	
				<table class="form-table rbfw_service_category_table">
					<tbody class="sortable_tr">
					<?php
					if(!empty($values)):
						$i = 0;
					foreach ($values as $value):?>
						<tr data-cat="<?php echo $i; ?>">
							<td>
								<div class="services_category_wrapper">
									<div class="field-list <?php echo esc_attr($field_id); ?>">
										<div class="service_category_inner_wrap">
											<div class="service_category_title">
												<label class=" mb-1">
													<?php echo esc_html('Service Category Title','booking-and-rental-manager-for-woocommerce'); ?>
												</label>
												<input type="text" value="<?php echo esc_attr($value['cat_title']); ?>" name="rbfw_service_category_price[<?php echo $i; ?>][cat_title]" data-key="<?php echo $i; ?>" placeholder="<?php echo esc_attr__('Service Category Label','booking-and-rental-manager-for-woocommerce'); ?>"/>
											</div>
											<div class="service_category_inner_item_wrap sortable">
												<?php
												if(!empty($value['cat_services'])){
													$c = 0;
													foreach ($value['cat_services'] as $service) {
														$icon = $service['icon'];
														$title = $service['title'];
														$price = $service['price'];
														$stock_quantity = isset($service['stock_quantity'])?$service['stock_quantity']:'';
														$service_price_type = $service['service_price_type'];
														?>
															<div class="item">
																<a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="<?php echo $c; ?>"><i class="fa-solid fa-circle-plus"></i> <?php echo esc_html__('Icon','booking-and-rental-manager-for-woocommerce'); ?></a>
																<div class="rbfw_service_icon_preview p-1" data-key="<?php echo $c; ?>"><?php if($icon){ echo '<i class="'.$icon.'"></i>'; } ?></div>

																<input type='hidden' name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][icon]' placeholder='<?php echo esc_attr__('Icon','booking-and-rental-manager-for-woocommerce'); ?>' value='<?php echo esc_attr($icon); ?>' data-key="<?php echo $c; ?>" class="rbfw_service_icon"/>
																<input type='text' name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][title]'  placeholder='<?php echo esc_attr($placeholder); ?>' value="<?php  echo esc_attr($title); ?>" data-key="<?php echo $c; ?>"/>

																<input type='text' class="medium" name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][price]'  placeholder='<?php echo __('Price','booking-and-rental-manager-for-woocommerce'); ?>' value='<?php  echo esc_attr($price); ?>'  data-key="0"/>

																<input type='text' class="medium" name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][stock_quantity]'  placeholder='<?php echo __('Stock','booking-and-rental-manager-for-woocommerce'); ?>' value='<?php  echo esc_attr($stock_quantity); ?>'  data-key="0"/>

																<label class="" for="rbfw_dt_sidebar_switch-on">
																	<input name="rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][service_price_type]" type="radio" <?php echo ($service_price_type=='one_time')?'checked':''  ?> id="rbfw_dt_sidebar_switch-on" value="one_time">
																	<span class="sw-button"> One Time</span>
																</label>
																<label class="checked" for="rbfw_dt_sidebar_switch-off">
																	<input name="rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][service_price_type]" type="radio" <?php echo ($service_price_type=='day_wise')?'checked':''  ?> id="rbfw_dt_sidebar_switch-off" value="day_wise">
																	<span class="sw-button"> Day Wise</span>
																</label>
																<div>
																	<?php if($sortable):?>
																		<span class="button sort"><i class="fas fa-arrows-alt"></i></span>
																	<?php endif; ?>
																	<span class="button remove" onclick="jQuery(this).parent().remove()"><?php echo ($remove_text); ?></span>
																</div>
															</div>
															<?php
															$c++;
														}
													}
													?>
												</div>
											</div>
										</div>
										<span class="ppof-button add-new-service">
											<i class="fa-solid fa-circle-plus"></i>
											<?php echo __('Add New Service','booking-and-rental-manager-for-woocommerce'); ?>
										</span>
								</div>
							</td>
							<td>
								<?php if($sortable):?>
									<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
								<?php endif; ?>
								<span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()"><?php echo ($remove_text); ?></span>
							</td>
						</tr>
						<?php
						$i++;
						endforeach;
					else:
						?>

						<tr data-cat="0">
							<td>
								<div class="services_category_wrapper">
									<div class="field-list <?php echo esc_attr($field_id); ?>">
										<div class="service_category_inner_wrap">
											<div class="service_category_title">
												<label>
													<?php echo esc_html('Service Category Title','booking-and-rental-manager-for-woocommerce'); ?>
												</label>
												<input type="text" name="rbfw_service_category_price[0][cat_title]" data-key="0" placeholder="<?php echo esc_attr__('Service Category Label','booking-and-rental-manager-for-woocommerce'); ?>"/>
											</div>
											<div class="service_category_inner_item_wrap sortable">

												<div class="item">

													<a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="0">
														<i class="fa-solid fa-circle-plus"></i>
														<?php echo esc_html__('Icon','booking-and-rental-manager-for-woocommerce'); ?>
													</a>
													<div class="rbfw_service_icon_preview p-1" data-key="0"></div>
													<input type='hidden' name='rbfw_service_category_price[0][cat_services][0][icon]' placeholder='<?php echo esc_attr__('Icon','booking-and-rental-manager-for-woocommerce'); ?>' data-key="0" class="rbfw_service_icon"/>
													<input type='text' name='rbfw_service_category_price[0][cat_services][0][title]'  placeholder='<?php echo esc_attr($placeholder); ?>' value='' data-key="0"/>

													<input type='text' class="medium" name='rbfw_service_category_price[0][cat_services][0][price]'  placeholder='<?php echo __('Price','booking-and-rental-manager-for-woocommerce'); ?>' value='' data-key="0"/>

													<input type='text' class="medium" name='rbfw_service_category_price[0][cat_services][0][stock_quantity]'  placeholder='<?php echo __('Stock','booking-and-rental-manager-for-woocommerce'); ?>' value=''  data-key="0"/>

													<label class="" for="rbfw_dt_sidebar_switch-on">
														<input name="rbfw_service_category_price[0][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time">
														<span class="sw-button"> One Time</span>
													</label>
													<label class="checked" for="rbfw_dt_sidebar_switch-off">
														<input name="rbfw_service_category_price[0][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked="">
														<span class="sw-button"> Day Wise</span>
													</label>

													<div>
														<?php if($sortable):?>
															<span class="button sort">
																<i class="fas fa-arrows-alt"></i>
															</span>
														<?php endif; ?>
														<span class="button remove" onclick="jQuery(this).parent().remove()">
															<?php echo ($remove_text); ?>
														</span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<span class="ppof-button add-new-service">
										<i class="fa-solid fa-circle-plus"></i>
										<?php echo __('Add New Feature','booking-and-rental-manager-for-woocommerce'); ?>
									</span>
								</div>
							</td>
							<td>
								<?php if($sortable):?>
									<span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
								<?php endif; ?>
								<span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()">
									<?php echo ($remove_text); ?>
								</span>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
				<span class="ppof-button add-service-category mt-1">
					<i class="fa-solid fa-circle-plus"></i>
					<?php echo __('Add New Service Category','booking-and-rental-manager-for-woocommerce'); ?>
				</span>
				<script>
					jQuery( ".sortable_tr" ).sortable({ handle: '.tr_sort_handler' });
					jQuery('.tr_remove').click(function (e) {
						jQuery(this).closest("tr").remove();
					});
	
					jQuery(document).on('click', '.add-service-category',function(e){
						e.stopImmediatePropagation();
						let dataCat = jQuery('.rbfw_service_category_table tbody tr:last-child').attr('data-cat');
						let nextCat = parseInt(dataCat) + 1;
						let html = '<tr data-cat="'+nextCat+'"><td><div class="services_category_wrapper"><div class="field-list rbfw_service_category_price"><div class="service_category_inner_wrap"><div class="service_category_title"><label><?php echo esc_html('Feature Category Title','booking-and-rental-manager-for-woocommerce'); ?></label><input type="text" class="rbfw_service_category_title" name="rbfw_service_category_price['+nextCat+'][cat_title]" data-cat="'+nextCat+'" placeholder="<?php echo esc_attr__('Feature Category Label','booking-and-rental-manager-for-woocommerce'); ?>"></div>';
						html +='<div class="service_category_inner_item_wrap sortable"><div class="item"><a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="0"><i class="fa-solid fa-circle-plus"></i> <?php echo esc_html__('Icon','booking-and-rental-manager-for-woocommerce'); ?></a><div class="rbfw_service_icon_preview p-1" data-key="0"></div><input type="hidden" name="rbfw_service_category_price['+nextCat+'][cat_services][0][icon]" placeholder="<?php echo esc_attr__('Icon','booking-and-rental-manager-for-woocommerce'); ?>" data-key="0" class="rbfw_service_icon">';
						html +='<input type="text" name="rbfw_service_category_price['+nextCat+'][cat_services][0][title]" placeholder="<?php echo esc_attr($placeholder); ?>" value="" data-key="0">';
						html +='<input type="text" class="medium" name="rbfw_service_category_price['+nextCat+'][cat_services][0][price]" placeholder="Price" value="" data-key="0">';
						html +='<input type="text" class="medium" name="rbfw_service_category_price['+nextCat+'][cat_services][0][stock]" placeholder="Stock" value="" data-key="0">';
						html += '<label class="" for="rbfw_dt_sidebar_switch-on"> <input name="rbfw_service_category_price['+ nextCat +'][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time"> <span class="sw-button"> One Time</span> </label>';
						html += '<label class="checked" for="rbfw_dt_sidebar_switch-off"> <input name="rbfw_service_category_price['+ nextCat +'][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked=""> <span class="sw-button"> Day Wise</span> </label>';
	
						html +='<div><?php if($sortable):?><span class="button sort"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button remove" onclick="jQuery(this).parent().remove()"><?php echo ($remove_text); ?></span></div></div></div></div></div><span class="ppof-button add-new-service"><i class="fa-solid fa-circle-plus"></i> <?php echo __('Add New Feature','booking-and-rental-manager-for-woocommerce'); ?></span></div></td><td> <?php if($sortable):?> <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button tr_remove"><?php echo ($remove_text); ?></span></td></tr>';
						jQuery('.rbfw_service_category_table tbody').append(html);
						jQuery( ".sortable_tr" ).sortable({ handle: '.tr_sort_handler' });
						jQuery('.tr_remove').click(function (e) { jQuery(this).closest("tr").remove();});
					});
	
					jQuery(document).on('click', '.add-new-service',function(e){
						e.stopImmediatePropagation();
						let data_key = jQuery(this).siblings(".rbfw_service_category_price").find("div.item:last-child input").attr('data-key');
						let i = parseInt(data_key);
						let c = i + 1;
						let theTarget = jQuery(this).siblings('.rbfw_service_category_price').find('.service_category_inner_wrap .service_category_inner_item_wrap');
						jQuery( ".sortable" ).sortable({ handle: '.sort' });
						let dataCat = jQuery(this).closest('tr').attr('data-cat');
	
						html = '<div class="item">';
	
						html += '<a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="'+ c +'"><i class="fa-solid fa-circle-plus"></i> <?php echo esc_html__('Icon','booking-and-rental-manager-for-woocommerce'); ?></a>';
						html += '<div class="rbfw_service_icon_preview p-1" data-key="'+ c +'"></div>';
						html += '<input type="hidden" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][icon]" placeholder="<?php echo esc_html__('Icon','booking-and-rental-manager-for-woocommerce'); ?>" data-key="'+ c +'" class="rbfw_service_icon"/>';
	
						html += '<input type="text" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][title]" placeholder="<?php echo esc_attr($placeholder); ?>" data-key="'+ c +'"/>';
						html += '<input type="text" class="medium" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][price]" placeholder="Price" data-key="'+ c +'"/>';
						html += '<input type="text" class="medium" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][stock]" placeholder="Stock" data-key="'+ c +'"/>';
	
						html += '<label class="" for="rbfw_dt_sidebar_switch-on"> <input name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time"> <span class="sw-button"> One Time</span> </label>';
						html += '<label class="checked" for="rbfw_dt_sidebar_switch-off"> <input name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked=""> <span class="sw-button"> Day Wise</span> </label>';
	
						<?php if($sortable):?>
						html += '<div>';
						html += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
						<?php endif; ?>
	
						html += '<span class="button remove" onclick="jQuery(this).parent().remove()' + '"><?php echo ($remove_text); ?></span>';
						html += '</div></div>';
	
						theTarget.append(html);
					});
	
	
	
	
	
	
					// Features Icon Popup
					jQuery(document).on('click', '.rbfw_service_icon_btn',function(e){
						e.stopImmediatePropagation();
						let remove_exist_data_key 	= jQuery("#rbfw_features_icon_list_wrapper").removeAttr('data-key');
						let remove_active_label 	= jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
						let data_key 				= jQuery(this).attr('data-key');
						let data_cat 				= jQuery(this).parents('tr').attr('data-cat');
	
	
	
						jQuery('#rbfw_services_search_icon').val('');
						jQuery('.rbfw_services_icon_list_body label').show();
						jQuery("#rbfw_features_icon_list_wrapper").attr('data-key', data_key);
						jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat', data_cat);
						jQuery("#rbfw_features_icon_list_wrapper").mage_modal({
							escapeClose: false,
							clickClose: false,
							showClose: false
						});
	
						// Selected Feature Icon Action
						jQuery(document).on('click', '.ggggg label',function(e){
							e.stopImmediatePropagation();
							let selected_label 		= jQuery(this);
							let selected_val 		= jQuery('input', this).val();
							let selected_data_key 	= jQuery("#rbfw_features_icon_list_wrapper").attr('data-key');
							let selected_data_cat 	= jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat');
	
							console.log('selected_val',selected_val);
							console.log('selected_label',selected_label);
							console.log('selected_data_key',selected_data_key);
							console.log('selected_data_cat',selected_data_cat);
	
							jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
	
							jQuery('.rbfw_service_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_service_icon_preview[data-key="'+selected_data_key+'"]').empty();
							jQuery(selected_label).addClass('selected');
							jQuery('.rbfw_service_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_service_icon[data-key="'+selected_data_key+'"]').val(selected_val);
							jQuery('.rbfw_service_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_service_icon_preview[data-key="'+selected_data_key+'"]').append('<i class="'+selected_val+'"></i>');
						});
	
						// Icon Filter
						jQuery('#rbfw_services_search_icon').keyup(function (e) {
							let value = jQuery(this).val().toLowerCase();
							jQuery(".rbfw_services_icon_list_body label[data-id]").show().filter(function() {
								jQuery(this).toggle(jQuery(this).attr('data-id').toLowerCase().indexOf(value) > -1)
							}).hide();
						});
					});
	
				</script>
				<?php
				return ob_get_clean();
			}

			public function category_service_price ($post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd'; 
				$enable_service_price =  get_post_meta($post_id, 'rbfw_enable_category_service_price', true) ? get_post_meta($post_id, 'rbfw_enable_category_service_price', true) : 'off'; 
				$section_visibility = ( $rbfw_item_type == 'bike_car_sd' && $rbfw_item_type != 'appointment' && $rbfw_item_type != 'resort')?'show':'hide'; 
			?>
				<div class="rbfw_general_price_config_wrapper <?php echo esc_attr( $section_visibility); ?>">
					<?php $this->panel_header('Service price settings ','Service price settings with category.'); ?>
					<section>
                        <div>
                            <label>
                                <?php echo esc_html__( 'Enable Category service price ', 'booking-and-rental-manager-for-woocommerce' ); ?>
                            </label>
                            <span><?php echo esc_html__('You can enable/disable this section switching this button.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
                        </div>
                        <?php $dt_sidebar_switch = get_post_meta($post_id,'rbfw_dt_sidebar_switch',true);?>
                        <label class="switch">
                            <input type="checkbox" name="rbfw_enable_category_service_price" value="<?php echo esc_attr(($enable_service_price=='on')?$enable_service_price:'off'); ?>" <?php echo esc_attr(($enable_service_price=='on')?'checked':''); ?>>
                            <span class="slider round"></span>
                        </label>
                    </section>

					<div id='field-wrapper-<?php echo esc_attr('rbfw_service_category_price'); ?>' class="field-wrapper field-text-multi-wrapper field-text-multi-wrapper-rbfw_service_category_price <?php echo esc_attr(($enable_service_price=='on')?'show':'hide') ?>">
						<?php
						$options = array(
							'id'          => 'rbfw_service_category_price',
							'type'        => 'md_service_category_price',
							'placeholder'        => 'Service Name',
						);
						$option_value         = get_post_meta($post_id, $options['id'], true);
						$options['value']      = is_serialized($option_value) ? unserialize($option_value) : $option_value;
						echo $this->field_service_price($options);
						?>
					</div>
				</div>
			<?php
			}

			public function bike_car_single_day($post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd';
				$rbfw_bike_car_sd_data 	 = get_post_meta($post_id, 'rbfw_bike_car_sd_data', true) ? get_post_meta($post_id, 'rbfw_bike_car_sd_data', true) : [];
			?>
				<div class="rbfw_bike_car_sd_wrapper <?php  echo esc_attr($rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' )?'show':'hide'; ?>" >
					<section>
						<div class="w-100">
							<div style="overflow-x: auto;">
								<table class='form-table rbfw_bike_car_sd_price_table'>
									<thead>
										<tr>
											<th><?php _e( 'Type', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
											<th><?php _e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
											<th><?php _e( 'Price <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
											<th class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><?php _e( 'Stock/Day <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
											<th class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><?php _e( 'Action', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
										</tr>
									</thead>
									<tbody class="rbfw_bike_car_sd_price_table_body">
									<?php
									
									if(! empty($rbfw_bike_car_sd_data)) :
									$i = 0;
									foreach ($rbfw_bike_car_sd_data as $key => $value):
									?>
										<tr class="rbfw_bike_car_sd_price_table_row" data-key="<?php echo mep_esc_html($i); ?>">
											<td><input type="text" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][rent_type]" value="<?php echo esc_attr( $value['rent_type'] ); ?>" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

											<td><input type="text" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][short_desc]" value="<?php echo esc_attr( $value['short_desc'] ); ?>" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

<<<<<<< HEAD
										<td><input type="number" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][price]" step=".01" value="<?php echo esc_attr( $value['price'] ); ?>" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>
=======
											<td><input type="number" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][price]" value="<?php echo esc_attr( $value['price'] ); ?>" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>
>>>>>>> 272b0a19b76087771273638707f3525565c38ab7

											<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>><input class="medium" type="number" name="rbfw_bike_car_sd_data[<?php echo mep_esc_html($i); ?>][qty]" value="<?php echo esc_attr( $value['qty'] ); ?>" placeholder="<?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

											<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
												<div class="mp_event_remove_move">
													<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
												</div>
											</td>
										</tr>
									<?php
									$i++;
									endforeach;
									else:
									?>
										<tr class="rbfw_bike_car_sd_price_table_row" data-key="0">
											<td><input type="text" name="rbfw_bike_car_sd_data[0][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

											<td><input type="text" name="rbfw_bike_car_sd_data[0][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

<<<<<<< HEAD
										<td><input type="number" name="rbfw_bike_car_sd_data[0][price]" step=".01" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>
=======
											<td><input type="number" name="rbfw_bike_car_sd_data[0][price]" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>
>>>>>>> 272b0a19b76087771273638707f3525565c38ab7

											<td class="rbfw_bike_car_sd_price_table_action_column" <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?> ><input class="medium" type="number" name="rbfw_bike_car_sd_data[0][qty]" value="" placeholder="<?php esc_html_e( '(Quantity/Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?>" /></td>

											<td class="rbfw_bike_car_sd_price_table_action_column"<?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
												<div class="mp_event_remove_move">
													<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
												</div>
											</td>
										</tr>
									<?php endif; ?>
									</tbody>
								</table>
							</div>
							<p class="mt-2 <?php echo esc_attr($rbfw_item_type == 'appointment'? 'show':'show'); ?>" >
								<button id="add-bike-car-sd-type-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Type', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							</p>
						</div>
					</section>

				</div>
			<?php
			}

			public function extra_service_table($post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd';
				$rbfw_extra_service_data = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
				$rbfw_enable_extra_service_qty = get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $post_id, 'rbfw_enable_extra_service_qty', true ) : 'no';
				
			?>
				<div class="rbfw_es_price_config_wrapper " <?php if($rbfw_item_type == 'appointment'){ echo 'style="display:none"'; } ?>>
					<?php $this->panel_header('Extra Service Price Settings','Extra Service Price Settings'); ?>

					<section>
						<div class="w-100">
							<div style="overflow-x: auto;">
							<table class='rbfw_pricing_table form-table w-100' id="repeatable-fieldset-one" >
								<thead>
								<tr>
									<th><?php _e( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php _e( 'Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php _e( 'Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php _e( 'Price <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php _e( 'Stock Quantity <b class="required">*</b>', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<!--<th><?php _e( 'Qty Box', 'booking-and-rental-manager-for-woocommerce' ); ?></th>-->
									<th></th>
								</tr>
								</thead>
								<tbody class="mp_event_type_sortable">
								<?php
									if ( !empty($rbfw_extra_service_data) ) :
										foreach ( $rbfw_extra_service_data as $field ) {

											if(!empty($field['service_img'])){

												$service_img = !empty($field['service_img']) ? esc_attr( $field['service_img'] ) : '';
												$img_url = wp_get_attachment_url($service_img);

											} else {

												$service_img = '';
												$img_url = '';
											}


											$service_name     = array_key_exists( 'service_name', $field ) ? esc_attr( $field['service_name'] ) : '';
											$service_price    = array_key_exists( 'service_price', $field ) ? esc_attr( $field['service_price'] ) : '';

											$service_desc    = array_key_exists( 'service_desc', $field ) ? esc_attr( $field['service_desc'] ) : '';

											$service_qty      = array_key_exists( 'service_qty', $field ) ? esc_attr( $field['service_qty'] ) : '';
											$service_qty_type = array_key_exists( 'service_qty_type', $field ) ? esc_attr( $field['service_qty_type'] ) : 'inputbox';
											?>
											<tr>
												<td>
													<div class="rbfw_service_image_wrap text-center">
														<div class="rbfw_service_image_preview">
														<?php if($img_url): ?>
															<img src="<?php echo esc_url($img_url); ?>">
														<?php endif; ?>
														</div>
														<div class="service_image_add_remove">
															<a class="rbfw_service_image_btn button"><i class="fa-solid fa-circle-plus"></i></a><a class="rbfw_remove_service_image_btn btn"><i class="fa-solid fa-circle-minus"></i></a>
															<input type="hidden" name="service_img[]" value="<?php echo esc_attr( $service_img ); ?>" class="rbfw_service_image"/>
														</div>
													</div>
												</td>
												<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap" value="<?php echo esc_html( $service_name ); ?>"/></td>
												<td><input type="text"  class="mp_formControl" name="service_desc[]" placeholder="Service Description" value="<?php echo esc_html( $service_desc ); ?>"/></td>
												<td><input type="number" class="medium" step="0.01" class="mp_formControl" name="service_price[]" placeholder="Ex: 10" value="<?php echo esc_html( $service_price ); ?>"/></td>
												<td><input type="number" class="medium" name="service_qty[]" placeholder="Ex: 100" value="<?php echo esc_html( $service_qty ); ?>"/></td>
												<td>
													<div class="mp_event_remove_move">
														<button class="button remove-row" type="button"><i class="fa-solid fa-trash-can"></i></button>
														<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
													</div>
												</td>
											</tr>
											<?php
										}

									endif;
								?>
								<!-- empty hidden one for jQuery -->
								<tr class="empty-row screen-reader-text">
									<td >
										<div class="rbfw_service_image_wrap text-center">
											<div class="rbfw_service_image_preview"></div>
											<div class="service_image_add_remove">
												<a class="rbfw_service_image_btn button"><i class="fa-solid fa-circle-plus"></i></a><a class="rbfw_remove_service_image_btn button"><i class="fa-solid fa-circle-minus"></i></a>
												<input type="hidden" name="service_img[]" value="" class="rbfw_service_image"/>
											</div>
										</div>
									</td>
									<td><input type="text" class="mp_formControl" name="service_name[]" placeholder="Ex: Cap"/></td>
									<td><input type="text"  class="mp_formControl " name="service_desc[]" placeholder="Service Description" value=""/></td>
									<td><input type="number" class="mp_formControl medium" step="0.01" name="service_price[]" placeholder="Ex: 10" value=""/></td>
									<td><input type="number" class="medium" name="service_qty[]" placeholder="Ex: 100" value=""/></td>
									<td>
											<div class="mp_event_remove_move">
												<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
												<div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
											</div>
									</td>
								</tr>
								</tbody>
							</table>
							</div>
							<p class="mt-2">
								<button id="add-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Extra Service', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							</p>
						</div>
					</section>
					<div class="wervice_quantity_input_box" <?php  if($rbfw_item_type == 'bike_car_sd'){ echo 'style="display:none"'; } ?>>
						<section >
							<div>
								<label><?php _e( 'Enable Service Quantity Box', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
								<span><?php  _e( 'If you Enable this customer can select number of quantity in front-end.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<label class="switch">
								<input type="checkbox" name="rbfw_enable_extra_service_qty" value="<?php echo esc_attr($rbfw_enable_extra_service_qty); ?>" <?php echo esc_attr(($rbfw_enable_extra_service_qty=='yes')?'checked':''); ?>>
								<span class="slider round"></span>
							</label>
						</section>
					</div>
				</div>
				<?php
			}

			public function resort_price_config($post_id){
				$rbfw_enable_resort_daylong_price  = get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) ? get_post_meta( get_the_id(), 'rbfw_enable_resort_daylong_price', true ) : 'no';
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd';
				$rbfw_resort_room_data = get_post_meta( $post_id, 'rbfw_resort_room_data', true ) ? get_post_meta( $post_id, 'rbfw_resort_room_data', true ) : [];
				
				?>
				<div class="rbfw_resort_price_config_wrapper " style="display: <?php if ( $rbfw_item_type == 'resort' ) { echo esc_attr( 'block' );} else {echo esc_attr( 'none' );} ?>;">
					
					<?php $this->panel_header('Resort Price Configuration','Here you can set price for resort.'); ?>
					<section>
						<div>
							<label>
								<?php echo esc_html__( 'Day-long Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</label>
							<span><?php echo esc_html__('If you like to set price for same day check-in/check-out this option can be used.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</div>
						<label class="switch">
							<input type="checkbox" name="rbfw_enable_resort_daylong_price" value="<?php echo esc_attr(($rbfw_enable_resort_daylong_price=='yes')?$rbfw_enable_resort_daylong_price:'no'); ?>" <?php echo esc_attr(($rbfw_enable_resort_daylong_price=='yes')?'checked':''); ?>>
							<span class="slider round"></span>
						</label>
					</section>

					<section>
						<div class="w-100">
						<?php do_action( 'rbfw_before_resort_price_table' ); ?>
						<div style="overflow-x:auto;">
							<table class='form-table rbfw_resort_price_table w-100'>
								<thead>
								<tr>
									<th><?php esc_html_e( 'Room Type', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Image', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th class="resort_day_long_price" style="display:<?php echo esc_attr(($rbfw_enable_resort_daylong_price == 'yes')?'table-cell':'none'); ?>"><?php esc_html_e( 'Day-long price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Day-night price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
									<th colspan="2"><?php esc_html_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								</tr>
								</thead>
								<tbody class="rbfw_resort_price_table_body">
								<?php
								if(! empty($rbfw_resort_room_data)) :
								$i = 0;
								foreach ($rbfw_resort_room_data as $key => $value):
									$img_url = wp_get_attachment_url($value['rbfw_room_image']);
								?>
								<tr class="rbfw_resort_price_table_row" data-key="<?php echo mep_esc_html($i); ?>">
									<td>
										<input type="text" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][room_type]" value="<?php echo esc_attr($value['room_type']); ?>" placeholder="<?php esc_html_e( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
									</td>
									<td class="text-center">
										<div class="rbfw_room_type_image_preview">
										<?php if($img_url): ?>
											<img src="<?php echo esc_url($img_url); ?>">
										<?php endif; ?>
										</div>
										<a class="rbfw_room_type_image_btn button"><i class="fa-solid fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fa-solid fa-circle-minus"></i></a>
										<input type="hidden" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_image]" value="<?php echo esc_attr($value['rbfw_room_image']); ?>" class="rbfw_room_image"/>
									</td>
									<td class="resort_day_long_price" style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'table-cell' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_daylong_rate]" value="<?php echo esc_attr( $value['rbfw_room_daylong_rate'] ); ?>" placeholder="<?php esc_html_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
									<td><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_daynight_rate]" value="<?php echo esc_attr( $value['rbfw_room_daynight_rate'] ); ?>" placeholder="<?php esc_html_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
									<td><input type="text"   name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_desc]" value="<?php echo esc_attr( $value['rbfw_room_desc'] ); ?>" placeholder="<?php esc_attr_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
									<td><input type="number" class="medium" name="rbfw_resort_room_data[<?php echo mep_esc_html($i); ?>][rbfw_room_available_qty]" value="<?php echo esc_attr( $value['rbfw_room_available_qty'] ); ?>" placeholder="<?php esc_attr_e( 'Stock Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
									<td>
									<div class="mp_event_remove_move">
										<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
									</div>
									</td>
								</tr>
								<?php
								$i++;
								endforeach;
								else:
								?>
								<tr class="rbfw_resort_price_table_row" data-key="0">
									<td>
										<input type="text" name="rbfw_resort_room_data[0][room_type]" value="" placeholder="<?php esc_html_e( 'Room type', 'booking-and-rental-manager-for-woocommerce' ); ?>" />
									</td>
									<td class="text-center">
										<div class="rbfw_room_type_image_preview"></div>
										<a class="rbfw_room_type_image_btn button"><i class="fa-solid fa-circle-plus"></i> </a><a class="rbfw_remove_room_type_image_btn button"><i class="fa-solid fa-circle-minus"></i></a>
										<input type="hidden" name="rbfw_resort_room_data[0][rbfw_room_image]" value="" class="rbfw_room_image"/>
									</td>
									<td class="resort_day_long_price"  style="display: <?php if (($rbfw_item_type == 'resort') && $rbfw_enable_resort_daylong_price == 'yes') { echo esc_attr( 'block' ); } else { echo esc_attr( 'none' ); } ?>;"><input type="number" class="medium" name="rbfw_resort_room_data[0][rbfw_room_daylong_rate]" value="" placeholder="<?php esc_html_e( 'Day-long Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
									<td><input type="number" class="medium" name="rbfw_resort_room_data[0][rbfw_room_daynight_rate]" value="" placeholder="<?php esc_html_e( 'Day-night Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td>
									<td><input type="text" name="rbfw_resort_room_data[0][rbfw_room_desc]" value="" placeholder="<?php esc_attr_e( "Short Description", "booking-and-rental-manager-for-woocommerce" ); ?>"></td>
									<td><input type="number" class="medium" name="rbfw_resort_room_data[0][rbfw_room_available_qty]" value="" placeholder="<?php esc_attr_e( "Stock Quantity", "booking-and-rental-manager-for-woocommerce" ); ?>"></td>
									<td>
									<div class="mp_event_remove_move">
										<button class="button remove-row"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div>
									</div>
									</td>
								</tr>
								<?php endif; ?>
								</tbody>
							</table>
						</div>
						<p class="mt-2">
							<span id="add-resort-type-row" class="ppof-button"><i class="fa-solid fa-circle-plus"></i> <?php esc_html_e( 'Add New Resort Type', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						</p>
						<?php do_action( 'rbfw_after_resort_price_table' ); ?>
						</div>
					</section>

				</div>
				<?php
			}
			public function rbfw_day_row( $day_name, $day_slug ) {
				$hourly_rate = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_hourly_rate', true ) : '';
				$daily_rate  = get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) ? get_post_meta( get_the_id(), 'rbfw_' . $day_slug . '_daily_rate', true ) : '';
				$enable      = !empty(get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true )) ? get_post_meta( get_the_id(), 'rbfw_enable_' . $day_slug . '_day', true ) : '';
				?>
				<tr>
					<th><?php esc_html_e( $day_name, '' ); ?></th>
					<td><input type="number" name='rbfw_<?php echo mep_esc_html($day_slug); ?>_hourly_rate' value="<?php echo esc_html( $hourly_rate ); ?>" placeholder="<?php esc_html_e( 'Hourly Price', '' ); ?>"></td>
					<td><input type="number" name='rbfw_<?php echo mep_esc_html($day_slug); ?>_daily_rate' value="<?php echo esc_html( $daily_rate ); ?>" placeholder="<?php esc_html_e( 'Daily Price', '' ); ?>"></td>
					<td><input type="checkbox" name='rbfw_enable_<?php echo mep_esc_html($day_slug); ?>_day' value='yes' <?php if ( $enable == 'yes' ) {
							echo 'checked';
						} ?> ></td>
				</tr>
				<?php
			}

			public function appointment( $post_id){
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd';
				$rbfw_sd_appointment_ondays_data =  get_post_meta($post_id, 'rbfw_sd_appointment_ondays', true) ? get_post_meta($post_id, 'rbfw_sd_appointment_ondays', true) : [];
				$rbfw_sd_appointment_max_qty_per_session = get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) ? get_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', true ) : 'appointment';

				?>
				<div class="rbfw_switch_sd_appointment_row <?php echo esc_attr( $rbfw_item_type != 'appointment')?'hide':'show'; ?>">
					<section>
						
							<label>
								<?php esc_html_e( 'Maximum Allowed Quantity Per Session/Time Slot', 'booking-and-rental-manager-for-woocommerce' ); ?>
							</label>
							<input type="number" name="rbfw_sd_appointment_max_qty_per_session" id="rbfw_sd_appointment_max_qty_per_session" value="<?php echo esc_attr($rbfw_sd_appointment_max_qty_per_session); ?>">
						
					</section>
				</div>
				<section class="hide">
					<label class="w-30">
						<?php esc_html_e( 'Appointment Ondays', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</label>
					<div class="rbfw_appointment_ondays_wrap">
							<div class="rbfw_appointment_ondays_value">
								<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Sunday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Sunday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
								<span><?php esc_html_e( 'Sunday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<div class="rbfw_appointment_ondays_value">
								<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Monday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Monday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
								<span><?php esc_html_e( 'Monday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<div class="rbfw_appointment_ondays_value">
								<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Tuesday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Tuesday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
								<span><?php esc_html_e( 'Tuesday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<div class="rbfw_appointment_ondays_value">
								<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Wednesday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Wednesday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
								<span><?php esc_html_e( 'Wednesday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<div class="rbfw_appointment_ondays_value">
								<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Thursday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Thursday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
								<span><?php esc_html_e( 'Thursday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<div class="rbfw_appointment_ondays_value">
								<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Friday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Friday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
								<span><?php esc_html_e( 'Friday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
							<div class="rbfw_appointment_ondays_value">
								<input type="checkbox" name="rbfw_sd_appointment_ondays[]" value="Saturday" <?php if(!empty($rbfw_sd_appointment_ondays_data) && in_array('Saturday',$rbfw_sd_appointment_ondays_data)){ echo 'checked'; }?>>
								<span><?php esc_html_e( 'Saturday', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
							</div>
						</div>
				</section>
				<?php
			}

			public function general_price_config($post_id){
				$rbfw_enable_hourly_rate = get_post_meta($post_id, 'rbfw_enable_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_hourly_rate', true ) : 'no';
				$rbfw_enable_daily_rate  = get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_enable_daily_rate', true ) : 'yes';
				$rbfw_daily_rate  = get_post_meta( $post_id, 'rbfw_daily_rate', true ) ? get_post_meta( $post_id, 'rbfw_daily_rate', true ) : 0;
				$rbfw_hourly_rate  = get_post_meta( $post_id, 'rbfw_hourly_rate', true ) ? get_post_meta( $post_id, 'rbfw_hourly_rate', true ) : 0;
				$rbfw_item_type =  get_post_meta($post_id, 'rbfw_item_type', true) ? get_post_meta($post_id, 'rbfw_item_type', true) : 'bike_car_sd';
				$mdedo = ( $rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment')?'block':'none';
				$rbfw_enable_daywise_price  = get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) ? get_post_meta( $post_id, 'rbfw_enable_daywise_price', true ) : 'no';
				$mdedo_eekday = ( $rbfw_item_type != 'resort' && $rbfw_item_type != 'bike_car_sd' && $rbfw_item_type != 'appointment' && $rbfw_enable_daywise_price=='yes')?'block':'none';
			?>
			<div class="rbfw_general_price_config_wrapper " style="display: <?php echo $mdedo ?>;">
				<?php do_action( 'rbfw_before_general_price_table' ); ?>
				<?php $this->panel_header('General Price Configuration','General Price Configuration'); ?>
				<?php do_action( 'rbfw_before_general_price_table_row' ); ?>
				<section >
					<div >
						<label for=""><?php esc_html_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span for=""><?php esc_html_e( 'Pricing will be calculated based on number of day.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<div>
						<label class="switch">
							<input type="checkbox" name="rbfw_enable_daily_rate" value="<?php echo esc_attr($rbfw_enable_daily_rate); ?>" <?php echo esc_attr(($rbfw_enable_daily_rate=='yes')?'checked':''); ?>>
							<span class="slider round"></span>
						</label>
						<span class="rbfw_daily_rate_input ms-2" >
							<input type="number" name='rbfw_daily_rate' value="<?php echo esc_html( $rbfw_daily_rate ); ?>" placeholder="<?php esc_html_e( 'Daily Price', '' ); ?>" <?php echo ( $rbfw_enable_daily_rate == 'no' ) ? 'disabled':''; ?>>
						</span>
					</div>
				</section>

				<section >
					<div >
						<label for=""><?php esc_html_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<span ><?php esc_html_e( 'Pricing will be calculated as per hour.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</div>
					<div>
						<label class="switch">
							<input type="checkbox" name="rbfw_enable_hourly_rate" value="<?php echo esc_attr($rbfw_enable_hourly_rate); ?>" <?php echo esc_attr(($rbfw_enable_hourly_rate=='yes')?'checked':''); ?>>
							<span class="slider round"></span>
						</label>
						<span class="rbfw_hourly_rate ms-2" >
							<input type="number" name='rbfw_hourly_rate' value="<?php echo esc_html( $rbfw_hourly_rate ); ?>" placeholder="<?php esc_html_e( 'Hourly Price', '' ); ?>" <?php echo ( $rbfw_enable_hourly_rate == 'no' ) ? 'disabled':''; ?>>
						</span>
					</div>
				</section>

				<section>
					<div>
						<label>
							<?php esc_html_e( 'Day-wise Price Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</label>
						<span>
							<?php esc_html_e( 'If you enable this, price calculation will work as weekly day. it will overwrite general daily price.', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</span>
					</div>

					<label class="switch">
						<input type="checkbox" name="rbfw_enable_daywise_price" value="<?php echo esc_attr($rbfw_enable_daywise_price); ?>" <?php echo esc_attr(($rbfw_enable_daywise_price=='yes')?'checked':''); ?>>
						<span class="slider round"></span>
					</label>
				</section>

				<section class="day-wise-price-configuration <?php echo esc_attr(($rbfw_enable_daywise_price=='yes')?'show':'hide'); ?>" >
					<table class='form-table'>
						<?php do_action( 'rbfw_before_week_price_table_row' ); ?>
						<thead>
							<tr>
								<th scope="row"><?php esc_html_e( 'Day Name', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th scope="row"><?php esc_html_e( 'Hourly Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th scope="row"><?php esc_html_e( 'Daily Price', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th scope="row"><?php esc_html_e( 'Enable/Disable', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$this->rbfw_day_row( __( 'Sunday:', 'booking-and-rental-manager-for-woocommerce' ), 'sun' );
							$this->rbfw_day_row( __( 'Monday:', 'booking-and-rental-manager-for-woocommerce' ), 'mon' );
							$this->rbfw_day_row( __( 'Tuesday:', 'booking-and-rental-manager-for-woocommerce' ), 'tue' );
							$this->rbfw_day_row( __( 'Wednesday:', 'booking-and-rental-manager-for-woocommerce' ), 'wed' );
							$this->rbfw_day_row( __( 'Thursday:', 'booking-and-rental-manager-for-woocommerce' ), 'thu' );
							$this->rbfw_day_row( __( 'Friday:', 'booking-and-rental-manager-for-woocommerce' ), 'fri' );
							$this->rbfw_day_row( __( 'Saturday:', 'booking-and-rental-manager-for-woocommerce' ), 'sat' );
							do_action( 'rbfw_after_week_price_table_row' );
							?>
						</tbody>
					</table>
				</section>

				<?php do_action( 'rbfw_after_general_price_table_row' ); ?>
				
				<?php do_action( 'rbfw_after_general_price_table' ); ?>
			</div>

			<?php do_action( 'rbfw_after_week_price_table',$post_id ); ?>
			
			<?php do_action('rbfw_after_extra_service_table'); ?>
			
			<?php
			}

			public function add_tabs_content( $post_id ) {
			?>
				<div class="mpStyle mp_tab_item" data-tab-item="#travel_pricing">
					<?php $this->section_header(); ?>
					
					<?php $this->rent_type($post_id); ?>
					<?php $this->bike_car_single_day($post_id); ?>
					<?php $this->general_price_config($post_id); ?>
					<?php $this->resort_price_config($post_id); ?>
					<?php $this->category_service_price($post_id); ?>
					<?php $this->appointment($post_id); ?>
					<?php $this->extra_service_table($post_id); ?>
				</div>
				<script>

					jQuery('input[name=rbfw_enable_category_service_price]').click(function(){
                        var status = jQuery(this).val();
                        if(status == 'on') {
                            jQuery(this).val('off') 
							jQuery('#field-wrapper-rbfw_service_category_price').slideUp().removeClass('show').addClass('hide');
                        }  
                        if(status == 'off') {
                            jQuery(this).val('on');  
							jQuery('#field-wrapper-rbfw_service_category_price').slideDown().removeClass('hide').addClass('show');
                        }
                    });

					jQuery('input[name=rbfw_enable_extra_service_qty]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
						}
					});

					jQuery(document).ready(function(){

						// onclick add-bike-car-sd-type-row action
						jQuery('#add-bike-car-sd-type-row').click(function (e) {
							e.preventDefault();
							let current_time = jQuery.now();
							if(jQuery('.rbfw_bike_car_sd_price_table .rbfw_bike_car_sd_price_table_row').length){
								let bike_car_sd_type_last_row = jQuery('.rbfw_bike_car_sd_price_table .rbfw_bike_car_sd_price_table_row:last-child()');
								let bike_car_sd_type_type_last_data_key = parseInt(bike_car_sd_type_last_row.attr('data-key'));
								let bike_car_sd_type_type_new_data_key = bike_car_sd_type_type_last_data_key + 1;
								let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" step=".01" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" step=".01"  value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input class="medium"  type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( '(Quantity/Stock)/Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';

								let bike_car_sd_type_type_add_new_row = jQuery('.rbfw_bike_car_sd_price_table').append(bike_car_sd_type_type_row);
							}
							else{
								let bike_car_sd_type_type_new_data_key = 0;
								let bike_car_sd_type_type_row = '<tr class="rbfw_bike_car_sd_price_table_row" data-key="'+bike_car_sd_type_type_new_data_key+'"><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][rent_type]" value="" placeholder="<?php esc_html_e( 'Type name', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="text" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][short_desc]" value="" placeholder="<?php esc_html_e( 'Short Description', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][price]" step=".01" value="" placeholder="<?php esc_html_e( 'Price', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><input class="medium"  type="number" name="rbfw_bike_car_sd_data['+bike_car_sd_type_type_new_data_key+'][qty]" value="" placeholder="<?php esc_html_e( 'Available Quantity/Stock Quantity Per Day', 'booking-and-rental-manager-for-woocommerce' ); ?>"></td><td><div class="mp_event_remove_move"><button class="button remove-row '+current_time+'"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
								let bike_car_sd_type_type_add_new_row = jQuery('.rbfw_bike_car_sd_price_table').append(bike_car_sd_type_type_row);
							}
							jQuery('.remove-row.'+current_time+'').on('click', function () {
								e.preventDefault();
								e.stopImmediatePropagation();
								if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
									jQuery(this).parents('tr').remove();
								} else {
									return false;
								}
							});

							jQuery( ".rbfw_bike_car_sd_price_table_body" ).sortable();

						});

						jQuery( ".rbfw_bike_car_sd_price_table_body" ).sortable();

					});


					jQuery(document).ready(function(){

					// extra service add image button and remove image button function
					function rbfw_service_image_addup(){
					// onclick extra service add image button action
					jQuery('.rbfw_service_image_btn').click(function() {
						let target = jQuery(this).parents('tr');
						let send_attachment_bkp = wp.media.editor.send.attachment;
						wp.media.editor.send.attachment = function(props, attachment) {
							target.find('.rbfw_service_image_preview img').remove();
							target.find('.rbfw_service_image_preview').append('<img src="'+attachment.url+'"/>');
							target.find('.rbfw_service_image').val(attachment.id);
							wp.media.editor.send.attachment = send_attachment_bkp;
						}
						wp.media.editor.open(jQuery(this));
						return false;
					});
					// end onclick extra service add image button action

					// onclick extra service remove image button action
					jQuery('.rbfw_remove_service_image_btn').click(function() {
						let target = jQuery(this).parents('tr');
						target.find('.rbfw_service_image_preview img').remove();
						target.find('.rbfw_service_image').val('');
					});
					// end onclick extra service remove image button action
					}
					rbfw_service_image_addup();
					// End extra service add image button and remove image button function

					});

					// Daily price
					jQuery('input[name=rbfw_enable_daily_rate]').click(function(){
						var status = jQuery(this).val();
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.rbfw_daily_rate_input input').attr("disabled", true);
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.rbfw_daily_rate_input input').removeAttr("disabled");
							
						}
					});

					// Hourly price
					jQuery('input[name=rbfw_enable_hourly_rate]').click(function(){
						var status = jQuery(this).val();
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.rbfw_hourly_rate input').attr("disabled", true);
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.rbfw_hourly_rate input').removeAttr("disabled");
							
						}
					});
					
					// daywise price
					jQuery('input[name=rbfw_enable_daywise_price]').click(function(){
						var status = jQuery(this).val();
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.day-wise-price-configuration').slideUp().removeClass('show').addClass('hide'); 
							
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.day-wise-price-configuration').slideDown().removeClass('hide').addClass('show'); 

							
						}
					});
					//day long price
					jQuery('input[name=rbfw_enable_resort_daylong_price]').click(function(){  
						var status = jQuery(this).val();
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.resort_day_long_price').hide();
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.resort_day_long_price').show(); 
						}
					});
                    
					// ===============resort============
					jQuery('#add-resort-type-row').click(function(e){
						e.preventDefault();
						let current_time = jQuery.now();

						if (jQuery('.rbfw_resort_price_table .rbfw_resort_price_table_row').length) {
							let resort_last_row = jQuery('.rbfw_resort_price_table .rbfw_resort_price_table_row:last-child()');
							let resort_type_last_data_key = parseInt(resort_last_row.attr('data-key'));
							let resort_type_new_data_key = resort_type_last_data_key + 1;
							let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '"><td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="Room type"></td><td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fa-solid fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fa-solid fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td><td class="resort_day_long_price" style="display: none;"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" value="" placeholder="Day-long Rate"></td><td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" value="" placeholder="Day-night Rate"></td><td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="Short Description"></td><td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="Available Qty"></td><td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
							let resort_type_add_new_row = jQuery('.rbfw_resort_price_table').append(resort_type_row);
						} else {
							let resort_type_new_data_key = 0;
							let resort_type_row = '<tr class="rbfw_resort_price_table_row" data-key="' + resort_type_new_data_key + '"><td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][room_type]" value="" placeholder="Room type"></td><td class="text-center"><div class="rbfw_room_type_image_preview"></div><a class="rbfw_room_type_image_btn button"><i class="fa-solid fa-circle-plus"></i></a><a class="rbfw_remove_room_type_image_btn button"><i class="fa-solid fa-circle-minus"></i></a><input type="hidden"  name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_image]" value="" class="rbfw_room_image"></td><td class="resort_day_long_price" style="display: none;"><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daylong_rate]" value="" placeholder="Day-long Rate"></td><td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_daynight_rate]" value="" placeholder="Day-night Rate"></td><td><input type="text" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_desc]" value="" placeholder="Short Description"></td><td><input type="number" class="medium" name="rbfw_resort_room_data[' + resort_type_new_data_key + '][rbfw_room_available_qty]" value="" placeholder="Available Qty"></td><td><div class="mp_event_remove_move"><button class="button remove-row ' + current_time + '"><i class="fa-solid fa-trash-can"></i></button><div class="button mp_event_type_sortable_button"><i class="fas fa-arrows-alt"></i></div></div></td></tr>';
							let resort_type_add_new_row = jQuery('.rbfw_resort_price_table').append(resort_type_row);
						}
						jQuery('.remove-row.' + current_time + '').on('click', function() {
							e.preventDefault();
							e.stopImmediatePropagation();
							if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
							jQuery(this).parents('tr').remove();
							} else {
							return false;
							}
						});
						jQuery(".rbfw_resort_price_table_body").sortable();
						rbfw_room_type_image_addup();

						var daylong_price_label_val = jQuery('.rbfw_resort_daylong_price_switch label.active').find('input').val();

						if (daylong_price_label_val == 'yes') {
							jQuery('.resort_day_long_price').show();
						} else {
							jQuery('.resort_day_long_price').hide();
						}
					});

					function rbfw_room_type_image_addup(){
						jQuery('.rbfw_room_type_image_btn').click(function() {
							let parent_data_key = jQuery(this).closest('.rbfw_resort_price_table_row').attr('data-key');
							let send_attachment_bkp = wp.media.editor.send.attachment;
							wp.media.editor.send.attachment = function(props, attachment) {
								jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_type_image_preview img').remove();
								jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_type_image_preview').append('<img src="'+attachment.url+'"/>');
								jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_image').val(attachment.id);
								wp.media.editor.send.attachment = send_attachment_bkp;
							}
							wp.media.editor.open(jQuery(this));
							return false;
						});

						jQuery('.rbfw_remove_room_type_image_btn').click(function() {
							let parent_data_key = jQuery(this).closest('.rbfw_resort_price_table_row').attr('data-key');
							jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_type_image_preview img').remove();
							jQuery('.rbfw_resort_price_table_row[data-key='+parent_data_key+'] .rbfw_room_image').val('');
						});

					}
					rbfw_room_type_image_addup();

					jQuery( ".rbfw_resort_price_table_body" ).sortable();
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
                    $rbfw_item_type = isset( $_POST['rbfw_item_type'] ) ? rbfw_array_strip( $_POST['rbfw_item_type'] ) : [];
					$rbfw_enable_daily_rate  = isset( $_POST['rbfw_enable_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_enable_daily_rate'] ) : 'no';				
					
					$daily_rate  = isset( $_POST['rbfw_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_daily_rate'] ) : 0;
					$rbfw_enable_hourly_rate = isset( $_POST['rbfw_enable_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_enable_hourly_rate'] ) : 'no';					
					$hourly_rate = isset( $_POST['rbfw_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_hourly_rate'] ) : 0;
					
					$rbfw_enable_daywise_price  = isset( $_POST['rbfw_enable_daywise_price'] ) ? rbfw_array_strip( $_POST['rbfw_enable_daywise_price'] ) : 'no';
					$rbfw_enable_category_service_price      = isset( $_POST['rbfw_enable_category_service_price'] ) ? rbfw_array_strip( $_POST['rbfw_enable_category_service_price'] ) : 'off';
					$rbfw_service_category_price      = isset( $_POST['rbfw_service_category_price'] ) ? rbfw_array_strip( $_POST['rbfw_service_category_price'] ) : [];
					$rbfw_bike_car_sd_data 	 = isset( $_POST['rbfw_bike_car_sd_data'] ) ? rbfw_array_strip( $_POST['rbfw_bike_car_sd_data'] ) : 0;
					$rbfw_enable_resort_daylong_price  = isset( $_POST['rbfw_enable_resort_daylong_price'] ) ? rbfw_array_strip( $_POST['rbfw_enable_resort_daylong_price'] ) : 'no';
					
					$rbfw_resort_room_data 	 = isset( $_POST['rbfw_resort_room_data'] ) ? rbfw_array_strip( $_POST['rbfw_resort_room_data'] ) : 0;
					$rbfw_sd_appointment_max_qty_per_session 	 = isset( $_POST['rbfw_sd_appointment_max_qty_per_session'] ) ?  $_POST['rbfw_sd_appointment_max_qty_per_session'] : '';
					$rbfw_sd_appointment_ondays = isset( $_POST['rbfw_sd_appointment_ondays'] ) ? rbfw_array_strip( $_POST['rbfw_sd_appointment_ondays'] ) : [];
					$rbfw_enable_extra_service_qty  = isset( $_POST['rbfw_enable_extra_service_qty'] ) ? $_POST['rbfw_enable_extra_service_qty']  : 'no';

					

					update_post_meta( $post_id, 'rbfw_enable_category_service_price', $rbfw_enable_category_service_price );
					update_post_meta( $post_id, 'rbfw_service_category_price', $rbfw_service_category_price );
					
					update_post_meta( $post_id, 'rbfw_item_type', $rbfw_item_type );
					update_post_meta( $post_id, 'rbfw_enable_daily_rate', $rbfw_enable_daily_rate );
					update_post_meta( $post_id, 'rbfw_daily_rate', $daily_rate );
					update_post_meta( $post_id, 'rbfw_enable_hourly_rate', $rbfw_enable_hourly_rate );
					update_post_meta( $post_id, 'rbfw_hourly_rate', $hourly_rate );
					update_post_meta( $post_id, 'rbfw_enable_daywise_price', $rbfw_enable_daywise_price );
					update_post_meta( $post_id, 'rbfw_bike_car_sd_data', $rbfw_bike_car_sd_data );
					update_post_meta( $post_id, 'rbfw_resort_room_data', $rbfw_resort_room_data );
					update_post_meta( $post_id, 'rbfw_enable_resort_daylong_price', $rbfw_enable_resort_daylong_price );
					update_post_meta( $post_id, 'rbfw_sd_appointment_max_qty_per_session', $rbfw_sd_appointment_max_qty_per_session );
					update_post_meta( $post_id, 'rbfw_sd_appointment_ondays', $rbfw_sd_appointment_ondays );
					update_post_meta( $post_id, 'rbfw_enable_extra_service_qty', $rbfw_enable_extra_service_qty );
					// daywise configureation============================
					//sun
					$hourly_rate_sun = isset( $_POST['rbfw_sun_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sun_hourly_rate'] ) : '';
					$daily_rate_sun  = isset( $_POST['rbfw_sun_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sun_daily_rate'] ) : '';
					$enabled_sun     = isset( $_POST['rbfw_enable_sun_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_sun_day'] ) : 'no';
					//mon
					$hourly_rate_mon = isset( $_POST['rbfw_mon_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_mon_hourly_rate'] ) : '';
					$daily_rate_mon  = isset( $_POST['rbfw_mon_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_mon_daily_rate'] ) : '';
					$enabled_mon     = isset( $_POST['rbfw_enable_mon_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_mon_day'] ) : 'no';
					//tue
					$hourly_rate_tue = isset( $_POST['rbfw_tue_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_tue_hourly_rate'] ) : '';
					$daily_rate_tue  = isset( $_POST['rbfw_tue_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_tue_daily_rate'] ) : '';
					$enabled_tue     = isset( $_POST['rbfw_enable_tue_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_tue_day'] ) : 'no';
					//wed
					$hourly_rate_wed = isset( $_POST['rbfw_wed_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_wed_hourly_rate'] ) : '';
					$daily_rate_wed  = isset( $_POST['rbfw_wed_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_wed_daily_rate'] ) : '';
					$enabled_wed     = isset( $_POST['rbfw_enable_wed_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_wed_day'] ) : 'no';
					//thu
					$hourly_rate_thu = isset( $_POST['rbfw_thu_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_thu_hourly_rate'] ) : '';
					$daily_rate_thu  = isset( $_POST['rbfw_thu_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_thu_daily_rate'] ) : '';
					$enabled_thu     = isset( $_POST['rbfw_enable_thu_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_thu_day'] ) : 'no';
					//fri
					$hourly_rate_fri = isset( $_POST['rbfw_fri_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_fri_hourly_rate'] ) : '';
					$daily_rate_fri  = isset( $_POST['rbfw_fri_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_fri_daily_rate'] ) : '';
					$enabled_fri     = isset( $_POST['rbfw_enable_fri_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_fri_day'] ) : 'no';
					//sat
					$hourly_rate_sat         = isset( $_POST['rbfw_sat_hourly_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sat_hourly_rate'] ) : '';
					$daily_rate_sat          = isset( $_POST['rbfw_sat_daily_rate'] ) ? rbfw_array_strip( $_POST['rbfw_sat_daily_rate'] ) : '';
					$enabled_sat             = isset( $_POST['rbfw_enable_sat_day'] ) ? rbfw_array_strip( $_POST['rbfw_enable_sat_day'] ) : 'no';
			
					// sun
					update_post_meta( $post_id, 'rbfw_sun_hourly_rate', $hourly_rate_sun );
					update_post_meta( $post_id, 'rbfw_sun_daily_rate', $daily_rate_sun );
					update_post_meta( $post_id, 'rbfw_enable_sun_day', $enabled_sun );
					// mon
					update_post_meta( $post_id, 'rbfw_mon_hourly_rate', $hourly_rate_mon );
					update_post_meta( $post_id, 'rbfw_mon_daily_rate', $daily_rate_mon );
					update_post_meta( $post_id, 'rbfw_enable_mon_day', $enabled_mon );
					// tue
					update_post_meta( $post_id, 'rbfw_tue_hourly_rate', $hourly_rate_tue );
					update_post_meta( $post_id, 'rbfw_tue_daily_rate', $daily_rate_tue );
					update_post_meta( $post_id, 'rbfw_enable_tue_day', $enabled_tue );
					// wed
					update_post_meta( $post_id, 'rbfw_wed_hourly_rate', $hourly_rate_wed );
					update_post_meta( $post_id, 'rbfw_wed_daily_rate', $daily_rate_wed );
					update_post_meta( $post_id, 'rbfw_enable_wed_day', $enabled_wed );
					// thu
					update_post_meta( $post_id, 'rbfw_thu_hourly_rate', $hourly_rate_thu );
					update_post_meta( $post_id, 'rbfw_thu_daily_rate', $daily_rate_thu );
					update_post_meta( $post_id, 'rbfw_enable_thu_day', $enabled_thu );
					// fri
					update_post_meta( $post_id, 'rbfw_fri_hourly_rate', $hourly_rate_fri );
					update_post_meta( $post_id, 'rbfw_fri_daily_rate', $daily_rate_fri );
					update_post_meta( $post_id, 'rbfw_enable_fri_day', $enabled_fri );
					// sat
					update_post_meta( $post_id, 'rbfw_sat_hourly_rate', $hourly_rate_sat );
					update_post_meta( $post_id, 'rbfw_sat_daily_rate', $daily_rate_sat );
					update_post_meta( $post_id, 'rbfw_enable_sat_day', $enabled_sat );

					// save extra service data==========================================
					$old_extra_service = get_post_meta( $post_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $post_id, 'rbfw_extra_service_data', true ) : [];
					$new_extra_service = array();
			
					$service_img     = !empty($_POST['service_img']) ? rbfw_array_strip( $_POST['service_img'] ) : array();
					$names    = $_POST['service_name'] ? rbfw_array_strip( $_POST['service_name'] ) : array();
					$urls     = $_POST['service_price'] ? rbfw_array_strip( $_POST['service_price'] ) : array();
					$service_desc     = $_POST['service_desc'] ? rbfw_array_strip( $_POST['service_desc'] ) : array();
					$qty      = $_POST['service_qty'] ? rbfw_array_strip( $_POST['service_qty'] ) : array();
					$qty_type = !empty($_POST['service_qty_type']) ? rbfw_array_strip( $_POST['service_qty_type'] ) : array();
					$count    = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
			
						if (!empty($service_img[ $i ])) :
							$new_extra_service[ $i ]['service_img'] = stripslashes( strip_tags( $service_img[ $i ] ) );
						endif;
			
						if ( $names[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_name'] = stripslashes( strip_tags( $names[ $i ] ) );
						endif;
			
						if ( $urls[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_price'] = stripslashes( strip_tags( $urls[ $i ] ) );
						endif;
			
						if ( $service_desc[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_desc'] = stripslashes( strip_tags( $service_desc[ $i ] ) );
						endif;
			
						if ( $qty[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_qty'] = stripslashes( strip_tags( $qty[ $i ] ) );
						endif;
			
						if ( !empty($qty_type[ $i ]) && $qty_type[ $i ] != '' ) :
							$new_extra_service[ $i ]['service_qty_type'] = stripslashes( strip_tags( $qty_type[ $i ] ) );
						endif;
					}
			
					$extra_service_data_arr = apply_filters( 'rbfw_extra_service_arr_save', $new_extra_service );
			
					if ( ! empty( $extra_service_data_arr ) && $extra_service_data_arr != $old_extra_service ) {
						update_post_meta( $post_id, 'rbfw_extra_service_data', $extra_service_data_arr );
					} elseif ( empty( $extra_service_data_arr ) && $old_extra_service ) {
						delete_post_meta( $post_id, 'rbfw_extra_service_data', $old_extra_service );
					}
					
				}
            }
		}
		new RBFW_Pricing();
	}
	
	