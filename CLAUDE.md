# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`booking-and-rental-manager-for-woocommerce` is the **free version** of MagePeople's "Booking and Rental Manager for WooCommerce" — a WordPress plugin that turns WooCommerce into a date/time-based rental & booking system (cars, bikes, equipment, dresses, resorts, appointments, etc.). The Pro version (`rent-pro.php` in a sibling plugin) is detected at runtime and toggles upsell UI; this repo never contains Pro code.

There is **no build step, package manager, or test suite** — it is plain PHP/JS/CSS loaded directly by WordPress. "Running" it means installing it into a WordPress + WooCommerce site (WooCommerce active is required; `rbfw_woo_install_check()` gates almost all functionality). Assets ship pre-minified; edit the source `.js`/`.css` directly.

- Code prefix convention: everything is namespaced with `rbfw` / `RBFW_` (Rent & Booking For WooCommerce).
- Text domain: `booking-and-rental-manager-for-woocommerce`.
- Version lives in the header of `rent-manager.php` (currently 2.7.1) and `readme.txt` (`Stable tag`). Requires PHP 7.0+, WP 5.3+.

## Architecture

### Bootstrap order (read these first)
1. **`rent-manager.php`** — `RBFW_Rent_Manager` class is the entry point. Defines constants (`RBFW_PLUGIN_DIR`, `RBFW_PLUGIN_URL`, `RBFW_TEMPLATE_PATH`), registers activation/deactivation hooks, and handles rewrite-rule self-healing for the rental CPT permalinks (see `rbfw_auto_flush_rewrite_rules` — rules are flushed automatically on slug change or when `/rent/...` routes go missing; never tell users to manually re-save permalinks).
2. **`functions.php`** — global helper functions loaded unconditionally. Contains `rbfw_woo_install_check()`, default-page creation (`rbfw_page_create` → "Rent List", "Rent Grid", "Search Item List"), default settings seeding (`rbfw_update_settings`), and WooCommerce cart hooks (e.g. security-deposit fee).
3. **`inc/rbfw_file_include.php`** — the master include manifest. **When adding a new class/feature file, register it here.** It also wires WooCommerce integration on `wp_loaded` via `rbfw_free_woocommerce_integrate()` (loads `Frontend/RBFW_Woocommerse.php` and `inc/woocommerce/*`).

### The global `$rbfw` object
`MageRBFWClass` (in `lib/classes/class-admin-menu.php`, instantiated at the bottom as `$rbfw = new MageRBFWClass()`) is the central singleton, available as `global $rbfw`. It owns: the admin menu, the settings API wiring, and the most-used accessors — `get_slug()`, `get_name()`, `get_icon()`, `get_option()` / `get_option_trans()` (read plugin settings options), `send_email()`, order/status helpers, and `rbfw_add_order_data()`. Code that may run under WP-CLI (where `$rbfw` is not guaranteed) falls back to reading the `rbfw_basic_gen_settings` option directly — preserve that pattern (see `admin/custom_post.php::rbfw_cpt`).

`RBFW_Function` (`inc/RBFW_Function.php`) is a static-method utility class: sanitization (`data_sanitize`), template resolution (`get_template_path` / `get_all_template` — themes can override templates via `<theme>/templates/...`), meta accessors, settings getters, and `rbfw_rent_types()`.

### Custom post type & taxonomies
- CPT **`rbfw_item`** — the rental product. Registered in `admin/custom_post.php` (`RBFW_Custom_Post`). The CPT must register in *every* execution context (web, cron, WP-CLI) or rewrite rules break — do not gate its registration behind admin/web-only checks.
- Taxonomies registered in `admin/taxonomy_register.php`: `rbfw_item_caregory` (category), plus location. (Note the misspelled slug `caregory` is intentional/legacy — do not "fix" it without a migration.)
- Each `rbfw_item` is also backed by a hidden WooCommerce product (`admin/RBFW_Hidden_Product.php`) so it can flow through the standard WooCommerce cart/checkout.

### Rental-item editor (admin)
Two editors coexist:
- **Classic meta-box editor** — built from tab modules in `admin/settings/*.php` (`General_Info`, `Pricing`, `Extra_Service`, `Fee_Management`, `Inventory`, `Off_Day`, `Tax`, `Location`, `Template`, `Gallery`, `Related`, `Settings`, `Faq`, `Terms`, `Security_Deposit`). Each is a class that hooks `rbfw_meta_box_tab_name` (renders the tab label) and `rbfw_meta_box_tab_content` (renders the panel for a `$post_id`), and saves on `save_post`. **To add an editor tab, follow this pattern and require the file from `admin/admin.php`.**
- **Modern full-page editor** — `admin/RBFW_Modern_Editor.php` (`RBFW_Modern_Editor`) intercepts the classic edit/new screens and redirects to a tabbed SPA-style page, saving via AJAX (`rbfw_modern_editor_save`). The `_rbfw_editor_mode` post-meta + "Switch to Classic" link toggle between the two. This is the active area of recent development (`modern-editor` branch).

