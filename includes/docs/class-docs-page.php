<?php
/**
 * Rental Docs — admin documentation module (renderer + menu).
 *
 * Read-only. Registers a top-level "Rental Docs" menu with 7 sub-pages that
 * render entirely from includes/docs/docs-data.php. Assets load only on these
 * pages. Every page renders the full document so the live search covers all
 * entries regardless of which sub-page is open.
 *
 * @package Booking_And_Rental_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RBFW_Docs_Page' ) ) {

	class RBFW_Docs_Page {

		/** @var string Text domain (main/free plugin). */
		const TD = 'booking-and-rental-manager-for-woocommerce';

		/** @var string Top-level menu slug. */
		const SLUG = 'rbfw_docs';

		/** @var array|null Cached data. */
		private $data = null;

		/** @var array Page hook suffixes we own (for asset gating). */
		private $hooks = array();

		/** @var array|null Section id => [title, dashicon]; built lazily (after init). */
		private $sections = null;

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 * Section map. Built lazily so the __() calls run on/after `init`
		 * (all our entry points fire on admin_menu / page render), avoiding
		 * WordPress 6.7's "textdomain loaded too early" notice.
		 *
		 * @return array
		 */
		private function sections() {
			if ( null === $this->sections ) {
				$this->sections = array(
					'getting-started' => array( __( 'Getting Started', self::TD ), 'dashicons-flag' ),
					'settings'        => array( __( 'Settings Reference', self::TD ), 'dashicons-admin-settings' ),
					'fields'          => array( __( 'Fields & Post Types', self::TD ), 'dashicons-database' ),
					'shortcodes'      => array( __( 'Shortcodes & Blocks', self::TD ), 'dashicons-shortcode' ),
					'woocommerce'     => array( __( 'WooCommerce Integration', self::TD ), 'dashicons-cart' ),
					'free-pro'        => array( __( 'Free vs Pro', self::TD ), 'dashicons-star-filled' ),
					'faq'             => array( __( 'Troubleshooting / FAQ', self::TD ), 'dashicons-editor-help' ),
				);
			}
			return $this->sections;
		}

		/* ============================ Data ============================ */

		private function data() {
			if ( null === $this->data ) {
				$file       = __DIR__ . '/docs-data.php';
				$this->data = file_exists( $file ) ? (array) require $file : array();
			}
			return $this->data;
		}

		/* ============================ Menu ============================ */

		public function register_menu() {
			// A single "Documentation" page under the main plugin (Rent Item)
			// menu. The whole documentation lives on this one page — the 7
			// sections, the in-page section nav and the live search are all
			// rendered together, so nothing needs its own submenu.
			$this->hooks[] = add_submenu_page(
				'edit.php?post_type=rbfw_item',
				__( 'Documentation', self::TD ),
				__( 'Documentation', self::TD ),
				'manage_options',
				self::SLUG,
				array( $this, 'render_page' )
			);
		}

		/** Single page callback — renders the full documentation. */
		public function render_page() {
			$this->render( 'getting-started' );
		}

		/* ============================ Assets ============================ */

		public function enqueue_assets( $hook ) {
			if ( ! in_array( $hook, $this->hooks, true ) ) {
				return;
			}
			$base = plugin_dir_path( __FILE__ ) . 'assets/';
			$url  = plugin_dir_url( __FILE__ ) . 'assets/';
			$cssv = file_exists( $base . 'docs.css' ) ? filemtime( $base . 'docs.css' ) : '1';
			$jsv  = file_exists( $base . 'docs.js' ) ? filemtime( $base . 'docs.js' ) : '1';
			wp_enqueue_style( 'rbfw-docs', $url . 'docs.css', array(), $cssv );
			wp_enqueue_script( 'rbfw-docs', $url . 'docs.js', array(), $jsv, true );
		}

		/* ============================ Helpers ============================ */

		/** Decode entity-encoded source text, then escape for safe display. */
		private function t( $s ) {
			return esc_html( html_entity_decode( (string) $s, ENT_QUOTES, 'UTF-8' ) );
		}

		/** Free / Pro badge. */
		private function badge( $plan ) {
			$plan  = ( 'pro' === $plan ) ? 'pro' : 'free';
			$label = ( 'pro' === $plan ) ? __( 'PRO', self::TD ) : __( 'FREE', self::TD );
			return '<span class="rbfw-doc-badge rbfw-doc-badge-' . esc_attr( $plan ) . '">' . esc_html( $label ) . '</span>';
		}

		/**
		 * Searchable-element attributes. Applied directly to a <tr>/<li>/<div>
		 * so no invalid wrapper element is introduced inside tables.
		 *
		 * @param string $text  Text the live-search matches against.
		 * @param string $class Extra classes for the element.
		 * @return string Attribute string (already escaped).
		 */
		private function entry_attrs( $text, $class = '' ) {
			$needle = strtolower( wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' ) ) );
			return ' class="rbfw-doc-entry ' . esc_attr( trim( $class ) ) . '" data-search="' . esc_attr( $needle ) . '"';
		}

		/**
		 * Plain-English name for a control type — admins should not have to read
		 * developer words like "multicheck" or "wysiwyg".
		 */
		private function friendly_type( $type ) {
			$map = array(
				'text'         => __( 'Text box', self::TD ),
				'textarea'     => __( 'Multi-line text', self::TD ),
				'wysiwyg'      => __( 'Formatted text', self::TD ),
				'select'       => __( 'Dropdown', self::TD ),
				'checkbox'     => __( 'On / off', self::TD ),
				'multicheck'   => __( 'Tick any that apply', self::TD ),
				'color'        => __( 'Colour picker', self::TD ),
				'media'        => __( 'Image / file', self::TD ),
				'generatepage' => __( 'Page chooser', self::TD ),
				'number'       => __( 'Number', self::TD ),
				'password'     => __( 'Password', self::TD ),
				'custom'       => __( 'Built-in panel', self::TD ),
			);
			return isset( $map[ $type ] ) ? $map[ $type ] : ucfirst( str_replace( '_', ' ', (string) $type ) );
		}

		/**
		 * Readable default. Turns stored values such as
		 * {"processing":"processing","completed":"completed"} into "Processing, Completed".
		 */
		private function friendly_default( $default, $options ) {
			$default = (string) $default;
			if ( '' === $default ) {
				return '';
			}
			// Map raw values to their human labels using the option list.
			$labels = array();
			foreach ( explode( '|', (string) $options ) as $pair ) {
				$kv = explode( '=', $pair, 2 );
				if ( isset( $kv[1] ) ) {
					$labels[ $kv[0] ] = $kv[1];
				}
			}
			$decoded = json_decode( $default, true );
			if ( is_array( $decoded ) ) {
				$out = array();
				foreach ( $decoded as $v ) {
					$out[] = isset( $labels[ $v ] ) ? $labels[ $v ] : $v;
				}
				return implode( ', ', $out );
			}
			return isset( $labels[ $default ] ) ? $labels[ $default ] : $default;
		}

		/**
		 * Turn "a=Label|b=Label2" into the choices an admin actually sees.
		 * Only the human labels are shown — the stored values behind them are
		 * an implementation detail and would just be noise here.
		 */
		private function options_chips( $opts ) {
			$opts = trim( (string) $opts );
			if ( '' === $opts ) {
				return '';
			}
			$out  = '';
			$seen = array();
			foreach ( explode( '|', $opts ) as $pair ) {
				$kv    = explode( '=', $pair, 2 );
				$label = isset( $kv[1] ) ? trim( $kv[1] ) : trim( $kv[0] );
				if ( '' === $label || isset( $seen[ $label ] ) ) {
					continue;
				}
				$seen[ $label ] = true;
				$out           .= '<span class="rbfw-doc-chip">' . $this->t( $label ) . '</span>';
			}
			return $out ? '<div class="rbfw-doc-chips">' . $out . '</div>' : '';
		}

		/* ============================ Render ============================ */

		public function render( $active = 'getting-started' ) {
			// Defense in depth: the menu is already registered with this cap,
			// but guard the output too (read-only docs, admins only).
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$d        = $this->data();
			$sections = $this->sections();
			if ( ! isset( $sections[ $active ] ) ) {
				$active = 'getting-started';
			}
			?>
			<div class="wrap rbfw-docs" data-active="<?php echo esc_attr( $active ); ?>">
				<div class="rbfw-docs-top">
					<div class="rbfw-docs-brand">
						<span class="dashicons dashicons-book-alt"></span>
						<div>
							<h1><?php esc_html_e( 'Rental Docs', self::TD ); ?></h1>
							<p><?php esc_html_e( 'Every menu, setting, field, shortcode and integration for Booking and Rental Manager (Free + Pro).', self::TD ); ?></p>
						</div>
					</div>
					<div class="rbfw-docs-searchbar">
						<span class="dashicons dashicons-search"></span>
						<input type="search" id="rbfw-docs-search" placeholder="<?php esc_attr_e( 'Search fields, settings, shortcodes…', self::TD ); ?>" autocomplete="off" spellcheck="false">
						<button type="button" id="rbfw-docs-search-clear" class="rbfw-docs-search-clear" aria-label="<?php esc_attr_e( 'Clear', self::TD ); ?>">&times;</button>
					</div>
					<p class="rbfw-docs-noresults" id="rbfw-docs-noresults" hidden><?php esc_html_e( 'No documentation matches your search.', self::TD ); ?></p>
				</div>

				<div class="rbfw-docs-layout">
					<nav class="rbfw-docs-nav" aria-label="<?php esc_attr_e( 'Documentation sections', self::TD ); ?>">
						<?php foreach ( $sections as $id => $meta ) : ?>
							<a href="#rbfw-sec-<?php echo esc_attr( $id ); ?>" class="rbfw-docs-navlink<?php echo $id === $active ? ' is-active' : ''; ?>" data-target="<?php echo esc_attr( $id ); ?>">
								<span class="dashicons <?php echo esc_attr( $meta[1] ); ?>"></span>
								<span class="rbfw-docs-navlabel"><?php echo esc_html( $meta[0] ); ?></span>
								<span class="rbfw-docs-navcount" data-count-for="<?php echo esc_attr( $id ); ?>"></span>
							</a>
						<?php endforeach; ?>
					</nav>

					<main class="rbfw-docs-main">
						<?php
						$this->section_getting_started( $d );
						$this->section_settings( $d );
						$this->section_fields( $d );
						$this->section_shortcodes( $d );
						$this->section_woocommerce( $d );
						$this->section_free_pro( $d );
						$this->section_faq( $d );
						?>
					</main>
				</div>
			</div>
			<?php
		}

		/** Section shell. */
		private function open_section( $id ) {
			$sections = $this->sections();
			$meta     = $sections[ $id ];
			echo '<section id="rbfw-sec-' . esc_attr( $id ) . '" class="rbfw-doc-section" data-section="' . esc_attr( $id ) . '">';
			echo '<h2 class="rbfw-doc-h2"><span class="dashicons ' . esc_attr( $meta[1] ) . '"></span>' . esc_html( $meta[0] ) . '</h2>';
		}
		private function close_section() {
			echo '<p class="rbfw-doc-section-empty rbfw-doc-muted" hidden>' . esc_html__( 'No matches in this section.', self::TD ) . '</p>';
			echo '</section>';
		}

		/* ---- 1. Getting Started ---- */
		private function section_getting_started( $d ) {
			$gs = isset( $d['getting_started'] ) ? $d['getting_started'] : array();
			$this->open_section( 'getting-started' );
			if ( ! empty( $gs['intro'] ) ) {
				echo '<p class="rbfw-doc-lead">' . $this->t( $gs['intro'] ) . '</p>';
			}
			echo '<ol class="rbfw-doc-flow">';
			$n = 0;
			foreach ( (array) ( isset( $gs['steps'] ) ? $gs['steps'] : array() ) as $step ) {
				$n++;
				echo '<li' . $this->entry_attrs( $step['title'] . ' ' . $step['body'], 'rbfw-doc-step' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<span class="rbfw-doc-step-num">' . esc_html( $n ) . '</span>';
				echo '<div class="rbfw-doc-step-body"><h3>' . $this->t( $step['title'] ) . '</h3><p>' . $this->t( $step['body'] ) . '</p>';
				if ( ! empty( $step['url'] ) ) {
					echo '<a class="rbfw-doc-openlink" href="' . esc_url( admin_url( $step['url'] ) ) . '"><span class="dashicons dashicons-external"></span>' . esc_html__( 'Open screen', self::TD ) . '</a>';
				}
				echo '</div></li>';
			}
			echo '</ol>';

			// Admin menu map.
			if ( ! empty( $d['menus']['items'] ) ) {
				echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Admin menu map', self::TD ) . '</h3>';
				if ( ! empty( $d['menus']['parent']['desc'] ) ) {
					echo '<p class="rbfw-doc-muted">' . $this->t( $d['menus']['parent']['desc'] ) . '</p>';
				}
				echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table"><thead><tr>';
				echo '<th>' . esc_html__( 'Menu', self::TD ) . '</th><th>' . esc_html__( 'What it does', self::TD ) . '</th><th>' . esc_html__( 'Capability', self::TD ) . '</th><th>' . esc_html__( 'Plan', self::TD ) . '</th></tr></thead><tbody>';
				foreach ( $d['menus']['items'] as $m ) {
					echo '<tr' . $this->entry_attrs( $m['title'] . ' ' . $m['desc'] . ' ' . $m['slug'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<td><a href="' . esc_url( admin_url( $m['slug'] ) ) . '">' . $this->t( $m['title'] ) . '</a></td>';
					echo '<td>' . $this->t( $m['desc'] ) . '</td>';
					echo '<td><code>' . $this->t( $m['cap'] ) . '</code></td>';
					echo '<td>' . $this->badge( $m['plan'] ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</tr>';
				}
				echo '</tbody></table></div>';
			}
			$this->close_section();
		}

		/* ---- 2. Settings Reference ---- */
		private function section_settings( $d ) {
			$this->open_section( 'settings' );
			echo '<p class="rbfw-doc-lead">' . esc_html__( 'Every field on the Settings screen, grouped by tab exactly as it appears in the admin. PRO tabs/fields are marked.', self::TD ) . '</p>';
			$settings = isset( $d['settings'] ) ? $d['settings'] : array();
			foreach ( $settings as $sec ) {
				$fields   = isset( $sec['fields'] ) ? $sec['fields'] : array();
				$sec_text = $sec['title'] . ' ' . ( isset( $sec['purpose'] ) ? $sec['purpose'] : '' );
				echo '<div' . $this->entry_attrs( $sec_text, 'rbfw-doc-acc' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<button type="button" class="rbfw-doc-acc-head" aria-expanded="false">';
				echo '<span class="rbfw-doc-acc-title">' . $this->t( $sec['title'] ) . ' ' . $this->badge( $sec['plan'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<span class="rbfw-doc-acc-meta"><span class="rbfw-doc-count">' . esc_html( count( $fields ) ) . ' ' . esc_html__( 'fields', self::TD ) . '</span><span class="dashicons dashicons-arrow-down-alt2"></span></span>';
				echo '</button>';
				echo '<div class="rbfw-doc-acc-body">';
				if ( ! empty( $sec['purpose'] ) ) {
					echo '<p class="rbfw-doc-muted"><strong>' . esc_html__( 'Location:', self::TD ) . '</strong> ' . $this->t( isset( $sec['path'] ) ? $sec['path'] : '' ) . ' &nbsp;·&nbsp; ' . $this->t( $sec['purpose'] ) . '</p>';
				}
				if ( $fields ) {
					$path = isset( $sec['path'] ) ? $sec['path'] . ' → ' . $sec['title'] : $sec['title'];
					echo '<div class="rbfw-doc-fieldlist">';
					foreach ( $fields as $f ) {
						$what  = isset( $f['what'] ) ? $f['what'] : $f['desc'];
						$where = isset( $f['where'] ) ? $f['where'] : '';
						$def   = $this->friendly_default( $f['default'], $f['options'] );
						$chips = $this->options_chips( $f['options'] );

						echo '<div' . $this->entry_attrs( $f['name'] . ' ' . $f['label'] . ' ' . $what . ' ' . $where . ' ' . $f['desc'], 'rbfw-doc-field' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '<div class="rbfw-doc-field-head"><span class="rbfw-doc-field-name">' . $this->t( $f['label'] ) . '</span>' . $this->badge( $f['plan'] ) . '<span class="rbfw-doc-field-type">' . esc_html( $this->friendly_type( $f['type'] ) ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

						echo '<dl class="rbfw-doc-dl">';
						echo '<dt>' . esc_html__( 'What it does', self::TD ) . '</dt><dd>' . ( '' !== $what ? $this->t( $what ) : '<span class="rbfw-doc-muted">' . esc_html__( 'No description available.', self::TD ) . '</span>' ) . '</dd>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						if ( '' !== $where ) {
							echo '<dt>' . esc_html__( 'Where it appears', self::TD ) . '</dt><dd>' . $this->t( $where ) . '</dd>';
						}
						echo '<dt>' . esc_html__( 'Default', self::TD ) . '</dt><dd>' . ( '' !== $def ? '<span class="rbfw-doc-default">' . $this->t( $def ) . '</span>' : '<span class="rbfw-doc-muted">' . esc_html__( 'Empty', self::TD ) . '</span>' ) . '</dd>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						if ( '' !== $chips ) {
							echo '<dt>' . esc_html__( 'Choices', self::TD ) . '</dt><dd>' . $chips . '</dd>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						echo '<dt>' . esc_html__( 'Where to find it', self::TD ) . '</dt><dd>' . $this->t( $path ) . '</dd>';
						echo '</dl>';
						echo '<div class="rbfw-doc-field-id">' . esc_html__( 'Reference:', self::TD ) . ' <code>' . $this->t( $f['name'] ) . '</code></div>';
						echo '</div>';
					}
					echo '</div>';
				} else {
					echo '<p class="rbfw-doc-muted">' . esc_html__( 'This tab uses custom controls with no individual saved fields.', self::TD ) . '</p>';
				}
				echo '</div></div>';
			}
			$this->close_section();
		}

		/* ---- 3. Fields & Post Types ---- */
		private function section_fields( $d ) {
			$this->open_section( 'fields' );

			// Rental types.
			echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Rental item types', self::TD ) . '</h3>';
			echo '<div class="rbfw-doc-cards">';
			foreach ( (array) ( isset( $d['item_types'] ) ? $d['item_types'] : array() ) as $it ) {
				echo '<div' . $this->entry_attrs( $it['name'] . ' ' . $it['desc'] . ' ' . $it['key'], 'rbfw-doc-card' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="rbfw-doc-card-head">' . $this->t( $it['name'] ) . ' ' . $this->badge( $it['plan'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<code class="rbfw-doc-key">' . $this->t( $it['key'] ) . '</code><p>' . $this->t( $it['desc'] ) . '</p>';
				echo '</div>';
			}
			echo '</div>';

			// Editor tabs (meta groups).
			echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Item editor tabs (meta fields)', self::TD ) . '</h3>';
			echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table"><thead><tr><th>' . esc_html__( 'Tab', self::TD ) . '</th><th>' . esc_html__( 'What you configure', self::TD ) . '</th><th>' . esc_html__( 'Plan', self::TD ) . '</th></tr></thead><tbody>';
			foreach ( (array) ( isset( $d['editor_tabs'] ) ? $d['editor_tabs'] : array() ) as $tab ) {
				echo '<tr' . $this->entry_attrs( $tab['name'] . ' ' . $tab['desc'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td><strong>' . $this->t( $tab['name'] ) . '</strong></td><td>' . $this->t( $tab['desc'] ) . '</td><td>' . $this->badge( $tab['plan'] ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</tbody></table></div>';

			// CPTs.
			echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Custom post types', self::TD ) . '</h3>';
			echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table"><thead><tr><th>' . esc_html__( 'Post type', self::TD ) . '</th><th>' . esc_html__( 'Slug', self::TD ) . '</th><th>' . esc_html__( 'Public', self::TD ) . '</th><th>' . esc_html__( 'Purpose', self::TD ) . '</th><th>' . esc_html__( 'Plan', self::TD ) . '</th></tr></thead><tbody>';
			foreach ( (array) ( isset( $d['post_types'] ) ? $d['post_types'] : array() ) as $pt ) {
				echo '<tr' . $this->entry_attrs( $pt['name'] . ' ' . $pt['slug'] . ' ' . $pt['desc'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td><strong>' . $this->t( $pt['name'] ) . '</strong></td><td><code>' . $this->t( $pt['slug'] ) . '</code></td><td>' . $this->t( $pt['public'] ) . '</td><td>' . $this->t( $pt['desc'] ) . '</td><td>' . $this->badge( $pt['plan'] ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</tbody></table></div>';

			// Taxonomies.
			echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Taxonomies', self::TD ) . '</h3>';
			echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table"><thead><tr><th>' . esc_html__( 'Taxonomy', self::TD ) . '</th><th>' . esc_html__( 'Slug', self::TD ) . '</th><th>' . esc_html__( 'Attached to', self::TD ) . '</th><th>' . esc_html__( 'Purpose', self::TD ) . '</th><th>' . esc_html__( 'Plan', self::TD ) . '</th></tr></thead><tbody>';
			foreach ( (array) ( isset( $d['taxonomies'] ) ? $d['taxonomies'] : array() ) as $tx ) {
				echo '<tr' . $this->entry_attrs( $tx['name'] . ' ' . $tx['slug'] . ' ' . $tx['desc'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td><strong>' . $this->t( $tx['name'] ) . '</strong></td><td><code>' . $this->t( $tx['slug'] ) . '</code></td><td><code>' . $this->t( $tx['object'] ) . '</code></td><td>' . $this->t( $tx['desc'] ) . '</td><td>' . $this->badge( $tx['plan'] ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</tbody></table></div>';
			$this->close_section();
		}

		/* ---- 4. Shortcodes & Blocks ---- */
		private function section_shortcodes( $d ) {
			$this->open_section( 'shortcodes' );
			echo '<p class="rbfw-doc-lead">' . esc_html__( 'Copy-paste shortcodes for your pages. Blocks and Elementor widgets wrap the same output.', self::TD ) . '</p>';
			foreach ( (array) ( isset( $d['shortcodes'] ) ? $d['shortcodes'] : array() ) as $sc ) {
				echo '<div' . $this->entry_attrs( $sc['tag'] . ' ' . $sc['desc'] . ' ' . $sc['example'], 'rbfw-doc-sc' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="rbfw-doc-sc-head"><code class="rbfw-doc-sc-tag">[' . $this->t( $sc['tag'] ) . ']</code> ' . $this->badge( $sc['plan'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<p>' . $this->t( $sc['desc'] ) . '</p>';
				echo '<div class="rbfw-doc-copyrow"><code class="rbfw-doc-code" data-copy>' . $this->t( $sc['example'] ) . '</code><button type="button" class="rbfw-doc-copybtn" aria-label="' . esc_attr__( 'Copy shortcode', self::TD ) . '"><span class="dashicons dashicons-clipboard"></span><span class="rbfw-doc-copytext">' . esc_html__( 'Copy', self::TD ) . '</span></button></div>';
				if ( ! empty( $sc['atts'] ) ) {
					echo '<table class="rbfw-doc-table rbfw-doc-atts"><thead><tr><th>' . esc_html__( 'Attribute', self::TD ) . '</th><th>' . esc_html__( 'Description', self::TD ) . '</th></tr></thead><tbody>';
					foreach ( $sc['atts'] as $att => $adesc ) {
						echo '<tr><td><code>' . $this->t( $att ) . '</code></td><td>' . $this->t( $adesc ) . '</td></tr>';
					}
					echo '</tbody></table>';
				}
				echo '</div>';
			}
			// Blocks.
			echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Blocks & page builders', self::TD ) . '</h3>';
			echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table"><thead><tr><th>' . esc_html__( 'Block / widget', self::TD ) . '</th><th>' . esc_html__( 'Description', self::TD ) . '</th><th>' . esc_html__( 'Plan', self::TD ) . '</th></tr></thead><tbody>';
			foreach ( (array) ( isset( $d['blocks'] ) ? $d['blocks'] : array() ) as $b ) {
				echo '<tr' . $this->entry_attrs( $b['name'] . ' ' . $b['desc'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td><strong>' . $this->t( $b['name'] ) . '</strong></td><td>' . $this->t( $b['desc'] ) . '</td><td>' . $this->badge( $b['plan'] ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</tbody></table></div>';
			$this->close_section();
		}

		/* ---- 5. WooCommerce Integration ---- */
		private function section_woocommerce( $d ) {
			$wc = isset( $d['woocommerce'] ) ? $d['woocommerce'] : array();
			$this->open_section( 'woocommerce' );
			if ( ! empty( $wc['intro'] ) ) {
				echo '<p class="rbfw-doc-lead">' . $this->t( $wc['intro'] ) . '</p>';
			}
			echo '<div class="rbfw-doc-cards">';
			foreach ( (array) ( isset( $wc['topics'] ) ? $wc['topics'] : array() ) as $tp ) {
				echo '<div' . $this->entry_attrs( $tp['title'] . ' ' . $tp['body'], 'rbfw-doc-card' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="rbfw-doc-card-head">' . $this->t( $tp['title'] ) . ' ' . $this->badge( $tp['plan'] ) . '</div><p>' . $this->t( $tp['body'] ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';
			}
			echo '</div>';

			echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Email notifications', self::TD ) . '</h3>';
			echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table"><thead><tr><th>' . esc_html__( 'Email', self::TD ) . '</th><th>' . esc_html__( 'Trigger', self::TD ) . '</th><th>' . esc_html__( 'Details', self::TD ) . '</th><th>' . esc_html__( 'Plan', self::TD ) . '</th></tr></thead><tbody>';
			foreach ( (array) ( isset( $wc['emails'] ) ? $wc['emails'] : array() ) as $em ) {
				echo '<tr' . $this->entry_attrs( $em['name'] . ' ' . $em['trigger'] . ' ' . $em['body'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td><strong>' . $this->t( $em['name'] ) . '</strong></td><td>' . $this->t( $em['trigger'] ) . '</td><td>' . $this->t( $em['body'] ) . '</td><td>' . $this->badge( $em['plan'] ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</tbody></table></div>';

			echo '<h3 class="rbfw-doc-h3">' . esc_html__( 'Automation, cron & capabilities', self::TD ) . '</h3>';
			echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table"><thead><tr><th>' . esc_html__( 'Item', self::TD ) . '</th><th>' . esc_html__( 'Description', self::TD ) . '</th><th>' . esc_html__( 'Plan', self::TD ) . '</th></tr></thead><tbody>';
			foreach ( (array) ( isset( $wc['automation'] ) ? $wc['automation'] : array() ) as $au ) {
				echo '<tr' . $this->entry_attrs( $au['name'] . ' ' . $au['desc'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td><strong>' . $this->t( $au['name'] ) . '</strong></td><td>' . $this->t( $au['desc'] ) . '</td><td>' . $this->badge( $au['plan'] ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</tbody></table></div>';
			$this->close_section();
		}

		/* ---- 6. Free vs Pro ---- */
		private function section_free_pro( $d ) {
			$this->open_section( 'free-pro' );
			echo '<p class="rbfw-doc-lead">' . esc_html__( 'What each feature includes in Free versus Pro. Based on the actual code (which plugin registers the feature).', self::TD ) . '</p>';
			echo '<div class="rbfw-doc-tablewrap"><table class="rbfw-doc-table rbfw-doc-matrix"><thead><tr><th>' . esc_html__( 'Feature', self::TD ) . '</th><th>' . esc_html__( 'Free', self::TD ) . '</th><th>' . esc_html__( 'Pro', self::TD ) . '</th></tr></thead><tbody>';
			foreach ( (array) ( isset( $d['free_vs_pro'] ) ? $d['free_vs_pro'] : array() ) as $row ) {
				echo '<tr' . $this->entry_attrs( $row['feature'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td><strong>' . $this->t( $row['feature'] ) . '</strong></td>';
				echo '<td>' . $this->cell( $row['free'] ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td>' . $this->cell( $row['pro'] ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</tr>';
			}
			echo '</tbody></table></div>';
			$this->close_section();
		}

		/** Matrix cell: bool → check/cross, string → text. */
		private function cell( $v ) {
			if ( true === $v ) {
				return '<span class="rbfw-doc-yes dashicons dashicons-yes-alt"></span>';
			}
			if ( false === $v ) {
				return '<span class="rbfw-doc-no dashicons dashicons-minus"></span>';
			}
			return '<span class="rbfw-doc-partial">' . $this->t( $v ) . '</span>';
		}

		/* ---- 7. FAQ ---- */
		private function section_faq( $d ) {
			$this->open_section( 'faq' );
			echo '<p class="rbfw-doc-lead">' . esc_html__( 'Common issues surfaced during the audit, with the exact fix.', self::TD ) . '</p>';
			foreach ( (array) ( isset( $d['faq'] ) ? $d['faq'] : array() ) as $item ) {
				echo '<div' . $this->entry_attrs( $item['q'] . ' ' . $item['a'], 'rbfw-doc-acc' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<button type="button" class="rbfw-doc-acc-head" aria-expanded="false"><span class="rbfw-doc-acc-title"><span class="dashicons dashicons-editor-help"></span>' . $this->t( $item['q'] ) . '</span><span class="rbfw-doc-acc-meta"><span class="dashicons dashicons-arrow-down-alt2"></span></span></button>';
				echo '<div class="rbfw-doc-acc-body"><p>' . $this->t( $item['a'] ) . '</p></div>';
				echo '</div>';
			}
			$this->close_section();
		}
	}

	new RBFW_Docs_Page();
}
