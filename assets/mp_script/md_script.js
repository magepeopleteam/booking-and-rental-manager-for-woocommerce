function rbfw_bikecarmd_ajax_price_calculation(that, reload_es){

    if (typeof reload_es === 'undefined' || reload_es === null) {
        reload_es = 1;
    }

    let post_id = jQuery('[data-service-id]').data('service-id');
    let pickup_date = jQuery('#pickup_date').val();
    let dropoff_date = jQuery('#dropoff_date').val();

    let pickup_time = jQuery('#pickup_time').find(':selected').val();
    let dropoff_time = jQuery('#dropoff_time').find(':selected').val();
    let item_quantity = jQuery('#rbfw_item_quantity').find(':selected').val();

    if(pickup_date == '' || dropoff_date == ''){

        return false;
    }

    if (typeof item_quantity === "undefined" || item_quantity == '') {

        item_quantity = 1;
    }

    if((pickup_date == dropoff_date) && (typeof pickup_time === "undefined" || pickup_time == '')){

        pickup_time = '00:00';
    }

    if((pickup_date == dropoff_date) && (typeof dropoff_time === "undefined" || dropoff_time == '')){

        dropoff_time = rbfw_end_time();
    }

    let data_cat = that.attr('data-cat');

    if(data_cat == 'service'){

        let data_qty         = that.attr('value');
        let data_price        = that.attr('data-price');
        let data_name        = that.attr('data-name');

        if(data_qty == 0){

            delete service_price_arr[data_name];
        }
        else{

            service_price_arr[data_name]  = {'data_qty' : data_qty, 'data_price' : data_price};
        }
    }



    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarmd_ajax_price_calculation',
            'post_id': post_id,
            'pickup_date': pickup_date,
            'pickup_time': pickup_time,
            'dropoff_date': dropoff_date,
            'dropoff_time': dropoff_time,
            'item_quantity': item_quantity,
            'service_price_arr': service_price_arr,
            'reload_es': reload_es
        },
        beforeSend: function() {
            jQuery('.rbfw_bikecarmd_price_result').empty();
            jQuery('.rbfw_bikecarmd_price_result').append('<span class="rbfw-loader rbfw_rp_loader"><i class="fas fa-spinner fa-spin"></i></span>');

            if(reload_es === 1){
                jQuery('.rbfw-resource').empty();
            }
        },
        success: function (response) {

            jQuery(".rbfw_next_btn").slideToggle();
            jQuery(".rbfw_prev_btn").toggleClass('rbfw_d_block');
            jQuery(".rbfw_muff_registration_wrapper .rbfw_mps_book_now_btn_regf").slideToggle();
            jQuery('.rbfw_reg_form_rb').show();

            if (response.duration) {
                jQuery('.rbfw-duration').slideDown('fast').find('.item-content').text(response.duration);
            } else {
                jQuery('.rbfw-duration').slideUp('fast');
            }

            if(Object.keys(response.reload_es).length !== 0){
                jQuery('.rbfw-quantity').slideDown('fast').html(response.item_quantity_box);
                jQuery('#rbfw_item_quantity option[value="'+item_quantity+'"]').attr('selected','selected');
            }

            if (response.variation_content && Object.keys(response.variation_content).length !== 0) {
                jQuery('.rbfw-variations-content-wrapper').slideDown('fast').html(response.variation_content);
            }

            if (response.extra_service_content && Object.keys(response.extra_service_content).length !== 0) {
                jQuery('.rbfw-resource').slideDown('fast').html(response.extra_service_content);
            }

            jQuery('.rbfw_rp_loader').hide();

            jQuery('.rbfw_bikecarmd_price_result').html(response.content);

            jQuery(' button.rbfw_bikecarmd_book_now_btn').removeAttr('disabled');
            jQuery('.rbfw_next_btn').removeAttr('disabled');


            if((response.max_available_qty == 0)) {
                jQuery('.rbfw_nia_notice').remove();
                jQuery('<div class="rbfw_nia_notice mps_alert_warning">No Items Available!</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
                jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
            } else {
                jQuery('.rbfw_nia_notice').remove();
                jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
            }

        },
        error : function(response){
            console.log(response);
        }
    });
}