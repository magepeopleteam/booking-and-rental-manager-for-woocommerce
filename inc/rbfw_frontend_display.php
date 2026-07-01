<?php
/**
 * Front-end Display settings — admin-configurable text, colour and visibility
 * for the badges/heading shown on the single rental item page (hero "Best
 * Seller" badge, the booking-summary "Available Today" / "Best Seller" badges,
 * and the "Instant Booking Summary" title + subtitle).
 *
 * Storage: global option `rbfw_basic_frontend_display_settings` (one set of
 * values applied to every rental item, all rent types). Registered as a new
 * section in the plugin Global Settings via the same filters the core sections
 * use, so it saves/loads through the bundled WeDevs Settings API.
 *
 * @package booking-and-rental-manager-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Defaults — mirror the current hard-coded text and CSS colours so the page
| looks identical until the admin changes something.
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'rbfw_fd_defaults' ) ) {
	function rbfw_fd_defaults() {
		return array(
			// Hero "Best Seller" badge (single item hero overlay).
			'hero_badge_show'    => 'on',
			'hero_badge_text'    => __( 'Best Seller', 'booking-and-rental-manager-for-woocommerce' ),
			'hero_badge_bg'      => '#e03248',
			'hero_badge_color'   => '#ffffff',
			// "Available Today" badge (booking summary).
			'avail_badge_show'   => 'on',
			'avail_badge_text'   => __( 'Available Today', 'booking-and-rental-manager-for-woocommerce' ),
			'avail_badge_bg'     => '#dcfce7',
			'avail_badge_color'  => '#15803d',
			// "Best Seller" badge (booking summary).
			'seller_badge_show'  => 'on',
			'seller_badge_text'  => __( 'Best Seller', 'booking-and-rental-manager-for-woocommerce' ),
			'seller_badge_bg'    => '#fff1f2',
			'seller_badge_color' => '#e11d48',
			// "Instant Booking Summary" title.
			'summary_title_show'  => 'on',
			'summary_title_text'  => __( 'Instant Booking Summary', 'booking-and-rental-manager-for-woocommerce' ),
			'summary_title_color' => '#111827',
			// Subtitle under the title.
			'summary_desc_show'  => 'on',
			'summary_desc_text'  => __( 'Select dates to see final price and availability in real time.', 'booking-and-rental-manager-for-woocommerce' ),
			'summary_desc_color' => '#6b7280',
		);
	}
}

/**
 * Read a single Front-end Display option, falling back to its default.
 *
 * @param string $key Option key (without section prefix).
 * @return string
 */
if ( ! function_exists( 'rbfw_fd_opt' ) ) {
	function rbfw_fd_opt( $key ) {
		static $opts = null;
		if ( null === $opts ) {
			$opts = get_option( 'rbfw_basic_frontend_display_settings', array() );
			if ( ! is_array( $opts ) ) {
				$opts = array();
			}
		}
		$defaults = rbfw_fd_defaults();
		$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
		if ( isset( $opts[ $key ] ) && '' !== $opts[ $key ] ) {
			return $opts[ $key ];
		}
		return $default;
	}
}

/**
 * Whether a show/hide toggle is enabled (defaults to shown).
 *
 * @param string $key Toggle option key.
 * @return bool
 */
if ( ! function_exists( 'rbfw_fd_is_on' ) ) {
	function rbfw_fd_is_on( $key ) {
		return 'off' !== rbfw_fd_opt( $key );
	}
}

/**
 * Build an inline style string from a background and/or text colour option.
 * Only emits properties that have a value so the stylesheet stays authoritative
 * when a colour is left blank.
 *
 * @param string $bg_key    Background colour key ('' to skip).
 * @param string $color_key Text colour key ('' to skip).
 * @return string           Escaped-ready style string (may be empty).
 */
if ( ! function_exists( 'rbfw_fd_style' ) ) {
	function rbfw_fd_style( $bg_key = '', $color_key = '' ) {
		$style = '';
		if ( $bg_key ) {
			$bg = rbfw_fd_opt( $bg_key );
			if ( '' !== $bg ) {
				$style .= 'background:' . $bg . ';';
			}
		}
		if ( $color_key ) {
			$fg = rbfw_fd_opt( $color_key );
			if ( '' !== $fg ) {
				$style .= 'color:' . $fg . ';';
			}
		}
		return $style;
	}
}

