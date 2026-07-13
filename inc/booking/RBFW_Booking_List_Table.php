<?php
/**
 * FREE teaser "Bookings" page.
 *
 * Lists BOTH custom (rbfw_booking) and WooCommerce (rbfw_order) bookings in one table
 * via the shared RBFW_Booking_Normalizer — the same single source of truth the Pro
 * "Bookings" page uses. This is the limited, free-tier version:
 *
 *   - Statistics + the filter panel are shown blurred behind a "PRO" overlay (and
 *     deliberately carry NO real data behind the blur).
 *   - "Detail" and "Change Status" are rendered as locked actions with a PRO badge.
 *   - There are no checkboxes / bulk actions.
 *   - Only DELETE actually works.
 *
 * It STANDS DOWN automatically when the Pro plugin is active: it registers nothing, so
 * the Pro "Bookings" page (same menu slug, rbfw_booking_orders) is the only listing.
 *
 * Fully self-contained: depends only on jQuery + dashicons — never on a Pro-only asset
 * (e.g. the Pro toast helper) — so it cannot fatal when Pro is absent.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Booking_List_Table' ) ) {

	class RBFW_Booking_List_Table {

		const MENU_SLUG = 'rbfw_booking_orders';
		const PER_PAGE  = 20;

		public function __construct() {
			// Stand down entirely when Pro is active — Pro owns the same menu slug.
			if ( function_exists( 'rbfw_check_pro_active' ) && rbfw_check_pro_active() ) {
				return;
			}
			add_action( 'admin_menu', array( $this, 'add_menu' ), 11 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_ajax_rbfw_free_booking_delete', array( $this, 'ajax_delete' ) );
		}

		public function add_menu() {
			add_submenu_page(
				'edit.php?post_type=rbfw_item',
				esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				esc_html__( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ),
				rbfw_bookings_capability(),
				self::MENU_SLUG,
				array( $this, 'render_page' )
			);
		}

		/** Whether we're on the Bookings admin screen. */
		private function is_bookings_screen( $hook ) {
			return is_string( $hook ) && false !== strpos( $hook, self::MENU_SLUG );
		}

		public function enqueue_assets( $hook ) {
			if ( ! $this->is_bookings_screen( $hook ) ) {
				return;
			}
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style(
				'rbfw-bookings-free',
				RBFW_PLUGIN_URL . '/assets/admin/rbfw-bookings-free.css',
				array(),
				filemtime( RBFW_PLUGIN_DIR . '/assets/admin/rbfw-bookings-free.css' )
			);
			wp_enqueue_script(
				'rbfw-bookings-free',
				RBFW_PLUGIN_URL . '/assets/admin/rbfw-bookings-free.js',
				array( 'jquery' ),
				filemtime( RBFW_PLUGIN_DIR . '/assets/admin/rbfw-bookings-free.js' ),
				true
			);
			wp_localize_script( 'rbfw-bookings-free', 'rbfwBookingsFree', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'rbfw_free_bookings' ),
				'i18n'    => array(
					'deleted'     => esc_html__( 'Booking moved to Trash.', 'booking-and-rental-manager-for-woocommerce' ),
					'deleteError' => esc_html__( 'Could not delete the booking.', 'booking-and-rental-manager-for-woocommerce' ),
					'proOnly'     => esc_html__( 'This is a PRO feature. Upgrade to unlock it.', 'booking-and-rental-manager-for-woocommerce' ),
				),
			) );
		}

		/* ------------------------------------------------------------------ *
		 * AJAX: delete (the only working action in the free teaser)
		 * ------------------------------------------------------------------ */

		/**
		 * Source-aware, WooCommerce-guarded delete:
		 *   - custom rows      → trash the rbfw_booking post + release reserved inventory.
		 *   - WooCommerce rows → trash the rbfw_order mirror ONLY (the real WC order is left
		 *     untouched). No WooCommerce function is called, so this never fatals with WC off.
		 */
		public function ajax_delete() {
			check_ajax_referer( 'rbfw_free_bookings', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'booking-and-rental-manager-for-woocommerce' ) ), 403 );
			}

			$id = isset( $_POST['booking_id'] ) ? absint( wp_unslash( $_POST['booking_id'] ) ) : 0;
			if ( ! $id || ! $this->is_booking_post( $id ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid booking.', 'booking-and-rental-manager-for-woocommerce' ) ) );
			}

			if ( RBFW_Booking_Normalizer::SOURCE_CUSTOM === RBFW_Booking_Normalizer::detect_source( $id )
				&& class_exists( 'RBFW_Booking_Actions' ) ) {
				$old = get_post_meta( $id, 'rbfw_status', true );
				if ( 'cancelled' !== RBFW_Booking_Normalizer::normalize_status( $old ) ) {
					RBFW_Booking_Actions::apply_transition( $id, 'cancelled', $old );
				}
			}

			wp_trash_post( $id );

			wp_send_json_success( array(
				'id'      => $id,
				'message' => esc_html__( 'Booking moved to Trash.', 'booking-and-rental-manager-for-woocommerce' ),
			) );
		}

		private function is_booking_post( $id ) {
			$type = get_post_type( $id );
			return RBFW_Booking_Normalizer::CPT_CUSTOM === $type || RBFW_Booking_Normalizer::CPT_WOO === $type;
		}

		/* ------------------------------------------------------------------ *
		 * Render
		 * ------------------------------------------------------------------ */

		public function render_page() {
			if ( ! current_user_can( rbfw_bookings_capability() ) ) {
				return;
			}

			$index = RBFW_Booking_Normalizer::query_index();
			$total = count( $index );
			$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
			$pages = max( 1, (int) ceil( $total / self::PER_PAGE ) );
			$rows  = RBFW_Booking_Normalizer::hydrate(
				array_slice( $index, ( $paged - 1 ) * self::PER_PAGE, self::PER_PAGE )
			);
			?>
			<div class="rbfwfb-wrap">

				<div class="rbfwfb-header">
					<div>
						<h1 class="rbfwfb-title"><span class="dashicons dashicons-clipboard"></span><?php esc_html_e( 'Bookings', 'booking-and-rental-manager-for-woocommerce' ); ?></h1>
						<p class="rbfwfb-subtitle"><?php esc_html_e( 'Every booking — WooCommerce and custom checkout — in one place.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
					</div>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_go_pro_page' ) ); ?>" class="rbfwfb-pro-cta"><span class="dashicons dashicons-star-filled"></span><?php esc_html_e( 'Upgrade to PRO', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
				</div>

				<?php $this->render_locked_stats(); ?>

				<?php $this->render_locked_filters(); ?>

				<div class="rbfwfb-table-wrap">
					<div class="rbfwfb-table-toolbar">
						<span class="rbfwfb-count">
							<?php
							/* translators: %s: number of bookings. */
							echo esc_html( sprintf( _n( '%s booking', '%s bookings', $total, 'booking-and-rental-manager-for-woocommerce' ), number_format_i18n( $total ) ) );
							?>
						</span>
					</div>

					<table class="rbfwfb-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Booking', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Customer', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Item', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Total', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Status', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Date', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
								<th class="rbfwfb-col-actions"><?php esc_html_e( 'Actions', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $rows ) ) : ?>
								<tr><td colspan="7" class="rbfwfb-empty"><span class="dashicons dashicons-clipboard"></span><p><?php esc_html_e( 'No bookings found.', 'booking-and-rental-manager-for-woocommerce' ); ?></p></td></tr>
							<?php else : ?>
								<?php foreach ( $rows as $row ) { $this->render_row( $row ); } ?>
							<?php endif; ?>
						</tbody>
					</table>

					<?php $this->render_pagination( $paged, $pages, $total ); ?>
				</div>

				<?php $this->render_delete_modal(); ?>
			</div>
			<?php
		}

		private function render_locked_stats() {
			// Deliberately no real data behind the blur (pitfall #6).
			$cards = array(
				array( 'dashicons-cart', esc_html__( 'Total Bookings', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'dashicons-money-alt', esc_html__( 'Total Revenue', 'booking-and-rental-manager-for-woocommerce' ) ),
				array( 'dashicons-clock', esc_html__( 'Pending', 'booking-and-rental-manager-for-woocommerce' ) ),
			);
			?>
			<div class="rbfwfb-locked">
				<div class="rbfwfb-stats" aria-hidden="true">
					<?php foreach ( $cards as $card ) : ?>
						<div class="rbfwfb-stat">
							<span class="rbfwfb-stat-icon dashicons <?php echo esc_attr( $card[0] ); ?>"></span>
							<div>
								<span class="rbfwfb-stat-value">•••</span>
								<span class="rbfwfb-stat-label"><?php echo esc_html( $card[1] ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="rbfwfb-lock-overlay">
					<span class="rbfwfb-pro-badge"><span class="dashicons dashicons-lock"></span><?php esc_html_e( 'PRO', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					<p><?php esc_html_e( 'Booking analytics & revenue insights are a PRO feature.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>
			</div>
			<?php
		}

		private function render_locked_filters() {
			?>
			<div class="rbfwfb-locked rbfwfb-locked-filters">
				<div class="rbfwfb-filters" aria-hidden="true">
					<div class="rbfwfb-filter-field">
						<span class="dashicons dashicons-search"></span>
						<input type="text" placeholder="<?php esc_attr_e( 'Search bookings…', 'booking-and-rental-manager-for-woocommerce' ); ?>" disabled />
					</div>
					<select disabled><option><?php esc_html_e( 'All Sources', 'booking-and-rental-manager-for-woocommerce' ); ?></option></select>
					<select disabled><option><?php esc_html_e( 'All Statuses', 'booking-and-rental-manager-for-woocommerce' ); ?></option></select>
					<select disabled><option><?php esc_html_e( 'All Items', 'booking-and-rental-manager-for-woocommerce' ); ?></option></select>
				</div>
				<div class="rbfwfb-lock-overlay">
					<span class="rbfwfb-pro-badge"><span class="dashicons dashicons-lock"></span><?php esc_html_e( 'PRO', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					<p><?php esc_html_e( 'Search, filtering & CSV export are available in PRO.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				</div>
			</div>
			<?php
		}

		private function render_row( $row ) {
			$is_woo = RBFW_Booking_Normalizer::SOURCE_WOO === $row['source'];
			?>
			<tr data-row-id="<?php echo esc_attr( $row['id'] ); ?>">
				<td>
					<strong><?php echo esc_html( $row['reference'] ); ?></strong>
					<span class="rbfwfb-sub">#<?php echo esc_html( $row['id'] ); ?></span>
					<span class="rbfwfb-source rbfwfb-source-<?php echo esc_attr( $is_woo ? 'woo' : 'custom' ); ?>">
						<span class="dashicons <?php echo esc_attr( $is_woo ? 'dashicons-cart' : 'dashicons-admin-users' ); ?>"></span><?php echo esc_html( $row['source_label'] ); ?>
					</span>
				</td>
				<td>
					<strong><?php echo esc_html( $row['customer_name'] ? $row['customer_name'] : '—' ); ?></strong>
					<?php if ( $row['customer_email'] ) : ?><br><small><?php echo esc_html( $row['customer_email'] ); ?></small><?php endif; ?>
				</td>
				<td><?php echo esc_html( $row['item_name'] ); ?></td>
				<td><strong><?php echo wp_kses_post( $row['total'] ); ?></strong></td>
				<td><span class="rbfwfb-status <?php echo esc_attr( $row['status_class'] ); ?>"><?php echo esc_html( $row['status_label'] ); ?></span></td>
				<td><?php echo esc_html( $row['date'] ); ?></td>
				<td class="rbfwfb-col-actions">
					<span class="rbfwfb-locked-action" title="<?php esc_attr_e( 'Available in PRO', 'booking-and-rental-manager-for-woocommerce' ); ?>">
						<span class="dashicons dashicons-visibility"></span>
						<span class="rbfwfb-mini-pro"><?php esc_html_e( 'PRO', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</span>
					<span class="rbfwfb-locked-action" title="<?php esc_attr_e( 'Available in PRO', 'booking-and-rental-manager-for-woocommerce' ); ?>">
						<span class="dashicons dashicons-update"></span>
						<span class="rbfwfb-mini-pro"><?php esc_html_e( 'PRO', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
					</span>
					<button type="button" class="rbfwfb-del-btn" data-id="<?php echo esc_attr( $row['id'] ); ?>" data-ref="<?php echo esc_attr( $row['reference'] ); ?>" data-source="<?php echo esc_attr( $row['source'] ); ?>" title="<?php esc_attr_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?>">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</td>
			</tr>
			<?php
		}

		private function render_pagination( $current, $pages, $total ) {
			if ( $pages < 2 ) {
				return;
			}
			echo '<div class="rbfwfb-pagination">';
			echo wp_kses_post( paginate_links( array(
				'base'      => add_query_arg( 'paged', '%#%' ),
				'format'    => '',
				'current'   => $current,
				'total'     => $pages,
				'prev_text' => '‹',
				'next_text' => '›',
			) ) );
			echo '</div>';
		}

		private function render_delete_modal() {
			?>
			<div id="rbfwfb-delete-modal" class="rbfwfb-modal" style="display:none;">
				<div class="rbfwfb-modal-card">
					<div class="rbfwfb-modal-head">
						<h2><span class="dashicons dashicons-trash"></span><?php esc_html_e( 'Delete Booking', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
						<span class="rbfwfb-modal-close dashicons dashicons-no-alt" role="button" aria-label="<?php esc_attr_e( 'Close', 'booking-and-rental-manager-for-woocommerce' ); ?>"></span>
					</div>
					<div class="rbfwfb-modal-body">
						<input type="hidden" id="rbfwfb-delete-id" value="">
						<p>
							<?php
							printf(
								/* translators: %s: booking reference. */
								esc_html__( 'Delete booking %s? It will be moved to Trash.', 'booking-and-rental-manager-for-woocommerce' ),
								'<strong id="rbfwfb-delete-ref">#0</strong>'
							);
							?>
						</p>
						<p class="rbfwfb-modal-note" id="rbfwfb-delete-note" style="display:none;">
							<span class="dashicons dashicons-info-outline"></span>
							<?php esc_html_e( 'The linked WooCommerce order is not affected — only this rental booking record is removed.', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</p>
						<div class="rbfwfb-modal-actions">
							<button type="button" class="rbfwfb-btn rbfwfb-btn-outline rbfwfb-modal-close"><?php esc_html_e( 'Cancel', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
							<button type="button" id="rbfwfb-delete-confirm" class="rbfwfb-btn rbfwfb-btn-danger"><span class="dashicons dashicons-trash"></span><?php esc_html_e( 'Delete', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	new RBFW_Booking_List_Table();
}
