<?php

use CreditKey\Api;
use CreditKey\Models\Address;
use CreditKey\Models\Charges;
use CreditKey\Models\CartItem;
use CreditKey\Checkout;
use CreditKey\Main;
use CreditKey\Orders;


class WC_Credit_Key extends WC_Payment_Gateway {
	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->id                 = Main::$gateway_id;
		$this->method_title       = esc_html__( 'Credit Key', 'credit_key' );
		$this->method_description = esc_html__( 'Credit Key offers better business credit, in seconds, right at e-commerce checkout.', 'credit_key' );
		$this->supports           = array( 'products', 'refunds' );
		$this->order_button_text  = __( 'Continue with Credit Key', 'credit_key' );

		// Method with all the options fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();
		$this->description   = $this->get_option( 'description' );
		$this->enabled       = $this->get_option( 'enabled' );
		$this->testmode      = 'yes' === $this->get_option( 'is_test' );
		$this->order_prefix  = $this->get_option( ' order_prefix ' );
		$this->public_key    = ( $this->testmode == 'yes' ) ? $this->get_option( 'test_public_key' ) : $this->get_option( 'public_key' );
		$this->shared_secret = ( $this->testmode == 'yes' ) ? $this->get_option( 'test_shared_secret' ) : $this->get_option( 'shared_secret' );
		$this->api_url       = ( $this->testmode == 'yes' ) ? 'https://staging.creditkey.com/app' : 'https://www.creditkey.com/app';
		$this->logging       = $this->get_option( 'logging' );
		$this->min_checkout  = ( $this->get_option( 'min_checkout' ) ) ? $this->get_option( 'min_checkout' ) : 0;
		$this->min_product   = ( $this->get_option( 'min_product' ) ) ? $this->get_option( 'min_product' ) : 0;
		$this->min_cart      = ( $this->get_option( 'min_cart' ) ) ? $this->get_option( 'min_cart' ) : 0;

		// Save payment gateway settings
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
		add_action( 'woocommerce_api_credit_key', array( $this, 'webhook' ) );

