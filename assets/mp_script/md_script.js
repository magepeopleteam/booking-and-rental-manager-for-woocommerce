jQuery('body').on('change', '.pickup_date', function(e) {
    jQuery(".dropoff_date").val('');
});
jQuery('body').on('change', '#hidden_pickup_date, #hidden_dropoff_date, .pickup_time, .dropoff_time', function (e) {

    let pickup_date = jQuery('#pickup_date').val();
    let dropoff_date = jQuery('#dropoff_date').val();
    let pickup_time = jQuery('#pickup_time').find(':selected').val();
    let dropoff_time = jQuery('#dropoff_time').find(':selected').val();
    let rbfw_available_time = jQuery('#rbfw_available_time').val();
    let date_format = jQuery('#wp_date_format').val();


    if(!dropoff_date){
        //jQuery('.mps_alert_warning').remove();
        //jQuery('<div class="rbfw_nia_notice mps_alert_warning">Please enter drop off date!</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
    }


    if(rbfw_available_time=='yes'){
        if(pickup_date && dropoff_date && pickup_time && dropoff_time){
            rbfw_bikecarmd_ajax_price_calculation();
        }
    }else{
        if(pickup_date && dropoff_date){
            rbfw_bikecarmd_ajax_price_calculation();
        }
    }
});


/*day wise service start*/
jQuery('body').on('click',".rbfw_service_price_data",function(event) {
    var total_days = jQuery('[name="total_days"]').val();
    if(total_days){
        rbfw_service_price_calculation(total_days);
    }
});

jQuery("body").on('click','.rbfw_service_quantity_plus ', function (e) {
    e.preventDefault();
    var service_quantity = jQuery(this).prev('input').val();
    var max_value = jQuery(this).prev('input').attr('max');

    if(max_value>Number(service_quantity)){
        jQuery(this).prev('input').val(Number(service_quantity)+1 );
        var item_no = jQuery(this).data('item');
        jQuery('.item_'+item_no).data('quantity',Number(service_quantity)+1);
    }else{
        jQuery(this).css({"cursor": "text", "color": "#8c8f94"});
    }
    var total_days = jQuery('[name="total_days"]').val();

    if(total_days){
        rbfw_service_price_calculation(total_days);
    }
});

jQuery("body").on('click','.rbfw_service_quantity_minus', function (e) {
    e.preventDefault();
    var service_quantity = jQuery(this).next('input').val();
    //var post_id = jQuery(this).next('input').data('post_id');
    if(service_quantity>0){
        jQuery(this).next('input').val(Number(service_quantity)-1 );
        var item_no = jQuery(this).data('item');
        console.log('item_no',item_no);
        jQuery('.item_'+item_no).data('quantity',Number(service_quantity)-1);
        jQuery(this).css({"cursor": "pointer", "color": "#2271b1"});
    }
    var total_days = jQuery('[name="total_days"]').val();

    if(total_days){
        rbfw_service_price_calculation(total_days);
    }
});


jQuery(document).on('change', '#rbfw_item_quantity', function(e) {
    let that = jQuery(this);
    var total_days = jQuery('[name="total_days"]').val();

    if(total_days){
        rbfw_service_price_calculation(total_days);
    }
});



/*Extra service start*/



let service_price_arr_md = {};

