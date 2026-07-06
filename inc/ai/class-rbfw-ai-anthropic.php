<?php
/**
 * Anthropic (Claude) AI Provider
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Anthropic provider implementation
 */
class RBFW_AI_Anthropic extends RBFW_AI_Provider {

	/**
	 * Get the provider ID
	 *
	 * @return string
	 */
	public function get_provider_id() {
		return 'anthropic';
	}

	/**
	 * Get the provider display name
	 *
	 * @return string
	 */
	public function get_provider_name() {
		return 'Anthropic (Claude)';
	}

	/**
	 * Get the API endpoint URL
	 *
	 * @return string
	 */
	public function get_api_endpoint() {
		return 'https://api.anthropic.com/v1/messages';
	}

	/**
	 * Get available models for this provider
	 *
	 * @return array
	 */
	public function get_available_models() {
		return array(
			'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (Recommended)',
			'claude-3-haiku-20240307'    => 'Claude 3 Haiku (Fast)',
		);
	}

	/**
	 * Get the request headers
	 *
	 * @return array
	 */
	protected function get_request_headers() {
		return array_merge(
			parent::get_request_headers(),
			array(
				'x-api-key'         => $this->api_key,
				'anthropic-version' => '2023-06-01',
			)
		);
	}

	/**
	 * Build the request body for the API
	 *
	 * @param string $prompt The prompt to send.
	 * @param array  $options Additional options.
	 * @return array
	 */
	protected function build_request_body( $prompt, $options ) {
		return array(
			'model'      => $this->model,
			'messages'   => array(
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			'system'     => 'You are a helpful assistant that generates SEO-optimized content for rental items.',
			'max_tokens' => $this->max_tokens,
		);
	}

	/**
	 * Parse the API response
	 *
	 * @param array $response The raw API response.
	 * @return array
	 */
	protected function parse_response( $response ) {
		if ( ! isset( $response['content'][0]['text'] ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid response format',
			);
		}

		return array(
			'success' => true,
			'content' => $response['content'][0]['text'],
			'usage'   => array(
				'input_tokens'  => $response['usage']['input_tokens'] ?? 0,
				'output_tokens' => $response['usage']['output_tokens'] ?? 0,
			),
		);
	}
}
