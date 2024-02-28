<div class="rbfw_bikecarsd_time_table_container rbfw-bikecarsd-step" data-step="2">
    <a class="rbfw_back_step_btn" back-step="1" data-step="2">
        <i class="fa-solid fa-circle-left"></i>
        <?php echo rbfw_string_return('rbfw_text_back_to_previous_step',__('Back to Previous Step','booking-and-rental-manager-for-woocommerce')) ?>
        kkk
    </a>

    <?php if($is_muffin_template == 0){ ?>

        <div class="rbfw_step_selected_date">
            <i class="fa-solid fa-calendar-check"></i>
            <?php echo rbfw_string_return('rbfw_text_you_selected',__('You selected','booking-and-rental-manager-for-woocommerce')).': '.$result ?>
        </div>

    <?php } ?>

    <?php if($is_muffin_template == 1){ ?>
        <div class="rbfw_step_selected_date rbfw_muff_selected_date">
             <div class="rbfw_muff_selected_date_col">
                 <span class="rbfw_muff_selected_date_value"><?php echo $result ?></span>
             </div>
        </div>
    <?php } ?>

    <div class="rbfw_bikecarsd_time_table_wrap">
        <?php
        foreach ($available_times as $value) {
            $converted_time =  date("H:i", strtotime($value));
            $ts_time = $this->rbfw_get_time_slot_by_label($value);
            $is_booked = $this->rbfw_get_time_booking_status($id, $selected_date, $ts_time);
            $disabled = '';
            if((($nowDate == $selected_date) && ($converted_time < $nowTime)) || ($is_booked === true)){
                $disabled = 'disabled';
            }
            ?>
            <a data-time="<?php echo $ts_time ?>" class="rbfw_bikecarsd_time <?php echo $disabled ?>">
                <span class="rbfw_bikecarsd_time_span">
                    <?php echo $value ?>
                </span>
                <?php if($is_booked === true){ ?>
                    <span class="rbfw_bikecarsd_time_booked">
                        <?php echo rbfw_string_return('rbfw_text_booked',__('Booked','booking-and-rental-manager-for-woocommerce')) ?>
                    </span>
               <?php } ?>
            </a>
       <?php } ?>
    </div>
</div>
