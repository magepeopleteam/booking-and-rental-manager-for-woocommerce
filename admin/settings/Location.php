<?php

	/*
   * @Author 		raselsha@gmail.com
   */
	if (!defined('ABSPATH')) {
		die;
	} 
	if (!class_exists('RBFW_Location')) {
        class RBFW_Location{
            public function __construct() {
                add_action( 'rbfw_meta_box_tab_name', [$this,'add_tab_menu'] );
                add_action( 'rbfw_meta_box_tab_content', [$this,'add_tabs_content'] );
                add_action('save_post', array($this, 'settings_save'), 99, 1);
			}

            public function add_tab_menu() {
            ?>
                <li data-target-tabs="#rbfw_location_config"><i class="fa-solid fa-location-dot"></i><?php esc_html_e('Location', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
            <?php
            }

             public function section_header(){
                ?>
                    <h2 class="mp_tab_item_title"><?php echo esc_html__('Location Configuration', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                    <p class="mp_tab_item_description"><?php echo esc_html__('Here you can configure locatoin', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
                        
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

			public function rbfw_get_location_arr() {
				$terms = get_terms( array(
					'taxonomy'   => 'rbfw_item_location',
					'hide_empty' => false,
				) );
				$arr   = array(
					'' => rbfw_string_return('rbfw_text_pls_select_location',__('Select a Location','booking-and-rental-manager-for-woocommerce'))
				);
				foreach ( $terms as $_terms ) {
					$arr[ $_terms->name ] = $_terms->name;
				}
		
				return $arr;
			}

			public function rbfw_get_location_dropdown( $name, $saved_value = '', $class = '' ){
				$location_arr = $this->rbfw_get_location_arr();
				echo "<select name=$name class=$class>";
				foreach ( $location_arr as $key => $value ) {
					$selected_text = ! empty( $saved_value ) && $saved_value == $key ? 'Selected' : '';
					echo "<option value='$key' $selected_text>" . esc_html( $value ) . "</option>";
				}
				echo "</select>";
			}
			public function pickup_location_config($post_id){
				$rbfw_enable_pick_point  = get_post_meta( $post_id, 'rbfw_enable_pick_point', true ) ? get_post_meta( $post_id, 'rbfw_enable_pick_point', true ) : 'yes';
				$rbfw_pickup_data        = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
				
			?>
			<section >
				<div>
					<label><?php _e( 'Pick-up Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
					<span><?php esc_html_e( 'Turn Pick-up Location On/Off', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</div>
				<label class="switch">
					<input type="checkbox" name="rbfw_enable_pick_point" value="<?php echo esc_attr($rbfw_enable_pick_point); ?>" <?php echo esc_attr(($rbfw_enable_pick_point=='yes')?'checked':''); ?>>
					<span class="slider round"></span>
				</label>
			</section>
			<section class="rbfw-pickup-location <?php echo esc_attr(($rbfw_enable_pick_point=='yes')?'show':'hide'); ?>" >
				<div class="rbfw-pickup-locations">
					<?php
						if ( sizeof( $rbfw_pickup_data ) > 0 ) :
							foreach ( $rbfw_pickup_data as $field ) {
								$location_name = array_key_exists( 'loc_pickup_name', $field ) ? esc_attr( $field['loc_pickup_name'] ) : '';
								?>
								<section class="rbfw-pickup">
									<label for=""><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<?php $this->rbfw_get_location_dropdown( 'loc_pickup_name[]' , $location_name ); ?>
									<div class="mp_event_remove_move">
										<button onclick="jQuery(this).parent().parent().remove()" class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
										
									</div>
								</section>
								<?php
							}
						else :
						endif;
					?>
					<section class="rbfw-pickup-clone">
						<label for=""><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<?php $this->rbfw_get_location_dropdown( 'loc_pickup_name[]' ); ?>
						<div class="mp_event_remove_move">
							<button onclick="jQuery(this).parent().parent().remove()" class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
						</div>
					</section>					
				</div>
				<div class="d-flex justify-content-center mt-2">
					<div class="ppof-button add-item" onclick="createPickupLocation()"><i class="fa-solid fa-circle-plus"></i> Add New Pick-up Location</d>
				</div>
			</section>
			<?php
			}

			public function drop_off_location_config($post_id){
				$rbfw_enable_dropoff_point  = get_post_meta( $post_id, 'rbfw_enable_dropoff_point', true ) ? get_post_meta( $post_id, 'rbfw_enable_dropoff_point', true ) : 'yes';
				$rbfw_dropoff_data        = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
				
			?>
				<section >
				<div>
					<label><?php _e( 'Drop-Off Location', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
					<span><?php esc_html_e( 'Turn drop off Location On/Off', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</div>
				<label class="switch">
					<input type="checkbox" name="rbfw_enable_dropoff_point" value="<?php echo esc_attr($rbfw_enable_dropoff_point); ?>" <?php echo esc_attr(($rbfw_enable_dropoff_point=='yes')?'checked':''); ?>>
					<span class="slider round"></span>
				</label>
			</section>
			<section class="rbfw-drop-off-location <?php echo esc_attr(($rbfw_enable_dropoff_point=='yes')?'show':'hide'); ?>" >
				<div class="rbfw-drop-off-locations">
					<?php
						if ( sizeof( $rbfw_dropoff_data ) > 0 ) :
							foreach ( $rbfw_dropoff_data as $field ) {
								$location_name = array_key_exists( 'loc_dropoff_name', $field ) ? esc_attr( $field['loc_dropoff_name'] ) : '';
								?>
								<section class="rbfw-pickup">
									<label for=""><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
									<?php $this->rbfw_get_location_dropdown( 'loc_dropoff_name[]' , $location_name ); ?>
									<div class="mp_event_remove_move">
										<button onclick="jQuery(this).parent().parent().remove()" class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
										
									</div>
								</section>
								<?php
							}
						else :
						endif;
					?>
					<section class="rbfw-drop-off-clone">
						<label for=""><?php esc_html_e( 'Location Name', 'booking-and-rental-manager-for-woocommerce' ); ?></label>
						<?php $this->rbfw_get_location_dropdown( 'loc_dropoff_name[]' ); ?>
						<div class="mp_event_remove_move">
							<button onclick="jQuery(this).parent().parent().remove()" class="button remove-row"><i class="fa-solid fa-trash-can"></i></button>
						</div>
					</section>					
				</div>
				<div class="d-flex justify-content-center mt-2">
					<div class="ppof-button add-item" onclick="createDropOffLocation()"><i class="fa-solid fa-circle-plus"></i> Add New Pick-up Location</d>
				</div>
			</section>
			<?php
			}

            public function add_tabs_content( $post_id ) {
            ?>
                <div class="mpStyle mp_tab_item" data-tab-item="#rbfw_location_config">
					<?php $this->section_header(); ?>

					<?php do_action( 'rbfw_location_config_before', $post_id ); ?>
                    <?php $this->panel_header('Pick-up Location Configuration','Here you can set location.'); ?>
					<?php $this->pickup_location_config($post_id); ?>

                    <?php $this->panel_header('Drop-off Location Configuration','Here you can set drop off location.'); ?>
					<?php $this->drop_off_location_config($post_id); ?>
					<?php do_action( 'rbfw_location_config_after', $post_id ); ?>
				</div>

				

				<script>
					jQuery('input[name=rbfw_enable_pick_point]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.rbfw-pickup-location').slideUp().removeClass('show').addClass('hide');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.rbfw-pickup-location').slideDown().removeClass('hide').addClass('show');
						}
					});

					jQuery('input[name=rbfw_enable_dropoff_point]').click(function(){
						var status = jQuery(this).val();
						
						if(status == 'yes') {
							jQuery(this).val('no');
							jQuery('.rbfw-drop-off-location').slideUp().removeClass('show').addClass('hide');
						}  
						if(status == 'no') {
							jQuery(this).val('yes'); 
							jQuery('.rbfw-drop-off-location').slideDown().removeClass('hide').addClass('show');
						}
					});

					function createPickupLocation(){
						jQuery(".rbfw-pickup-clone").clone().appendTo(".rbfw-pickup-locations")
						.removeClass('rbfw-pickup-clone').addClass('rbfw-pickup');
					};
					function createDropOffLocation(){
						jQuery(".rbfw-drop-off-clone").clone().appendTo(".rbfw-drop-off-locations")
						.removeClass('rbfw-drop-off-clone').addClass('rbfw-drop-off');
					};
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
                    $rbfw_enable_pick_point  = isset( $_POST['rbfw_enable_pick_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_pick_point'] ) : 'no';
                    $rbfw_enable_dropoff_point  = isset( $_POST['rbfw_enable_dropoff_point'] ) ? rbfw_array_strip( $_POST['rbfw_enable_dropoff_point'] ) : 'no';
					
					update_post_meta( $post_id, 'rbfw_enable_pick_point', $rbfw_enable_pick_point );
					update_post_meta( $post_id, 'rbfw_enable_dropoff_point', $rbfw_enable_dropoff_point );
					
					// Saving Pickup Location Data
					$old_rbfw_pickup_data = get_post_meta( $post_id, 'rbfw_pickup_data', true ) ? get_post_meta( $post_id, 'rbfw_pickup_data', true ) : [];
					$new_rbfw_pickup_data = array();
					$names                = $_POST['loc_pickup_name'] ? rbfw_array_strip( $_POST['loc_pickup_name'] ) : array();
					$count                = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
						if ( $names[ $i ] != '' ) :
							$new_rbfw_pickup_data[ $i ]['loc_pickup_name'] = stripslashes( strip_tags( $names[ $i ] ) );
						endif;
					}
					$pickup_data_arr = apply_filters( 'rbfw_pickup_arr_save', $new_rbfw_pickup_data );
					if ( ! empty( $pickup_data_arr ) && $pickup_data_arr != $old_rbfw_pickup_data ) {
						update_post_meta( $post_id, 'rbfw_pickup_data', $pickup_data_arr );
					} elseif ( empty( $pickup_data_arr ) && $old_rbfw_pickup_data ) {
						delete_post_meta( $post_id, 'rbfw_pickup_data', $old_rbfw_pickup_data );
					}

					// Saving Dropoff Data
					$old_rbfw_dropoff_data = get_post_meta( $post_id, 'rbfw_dropoff_data', true ) ? get_post_meta( $post_id, 'rbfw_dropoff_data', true ) : [];
					$new_rbfw_dropoff_data = array();
					$names                 = $_POST['loc_dropoff_name'] ? rbfw_array_strip( $_POST['loc_dropoff_name'] ) : array();
					$count                 = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
						if ( $names[ $i ] != '' ) :
							$new_rbfw_dropoff_data[ $i ]['loc_dropoff_name'] = stripslashes( strip_tags( $names[ $i ] ) );
						endif;
					}
					$dropoff_data_arr = apply_filters( 'rbfw_dropoff_arr_save', $new_rbfw_dropoff_data );
					if ( ! empty( $dropoff_data_arr ) && $dropoff_data_arr != $old_rbfw_dropoff_data ) {
						update_post_meta( $post_id, 'rbfw_dropoff_data', $dropoff_data_arr );
					} elseif ( empty( $dropoff_data_arr ) && $old_rbfw_dropoff_data ) {
						delete_post_meta( $post_id, 'rbfw_dropoff_data', $old_rbfw_dropoff_data );
					}
                }
            }
		}
		new RBFW_Location();
	}