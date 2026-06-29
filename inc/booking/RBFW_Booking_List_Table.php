<?php
/**
 * Admin "Bookings" list for native (standalone) bookings.
 *
 * Adds a submenu under the Rental menu that lists rbfw_booking records (read-only in
 * Phase 1). WooCommerce orders continue to use the existing "Order List" page; this page
 * surfaces bookings created through the standalone flow.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_List_Table' ) ) {
	class RBFW_Booking_List_Table {

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_menu' ), 11 );
		}

		public function add_menu() {
			add_submenu_page(
				'edit.php?post_type=rbfw_item',
				esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				'manage_options',
				'rbfw_bookings',
				array( $this, 'render_page' )
			);
		}

		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
			$query = new WP_Query( array(
				'post_type'      => RBFW_Booking_Post_Type::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'paged'          => $paged,
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );

			echo '<div class="wrap">';
			echo '<h1>' . esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ) . '</h1>';

			if ( isset( $_GET['rbfw_status_updated'] ) ) {
				if ( '1' === (string) $_GET['rbfw_status_updated'] ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Booking status updated.', 'booking-and-rental-manager-for-woocommerce' ) . '</p></div>';
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Could not update the booking status.', 'booking-and-rental-manager-for-woocommerce' ) . '</p></div>';
				}
			}

			if ( ! RBFW_Function::use_wc() ) {
				echo '<p class="description">' . esc_html__( 'Bookings created through the standalone (non-WooCommerce) flow.', 'booking-and-rental-manager-for-woocommerce' ) . '</p>';
			}

			if ( ! $query->have_posts() ) {
				echo '<p>' . esc_html__( 'No bookings found.', 'booking-and-rental-manager-for-woocommerce' ) . '</p>';
				echo '</div>';
				return;
			}

			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Reference', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '<th>' . esc_html__( 'Item', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '<th>' . esc_html__( 'Customer', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '<th>' . esc_html__( 'Dates', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '<th>' . esc_html__( 'Total', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '<th>' . esc_html__( 'Status', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '<th>' . esc_html__( 'Created', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '<th>' . esc_html__( 'Change Status', 'booking-and-rental-manager-for-woocommerce' ) . '</th>';
			echo '</tr></thead><tbody>';

			while ( $query->have_posts() ) {
				$query->the_post();
				$id        = get_the_ID();
				$reference = get_post_meta( $id, 'rbfw_reference', true );
				$item_name = get_post_meta( $id, 'rbfw_item_name', true );
				$cust_name = get_post_meta( $id, 'rbfw_customer_name', true );
				$cust_mail = get_post_meta( $id, 'rbfw_customer_email', true );
				$start     = get_post_meta( $id, 'rbfw_start_date', true );
				$end       = get_post_meta( $id, 'rbfw_end_date', true );
				$total     = (float) get_post_meta( $id, 'rbfw_total', true );
				$status    = get_post_meta( $id, 'rbfw_status', true );

				$dates = $start;
				if ( $end && $end !== $start ) {
					$dates .= ' &rarr; ' . $end;
				}

				echo '<tr>';
				echo '<td>' . esc_html( $reference ) . '</td>';
				echo '<td>' . esc_html( $item_name ) . '</td>';
				echo '<td>' . esc_html( $cust_name );
				if ( $cust_mail ) {
					echo '<br><small>' . esc_html( $cust_mail ) . '</small>';
				}
				echo '</td>';
				echo '<td>' . wp_kses_post( $dates ) . '</td>';
				echo '<td>' . wp_kses_post( wc_price( $total ) ) . '</td>';
				echo '<td><span class="rbfw-booking-status rbfw-booking-status--' . esc_attr( $status ) . '">' . esc_html( ucfirst( $status ) ) . '</span></td>';
				echo '<td>' . esc_html( get_the_date() . ' ' . get_the_time() ) . '</td>';
				echo '<td>';
				if ( class_exists( 'RBFW_Booking_Actions' ) ) {
					RBFW_Booking_Actions::render_status_control( $id, $status );
				}
				echo '</td>';
				echo '</tr>';
			}
			wp_reset_postdata();

			echo '</tbody></table>';

			$total_pages = (int) $query->max_num_pages;
			if ( $total_pages > 1 ) {
				echo '<div class="tablenav"><div class="tablenav-pages">';
				echo wp_kses_post( paginate_links( array(
					'base'    => add_query_arg( 'paged', '%#%' ),
					'format'  => '',
					'current' => $paged,
					'total'   => $total_pages,
				) ) );
				echo '</div></div>';
			}

			echo '</div>';
		}
	}
	new RBFW_Booking_List_Table();
}
