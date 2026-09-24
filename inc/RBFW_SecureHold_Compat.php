<?php
/**
 * SecureHold WP compatibility.
 *
 * SecureHold WP (3.4.11+) can take a rental's fixed security deposit off the
 * WooCommerce payable total and hold it on the customer's card instead. It does
 * this through the `rbfw_security_deposit` filter, which only covers the
 * server-side totals. This class keeps the rest of the plugin consistent with it:
 *
 *  - Booking forms add the deposit in the browser from hidden fields, so for a
 *    held deposit they would show a total the cart never charges. Those forms
 *    get a note saying the deposit is a card hold, and their calculator stops
 *    adding it.
 *  - SecureHold removes the deposit even where it cannot place a hold: in the
 *    Standalone checkout, which creates no WooCommerce order, and before Stripe
 *    is connected. There the deposit is kept in the charge exactly as core
 *    calculates it, instead of silently disappearing.
 *
 * @package Booking_And_Rental_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_SecureHold_Compat' ) ) {
	class RBFW_SecureHold_Compat {

		const PLUGIN        = 'securehold-security-deposit-holds/securehold-wp-stripe-deposits.php';
		const BRIDGE_OPTION = 'securehold_magepeople_deposit_enabled';
		const MIN_VERSION   = '3.4.11';

		/** Key used to carry core's own deposit through the filter chain. Never leaves it. */
		const CORE_KEY = '_rbfw_core_security_deposit';

		/** @var array|null Per-request Stripe readiness. */
		private static $stripe_status = null;

		public static function init() {
			add_filter( 'rbfw_security_deposit', array( __CLASS__, 'remember_core_deposit' ), 1, 1 );
			add_filter( 'rbfw_security_deposit', array( __CLASS__, 'keep_unheld_deposit' ), 30, 2 );
			add_action( 'rbfw_add_term_condition', array( __CLASS__, 'form_note' ), 5, 1 );
			// The resort form only reaches rbfw_add_term_condition inside its AJAX price
			// segment, too late to load the form script, so it gets the note here instead.
			add_action( 'rbfw_discount_ad', array( __CLASS__, 'resort_form_note' ), 5, 1 );

			// Cart and checkout deposit notice. For carts with rentals it replaces
			// SecureHold's own notice, which cannot tell a held deposit from one this
			// plugin charges.
			add_action( 'init', array( __CLASS__, 'register_store_api_data' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_cart_notice' ), 100 );
			add_action( 'woocommerce_before_cart_totals', array( __CLASS__, 'classic_notices' ) );
			add_action( 'woocommerce_review_order_before_payment', array( __CLASS__, 'classic_checkout_notices' ), 1 );

			// Deposit hold / charged deposit details under each booking on the Bookings page.
			add_action( 'rbfw_booking_list_after_row', array( __CLASS__, 'booking_row' ) );
		}

		/** Whether SecureHold is running with "Use MagePeople security deposit amounts" on. */
		public static function bridge_enabled() {
			return defined( 'SECUREHOLD_PLUGIN_DIR' ) && 'yes' === get_option( self::BRIDGE_OPTION, 'no' );
		}

		/**
		 * Deposit SecureHold takes over for a rental item or its linked product.
		 *
		 * Asks SecureHold's own resolver, the same check its `rbfw_security_deposit`
		 * filter uses before zeroing the charge, so both plugins always agree.
		 *
		 * @param int $item_id Rental item (or linked WooCommerce product) ID.
		 * @return float Deposit amount, or 0 when SecureHold leaves this item alone.
		 */
		public static function claimed_amount( $item_id ) {
			if ( ! self::bridge_enabled() ) {
				return 0.0;
			}
			if ( ! class_exists( 'Securehold_Config_Resolver' ) ) {
				$resolver = SECUREHOLD_PLUGIN_DIR . 'includes/class-securehold-wp-config-resolver.php';
				if ( file_exists( $resolver ) ) {
					require_once $resolver;
				}
			}
			if ( ! method_exists( 'Securehold_Config_Resolver', 'get_magepeople_settings' ) ) {
				return 0.0;
			}

			$settings = Securehold_Config_Resolver::get_magepeople_settings( (int) $item_id );

			return ( is_array( $settings ) && isset( $settings['deposit_amount'] ) ) ? max( 0.0, (float) $settings['deposit_amount'] ) : 0.0;
		}

		/**
		 * Stripe settings SecureHold needs before it can hold anything.
		 *
		 * SecureHold places the hold with its own API keys, on the card the customer
		 * paid with through the official WooCommerce Stripe gateway, so that gateway
		 * must take payments and both must use the same Stripe mode. Reads options
		 * only: no API calls and no gateway loading.
		 *
		 * @return array{gateway_loaded:bool,gateway_enabled:bool,gateway_keys:bool,gateway_mode:string,securehold_keys:bool,securehold_mode:string,modes_match:bool}
		 */
		public static function stripe_status() {
			if ( null !== self::$stripe_status ) {
				return self::$stripe_status;
			}

			$gateway      = get_option( 'woocommerce_stripe_settings', array() );
			$gateway      = is_array( $gateway ) ? $gateway : array();
			$gateway_mode = ( isset( $gateway['testmode'] ) && 'yes' === $gateway['testmode'] ) ? 'test' : 'live';
			$key_prefix   = 'test' === $gateway_mode ? 'test_' : '';
			$sh_mode      = 'live' === get_option( 'securehold_stripe_mode', 'test' ) ? 'live' : 'test';

			self::$stripe_status = array(
				'gateway_loaded'  => defined( 'WC_STRIPE_VERSION' ),
				'gateway_enabled' => isset( $gateway['enabled'] ) && 'yes' === $gateway['enabled'],
				'gateway_keys'    => ! empty( $gateway[ $key_prefix . 'publishable_key' ] ) && ! empty( $gateway[ $key_prefix . 'secret_key' ] ),
				'gateway_mode'    => $gateway_mode,
				'securehold_keys' => '' !== trim( (string) get_option( 'securehold_stripe_' . $sh_mode . '_publishable_key', '' ) )
					&& '' !== trim( (string) get_option( 'securehold_stripe_' . $sh_mode . '_secret_key', '' ) ),
				'securehold_mode' => $sh_mode,
				'modes_match'     => $sh_mode === $gateway_mode,
			);

			return self::$stripe_status;
		}

		/** Whether a claimed deposit will actually be held rather than lost. */
		public static function can_hold() {
			if ( ! class_exists( 'RBFW_Function' ) || ! RBFW_Function::use_wc() ) {
				return false;
			}
			$stripe = self::stripe_status();
			$ready  = $stripe['gateway_loaded'] && $stripe['gateway_enabled'] && $stripe['gateway_keys']
				&& $stripe['securehold_keys'] && $stripe['modes_match'];

			/**
			 * Filters whether SecureHold can place deposit holds on this site.
			 *
			 * When false, deposits SecureHold claims stay in the booking total. Sites
			 * that connect Stripe in a way these option checks do not see can force it.
			 *
			 * @param bool  $ready  Whether WooCommerce checkout, the Stripe gateway and SecureHold's keys are ready.
			 * @param array $stripe Readiness details from stripe_status().
			 */
			return (bool) apply_filters( 'rbfw_securehold_can_hold', $ready, $stripe );
		}

		/**
		 * Deposit that goes on the customer's card instead of into the payment.
		 *
		 * @param int $item_id Rental item ID.
		 * @return float
		 */
		public static function held_amount( $item_id ) {
			$amount = self::claimed_amount( $item_id );

			return ( $amount > 0 && self::can_hold() ) ? $amount : 0.0;
		}

		/**
		 * Carry core's calculated deposit through the filter chain (priority 1).
		 *
		 * @param array $deposit Deposit from rbfw_security_deposit().
		 * @return array
		 */
		public static function remember_core_deposit( $deposit ) {
			if ( is_array( $deposit ) && self::bridge_enabled() ) {
				$deposit[ self::CORE_KEY ] = array(
					'amount' => isset( $deposit['security_deposit_amount'] ) ? $deposit['security_deposit_amount'] : 0,
					'desc'   => isset( $deposit['security_deposit_desc'] ) ? $deposit['security_deposit_desc'] : 0,
				);
			}

			return $deposit;
		}

		/**
		 * Put back a deposit SecureHold removed but cannot hold (priority 30).
		 *
		 * @param array $deposit Filtered deposit.
		 * @param int   $item_id Rental item ID.
		 * @return array
		 */
		public static function keep_unheld_deposit( $deposit, $item_id ) {
			if ( ! is_array( $deposit ) || ! isset( $deposit[ self::CORE_KEY ] ) ) {
				return $deposit;
			}

			$core = $deposit[ self::CORE_KEY ];
			unset( $deposit[ self::CORE_KEY ] );

			$removed = (float) $core['amount'] > 0 && empty( $deposit['security_deposit_amount'] );
			// PRO's "included in price" policy zeroes the charge on purpose; leave it to PRO.
			$included = isset( $deposit['security_deposit_price_mode'] ) && 'included' === $deposit['security_deposit_price_mode'];

			if ( $removed && ! $included && self::claimed_amount( $item_id ) > 0 && ! self::can_hold() ) {
				$deposit['security_deposit_amount'] = $core['amount'];
				$deposit['security_deposit_desc']   = $core['desc'];
			}

			return $deposit;
		}

		/** Whether the WooCommerce cart holds at least one rental. */
		private static function cart_has_rentals() {
			if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
				return false;
			}
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				if ( ! empty( $cart_item['rbfw_id'] ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Deposit notices for the current cart.
		 *
		 * "held": the amount SecureHold will authorize, from SecureHold's own cart
		 * calculation, only when the hold can really be placed. "charged": deposits
		 * this plugin adds to the payment, the same sum custom_taxable_fee() bills as
		 * the "Security Deposit" cart fee.
		 *
		 * @return array<int,array{type:string,html:string}>
		 */
		public static function cart_deposit_notices() {
			if ( ! self::bridge_enabled() || ! self::cart_has_rentals() ) {
				return array();
			}

			$cart    = WC()->cart->get_cart();
			$charged = 0.0;
			foreach ( $cart as $cart_item ) {
				if ( empty( $cart_item['rbfw_id'] ) || empty( $cart_item['rbfw_ticket_info'] ) || ! is_array( $cart_item['rbfw_ticket_info'] ) ) {
					continue;
				}
				foreach ( $cart_item['rbfw_ticket_info'] as $ticket ) {
					$charged += isset( $ticket['security_deposit_amount'] ) ? (float) $ticket['security_deposit_amount'] : 0.0;
				}
			}

			$held = 0.0;
			if ( self::can_hold() ) {
				if ( ! class_exists( 'Securehold_Deposit_Computation_Service' ) ) {
					$service = SECUREHOLD_PLUGIN_DIR . 'includes/services/class-securehold-wp-computation-service.php';
					if ( file_exists( $service ) ) {
						require_once $service;
					}
				}
				if ( method_exists( 'Securehold_Deposit_Computation_Service', 'compute_for_cart' ) ) {
					$result = Securehold_Deposit_Computation_Service::compute_for_cart( $cart );
					$held   = ! empty( $result['has_hold'] ) ? max( 0.0, (float) $result['total_amount'] ) : 0.0;
				}
			}

			$notices = array();
			if ( $held > 0 ) {
				$notices[] = array(
					'type' => 'held',
					'html' => sprintf(
						/* translators: %s: formatted deposit amount. */
						__( 'A security deposit of %s will be held on your card when you pay by card. This is an authorization only, not an additional charge.', 'booking-and-rental-manager-for-woocommerce' ),
						'<strong>' . wc_price( $held ) . '</strong>'
					),
				);
			}
			if ( $charged > 0 ) {
				$notices[] = array(
					'type' => 'charged',
					'html' => sprintf(
						/* translators: %s: formatted deposit amount. */
						__( 'The refundable security deposit of %s is included in your total.', 'booking-and-rental-manager-for-woocommerce' ),
						'<strong>' . wc_price( $charged ) . '</strong>'
					),
				);
			}

			return $notices;
		}

		/** Expose the notices on the Store API cart, for the Cart and Checkout blocks. */
		public static function register_store_api_data() {
			if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
				return;
			}

			woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => 'cart',
					'namespace'       => 'rbfw_securehold',
					'data_callback'   => function () {
						return array( 'notices' => self::cart_deposit_notices() );
					},
					'schema_callback' => function () {
						return array(
							'notices' => array(
								'description' => __( 'Security deposit notices for the cart.', 'booking-and-rental-manager-for-woocommerce' ),
								'type'        => 'array',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
								'items'       => array(
									'type'       => 'object',
									'properties' => array(
										'type' => array( 'type' => 'string' ),
										'html' => array( 'type' => 'string' ),
									),
								),
							),
						);
					},
					'schema_type'     => ARRAY_A,
				)
			);
		}

		/** Load the block notice on the cart and checkout pages, instead of SecureHold's. */
		public static function enqueue_cart_notice() {
			if ( ! self::bridge_enabled() || ! function_exists( 'is_cart' ) || ! ( is_cart() || is_checkout() ) || is_wc_endpoint_url() ) {
				return;
			}

			if ( self::cart_has_rentals() ) {
				wp_dequeue_script( 'securehold-checkout-blocks-notice' );
			}

			$script = RBFW_PLUGIN_DIR . '/assets/mp_script/rbfw_securehold_cart_notice.js';
			wp_enqueue_script(
				'rbfw-securehold-cart-notice',
				RBFW_PLUGIN_URL . '/assets/mp_script/rbfw_securehold_cart_notice.js',
				array( 'wp-element', 'wp-plugins', 'wc-blocks-checkout' ),
				file_exists( $script ) ? filemtime( $script ) : false,
				true
			);
		}

		/** Render the notices in the classic cart and checkout templates. */
		public static function classic_notices() {
			foreach ( self::cart_deposit_notices() as $notice ) {
				?>
				<div class="rbfw-securehold-deposit-note rbfw-deposit-notice-<?php echo esc_attr( $notice['type'] ); ?>">
					<i class="fas <?php echo esc_attr( 'held' === $notice['type'] ? 'fa-lock' : 'fa-info-circle' ); ?>" aria-hidden="true"></i>
					<span><?php echo wp_kses_post( $notice['html'] ); ?></span>
				</div>
				<?php
			}
		}

		/**
		 * Classic checkout: show the notices and take SecureHold's out for rental
		 * carts, whichever of the two payment hooks its position setting uses.
		 */
		public static function classic_checkout_notices() {
			if ( self::bridge_enabled() && self::cart_has_rentals() ) {
				global $wp_filter;
				foreach ( array( 'woocommerce_review_order_before_payment', 'woocommerce_review_order_after_payment' ) as $hook ) {
					if ( empty( $wp_filter[ $hook ] ) ) {
						continue;
					}
					foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
						foreach ( $callbacks as $callback ) {
							if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] )
								&& 'Securehold_Frontend_Manager' === get_class( $callback['function'][0] )
								&& 'display_checkout_message' === $callback['function'][1] ) {
								remove_action( $hook, $callback['function'], $priority );
							}
						}
					}
				}
			}

			self::classic_notices();
		}

		/**
		 * Latest SecureHold hold recorded for a WooCommerce order.
		 *
		 * @param int $order_id WooCommerce order ID.
		 * @return object|null Row from the securehold_holds table.
		 */
		private static function order_hold( $order_id ) {
			global $wpdb;
			$table = $wpdb->prefix . 'securehold_holds';

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- SecureHold's own table; no API exposes it.
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1", $order_id ) );
		}

		/**
		 * Deposit this plugin charged with a WooCommerce order (the "Security Deposit" fee).
		 *
		 * @param WC_Order $order Order.
		 * @return float
		 */
		private static function order_charged_deposit( $order ) {
			$amount = 0.0;
			foreach ( $order->get_items() as $item ) {
				$tickets = $item->get_meta( '_rbfw_ticket_info' );
				if ( ! is_array( $tickets ) ) {
					continue;
				}
				foreach ( $tickets as $ticket ) {
					$amount += isset( $ticket['security_deposit_amount'] ) ? (float) $ticket['security_deposit_amount'] : 0.0;
				}
			}

			return $amount;
		}

		/**
		 * Bookings page: show the deposit hold under a WooCommerce booking, what
		 * happens to it next, and, when the deposit was charged instead, how it is
		 * refunded.
		 *
		 * @param array $row Normalised booking row.
		 */
		public static function booking_row( $row ) {
			if ( ! defined( 'SECUREHOLD_PLUGIN_DIR' ) || empty( $row['wc_order_id'] ) || ! function_exists( 'wc_get_order' ) ) {
				return;
			}

			$order = wc_get_order( (int) $row['wc_order_id'] );
			if ( ! $order ) {
				return;
			}

			$hold    = self::order_hold( $order->get_id() );
			$charged = ( ! $hold && self::bridge_enabled() ) ? self::order_charged_deposit( $order ) : 0.0;
			if ( ! $hold && $charged <= 0 ) {
				return;
			}

			$currency = array( 'currency' => $order->get_currency() );
			$badge    = '';
			$state    = '';
			$detail   = '';

			if ( $hold ) {
				$amount   = (float) $hold->amount;
				$captured = (float) $hold->captured_amount;
				$date     = function ( $mysql ) {
					return $mysql ? mysql2date( get_option( 'date_format' ), $mysql ) : '';
				};

				switch ( $hold->status ) {
					case 'authorized':
						$badge  = __( 'Held, not charged', 'booking-and-rental-manager-for-woocommerce' );
						$state  = 'held';
						$detail = 'yes' === get_option( 'securehold_auto_release', 'no' )
							/* translators: %s: release date. */
							? sprintf( __( 'Released automatically on %s unless you capture it for damages in SecureHold. Nothing needs refunding.', 'booking-and-rental-manager-for-woocommerce' ), $date( $hold->expires_at ) )
							/* translators: %s: hold expiry date. */
							: sprintf( __( 'Auto-release is off in SecureHold: capture or release it there before %s. Stripe cancels an uncaptured hold after about 7 days.', 'booking-and-rental-manager-for-woocommerce' ), $date( $hold->expires_at ) );
						break;
					case 'captured':
						$state = 'captured';
						if ( $captured > 0 && $captured < $amount ) {
							$badge  = __( 'Partly captured', 'booking-and-rental-manager-for-woocommerce' );
							$detail = sprintf(
								/* translators: 1: captured amount, 2: held amount. */
								__( '%1$s of the %2$s hold was charged for damages; the rest was released. Refund any of it from Stripe if needed.', 'booking-and-rental-manager-for-woocommerce' ),
								wp_strip_all_tags( wc_price( $captured, $currency ) ),
								wp_strip_all_tags( wc_price( $amount, $currency ) )
							);
						} else {
							$badge  = __( 'Captured', 'booking-and-rental-manager-for-woocommerce' );
							$detail = __( 'The deposit was charged for damages. Refund it from Stripe if needed.', 'booking-and-rental-manager-for-woocommerce' );
						}
						break;
					case 'released':
						$badge  = __( 'Released', 'booking-and-rental-manager-for-woocommerce' );
						$state  = 'released';
						/* translators: %s: release date. */
						$detail = sprintf( __( 'Released on %s. The customer was never charged.', 'booking-and-rental-manager-for-woocommerce' ), $date( $hold->released_at ? $hold->released_at : $hold->updated_at ) );
						break;
					case 'failed':
						$badge  = __( 'Hold failed', 'booking-and-rental-manager-for-woocommerce' );
						$state  = 'failed';
						$reason = (string) $order->get_meta( '_securehold_hold_failure_reason', true );
						$detail = $reason
							/* translators: %s: failure reason code. */
							? sprintf( __( 'No deposit is secured for this booking (%s).', 'booking-and-rental-manager-for-woocommerce' ), $reason )
							: __( 'No deposit is secured for this booking.', 'booking-and-rental-manager-for-woocommerce' );
						break;
					default:
						$badge  = ucfirst( (string) $hold->status );
						$state  = 'pending';
						$detail = __( 'SecureHold has not placed the hold yet.', 'booking-and-rental-manager-for-woocommerce' );
				}
				$figure = wc_price( $amount, $currency );
				$link   = current_user_can( 'manage_woocommerce' ) ? admin_url( 'admin.php?page=securehold-deposit-details&deposit_id=' . (int) $hold->id ) : '';
				$label  = __( 'Security deposit hold', 'booking-and-rental-manager-for-woocommerce' );
				$link_t = __( 'Manage in SecureHold', 'booking-and-rental-manager-for-woocommerce' );
			} else {
				$badge  = __( 'Charged with the order', 'booking-and-rental-manager-for-woocommerce' );
				$state  = 'charged';
				$detail = __( 'This deposit could not be held on the card, so it was paid with the booking. It is not refunded automatically: refund it from the order after the rental.', 'booking-and-rental-manager-for-woocommerce' );
				$figure = wc_price( $charged, $currency );
				$link   = current_user_can( 'edit_shop_orders' ) ? $order->get_edit_order_url() : '';
				$label  = __( 'Security deposit', 'booking-and-rental-manager-for-woocommerce' );
				$link_t = __( 'Open order', 'booking-and-rental-manager-for-woocommerce' );
			}

			$colspan = defined( 'RBFW_Booking_List_Table::COLUMN_COUNT' ) ? (int) RBFW_Booking_List_Table::COLUMN_COUNT : 7;
			?>
			<tr class="rbfw-sh-booking-row">
				<td colspan="<?php echo esc_attr( (string) $colspan ); ?>">
					<div class="rbfw-sh-booking">
						<span class="dashicons <?php echo esc_attr( $hold ? 'dashicons-lock' : 'dashicons-money-alt' ); ?>" aria-hidden="true"></span>
						<strong><?php echo esc_html( $label ); ?></strong>
						<span class="rbfw-sh-amount"><?php echo wp_kses_post( $figure ); ?></span>
						<span class="rbfw-sh-badge is-<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $badge ); ?></span>
						<span class="rbfw-sh-detail"><?php echo esc_html( $detail ); ?></span>
						<?php if ( $link ) : ?>
							<a class="rbfw-sh-link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $link_t ); ?></a>
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<?php
		}

		/**
		 * Card-hold note for the single-day, multi-day and multiple-items forms.
		 *
		 * @param int $item_id Rental item ID.
		 */
		public static function form_note( $item_id ) {
			if ( 'resort' !== get_post_meta( (int) $item_id, 'rbfw_item_type', true ) ) {
				self::render_note( $item_id );
			}
		}

		/**
		 * Card-hold note for the resort form.
		 *
		 * @param int $item_id Rental item ID.
		 */
		public static function resort_form_note( $item_id ) {
			if ( 'resort' === get_post_meta( (int) $item_id, 'rbfw_item_type', true ) ) {
				self::render_note( $item_id );
			}
		}

		/**
		 * Tell the customer the deposit is held, and switch the form calculator off it.
		 *
		 * @param int $item_id Rental item ID.
		 */
		private static function render_note( $item_id ) {
			$item_id = (int) $item_id;
			$amount  = $item_id ? self::held_amount( $item_id ) : 0.0;
			if ( $amount <= 0 ) {
				return;
			}

			$script = RBFW_PLUGIN_DIR . '/assets/mp_script/rbfw_securehold_compat.js';
			wp_enqueue_script(
				'rbfw-securehold-compat',
				RBFW_PLUGIN_URL . '/assets/mp_script/rbfw_securehold_compat.js',
				array(),
				file_exists( $script ) ? filemtime( $script ) : false,
				true
			);

			$label = get_post_meta( $item_id, 'rbfw_security_deposit_label', true );
			$label = $label ? $label : __( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' );
			?>
			<div class="rbfw-securehold-deposit-note" data-rbfw-item="<?php echo esc_attr( $item_id ); ?>">
				<i class="fas fa-lock" aria-hidden="true"></i>
				<span>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: 1: security deposit label, 2: formatted deposit amount. */
							__( '%1$s: %2$s is held on your card at checkout. It is not added to the amount you pay.', 'booking-and-rental-manager-for-woocommerce' ),
							esc_html( $label ),
							wc_price( $amount )
						)
					);
					?>
				</span>
			</div>
			<?php
		}
	}

	RBFW_SecureHold_Compat::init();
}
