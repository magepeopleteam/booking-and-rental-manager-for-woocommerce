/**
 * Customer Management JavaScript
 */
jQuery(document).ready(function($) {
    
    // Handle loyalty points redemption
    $('.rbfw-redeem-points-button').on('click', function(e) {
        e.preventDefault();
        
        var pointsToRedeem = $('#rbfw-points-to-redeem').val();
        
        if (pointsToRedeem <= 0) {
            alert('Please enter a valid number of points to redeem.');
            return;
        }
        
        if (confirm(rbfw_customer.redeem_confirm)) {
            $.ajax({
                type: 'POST',
                url: rbfw_customer.ajax_url,
                data: {
                    action: 'rbfw_redeem_loyalty_points',
                    nonce: rbfw_customer.nonce,
                    points: pointsToRedeem
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
    
    // Handle admin points adjustment
    $('#rbfw_update_points').on('click', function() {
        var pointsAdjustment = $('#rbfw_adjust_points').val();
        var userId = $('input[name="rbfw_user_id"]').val();
        var nonce = $('input[name="rbfw_loyalty_nonce"]').val();
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'rbfw_update_loyalty_points',
                nonce: nonce,
                user_id: userId,
                points: pointsAdjustment
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Add points redemption form to checkout if user has points
    if ($('.woocommerce-checkout').length && typeof rbfw_customer_points !== 'undefined' && rbfw_customer_points > 0) {
        var redeemForm = '<div class="rbfw-redeem-points-form">' +
            '<h3>Redeem Loyalty Points</h3>' +
            '<p>You have ' + rbfw_customer_points + ' points available.</p>' +
            '<label for="rbfw-points-to-redeem">Points to redeem:</label>' +
            '<input type="number" id="rbfw-points-to-redeem" min="1" max="' + rbfw_customer_points + '" value="0">' +
            '<button type="button" class="rbfw-redeem-points-button">Apply Points</button>' +
            '</div>';
        
        $('#order_review').before(redeemForm);
    }
});