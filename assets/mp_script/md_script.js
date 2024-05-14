jQuery(".rbfw_service_price_data").click(function(event) {
    var total_days = jQuery('[name="total_days"]').val();
    if(total_days!=0){
        rbfw_service_price_calculation(total_days);
    }
});

jQuery("body").on('click','.rbfw_service_quantity_plus ', function (e) {
    e.preventDefault();
    var service_quantity = jQuery(this).prev('input').val();
    //var post_id = jQuery(this).prev('input').data('post_id');

    var max_value = jQuery(this).prev('input').attr('max');

    if(max_value>Number(service_quantity)){
        jQuery(this).prev('input').val(Number(service_quantity)+1 );

        var item_no = jQuery(this).data('item');
        jQuery('.item_'+item_no).data('quantity',Number(service_quantity)+1);

        //jQuery(this).find('.rbfw_service_price_data').data('quantity',Number(service_quantity)+1);
    }else{
        jQuery(this).css({"cursor": "text", "color": "#8c8f94"});
    }
    var total_days = jQuery('[name="total_days"]').val();
    if(total_days!=0){
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
    if(total_days!=0){
        rbfw_service_price_calculation(total_days);
    }
});


jQuery("body").on('change','.rbfw_service_qty.rbfw_service_info_stock', function (e) {
    e.preventDefault();
    var get_value = jQuery(this).val();
    let max_value = parseInt(jQuery(this).attr('max'));

    if(get_value <= max_value){
        jQuery(this).val(get_value);
        var item_no = jQuery(this).data('item');
        console.log('item_no', item_no);
        jQuery('.item_' + item_no).data('quantity', Number(get_value));
    }else{
        jQuery(this).val(max_value);
        var item_no = jQuery(this).data('item');
        console.log('item_no', item_no);
        jQuery('.item_' + item_no).data('quantity', Number(max_value));
    }



    var total_days = jQuery('[name="total_days"]').val();
    if(total_days!=0){
        rbfw_service_price_calculation(total_days);
    }
});


function rbfw_service_price_calculation(total_days){
    jQuery(".rbfw_service_price_data").val(0);
    jQuery('.rbfw_service_quantity').css( "display", "none" );
    var total = 0;
    jQuery(".rbfw_service_price_data:checked").each(function() {
        var item_no = jQuery(this).data('item');
        console.log('item_no',item_no);
        jQuery(this).val(1);
        var service_price_type =  jQuery(this).data('service_price_type');
        var service_quantity = jQuery(this).data('quantity');


        jQuery('.item_'+item_no).css( "display", "table" );
        if(service_price_type=='day_wise'){
            total +=  jQuery(this).data('price')*service_quantity*total_days;
        }else{
            total +=  jQuery(this).data('price')*service_quantity;
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
    var total_days = jQuery('[name="total_days"]').val();
    if(total_days!=0){
        rbfw_service_price_calculation(total_days);
    }
    //rbfw_bikecarmd_ajax_price_calculation(that, 0);
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

    if(typeof item_quantity === "undefined"){
        item_quantity = jQuery("[name='rbfw_item_quantity']").val();
    }

    let rbfw_service_price = jQuery('#rbfw_service_price').val();

    if(pickup_date == '' || dropoff_date == ''){
        return false;
    }


    if((pickup_date == dropoff_date) && (typeof pickup_time === "undefined" || pickup_time == '')){
      //  pickup_time = '00:00';
    }

    if((pickup_date == dropoff_date) && (typeof dropoff_time === "undefined" || dropoff_time == '')){
       // dropoff_time = rbfw_end_time();
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
            //jQuery('.rbfw-service-costing .price-figure').html(response.rbfw_service_price_html);
            jQuery('.resource-costing .price-figure').html(response.service_cost_html);
            jQuery('.subtotal .price-figure').html(response.sub_total_price_html);

            if(response.discount){
                jQuery('.discount span').html(response.discount);
            }else{
                jQuery('.discount').hide();
            }

            jQuery('.total .price-figure').html(response.total_price_html);
            jQuery('.rbfw-duration').show();
            jQuery('.rbfw-duration .item-content').html(response.total_duration);


            var remaining_stock =  response.max_available_qty.remaining_stock;
            var ticket_item_quantity =  response.ticket_item_quantity;

            var  quantity_options = ''
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

            jQuery('.rbfw_reg_form_rb').show();
            jQuery('[name="total_days"]').val(response.total_days);


            jQuery(".rbfw_service_info_stock").each(function(index, value) {
                if(response.max_available_qty.service_stock[index]==0){
                    jQuery(this).val(0);
                    let item = jQuery(this).data('item');
                    jQuery('.rbfw_service_price_data.item_'+item).data('quantity',0)
                }
                jQuery(this).attr('max',response.max_available_qty.service_stock[index]);
            });

            jQuery(".rbfw_bikecarmd_es_qty").each(function(index, value) {
                jQuery(this).attr('max',response.max_available_qty.extra_service_instock[index]);
            });

            jQuery('.rbfw_rp_loader').hide();

            if((response.max_available_qty.remaining_stock == 0)) {
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

