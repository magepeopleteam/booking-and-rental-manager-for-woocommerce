jQuery(document).ready(function($) {


    jQuery(".dependency-field").formFieldDependency({});
    jQuery(".sortable").sortable({ handle: ".sort" });
    jQuery('.colorpicker').wpColorPicker();


    jQuery(document).on('click', '.field-switcher-wrapper .switcher .layer', function() {
        if (jQuery(this).parent().hasClass('checked')) {
            jQuery(this).parent().removeClass('checked');
        } else {
            jQuery(this).parent().addClass('checked');
        }
    })

    jQuery(document).on('click', '.field-img-select-wrapper .sw-button img', function() {
        var dataId = jQuery(this).attr('data-id');
        var src = jQuery(this).attr('src');
        jQuery('.field-img-select-wrapper-' + dataId + ' .img-val input').val(src);
        jQuery('.field-img-select-wrapper-' + dataId + ' label').removeClass('checked');
        if (jQuery(this).parent().parent().hasClass('checked')) {
            jQuery(this).parent().parent().removeClass('checked');
        } else {
            jQuery(this).parent().parent().addClass('checked');
        }
    })


    jQuery(document).on('change', '.field-range-input-wrapper .range-hndle', function() {
        val = jQuery(this).val();
        jQuery(this).parent().children('.range-val').val(val);
    })
    jQuery(document).on('keyup', '.field-range-input-wrapper .range-val', function() {
        val = jQuery(this).val();
        jQuery(this).parent().children('.range-hndle').val(val);
    })


    jQuery(document).on('click', '.field-switch-wrapper .sw-button', function() {

        jQuery(this).parent().parent().children('label').removeClass('checked');
        //jQuery('.field-switch-wrapper label').removeClass('checked');

        if (jQuery(this).parent().hasClass('checked')) {
            jQuery(this).parent().removeClass('checked');
        } else {
            jQuery(this).parent().addClass('checked');
        }
    })


    jQuery(document).on('click', '.field-switch-multi-wrapper .sw-button', function() {
        if (jQuery(this).parent().hasClass('checked')) {
            jQuery(this).parent().removeClass('checked');
        } else {
            jQuery(this).parent().addClass('checked');
        }
    })

    jQuery(document).on('click', '.field-switch-img-wrapper .sw-button img', function() {

        jQuery(this).parent().parent().children('label').removeClass('checked');
        //jQuery('.field-switch-img-wrapper label').removeClass('checked');


        if (jQuery(this).parent().parent().hasClass('checked')) {
            jQuery(this).parent().parent().removeClass('checked');
        } else {
            jQuery(this).parent().parent().addClass('checked');
        }
    })



    jQuery(document).on('click', '.field-time-format-wrapper .format-list input[type="radio"]', function() {
        value = jQuery(this).val();
        jQuery(this).parent().parent().parent().children('.format-value').children('.format').children('input').val(value);
        //jQuery(this).parent().parent().parent().children('.format-value').children('input').val(value);

    })


    jQuery(document).on('click', '.field-date-format-wrapper .format-list input[type="radio"]', function() {
        value = jQuery(this).val();
        jQuery(this).parent().parent().parent().children('.format-value').children('.format').children('input').val(value);
        //jQuery('.field-date-format-wrapper .format-value input').val(value);
    })


    /*field-icon-wrapper*/

    jQuery(document).on('click', '.field-icon-wrapper .select-icon', function() {
        if (jQuery(this).parent().hasClass('active')) {
            jQuery(this).parent().removeClass('active');
        } else {
            jQuery(this).parent().addClass('active');
        }
    })
    jQuery(document).on('keyup', '.field-icon-wrapper .search-icon input', function() {

        text = jQuery(this).val();

        jQuery(this).parent().parent().children('ul').children('li').each(function(index) {
            console.log(index + ": " + jQuery(this).attr('title'));
            title = jQuery(this).attr('title');
            n = title.indexOf(text);
            if (n < 0) {
                jQuery(this).hide();
            } else {
                jQuery(this).show();
            }
        });
    })
    jQuery(document).on('click', '.field-icon-wrapper .icon-list li', function() {
        iconData = jQuery(this).attr('iconData');
        html = '<i class="' + iconData + '"></i>';

        jQuery(this).parent().parent().parent().children('.icon-wrapper').children('span').html(html);
        jQuery(this).parent().parent().parent().children('.icon-wrapper').children('input').val(iconData);

        //jQuery('.field-icon-wrapper .icon-wrapper input').val(iconData);
    })


    // jQuery('.field-select2-wrapper select').select2({
    //     width: '320px',
    //     allowClear: true

    // });



    jQuery(document).on('click', '.field-option-group-tabs-wrapper .tab-navs li', function() {

        index = jQuery(this).attr('index');

        jQuery(".field-option-group-tabs-wrapper .tab-navs li").removeClass('active');
        jQuery(".field-option-group-tabs-wrapper .tab-content").removeClass('active');
        if (jQuery(this).hasClass('active')) {

        } else {
            jQuery(this).addClass('active');
            jQuery(".field-option-group-tabs-wrapper .tab-content-" + index).addClass('active');
        }



    })



    jQuery(document).on('click', '.field-color-sets-wrapper .color-srick', function() {

        jQuery('.field-color-sets-wrapper label').removeClass('checked');
        if (jQuery(this).parent().hasClass('checked')) {
            jQuery(this).parent().removeClass('checked');
        } else {
            jQuery(this).parent().addClass('checked');
        }


    })

    jQuery(document).on('click', '.field-color-palette-wrapper .sw-button', function() {
        jQuery('.field-color-palette-wrapper label').removeClass('checked');
        if (jQuery(this).parent().hasClass('checked')) {
            jQuery(this).parent().removeClass('checked');
        } else {
            jQuery(this).parent().addClass('checked');
        }
    })



    jQuery(document).on('click', '.field-color-palette-multi-wrapper .sw-button', function() {
        if (jQuery(this).parent().hasClass('checked')) {
            jQuery(this).parent().removeClass('checked');
        } else {
            jQuery(this).parent().addClass('checked');
        }
    })




    jQuery(document).on('keyup', '.field-password-wrapper input', function() {
        pass = jQuery(this).val();
        var score = 0;
        if (!pass)
            return score;
        // award every unique letter until 5 repetitions
        var letters = new Object();
        for (var i = 0; i < pass.length; i++) {
            letters[pass[i]] = (letters[pass[i]] || 0) + 1;
            score += 5.0 / letters[pass[i]];
        }
        // bonus points for mixing it up
        var variations = {
            digits: /\d/.test(pass),
            lower: /[a-z]/.test(pass),
            upper: /[A-Z]/.test(pass),
            nonWords: /\W/.test(pass),
        }
        variationCount = 0;
        for (var check in variations) {
            variationCount += (variations[check] == true) ? 1 : 0;
        }
        score += (variationCount - 1) * 10;
        if (score > 80) {
            score_style = '#4CAF50;';
            score_text = 'Strong';
        } else if (score > 60) {
            score_style = '#cddc39;';
            score_text = 'Good';
        } else if (score > 30) {
            score_style = '#FF9800;';
            score_text = 'Normal';
        } else {
            score_style = '#F44336;';
            score_text = 'Week';
        }
        html = '<span style="width:' + parseInt(score) + '%;background-color: ' + score_style + '"></span>';
        jQuery(".field-password-wrapper- .scorePassword").html(html)
        jQuery(".field-password-wrapper- .scoreText").html(score_text)
    })


    jQuery(document).on('keyup', 'input.search-options', function() {
        keyword = jQuery(this).val();

        if (keyword != '') {
            jQuery('.form-table tr th').each(function(index) {
                title = jQuery(this).text();
                // console.log( index + ": " + title );

                title = title.toLowerCase();

                n = title.indexOf(keyword);
                if (n < 0) {
                    jQuery(this).parent().hide();
                } else {
                    jQuery(this).parent().show();
                }
            });


            jQuery('.form-section .tab-content').each(function(index) {

                jQuery(this).show();

            });


            jQuery('.form-section .tab-content h2').each(function(index) {

                jQuery(this).hide();

            });

        } else {

            jQuery('.form-table tr th').each(function(index) {
                jQuery(this).parent().show();
            });


            jQuery('.form-section .tab-content').each(function(index) {

                if (index == 0) {
                    jQuery(this).addClass('active');
                    jQuery(this).show();
                } else {
                    jQuery(this).removeClass('active');
                    jQuery(this).removeAttr('style');
                }


            });

            jQuery('.form-section .tab-content h2').each(function(index) {

                jQuery(this).show();

            });
        }





    })



    // jQuery(document).on('click','.ppof-settings .nav-items .child-nav-icon',function(event){
    //     event.preventDefault()
    //     //dataid = jQuery(this).attr('dataid');
    //
    //     if(jQuery(this).parent().parent().hasClass('active')){
    //         jQuery( this ).parent().parent().removeClass('active');
    //
    //     }else{
    //         jQuery( this ).parent().parent().addClass('active');
    //     }
    //
    //     //jQuery('.nav-items .nav-item').removeClass('active');
    //    // jQuery(this).addClass('active');
    //     //jQuery('.tab-content').removeClass('active');
    //     //jQuery('.tab-content-'+dataid).addClass('active');
    // })









    jQuery(document).on('click', '.ppof-settings .nav-items .nav-item', function(event) {
        event.preventDefault()
        dataid = jQuery(this).attr('dataid');
        sectionId = jQuery(this).attr('sectionId');
        //jQuery('.nav-item-wrap').removeClass('active');

        if (jQuery(this).parent().hasClass('active')) {
            jQuery(this).parent().removeClass('active');

        } else {
            jQuery(this).parent().addClass('active');
        }


        jQuery('.nav-items .nav-item').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('.tab-content').removeClass('active');
        jQuery('.tab-content-' + dataid).addClass('active');

        if (sectionId != null) {
            jQuery('html, body, .edit-post-layout__content').animate({
                scrollTop: (jQuery("#" + sectionId).offset().top - 80)
            }, 500);
        }



    })
});