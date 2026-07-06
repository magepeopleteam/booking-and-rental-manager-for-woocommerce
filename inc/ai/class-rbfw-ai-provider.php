<?php
/**
 * Abstract AI Provider Base Class
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Base class for all AI providers
 */
abstract class RBFW_AI_Provider {

	/**
	 * API key for the provider
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * Model to use
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Maximum tokens for response
	 *
	 * @var int
	 */
	protected $max_tokens;

	/**
	 * Temperature for generation (creativity)
	 *
	 * @var float
	 */
	protected $temperature;

	/**
	 * Constructor
	 *
	 * @param array $config Configuration options.
	 */
	public function __construct( $config = array() ) {
		// Settings are stored as strings (form inputs). The chat APIs reject
		// max_tokens / temperature when sent as strings ("Invalid input: expected
		// number, received string"), so coerce to the correct scalar types here —
		// once, for every provider — with safe fallbacks for empty/invalid values.
		$max_tokens  = $config['max_tokens'] ?? 500;
		$temperature = $config['temperature'] ?? 0.7;

		// Trim the key: a stray space/newline from copy-paste otherwise corrupts the
		// "Authorization: Bearer <key>" header and the API returns "invalid
		// authorization header or token".
		$this->api_key     = trim( (string) ( $config['api_key'] ?? '' ) );
		$this->model       = (string) ( $config['model'] ?? '' );
		$this->max_tokens  = ( is_numeric( $max_tokens ) && (int) $max_tokens > 0 ) ? (int) $max_tokens : 500;
		$this->temperature = is_numeric( $temperature ) ? (float) $temperature : 0.7;
	}

	/**
	 * Whether a usable (non-empty) API key is configured for this provider.
	 *
	 * @return bool
	 */
	public function is_configured() {
		return '' !== trim( (string) $this->api_key );
	}

	/**
	 * Get the provider ID
	 *
	 * @return string
	 */
	abstract public function get_provider_id();

	/**
	 * Get the provider display name
	 *
	 * @return string
	 */
	abstract public function get_provider_name();

	/**
	 * Get the API endpoint URL
	 *
	 * @return string
	 */
	abstract public function get_api_endpoint();

	/**
	 * Get available models for this provider
	 *
	 * @return array
	 */
	abstract public function get_available_models();

	/**
	 * Get the models-listing endpoint URL, if the provider exposes one.
	 *
	 * Return an empty string to indicate that this provider does not support
	 * remote model discovery. Sub-classes that do (e.g. OpenAI-compatible APIs)
	 * should override this.
	 *
	 * @return string
	 */
	public function get_models_endpoint() {
		return '';
	}

	/**
	 * Whether this provider has a remote /models endpoint to fetch from.
	 *
	 * @return bool
	 */
	public function supports_remote_models() {
		return '' !== $this->get_models_endpoint();
	}

	/**
	 * Public alias of {@see get_available_models()} for callers outside the
	 * class hierarchy (settings page, JS-driven admin UI). Kept as a thin
	 * wrapper so the abstract method remains the single source of truth.
	 *
	 * @return array
	 */
	public function get_static_models() {
		return $this->get_available_models();
	}

