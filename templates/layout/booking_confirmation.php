<?php
/**
 * Booking confirmation / pending notice (standalone mode).
 *
 * Rendered by RBFW_Booking_Confirmation on the rental item page when it is reached with
 * ?rbfw_booking=success|pending|cancelled. Expects $status and $reference in scope.
 *
 * Redesigned into a proper confirmation panel (icon + heading + an actual order-details
 * card — item, dates, quantity, price breakdown, payment method, status) instead of a
 * one-line notice box. Every detail below comes straight from the flat meta
 * RBFW_Standalone_Booking_Service wrote when the booking was created (rbfw_item_name,
 * rbfw_start_date, rbfw_total, etc.) — no extra query, and it degrades gracefully to just
 * the message + reference if the booking couldn't be resolved (e.g. an old/edited link).
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
$rbfw_real_status  = '';
if ( $booking_id ) {
	$rbfw_real_status = get_post_meta( $booking_id, 'rbfw_status', true );
	if ( class_exists( 'RBFW_Booking_Normalizer' ) && class_exists( 'RBFW_Customer_Portal' ) && RBFW_Booking_Normalizer::is_ticket_ready( $rbfw_real_status ) ) {
		$rbfw_download_url = RBFW_Customer_Portal::invoice_url( $booking_id, $reference );
	}
}

// "Reconfirm Booking Yourself" — only offered while the booking is genuinely still
// pending, the feature is on (Payments -> Custom Payment -> Offline Payment, default on),
// and there's an email on file to send the code to.
$rbfw_show_reconfirm = false;
$rbfw_reconfirm_nonce = '';
if ( $booking_id
	&& class_exists( 'RBFW_Booking_Normalizer' )
	&& 'pending' === RBFW_Booking_Normalizer::normalize_status( $rbfw_real_status )
	&& class_exists( 'RBFW_Function' ) && RBFW_Function::offline_reconfirm_enabled()
	&& class_exists( 'RBFW_Booking_Reconfirm' )
	&& get_post_meta( $booking_id, 'rbfw_customer_email', true )
) {
	$rbfw_show_reconfirm  = true;
	$rbfw_reconfirm_nonce = wp_create_nonce( RBFW_Booking_Reconfirm::nonce_action( $booking_id ) );
}

switch ( $status ) {
	case 'success':
		$title = esc_html__( 'Booking confirmed', 'booking-and-rental-manager-for-woocommerce' );
		$body  = esc_html__( 'Thank you! Your booking has been confirmed. A confirmation email has been sent to you.', 'booking-and-rental-manager-for-woocommerce' );
		$class = 'rbfw-bc--success';
		break;
	case 'cancelled':
		$title = esc_html__( 'Booking cancelled', 'booking-and-rental-manager-for-woocommerce' );
		$body  = esc_html__( 'Your booking was cancelled. You can try again below.', 'booking-and-rental-manager-for-woocommerce' );
		$class = 'rbfw-bc--cancelled';
		break;
	default: // pending
		$title = esc_html__( 'Booking received', 'booking-and-rental-manager-for-woocommerce' );
		$body  = esc_html__( 'Your booking has been received and is pending. We will follow up with payment details to confirm your reservation.', 'booking-and-rental-manager-for-woocommerce' );
		$class = 'rbfw-bc--pending';
		break;
}

// Order-details card — only when the booking actually resolved. Every value here is
// flat post meta written by RBFW_Standalone_Booking_Service::create_booking(); nothing
// requires WooCommerce.
$rbfw_details = array();
if ( $booking_id ) {
	$rbfw_item_id   = (int) get_post_meta( $booking_id, 'rbfw_item_id', true );
	$rbfw_item_name = get_post_meta( $booking_id, 'rbfw_item_name', true );
	$rbfw_item_url  = $rbfw_item_id ? get_permalink( $rbfw_item_id ) : '';

	$rbfw_start = trim(
		get_post_meta( $booking_id, 'rbfw_start_date', true ) . ' ' . get_post_meta( $booking_id, 'rbfw_start_time', true )
	);
	$rbfw_end = trim(
		get_post_meta( $booking_id, 'rbfw_end_date', true ) . ' ' . get_post_meta( $booking_id, 'rbfw_end_time', true )
	);

	$rbfw_quantity = max( 1, (int) get_post_meta( $booking_id, 'rbfw_quantity', true ) );
	$rbfw_subtotal = (float) get_post_meta( $booking_id, 'rbfw_subtotal', true );
	$rbfw_discount = (float) get_post_meta( $booking_id, 'rbfw_discount', true );
	$rbfw_total    = get_post_meta( $booking_id, 'rbfw_total', true );
	$rbfw_coupon   = get_post_meta( $booking_id, 'rbfw_coupon_code', true );
	$rbfw_payment  = get_post_meta( $booking_id, 'rbfw_payment_method', true );

	if ( $rbfw_item_name ) {
		$rbfw_details[] = array(
			'label' => esc_html__( 'Item', 'booking-and-rental-manager-for-woocommerce' ),
			'value' => $rbfw_item_url ? '<a href="' . esc_url( $rbfw_item_url ) . '">' . esc_html( $rbfw_item_name ) . '</a>' : esc_html( $rbfw_item_name ),
		);
	}
	if ( $rbfw_start ) {
		$rbfw_details[] = array(
			'label' => esc_html__( 'Pickup', 'booking-and-rental-manager-for-woocommerce' ),
			'value' => esc_html( $rbfw_start ),
		);
	}
	if ( $rbfw_end ) {
		$rbfw_details[] = array(
			'label' => esc_html__( 'Return', 'booking-and-rental-manager-for-woocommerce' ),
			'value' => esc_html( $rbfw_end ),
		);
	}
	$rbfw_details[] = array(
		'label' => esc_html__( 'Quantity', 'booking-and-rental-manager-for-woocommerce' ),
		'value' => esc_html( $rbfw_quantity ),
	);
	if ( $rbfw_discount > 0 ) {
		$rbfw_details[] = array(
			'label' => esc_html__( 'Subtotal', 'booking-and-rental-manager-for-woocommerce' ),
			'value' => wp_kses_post( RBFW_Booking_Normalizer::format_price( $rbfw_subtotal ) ),
		);
		$rbfw_coupon_suffix = $rbfw_coupon ? ' (' . esc_html( $rbfw_coupon ) . ')' : '';
		$rbfw_details[]     = array(
			'label' => esc_html__( 'Discount', 'booking-and-rental-manager-for-woocommerce' ) . $rbfw_coupon_suffix,
			'value' => '&minus;' . wp_kses_post( RBFW_Booking_Normalizer::format_price( $rbfw_discount ) ),
		);
	}
	$rbfw_details[] = array(
		'label'  => esc_html__( 'Total', 'booking-and-rental-manager-for-woocommerce' ),
		'value'  => wp_kses_post( RBFW_Booking_Normalizer::format_price( $rbfw_total ) ),
		'strong' => true,
	);
	if ( $rbfw_payment ) {
		$rbfw_details[] = array(
			'label' => esc_html__( 'Payment method', 'booking-and-rental-manager-for-woocommerce' ),
			'value' => esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $rbfw_payment ) ) ),
		);
	}
	if ( $rbfw_real_status && class_exists( 'RBFW_Booking_Normalizer' ) ) {
		$rbfw_details[] = array(
			'label' => esc_html__( 'Status', 'booking-and-rental-manager-for-woocommerce' ),
			'value' => '<span class="rbfw-bc__status ' . esc_attr( RBFW_Booking_Normalizer::status_class( $rbfw_real_status ) ) . '">'
				. esc_html( RBFW_Booking_Normalizer::status_label( $rbfw_real_status ) ) . '</span>',
		);
	}
}
?>
<style>
	.rbfw-bc{--rbfw-bc-primary:var(--rentiva-primary,#1B5E3B);--rbfw-bc-primary-dark:var(--rentiva-primary-dark,#154D2E);--rbfw-bc-radius:var(--rentiva-radius-lg,14px);--rbfw-bc-border:var(--rentiva-border,#E8E4DC);--rbfw-bc-muted:var(--rentiva-muted-foreground,#6b7280);--rbfw-bc-fg:var(--rentiva-foreground,#141414);max-width:560px;margin:0 auto 28px;padding:32px 28px 28px;border-radius:var(--rbfw-bc-radius);background:var(--rentiva-card,#fff);border:1px solid var(--rbfw-bc-border);box-shadow:var(--rentiva-shadow-card,0 1px 2px rgba(20,20,20,.04),0 8px 24px rgba(20,20,20,.06));text-align:center;font-family:var(--rentiva-font-sans,inherit);color:var(--rbfw-bc-fg);}
	.rbfw-bc__icon{display:flex;align-items:center;justify-content:center;width:64px;height:64px;margin:0 auto 16px;border-radius:50%;}
	.rbfw-bc__icon svg{width:32px;height:32px;}
	.rbfw-bc--success .rbfw-bc__icon{background:#e8f5ee;color:var(--rbfw-bc-primary);}
	.rbfw-bc--pending .rbfw-bc__icon{background:#fff7e6;color:#b45309;}
	.rbfw-bc--cancelled .rbfw-bc__icon{background:#fef2f2;color:#dc2626;}
	.rbfw-bc__title{margin:0 0 8px;font-size:24px;font-weight:800;font-family:var(--rentiva-font-display,inherit);letter-spacing:-.01em;}
	.rbfw-bc__body{margin:0 0 22px;font-size:14.5px;line-height:1.6;color:var(--rbfw-bc-muted);}
	.rbfw-bc__ref{display:inline-block;margin:0 0 22px;padding:8px 16px;border-radius:999px;background:var(--rentiva-secondary,#F3F0EB);font-size:12.5px;color:var(--rbfw-bc-muted);}
	.rbfw-bc__ref strong{color:var(--rbfw-bc-fg);font-weight:700;letter-spacing:.02em;}
	.rbfw-bc__details{margin:0 0 22px;padding:4px 20px;border:1px solid var(--rbfw-bc-border);border-radius:calc(var(--rbfw-bc-radius) - 4px);text-align:left;}
	.rbfw-bc__row{display:flex;align-items:baseline;justify-content:space-between;gap:16px;padding:12px 0;font-size:13.5px;}
	.rbfw-bc__row + .rbfw-bc__row{border-top:1px solid var(--rbfw-bc-border);}
	.rbfw-bc__row-label{color:var(--rbfw-bc-muted);flex-shrink:0;}
	.rbfw-bc__row-value{font-weight:600;text-align:right;}
	.rbfw-bc__row-value a{color:var(--rbfw-bc-primary);text-decoration:none;}
	.rbfw-bc__row-value a:hover{text-decoration:underline;}
	.rbfw-bc__row--total .rbfw-bc__row-label,
	.rbfw-bc__row--total .rbfw-bc__row-value{font-size:15.5px;font-weight:800;}
	.rbfw-bc__status{display:inline-block;padding:.2rem .65rem;border-radius:999px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;background:#fff7e6;color:#b45309;}
	.rbfw-bc__status.rbfw-status-completed,.rbfw-bc__status.rbfw-status-processing{background:#e8f5ee;color:var(--rbfw-bc-primary-dark);}
	.rbfw-bc__status.rbfw-status-cancelled,.rbfw-bc__status.rbfw-status-failed{background:#fef2f2;color:#dc2626;}
	.rbfw-bc__actions{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;}
	.rbfw-bc__btn{display:inline-flex;align-items:center;gap:6px;padding:.75rem 1.35rem;border-radius:999px;font-size:13.5px;font-weight:600;text-decoration:none;transition:background-color .15s ease,transform .15s ease,color .15s ease;}
	.rbfw-bc__btn--primary{background:var(--rbfw-bc-primary);color:#fff;}
	.rbfw-bc__btn--primary:hover{background:var(--rbfw-bc-primary-dark);color:#fff;transform:translateY(-1px);}
	.rbfw-bc__btn--ghost{background:transparent;border:1px solid var(--rbfw-bc-border);color:var(--rbfw-bc-fg);}
	.rbfw-bc__btn--ghost:hover{border-color:var(--rbfw-bc-primary);color:var(--rbfw-bc-primary);}
	.rbfw-bc__btn:disabled{opacity:.6;cursor:default;transform:none !important;}
	/* Reconfirm-yourself panel */
	.rbfw-bc__reconfirm{margin:0 0 22px;padding:18px 20px;border:1px dashed var(--rbfw-bc-border);border-radius:calc(var(--rbfw-bc-radius) - 4px);text-align:left;background:#fffdf7;}
	.rbfw-bc__reconfirm-text{margin:0 0 12px;font-size:13px;line-height:1.6;color:var(--rbfw-bc-muted);}
	.rbfw-bc__reconfirm-text strong{color:var(--rbfw-bc-fg);}
	.rbfw-bc__reconfirm-otp{display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--rbfw-bc-border);}
	.rbfw-bc__reconfirm.is-open .rbfw-bc__reconfirm-otp{display:block;}
	.rbfw-bc__reconfirm.is-open .rbfw-bc__reconfirm-start{display:none;}
	.rbfw-bc__reconfirm-row{display:flex;gap:8px;flex-wrap:wrap;}
	.rbfw-bc__reconfirm-input{flex:1;min-width:140px;padding:.7rem .9rem;border:1px solid var(--rbfw-bc-border);border-radius:8px;font-size:16px;letter-spacing:.35em;text-align:center;font-weight:700;box-sizing:border-box;}
	.rbfw-bc__reconfirm-msg{margin:10px 0 0;font-size:12.5px;line-height:1.5;min-height:1.2em;}
	.rbfw-bc__reconfirm-msg.error{color:#dc2626;}
	.rbfw-bc__reconfirm-msg.success{color:var(--rbfw-bc-primary-dark);font-weight:600;}
	.rbfw-bc__reconfirm-resend{display:inline-block;margin-top:10px;font-size:12px;color:var(--rbfw-bc-muted);text-decoration:underline;cursor:pointer;background:none;border:0;padding:0;}
	.rbfw-bc__reconfirm-resend:disabled{cursor:default;text-decoration:none;opacity:.6;}
	@media (max-width:480px){.rbfw-bc{padding:26px 18px 22px;}.rbfw-bc__details{padding:2px 14px;}}
</style>
<div class="rbfw-bc <?php echo esc_attr( $class ); ?>" role="status">
	<div class="rbfw-bc__icon" aria-hidden="true">
		<?php if ( 'success' === $status ) : ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
		<?php elseif ( 'cancelled' === $status ) : ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="M6 6l12 12"></path></svg>
		<?php else : ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>
		<?php endif; ?>
	</div>

	<h2 class="rbfw-bc__title"><?php echo esc_html( $title ); ?></h2>
	<p class="rbfw-bc__body"><?php echo esc_html( $body ); ?></p>

	<?php if ( $reference ) : ?>
		<div class="rbfw-bc__ref">
			<?php echo esc_html__( 'Reference:', 'booking-and-rental-manager-for-woocommerce' ); ?>
			<strong><?php echo esc_html( $reference ); ?></strong>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $rbfw_details ) ) : ?>
		<div class="rbfw-bc__details">
			<?php foreach ( $rbfw_details as $rbfw_row ) : ?>
				<div class="rbfw-bc__row<?php echo ! empty( $rbfw_row['strong'] ) ? ' rbfw-bc__row--total' : ''; ?>">
					<span class="rbfw-bc__row-label"><?php echo wp_kses_post( $rbfw_row['label'] ); ?></span>
					<span class="rbfw-bc__row-value"><?php echo wp_kses_post( $rbfw_row['value'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $rbfw_show_reconfirm ) : ?>
		<div class="rbfw-bc__reconfirm" data-rbfw-reconfirm data-booking-id="<?php echo esc_attr( $booking_id ); ?>" data-nonce="<?php echo esc_attr( $rbfw_reconfirm_nonce ); ?>">
			<p class="rbfw-bc__reconfirm-text">
				<?php
				echo wp_kses(
					__( '<strong>Don\'t want to wait for a call?</strong> If you confirm this booking yourself, no one will call you to confirm it — it will be confirmed as your booking right away.', 'booking-and-rental-manager-for-woocommerce' ),
					array( 'strong' => array() )
				);
				?>
			</p>

			<div class="rbfw-bc__reconfirm-start">
				<button type="button" class="rbfw-bc__btn rbfw-bc__btn--primary" data-rbfw-reconfirm-send>
					<?php esc_html_e( 'Reconfirm Booking Yourself', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</button>
			</div>

			<div class="rbfw-bc__reconfirm-otp">
				<p class="rbfw-bc__reconfirm-text">
					<?php esc_html_e( "We've emailed a 6-digit code to your address on file. Enter it below to confirm your booking yourself.", 'booking-and-rental-manager-for-woocommerce' ); ?>
				</p>
				<div class="rbfw-bc__reconfirm-row">
					<input type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="000000" class="rbfw-bc__reconfirm-input" data-rbfw-reconfirm-otp-input>
					<button type="button" class="rbfw-bc__btn rbfw-bc__btn--primary" data-rbfw-reconfirm-verify>
						<?php esc_html_e( 'Confirm Booking', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</button>
				</div>
				<button type="button" class="rbfw-bc__reconfirm-resend" data-rbfw-reconfirm-resend>
					<?php esc_html_e( "Didn't get a code? Resend", 'booking-and-rental-manager-for-woocommerce' ); ?>
				</button>
			</div>

			<p class="rbfw-bc__reconfirm-msg" data-rbfw-reconfirm-msg aria-live="polite"></p>
		</div>
		<script>
		(function () {
			// The panel this script tag immediately follows — not a page-wide lookup, so
			// this still targets the right instance if this template is ever included
			// more than once on a page.
			var panel = document.currentScript.previousElementSibling;
			if ( ! panel ) { return; }
			var msg     = panel.querySelector('[data-rbfw-reconfirm-msg]');
			var sendBtn = panel.querySelector('[data-rbfw-reconfirm-send]');
			var verifyBtn = panel.querySelector('[data-rbfw-reconfirm-verify]');
			var resendBtn = panel.querySelector('[data-rbfw-reconfirm-resend]');
			var otpInput  = panel.querySelector('[data-rbfw-reconfirm-otp-input]');
			var bookingId = panel.getAttribute('data-booking-id');
			var nonce     = panel.getAttribute('data-nonce');
			var ajaxUrl   = ( typeof rbfw_ajax_url !== 'undefined' && rbfw_ajax_url ) ? rbfw_ajax_url : ( typeof rbfw_ajax_front !== 'undefined' ? rbfw_ajax_front.rbfw_ajaxurl : '/wp-admin/admin-ajax.php' );

			function setMsg( text, type ) {
				msg.textContent = text || '';
				msg.className = 'rbfw-bc__reconfirm-msg' + ( type ? ' ' + type : '' );
			}

			function post( action, extra, done ) {
				var data = new URLSearchParams();
				data.set( 'action', action );
				data.set( 'booking_id', bookingId );
				data.set( 'nonce', nonce );
				for ( var k in extra ) { data.set( k, extra[ k ] ); }
				fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: data.toString() } )
					.then( function ( r ) { return r.json(); } )
					.then( done )
					.catch( function () { done( { success: false, data: { message: '<?php echo esc_js( __( 'Network error. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>' } } ); } );
			}

			function sendOtp( isResend ) {
				var btn = isResend ? resendBtn : sendBtn;
				btn.disabled = true;
				setMsg( '<?php echo esc_js( __( 'Sending…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>' );
				post( 'rbfw_reconfirm_send_otp', {}, function ( res ) {
					btn.disabled = false;
					if ( res && res.success ) {
						panel.classList.add( 'is-open' );
						setMsg( ( res.data && res.data.message ) || '', 'success' );
						otpInput.value = '';
						otpInput.focus();
					} else {
						setMsg( ( res && res.data && res.data.message ) || '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>', 'error' );
					}
				} );
			}

			sendBtn.addEventListener( 'click', function () { sendOtp( false ); } );
			resendBtn.addEventListener( 'click', function () { sendOtp( true ); } );

			verifyBtn.addEventListener( 'click', function () {
				var otp = ( otpInput.value || '' ).replace( /\D/g, '' );
				if ( otp.length !== 6 ) {
					setMsg( '<?php echo esc_js( __( 'Please enter the 6-digit code from your email.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>', 'error' );
					return;
				}
				verifyBtn.disabled = true;
				setMsg( '<?php echo esc_js( __( 'Confirming…', 'booking-and-rental-manager-for-woocommerce' ) ); ?>' );
				post( 'rbfw_reconfirm_verify_otp', { otp: otp }, function ( res ) {
					if ( res && res.success ) {
						setMsg( ( res.data && res.data.message ) || '', 'success' );
						setTimeout( function () { window.location.reload(); }, 1200 );
					} else {
						verifyBtn.disabled = false;
						setMsg( ( res && res.data && res.data.message ) || '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'booking-and-rental-manager-for-woocommerce' ) ); ?>', 'error' );
					}
				} );
			} );

			otpInput.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Enter' ) { e.preventDefault(); verifyBtn.click(); }
			} );
		})();
		</script>
	<?php endif; ?>

	<div class="rbfw-bc__actions">
		<?php if ( $rbfw_download_url ) : ?>
			<a href="<?php echo esc_url( $rbfw_download_url ); ?>" target="_blank" rel="noopener" class="rbfw-bc__btn rbfw-bc__btn--primary">
				<?php echo esc_html__( 'Download Ticket', 'booking-and-rental-manager-for-woocommerce' ); ?>
			</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rbfw-bc__btn rbfw-bc__btn--ghost">
			<?php echo esc_html__( 'Continue browsing', 'booking-and-rental-manager-for-woocommerce' ); ?>
		</a>
	</div>
</div>
