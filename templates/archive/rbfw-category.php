<?php
/**
 * Rent type / location archive.
 *
 * Without this, /?rbfw_item_caregory=<slug> fell through to the THEME's blog archive: one
 * enormous image per rental, an excerpt and a post date — no price, no booking link, no way
 * to switch view. This renders the plugin's own listing instead, so a category page looks and
 * behaves like the rental listing everywhere else in the plugin.
 *
 * The listing itself is [rent-list], which already owns the grid/list toggle (persisted in the
 * rbfw_rent_item_list_grid cookie), the left-hand filters, pricing and pagination — reusing it
 * keeps one card system rather than a second one that drifts.
 *
 * Override by copying to yourtheme/templates/archive/rbfw-category.php.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rbfw_term = get_queried_object();
$rbfw_name = ( $rbfw_term instanceof WP_Term ) ? $rbfw_term->name : '';
$rbfw_desc = ( $rbfw_term instanceof WP_Term ) ? term_description( $rbfw_term ) : '';
$rbfw_count = ( $rbfw_term instanceof WP_Term ) ? (int) $rbfw_term->count : 0;

/*
 * Header / footer, both theme kinds.
 *
 * get_header() only renders a real header on CLASSIC themes. Under a block theme there is no
 * header.php, so WordPress falls back to a bare document — the site title and "proudly powered
 * by WordPress" — which is what this page used to show. Block themes expose their header and
 * footer as template PARTS, so they have to be rendered as blocks, and the document itself
 * (doctype, wp_head, body_class) becomes ours to emit.
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
	<body <?php body_class( 'rbfw-archive-page' ); ?>>
	<?php
	wp_body_open();
	echo do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output.
} else {
	get_header();
}
?>
<div class="rbfw-archive mpStyle">
	<div class="rbfw-archive-head">
		<div class="rbfw-archive-head-inner">
			<?php if ( $rbfw_term instanceof WP_Term ) : ?>
				<nav class="rbfw-archive-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'booking-and-rental-manager-for-woocommerce' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'booking-and-rental-manager-for-woocommerce' ); ?></a>
					<span aria-hidden="true">/</span>
					<span><?php echo esc_html( $rbfw_name ); ?></span>
				</nav>
			<?php endif; ?>

			<h1 class="rbfw-archive-title"><?php echo esc_html( $rbfw_name ); ?></h1>

			<p class="rbfw-archive-count">
				<?php
				printf(
					/* translators: %s: number of rentals in this category */
					esc_html( _n( '%s rental available', '%s rentals available', $rbfw_count, 'booking-and-rental-manager-for-woocommerce' ) ),
					esc_html( number_format_i18n( $rbfw_count ) )
				);
				?>
			</p>

			<?php if ( $rbfw_desc ) : ?>
				<div class="rbfw-archive-desc"><?php echo wp_kses_post( $rbfw_desc ); ?></div>
			<?php endif; ?>
		</div>
	</div>

	<div class="rbfw-archive-body">
		<?php
		if ( $rbfw_count > 0 && $rbfw_term instanceof WP_Term ) {
			/*
			 * Passed by term id, not name: ids are unambiguous when two taxonomies happen to
			 * share a term name. The location taxonomy filters on its own attribute.
			 */
			$rbfw_attr = ( 'rbfw_item_location' === $rbfw_term->taxonomy )
				? sprintf( 'location="%s"', esc_attr( $rbfw_term->name ) )
				: sprintf( 'category="%d"', (int) $rbfw_term->term_id );

			echo do_shortcode( '[rent-list ' . $rbfw_attr . ' style="grid" show="12"]' );
		} else {
			?>
			<div class="rbfw-archive-empty">
				<i class="fas fa-box-open" aria-hidden="true"></i>
				<strong><?php esc_html_e( 'Nothing here yet', 'booking-and-rental-manager-for-woocommerce' ); ?></strong>
				<span><?php esc_html_e( 'No rentals are listed under this category at the moment.', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
				<a class="rbfw-archive-empty-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Browse all rentals', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</a>
			</div>
			<?php
		}
		?>
	</div>
</div>
<?php
if ( $rbfw_is_block_theme ) {
	echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output.
	wp_footer();
	echo '</body></html>';
} else {
	get_footer();
}
