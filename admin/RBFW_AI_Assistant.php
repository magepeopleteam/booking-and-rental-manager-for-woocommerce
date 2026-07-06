<?php
/**
 * AI Assistant AJAX Handlers
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * AJAX handler class for AI content generation and SEO scoring
 */
class RBFW_AI_Assistant {

	/**
	 * Nonce action name
	 */
	const NONCE_ACTION = 'rbfw_ai_action';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_rbfw_ai_generate', array( $this, 'ajax_generate' ) );
		add_action( 'wp_ajax_rbfw_ai_seo_score', array( $this, 'ajax_seo_score' ) );
		add_action( 'wp_ajax_rbfw_ai_validate_key', array( $this, 'ajax_validate_key' ) );
		add_action( 'wp_ajax_rbfw_ai_get_models', array( $this, 'ajax_get_models' ) );
	}

	/**
	 * Generate AI content
	 */
	public function ajax_generate() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$input   = isset( $_POST['input'] ) ? sanitize_textarea_field( wp_unslash( $_POST['input'] ) ) : '';
		$context = isset( $_POST['context'] ) ? json_decode( stripslashes( wp_unslash( $_POST['context'] ) ), true ) : array();

		// Only the generation type is required. The input (usually the current title)
		// is optional: build_prompt() intentionally supports an empty input — e.g. it
		// generates a fresh title/subtitle/description when the field is still blank.
		// Requiring input here previously made "AI Title" on a new item fail with
		// "Missing required fields".
		if ( empty( $type ) ) {
			wp_send_json_error( 'Missing required fields' );
		}

		$provider = RBFW_AI_Manager::get_active_provider();
		if ( ! $provider ) {
			wp_send_json_error( 'No AI provider configured. Please add an API key in Settings > AI & SEO.' );
		}
		// Surface a clear, actionable message instead of the provider's cryptic
		// "invalid authorization header or token" when the key is missing/blank.
		if ( ! $provider->is_configured() ) {
			wp_send_json_error( __( 'No API key set for the selected AI provider. Add your key in Settings > AI & SEO, then try again.', 'booking-and-rental-manager-for-woocommerce' ) );
		}

		$prompt = $this->build_prompt( $type, $input, $context );

		// Check cache
		$cache_key = 'rbfw_ai_' . md5( $prompt );
		$cached    = get_transient( $cache_key );
		if ( $cached ) {
			wp_send_json_success( $cached );
		}

		$result = $provider->generate_content( $prompt );

		if ( ! $result['success'] ) {
			wp_send_json_error( $result['message'] );
		}

		// Clean the model output (strip code fences, quotes, leading labels)
		// so the JS can write the result directly into a form field. The "all"
		// type is expected to return raw JSON that the JS will JSON.parse, so
		// we leave it untouched.
		if ( 'all' !== $type ) {
			$result['content'] = $this->clean_ai_output( $type, $result['content'] );
		}

		// Cache for 1 hour
		set_transient( $cache_key, $result, HOUR_IN_SECONDS );

		wp_send_json_success( $result );
	}

	/**
	 * Calculate SEO score
	 */
	public function ajax_seo_score() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$slug        = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		$score = $this->calculate_seo_score( $title, $slug, $description );

		wp_send_json_success( $score );
	}

	/**
	 * Validate API key
	 */
	public function ajax_validate_key() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$provider_id = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$api_key     = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

		$provider = RBFW_AI_Manager::get_provider( $provider_id );
		if ( ! $provider ) {
			wp_send_json_error( 'Invalid provider' );
		}

		$valid = $provider->validate_api_key( $api_key );

		wp_send_json_success( array( 'valid' => $valid ) );
	}

	/**
	 * Get available models for a provider
	 */
	public function ajax_get_models() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$provider_id = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$api_key     = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$force       = ! empty( $_POST['force'] );

		$provider = RBFW_AI_Manager::get_provider( $provider_id );
		if ( ! $provider ) {
			wp_send_json_error( 'Invalid provider' );
		}

		// Re-instantiate the provider with the supplied API key (the static
		// get_provider() returns an un-configured instance with no key).
		$provider_class  = get_class( $provider );
		$settings        = get_option( 'rbfw_ai_settings', array() );
		$effective_key   = $api_key !== '' ? $api_key : ( $settings[ "rbfw_ai_{$provider_id}_key" ] ?? '' );
		$configured      = new $provider_class(
			array(
				'api_key'     => $effective_key,
				'model'       => $settings['rbfw_ai_model'] ?? '',
				'max_tokens'  => $settings['rbfw_ai_max_tokens'] ?? 500,
				'temperature' => $settings['rbfw_ai_temperature'] ?? 0.7,
			)
		);

		// If this provider has no remote models endpoint, return the static list
		// immediately. Nothing to fetch, nothing to cache.
		$models_endpoint = method_exists( $configured, 'get_models_endpoint' )
			? $configured->get_models_endpoint()
			: '';
		if ( '' === $models_endpoint ) {
			wp_send_json_success(
				array(
					'models'  => $configured->get_available_models(),
					'source'  => 'no_endpoint',
					'message' => __( 'This provider has no remote models endpoint; showing the built-in list.', 'booking-and-rental-manager-for-woocommerce' ),
				)
			);
		}

		// Cache successful remote lookups for 1 hour. Pass force=1 to bypass
		// the cache (used by the "Fetch Models" button on the settings page).
		$cache_key = 'rbfw_ai_models_' . $provider_id . '_' . md5( $effective_key );
		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( is_array( $cached ) && ! empty( $cached ) ) {
				wp_send_json_success(
					array(
						'models'  => $cached,
						'source'  => 'cache',
						'message' => '',
					)
				);
			}
		}

		$result = $configured->fetch_remote_models();
		$source = isset( $result['source'] ) ? $result['source'] : 'error';
		$models = isset( $result['models'] ) ? $result['models'] : $configured->get_available_models();
		$msg    = isset( $result['message'] ) ? $result['message'] : '';

		if ( 'remote' === $source ) {
			set_transient( $cache_key, $models, HOUR_IN_SECONDS );
		}

		wp_send_json_success(
			array(
				'models'  => $models,
				'source'  => $source,
				'message' => $msg,
			)
		);
	}

	/**
	 * Build prompt based on generation type
	 *
	 * @param string $type    The type of content to generate.
	 * @param string $input   The user input.
	 * @param array  $context Additional context.
	 * @return string
	 */
	private function build_prompt( $type, $input, $context ) {
		$item_type = $context['item_type'] ?? 'rental item';
		$category  = $context['category'] ?? '';

		switch ( $type ) {
			case 'title':
				$base = "Generate an SEO-optimized title for a {$item_type} rental listing. " .
					"The title should be 50-60 characters, include relevant keywords, and be compelling.";
				if ( '' !== $input ) {
					$base .= " Improve / rewrite this existing title: \"{$input}\".";
				} else {
					$base .= " Generate a fresh title.";
				}
				$base .= ( $category ? " Category: {$category}. " : ' ' ) .
					"Return ONLY the title, no quotes, no labels, no markdown.";
				return $base;

			case 'slug':
				$base = "Generate a URL-friendly slug for a {$item_type} rental listing. " .
					"The slug should be 3-5 words, lowercase, hyphen-separated, no stop words.";
				if ( '' !== $input ) {
					$base .= " Based on this title: \"{$input}\".";
				}
				$base .= " Return ONLY the slug (letters, digits, hyphens), no quotes, no labels, no markdown.";
				return $base;

			case 'subtitle':
				$base = "Write a short subtitle (30-70 characters) for a {$item_type} rental listing. " .
					"It should be a single line, descriptive, and inviting.";
				if ( '' !== $input ) {
					$base .= " The title is: \"{$input}\".";
				}
				$base .= ( $category ? " Category: {$category}. " : ' ' ) .
					"Return ONLY the subtitle, no quotes, no labels, no markdown.";
				return $base;

			case 'description':
				$base = "Generate an SEO-optimized description for a {$item_type} rental listing. " .
					"The description should be 150-160 characters, include a call-to-action, and highlight key benefits.";
				if ( '' !== $input ) {
					$base .= " Based on this title: \"{$input}\".";
				}
				$base .= ( $category ? " Category: {$category}. " : ' ' ) .
					"Return ONLY the description, no quotes, no labels, no markdown.";
				return $base;

			case 'all':
				$base = "Generate SEO-optimized content for a {$item_type} rental listing.";
				if ( '' !== $input ) {
					$base .= " Based on this title: \"{$input}\".";
				}
				$base .= ( $category ? " Category: {$category}." : '' ) .
					" Return a JSON object with three fields: title (50-60 chars), slug (3-5 words, hyphenated), and description (150-160 chars with CTA). " .
					"Format: {\"title\": \"...\", \"slug\": \"...\", \"description\": \"...\"}. " .
					"Return ONLY the JSON, no surrounding text.";
				return $base;

			default:
				return $input;
		}
	}

	/**
	 * Clean a single-field AI response so it can be written directly into a
	 * form field without further processing.
	 *
	 * Strips:
	 *   - Markdown code fences (``` or `)
	 *   - Surrounding straight/curly quotes
	 *   - Leading labels like "Title:", "Slug:", "Here is the title:"
	 *   - A "type": "value" JSON-ish prefix (only kept for the "all" type)
	 *   - Excess whitespace
	 *
	 * For slugs, additionally:
	 *   - Lower-cases
	 *   - Replaces whitespace with hyphens
	 *   - Removes any character that is not [a-z0-9-]
	 *   - Collapses repeated hyphens and trims leading/trailing hyphens
	 *
	 * @param string $type  One of title|slug|subtitle|description|all.
	 * @param string $raw   Raw model output.
	 * @return string
	 */
	private function clean_ai_output( $type, $raw ) {
		if ( ! is_string( $raw ) ) {
			return '';
		}

		$text = $raw;

		// 1. Strip markdown code fences.
		$text = preg_replace( '/^```[a-zA-Z0-9_+\-]*\s*/', '', trim( $text ) );
		$text = preg_replace( '/```\s*$/', '', $text );
		$text = str_replace( '`', '', $text );

		// 2. Strip "type": "value" or "type": value JSON-ish prefix (the "all"
		//    response is handled by the caller; for single fields this strips
		//    e.g. `{"title": "Foo"}` -> `Foo`).
		if ( preg_match( '/^\{?\s*"?(?:title|slug|description|subtitle)"?\s*:\s*"(.*)"\s*\}?$/is', $text, $m ) ) {
			$text = $m[1];
		}

		// 3. Strip leading labels: "Title:", "Slug:", "Here is your title:", etc.
		$text = preg_replace( '/^\s*(?:here\s+is|here\'s|answer|output|result|response)\s*(?:your|the)?\s*(?:title|slug|description|subtitle|answer)?\s*[:\-]\s*/i', '', $text );
		$text = preg_replace( '/^\s*(?:title|slug|description|subtitle)\s*[:\-]\s*/i', '', $text );

		// 4. Strip surrounding straight/curly quotes.
		$text = trim( $text );
		$text = trim( $text, "\"'“”‘’" );

		// 5. Collapse internal whitespace.
		$text = preg_replace( '/\s+/', ' ', $text );

		if ( 'slug' === $type ) {
			$text = strtolower( $text );
			$text = preg_replace( '/[^a-z0-9]+/', '-', $text );
			$text = trim( $text, '-' );
			$text = preg_replace( '/-+/', '-', $text );
		} elseif ( 'subtitle' === $type ) {
			// Cap subtitle length to a reasonable one-line value.
			if ( function_exists( 'mb_substr' ) ) {
				$text = mb_substr( $text, 0, 120 );
			} else {
				$text = substr( $text, 0, 120 );
			}
		}

		return trim( $text );
	}

	/**
	 * Calculate SEO score for title, slug, and description
	 *
	 * @param string $title       The title.
	 * @param string $slug        The slug.
	 * @param string $description The description.
	 * @return array
	 */
	/**
	 * Parse an admin "min-max" length range setting (e.g. "150-160") into
	 * [ min, max ] integers, falling back to the supplied defaults when the value
	 * is empty or malformed.
	 *
	 * @param mixed $value       The saved setting value.
	 * @param int   $default_min Fallback minimum.
	 * @param int   $default_max Fallback maximum.
	 * @return array{0:int,1:int}
	 */
	private function parse_length_range( $value, $default_min, $default_max ) {
		$min = (int) $default_min;
		$max = (int) $default_max;
		if ( is_string( $value ) && preg_match( '/^\s*(\d+)\s*-\s*(\d+)\s*$/', $value, $m ) ) {
			$min = (int) $m[1];
			$max = (int) $m[2];
			if ( $min > $max ) {
				$tmp = $min;
				$min = $max;
				$max = $tmp;
			}
		}
		return array( $min, $max );
	}

	private function calculate_seo_score( $title, $slug, $description ) {
		$score    = 0;
		$feedback = array();

		// Optimal length ranges are admin-configurable in Settings > AI & SEO
		// (rbfw_seo_title_length / rbfw_seo_description_length). Respect them instead
		// of hardcoding 50-60 / 150-160, and derive a slightly wider "acceptable" band.
		$settings = get_option( 'rbfw_ai_settings', array() );
		list( $t_min, $t_max ) = $this->parse_length_range( $settings['rbfw_seo_title_length'] ?? '', 50, 60 );
		list( $d_min, $d_max ) = $this->parse_length_range( $settings['rbfw_seo_description_length'] ?? '', 150, 160 );
		$t_amin = (int) floor( $t_min * 0.8 );
		$t_amax = (int) ceil( $t_max * 1.15 );
		$d_amin = (int) floor( $d_min * 0.8 );
		$d_amax = (int) ceil( $d_max * 1.15 );

		// Title scoring (35 points)
		$title_len = strlen( $title );
		if ( $title_len >= $t_min && $title_len <= $t_max ) {
			$score               += 35;
			$feedback['title']    = array( 'status' => 'good', 'message' => sprintf( 'Perfect length (%d-%d chars)', $t_min, $t_max ) );
		} elseif ( $title_len >= $t_amin && $title_len <= $t_amax ) {
			$score               += 25;
			$feedback['title']    = array( 'status' => 'ok', 'message' => 'Acceptable length' );
		} else {
			$score               += 10;
			$feedback['title']    = array(
				'status'  => 'bad',
				'message' => $title_len < $t_min ? sprintf( 'Too short (min %d chars)', $t_min ) : sprintf( 'Too long (max %d chars)', $t_max ),
			);
		}

		// Power words bonus
		$power_words = array( 'best', 'top', 'ultimate', 'guide', 'review', 'cheap', 'premium', 'luxury', 'affordable' );
		if ( preg_match( '/\b(' . implode( '|', $power_words ) . ')\b/i', $title ) ) {
			$score += 5;
		}

		// Slug scoring (25 points)
		$slug_words      = explode( '-', $slug );
		$slug_word_count = count( $slug_words );
		if ( $slug_word_count >= 3 && $slug_word_count <= 5 ) {
			$score               += 15;
			$feedback['slug']     = array( 'status' => 'good', 'message' => 'Optimal word count (3-5 words)' );
		} elseif ( $slug_word_count >= 2 && $slug_word_count <= 7 ) {
			$score               += 10;
			$feedback['slug']     = array( 'status' => 'ok', 'message' => 'Acceptable word count' );
		} else {
			$score               += 5;
			$feedback['slug']     = array(
				'status'  => 'bad',
				'message' => $slug_word_count < 2 ? 'Too few words' : 'Too many words',
			);
		}

		// Slug format check
		if ( preg_match( '/^[a-z][a-z0-9-]*$/', $slug ) && ! preg_match( '/^-|-$/', $slug ) ) {
			$score += 10;
		}

		// Description scoring (40 points)
		$desc_len = strlen( $description );
		if ( $desc_len >= $d_min && $desc_len <= $d_max ) {
			$score                       += 30;
			$feedback['description']      = array( 'status' => 'good', 'message' => sprintf( 'Perfect length (%d-%d chars)', $d_min, $d_max ) );
		} elseif ( $desc_len >= $d_amin && $desc_len <= $d_amax ) {
			$score                       += 20;
			$feedback['description']      = array( 'status' => 'ok', 'message' => 'Acceptable length' );
		} else {
			$score                       += 10;
			$feedback['description']      = array(
				'status'  => 'bad',
				'message' => $desc_len < $d_min ? sprintf( 'Too short (min %d chars)', $d_min ) : sprintf( 'Too long (max %d chars)', $d_max ),
			);
		}

		// CTA bonus
		$cta_words = array( 'book', 'rent', 'reserve', 'get', 'try', 'start', 'discover', 'explore' );
		if ( preg_match( '/\b(' . implode( '|', $cta_words ) . ')\b/i', $description ) ) {
			$score += 10;
		}

		return array(
			'score'    => min( 100, $score ),
			'grade'    => $this->get_grade( $score ),
			'feedback' => $feedback,
			'details'  => array(
				'title_length'       => $title_len,
				'slug_words'         => $slug_word_count,
				'description_length' => $desc_len,
			),
		);
	}

	/**
	 * Get grade letter from score
	 *
	 * @param int $score The score.
	 * @return string
	 */
	private function get_grade( $score ) {
		if ( $score >= 90 ) {
			return 'A+';
		}
		if ( $score >= 80 ) {
			return 'A';
		}
		if ( $score >= 70 ) {
			return 'B';
		}
		if ( $score >= 60 ) {
			return 'C';
		}
		if ( $score >= 50 ) {
			return 'D';
		}
		return 'F';
	}
}

new RBFW_AI_Assistant();
