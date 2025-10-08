# Fee Management System Documentation

## Overview
The Fee Management System is a comprehensive solution for managing additional fees in the Booking and Rental Manager for WooCommerce plugin. This system allows administrators to configure, manage, and apply various types of fees to rental bookings with flexible calculation methods and professional administration interface.

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Features](#features)
3. [File Structure](#file-structure)
4. [Implementation Details](#implementation-details)
5. [Usage Guide](#usage-guide)
6. [Database Schema](#database-schema)
7. [Frontend Integration](#frontend-integration)
8. [API Reference](#api-reference)
9. [Customization](#customization)
10. [Troubleshooting](#troubleshooting)

## Architecture Overview

The Fee Management System follows WordPress plugin development best practices with a modular architecture:

```
Fee Management System
├── Admin Interface (Fee_Management.php)
├── Helper Functions (rbfw_fee_functions.php)
├── Styling (fee-management.css)
├── Database Integration (WordPress Post Meta)
└── Frontend Integration (WooCommerce Hooks)
```

### Core Components

1. **Admin Settings Tab**: Complete CRUD interface for fee management
2. **Helper Functions**: Utility functions for fee calculations and operations
3. **Database Layer**: WordPress post meta storage with sanitization
4. **Frontend Integration**: WooCommerce cart and checkout integration
5. **Responsive Design**: Mobile-friendly admin interface

## Features

### ✅ Complete CRUD Operations
- **Create**: Add new fees with various configuration options
- **Read**: Display fees in professional table format
- **Update**: Edit existing fee settings with real-time updates
- **Delete**: Remove fees with confirmation dialogs

### ✅ Fee Configuration Options
- **Fee Label**: Custom name for the fee
- **Description**: Detailed explanation of the fee purpose
- **Calculation Type**: Fixed amount or percentage-based
- **Amount**: Numeric value for fee calculation
- **Frequency**: One-time, per-day, or per-night application
- **Priority**: Optional or Required designation
- **Refundable Status**: Toggle between refundable and non-refundable
- **Status**: Active/Inactive toggle for fee availability

### ✅ Professional Admin Interface
- Responsive table design with horizontal scrolling
- Color-coded badges for visual feedback
- Interactive toggles for boolean settings
- Real-time form validation
- Professional styling following WordPress admin standards

### ✅ Data Integrity & Security
- WordPress nonce verification for all operations
- Input sanitization using WordPress functions
- User capability checks for admin access
- Proper data validation and error handling

## File Structure

```
booking-and-rental-manager-for-woocommerce/
├── admin/
│   ├── admin.php                     # Main admin file (includes Fee_Management)
│   └── settings/
│       └── Fee_Management.php        # Core fee management class
├── inc/
│   ├── rbfw_file_include.php        # File inclusion handler
│   └── rbfw_fee_functions.php       # Helper functions for fee operations
├── css/
│   └── fee-management.css           # Dedicated styling (future file)
└── README_FEE_MANAGEMENT.md         # This documentation file
```

## Implementation Details

### Class Structure: RBFW_Fee_Management

```php
class RBFW_Fee_Management {
    public function __construct()           // Hook initialization
    public function add_tab_menu()          // Add admin tab menu item
    public function add_tabs_content()      // Render tab content
    public function fee_management_table()  // Generate main table interface
    private function render_fee_row()       // Render individual fee rows
    private function render_fee_table()     // Render complete table structure
    public function settings_save()         // Handle form submission and data saving
    public function ajax_add_fee_row()      // AJAX handler for adding rows
    public function ajax_delete_fee_row()   // AJAX handler for deleting rows
    public function enqueue_admin_scripts() // Load admin CSS/JS
    public function enqueue_frontend_scripts() // Load frontend assets
}
```

### Data Structure

Each fee is stored as an array with the following structure:

```php
$fee_data = array(
    'label'            => string,    // Fee display name
    'description'      => string,    // Fee description
    'calculation_type' => string,    // 'fixed' or 'percentage'
    'amount'           => float,     // Numeric fee amount
    'frequency'        => string,    // 'one-time', 'per-day', 'per-night'
    'priority'         => string,    // 'optional' or 'required'
    'refundable'       => string,    // 'yes' or 'no'
    'taxable'          => string,    // 'yes' or 'no'
    'status'           => string,    // 'active' or 'inactive'
    'icon'             => string,    // Emoji icon for visual identification
    'color'            => string     // Color class for styling
);
```

### WordPress Hooks Integration

```php
// Admin hooks
add_action('rbfw_meta_box_tab_name', [$this, 'add_tab_menu']);
add_action('rbfw_meta_box_tab_content', [$this, 'add_tabs_content']);
add_action('save_post', [$this, 'settings_save'], 99, 1);

// AJAX hooks
add_action('wp_ajax_rbfw_add_fee_row', [$this, 'ajax_add_fee_row']);
add_action('wp_ajax_rbfw_delete_fee_row', [$this, 'ajax_delete_fee_row']);

// Script/Style hooks
add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
```

## Usage Guide

### For Administrators

1. **Access Fee Management**
   - Navigate to WordPress Admin → Rental Items
   - Edit any rental item
   - Click on "Fee Management" tab

2. **Adding New Fees**
   - Click "Add Fee" button at bottom of table
   - Fill in fee details:
     - **Label**: Enter descriptive name (e.g., "Security Deposit")
     - **Description**: Add detailed explanation
     - **Calculation**: Choose Fixed ($) or Percentage (%)
     - **Amount**: Enter numeric value
     - **Frequency**: Select application frequency
     - **Priority**: Set as Optional or Required
     - **Refundable**: Toggle refund status
     - **Status**: Activate/deactivate fee

3. **Managing Existing Fees**
   - **Edit**: Click directly in table cells to modify values
   - **Duplicate**: Use duplicate button (⎘) to copy fee settings
   - **Delete**: Use delete button (✕) with confirmation dialog
   - **Toggle Status**: Use switches for refundable/status changes

4. **Visual Indicators**
   - **Green badges**: Refundable, Optional, Active fees
   - **Red badges**: Non-refundable, Required, Inactive fees
   - **Color-coded icons**: Visual fee categorization

### For Developers

1. **Retrieving Fee Data**
```php
$post_id = 123; // Rental item post ID
$fees = get_post_meta($post_id, 'rbfw_fee_data', true);
```

2. **Calculating Total Fees**
```php
$total_fees = rbfw_calculate_total_fees($item_id, $base_price, $days);
```

3. **Getting Active Fees Only**
```php
$active_fees = rbfw_get_active_fees($item_id);
```

## Database Schema

### Storage Method
- **Storage Type**: WordPress Post Meta
- **Meta Key**: `rbfw_fee_data`
- **Meta Value**: Serialized array of fee configurations
- **Post Type**: `rbfw_item` (rental items)

### Data Validation
- All text fields sanitized with `sanitize_text_field()`
- Numeric amounts validated with `floatval()`
- Boolean values normalized to 'yes'/'no' strings
- Empty labels filtered out during save operation

## Frontend Integration

### WooCommerce Integration Points

1. **Product Display**: Fees shown on single product pages
2. **Cart Integration**: Fees added to cart with proper line items
3. **Checkout Process**: Fees calculated and displayed in totals
4. **Order Processing**: Fees saved to order meta for record keeping

### Frontend Display Functions

```php
// Display fees on product page
rbfw_display_item_fees($post_id);

// Add fees to cart
rbfw_add_fees_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data);

// Calculate fee totals for cart
rbfw_calculate_cart_fees($cart);
```

## API Reference

### Core Functions

#### `rbfw_get_fee_data($post_id)`
Retrieves all fee data for a specific rental item.

**Parameters:**
- `$post_id` (int): The rental item post ID

**Returns:**
- `array`: Fee data array or empty array if none found

#### `rbfw_calculate_total_fees($item_id, $base_price, $days = 1)`
Calculates total fees for a rental booking.

**Parameters:**
- `$item_id` (int): Rental item ID
- `$base_price` (float): Base rental price
- `$days` (int): Number of rental days

**Returns:**
- `float`: Total calculated fees

#### `rbfw_get_active_fees($item_id)`
Retrieves only active fees for a rental item.

**Parameters:**
- `$item_id` (int): Rental item ID

**Returns:**
- `array`: Array of active fees only

### JavaScript Functions

#### `rbfwAddFeeRow()`
Adds a new fee row to the admin table.

#### `rbfwDeleteFeeRow(button)`
Deletes a fee row with confirmation.

**Parameters:**
- `button` (HTMLElement): The delete button element

#### `rbfwUpdateRefundableStatus(checkbox)`
Updates refundable toggle status and visual feedback.

**Parameters:**
- `checkbox` (HTMLElement): The refundable checkbox element

#### `rbfwUpdatePriorityBadge(select)`
Updates priority badge when priority selection changes.

**Parameters:**
- `select` (HTMLElement): The priority select element

## Customization

### Adding New Fee Types

1. **Extend Calculation Types**
```php
// Add to Fee_Management.php in calculation_type select options
<option value="per_person">Per Person</option>
```

2. **Modify Calculation Logic**
```php
// Update rbfw_fee_functions.php calculation logic
case 'per_person':
    $calculated_amount = $amount * $person_count;
    break;
```

### Custom Fee Display

1. **Override Template**
Create `fee-display-template.php` in theme directory:
```php
foreach ($fees as $fee) {
    echo "<div class='custom-fee'>{$fee['label']}: {$fee['amount']}</div>";
}
```

2. **Custom Styling**
Add to theme's CSS:
```css
.custom-fee {
    background: #f9f9f9;
    padding: 10px;
    margin: 5px 0;
    border-left: 3px solid #0073aa;
}
```

### Extending Fee Properties

1. **Add New Field to Data Structure**
```php
'custom_field' => isset($fee['custom_field']) ? sanitize_text_field(wp_unslash($fee['custom_field'])) : '',
```

2. **Add to Admin Form**
```html
<input type="text" name="rbfw_fee_data[<?php echo esc_attr($index); ?>][custom_field]" value="<?php echo esc_attr($custom_field); ?>">
```

## Troubleshooting

### Common Issues

#### 1. Fees Not Displaying
**Problem**: Fees not showing on frontend
**Solution**: 
- Check if fees are marked as "Active"
- Verify WooCommerce integration hooks
- Clear cache if using caching plugins

#### 2. Calculation Errors
**Problem**: Incorrect fee amounts
**Solution**:
- Verify calculation type (fixed vs percentage)
- Check frequency settings (one-time vs per-day)
- Ensure base price is correctly passed

#### 3. Admin Table Not Loading
**Problem**: Fee management table not displaying
**Solution**:
- Check JavaScript console for errors
- Verify user has proper capabilities
- Ensure scripts are properly enqueued

#### 4. Data Not Saving
**Problem**: Fee configurations not persisting
**Solution**:
- Verify nonce validation
- Check user permissions
- Ensure post type is 'rbfw_item'

### Debug Mode

Enable debug mode by adding to wp-config.php:
```php
define('RBFW_FEE_DEBUG', true);
```

This enables detailed logging of fee operations.

### Performance Optimization

1. **Cache Fee Data**
```php
$cache_key = 'rbfw_fees_' . $post_id;
$fees = wp_cache_get($cache_key);
if (false === $fees) {
    $fees = get_post_meta($post_id, 'rbfw_fee_data', true);
    wp_cache_set($cache_key, $fees, '', 3600);
}
```

2. **Optimize Database Queries**
```php
// Bulk load fee data for multiple items
$post_ids = [1, 2, 3, 4, 5];
$meta_data = get_post_meta_multiple($post_ids, 'rbfw_fee_data');
```

## Security Considerations

### Input Validation
- All user inputs sanitized using WordPress functions
- Numeric validation for amounts and calculations
- Proper escaping for output display

### Access Control
- Admin capability checks: `current_user_can('edit_post', $post_id)`
- Nonce verification for all form submissions
- AJAX request validation

### Data Protection
- No sensitive data stored in fee configurations
- Proper data serialization for database storage
- Regular sanitization of stored data

## Future Enhancements

### Planned Features
1. **Fee Templates**: Predefined fee sets for common scenarios
2. **Conditional Logic**: Rules-based fee application
3. **Multi-Currency Support**: International pricing options
4. **Fee Analytics**: Reporting and statistics dashboard
5. **Import/Export**: Bulk fee management capabilities

### API Extensions
1. **REST API Endpoints**: External system integration
2. **Webhook Support**: Real-time fee updates
3. **Third-party Integrations**: Payment gateway specific fees

## Conclusion

The Fee Management System provides a robust, professional solution for managing additional fees in the Booking and Rental Manager plugin. With its comprehensive admin interface, secure data handling, and flexible configuration options, it meets the needs of modern rental businesses while maintaining WordPress development standards.

For additional support or feature requests, please refer to the plugin documentation or contact the development team.

---

**Version**: 1.0.0  
**Author**: Shahnur Alam  
**Last Updated**: 2025-10-08  
**WordPress Compatibility**: 5.0+  
**PHP Compatibility**: 7.4+