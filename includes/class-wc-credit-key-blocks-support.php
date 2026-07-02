<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use CreditKey\Api;
use CreditKey\Checkout;
use CreditKey\Main;
use CreditKey\Models\CartItem;

if ( class_exists( 'Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType' ) ) {

    class WC_Credit_Key_Blocks_Support extends AbstractPaymentMethodType {

        /** @var array */
        protected $settings = [];

        public function get_name() {
            return Main::$gateway_id;
        }

        public function initialize() {
            $this->settings = get_option( 'woocommerce_' . Main::$gateway_id . '_settings', [] );
        }

        public function is_active() {
            return isset( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
        }

        public function get_payment_method_script_handles() {
            wp_register_script(
                'wc-credit-key-blocks',
                Main::$plugin_url . 'assets/js/credit-key-blocks.js',
                [ 'wc-settings', 'wc-blocks-registry', 'wp-element', 'wp-i18n' ],
                defined('WP_DEBUG') && WP_DEBUG ? time() : '1.0.0',
                true
            );

            return [ 'wc-credit-key-blocks' ];
        }

        public function get_payment_method_data() {
            $title       = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Credit Key', 'credit_key' );
            $description = isset( $this->settings['description'] ) ? $this->settings['description'] : '';

            $is_eligible = $this->is_checkout_eligible();

            return [
                'title'       => $title,
                'description' => $description,
                'isEligible'  => $is_eligible,
                'icon'        => Main::$plugin_url . 'assets/images/credit-key-payment-method-new-logo.svg',
            ];
        }

        private function is_checkout_eligible() {
            if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
                return false;
            }

            // Minimum order amount check.
            $min_total  = isset( $this->settings['min_checkout'] ) ? (float) $this->settings['min_checkout'] : 0;
            $cart_total = (float) WC()->cart->get_totals()['total'];
            if ( $cart_total <= $min_total ) {
                return false;
            }

            // Build cart items for API eligibility check.
            $cart_items = [];
            foreach ( WC()->cart->get_cart() as $values ) {
                $product_id = $values['data']->get_id();
                $name       = $values['data']->get_title();
                $price      = $values['data']->get_price();
                $quantity   = $values['quantity'];
                $sku        = $values['data']->get_sku();
                $cart_items[] = new CartItem( $product_id, $name, $price, $sku, $quantity, null, null );
            }

            // Configure API keys based on mode.
            $is_test      = isset( $this->settings['is_test'] ) && 'yes' === $this->settings['is_test'];
            $public_key   = $is_test ? ( $this->settings['test_public_key'] ?? '' ) : ( $this->settings['public_key'] ?? '' );
            $shared_key   = $is_test ? ( $this->settings['test_shared_secret'] ?? '' ) : ( $this->settings['shared_secret'] ?? '' );
            $api_url      = $is_test ? 'https://staging.creditkey.com/app' : 'https://www.creditkey.com/app';

            try {
                Api::configure( $api_url, $public_key, $shared_key );
                $customer_id = get_current_user_id() ?: 0;
                return Checkout::isDisplayedInCheckout( $cart_items, $customer_id );
            } catch ( \Exception $e ) {
                return false;
            }
        }
    }
}


