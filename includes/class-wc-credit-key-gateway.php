<?php

use CreditKey\Api;
use CreditKey\Checkout;
use CreditKey\Main;
use CreditKey\Models\Address;
use CreditKey\Models\CartItem;
use CreditKey\Models\Charges;
use CreditKey\Orders;


class WC_Credit_Key extends WC_Payment_Gateway
{
    /** @var string */
    public $id;
    /** @var string */
    public $method_title;
    /** @var string */
    public $method_description;
    /** @var array */
    public $supports = [];
    /** @var string */
    public $order_button_text;
    /** @var array */
    public $form_fields = [];
    /** @var string */
    public $title;
    /** @var string */
    public $description;
    /** @var string */
    public $enabled;
    /** @var bool */
    public $testmode = false;
    /** @var string */
    public $order_prefix = '';
    /** @var string */
    public $order_suffix = '';
    /** @var string */
    public $public_key = '';
    /** @var string */
    public $shared_secret = '';
    /** @var string */
    public $api_url = '';
    /** @var string */
    public $logging = 'no';
    /** @var float|int */
    public $min_checkout = 0;
    /** @var float|int */
    public $min_product = 0;
    /** @var float|int */
    public $min_cart = 0;
    /** @var bool Recursion guard for order update hook */
    private static $isOrderUpdateInProgress = false;
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->id                 = Main::$gateway_id;
        $this->method_title       = esc_html__('Credit Key', 'credit_key');
        $this->method_description = esc_html__('Flexible B2B Financing for Your Customers', 'credit_key');
        $this->supports           = ['products', 'refunds'];
        $this->order_button_text  = __('Continue with Credit Key', 'credit_key');

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title         = '';
        $this->description   = $this->get_option('description');
        $this->enabled       = $this->get_option('enabled');
        $this->testmode      = ('yes' === $this->get_option('is_test'));
        $this->order_prefix  = $this->get_option('order_prefix');
        $this->order_suffix  = $this->get_option('order_suffix');
        $this->public_key    = $this->testmode ? $this->get_option('test_public_key') : $this->get_option('public_key');
        $this->shared_secret = $this->testmode ? $this->get_option('test_shared_secret') : $this->get_option('shared_secret');
        $this->api_url       = $this->testmode ? 'https://staging.creditkey.com/app' : 'https://www.creditkey.com/app';
        $this->logging       = $this->get_option('logging');
        $this->min_checkout  = ($this->get_option('min_checkout')) ? $this->get_option('min_checkout') : 0;
        $this->min_product   = ($this->get_option('min_product')) ? $this->get_option('min_product') : 0;
        $this->min_cart      = ($this->get_option('min_cart')) ? $this->get_option('min_cart') : 0;

