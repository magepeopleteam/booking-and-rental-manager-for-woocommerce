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

		/**
		 * Whether the feature is unlocked. Delegates to the engine so the settings screen and
		 * the pricing paths can never disagree about who may use delivery.
		 *
		 * @return bool
		 */
		private function is_pro() {
			return function_exists( 'rbfw_delivery_is_pro' ) ? rbfw_delivery_is_pro() : false;
		}

		public function register_section( $sections ) {
			// The tab stays visible without Pro — it is how the feature is discovered — but
			// carries a padlock so nobody mistakes it for something they can switch on.
			$icon = $this->is_pro() ? 'fa-truck' : 'fa-lock';

			$sections[] = array(
				'id'    => RBFW_DELIVERY_SECTION,
				'title' => '<i class="fas ' . esc_attr( $icon ) . '"></i>' . esc_html__( 'Delivery', 'booking-and-rental-manager-for-woocommerce' ),
			);
			return $sections;
		}

		public function register_fields( $fields ) {
			/*
			 * Without Pro the tab renders an upsell and NOTHING writable. Showing the real
			 * fields disabled would still be a form an admin could fill in and save, leaving a
			 * configured-looking feature that never runs.
			 */
			if ( ! $this->is_pro() ) {
				$fields[ RBFW_DELIVERY_SECTION ] = array(
					array(
						'name'     => 'rbfw_delivery_pro_notice',
						'label'    => '',
						'type'     => 'html',
						'callback' => array( $this, 'render_pro_lock' ),
					),
				);
				return $fields;
			}

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
					'name'  => 'rbfw_delivery_required_heading',
					'label' => esc_html__( 'Required Fields', 'booking-and-rental-manager-for-woocommerce' ),
					'type'  => 'heading',
					'desc'  => esc_html__( 'What the customer must complete before they can book. Each of these is enforced on the booking form AND again on the server, so it cannot be skipped by editing the page.', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'    => 'rbfw_delivery_require_mode',
					'label'   => esc_html__( 'Delivery Choice', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'How much of the delivery service the customer must take. "Both" suits a shop that will not leave a rental at an address it is not coming back to. Only the legs you actually offer above are ever required.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'select',
					'default' => 'off',
					'options' => array(
						'off'  => esc_html__( 'Optional — they may collect in store', 'booking-and-rental-manager-for-woocommerce' ),
						'any'  => esc_html__( 'At least one — delivery or collection', 'booking-and-rental-manager-for-woocommerce' ),
						'both' => esc_html__( 'Both — delivery and collection together', 'booking-and-rental-manager-for-woocommerce' ),
					),
				),
				array(
					'name'    => 'rbfw_delivery_require_address',
					'label'   => esc_html__( 'Address Mandatory', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Make the delivery address mandatory once delivery is chosen.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'on',
				),
				array(
					'name'    => 'rbfw_delivery_require_phone',
					'label'   => esc_html__( 'Contact Number Mandatory', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Ask for a number to call on arrival, and require it once delivery is chosen.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'off',
				),
				array(
					'name'    => 'rbfw_delivery_require_note',
					'label'   => esc_html__( 'Delivery Notes Mandatory', 'booking-and-rental-manager-for-woocommerce' ),
					'desc'    => esc_html__( 'Ask for access details (gate code, floor, where to leave it) and require them once delivery is chosen.', 'booking-and-rental-manager-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'off',
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
		 * Pro lock
		 * ================================================================== */

		/**
		 * The whole Delivery tab when Pro is not active: what the feature does, and where to
		 * get it. No inputs, because nothing here could be saved into effect.
		 *
		 * @param array $args Settings field args (unused).
		 * @return void
		 */
		public function render_pro_lock( $args ) {
			$pro_url = admin_url( 'edit.php?post_type=rbfw_item&page=rbfw_go_pro_page' );
			$points  = array(
				__( 'Charge for delivering a rental to the customer and collecting it again.', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'Price by distance bands you define — no mapping service and no API key.', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'A free radius for nearby customers, and a maximum distance beyond which you do not deliver.', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'Turn delivery off per rental item for anything you cannot transport.', 'booking-and-rental-manager-for-woocommerce' ),
				__( 'Delivery and collection appear as separate lines on the cart, order, invoice and PDF ticket.', 'booking-and-rental-manager-for-woocommerce' ),
			);
			?>
			<div class="rbfw-dl-lock">
				<div class="rbfw-dl-lock__head">
					<span class="rbfw-dl-lock__icon dashicons dashicons-lock" aria-hidden="true"></span>
					<div>
						<span class="rbfw-dl-lock__eyebrow"><?php esc_html_e( 'Pro feature', 'booking-and-rental-manager-for-woocommerce' ); ?></span>
						<h2 class="rbfw-dl-lock__title"><?php esc_html_e( 'Delivery &amp; Collection', 'booking-and-rental-manager-for-woocommerce' ); ?></h2>
					</div>
				</div>
				<p class="rbfw-dl-lock__intro">
					<?php esc_html_e( 'Bring the rental to your customer and pick it up again, priced by how far away they are.', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</p>
				<ul class="rbfw-dl-lock__list">
					<?php foreach ( $points as $point ) : ?>
						<li><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php echo esc_html( $point ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p class="rbfw-dl-lock__cta">
					<a class="button button-primary" href="<?php echo esc_url( $pro_url ); ?>">
						<?php esc_html_e( 'Unlock with Pro', 'booking-and-rental-manager-for-woocommerce' ); ?>
					</a>
				</p>
				<p class="description rbfw-dl-lock__note">
					<?php esc_html_e( 'Bookings that already recorded a delivery keep showing it on their order and invoice — activating Pro restores your saved settings exactly as they were.', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</p>
			</div>
			<?php
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

				/* Pro lock (shown in place of the fields when Pro is inactive). */
				.rbfw-dl-lock { max-width: 640px; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 22px 26px 20px; }
				.rbfw-dl-lock__head { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
				.rbfw-dl-lock__icon { width: 40px; height: 40px; font-size: 22px; line-height: 40px; text-align: center; border-radius: 50%; background: #fef3c7; color: #b45309; flex-shrink: 0; }
				.rbfw-dl-lock__eyebrow { display: block; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #b45309; }
				.rbfw-dl-lock__title { margin: 2px 0 0; font-size: 18px; line-height: 1.2; }
				.rbfw-dl-lock__intro { margin: 0 0 12px; color: #4b5563; }
				.rbfw-dl-lock__list { margin: 0 0 18px; padding: 0; list-style: none; }
				.rbfw-dl-lock__list li { display: flex; align-items: flex-start; gap: 8px; margin: 0 0 8px; color: #374151; }
				.rbfw-dl-lock__list .dashicons { color: #16a34a; flex-shrink: 0; }
				.rbfw-dl-lock__cta { margin: 0 0 10px; }
				.rbfw-dl-lock__note { margin: 0; }
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
