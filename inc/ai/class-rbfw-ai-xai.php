<?php
/**
 * xAI (Grok) AI Provider
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * xAI provider implementation
 */
class RBFW_AI_XAI extends RBFW_AI_Provider {

	/**
	 * Get the provider ID
	 *
	 * @return string
	 */
	public function get_provider_id() {
		return 'xai';
	}

	/**
	 * Get the provider display name
	 *
	 * @return string
	 */
	public function get_provider_name() {
		return 'xAI Grok ($25 Free Credits)';
	}

	/**
	 * Get the API endpoint URL
	 *
	 * @return string
	 */
	public function get_api_endpoint() {
		return 'https://api.x.ai/v1/chat/completions';
	}

	/**
	 * Get available models for this provider
	 *
	 * @return array
	 */
	public function get_available_models() {
		return array(
			'grok-2-latest' => 'Grok 2',
			'grok-3-latest' => 'Grok 3 (Recommended)',
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
				'Authorization' => 'Bearer ' . $this->api_key,
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
			'model'       => $this->model,
			'messages'    => array(
				array(
					'role'    => 'system',
					'content' => 'You are a helpful assistant that generates SEO-optimized content for rental items.',
				),
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			'max_tokens'  => $this->max_tokens,
			'temperature' => $this->temperature,
		);
	}

	/**
	 * Parse the API response
	 *
	 * @param array $response The raw API response.
	 * @return array
	 */
	protected function parse_response( $response ) {
		if ( ! isset( $response['choices'][0]['message']['content'] ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid response format',
			);
		}

		return array(
			'success' => true,
			'content' => $response['choices'][0]['message']['content'],
			'usage'   => array(
				'prompt_tokens'     => $response['usage']['prompt_tokens'] ?? 0,
				'completion_tokens' => $response['usage']['completion_tokens'] ?? 0,
			),
		);
	}
}
