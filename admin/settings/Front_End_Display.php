<?php


 /*********************************
 * Start: Front-end Display Tab
 * ******************************/
add_action( 'rbfw_meta_box_tab_name', 'rbfw_frontend_display_tab_name' , 90);

function rbfw_frontend_display_tab_name($rbfw_id){

	?>
	<li data-target-tabs="#rbfw_frontend_display"><i class="fa-solid fa-display"></i><?php esc_html_e( ' Front-end Display', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
	<?php

}

add_action( 'rbfw_meta_box_tab_content', 'rbfw_frontend_display_tab_content' , 90);

function rbfw_frontend_display_tab_content($rbfw_id){
	$rbfw_item_type = get_post_meta( $rbfw_id, 'rbfw_item_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_item_type', true ) : '';
	$rbfw_available_qty_info_switch = get_post_meta( $rbfw_id, 'rbfw_available_qty_info_switch', true ) ? get_post_meta( $rbfw_id, 'rbfw_available_qty_info_switch', true ) : 'no';
	$rbfw_enable_extra_service_qty = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true ) : 'no';
	$rbfw_enable_md_type_item_qty = get_post_meta( $rbfw_id, 'rbfw_enable_md_type_item_qty', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_md_type_item_qty', true ) : 'no';
	?>

	<div class="mp_tab_item " data-tab-item="#rbfw_frontend_display">
		<h2  class="h5 text-white bg-primary mb-1 rounded-top"><?php echo esc_html__( 'Front-end Display Settings', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
		<section class="component d-flex justify-content-between align-items-center mb-2">
			<div class="w-50 d-flex justify-content-between align-items-center">
				<label class=""><?php esc_html_e( 'Enable the Available Item Quantity Display on Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span><?php esc_html_e( 'It displays available quantity information in item details page.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></i></label>
			</div>
			<div class="w-50 d-flex justify-content-between align-items-center">
				<div class="rbfw_switch_wrapper rbfw_m_0">
					<div class="rbfw_switch">
						<label for="rbfw_available_qty_info_switch_on" class="<?php if ( $rbfw_available_qty_info_switch == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_available_qty_info_switch" class="rbfw_available_qty_info_switch" value="yes" id="rbfw_available_qty_info_switch_on" <?php if ( $rbfw_available_qty_info_switch == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_available_qty_info_switch_off" class="<?php if ( $rbfw_available_qty_info_switch != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_available_qty_info_switch" class="rbfw_available_qty_info_switch" value="no" id="rbfw_available_qty_info_switch_off" <?php if ( $rbfw_available_qty_info_switch != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
					</div>
				</div>
			</div>
		</section>

		<div class="rbfw_switch_md_type_item_qty" <?php if ( $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment' || $rbfw_item_type == 'resort' ) { echo 'style="display:none"'; } ?>>
			<section class="component d-flex justify-content-between align-items-center mb-2">
				<div class="w-50 d-flex justify-content-between align-items-center">
					<label class=""><?php esc_html_e( 'Enable Multiple Item Quantity Box Display in Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span><?php esc_html_e( 'It enables the multiple item quantity selection option. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></i></label>
				</div>
				<div class="w-50 d-flex justify-content-between align-items-center">
					<div class="rbfw_switch_wrapper rbfw_m_0">
						<div class="rbfw_switch">
							<label for="rbfw_enable_md_type_item_qty_on" class="<?php if ( $rbfw_enable_md_type_item_qty == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_md_type_item_qty" class="rbfw_enable_md_type_item_qty" value="yes" id="rbfw_enable_md_type_item_qty_on" <?php if ( $rbfw_enable_md_type_item_qty == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_md_type_item_qty_off" class="<?php if ( $rbfw_enable_md_type_item_qty != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_md_type_item_qty" class="rbfw_enable_md_type_item_qty" value="no" id="rbfw_enable_md_type_item_qty_off" <?php if ( $rbfw_enable_md_type_item_qty != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
						</div>
					</div>
				</div>
			</section>
			<section class="component d-flex justify-content-between align-items-center mb-2">
				<div class="w-50 d-flex justify-content-between align-items-center">
					<label class=""><?php esc_html_e( 'Enable Multiple Extra Service Quantity Box Display in Front-end', 'booking-and-rental-manager-for-woocommerce' ); ?> <i class="fas fa-question-circle tool-tips"><span><?php esc_html_e( 'Enable/Disable multiple service quantity selection. It will work when the type is Bike/Car for multiple day, Dress, Equipment & Others.', 'booking-and-rental-manager-for-woocommerce' ); ?></span></i></label>
				</div>
				<div class="w-50 d-flex justify-content-between align-items-center">
					<div class="rbfw_switch_wrapper rbfw_switch_extra_service_qty" <?php if ( $rbfw_item_type == 'resort' || $rbfw_item_type == 'bike_car_sd' || $rbfw_item_type == 'appointment'){ echo 'style="display: none"'; } ?>>
						<div class="rbfw_switch">
							<label for="rbfw_enable_extra_service_qty_on" class="<?php if ( $rbfw_enable_extra_service_qty == 'yes' ) { echo 'active'; } ?>"><input type="radio" name="rbfw_enable_extra_service_qty" class="rbfw_enable_extra_service_qty" value="yes" id="rbfw_enable_extra_service_qty_on" <?php if ( $rbfw_enable_extra_service_qty == 'yes' ) { echo 'Checked'; } ?>> <span>On</span></label><label for="rbfw_enable_extra_service_qty_off" class="<?php if ( $rbfw_enable_extra_service_qty != 'yes' ) { echo 'active'; } ?> off"><input type="radio" name="rbfw_enable_extra_service_qty" class="rbfw_enable_extra_service_qty" value="no" id="rbfw_enable_extra_service_qty_off" <?php if ( $rbfw_enable_extra_service_qty != 'yes' ) { echo 'Checked'; } ?>> <span>Off</span></label>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
	<?php
}

/*********************************
 * End: Front-end Display Tab
 * ******************************/