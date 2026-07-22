<?php
/**
 * Booking confirmation / pending notice (standalone mode).
 *
 * Rendered by RBFW_Booking_Confirmation on the rental item page when it is reached with
 * ?rbfw_booking=success|pending|cancelled. Expects $status and $reference in scope.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$status     = isset( $status ) ? $status : '';
$reference  = isset( $reference ) ? $reference : '';
$booking_id = isset( $booking_id ) ? absint( $booking_id ) : 0;

// Ticket download: shown only once the booking's REAL status (not the URL hint, which
// can go stale or be edited) reaches the admin-configured "Inventory Managed Order
// Status" — the same gate used by the My Account download button and the confirmation
// email's PDF attachment. RBFW_Customer_Portal (Pro) owns the download endpoint and its
// ownership check; the free plugin only links to it when both are available.
$rbfw_download_url = '';
if ( $booking_id && class_exists( 'RBFW_Booking_Normalizer' ) && class_exists( 'RBFW_Customer_Portal' ) ) {
	$rbfw_real_status = get_post_meta( $booking_id, 'rbfw_status', true );
	if ( RBFW_Booking_Normalizer::is_ticket_ready( $rbfw_real_status ) ) {
		$rbfw_download_url = RBFW_Customer_Portal::invoice_url( $booking_id, $reference );
	}
}

switch ( $status ) {
	case 'success':
		$title = esc_html__( 'Booking confirmed', 'booking-and-rental-manager-for-woocommerce' );
		$body  = esc_html__( 'Thank you! Your booking has been confirmed. A confirmation email has been sent to you.', 'booking-and-rental-manager-for-woocommerce' );
		$class = 'rbfw-booking-notice--success';
		break;
	case 'cancelled':
		$title = esc_html__( 'Booking cancelled', 'booking-and-rental-manager-for-woocommerce' );
		$body  = esc_html__( 'Your booking was cancelled. You can try again below.', 'booking-and-rental-manager-for-woocommerce' );
		$class = 'rbfw-booking-notice--cancelled';
		break;
	default: // pending
		$title = esc_html__( 'Booking received', 'booking-and-rental-manager-for-woocommerce' );
		$body  = esc_html__( 'Your booking has been received and is pending. We will follow up with payment details to confirm your reservation.', 'booking-and-rental-manager-for-woocommerce' );
		$class = 'rbfw-booking-notice--pending';
		break;
}
?>
<div class="rbfw-booking-notice <?php echo esc_attr( $class ); ?>" role="status" style="margin:0 0 20px;padding:16px 20px;border-radius:8px;border:1px solid #d6e9c6;background:#f4faf0;">
	<h3 style="margin:0 0 6px;font-size:18px;"><?php echo esc_html( $title ); ?></h3>
	<p style="margin:0;"><?php echo esc_html( $body ); ?></p>
	<?php if ( $reference ) : ?>
		<p style="margin:8px 0 0;font-size:13px;opacity:.8;">
			<?php echo esc_html__( 'Reference:', 'booking-and-rental-manager-for-woocommerce' ); ?>
			<strong><?php echo esc_html( $reference ); ?></strong>
		</p>
	<?php endif; ?>
	<?php if ( $rbfw_download_url ) : ?>
		<p style="margin:12px 0 0;">
			<a href="<?php echo esc_url( $rbfw_download_url ); ?>" target="_blank" rel="noopener" class="button" style="display:inline-flex;align-items:center;gap:6px;">
				<?php echo esc_html__( 'Download Ticket', 'booking-and-rental-manager-for-woocommerce' ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
