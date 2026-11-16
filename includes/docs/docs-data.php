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
 *   post_types, taxonomies, shortcodes, blocks, woocommerce, calendar_sync,
 *   free_vs_pro, faq
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
				'url'   => 'admin.php?page=rbfw_onboarding',
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
			array( 'title' => 'Quick Setup',      'slug' => 'admin.php?page=rbfw_onboarding',    'cap' => 'manage_options', 'plan' => 'free', 'desc' => 'Guided first-run wizard (creates pages, picks mode).' ),
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
		array( 'name' => 'Calendar Sync',     'plan' => 'pro',  'desc' => 'The item\'s Export link for Airbnb / Booking.com / Stayz / Vrbo, the platforms\' calendar links to import, Sync now, and the upcoming imported dates. Modern editor: Advanced step → Calendar Sync (PRO) card. See the Calendar Sync section of this page.' ),
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
		'integrations' => array(
			array(
				'name'        => 'SecureHold WP',
				'version'     => '3.4.11 or later',
				'logo'        => 'assets/images/securehold-icon.png',
				'description' => 'A compatible third-party option for merchants who want to use a separate Stripe authorization hold instead of charging and later refunding a fixed WpRently security deposit.',
				'requirements' => array(
					'WooCommerce checkout mode and the official WooCommerce Stripe Gateway.',
					'A linked WooCommerce rental product with a fixed WpRently security deposit.',
					'SecureHold\'s own Stripe API keys, in the same test/live mode as the Stripe gateway.',
					'The MagePeople compatibility option enabled in SecureHold.',
					'SecureHold\'s Default Hold Amount set to 0, so it only holds fixed WpRently deposits.',
					'A Stripe authorization duration that suits the merchant\'s rental period.',
				),
				'limitation'  => 'Percentage-based and other unsupported WpRently deposits continue through the normal WpRently checkout flow. SecureHold controls the compatible fixed deposit hold and prevents that amount from also being added to the WooCommerce payable total. Until every requirement is met, and always in the Standalone checkout, WpRently keeps charging the deposit as part of the booking total.',
				'test_steps'  => array(
					'Enable Stripe test mode in both SecureHold and the official WooCommerce Stripe Gateway.',
					'Link one WooCommerce rental product with a €100 rental price and a fixed €500 WpRently security deposit.',
					'Place a booking through the normal WooCommerce checkout and confirm the customer pays €100.',
					'Confirm SecureHold creates a separate €500 Stripe authorization and the deposit is not added to the WooCommerce payable total.',
					'Confirm a percentage-based or otherwise unsupported deposit continues through the normal WpRently checkout flow, with no SecureHold hold added.',
				),
				'docs_url'    => 'https://secureholdwp.com/docs/',
				'support_url' => 'https://secureholdwp.com/support/',
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
			array( 'name' => 'Calendar Sync import',  'plan' => 'pro', 'desc' => 'Downloads every connected Airbnb / Booking.com / Stayz / Vrbo / iCal calendar at the Import Frequency (Settings → Calendar Sync, default every 30 minutes), plus once right after a new link is saved. One run at a time; items not reached in about 45 seconds continue on the next run. Needs WordPress scheduled tasks (site traffic or a real server cron for wp-cron.php).' ),
			array( 'name' => 'Capabilities',          'plan' => 'free','desc' => 'No new roles are created. All admin screens require manage_options; override with the rbfw_bookings_capability filter to grant e.g. shop managers.' ),
		),
	),

	/* ------------------------------------------------------------------ *
	 * 5b. Calendar Sync (Pro) — Airbnb, Booking.com, Stayz, Vrbo, iCal
	 * ------------------------------------------------------------------ */
	'calendar_sync' => array(
		'intro' => 'Keep each rental item\'s availability in step with Airbnb, Booking.com, Stayz, Vrbo, Google Calendar and any other platform that offers an iCal (.ics) calendar link. Dates booked on those platforms close the item on your website, and dates booked on your website are blocked on those platforms. It uses the calendar links every platform gives its hosts, so there is nothing to apply for and no account to connect.',
		'basics' => array(
			array(
				'title' => 'Do I need an API key, listing ID or partner account?',
				'body'  => 'No. You only copy links. Every platform shows an "export calendar" link for each listing, and your website shows an Export link for each rental item. The listing ID and a secret key are already inside those links, so there is nothing else to enter. Direct API connections to Airbnb or Booking.com are only open to approved channel managers; iCal links are what they offer every host.',
			),
			array(
				'title' => 'What you need',
				'body'  => 'Booking and Rental Manager Pro active, and Rent Item → Settings → Calendar Sync turned on (it is on by default). Your website must be reachable on the internet at its public address, because the platforms download your link: a local or office-network address (localhost, 192.168.x.x) or a password-protected / coming-soon site cannot be reached, so test on a live or staging site. WordPress scheduled tasks must run (normal visitor traffic is enough; a real server cron is better).',
			),
			array(
				'title' => 'Two links per platform, per listing',
				'body'  => 'Link 1 goes from your website to the platform: copy the item\'s Export link and paste it into the platform\'s "import calendar" field. Link 2 goes from the platform to your website: copy the platform\'s "export calendar" link and paste it into the item\'s Imported calendars. Do both for every platform the listing is on.',
			),
			array(
				'title' => 'One rental item per listing',
				'body'  => 'Dates imported from a platform close the whole rental item (every unit). Connect each Airbnb / Booking.com / Stayz listing to its own rental item. Three identical apartments listed separately = three rental items.',
			),
			array(
				'title' => 'Where the settings are',
				'body'  => 'Shop-wide: Rent Item → Settings → Calendar Sync. Per item: edit the rental item — Classic editor: the Calendar Sync tab; Modern editor: the Advanced step → Calendar Sync (PRO) card.',
			),
		),
		'steps' => array(
			array(
				'title' => 'Turn on Calendar Sync',
				'body'  => 'Rent Item → Settings → Calendar Sync. Keep "Calendar Sync" ticked, choose the Import Frequency (every 30 minutes is a good default) and save. Leave "Share Imported Dates" off unless you read "Share Imported Dates (hub mode)" below.',
				'url'   => $rbfw_item_base . '&page=rbfw_settings_page',
			),
			array(
				'title' => 'Open the rental item that matches the listing',
				'body'  => 'Edit the rental item for the property (one item per listing) and open its Calendar Sync tab (Classic editor) or the Advanced step → Calendar Sync (PRO) card (Modern editor).',
				'url'   => $rbfw_item_base,
			),
			array(
				'title' => 'Copy the item\'s Export link',
				'body'  => 'Under "Export link (this site → channels)" click Copy. It looks like https://your-site.com/?rbfw_ical=123-AbCd…xyz.ics. Keep it private: anyone with it can see which dates are booked (no guest details).',
			),
			array(
				'title' => 'Paste it into each platform (Link 1)',
				'body'  => 'On Airbnb, Booking.com, Stayz or Vrbo, open the listing\'s calendar sync / import calendar screen, paste the Export link and give it a name such as "My website". The exact clicks for each platform are listed under "Platform by platform" below.',
			),
			array(
				'title' => 'Copy each platform\'s export link (Link 2)',
				'body'  => 'On the same platform screen, copy its "export calendar" link. It is a long address that usually ends in .ics — not the address of the listing page.',
			),
			array(
				'title' => 'Add it to the rental item',
				'body'  => 'Back in the item, click "Add calendar", choose the Channel (Airbnb, Booking.com, Stayz, Vrbo, Google Calendar or Other) and paste the link. Add one row per platform (up to 10 per item).',
			),
			array(
				'title' => 'Save the item, then click Sync now',
				'body'  => 'Save / Update the item. New links are downloaded in the background within a few minutes; click "Sync now" to do it at once. Each row then shows "Synced … ago · N upcoming booked periods", and the dates are listed under "Upcoming imported dates".',
			),
			array(
				'title' => 'Check that it works',
				'body'  => 'Open the item on your website: the platform\'s booked nights are greyed out in the booking calendar. Open your Export link in a private browser window: it downloads a file that starts with BEGIN:VCALENDAR. On the platform, the imported calendar shows as connected (the first import can take a few hours there).',
			),
		),
		'channels_note' => 'Platforms rename their menus from time to time. On any platform, look for "Sync calendars", "Connect calendars", "Import calendar", "Export calendar" or "iCal".',
		'channels' => array(
			array(
				'name'          => 'Airbnb',
				'to_platform'   => array(
					'Airbnb → Listings → choose the listing → Availability (calendar settings).',
					'Under "Connect calendars" (calendar sync), choose "Connect to another website".',
					'Paste the item\'s Export link, name it (e.g. "My website") and click Connect.',
				),
				'from_platform' => array(
					'Same screen → "Export calendar".',
					'Copy the link shown (it ends in .ics).',
					'In the rental item: Add calendar → Channel "Airbnb" → paste → save the item → Sync now.',
				),
				'note'          => 'Airbnb downloads your link on its own schedule, usually every few hours. Its export contains reservations and the nights you block by hand ("Airbnb (Not available)"); both block the item here.',
			),
			array(
				'name'          => 'Booking.com',
				'to_platform'   => array(
					'Extranet → Rates & Availability → Sync calendars.',
					'Choose the room / unit and click "Add calendar connection" (import a calendar).',
					'Paste the item\'s Export link, give it a name and save.',
				),
				'from_platform' => array(
					'Same screen → export calendar.',
					'Copy the link.',
					'In the rental item: Add calendar → Channel "Booking.com" → paste → save the item → Sync now.',
				),
				'note'          => 'Connect each Booking.com room / unit to its own rental item. Booking.com offers calendar sync mainly for homes and apartments, and hides it when the property already uses a channel manager.',
			),
			array(
				'name'          => 'Stayz / Vrbo',
				'to_platform'   => array(
					'Owner dashboard → Calendar → Import & export (Stayz runs on the Vrbo platform, so the screens are the same).',
					'Choose "Import calendar".',
					'Paste the item\'s Export link, name it and save.',
				),
				'from_platform' => array(
					'Calendar → Import & export → "Export calendar".',
					'Copy the link.',
					'In the rental item: Add calendar → Channel "Stayz" or "Vrbo" → paste → save the item → Sync now.',
				),
				'note'          => 'Imported calendars are refreshed by Stayz / Vrbo on their own schedule.',
			),
			array(
				'name'          => 'Google Calendar',
				'to_title'      => 'Link 1 — your bookings into Google Calendar',
				'from_title'    => 'Link 2 — busy events in Google block your website',
				'to_platform'   => array(
					'To see the item\'s bookings in Google: Google Calendar → Other calendars → + → From URL.',
					'Paste the item\'s Export link and click Add calendar.',
				),
				'from_platform' => array(
					'To block dates from Google: Google Calendar → Settings → choose the calendar → Integrate calendar.',
					'Copy "Secret address in iCal format".',
					'In the rental item: Add calendar → Channel "Google Calendar" → paste → save the item → Sync now.',
				),
				'note'          => 'Only events shown as Busy block dates; events marked Free do not. Google refreshes calendars added from a URL slowly (it can take many hours).',
			),
			array(
				'name'          => 'Any other platform (iCal)',
				'to_title'      => 'Link 1 — your bookings to the platform',
				'from_title'    => 'Link 2 — the platform\'s bookings to your website',
				'to_platform'   => array(
					'Find the listing\'s "import calendar", "iCal" or "calendar sync" option.',
					'Paste the item\'s Export link and save.',
				),
				'from_platform' => array(
					'Copy the platform\'s export / iCal (.ics) link for the listing.',
					'In the rental item: Add calendar → Channel "Other (iCal link)" → paste → save the item → Sync now.',
				),
				'note'          => 'Any platform or calendar app that publishes a standard iCal (.ics) link works the same way.',
			),
		),
		'behaviour' => array(
			array(
				'title' => 'Which dates are blocked on your website',
				'body'  => 'Every booking and every blocked date in a connected calendar, except cancelled events and events marked as free time. A stay from the 20th to the 23rd blocks the nights of the 20th, 21st and 22nd; the 23rd (check-out day) stays bookable so a new guest can arrive that day. Blocked dates are greyed out in the booking calendar, the item is left out of search results when the searched pickup date is blocked, and a pickup-to-return range that crosses blocked dates cannot be chosen.',
			),
			array(
				'title' => 'Where a clashing booking is refused',
				'body'  => 'Even on a cached page, the booking is refused when it is added to the cart, again in the cart and at checkout (a rental already in a cart is stopped if the dates were imported meanwhile), in the Standalone checkout and for booking requests. The customer is told which dates are taken. Bookings you create yourself in the admin (Backend Orders add-on) are not blocked, so you can still override.',
			),
			array(
				'title' => 'What the Export link sends',
				'body'  => 'The nights the item cannot take another booking: when every unit is booked (paid, processing, on-hold and pending orders all count, as in the stock rules). An item with several units is closed on the platforms only when the last unit is gone. By default it also sends the item\'s Off Day date ranges and the global off dates as "Not available" (Export Closed Dates); weekly off days are not sent. It covers the next 12 months (Export Period). It contains dates only — no names, emails, phone numbers or order numbers.',
			),
			array(
				'title' => 'How fast it syncs',
				'body'  => 'Your website downloads the connected calendars every 15 minutes, 30 minutes, hour or 3 hours (Import Frequency), right after a new link is saved, and whenever you click Sync now. The platforms download your Export link on their own schedule — often every one to a few hours — which you cannot speed up. No iCal sync is instant, so the same dates can occasionally be booked on two sites within that short window; keep the Import Frequency short to narrow it.',
			),
			array(
				'title' => 'If a platform cannot be reached',
				'body'  => 'If a calendar cannot be downloaded (the platform is down, or the link was removed), the dates from the last successful download stay in force, so booked dates never reopen by accident. The calendar row shows the error in red until a sync succeeds again.',
			),
			array(
				'title' => 'Share Imported Dates (hub mode)',
				'body'  => 'Off by default: the Export link only sends bookings made on your website, so connect the platforms to each other as well (for example Airbnb ↔ Booking.com). Turn it on in Settings → Calendar Sync to make your website the hub instead: each imported calendar row then shows its own "Export link for this channel" — paste that link, not the general one, into that platform. It sends every other platform\'s dates but never the platform\'s own, so bookings cannot bounce back. Use either hub mode or direct platform-to-platform links, not both.',
			),
			array(
				'title' => 'Changing, removing or resetting links',
				'body'  => 'Delete a row (bin icon) or change its link and save the item: the old calendar\'s dates stop blocking at once. If the Export link was shared by mistake, click Reset link and paste the new link into every platform — the old one stops working immediately.',
			),
		),
		'troubleshooting' => array(
			array(
				'q' => 'Do I need to enter an Airbnb / Booking.com ID, API key or password anywhere?',
				'a' => 'No. Only the links are exchanged. The platform\'s export link already identifies the listing, and your Export link already identifies the rental item (with a secret key).',
			),
			array(
				'q' => 'The platform says it cannot read or import my calendar link.',
				'a' => 'Open the Export link in a private browser window. It must download text that starts with BEGIN:VCALENDAR. "Not found" means the link was reset or the item is in the trash — copy the current link from the item. If the site cannot be opened from outside your network (localhost, 192.168.x.x, a password-protected or coming-soon site), the platform cannot reach it either. Security / firewall rules (bot protection) and page-cache plugins must let addresses containing ?rbfw_ical= through and must not cache them.',
			),
			array(
				'q' => 'The calendar row says "The link did not return a calendar (.ics) file".',
				'a' => 'The pasted address is a web page (usually the listing page), not the calendar. Copy the platform\'s export / iCal link instead; it usually ends in .ics.',
			),
			array(
				'q' => 'The calendar row says "The calendar link answered with HTTP 404" (or 403 / 410).',
				'a' => 'The platform no longer accepts that link: it was regenerated or the calendar connection was removed there. Copy a fresh export link from the platform, replace it in the item and save.',
			),
			array(
				'q' => 'The calendar row says "Could not download the calendar".',
				'a' => 'The platform did not answer in time or the address could not be reached. It retries automatically at the next sync, and the previous dates stay in force meanwhile. For security, links pointing to private or local network addresses are refused.',
			),
			array(
				'q' => 'Dates booked on Airbnb (or another platform) are still open on my website.',
				'a' => 'Open the item and click Sync now, then read the row status. Check that Rent Item → Settings → Calendar Sync is ticked. If the status is fine but a page still shows the old calendar, clear your page cache — the booking is refused at add to cart anyway. If syncs only happen when you click Sync now, WordPress scheduled tasks are not running: the site needs visitors or a real server cron job for wp-cron.php.',
			),
			array(
				'q' => 'A booking made on my website does not show on Airbnb / Booking.com.',
				'a' => 'The platform downloads your link on its own schedule (often every few hours). The Export link only lists nights where no unit is left, so an item with 2 units and 1 booking stays open; cancelled orders are not sent. Check that the platform shows your calendar as connected.',
			),
			array(
				'q' => 'Imported dates are one day off.',
				'a' => 'Check Settings → General → Timezone in WordPress: it must be the time zone of the property. Times from the platforms are converted to that time zone.',
			),
			array(
				'q' => 'Can I see the guest\'s name for an imported booking?',
				'a' => 'Only what the platform writes into its calendar (for example Airbnb shows "Reserved", Booking.com "CLOSED - Not available"). Guest details stay on the platform. Imported periods are listed under "Upcoming imported dates" in the item.',
			),
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
		array( 'feature' => 'Calendar Sync with Airbnb, Booking.com, Stayz, Vrbo (iCal)', 'free' => false, 'pro' => true ),
		array( 'feature' => 'Customer Portal (login, bookings, invoices)', 'free' => false, 'pro' => true ),
		array( 'feature' => 'Reports / attendee dashboard',             'free' => false, 'pro' => true ),
	),

	/* ------------------------------------------------------------------ *
	 * 7. Troubleshooting / FAQ
	 * ------------------------------------------------------------------ */
	'faq' => array(
		array(
			'q' => 'How do I sync availability with Airbnb, Booking.com, Stayz or Vrbo?',
			'a' => 'Use Calendar Sync (Pro). No API key or ID is needed: you exchange iCal links between each rental item and each platform listing. Follow the step-by-step guide in the Calendar Sync section of this page.',
		),
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
