<?php
/**
 * Status actions for native (standalone) bookings.
 *
 * Adds the ability to change an rbfw_booking status from the admin Bookings list
 * (which is otherwise read-only) and wires the side effects of each transition:
 *
 *   - Confirmed / Completed  -> email the customer, with a PDF ticket attached
 *                               when the PDF library (Mpdf, shipped by the active
 *                               magepeople-pdf-support addon) is available.
 *   - Cancelled              -> email the customer and release the inventory that
 *                               was reserved at booking creation.
 *
 * All work is gated behind a nonce + manage_options and a status whitelist. The
 * email/PDF helpers degrade gracefully: no Mpdf -> email without attachment; no
 * customer email on file -> silently skip the notification.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_Actions' ) ) {
	class RBFW_Booking_Actions {

		/** admin-post action name for the status update form. */
		const ACTION = 'rbfw_update_booking_status';

		public function __construct() {
			add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_status_update' ) );
		}

		/**
		 * The booking statuses an admin can move a booking through.
		 *
		 * @return array<string,string> slug => translated label
		 */
		public static function get_statuses() {
			return array(
				'pending'   => esc_html__( 'Pending', 'booking-and-rental-manager-for-woocommerce' ),
				'confirmed' => esc_html__( 'Confirmed', 'booking-and-rental-manager-for-woocommerce' ),
				'completed' => esc_html__( 'Completed', 'booking-and-rental-manager-for-woocommerce' ),
				'cancelled' => esc_html__( 'Cancelled', 'booking-and-rental-manager-for-woocommerce' ),
			);
		}

		/**
		 * Render the inline status-change form for one booking row.
		 *
		 * @param int    $booking_id rbfw_booking post id.
		 * @param string $current    Current status slug.
		 */
		public static function render_status_control( $booking_id, $current ) {
			$booking_id = absint( $booking_id );
			$statuses   = self::get_statuses();
			$current    = isset( $statuses[ $current ] ) ? $current : 'pending';
			?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="rbfw-booking-status-form" style="display:flex;gap:6px;align-items:center;">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking_id ); ?>">
				<input type="hidden" name="paged" value="<?php echo esc_attr( isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ); ?>">
				<?php wp_nonce_field( self::ACTION . '_' . $booking_id ); ?>
				<select name="rbfw_status" aria-label="<?php esc_attr_e( 'Booking status', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<?php foreach ( $statuses as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button button-small"><?php esc_html_e( 'Update', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
			</form>
			<?php
		}

		/**
		 * Handle the status-update form submission.
		 */
		public function handle_status_update() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You are not allowed to do this.', 'booking-and-rental-manager-for-woocommerce' ) );
			}

			$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
			check_admin_referer( self::ACTION . '_' . $booking_id );

			$new_status = isset( $_POST['rbfw_status'] ) ? sanitize_key( wp_unslash( $_POST['rbfw_status'] ) ) : '';
			$statuses   = self::get_statuses();

			if ( ! $booking_id
				|| get_post_type( $booking_id ) !== RBFW_Booking_Post_Type::POST_TYPE
				|| ! isset( $statuses[ $new_status ] ) ) {
				$this->redirect_back( 0 );
			}

			$old_status = get_post_meta( $booking_id, 'rbfw_status', true );

			update_post_meta( $booking_id, 'rbfw_status', $new_status );
			self::apply_transition( $booking_id, $new_status, $old_status );

			$this->redirect_back( 1 );
		}

		/**
		 * Apply the side effects of a status transition. Shared entry point so other
		 * listings (e.g. the Pro "Booking Orders" page) get identical behaviour:
		 *
		 *   - confirmed / processing / completed -> notify the customer (+ PDF ticket)
		 *   - cancelled / refunded               -> notify the customer + release inventory
		 *
		 * The status meta is expected to already be saved by the caller. Side effects
		 * only run on an actual change of status.
		 *
		 * @param int    $booking_id rbfw_booking post id.
		 * @param string $new_status New status slug.
		 * @param string $old_status Previous status slug.
		 */
		public static function apply_transition( $booking_id, $new_status, $old_status = '' ) {
			$booking_id = absint( $booking_id );
			if ( ! $booking_id || $new_status === $old_status ) {
				return;
			}

			self::log_status_change( $booking_id, $new_status, $old_status );

			if ( in_array( $new_status, array( 'confirmed', 'processing', 'completed' ), true ) ) {
				self::notify_customer( $booking_id, $new_status, true );
			} elseif ( in_array( $new_status, array( 'cancelled', 'refunded' ), true ) ) {
				self::notify_customer( $booking_id, $new_status, false );
				self::release_inventory( $booking_id );
			}
		}

		/**
		 * Append a status-change entry (who / when / from / to) to the booking's timeline.
		 * `status`/`by`/`time` are kept for back-compat with anything already reading the
		 * pre-existing shape; `type`/`from`/`to`/`note` are the richer shape the Pro Booking
		 * Orders page's activity log / notes UI reads via get_status_history().
		 */
		private static function log_status_change( $booking_id, $new_status, $old_status = '' ) {
			$user = wp_get_current_user();
			self::append_history( $booking_id, array(
				'type'   => 'status_change',
				'status' => $new_status,
				'from'   => $old_status,
				'to'     => $new_status,
				'note'   => '',
				'by'     => $user && $user->exists() ? $user->display_name : '',
				'time'   => current_time( 'mysql' ),
			) );
		}

		/**
		 * Append an admin-authored freeform note to the booking's timeline.
		 *
		 * @param int    $booking_id rbfw_booking post id.
		 * @param string $note       Note text (plain text; escaped on output).
		 * @return bool True on success, false if the note/booking was invalid.
		 */
		public static function add_note( $booking_id, $note ) {
			$booking_id = absint( $booking_id );
			$note       = trim( (string) $note );
			if ( ! $booking_id || RBFW_Booking_Post_Type::POST_TYPE !== get_post_type( $booking_id ) || '' === $note ) {
				return false;
			}
			$user = wp_get_current_user();
			self::append_history( $booking_id, array(
				'type'   => 'note',
				'status' => '',
				'from'   => '',
				'to'     => '',
				'note'   => $note,
				'by'     => $user && $user->exists() ? $user->display_name : '',
				'time'   => current_time( 'mysql' ),
			) );
			return true;
		}

		private static function append_history( $booking_id, $entry ) {
			$log = get_post_meta( $booking_id, 'rbfw_status_log', true );
			if ( ! is_array( $log ) ) {
				$log = array();
			}
			$log[] = $entry;
			update_post_meta( $booking_id, 'rbfw_status_log', $log );
		}

		/**
		 * Normalized timeline for a booking, oldest first: status changes + notes.
		 * Legacy entries (written before `type`/`from`/`to`/`note` existed) are
		 * backfilled so callers never have to special-case the old shape.
		 *
		 * @param int $booking_id rbfw_booking post id.
		 * @return array<int,array<string,string>> Each entry has type, from, to, note, by, time.
		 */
		public static function get_status_history( $booking_id ) {
			$log = get_post_meta( absint( $booking_id ), 'rbfw_status_log', true );
			if ( ! is_array( $log ) ) {
				return array();
			}
			$history = array();
			foreach ( $log as $entry ) {
				if ( ! is_array( $entry ) ) {
					continue;
				}
				$history[] = array(
					'type' => isset( $entry['type'] ) ? $entry['type'] : 'status_change',
					'from' => isset( $entry['from'] ) ? $entry['from'] : '',
					'to'   => isset( $entry['to'] ) ? $entry['to'] : ( isset( $entry['status'] ) ? $entry['status'] : '' ),
					'note' => isset( $entry['note'] ) ? $entry['note'] : '',
					'by'   => isset( $entry['by'] ) ? $entry['by'] : '',
					'time' => isset( $entry['time'] ) ? $entry['time'] : '',
				);
			}
			return $history;
		}

		/**
		 * Email the customer about the new status, attaching a PDF ticket when asked
		 * for and when the PDF library is available.
		 */
		private static function notify_customer( $booking_id, $status, $attach_pdf ) {
			global $rbfw;

			$email = sanitize_email( (string) get_post_meta( $booking_id, 'rbfw_customer_email', true ) );
			if ( ! $email || ! is_email( $email ) || ! isset( $rbfw ) || ! method_exists( $rbfw, 'send_email' ) ) {
				return;
			}

			$item_id = absint( get_post_meta( $booking_id, 'rbfw_item_id', true ) );
			$subject = self::email_subject( $booking_id, $status );
			$body    = self::email_body( $booking_id, $status );

			$pdf_path = '';
			if ( $attach_pdf ) {
				$pdf_path = self::generate_ticket_pdf( $booking_id );
			}

			$rbfw->send_email( $email, $item_id, $subject, $body, $booking_id, $pdf_path );

			// The attachment was a throwaway temp file; remove it once sent.
			if ( $pdf_path && file_exists( $pdf_path ) ) {
				@unlink( $pdf_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}

		private static function email_subject( $booking_id, $status ) {
			$reference = (string) get_post_meta( $booking_id, 'rbfw_reference', true );
			switch ( $status ) {
				case 'confirmed':
				case 'processing':
					/* translators: %s: booking reference */
					return sprintf( esc_html__( 'Your booking is confirmed — %s', 'booking-and-rental-manager-for-woocommerce' ), $reference );
				case 'completed':
					/* translators: %s: booking reference */
					return sprintf( esc_html__( 'Your booking is completed — %s', 'booking-and-rental-manager-for-woocommerce' ), $reference );
				case 'cancelled':
					/* translators: %s: booking reference */
					return sprintf( esc_html__( 'Your booking has been cancelled — %s', 'booking-and-rental-manager-for-woocommerce' ), $reference );
				case 'refunded':
					/* translators: %s: booking reference */
					return sprintf( esc_html__( 'Your booking has been refunded — %s', 'booking-and-rental-manager-for-woocommerce' ), $reference );
			}
			/* translators: %s: booking reference */
			return sprintf( esc_html__( 'Your booking update — %s', 'booking-and-rental-manager-for-woocommerce' ), $reference );
		}

		/**
		 * Plain-text email body (send_email() runs nl2br on it).
		 */
		private static function email_body( $booking_id, $status ) {
			$d = self::get_booking_details( $booking_id );

			switch ( $status ) {
				case 'confirmed':
				case 'processing':
					$intro = esc_html__( 'Good news! Your booking has been confirmed. Details are below.', 'booking-and-rental-manager-for-woocommerce' );
					break;
				case 'completed':
					$intro = esc_html__( 'Your booking is now marked as completed. Thank you for choosing us!', 'booking-and-rental-manager-for-woocommerce' );
					break;
				case 'cancelled':
					$intro = esc_html__( 'Your booking has been cancelled. If this is unexpected, please contact us.', 'booking-and-rental-manager-for-woocommerce' );
					break;
				case 'refunded':
					$intro = esc_html__( 'Your booking has been refunded. The amount will be returned to your original payment method.', 'booking-and-rental-manager-for-woocommerce' );
					break;
				default:
					$intro = esc_html__( 'There is an update to your booking.', 'booking-and-rental-manager-for-woocommerce' );
			}

			$lines   = array();
			$lines[] = sprintf( esc_html__( 'Hi %s,', 'booking-and-rental-manager-for-woocommerce' ), $d['customer'] ? $d['customer'] : esc_html__( 'there', 'booking-and-rental-manager-for-woocommerce' ) );
			$lines[] = '';
			$lines[] = $intro;
			$lines[] = '';
			$lines[] = esc_html__( 'Reference:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['reference'];
			$lines[] = esc_html__( 'Item:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['item'];
			$lines[] = esc_html__( 'Dates:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['dates'];
			$lines[] = esc_html__( 'Quantity:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['quantity'];
			if ( ! empty( $d['discount_raw'] ) || ! empty( $d['coupon_code'] ) ) {
				$lines[] = esc_html__( 'Subtotal:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['subtotal'];
				$coupon_line = esc_html__( 'Discount:', 'booking-and-rental-manager-for-woocommerce' );
				if ( ! empty( $d['coupon_code'] ) ) {
					$coupon_line .= ' (' . $d['coupon_code'] . ')';
				}
				$lines[] = $coupon_line . ' -' . $d['discount'];
			}
			$lines[] = esc_html__( 'Total:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['total'];
			$lines[] = esc_html__( 'Status:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['status_label'];
			$lines[] = '';
			$lines[] = sprintf( esc_html__( 'Thank you, %s', 'booking-and-rental-manager-for-woocommerce' ), get_bloginfo( 'name' ) );

			return implode( "\n", $lines );
		}

		/**
		 * Normalised, display-ready booking details used by the email and the PDF.
		 * Public: reused by the Pro Booking Orders page's "Print/Download PDF" buttons.
		 *
		 * @return array<string,string>
		 */
		public static function get_booking_details( $booking_id ) {
			$statuses = self::get_statuses();
			$status   = (string) get_post_meta( $booking_id, 'rbfw_status', true );
			$start    = (string) get_post_meta( $booking_id, 'rbfw_start_date', true );
			$end      = (string) get_post_meta( $booking_id, 'rbfw_end_date', true );

			$dates = $start;
			if ( $end && $end !== $start ) {
				$dates .= ' → ' . $end;
			}

			$total = (float) get_post_meta( $booking_id, 'rbfw_total', true );

			$coupon_code  = (string) get_post_meta( $booking_id, 'rbfw_coupon_code', true );
			$discount_raw = (float) get_post_meta( $booking_id, 'rbfw_discount', true );
			$subtotal_raw = get_post_meta( $booking_id, 'rbfw_subtotal', true );
			$subtotal_raw = ( '' === $subtotal_raw ) ? ( $total + $discount_raw ) : (float) $subtotal_raw;
			$money        = static function ( $amount ) {
				return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, 'UTF-8' );
			};

			return array(
				'reference'    => (string) get_post_meta( $booking_id, 'rbfw_reference', true ),
				'item'         => (string) get_post_meta( $booking_id, 'rbfw_item_name', true ),
				'customer'     => (string) get_post_meta( $booking_id, 'rbfw_customer_name', true ),
				'email'        => (string) get_post_meta( $booking_id, 'rbfw_customer_email', true ),
				'phone'        => (string) get_post_meta( $booking_id, 'rbfw_customer_phone', true ),
				'dates'        => $dates ? $dates : '—',
				'quantity'     => (string) max( 1, absint( get_post_meta( $booking_id, 'rbfw_quantity', true ) ) ),
				'subtotal'     => $money( $subtotal_raw ),
				'discount'     => $money( $discount_raw ),
				'discount_raw' => $discount_raw,
				'coupon_code'  => $coupon_code,
				'total'        => $money( $total ),
				'status_label' => isset( $statuses[ $status ] ) ? $statuses[ $status ] : ucfirst( $status ),
			);
		}

		/** Run an action and return whatever it echoed, trimmed, instead of just whether it's hooked. */
		private static function captured_action( $hook ) {
			ob_start();
			do_action( $hook );
			return trim( ob_get_clean() );
		}

		/** Badge background/text colors per status, matching the admin list's status pills. */
		private static function status_badge_colors( $status ) {
			$map = array(
				'completed'  => array( '#d1fae5', '#065f46' ),
				'processing' => array( '#dbeafe', '#1e40af' ),
				'confirmed'  => array( '#dbeafe', '#1e40af' ),
				'pending'    => array( '#fef3c7', '#92400e' ),
				'cancelled'  => array( '#fee2e2', '#991b1b' ),
				'refunded'   => array( '#e0e7ff', '#3730a3' ),
			);
			return isset( $map[ $status ] ) ? $map[ $status ] : array( '#f1f5f9', '#475569' );
		}

		/**
		 * Render the booking ticket as a standalone HTML fragment (used for both the
		 * emailed PDF attachment and the Pro Booking Orders page's Print/Download PDF).
		 * Layout mirrors the Pro plugin's branded WooCommerce-order PDF template
		 * (templates/pdf-templates/default/default.php: header + bordered section tables)
		 * so a standalone booking ticket looks consistent with the rest of the site's PDFs.
		 * The company logo/address/phone are optional — they only render when the Pro
		 * plugin's PDF settings hooks are attached (rbfw_pdf_logo / _company_address / _company_phone);
		 * without Pro this quietly falls back to just the site name.
		 *
		 * @return string HTML markup.
		 */
		public static function build_ticket_html( $booking_id ) {
			$d          = self::get_booking_details( $booking_id );
			$site       = get_bloginfo( 'name' );
			$status_key = sanitize_key( (string) get_post_meta( $booking_id, 'rbfw_status', true ) ?: 'pending' );
			list( $badge_bg, $badge_color ) = self::status_badge_colors( $status_key );

			// Pro's PDF settings register these hooks unconditionally, but each one echoes
			// nothing when its own setting (logo/address/phone) hasn't been filled in — so
			// has_action() alone would report "present" and leave a blank header. Capture
			// the actual output instead, and only skip the site-name / address block when
			// there's really nothing to show.
			$logo_html = self::captured_action( 'rbfw_pdf_logo' );
			$addr_html = self::captured_action( 'rbfw_pdf_company_address' );
			$phone_html = self::captured_action( 'rbfw_pdf_company_phone' );

			ob_start();
			?>
			<style>
				body { font-family: DejaVuSans, sans-serif; color: #1e293b; }
				.rbfw-ticket-header td { vertical-align: top; padding-bottom: 18px; }
				.rbfw-ticket-header .rbfw-site-name { font-size: 18px; font-weight: bold; color: #0f172a; margin: 0 0 2px; }
				.rbfw-ticket-header .rbfw-muted { color: #64748b; font-size: 10.5px; line-height: 1.5; }
				.rbfw-ticket-title { font-size: 22px; font-weight: bold; color: #0f172a; margin: 0 0 4px; }
				.rbfw-ticket-ref { font-size: 11px; color: #64748b; margin: 0 0 8px; }
				.rbfw-badge { display: inline-block; padding: 4px 14px; border-radius: 14px; font-size: 10.5px; font-weight: bold; background: <?php echo esc_attr( $badge_bg ); ?>; color: <?php echo esc_attr( $badge_color ); ?>; }
				.rbfw-section-title { background: #F12971; color: #fff; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; padding: 7px 12px; margin-top: 16px; }
				table.rbfw-info-table { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-top: none; }
				table.rbfw-info-table td { border: 1px solid #e2e8f0; padding: 8px 12px; font-size: 11.5px; vertical-align: top; }
				table.rbfw-info-table td.rbfw-k { background: #f8fafc; font-weight: bold; width: 32%; color: #475569; }
				table.rbfw-total-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
				table.rbfw-total-table td { padding: 10px 12px; font-size: 12.5px; border: none; }
				table.rbfw-total-table tr.rbfw-total-row td { border-top: 2px solid #0f172a; font-weight: bold; font-size: 15px; color: #0f172a; }
				.rbfw-footer { margin-top: 22px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9.5px; color: #94a3b8; text-align: center; }
			</style>

			<table class="rbfw-ticket-header" width="100%">
				<tr>
					<td width="55%">
						<?php if ( $logo_html ) : ?>
							<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted admin-configured PDF settings markup, same as the Pro order-PDF template. ?>
						<?php else : ?>
							<p class="rbfw-site-name"><?php echo esc_html( $site ); ?></p>
						<?php endif; ?>
						<?php if ( $addr_html || $phone_html ) : ?>
							<p class="rbfw-muted">
								<?php echo $addr_html; ?>
								<?php if ( $addr_html && $phone_html ) : ?><br><?php endif; ?>
								<?php echo $phone_html; ?>
							</p>
						<?php endif; ?>
					</td>
					<td width="45%" style="text-align:right;">
						<p class="rbfw-ticket-title"><?php esc_html_e( 'Booking Ticket', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
						<p class="rbfw-ticket-ref">#<?php echo esc_html( $d['reference'] ); ?></p>
						<span class="rbfw-badge"><?php echo esc_html( $d['status_label'] ); ?></span>
					</td>
				</tr>
			</table>

			<div class="rbfw-section-title"><?php esc_html_e( 'Booking Details', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			<table class="rbfw-info-table">
				<tr><td class="rbfw-k"><?php esc_html_e( 'Reference', 'booking-and-rental-manager-for-woocommerce' ); ?></td><td><?php echo esc_html( $d['reference'] ); ?></td></tr>
				<tr><td class="rbfw-k"><?php esc_html_e( 'Item', 'booking-and-rental-manager-for-woocommerce' ); ?></td><td><?php echo esc_html( $d['item'] ); ?></td></tr>
				<tr><td class="rbfw-k"><?php esc_html_e( 'Dates', 'booking-and-rental-manager-for-woocommerce' ); ?></td><td><?php echo esc_html( $d['dates'] ); ?></td></tr>
				<tr><td class="rbfw-k"><?php esc_html_e( 'Quantity', 'booking-and-rental-manager-for-woocommerce' ); ?></td><td><?php echo esc_html( $d['quantity'] ); ?></td></tr>
			</table>

			<div class="rbfw-section-title"><?php esc_html_e( 'Customer', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
			<table class="rbfw-info-table">
				<tr><td class="rbfw-k"><?php esc_html_e( 'Name', 'booking-and-rental-manager-for-woocommerce' ); ?></td><td><?php echo esc_html( $d['customer'] ? $d['customer'] : '—' ); ?></td></tr>
				<tr><td class="rbfw-k"><?php esc_html_e( 'Email', 'booking-and-rental-manager-for-woocommerce' ); ?></td><td><?php echo esc_html( $d['email'] ? $d['email'] : '—' ); ?></td></tr>
				<tr><td class="rbfw-k"><?php esc_html_e( 'Phone', 'booking-and-rental-manager-for-woocommerce' ); ?></td><td><?php echo esc_html( $d['phone'] ? $d['phone'] : '—' ); ?></td></tr>
			</table>

			<table class="rbfw-total-table">
				<?php if ( ! empty( $d['discount_raw'] ) || ! empty( $d['coupon_code'] ) ) : ?>
				<tr>
					<td><?php esc_html_e( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
					<td style="text-align:right;"><?php echo esc_html( $d['subtotal'] ); ?></td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Discount', 'booking-and-rental-manager-for-woocommerce' ); ?>
						<?php if ( ! empty( $d['coupon_code'] ) ) : ?><small>(<?php echo esc_html( $d['coupon_code'] ); ?>)</small><?php endif; ?>
					</td>
					<td style="text-align:right;">&minus;<?php echo esc_html( $d['discount'] ); ?></td>
				</tr>
				<?php endif; ?>
				<tr class="rbfw-total-row">
					<td><?php esc_html_e( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?></td>
					<td style="text-align:right;"><?php echo esc_html( $d['total'] ); ?></td>
				</tr>
			</table>

			<div class="rbfw-footer">
				<?php printf( /* translators: %s: site name */ esc_html__( 'Thank you for booking with %s', 'booking-and-rental-manager-for-woocommerce' ), esc_html( $site ) ); ?>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Build a PDF ticket for the booking and return its file path, or '' when
		 * the PDF library is unavailable or generation fails (email-only fallback).
		 *
		 * @return string Absolute file path or empty string.
		 */
		private static function generate_ticket_pdf( $booking_id ) {
			if ( ! class_exists( '\Mpdf\Mpdf' ) ) {
				return '';
			}

			$d    = self::get_booking_details( $booking_id );
			$html = self::build_ticket_html( $booking_id );

			// Write to the system temp dir (not web-accessible). The file is a
			// throwaway attachment removed immediately after the email is sent.
			$base_dir = trailingslashit( get_temp_dir() ) . 'rbfw-tickets';
			$temp_dir = trailingslashit( get_temp_dir() ) . 'rbfw-mpdf-tmp';
			wp_mkdir_p( $base_dir );
			wp_mkdir_p( $temp_dir );

			$file_ref = $d['reference'] ? $d['reference'] : (string) $booking_id;
			$path     = trailingslashit( $base_dir ) . 'booking-' . sanitize_file_name( $file_ref ) . '.pdf';

			try {
				$mpdf = new \Mpdf\Mpdf( array(
					'mode'    => 'utf-8',
					'format'  => 'A4',
					'tempDir' => $temp_dir,
				) );
				$mpdf->WriteHTML( $html );
				$mpdf->Output( $path, \Mpdf\Output\Destination::FILE );
			} catch ( \Throwable $e ) {
				return '';
			}

			return file_exists( $path ) ? $path : '';
		}

		/**
		 * Release the inventory reserved for this booking by removing its entry from
		 * the rental item's `rbfw_inventory` meta (entries are keyed by booking id).
		 */
		private static function release_inventory( $booking_id ) {
			$item_id = absint( get_post_meta( $booking_id, 'rbfw_item_id', true ) );
			if ( ! $item_id ) {
				return;
			}

			$inventory = get_post_meta( $item_id, 'rbfw_inventory', true );
			if ( ! is_array( $inventory ) || ! array_key_exists( $booking_id, $inventory ) ) {
				return;
			}

			unset( $inventory[ $booking_id ] );
			update_post_meta( $item_id, 'rbfw_inventory', $inventory );
		}

		/**
		 * Redirect back to the Bookings list with a result flag.
		 *
		 * @param int $ok 1 = success, 0 = failure.
		 */
		private function redirect_back( $ok ) {
			$url = add_query_arg(
				array(
					'post_type'             => 'rbfw_item',
					'page'                  => 'rbfw_bookings',
					'paged'                 => isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1,
					'rbfw_status_updated'   => $ok ? 1 : 0,
				),
				admin_url( 'edit.php' )
			);
			wp_safe_redirect( $url );
			exit;
		}
	}

	new RBFW_Booking_Actions();
}
