<?php
namespace CreditKey;

/*
 * Plugin Name: Credit Key Payment Gateway
 * Description: Enable your customer to pay for your products through Credit Key.
 * Author: Credit Key
 * Author URI: https://www.creditkey.com
 * Version: 1.0.0
 */

class Main
{
    private static $instance;
    public static $plugin_url;
	public static $gateway_id;
    public static $plugin_path;

    private function __construct()
    {
	    self::$gateway_id = 'credit_key';
        self::$plugin_url = plugin_dir_url(__FILE__);
        self::$plugin_path = plugin_dir_path(__FILE__);
        add_action('plugins_loaded', [$this, 'pluginsLoaded']);
        add_filter('woocommerce_payment_gateways', [$this, 'woocommercePaymentGateways']);
        add_action('init', array($this, 'check_woocommerce_country'));
	    add_action( 'admin_notices', array($this, 'error_notice') );
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public function check_woocommerce_country(){
	    $woo_countries = new \WC_Countries();
	    $state = $woo_countries->get_base_state();
	    $country = $woo_countries->get_base_country();
	    if( $state != 'CA' || $country != 'US' ){
		    deactivate_plugins(plugin_basename(__FILE__));
        }
    }

    public function error_notice(){
	    $woo_countries = new \WC_Countries();
	    $state = $woo_countries->get_base_state();
	    $country = $woo_countries->get_base_country();
	    if( $state != 'CA' || $country != 'US' ){ ?>
        <div class="error notice">
            <p><?php _e( 'Woocommerce default country/state should be "United States - California" for activation Credit Key plugin', 'credit_key' ); ?></p>
        </div>
<?php
        }
    }

    public function pluginsLoaded()
    {
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
    }

    public function woocommercePaymentGateways($gateways)
    {
        $gateways[] = 'WC_Credit_Key';
        return $gateways;
    }

    public static function getInstance(){
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }
}
Main::getInstance();