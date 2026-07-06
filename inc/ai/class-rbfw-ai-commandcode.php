<?php
/**
 * CommandCode AI Provider
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * CommandCode provider implementation (placeholder for future API)
 */
class RBFW_AI_CommandCode extends RBFW_AI_Provider {

	/**
	 * Get the provider ID
	 *
	 * @return string
	 */
	public function get_provider_id() {
		return 'commandcode';
	}

	/**
	 * Get the provider display name
	 *
	 * @return string
	 */
	public function get_provider_name() {
		return 'Command Code (Provider API)';
	}

	/**
	 * Get the API endpoint URL
	 *
	 * @return string
	 */
	public function get_api_endpoint() {
		return 'https://api.commandcode.ai/provider/v1/chat/completions';
	}

	/**
	 * Get the models-listing endpoint URL (OpenAI-compatible).
	 *
	 * @return string
	 */
	public function get_models_endpoint() {
		return 'https://api.commandcode.ai/provider/v1/models';
	}

	/**
	 * Get available models for this provider.
	 *
	 * Static fallback used only when the remote /models endpoint cannot be
	 * reached. The full, current list is fetched at runtime via
	 * {@see RBFW_AI_Provider::fetch_remote_models()}.
	 *
	 * @return array
	 */
	public function get_available_models() {
		return array(
			'claude-sonnet-5'            => 'Claude Sonnet 5 (Command Code)',
			'claude-sonnet-4-6'          => 'Claude Sonnet 4.6 (Command Code)',
			'claude-opus-4-8'            => 'Claude Opus 4.8 (Command Code)',
			'claude-opus-4-7'            => 'Claude Opus 4.7 (Command Code)',
			'claude-haiku-4-5-20251001'  => 'Claude Haiku 4.5 (Command Code)',
			'gpt-5.5'                    => 'GPT-5.5 (Command Code)',
			'gpt-5.4'                    => 'GPT-5.4 (Command Code)',
			'gpt-5.4-mini'               => 'GPT-5.4 Mini (Command Code)',
			'gpt-5.3-codex'              => 'GPT-5.3 Codex (Command Code)',
			'deepseek/deepseek-v4-pro'   => 'DeepSeek V4 Pro (Command Code)',
			'deepseek/deepseek-v4-flash' => 'DeepSeek V4 Flash (Command Code)',
			'MiniMaxAI/MiniMax-M3'  => 'MiniMax M3 (Command Code)',
			'MiniMaxAI/MiniMax-M2.7' => 'MiniMax M2.7 (Command Code)',
			'MiniMaxAI/MiniMax-M2.5' => 'MiniMax M2.5 (Command Code)',
			'moonshotai/Kimi-K2.6'       => 'Kimi K2.6 (Command Code)',
			'moonshotai/Kimi-K2.5'       => 'Kimi K2.5 (Command Code)',
			'Qwen/Qwen3.6-Max-Preview'   => 'Qwen 3.6 Max Preview (Command Code)',
			'Qwen/Qwen3.6-Plus'          => 'Qwen 3.6 Plus (Command Code)',
			'Qwen/Qwen3.7-Max'           => 'Qwen 3.7 Max (Command Code)',
			'Qwen/Qwen3.7-Plus'          => 'Qwen 3.7 Plus (Command Code)',
			'zai-org/GLM-5.2'            => 'GLM 5.2 (Command Code)',
			'zai-org/GLM-5.1'            => 'GLM 5.1 (Command Code)',
			'xiaomi/mimo-v2.5-pro'       => 'MiMo V2.5 Pro (Command Code)',
			'google/gemini-3.5-flash'    => 'Gemini 3.5 Flash (Command Code)',
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
