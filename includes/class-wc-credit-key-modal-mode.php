<?php

namespace CreditKey;

class CreditKeyModalMode {
	private static ?CreditKeyModalMode $instance = null;
	public string $gateway_id;
	public $gateway_settings;

	private function __construct() {
		$this->gateway_id       = Main::$gateway_id;
		$this->gateway_settings = get_option( 'woocommerce_' . Main::$gateway_id . '_settings' );

		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
	}

	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function payment_scripts() {
		$gateway_settings = $this->gateway_settings;

		if ( empty( $gateway_settings ) ) {
			return;
		}

		$mode       = ( isset( $gateway_settings['mode'] ) ) ? $gateway_settings['mode'] : 'redirect';
		$is_enabled = isset( $gateway_settings['enabled'] ) ? $gateway_settings['enabled'] : 'no';
		if ( 'no' === $is_enabled || $mode !== 'modal' || ! is_checkout() ) {
			return;
		}

		wp_enqueue_script( 'credit-key-js', Main::$plugin_url . 'assets/js/creditkey-js.js', null, '1.0.96' );

		wp_enqueue_script( 'credit-key-modal-mode', Main::$plugin_url . 'assets/js/modal-mode.js', array(
			'jquery',
			'credit-key-js'
		), ( defined('WP_DEBUG') && WP_DEBUG ) ? time() : '2.2' );

		wp_localize_script( 'credit-key-modal-mode', 'creditKeyModalModeData', array(
			'gateway_id' => $this->gateway_id,
			'urls'     => array(),
		) );
	}
}

CreditKeyModalMode::getInstance();