<?php

class WC_Credit_Key extends WC_Payment_Gateway {
    /**
     * Class constructor
     */
    public function __construct() {
        $plugin_dir = plugin_dir_url(__FILE__);

        $this->id = 'credit_key';
        $this->method_title         = esc_html__( 'Credit Key', 'credit_key' );
        $this->method_description   = esc_html__( 'Enable your customer to pay for your products through Credit Key.', 'credit_key' );
        $this->icon                 = apply_filters( 'woocommerce_gateway_icon', $plugin_dir . 'assets/images/credit-key-logo.svg' );
        $this->supports             = array(
            'products', 'refunds'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title            = $this->get_option( 'title' );
        $this->description      = $this->get_option( 'description' );
        $this->enabled          = $this->get_option( 'enabled' );
        $this->testmode         = 'yes' === $this->get_option( 'is_test' );
        $this->order_prefix     = $this->get_option(' order_prefix ');
        $this->public_key       = ( $this->testmode == 'yes') ? $this->get_option( 'test_public_key' ) : $this->get_option( 'public_key' );
        $this->shared_secret    = ( $this->testmode == 'yes') ? $this->get_option( 'test_shared_secret' ) : $this->get_option( 'shared_secret' );
        $this->api_url          = ( $this->testmode == 'yes') ? $this->get_option( 'test_api_url' ) : $this->get_option( 'api_url' );
        $this->logging          = $this->get_option( 'logging' );

        // Save payment gateway settings
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_api_credit_key', array( $this, 'webhook' ) );

        // Is displayed in checkout
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'credit_key_gateway_enable_condition' ));
    }

    /**
     * Plugin options
     */

    public function init_form_fields(){
        $this->form_fields = array(
            'enabled' => array(
                'title'       => esc_html__('Enable/Disable', 'credit_key' ),
                'label'       => esc_html__('Enable Credit Key Payment Gateway', 'credit_key' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),

            'title' => array(
                'title'       => esc_html__('Title', 'credit_key' ),
                'type'        => 'text',
                'description' => esc_html__('This controls the title which the user sees during checkout.', 'credit_key' ),
                'default'     => esc_html__('Pay with Credit key', 'credit_key' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__('Description', 'credit_key' ),
                'type'        => 'textarea',
                'description' => esc_html__('This controls the description which the user sees during checkout.', 'credit_key' ),
                'default'     => esc_html__('Pay the order via Secret Key payment gateway.', 'credit_key' ),
            ),

            'order_prefix' => array(
                'title'       => esc_html__('Orders prefix', 'credit_key' ),
                'type'        => 'text',
                'default'     => 'wс_ck_',
            ),

            'is_test' => array(
                'title'       => esc_html__('Test Mode/Live Mode', 'credit_key'),
                'label'       => esc_html__('Enable Test Mode/Disable Test Mode', 'credit_key' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),

            'public_key' => array(
                'title'       => esc_html__('Public Key', 'credit_key' ),
                'type'        => 'text',
            ),

            'test_public_key' => array(
                'title'       => esc_html__('Test Public Key', 'credit_key' ),
                'type'        => 'text',
            ),


            'shared_secret' => array(
                'title'       => esc_html__('Shared Secret', 'credit_key' ),
                'type'        => 'text',
            ),

            'test_shared_secret' => array(
                'title'       => esc_html__('Test Shared Secret', 'credit_key' ),
                'type'        => 'text',
            ),

            'api_url' => array(
                'title'       => esc_html__('Production URL', 'credit_key' ),
                'type'        => 'text',
            ),

            'test_api_url' => array(
                'title'       => esc_html__('Staging URL', 'credit_key' ),
                'type'        => 'text',
            ),
            'logging' => array(
                'title'       => esc_html__('Enable/Disable', 'credit_key' ),
                'label'       => esc_html__('Enable logging', 'credit_key' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
        );
    }

    private function get_customer_id() {
        if(is_user_logged_in()){
            $customerId = get_current_user_id();
        } else {
            $customerId = 0;
        }
        return $customerId;
    }

    public function lets_log( $e ){
        if( $this->logging == 'yes' ){
            $message    = $e->getMessage();
            $code       = $e->getCode();
            $file       = $e->getFile();
            $line       = $e->getLine();
            //$trace      = $e->getTrace();

            $data       = array();
            if($message){
                $data['message'] = $message;
            }
            if($code){
                $data['code'] = $code;
            }
            if($file){
                $data['file'] = $file;
            }
            if($line){
                $data['line'] = $line;
            }

            wc_get_logger()->debug(print_r($data, true), ['source' => $this->id]);
        }
    }

    public function credit_key_gateway_enable_condition( $available_gateways ){
        if ( ! is_admin() ) {
            try{
                global $woocommerce;

                $cart_items = array();
                $items = $woocommerce->cart->get_cart();
                if(is_array($items) && !empty($items)){
                    foreach($items as $item => $values) {
                        $merchant_id        = $values['data']->get_id();
                        $name               = $values['data']->get_title();
                        $price              = $values['data']->get_price();
                        $quantity           = $values['quantity'];
                        $sku                = $values['data']->get_sku();
                        $cart_items[] = new \CreditKey\Models\CartItem($merchant_id, $name, $price, $sku, $quantity, null, null);
                    }
                }

                $customerId = $this->get_customer_id();

                \CreditKey\Api::configure($this->api_url, $this->public_key, $this->shared_secret);
                $is_displayed = \CreditKey\Checkout::isDisplayedInCheckout($cart_items, $customerId);
                if( !$is_displayed ){
                    unset( $available_gateways['credit_key'] );
                }
            } catch (Exception $e){
                $this->lets_log($e);
                unset( $available_gateways['credit_key'] );
            }
        }
        return $available_gateways;
    }

    //Show Credit Key description on checkout page;
    public function payment_fields() {
        echo '<p>' . $this->description. '</p>';
    }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        // Create billing data
        $billing_first_name    = ($order->get_billing_first_name()) ? $order->get_billing_first_name() : null;
        $billing_last_name     = ($order->get_billing_last_name())  ? $order->get_billing_last_name() : null;
        $billing_company_name  = ($order->get_billing_company())    ? $order->get_billing_company() : null;
        $billing_email         = ($order->get_billing_email())      ? $order->get_billing_email() : null;
        $billing_address1      = ($order->get_billing_address_1())  ? $order->get_billing_address_1() : null;
        $billing_address2      = ($order->get_billing_address_2())  ? $order->get_billing_address_2() : null;
        $billing_city          = ($order->get_billing_city())       ? $order->get_billing_city() : null;
        $billing_state         = ($order->get_billing_state())      ? $order->get_billing_state() : null;
        $billing_zip           = ($order->get_billing_postcode())   ? $order->get_billing_postcode() : null;
        $billing_phone_number  = ($order->get_billing_phone())      ? $order->get_billing_phone() : null;

        $billing_address = new \CreditKey\Models\Address($billing_first_name, $billing_last_name, $billing_company_name, $billing_email,
            $billing_address1, $billing_address2, $billing_city, $billing_state, $billing_zip, $billing_phone_number);

        // Create shipping data
        $shipping_first_name    = ($order->get_shipping_first_name() ) ? $order->get_shipping_first_name() : null;
        $shipping_last_name     = ($order->get_shipping_last_name() )  ? $order->get_shipping_last_name() : null;
        $shipping_company_name  = ( $order->get_shipping_company() )   ? $order->get_shipping_company() : null;
        $shipping_email         = $billing_email;
        $shipping_address1      = ($order->get_shipping_address_1() )  ? $order->get_shipping_address_1() : null;
        $shipping_address2      = ($order->get_shipping_address_2() )  ? $order->get_shipping_address_2() : null;
        $shipping_city          = ($order->get_shipping_city() )       ? $order->get_shipping_city() : null;
        $shipping_state         = ($order->get_shipping_state() )      ? $order->get_shipping_state() : null;
        $shipping_zip           = ($order->get_shipping_postcode())    ? $order->get_shipping_postcode() : null;
        $shipping_phone_number  = $billing_phone_number;

        $shipping_address = new \CreditKey\Models\Address($shipping_first_name, $shipping_last_name, $shipping_company_name, $shipping_email,
            $shipping_address1, $shipping_address2, $shipping_city, $shipping_state, $shipping_zip, $shipping_phone_number);

        // Create Order items data
        $order_items            = array();
        $total   = 0;
        $discount_amount = 0;
        $order_items = array();
        foreach ( $order->get_items() as $key => $item) {
            $product        = $item->get_product();
            $product_id     = $product->get_id();
            $product_name   = $product->get_name();
            $product_price  = $product->get_price();
            $product_sku    = $product->get_sku();
            $product_qty    = intval($item->get_quantity());
            $order_items[]  = new \CreditKey\Models\CartItem($product_id, $product_name, $product_price, $product_sku, $product_qty, null, null);

            $total              += $product->get_regular_price() * $product_qty;
            $discount_amount    += ($product->get_regular_price() - $product_price) * $product_qty;
        }
        $total          = number_format($total, 2);
        $discount_amount= number_format($discount_amount + $order->get_total_discount(), 2);
        $shipping       = number_format($order->get_shipping_total(), 2);
        $tax            = number_format($order->get_total_tax(), 2);
        $grand_total    = number_format($total + $shipping + $tax - $discount_amount, 2);

        // Create charges data
        $charges            = new \CreditKey\Models\Charges($total, $shipping, $tax, $discount_amount, $grand_total);

        $customerId = $this->get_customer_id();

        $remoteId = $this->order_prefix . $order_id;
        $returnUrl = home_url() . '/wc-api/credit_key';
        $cancelUrl = wc_get_checkout_url();

        \CreditKey\Api::configure($this->api_url, $this->public_key, $this->shared_secret);
        $customerCheckoutUrl = \CreditKey\Checkout::beginCheckout($order_items,
            $billing_address, $shipping_address, $charges, $remoteId, $customerId,
            $returnUrl, $cancelUrl, 'redirect');

        return ['result' => 'success', 'redirect' => $customerCheckoutUrl];
    }

    public function webhook(){
        if( isset($_GET['id']) ) {
            $order_id = $_GET['id'];
        }
    }
}