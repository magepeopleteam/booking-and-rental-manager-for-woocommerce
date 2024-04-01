jQuery(".rbfw_service_price_data").click(function(event) {
    var total_days = jQuery('[name="total_days"]').val();
    if(total_days!=0){
        rbfw_service_price_calculation(total_days);
    }
});

function rbfw_service_price_calculation(total_days){
    jQuery(".rbfw_service_price_data").val(0);
    var total = 0;
    jQuery(".rbfw_service_price_data:checked").each(function() {
        jQuery(this).val(1);
        var service_price_type =  jQuery(this).data('service_price_type');
        if(service_price_type=='day_wise'){
            total +=  jQuery(this).data('price')*total_days;
        }else{
            total +=  jQuery(this).data('price');
        }
    });
    jQuery('#rbfw_service_price').val(total);

    let that = jQuery(this);
    rbfw_bikecarmd_ajax_price_calculation(that, 0);
}


jQuery('.rbfw_bikecarmd_es_qty_minus,.rbfw_bikecarmd_es_qty_plus').click(function (e) {
    let that = jQuery(this).siblings('.rbfw_bikecarmd_es_qty');
    rbfw_bikecarmd_ajax_price_calculation(that, 0);
});

jQuery('#pickup_date,#dropoff_date,#pickup_time,#dropoff_time').change(function (e) {
    let that = jQuery(this);
    rbfw_bikecarmd_ajax_price_calculation(that, 0);
    service_price_arr = {};
});

jQuery('.rbfw_bikecarmd_es_qty').change(function (e) {
    let that = jQuery(this);
    rbfw_bikecarmd_ajax_price_calculation(that, 0);
});

jQuery(document).on('change', '#rbfw_item_quantity', function(e) {
    let that = jQuery(this);
    rbfw_bikecarmd_ajax_price_calculation(that, 0);
});



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
    let rbfw_service_price = jQuery('#rbfw_service_price').val();

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
            'rbfw_service_price': rbfw_service_price,
            'service_price_arr': service_price_arr,
            'reload_es': reload_es
        },
        beforeSend: function() {

            jQuery('.rbfw_bikecarmd_price_result').append('<span class="rbfw-loader rbfw_rp_loader"><i class="fas fa-spinner fa-spin"></i></span>');

        },
        success: function (response) {

            jQuery('.duration-costing .price-figure').html(response.duration_price_html);
            jQuery('.rbfw-service-costing .price-figure').html(response.rbfw_service_price_html);
            jQuery('.resource-costing .price-figure').html(response.service_cost_html);
            jQuery('.subtotal .price-figure').html(response.sub_total_price_html);
            jQuery('.discount span').html(response.discount);
            jQuery('.total .price-figure').html(response.total_price_html);
            jQuery('.rbfw-duration').show();
            jQuery('.rbfw-duration .item-content').html(response.total_duration);



            /*jQuery(".rbfw_next_btn").slideToggle();
            jQuery(".rbfw_prev_btn").toggleClass('rbfw_d_block');
            jQuery(".rbfw_muff_registration_wrapper .rbfw_mps_book_now_btn_regf").slideToggle();*/

            jQuery('.rbfw_reg_form_rb').show();
            jQuery('[name="total_days"]').val(response.total_days);


          /*  if(Object.keys(response.reload_es).length !== 0){
                jQuery('.rbfw-quantity').slideDown('fast').html(response.item_quantity_box);
                jQuery('#rbfw_item_quantity option[value="'+item_quantity+'"]').attr('selected','selected');
            }*/

            jQuery('.rbfw_rp_loader').hide();

            //jQuery('.rbfw_bikecarmd_price_result').html('');

            //jQuery(' button.rbfw_bikecarmd_book_now_btn').removeAttr('disabled');
           // jQuery('.rbfw_next_btn').removeAttr('disabled');


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