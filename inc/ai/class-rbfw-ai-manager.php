<?php
/**
 * AI Provider Manager
 *
 * @package Booking and Rental Manager for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Manager class for AI providers
 */
class RBFW_AI_Manager {

	/**
	 * Registered providers
	 *
	 * @var array
	 */
	private static $providers = array();

	/**
	 * Register a provider
	 *
	 * @param string $provider_class The provider class name.
	 */
	public static function register_provider( $provider_class ) {
		if ( ! class_exists( $provider_class ) ) {
			return;
		}
		$instance                                         = new $provider_class();
		self::$providers[ $instance->get_provider_id() ] = $instance;
	}

	/**
	 * Get a specific provider
	 *
	 * @param string $provider_id The provider ID.
	 * @return RBFW_AI_Provider|null
	 */
	public static function get_provider( $provider_id ) {
		return self::$providers[ $provider_id ] ?? null;
	}

	/**
	 * Get all registered providers
	 *
	 * @return array
	 */
	public static function get_all_providers() {
		return self::$providers;
	}

	/**
	 * Get the active provider configured in settings
	 *
	 * @return RBFW_AI_Provider|null
	 */
	public static function get_active_provider() {
		$settings    = get_option( 'rbfw_ai_settings', array() );
		$provider_id = $settings['rbfw_ai_provider'] ?? 'openai';

		$provider = self::get_provider( $provider_id );
		if ( ! $provider ) {
			return null;
		}

		// Configure with settings
		$config = array(
			'api_key'     => $settings[ "rbfw_ai_{$provider_id}_key" ] ?? '',
			'model'       => $settings['rbfw_ai_model'] ?? '',
			'max_tokens'  => $settings['rbfw_ai_max_tokens'] ?? 500,
			'temperature' => $settings['rbfw_ai_temperature'] ?? 0.7,
		);

		$provider_class = get_class( $provider );
		return new $provider_class( $config );
	}

	/**
	 * Get a list of registered providers as an id => name map.
	 *
	 * Used by the settings page and admin JS so the available providers
	 * are driven by what the manager has registered, not by a hardcoded
	 * list in the UI.
	 *
	 * @return array
	 */
	public static function get_provider_options() {
		$out = array();
		foreach ( self::$providers as $id => $instance ) {
			$out[ $id ] = $instance->get_provider_name();
		}
		return $out;
	}

	/**
	 * Get the registered provider ids (canonical order).
	 *
	 * @return string[]
	 */
	public static function get_provider_ids() {
		return array_keys( self::$providers );
	}

	/**
	 * Initialize all providers
	 */
	public static function init() {
		self::register_provider( 'RBFW_AI_OpenAI' );
		self::register_provider( 'RBFW_AI_Anthropic' );
		self::register_provider( 'RBFW_AI_Groq' );
		self::register_provider( 'RBFW_AI_XAI' );
		self::register_provider( 'RBFW_AI_CommandCode' );
	}
}

add_action( 'init', array( 'RBFW_AI_Manager', 'init' ) );
