<?php
/**
 * [rbfw_rent_types] — a "browse by rent type" grid, one card per
 * rbfw_item_caregory term (image, name, item count), linking to that
 * term's own archive page (templates/archive/rbfw-category.php already
 * renders that as a full [rent-list] listing). Mirrors the visual
 * language of that archive's header (.rbfw-archive-*) so the two pages
 * read as the same product.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! function_exists( 'rbfw_rent_types_shortcode' ) ) {
	/**
	 * @param array $atts {
	 *     @type string $title       Heading above the grid. Empty hides it.
	 *     @type string $columns     Grid columns at desktop width. Default 4.
	 *     @type string $hide_empty  'yes' to only list types with at least one
	 *                               published rental. Default 'no' — show every
	 *                               rent type that exists.
	 * }
	 */
	function rbfw_rent_types_shortcode( $atts = array() ) {
		$a = shortcode_atts(
			array(
				'title'      => __( 'Browse by Rent Type', 'booking-and-rental-manager-for-woocommerce' ),
				'columns'    => 4,
				'hide_empty' => 'no',
			),
			$atts,
			'rbfw_rent_types'
		);

		$terms = get_terms( array(
			'taxonomy'   => 'rbfw_item_caregory',
			'hide_empty' => ( 'yes' === $a['hide_empty'] ),
			'orderby'    => 'name',
			'order'      => 'ASC',
		) );

		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		$columns  = max( 2, min( 6, absint( $a['columns'] ) ) );
		$no_image = RBFW_PLUGIN_URL . '/assets/images/no_image.png';

		ob_start();
		?>
		<div class="rbfw-rt-archive">
			<?php if ( '' !== trim( (string) $a['title'] ) ) : ?>
				<div class="rbfw-rt-archive-head">
					<h2 class="rbfw-rt-archive-title"><?php echo esc_html( $a['title'] ); ?></h2>
					<?php if ( ! empty( $terms ) ) : ?>
						<p class="rbfw-rt-archive-count">
							<?php
							printf(
								/* translators: %s: number of rent types. */
								esc_html( _n( '%s rent type available', '%s rent types available', count( $terms ), 'booking-and-rental-manager-for-woocommerce' ) ),
								esc_html( number_format_i18n( count( $terms ) ) )
							);
							?>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( empty( $terms ) ) : ?>
				<div class="rbfw-archive-empty">
					<i class="fas fa-box-open" aria-hidden="true"></i>
					<strong><?php esc_html_e( 'Nothing here yet', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
					<span><?php esc_html_e( 'No rent types have been set up yet.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				</div>
			<?php else : ?>
				<div class="rbfw-rt-grid" style="--rbfw-rt-cols: <?php echo esc_attr( $columns ); ?>;">
					<?php foreach ( $terms as $term ) :
						$image_id  = (int) get_term_meta( $term->term_id, 'rentiva_category_image_id', true );
						$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
						$count     = (int) $term->count;
						?>
						<a class="rbfw-rt-card" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
							<div class="rbfw-rt-card__media">
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy">
								<?php else : ?>
									<img src="<?php echo esc_url( $no_image ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy">
								<?php endif; ?>
								<span class="rbfw-rt-card__count">
									<?php
									printf(
										/* translators: %s: number of rentals in this type. */
										esc_html( _n( '%s rental', '%s rentals', $count, 'booking-and-rental-manager-for-woocommerce' ) ),
										esc_html( number_format_i18n( $count ) )
									);
									?>
								</span>
							</div>
							<div class="rbfw-rt-card__body">
								<h3 class="rbfw-rt-card__title"><?php echo esc_html( $term->name ); ?></h3>
								<span class="rbfw-rt-card__cta">
									<?php esc_html_e( 'View Rentals', 'booking-and-rental-manager-for-woocommerce' ); ?>
									<i class="fas fa-arrow-right" aria-hidden="true"></i>
								</span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
	add_shortcode( 'rbfw_rent_types', 'rbfw_rent_types_shortcode' );
}