	/**
	 * Fetch the available models from the provider's remote API.
	 *
	 * Returns a structured array so callers outside this class hierarchy
	 * (e.g. the AJAX handler in {@see RBFW_AI_Assistant}) can surface a
	 * human-readable reason when the remote call fails:
	 *
	 *   array(
	 *     'models'  => array( id => label, ... ), // id => label map
	 *     'source'  => 'remote' | 'no_endpoint' | 'no_key' | 'error' | 'fallback',
	 *     'message' => '',                        // human-readable reason on failure
	 *   )
	 *
	 * Falls back to the static {@see get_available_models()} list when:
	 *   - the provider has no models endpoint,
	 *   - the API key is empty,
	 *   - the remote request fails for any reason (network, auth, parse).
	 *
	 * The expected response shape is the OpenAI-compatible one:
	 *   { "data": [ { "id": "model-id", "name": "Display Name" }, ... ] }
	 *
	 * @return array
	 */
	public function fetch_remote_models() {
		$endpoint = $this->get_models_endpoint();
		if ( '' === $endpoint ) {
			return array(
				'models'  => $this->get_available_models(),
				'source'  => 'no_endpoint',
				'message' => '',
			);
		}

		if ( empty( $this->api_key ) ) {
			return array(
				'models'  => $this->get_available_models(),
				'source'  => 'no_key',
				'message' => '',
			);
		}

		$response = wp_remote_get(
			$endpoint,
			array(
				'headers' => $this->build_request_headers(),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'models'  => $this->get_available_models(),
				'source'  => 'error',
				'message' => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			$body_text = wp_remote_retrieve_body( $response );
			$error     = json_decode( $body_text, true );
			$message   = isset( $error['error']['message'] ) ? $error['error']['message'] : "API returned status $code";
			return array(
				'models'  => $this->get_available_models(),
				'source'  => 'error',
				'message' => $message,
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['data'] ) || ! is_array( $body['data'] ) ) {
			return array(
				'models'  => $this->get_available_models(),
				'source'  => 'error',
				'message' => __( 'Provider returned an unexpected response format.', 'booking-and-rental-manager-for-woocommerce' ),
			);
		}

		$models = array();
		foreach ( $body['data'] as $row ) {
			if ( empty( $row['id'] ) ) {
				continue;
			}
			$id    = (string) $row['id'];
			$label = isset( $row['name'] ) && '' !== trim( (string) $row['name'] )
				? (string) $row['name']
				: $id;
			$models[ $id ] = $label;
		}

		if ( empty( $models ) ) {
			return array(
				'models'  => $this->get_available_models(),
				'source'  => 'error',
				'message' => __( 'Provider returned no models.', 'booking-and-rental-manager-for-woocommerce' ),
			);
		}

		return array(
			'models'  => $models,
			'source'  => 'remote',
			'message' => '',
		);
	}

	/**
	 * Build the request body for the API
	 *
	 * @param string $prompt The prompt to send.
	 * @param array  $options Additional options.
	 * @return array
	 */
	abstract protected function build_request_body( $prompt, $options );

	/**
	 * Parse the API response
	 *
	 * @param array $response The raw API response.
	 * @return array
	 */
	abstract protected function parse_response( $response );

	/**
	 * Generate content using the AI provider
	 *
	 * @param string $prompt The prompt to send.
	 * @param array  $options Additional options.
	 * @return array
	 */
	public function generate_content( $prompt, $options = array() ) {
		$endpoint = $this->get_api_endpoint();
		$body     = $this->build_request_body( $prompt, $options );
		$headers  = $this->get_request_headers();

		$response = $this->make_request( $endpoint, $body, $headers );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		return $this->parse_response( $response );
	}

	/**
	 * Validate an API key
	 *
	 * @param string $key The API key to validate.
	 * @return bool
	 */
	public function validate_api_key( $key ) {
		$this->api_key = $key;
		$result        = $this->generate_content( 'Say "test"' );
		return ! isset( $result['success'] ) || false !== $result['success'];
	}

	/**
	 * Make an HTTP request to the API
	 *
	 * @param string $url The API endpoint.
	 * @param array  $body The request body.
	 * @param array  $headers The request headers.
	 * @return array|WP_Error
	 */
	protected function make_request( $url, $body, $headers ) {
		$args = array(
			'method'  => 'POST',
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => 60,
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			$body_text = wp_remote_retrieve_body( $response );
			$error     = json_decode( $body_text, true );
			$message   = isset( $error['error']['message'] ) ? $error['error']['message'] : "API returned status $code";
			return new WP_Error( 'api_error', $message );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Public alias of {@see get_request_headers()} for use by other methods
	 * in this class (e.g. {@see fetch_remote_models()}) and for any future
	 * external code that needs the request headers. Kept as a thin wrapper
	 * so the protected method stays the single source of truth.
	 *
	 * @return array
	 */
	public function build_request_headers() {
		return $this->get_request_headers();
	}

	/**
	 * Get the request headers
	 *
	 * @return array
	 */
	protected function get_request_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}
}
