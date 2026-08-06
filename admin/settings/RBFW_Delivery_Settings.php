<?php
/**
 * Settings screen for Delivery & Collection mileage pricing.
 *
 * Registers a "Delivery" tab on the global settings page through the same
 * rbfw_settings_sec_reg / rbfw_settings_sec_fields filters every other section uses, plus
 * a custom-rendered repeater for the distance bands (the Settings API has no repeater type).
 *
 * @package booking-and-rental-manager-for-woocommerce
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Delivery_Settings' ) ) {

	class RBFW_Delivery_Settings {

		public function __construct() {
			add_filter( 'rbfw_settings_sec_reg', array( $this, 'register_section' ), 13 );
			add_filter( 'rbfw_settings_sec_fields', array( $this, 'register_fields' ), 13 );
			add_action( 'admin_footer', array( $this, 'print_styles' ) );
		}

		public function register_section( $sections ) {
			$sections[] = array(
				'id'    => RBFW_DELIVERY_SECTION,
				'title' => '<i class="fas fa-truck"></i>' . esc_html__( 'Delivery', 'booking-and-rental-manager-for-woocommerce' ),
			);
			return $sections;
		}

		public function register_fields( $fields ) {
			$fields[ RBFW_DELIVERY_SECTION ] = array(
				array(
					'name'  => 'rbfw_delivery_heading',
					'label' => esc_html__( 'Delivery & Collection', 'booking-and-rental-manager-for-woocommerce' ),
					'type'  => 'heading',
					'desc'  => esc_html__( 'Charge for bringing a rental to the customer and picking it up again, priced by how far away they are. The customer states their address and distance; the price comes from the bands below.', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'    => 'rbfw_delivery_enable',
					'label'   => esc_html__( 'Offer Delivery', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Let customers ask for the rental to be delivered to them.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'off',
				),
				array(
					'name'    => 'rbfw_collection_enable',
					'label'   => esc_html__( 'Offer Collection', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Let customers ask for the rental to be collected at the end of the hire.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'off',
				),
				array(
					'name'    => 'rbfw_delivery_label',
					'label'   => esc_html__( 'Delivery Label', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Wording shown to the customer and on the invoice line.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'text',
					'default' => esc_html__( 'Delivery', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'    => 'rbfw_collection_label',
					'label'   => esc_html__( 'Collection Label', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'text',
					'default' => esc_html__( 'Collection', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'    => 'rbfw_delivery_base_fee',
					'label'   => esc_html__( 'Base Fee', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Flat amount added on top of the band price, for each leg requested. Leave at 0 to price purely by distance.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'number',
					'step'    => '0.01',
					'min'     => '0',
					'default' => '0',
				),
				array(
					'name'    => 'rbfw_delivery_free_radius',
					'label'   => esc_html__( 'Free Radius (km)', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Deliver free within this distance. The booking is still recorded as a delivery. Set 0 to always charge.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'number',
					'step'    => '0.1',
					'min'     => '0',
					'default' => '0',
				),
				array(
					'name'    => 'rbfw_delivery_max_distance',
					'label'   => esc_html__( 'Maximum Distance (km)', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Refuse delivery beyond this distance. Set 0 for no limit.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'number',
					'step'    => '0.1',
					'min'     => '0',
					'default' => '0',
				),
				array(
					'name'    => 'rbfw_collection_mode',
					'label'   => esc_html__( 'Collection Pricing', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'How the return leg is priced when the customer asks for collection.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'select',
					'default' => 'same',
					'options' => array(
						'same' => esc_html__( 'Same as delivery', 'booking-and-rental-manager-for-woocommerce' ),
						'own'  => esc_html__( 'Its own bands (below)', 'booking-and-rental-manager-for-woocommerce' ),
						'free' => esc_html__( 'Free', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'name'              => 'rbfw_delivery_bands',
					'label'             => esc_html__( 'Distance Bands', 'booking-and-rental-manager-for-woocommerce' ),
					'type'              => 'delivery_bands',
					'callback'          => array( $this, 'render_bands' ),
					'sanitize_callback' => 'rbfw_delivery_sanitize_bands',
					'default'           => '',
				),
				array(
					'name'              => 'rbfw_collection_bands',
					'label'             => esc_html__( 'Collection Bands', 'booking-and-rental-manager-for-woocommerce' ),
					'type'              => 'delivery_bands',
					'callback'          => array( $this, 'render_collection_bands' ),
					'sanitize_callback' => 'rbfw_delivery_sanitize_bands',
					'default'           => '',
				),
				array(
					'name'    => 'rbfw_delivery_require_address',
					'label'   => esc_html__( 'Require an Address', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Make the delivery address mandatory when delivery is chosen.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'on',
				),
				array(
					'name'        => 'rbfw_delivery_help_text',
					'label'       => esc_html__( 'Help Text', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'        => esc_html__( 'Shown under the delivery fields on the booking form.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'        => 'textarea',
					'placeholder' => esc_html__( 'e.g. Distance from our shop. We will confirm before your rental starts.', 'booking-and-rental-manager-for-woocommerce' ),
					'default'     => '',
				),
			);

			return $fields;
		}

		/* ================================================================== *
		 * Band repeater
		 * ================================================================== */

		public function render_bands( $args ) {
			$this->render_band_table( 'rbfw_delivery_bands', esc_html__( 'Charged for each leg the customer asks for. A distance not covered by any band is refused rather than quoted free.', 'booking-and-rental-manager-for-woocommerce' ) );
		}

		public function render_collection_bands( $args ) {
			$this->render_band_table( 'rbfw_collection_bands', esc_html__( 'Only used when Collection Pricing is set to "Its own bands". A distance these bands do not cover falls back to the delivery price.', 'booking-and-rental-manager-for-woocommerce' ) );
		}

		/**
		 * One band repeater table.
		 *
		 * Rows post as rbfw_delivery_settings[<field>][<i>][from|to|price] so the Settings
		 * API stores them under the section option, and rbfw_delivery_sanitize_bands() (the
		 * registered sanitize_callback) normalizes, drops broken rows and sorts on save.
		 *
		 * @param string $field Field key.
		 * @param string $hint  Help text.
		 * @return void
		 */
		private function render_band_table( $field, $hint ) {
			$opts  = get_option( RBFW_DELIVERY_SECTION, array() );
			$rows  = ( is_array( $opts ) && ! empty( $opts[ $field ] ) && is_array( $opts[ $field ] ) )
				? $opts[ $field ]
				: ( 'rbfw_delivery_bands' === $field ? rbfw_delivery_default_bands() : array() );
			$name  = RBFW_DELIVERY_SECTION . '[' . $field . ']';
			$symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
			?>
			<div class="rbfw-bands" data-field="<?php echo esc_attr( $field ); ?>" data-name="<?php echo esc_attr( $name ); ?>">
				<table class="rbfw-bands-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'From (km)', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'To (km)', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th><?php echo esc_html( sprintf( /* translators: %s: currency symbol. */ __( 'Price (%s)', 'booking-and-rental-manager-for-woocommerce' ), html_entity_decode( $symbol, ENT_QUOTES, 'UTF-8' ) ) ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody class="rbfw-bands-body">
						<?php foreach ( $rows as $i => $row ) : ?>
							<tr class="rbfw-band-row">
								<td><input type="number" step="0.1" min="0" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][from]" value="<?php echo esc_attr( isset( $row['from'] ) ? $row['from'] : 0 ); ?>"></td>
								<td><input type="number" step="0.1" min="0" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][to]" value="<?php echo esc_attr( isset( $row['to'] ) ? $row['to'] : 0 ); ?>"></td>
								<td><input type="number" step="0.01" min="0" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][price]" value="<?php echo esc_attr( isset( $row['price'] ) ? $row['price'] : 0 ); ?>"></td>
								<td><button type="button" class="button-link rbfw-band-remove" aria-label="<?php esc_attr_e( 'Remove band', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<button type="button" class="button rbfw-band-add"><?php esc_html_e( '+ Add band', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
				<p class="description"><?php echo esc_html( $hint ); ?></p>
			</div>
			<?php
		}

		/**
		 * Repeater styles + behaviour. Printed on the settings screen only.
		 *
		 * Row indices are re-numbered on every add/remove so a gap left by deleting a middle
		 * row cannot produce a sparse array on save.
		 *
		 * @return void
		 */
		public function print_styles() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( ! $screen || false === strpos( (string) $screen->id, 'rbfw_settings_page' ) ) {
				return;
			}
			?>
			<style>
				.rbfw-bands-table { border-collapse: collapse; margin-bottom: 8px; }
				.rbfw-bands-table th { text-align: left; font-size: 12px; color: #50575e; padding: 0 8px 4px 0; font-weight: 600; }
				.rbfw-bands-table td { padding: 0 8px 6px 0; }
				.rbfw-bands-table input { width: 110px; }
				.rbfw-band-remove { color: #b32d2e !important; font-size: 18px; text-decoration: none !important; line-height: 1; }
				.rbfw-band-remove:hover { color: #8a2424 !important; }
			</style>
			<script>
			( function ( $ ) {
				function reindex( $wrap ) {
					var name = $wrap.data( 'name' );
					$wrap.find( '.rbfw-band-row' ).each( function ( i ) {
						$( this ).find( 'input' ).each( function () {
							var key = ( this.name.match( /\[(from|to|price)\]$/ ) || [] )[ 1 ];
							if ( key ) { this.name = name + '[' + i + '][' + key + ']'; }
						} );
					} );
				}
				$( document ).on( 'click', '.rbfw-band-add', function () {
					var $wrap = $( this ).closest( '.rbfw-bands' );
					var $body = $wrap.find( '.rbfw-bands-body' );
					var $last = $body.find( '.rbfw-band-row' ).last();
					// Start the new band where the previous one ended, which is what an admin
					// filling in a ladder of bands is about to type anyway.
					var from  = $last.length ? ( parseFloat( $last.find( 'input' ).eq( 1 ).val() ) || 0 ) : 0;
					$body.append(
						'<tr class="rbfw-band-row">' +
						'<td><input type="number" step="0.1" min="0" value="' + from + '"></td>' +
						'<td><input type="number" step="0.1" min="0" value="' + ( from + 5 ) + '"></td>' +
						'<td><input type="number" step="0.01" min="0" value="0"></td>' +
						'<td><button type="button" class="button-link rbfw-band-remove">&times;</button></td>' +
						'</tr>'
					);
					// The fresh inputs have no name yet; reindex assigns from/to/price by
					// position for the new row.
					var $new = $body.find( '.rbfw-band-row' ).last().find( 'input' );
					var keys = [ 'from', 'to', 'price' ];
					$new.each( function ( i ) { this.name = $wrap.data( 'name' ) + '[x][' + keys[ i ] + ']'; } );
					reindex( $wrap );
				} );
				$( document ).on( 'click', '.rbfw-band-remove', function () {
					var $wrap = $( this ).closest( '.rbfw-bands' );
					$( this ).closest( '.rbfw-band-row' ).remove();
					reindex( $wrap );
				} );
			} )( jQuery );
			</script>
			<?php
		}
	}

	new RBFW_Delivery_Settings();
}