        // Save payment gateway settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [
            $this,
            'process_admin_options'
        ]);
        add_action('woocommerce_api_credit_key', [$this, 'webhook']);

        // Is displayed in checkout
        add_filter('woocommerce_available_payment_gateways', [$this, 'credit_key_gateway_enable_condition']);

        add_action('woocommerce_order_status_completed', [$this, 'call_credit_key_order_confirm'], 10, 1);
        add_action('woocommerce_order_status_cancelled', [$this, 'call_credit_key_order_cancel'], 10, 1);
        add_action('woocommerce_order_status_refunded', [$this, 'call_credit_key_order_refund'], 10, 1);
        add_action('woocommerce_update_order', [$this, 'call_credit_key_order_update'], 10, 1);

        add_filter('wc_order_statuses', [$this, 'control_order_statuses'], 10, 1);
        add_action('admin_enqueue_scripts', [$this, 'dashboard_payment_scripts']);

        add_filter('woocommerce_gateway_icon', [$this, 'change_gateway_icon'], 10, 2);

        add_action('wp_enqueue_scripts', [$this, 'payment_scripts']);

        add_action('woocommerce_order_status_changed', [$this, 'prevent_unauthorized_status_change'], 10, 4);
    }

    /**
     * Plugin options
     */

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled'      => [
                'title'       => esc_html__('Enable Credit Key Payment Gateway', 'credit_key'),
                'label'       => esc_html__('Enable/Disable', 'credit_key'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ],
            'description'  => [
                'title'       => esc_html__('Payment Method Description', 'credit_key'),
                'type'        => 'textarea',
                'description' => esc_html__('Description under payment method in checkout.', 'credit_key'),
                'default'     => esc_html__('Pay the order via Secret Key payment gateway.', 'credit_key'),
            ],
            'min_checkout' => [
                'title'       => esc_html__('Minimum Order Amount', 'credit_key'),
                'label'       => esc_html__('Show/Hide', 'credit_key'),
                'type'        => 'number',
                'description' => 'The minimum order amount to offer Credit Key as a payment method in checkout.',
                'default'     => 0
            ],
            'order_prefix' => [
                'title'       => esc_html__('Order Prefix', 'credit_key'),
                'type'        => 'text',
                'description' => esc_html__('Prefix added to merchant order IDs sent to Credit Key.', 'credit_key'),
                'default'     => '',
            ],
            'order_suffix' => [
                'title'       => esc_html__('Order Suffix', 'credit_key'),
                'type'        => 'text',
                'description' => esc_html__('Suffix added to merchant order IDs sent to Credit Key.', 'credit_key'),
                'default'     => '',
            ],
            'is_test'      => [
                'title'       => esc_html__('API Mode', 'credit_key'),
                'label'       => esc_html__('Staging', 'credit_key'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ],
            'public_key'   => [
                'title' => esc_html__('Public Key', 'credit_key'),
                'type'  => 'text',
            ],

            'test_public_key' => [
                'title' => esc_html__('Public Key', 'credit_key'),
                'type'  => 'text',
            ],

            'shared_secret' => [
                'title' => esc_html__('Shared Secret', 'credit_key'),
                'type'  => 'text',
            ],

            'test_shared_secret'             => [
                'title' => esc_html__('Shared Secret', 'credit_key'),
                'type'  => 'text',
            ],
            'product_page'                   => [
                'title'       => esc_html__('Show Credit Key on product pages', 'credit_key'),
                'label'       => esc_html__('Show the Credit Key under price label in product pages', 'credit_key'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ],
            'promo_message_product_selector' => [
                'title'       => esc_html__('Custom selector for promo message on product page', 'credit_key'),
                'type'        => 'text',
                'description' => '',
            ],
            'min_product'                    => [
                'title'       => esc_html__('Minimum Product Price', 'credit_key'),
                'label'       => esc_html__('Show/Hide', 'credit_key'),
                'type'        => 'number',
                'description' => 'The minimum price for a product where the promotion should be displayed (in dollars).',
                'default'     => 0
            ],
            'cart_page'                      => [
                'title'       => esc_html__('Show Credit Key on Cart Page', 'credit_key'),
                'label'       => esc_html__('Show the Credit Key under cart total on cart page', 'credit_key'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ],
            'promo_message_cart_selector'    => [
                'title'       => esc_html__('Custom selector for promo message on cart page', 'credit_key'),
                'type'        => 'text',
                'description' => '',
            ],
            'min_cart'                       => [
                'title'       => esc_html__('Minimum Cart Total', 'credit_key'),
                'label'       => esc_html__('Show/Hide', 'credit_key'),
                'type'        => 'number',
                'description' => 'The minimum cart total where the promotion should be displayed (in dollars).',
                'default'     => 0
            ],
            'cart_alignment_desktop'         => [
                'title'       => 'Cart Page Alignment for Desktop',
                'description' => 'Cart page text can be aligned left, centered, or right for desktop devices',
                'type'        => 'select',
                'options'     => [
                    'right'    => 'Right',
                    'centered' => 'Centered',
                    'left'     => 'Left'
                ]
            ],
            'cart_alignment_mobile'          => [
                'title'       => 'Cart Page Alignment for Mobile',
                'description' => 'Cart page text can be aligned left, centered, or right for mobile devices',
                'type'        => 'select',
                'options'     => [
                    'right'    => 'Right',
                    'centered' => 'Centered',
                    'left'     => 'Left'
                ]
            ],
            'logging'                        => [
                'title'       => esc_html__('Enable logging', 'credit_key'),
                'label'       => esc_html__('Enable/Disable', 'credit_key'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ],
        ];
    }

    public function dashboard_payment_scripts()
    {
        if ('no' === $this->enabled) {
            return;
        }
        wp_register_script('creditkey-dashboard-scripts', Main::$plugin_url . 'assets/js/scripts-dashboard.js', ['jquery'], time());
        wp_enqueue_script('creditkey-dashboard-scripts');
        wp_enqueue_editor();
    }

    private function get_customer_id()
    {
        if (is_user_logged_in()) {
            $customerId = get_current_user_id();
        } else {
            $customerId = 0;
        }

        return $customerId;
    }

    public function payment_scripts()
    {
        wp_enqueue_style('credit-key-styles', Main::$plugin_url . 'assets/css/styles.css');
    }

    public function lets_log($e)
    {
        if ($this->logging == 'yes') {
            $message = $e->getMessage();
            $code    = $e->getCode();
            $file    = $e->getFile();
            $line    = $e->getLine();

            $data = [];
            if ($message) {
                $data['message'] = $message;
            }
            if ($code) {
                $data['code'] = $code;
            }
            if ($file) {
                $data['file'] = $file;
            }
            if ($line) {
                $data['line'] = $line;
            }

            wc_get_logger()->debug(print_r($data, true), ['source' => $this->id]);
        }
    }

    public function credit_key_gateway_enable_condition($available_gateways)
    {
        if (!is_admin() && is_checkout()) {
            try {
                $cart_items = [];
                $cart_total = 0.0;

                // Support order-pay endpoint where the cart is empty
                $is_order_pay = ( function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay') )
                    || ( function_exists('is_checkout_pay_page') && is_checkout_pay_page() );

                if ($is_order_pay) {
                    global $wp;
                    $order_id = isset($wp->query_vars['order-pay']) ? absint($wp->query_vars['order-pay']) : 0;
                    if ($order_id) {
                        $order = wc_get_order($order_id);
                        if ($order) {
                            $cart_total = (float) $order->get_total();
                            foreach ($order->get_items() as $item) {
                                $product_id = $item->get_product_id();
                                $name       = $item->get_name();
                                $quantity   = (int) $item->get_quantity();
                                $price      = $quantity > 0 ? (float) $item->get_subtotal() / $quantity : 0.0;
                                $sku        = '';
                                $product    = $item->get_product();
                                if ($product) {
                                    $sku = $product->get_sku();
                                }
                                $cart_items[] = new CartItem($product_id, $name, $price, $sku, $quantity, null, null);
                            }
                        }
                    }
                } else {
                    global $woocommerce;
                    $items = $woocommerce->cart ? $woocommerce->cart->get_cart() : [];
                    if (is_array($items) && !empty($items)) {
                        foreach ($items as $item => $values) {
                            $merchant_id  = $values['data']->get_id();
                            $name         = $values['data']->get_title();
                            $price        = $values['data']->get_price();
                            $quantity     = $values['quantity'];
                            $sku          = $values['data']->get_sku();
                            $cart_items[] = new CartItem($merchant_id, $name, $price, $sku, $quantity, null, null);
                        }
                    }
                    $cart_totals = $woocommerce->cart ? $woocommerce->cart->get_totals() : ['total' => 0];
                    $cart_total  = (float) $cart_totals['total'];
                }

                $min_total   = (float) $this->min_checkout;
                $customerId  = $this->get_customer_id();

                Api::configure($this->api_url, $this->public_key, $this->shared_secret);
                $is_displayed = Checkout::isDisplayedInCheckout($cart_items, $customerId);

                if (!$is_displayed || $cart_total <= $min_total) {
                    unset($available_gateways['credit_key']);
                }
            } catch (Exception $e) {
                $this->lets_log($e);
                unset($available_gateways['credit_key']);
            }
        }

        return $available_gateways;
    }

    //Show Credit Key description on checkout page;
    public function payment_fields()
    {
        echo '<p>' . $this->description . '</p>';
    }

    private function get_order_data($order_id)
    {

        $order = wc_get_order($order_id);

        // Create billing data
        $billing_first_name   = ($order->get_billing_first_name()) ? $order->get_billing_first_name() : null;
        $billing_last_name    = ($order->get_billing_last_name()) ? $order->get_billing_last_name() : null;
        $billing_company_name = ($order->get_billing_company()) ? $order->get_billing_company() : null;
        $billing_email        = ($order->get_billing_email()) ? $order->get_billing_email() : null;
        $billing_address1     = ($order->get_billing_address_1()) ? $order->get_billing_address_1() : null;
        $billing_address2     = ($order->get_billing_address_2()) ? $order->get_billing_address_2() : null;
        $billing_city         = ($order->get_billing_city()) ? $order->get_billing_city() : null;
        $billing_state        = ($order->get_billing_state()) ? $order->get_billing_state() : null;
        $billing_zip          = ($order->get_billing_postcode()) ? $order->get_billing_postcode() : null;
        $billing_phone_number = ($order->get_billing_phone()) ? $order->get_billing_phone() : null;

        $billing_address = new Address($billing_first_name, $billing_last_name, $billing_company_name, $billing_email,
            $billing_address1, $billing_address2, $billing_city, $billing_state, $billing_zip, $billing_phone_number);

        // Create shipping data
        $shipping_first_name   = ($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : null;
        $shipping_last_name    = ($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : null;
        $shipping_company_name = ($order->get_shipping_company()) ? $order->get_shipping_company() : null;
        $shipping_email        = $billing_email;
        $shipping_address1     = ($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : null;
        $shipping_address2     = ($order->get_shipping_address_2()) ? $order->get_shipping_address_2() : null;
        $shipping_city         = ($order->get_shipping_city()) ? $order->get_shipping_city() : null;
        $shipping_state        = ($order->get_shipping_state()) ? $order->get_shipping_state() : null;
        $shipping_zip          = ($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : null;
        $shipping_phone_number = $billing_phone_number;

        $shipping_address = new Address($shipping_first_name, $shipping_last_name, $shipping_company_name, $shipping_email,
            $shipping_address1, $shipping_address2, $shipping_city, $shipping_state, $shipping_zip, $shipping_phone_number);

        // Create Order items data
        $total           = 0;
        $discount_amount = 0;
        $order_items     = [];
        foreach ($order->get_items() as $key => $item) {
            $product       = $item->get_product();
            $product_id    = $product->get_id();
            $product_name  = $product->get_name();
            $product_price = $product->get_price();
            $product_sku   = $product->get_sku();
            $product_qty   = intval($item->get_quantity());
            $order_items[] = new CartItem($product_id, $product_name, $product_price, $product_sku, $product_qty, null, null);

            $total           += $product->get_regular_price() * $product_qty;
            $discount_amount += ($product->get_regular_price() - $product_price) * $product_qty;
        }
        $total           = number_format($total, 2, '.', '');
        $discount_amount = number_format($discount_amount + $order->get_total_discount(), 2, '.', '');
        $shipping        = number_format($order->get_shipping_total(), 2, '.', '');
        $tax             = number_format($order->get_total_tax(), 2, '.', '');
        $grand_total     = number_format($order->get_total(), 2, '.', '');

        // Create charges data
        $charges = new Charges($total, $shipping, $tax, $discount_amount, $grand_total);

        return [
            'order_items'      => $order_items,
            'billing_address'  => $billing_address,
            'shipping_address' => $shipping_address,
            'charges'          => $charges,
        ];
    }

    public function process_payment($order_id)
    {
        $order_data       = $this->get_order_data($order_id);
        $order_items      = $order_data['order_items'];
        $billing_address  = $order_data['billing_address'];
        $shipping_address = $order_data['shipping_address'];
        $charges          = $order_data['charges'];
        $customerId       = $this->get_customer_id();

        $remoteId = $this->get_credit_key_merchant_order_id($order_id);

        $returnUrl = home_url() . '/wc-api/credit_key?order_id=' . urlencode($order_id) . '&id=%CKKEY%';

        $cancelUrl = wc_get_checkout_url();

        // Server-to-server callback Credit Key uses to complete administratively
        // approved pended orders, independent of the borrower's browser session.
        // Points at the same webhook endpoint, which completes orders idempotently.
        $orderCompleteUrl = $returnUrl;

        Api::configure($this->api_url, $this->public_key, $this->shared_secret);
        $customerCheckoutUrl = Checkout::beginCheckout(
            $order_items,
            $billing_address,
            $shipping_address,
            $charges,
            $remoteId,
            $customerId,
            $returnUrl,
            $cancelUrl,
            $orderCompleteUrl,
            'redirect'
        );

        return ['result' => 'success', 'redirect' => $customerCheckoutUrl];
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = wc_get_order($order_id);
        $ck_order_id = $order ? $order->get_meta('ck_order_id', true) : '';
        if (isset($ck_order_id) && $amount > 0) {

            $is_confirmed = $order ? $order->get_meta('ck_is_confirmed', true) : '';

            if ($is_confirmed) {
                Api::configure($this->api_url, $this->public_key, $this->shared_secret);
                $refund_order = Orders::refund($ck_order_id, $amount);
                if ($order) {
                    $order->update_meta_data('ck_is_refunded', true);
                    $order->save();
                }

                return true;
            } else {
                return false;
            }

        }
    }

    public function webhook() {
        if (isset($_GET['id'], $_GET['order_id'])) {

            $ck_order_id = sanitize_text_field(wp_unslash($_GET['id']));
            $order_id = absint($_GET['order_id']);
            $order = wc_get_order($order_id);

            if (!$order) {
                wp_redirect(wc_get_checkout_url());
                exit;
            }

            if (!$order || $order->get_payment_method() !== $this->id) {
                wp_safe_redirect(wc_get_checkout_url());
                exit;
            }

            Api::configure($this->api_url, $this->public_key, $this->shared_secret);
            try {
                $remote_order = Orders::find($ck_order_id);
            } catch (Exception $e) {
                $this->lets_log($e);
                wp_safe_redirect(wc_get_checkout_url());
                exit;
            }

            if ($remote_order->getMerchantOrderId() !== $this->get_credit_key_merchant_order_id($order->get_id())) {
                wp_safe_redirect(wc_get_checkout_url());
                exit;
            }

            $order->update_meta_data('ck_order_id', $ck_order_id);
            $order->save();
            $complete_checkout = Checkout::completeCheckout($ck_order_id);

            if ($complete_checkout) {

                $order->add_order_note(esc_html__('Order paid via Credit Key.', 'credit_key'), 1);
                $order->payment_complete($ck_order_id);
                WC()->cart->empty_cart();

                $thank_you_url = $order->get_checkout_order_received_url();
                wp_safe_redirect($thank_you_url);
                exit;

            } else {
                wp_redirect(wc_get_checkout_url());
                exit;
            }
        }

        wp_die();
    }

    public function call_credit_key_order_confirm($order_id)
    {
        try {
            $order          = wc_get_order($order_id);
            $payment_method = $order->get_payment_method();
            if ($payment_method == $this->id) {
                $is_confirmed = $order->get_meta('ck_is_confirmed', true);
                $ck_order_id  = $order->get_meta('ck_order_id', true);
                $is_cancelled = $order->get_meta('ck_is_cancelled', true)
                    || $order->has_status('cancelled')
                    || !is_null($order->get_date_cancelled());

                if (!$is_confirmed && !$is_cancelled) {

                    $order_status = $order->get_status();

                    Api::configure($this->api_url, $this->public_key, $this->shared_secret);

                    $order_data  = $this->get_order_data($order_id);
                    $order_items = $order_data['order_items'];
                    $charges     = $order_data['charges'];

                    if ($this->logging === 'yes') {
                        wc_get_logger()->debug(print_r([
                            'action'      => 'credit_key_confirm_before',
                            'order_id'    => $order_id,
                            'ck_order_id' => $ck_order_id,
                            'status'      => $order_status,
                            'merchant_no' => $this->get_credit_key_merchant_order_id($order_id),
                        ], true), ['source' => $this->id]);
                    }

                    $result = Orders::confirm($ck_order_id, $this->get_credit_key_merchant_order_id($order_id), $order_status, $order_items, $charges);

                    if ($this->logging === 'yes') {
                        wc_get_logger()->debug(print_r([
                            'action'      => 'credit_key_confirm_after',
                            'order_id'    => $order_id,
                            'ck_order_id' => $result->getOrderId(),
                            'status'      => $result->getStatus(),
                        ], true), ['source' => $this->id]);
                    }
                    $order->update_meta_data('ck_is_confirmed', true);
                    $order->save();
                }
            }
        } catch (Exception $e) {
            $this->lets_log($e);
        }
    }

    public function call_credit_key_order_cancel($order_id)
    {
        try {
            $order          = wc_get_order($order_id);
            $payment_method = $order->get_payment_method();
            if ($payment_method == $this->id) {
                $ck_order_id  = $order->get_meta('ck_order_id', true);
                if (!empty($ck_order_id)) {
                    Api::configure($this->api_url, $this->public_key, $this->shared_secret);
                    Orders::cancel($ck_order_id);
                }
                $order->update_meta_data('ck_is_cancelled', true);
                $order->save();
            }
        } catch (Exception $e) {
            $this->lets_log($e);
        }
    }

    public function call_credit_key_order_refund($order_id)
    {
        try {
            $order          = wc_get_order($order_id);
            $payment_method = $order->get_payment_method();
            if ($payment_method == $this->id) {
                $is_confirmed = $order->get_meta('ck_is_confirmed', true);
                $is_refunded  = $order->get_meta('ck_is_refunded', true);
                $ck_order_id  = $order->get_meta('ck_order_id', true);

                if ($is_confirmed && !$is_refunded && !empty($ck_order_id)) {
                    $refund_amount = (float) $order->get_total_refunded();
                    if ($refund_amount <= 0) {
                        $refund_amount = (float) $order->get_total();
                    }
                    Api::configure($this->api_url, $this->public_key, $this->shared_secret);
                    Orders::refund($ck_order_id, $refund_amount);
                }
                $order->update_meta_data('ck_is_refunded', true);
                $order->save();
            }
        } catch (Exception $e) {
            $this->lets_log($e);
        }
    }

    public function call_credit_key_order_update($order_id)
    {
        // Prevent recursive loop triggered by $order->save() inside this hook
        if (self::$isOrderUpdateInProgress) {
            return;
        }

        self::$isOrderUpdateInProgress = true;
        try {
            $order = wc_get_order($order_id);
            $payment_method = $order->get_payment_method();

            if ($payment_method === $this->id) {
                $order_status = $order->get_status();

                // Skip non-terminal updates if already confirmed by Credit Key.
                $is_confirmed = $order->get_meta('ck_is_confirmed', true);
                if ($is_confirmed && !in_array($order_status, ['cancelled', 'refunded'], true)) {
                    return;
                }
                $ck_order_id = $order->get_meta('ck_order_id', true);
                if (empty($ck_order_id)) {
                    if ($this->logging === 'yes') {
                        wc_get_logger()->debug(print_r([
                            'action' => 'credit_key_update_skipped',
                            'reason' => 'missing_ck_order_id',
                            'order_id' => $order_id,
                        ], true), ['source' => $this->id]);
                    }
                    return;
                }

                $allowed_statuses = ['processing', 'completed', 'refunded', 'cancelled'];
                if (!in_array($order_status, $allowed_statuses, true)) {
                    if ($this->logging === 'yes') {
                        wc_get_logger()->debug(print_r([
                            'action' => 'credit_key_update_skipped',
                            'reason' => 'status_not_allowed',
                            'order_id' => $order_id,
                            'status' => $order_status,
                        ], true), ['source' => $this->id]);
                    }
                    return;
                }
                $order_data = $this->get_order_data($order_id);
                $order_items = $order_data['order_items'];
                $shipping_address = $order_data['shipping_address'];
                $charges = $order_data['charges'];

                $merchant_order_no = $this->get_credit_key_merchant_order_id($order_id);

                Api::configure($this->api_url, $this->public_key, $this->shared_secret);
                Orders::update($ck_order_id, $order_status, $merchant_order_no, $order_items, $charges, $shipping_address);
                $order->update_meta_data('ck_order_updated_timestamp', time());
                $order->save();
            }
        } catch (Exception $e) {
            $this->lets_log($e);
        } finally {
            self::$isOrderUpdateInProgress = false;
        }
    }

    public function control_order_statuses($wc_statuses_arr)
    {
        global $pagenow;

        $order_id = null;
        if (is_admin() && $pagenow === 'post.php' && get_post_type() === 'shop_order') {
            $order_id = get_the_ID();
        } elseif (is_admin() && $pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'wc-orders' && isset($_GET['id'])) {
            $order_id = absint($_GET['id']);
        }

        if ($order_id) {
            $order          = wc_get_order($order_id);
            $payment_method = $order->get_payment_method();

            if ($payment_method == $this->id) {

                $is_confirmed = $order->get_meta('ck_is_confirmed', true);
                $is_refunded  = $order->get_meta('ck_is_refunded', true);
                $is_cancelled = $order->get_meta('ck_is_cancelled', true);

                if ($order->get_status() === 'cancelled') {
                    $is_cancelled = true;
                }

                if ($is_confirmed && !$is_refunded && !$is_cancelled) {
                    foreach ($wc_statuses_arr as $status_key => $status) {
                        if ($status_key != 'wc-completed' && $status_key != 'wc-cancelled' && $status_key != 'wc-refunded') {
                            unset($wc_statuses_arr[$status_key]);
                        }
                    }
                }

                if ($is_refunded) {
                    foreach ($wc_statuses_arr as $status_key => $status) {
                        if ($status_key != 'wc-refunded') {
                            unset($wc_statuses_arr[$status_key]);
                        }
                    }
                }

                if ($is_cancelled) {
                    foreach ($wc_statuses_arr as $status_key => $status) {
                        if ($status_key != 'wc-cancelled') {
                            unset($wc_statuses_arr[$status_key]);
                        }
                    }

                }
            }

        }

        return $wc_statuses_arr;
    }

    public function change_gateway_icon($icon, $gateway_id)
    {
        if ($gateway_id === Main::$gateway_id) {
            $icon = '<img src="' . Main::$plugin_url . 'assets/images/credit-key-payment-method-new-logo.svg' . '" alt="' . __('Pay with Credit Key', 'credit_key') . '"> ';
        }
        return $icon;
    }

    /**
	 * @param int $order_id
	 *
	 * @return string
	 */
    public static function get_sequential_order_number($order_id) {
        if(function_exists( 'wc_sequential_order_numbers' )){
            $order = wc_get_order($order_id);
            return $order->get_meta( '_order_number', true, 'edit' );
        }

        return strval($order_id);
    }

    private function get_credit_key_merchant_order_id($order_id) {
        $order_number = self::get_sequential_order_number($order_id);
        $prefix       = is_string($this->order_prefix) ? $this->order_prefix : '';
        $suffix       = is_string($this->order_suffix) ? $this->order_suffix : '';

        return apply_filters(
            'woocommerce_credit_key_order_number',
            $prefix . $order_number . $suffix,
            $order_id
        );
    }

    public function prevent_unauthorized_status_change($order_id, $old_status, $new_status, $order) {
        // 1. Check if it is our gateway
        if ($order->get_payment_method() !== $this->id) {
            return;
        }

        // 2. Define "Locked" statuses (from)
        if ($old_status === 'cancelled') {
            // 3. Define allowed transitions (e.g. maybe to 'refunded' if you want that, otherwise just block all)
            // If we strictly want "Once cancelled, always cancelled":
            if ($new_status !== 'cancelled') {

                // Remove this hook to prevent infinite loop during revert
                remove_action('woocommerce_order_status_changed', [$this, 'prevent_unauthorized_status_change'], 10);

                // Revert status
                $order->update_status('cancelled', __('Credit Key: Cannot change status from Cancelled.', 'credit_key'));

                // Re-add hook
                add_action('woocommerce_order_status_changed', [$this, 'prevent_unauthorized_status_change'], 10, 4);
            }
        }
    }
}
