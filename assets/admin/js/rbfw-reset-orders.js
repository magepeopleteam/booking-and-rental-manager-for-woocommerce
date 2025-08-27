/**
 * RBFW Reset Orders JavaScript
 * Professional JS for the reset orders functionality
 */

(function($) {
    'use strict';

    /**
     * RBFW Reset Orders Handler
     */
    var RBFWResetOrders = {
        
        /**
         * Initialize the reset orders functionality
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $(document).on('click', '#rbfw-reset-orders-btn', this.handleResetClick);
        },

        /**
         * Handle reset button click
         */
        handleResetClick: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var itemId = $button.data('item-id');
            var $resultDiv = $('#rbfw-reset-orders-result');
            
            // Validate item ID
            if (!itemId) {
                RBFWResetOrders.showMessage($resultDiv, 'error', rbfw_reset_orders.messages.invalid_item);
                return;
            }
            
            // Show confirmation dialog
            if (!confirm(rbfw_reset_orders.messages.confirm)) {
                return;
            }
            
            RBFWResetOrders.processReset($button, itemId, $resultDiv);
        },

        /**
         * Process the reset request
         */
        processReset: function($button, itemId, $resultDiv) {
            // Update button state
            this.setButtonState($button, true);
            $resultDiv.html('');
            
            // Make AJAX request
            $.ajax({
                url: rbfw_reset_orders.ajax_url,
                type: 'POST',
                data: {
                    action: 'rbfw_cancel_all_orders',
                    item_id: itemId,
                    nonce: rbfw_reset_orders.nonce
                },
                success: function(response) {
                    RBFWResetOrders.handleResponse(response, $resultDiv);
                },
                error: function(xhr, status, error) {
                    console.error('RBFW Reset Orders Error:', error);
                    RBFWResetOrders.showMessage($resultDiv, 'error', rbfw_reset_orders.messages.ajax_error);
                },
                complete: function() {
                    RBFWResetOrders.setButtonState($button, false);
                }
            });
        },

        /**
         * Handle AJAX response
         */
        handleResponse: function(response, $resultDiv) {
            if (response.success) {
                this.showMessage($resultDiv, 'success', response.data.message);
            } else {
                var message = response.data && response.data.message ? 
                    response.data.message : rbfw_reset_orders.messages.unknown_error;
                this.showMessage($resultDiv, 'error', message);
            }
        },

        /**
         * Show message in result div
         */
        showMessage: function($resultDiv, type, message) {
            var className = type === 'success' ? 'rbfw-success' : 'rbfw-error';
            $resultDiv.html('<div class="' + className + '">' + message + '</div>');
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(function() {
                    $resultDiv.fadeOut(300);
                }, 5000);
            }
        },

        /**
         * Set button state (enabled/disabled)
         */
        setButtonState: function($button, isProcessing) {
            if (isProcessing) {
                $button.prop('disabled', true)
                       .text(rbfw_reset_orders.messages.processing);
            } else {
                $button.prop('disabled', false)
                       .text(rbfw_reset_orders.messages.button_text);
            }
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        RBFWResetOrders.init();
    });

})(jQuery);
