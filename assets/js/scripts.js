jQuery(document).ready(function($){
    $('body').on('click', '#ck-cart-link', function (e){
        e.preventDefault();
        var data = new FormData();
        data.append('action', 'get_cart_data');
        $.ajax({
            url: CreditKey.ajax_url,
            type: 'post',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                let cart_subtotal      = Number(data.cart_subtotal);
                let cart_tax_total     = Number(data.cart_tax_total);
                let cart_discount_total= Number(data.cart_discount_total);
                let cart_shipping_total= Number(data.cart_shipping_total);
                let cart_total         = Number(data.cart_total);
                let modalPdp = document.getElementById('modal-pdp');
                let charges = new ck.Charges(cart_subtotal, cart_shipping_total, cart_tax_total, cart_discount_total, cart_total);
                client.enhanced_pdp_modal(charges);
            },
        });
    });
});