		// Is displayed in checkout
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'credit_key_gateway_enable_condition' ) );

		add_action( 'woocommerce_order_status_completed', array( $this, 'call_credit_key_order_confirm' ), 10, 1 );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'call_credit_key_order_cancel' ), 10, 1 );
		add_action( 'woocommerce_update_order', array( $this, 'call_credit_key_order_update' ), 10, 1 );

		add_filter( 'wc_order_statuses', array( $this, 'control_order_statuses' ), 10, 1 );
		add_action('admin_enqueue_scripts', array($this, 'dashboard_payment_scripts'));

		add_filter( 'woocommerce_gateway_title', array($this, 'change_payment_gateway_title'), 100, 2 );

		add_filter( 'woocommerce_gateway_icon', array($this, 'change_gateway_icon'), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

	}

	/**
	 * Plugin options
	 */
	
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => esc_html__( 'Enable Credit Key Payment Gateway', 'credit_key' ),
				'label'       => esc_html__( 'Enable/Disable', 'credit_key' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'description' => array(
				'title'       => esc_html__( 'Payment Method Description', 'credit_key' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Description under payment method in checkout.', 'credit_key' ),
				'default'     => esc_html__( 'Pay the order via Secret Key payment gateway.', 'credit_key' ),
			),
			'min_checkout'      => array(
				'title'       => esc_html__( 'Minimum Order Amount', 'credit_key' ),
				'label'       => esc_html__( 'Show/Hide', 'credit_key' ),
				'type'        => 'number',
				'description' => 'The minimum order amount to offer Credit Key as a payment method in checkout.',
				'default'     => 0
			),
			'is_test' => array(
				'title'       => esc_html__( 'API Mode', 'credit_key' ),
				'label'       => esc_html__( 'Staging', 'credit_key' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),

			'public_key' => array(
				'title' => esc_html__( 'Public Key', 'credit_key' ),
				'type'  => 'text',
			),

			'test_public_key' => array(
				'title' => esc_html__( 'Public Key', 'credit_key' ),
				'type'  => 'text',
			),

			'shared_secret' => array(
				'title' => esc_html__( 'Shared Secret', 'credit_key' ),
				'type'  => 'text',
			),

			'test_shared_secret' => array(
				'title' => esc_html__( 'Shared Secret', 'credit_key' ),
				'type'  => 'text',
			),
			'product_page'    => array(
				'title'       => esc_html__( 'Show Credit Key on product pages', 'credit_key' ),
				'label'       => esc_html__( 'Show the Credit Key under price label in product pages', 'credit_key' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'min_product'      => array(
				'title'       => esc_html__( 'Minimum Product Price', 'credit_key' ),
				'label'       => esc_html__( 'Show/Hide', 'credit_key' ),
				'type'        => 'number',
				'description' => 'The minimum price for a product where the promotion should be displayed (in dollars).',
				'default'     => 0
			),
			'cart_page'      => array(
				'title'       => esc_html__( 'Show Credit Key on Cart Page', 'credit_key' ),
				'label'       => esc_html__( 'Show the Credit Key under cart total on cart page', 'credit_key' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'min_cart'      => array(
				'title'       => esc_html__( 'Minimum Cart Total', 'credit_key' ),
				'label'       => esc_html__( 'Show/Hide', 'credit_key' ),
				'type'        => 'number',
				'description' => 'The minimum cart total where the promotion should be displayed (in dollars).',
				'default'     => 0
			),
			'cart_alignment_desktop'  => array(
				'title'       => 'Cart Page Alignment for Desktop',
				'description' => 'Cart page text can be aligned left, centered, or right for desktop devices',
				'type'        => 'select',
				'options'     => array(
					'right'   => 'Right',
					'centered' => 'Centered',
					'left'    => 'Left'
				)
			),
			'cart_alignment_mobile'  => array(
				'title'       => 'Cart Page Alignment for Mobile',
				'description' => 'Cart page text can be aligned left, centered, or right for mobile devices',
				'type'        => 'select',
				'options'     => array(
					'right'   => 'Right',
					'centered' => 'Centered',
					'left'    => 'Left'
				)
			),
			'logging'      => array(
				'title'       => esc_html__( 'Enable logging', 'credit_key' ),
				'label'       => esc_html__( 'Enable/Disable', 'credit_key' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
		);
	}

	public function dashboard_payment_scripts() {

		if ( 'no' === $this->enabled ) {
			return;
		}
		wp_register_script('creditkey-dashboard-scripts', Main::$plugin_url . 'assets/js/scripts-dashboard.js', array('jquery'), time());
		wp_enqueue_script('creditkey-dashboard-scripts');

	}

	private function get_customer_id() {
		if ( is_user_logged_in() ) {
			$customerId = get_current_user_id();
		} else {
			$customerId = 0;
		}

		return $customerId;
	}

	public function payment_scripts(){
		wp_enqueue_style( 'credit-key-styles', Main::$plugin_url . 'assets/css/styles.css' );
	}

	public function lets_log( $e ) {
		if ( $this->logging == 'yes' ) {
			$message = $e->getMessage();
			$code    = $e->getCode();
			$file    = $e->getFile();
			$line    = $e->getLine();

			$data = array();
			if ( $message ) {
				$data['message'] = $message;
			}
			if ( $code ) {
				$data['code'] = $code;
			}
			if ( $file ) {
				$data['file'] = $file;
			}
			if ( $line ) {
				$data['line'] = $line;
			}

			wc_get_logger()->debug( print_r( $data, true ), [ 'source' => $this->id ] );
		}
	}

	public function credit_key_gateway_enable_condition( $available_gateways ) {
		if ( ! is_admin() && is_checkout() ) {
			try {
				global $woocommerce;

				$cart_items = array();
				$items      = $woocommerce->cart->get_cart();
				if ( is_array( $items ) && ! empty( $items ) ) {
					foreach ( $items as $item => $values ) {
						$merchant_id  = $values['data']->get_id();
						$name         = $values['data']->get_title();
						$price        = $values['data']->get_price();
						$quantity     = $values['quantity'];
						$sku          = $values['data']->get_sku();
						$cart_items[] = new CartItem( $merchant_id, $name, $price, $sku, $quantity, null, null );
					}
				}

				$cart_totals = $woocommerce->cart->get_totals();
				$cart_total = (float)$cart_totals['total'];
				$min_total = (float)$this->min_checkout;

				$customerId = $this->get_customer_id();

				Api::configure( $this->api_url, $this->public_key, $this->shared_secret );
				$is_displayed = Checkout::isDisplayedInCheckout( $cart_items, $customerId );
				if ( ! $is_displayed || $cart_total <= $min_total ) {
					unset( $available_gateways['credit_key'] );
				}
			} catch ( Exception $e ) {
				$this->lets_log( $e );
				unset( $available_gateways['credit_key'] );
			}
		}

		return $available_gateways;
	}

	//Show Credit Key description on checkout page;
	public function payment_fields() {
		echo '<p>' . $this->description . '</p>';
	}

	private function get_order_data( $order_id ) {

		$order = wc_get_order( $order_id );

		// Create billing data
		$billing_first_name   = ( $order->get_billing_first_name() ) ? $order->get_billing_first_name() : null;
		$billing_last_name    = ( $order->get_billing_last_name() ) ? $order->get_billing_last_name() : null;
		$billing_company_name = ( $order->get_billing_company() ) ? $order->get_billing_company() : null;
		$billing_email        = ( $order->get_billing_email() ) ? $order->get_billing_email() : null;
		$billing_address1     = ( $order->get_billing_address_1() ) ? $order->get_billing_address_1() : null;
		$billing_address2     = ( $order->get_billing_address_2() ) ? $order->get_billing_address_2() : null;
		$billing_city         = ( $order->get_billing_city() ) ? $order->get_billing_city() : null;
		$billing_state        = ( $order->get_billing_state() ) ? $order->get_billing_state() : null;
		$billing_zip          = ( $order->get_billing_postcode() ) ? $order->get_billing_postcode() : null;
		$billing_phone_number = ( $order->get_billing_phone() ) ? $order->get_billing_phone() : null;

		$billing_address = new Address( $billing_first_name, $billing_last_name, $billing_company_name, $billing_email,
			$billing_address1, $billing_address2, $billing_city, $billing_state, $billing_zip, $billing_phone_number );

		// Create shipping data
		$shipping_first_name   = ( $order->get_shipping_first_name() ) ? $order->get_shipping_first_name() : null;
		$shipping_last_name    = ( $order->get_shipping_last_name() ) ? $order->get_shipping_last_name() : null;
		$shipping_company_name = ( $order->get_shipping_company() ) ? $order->get_shipping_company() : null;
		$shipping_email        = $billing_email;
		$shipping_address1     = ( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() : null;
		$shipping_address2     = ( $order->get_shipping_address_2() ) ? $order->get_shipping_address_2() : null;
		$shipping_city         = ( $order->get_shipping_city() ) ? $order->get_shipping_city() : null;
		$shipping_state        = ( $order->get_shipping_state() ) ? $order->get_shipping_state() : null;
		$shipping_zip          = ( $order->get_shipping_postcode() ) ? $order->get_shipping_postcode() : null;
		$shipping_phone_number = $billing_phone_number;

		$shipping_address = new Address( $shipping_first_name, $shipping_last_name, $shipping_company_name, $shipping_email,
			$shipping_address1, $shipping_address2, $shipping_city, $shipping_state, $shipping_zip, $shipping_phone_number );

		// Create Order items data
		$total           = 0;
		$discount_amount = 0;
		$order_items     = array();
		foreach ( $order->get_items() as $key => $item ) {
			$product       = $item->get_product();
			$product_id    = $product->get_id();
			$product_name  = $product->get_name();
			$product_price = $product->get_price();
			$product_sku   = $product->get_sku();
			$product_qty   = intval( $item->get_quantity() );
			$order_items[] = new CartItem( $product_id, $product_name, $product_price, $product_sku, $product_qty, null, null );

			$total           += $product->get_regular_price() * $product_qty;
			$discount_amount += ( $product->get_regular_price() - $product_price ) * $product_qty;
		}
		$total           = number_format( $total, 2 );
		$discount_amount = number_format( $discount_amount + $order->get_total_discount(), 2 );
		$shipping        = number_format( $order->get_shipping_total(), 2 );
		$tax             = number_format( $order->get_total_tax(), 2 );
		$grand_total     = number_format( $total + $shipping + $tax - $discount_amount, 2 );

		// Create charges data
		$charges = new Charges( $total, $shipping, $tax, $discount_amount, $grand_total );

		return array(
			'order_items'      => $order_items,
			'billing_address'  => $billing_address,
			'shipping_address' => $shipping_address,
			'charges'          => $charges,
		);
	}

	public function process_payment( $order_id ) {

		$order_data       = $this->get_order_data( $order_id );
		$order_items      = $order_data['order_items'];
		$billing_address  = $order_data['billing_address'];
		$shipping_address = $order_data['shipping_address'];
		$charges          = $order_data['charges'];
		$customerId       = $this->get_customer_id();
		$remoteId         = $order_id;
		$returnUrl        = home_url() . '/wc-api/credit_key?order_id=' . $order_id . '&id=%CKKEY%';
		$cancelUrl        = wc_get_checkout_url();

		Api::configure( $this->api_url, $this->public_key, $this->shared_secret );
		$customerCheckoutUrl = Checkout::beginCheckout( $order_items,
			$billing_address, $shipping_address, $charges, $remoteId, $customerId,
			$returnUrl, $cancelUrl, 'redirect' );

		return [ 'result' => 'success', 'redirect' => $customerCheckoutUrl ];
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$ck_order_id = get_post_meta( $order_id, 'ck_order_id', true );
		if ( isset( $ck_order_id ) && $amount > 0 ) {

			$is_confirmed = get_post_meta( $order_id, 'ck_is_confirmed', true );

			if( $is_confirmed ){
				Api::configure( $this->api_url, $this->public_key, $this->shared_secret );
				$refund_order = Orders::refund( $ck_order_id, $amount );
				update_post_meta( $order_id, 'ck_is_refunded', true );
				return true;
			} else {
				return false;
			}

		}
	}

	public function webhook() {
		if ( isset( $_GET ) && isset($_GET['id']) && isset($_GET['order_id']) ) {

			$ck_order_id = sanitize_text_field($_GET['id']);
			$order_id    = sanitize_text_field($_GET['order_id']);
			$order       = wc_get_order( $order_id );
			update_post_meta( $order_id, 'ck_order_id', $ck_order_id );

			Api::configure( $this->api_url, $this->public_key, $this->shared_secret );
			$complete_checkout = Checkout::completeCheckout( $ck_order_id );
			if ( $complete_checkout ) {

				$order->add_order_note( esc_html__( 'Order paid via Credit Key.', 'credit_key' ), 1 );
				$order->payment_complete( $ck_order_id );
				WC()->cart->empty_cart();

				$thank_you_url = $order->get_checkout_order_received_url();
				header( 'Location: ' . $thank_you_url );
				die();

			} else {
				wp_redirect( wc_get_checkout_url() );
				exit;
			}
		}
		die();
	}

	public function call_credit_key_order_confirm( $order_id ) {
		$ck_order_id = get_post_meta( $order_id, 'ck_order_id', true );
		try {
			$order          = wc_get_order( $order_id );
			$payment_method = $order->get_payment_method();
			if ( $payment_method == $this->id ) {
				$is_confirmed = get_post_meta( $order_id, 'ck_is_confirmed', true );
				$ck_order_id  = get_post_meta( $order_id, 'ck_order_id', true );

				if ( ! $is_confirmed ) {

					$order_status = $order->get_status();

					Api::configure( $this->api_url, $this->public_key, $this->shared_secret );

					$order_data  = $this->get_order_data( $order_id );
					$order_items = $order_data['order_items'];
					$charges     = $order_data['charges'];

					Orders::confirm( $ck_order_id, strval( $order_id ), $order_status, $order_items, $charges );
					update_post_meta( $order_id, 'ck_is_confirmed', true );
				}
			}
		} catch ( Exception $e ) {
			$this->lets_log( $e );
		}
	}

	public function call_credit_key_order_cancel( $order_id ) {
		try {
			$order          = wc_get_order( $order_id );
			$payment_method = $order->get_payment_method();
			if ( $payment_method == $this->id ) {
				$is_confirmed = get_post_meta( $order_id, 'ck_is_confirmed', true );
				$ck_order_id  = get_post_meta( $order_id, 'ck_order_id', true );

				if ( ! $is_confirmed ) {

					Api::configure( $this->api_url, $this->public_key, $this->shared_secret );

					$ck_order = Orders::cancel( $ck_order_id );
					update_post_meta( $order_id, 'ck_is_cancelled', true );
				}
			}
		} catch ( Exception $e ) {
			$this->lets_log( $e );
		}
	}

	public function call_credit_key_order_update( $order_id ) {
		try {
			$order          = wc_get_order( $order_id );
			$payment_method = $order->get_payment_method();
			if ( $payment_method == $this->id ) {

				$ck_order_id      = get_post_meta( $order_id, 'ck_order_id', true );
				$order_status     = $order->get_status();
				$order_data       = $this->get_order_data( $order_id );
				$order_items      = $order_data['order_items'];
				$shipping_address = $order_data['shipping_address'];
				$charges          = $order_data['charges'];

				Api::configure( $this->api_url, $this->public_key, $this->shared_secret );
				Orders::update( $ck_order_id, $order_status, strval( $order_id ), $order_items, $charges, $shipping_address );
				update_post_meta( $order_id, 'ck_order_updated_timestamp', time() );
			}
		} catch ( Exception $e ) {
			$this->lets_log( $e );
		}
	}

	public function control_order_statuses( $wc_statuses_arr ) {
		global $pagenow;
		if ( is_admin() && $pagenow == 'post.php' && get_post_type() == 'shop_order' ) {
			$order_id       = get_the_ID();
			$order          = wc_get_order( $order_id );
			$payment_method = $order->get_payment_method();

			if ( $payment_method == $this->id ) {

				$is_confirmed = get_post_meta( $order_id, 'ck_is_confirmed', true );
				$is_refunded  = get_post_meta( $order_id, 'ck_is_refunded', true );
				$is_cancelled = get_post_meta( $order_id, 'ck_is_cancelled', true );

				if ( $is_confirmed && !$is_refunded ) {
					foreach ($wc_statuses_arr as $status_key => $status) {
						if( $status_key != 'wc-completed' ) {
							unset( $wc_statuses_arr[$status_key] );
						}
					}
				}

				if ( $is_refunded ) {
					foreach ($wc_statuses_arr as $status_key => $status) {
						if( $status_key != 'wc-refunded' ) {
							unset( $wc_statuses_arr[$status_key] );
						}
					}
				}

				if ( $is_cancelled ) {
					foreach ($wc_statuses_arr as $status_key => $status) {
						if( $status_key != 'wc-cancelled' ) {
							unset( $wc_statuses_arr[$status_key] );
						}
					}

				}
			}

		}

		return $wc_statuses_arr;
	}

	public function change_payment_gateway_title( $title, $payment_id ){
		if( $payment_id === $this->id ) {
			$title = '';
		}
		return $title;
	}

	public function change_gateway_icon( $icon, $id ) {
		if ( $id === $this->id ) {
			return '<img src="' . Main::$plugin_url . 'assets/images/ck-checkout.png' . '" alt="' . __('Pay with Credit key', 'credit_key') . '"> ';
		} else {
			return $icon;
		}
	}

}