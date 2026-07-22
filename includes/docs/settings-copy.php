<?php
/**
 * Rental Docs — plain-English copy for each setting.
 *
 * settings-data.php is generated from the plugin's own settings (labels, types,
 * defaults, choices). The wording there is terse and written for developers, so
 * this file supplies the human explanation shown in the documentation:
 *
 *   'what'  — what the setting actually does, in plain language.
 *   'where' — where the customer (or you) sees the effect.
 *
 * Keyed by the setting name. Anything without an entry falls back to the
 * plugin's own description. Add new settings here — nothing else to change.
 *
 * @package Booking_And_Rental_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------- General Settings ---------------- */
	'rbfw_gutenburg_switch' => array(
		'what'  => 'Chooses which editor you use when writing a rental item description. Leave this off to use the plugin\'s own simplified editor, or turn it on if your team prefers the standard WordPress block editor.',
		'where' => 'Affects the admin editing screen only — customers never see a difference.',
	),
	'rbfw_rent_label' => array(
		'what'  => 'The name used for your rental items throughout the admin. Change it to match your business — for example Cars, Bikes, Rooms or Equipment.',
		'where' => 'The left-hand admin menu and the item screens. It does not change any existing web addresses.',
	),
	'rbfw_rent_slug' => array(
		'what'  => 'The word that appears in the web address of every rental item page. Keep it short and meaningful, such as "cars" or "rooms".',
		'where' => 'The public link of each item, e.g. yoursite.com/rent/item-name. After changing it, open Settings → Permalinks and press Save once, otherwise item pages will show "page not found".',
	),
	'rbfw_rent_icon' => array(
		'what'  => 'The small icon shown beside your rental menu. Any standard WordPress icon name can be used.',
		'where' => 'The admin sidebar only.',
	),
	'rbfw_thankyou_page' => array(
		'what'  => 'The page customers land on once their booking is complete, where the confirmation and booking summary appear.',
		'where' => 'Shown immediately after checkout. This page must contain the booking confirmation shortcode.',
	),
	'rbfw_search_page' => array(
		'what'  => 'The page that displays the results when somebody uses a rental search form.',
		'where' => 'Customers are sent here after pressing Search. This page must contain the search results shortcode.',
	),
	'rbfw_count_extra_day_enable' => array(
		'what'  => 'Decides whether the return day is charged. When on, a Monday to Wednesday hire counts as three days; when off it counts as two.',
		'where' => 'Every price the customer sees — item page, search results, basket and final total.',
	),
	'rbfw_pricing_info_display' => array(
		'what'  => 'Shows a "Pricing Info" link on the booking form that opens a full breakdown of your rates, so customers can see how the price is worked out.',
		'where' => 'The booking form on each rental item page.',
	),
	'rbfw_real_time_availability_display' => array(
		'what'  => 'Shows reassurance wording telling customers that availability is live and confirmation is instant.',
		'where' => 'The booking summary on the item page.',
	),
	'want_loco_translate' => array(
		'what'  => 'Turn this on if you translate the plugin with a translation tool such as Loco Translate. The plugin then ignores the wording typed into its own translation boxes so your translations are used instead.',
		'where' => 'All customer-facing wording.',
	),
	'today_booking_enable' => array(
		'what'  => 'Allows customers to book for today. Leave it off if you need notice to prepare items before collection.',
		'where' => 'The date picker on every booking form.',
	),
	'inventory_managed_order_status' => array(
		'what'  => 'Chooses which order statuses reserve stock. An item only becomes unavailable to other customers once its order reaches one of the statuses you tick here.',
		'where' => 'Availability everywhere — search, calendar and booking forms.',
	),
	'rbfw_allow_duplicate_rental_cart_item' => array(
		'what'  => 'Allows the same item to be added to the basket more than once — for example the same car booked for two different weeks. When off, each item can only appear in the basket once.',
		'where' => 'The basket.',
	),
	'inventory_based_on_return' => array(
		'what'  => 'Releases an item for the next customer based on when it is returned rather than blocking the whole day, so back-to-back hires are possible.',
		'where' => 'Availability checks and search results.',
	),
	'rbfw_share_section_enable' => array(
		'what'  => 'Shows social sharing buttons so visitors can share an item with others.',
		'where' => 'The rental item page.',
	),
	'pricing_display_for_listing' => array(
		'what'  => 'Chooses which rate is used for the "from" price shown in listings — hourly, daily, weekly or monthly. Pick the one that best represents how you hire out.',
		'where' => 'Item cards in listings and search results.',
	),
	'rbfw_new_list_design' => array(
		'what'  => 'Uses the modern, mobile-friendly card and table layout for your rental items list. Turn it off to return to the plain WordPress list.',
		'where' => 'The admin list of rental items.',
	),

	/* ---------------- Style Settings ---------------- */
	'rbfw_rent_list_base_color' => array(
		'what'  => 'The main accent colour used across your rental listings — buttons, prices and highlights.',
		'where' => 'Listing and search-result pages.',
	),
	'rbfw_single_page_base_color_4' => array(
		'what'  => 'The supporting colour used alongside the main accent in listings, typically for text and outlines.',
		'where' => 'Listing and search-result pages.',
	),
	'rbfw_single_page_base_color_5' => array(
		'what'  => 'The main accent colour of the booking form — the Book Now button, selected dates and highlights.',
		'where' => 'The booking form on item pages.',
	),
	'rbfw_single_page_secondary_color' => array(
		'what'  => 'The supporting colour on the booking page, used for headings and body text.',
		'where' => 'Rental item pages.',
	),
	'rbfw_booking_form_bg_color' => array(
		'what'  => 'The background colour of the booking form panel.',
		'where' => 'The booking form on item pages.',
	),
	'rbfw_single_page_base_color_1' => array(
		'what'  => 'An extra accent colour used for highlights by some item page layouts.',
		'where' => 'Item pages using a matching template.',
	),
	'rbfw_single_page_base_color_2' => array(
		'what'  => 'A second extra accent colour used by some item page layouts, usually for darker areas.',
		'where' => 'Item pages using a matching template.',
	),
	'rbfw_single_page_base_color_3' => array(
		'what'  => 'A third extra accent colour available to item page layouts.',
		'where' => 'Item pages using a matching template.',
	),
	'rbfw_single_page_base_color_6' => array(
		'what'  => 'A further accent colour available to item page layouts.',
		'where' => 'Item pages using a matching template.',
	),

	/* ---------------- Custom CSS ---------------- */
	'rbfw_custom_css' => array(
		'what'  => 'Your own styling rules, applied on top of the plugin\'s design. Useful for small visual tweaks without editing your theme. If you are unsure, leave this empty.',
		'where' => 'All customer-facing rental pages.',
	),

	/* ---------------- Checkout Page ---------------- */
	'rbfw_wps_add_to_cart_redirect' => array(
		'what'  => 'Where customers go immediately after adding an item to the basket. Sending them to the checkout shortens the journey; sending them to the basket encourages extra bookings.',
		'where' => 'Applies when you take payment through WooCommerce.',
	),
	'rbfw_mps_currency' => array(
		'what'  => 'The currency used when you take bookings without WooCommerce. Enter the three-letter code, such as USD, EUR or GBP.',
		'where' => 'Every price shown in Standalone mode.',
	),
	'rbfw_mps_currency_position' => array(
		'what'  => 'Where the currency symbol sits in relation to the amount.',
		'where' => 'Every price shown in Standalone mode.',
	),
	'rbfw_mps_currency_thousand_sep' => array(
		'what'  => 'The character separating thousands — for example the comma in 1,000.',
		'where' => 'Every price shown in Standalone mode.',
	),
	'rbfw_mps_currency_decimal_sep' => array(
		'what'  => 'The character placed before the decimals — for example the full stop in 9.99.',
		'where' => 'Every price shown in Standalone mode.',
	),
	'rbfw_mps_currency_decimal_number' => array(
		'what'  => 'How many decimal places prices are shown to. Use 0 if you only deal in whole amounts.',
		'where' => 'Every price shown in Standalone mode.',
	),

	/* ---------------- Payments ---------------- */
	'rbfw_booking_mode_selector' => array(
		'what'  => 'Chooses how you take payment: through WooCommerce, using its basket, checkout and payment methods; or Standalone, where the plugin handles the booking and checkout on its own without WooCommerce.',
		'where' => 'The whole booking and checkout journey.',
	),
	'rbfw_payment_tabs_html' => array(
		'what'  => 'The tabbed panel that groups the payment options on this screen.',
		'where' => 'This settings screen only.',
	),
	'rbfw_wc_payment_gateways_manager' => array(
		'what'  => 'Lets you choose which of your WooCommerce payment methods are offered for rental bookings, so you can accept different methods for hires than for ordinary products.',
		'where' => 'The checkout.',
	),
	'rbfw_wc_add_to_cart_redirect' => array(
		'what'  => 'Where customers are taken after adding an item to the basket.',
		'where' => 'Applies when you take payment through WooCommerce.',
	),
	'rbfw_wc_require_login' => array(
		'what'  => 'Requires customers to sign in or create an account before they can finish a booking. Useful if you need a verified customer record; leave off for the quickest checkout.',
		'where' => 'The checkout.',
	),
	'rbfw_wc_show_billing_info' => array(
		'what'  => 'Shows the billing address fields at checkout. Turn it off for a shorter form when you do not need an address.',
		'where' => 'The checkout.',
	),
	'rbfw_wc_confirm_status' => array(
		'what'  => 'Chooses which order statuses mark a booking as confirmed. Until an order reaches one of these, the booking stays unconfirmed.',
		'where' => 'Booking records, confirmation emails and availability.',
	),
	'rbfw_payment_gateways_ui' => array(
		'what'  => 'The panel where you set up the payment methods offered in Standalone mode, such as card or PayPal payments.',
		'where' => 'The Standalone checkout.',
	),

	/* ---------------- PDF Settings ---------------- */
	'rbfw_send_pdf' => array(
		'what'  => 'Attaches a printable booking ticket to the confirmation email, so customers can bring it with them.',
		'where' => 'The confirmation email sent to the customer.',
	),
	'rbfw_thermal_width' => array(
		'what'  => 'The paper width for the till-style receipt. Match this to the roll in your receipt printer.',
		'where' => 'The receipt printed from the Bookings screen.',
	),
	'rbfw_pdf_logo' => array(
		'what'  => 'Your logo, printed at the top of the booking ticket.',
		'where' => 'The PDF ticket.',
	),
	'rbfw_pdf_bg' => array(
		'what'  => 'An optional background image for the ticket. Around 680 pixels wide gives the best result.',
		'where' => 'The PDF ticket.',
	),
	'rbfw_pdf_address' => array(
		'what'  => 'Your company address, printed on the ticket.',
		'where' => 'The PDF ticket.',
	),
	'rbfw_pdf_phone' => array(
		'what'  => 'A contact phone number printed on the ticket so customers can reach you.',
		'where' => 'The PDF ticket.',
	),
	'rbfw_pdf_tc_title' => array(
		'what'  => 'The heading shown above your terms on the ticket — for example "Terms & Conditions".',
		'where' => 'The footer of the PDF ticket.',
	),
	'rbfw_pdf_tc_text' => array(
		'what'  => 'The terms and conditions wording printed on the ticket, such as your damage, fuel or cancellation policy.',
		'where' => 'The footer of the PDF ticket.',
	),
	'rbfw_pdf_bg_color' => array(
		'what'  => 'The background colour of the ticket.',
		'where' => 'The PDF ticket.',
	),
	'rbfw_pdf_text_color' => array(
		'what'  => 'The text colour of the ticket. Keep a strong contrast with the background so it prints clearly.',
		'where' => 'The PDF ticket.',
	),

	/* ---------------- Email Settings ---------------- */
	'rbfw_email_status' => array(
		'what'  => 'Chooses which order statuses trigger the confirmation email. Nothing is sent until an order reaches one of the statuses ticked here.',
		'where' => 'The customer\'s inbox.',
	),
	'rbfw_email_subject' => array(
		'what'  => 'The subject line of the booking confirmation email.',
		'where' => 'The customer\'s inbox.',
	),
	'rbfw_email_content' => array(
		'what'  => 'The message customers read in the confirmation email. The booking details are added underneath automatically, so a short friendly note is enough.',
		'where' => 'The customer\'s inbox.',
	),
	'rbfw_email_from_name' => array(
		'what'  => 'The sender name customers see. Leave blank to use your site name.',
		'where' => 'The customer\'s inbox.',
	),
	'rbfw_email_from' => array(
		'what'  => 'The address the email is sent from. Use an address on your own domain, otherwise messages are more likely to be treated as spam.',
		'where' => 'The customer\'s inbox.',
	),
	'native_email_heading' => array(
		'what'  => 'A divider introducing the settings for bookings taken without WooCommerce.',
		'where' => 'This settings screen only.',
	),
	'native_email_enable' => array(
		'what'  => 'Turns on confirmation emails for bookings taken through the Standalone checkout. If Standalone bookings are not being acknowledged, check this first.',
		'where' => 'The customer\'s and your inbox.',
	),
	'native_attach_pdf' => array(
		'what'  => 'Attaches the printable booking ticket to Standalone confirmation emails.',
		'where' => 'The customer\'s inbox.',
	),
	'native_admin_email' => array(
		'what'  => 'The address that receives the new-booking alert. Leave blank to use the main site administrator address.',
		'where' => 'Your inbox.',
	),
	'native_customer_email_subject' => array(
		'what'  => 'The subject line of the Standalone booking confirmation email.',
		'where' => 'The customer\'s inbox.',
	),
	'native_customer_email_content' => array(
		'what'  => 'A short introduction for the Standalone confirmation email; the booking summary is added automatically beneath it. Type {customer_name} where you want the customer\'s name to appear.',
		'where' => 'The customer\'s inbox.',
	),
	'native_admin_email_subject' => array(
		'what'  => 'The subject line of the new-booking alert sent to you.',
		'where' => 'Your inbox.',
	),
	'native_admin_email_content' => array(
		'what'  => 'The message body of the new-booking alert. The customer and booking details are added automatically.',
		'where' => 'Your inbox.',
	),

	/* ---------------- Review Settings ---------------- */
	'rbfw_review_system' => array(
		'what'  => 'Lets customers leave star ratings and written reviews on your rental items. Reviews wait for your approval before they appear.',
		'where' => 'Rental item pages.',
	),

	/* ---------------- Super Slider ---------------- */
	'super_slider_type' => array(
		'what'  => 'Chooses whether an item\'s photos appear as a rotating slideshow or as a single main photo.',
		'where' => 'The top of the rental item page.',
	),
	'super_slider_style' => array(
		'what'  => 'The visual style of the slideshow.',
		'where' => 'The top of the rental item page.',
	),
	'super_slider_indicator_visible' => array(
		'what'  => 'Shows the small markers telling customers how many photos there are and which one they are viewing.',
		'where' => 'The item photo slideshow.',
	),
	'super_slider_indicator_type' => array(
		'what'  => 'Whether those markers are simple dots or small photo thumbnails.',
		'where' => 'The item photo slideshow.',
	),
	'super_slider_showcase_visible' => array(
		'what'  => 'Shows the strip of thumbnail photos beside the main image.',
		'where' => 'The item photo slideshow.',
	),
	'super_slider_showcase_position' => array(
		'what'  => 'Which side of the main photo the thumbnail strip sits on.',
		'where' => 'The item photo slideshow.',
	),
	'super_slider_popup_image_indicator' => array(
		'what'  => 'Shows thumbnail markers when a photo is opened full screen.',
		'where' => 'The enlarged photo view.',
	),
	'super_slider_popup_icon_indicator' => array(
		'what'  => 'Shows the next and previous arrows when a photo is opened full screen.',
		'where' => 'The enlarged photo view.',
	),
	'slider_height' => array(
		'what'  => 'How tall the photo area is. Taller images stand out more but push the booking form further down the page.',
		'where' => 'The top of the rental item page.',
	),

	/* ---------------- Front-end Display ---------------- */
	'hero_badge_show' => array(
		'what'  => 'Shows a promotional badge over the item\'s main photo to draw attention.',
		'where' => 'The top of the rental item page.',
	),
	'hero_badge_text' => array(
		'what'  => 'The wording on the badge over the main photo — for example "Best Seller", "New" or "Popular".',
		'where' => 'The top of the rental item page.',
	),
	'hero_badge_bg' => array(
		'what'  => 'The background colour of the badge on the main photo.',
		'where' => 'The top of the rental item page.',
	),
	'hero_badge_color' => array(
		'what'  => 'The text colour of the badge on the main photo.',
		'where' => 'The top of the rental item page.',
	),
	'avail_badge_show' => array(
		'what'  => 'Shows a badge confirming the item can be booked today, which reassures customers who need it straight away.',
		'where' => 'The booking summary on the item page.',
	),
	'avail_badge_text' => array(
		'what'  => 'The wording of the availability badge.',
		'where' => 'The booking summary on the item page.',
	),
	'avail_badge_bg' => array(
		'what'  => 'The background colour of the availability badge.',
		'where' => 'The booking summary on the item page.',
	),
	'avail_badge_color' => array(
		'what'  => 'The text colour of the availability badge.',
		'where' => 'The booking summary on the item page.',
	),
	'seller_badge_show' => array(
		'what'  => 'Shows a popularity badge in the booking summary to encourage customers to book.',
		'where' => 'The booking summary on the item page.',
	),
	'seller_badge_text' => array(
		'what'  => 'The wording of the popularity badge.',
		'where' => 'The booking summary on the item page.',
	),
	'seller_badge_bg' => array(
		'what'  => 'The background colour of the popularity badge.',
		'where' => 'The booking summary on the item page.',
	),
	'seller_badge_color' => array(
		'what'  => 'The text colour of the popularity badge.',
		'where' => 'The booking summary on the item page.',
	),
	'summary_title_show' => array(
		'what'  => 'Shows the heading above the booking summary.',
		'where' => 'The booking summary on the item page.',
	),
	'summary_title_text' => array(
		'what'  => 'The heading wording above the booking summary.',
		'where' => 'The booking summary on the item page.',
	),
	'summary_title_color' => array(
		'what'  => 'The colour of the booking summary heading.',
		'where' => 'The booking summary on the item page.',
	),
	'summary_desc_show' => array(
		'what'  => 'Shows the short explanatory line beneath the booking summary heading.',
		'where' => 'The booking summary on the item page.',
	),
	'summary_desc_text' => array(
		'what'  => 'The explanatory wording beneath the booking summary heading — a good place to tell customers what to do next.',
		'where' => 'The booking summary on the item page.',
	),
	'summary_desc_color' => array(
		'what'  => 'The colour of the explanatory line beneath the heading.',
		'where' => 'The booking summary on the item page.',
	),
	'search_type_label' => array(
		'what'  => 'The wording shown in the rental type box of the search form before a customer chooses — for example "Rental Type" or "What do you need?".',
		'where' => 'Search forms.',
	),
	'search_location_label' => array(
		'what'  => 'The wording shown in the location box of the search form before a customer chooses — for example "Pickup Location".',
		'where' => 'Search forms.',
	),
);
