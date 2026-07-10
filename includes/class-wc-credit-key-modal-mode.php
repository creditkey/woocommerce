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

		$show_on_product_page = ( isset( $gateway_settings['mode'] ) ) ? $gateway_settings['mode'] : 'redirect';
		$is_enabled           = $gateway_settings['enabled'];
		if ( 'no' === $is_enabled || $show_on_product_page !== 'modal' || ! is_checkout() ) {
			return;
		}

		wp_enqueue_script( 'credit-key-js', 'https://unpkg.com/@credit-key/creditkey-js@1.0.96/umd/creditkey-js.js', null, '1.0.96' );

		wp_enqueue_script( 'credit-key-modal-mode', Main::$plugin_url . 'assets/js/modal-mode.js', array(
			'jquery',
			'credit-key-js'
		), time() );

		wp_localize_script( 'credit-key-modal-mode', 'creditKeyModalModeData', array(
			'gateway_id' => $this->gateway_id,
			'urls'     => array(),
		) );
	}
}

CreditKeyModalMode::getInstance();