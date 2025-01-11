(function ($) {
    "use strict";
    //Accordion
    $(".rbfw-event-accordion").accordion({
        collapsible: true,
        active: false
    });
    $(document).on("click", ".rbfw_default_widget .rbfw_default_widget_title", function () {
        $(this).closest('.rbfw_default_widget').find('.rbfw_default_widget_content').slideToggle(250);
    });
    $(document).ready(function () {
        //after loaded call
        mprbfwTotalShow();
        //=============//
    });
    $(document).on("change", ".formControl[data-price]", function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
        let target = $(this);
        let value = parseInt(target.val());
        mprbfwTicketQtyValidation(target, value);
    });
    $(document).on("click", ".mp_rbfw_ticket_form .decQty ,.mp_rbfw_ticket_form .incQty", function () {
        let current = $(this);
        let target = current.closest('.qtyIncDec').find('input');
        let currentValue = parseInt(target.val());
        let value = current.hasClass('incQty') ? (currentValue + 1) : ((currentValue - 1) > 0 ? (currentValue - 1) : 0);
        mprbfwTicketQtyValidation(target, value);
    });
    $(document).on("click", ".mp_rbfw_book_now", function () {
        if (mp_rbfw_ticket_qty() > 0) {
            $(this).siblings('.mp_rbfw_book_now_submit').trigger('click');
        } else {
            alert('Please Select Ticket Type');
            return false;
        }
    });
    function mprbfwTicketQtyValidation(target, value) {
        let extraParents = target.closest('.mp_rbfw_ticket_extra');
        if (extraParents.length > 0) {
            if (mp_rbfw_ticket_qty() > 0) {
                extraParents.find('.formControl[data-price]').each(function () {
                    $(this).removeAttr('disabled');
                }).promise().done(function () {
                    mprbfwTicketQty(target, value);
                });
            } else {
                extraParents.find('.formControl[data-price]').each(function () {
                    $(this).attr("disabled", "disabled");
                }).promise().done(function () {
                    $('.mp_rbfw_ticket_form .mp_rbfw_ticket_type tbody tr:first-child').find('.formControl[data-price]').trigger('focus');
                });
            }
        } else {
            $('.mp_rbfw_ticket_extra').find('.formControl[data-price]').each(function () {
                $(this).removeAttr("disabled", "disabled");
            }).promise().done(function () {
                mprbfwTicketQty(target, value);
            });
        }
    }

    function mprbfwTicketQty(target, value) {
        let min = parseInt(target.attr('min'));
        let max = parseInt(target.attr('max'));
        target.parents('.qtyIncDec').find('.incQty , .decQty').removeClass('mage_disabled');
        if (value < min || isNaN(value) || value === 0) {
            value = min;
            target.parents('.qtyIncDec').find('.decQty').addClass('mage_disabled');
        }
        if (value > max) {
            value = max;
            target.parents('.qtyIncDec').find('.incQty').addClass('mage_disabled');
        }
        target.val(value);
        mprbfwTotalShow();
    }

    function mprbfwTotalShow() {
        let total = mprbfwTotalPrice();
        let qty = mp_rbfw_ticket_qty();
        $('.rbfw_ticket_calculation .rbfw_price').html(mp_rbfw_price_format(total));
        $('.rbfw_ticket_calculation .rbfw_qty').html(qty);
    }

    function mp_rbfw_ticket_qty() {
        let totalQty = 0;
        $('.mp_rbfw_ticket_type').find('.formControl[data-price]').each(function () {
            let qty = parseInt($(this).val());
            totalQty += qty;
            mp_rbfw_form_place($(this).closest('tr'), qty)
        });
        return totalQty > 0 ? totalQty : 0;
    }

    function mp_rbfw_form_place(parentTr, qty) {
        let target_tr = parentTr.next('tr');
        let target_form = target_tr.find('.rbfw-pro-user-reg-form');
        let formLength=target_form.length;
        if (qty > 0) {
            if (formLength !== qty) {
                let ticket_type = parentTr.find('[data-ticket-type-name]').attr('data-ticket-type-name');
                if (formLength > qty) {
                    for (let i=formLength;i>qty;i--) {
                        target_tr.find('.rbfw-pro-user-reg-form:last-child').slideUp(250).remove();
                    }
                } else {
                    let form_copy = $('[data-form-type="' + ticket_type + '"]').html();
                    for (let i=formLength;i<qty;i++) {
                        target_tr.find('td').append(form_copy).find('.rbfw-pro-user-reg-form:last-child').slideDown(250).find('h5.rbfw_default_widget_title strong').html(i+1);
                    }
                }
            }

        } else {
            target_form.slideUp(250).remove();
        }
    }

    function mprbfwTotalPrice() {
        let currentTarget = $('.formControl[data-price]');
        let total = 0;
        let totalQty = 0;
        currentTarget.each(function () {
            let unitPrice = parseFloat($(this).attr('data-price'));
            let qty = parseInt($(this).val());
            totalQty += qty;
            total = total + (unitPrice * qty > 0 ? unitPrice * qty : 0);
        });
        if (totalQty > 0) {
            currentTarget.removeClass('error');
        } else {
            currentTarget.addClass('error');
        }
        total = total.toFixed(2);
        let total_part = total.toString().split(".");
        total_part[0] = total_part[0].replace(/\B(?=(\d{3})+(?!\d))/g, $('input[name="currency_thousands_separator"]').val());
        total = total_part.join($('input[name="currency_decimal"]').val());
        return total;
    }

    function mp_rbfw_price_format(price) {
        let currency_position = $('input[name="currency_position"]').val();
        let currency_symbol = $('input[name="currency_symbol"]').val();
        let price_text ='';
        if (currency_position === 'right') {
            price_text = price + currency_symbol.val();
        } else if (currency_position === 'right_space') {
            price_text = price + '&nbsp;' + currency_symbol;
        } else if (currency_position === 'left') {
            price_text = currency_symbol + price;
        } else {
            price_text = currency_symbol + '&nbsp;' + price;
        }
        return price_text;
    }
}(jQuery));