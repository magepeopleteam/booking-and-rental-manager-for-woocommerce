<?php
	/**
	 * @author Shahadat hossain <raselsha@gmail.com>
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'RBFW_Related' ) ) {
		class RBFW_Related {
			public function __construct() {
				add_action( 'rbfw_meta_box_tab_name', [ $this, 'add_tab_menu' ] );
				add_action( 'rbfw_meta_box_tab_content', [ $this, 'add_tabs_content' ] );
				add_action( 'save_post', [ $this, 'settings_save' ], 99, 1 );
			}

			public function add_tab_menu() {
				?>
                <li data-target-tabs="#rbfw_related"><i class="fas fa-plug"></i><?php esc_html_e( 'Related', 'booking-and-rental-manager-for-woocommerce' ); ?></li>
				<?php
			}

			public function section_header() {
				?>
                <h2 class="mp_tab_item_title"><?php echo esc_html__( 'Related Items', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
                <p class="mp_tab_item_description"><?php echo esc_html__( 'Here you can configure related items.', 'booking-and-rental-manager-for-woocommerce' ); ?></p>
				<?php
			}

			public function panel_header( $title, $description ) {
				?>
                <section class="bg-light mt-5">
                    <div>
                        <label><?php echo esc_html( $title ); ?></label>
                        <P><?php echo esc_html( $description ); ?></P>
                    </div>
                </section>
				<?php
			}

			public function related_items( $post_id ) {
				?>
                <section>
                    <div id="rbfw_releted_rbfw" class=" field-wrapper field-select2-wrapper field-select2-wrapper-rbfw_releted_rbfw w-100">
                        <select name="rbfw_releted_rbfw[]" id="rbfw_releted_rbfw" multiple="" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
							<?php
								$releted_post_id = get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ? maybe_unserialize( get_post_meta( $post_id, 'rbfw_releted_rbfw', true ) ) : [];
								$the_query       = new WP_Query( array(
									'post_type'      => 'rbfw_item',
									'posts_per_page' => - 1,
								) );
							?>
							<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                <option
									<?php echo ( in_array( get_the_ID(), $releted_post_id ) ) ? 'selected' : ''; ?>
                                    value="<?php echo esc_attr( get_the_ID() ); ?>">
									<?php echo esc_html( get_the_title() ); ?>
                                </option>
							<?php endwhile; ?>
                        </select>
                    </div>
                </section>
				<?php
			}

			public function add_tabs_content( $post_id ) {
				?>
                <div class="mpStyle mp_tab_item " data-tab-item="#rbfw_related">
					<?php $this->section_header(); ?>
					<?php $this->panel_header( 'Related Items', 'Related Items' ); ?>
					<?php $this->related_items( $post_id ); ?>
                </div>
				<?php
			}

			public static function render_for_modern_editor( int $post_id ): void {
				$selected_ids = get_post_meta( $post_id, 'rbfw_releted_rbfw', true );
				$selected_ids = $selected_ids ? maybe_unserialize( $selected_ids ) : [];
				$selected_ids = is_array( $selected_ids ) ? array_map( 'intval', $selected_ids ) : [];

				$all_posts = get_posts( [
					'post_type'      => 'rbfw_item',
					'posts_per_page' => -1,
					'post_status'    => [ 'publish', 'draft' ],
					'orderby'        => 'title',
					'order'          => 'ASC',
					'fields'         => 'ids',
				] );

				$items_map = [];
				foreach ( $all_posts as $id ) {
					$items_map[ (int) $id ] = get_the_title( $id );
				}
				?>
				<input type="hidden" name="rbfw_has_related_picker" value="1">
				<div class="rbfw-me-tag-picker">
					<div class="rbfw-me-tag-picker__field">
						<?php foreach ( $selected_ids as $sid ) :
							$sid = (int) $sid;
							if ( ! isset( $items_map[ $sid ] ) ) continue; ?>
							<div class="rbfw-me-tag" data-id="<?php echo esc_attr( $sid ); ?>">
								<span><?php echo esc_html( $items_map[ $sid ] ); ?></span>
								<button type="button" class="rbfw-me-tag__remove" aria-label="<?php esc_attr_e( 'Remove', 'booking-and-rental-manager-for-woocommerce' ); ?>">
									<span class="dashicons dashicons-no-alt"></span>
								</button>
								<input type="hidden" name="rbfw_releted_rbfw[]" value="<?php echo esc_attr( $sid ); ?>">
							</div>
						<?php endforeach; ?>
						<input type="text" class="rbfw-me-tag-picker__search" placeholder="<?php esc_attr_e( 'Search items…', 'booking-and-rental-manager-for-woocommerce' ); ?>" autocomplete="off">
					</div>
					<div class="rbfw-me-tag-picker__dropdown rbfw-me-hidden">
						<?php foreach ( $items_map as $id => $title ) : ?>
							<div class="rbfw-me-tag-picker__option<?php echo in_array( $id, $selected_ids, true ) ? ' is-selected' : ''; ?>"
								data-id="<?php echo esc_attr( $id ); ?>"
								data-title="<?php echo esc_attr( $title ); ?>">
								<?php echo esc_html( $title ); ?>
							</div>
						<?php endforeach; ?>
						<div class="rbfw-me-tag-picker__no-results rbfw-me-hidden">
							<?php esc_html_e( 'No items found.', 'booking-and-rental-manager-for-woocommerce' ); ?>
						</div>
					</div>
				</div>
				<?php
			}

			public function settings_save( $post_id ) {
				if ( ! isset( $_POST['rbfw_ticket_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbfw_ticket_type_nonce'] ) ), 'rbfw_ticket_type_nonce' ) ) {
					return;
				}
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				if ( get_post_type( $post_id ) == 'rbfw_item' ) {
					$related_categories = isset( $_POST['rbfw_releted_rbfw'] ) ? RBFW_Function::data_sanitize( $_POST['rbfw_releted_rbfw'] ) : [];
					// Update the post meta
					update_post_meta( $post_id, 'rbfw_releted_rbfw', $related_categories );
				}
			}
		}
		new RBFW_Related();
	}