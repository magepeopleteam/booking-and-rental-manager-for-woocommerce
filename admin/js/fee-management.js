jQuery(document).ready(function($) {
    'use strict';

    // Fee Management JavaScript - Exact match to your HTML
    const icons = ['🔒', '🛡️', '🧹', '🐾', '💰'];
    const colors = ['security', 'insurance', 'cleaning', 'pet'];

    // Add new fee row - exact match to your HTML
    window.rbfwAddFeeRow = function() {
        const tbody = document.getElementById('wprently_fee_body');
        const icon = icons[Math.floor(Math.random() * icons.length)];
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>
                <div class="wprently_fee-type">
                    <div class="wprently_fee-icon ${color}">${icon}</div>
                    <div class="wprently_fee-info">
                        <input type="text" class="wprently_fee-input" placeholder="Fee label">
                        <input type="text" class="wprently_fee-input" placeholder="Description">
                    </div>
                </div>
            </td>
            <td>
                <div class="wprently_fee-amount">
                    <select class="wprently_fee-input" onchange="updateCalculationType(this)">
                        <option>Percentage</option>
                        <option selected>Fixed</option>
                    </select>
                    <input type="number" class="wprently_fee-input" value="0">
                    <span>$</span>
                </div>
            </td>
            <td>
                <select class="wprently_fee-input">
                    <option selected>One-time</option>
                    <option>Per day</option>
                    <option>Per night</option>
                </select>
            </td>
            <td>
                <select class="wprently_fee-input">
                    <option selected>At booking</option>
                    <option>At check-in</option>
                </select>
            </td>
            <td>
                <div class="wprently_fee-badges">
                    <span class="wprently_fee-badge non-refundable">Non-refund</span>
                </div>
            </td>
            <td>
                <div class="wprently_fee-status">
                    <label class="wprently_fee-toggle">
                        <input type="checkbox" checked onchange="updateStatus(this)">
                        <span class="wprently_fee-slider"></span>
                    </label>
                    <div class="wprently_fee-status-badge active">
                        <span class="wprently_fee-status-dot"></span>Active
                    </div>
                </div>
            </td>
            <td>
                <div class="wprently_fee-actions">
                    <button class="wprently_fee-btn-icon">⎘</button>
                    <button class="wprently_fee-btn-icon" onclick="deleteRow(this)">✕</button>
                </div>
            </td>
        `;
    };

    // Update calculation type symbol - exact match to your HTML
    window.updateCalculationType = function(select) {
        const span = select.parentElement.querySelector('span');
        span.textContent = select.value === 'Percentage' ? '%' : '$';
    };

    // Update status badge - exact match to your HTML
    window.updateStatus = function(checkbox) {
        const badge = checkbox.closest('td').querySelector('.wprently_fee-status-badge');
        if (checkbox.checked) {
            badge.className = 'wprently_fee-status-badge active';
            badge.innerHTML = '<span class="wprently_fee-status-dot"></span>Active';
        } else {
            badge.className = 'wprently_fee-status-badge inactive';
            badge.innerHTML = '<span class="wprently_fee-status-dot"></span>Inactive';
        }
    };

    // Delete fee row - exact match to your HTML
    window.deleteRow = function(btn) {
        btn.closest('tr').remove();
    };

    // Add fee row - exact match to your HTML
    window.addFeeRow = function() {
        const tbody = document.getElementById('wprently_fee_body');
        const icon = icons[Math.floor(Math.random() * icons.length)];
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>
                <div class="wprently_fee-type">
                    <div class="wprently_fee-icon ${color}">${icon}</div>
                    <div class="wprently_fee-info">
                        <input type="text" class="wprently_fee-input" placeholder="Fee label">
                        <input type="text" class="wprently_fee-input" placeholder="Description">
                    </div>
                </div>
            </td>
            <td>
                <div class="wprently_fee-amount">
                    <select class="wprently_fee-input" onchange="updateCalculationType(this)">
                        <option>Percentage</option>
                        <option selected>Fixed</option>
                    </select>
                    <input type="number" class="wprently_fee-input" value="0">
                    <span>$</span>
                </div>
            </td>
            <td>
                <select class="wprently_fee-input">
                    <option selected>One-time</option>
                    <option>Per day</option>
                    <option>Per night</option>
                </select>
            </td>
            <td>
                <select class="wprently_fee-input">
                    <option selected>At booking</option>
                    <option>At check-in</option>
                </select>
            </td>
            <td>
                <div class="wprently_fee-badges">
                    <span class="wprently_fee-badge non-refundable">Non-refund</span>
                </div>
            </td>
            <td>
                <div class="wprently_fee-status">
                    <label class="wprently_fee-toggle">
                        <input type="checkbox" checked onchange="updateStatus(this)">
                        <span class="wprently_fee-slider"></span>
                    </label>
                    <div class="wprently_fee-status-badge active">
                        <span class="wprently_fee-status-dot"></span>Active
                    </div>
                </div>
            </td>
            <td>
                <div class="wprently_fee-actions">
                    <button class="wprently_fee-btn-icon">⎘</button>
                    <button class="wprently_fee-btn-icon" onclick="deleteRow(this)">✕</button>
                </div>
            </td>
        `;
    };

    // Show message
    function rbfwShowMessage(message, type) {
        // Remove existing messages
        $('.rbfw-fee-message').remove();
        
        const messageClass = type === 'success' ? 'notice-success' : 'notice-error';
        const messageHtml = `
            <div class="notice ${messageClass} is-dismissible rbfw-fee-message">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `;
        
        $('.wprently_fee-container').before(messageHtml);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $('.rbfw-fee-message').fadeOut();
        }, 3000);
    }

    // Handle form submission to save fees
    $(document).on('submit', '#post', function() {
        // Collect all fee data
        const fees = [];
        $('#wprently_fee_body tr[data-index]').each(function() {
            const row = $(this);
            const index = row.attr('data-index');
            
            const fee = {
                label: row.find('input[name*="[label]"]').val(),
                description: row.find('input[name*="[description]"]').val(),
                calculation_type: row.find('select[name*="[calculation_type]"]').val(),
                amount: parseFloat(row.find('input[name*="[amount]"]').val()) || 0,
                frequency: row.find('select[name*="[frequency]"]').val(),
                when_to_apply: row.find('select[name*="[when_to_apply]"]').val(),
                refundable: row.find('input[name*="[refundable]"]').val() === '1',
                taxable: row.find('input[name*="[taxable]"]').val() === '1',
                active: row.find('input[name*="[active]"]').is(':checked')
            };
            
            fees.push(fee);
        });
        
        // Update hidden input with fees data
        $('input[name="rbfw_fees_data"]').remove();
        $('<input>').attr({
            type: 'hidden',
            name: 'rbfw_fees_data',
            value: JSON.stringify(fees)
        }).appendTo('#post');
    });

    // Initialize existing calculation type symbols
    $('.wprently_fee-amount select').each(function() {
        rbfwUpdateCalculationType(this);
    });

    // Handle dynamic calculation type changes - exact match to your HTML
    document.addEventListener('change', function(e) {
        if (e.target.matches('.wprently_fee-amount select')) {
            const span = e.target.parentElement.querySelector('span');
            span.textContent = e.target.value === 'Percentage' ? '%' : '$';
        }
    });
});
