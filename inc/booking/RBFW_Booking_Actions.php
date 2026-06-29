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

			self::log_status_change( $booking_id, $new_status );

			if ( in_array( $new_status, array( 'confirmed', 'processing', 'completed' ), true ) ) {
				self::notify_customer( $booking_id, $new_status, true );
			} elseif ( in_array( $new_status, array( 'cancelled', 'refunded' ), true ) ) {
				self::notify_customer( $booking_id, $new_status, false );
				self::release_inventory( $booking_id );
			}
		}

		/**
		 * Append a human-readable revision note (who / when / what).
		 */
		private static function log_status_change( $booking_id, $new_status ) {
			$log = get_post_meta( $booking_id, 'rbfw_status_log', true );
			if ( ! is_array( $log ) ) {
				$log = array();
			}
			$user    = wp_get_current_user();
			$log[]   = array(
				'status' => $new_status,
				'by'     => $user && $user->exists() ? $user->display_name : '',
				'time'   => current_time( 'mysql' ),
			);
			update_post_meta( $booking_id, 'rbfw_status_log', $log );
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
			$lines[] = esc_html__( 'Total:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['total'];
			$lines[] = esc_html__( 'Status:', 'booking-and-rental-manager-for-woocommerce' ) . ' ' . $d['status_label'];
			$lines[] = '';
			$lines[] = sprintf( esc_html__( 'Thank you, %s', 'booking-and-rental-manager-for-woocommerce' ), get_bloginfo( 'name' ) );

			return implode( "\n", $lines );
		}

		/**
		 * Normalised, display-ready booking details used by the email and the PDF.
		 *
		 * @return array<string,string>
		 */
		private static function get_booking_details( $booking_id ) {
			$statuses = self::get_statuses();
			$status   = (string) get_post_meta( $booking_id, 'rbfw_status', true );
			$start    = (string) get_post_meta( $booking_id, 'rbfw_start_date', true );
			$end      = (string) get_post_meta( $booking_id, 'rbfw_end_date', true );

			$dates = $start;
			if ( $end && $end !== $start ) {
				$dates .= ' → ' . $end;
			}

			$total = (float) get_post_meta( $booking_id, 'rbfw_total', true );

			return array(
				'reference'    => (string) get_post_meta( $booking_id, 'rbfw_reference', true ),
				'item'         => (string) get_post_meta( $booking_id, 'rbfw_item_name', true ),
				'customer'     => (string) get_post_meta( $booking_id, 'rbfw_customer_name', true ),
				'email'        => (string) get_post_meta( $booking_id, 'rbfw_customer_email', true ),
				'phone'        => (string) get_post_meta( $booking_id, 'rbfw_customer_phone', true ),
				'dates'        => $dates ? $dates : '—',
				'quantity'     => (string) max( 1, absint( get_post_meta( $booking_id, 'rbfw_quantity', true ) ) ),
				'total'        => html_entity_decode( wp_strip_all_tags( wc_price( $total ) ), ENT_QUOTES, 'UTF-8' ),
				'status_label' => isset( $statuses[ $status ] ) ? $statuses[ $status ] : ucfirst( $status ),
			);
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

			$d        = self::get_booking_details( $booking_id );
			$site     = esc_html( get_bloginfo( 'name' ) );
			$rows     = array(
				esc_html__( 'Reference', 'booking-and-rental-manager-for-woocommerce' ) => $d['reference'],
				esc_html__( 'Item', 'booking-and-rental-manager-for-woocommerce' )      => $d['item'],
				esc_html__( 'Customer', 'booking-and-rental-manager-for-woocommerce' )  => $d['customer'],
				esc_html__( 'Email', 'booking-and-rental-manager-for-woocommerce' )     => $d['email'],
				esc_html__( 'Phone', 'booking-and-rental-manager-for-woocommerce' )     => $d['phone'],
				esc_html__( 'Dates', 'booking-and-rental-manager-for-woocommerce' )     => $d['dates'],
				esc_html__( 'Quantity', 'booking-and-rental-manager-for-woocommerce' )  => $d['quantity'],
				esc_html__( 'Total', 'booking-and-rental-manager-for-woocommerce' )     => $d['total'],
				esc_html__( 'Status', 'booking-and-rental-manager-for-woocommerce' )    => $d['status_label'],
			);

			$html  = '<style>body{font-family:sans-serif;color:#222;} h1{font-size:20px;margin:0 0 4px;} .muted{color:#777;font-size:12px;} table{width:100%;border-collapse:collapse;margin-top:18px;} td{padding:9px 12px;border:1px solid #e3e3e3;font-size:13px;} td.k{background:#f7f7f7;font-weight:bold;width:34%;}</style>';
			$html .= '<h1>' . $site . '</h1>';
			$html .= '<div class="muted">' . esc_html__( 'Booking Ticket', 'booking-and-rental-manager-for-woocommerce' ) . '</div>';
			$html .= '<table>';
			foreach ( $rows as $label => $value ) {
				$html .= '<tr><td class="k">' . esc_html( $label ) . '</td><td>' . esc_html( $value ) . '</td></tr>';
			}
			$html .= '</table>';

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
