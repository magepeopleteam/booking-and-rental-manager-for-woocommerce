<?php
/**
 * [rbfw_item_search] — inline search box for rental items by name.
 *
 * Renders a single text field (no dates / location / type dropdowns) that
 * lets visitors type the name of a rental item they are looking for. On
 * submit it sends the term to the configured search result page where
 * [search-result] renders the matching items.
 *
 * Usage:
 *   [rbfw_item_search]
 *   [rbfw_item_search placeholder="Find a bike" button_text="Search" result_page="42"]
 *
 * The result page must contain the [search-result] shortcode (or the page
 * selected in Settings -> Search Page). The term is carried as the
 * `rbfw_search_item_name` query parameter.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_shortcode( 'rbfw_item_search', 'rbfw_item_search_shortcode' );

/**
 * Hide the theme-rendered "Booking Search" page title (the H1 above the
 * search form) so the page shows only the inline search box. The browser-tab
 * title is separate (document_title_parts) and stays untouched.
 */
add_filter( 'the_title', 'rbfw_item_search_hide_booking_page_title', 10, 2 );
function rbfw_item_search_hide_booking_page_title( $title, $id ) {
	if ( ! in_the_loop() ) {
		return $title;
	}
	$search_page = get_page_by_path( 'booking-search' );
	if ( $search_page && (int) $id === (int) $search_page->ID && is_page( $search_page->ID ) ) {
		return '';
	}

	return $title;
}

function rbfw_item_search_shortcode( $atts = null ) {
	$attributes = shortcode_atts(
		array(
			'placeholder' => '',
			'button_text' => '',
			'result_page' => '',
		),
		$atts
	);

	$placeholder = '' !== trim( (string) $attributes['placeholder'] )
		? sanitize_text_field( $attributes['placeholder'] )
		: __( 'Search rental items by name…', 'booking-and-rental-manager-for-woocommerce' );

	$button_text = '' !== trim( (string) $attributes['button_text'] )
		? sanitize_text_field( $attributes['button_text'] )
		: __( 'Search', 'booking-and-rental-manager-for-woocommerce' );

	/* Where should the form land? Priority: shortcode attr -> the plugin's own
	 * "search-item-list" page (it already renders [search-result]) -> the
	 * Settings "Search Page" -> the home page. */
	$result_url = '';
	if ( '' !== trim( (string) $attributes['result_page'] ) ) {
		$result_page_id = absint( $attributes['result_page'] );
		$result_url     = $result_page_id ? get_permalink( $result_page_id ) : '';
		if ( ! $result_url ) {
			$result_url = esc_url_raw( $attributes['result_page'] );
		}
	}
	if ( ! $result_url ) {
		$search_item_page = get_page_by_path( 'search-item-list' );
		if ( $search_item_page ) {
			$result_url = get_permalink( $search_item_page );
		}
	}
	if ( ! $result_url ) {
		$search_page_id = rbfw_get_option( 'rbfw_search_page', 'rbfw_basic_gen_settings' );
		if ( $search_page_id ) {
			$result_url = get_page_link( $search_page_id );
		}
	}
	if ( ! $result_url ) {
		$result_url = home_url();
	}

	$current_term = isset( $_GET['rbfw_search_item_name'] ) ? sanitize_text_field( wp_unslash( $_GET['rbfw_search_item_name'] ) ) : '';

	ob_start();
	?>
	<div class="rbfw_item_search_wrap">
		<form class="rbfw_item_search_form" action="<?php echo esc_url( $result_url ); ?>" method="get" role="search" autocomplete="off">
			<?php wp_nonce_field( 'rbfw_nonce_action', 'nonce' ); ?>
			<div class="rbfw_item_search_box">
				<input type="text" name="rbfw_search_item_name" class="rbfw_item_search_input"
					value="<?php echo esc_attr( $current_term ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					aria-label="<?php echo esc_attr( $placeholder ); ?>">
				<button type="submit" class="rbfw_item_search_submit"><?php echo esc_html( $button_text ); ?></button>
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