### Plugin settings (global, not per-item)
Built on the bundled **WeDevs Settings API** (`lib/classes/class.settings-api.php`, `RBFW_Setting_API`). Sections/fields are declared in `MageRBFWClass::get_settings_sections()` / `get_settings_fields()` and rendered by `admin/settings.php`. Settings are stored in WP options like `rbfw_basic_gen_settings`, `rbfw_basic_payment_settings`, `rbfw_basic_pdf_settings`. Read them via `$rbfw->get_option(...)` or `rbfw_get_option(...)`.

### Pricing / availability engine (frontend, AJAX-heavy)
Pricing is computed server-side via `admin-ajax.php`. The two core engines:
- **`inc/class-bike-car-sd-function.php`** (`RBFW_BikeCarSd_Function`) — **single-day** / hourly / time-slot rentals.
- **`inc/class-bike-car-md-function.php`** (`RBFW_BikeCarMd_Function`) — **multi-day** duration rentals and multi-item bookings.

Supporting engines: `inc/class-resort-function.php` / `inc/rbfw_resort_functions.php` (resort/room booking), `inc/rbfw_inventory_functions.php` (stock/availability), `inc/rbfw_fee_functions.php` (fees), `inc/rbfw_dynamic_css.php` (per-site CSS from style settings). Each engine registers paired `wp_ajax_*` / `wp_ajax_nopriv_*` actions (both required so logged-out visitors can price/book) and enqueues its frontend JS in `wp_footer`. Frontend JS lives in `assets/mp_script/` (`rbfw_script.js`, `sd_script.js`, `md_script.js`).

### WooCommerce checkout flow
`Frontend/RBFW_Woocommerse.php` (`RBFW_Woocommerce`) bridges rental selections into WooCommerce: validates add-to-cart, attaches booking data via `rbfw_add_cart_item_data` (`rbfw_ticket_info`), recalculates cart totals (`woocommerce_before_calculate_totals`), renders cart line details, validates at checkout, writes order line-item meta, and triggers post-purchase booking management on the thank-you page. Order/status meta handling is in `inc/woocommerce/class-meta.php`, `inc/woocommerce/class-status.php`, and `inc/rbfw_order_meta.php`.

### Frontend display
- **Shortcodes** (`inc/rbfw_shortcodes.php`): `[rent-list style='list'|'grid']`, `[rbfw_search]` / `[rbfw_search_ac]` / `[search-result]` (search page), `[rent-add-to-cart]`, `[rbfw_left_filter]`.
- **Templates** (`templates/`): list/grid (`rental_lists.php`), single-item (`templates/single/<name>/` — selectable per item via the Template tab; `default` and `muffin` ship in free), AJAX form partials (`templates/ajax_form/`, `templates/forms/`), archive, cart page. Theme overrides resolve through `RBFW_Function::get_template_path()`.
- **Page-builder support** (loaded conditionally): `support/elementor/` (Elementor widget) and `support/blocks/` (Gutenberg block).
- Asset enqueueing for both admin and frontend is centralized in `inc/RBFW_Dependencies.php` (`RBFW_Dependencies`). Note many enqueues use `time()` as the version (cache-busting during dev) — be aware when touching them.

## Conventions & gotchas
- **Sanitize on input, escape on output** (`esc_html__`, `esc_attr`, `wp_kses`); use `RBFW_Function::data_sanitize()` for nested arrays. Serialized meta is unserialized with `allowed_classes => false` to block object injection — see `rbfw_prepare_feature_category_meta_value()` in `functions.php`; mirror that when handling untrusted serialized meta.
- **Nonces**: AJAX/save handlers verify nonces such as `rbfw_ajax_action`. Reuse existing nonce names rather than inventing new ones for the same flow.
- Per-item config is stored as **post meta on the `rbfw_item`**; plugin-wide config is stored as **WP options** (`rbfw_basic_*`). Don't conflate the two.
- The legacy meta key `mep_event_term` and similar `mep_*`/`mep-events` references are inherited from MagePeople's events plugin lineage — they are intentional, not typos.
- Branch `modern-editor` is where the new editor work happens; `main` is the release branch.
