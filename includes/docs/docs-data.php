<?php
/**
 * Rental Docs — single audit data source.
 *
 * Everything the documentation pages render comes from this file (plus the
 * auto-derived settings-data.php partial). To document a new field/feature,
 * add it here — no HTML lives in the templates.
 *
 * Returned array keys map to the documentation pages:
 *   meta, getting_started, menus, settings, item_types, editor_tabs,
 *   post_types, taxonomies, shortcodes, blocks, woocommerce, free_vs_pro, faq
 *
 * @package Booking_And_Rental_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Settings Reference = generated field data (settings-data.php) + hand-written
 * plain-English copy (settings-copy.php), merged here so each field shows a
 * proper "What it does" / "Where it appears" explanation instead of the
 * plugin's terse internal description.
 */
$rbfw_docs_settings = require __DIR__ . '/settings-data.php';
$rbfw_docs_copy     = require __DIR__ . '/settings-copy.php';

foreach ( $rbfw_docs_settings as $rbfw_sec_id => $rbfw_sec ) {
	if ( empty( $rbfw_sec['fields'] ) ) {
		continue;
	}
	foreach ( $rbfw_sec['fields'] as $rbfw_i => $rbfw_field ) {
		$rbfw_name = isset( $rbfw_field['name'] ) ? $rbfw_field['name'] : '';
		$rbfw_copy = isset( $rbfw_docs_copy[ $rbfw_name ] ) ? $rbfw_docs_copy[ $rbfw_name ] : array();
		// Fall back to the plugin's own description when no copy is written yet.
		$rbfw_docs_settings[ $rbfw_sec_id ]['fields'][ $rbfw_i ]['what']  = ! empty( $rbfw_copy['what'] ) ? $rbfw_copy['what'] : $rbfw_field['desc'];
		$rbfw_docs_settings[ $rbfw_sec_id ]['fields'][ $rbfw_i ]['where'] = isset( $rbfw_copy['where'] ) ? $rbfw_copy['where'] : '';
	}
}
unset( $rbfw_sec_id, $rbfw_sec, $rbfw_i, $rbfw_field, $rbfw_name, $rbfw_copy, $rbfw_docs_copy );

$rbfw_item_base = 'edit.php?post_type=rbfw_item';