/*
|--------------------------------------------------------------------------
| Render helpers — used by all single/registration templates so the markup
| stays identical everywhere and template edits are one-liners.
|--------------------------------------------------------------------------
*/

/** Hero "Best Seller" badge (single item hero overlay). */
if ( ! function_exists( 'rbfw_fd_hero_badge' ) ) {
	function rbfw_fd_hero_badge() {
		if ( ! rbfw_fd_is_on( 'hero_badge_show' ) ) {
			return;
		}
		$style = rbfw_fd_style( 'hero_badge_bg', 'hero_badge_color' );
		printf(
			'<div class="rbfw_default_hero_badge"%1$s><i class="fas fa-star"></i> %2$s</div>',
			$style ? ' style="' . esc_attr( $style ) . '"' : '',
			esc_html( rbfw_fd_opt( 'hero_badge_text' ) )
		);
	}
}

/** Booking-summary badges wrapper ("Available Today" + "Best Seller"). */
if ( ! function_exists( 'rbfw_fd_summary_badges' ) ) {
	function rbfw_fd_summary_badges() {
		$show_avail  = rbfw_fd_is_on( 'avail_badge_show' );
		$show_seller = rbfw_fd_is_on( 'seller_badge_show' );
		if ( ! $show_avail && ! $show_seller ) {
			return;
		}
		echo '<div class="rbfw-sd-rate-box-badges">';
		if ( $show_avail ) {
			$style = rbfw_fd_style( 'avail_badge_bg', 'avail_badge_color' );
			printf(
				'<span class="rbfw-sd-badge rbfw-sd-badge--available"%1$s><span class="rbfw-sd-badge-dot"></span> %2$s</span>',
				$style ? ' style="' . esc_attr( $style ) . '"' : '',
				esc_html( rbfw_fd_opt( 'avail_badge_text' ) )
			);
		}
		if ( $show_seller ) {
			$style = rbfw_fd_style( 'seller_badge_bg', 'seller_badge_color' );
			printf(
				'<span class="rbfw-sd-badge rbfw-sd-badge--seller"%1$s>%2$s</span>',
				$style ? ' style="' . esc_attr( $style ) . '"' : '',
				esc_html( rbfw_fd_opt( 'seller_badge_text' ) )
			);
		}
		echo '</div>';
	}
}

/** "Instant Booking Summary" title. */
if ( ! function_exists( 'rbfw_fd_summary_title' ) ) {
	function rbfw_fd_summary_title() {
		if ( ! rbfw_fd_is_on( 'summary_title_show' ) ) {
			return;
		}
		$style = rbfw_fd_style( '', 'summary_title_color' );
		printf(
			'<h3 class="rbfw-sd-rate-box-title"%1$s>%2$s</h3>',
			$style ? ' style="' . esc_attr( $style ) . '"' : '',
			esc_html( rbfw_fd_opt( 'summary_title_text' ) )
		);
	}
}

/** Subtitle under the booking-summary title. */
if ( ! function_exists( 'rbfw_fd_summary_desc' ) ) {
	function rbfw_fd_summary_desc() {
		if ( ! rbfw_fd_is_on( 'summary_desc_show' ) ) {
			return;
		}
		$style = rbfw_fd_style( '', 'summary_desc_color' );
		printf(
			'<p class="rbfw-sd-rate-box-desc"%1$s>%2$s</p>',
			$style ? ' style="' . esc_attr( $style ) . '"' : '',
			esc_html( rbfw_fd_opt( 'summary_desc_text' ) )
		);
	}
}

/*
|--------------------------------------------------------------------------
| Global Settings registration (WeDevs Settings API via core filters).
|--------------------------------------------------------------------------
*/

