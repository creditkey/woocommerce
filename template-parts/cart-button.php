<?php
	use CreditKey\Main;
	
	$cart_totals = WC()->cart->get_totals();
	$cart_total         = (float)$cart_totals['total'];
	$monthly_price      = get_woocommerce_currency_symbol() . number_format((($cart_total * 1.11)/12), 2);
?>
<div class="creditkey" style="margin-bottom: 15px; display: flex; justify-content: center;">
	<a href="#" id="ck-cart-link" class="ck-link" style="color: #3a3a3a;font-family: 'Open Sans', sans-serif;font-weight:400;font-size: 15px;">
		As low as <span id="money"><?php echo $monthly_price; ?></span>/month<br>Select&nbsp;<img style="height: 15px;width:auto;vertical-align:middle;margin-bottom: 2px;" src="<?php echo Main::$plugin_url . 'assets/images/credit-key-cart.svg'; ?>" />&nbsp;at checkout
	</a>
</div>