jQuery('body').on('change','.rbfw-resource-price-multiple-qty',function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    let that = jQuery(this);
    let this_checkbox = jQuery(this);
    let this_checkbox_status = this_checkbox.attr('data-status');

    if (this_checkbox_status.length > 0) {
        if (this_checkbox_status == '0') {
            jQuery(this_checkbox).attr('data-status', '1');
            jQuery(this_checkbox).attr('checked', true);
            jQuery(this_checkbox).prop('checked', true);
            jQuery(this_checkbox).parents('div').siblings('.rbfw_bikecarmd_es_input_box').find('.rbfw_bikecarmd_es_qty').val('1').attr('value','1');
            jQuery(this_checkbox).val('1');
            jQuery(this_checkbox).parents('td').siblings('.rbfw_bikecarmd_es_input_box').show();
            jQuery(this_checkbox).parents('td').siblings('.resource-title-qty').find('.resource-qty').css('display','block');
            jQuery(this_checkbox).parent('.switch').siblings('.rbfw-resource-qty').val('1').attr('value','1');
        } else {
            jQuery(this_checkbox).parents('td').siblings('.rbfw_bikecarmd_es_input_box').hide();
            jQuery(this_checkbox).parents('td').siblings('.resource-title-qty').find('.resource-qty').hide();
            jQuery(this_checkbox).attr('data-status', '0');
            jQuery(this_checkbox).removeAttr('checked');
            jQuery(this_checkbox).prop('checked', false);
            jQuery(this_checkbox).parents('div').siblings('.rbfw_bikecarmd_es_input_box').find('.rbfw_bikecarmd_es_qty').val('0').attr('value','0');
            jQuery(this_checkbox).val('0');
            jQuery(this_checkbox).parents('div').siblings('.rbfw_bikecarmd_es_input_box').hide();
            jQuery(this_checkbox).parent('.switch').siblings('.rbfw-resource-qty').val('').attr('value','');
        }
    }

    let status = this_checkbox.attr('data-status');
    let data_name = jQuery(this_checkbox).attr('data-name');

    if(status != '1'){
        delete service_price_arr_md[data_name];
    }
    rbfw_es_service_price_calculation();
});

jQuery('body').on('click','.rbfw_bikecarmd_es_qty_plus',function (e) {

    e.preventDefault();
    var service_quantity = parseInt(jQuery(this).prev('input').val());
    var max_value = parseInt(jQuery(this).prev('input').attr('max'));

    if(max_value > service_quantity){
        jQuery(this).prev('input').val(service_quantity + 1 );
        var item_no = jQuery(this).data('item');
        jQuery('.key_value_'+item_no).data('quantity',service_quantity + 1);
        jQuery('.key_value_cart_'+item_no).val(service_quantity + 1);

        rbfw_es_service_price_calculation();

    }else{
        jQuery(this).css({"cursor": "text", "color": "#8c8f94"});
    }
});

jQuery('body').on('click','.rbfw_bikecarmd_es_qty_minus',function (e) {

    e.preventDefault();
    var service_quantity = parseInt(jQuery(this).next('input').val());
    var max_value = parseInt(jQuery(this).next('input').attr('max'));

    if(max_value >= service_quantity && service_quantity > 0 ){
        jQuery(this).next('input').val(service_quantity - 1 );
        var item_no = jQuery(this).data('item');
        jQuery('.key_value_'+item_no).data('quantity',service_quantity - 1);
        jQuery('.key_value_cart_'+item_no).val(service_quantity - 1);

        rbfw_es_service_price_calculation();

    }else{
        jQuery(this).css({"cursor": "text", "color": "#8c8f94"});
    }

});




jQuery('body').on('change','.rbfw_bikecarmd_es_qty',function (e) {

    e.preventDefault();
    let service_quantity = parseInt(jQuery(this).val());
    let max_value = parseInt(jQuery(this).attr('max'));

    if(service_quantity > max_value){
        jQuery(this).val(max_value);
        service_quantity = max_value;
     }

    var item_no = jQuery(this).data('item');
    jQuery('.key_value_'+item_no).data('quantity',service_quantity);
    jQuery('.key_value_cart_'+item_no).val(service_quantity);
    var countable_time = jQuery('[name="countable_time"]').val();

    rbfw_es_service_price_calculation();

});


/*Extra service end*/


function rbfw_service_price_calculation(total_days){
    jQuery(".rbfw_service_price_data").val(0);
    jQuery('.rbfw_service_quantity').css( "display", "none" );
    jQuery('.available-stock').css('display','none');
    var total = 0;
    jQuery(".rbfw_service_price_data:checked").each(function() {
        var item_no = jQuery(this).data('item');
        jQuery(this).val(1);
        var service_price_type =  jQuery(this).data('service_price_type');
        var service_quantity = jQuery(this).data('quantity');
        var rbfw_enable_md_type_item_qty = jQuery(this).data('rbfw_enable_md_type_item_qty');
        if(rbfw_enable_md_type_item_qty=='yes'){
            jQuery('.item_'+item_no).removeAttr('style');
            jQuery('.available-stock'+'.item_'+item_no).css('display','block');
        }
        if(service_price_type=='day_wise'){
            total +=  jQuery(this).data('price')*service_quantity*total_days;
        }else{
            total +=  jQuery(this).data('price')*service_quantity;
        }
    });
    jQuery('#rbfw_service_price').val(total);
    rbfw_bikecarmd_ajax_price_calculation();
}