add_filter( 'rbfw_settings_sec_reg', 'rbfw_fd_register_section', 12 );
function rbfw_fd_register_section( $sections ) {
	$sections[] = array(
		'id'    => 'rbfw_basic_frontend_display_settings',
		'title' => '<i class="fas fa-eye"></i>' . esc_html__( 'Front-end Display', 'booking-and-rental-manager-for-woocommerce' ),
	);
	return $sections;
}

add_filter( 'rbfw_settings_sec_fields', 'rbfw_fd_register_fields', 12 );
function rbfw_fd_register_fields( $fields ) {
	$d = rbfw_fd_defaults();

	$fields['rbfw_basic_frontend_display_settings'] = array(
		// ── Hero "Best Seller" badge ──
		array(
			'name'    => 'hero_badge_show',
			'label'   => esc_html__( 'Hero Badge — Show', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Show the "Best Seller" badge on the item hero image.', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => $d['hero_badge_show'],
		),
		array(
			'name'    => 'hero_badge_text',
			'label'   => esc_html__( 'Hero Badge — Text', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'text',
			'default' => $d['hero_badge_text'],
		),
		array(
			'name'    => 'hero_badge_bg',
			'label'   => esc_html__( 'Hero Badge — Background', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['hero_badge_bg'],
		),
		array(
			'name'    => 'hero_badge_color',
			'label'   => esc_html__( 'Hero Badge — Text Colour', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['hero_badge_color'],
		),
		// ── "Available Today" badge ──
		array(
			'name'    => 'avail_badge_show',
			'label'   => esc_html__( 'Available Badge — Show', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Show the "Available Today" badge in the booking summary.', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => $d['avail_badge_show'],
		),
		array(
			'name'    => 'avail_badge_text',
			'label'   => esc_html__( 'Available Badge — Text', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'text',
			'default' => $d['avail_badge_text'],
		),
		array(
			'name'    => 'avail_badge_bg',
			'label'   => esc_html__( 'Available Badge — Background', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['avail_badge_bg'],
		),
		array(
			'name'    => 'avail_badge_color',
			'label'   => esc_html__( 'Available Badge — Text Colour', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['avail_badge_color'],
		),
		// ── "Best Seller" badge (booking summary) ──
		array(
			'name'    => 'seller_badge_show',
			'label'   => esc_html__( 'Seller Badge — Show', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Show the "Best Seller" badge in the booking summary.', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => $d['seller_badge_show'],
		),
		array(
			'name'    => 'seller_badge_text',
			'label'   => esc_html__( 'Seller Badge — Text', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'text',
			'default' => $d['seller_badge_text'],
		),
		array(
			'name'    => 'seller_badge_bg',
			'label'   => esc_html__( 'Seller Badge — Background', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['seller_badge_bg'],
		),
		array(
			'name'    => 'seller_badge_color',
			'label'   => esc_html__( 'Seller Badge — Text Colour', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['seller_badge_color'],
		),
		// ── "Instant Booking Summary" title ──
		array(
			'name'    => 'summary_title_show',
			'label'   => esc_html__( 'Summary Title — Show', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Show the booking summary title.', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => $d['summary_title_show'],
		),
		array(
			'name'    => 'summary_title_text',
			'label'   => esc_html__( 'Summary Title — Text', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'text',
			'default' => $d['summary_title_text'],
		),
		array(
			'name'    => 'summary_title_color',
			'label'   => esc_html__( 'Summary Title — Colour', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['summary_title_color'],
		),
		// ── Subtitle ──
		array(
			'name'    => 'summary_desc_show',
			'label'   => esc_html__( 'Summary Subtitle — Show', 'booking-and-rental-manager-for-woocommerce' ),
			'desc'    => esc_html__( 'Show the booking summary subtitle.', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => $d['summary_desc_show'],
		),
		array(
			'name'    => 'summary_desc_text',
			'label'   => esc_html__( 'Summary Subtitle — Text', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'textarea',
			'default' => $d['summary_desc_text'],
		),
		array(
			'name'    => 'summary_desc_color',
			'label'   => esc_html__( 'Summary Subtitle — Colour', 'booking-and-rental-manager-for-woocommerce' ),
			'type'    => 'color',
			'default' => $d['summary_desc_color'],
		),
	);

	return $fields;
}
