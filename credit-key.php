<?php

namespace CreditKey;

/*
 * Plugin Name: Credit Key Payment Gateway
 * Description: Flexible B2B Financing for Your Customers
 * Author: Credit Key
 * Author URI: https://www.creditkey.com
 * Version: 2.2
 * Text Domain: credit_key
 * WC tested up to: 10.5.3
 * WC requires at least: 8.4
 */

class Main
{
    private static $instance;
    public static $plugin_url;
    public static $gateway_id;
    public static $plugin_path;
    private $classesLoaded = false;

    private function __construct()
    {
        self::$gateway_id = 'credit_key';
        self::$plugin_url = plugin_dir_url(__FILE__);
        self::$plugin_path = plugin_dir_path(__FILE__);
        add_action('plugins_loaded', [$this, 'pluginsLoaded']);
        add_filter('woocommerce_payment_gateways', [$this, 'woocommercePaymentGateways']);
        add_action('admin_notices', array($this, 'error_notice'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'settingsLink']);
        add_action('before_woocommerce_init', [$this, 'declareHposCompatibility']);
        add_action('woocommerce_blocks_payment_method_type_registration', [$this, 'registerBlocksIntegration']);
    }
    public function error_notice()
    {
        $woo_countries = new \WC_Countries();
        $country = $woo_countries->get_base_country();
        if ( $country != 'US' ) { ?>
            <div class="error notice">
                <p><?php _e('Credit Key requires "United States" to be the default country.', 'credit_key'); ?></p>
            </div>
            <?php
        }
    }

    public function pluginsLoaded()
    {
        $this->loadGatewayClasses();
    }

    private function loadGatewayClasses()
    {
        if ( $this->classesLoaded ) {
            return;
        }

	    $woo_countries = new \WC_Countries();
	    $country = $woo_countries->get_base_country();
	    if ( $country == 'US' ) {
            require_once 'sdk/Models/Address.php';
            require_once 'sdk/Models/CartItem.php';
            require_once 'sdk/Models/Charges.php';
            require_once 'sdk/Models/Order.php';

            /* Exceptions */
            require_once 'sdk/Exceptions/ApiNotConfiguredException.php';
            require_once 'sdk/Exceptions/ApiUnauthorizedException.php';
            require_once 'sdk/Exceptions/InvalidRequestException.php';
            require_once 'sdk/Exceptions/NotFoundException.php';
            require_once 'sdk/Exceptions/OperationErrorException.php';

            /* Business Logic */
            require_once 'sdk/Api.php';
            require_once 'sdk/Authentication.php';
            require_once 'sdk/CartContents.php';
            require_once 'sdk/Checkout.php';
            require_once 'sdk/Orders.php';
            require_once 'includes/class-wc-credit-key-gateway.php';
            require_once 'includes/class-wc-credit-key-js-gateway.php';
            require_once 'includes/class-wc-credit-key-modal-mode.php';
            require_once 'includes/class-wc-credit-key-promotion.php';
            // Blocks integration is registered in constructor via registerBlocksIntegration().

            $this->classesLoaded = true;
	    }
    }

    public function woocommercePaymentGateways($gateways)
    {
        // WooCommerce can build (and cache) its payment-gateway list before our
        // classes are required on `plugins_loaded` (order depends on which other
        // plugins are active), which silently drops the gateway via WC's
        // class_exists() guard. Load the classes here — at the moment WooCommerce
        // reads the list — so the gateway is always registered.
        $this->loadGatewayClasses();

        if ( class_exists( 'WC_Credit_Key' ) ) {
            $gateways[] = 'WC_Credit_Key';
        }

        return $gateways;
    }

    public function settingsLink($links)
    {
        $url = admin_url('admin.php?page=wc-settings&tab=checkout&section=' . self::$gateway_id);
        $settings_link = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'credit_key') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function registerBlocksIntegration( $payment_method_registry )
    {
        $woo_countries = new \WC_Countries();
        if ( $woo_countries->get_base_country() !== 'US' ) {
            return;
        }

        // Ensure class file is loaded before registration.
        if ( ! class_exists( '\\WC_Credit_Key_Blocks_Support' ) ) {
            require_once __DIR__ . '/includes/class-wc-credit-key-blocks-support.php';
        }

        if ( class_exists( '\\WC_Credit_Key_Blocks_Support' ) ) {
            $payment_method_registry->register( new \WC_Credit_Key_Blocks_Support() );
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function declareHposCompatibility()
    {
        if (class_exists('\Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        }
    }
}

Main::getInstance();
