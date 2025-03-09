/**
 * Loyalty Program JavaScript
 */
jQuery(document).ready(function($) {
    // Generate coupon button click
    $('.rbfw-generate-coupon-btn').on('click', function() {
        var $button = $(this);
        var $message = $('.rbfw-coupon-message');
        var nonce = $button.data('nonce');
        
        // Disable button and show loading message
        $button.prop('disabled', true);
        $message.removeClass('success error').html(rbfw_loyalty.i18n.generating_coupon);
        
        // Send AJAX request
        $.ajax({
            url: rbfw_loyalty.ajax_url,
            type: 'POST',
            data: {
                action: 'rbfw_generate_loyalty_coupon',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update points display
                    $('.rbfw-loyalty-points-count').text(response.data.new_points);
                    
                    // Show success message
                    $message.addClass('success').html(
                        '<strong>' + rbfw_loyalty.i18n.success + '</strong> ' + 
                        response.data.message + '<br>' +
                        rbfw_loyalty.i18n.coupon_code + ' <span class="rbfw-coupon-code">' + 
                        response.data.coupon_code + '</span>'
                    );
                    
                    // Reload page after 3 seconds to show the new coupon in the list
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    // Show error message
                    $message.addClass('error').html('<strong>' + rbfw_loyalty.i18n.error + '</strong> ' + response.data.message);
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                // Show error message
                $message.addClass('error').html('<strong>' + rbfw_loyalty.i18n.error + '</strong> ' + 'An unexpected error occurred.');
                $button.prop('disabled', false);
            }
        });
    });
});