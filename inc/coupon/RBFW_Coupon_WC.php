<?php
/**
 * WooCommerce application layer — bridges the unified coupon engine into WooCommerce's own
 * coupon system so codes work in EVERY WooCommerce surface: the classic cart/checkout, the
 * Cart/Checkout Blocks, and the Store API. All of those validate a code by constructing a
 * WC_Coupon, which fires `woocommerce_get_shop_coupon_data`; we answer that filter with a
 * "virtual" coupon (no shop_coupon post needed) whose amount is computed by OUR engine.
 *
 * The engine stays authoritative: it decides validity (targeting, dates, spend, usage limits,
 * eligibility) and the exact discount amount. WooCommerce is only the delivery mechanism, so the
 * discount renders natively in the cart, Block checkout, order, emails and reports.
 *
 * The `rbfw_order` mirror / thank-you / PDF read the `_rbfw_ticket_info` snapshot (captured at
 * add-to-cart, pre-coupon), which a native coupon does NOT touch — so we still rewrite that
 * snapshot at order creation so revenue reporting reflects the discount.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Coupon_WC' ) ) {
	class RBFW_Coupon_WC {

		/** @var array code(normalized) => rejection reason, for friendlier error messaging. */
		protected static $reasons = array();

		/** @var array|null Memoized applied-coupon resolution during order creation. */
		protected $order_resolution = null;

		public function __construct() {
			// THE bridge: make our codes resolvable as WooCommerce coupons everywhere.
			add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'virtual_coupon' ), 10, 3 );
			add_filter( 'woocommerce_coupon_error', array( $this, 'coupon_error' ), 10, 3 );

			// Auto-apply no-code rules onto the cart.
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'auto_apply' ), 5 );

			// Keep the rbfw_order mirror / thank-you / PDF honest (they read _rbfw_ticket_info).
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'persist_line_coupon' ), 95, 4 );
			add_action( 'woocommerce_checkout_create_order', array( $this, 'persist_order_meta' ), 20, 2 );
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'persist_order_meta_and_save' ), 20 );

			// Record usage on paid (idempotent per coupon + order).
			add_action( 'woocommerce_payment_complete', array( $this, 'record_usage_once' ) );
			add_action( 'woocommerce_order_status_processing', array( $this, 'record_usage_once' ) );
			add_action( 'woocommerce_order_status_completed', array( $this, 'record_usage_once' ) );
		}

		protected function active() {
			return RBFW_Function::use_wc() && RBFW_Coupon_Engine::is_enabled();
		}

		protected function context() {
			return RBFW_Coupon_Context::from_wc_cart();
		}

		/* -------------------------------------------------------------------------
		 * Virtual coupon bridge
		 * ---------------------------------------------------------------------- */

		/**
		 * Answer WooCommerce's coupon-data lookup for our codes.
		 *
		 * @param mixed  $data   false unless another filter already provided data.
		 * @param string $code   The (WC-normalized, lowercase) coupon code being looked up.
		 * @param mixed  $coupon The WC_Coupon being constructed.
		 * @return array|false   Virtual coupon data, or false to let WooCommerce handle it.
		 */
		public function virtual_coupon( $data, $code, $coupon = null ) {
			// Respect an existing real coupon / another integration.
			if ( false !== $data ) {
				return $data;
			}
			if ( ! $this->active() ) {
				return false;
			}

			$rbfw = RBFW_Coupon::load_by_code( $code );
			if ( ! $rbfw ) {
				return false; // not one of ours → let WooCommerce say "does not exist".
			}

			$ctx = $this->context();
			if ( empty( $ctx['items'] ) ) {
				return $this->reject( $code, esc_html__( 'Add a rental to your cart before applying this coupon.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$valid = RBFW_Coupon_Engine::validate( $rbfw, $ctx );
			if ( empty( $valid['valid'] ) ) {
				return $this->reject( $code, $valid['reason'] );
			}

			$discount = RBFW_Coupon_Engine::calculate_discount( $rbfw, $ctx );
			if ( $discount['total'] <= 0 ) {
				return $this->reject( $code, esc_html__( 'This coupon does not apply to the item(s) in your cart.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			// A fixed_cart coupon of the exact engine-computed amount keeps OUR rules authoritative
			// (targeting, caps, base = rental minus mandatory fees) — WooCommerce just subtracts it.
			return array(
				'id'                   => $rbfw->get_id(),
				'amount'               => $discount['total'],
				'discount_type'        => 'fixed_cart',
				'individual_use'       => ! $rbfw->allows_combine(),
				'usage_limit'          => 0, // enforced by our engine, not WooCommerce.
				'usage_limit_per_user' => 0,
				'product_ids'          => array(),
				'excluded_product_ids' => array(),
				'exclude_sale_items'   => false,
				'minimum_amount'       => '',
				'maximum_amount'       => '',
				'free_shipping'        => false,
			);
		}

		protected function reject( $code, $reason ) {
			self::$reasons[ RBFW_Coupon::normalize_code( $code ) ] = $reason;
			return false;
		}

		/**
		 * Replace WooCommerce's generic "does not exist" with our real reason when the code is
		 * one of ours but was rejected (expired, not applicable, usage limit, …).
		 */
		public function coupon_error( $err, $err_code, $coupon = null ) {
			if ( is_object( $coupon ) && method_exists( $coupon, 'get_code' ) ) {
				$key = RBFW_Coupon::normalize_code( $coupon->get_code() );
				if ( ! empty( self::$reasons[ $key ] ) ) {
					return self::$reasons[ $key ];
				}
			}
			return $err;
		}

		/* -------------------------------------------------------------------------
		 * Automatic (no-code) rules
		 * ---------------------------------------------------------------------- */

		public function auto_apply( $cart ) {
			static $running = false;
			if ( $running || ! $this->active() || ! is_object( $cart ) ) {
				return;
			}
			if ( is_admin() && ! wp_doing_ajax() ) {
				return;
			}

			$autos = RBFW_Coupon::get_active_auto_coupons();
			if ( empty( $autos ) ) {
				return;
			}

			$ctx = $this->context();
			if ( empty( $ctx['items'] ) ) {
				return;
			}

			$running = true;
			foreach ( $autos as $c ) {
				$code = $c->get_code();
				if ( $cart->has_discount( wc_format_coupon_code( $code ) ) ) {
					continue;
				}
				$valid = RBFW_Coupon_Engine::validate( $c, $ctx );
				if ( empty( $valid['valid'] ) ) {
					continue;
				}
				$d = RBFW_Coupon_Engine::calculate_discount( $c, $ctx );
				if ( $d['total'] > 0 ) {
					$cart->apply_coupon( $code );
				}
			}
			$running = false;
		}

		/* -------------------------------------------------------------------------
		 * Order persistence (mirror / usage source of truth)
		 * ---------------------------------------------------------------------- */

		/**
		 * Resolve every applied coupon that maps to an rbfw_coupon, with a per-line breakdown.
		 * Memoized for the duration of order creation.
		 *
		 * @return array{applied:array,per_line:array<string,float>,total:float}
		 */
		protected function cart_resolution() {
			if ( null !== $this->order_resolution ) {
				return $this->order_resolution;
			}
			$empty = array( 'applied' => array(), 'per_line' => array(), 'total' => 0.0 );

			if ( ! $this->active() || ! function_exists( 'WC' ) || ! WC()->cart ) {
				$this->order_resolution = $empty;
				return $empty;
			}

			$ctx      = $this->context();
			$applied  = array();
			$per_line = array();

			foreach ( WC()->cart->get_applied_coupons() as $code ) {
				$rbfw = RBFW_Coupon::load_by_code( $code );
				if ( ! $rbfw ) {
					continue; // a genuine WooCommerce coupon — not ours.
				}
				$d = RBFW_Coupon_Engine::calculate_discount( $rbfw, $ctx );
				if ( $d['total'] <= 0 ) {
					continue;
				}
				foreach ( $d['per_line'] as $key => $amt ) {
					$per_line[ $key ] = ( isset( $per_line[ $key ] ) ? $per_line[ $key ] : 0 ) + (float) $amt;
				}
				$applied[] = array( 'id' => $rbfw->get_id(), 'code' => $rbfw->get_code(), 'amount' => $d['total'] );
			}

			$this->order_resolution = array(
				'applied'  => $applied,
				'per_line' => $per_line,
				'total'    => round( array_sum( $per_line ), wc_get_price_decimals() ),
			);
			return $this->order_resolution;
		}

		protected function applied_codes( $res ) {
			return implode( ', ', wp_list_pluck( $res['applied'], 'code' ) );
		}

		/** Classic checkout: order not yet saved, update_meta_data suffices. */
		public function persist_order_meta( $order, $data = array() ) {
			if ( ! $this->active() || ! is_a( $order, 'WC_Order' ) ) {
				return;
			}
			$res = $this->cart_resolution();
			if ( $res['total'] <= 0 || empty( $res['applied'] ) ) {
				return;
			}
			$order->update_meta_data( 'rbfw_coupon_code', $this->applied_codes( $res ) );
			$order->update_meta_data( 'rbfw_coupon_discount', (float) $res['total'] );
			$order->update_meta_data( 'rbfw_coupon_applied', $res['applied'] );
		}

		/** Block / Store API: order already persisted, so save() explicitly. */
		public function persist_order_meta_and_save( $order ) {
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}
			$this->persist_order_meta( $order );
			if ( $order->get_id() ) {
				$order->save();
			}
		}

		/**
		 * Rewrite the `_rbfw_ticket_info` snapshot for a discounted line so the rbfw_order mirror,
		 * thank-you page and PDF report the discounted price. Runs at 95 (after the plugin's own
		 * rbfw_add_order_item_data at 90).
		 */
		public function persist_line_coupon( $item, $cart_item_key, $values, $order ) {
			if ( ! $this->active() || ! is_object( $item ) ) {
				return;
			}
			$res   = $this->cart_resolution();
			$share = isset( $res['per_line'][ $cart_item_key ] ) ? (float) $res['per_line'][ $cart_item_key ] : 0.0;
			if ( $share <= 0 ) {
				return;
			}

			$codes = $this->applied_codes( $res );
			$item->update_meta_data( '_rbfw_coupon_code', $codes );
			$item->update_meta_data( '_rbfw_coupon_discount', $share );

			$ticket_info = $item->get_meta( '_rbfw_ticket_info', true );
			if ( is_array( $ticket_info ) && $ticket_info ) {
				$item->update_meta_data( '_rbfw_ticket_info', self::discount_ticket_info( $ticket_info, $share, $codes ) );
			}
		}

		/**
		 * Subtract a line's coupon share from its ticket_info snapshot, split across rows in
		 * proportion to (ticket_price * ticket_qty), and stamp the coupon fields for display.
		 */
		public static function discount_ticket_info( array $ticket_info, $share, $codes ) {
			$decimals = wc_get_price_decimals();

			$weights = array();
			foreach ( $ticket_info as $i => $row ) {
				$price         = isset( $row['ticket_price'] ) ? (float) $row['ticket_price'] : 0.0;
				$qty           = isset( $row['ticket_qty'] ) ? max( 1, (int) $row['ticket_qty'] ) : 1;
				$weights[ $i ] = max( 0, $price * $qty );
			}
			$total_weight = array_sum( $weights );

			foreach ( $ticket_info as $i => $row ) {
				$row_share = ( $total_weight > 0 ) ? $share * ( $weights[ $i ] / $total_weight ) : 0.0;
				$qty       = isset( $row['ticket_qty'] ) ? max( 1, (int) $row['ticket_qty'] ) : 1;
				$price     = isset( $row['ticket_price'] ) ? (float) $row['ticket_price'] : 0.0;

				$new_price = max( 0, $price - ( $row_share / $qty ) );

				$ticket_info[ $i ]['ticket_price']         = round( $new_price, $decimals );
				$ticket_info[ $i ]['rbfw_coupon_code']     = $codes;
				$ticket_info[ $i ]['rbfw_coupon_discount'] = round( $row_share, $decimals );
			}

			return $ticket_info;
		}

		/* -------------------------------------------------------------------------
		 * Usage recording (idempotent per coupon + order)
		 * ---------------------------------------------------------------------- */

		public function record_usage_once( $order_id ) {
			if ( ! function_exists( 'wc_get_order' ) ) {
				return;
			}
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$applied = $order->get_meta( 'rbfw_coupon_applied' );
			if ( empty( $applied ) || ! is_array( $applied ) ) {
				return;
			}

			$user_id = (int) $order->get_customer_id();
			$email   = (string) $order->get_billing_email();

			foreach ( $applied as $a ) {
				$cid = isset( $a['id'] ) ? absint( $a['id'] ) : 0;
				$amt = isset( $a['amount'] ) ? (float) $a['amount'] : 0.0;
				if ( $cid ) {
					RBFW_Coupon_Usage::record( $cid, array( 'type' => 'order', 'id' => $order->get_id() ), $user_id, $email, $amt );
				}
			}
		}
	}

	new RBFW_Coupon_WC();
}
