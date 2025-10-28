jQuery(document).ready(function($) {
    'use strict';
    
    console.log('RBFW WooCommerce Products JS loaded');

    // WooCommerce Products Quantity Controls
    $(document).on('click', '.rbfw_wc_qty_plus', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Plus button clicked');
        
        var $input = $(this).siblings('input.rbfw_wc_qty');
        console.log('Input found:', $input.length);
        
        if ($input.length === 0) {
            console.log('No input found, trying alternative selector');
            $input = $(this).closest('.rbfw_qty_input').find('input.rbfw_wc_qty');
            console.log('Alternative input found:', $input.length);
        }
        
        if ($input.length > 0) {
            var currentVal = parseInt($input.val()) || 0;
            var maxVal = parseInt($input.data('max')) || 10;
            
            console.log('Current val:', currentVal, 'Max val:', maxVal);
            
            if (currentVal < maxVal) {
                $input.val(currentVal + 1).trigger('change');
                console.log('Updated to:', currentVal + 1);
            }
        }
    });

    $(document).on('click', '.rbfw_wc_qty_minus', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Minus button clicked');
        
        var $input = $(this).siblings('input.rbfw_wc_qty');
        console.log('Input found:', $input.length);
        
        if ($input.length === 0) {
            console.log('No input found, trying alternative selector');
            $input = $(this).closest('.rbfw_qty_input').find('input.rbfw_wc_qty');
            console.log('Alternative input found:', $input.length);
        }
        
        if ($input.length > 0) {
            var currentVal = parseInt($input.val()) || 0;
            
            console.log('Current val:', currentVal);
            
            if (currentVal > 0) {
                $input.val(currentVal - 1).trigger('change');
                console.log('Updated to:', currentVal - 1);
            }
        }
    });

    // Update WooCommerce Products Total in Booking Summary
    $(document).on('change', '.rbfw_wc_qty', function() {
        console.log('Quantity changed for:', $(this).val());
        updateWooCommerceProductsTotal();
    });

    function updateWooCommerceProductsTotal() {
        console.log('Updating WooCommerce products total');
        var total = 0;
        var hasProducts = false;
        
        $('.rbfw_wc_qty').each(function() {
            var quantity = parseInt($(this).val()) || 0;
            var price = parseFloat($(this).data('price')) || 0;
            
            console.log('Product qty:', quantity, 'price:', price);
            
            if (quantity > 0) {
                total += quantity * price;
                hasProducts = true;
            }
        });

        console.log('Total WC products cost:', total, 'Has products:', hasProducts);

        // Update the display
        var $wcProductsCost = $('.wc_products_cost');
        var $wcProductsSpan = $wcProductsCost.find('span');
        
        console.log('WC products cost element found:', $wcProductsCost.length);
        console.log('WC products span found:', $wcProductsSpan.length);
        
        if (hasProducts) {
            $wcProductsCost.removeClass('rbfw-cond').addClass('show');
            $wcProductsSpan.html(wc_price(total));
        } else {
            $wcProductsCost.removeClass('show').addClass('rbfw-cond');
            $wcProductsSpan.html(wc_price(0));
        }

        // Update subtotal
        updateBookingSubtotal();
    }

    function updateBookingSubtotal() {
        var durationCost = parseFloat($('.duration-costing span').text().replace(/[^0-9.-]+/g, '')) || 0;
        var resourceCost = parseFloat($('.extra_service_cost span').text().replace(/[^0-9.-]+/g, '')) || 0;
        var wcProductsCost = parseFloat($('.wc_products_cost span').text().replace(/[^0-9.-]+/g, '')) || 0;
        
        var subtotal = durationCost + resourceCost + wcProductsCost;
        
        $('.subtotal span').html(wc_price(subtotal));
        
        // Update total
        var securityDeposit = parseFloat($('.security_deposit span').text().replace(/[^0-9.-]+/g, '')) || 0;
        var total = subtotal + securityDeposit;
        
        $('.total span').html(wc_price(total));
    }

    // Helper function to format price (if wc_price is not available)
    function wc_price(amount) {
        if (typeof window.wc_price === 'function') {
            return window.wc_price(amount);
        }
        
        // Fallback price formatting
        var currencySymbol = '€'; // Default currency symbol
        if (typeof window.wc_currency_symbol !== 'undefined') {
            currencySymbol = window.wc_currency_symbol;
        }
        
        return currencySymbol + parseFloat(amount).toFixed(2);
    }

    // Initialize on page load
    updateWooCommerceProductsTotal();
});
