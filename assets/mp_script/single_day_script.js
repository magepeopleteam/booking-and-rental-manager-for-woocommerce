jQuery('body').on('click',".rbfw_service_price_data_sd",function(event) {
    rbfw_service_price_calculation_sd();
});

jQuery("body").on('click','.rbfw_service_quantity_plus_sd', function (e) {
    e.preventDefault();
    var service_quantity = jQuery(this).prev('input').val();
    //var post_id = jQuery(this).prev('input').data('post_id');

/*    var max_value = jQuery(this).prev('input').attr('max');

    if(max_value>Number(service_quantity)){*/
        jQuery(this).prev('input').val(Number(service_quantity)+1 );
        var item_no = jQuery(this).data('item');
        jQuery('.item_'+item_no).data('quantity',Number(service_quantity)+1);


    rbfw_service_price_calculation_sd();

});

jQuery("body").on('click','.rbfw_service_quantity_minus_sd', function (e) {
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

    rbfw_service_price_calculation_sd();

});


function rbfw_service_price_calculation_sd(){
    jQuery(".rbfw_service_price_data").val(0);
    //jQuery('.rbfw_service_quantity').css( "display", "none" );
    var total = 0;
    jQuery(".rbfw_service_price_data_sd:checked").each(function() {
        var item_no = jQuery(this).data('item');
        console.log('item_no',item_no);
        jQuery(this).val(1);
        var service_price_type =  jQuery(this).data('service_price_type');
        var service_quantity = jQuery(this).data('quantity');
        var rbfw_enable_md_type_item_qty = jQuery(this).data('rbfw_enable_md_type_item_qty');

        console.log('rbfw_enable_md_type_item_qty',rbfw_enable_md_type_item_qty);

        rbfw_enable_md_type_item_qty = 'yes';


        if(rbfw_enable_md_type_item_qty=='yes'){
            jQuery('.item_'+item_no).css( "display", "table" );
        }

        total +=  jQuery(this).data('price')*service_quantity;

    });

    let post_id = jQuery('#rbfw_post_id').val();

    var bikecarsd_price_arr = [];
    var service_price_arr = [];

    console.log('post_id',post_id);

    var currentRequest = null;
    currentRequest = jQuery.ajax({
        type: 'POST',
        url: rbfw_ajax.rbfw_ajaxurl,
        data: {
            'action'        : 'rbfw_bikecarsd_ajax_price_calculation',
            'post_id': post_id,
            'bikecarsd_price_arr': bikecarsd_price_arr,
            'service_price_arr': service_price_arr,
            'rbfw_service_price': total,
        },
        beforeSend: function() {
            if(currentRequest != null) {
                currentRequest.abort();
            }
            jQuery('.rbfw_bikecarsd_price_summary').addClass('old');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').addClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').append('<i class="fas fa-spinner fa-spin"></i>');
            jQuery(' button.rbfw_bikecarsd_book_now_btn').attr('disabled',true);
        },
        success: function (response) {
            jQuery('.rbfw_bikecarsd_pricing_table_wrap').removeClass('rbfw_loader_in');
            jQuery('.rbfw_bikecarsd_pricing_table_wrap i.fa-spinner').remove();
            jQuery(response).insertAfter('.rbfw_bikecarsd_price_summary.old');
            jQuery('.rbfw_bikecarsd_price_summary.old').remove();
            jQuery(' button.rbfw_bikecarsd_book_now_btn').removeAttr('disabled');

        }
    });



   /* jQuery('#rbfw_service_price').val(total);

    let that = jQuery(this);
    rbfw_bikecarmd_ajax_price_calculation(that, 0);*/
}


function openCity(evt, cityName) {
    evt.preventDefault();
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    jQuery( ".switch input" ).prop( "checked", false );
    rbfw_service_price_calculation();
    evt.currentTarget.className += " active";
}