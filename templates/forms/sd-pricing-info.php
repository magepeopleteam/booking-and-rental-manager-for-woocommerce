<?php
	/**
	 * Pricing Info link + "Pricing Details" modal for the Single Day /
	 * Appointment booking form (single-day-registration.php).
	 *
	 * Mirrors the Multiple Day form's modal: same markup and classes, so the
	 * open/close handler in assets/mp_script/md_script.js and the styling in
	 * css/rbfw_style.css apply unchanged. Shown only when the global
	 * "Pricing Info Display" setting (rbfw_pricing_info_display) is "yes".
	 *
	 * Expects $rbfw_id (rental item ID) from the including template.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( rbfw_get_option( 'rbfw_pricing_info_display', 'rbfw_basic_gen_settings' ) !== 'yes' ) {
		return;
	}

	$rbfw_pi_options = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data', true );
	$rbfw_pi_options = is_array( $rbfw_pi_options ) ? $rbfw_pi_options : array();
	$rbfw_pi_options = array_filter(
		$rbfw_pi_options,
		static function ( $row ) {
			return is_array( $row ) && isset( $row['rent_type'] ) && '' !== trim( (string) $row['rent_type'] );
		}
	);

	$rbfw_pi_specific_duration = get_post_meta( $rbfw_id, 'enable_specific_duration', true ) === 'on';

	// Seasonal prices replace an option's price on dates inside the season,
	// matched by option name (check_seasonal_price_sd()), and only when a
	// price is set -- list exactly those.
	$rbfw_pi_seasons = array();
	if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' ) ) {
		$rbfw_pi_sp_data = get_post_meta( $rbfw_id, 'rbfw_bike_car_sd_data_sp', true );
		foreach ( is_array( $rbfw_pi_sp_data ) ? $rbfw_pi_sp_data : array() as $rbfw_pi_season ) {
			if ( empty( $rbfw_pi_season['start_date'] ) || empty( $rbfw_pi_season['end_date'] ) ) {
				continue;
			}
			$rbfw_pi_season_rows = array();
			foreach ( isset( $rbfw_pi_season['type_price'] ) && is_array( $rbfw_pi_season['type_price'] ) ? $rbfw_pi_season['type_price'] : array() as $rbfw_pi_tp ) {
				if ( empty( $rbfw_pi_tp['type'] ) || ! isset( $rbfw_pi_tp['price'] ) || ! is_numeric( $rbfw_pi_tp['price'] ) || (float) $rbfw_pi_tp['price'] <= 0 ) {
					continue;
				}
				$rbfw_pi_season_rows[] = $rbfw_pi_tp;
			}
			if ( $rbfw_pi_season_rows ) {
				$rbfw_pi_seasons[] = array(
					'start' => $rbfw_pi_season['start_date'],
					'end'   => $rbfw_pi_season['end_date'],
					'rows'  => $rbfw_pi_season_rows,
				);
			}
		}
	}

	$rbfw_pi_services = get_post_meta( $rbfw_id, 'rbfw_extra_service_data', true );
	$rbfw_pi_services = array_filter(
		is_array( $rbfw_pi_services ) ? $rbfw_pi_services : array(),
		static function ( $service ) {
			return is_array( $service ) && ! empty( $service['service_name'] );
		}
	);

	$rbfw_pi_deposit_on     = get_post_meta( $rbfw_id, 'rbfw_enable_security_deposit', true ) === 'yes';
	$rbfw_pi_deposit_type   = get_post_meta( $rbfw_id, 'rbfw_security_deposit_type', true ) ? get_post_meta( $rbfw_id, 'rbfw_security_deposit_type', true ) : 'percentage';
	$rbfw_pi_deposit_amount = (float) get_post_meta( $rbfw_id, 'rbfw_security_deposit_amount', true );
?>
<div class="item pricing-content-container">
	<?php do_action( 'rbfw_pricing_info_header' ); ?>
	<div class="price-item-container">
		<span class="close-price-container"><i class="mi mi-x"></i></span>
		<div class="rbfw-pi-title"><?php esc_html_e( 'Pricing Details', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
		<div class="mpStyle">

			<?php if ( $rbfw_pi_options ) : ?>
				<div class="rbfw-pi-section-label"><?php esc_html_e( 'Rental Options', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
				<div class="rbfw_day_wise_price">
					<table>
						<tbody>
						<?php foreach ( $rbfw_pi_options as $rbfw_pi_option ) :
							if ( $rbfw_pi_specific_duration && ! empty( $rbfw_pi_option['start_time'] ) && ! empty( $rbfw_pi_option['end_time'] ) ) {
								$rbfw_pi_length = gmdate( get_option( 'time_format' ), strtotime( $rbfw_pi_option['start_time'] ) ) . ' - ' . gmdate( get_option( 'time_format' ), strtotime( $rbfw_pi_option['end_time'] ) );
							} elseif ( ! empty( $rbfw_pi_option['duration'] ) ) {
								$rbfw_pi_length = $rbfw_pi_option['duration'] . ' ' . ( isset( $rbfw_pi_option['d_type'] ) ? $rbfw_pi_option['d_type'] : '' );
							} else {
								$rbfw_pi_length = '';
							}
						?>
							<tr>
								<td><strong><?php echo esc_html( $rbfw_pi_option['rent_type'] ); ?></strong><?php if ( '' !== $rbfw_pi_length ) : ?> <span class="rbfw-pi-hint">(<?php echo esc_html( trim( $rbfw_pi_length ) ); ?>)</span><?php endif; ?></td>
								<td><?php echo wp_kses( wc_price( isset( $rbfw_pi_option['price'] ) ? (float) $rbfw_pi_option['price'] : 0 ), rbfw_allowed_html() ); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php if ( $rbfw_pi_seasons ) : ?>
				<div class="rbfw-pi-section-label"><?php esc_html_e( 'Seasonal Pricing', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
				<div class="rbfw_day_wise_price rbfw-pi-seasonal">
					<table>
						<tbody>
						<?php foreach ( $rbfw_pi_seasons as $rbfw_pi_season ) : ?>
							<tr class="rbfw-pi-range"><td colspan="2"><?php esc_html_e( 'From', 'booking-and-rental-manager-for-woocommerce' ); ?> <strong><?php echo esc_html( rbfw_date_format( $rbfw_pi_season['start'] ) ); ?></strong> <?php esc_html_e( 'To', 'booking-and-rental-manager-for-woocommerce' ); ?> <strong><?php echo esc_html( rbfw_date_format( $rbfw_pi_season['end'] ) ); ?></strong></td></tr>
							<?php foreach ( $rbfw_pi_season['rows'] as $rbfw_pi_tp ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $rbfw_pi_tp['type'] ); ?></strong></td>
									<td><?php echo wp_kses( wc_price( (float) $rbfw_pi_tp['price'] ), rbfw_allowed_html() ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php if ( $rbfw_pi_services ) : ?>
				<div class="rbfw-pi-section-label"><?php esc_html_e( 'Extra Services', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
				<div class="rbfw_day_wise_price">
					<table>
						<tbody>
						<?php foreach ( $rbfw_pi_services as $rbfw_pi_service ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $rbfw_pi_service['service_name'] ); ?></strong></td>
								<td><?php echo wp_kses_post( wc_price( isset( $rbfw_pi_service['service_price'] ) ? (float) $rbfw_pi_service['service_price'] : 0 ) ); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php if ( $rbfw_pi_deposit_on && $rbfw_pi_deposit_amount > 0 ) : ?>
				<div class="rbfw-pi-section-label"><?php esc_html_e( 'Security Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></div>
				<div class="rbfw_day_wise_price">
					<table>
						<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Refundable Deposit', 'booking-and-rental-manager-for-woocommerce' ); ?></strong></td>
							<td><?php
								if ( 'percentage' === $rbfw_pi_deposit_type ) {
									echo esc_html( $rbfw_pi_deposit_amount ) . '%';
								} else {
									echo wp_kses_post( wc_price( $rbfw_pi_deposit_amount ) );
								}
							?></td>
						</tr>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
