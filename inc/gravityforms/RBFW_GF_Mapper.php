<?php
	/**
	 * The bridge proper: a verified Gravity Forms entry becomes rental
	 * registration data.
	 *
	 * The host plugin already carries a label/value structure called
	 * `rbfw_regf_info` the full length of the system — cart item, order line item,
	 * booking record, admin order screen, customer booking view, and the booking
	 * emails all read it (see inc/rbfw_functions.php:78 and
	 * inc/rbfw_order_meta.php:373). Writing the Gravity answers into that shape
	 * means every one of those surfaces displays them with no new display code.
	 *
	 * The merge runs at priority 95 on woocommerce_add_cart_item_data, i.e. after
	 * the host plugin's own handler at 90 has built the ticket rows. Merging into
	 * the finished structure — rather than racing it at a lower priority — is what
	 * keeps the Pro plugin's own registration fields intact instead of replacing
	 * them.
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'RBFW_GF_Mapper' ) ) {

		class RBFW_GF_Mapper {

			/** Field types that carry no answer worth storing. */
			const SKIP_TYPES = array( 'page', 'html', 'section', 'captcha', 'password', 'total' );

			public function __construct() {
				// Block the booking when answers are required but absent. Priority 30
				// sits after the host plugin's own validators (11–25) so its messages
				// win when the booking itself is invalid.
				add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate' ), 30, 2 );

				// Attach after the host plugin has built rbfw_ticket_info at 90.
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'attach_to_cart_item' ), 95, 2 );

				// Show the answers in the cart and at checkout.
				add_filter( 'woocommerce_get_item_data', array( $this, 'cart_item_display' ), 95, 2 );

				// Persist onto the order line item and link the entry back.
				add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'attach_to_order_item' ), 95, 4 );
			}

			/* ── Validation ───────────────────────────────────────────────────── */

			/**
			 * @param bool $passed
			 * @param int  $product_id
			 */
			public function validate( $passed, $product_id ) {
				if ( ! $passed ) {
					return $passed;
				}

				$item_id = rbfw_gf_item_id_from_post( $product_id );
				if ( $item_id <= 0 ) {
					return $passed;
				}

				if ( ! rbfw_gf_answers_required( $item_id ) ) {
					return $passed;
				}

				if ( null === rbfw_gf_verified_entry_from_post( $item_id ) ) {
					wc_add_notice(
						esc_html__( 'Please complete the booking questions for this rental before continuing.', 'booking-and-rental-manager-for-woocommerce' ),
						'error'
					);

					return false;
				}

				return $passed;
			}

			/* ── Cart ─────────────────────────────────────────────────────────── */

			/**
			 * @param array $cart_item_data
			 * @param int   $product_id
			 *
			 * @return array
			 */
			public function attach_to_cart_item( $cart_item_data, $product_id ) {
				$item_id = rbfw_gf_item_id_from_post( $product_id );
				if ( $item_id <= 0 ) {
					return $cart_item_data;
				}

				$entry = rbfw_gf_verified_entry_from_post( $item_id );
				if ( null === $entry ) {
					return $cart_item_data;
				}

				$rows = self::entry_to_rows( $entry );
				if ( ! $rows ) {
					return $cart_item_data;
				}

				$entry_id = (int) $entry['id'];

				$cart_item_data['rbfw_gf_entry_id'] = $entry_id;
				$cart_item_data['rbfw_gf_form_id']  = (int) $entry['form_id'];
				$cart_item_data['rbfw_gf_rows']     = $rows;

				// Merge into every ticket row the host plugin built for this line.
				if ( ! empty( $cart_item_data['rbfw_ticket_info'] ) && is_array( $cart_item_data['rbfw_ticket_info'] ) ) {
					foreach ( $cart_item_data['rbfw_ticket_info'] as $i => $ticket ) {
						if ( ! is_array( $ticket ) ) {
							continue;
						}

						$existing = ( isset( $ticket['rbfw_regf_info'] ) && is_array( $ticket['rbfw_regf_info'] ) )
							? $ticket['rbfw_regf_info']
							: array();

						// Host keys first so the Pro registration form keeps its
						// position; Gravity rows append after it.
						$merged = $existing + $rows;

						$cart_item_data['rbfw_ticket_info'][ $i ]['rbfw_regf_info'] = $merged;

						// rbfw_regf_attendees is the per-booked-unit list. One
						// Gravity entry describes the booking as a whole, so the
						// same rows are merged into each unit rather than being
						// split across them.
						if ( ! empty( $ticket['rbfw_regf_attendees'] ) && is_array( $ticket['rbfw_regf_attendees'] ) ) {
							foreach ( $ticket['rbfw_regf_attendees'] as $a => $attendee ) {
								if ( is_array( $attendee ) ) {
									$cart_item_data['rbfw_ticket_info'][ $i ]['rbfw_regf_attendees'][ $a ] = $attendee + $rows;
								}
							}
						} else {
							$cart_item_data['rbfw_ticket_info'][ $i ]['rbfw_regf_attendees'] = array( $merged );
						}
					}
				}

				// Spend the entry so a refresh cannot reuse these answers.
				RBFW_GF_Entry_Store::mark_claimed( $entry_id );

				return $cart_item_data;
			}

			/**
			 * Show the answers in the cart and checkout item summary.
			 *
			 * @param array $item_data
			 * @param array $cart_item
			 *
			 * @return array
			 */
			public function cart_item_display( $item_data, $cart_item ) {
				if ( empty( $cart_item['rbfw_gf_rows'] ) || ! is_array( $cart_item['rbfw_gf_rows'] ) ) {
					return $item_data;
				}

				foreach ( $cart_item['rbfw_gf_rows'] as $row ) {
					if ( empty( $row['label'] ) || '' === (string) $row['value'] ) {
						continue;
					}
					$item_data[] = array(
						'key'     => wp_strip_all_tags( (string) $row['label'] ),
						'value'   => wp_strip_all_tags( (string) $row['value'] ),
						'display' => '',
					);
				}

				return $item_data;
			}

			/* ── Order ────────────────────────────────────────────────────────── */

			/**
			 * @param WC_Order_Item_Product $item
			 * @param string                $cart_item_key
			 * @param array                 $values
			 * @param WC_Order              $order
			 */
			public function attach_to_order_item( $item, $cart_item_key, $values, $order ): void {
				if ( empty( $values['rbfw_gf_entry_id'] ) ) {
					return;
				}

				$entry_id = (int) $values['rbfw_gf_entry_id'];

				// Hidden meta: machine-readable link, not shown on the order.
				$item->add_meta_data( '_rbfw_gf_entry_id', $entry_id, true );

				if ( ! empty( $values['rbfw_gf_rows'] ) && is_array( $values['rbfw_gf_rows'] ) ) {
					foreach ( $values['rbfw_gf_rows'] as $row ) {
						if ( empty( $row['label'] ) || '' === (string) $row['value'] ) {
							continue;
						}
						$item->add_meta_data(
							wp_strip_all_tags( (string) $row['label'] ),
							wp_strip_all_tags( (string) $row['value'] ),
							false
						);
					}
				}

				$order_id = method_exists( $order, 'get_id' ) ? (int) $order->get_id() : 0;
				RBFW_GF_Entry_Store::link_order( $entry_id, $order_id );
			}

			/* ── Entry → rows ─────────────────────────────────────────────────── */

			/**
			 * Flatten a Gravity entry into the host plugin's label/value shape.
			 *
			 * Walks whatever fields the form happens to have — there is no field
			 * map to maintain, so a form edited in Gravity Forms needs no change
			 * here. GFCommon::get_lead_field_display() is used rather than reading
			 * $entry[$id] directly so multi-input fields (Name, Address, checkbox
			 * groups, products with quantity) read the way they do in the Gravity
			 * entry view.
			 *
			 * @param array $entry
			 *
			 * @return array<string,array{label:string,value:string}>
			 */
			public static function entry_to_rows( array $entry ): array {
				$rows = array();

				if ( empty( $entry['form_id'] ) ) {
					return $rows;
				}

				$form = GFAPI::get_form( (int) $entry['form_id'] );
				if ( ! is_array( $form ) || empty( $form['fields'] ) ) {
					return $rows;
				}

				$currency = ! empty( $entry['currency'] ) ? (string) $entry['currency'] : '';

				foreach ( $form['fields'] as $field ) {
					$type = isset( $field->type ) ? (string) $field->type : '';

					if ( in_array( $type, self::SKIP_TYPES, true ) ) {
						continue;
					}
					// Administrative-only fields are not customer answers.
					if ( ! empty( $field->visibility ) && 'administrative' === $field->visibility ) {
						continue;
					}

					$field_id = isset( $field->id ) ? (string) $field->id : '';
					if ( '' === $field_id ) {
						continue;
					}

					// GFFormsModel is the current class; RGFormsModel is the legacy
					// alias kept for older Gravity Forms builds.
					if ( method_exists( 'GFFormsModel', 'get_lead_field_value' ) ) {
						$raw = GFFormsModel::get_lead_field_value( $entry, $field );
					} elseif ( class_exists( 'RGFormsModel' ) ) {
						$raw = RGFormsModel::get_lead_field_value( $entry, $field );
					} else {
						$raw = isset( $entry[ $field_id ] ) ? $entry[ $field_id ] : '';
					}

					$value = GFCommon::get_lead_field_display( $field, $raw, $currency, false, 'text' );
					$value = is_array( $value ) ? implode( ', ', array_filter( (array) $value ) ) : (string) $value;
					$value = trim( wp_strip_all_tags( $value ) );

					if ( '' === $value ) {
						continue;
					}

					$label = GFCommon::get_label( $field );
					$label = trim( wp_strip_all_tags( (string) $label ) );
					if ( '' === $label ) {
						/* translators: %s: Gravity Forms field ID used when a field has no label. */
						$label = sprintf( __( 'Field %s', 'booking-and-rental-manager-for-woocommerce' ), $field_id );
					}

					// Namespaced key so Gravity answers can never collide with the
					// Pro registration form's own keys (fullname, email, phone…).
					$rows[ 'gf_' . $field_id ] = array(
						'label' => $label,
						'value' => sanitize_textarea_field( $value ),
					);
				}

				return (array) apply_filters( 'rbfw_gf_entry_rows', $rows, $entry, $form );
			}

		}
	}
