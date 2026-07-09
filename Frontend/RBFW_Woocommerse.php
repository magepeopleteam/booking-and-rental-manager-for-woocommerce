<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
if (!class_exists('RBFW_Woocommerce')) {
    class RBFW_Woocommerce
    {

        public function __construct()
        {
            add_filter( 'woocommerce_add_to_cart_validation', array($this , 'rbfw_block_add_to_cart_when_standalone'), 5, 2 );
            add_filter( 'woocommerce_add_to_cart_validation', array($this , 'rbfw_prevent_duplicate_cart_item'), 10, 2 );
            add_filter( 'woocommerce_add_to_cart_validation', array($this , 'rbfw_validate_availability_add_to_cart'), 20, 3 );
            add_filter( 'woocommerce_add_to_cart_validation', array($this , 'rbfw_validate_location_stock'), 25, 3 );
            add_filter( 'woocommerce_add_cart_item_data',array($this ,  'rbfw_add_info_to_cart_item'), 90, 3 );
            add_action( 'woocommerce_before_calculate_totals', array($this ,  'rbfw_set_new_cart_price'), 90 );
            add_filter( 'woocommerce_get_item_data', array($this ,  'rbfw_show_cart_items') , 90, 2 );
            /*after place order*/
            add_action( 'woocommerce_after_checkout_validation', array($this ,  'rbfw_validation_before_checkout') );
            add_action( 'woocommerce_after_checkout_validation', array($this ,  'rbfw_validate_availability_before_checkout'), 20 );
            add_action( 'woocommerce_checkout_create_order_line_item', array($this ,  'rbfw_add_order_item_data'), 90, 4 );
            /*
             * Build the rbfw_order mirror + inventory + attendee records.
             *
             * These records must be created from server-side order lifecycle events, NOT only
             * from the thank-you page view. Redirect/IPN gateways (Opay, PayPal, Stripe redirect,
             * etc.) complete the order server-side and the customer frequently never lands on the
             * WooCommerce thank-you page, so `woocommerce_before_thankyou` alone silently drops the
             * booking from the Order List / Booking Calendar / Inventory / Attendee list even though
             * the order is paid. rbfw_booking_management() is idempotent (it early-returns when a
             * mirror with the same rbfw_link_order_id already exists), so firing it from several
             * hooks is safe — whichever runs first records the booking and the rest no-op.
             */
            add_action( 'woocommerce_checkout_order_processed', array($this ,  'rbfw_booking_management') );           // classic checkout (all gateways, at placement)
            add_action( 'woocommerce_store_api_checkout_order_processed', array($this ,  'rbfw_booking_management_from_order') ); // block / Store API checkout
            add_action( 'woocommerce_payment_complete', array($this ,  'rbfw_booking_management') );                   // async payment confirmation (redirect/IPN gateways)
            add_action( 'woocommerce_order_status_processing', array($this ,  'rbfw_booking_management') );            // safety net: any path that marks the order paid
            add_action( 'woocommerce_order_status_completed', array($this ,  'rbfw_booking_management') );             // safety net: manual/offline/admin completion
            add_action( 'woocommerce_before_thankyou', array($this ,  'rbfw_booking_management') );                    // legacy path (kept; harmless when already recorded)
            add_action( 'rbfw_wc_order_status_change', array($this ,  'rbfw_change_user_order_status_on_order_status_change'), 10, 3 );
            /* Self-healing: rebuild booking records for paid orders placed before this fix
               (e.g. redirect-gateway orders that never triggered the thank-you page). */
            add_action( 'admin_init', array($this ,  'rbfw_backfill_missing_order_mirrors') );
        }

        /**
         * Refuse to add a rental item to the WooCommerce cart when the plugin is not
         * in WooCommerce mode (WooCommerce active but "Enable WooCommerce Payment" is
         * off, i.e. bookings are handled by the standalone / custom-payment flow).
         *
         * Normally the native-checkout JS intercepts the submit before it ever reaches
         * WooCommerce, so this only fires on the fallback path (JS disabled, or a
         * forged/direct add-to-cart POST). It guarantees WooCommerce never quietly
         * takes ownership of a booking that the standalone flow is supposed to own —
         * the mirror of RBFW_Native_Checkout refusing to run while use_wc() is true.
         */
        public function rbfw_block_add_to_cart_when_standalone( $passed, $product_id ) {
            if ( ! $passed ) {
                return $passed;
            }
            if ( function_exists( 'rbfw_use_wc' ) && rbfw_use_wc() ) {
                return $passed; // WooCommerce mode — nothing to block.
            }

            $linked_rbfw_id = get_post_meta( $product_id, 'link_rbfw_id', true ) ? get_post_meta( $product_id, 'link_rbfw_id', true ) : $product_id;
            $rbfw_id        = rbfw_check_product_exists( $linked_rbfw_id ) ? $linked_rbfw_id : $product_id;

            if ( get_post_type( $rbfw_id ) !== 'rbfw_item' ) {
                return $passed; // not a rental item — leave other products alone.
            }

            wc_add_notice( esc_html__( 'This booking is handled through the site\'s own checkout, not the WooCommerce cart. Please use the "Book Now" button to complete your booking.', 'booking-and-rental-manager-for-woocommerce' ), 'error' );

            return false;
        }

        public function rbfw_prevent_duplicate_cart_item( $passed, $product_id  ) {


            $rbfw_allow_duplicate_rental_cart_item = rbfw_get_option( 'rbfw_allow_duplicate_rental_cart_item', 'rbfw_basic_gen_settings' );

            // If share section is disabled, don't display it
            if ( $rbfw_allow_duplicate_rental_cart_item !== 'yes' ) {
                foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                    $_product = $values['data'];

                    if ( $_product->get_id() == $product_id ) {

                        $cart_url = wc_get_cart_url();

                        if ( wp_get_theme()->get( 'Name' ) === 'Blocksy' ) {

                            ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Create the message HTML as a safe string
                                    const messageText = `<?php
                                    echo esc_html__( 'This product is already in your cart.', 'booking-and-rental-manager-for-woocommerce' );
                                    ?>`;

                                    const messageLink = `<?php
                                    echo sprintf(
                                        '<a href="%s" class="wc-forward">%s</a>',
                                        esc_url( $cart_url ),
                                        esc_html__( 'View Cart', 'booking-and-rental-manager-for-woocommerce' )
                                    );
                                    ?>`;
                                    // Combine if needed
                                    const messageHTML = `${messageText} ${messageLink}`;
                                    // Find the WooCommerce notices wrapper
                                    const wrapper = document.querySelector('.woocommerce-notices-wrapper');

                                    if (messageHTML && wrapper) {
                                        // Create a new div element
                                        const messageDiv = document.createElement('div');
                                        messageDiv.classList.add('woocommerce-message');
                                        messageDiv.innerHTML = messageHTML;

                                        // Append it to the wrapper
                                        wrapper.appendChild(messageDiv);
                                    }
                                });
                            </script>
                            <?php
                        }else{
                            wc_add_notice(
                                sprintf(
                                    __('This product is already in your cart. <a href="%s" class="wc-forward">View Cart</a>', 'booking-and-rental-manager-for-woocommerce'),
                                    esc_url($cart_url)
                                ),
                                'error'
                            );

                        }

                        // For AJAX requests, send the notice immediately
                        if ( wp_doing_ajax() ) {
                            wc_print_notices();
                            wp_die(); // Stop further execution
                        }

                        return false;
                    }
                }

            }
            return $passed;
        }

        /**
         * Block adding a rental to the cart when the chosen dates are no longer available.
         *
         * Reuses rbfw_add_cart_item_func() to parse the submitted booking exactly as the
         * real cart build does, then checks it against existing orders and whatever is
         * already in the cart. Fails open (returns $passed) whenever the request can't be
         * evaluated, so a valid booking is never wrongly blocked.
         *
         * @param bool $passed     Current validation result.
         * @param int  $product_id WooCommerce product id being added.
         * @param int  $quantity   Quantity (unused; rental qty travels in the booking POST).
         * @return bool
         */
        public function rbfw_validate_availability_add_to_cart( $passed, $product_id, $quantity = 1 ) {
            if ( ! $passed ) {
                return $passed; // already rejected by another validator
            }
            global $rbfw;

            $linked_rbfw_id = get_post_meta( $product_id, 'link_rbfw_id', true ) ? get_post_meta( $product_id, 'link_rbfw_id', true ) : $product_id;
            $rbfw_id        = rbfw_check_product_exists( $linked_rbfw_id ) ? $linked_rbfw_id : $product_id;

            if ( get_post_type( $rbfw_id ) !== $rbfw->get_cpt_name() ) {
                return $passed;
            }
            if ( ! function_exists( 'rbfw_check_rental_availability' ) ) {
                return $passed;
            }

            // Parse the submitted booking the same way the cart build does.
            $values = $this->rbfw_add_cart_item_func( array(), $rbfw_id );
            if ( ! is_array( $values ) ) {
                return $passed; // nonce missing / not a booking submit -> fail open
            }

            // Other lines for the same item already in the cart compete for the stock.
            $siblings = array();
            if ( function_exists( 'WC' ) && WC()->cart ) {
                foreach ( WC()->cart->get_cart() as $ci ) {
                    if ( isset( $ci['rbfw_id'] ) && (int) $ci['rbfw_id'] === (int) $rbfw_id ) {
                        $siblings[] = $ci;
                    }
                }
            }

            $checks  = rbfw_check_rental_availability( $rbfw_id, $values, $siblings );
            $blocked = false;
            foreach ( $checks as $check ) {
                if ( empty( $check['ok'] ) ) {
                    $blocked = true;
                    wc_add_notice( $this->rbfw_availability_notice( $check ), 'error' );
                }
            }

            if ( $blocked ) {
                if ( wp_doing_ajax() ) {
                    wc_print_notices();
                    wp_die();
                }
                return false;
            }

            return $passed;
        }

        /**
         * Location-wise stock gate at add-to-cart.
         *
         * Runs only for items with Location Inventory enabled: the submitted
         * pickup location must be one of the configured locations, and the
         * requested quantity may not exceed that location's remaining stock
         * for the booked dates (rbfw_location_remaining_stock). Global stock
         * is still enforced by the existing availability validators — this is
         * an additional, per-location cap.
         */
        public function rbfw_validate_location_stock( $passed, $product_id, $quantity = 1 ) {
            if ( ! $passed || ! function_exists( 'rbfw_get_location_inventory' ) ) {
                return $passed;
            }

            $linked_rbfw_id = get_post_meta( $product_id, 'link_rbfw_id', true ) ? get_post_meta( $product_id, 'link_rbfw_id', true ) : $product_id;
            $rbfw_id        = rbfw_check_product_exists( $linked_rbfw_id ) ? $linked_rbfw_id : $product_id;

            $conf = rbfw_get_location_inventory( $rbfw_id );
            if ( empty( $conf ) ) {
                return $passed;
            }

            // phpcs:disable WordPress.Security.NonceVerification.Missing -- read-only look at the booking submit; nonce is enforced by the cart build.
            $pickup_point = isset( $_POST['rbfw_pickup_point'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_pickup_point'] ) ) : '';
            // Booked dates: md/multi-items post rbfw_pickup_(start|end)_date,
            // resort posts rbfw_(start|end)_datetime, single-day posts
            // rbfw_bikecarsd_selected_date.
            $start_date   = isset( $_POST['rbfw_pickup_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_pickup_start_date'] ) ) : '';
            if ( '' === $start_date && isset( $_POST['rbfw_start_datetime'] ) ) {
                $start_date = sanitize_text_field( wp_unslash( $_POST['rbfw_start_datetime'] ) );
            }
            if ( '' === $start_date && isset( $_POST['rbfw_bikecarsd_selected_date'] ) ) {
                $start_date = sanitize_text_field( wp_unslash( $_POST['rbfw_bikecarsd_selected_date'] ) );
            }
            $end_date = isset( $_POST['rbfw_pickup_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_pickup_end_date'] ) ) : '';
            if ( '' === $end_date && isset( $_POST['rbfw_end_datetime'] ) ) {
                $end_date = sanitize_text_field( wp_unslash( $_POST['rbfw_end_datetime'] ) );
            }
            if ( '' === $end_date ) {
                $end_date = $start_date;
            }
            /* Duration-based forms (multiple_items) post no end date — derive
               it from durationType/durationQty, mirroring the cart build. */
            if ( $end_date === $start_date && isset( $_POST['durationType'] ) && '' !== $start_date ) {
                $d_type = sanitize_text_field( wp_unslash( $_POST['durationType'] ) );
                $d_qty  = isset( $_POST['durationQty'] ) ? max( 1, absint( wp_unslash( $_POST['durationQty'] ) ) ) : 1;
                $d_days = 'hourly' === $d_type ? 0 : ( 'daily' === $d_type ? $d_qty : ( 'weekly' === $d_type ? $d_qty * 7 : $d_qty * 30 ) );
                $d_ts   = strtotime( $start_date );
                if ( $d_ts && $d_days > 0 ) {
                    $end_date = gmdate( 'Y-m-d', $d_ts + $d_days * DAY_IN_SECONDS );
                }
            }
            $qty = isset( $_POST['rbfw_item_quantity'] ) ? max( 1, absint( wp_unslash( $_POST['rbfw_item_quantity'] ) ) ) : 1;
            // phpcs:enable WordPress.Security.NonceVerification.Missing

            if ( '' === $pickup_point || ! isset( $conf[ $pickup_point ] ) ) {
                wc_add_notice( __( 'Please choose a location before booking.', 'booking-and-rental-manager-for-woocommerce' ), 'error' );
                return false;
            }

            $remaining = rbfw_location_remaining_stock( $rbfw_id, $pickup_point, $start_date, $end_date );
            if ( null !== $remaining && $qty > $remaining ) {
                $term = get_term_by( 'slug', $pickup_point, 'rbfw_item_location' );
                $name = $term && ! is_wp_error( $term ) ? $term->name : ucwords( str_replace( '-', ' ', $pickup_point ) );
                wc_add_notice(
                    sprintf(
                        /* translators: 1: location name, 2: remaining units */
                        __( 'Only %2$d unit(s) are available at %1$s for the selected dates.', 'booking-and-rental-manager-for-woocommerce' ),
                        $name,
                        $remaining
                    ),
                    'error'
                );
                return false;
            }

            return $passed;
        }

        /**
         * Final server-side availability gate at checkout.
         *
         * Walks the cart once, validating each rental line against existing orders and the
         * lines processed before it (so several lines can't oversubscribe the same finite
         * stock). Adding an error notice here halts checkout — closing the race / stale-cart
         * / bypassed-JS gap that previously allowed double-booking.
         *
         * @param array $data Posted checkout data (unused).
         * @return void
         */
        public function rbfw_validate_availability_before_checkout( $data = null ) {
            global $rbfw;
            if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
                return;
            }
            if ( ! function_exists( 'rbfw_check_rental_availability' ) ) {
                return;
            }

            $seen = array(); // rbfw_id => array of already-validated line values (siblings)

            foreach ( WC()->cart->get_cart() as $values ) {
                $rbfw_id = isset( $values['rbfw_id'] ) ? $values['rbfw_id'] : 0;
                if ( ! $rbfw_id || get_post_type( $rbfw_id ) !== $rbfw->get_cpt_name() ) {
                    continue;
                }

                $siblings = isset( $seen[ $rbfw_id ] ) ? $seen[ $rbfw_id ] : array();
                $checks   = rbfw_check_rental_availability( $rbfw_id, $values, $siblings );
                foreach ( $checks as $check ) {
                    if ( empty( $check['ok'] ) ) {
                        wc_add_notice( $this->rbfw_availability_notice( $check ), 'error' );
                    }
                }

                $seen[ $rbfw_id ][] = $values;
            }
        }

        /**
         * Build a customer-facing "no longer available" notice for a failed availability check.
         *
         * @param array $check One entry from rbfw_check_rental_availability().
         * @return string
         */
        private function rbfw_availability_notice( $check ) {
            $label     = isset( $check['label'] ) ? $check['label'] : __( 'This rental', 'booking-and-rental-manager-for-woocommerce' );
            $available = isset( $check['available'] ) ? (int) $check['available'] : 0;

            if ( $available > 0 ) {
                return sprintf(
                    /* translators: 1: item name, 2: available quantity */
                    esc_html__( 'Sorry, "%1$s" is no longer available for the selected dates. Only %2$d left for those dates — please reduce the quantity or choose different dates.', 'booking-and-rental-manager-for-woocommerce' ),
                    esc_html( $label ),
                    $available
                );
            }

            return sprintf(
                /* translators: %s: item name */
                esc_html__( 'Sorry, "%s" is already booked for the selected dates. Please choose different dates.', 'booking-and-rental-manager-for-woocommerce' ),
                esc_html( $label )
            );
        }

        public function rbfw_add_info_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
            global $rbfw;
            $linked_rbfw_id = get_post_meta( $product_id, 'link_rbfw_id', true ) ? get_post_meta( $product_id, 'link_rbfw_id', true ) : $product_id;
            $product_id     = rbfw_check_product_exists( $linked_rbfw_id ) ? $linked_rbfw_id : $product_id;
            if ( get_post_type( $product_id ) == $rbfw->get_cpt_name() ) {
                $cart_item_data = $this->rbfw_add_cart_item_func( $cart_item_data, $product_id );
            }
            $cart_item_data['rbfw_id'] = $product_id;

            return $cart_item_data;
        }

        private function rbfw_get_multi_items_billing( $rbfw_id, $duration_type, $duration_qty ) {
            $duration_qty = max( 1, absint( $duration_qty ) );
            $pricing_types = get_post_meta( $rbfw_id, 'pricing_types', true );
            $pricing_types = is_array( $pricing_types ) ? $pricing_types : array();
            $allowed_types = array( 'hourly', 'daily', 'weekly', 'monthly' );

            if ( ! in_array( $duration_type, $allowed_types, true ) || ( isset( $pricing_types[ $duration_type ] ) && 'on' !== $pricing_types[ $duration_type ] ) ) {
                $duration_type = 'daily';

                foreach ( $allowed_types as $allowed_type ) {
                    if ( isset( $pricing_types[ $allowed_type ] ) && 'on' === $pricing_types[ $allowed_type ] ) {
                        $duration_type = $allowed_type;
                        break;
                    }
                }
            }

            $billing      = array(
                'price_type' => $duration_type,
                'multiplier' => $duration_qty,
            );

            $daily_to_weekly   = (float) get_post_meta( $rbfw_id, 'rbfw_mi_daily_to_weekly_pivot', true );
            $weekly_to_monthly = (float) get_post_meta( $rbfw_id, 'rbfw_mi_weekly_to_monthly_pivot', true );
            $hourly_to_day     = (float) get_post_meta( $rbfw_id, 'rbfw_mi_hourly_to_half_day_pivot', true );

            if ( 'weekly' === $duration_type && $weekly_to_monthly > 0 && $duration_qty >= $weekly_to_monthly ) {
                $billing['price_type'] = 'monthly';
                $billing['multiplier'] = max( 1, (int) ceil( $duration_qty / 4 ) );
            } elseif ( 'daily' === $duration_type && $daily_to_weekly > 0 && $duration_qty >= $daily_to_weekly ) {
                $billing['price_type'] = 'weekly';
                $billing['multiplier'] = max( 1, (int) ceil( $duration_qty / 7 ) );
            } elseif ( 'hourly' === $duration_type && $hourly_to_day > 0 && $duration_qty >= $hourly_to_day ) {
                $billing['price_type'] = 'daily';
                $billing['multiplier'] = max( 1, (int) ceil( $duration_qty / 24 ) );
            }

            return $billing;
        }

        private function rbfw_prepare_multi_items_from_post( $rbfw_id, $submitted_items, $duration_type, $duration_qty ) {
            $stored_items = get_post_meta( $rbfw_id, 'multiple_items_info', true );
            $stored_items = is_array( $stored_items ) ? $stored_items : array();
            $billing      = $this->rbfw_get_multi_items_billing( $rbfw_id, $duration_type, $duration_qty );
            $price_key    = $billing['price_type'] . '_price';
            $items        = array();
            $total        = 0;

            foreach ( (array) $submitted_items as $key => $submitted_item ) {
                $item_key = absint( $key );
                if ( ! isset( $stored_items[ $item_key ] ) || ! is_array( $stored_items[ $item_key ] ) ) {
                    continue;
                }

                $quantity = isset( $submitted_item['item_qty'] ) ? absint( $submitted_item['item_qty'] ) : 0;
                if ( $quantity <= 0 ) {
                    continue;
                }

                $stored_item = $stored_items[ $item_key ];
                $available   = isset( $stored_item['available_qty'] ) ? absint( $stored_item['available_qty'] ) : 0;
                if ( $available > 0 ) {
                    $quantity = min( $quantity, $available );
                }

                $unit_price = isset( $stored_item[ $price_key ] ) ? (float) $stored_item[ $price_key ] : 0;
                $total     += $unit_price * $quantity * $billing['multiplier'];
                $items[]    = array(
                    'item_name'  => isset( $stored_item['item_name'] ) ? sanitize_text_field( $stored_item['item_name'] ) : '',
                    'item_qty'   => $quantity,
                    'item_price' => $unit_price,
                );
            }

            return array(
                'items' => $items,
                'total' => $total,
            );
        }

        private function rbfw_prepare_multi_item_addons_from_post( $rbfw_id, $submitted_categories, $total_days ) {
            static $addon_cache = array();
            if ( ! isset( $addon_cache[ $rbfw_id ] ) ) {
                $raw = get_post_meta( $rbfw_id, 'rbfw_enable_category_service_price', true );
                $enable_service_price = $raw ?: 'off';
                $cats_raw = get_post_meta( $rbfw_id, 'rbfw_service_category_price', true );
                if ( ! is_array( $cats_raw ) ) {
                    $cats_raw = json_decode( $cats_raw, true );
                }
                $addon_cache[ $rbfw_id ] = array(
                    'enabled'    => $enable_service_price,
                    'categories' => is_array( $cats_raw ) ? $cats_raw : array(),
                );
            }
            $enable_service_price = $addon_cache[ $rbfw_id ]['enabled'];
            if ( 'on' !== $enable_service_price ) {
                return array( 'items' => array(), 'total' => 0 );
            }
            $stored_categories = $addon_cache[ $rbfw_id ]['categories'];
            $total_days        = max( 1, absint( $total_days ) );
            $prepared          = array();
            $total             = 0;

            foreach ( (array) $submitted_categories as $cat_key => $submitted_category ) {
                if ( ! isset( $stored_categories[ $cat_key ]['cat_services'] ) || ! is_array( $submitted_category ) ) {
                    continue;
                }

                $new_category = array( 'cat_title' => isset( $stored_categories[ $cat_key ]['cat_title'] ) ? sanitize_text_field( $stored_categories[ $cat_key ]['cat_title'] ) : '' );

                foreach ( $submitted_category as $service_key => $submitted_service ) {
                    if ( 'cat_title' === $service_key || ! is_array( $submitted_service ) || ! isset( $stored_categories[ $cat_key ]['cat_services'][ $service_key ] ) ) {
                        continue;
                    }

                    $quantity = isset( $submitted_service['quantity'] ) ? absint( $submitted_service['quantity'] ) : 0;
                    if ( $quantity <= 0 ) {
                        continue;
                    }

                    $stored_service     = $stored_categories[ $cat_key ]['cat_services'][ $service_key ];
                    $price              = isset( $stored_service['price'] ) ? (float) $stored_service['price'] : 0;
                    $service_price_type = isset( $stored_service['service_price_type'] ) ? sanitize_text_field( $stored_service['service_price_type'] ) : '';
                    $total             += ( 'day_wise' === $service_price_type ) ? $price * $quantity * $total_days : $price * $quantity;
                    $new_category[]     = array(
                        'name'               => isset( $stored_service['title'] ) ? sanitize_text_field( $stored_service['title'] ) : '',
                        'service_price_type' => $service_price_type,
                        'price'              => $price,
                        'quantity'           => $quantity,
                    );
                }

                if ( count( $new_category ) > 1 ) {
                    $prepared[] = $new_category;
                }
            }

            return array(
                'items' => $prepared,
                'total' => $total,
            );
        }

        private function rbfw_prepare_multi_item_fees_from_post( $rbfw_id, $submitted_fees, $sub_total_price ) {
            $stored_fees = get_post_meta( $rbfw_id, 'rbfw_fee_data', true );
            $stored_fees = is_array( $stored_fees ) ? $stored_fees : array();
            $fee_info    = array();
            $fee_total   = 0;

            foreach ( $stored_fees as $key => $fee ) {
                if ( empty( $fee['label'] ) ) {
                    continue;
                }

                $label       = sanitize_text_field( $fee['label'] );
                $is_required = isset( $fee['priority'] ) && 'required' === $fee['priority'];
                $is_checked  = $is_required
                    || ( isset( $submitted_fees[ $key ]['is_checked'] ) && 'yes' === $submitted_fees[ $key ]['is_checked'] )
                    || ( isset( $submitted_fees[ $key ]['label'], $submitted_fees[ $key ]['is_checked'] ) && $label === $submitted_fees[ $key ]['label'] && 'yes' === $submitted_fees[ $key ]['is_checked'] );
                if ( ! $is_checked ) {
                    continue;
                }

                $amount      = isset( $fee['amount'] ) ? (float) $fee['amount'] : 0;
                $price_type  = isset( $fee['calculation_type'] ) ? sanitize_text_field( $fee['calculation_type'] ) : '';
                $refundable  = ! empty( $fee['refundable'] ) ? sanitize_text_field( $fee['refundable'] ) : 'no';
                $fee_price   = ( 'percentage' === $price_type ) ? ( $amount / 100 ) * $sub_total_price : $amount;
                $fee_total  += $fee_price;
                $fee_info[ $label ] = array(
                    'price'      => $fee_price,
                    'price_desc' => ( 'percentage' === $price_type ) ? $amount . '% of ' . wc_price( $sub_total_price ) : wc_price( $amount ),
                    'refundable' => $refundable,
                );
            }

            return array(
                'items' => $fee_info,
                'total' => $fee_total,
            );
        }

        public function rbfw_add_cart_item_func( $cart_item_data, $rbfw_id ) {

            if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rbfw_ajax_action' ) ) ) {
                return;
            }

            $sd_input_data_sabitized = RBFW_Function::data_sanitize( $_POST );
            $rbfw_rent_type     = get_post_meta( $rbfw_id, 'rbfw_item_type', true );

            $_raw = get_post_meta( $rbfw_id, 'rbfw_enable_extra_service_qty', true );
            $rbfw_enable_extra_service_qty = $_raw ?: 'no';


            $rbfw_item_quantity = isset( $sd_input_data_sabitized['rbfw_item_quantity'] ) ? intval( $sd_input_data_sabitized['rbfw_item_quantity'] ) : 1;
            $rbfw_service_info_all = (isset( $sd_input_data_sabitized['rbfw_service_info'] ) && is_array( $sd_input_data_sabitized['rbfw_service_info'] ) ) ? $sd_input_data_sabitized['rbfw_service_info'] : [];

            $rbfw_service_info             = array();
            if ( ! empty( $rbfw_service_info_all ) ) {
                foreach ( $rbfw_service_info_all as $key => $value ) {
                    $service_name = ! empty( $value['service_name'] ) ? $value['service_name'] : '';
                    $service_qty  = ! empty( $value['service_qty'] ) ? $value['service_qty'] : 0;
                    if ( $service_qty > 0 ) {
                        $rbfw_service_info[ $service_name ] = $service_qty;
                    }
                }
            }


            $rbfw_management_info_all = (isset( $sd_input_data_sabitized['rbfw_management_info'] ) && is_array( $sd_input_data_sabitized['rbfw_management_info'] ) ) ? $sd_input_data_sabitized['rbfw_management_info'] : [];

            $rbfw_management_info             = array();
            if ( ! empty( $rbfw_management_info_all ) ) {
                foreach ( $rbfw_management_info_all as $key => $value ) {
                    $management_label = ! empty( $value['label'] ) ? $value['label'] : '';
                    $is_checked  = ! empty( $value['is_checked'] ) ? $value['is_checked'] : 0;
                    if ( $is_checked == 'yes' ) {
                        $rbfw_management_info[ $management_label ] = $is_checked;
                    }
                }
            }



            $discount_type   = '';
            $discount_amount = 0;
            $rbfw_regf_info  = [];
            if ( class_exists( 'Rbfw_Reg_Form' ) ) {
                $ClassRegForm   = new Rbfw_Reg_Form();
                $rbfw_regf_info = $ClassRegForm->rbfw_regf_value_array_function( $rbfw_id );
            }
            $cart_item_data['rbfw_id'] = $rbfw_id;

            if ( $rbfw_rent_type == 'resort' ) {
                global $rbfw;
                $rbfw_resort              = new RBFW_Resort_Function();
                $rbfw_checkin_datetime    = isset( $sd_input_data_sabitized['rbfw_start_datetime'] ) ? wp_strip_all_tags( $sd_input_data_sabitized['rbfw_start_datetime'] ) : '';
                $rbfw_checkout_datetime   = isset( $sd_input_data_sabitized['rbfw_end_datetime'] ) ? wp_strip_all_tags( $sd_input_data_sabitized['rbfw_end_datetime'] ) : '';
                $rbfw_room_price_category = isset( $sd_input_data_sabitized['rbfw_room_price_category'] ) ? $sd_input_data_sabitized['rbfw_room_price_category'] : '';
                $rbfw_room_info_all = isset( $sd_input_data_sabitized['rbfw_room_info'] ) ? $sd_input_data_sabitized['rbfw_room_info'] : [];
                $rbfw_room_info = array();
                $rbfw_room_price = array();
                $i              = 0;

                foreach ( $rbfw_room_info_all as $key => $value ) {
                    if( isset($sd_input_data_sabitized['rbfw_room_info'][ $i ]['room_qty']) && isset($sd_input_data_sabitized['rbfw_room_info'][ $i ]['room_price']) && isset($sd_input_data_sabitized['rbfw_room_info'][ $i ]['room_type']) ) {
                        $room_type = $sd_input_data_sabitized['rbfw_room_info'][$i]['room_type'];
                        $room_qty = $sd_input_data_sabitized['rbfw_room_info'][$i]['room_qty'];
                        $room_price = $sd_input_data_sabitized['rbfw_room_info'][$i]['room_price'];
                        if (!empty($room_qty)) {
                            $rbfw_room_info[$room_type] = $room_qty;
                            $rbfw_room_price[$room_type] = $room_price;
                        }
                        $i++;
                    }
                }


                $rbfw_room_duration_price = $this->rbfw_resort_price_calculation( $rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info,$rbfw_management_info, 'rbfw_room_duration_price' );
                $rbfw_room_service_price  = $this->rbfw_resort_price_calculation( $rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info,$rbfw_management_info, 'rbfw_room_service_price' );
                $rbfw_management_price  = $this->rbfw_resort_price_calculation( $rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info,$rbfw_management_info, 'rbfw_room_management_price' );


                $sub_total_price = $rbfw_room_duration_price + $rbfw_room_service_price + $rbfw_management_price;

                $origin     = $rbfw_checkin_datetime ? date_create( $rbfw_checkin_datetime ) : false;
                $target     = $rbfw_checkout_datetime ? date_create( $rbfw_checkout_datetime ) : false;
                $interval   = ( $origin && $target ) ? date_diff( $origin, $target ) : false;
                $total_days = $interval ? $interval->format( '%a' ) : 0;

                // Check if extra day should be counted
                $count_extra_day = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
                if ($count_extra_day === 'on') {
                    $total_days++;
                }




                if ( function_exists( 'rbfw_get_discount_array' ) ) {
                    $discount_arr = rbfw_get_discount_array( $rbfw_id, $total_days, $sub_total_price, $rbfw_item_quantity );
                } else {
                    $discount_arr = [];
                }
                $discounted_total = $sub_total_price;
                $discount_type   = '';
                $discount_amount = 0;
                if ( ! empty( $discount_arr ) ) {
                    $discounted_total = $discount_arr['total_amount'];
                    $discount_type    = $discount_arr['discount_type'];
                    $discount_amount  = $discount_arr['discount_amount'];
                }
                $rbfw_resort_ticket_info = $rbfw_resort->rbfw_resort_ticket_info( $rbfw_id, $rbfw_checkin_datetime, $rbfw_checkout_datetime, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info, $rbfw_regf_info, $rbfw_room_price , $rbfw_management_info  );

                $security_deposit                           = rbfw_security_deposit( $rbfw_id, $sub_total_price );
                $total_price                                = $discounted_total + $security_deposit['security_deposit_amount'];
                $start_date                                 = $rbfw_checkin_datetime;
                $end_date                                   = $rbfw_checkout_datetime;
                $cart_item_data['rbfw_start_datetime']      = $rbfw_checkin_datetime;
                $cart_item_data['rbfw_end_datetime']        = $rbfw_checkout_datetime;
                $cart_item_data['rbfw_start_date']          = $rbfw_checkin_datetime;
                $cart_item_data['rbfw_start_time']          = '';
                $cart_item_data['rbfw_end_date']            = $rbfw_checkout_datetime;
                $cart_item_data['rbfw_end_time']            = '';
                $cart_item_data['rbfw_room_price_category'] = $rbfw_room_price_category;
                $cart_item_data['rbfw_room_info']           = $rbfw_room_info;
                $cart_item_data['rbfw_type_info']           = $rbfw_room_info;
                $cart_item_data['rbfw_room_price']           = $rbfw_room_price;
                $cart_item_data['rbfw_service_info']        = $rbfw_service_info;
                $cart_item_data['rbfw_room_duration_price'] = $rbfw_room_duration_price;
                $cart_item_data['rbfw_room_service_price']  = $rbfw_room_service_price;
                $cart_item_data['rbfw_ticket_info']         = $rbfw_resort_ticket_info;
                $cart_item_data['rbfw_management_info']     = $rbfw_management_info;
                $cart_item_data['discount_type']            = $discount_type;
                $cart_item_data['discount_amount']          = $discount_amount;
                $cart_item_data['security_deposit_amount']  = $security_deposit['security_deposit_amount'];
                $cart_item_data['security_deposit_desc']    = $security_deposit['security_deposit_desc'];

            } elseif ( $rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment' ) {

                $rbfw_bikecarsd               = new RBFW_BikeCarSd_Function();
                $rbfw_bikecarsd_selected_date = isset( $sd_input_data_sabitized['rbfw_bikecarsd_selected_date'] ) ? $sd_input_data_sabitized['rbfw_bikecarsd_selected_date'] : '';
                $bikecarsd_selected_date      = isset( $sd_input_data_sabitized['rbfw_bikecarsd_selected_date'] ) ? $sd_input_data_sabitized['rbfw_bikecarsd_selected_date'] : '';
                $rbfw_bikecarsd_selected_time = isset( $sd_input_data_sabitized['rbfw_start_time'] ) ? $sd_input_data_sabitized['rbfw_start_time'] : '';
                $end_date = isset( $_POST['rbfw_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_end_date'] ) ) : '';
                $end_time = isset( $_POST['rbfw_end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['rbfw_end_time'] ) ) : '';
                if ( ! ( $end_date && $end_time ) ) {
                    $end_date = $bikecarsd_selected_date;
                }
                $rbfw_start_datetime = $rbfw_bikecarsd_selected_date;
                $rbfw_type_info_all = isset( $sd_input_data_sabitized['rbfw_bikecarsd_info'] ) ? $sd_input_data_sabitized['rbfw_bikecarsd_info'] : [];
                $rbfw_type_info = array();
                if ( isset( $sd_input_data_sabitized['service_type'] ) ) {
                    $rbfw_type_info[ $sd_input_data_sabitized['service_type'] ] = $rbfw_item_quantity;
                } else {
                    $a = 1;
                    foreach ( $rbfw_type_info_all as $key => $value ) {
                        if ( ! empty( $rbfw_type_info_all[ $a ]['rent_type'] ) ) {
                            $rent_type = $rbfw_type_info_all[ $a ]['rent_type'];
                            $rent_qty  = $rbfw_type_info_all[ $a ]['qty'];
                            if ( ! empty( $rent_qty ) && $rent_qty > 0 ) {
                                $rbfw_type_info[ $rent_type ] = $rent_qty;
                            }
                        }
                        $a ++;
                    }
                }

                if ( ! ( $end_date && $end_time ) && ! empty( $rbfw_type_info ) ) {
                    $rbfw_bike_car_sd_data = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) : array();
                    $selected_rent_type    = '';
                    foreach ( $rbfw_type_info as $type_name => $type_qty ) {
                        $selected_rent_type = $type_name;
                        break;
                    }
                    foreach ( $rbfw_bike_car_sd_data as $rent_row ) {
                        if ( ! empty( $rent_row['rent_type'] ) && $rent_row['rent_type'] === $selected_rent_type ) {
                            $duration   = ! empty( $rent_row['duration'] ) ? (int) $rent_row['duration'] : 0;
                            $d_type     = ! empty( $rent_row['d_type'] ) ? $rent_row['d_type'] : 'Days';
                            $start_time = $rbfw_bikecarsd_selected_time ? $rbfw_bikecarsd_selected_time : '00:00';
                            if ( $duration > 0 ) {
                                try {
                                    $start_date_time = new DateTime( $bikecarsd_selected_date . ' ' . $start_time );
                                } catch ( Exception $e ) {
                                    $start_date_time = new DateTime();
                                }
                                $total_hours     = ( $d_type == 'Hours' ? $duration : ( $d_type == 'Days' ? $duration * 24 : ( $d_type == 'Weeks' ? $duration * 24 * 7 : $duration * 24 * 30 ) ) );
                                $start_date_time->modify( "+$total_hours hours" );
                                $end_date = $start_date_time->format( 'Y-m-d' );
                                $end_time = $start_date_time->format( 'H:i:s' );
                            }
                            break;
                        }
                    }
                }
                if ( ! ( $end_date && $end_time ) ) {
                    $end_date = $bikecarsd_selected_date;
                }

                $rbfw_bikecarsd_duration_price                   = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation( $rbfw_id, $rbfw_type_info, $rbfw_service_info, 'rbfw_bikecarsd_duration_price' , $bikecarsd_selected_date);
                $rbfw_bikecarsd_service_price                    = $rbfw_bikecarsd->rbfw_bikecarsd_price_calculation( $rbfw_id, $rbfw_type_info, $rbfw_service_info, 'rbfw_bikecarsd_service_price' );



                $sub_total_price = $rbfw_bikecarsd_duration_price + $rbfw_bikecarsd_service_price;



                $rbfw_management_info_all = (isset( $sd_input_data_sabitized['rbfw_management_info'] ) && is_array( $sd_input_data_sabitized['rbfw_management_info'] ) ) ? $sd_input_data_sabitized['rbfw_management_info'] : [];

                $rbfw_management_price = 0;
                $rbfw_management_info             = array();
                $selected_management_fees = array();
                if ( ! empty( $rbfw_management_info_all ) ) {
                    foreach ( $rbfw_management_info_all as $key => $value ) {
                        $service_label = ! empty( $value['label'] ) ? $value['label'] : '';
                        $is_checked  = ! empty( $value['is_checked'] ) ? $value['is_checked'] : 0;
                        if ( $service_label && $is_checked == 'yes' ) {
                            $selected_management_fees[ $service_label ] = true;
                        }
                    }
                }

                $rbfw_fee_data = get_post_meta( $rbfw_id, 'rbfw_fee_data', true );
                $rbfw_fee_data = is_array( $rbfw_fee_data ) ? $rbfw_fee_data : array();
                foreach ( $rbfw_fee_data as $fee ) {
                    $service_label = ! empty( $fee['label'] ) ? $fee['label'] : '';
                    $priority = ! empty( $fee['priority'] ) ? $fee['priority'] : 'optional';
                    if ( $service_label && ( isset( $selected_management_fees[ $service_label ] ) || $priority == 'required' ) ) {
                        $price = ! empty( $fee['amount'] ) ? (float) $fee['amount'] : 0;
                        $price_type = ! empty( $fee['calculation_type'] ) ? $fee['calculation_type'] : 'fixed';
                        $refundable = ! empty( $fee['refundable'] ) ? $fee['refundable'] : 'no';
                        if ( $price_type === 'percentage' ) {
                            $fee_total = ( $price / 100 ) * $sub_total_price;
                            $rbfw_management_price += $fee_total;
                            $rbfw_management_info[ $service_label ] = array( 'price' => $fee_total, 'price_desc' => wc_price( $fee_total ), 'refundable' => $refundable );
                        } else {
                            $rbfw_management_price += $price;
                            $rbfw_management_info[ $service_label ] = array( 'price' => $price, 'price_desc' => wc_price( $price ), 'refundable' => $refundable );
                        }
                    }
                }




                $rbfw_pickup_point                               = isset( $sd_input_data_sabitized['rbfw_pickup_point'] ) ? $sd_input_data_sabitized['rbfw_pickup_point'] : '';
                $rbfw_dropoff_point                              = isset( $sd_input_data_sabitized['rbfw_dropoff_point'] ) ? $sd_input_data_sabitized['rbfw_dropoff_point'] : '';
                list( $rbfw_management_info, $rbfw_management_price ) = rbfw_apply_location_charge( $rbfw_id, $rbfw_pickup_point, $rbfw_management_info, $rbfw_management_price );

                /* Item Variations (Single Day): per-value quantity steppers submit
                   rbfw_variation_qty[field_id][value_name] = qty. Build one cart entry
                   per chosen size (qty > 0) carrying its per-unit surcharge price -- the
                   same flat shape rbfw_room_info uses -- so per-size stock can be enforced
                   by rbfw_check_rental_availability() and each size shows in cart/order.
                   Mirrors the multi-day capture block below in this same function. */
                $variation_data           = get_post_meta( $rbfw_id, 'rbfw_variations_data', true );
                $variation_info           = [];
                $rbfw_variation_surcharge = 0.0;
                $rbfw_variation_qty_input = ( isset( $sd_input_data_sabitized['rbfw_variation_qty'] ) && is_array( $sd_input_data_sabitized['rbfw_variation_qty'] ) ) ? $sd_input_data_sabitized['rbfw_variation_qty'] : [];
                if ( ! empty( $variation_data ) && is_array( $variation_data ) ) {
                    $i = 0;
                    foreach ( $variation_data as $level_one_arr ) {
                        // Skip incomplete/legacy variation rows (missing id or values).
                        if ( ! is_array( $level_one_arr ) || empty( $level_one_arr['field_id'] ) || empty( $level_one_arr['value'] ) || ! is_array( $level_one_arr['value'] ) ) {
                            continue;
                        }
                        $field_id    = $level_one_arr['field_id'];
                        $field_label = isset( $level_one_arr['field_label'] ) ? $level_one_arr['field_label'] : '';
                        $qty_map     = ( isset( $rbfw_variation_qty_input[ $field_id ] ) && is_array( $rbfw_variation_qty_input[ $field_id ] ) ) ? $rbfw_variation_qty_input[ $field_id ] : [];
                        foreach ( $level_one_arr['value'] as $level_two_arr_value ) {
                            $level_two_name = isset( $level_two_arr_value['name'] ) ? $level_two_arr_value['name'] : '';
                            if ( '' === $level_two_name ) {
                                continue;
                            }
                            $chosen_qty = isset( $qty_map[ $level_two_name ] ) ? max( 0, (int) $qty_map[ $level_two_name ] ) : 0;
                            if ( $chosen_qty <= 0 ) {
                                continue;
                            }
                            $unit_price                = rbfw_get_variation_price_for_value( $rbfw_id, $level_two_name );
                            $variation_info[ $i ]      = array(
                                'field_id'    => $field_id,
                                'field_label' => $field_label,
                                'field_value' => $level_two_name,
                                'qty'         => $chosen_qty,
                                'price'       => $unit_price,
                            );
                            $rbfw_variation_surcharge += $unit_price * $chosen_qty;
                            $i ++;
                        }
                    }
                }
                // Per-unit variant surcharge adds on top of the quantity-scaled duration price.
                $sub_total_price += $rbfw_variation_surcharge;

                $rbfw_bikecarsd_ticket_info                      = $rbfw_bikecarsd->rbfw_bikecarsd_ticket_info( $rbfw_id, $rbfw_start_datetime, $end_date, $rbfw_type_info, $rbfw_service_info, $rbfw_bikecarsd_selected_time, $rbfw_regf_info, $rbfw_pickup_point, $rbfw_dropoff_point, $end_time, $rbfw_item_quantity , $bikecarsd_selected_date , $rbfw_management_info , $rbfw_management_price, $variation_info);

                $sub_total_price                                 = apply_filters( 'rbfw_cart_base_price', $sub_total_price );
                $security_deposit                                = rbfw_security_deposit( $rbfw_id, $sub_total_price );
                $total_price                                     = $sub_total_price + $rbfw_management_price;

                $cart_item_data['rbfw_item_quantity']            = $rbfw_item_quantity;
                $cart_item_data['rbfw_pickup_point']             = $rbfw_pickup_point;
                $cart_item_data['rbfw_dropoff_point']            = $rbfw_dropoff_point;
                $cart_item_data['rbfw_start_datetime']           = $rbfw_start_datetime;
                $cart_item_data['rbfw_end_datetime']             = $end_date . ' ' . $end_time;
                $cart_item_data['rbfw_start_date']               = $bikecarsd_selected_date;
                $cart_item_data['rbfw_start_time']               = $rbfw_bikecarsd_selected_time;
                $cart_item_data['rbfw_end_date']                 = $end_date;
                $cart_item_data['rbfw_end_time']                 = $end_time;
                $cart_item_data['rbfw_type_info']                = $rbfw_type_info;
                $cart_item_data['rbfw_variation_info']           = $variation_info;
                $cart_item_data['rbfw_variation_surcharge']      = $rbfw_variation_surcharge;
                $cart_item_data['rbfw_service_info']             = $rbfw_service_info;
                $cart_item_data['rbfw_bikecarsd_duration_price'] = $rbfw_bikecarsd_duration_price;
                $cart_item_data['rbfw_bikecarsd_service_price']  = $rbfw_bikecarsd_service_price;
                $cart_item_data['rbfw_ticket_info']              = $rbfw_bikecarsd_ticket_info;
                $cart_item_data['rbfw_management_info']          = $rbfw_management_info;
                $cart_item_data['rbfw_management_price']          = $rbfw_management_price;
                $cart_item_data['security_deposit_amount']       = $security_deposit['security_deposit_amount'];
                $cart_item_data['security_deposit_desc']         = $security_deposit['security_deposit_desc'];

            }elseif ( $rbfw_rent_type == 'multiple_items' ) {

                $start_date                = isset( $sd_input_data_sabitized['rbfw_pickup_start_date'] ) ? $sd_input_data_sabitized['rbfw_pickup_start_date'] : '';
                $start_time                = isset( $sd_input_data_sabitized['rbfw_pickup_start_time'] ) ? $sd_input_data_sabitized['rbfw_pickup_start_time'] : '';
                $pickup_datetime           = gmdate( 'Y-m-d H:i', strtotime( $start_date . ' ' . $start_time ) );
                $durationType = isset($_POST['durationType'])?sanitize_text_field(wp_unslash($_POST['durationType'])):'';
                $durationQty = isset($_POST['durationQty'])?max( 1, absint(sanitize_text_field(wp_unslash($_POST['durationQty'])))):1;
                if ( ! in_array( $durationType, array( 'hourly', 'daily', 'weekly', 'monthly' ), true ) ) {
                    $durationType = 'daily';
                }
                try {
                    $start_date_time = new DateTime($start_date.' '.$start_time);
                } catch ( Exception $e ) {
                    $start_date_time = new DateTime();
                }
                $total_hours = ($durationType == 'hourly' ? $durationQty : ($durationType == 'daily' ? $durationQty * 24 :($durationType == 'weekly'?$durationQty * 24 * 7: $durationQty * 24 * 30)));

                $start_date_time->modify("+$total_hours hours");
                $end_date = $start_date_time->format('Y-m-d');
                $end_time = $start_date_time->format('H:i:s');
                $dropoff_datetime = gmdate('Y-m-d H:i', strtotime($end_date . ' ' . $end_time));

                $rbfw_pickup_point         = isset( $sd_input_data_sabitized['rbfw_pickup_point'] ) ? $sd_input_data_sabitized['rbfw_pickup_point'] : '';
                $rbfw_dropoff_point        = isset( $sd_input_data_sabitized['rbfw_dropoff_point'] ) ? $sd_input_data_sabitized['rbfw_dropoff_point'] : '';
                $rbfw_duration_md       = isset( $sd_input_data_sabitized['rbfw_duration_md'] ) ? $sd_input_data_sabitized['rbfw_duration_md'] : '';

                try {
                    $pickup  = new DateTime($pickup_datetime);
                    $dropoff = new DateTime($dropoff_datetime);
                } catch ( Exception $e ) {
                    $pickup  = new DateTime();
                    $dropoff = new DateTime();
                }
                $interval = $pickup->diff($dropoff);
                $total_days = $interval->days ? absint( $interval->days ) : 1;

                $submitted_multiple_items = (isset( $sd_input_data_sabitized['multiple_items_info'] ) && is_array( $sd_input_data_sabitized['multiple_items_info'] ) ) ? $sd_input_data_sabitized['multiple_items_info'] : [];
                $prepared_multiple_items  = $this->rbfw_prepare_multi_items_from_post( $rbfw_id, $submitted_multiple_items, $durationType, $durationQty );
                $multiple_items_info      = $prepared_multiple_items['items'];
                $rbfw_multi_item_price    = $prepared_multiple_items['total'];

                $submitted_category_wise_info = isset( $sd_input_data_sabitized['rbfw_category_wise_info'] ) ? $sd_input_data_sabitized['rbfw_category_wise_info'] : [];
                $prepared_category_wise_info  = $this->rbfw_prepare_multi_item_addons_from_post( $rbfw_id, $submitted_category_wise_info, $total_days );
                $rbfw_category_wise_info      = $prepared_category_wise_info['items'];
                $rbfw_category_wise_price     = $prepared_category_wise_info['total'];

                $sub_total_price = $rbfw_multi_item_price + $rbfw_category_wise_price;

                $rbfw_management_info_all = (isset( $sd_input_data_sabitized['rbfw_management_info'] ) && is_array( $sd_input_data_sabitized['rbfw_management_info'] ) ) ? $sd_input_data_sabitized['rbfw_management_info'] : [];
                $prepared_management_info = $this->rbfw_prepare_multi_item_fees_from_post( $rbfw_id, $rbfw_management_info_all, $sub_total_price );
                $rbfw_management_info     = $prepared_management_info['items'];
                $rbfw_management_price    = $prepared_management_info['total'];
                list( $rbfw_management_info, $rbfw_management_price ) = rbfw_apply_location_charge( $rbfw_id, $rbfw_pickup_point, $rbfw_management_info, $rbfw_management_price );




                $security_deposit                                 = rbfw_security_deposit( $rbfw_id, $sub_total_price );
                $total_price                                      = $sub_total_price + $rbfw_management_price - $discount_amount;
                $rbfw_ticket_info                                 = $this->rbfw_cart_multi_items_ticket_info( $rbfw_id, $start_date, $end_date, $start_time, $end_time, $rbfw_pickup_point, $rbfw_dropoff_point,$total_price, $multiple_items_info , $rbfw_category_wise_info,$total_days,$durationQty, $rbfw_regf_info, $security_deposit,$rbfw_management_info,$rbfw_management_price,$rbfw_multi_item_price);
                $cart_item_data['rbfw_pickup_point']              = $rbfw_pickup_point;
                $cart_item_data['rbfw_dropoff_point']             = $rbfw_dropoff_point;
                $cart_item_data['rbfw_duration_md']               = $rbfw_duration_md;

                $cart_item_data['rbfw_start_date']                = $start_date;
                $cart_item_data['rbfw_start_time']                = $start_time;
                $cart_item_data['rbfw_end_date']                  = $end_date;
                $cart_item_data['rbfw_end_time']                  = $end_time;
                $cart_item_data['rbfw_start_datetime']            = $pickup_datetime;
                $cart_item_data['rbfw_end_datetime']              = $dropoff_datetime;


                $cart_item_data['multiple_items_info']            = $multiple_items_info;
                $cart_item_data['rbfw_duration_price']            = $rbfw_multi_item_price;

                $cart_item_data['rbfw_category_wise_info']        = $rbfw_category_wise_info;

                $cart_item_data['duration_type']                  = $durationType;
                $cart_item_data['duration_qty']                   = $durationQty;
                $cart_item_data['rbfw_ticket_info']               = $rbfw_ticket_info;
                $cart_item_data['rbfw_management_info']           = $rbfw_management_info;
                $cart_item_data['rbfw_management_price']          = $rbfw_management_price;
                $cart_item_data['total_days']                     = $total_days;
                $cart_item_data['discount_type']                  = $discount_type;
                $cart_item_data['discount_amount']                = $discount_amount;
                $cart_item_data['security_deposit_amount']        = $security_deposit['security_deposit_amount'];
                $cart_item_data['security_deposit_desc']          = $security_deposit['security_deposit_desc'];

            } else {
                global $rbfw;
                $start_date                = isset( $sd_input_data_sabitized['rbfw_pickup_start_date'] ) ? $sd_input_data_sabitized['rbfw_pickup_start_date'] : '';

                $rbfw_count_extra_day_enable = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
                $rbfw_enable_time_picker  = get_post_meta( $rbfw_id, 'rbfw_enable_time_picker', true ) ? get_post_meta( $rbfw_id, 'rbfw_enable_time_picker', true ) : '';
                $end_date = isset($sd_input_data_sabitized['rbfw_pickup_end_date']) ? $sd_input_data_sabitized['rbfw_pickup_end_date'] : '';
             /*   if($rbfw_count_extra_day_enable=='on' && $rbfw_enable_time_picker=='no') {
                    $date = new DateTime($end_date);
                    $date->modify("+1 day");
                    $end_date = $date->format("Y-m-d");
                }*/
                $start_time                = isset( $sd_input_data_sabitized['rbfw_pickup_start_time'] ) ? $sd_input_data_sabitized['rbfw_pickup_start_time'] : '';
                $end_time                  = isset( $sd_input_data_sabitized['rbfw_pickup_end_time'] ) ? $sd_input_data_sabitized['rbfw_pickup_end_time'] : '';


                $pickup_datetime           = gmdate( 'Y-m-d H:i', strtotime( $start_date . ' ' . $start_time ) );
                $dropoff_datetime          = gmdate( 'Y-m-d H:i', strtotime( $end_date . ' ' . $end_time ) );
                $rbfw_pickup_point         = isset( $sd_input_data_sabitized['rbfw_pickup_point'] ) ? $sd_input_data_sabitized['rbfw_pickup_point'] : '';
                $rbfw_dropoff_point        = isset( $sd_input_data_sabitized['rbfw_dropoff_point'] ) ? $sd_input_data_sabitized['rbfw_dropoff_point'] : '';
                $rbfw_duration_md       = isset( $sd_input_data_sabitized['rbfw_duration_md'] ) ? $sd_input_data_sabitized['rbfw_duration_md'] : '';
                $rbfw_enable_time_slot     = isset( $sd_input_data_sabitized['rbfw_enable_time_slot'] ) ? $sd_input_data_sabitized['rbfw_enable_time_slot'] : 'off';
                $duration_price_info       = rbfw_md_duration_price_calculation( $rbfw_id, $pickup_datetime, $dropoff_datetime, $start_date, $end_date, $start_time, $end_time, $rbfw_enable_time_slot );

                $duration_price_individual = $duration_price_info['duration_price'];
                $duration_price            = $duration_price_info['duration_price'] * $rbfw_item_quantity;
                $total_days                = $duration_price_info['total_days'];
                /* service price start for multiple days */
                $rbfw_service_price = 0;
                $rbfw_service_infos_post = isset( $sd_input_data_sabitized['rbfw_service_price_data'] ) ? $sd_input_data_sabitized['rbfw_service_price_data'] : [];
                $rbfw_service_infos = [];

                /*
                 * SECURITY: the per-service unit price and price type are taken from the
                 * item's stored configuration (rbfw_service_category_price), NEVER from the
                 * request. The form posts rbfw_service_price_data[..][price], but trusting it
                 * would let any visitor set an arbitrary/negative price and force the order
                 * total to pennies. We only honour the customer's selection (which service +
                 * quantity); the price is re-derived server-side and unknown services are
                 * not charged.
                 */
                $rbfw_trusted_service_prices = rbfw_get_trusted_category_service_prices( $rbfw_id );

                if ( ! empty( $rbfw_service_infos_post ) && is_array( $rbfw_service_infos_post ) ) {
                    foreach ( $rbfw_service_infos_post as $key_cat => $value ) {
                        if ( ! is_array( $value ) ) {
                            continue;
                        }
                        $cat_title                       = isset( $value['cat_title'] ) ? $value['cat_title'] : '';
                        $rbfw_service_infos[ $cat_title ] = [];
                        foreach ( $value as $key_ser => $item ) {
                            // The category-level "cat_title" entry is a string; only service rows are arrays
                            // carrying the [name] field posted by forms/multi-day-registration.php.
                            if ( 'cat_title' === $key_ser || ! is_array( $item ) || empty( $item['name'] ) ) {
                                continue;
                            }
                            $service_qty = isset( $item['quantity'] ) ? (float) $item['quantity'] : 0;
                            if ( $service_qty <= 0 ) {
                                continue; // service not selected
                            }
                            $service_name = (string) $item['name'];
                            // Trusted price/type from stored config; a service not present in the
                            // item's configuration is never charged (posted price is ignored).
                            if ( ! isset( $rbfw_trusted_service_prices[ $cat_title ][ $service_name ] ) ) {
                                continue;
                            }
                            $service_unit_price = $rbfw_trusted_service_prices[ $cat_title ][ $service_name ]['price'];
                            $service_type       = $rbfw_trusted_service_prices[ $cat_title ][ $service_name ]['type'];
                            // Overwrite any request-supplied price/type so the stored order/ticket
                            // record reflects the real, server-derived values.
                            $item['price']              = $service_unit_price;
                            $item['service_price_type'] = $service_type;
                            $rbfw_service_infos[ $cat_title ][] = $item;
                            if ( 'day_wise' === $service_type ) {
                                $rbfw_service_price += $service_unit_price * $service_qty * $total_days;
                            } else {
                                $rbfw_service_price += $service_unit_price * $service_qty;
                            }
                        }
                    }
                }


                $rbfw_service_infos_new = [];
                foreach ( $rbfw_service_infos as $item_s ) {
                    if ( ! empty( $item_s ) ) {
                        $rbfw_service_infos_new = $rbfw_service_infos;
                    }
                }
                $rbfw_service_infos = $rbfw_service_infos_new;
                $rbfw_service_price = $rbfw_service_price * $rbfw_item_quantity;
                /* service price end for multiple days */
                $rbfw_extra_service_price = 0;
                $rbfw_duration_price      = $duration_price;
                $rbfw_extra_service_data  = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : '';
                if ( ! empty( $rbfw_extra_service_data ) ) {
                    $extra_services = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
                } else {
                    $extra_services = array();
                }
                foreach ( $rbfw_service_info as $key => $value ) {
                    $service_name = $key; //Service1
                    if ( array_key_exists( $service_name, $extra_services ) ) { // if Service1 exist in array
                        if ( $rbfw_item_quantity > 1 && (int) $extra_services[ $service_name ] == 1 && $rbfw_enable_extra_service_qty != 'yes' ) {
                            $rbfw_extra_service_price +=  $rbfw_item_quantity * (float) $value; // quantity * price
                        } else {
                            $rbfw_extra_service_price +=  $extra_services[ $service_name ] * (float) $value; // quantity * price
                        }
                    }
                }
                /* Item Variations (Multi Day): per-value quantity steppers submit
                   rbfw_variation_qty[field_id][value_name] = qty. Mirror of the single-day
                   capture above -- one flat entry per chosen size with its per-unit
                   surcharge, so stock is enforced per size and each shows in cart/order. */
                $variation_data           = get_post_meta( $rbfw_id, 'rbfw_variations_data', true );
                $variation_info           = [];
                $rbfw_variation_surcharge = 0.0;
                $rbfw_variation_qty_input = ( isset( $sd_input_data_sabitized['rbfw_variation_qty'] ) && is_array( $sd_input_data_sabitized['rbfw_variation_qty'] ) ) ? $sd_input_data_sabitized['rbfw_variation_qty'] : [];
                if ( ! empty( $variation_data ) && is_array( $variation_data ) ) {
                    $i = 0;
                    foreach ( $variation_data as $level_one_arr ) {
                        // Skip incomplete/legacy variation rows (missing id or values)
                        // so they cannot raise "Undefined array key" notices here.
                        if ( ! is_array( $level_one_arr ) || empty( $level_one_arr['field_id'] ) || empty( $level_one_arr['value'] ) || ! is_array( $level_one_arr['value'] ) ) {
                            continue;
                        }
                        $field_id    = $level_one_arr['field_id'];
                        $field_label = isset( $level_one_arr['field_label'] ) ? $level_one_arr['field_label'] : '';
                        $qty_map     = ( isset( $rbfw_variation_qty_input[ $field_id ] ) && is_array( $rbfw_variation_qty_input[ $field_id ] ) ) ? $rbfw_variation_qty_input[ $field_id ] : [];
                        foreach ( $level_one_arr['value'] as $level_two_arr_value ) {
                            $level_two_name = isset( $level_two_arr_value['name'] ) ? $level_two_arr_value['name'] : '';
                            if ( '' === $level_two_name ) {
                                continue;
                            }
                            $chosen_qty = isset( $qty_map[ $level_two_name ] ) ? max( 0, (int) $qty_map[ $level_two_name ] ) : 0;
                            if ( $chosen_qty <= 0 ) {
                                continue;
                            }
                            $unit_price                = rbfw_get_variation_price_for_value( $rbfw_id, $level_two_name );
                            $variation_info[ $i ]      = array(
                                'field_id'    => $field_id,
                                'field_label' => $field_label,
                                'field_value' => $level_two_name,
                                'qty'         => $chosen_qty,
                                'price'       => $unit_price,
                            );
                            $rbfw_variation_surcharge += $unit_price * $chosen_qty;
                            $i ++;
                        }
                    }
                }



                // Per-unit variant surcharge adds on top of the quantity-scaled duration price.
                $sub_total_price = $rbfw_duration_price + $rbfw_service_price + $rbfw_extra_service_price + $rbfw_variation_surcharge;

                $rbfw_management_info_all = (isset( $sd_input_data_sabitized['rbfw_management_info'] ) && is_array( $sd_input_data_sabitized['rbfw_management_info'] ) ) ? $sd_input_data_sabitized['rbfw_management_info'] : [];

                $rbfw_management_price = 0;
                $rbfw_management_info             = array();
                $selected_management_fees = array();
                if ( ! empty( $rbfw_management_info_all ) ) {
                    foreach ( $rbfw_management_info_all as $key => $value ) {
                        $service_label = ! empty( $value['label'] ) ? $value['label'] : '';
                        $is_checked  = ! empty( $value['is_checked'] ) ? $value['is_checked'] : 0;
                        if ( $service_label && $is_checked == 'yes' ) {
                            $selected_management_fees[ $service_label ] = true;
                        }
                    }
                }

                $rbfw_fee_data = get_post_meta( $rbfw_id, 'rbfw_fee_data', true );
                $rbfw_fee_data = is_array( $rbfw_fee_data ) ? $rbfw_fee_data : array();
                foreach ( $rbfw_fee_data as $fee ) {
                    $service_label = ! empty( $fee['label'] ) ? $fee['label'] : '';
                    $priority = ! empty( $fee['priority'] ) ? $fee['priority'] : 'optional';
                    if ( $service_label && ( isset( $selected_management_fees[ $service_label ] ) || $priority == 'required' ) ) {
                        $price = ! empty( $fee['amount'] ) ? (float) $fee['amount'] : 0;
                        $price_type = ! empty( $fee['calculation_type'] ) ? $fee['calculation_type'] : 'fixed';
                        $frequency = ! empty( $fee['frequency'] ) ? $fee['frequency'] : 'one-time';
                        $refundable = ! empty( $fee['refundable'] ) ? $fee['refundable'] : 'no';
                        if ( $price_type === 'percentage' ) {
                            $fee_total = ( $price / 100 ) * $sub_total_price;
                            $rbfw_management_price += $fee_total;
                            $rbfw_management_info[ $service_label ] = array( 'price' => $fee_total, 'price_desc' => $price . '% of ' . wc_price( $sub_total_price ), 'refundable' => $refundable );
                        } else {
                            $is_day_wise_fee = in_array( $frequency, array( 'per-day', 'day-wise', 'day_wise' ), true );
                            if ( $is_day_wise_fee ) {
                                $fee_total = $price * $rbfw_item_quantity * $total_days;
                                $rbfw_management_price += $fee_total;
                                $rbfw_management_info[ $service_label ] = array( 'price_desc' => wc_price( $price ) . '*' . $rbfw_item_quantity . '*' . $total_days, 'price' => $fee_total, 'refundable' => $refundable );
                            } else {
                                $fee_total = $price * $rbfw_item_quantity;
                                $rbfw_management_price += $fee_total;
                                $rbfw_management_info[ $service_label ] = array( 'price_desc' => wc_price( $price ) . '*' . $rbfw_item_quantity, 'price' => $fee_total, 'refundable' => $refundable );
                            }
                        }
                    }
                }


                list( $rbfw_management_info, $rbfw_management_price ) = rbfw_apply_location_charge( $rbfw_id, $rbfw_pickup_point, $rbfw_management_info, $rbfw_management_price );

                $discount_amount = 0;
                if ( function_exists( 'rbfw_get_discount_array' ) ) {
                    $discount_arr = rbfw_get_discount_array( $rbfw_id, $total_days, $sub_total_price, $rbfw_item_quantity );
                    if ( ! empty( $discount_arr ) ) {
                        $discount_type   = $discount_arr['discount_type'];
                        $discount_amount = $discount_arr['discount_amount'];
                    }
                }
                $security_deposit                                 = rbfw_security_deposit( $rbfw_id, $sub_total_price );
                $total_price                                      = $sub_total_price + $rbfw_management_price - $discount_amount;
                $rbfw_ticket_info                                 = $this->rbfw_cart_ticket_info( $rbfw_id, $start_date, $end_date, $start_time, $end_time, $rbfw_pickup_point, $rbfw_dropoff_point, $rbfw_item_quantity, $rbfw_duration_price, $rbfw_service_price + $rbfw_extra_service_price, $total_price, $rbfw_service_info, $variation_info, $discount_type, $discount_amount, $rbfw_regf_info, $rbfw_service_infos, $total_days, $security_deposit , $rbfw_management_info, $rbfw_management_price);
                $cart_item_data['rbfw_pickup_point']              = $rbfw_pickup_point;
                $cart_item_data['rbfw_dropoff_point']             = $rbfw_dropoff_point;
                $cart_item_data['rbfw_duration_md']               = $rbfw_duration_md;
                $cart_item_data['rbfw_start_date']                = $start_date;
                $cart_item_data['rbfw_start_time']                = $start_time;
                $cart_item_data['rbfw_end_date']                  = $end_date;
                $cart_item_data['rbfw_end_time']                  = $end_time;
                $cart_item_data['rbfw_start_datetime']            = $pickup_datetime;
                $cart_item_data['rbfw_end_datetime']              = $dropoff_datetime;
                $cart_item_data['rbfw_item_quantity']             = $rbfw_item_quantity;
                $cart_item_data['rbfw_service_info']              = $rbfw_service_info;
                $cart_item_data['rbfw_management_info']           = $rbfw_management_info;
                $cart_item_data['rbfw_management_price']           = $rbfw_management_price;
                $cart_item_data['rbfw_service_infos']             = $rbfw_service_infos;
                $cart_item_data['rbfw_variation_info']            = $variation_info;
                $cart_item_data['rbfw_variation_surcharge']       = $rbfw_variation_surcharge;
                $cart_item_data['rbfw_ticket_info']               = $rbfw_ticket_info;
                $cart_item_data['rbfw_duration_price_individual'] = $duration_price_individual;
                $cart_item_data['rbfw_duration_price']            = $rbfw_duration_price;
                $cart_item_data['rbfw_service_price']             = $rbfw_service_price + $rbfw_extra_service_price;
                $cart_item_data['discount_type']                  = $discount_type;
                $cart_item_data['discount_amount']                = $discount_amount;
                $cart_item_data['security_deposit_amount']        = $security_deposit['security_deposit_amount'];
                $cart_item_data['security_deposit_desc']          = $security_deposit['security_deposit_desc'];
                $cart_item_data['total_days']                     = $total_days;
            }

            $cart_item_data['start_date']    = isset( $start_date ) ? $start_date : '';
            $cart_item_data['end_date']      = $end_date;
            $cart_item_data['rbfw_tp']       = $total_price;
            $cart_item_data['line_total']    = $total_price;
            $cart_item_data['line_subtotal'] = $total_price;

            return apply_filters( 'rbfw_add_cart_function_after', $cart_item_data, $rbfw_id );
        }
        public function rbfw_set_new_cart_price( $cart_object ) {
            global $rbfw;
            foreach ( $cart_object->cart_contents as $key => $value ) {
                $rbfw_id = array_key_exists( 'rbfw_id', $value ) ? $value['rbfw_id'] : 0;
                if ( get_post_type( $rbfw_id ) == $rbfw->get_cpt_name() ) {
                    do_action( 'rbfw_set_cart_item_price', $value, $rbfw_id );
                }
            }
        }
        public  function rbfw_show_cart_items( $item_data, $cart_item ) {
            global $rbfw;
            $rbfw_id = array_key_exists( 'rbfw_id', $cart_item ) ? $cart_item['rbfw_id'] : 0;
            ob_start();

            if ( get_post_type( $rbfw_id ) == $rbfw->get_cpt_name() ) {
                
                include( RBFW_Function::get_template_path( 'cart_page.php' ) );
            }
            $content     = ob_get_clean();
            $item_data[] = array(
                'name'    => '',
                'key'     => '',
                'value'   => $content,
                'display' => '',
            );

            return $item_data;
        }
        public  function rbfw_validation_before_checkout( $posted ) {
            global $woocommerce, $rbfw;
            $items = $woocommerce->cart->get_cart();
            foreach ( $items as $item => $values ) {
                $rbfw_id = array_key_exists( 'rbfw_id', $values ) ? $values['rbfw_id'] : 0;
                if ( get_post_type( $rbfw_id ) == $rbfw->get_cpt_name() ) {
                    do_action( 'rbfw_validate_cart_item', $values, $rbfw_id );
                }
            }
        }
        public   function rbfw_add_order_item_data( $item, $cart_item_key, $values, $order ) {
            global $rbfw;
            $rbfw_id = array_key_exists( 'rbfw_id', $values ) ? $values['rbfw_id'] : 0;
            if ( get_post_type( $rbfw_id ) == $rbfw->get_cpt_name() ) {
                $this->rbfw_validate_add_order_item_func( $values, $item, $rbfw_id );
            }
        }
        public  function rbfw_validate_add_order_item_func( $values, $item, $rbfw_id ) {
            global $rbfw;
            $rbfw_rent_type              = get_post_meta( $rbfw_id, 'rbfw_item_type', true );
            $rbfw_security_deposit_label = get_post_meta( $rbfw_id, 'rbfw_security_deposit_label', true );
            /* Type: Resort */
            if ( $rbfw_rent_type == 'resort' ) {
                $rbfw_start_datetime = $values['rbfw_start_datetime'] ? $values['rbfw_start_datetime'] : '';
                $rbfw_end_datetime = $values['rbfw_end_datetime'] ? $values['rbfw_end_datetime'] : '';
                $origin     = $rbfw_start_datetime ? date_create( $rbfw_start_datetime ) : false;
                $target     = $rbfw_end_datetime ? date_create( $rbfw_end_datetime ) : false;
                $interval   = ( $origin && $target ) ? date_diff( $origin, $target ) : false;
                $total_days = $interval ? $interval->format( '%a' ) : 0;
                $rbfw_count_extra_day_enable = $rbfw->get_option_trans( 'rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on' );
                if ( $rbfw_count_extra_day_enable == 'on' ) {
                    $total_days++;
                }
                $rbfw_room_price_category = $values['rbfw_room_price_category'] ? $values['rbfw_room_price_category'] : '';
                $rbfw_ticket_info = $values['rbfw_ticket_info'] ? $values['rbfw_ticket_info'] : [];
                $rbfw_room_info = $values['rbfw_room_info'] ? $values['rbfw_room_info'] : [];
                $rbfw_management_info = $values['rbfw_management_info'] ? $values['rbfw_management_info'] : [];
                $rbfw_fee_data = get_post_meta($rbfw_id, 'rbfw_fee_data', true) ? get_post_meta($rbfw_id, 'rbfw_fee_data', true) : array();



               // $rbfw_management_price = $values['management_price'] ? $values['rbfw_management_price'] : [];
                $rbfw_room_price = $values['rbfw_room_price'] ? $values['rbfw_room_price'] : [];
                $rbfw_resort_room_data = get_post_meta($rbfw_id, 'rbfw_resort_room_data', true) ? get_post_meta($rbfw_id, 'rbfw_resort_room_data', true) : array();
                if ($rbfw_room_price_category == 'daynight') {
                    $room_types = array_column($rbfw_resort_room_data, 'rbfw_room_daynight_rate', 'room_type');
                } elseif ($rbfw_room_price_category == 'daylong') {
                    $room_types = array_column($rbfw_resort_room_data, 'rbfw_room_daylong_rate', 'room_type');
                } else {
                    $room_types = array();
                }
                $rbfw_service_info = $values['rbfw_service_info'] ? $values['rbfw_service_info'] : [];
                $rbfw_extra_service_data = get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) ? get_post_meta($rbfw_id, 'rbfw_extra_service_data', true) : array();
                if (!empty($rbfw_extra_service_data)):
                    $extra_services = array_column($rbfw_extra_service_data, 'service_price', 'service_name');
                else:
                    $extra_services = array();
                endif;
                $rbfw_room_duration_price = $values['rbfw_room_duration_price'] ? $values['rbfw_room_duration_price'] : '';
                $rbfw_room_service_price = $values['rbfw_room_service_price'] ? $values['rbfw_room_service_price'] : '';
                $discount_amount = $values['discount_amount'] ? $values['discount_amount'] : '';

                $checkin_label = (
                    $rbfw->get_option_trans('rbfw_text_checkin_date', 'rbfw_basic_translation_settings')
                    && want_loco_translate() == 'no'
                )
                    ? $rbfw->get_option_trans('rbfw_text_checkin_date', 'rbfw_basic_translation_settings')
                    : esc_html__('Check-In Date:', 'booking-and-rental-manager-for-woocommerce');

                $item->add_meta_data(
                    esc_html($checkin_label),
                    rbfw_date_format($rbfw_start_datetime)
                );

                $checkout_label = (
                    $rbfw->get_option_trans('rbfw_text_checkout_date', 'rbfw_basic_translation_settings')
                    && want_loco_translate() == 'no'
                )
                    ? $rbfw->get_option_trans('rbfw_text_checkout_date', 'rbfw_basic_translation_settings')
                    : esc_html__('Check-Out Date:', 'booking-and-rental-manager-for-woocommerce');

                $item->add_meta_data(
                    esc_html($checkout_label),
                    rbfw_date_format($rbfw_end_datetime)
                );


                $package_label = (
                    $rbfw->get_option_trans('rbfw_text_package', 'rbfw_basic_translation_settings')
                    && want_loco_translate() == 'no'
                )
                    ? $rbfw->get_option_trans('rbfw_text_package', 'rbfw_basic_translation_settings')
                    : esc_html__('Package:', 'booking-and-rental-manager-for-woocommerce');

                $item->add_meta_data(
                    esc_html($package_label),
                    $rbfw_room_price_category
                );


                if (!empty($rbfw_room_info)):
                    foreach ($rbfw_room_info as $key => $value):
                        $room_type = $key; //Type
                        if (array_key_exists($room_type, $room_types)) { // if Type exist in array
                            $room_price = $rbfw_room_price[$room_type]; // get type price from array
                            $room_qty = $value;
                            $total_price = (float)$room_price * (float)$room_qty;
                            $room_description = '';
                            foreach ($rbfw_resort_room_data as $resort_room_data) {
                                if ($resort_room_data['room_type'] == $room_type) {
                                    $room_description = $resort_room_data['rbfw_room_desc']; // get type description from array
                                }
                            }
                            $room_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                            $room_content .= '<tr>';
                            $room_content .= '<td style="border:1px solid #f5f5f5;">';
                            $room_content .= '<strong>' . $room_type . '</strong>';
                            $room_content .= '<br>';
                            $room_content .= '<span>' . $room_description . '</span>';
                            $room_content .= '</td>';
                            $room_content .= '<td style="border:1px solid #f5f5f5;">';
                            $room_content .= '(' . wc_price($room_price) . ' x ' . $room_qty . ') = ' . wc_price($total_price);
                            $room_content .= '</td>';
                            $room_content .= '</tr>';
                            $room_content .= '</table>';
                            if ($room_qty > 0):
                                $room_info_label = (
                                    $rbfw->get_option_trans('rbfw_text_room_information', 'rbfw_basic_translation_settings')
                                    && want_loco_translate() == 'no'
                                )
                                    ? $rbfw->get_option_trans('rbfw_text_room_information', 'rbfw_basic_translation_settings')
                                    : esc_html__('Room Information:', 'booking-and-rental-manager-for-woocommerce');

                                $item->add_meta_data(
                                    esc_html($room_info_label),
                                    $room_content
                                );
                            endif;
                        }
                    endforeach;
                endif;


                $resort_service_arr = [];
                if (!empty($rbfw_service_info)):
                    foreach ($rbfw_service_info as $key => $value):
                        $service_name = $key; //service name
                        if (array_key_exists($service_name, $extra_services)) { // if service name exist in array
                            $service_price = $extra_services[$service_name]; // get type price from array
                            $service_qty = $value;
                            $total_service_price = (float)$service_price * (float)$service_qty;
                            $resort_service_arr[] = array(
                                $service_name => $service_qty
                            );
                            $room_service_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                            $room_service_content .= '<tr>';
                            $room_service_content .= '<td style="border:1px solid #f5f5f5;">';
                            $room_service_content .= '<strong>' . $service_name . '</strong>';
                            $room_service_content .= '</td>';
                            $room_service_content .= '<td style="border:1px solid #f5f5f5;">';
                            $room_service_content .= '(' . wc_price($service_price) . ' x ' . $service_qty . ') = ' . wc_price($total_service_price);
                            $room_service_content .= '</td>';
                            $room_service_content .= '</tr>';
                            $room_service_content .= '</table>';
                            if ($service_qty > 0):
                                $service_info_label = (
                                    $rbfw->get_option_trans('rbfw_text_room_service_information', 'rbfw_basic_translation_settings')
                                    && want_loco_translate() == 'no'
                                )
                                    ? $rbfw->get_option_trans('rbfw_text_room_service_information', 'rbfw_basic_translation_settings')
                                    : esc_html__('Service Information:', 'booking-and-rental-manager-for-woocommerce');

                                $item->add_meta_data(
                                    esc_html($service_info_label),
                                    $room_service_content
                                );
                            endif;
                        }
                    endforeach;
                endif;


                if (!empty($rbfw_fee_data)){
                    $fee_management_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    foreach ($rbfw_fee_data as  $fee) {
                        $service_price = (float)$fee['amount'];
                        $refundable = ($fee['refundable']=='yes')?'( Refundable )':'( Non refundable )';
                        if (isset($rbfw_management_info[$fee['label']]) && $rbfw_management_info[$fee['label']] == "yes") {
                            $fee_calculation_type = ! empty( $fee['calculation_type'] ) ? $fee['calculation_type'] : 'fixed';
                            $fee_frequency = ! empty( $fee['frequency'] ) ? $fee['frequency'] : 'one-time';
                            $fee_base_price = (float) $rbfw_room_duration_price + (float) $rbfw_room_service_price;
                            if ( $fee_calculation_type == 'percentage' ) {
                                $fee_total = ( $service_price / 100 ) * $fee_base_price;
                                $fee_price_display = $service_price . '% x ' . wc_price( $fee_base_price ) . ' = ' . wc_price( $fee_total );
                            } elseif ( $fee_frequency != 'one-time' ) {
                                $fee_total = $service_price * (int) $total_days;
                                $fee_price_display = wc_price( $service_price ) . ' x ' . esc_html( $total_days ) . ' = ' . wc_price( $fee_total );
                            } else {
                                $fee_total = $service_price;
                                $fee_price_display = wc_price( $fee_total );
                            }
                            $fee_management_content .= '<tr>';
                            $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                            $fee_management_content .= '<strong>' . $fee['label'] . ' ' . $refundable . '</strong>';
                            $fee_management_content .= '</td>';
                            $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                            $fee_management_content .= '(' . $fee_price_display . ')';
                            $fee_management_content .= '</td>';
                            $fee_management_content .= '</tr>';
                        }
                    }
                    $fee_management_content .= '</table>';

                    $fee_management_info_label = esc_html__('Fee Management Info:', 'booking-and-rental-manager-for-woocommerce');
                    $item->add_meta_data(
                        esc_html($fee_management_info_label),
                        $fee_management_content
                    );
                }

                $duration_cost_label = (
                    $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings' )
                    && want_loco_translate() == 'no'
                )
                    ? $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings' )
                    : esc_html__( 'Duration Cost:', 'booking-and-rental-manager-for-woocommerce' );

                $item->add_meta_data(
                    esc_html( $duration_cost_label ),
                    wc_price( $rbfw_room_duration_price )
                );
                if ( $rbfw_room_service_price ) {
                    $resource_cost_label = (
                        $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings' )
                        && want_loco_translate() == 'no'
                    )
                        ? $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings' )
                        : esc_html__( 'Resource Cost:', 'booking-and-rental-manager-for-woocommerce' );

                    $item->add_meta_data(
                        esc_html( $resource_cost_label ),
                        wc_price( $rbfw_room_service_price )
                    );
                }





               /* $fee_management_label = esc_html__( 'Fee Management Cost', 'booking-and-rental-manager-for-woocommerce' );

                if($rbfw_management_price){
                    $item->add_meta_data(
                        esc_html( $fee_management_label ),
                        wc_price($rbfw_management_price)
                    );
                }*/



                $discount_label = (
                    $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings' )
                    && want_loco_translate() == 'no'
                )
                    ? $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings' )
                    : esc_html__( 'Discount', 'booking-and-rental-manager-for-woocommerce' ) . ' :';

                if($discount_amount){
                    $item->add_meta_data(
                        esc_html( $discount_label ),
                        wc_price( $discount_amount )
                    );
                }


                $security_deposit = rbfw_security_deposit( $rbfw_id, ( (int) $rbfw_room_duration_price + (int) $rbfw_room_service_price ) );
                if ( $security_deposit['security_deposit_amount'] ) {
                    $item->add_meta_data( $rbfw_security_deposit_label, wc_price( $security_deposit['security_deposit_amount'] ) );
                }
                $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );

            } elseif ( $rbfw_rent_type == 'bike_car_sd' || $rbfw_rent_type == 'appointment' ) {

                $pickup_location     = $values['rbfw_pickup_point'] ? $values['rbfw_pickup_point'] : '';
                $dropoff_location    = $values['rbfw_dropoff_point'] ? $values['rbfw_dropoff_point'] : '';
                $rbfw_start_datetime = $values['rbfw_start_datetime'] ? $values['rbfw_start_datetime'] : '';
                $rbfw_start_time     = $values['rbfw_start_time'] ? $values['rbfw_start_time'] : '';
                $rbfw_end_datetime   = $values['rbfw_end_datetime'] ? $values['rbfw_end_datetime'] : '';
                $rbfw_end_time       = $values['rbfw_end_time'] ? $values['rbfw_end_time'] : '';
                $rbfw_ticket_info    = $values['rbfw_ticket_info'] ? $values['rbfw_ticket_info'] : [];
                $rbfw_type_info      = $values['rbfw_type_info'] ? $values['rbfw_type_info'] : [];
                $variation_info      = ! empty( $values['rbfw_variation_info'] ) ? $values['rbfw_variation_info'] : [];

                $rbfw_management_info = $values['rbfw_management_info'] ? $values['rbfw_management_info'] : [];
                $rbfw_management_price = $values['rbfw_management_price'] ? $values['rbfw_management_price'] : [];

                $rbfw_bikecarsd_data = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true ) : array();
                if ( ! empty( $rbfw_bikecarsd_data ) ):
                    $rent_types = array_column( $rbfw_bikecarsd_data, 'price', 'rent_type' );
                else:
                    $rent_types = array();
                endif;
                $rbfw_service_info       = $values['rbfw_service_info'] ? $values['rbfw_service_info'] : [];
                $rbfw_extra_service_data = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();
                if ( ! empty( $rbfw_extra_service_data ) ):
                    $extra_services = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
                else:
                    $extra_services = array();
                endif;
                $rbfw_bikecarsd_duration_price = $values['rbfw_bikecarsd_duration_price'] ? $values['rbfw_bikecarsd_duration_price'] : '';
                $rbfw_bikecarsd_service_price  = $values['rbfw_bikecarsd_service_price'] ? $values['rbfw_bikecarsd_service_price'] : '';
                if ( rbfw_booking_has_time( $rbfw_start_time ) ) {
                    $start_date_time_label = (
                        $rbfw->get_option_trans( 'rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings' )
                        && want_loco_translate() == 'no'
                    )
                        ? $rbfw->get_option_trans( 'rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings' )
                        : esc_html__( 'Start Date and Time:', 'booking-and-rental-manager-for-woocommerce' );

                    $item->add_meta_data(
                        esc_html( $start_date_time_label ),
                        rbfw_date_format( $rbfw_start_datetime ) . ' ' . gmdate( get_option( 'time_format' ), strtotime( $rbfw_start_time ) )
                    );
                } else {
                    $start_date_label = (
                        $rbfw->get_option_trans( 'rbfw_text_start_date', 'rbfw_basic_translation_settings' )
                        && want_loco_translate() == 'no'
                    )
                        ? $rbfw->get_option_trans( 'rbfw_text_start_date', 'rbfw_basic_translation_settings' )
                        : esc_html__( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' );

                    $item->add_meta_data(
                        esc_html( $start_date_label ),
                        rbfw_date_format( $rbfw_start_datetime )
                    );
                }
                if ( ! empty( $rbfw_end_datetime ) ) {
                    if ( rbfw_booking_has_time( $rbfw_start_time ) && rbfw_booking_has_time( $rbfw_end_time ) ) {
                        $end_date_time_label = (
                            $rbfw->get_option_trans( 'rbfw_text_end_date_and_time', 'rbfw_basic_translation_settings' )
                            && want_loco_translate() == 'no'
                        )
                            ? $rbfw->get_option_trans( 'rbfw_text_end_date_and_time', 'rbfw_basic_translation_settings' )
                            : esc_html__( 'End Date and Time:', 'booking-and-rental-manager-for-woocommerce' );

                        $item->add_meta_data(
                            esc_html( $end_date_time_label ),
                            rbfw_date_format( $rbfw_end_datetime ) . ' ' . gmdate( get_option( 'time_format' ), strtotime( $rbfw_end_time ) )
                        );
                    } else {
                        $end_date_label = (
                            $rbfw->get_option_trans( 'rbfw_text_end_date', 'rbfw_basic_translation_settings' )
                            && want_loco_translate() == 'no'
                        )
                            ? $rbfw->get_option_trans( 'rbfw_text_end_date', 'rbfw_basic_translation_settings' )
                            : esc_html__( 'End Date:', 'booking-and-rental-manager-for-woocommerce' );

                        $item->add_meta_data(
                            esc_html( $end_date_label ),
                            rbfw_date_format( $rbfw_end_datetime )
                        );
                    }
                }
                if ( ! empty( $pickup_location ) ) {
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_pickup_location', esc_html__( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ) ), $pickup_location );
                }
                if ( ! empty( $dropoff_location ) ) {
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_dropoff_location', esc_html__( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ) ), $dropoff_location );
                }
                if ( ! empty( $variation_info ) ) {
                    $variation_content  = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    foreach ( $variation_info as $key => $value ) {
                        $variation_content .= '<tr>';
                        $variation_content .= '<td style="border:1px solid #f5f5f5;"><strong>' . esc_html( $value['field_label'] ?? '' ) . '</strong></td>';
                        $variation_content .= '<td style="border:1px solid #f5f5f5;">' . esc_html( $value['field_value'] ?? '' ) . '</td>';
                        $variation_content .= '</tr>';
                    }
                    $variation_content .= '</table>';
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_variation_information', esc_html__( 'Variation Information', 'booking-and-rental-manager-for-woocommerce' ) ), $variation_content );
                }


                foreach ( $rbfw_bikecarsd_data as $key => $value ){
                    $rent_type = $value['rent_type'];
                    if ( array_key_exists( $rent_type, $rbfw_type_info ) ) {

                        if ( is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) ) {
                            $rbfw_sp_prices = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data_sp', true );
                            if ( isset( $rbfw_sp_prices ) && $rbfw_sp_prices  ) {
                                $sp_price = check_seasonal_price_sd( $values['rbfw_start_date'], $rbfw_sp_prices, $rent_type );
                            }
                        }
                        $type_price = (isset($sp_price) and $sp_price)?$sp_price:$value['price'];

                        $rent_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                        $rent_content .= '<tr>';
                        $rent_content .= '<td style="border:1px solid #f5f5f5;">';
                        $rent_content .= '<strong>' . $rent_type . '</strong>';
                        $rent_content .= '<br>';
                        $rent_content .= '<span>' . $value['short_desc'] . '</span>';
                        $rent_content .= '</td>';
                        $rent_content .= '<td style="border:1px solid #f5f5f5;">';
                        $rent_content .= '(' . wc_price( $type_price ) . ' x ' . $rbfw_type_info[ $rent_type ] . ') = ' . wc_price( $rbfw_type_info[ $rent_type ] * $type_price );
                        $rent_content .= '</td>';
                        $rent_content .= '</tr>';
                        $rent_content .= '</table>';
                        if ( $rbfw_type_info[ $rent_type ] > 0 ):
                            $item->add_meta_data( rbfw_string_return( 'rbfw_text_rent_information', esc_html__( 'Rent Information', 'booking-and-rental-manager-for-woocommerce' ) ), $rent_content );
                        endif;
                    }
                }





                $bikecarsd_service_arr = [];
                if ( ! empty( $rbfw_service_info ) ):
                    foreach ( $rbfw_service_info as $key => $value ):
                        $service_name = $key; //service name
                        if ( array_key_exists( $service_name, $extra_services ) ) { // if service name exist in array
                            $service_price           = $extra_services[ $service_name ]; // get type price from array
                            $service_qty             = $value;
                            $total_service_price     = (float) $service_price * (float) $service_qty;
                            $bikecarsd_service_arr[] = array(
                                $service_name => $service_qty
                            );
                            $rent_service_content    = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                            $rent_service_content    .= '<tr>';
                            $rent_service_content    .= '<td style="border:1px solid #f5f5f5;">';
                            $rent_service_content    .= '<strong>' . $service_name . '</strong>';
                            $rent_service_content    .= '</td>';
                            $rent_service_content    .= '<td style="border:1px solid #f5f5f5;">';
                            $rent_service_content    .= '(' . wc_price( $service_price ) . ' x ' . $service_qty . ') = ' . wc_price( $total_service_price );
                            $rent_service_content    .= '</td>';
                            $rent_service_content    .= '</tr>';
                            $rent_service_content    .= '</table>';
                            if ( $service_qty > 0 ):
                                $item->add_meta_data( rbfw_string_return( 'rbfw_text_extra_service_information', esc_html__( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ) ), $rent_service_content );
                            endif;
                        }
                    endforeach;
                endif;

                if (!empty($rbfw_management_info)){
                    $fee_management_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    foreach ($rbfw_management_info as $key => $value) {
                        $service_label = $key; //service name
                        $service_price = (float)$value['price'];
                        $refundable = ($value['refundable']=='yes')?'( Refundable )':'( Non refundable )';
                        $fee_management_content .= '<tr>';
                        $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                        $fee_management_content .= '<strong>' . $service_label.' '. $refundable . '</strong>';
                        $fee_management_content .= '</td>';
                        $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                        $fee_management_content .=  wc_price($service_price);
                        $fee_management_content .= '</td>';
                        $fee_management_content .= '</tr>';
                    }
                    $fee_management_content .= '</table>';

                    $fee_management_info_label = esc_html__('Fee Management Info:', 'booking-and-rental-manager-for-woocommerce');
                    $item->add_meta_data(
                        esc_html($fee_management_info_label),
                        $fee_management_content
                    );
                }


                $item->add_meta_data( $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings', esc_html__( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) ), wc_price( $rbfw_bikecarsd_duration_price ) );
                if ( $rbfw_bikecarsd_service_price ) {
                    $item->add_meta_data( $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings', esc_html__( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) ), wc_price( $rbfw_bikecarsd_service_price ) );
                }

                $fee_management_label = esc_html__( 'Fee Management Cost', 'booking-and-rental-manager-for-woocommerce' );

                if($rbfw_management_price){
                    $item->add_meta_data(
                        esc_html( $fee_management_label ),
                        wc_price($rbfw_management_price)
                    );
                }


                $security_deposit = rbfw_security_deposit( $rbfw_id, ( (int) $rbfw_bikecarsd_duration_price + (int) $rbfw_bikecarsd_service_price ) );
                if ( $security_deposit['security_deposit_amount'] ) {
                    $item->add_meta_data( $rbfw_security_deposit_label, wc_price( $security_deposit['security_deposit_amount'] ) );
                }
                $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );


            }elseif ( $rbfw_rent_type == 'multiple_items'  ) {

                $pickup_location     = $values['rbfw_pickup_point'] ? $values['rbfw_pickup_point'] : '';
                $dropoff_location    = $values['rbfw_dropoff_point'] ? $values['rbfw_dropoff_point'] : '';
                $rbfw_start_datetime = $values['rbfw_start_datetime'] ? $values['rbfw_start_datetime'] : '';
                $rbfw_start_time     = $values['rbfw_start_time'] ? $values['rbfw_start_time'] : '';
                $rbfw_end_datetime   = $values['rbfw_end_datetime'] ? $values['rbfw_end_datetime'] : '';
                $rbfw_end_time       = $values['rbfw_end_time'] ? $values['rbfw_end_time'] : '';
                $rbfw_ticket_info    = $values['rbfw_ticket_info'] ? $values['rbfw_ticket_info'] : [];

                $rbfw_management_info = $values['rbfw_management_info'] ? $values['rbfw_management_info'] : [];
                $rbfw_management_price = $values['rbfw_management_price'] ? $values['rbfw_management_price'] : [];

                $rbfw_multi_item_price    = $values['rbfw_duration_price'] ? $values['rbfw_duration_price'] : 0;
                $rbfw_service_category_price    = '0';


                $duration_type     = $values['duration_type'] ? $values['duration_type'] : '';
                $duration_qty      = $values['duration_qty'] ? $values['duration_qty'] : '';


                $multiple_items_info = get_post_meta( $rbfw_id, 'multiple_items_info', true ) ? get_post_meta( $rbfw_id, 'multiple_items_info', true ) : array();


                if ( rbfw_booking_has_time( $rbfw_start_time ) ) {
                    $start_date_time_label = (
                        $rbfw->get_option_trans( 'rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings' )
                        && want_loco_translate() == 'no'
                    )
                        ? $rbfw->get_option_trans( 'rbfw_text_start_date_and_time', 'rbfw_basic_translation_settings' )
                        : esc_html__( 'Start Date and Time:', 'booking-and-rental-manager-for-woocommerce' );

                    $item->add_meta_data(
                        esc_html( $start_date_time_label ),
                        rbfw_date_format( $rbfw_start_datetime ) . ' ' . gmdate( get_option( 'time_format' ), strtotime( $rbfw_start_time ) )
                    );
                } else {
                    $start_date_label = (
                        $rbfw->get_option_trans( 'rbfw_text_start_date', 'rbfw_basic_translation_settings' )
                        && want_loco_translate() == 'no'
                    )
                        ? $rbfw->get_option_trans( 'rbfw_text_start_date', 'rbfw_basic_translation_settings' )
                        : esc_html__( 'Start Date:', 'booking-and-rental-manager-for-woocommerce' );

                    $item->add_meta_data(
                        esc_html( $start_date_label ),
                        rbfw_date_format( $rbfw_start_datetime )
                    );
                }
                if ( ! empty( $rbfw_end_datetime ) ) {
                    if ( rbfw_booking_has_time( $rbfw_start_time ) && rbfw_booking_has_time( $rbfw_end_time ) ) {
                        $end_date_time_label = (
                            $rbfw->get_option_trans( 'rbfw_text_end_date_and_time', 'rbfw_basic_translation_settings' )
                            && want_loco_translate() == 'no'
                        )
                            ? $rbfw->get_option_trans( 'rbfw_text_end_date_and_time', 'rbfw_basic_translation_settings' )
                            : esc_html__( 'End Date and Time:', 'booking-and-rental-manager-for-woocommerce' );

                        $item->add_meta_data(
                            esc_html( $end_date_time_label ),
                            rbfw_date_format( $rbfw_end_datetime ) . ' ' . gmdate( get_option( 'time_format' ), strtotime( $rbfw_end_time ) )
                        );
                    } else {
                        $end_date_label = (
                            $rbfw->get_option_trans( 'rbfw_text_end_date', 'rbfw_basic_translation_settings' )
                            && want_loco_translate() == 'no'
                        )
                            ? $rbfw->get_option_trans( 'rbfw_text_end_date', 'rbfw_basic_translation_settings' )
                            : esc_html__( 'End Date:', 'booking-and-rental-manager-for-woocommerce' );

                        $item->add_meta_data(
                            esc_html( $end_date_label ),
                            rbfw_date_format( $rbfw_end_datetime )
                        );
                    }
                }
                if ( ! empty( $pickup_location ) ) {
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_pickup_location', esc_html__( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ) ), $pickup_location );
                }
                if ( ! empty( $dropoff_location ) ) {
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_dropoff_location', esc_html__( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ) ), $dropoff_location );
                }


                $security_deposit = rbfw_security_deposit( $rbfw_id, ( (int) $rbfw_multi_item_price + (int) $rbfw_service_category_price ) );


                if ( $security_deposit['security_deposit_amount'] ) {
                    $item->add_meta_data( $rbfw_security_deposit_label, wc_price( $security_deposit['security_deposit_amount'] ) );
                }


                ?>

                <?php  if ( ! empty( $rbfw_category_wise_info ) ){ ?>
                    <?php foreach ($rbfw_category_wise_info as $key => $value){ ?>
                        <tr>
                            <th><?php echo esc_html($value['cat_title']); ?> </th>
                            <td>
                                <table>
                                    <?php foreach ($value as $item){ ?>
                                        <?php if(isset($item['name'])){ ?>
                                            <tr>
                                                <td><?php echo esc_html($item['name']); ?></td>
                                                <td>
                                                    <?php
                                                    if($item['service_price_type']=='day_wise'){
                                                        echo '('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) . 'x' .esc_html($total_days) .'='.wp_kses(wc_price($item['price']*(int)$item['quantity']*$total_days),rbfw_allowed_html()).')';
                                                    }else{
                                                        echo ('('.wp_kses(wc_price($item['price']),rbfw_allowed_html()). 'x'. esc_html($item['quantity']) .'='.wp_kses(wc_price($item['price']*$item['quantity']),rbfw_allowed_html())).')';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>


                <?php


                $multiple_items_info = $values['multiple_items_info'] ?? [];
                $multiple_items_info_meta = '';

                if ( ! empty( $multiple_items_info ) ) {
                    $selected_items_count = count( $multiple_items_info );
                    $multiple_items_info_meta .= '<table>';
                    foreach ( $multiple_items_info as $key => $value ) {
                        $item_name = esc_html( $value['item_name'] );
                        $item_price = floatval( $value['item_price'] );
                        $item_qty = intval( $value['item_qty'] );
                        $duration_quantity = max( 1, intval( $duration_qty ) );
                        $total_price = ( 1 === $selected_items_count ) ? floatval( $rbfw_multi_item_price ) : $item_price * $item_qty * $duration_quantity;

                        $price_string = '(' .
                            wp_kses( wc_price( $item_price ), rbfw_allowed_html() ) .
                            ' x ' . esc_html( $item_qty ) .
                            ( ( $total_price === $item_price * $item_qty ) ? '' : ' x ' . esc_html( $duration_quantity ) ) .
                            ') = ' . wp_kses( wc_price( $total_price ), rbfw_allowed_html() );

                        $multiple_items_info_meta .= '<tr>';
                        $multiple_items_info_meta .= '<th>' . $item_name . '</th>';
                        $multiple_items_info_meta .= '<td>' . $price_string . '</td>';
                        $multiple_items_info_meta .= '</tr>';
                    }
                    $multiple_items_info_meta .= '</table>';

                    $item->add_meta_data(esc_html__( 'Items Informations', 'booking-and-rental-manager-for-woocommerce' ), $multiple_items_info_meta);
                }


                $rbfw_category_wise_info 	= $values['rbfw_category_wise_info'] ? $values['rbfw_category_wise_info'] : [];




                $rbfw_category_wise_info_meta = '';

                if ( ! empty( $rbfw_category_wise_info ) ) {
                    $rbfw_category_wise_info_meta .= '<table>';
                    foreach ( $rbfw_category_wise_info as $key => $value ) {
                        $rbfw_category_wise_info_meta .= '<tr>';
                        $rbfw_category_wise_info_meta .= '<th>' . esc_html( $value['cat_title'] ) . '</th>';
                        $rbfw_category_wise_info_meta .= '<td>';
                        $rbfw_category_wise_info_meta .= '<table>';

                        foreach ( $value as $single ) {

                            if ( isset( $single['name'] ) ) {
                                $rbfw_category_wise_info_meta .= '<tr>';
                                $rbfw_category_wise_info_meta .= '<td>' . esc_html( $single['name'] ) . '</td>';
                                $rbfw_category_wise_info_meta .= '<td>';

                                if ( $single['service_price_type'] == 'day_wise' ) {
                                    $price = $single['price'];
                                    $quantity = (int) $single['quantity'];
                                    $total_price = $price * $quantity * $total_days;
                                    $rbfw_category_wise_info_meta .= '(' . wp_kses( wc_price( $price ), rbfw_allowed_html() ) . ' x ' . esc_html( $quantity ) . ' x ' . esc_html( $total_days ) . ' = ' . wp_kses( wc_price( $total_price ), rbfw_allowed_html() ) . ')';
                                } else {
                                    $price = $single['price'];
                                    $quantity = (int) $single['quantity'];
                                    $total_price = $price * $quantity;
                                    $rbfw_category_wise_info_meta .= '(' . wp_kses( wc_price( $price ), rbfw_allowed_html() ) . ' x ' . esc_html( $quantity ) . ' = ' . wp_kses( wc_price( $total_price ), rbfw_allowed_html() ) . ')';
                                }

                                $rbfw_category_wise_info_meta .= '</td>';
                                $rbfw_category_wise_info_meta .= '</tr>';
                            }
                        }

                        $rbfw_category_wise_info_meta .= '</table>';
                        $rbfw_category_wise_info_meta .= '</td>';
                        $rbfw_category_wise_info_meta .= '</tr>';
                    }
                    $rbfw_category_wise_info_meta .= '</table>';
                    $item->add_meta_data( esc_html__('Additional Informations','booking-and-rental-manager-for-woocommerce'), $rbfw_category_wise_info_meta );

                }

                if (!empty($rbfw_management_info)){
                    $fee_management_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    foreach ($rbfw_management_info as $key => $value) {
                        $service_label = $key; //service name
                        $service_price = (float)$value['price'];
                        $refundable = ($value['refundable']=='yes')?'( Refundable )':'( Non refundable )';
                        $fee_management_content .= '<tr>';
                        $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                        $fee_management_content .= '<strong>' . $service_label.' '. $refundable . '</strong>';
                        $fee_management_content .= '</td>';
                        $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                        $fee_management_content .=  wc_price($service_price);
                        $fee_management_content .= '</td>';
                        $fee_management_content .= '</tr>';
                    }
                    $fee_management_content .= '</table>';

                    $fee_management_info_label = esc_html__('Fee Management Info:', 'booking-and-rental-manager-for-woocommerce');
                    $item->add_meta_data(
                        esc_html($fee_management_info_label),
                        $fee_management_content
                    );
                }

                $fee_management_label = esc_html__( 'Fee Management Cost', 'booking-and-rental-manager-for-woocommerce' );

                if($rbfw_management_price){
                    $item->add_meta_data(
                        esc_html( $fee_management_label ),
                        wc_price($rbfw_management_price)
                    );
                }

                $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );

            } else {

                $rbfw_extra_service_data = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) ? get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true ) : array();
                if ( ! empty( $rbfw_extra_service_data ) ) {
                    $extra_services = array_column( $rbfw_extra_service_data, 'service_price', 'service_name' );
                } else {
                    $extra_services = array();
                }
                $variation_info      = $values['rbfw_variation_info'] ?: [];
                $rbfw_service_info   = $values['rbfw_service_info'] ?: [];
                $rbfw_service_infos  = $values['rbfw_service_infos'] ?: [];
                $rbfw_ticket_info    = $values['rbfw_ticket_info'] ?: [];
                $start_time          = $values['rbfw_start_time'] ?: '';
                $end_time            = $values['rbfw_end_time'] ?: '';
                $start_datetime      = rbfw_get_datetime( $values['rbfw_start_datetime'], ( $start_time ) ? 'date-time-text' : 'date-text' );
                $end_datetime        = rbfw_get_datetime( $values['rbfw_end_datetime'], ( $end_time ) ? 'date-time-text' : 'date-text' );
                $total_days          = $values['total_days'] ?: '';
                $pickup_location     = $values['rbfw_pickup_point'] ?: '';
                $dropoff_location    = $values['rbfw_dropoff_point'] ?: '';
                $rbfw_item_quantity  = $values['rbfw_item_quantity'] ?: 1;
                $rbfw_duration_price = $values['rbfw_duration_price'] ?: '';
                $rbfw_service_price  = $values['rbfw_service_price'] ?: '';
                $discount_amount     = $values['discount_amount'] ?: '';

                $rbfw_management_info = $values['rbfw_management_info'] ? $values['rbfw_management_info'] : [];
                $rbfw_management_price = $values['rbfw_management_price'] ? $values['rbfw_management_price'] : [];


                $item->add_meta_data( rbfw_string_return( 'rbfw_text_start_date_and_time', esc_html__( 'Start Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ), $start_datetime );
                $item->add_meta_data( rbfw_string_return( 'rbfw_text_end_date_and_time', esc_html__( 'End Date and Time', 'booking-and-rental-manager-for-woocommerce' ) ), $end_datetime );
                if ( ! empty( $pickup_location ) ) {
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_pickup_location', esc_html__( 'Pickup Location', 'booking-and-rental-manager-for-woocommerce' ) ), $pickup_location );
                }
                if ( ! empty( $dropoff_location ) ) {
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_dropoff_location', esc_html__( 'Drop-off Location', 'booking-and-rental-manager-for-woocommerce' ) ), $dropoff_location );
                }
                if ( ! empty( $variation_info ) ) {
                    $variation_content = '';
                    $variation_content .= '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    foreach ( $variation_info as $key => $value ) {
                        $variation_content .= '<tr>';
                        $variation_content .= '<td style="border:1px solid #f5f5f5;"><strong>' . esc_html( $value['field_label'] ?? '' ) . '</strong></td>';
                        $variation_content .= '<td style="border:1px solid #f5f5f5;">' . esc_html( $value['field_value'] ?? '' ) . '</td>';
                        $variation_content .= '</tr>';
                    }
                    $variation_content .= '</table>';
                    $item->add_meta_data( rbfw_string_return( 'rbfw_text_variation_information', esc_html__( 'Variation Information', 'booking-and-rental-manager-for-woocommerce' ) ), $variation_content );
                }
                $item->add_meta_data( rbfw_string_return( 'rbfw_text_item_quantity', esc_html__( 'Item Quantity', 'booking-and-rental-manager-for-woocommerce' ) ), $rbfw_item_quantity );
                $rbfw_service_infos_order = '';
                if ( ! empty( $rbfw_service_infos ) ) {
                    $rbfw_service_infos_order .= '<table>';
                    foreach ( $rbfw_service_infos as $key => $item_parent ) {
                        if ( count( $item_parent ) ) {
                            $rbfw_service_infos_order .= '<tr><th colspan="2" >' . $key . '</th></tr>';
                            foreach ( $item_parent as $key1 => $item_child ) {
                                $rbfw_service_infos_order .= '<tr><td>' . $item_child['name'] . '</td><td>';
                                if ( $item_child['service_price_type'] == 'day_wise' ) {
                                    $rbfw_service_infos_order .= '(' . wc_price( (float) $item_child['price'] ) . 'x' . $item_child['quantity'] . 'x' . $total_days . '=' . wc_price( (float) $item_child['price'] * (int) $item_child['quantity'] * (int) $total_days ) . ')';
                                } else {
                                    $rbfw_service_infos_order .= '(' . wc_price( $item_child['price'] ) . 'x' . $item_child['quantity'] . '=' . wc_price( (float) $item_child['price'] * (int) $item_child['quantity'] ) . ')';
                                }
                                $rbfw_service_infos_order .= '</td></tr>';
                            }
                        }
                    }
                    $rbfw_service_infos_order .= '</table>';
                }

               
                $item->add_meta_data( rbfw_string_return( 'rbfw_text_service_info', esc_html__( 'Service Info', 'booking-and-rental-manager-for-woocommerce' ) ), $rbfw_service_infos_order );




                if ( ! empty( $rbfw_service_info ) ) {
                    foreach ( $rbfw_service_info as $key => $value ) {
                        $service_name = $key; //service name
                        if ( array_key_exists( $service_name, $extra_services ) ) { // if service name exist in array
                            $service_price        = $extra_services[ $service_name ]; // get type price from array
                            $service_qty          = $value;
                            $total_service_price  = (float) $service_price * (float) $service_qty;
                            $rent_service_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                            $rent_service_content .= '<tr>';
                            $rent_service_content .= '<td style="border:1px solid #f5f5f5;">';
                            $rent_service_content .= '<strong>' . $service_name . '</strong>';
                            $rent_service_content .= '</td>';
                            $rent_service_content .= '<td style="border:1px solid #f5f5f5;">';
                            $rent_service_content .= '(' . wc_price( $service_price ) . ' x ' . $service_qty . ') = ' . wc_price( $total_service_price );
                            $rent_service_content .= '</td>';
                            $rent_service_content .= '</tr>';
                            $rent_service_content .= '</table>';
                            if ( $service_qty > 0 ):
                                $item->add_meta_data( rbfw_string_return( 'rbfw_text_extra_service_information', esc_html__( 'Extra Service Information', 'booking-and-rental-manager-for-woocommerce' ) ), $rent_service_content );
                            endif;
                        }
                    }
                }

                if (!empty($rbfw_management_info)){
                    $fee_management_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                    foreach ($rbfw_management_info as $key => $value) {
                        $service_label = $key; //service name
                        $service_price = (float)$value['price'];
                        $refundable = ($value['refundable']=='yes')?'( Refundable )':'( Non refundable )';
                        $fee_management_content .= '<tr>';
                        $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                        $fee_management_content .= '<strong>' . $service_label.' '. $refundable . '</strong>';
                        $fee_management_content .= '</td>';
                        $fee_management_content .= '<td style="border:1px solid #f5f5f5;">';
                        $fee_management_content .=  wc_price($service_price);
                        $fee_management_content .= '</td>';
                        $fee_management_content .= '</tr>';
                    }
                    $fee_management_content .= '</table>';

                    $fee_management_info_label = esc_html__('Fee Management Info:', 'booking-and-rental-manager-for-woocommerce');
                    $item->add_meta_data(
                        esc_html($fee_management_info_label),
                        $fee_management_content
                    );
                }

                $item->add_meta_data( $rbfw->get_option_trans( 'rbfw_text_duration_cost', 'rbfw_basic_translation_settings', esc_html__( 'Duration Cost', 'booking-and-rental-manager-for-woocommerce' ) ), wc_price( $rbfw_duration_price ) );
                if ( $rbfw_service_price ) {
                    $item->add_meta_data( $rbfw->get_option_trans( 'rbfw_text_resource_cost', 'rbfw_basic_translation_settings', esc_html__( 'Resource Cost', 'booking-and-rental-manager-for-woocommerce' ) ), wc_price( $rbfw_service_price ) );
                }
                if($discount_amount){
                    $item->add_meta_data( $rbfw->get_option_trans( 'rbfw_text_discount', 'rbfw_basic_translation_settings', esc_html__( 'Discount', 'booking-and-rental-manager-for-woocommerce' ) ), wc_price( $discount_amount ) );
                }

                if($rbfw_management_price){
                    $fee_management_label = esc_html__( 'Fee Management Cost', 'booking-and-rental-manager-for-woocommerce' );
                    $item->add_meta_data(
                        esc_html( $fee_management_label ),
                        wc_price($rbfw_management_price)
                    );
                }



                $security_deposit = rbfw_security_deposit( $rbfw_id, ( (int) $rbfw_duration_price + (int) $rbfw_service_price ) );
                if ( $security_deposit['security_deposit_amount'] ) {
                    $item->add_meta_data( $rbfw_security_deposit_label, wc_price( $security_deposit['security_deposit_amount'] ) );
                }
                
                $item->add_meta_data( '_rbfw_ticket_info', $rbfw_ticket_info );
            }


            $item->add_meta_data( '_rbfw_id', $rbfw_id );
            $rbfw_regf_info = isset( $values['rbfw_regf_info'] ) ? $values['rbfw_regf_info'] : [];
            if ( ! empty( $rbfw_regf_info ) ) {
                $rbfw_regf_info_content = '<table style="border:1px solid #f5f5f5;margin:0;width: 100%;">';
                foreach ( $rbfw_regf_info as $key => $value ):
                    $the_label = $value['label'];
                    $the_value = $value['value'];
                    if ( is_array( $the_value ) && ! empty( $the_value ) ) {
                        $new_value   = '';
                        $i           = 1;
                        $count_value = count( $the_value );
                        foreach ( $the_value as $val ) {
                            if ( $i < $count_value ) {
                                $new_value .= $val . ', ';
                            } else {
                                $new_value .= $val;
                            }
                            $i ++;
                        }
                        $the_value = $new_value;
                    }
                    if ( ! empty( $the_label ) && ! empty( $the_value ) ) {
                        $rbfw_regf_info_content .= '<tr>';
                        $rbfw_regf_info_content .= '<td style="border:1px solid #f5f5f5;">';
                        $rbfw_regf_info_content .= '<strong>' . $the_label . '</strong>';
                        $rbfw_regf_info_content .= '</td>';
                        $rbfw_regf_info_content .= '<td style="border:1px solid #f5f5f5;">';
                        $rbfw_regf_info_content .= $the_value;
                        $rbfw_regf_info_content .= '</td>';
                        $rbfw_regf_info_content .= '</tr>';
                    }
                endforeach;
                $rbfw_regf_info_content .= '</table>';
                $item->add_meta_data( rbfw_string_return( 'rbfw_text_customer_information', esc_html__( 'Customer Information', 'booking-and-rental-manager-for-woocommerce' ) ), $rbfw_regf_info_content );
            }

        }
        public   function rbfw_cart_ticket_info( $product_id, $rbfw_pickup_start_date, $rbfw_pickup_end_date, $rbfw_pickup_start_time, $rbfw_pickup_end_time, $rbfw_pickup_point, $rbfw_dropoff_point, $rbfw_item_quantity, $rbfw_duration_price, $rbfw_service_price, $total_price, $rbfw_service_info, $variation_info, $discount_type = null, $discount_amount = null, $rbfw_regf_info = array(), $rbfw_service_infos = null, $total_days = 0, $security_deposit = [], $rbfw_management_info =[], $rbfw_management_price=0 ) {
            global $rbfw;
            $rbfw_rent_type  = get_post_meta( $product_id, 'rbfw_item_type', true );
            $names           = [ get_the_title( $product_id ) ];
            $qty             = [ 1 ];
            $count           = count( $names );
            $ticket_type_arr = [];
            $start_datetime  = gmdate( 'Y-m-d H:i', strtotime( $rbfw_pickup_start_date . ' ' . $rbfw_pickup_start_time ) );
            $end_datetime    = gmdate( 'Y-m-d H:i', strtotime( $rbfw_pickup_end_date . ' ' . $rbfw_pickup_end_time ) );
            if ( sizeof( $names ) > 0 ) {
                for ( $i = 0; $i < $count; $i ++ ) {
                    if ( $qty[ $i ] > 0 ) {
                        $ticket_type_arr[ $i ]['ticket_name']             = ! empty( $names[ $i ] ) ? wp_strip_all_tags( $names[ $i ] ) : '';
                        $ticket_type_arr[ $i ]['ticket_price']            = $total_price;
                        $ticket_type_arr[ $i ]['ticket_qty']              = ! empty( $qty[ $i ] ) ? stripslashes( wp_strip_all_tags( $qty[ $i ] ) ) : '';
                        $ticket_type_arr[ $i ]['rbfw_start_date']         = $rbfw_pickup_start_date;
                        $ticket_type_arr[ $i ]['rbfw_start_time']         = $rbfw_pickup_start_time;
                        $ticket_type_arr[ $i ]['rbfw_end_date']           = $rbfw_pickup_end_date;
                        $ticket_type_arr[ $i ]['rbfw_end_time']           = $rbfw_pickup_end_time;
                        $ticket_type_arr[ $i ]['rbfw_start_datetime']     = $start_datetime;
                        $ticket_type_arr[ $i ]['rbfw_end_datetime']       = $end_datetime;
                        $ticket_type_arr[ $i ]['rbfw_pickup_point']       = $rbfw_pickup_point;
                        $ticket_type_arr[ $i ]['rbfw_dropoff_point']      = $rbfw_dropoff_point;
                        $ticket_type_arr[ $i ]['rbfw_item_quantity']      = $rbfw_item_quantity;
                        $ticket_type_arr[ $i ]['rbfw_rent_type']          = $rbfw_rent_type;
                        $ticket_type_arr[ $i ]['rbfw_id']                 = stripslashes( wp_strip_all_tags( $product_id ) );
                        $ticket_type_arr[ $i ]['rbfw_service_info']       = $rbfw_service_info;
                        $ticket_type_arr[ $i ]['rbfw_variation_info']     = $variation_info;
                        $ticket_type_arr[ $i ]['duration_cost']           = $rbfw_duration_price;
                        $ticket_type_arr[ $i ]['service_cost']            = $rbfw_service_price;
                        $ticket_type_arr[ $i ]['service_cost']            = $rbfw_service_price;
                        $ticket_type_arr[ $i ]['discount_type']           = $discount_type;
                        $ticket_type_arr[ $i ]['discount_amount']         = $discount_amount;
                        $ticket_type_arr[ $i ]['rbfw_management_info']    = $rbfw_management_info;
                        $ticket_type_arr[ $i ]['rbfw_management_price']    = $rbfw_management_price;
                        $ticket_type_arr[ $i ]['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
                        $ticket_type_arr[ $i ]['rbfw_regf_info']          = $rbfw_regf_info;
                        $ticket_type_arr[ $i ]['rbfw_service_infos']      = $rbfw_service_infos;
                        $ticket_type_arr[ $i ]['total_days']              = $total_days;
                    }
                }
            }

            return $ticket_type_arr;
        }




        public   function rbfw_cart_multi_items_ticket_info( $product_id, $rbfw_pickup_start_date, $rbfw_pickup_end_date, $rbfw_pickup_start_time, $rbfw_pickup_end_time, $rbfw_pickup_point, $rbfw_dropoff_point, $total_price, $multiple_items_info, $rbfw_category_wise_info, $total_days, $durationQty,  $rbfw_regf_info = array(),  $security_deposit = [],$rbfw_management_info=[],$rbfw_management_price=0,$rbfw_multi_item_price=0) {
            global $rbfw;
            $rbfw_rent_type  = get_post_meta( $product_id, 'rbfw_item_type', true );
            $names           = [ get_the_title( $product_id ) ];
            $qty             = [ 1 ];
            $count           = count( $names );
            $ticket_type_arr = [];
            $start_datetime  = gmdate( 'Y-m-d H:i', strtotime( $rbfw_pickup_start_date . ' ' . $rbfw_pickup_start_time ) );
            $end_datetime    = gmdate( 'Y-m-d H:i', strtotime( $rbfw_pickup_end_date . ' ' . $rbfw_pickup_end_time ) );
            if ( sizeof( $names ) > 0 ) {
                for ( $i = 0; $i < $count; $i ++ ) {
                    if ( $qty[ $i ] > 0 ) {
                        $ticket_type_arr[ $i ]['ticket_name']             = ! empty( $names[ $i ] ) ? wp_strip_all_tags( $names[ $i ] ) : '';
                        $ticket_type_arr[ $i ]['ticket_price']            = $total_price;
                        $ticket_type_arr[ $i ]['duration_cost']           = $rbfw_multi_item_price;
                        $ticket_type_arr[ $i ]['ticket_qty']              = ! empty( $qty[ $i ] ) ? stripslashes( wp_strip_all_tags( $qty[ $i ] ) ) : '';
                        $ticket_type_arr[ $i ]['rbfw_start_date']         = $rbfw_pickup_start_date;
                        $ticket_type_arr[ $i ]['rbfw_start_time']         = $rbfw_pickup_start_time;
                        $ticket_type_arr[ $i ]['rbfw_end_date']           = $rbfw_pickup_end_date;
                        $ticket_type_arr[ $i ]['rbfw_end_time']           = $rbfw_pickup_end_time;
                        $ticket_type_arr[ $i ]['rbfw_start_datetime']     = $start_datetime;
                        $ticket_type_arr[ $i ]['rbfw_end_datetime']       = $end_datetime;
                        $ticket_type_arr[ $i ]['rbfw_pickup_point']       = $rbfw_pickup_point;
                        $ticket_type_arr[ $i ]['rbfw_dropoff_point']      = $rbfw_dropoff_point;
                        $ticket_type_arr[ $i ]['rbfw_rent_type']          = $rbfw_rent_type;
                        $ticket_type_arr[ $i ]['rbfw_management_info']    = $rbfw_management_info;
                        $ticket_type_arr[ $i ]['rbfw_management_price']          = $rbfw_management_price;
                        $ticket_type_arr[ $i ]['rbfw_id']                 = stripslashes( wp_strip_all_tags( $product_id ) );
                        $ticket_type_arr[ $i ]['multiple_items_info']     = $multiple_items_info;
                        $ticket_type_arr[ $i ]['rbfw_category_wise_info'] = $rbfw_category_wise_info;
                        $ticket_type_arr[ $i ]['total_days']              = $total_days;
                        $ticket_type_arr[ $i ]['duration_qty']              = $durationQty;
                        $ticket_type_arr[ $i ]['discount_type']           = '';
                        $ticket_type_arr[ $i ]['discount_amount']         = '';
                        $ticket_type_arr[ $i ]['security_deposit_amount'] = $security_deposit['security_deposit_amount'];
                        $ticket_type_arr[ $i ]['rbfw_regf_info']          = $rbfw_regf_info;

                    }
                }
            }

            return $ticket_type_arr;
        }
        public   function rbfw_change_user_order_status_on_order_status_change( $order_status, $rbfw_id, $order_id ) {
            // Update meta on rbfw_order_meta post type
            rbfw_update_inventory_extra( $rbfw_id, $order_id, $order_status );
            $args = array(
                'post_type'      => 'rbfw_order_meta',
                'posts_per_page' => - 1,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        array(
                            'key'     => 'rbfw_id',
                            'value'   => $rbfw_id,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'rbfw_order_id',
                            'value'   => $order_id,
                            'compare' => '='
                        )
                    )
                )
            );
            $loop = new WP_Query( $args );
            foreach ( $loop->posts as $rbfw_post ) {
                $rbfw_post_id = $rbfw_post->ID;
                update_post_meta( $rbfw_post_id, 'rbfw_order_status', $order_status );
                rbfw_update_inventory( $rbfw_post_id, $order_status );
            }
            // Update meta on rbfw_order post type
            $args = array(
                'post_type'      => 'rbfw_order',
                'posts_per_page' => - 1,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        array(
                            'key'     => 'rbfw_order_id',
                            'value'   => $order_id,
                            'compare' => '='
                        )
                    )
                )
            );
            $loop = new WP_Query( $args );
            foreach ( $loop->posts as $rbfw_post ) {
                $rbfw_post_id = $rbfw_post->ID;
                update_post_meta( $rbfw_post_id, 'rbfw_order_status', $order_status );
                rbfw_update_inventory( $rbfw_post_id, $order_status );
            }
        }
        /**
         * One-time reconcile that rebuilds the rbfw_order mirror / inventory / attendee
         * records for paid orders that were placed before the server-side hooks above were
         * wired in — most importantly redirect/IPN-gateway orders (Opay, PayPal, Stripe redirect)
         * whose customers never landed on the thank-you page, so the booking was never recorded.
         *
         * rbfw_booking_management() is idempotent, so re-running it over existing orders only
         * creates what is missing. The scan walks paid orders newest-first, a bounded batch per
         * admin request (to avoid timeouts on large stores), advancing a date cursor until it
         * reaches the beginning, then marks itself done. Uses wc_get_orders() so it is safe under
         * both legacy and High-Performance Order Storage.
         */
        public function rbfw_backfill_missing_order_mirrors() {
            if ( get_option( 'rbfw_order_mirror_backfill_done' ) === 'yes' ) {
                return;
            }
            $cap = function_exists( 'rbfw_bookings_capability' ) ? rbfw_bookings_capability() : 'manage_options';
            if ( ! current_user_can( $cap ) ) {
                return;
            }
            if ( ! function_exists( 'wc_get_orders' ) ) {
                return;
            }

            $batch  = 30;
            $cursor = (int) get_option( 'rbfw_order_mirror_backfill_cursor', 0 ); // unix ts; 0 = start from newest
            $query  = array(
                'limit'   => $batch,
                'status'  => array( 'processing', 'completed', 'on-hold', 'refunded' ),
                'orderby' => 'date',
                'order'   => 'DESC',
                'return'  => 'ids',
            );
            if ( $cursor > 0 ) {
                $query['date_created'] = '<' . $cursor;
            }
            $order_ids = wc_get_orders( $query );

            if ( empty( $order_ids ) ) {
                update_option( 'rbfw_order_mirror_backfill_done', 'yes' );
                delete_option( 'rbfw_order_mirror_backfill_cursor' );
                return;
            }

            $oldest_ts = $cursor;
            foreach ( $order_ids as $oid ) {
                $order = wc_get_order( $oid );
                if ( ! $order ) {
                    continue;
                }
                $created = $order->get_date_created();
                if ( $created ) {
                    $ts        = $created->getTimestamp();
                    $oldest_ts = ( $oldest_ts === 0 ) ? $ts : min( $oldest_ts, $ts );
                }
                // Idempotent: no-ops when this order already has its mirror, and when it has no rental items.
                $this->rbfw_booking_management( $order->get_id() );
            }

            if ( $oldest_ts > 0 ) {
                update_option( 'rbfw_order_mirror_backfill_cursor', $oldest_ts );
            }
            if ( count( $order_ids ) < $batch ) {
                update_option( 'rbfw_order_mirror_backfill_done', 'yes' );
                delete_option( 'rbfw_order_mirror_backfill_cursor' );
            }
        }
        /**
         * Adapter for hooks that pass a WC_Order object instead of an order id
         * (e.g. woocommerce_store_api_checkout_order_processed).
         */
        public function rbfw_booking_management_from_order( $order ) {
            if ( is_a( $order, 'WC_Order' ) ) {
                $this->rbfw_booking_management( $order->get_id() );
            } elseif ( is_numeric( $order ) ) {
                $this->rbfw_booking_management( (int) $order );
            }
        }
        public  function rbfw_booking_management( $wc_order_id = 0 ) {
            global $rbfw;
            $post = get_post( $wc_order_id );
            if ( $post ) {
                $parent_id = $post->post_parent;
                if ( $parent_id ) {
                    if ( isset( $_COOKIE['parent_id'] ) && ( $_COOKIE['parent_id'] == $parent_id ) ) {
                        return;
                    }
                    setcookie( 'parent_id', $parent_id );
                    $wc_order_id = $parent_id;
                }
            }
            if ( ! $wc_order_id ) {
                return;
            }
            $args       = array(
                'post_type'    => 'rbfw_order',
                'meta_key'     => 'rbfw_link_order_id',
                'meta_value'   => $wc_order_id,
                'meta_compare' => '=',
            );
            $query      = new WP_Query( $args );
            $post_count = $query->post_count;
            if ( $post_count ) {
                return;
            }
            $order = wc_get_order( $wc_order_id );
            $order_status = $order->get_status();
            if ( $order_status != 'failed' ) {
                foreach ( $order->get_items() as $item_id => $item_values ) {
                    $start_date                     = wc_get_order_item_meta( $item_id, 'start_date', true );
                    $end_date                       = wc_get_order_item_meta( $item_id, 'end_date', true );
                    $rbfw_service_price_data_actual = wc_get_order_item_meta( $item_id, '_rbfw_service_price_data_actual', true ) ? wc_get_order_item_meta( $item_id, '_rbfw_service_price_data_actual', true ) : [];
                    $rbfw_id                        = $item_values->get_meta( '_rbfw_id' );
                    if ( get_post_type( $rbfw_id ) == $rbfw->get_cpt_name() ) {
                        $ticket_info     = wc_get_order_item_meta( $item_id, '_rbfw_ticket_info', true ) ? maybe_unserialize( wc_get_order_item_meta( $item_id, '_rbfw_ticket_info', true ) ) : [];
                        $wc_deposit_meta = wc_get_order_item_meta( $item_id, 'wc_deposit_meta', true ) ? maybe_unserialize( wc_get_order_item_meta( $item_id, 'wc_deposit_meta', true ) ) : [];
                        $this->rbfw_prepar_and_add_user_data( $ticket_info, $rbfw_id, $wc_order_id, $start_date, $end_date, $rbfw_service_price_data_actual );
                    }
                }
            }
        }
        public  function rbfw_prepar_and_add_user_data( $ticket_info, $rbfw_id, $wc_order_id, $start_date = null, $end_date = null, $rbfw_service_price_data_actual = array() ) {


            global $rbfw;
            $order           = wc_get_order( $wc_order_id );
            $order_meta      = get_post_meta( $wc_order_id );
            $billing_name    = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $billing_email   = $order->get_billing_email();
            $billing_phone   = $order->get_billing_phone();
            $billing_address = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
            $order_status    = $order->get_status();
            $payment_method  = isset( $order_meta['_payment_method_title'][0] ) ? $order_meta['_payment_method_title'][0] : '';
            $user_id         = isset( $order_meta['_customer_user'][0] ) ? $order_meta['_customer_user'][0] : '';
            foreach ( $ticket_info as $_ticket ) {
                $qty = 1;
                for ( $key = 0; $key < $qty; $key ++ ) {
                    $zdata[ $key ]['rbfw_ticket_total_price'] = ( (float) $_ticket['ticket_price'] * (int) $_ticket['ticket_qty'] );
                    $zdata[ $key ]['rbfw_ticket_qty']         = $_ticket['ticket_qty'];
                    $zdata[ $key ]['discount_amount']         = isset( $_ticket['discount_amount'] ) ? $_ticket['discount_amount'] : 0;
                    $zdata[ $key ]['rbfw_order_id']           = $wc_order_id;
                    $zdata[ $key ]['rbfw_order_status']       = $order_status;
                    $zdata[ $key ]['rbfw_payment_method']     = $payment_method;
                    $zdata[ $key ]['rbfw_user_id']            = $user_id;
                    $zdata[ $key ]['rbfw_billing_name']       = $billing_name;
                    $zdata[ $key ]['rbfw_billing_email']      = $billing_email;
                    $zdata[ $key ]['rbfw_billing_phone']      = $billing_phone;
                    $zdata[ $key ]['rbfw_billing_address']    = $billing_address;
                    $zdata[ $key ]['start_date']              = $start_date;
                    $zdata[ $key ]['end_date']                = $end_date;
                    $zdata[ $key ]['rbfw_id']                 = $rbfw_id;
                    $zdata[ $key ]['rbfw_ticket_info']        = $ticket_info;
                    $meta_data                                = array_merge( $zdata[ $key ] );
                    /*rbfw_order add*/
                    $order_id = $rbfw->rbfw_add_order_data( $meta_data, $ticket_info, $rbfw_service_price_data_actual );
                    /*rbfw_order_mata add and manage inventory*/
                    $order_meta_id = rbfw_add_order_meta_data( $meta_data, $ticket_info );
                    if ( $order_id && $order_meta_id ) {
                        update_post_meta( $order_id, 'rbfw_order_status', $order_status );
                        update_post_meta( $order_meta_id, 'rbfw_order_status', $order_status );
                    }
                }
            }
        }

        /*  for checkout page resort pricing calculation   */
        public function rbfw_resort_price_calculation($product_id, $checkin_date, $checkout_date, $rbfw_room_price_category, $rbfw_room_info, $rbfw_service_info = array(),$rbfw_management_info = array(), $rbfw_request = null) {
            global $rbfw;
            // Basic validation
            if (empty($product_id) || empty($checkin_date) || empty($checkout_date) || empty($rbfw_room_info)) {
                return false;
            }
            // Calculate total days
            $origin     = date_create($checkin_date);
            $target     = date_create($checkout_date);
            $interval   = date_diff($origin, $target);
            $total_days = (int) $interval->format('%a');

            // Check if extra day should be counted
            $count_extra_day = $rbfw->get_option_trans('rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on');
            if ($count_extra_day === 'on') {
                $total_days++;
            }

            // Initialize variables
            $room_price = 0;
            $total_room_price = 0;
            $service_price = 0;
            $management_price = 0;

            // Get room pricing based on category
            $room_data = get_post_meta($product_id, 'rbfw_resort_room_data', true) ?: array();
            if ($rbfw_room_price_category === 'daynight') {
                $room_types = array_column($room_data, 'rbfw_room_daynight_rate', 'room_type');
            } elseif ($rbfw_room_price_category === 'daylong') {
                $room_types = array_column($room_data, 'rbfw_room_daylong_rate', 'room_type');
            } else {
                $room_types = array();
            }

            // Get extra service pricing
            $service_data = get_post_meta($product_id, 'rbfw_extra_service_data', true) ?: array();
            $rbfw_fee_data = get_post_meta($product_id, 'rbfw_fee_data', true) ?: array();
            $extra_services = !empty($service_data) ? array_column($service_data, 'service_price', 'service_name') : array();

            // Loop through selected rooms
            foreach ($rbfw_room_info as $room_type => $quantity) {
                if (!array_key_exists($room_type, $room_types)) continue;

                // Plugin-specific pricing logic
                $mds_enabled = is_plugin_active('multi-day-price-saver-addon-for-wprently/additional-day-price.php');
                $sp_enabled  = is_plugin_active('booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php');
                $tp_enabled  = is_plugin_active('tiered-pricing-addon-wprently/tiered-pricing-addon.php');

                $mds_data = get_post_meta($product_id, 'rbfw_resort_data_mds', true) ?: array();
                $sp_data  = get_post_meta($product_id, 'rbfw_resort_data_sp', true) ?: array();
                $tp_data  = get_post_meta($product_id, 'rbfw_resort_data_tp', true) ?: array();

                if ($mds_enabled && !empty($mds_data)) {
                    $sp_price = check_seasonal_price_resort_mds($total_days, $mds_data, $room_type, $rbfw_room_price_category);
                    $unit_price = ($sp_price !== '0') ? (float)$sp_price : (float)$room_types[$room_type];
                    $room_price += $unit_price;
                    $total_room_price += $unit_price * $total_days * $quantity;

                }elseif ($tp_enabled && !empty($tp_data)) {

                    for($d = 0; $d < $total_days; $d++) {
                        $tp_price = check_price_resort_tp($d, $tp_data, $room_type, $rbfw_room_price_category , $room_types[$room_type]);
                        $room_price += (float)$tp_price;
                    }
                    $total_room_price += $room_price * $quantity;


                } elseif ($sp_enabled && !empty($sp_data)) {
                    $book_dates = getAllDates($checkin_date, $checkout_date);
                    for ($d = 0; $d < $total_days; $d++) {
                        $sp_price = check_seasonal_price_resort($book_dates[$d], $sp_data, $room_type, $rbfw_room_price_category);
                        $unit_price = ($sp_price !== 'not_found') ? (float)$sp_price : (float)$room_types[$room_type];
                        $room_price += $unit_price;
                    }
                    $total_room_price += $room_price * $quantity;

                } else {
                    $unit_price = (float)$room_types[$room_type];
                    $room_price += $unit_price * $quantity;
                    $total_room_price += $unit_price * $quantity * $total_days;
                }
            }

            // Loop through selected services
            foreach ($rbfw_service_info as $service_name => $quantity) {
                if (array_key_exists($service_name, $extra_services)) {
                    $service_price += (float) $extra_services[$service_name] * $quantity;
                }
            }

            $total_service_price = $service_price;
            $subtotal_price = $total_room_price + $total_service_price;

            foreach ($rbfw_fee_data as $fee){
                if (isset($rbfw_management_info[$fee['label']]) && $rbfw_management_info[$fee['label']] == "yes") {

                    $price_type = ! empty( $fee['calculation_type'] ) ? $fee['calculation_type'] : 'fixed';
                    $price = ! empty( $fee['amount'] ) ? (float) $fee['amount'] : 0;
                    $frequency = ! empty( $fee['frequency'] ) ? $fee['frequency'] : 'one-time';

                    if ($price_type === 'percentage') {
                        $management_price += ( $price / 100 ) * $subtotal_price;
                    } else {
                        $management_price += ( $frequency === 'one-time' ) ? $price : $price * $total_days;
                    }

                }
            }

            $subtotal_price += $management_price;
            $total_price = $subtotal_price;

            // Placeholder for future tax calculation
            $percent = 0;

            // Return based on request
            switch ($rbfw_request) {
                case 'rbfw_room_total_price':
                    return $total_price;
                case 'rbfw_room_duration_price':
                    return $total_room_price;
                case 'rbfw_room_service_price':
                    return $total_service_price;
                case 'rbfw_room_management_price':
                    return $management_price;
                case 'rbfw_tax_price':
                    return $percent;
                default:
                    return $total_price;
            }
        }

    }
    new RBFW_Woocommerce();
}




