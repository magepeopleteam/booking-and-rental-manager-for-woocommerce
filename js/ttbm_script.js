(function ($) {
    "use strict";

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

    
}(jQuery));