return array(

	/* ------------------------------------------------------------------ *
	 * Meta
	 * ------------------------------------------------------------------ */
	'meta' => array(
		'free_version' => defined( 'RBFW_Rent_Manager' ) ? '' : '2.7.2',
		'pro_version'  => '1.2.6',
		'item_base'    => $rbfw_item_base,
	),

	/* ------------------------------------------------------------------ *
	 * 1. Getting Started — ordered setup flow
	 * ------------------------------------------------------------------ */
	'getting_started' => array(
		'intro' => 'Booking and Rental Manager turns WordPress into a full rental/booking system. Follow the steps below in order — each links to the exact admin screen. You can run the whole store on WooCommerce, or in Standalone (Custom Payment) mode with no WooCommerce at all.',
		'steps' => array(
			array(
				'title' => 'Run the Quick Setup',
				'url'   => $rbfw_item_base . '&page=rbfw_quick_setup',
				'body'  => 'Open Rent Item → Quick Setup. It creates the required pages (search, cart, checkout, thank-you) and lets you pick WooCommerce or Standalone mode. This is the fastest way to a working store.',
			),
			array(
				'title' => 'Choose your booking mode',
				'url'   => $rbfw_item_base . '&page=rbfw_settings_page',
				'body'  => 'In Settings → Payments, select WooCommerce mode (uses the WooCommerce cart, checkout and gateways) or Standalone mode (the plugin\'s own checkout — Stripe/PayPal come with Pro). Set which order statuses confirm a booking.',
			),
			array(
				'title' => 'Create your first rental item',
				'url'   => 'post-new.php?post_type=rbfw_item',
				'body'  => 'Rent Item → Add New opens the modern editor. Give it a title/photos, pick a Rental Type (Single day, Multiple day, Resort, Appointment, or Multiple items), then set pricing on the Pricing step.',
			),
			array(
				'title' => 'Set pricing',
				'url'   => 'post-new.php?post_type=rbfw_item',
				'body'  => 'On the Pricing step choose the rate model — hourly, daily, weekly, monthly, half-day, day-wise (per weekday), seasonal or tiered. Whole months/weeks bill at the bulk rate and leftover days at the daily/day-wise rate.',
			),
			array(
				'title' => 'Organise with Rental Types & Locations',
				'url'   => 'edit-tags.php?taxonomy=rbfw_item_caregory&post_type=rbfw_item',
				'body'  => 'Add categories under Rent Item → Categories (used by the search "type" filter) and pickup/drop-off points under Locations.',
			),
			array(
				'title' => 'Manage stock in Inventory',
				'url'   => $rbfw_item_base . '&page=rbfw_inventory',
				'body'  => 'Rent Item → Inventory shows availability per item/location/date. Set which order statuses reserve stock in Settings → General.',
			),
			array(
				'title' => 'Publish the search & list on a page',
				'url'   => 'edit.php?post_type=page',
				'body'  => 'Drop the [rbfw_booking_search] or [rent-list] shortcode (or the matching block) on any page so customers can browse and book. See the Shortcodes & Blocks page.',
			),
			array(
				'title' => 'Watch orders come in',
				'url'   => $rbfw_item_base . '&page=rbfw_booking_orders',
				'body'  => 'Rent Item → Bookings lists every booking (WooCommerce and Standalone) with revenue stats, status editing, filtering and CSV/PDF export (export & row actions require Pro).',
			),
		),
	),

	/* ------------------------------------------------------------------ *
	 * Admin menu map (used by Getting Started + Settings Reference)
	 * ------------------------------------------------------------------ */
	'menus' => array(
		'parent' => array(
			'title' => 'Rent Item',
			'slug'  => $rbfw_item_base,
			'desc'  => 'Top-level menu (the Rental Item custom post type). Its label/icon are configurable in Settings → General (CPT Label / CPT Icon).',
		),
		'items' => array(
			array( 'title' => 'All Items',        'slug' => $rbfw_item_base,                              'cap' => 'edit_posts',    'plan' => 'free', 'desc' => 'List of every rental item.' ),
			array( 'title' => 'Add New',          'slug' => 'post-new.php?post_type=rbfw_item',            'cap' => 'edit_posts',    'plan' => 'free', 'desc' => 'Create an item in the modern step-by-step editor.' ),
			array( 'title' => 'Categories',       'slug' => 'edit-tags.php?taxonomy=rbfw_item_caregory&post_type=rbfw_item', 'cap' => 'manage_categories', 'plan' => 'free', 'desc' => 'Rental Types / categories (drives the search "type" filter).' ),
			array( 'title' => 'Locations',        'slug' => 'edit-tags.php?taxonomy=rbfw_item_location&post_type=rbfw_item',  'cap' => 'manage_categories', 'plan' => 'free', 'desc' => 'Pickup / drop-off locations.' ),
			array( 'title' => 'Quick Setup',      'slug' => $rbfw_item_base . '&page=rbfw_quick_setup',    'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'Guided first-run wizard (creates pages, picks mode).' ),
			array( 'title' => 'Time Slots',       'slug' => $rbfw_item_base . '&page=rbfw_time_slots',     'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'Reusable time-slot presets for hourly/appointment items.' ),
			array( 'title' => 'Inventory',        'slug' => $rbfw_item_base . '&page=rbfw_inventory',      'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'Stock/availability grid by item, location and date.' ),
			array( 'title' => 'Coupons',          'slug' => 'edit.php?post_type=rbfw_coupon',              'cap' => 'edit_posts',    'plan' => 'free', 'desc' => 'Discount/coupon engine (works in WooCommerce and Standalone).' ),
			array( 'title' => 'Bookings',         'slug' => $rbfw_item_base . '&page=rbfw_booking_orders', 'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'Unified order list + revenue dashboard. View/edit/delete rows and CSV/PDF export require Pro.' ),
			array( 'title' => 'Booking Forms',    'slug' => $rbfw_item_base . '&page=rbfw_booking_forms',  'cap' => 'manage_options', 'plan' => 'pro',  'desc' => 'Drag-and-drop registration/booking form builder with conditional logic.' ),
			array( 'title' => 'Booking Calendar', 'slug' => $rbfw_item_base . '&page=rbmw_pro_booking_in_calender', 'cap' => 'manage_options', 'plan' => 'pro', 'desc' => 'Month/day calendar of all bookings; optional Google Calendar sync.' ),
			array( 'title' => 'Reviews',          'slug' => 'edit.php?post_type=rbfw_item_reviews',        'cap' => 'manage_options', 'plan' => 'pro',  'desc' => 'Customer review submission & moderation.' ),
			array( 'title' => 'Settings',         'slug' => $rbfw_item_base . '&page=rbfw_settings_page',  'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'Global settings (all tabs documented in Settings Reference).' ),
			array( 'title' => 'Status',           'slug' => $rbfw_item_base . '&page=rbfw-status',         'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'System/environment status.' ),
			array( 'title' => 'Get PRO',          'slug' => $rbfw_item_base . '&page=rbfw_go_pro_page',    'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'Upgrade page (only shown when Pro is not active).' ),
		),
	),

	/* ------------------------------------------------------------------ *
	 * 2. Settings Reference — auto-derived (free + Pro merged)
	 * ------------------------------------------------------------------ */
	'settings' => $rbfw_docs_settings,

	/* ------------------------------------------------------------------ *
	 * 3a. Rental item types
	 * ------------------------------------------------------------------ */
	'item_types' => array(
		array( 'key' => 'bike_car_sd',   'name' => 'Single day',                     'plan' => 'free', 'desc' => 'Rented within one day with time slots (hourly / morning / evening / full day). Best for bikes, boats, kayaks and short-use gear.' ),
		array( 'key' => 'bike_car_md',   'name' => 'Multiple day',                   'plan' => 'free', 'desc' => 'Rented across several days. Supports hourly, daily, weekly, monthly, half-day, day-wise, seasonal and tiered pricing. Best for cars, equipment, dresses.' ),
		array( 'key' => 'resort',        'name' => 'Resort',                         'plan' => 'free', 'desc' => 'Stays priced per night / day-night, for hotels and resorts. Supports seasonal and day-wise night rates.' ),
		array( 'key' => 'appointment',   'name' => 'Appointment',                    'plan' => 'free', 'desc' => 'Slot-based bookings for services/appointments on a chosen date and time.' ),
		array( 'key' => 'multiple_items','name' => 'Multiple day for multiple items','plan' => 'free', 'desc' => 'A single booking that spans several sub-items (e.g. a package), each with its own quantity/price across the date range.' ),
	),

	/* ------------------------------------------------------------------ *
	 * 3b. Per-item editor tabs (modern editor steps / classic meta boxes)
	 * ------------------------------------------------------------------ */
	'editor_tabs' => array(
		array( 'name' => 'General Info',      'plan' => 'free', 'desc' => 'Title, description, rental type, short details and core toggles for the item.' ),
		array( 'name' => 'Pricing',           'plan' => 'free', 'desc' => 'The rate model: monthly / weekly / daily / hourly / half-day rates, thresholds, day-wise per-weekday grid, time picker and time slots.' ),
		array( 'name' => 'Inventory',         'plan' => 'free', 'desc' => 'Stock quantity, per-location stock and quantity limits.' ),
		array( 'name' => 'Gallery',           'plan' => 'free', 'desc' => 'Additional image gallery for the single item page (toggle: Enable Additional Gallery).' ),
		array( 'name' => 'FAQ',               'plan' => 'free', 'desc' => 'Per-item question/answer accordion (toggle: FAQ Settings Enable).' ),
		array( 'name' => 'Extra Service',     'plan' => 'free', 'desc' => 'Optional paid add-ons (e.g. insurance, helmet) added to the booking total.' ),
		array( 'name' => 'Location',          'plan' => 'free', 'desc' => 'Pickup / drop-off points, per-location stock and location-based pricing.' ),
		array( 'name' => 'Off Day',           'plan' => 'free', 'desc' => 'Dates/weekdays the item cannot be booked.' ),
		array( 'name' => 'Related',           'plan' => 'free', 'desc' => 'Related/cross-sell items shown on the single page.' ),
		array( 'name' => 'Security Deposit',  'plan' => 'free', 'desc' => 'Refundable deposit — fixed amount or percentage — added at checkout.' ),
		array( 'name' => 'Tax',               'plan' => 'free', 'desc' => 'Per-item tax rate/label applied to the rental total.' ),
		array( 'name' => 'Fee Management',    'plan' => 'free', 'desc' => 'Extra fixed/percentage fees (cleaning, service) added to the total.' ),
		array( 'name' => 'Terms',             'plan' => 'free', 'desc' => 'Per-item terms & conditions text shown on the booking form.' ),
		array( 'name' => 'Template',          'plan' => 'free', 'desc' => 'Layout/template choice for the single item page.' ),
		array( 'name' => 'Coupon Settings',   'plan' => 'free', 'desc' => 'Enable coupons and restrict which coupons apply to this item.' ),
		array( 'name' => 'Payment Settings',  'plan' => 'free', 'desc' => 'Per-item payment overrides used by the checkout.' ),
	),

	/* ------------------------------------------------------------------ *
	 * 3c. Custom post types
	 * ------------------------------------------------------------------ */
	'post_types' => array(
		array( 'slug' => 'rbfw_item',        'name' => 'Rental Item',   'plan' => 'free', 'public' => 'Yes',       'desc' => 'The core rentable product. Holds all pricing, inventory, gallery, FAQ and location meta.' ),
		array( 'slug' => 'rbfw_order',       'name' => 'Booking Record','plan' => 'free', 'public' => 'No (admin)', 'desc' => 'One record per WooCommerce-backed booking; stores billing name, linked WC order id and the ticket_info (dates/items).' ),
		array( 'slug' => 'rbfw_order_meta',  'name' => 'Order Meta Store','plan' => 'free','public' => 'No (internal)', 'desc' => 'Internal helper post type used to persist booking meta reliably.' ),
		array( 'slug' => 'rbfw_booking',     'name' => 'Standalone Booking','plan' => 'free','public' => 'No (admin)', 'desc' => 'Booking record created by the Standalone (Custom Payment) checkout when WooCommerce is not used.' ),
		array( 'slug' => 'rbfw_coupon',      'name' => 'Coupon',        'plan' => 'free', 'public' => 'No (admin)', 'desc' => 'A discount rule (percentage/fixed, usage limits, per-item restrictions) used by both checkouts.' ),
		array( 'slug' => 'rbfw_reg_form',    'name' => 'Booking Form',  'plan' => 'pro',  'public' => 'No (admin)', 'desc' => 'A saved registration/booking form built in the drag-and-drop Booking Forms builder.' ),
		array( 'slug' => 'rbfw_item_reviews','name' => 'Item Review',   'plan' => 'pro',  'public' => 'No (admin)', 'desc' => 'A customer review of a rental item (with moderation state).' ),
	),

	'taxonomies' => array(
		array( 'slug' => 'rbfw_item_caregory', 'name' => 'Rental Type / Category', 'object' => 'rbfw_item', 'plan' => 'free', 'desc' => 'Groups items into rental types/categories. The [rbfw_booking_search] "type" attribute filters by these.' ),
		array( 'slug' => 'rbfw_item_location', 'name' => 'Location',               'object' => 'rbfw_item', 'plan' => 'free', 'desc' => 'Pickup/drop-off locations. Used by the location filter and per-location stock.' ),
	),

	/* ------------------------------------------------------------------ *
	 * 4. Shortcodes & Blocks
	 * ------------------------------------------------------------------ */
	'shortcodes' => array(
		array(
			'tag'  => 'rbfw_booking_search',
			'plan' => 'free',
			'desc' => 'The recommended all-in-one search + results widget: a search form with live results (grid/list), type/location filters, stock and quantity.',
			'example' => '[rbfw_booking_search type="" columns="3" show="24" style="grid"]',
			'atts' => array(
				'type'          => 'CSV of rental-type (category) names to search; empty = all.',
				'types'         => 'Back-compat alias of "type".',
				'location'      => 'Fixed location slug — hides the location dropdown.',
				'hide_location' => 'no | yes — hide the location dropdown.',
				'hide_type'     => 'no | yes — hide the rental-type dropdown.',
				'columns'       => 'Result columns (default 3).',
				'show'          => 'Max results (default 24).',
				'button_text'   => 'Custom search button label.',
				'show_stock'    => 'yes | no — show remaining stock on results.',
				'show_qty'      => 'yes | no — show the quantity selector.',
				'style'         => 'grid | list — initial results layout.',
			),
		),
		array(
			'tag'  => 'rent-list',
			'plan' => 'free',
			'desc' => 'A grid/list of rental items (like a shop archive). Good for a "browse all" page.',
			'example' => '[rent-list style="grid" show="-1" order="DESC" orderby="" type=""]',
			'atts' => array(
				'style'    => 'grid | list layout (default grid).',
				'show'     => 'How many items (-1 = all).',
				'order'    => 'ASC | DESC.',
				'orderby'  => 'WordPress orderby (date, title, meta_value…).',
				'meta_key' => 'Meta key when ordering by meta_value.',
				'type'     => 'Filter by rental-type (category).',
			),
		),
		array(
			'tag'  => 'rent-add-to-cart',
			'plan' => 'free',
			'desc' => 'Renders one item\'s full booking form (date/time pickers, extras, price). Use it to place a specific item on a landing page.',
			'example' => '[rent-add-to-cart id="123"]',
			'atts' => array(
				'id'      => 'Rental item ID (required).',
				'backend' => 'Internal flag used when rendering from admin; leave empty on the front-end.',
			),
		),
		array(
			'tag'  => 'rbfw_search',
			'plan' => 'free',
			'desc' => 'A standalone search form that posts to your Search Page (set in Settings → General). Pair with [search-result].',
			'example' => '[rbfw_search hide_pickup_date="no" hide_location="no"]',
			'atts' => array(
				'search-type'      => 'Pre-select a rental type.',
				'hide_pickup_date' => 'no | yes.',
				'hide_location'    => 'no | yes.',
				'type_label'       => 'Override the rental-type dropdown placeholder.',
				'location_label'   => 'Override the location dropdown placeholder.',
			),
		),
		array(
			'tag'  => 'rbfw_search_ac',
			'plan' => 'free',
			'desc' => 'Autocomplete variant of the search form (same attributes as [rbfw_search]).',
			'example' => '[rbfw_search_ac]',
			'atts' => array(),
		),
		array(
			'tag'  => 'search-result',
			'plan' => 'free',
			'desc' => 'Renders the results of a [rbfw_search] submission. Place it on the Search Page.',
			'example' => '[search-result]',
			'atts' => array(),
		),
		array(
			'tag'  => 'rbfw_left_filter',
			'plan' => 'free',
			'desc' => 'A sidebar of filters (title, price, location, category, type, features) for the results page.',
			'example' => '[rbfw_left_filter price-filter="on" location-filter="on" category-filter="on"]',
			'atts' => array(
				'title-filter'    => 'on | off.',
				'price-filter'    => 'on | off.',
				'location-filter' => 'on | off.',
				'category-filter' => 'on | off.',
				'type-filter'     => 'on | off.',
				'feature-filter'  => 'on | off.',
			),
		),
		array(
			'tag'  => 'rbfw_thankyou',
			'plan' => 'free',
			'desc' => 'The booking confirmation / thank-you output. Place it on the Thank You Page (Settings → General).',
			'example' => '[rbfw_thankyou]',
			'atts' => array(),
		),
		array(
			'tag'  => 'rbfw_customer_portal',
			'plan' => 'pro',
			'desc' => 'A front-end customer area: login/register, view bookings and download invoices.',
			'example' => '[rbfw_customer_portal]',
			'atts' => array(),
		),
		array(
			'tag'  => 'stripe',
			'plan' => 'pro',
			'desc' => 'Renders the Stripe card form for the Standalone (Custom Payment) checkout.',
			'example' => '[stripe]',
			'atts' => array(),
		),
	),

	'blocks' => array(
		array( 'name' => 'Rent List (block)',   'plan' => 'free', 'desc' => 'Gutenberg block wrapper around [rent-list] with style/show/order/type controls.' ),
		array( 'name' => 'Left Filter (block)', 'plan' => 'free', 'desc' => 'Gutenberg block wrapper around [rbfw_left_filter] with per-filter on/off toggles.' ),
		array( 'name' => 'Elementor widgets',   'plan' => 'free', 'desc' => 'Elementor widgets are provided under support/elementor for placing the rental list/search in Elementor layouts.' ),
	),

	/* ------------------------------------------------------------------ *
	 * 5. WooCommerce Integration
	 * ------------------------------------------------------------------ */
	'woocommerce' => array(
		'intro' => 'The plugin runs in one of two modes. In WooCommerce mode each rental item is backed by a hidden WooCommerce product so bookings flow through the normal cart, checkout, order and email pipeline. In Standalone (Custom Payment) mode the plugin uses its own checkout and booking records — no WooCommerce needed (Stripe/PayPal gateways for this mode come with Pro).',
		'topics' => array(
			array(
				'title' => 'Hidden backing product',
				'plan'  => 'free',
				'body'  => 'When an item is created a hidden WooCommerce product is generated and linked to it. If an item was imported in Standalone mode and you later switch to WooCommerce, the plugin self-heals by back-filling the missing hidden product on first add-to-cart.',
			),
			array(
				'title' => 'Price calculation in the cart',
				'plan'  => 'free',
				'body'  => 'The rental price (dates, duration, extras, deposit, fees, coupons) is computed by the plugin and injected as the cart line price — not the product\'s catalog price. The same engine drives the live on-page price, the cart, and the search results so they always match.',
			),
			array(
				'title' => 'Order status → booking confirmation',
				'plan'  => 'free',
				'body'  => 'Settings → Payments "Confirm Booking Based on Payment Status" picks which WooCommerce statuses (Processing/Completed by default) mark a booking confirmed. Settings → General "Inventory Managed Order Status" picks which statuses reserve stock.',
			),
			array(
				'title' => 'Add-to-cart redirect & sold-individually',
				'plan'  => 'free',
				'body'  => 'Choose Cart or Checkout after add-to-cart (Settings → Payments / Checkout Page). "Allow duplicate rental item in cart" (Settings → General) controls whether the same item can be added multiple times.',
			),
			array(
				'title' => 'Bookings list & revenue',
				'plan'  => 'free',
				'body'  => 'Rent Item → Bookings reads WooCommerce orders + booking records to show revenue, status counts and per-booking details for both modes.',
			),
			array(
				'title' => 'CSV / PDF / thermal export',
				'plan'  => 'pro',
				'body'  => 'Pro adds CSV export, a PDF ticket (attached to confirmation emails) and an 80mm/58mm thermal receipt from the Bookings page, plus the per-row View/Edit/Delete actions.',
			),
			array(
				'title' => 'Standalone gateways',
				'plan'  => 'pro',
				'body'  => 'Stripe and PayPal gateways for the Standalone checkout are provided by Pro (inc/gateways). The Standalone currency format is set in Settings → Checkout Page.',
			),
		),
		'emails' => array(
			array( 'name' => 'WooCommerce booking confirmation',   'plan' => 'free', 'trigger' => 'Order reaches a status in Settings → Email "Send Email on".', 'body' => 'Confirmation email with the booking summary; optional PDF attachment (Pro).' ),
			array( 'name' => 'Standalone customer confirmation',    'plan' => 'free', 'trigger' => 'A Standalone booking is placed (native_email_enable = Enabled).', 'body' => 'Customer email using native_customer_email_subject/content; booking summary appended automatically.' ),
			array( 'name' => 'Standalone admin notification',       'plan' => 'free', 'trigger' => 'A Standalone booking is placed.', 'body' => 'Admin notice to native_admin_email using native_admin_email_subject/content.' ),
			array( 'name' => 'PDF ticket attachment / thermal',     'plan' => 'pro',  'trigger' => 'Send Ticket = Yes (Settings → PDF).', 'body' => 'Generates and attaches the PDF ticket; thermal receipt export from Bookings.' ),
		),
		'automation' => array(
			array( 'name' => 'Google Calendar sync', 'plan' => 'pro', 'desc' => 'Bookings sync to a Google Calendar via a service account. Runs on save and on a background cron (single events scheduled ~30s after a change).' ),
			array( 'name' => 'License update check',  'plan' => 'pro', 'desc' => 'A daily cron checks for Pro updates via the bundled update checker.' ),
			array( 'name' => 'Capabilities',          'plan' => 'free','desc' => 'No new roles are created. All admin screens require manage_options; override with the rbfw_bookings_capability filter to grant e.g. shop managers.' ),
		),
	),

	/* ------------------------------------------------------------------ *
	 * 6. Free vs Pro feature matrix
	 * ------------------------------------------------------------------ */
	'free_vs_pro' => array(
		array( 'feature' => 'All 5 rental types (single/multi-day, resort, appointment, multi-item)', 'free' => true,  'pro' => true ),
		array( 'feature' => 'Pricing engine (hourly, daily, weekly, monthly, half-day, day-wise, tiered, seasonal)', 'free' => true, 'pro' => true ),
		array( 'feature' => 'Inventory & per-location stock',           'free' => true,  'pro' => true ),
		array( 'feature' => 'Search, results, sidebar filters, blocks & Elementor', 'free' => true, 'pro' => true ),
		array( 'feature' => 'WooCommerce checkout & cart integration',  'free' => true,  'pro' => true ),
		array( 'feature' => 'Standalone (Custom Payment) checkout',     'free' => 'Cash/manual only', 'pro' => 'Stripe & PayPal' ),
		array( 'feature' => 'Coupons / discounts',                      'free' => true,  'pro' => true ),
		array( 'feature' => 'Security deposit, extra services, fees, tax', 'free' => true, 'pro' => true ),
		array( 'feature' => 'Bookings list & revenue dashboard',        'free' => 'View only', 'pro' => 'View/Edit/Delete + status manager' ),
		array( 'feature' => 'CSV / PDF ticket / thermal receipt export', 'free' => false, 'pro' => true ),
		array( 'feature' => 'PDF Settings tab',                         'free' => false, 'pro' => true ),
		array( 'feature' => 'Email Settings tab (WC + Standalone emails)', 'free' => false, 'pro' => true ),
		array( 'feature' => 'Booking Form Builder + conditional logic', 'free' => false, 'pro' => true ),
		array( 'feature' => 'Review system (submit & moderate)',        'free' => 'Display toggle only', 'pro' => true ),
		array( 'feature' => 'Booking Calendar + Google Calendar sync',  'free' => false, 'pro' => true ),
		array( 'feature' => 'Customer Portal (login, bookings, invoices)', 'free' => false, 'pro' => true ),
		array( 'feature' => 'Reports / attendee dashboard',             'free' => false, 'pro' => true ),
	),

	/* ------------------------------------------------------------------ *
	 * 7. Troubleshooting / FAQ
	 * ------------------------------------------------------------------ */
	'faq' => array(
		array(
			'q' => 'I changed the CPT slug and now item pages 404.',
			'a' => 'After changing CPT Slug (Settings → General) you must flush permalinks: go to Settings → Permalinks and click Save Changes once.',
		),
		array(
			'q' => 'Half-day price is set but never applies at checkout.',
			'a' => 'Half-day needs an hour window. On the item\'s Pricing step fill in "Half-Day Hour Threshold" From/To (e.g. 2 to 4). Without a valid range the rate is shown but never triggers.',
		),
		array(
			'q' => 'Day-wise (per-weekday) prices are ignored when I also use weekly/monthly rates.',
			'a' => 'That is expected for whole periods: whole weeks/months bill at the bulk rate; only the leftover days are priced per weekday. Test a partial week (e.g. 8–9 days) to see day-wise apply.',
		),
		array(
			'q' => 'Booking says paid but stock did not go down.',
			'a' => 'Stock is reserved only for the statuses selected in Settings → General "Inventory Managed Order Status" (default Processing/Completed). Add the status you use, or enable "Inventory manage based on return".',
		),
		array(
			'q' => 'Orders placed via redirect gateways are missing from the list.',
			'a' => 'Booking records are built from server-side order hooks. Ensure the confirming statuses in Settings → Payments match your gateway\'s completed status (Processing/Completed).',
		),
		array(
			'q' => 'The "Bookings" page shows items but View/Edit/Delete and Export do nothing.',
			'a' => 'Those actions and CSV/PDF export are Pro features. In free the list is read-only.',
		),
		array(
			'q' => 'Buttons/price on a page do nothing (no calculation).',
			'a' => 'The front-end scripts load only on rental pages. If you place a rental shortcode on an unrelated page, ensure that page actually contains a rental shortcode/block so the assets enqueue.',
		),
		array(
			'q' => 'I want a shop manager (not admin) to manage bookings.',
			'a' => 'Add a small snippet: add_filter( "rbfw_bookings_capability", fn() => "manage_woocommerce" ); The admin pages then open for that capability.',
		),
		array(
			'q' => 'PDF ticket is not attached to emails.',
			'a' => 'Set Settings → PDF "Send Ticket" to Yes (requires Pro / the PDF add-on) and confirm the order reaches a status listed in Settings → Email "Send Email on".',
		),
		array(
			'q' => 'Standalone confirmation emails are not sending.',
			'a' => 'Enable Settings → Email "Standalone Booking Emails". Also verify your server can send mail (an SMTP plugin is recommended).',
		),
	),
);
