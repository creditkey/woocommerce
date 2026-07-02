<?php

namespace CreditKey;

class CreditKeyNotCheckoutPayment
{
    private static $instance;
    public string $gateway_id;
    /**
     * @var array|null
     */
    private $gateway_settings;
    
    private function __construct()
    {
        $this->gateway_id       = Main::$gateway_id;
        $this->gateway_settings = get_option('woocommerce_' . Main::$gateway_id . '_settings');
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        add_action('woocommerce_after_add_to_cart_button', array($this, 'add_credit_key_button'));
        add_action('woocommerce_after_cart_totals', array($this, 'add_credit_key_button_to_cart'));
        
        add_action('wp_ajax_nopriv_get_cart_data', [$this, 'get_cart_data_handler']);
        add_action('wp_ajax_get_cart_data', [$this, 'get_cart_data_handler']);
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function payment_scripts()
    {
        $gateway_settings     = $this->gateway_settings;

	    if ( empty( $gateway_settings ) ) {
		    return;
	    }

        $show_on_product_page = (isset($gateway_settings['product_page'])) ? $gateway_settings['product_page'] : 'no';
        $show_on_cart_page    = (isset($gateway_settings['cart_page'])) ? $gateway_settings['cart_page'] : 'no';
        $is_enable            = (isset($gateway_settings['enabled'])) ? $gateway_settings['enabled'] : 'no';
        if ('no' === $is_enable) {
            return;
        }

        // Enqueue per surface: product messaging on product pages, cart messaging on
        // the cart page. Cart assets must load even when product messaging is disabled,
        // otherwise block-cart messaging (and classic-cart messaging) have no `ck` runtime.
        $enqueue_for_product = is_product() && $show_on_product_page == 'yes';
        $enqueue_for_cart    = is_cart() && $show_on_cart_page == 'yes';
        if (!$enqueue_for_product && !$enqueue_for_cart) {
            return;
        }

        wp_register_script('credit-key-js', 'https://unpkg.com/@credit-key/creditkey-js@1.0.96/umd/creditkey-js.js', null, '1.0.96');
        wp_enqueue_script('credit-key-js');

        wp_register_script('credit-key-scripts', Main::$plugin_url . 'assets/js/scripts.js', array(
            'jquery',
            'credit-key-js'
        ), ( defined('WP_DEBUG') && WP_DEBUG ) ? time() : '2.2');
        wp_enqueue_script('credit-key-scripts');

        $environment = ($gateway_settings['is_test'] == "yes") ? 'staging' : 'production';
        $public_key  = ($gateway_settings['is_test'] == "yes") ? $gateway_settings['test_public_key'] : $gateway_settings['public_key'];
        $cart_alignment_desktop = $gateway_settings['cart_alignment_desktop'] == 'centered' ? 'center' : $gateway_settings['cart_alignment_desktop'];
        $cart_alignment_mobile  = $gateway_settings['cart_alignment_mobile'] == 'centered' ? 'center' : $gateway_settings['cart_alignment_mobile'];

        wp_localize_script('credit-key-scripts', 'CreditKey', array(
            'ajax_url'             => admin_url('admin-ajax.php'),
            'nonce'                => wp_create_nonce('credit_key_cart_data'),
            'imagesPath'           => Main::$plugin_url,
            'cartEnabled'          => $show_on_cart_page,
            'publicKey'            => $public_key,
            'environment'          => $environment,
            'minCart'              => isset($gateway_settings['min_cart']) ? $gateway_settings['min_cart'] : 0,
            'cartSelector'         => isset($gateway_settings['promo_message_cart_selector']) ? $gateway_settings['promo_message_cart_selector'] : '',
            'cartAlignmentDesktop' => $cart_alignment_desktop,
            'cartAlignmentMobile'  => $cart_alignment_mobile,
        ));

        // Block-cart messaging: the classic `woocommerce_after_cart_totals` hook never
        // fires for the `woocommerce/cart` block, so render client-side after it mounts.
        if ($enqueue_for_cart && function_exists('has_block') && has_block('woocommerce/cart')) {
            wp_register_script('credit-key-cart-block', Main::$plugin_url . 'assets/js/cart-block.js', array(
                'credit-key-js',
                'credit-key-scripts',
                'wp-data'
            ), ( defined('WP_DEBUG') && WP_DEBUG ) ? time() : '2.2', true);
            wp_enqueue_script('credit-key-cart-block');
        }

        wp_enqueue_style('credit-key-styles', Main::$plugin_url . 'assets/css/styles.css');
    }
    
    public function add_credit_key_button()
    {
        $gateway_settings     = $this->gateway_settings;
        $show_on_product_page = (isset($gateway_settings['product_page'])) ? $gateway_settings['product_page'] : 'no';
        $active_plugin        = (isset($gateway_settings['enabled'])) ? $gateway_settings['enabled'] : 'no';
        
        $product_id    = get_the_ID();
        $product       = wc_get_product($product_id);
        $product_price = $product->get_price();
        $min_total     = isset($gateway_settings['min_product']) ? $gateway_settings['min_product'] : 0;
        $button_type   = isset($gateway_settings['button_display']) ? $gateway_settings['button_display'] : '';
        
        if ($show_on_product_page == 'yes' && $product_price >= $min_total && $active_plugin == 'yes') {
            
            echo '<div>';
            
            switch ($button_type) {
                case 'text_no_modal':
                    echo '<div id="pdp"></div>';
                    break;
                default:
                    echo '<div id="pdp"></div>';
            }
            
            echo '</div>';
            
            $environment = ($gateway_settings['is_test'] == "yes") ? 'staging' : 'production';
            $public_key  = ($gateway_settings['is_test'] == "yes") ? $gateway_settings['test_public_key'] : $gateway_settings['public_key'];
            ?>
            <script type="text/javascript">
                let client = new ck.Client('<?php echo $public_key; ?>', '<?php echo $environment; ?>');
                let charges = new ck.Charges(<?php echo $product_price; ?>, 0, 0, 0, <?php echo $product_price; ?>);
                
                <?php if (! empty($gateway_settings['promo_message_product_selector'])): ?>
                jQuery(document).ready(function ($) {
                    $(<?php echo wp_json_encode($gateway_settings['promo_message_product_selector']); ?>).append(client.get_pdp_display(charges));
                });
                <?php else: ?>
                document.getElementById('pdp').innerHTML = client.get_pdp_display(charges);
                <?php endif; ?>
            </script>
            <?php
        }
    }
    
    public function add_credit_key_button_to_cart()
    {
        global $woocommerce;
        $gateway_settings  = $this->gateway_settings;
        $show_on_cart_page = (isset($gateway_settings['cart_page'])) ? $gateway_settings['cart_page'] : 'no';
        $active_plugin     = (isset($gateway_settings['enabled'])) ? $gateway_settings['enabled'] : 'no';
        
        $cart_totals = ($woocommerce && $woocommerce->cart) ? $woocommerce->cart->get_totals() : ['total' => 0];
        $cart_total  = isset($cart_totals['total']) ? (float) $cart_totals['total'] : 0.0;
        $min_total   = isset($gateway_settings['min_cart']) ? (float) $gateway_settings['min_cart'] : 0.0;

        $desktop_align = $gateway_settings['cart_alignment_desktop'] ?? 'right';
        $mobile_align  = $gateway_settings['cart_alignment_mobile'] ?? 'right';
        $desktop_align = ($desktop_align === 'centered') ? 'center' : $desktop_align;
        $mobile_align  = ($mobile_align === 'centered') ? 'center' : $mobile_align;
        $cart_alignment_desktop = "'" . $desktop_align . "'";
        $cart_alignment_mobile  = "'" . $mobile_align . "'";
        
        if ($show_on_cart_page == 'yes' && $cart_total >= $min_total && $active_plugin == 'yes') {
            $environment = ($gateway_settings['is_test'] == "yes") ? 'staging' : 'production';
            $public_key  = ($gateway_settings['is_test'] == "yes") ? $gateway_settings['test_public_key'] : $gateway_settings['public_key'];
            echo '<div>';
            echo '<div id="cartbanner"></div>';
            echo '</div>';
            ?>
            <script type="text/javascript">
                let client = new ck.Client('<?php echo $public_key; ?>', '<?php echo $environment; ?>');
                let charges = new ck.Charges(<?php echo $cart_total; ?>, 0, 0, 0, <?php echo $cart_total; ?>);
                <?php if (! empty($gateway_settings['promo_message_cart_selector'])): ?>
                jQuery(document).ready(function ($) {
                    $(<?php echo wp_json_encode($gateway_settings['promo_message_cart_selector']); ?>).append(client.get_cart_display(charges, <?php echo $cart_alignment_desktop . ", " . $cart_alignment_mobile; ?>));
                });
                <?php else: ?>
                document.getElementById('cartbanner').innerHTML = client.get_cart_display(charges, <?php echo $cart_alignment_desktop . ", " . $cart_alignment_mobile; ?>);
                <?php endif; ?>
            </script>
            <?php
        }
    }
    
    public function get_cart_data_handler()
    {
        check_ajax_referer('credit_key_cart_data', 'nonce');
        $cart_totals         = WC()->cart->get_totals();
        $cart_subtotal       = number_format($cart_totals['subtotal'],  2, '.', '' );
        $cart_tax_total      = number_format($cart_totals['total_tax'],  2, '.', '' );
        $cart_discount_total = number_format($cart_totals['discount_total'],  2, '.', '' );
        $cart_shipping_total = number_format($cart_totals['shipping_total'],  2, '.', '' );
        $cart_total          = number_format($cart_totals['total'],  2, '.', '' );
        wp_send_json(array(
            'cart_subtotal'       => $cart_subtotal,
            'cart_tax_total'      => $cart_tax_total,
            'cart_discount_total' => $cart_discount_total,
            'cart_shipping_total' => $cart_shipping_total,
            'cart_total'          => $cart_total,
        ));
    }
}

CreditKeyNotCheckoutPayment::getInstance();