<?php

namespace CreditKey;

use CreditKey\Main;

class CreditKeyNotCheckoutPayment {
	private static $instance;
	private function __construct(){

		$this->gateway_id = Main::$gateway_id;
		$this->gateway_settings = get_option('woocommerce_' . Main::$gateway_id . '_settings');
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_credit_key_button' ) );
		add_action('woocommerce_proceed_to_checkout', array($this, 'add_credit_key_button_to_cart' ));

		add_action( 'wp_ajax_nopriv_get_cart_data', [$this, 'get_cart_data_handler'] );
		add_action( 'wp_ajax_get_cart_data', [$this, 'get_cart_data_handler'] );
	}
	public static function getInstance(){
		if(self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function payment_scripts() {

		$gateway_settings = $this->gateway_settings;
	    $is_enable = $gateway_settings['enabled'];
		if ( 'no' === $is_enable ) {
			return;
		}

		if( is_product() || is_cart() ){
			wp_register_script('credit-key-js', Main::$plugin_url . 'assets/js/creditkey-js.js', null, '1.0.80');
			wp_enqueue_script('credit-key-js');

			wp_register_script('credit-key-scripts', Main::$plugin_url . 'assets/js/scripts.js', array('jquery', 'credit-key-js'), time());
			wp_enqueue_script('credit-key-scripts');
			wp_localize_script('credit-key-scripts', 'CreditKey', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

			wp_enqueue_style( 'credit-key-styles', Main::$plugin_url . 'assets/css/styles.css' );
		}

	}

	public function add_credit_key_button(){
		$gateway_settings   = $this->gateway_settings;
	    $show_on_product_page = (isset($gateway_settings['product_page'])) ? $gateway_settings['product_page'] : 'no';
	    if($show_on_product_page == 'yes'){
		    $product_id = get_the_ID();
		    $product = wc_get_product($product_id);
		    $product_price = $product->get_price();
		    echo '<div id="pdp"></div>';
		    $staging    = ($gateway_settings['is_test'] == "yes") ? 'staging' : 'production';
		    $public_key = ($gateway_settings['is_test'] == "yes") ? $gateway_settings['test_public_key'] : $gateway_settings['public_key'];
		    $button_display = ( isset($gateway_settings['button_display']) ) ? $gateway_settings['button_display'] : 'button';
		    $button_size    = ( isset($gateway_settings['button_size']) ) ? $gateway_settings['button_size'] : 'small';
?>
            <script type="text/javascript">
                let client = new ck.Client('<?php echo $public_key; ?>', '<?php echo $staging; ?>');
                let charges = new ck.Charges(<?php echo $product_price; ?>, 0, 0 , 0, <?php echo $product_price; ?>);
                client.get_marketing_display(charges, 'pdp', '<?php echo $button_display; ?>', '<?php echo $button_size; ?>')
                    .then(res => pdp.innerHTML = res);
            </script>
		    <?php
        }
	}

	public function add_credit_key_button_to_cart(){
		$gateway_settings   = $this->gateway_settings;
		$show_on_cart_page = (isset($gateway_settings['cart_page'])) ? $gateway_settings['cart_page'] : 'no';
		if($show_on_cart_page == 'yes'){
			$staging    = ($gateway_settings['is_test'] == "yes") ? 'staging' : 'production';
			$public_key = ($gateway_settings['is_test'] == "yes") ? $gateway_settings['test_public_key'] : $gateway_settings['public_key'];
            include_once Main::$plugin_path . 'template-parts/cart-button.php';
            echo '<div id="modal-pdp"></div>';
?>
            <script type="text/javascript">
                let client = new ck.Client('<?php echo $public_key; ?>', '<?php echo $staging; ?>');
            </script>
			<?php
		}
	}

	public function get_cart_data_handler(){
		$cart_totals = WC()->cart->get_totals();
		$cart_subtotal      = number_format($cart_totals['subtotal'], 2);
		$cart_tax_total     = number_format($cart_totals['total_tax'], 2);
		$cart_discount_total= number_format($cart_totals['discount_total'], 2);
		$cart_shipping_total= number_format($cart_totals['shipping_total'], 2);
		$cart_total         = number_format($cart_totals['total'], 2);
		wp_send_json(array(
            'cart_subtotal'        => $cart_subtotal,
            'cart_tax_total'       => $cart_tax_total,
            'cart_discount_total'  => $cart_discount_total,
            'cart_shipping_total'  => $cart_shipping_total,
            'cart_total'           => $cart_total,
        ));
	}
}
CreditKeyNotCheckoutPayment::getInstance();