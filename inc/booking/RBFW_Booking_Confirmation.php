<?php
/**
 * Renders the booking confirmation/pending page after a native (standalone) checkout
 * redirects back with ?rbfw_booking=success|pending|cancelled.
 *
 * Used to inject the notice into the rental item page itself (hooked into
 * rbfw_single_page_before_wrapper), so a customer landed back on the same item page —
 * hero image, gallery, booking form and all — with a small banner glued on top of it.
 * Now intercepts on `template_redirect` (before WordPress decides which template to
 * load for the current URL) and prints a full, standalone page instead — its own
 * document, header and footer, with just the confirmation panel as the content — then
 * exits, so none of the item page's own template ever runs for this request.
 *
 * Mirrors mage-eventpress: inc/MPWEM_Booking_Confirmation.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_Confirmation' ) ) {
	class RBFW_Booking_Confirmation {

		/** @var string Cached for the document_title_parts filter. */
		private $title = '';

		public function __construct() {
			add_action( 'template_redirect', array( $this, 'render' ) );
			add_filter( 'document_title_parts', array( $this, 'filter_title' ) );
		}

		/**
		 * Whether the current request is a booking-confirmation redirect.
		 *
		 * @return bool
		 */
		private function is_confirmation_request() {
			$status = isset( $_GET['rbfw_booking'] ) ? sanitize_key( wp_unslash( $_GET['rbfw_booking'] ) ) : '';
			return in_array( $status, array( 'success', 'pending', 'cancelled' ), true );
		}

		public function filter_title( $title_parts ) {
			if ( $this->is_confirmation_request() && $this->title ) {
				$title_parts['title'] = $this->title;
			}
			return $title_parts;
		}

		public function render() {
			if ( ! $this->is_confirmation_request() ) {
				return;
			}

			$status    = sanitize_key( wp_unslash( $_GET['rbfw_booking'] ) );
			$reference = isset( $_GET['rbfw_booking_id'] ) ? sanitize_text_field( wp_unslash( $_GET['rbfw_booking_id'] ) ) : '';

			// Resolve the real booking so the ticket-download gate reflects the booking's
			// CURRENT status (it can change after this redirect, e.g. an admin marking an
			// offline payment received), not just the one-time status hint in the URL.
			$booking_id = $this->find_booking_by_reference( $reference );

			$this->title = array(
				'success'   => esc_html__( 'Booking Confirmed', 'booking-and-rental-manager-for-woocommerce' ),
				'cancelled' => esc_html__( 'Booking Cancelled', 'booking-and-rental-manager-for-woocommerce' ),
			);
			$this->title = isset( $this->title[ $status ] ) ? $this->title[ $status ] : esc_html__( 'Booking Received', 'booking-and-rental-manager-for-woocommerce' );

			$template = RBFW_Function::get_template_path( 'layout/booking_confirmation.php' );
			if ( ! $template || ! file_exists( $template ) ) {
				return; // Nothing to show — let the request fall through to its normal template.
			}

			/*
			 * Full standalone document, both theme kinds. get_header()/get_footer() only
			 * render real markup on CLASSIC themes — a block theme has no header.php, so
			 * WordPress would fall back to a bare document. Block themes expose their
			 * header/footer as template PARTS instead, so the document itself (doctype,
			 * wp_head, body_class) becomes ours to emit. Same pattern as
			 * templates/archive/rbfw-category.php.
			 */
			$rbfw_is_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

			if ( $rbfw_is_block_theme ) {
				?>
				<!doctype html>
				<html <?php language_attributes(); ?>>
				<head>
					<meta charset="<?php bloginfo( 'charset' ); ?>">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<?php wp_head(); ?>
				</head>
				<body <?php body_class( 'rbfw-booking-confirmation-page' ); ?>>
				<?php
				wp_body_open();
				echo do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output.
			} else {
				get_header();
			}
			?>
			<div class="rbfw-booking-confirmation-page" style="max-width:1240px;margin:0 auto;padding:48px 20px 80px;">
				<?php include $template; // Expects $status, $reference, $booking_id in scope. ?>
			</div>
			<?php
			if ( $rbfw_is_block_theme ) {
				echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output.
				wp_footer();
				echo '</body></html>';
			} else {
				get_footer();
			}
			exit;
		}

		/**
		 * Resolve the rbfw_booking post id from its reference. Returns 0 when not found.
		 *
		 * @param string $reference
		 * @return int
		 */
		private function find_booking_by_reference( $reference ) {
			$reference = trim( (string) $reference );
			if ( '' === $reference ) {
				return 0;
			}
			$ids = get_posts( array(
				'post_type'        => 'rbfw_booking',
				'post_status'      => 'publish',
				'numberposts'      => 1,
				'fields'           => 'ids',
				'meta_key'         => 'rbfw_reference',
				'meta_value'       => $reference,
				'no_found_rows'    => true,
				'suppress_filters' => true,
			) );
			return ! empty( $ids ) ? absint( $ids[0] ) : 0;
		}
	}
	new RBFW_Booking_Confirmation();
}
