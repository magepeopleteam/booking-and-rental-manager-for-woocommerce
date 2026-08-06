<?php
/**
 * Settings screen for the accounting payment methods.
 *
 * Its own section rather than a field inside the Offline gateway modal: the list is not
 * only an Offline-checkout concern. It is also what an admin records against a WooCommerce
 * booking paid by cheque at the counter, and what the per-method totals reconcile against.
 *
 * @package booking-and-rental-manager-for-woocommerce
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'RBFW_Payment_Methods_Settings' ) ) {

	class RBFW_Payment_Methods_Settings {

		/** Section id — deliberately NOT the same option the gateway settings use. */
		const SECTION = 'rbfw_accounting_settings';

		public function __construct() {
			add_filter( 'rbfw_settings_sec_reg', array( $this, 'register_section' ), 14 );
			add_filter( 'rbfw_settings_sec_fields', array( $this, 'register_fields' ), 14 );
			add_action( 'admin_footer', array( $this, 'print_repeater_assets' ) );
			// The registry reads from rbfw_payment_settings, so mirror the saved list there.
			add_action( 'update_option_' . self::SECTION, array( $this, 'sync_to_registry' ), 10, 2 );
			add_action( 'add_option_' . self::SECTION, array( $this, 'sync_to_registry_added' ), 10, 2 );
		}

		public function register_section( $sections ) {
			$sections[] = array(
				'id'    => self::SECTION,
				'title' => '<i class="fas fa-file-invoice-dollar"></i>' . esc_html__( 'Accounting', 'booking-and-rental-manager-for-woocommerce' ),
			);
			return $sections;
		}

		public function register_fields( $fields ) {
			$fields[ self::SECTION ] = array(
				array(
					'name'  => 'rbfw_accounting_heading',
					'label' => esc_html__( 'Payment Methods', 'booking-and-rental-manager-for-woocommerce' ),
					'type'  => 'heading',
					'desc'  => esc_html__( 'How customers actually pay you. These are the options offered at the standalone checkout, the choices you can record against any booking afterwards, and the buckets the per-method totals reconcile into. Recording a method against a WooCommerce order never changes the gateway that processed it.', 'booking-and-rental-manager-for-woocommerce' ),
				),
				array(
					'name'              => 'rbfw_payment_methods',
					'label'             => esc_html__( 'Methods', 'booking-and-rental-manager-for-woocommerce' ),
					'type'              => 'payment_methods',
					'callback'          => array( $this, 'render_methods' ),
					'sanitize_callback' => array( __CLASS__, 'sanitize_methods' ),
					'default'           => '',
				),
			);
			return $fields;
		}

		/* ================================================================== *
		 * Persistence
		 * ================================================================== */

		/**
		 * Sanitize the posted method rows.
		 *
		 * Slugs are derived from the label when absent, and de-duplicated, because two rows
		 * sharing a slug would silently overwrite each other and make the totals wrong.
		 *
		 * @param mixed $raw
		 * @return array
		 */
		public static function sanitize_methods( $raw ) {
			if ( ! is_array( $raw ) ) {
				return array();
			}

			$out = array();
			foreach ( $raw as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$label = isset( $row['label'] ) ? sanitize_text_field( wp_unslash( $row['label'] ) ) : '';
				if ( '' === trim( $label ) ) {
					// A method with no label cannot be shown or reconciled.
					continue;
				}

				$slug = isset( $row['slug'] ) ? sanitize_key( $row['slug'] ) : '';
				if ( '' === $slug ) {
					$slug = sanitize_key( sanitize_title( $label ) );
				}
				if ( '' === $slug ) {
					continue;
				}

				// Keep the slug stable but unique — a renamed duplicate must not clobber the
				// bookings already recorded against the original.
				$base = $slug;
				$i    = 2;
				while ( isset( $out[ $slug ] ) ) {
					$slug = $base . '_' . $i;
					$i++;
				}

				$out[ $slug ] = array(
					'label'        => $label,
					'icon'         => isset( $row['icon'] ) ? sanitize_html_class( $row['icon'] ) : '',
					'instructions' => isset( $row['instructions'] ) ? wp_kses_post( wp_unslash( $row['instructions'] ) ) : '',
					'enabled'      => ! empty( $row['enabled'] ),
				);
			}

			return $out;
		}

		/**
		 * Mirror the saved list into the option the registry reads.
		 *
		 * rbfw_payment_methods() reads rbfw_payment_settings['rbfw_payment_methods'] because
		 * that is where the gateway configuration already lives; this section stores under
		 * its own option so it can have its own tab. Copying on save keeps one source of
		 * truth for readers while letting the two screens stay independent.
		 *
		 * @param mixed $old
		 * @param mixed $new
		 * @return void
		 */
		public function sync_to_registry( $old, $new ) {
			$methods = ( is_array( $new ) && isset( $new['rbfw_payment_methods'] ) && is_array( $new['rbfw_payment_methods'] ) )
				? $new['rbfw_payment_methods']
				: array();

			$settings = get_option( RBFW_PAYMENT_METHODS_OPTION, array() );
			$settings = is_array( $settings ) ? $settings : array();
			$settings['rbfw_payment_methods'] = $methods;

			update_option( RBFW_PAYMENT_METHODS_OPTION, $settings );
			rbfw_payment_methods_flush_cache();
		}

		/** add_option passes (option, value) rather than (old, new). */
		public function sync_to_registry_added( $option, $value ) {
			$this->sync_to_registry( array(), $value );
		}

		/* ================================================================== *
		 * Repeater UI
		 * ================================================================== */

		public function render_methods( $args ) {
			$methods = rbfw_payment_methods();
			$name    = self::SECTION . '[rbfw_payment_methods]';
			$i       = 0;
			?>
			<div class="rbfw-pm-repeater" data-name="<?php echo esc_attr( $name ); ?>">
				<table class="rbfw-pm-table">
					<thead>
						<tr>
							<th style="width:60px;"><?php esc_html_e( 'Active', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Label', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Instructions (shown at checkout)', 'booking-and-rental-manager-for-woocommerce' ); ?></th>
							<th style="width:40px;"></th>
						</tr>
					</thead>
					<tbody class="rbfw-pm-body">
						<?php foreach ( $methods as $slug => $m ) : ?>
							<tr class="rbfw-pm-row">
								<td style="text-align:center;">
									<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][enabled]" value="1" <?php checked( ! empty( $m['enabled'] ) ); ?>>
								</td>
								<td>
									<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $m['label'] ); ?>">
									<?php // The slug is what past bookings were recorded against, so it is carried, not regenerated from a renamed label. ?>
									<input type="hidden" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][slug]" value="<?php echo esc_attr( $slug ); ?>">
									<code class="rbfw-pm-slug"><?php echo esc_html( $slug ); ?></code>
								</td>
								<td>
									<input type="text" class="large-text" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][instructions]" value="<?php echo esc_attr( $m['instructions'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Make cheques payable to…', 'booking-and-rental-manager-for-woocommerce' ); ?>">
								</td>
								<td><button type="button" class="button-link rbfw-pm-remove" aria-label="<?php esc_attr_e( 'Remove method', 'booking-and-rental-manager-for-woocommerce' ); ?>">&times;</button></td>
							</tr>
							<?php $i++; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
				<button type="button" class="button rbfw-pm-add"><?php esc_html_e( '+ Add method', 'booking-and-rental-manager-for-woocommerce' ); ?></button>
				<p class="description">
					<?php esc_html_e( 'Removing a method here does not change bookings already recorded against it — those keep showing the method they were paid with.', 'booking-and-rental-manager-for-woocommerce' ); ?>
				</p>
			</div>
			<?php
		}

		public function print_repeater_assets() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( ! $screen || false === strpos( (string) $screen->id, 'rbfw_settings_page' ) ) {
				return;
			}
			?>
			<style>
				.rbfw-pm-table { border-collapse: collapse; margin-bottom: 8px; width: 100%; max-width: 900px; }
				.rbfw-pm-table th { text-align: left; font-size: 12px; color: #50575e; padding: 0 8px 4px 0; font-weight: 600; }
				.rbfw-pm-table td { padding: 0 8px 8px 0; vertical-align: top; }
				.rbfw-pm-slug { display: block; margin-top: 3px; font-size: 11px; color: #787c82; background: none; padding: 0; }
				.rbfw-pm-remove { color: #b32d2e !important; font-size: 18px; text-decoration: none !important; line-height: 1; }
			</style>
			<script>
			( function ( $ ) {
				function reindex( $wrap ) {
					var name = $wrap.data( 'name' );
					$wrap.find( '.rbfw-pm-row' ).each( function ( i ) {
						$( this ).find( 'input' ).each( function () {
							var key = ( this.name.match( /\[(enabled|label|slug|instructions)\]$/ ) || [] )[ 1 ];
							if ( key ) { this.name = name + '[' + i + '][' + key + ']'; }
						} );
					} );
				}
				$( document ).on( 'click', '.rbfw-pm-add', function () {
					var $wrap = $( this ).closest( '.rbfw-pm-repeater' );
					var n     = $wrap.data( 'name' );
					var i     = $wrap.find( '.rbfw-pm-row' ).length;
					// Slug left empty: the sanitizer derives it from the label on save, which
					// is what makes a brand new method get a sensible, stable key.
					$wrap.find( '.rbfw-pm-body' ).append(
						'<tr class="rbfw-pm-row">' +
						'<td style="text-align:center;"><input type="checkbox" name="' + n + '[' + i + '][enabled]" value="1" checked></td>' +
						'<td><input type="text" class="regular-text" name="' + n + '[' + i + '][label]" value="">' +
						'<input type="hidden" name="' + n + '[' + i + '][slug]" value=""></td>' +
						'<td><input type="text" class="large-text" name="' + n + '[' + i + '][instructions]" value=""></td>' +
						'<td><button type="button" class="button-link rbfw-pm-remove">&times;</button></td>' +
						'</tr>'
					);
					reindex( $wrap );
				} );
				$( document ).on( 'click', '.rbfw-pm-remove', function () {
					var $wrap = $( this ).closest( '.rbfw-pm-repeater' );
					$( this ).closest( '.rbfw-pm-row' ).remove();
					reindex( $wrap );
				} );
			} )( jQuery );
			</script>
			<?php
		}
	}

	new RBFW_Payment_Methods_Settings();
}
