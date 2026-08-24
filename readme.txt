=== Booking and Rental Manager for Bike | Car | Resort | Appointment | Dress | Equipment ===
Contributors: magepeopleteam, aamahin, raselsha, rabiul042
Plugin link: https://mage-people.com/
Tags: woocommerce rental, rental booking, booking calendar, car rental, bike rental
Requires at least: 5.3
Stable tag: 2.7.6
Tested up to: 7.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create rental and booking products with WooCommerce for cars, bikes, equipment, dresses, resorts, appointments, and more.

== Description ==

Booking and Rental Manager for WooCommerce helps you build a rental booking system inside WordPress and WooCommerce. Create single-day, multi-day, appointment, resort, equipment, dress, and multi-item rentals, then accept bookings through the WooCommerce checkout flow.

Use it for car rentals, bike rentals, equipment rentals, dress rentals, appointment booking, resort booking, vacation rentals, parking, boat rentals, sports kits, office spaces, and other rental businesses that need date-based booking, pricing, inventory, and order management.

= Demo and documentation =

* [Frontend live demo](https://wprently.com/)
* [Documentation](https://docs.mage-people.com/plugins/wprently/overview)
* [Pro version](https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-pro/)

= What you can build =

* Car and bike rental websites
* Equipment and tool rental websites
* Dress and costume rental websites
* Appointment booking systems
* Resort and room booking websites
* Vacation rental and holiday rental websites
* Boat, sports kit, parking, and office space rental websites
* Multi-item rental catalogs with separate item quantities

= Key free features =

* WooCommerce-based rental checkout and payment flow
* Single-day and multi-day rental booking
* Multi-item rental support
* Appointment and resort booking support
* Hourly, daily, weekly, monthly, day-wise, day-long, and day-night pricing options
* Pickup and drop-off location settings
* Inventory and availability controls
* Extra services and add-ons
* Dress size configuration
* Related rental items and highlighted features
* Rental galleries, sliders, and multiple list layouts
* Frontend customer booking dashboard
* Order list with booking details
* Tax settings and WooCommerce payment gateway support
* Responsive layouts and multilingual translation support
* Shortcodes for rental lists and single-item booking forms

= Pro features =

* Booking calendar with order details
* Reports with order details
* CSV export for reports
* PDF booking receipts
* Booking-related email features

[View Pro version](https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-pro/)

= Video overview =

https://www.youtube.com/watch?v=JK33WAWKo7E

= Shortcodes =

Show rental items:

`[rent-list type='' style='grid' show='' columns='' left-filter='yes/no' category='' order='DESC']`

Show only single-day bike/car rentals:

`[rent-list type='bike_car_sd']`

Show grid-style rental list:

`[rent-list style='grid' left-filter='yes/no']`

Show list-style rental list:

`[rent-list style='list' left-filter='yes/no']`

Show six rental items:

`[rent-list show='6']`

Show rental items in ascending order:

`[rent-list order='ASC']`

Show a four-column rental list:

`[rent-list columns='4']`

Show rentals from selected category IDs:

`[rent-list category='2,3']`

Show the booking form for one rental item:

`[rent-add-to-cart id='1']`

= Support =

Need help or want to suggest an improvement? Use the [support form](https://mage-people.com/submit-ticket/).

= Theme compatibility =

The plugin is designed to work with standards-compliant WordPress themes.

== Installation ==

1. Go to `Plugins > Add New` in the WordPress dashboard.
2. Search for `Booking and Rental Manager`.
3. Click `Install Now`, then `Activate`.
4. Create a rental item and configure pricing, availability, and booking settings.

You can also upload the plugin ZIP from `Plugins > Add New > Upload Plugin`.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes. The plugin uses WooCommerce for cart, checkout, orders, and payment gateways.

= Can I create car or bike rentals with hourly and daily pricing? =

Yes. You can configure hourly, daily, weekly, monthly, and other pricing types depending on the rental setup.

= Can I manage more than one rental item in a single product? =

Yes. Multi-item rentals are supported, including separate quantities for multiple rentable items.

= Can I use shortcodes to show rental lists? =

Yes. Use the included `[rent-list]` and `[rent-add-to-cart]` shortcodes to place rental content on pages.

== Screenshots ==

1. Rental booking form with duration, date, and pricing controls.
2. Multi-item rental booking interface with item quantities.
3. Rental item configuration screen in the WordPress admin.
4. Availability, inventory, and booking settings.
5. Rental list layout for browsing available items.
6. Booking order details in the admin area.

== Other plugins ==

* [Booking and Rental Manager for WooCommerce Addon: Seasonal Pricing](https://mage-people.com/product/booking-and-rental-manager-for-woocommerce-addon-seasonal-pricing/)
* [Bus Booking Manager](https://wordpress.org/plugins/bus-booking-manager/)
* [Bus Ticket Booking with Seat Reservation](https://wordpress.org/plugins/bus-ticket-booking-with-seat-reservation/)
* [WooCommerce Events Manager](https://wordpress.org/plugins/mage-eventpress/)

== Privacy Policy ==

Booking and Rental Manager for WooCommerce uses the [Appsero](https://appsero.com) SDK to collect basic telemetry data only after the user gives permission through the admin notice. This helps with troubleshooting and product improvements.

Appsero does not collect data by default. Data collection starts only after user consent.

Learn more about how [Appsero collects and uses data](https://appsero.com/privacy-policy/).

== Changelog ==
2.7.6
Gravity Forms integration added. Any Gravity Form can be attached to any rental item as Booking Questions, from either the classic or the modern editor. The customer's answers travel with the booking into the cart, the WooCommerce order, the booking record, the Bookings screen and the confirmation e-mail, and are shown in a collapsible row beneath each booking. Three modes per item: asked before the booking form appears, optional alongside it, or the Gravity form itself as the order form. A Global Settings switch applies order-form mode to every rental that has a form, for shops running a single ordering flow. Fields are matched by type rather than by id, so editing a form in Gravity Forms cannot break the mapping, and both the WooCommerce and the Standalone checkout are covered. Note that when the Gravity form is the order form the dates come from that form, so availability, inventory and off days no longer restrict the booking.
Security Deposit Management added (Pro): a deposit can be included in the quoted rental price or charged on top, set globally and overridden per item, and marked refundable or non-refundable. Deposits are settled from a new Security Deposits screen with full or partial gateway refunds, refunds recorded outside WordPress, retained balances, customer notices and an audit history. Works in both WooCommerce and Standalone checkout.
Security deposits can now follow a Pro deposit policy. A new rbfw_security_deposit filter lets the Pro deposit manager show a deposit as a liability without adding it to the payable rental total, and the booking summary rows now carry their own price element so a figure can be rewritten in place. With no add-on installed the deposit behaves exactly as before.
Security: fixed rental availability, pricing and inventory data being readable for items that are not published. Booking endpoints now check that the requested item may actually be viewed, instead of only checking that the request came from the site, so drafts, pending, private and trashed items are no longer exposed to visitors. Administrators previewing an unpublished item are unaffected.
Security: fixed unpublished items being priced, coupon-discounted and added to the cart through the search, quick-add, coupon and standalone checkout endpoints.
Security: fixed draft and pending items leaking into public search results and into the sidebar filter options.
Security: fixed a PHP object injection issue. Serialized payloads can no longer be stored in the plugin's post meta, every value the plugin reads back is decoded with object creation disabled, and any payload already saved on the site is cleaned up automatically on the next admin page load.
Security: the item pricing form builder in the editor is no longer reachable by logged-out visitors and now requires edit permission.
Fixed Delivery and Collection wiping the rental cost from multi-day and multiple-items bookings. Choosing a delivery option left only the delivery legs and the fees on the Price row. In Standalone mode this was not cosmetic: the native checkout posts the displayed total, so the booking was created for the fees alone. Delivery and collection are now rendered as two separate lines in the multi-day and multiple-items summaries, and every multi-day total is written through one renderer that folds the delivery figure into the Price row. Server-side pricing was never affected, and both legs are still re-priced from the stored distance bands when the booking is added to the cart.
2.7.5
Delivery and Collection added (Pro): distance-band pricing with a base fee, a free radius and a maximum distance, editable labels, and a per-item opt-out. Collection can be priced the same as delivery, on its own bands, or free.
Customers pick a named delivery zone ("3 - 5 km - $5.00") instead of guessing a distance, and delivery and collection are shown, stored and invoiced as two separate lines. Every amount is recomputed on the server from the stored bands, so the browser can never lower the fee.
Delivery required-field settings added: whether a delivery choice is mandatory (optional, at least one leg, or both legs), and whether address, contact number and delivery notes must be completed.
Accounting payment methods added (card, cheque, cash, bank transfer). Editable in Settings > Payments, offered at the standalone checkout, and recordable on any booking from the Bookings screen, with revenue split by method.
Bookings can now be filtered and sorted by rental date, not only by the date the order was placed. A search for 13 August finds a 12-14 August rental.
Picked and Returned booking statuses now appear in the Order List column, badge, status filter, edit modal and export, so a return can be recorded without opening the booking.
Rent type and location archives now use the plugin's own listing (grid/list toggle, left filter, price and Book Now) instead of the theme's blog archive, and an item's categories are shown as links on the single item page.
New [rbfw_item_search] shortcode: an inline item-name search box.
Payment setup surfaced inside the modern editor: a Payment Method card, a warning while the active booking flow has no usable gateway, and a popup that fixes it without leaving the item.
New Booking Calendar setting to show customer names or the booking count on each day.
Fixed bookings being completed with a 0,00 total on cached pages. An expired nonce now asks the customer to reload instead of adding an empty line to the cart.
Fixed the same unit being booked twice on the same day when stock is not managed on the return date.
Fixed per-rental tax never reaching WooCommerce. An item set to Taxable is now taxed at checkout, and the tax is shown in the booking summary.
Fixed tax missing from the PDF receipt, e-mails, thank-you page and booking detail on High-Performance Order Storage sites.
Fixed activating WooCommerce hijacking a working Standalone shop and asking again which booking mode to use.
Fixed coupon rules in Standalone mode: the booking start date, billed days and customer e-mail are now read correctly, so weekday, blackout, booking-window, free-day and first-booking rules apply as configured. Validity dates are measured in site time, and a new per-coupon setting chooses whether that window is matched against the booking date or the redemption date.
Blackout Dates and Valid From/To are now date pickers with removable chips, and the discount type cards no longer overlap.
Fixed extra-service add-ons: the Resource Cost line now appears for the single-day slot table, zero lines and empty image placeholders are no longer rendered, and the add-on image lightbox works.
Fixed the "Available Today" badge showing when today is an Off Day.
Fixed block themes: the site header, navigation and footer now render correctly on rental item pages and on the new archives, with proper page gutters.
Fixed WooCommerce's Featured Products block listing every product, and category, tag, attribute and stock filters being dropped from other product queries.
Fixed a rental item becoming permanently unbookable when its hidden WooCommerce product was deleted. Broken links now repair themselves, and duplicating an item no longer shares the original's product.
Fixed the booking form scrolling past itself after choosing a date or time. The step that was loaded is brought into view instead.
Fixed missing item images in filtered search results when the photo lives only in the Gallery tab.
Fixed the rent list "list" view rendering as a stacked card instead of a row.
Fixed category filtering in [rent-list]: category names are accepted, and an item matching only the location is no longer returned.
Fixed the Bookings Payment Method filter returning nothing for a method plainly shown in the column, and the dropdown listing only "offline".
Fixed gateway settings overwriting unposted text fields with the string "off", which could erase a stored API key or label.
Duration strings such as "3 days" are now translatable, and the translation bundle was regenerated (2927 to 3117 strings, all 7 locales merged with no translations lost).

2.7.4
Seasonal and date-wise Min/Max booking days are now actually applied — the booking calendars honour them and they are enforced when adding to the cart. Previously these values were saved but never read.
Date-wise Min/Max ranges now work in the [rbfw_booking_search] widget as well.
Payments settings redesigned: both booking flows stay visible, and an unavailable one carries the action that unlocks it (activate WooCommerce, or enable the free Offline gateway).
Payments tab now follows the Global Settings colour scheme.
Offline Payment is now a free standalone gateway, with a single unified payment-method notice.
Inventory page now shows both the single-day and multi-day stock models.
Fee Configuration now respects its enable toggle everywhere.
Single-day item variations charge the base rate once, with quantity and price shown consistently.
Sample data import moved to a non-blocking corner widget with progress.
Fixed the [rbfw_booking_search] form layout on mobile.
Fixed the calendar still showing bookings deleted from the Bookings page.
Fixed modern editor rent-type switching and the Discount addon card.
Fixed duplicate rental items appearing in the WooCommerce cart.
Fixed the Booking Mode never being stored when the admin confirmed the flow that was already selected.
Fixed cart page notices on items saved without security deposit or fee data.
Fixed documentation links.

2.7.3
Unified coupon and discount engine for both WooCommerce and Standalone bookings.
Rental Docs added — in-admin documentation covering the free and Pro features.
Multi-location inventory and pricing for every rental type, plus a By Location tab on the Inventory page.
Booking search rebuilt: multi-item date, location and type search with in-page booking and checkout.
Per-attendee registration form data across all booking surfaces.
Global Settings redesigned, the Status page moved in as a tab, and the whole page made mobile responsive.
Order List gained CSV and PDF export, a revenue summary, and an Export Settings accordion.
Edit Stock added to the Inventory page, including extra-service stock.
Standalone booking status management and native checkout flow added.
Buffer Time is now enforced server-side and no longer overrides the "today booking" setting.
Single-day hourly inventory, sold-out time slots and end-time handling fixed.
Server-side availability checks added to prevent double booking.
Multi-day service price manipulation from the client blocked.
Modern editor saving fixed: toggle persistence, pricing-gate abort, and timeslots.
Fixed Scandinavian characters (ae/oe/aa) being corrupted on save.
Admin and frontend strings made translatable; POT and all 7 locale files regenerated.

2.7.2
New modern full-page rental item editor, now the default, with the classic editor still available.
Rental Items list redesigned: responsive layout, list view by default, image and author columns, icon actions and trash support.
Duplicate action added to the rental list.
Muffin theme templates added for single-day, multi-day, resort and multi-item bookings.
WooCommerce install and demo import are now chunked and low-memory safe.
Fixed a 504 timeout when filtering the Rent List by multiple categories.
Fixed broken access control in admin handlers by adding capability checks.
Fixed same-day rental price showing 0 when the monthly or weekly rate was enabled.
Count Extra Day now applies to the monthly and weekly pricing breakdown.
Fixed rental permalinks returning 404 after a rewrite flush.
Fixed pagination for the [rent-list] shortcode on live servers.
Fixed the left filter category search and a stray rent type.
Frontend pickup time now shows English AM/PM instead of the browser locale.

2.7.1
Code updated and minor bug fixes.
Big Design Improvemnt

2.7.0
Multiple items price manipulation vulnerable issue solved.
Multiple items server side price calculation updated.
Multiple items fee management total price calculation problem solved.
Elementor rent list category multiple select option added.
Elementor rent list left filter problem solved.
Cart and checkout multiple items price breakdown problem solved.
Category meta warning problem solved.
Code updated.

2.6.5
Signle day inventory problem solved.
Extra service pricing problem solved.


2.6.4
Fee Management feature update.
Location problem solved.
Inventory manage based on order status by settings.

2.6.3
Vernulable issue solved.
Terms and condition feature added.
Null value check and hide for price calculation.


2.6.2
Vernulable issue solved.
Sold out problem solved.
Bug fixing.

2.6.1
Vernulable issue solved.
Translation updated.
Location validation highlighted

2.5.9
Bug fixing.
Validation checked.
variation saving problem solved.

2.5.8
Vernulable issue solved.
Calandar soldout problem solved.


2.5.7
Currency position problem solved.
Decimal pricing calculation problem solved.
Soldout problem solbed in single day.

2.5.6
Date language problem solved.
Currency position problem solved.
Category wise search updated.
My accounct order pdf download problem solved and updated

2.5.5
Fee management option added.


2.5.4
Single day inventory problem solved.
Vernulable issue solved.

2.5.3
Time slot for particular date problem solved.
Buffer time option added.

2.5.2
Ajax request removed for time load depend on date.
Considered inventory management by return date
Code updated.
Bug fixing

2.5.1
Multi day Optional Add-ons pricing problem solved.


2.5.0
Bug fixing.
Translation updated.

2.4.9
bug fixing.
Security issue updated.

2.4.7
bug fixing.

2.4.6
Extra ajax request removed and calculation fast.
Updated UI and UX design.

2.4.4
Monthly and weekly price implemented for multi day.
Search result update.
Search form update.
bug fixing.

2.4.3
Search by rent type, pickup date and dropoff date by using "[rbfw_search search-type='item']" this shortcode.

2.4.2
Some bug fixing.

2.4.1
Dashboard UX & UI Improved
Hourly Thresold Option added.
Code updated.

= 2.4.0 =
* Updated pricing info.
* Add new resort problem solved in resort type.
* Code updated.

= 2.3.9 =
* For multi day default stock quantity is 100, if stock null.
* Console error fixed.
* Code updated.

= 2.3.8 =
* Sold out problem option added on calendar.
* Extra service and totat pricing problem solved in cart page.


= 2.3.7 =
* Add to cart redirection problem solved.
* Count extra day calculation problem solved in resort.


= 2.3.6 =
* Shipping class select option added.
* Code updat
* Shortcode problem solved.

= 2.3.4 =
* Translation issues solved.
* Icon adding problem solved.

= 2.3.3 =
* Warning fixed.

= 2.3.1 =
* Seassional price implementation on single day option.
* In Multiday quantity seassional price solve solved.
* PDF issue solved.

= 2.2.8 =
* Cart page update.
* In Multiday quantity visible option solved.
* Category wise showing fixed.

= 2.2.7 =
* Quantity off option added in single day.
* Multi category solved in rent list page.


= 2.2.6 =
* Security discount exclude vat.
* Shipping enable fixed.


= 2.2.5 =
* Label fixed.
* Warning removed.
* Single daye time fixed.


= 2.2.2 =
* Setup wizard updated.
* Vurnalable issue fixed.
* Appointmanet problem solved.
* Multiple cart problem solved.
* Bug fixed.

= 2.2.1 =
* Multi date inventory problem solved.
* Code updated.

= 2.2.0 =
* Day wise time slot option added.
* Manage inventory as hourly option added.


= 2.1.9 =
* Date calendar weekday start as "Week Starts On" settings.
* Time format fixed.
* Single Day Inventory problem solved.
* Multi Day Inventory problem solved.
* Single day sold out problem solved for without time slot.
* Multiday minimum day count 1 instead of 0.


= 2.1.5 =
* Single day discount problem solved.
* Resort date problem solved.
* Order list modifications.
* Language wise datepicker and price problem solved.
* Price info in multi day, daywise and seasonal price added.


= 2.1.4 =
* Resort discount and checkout problem solved.
* Discount price displaying convert to amount instead of percentage.
* Code updated.
* Multiday cart page pricing solved.


= 2.1.3 =
* pricing fixed on cart and thankyou page.


== Changelog ==
= 2.1.2 =
* Pickup and drop off point added in single day.
* Registration form and service info dispaly after select ticket.
* Bug fixing

== Changelog ==
= 2.1.1 =
* add_to_cart_id Constant warning removed.

== Changelog ==
= 2.1.1 =
* Hourly price solved if day pricing disable.
* Time slot order fixed



== Changelog ==
= 2.1.0 =
* Reports paid amount and filtering problem solved.


= 2.0.9 =
* Multiple day validation.
* Minimum day count 1
* Code updated and Bug fixing
* Time Based inventory option added.


= 2.0.8 =
* Same day booking for Single day problem solved.


= 2.0.7 =
* Single day item pricing solved.
* Realated product pricing problem solved.
* Multiple day item end date problem solved.


= 2.0.6 =
* Price circle overlaping fix.
* Multiple day booking resource section stock quanty added.
* Block theme width adjustment.
* css update in few sections
* Feature List Broken Fixed

= 2.0.5 =
* Picked note, returned note and returned security deposit amount added in order list.
* Rent list item pricing display issue solved.
* Registration form problem solved.

= 2.0.4 =
* Pricing and discount problem solved after pickup or drop off date change.
* Yoast seo plugin not supported, solved it.
* Warning removed.
* Without input inventory customer can booking.

= 2.0.3 =
* Untrash problem solved.
* Single day calendar problem solved.

= 2.0.2 =
* Current day booking option added.

= 2.0.1 =
* Category ID added in category list.
* Count field fixed in category list.
* Shortcode of rent-list category parameter replaced by cat_ids.
* Dashboard categories empty column error fix.
* Additional Gallery added to template section for Muffin template.
* On donut template selection sidebar options visible.
* Custom time slot selection issue fix in date/time section. 

= 2.0.0 =
* Multiple category pricing ontime and daywise feature added
* Stock managemant problem solved
* Seasional price calculation problem solved
* Day wise price calculation problem solved
* Admin dhashboad UX/UI modification


= 1.4.5 =
* Rental search problem soleved.
* Day wise or one time services added.
* Seasonal price calculation problem solved.
* Day wise calculation problem solved.
* extra service stock validation added.


= 1.4.1 =
* Bug fixed about discount

= 1.3.1 =
* cart date time missing fixed for single rent
= 1.3.1 =
* Bug fixed

= 1.2.8 =
* Bug fixed

= 1.2.6 =
* Yoast Plugin Issue fixed.
* Security Issue Fixed
* Bug fixed

= 1.2.5 =
* Security Issue Fixed
* Bug fixed

= 1.2.2 =
* Security Issue Fixed
* Bug fixed

= 1.2.1 =
* Bug fixed

= 1.1.9 =
* Bug fixed

= 1.1.6 =
* WooCommerce integration.
* Fixed bug with flat some theme

= 1.1.5 =
* Donut template added.

= 1.1.4 =
* Bug Fixed.

= 1.1.3 =
* Appointment type added.
* Time slot page added.

= 1.0.4 =
* New rent types added.
* Multiple payment system added.
* Back-end Order list added.
* Front-end user booking dashboard added.

= 1.0.0 =
*Initial Release*