function rbfw_es_service_price_calculation(total_days){

    var total = 0;
    jQuery(".rbfw-resource-price-multiple-qty:checked").each(function() {
        jQuery(this).val(1);
        var service_quantity = jQuery(this).data('quantity');
        total +=  jQuery(this).data('price')*service_quantity;
    });
    jQuery('#rbfw_es_service_price').val(total);
    rbfw_bikecarmd_ajax_price_calculation();
}



function rbfw_bikecarmd_ajax_price_calculation(stock_no_effect){


    let post_id = jQuery('[data-service-id]').data('service-id');

    let date_format = jQuery('#wp_date_format').val();


    let pickup_date = jQuery('#hidden_pickup_date').val();
    let dropoff_date = jQuery('#hidden_dropoff_date').val();




    let rbfw_available_time = jQuery('#rbfw_available_time').val();

    let pickup_time = jQuery('.pickup_time').find(':selected').val();
    let dropoff_time = jQuery('.dropoff_time').find(':selected').val();

    let item_quantity = jQuery('#rbfw_item_quantity').find(':selected').val();
    let rbfw_enable_variations = jQuery('#rbfw_enable_variations').val();

    var rbfw_input_stock_quantity = jQuery('#rbfw_input_stock_quantity').val();

    if(typeof item_quantity === "undefined"){
        item_quantity = jQuery("[name='rbfw_item_quantity']").val();
    }

    let rbfw_service_price = jQuery('#rbfw_service_price').val();
    let rbfw_es_service_price = jQuery('#rbfw_es_service_price').val();

    if(pickup_date == '' || dropoff_date == ''){
        return false;
    }



    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action' : 'rbfw_bikecarmd_ajax_price_calculation',
            'post_id': post_id,
            'date_format': date_format,
            'pickup_date': pickup_date,
            'pickup_time': pickup_time,
            'dropoff_date': dropoff_date,
            'dropoff_time': dropoff_time,
            'item_quantity': item_quantity,
            'rbfw_service_price': rbfw_service_price,
            'rbfw_enable_variations': rbfw_enable_variations,
            'rbfw_es_service_price': rbfw_es_service_price,
            'rbfw_available_time': rbfw_available_time

        },
        beforeSend: function() {
            jQuery('.rbfw_bike_car_md_item_wrapper').addClass('rbfw_loader_in');
            jQuery('.rbfw_bike_car_md_item_wrapper').append('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {
            jQuery('.rbfw_bike_car_md_item_wrapper').removeClass('rbfw_loader_in');
            jQuery('.rbfw_bike_car_md_item_wrapper i.fa-spinner').remove();

            jQuery('[name="total_days"]').val(response.total_days);

            jQuery('.duration-costing .price-figure').html(response.duration_price_html);
            jQuery('.resource-costing .price-figure').html(response.service_cost_html);
            jQuery('.subtotal .price-figure').html(response.sub_total_price_html);

            if(response.discount){
                jQuery('.discount').show();
                jQuery('.discount span').html(response.discount_html);
                jQuery('.discount').show();
            }else{
                jQuery('.discount').hide();
            }

            if(response.security_deposit_amount){
                jQuery('.security_deposit').show();
                jQuery('.security_deposit span').html(response.security_deposit_desc);
            }else{
                jQuery('.security_deposit').hide();
            }

            jQuery('.total .price-figure').html(response.total_price_html);
            jQuery('.rbfw-duration').show();
            jQuery('.rbfw-duration .item-content').html(response.total_duration);


            var remaining_stock =  response.max_available_qty.remaining_stock;
            var ticket_item_quantity =  response.ticket_item_quantity;

            var  quantity_options = '';
            for (let i = 0; i <= remaining_stock; i++) {
                var selected = '';
                if(ticket_item_quantity == i){
                    var selected = 'selected';
                }
                if(i==0){
                    quantity_options += "<option "+selected+" value="+i+">Choose number of quantity</option>";
                }else{
                    quantity_options += "<option "+selected+" value="+i+">"+i+"</option>";
                }
            }

            jQuery('#rbfw_item_quantity').html(quantity_options);


            jQuery('.rbfw_quantity_md').show();
            jQuery('.multi-service-category-section').show();
            jQuery('.rbfw-variations-content-wrapper').show();
            jQuery('.rbfw_resourse_md').show();
            jQuery('.rbfw_bikecarmd_price_result').show();
            jQuery('.rbfw_reg_form_rb').show();

            /*multiple service price day wise*/
            jQuery(".service-price-item").each(function(index, value) {
                if(response.max_available_qty.service_stock[index]==0){
                    jQuery(this).find(".rbfw-sold-out").show();
                    jQuery(this).find(".rbfw-checkbox").hide();
                    jQuery(this).find(".rbfw_service_price_data").data('quantity',0);
                }else{
                    jQuery(this).find(".rbfw-sold-out").hide();
                    jQuery(this).find(".rbfw-checkbox").show();
                }
                if(response.max_available_qty.service_stock[index]==0){
                    jQuery(this).find(".rbfw_service_info_stock").attr('value',response.max_available_qty.service_stock[index]);
                }
                jQuery(this).find(".rbfw_service_info_stock").attr('max',response.max_available_qty.service_stock[index]);

                jQuery(this).find(".remaining_stock").text('('+response.max_available_qty.service_stock[index]+')');
            });

            /*extra service */

            jQuery(".rbfw_bikecarmd_es_qty").each(function(index, value) {
                if(response.max_available_qty.extra_service_instock[index]==0){
                    jQuery(this).val(0);
                }
                jQuery(this).attr('max',response.max_available_qty.extra_service_instock[index]);
            });
            jQuery(".es_stock").each(function(index, value) {
                if(response.max_available_qty.extra_service_instock[index]==0){
                    jQuery(this).find(".rbfw-sold-out").show();
                    jQuery(this).find(".rbfw-checkbox").hide();
                }
                jQuery(this).text(response.max_available_qty.extra_service_instock[index]);
            });
            jQuery(".rbfw_bikecarmd_es_hidden_input_box").each(function(index, value) {
                if(response.max_available_qty.extra_service_instock[index]==0){
                    jQuery(this).find(".rbfw-sold-out").show();
                    jQuery(this).find(".rbfw-checkbox").hide();
                }else{
                    jQuery(this).find(".rbfw-sold-out").hide();
                    jQuery(this).find(".rbfw-checkbox").show();
                }
            });


            if(response.rbfw_enable_variations == 'yes'){
                var total_variation_stock = 0;
                jQuery(".rbfw_variant").each(function(index, value) {
                    var variant_text = jQuery(this).val();
                    if(response.max_available_qty.variant_instock[index]<response.ticket_item_quantity){
                        jQuery(this).attr("disabled", 'disabled');
                        jQuery(this).text(variant_text+' (Stock Out)');
                        if(jQuery(this).is(':selected')){
                            jQuery(this).removeAttr("selected");
                        }
                    }else{
                         total_variation_stock = 1;
                         jQuery(this).removeAttr("disabled");
                        jQuery(this).text(variant_text);
                    }
                });

                if((total_variation_stock == 0)) {
                    jQuery('.rbfw_nia_notice').remove();
                    jQuery('<div class="rbfw_nia_notice mps_alert_warning">No Items Available!</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
                    jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
                }else {
                    jQuery('.rbfw_nia_notice').remove();
                    jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
                }
            }else{

                if((rbfw_input_stock_quantity == 'no_has_value')) {
                    jQuery('.rbfw_nia_notice').remove();
                    jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
                }else{
                    if((response.max_available_qty.remaining_stock == 0)) {
                        jQuery('.rbfw_nia_notice').remove();
                        jQuery('<div class="rbfw_nia_notice mps_alert_warning">No Items Available!</div>').insertBefore(' button.rbfw_bikecarmd_book_now_btn');
                        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',true);
                    } else {
                        jQuery('.rbfw_nia_notice').remove();
                        jQuery('button.rbfw_bikecarmd_book_now_btn').attr('disabled',false);
                    }
                }
            }

        },
        error : function(response){
            console.log(response);
        }
    });
}