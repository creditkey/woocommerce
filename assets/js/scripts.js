jQuery(document).ready(function ($) {
    $('body').on('click', '#ck-cart-link', function (e) {
        e.preventDefault();
        if (typeof ck === 'undefined' || typeof client === 'undefined') {
            return;
        }
        var data = new FormData();
        data.append('action', 'get_cart_data');
        $.ajax({
            url: CreditKey.ajax_url,
            type: 'post',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {
                let cart_subtotal = Number(data.cart_subtotal);
                let cart_tax_total = Number(data.cart_tax_total);
                let cart_discount_total = Number(data.cart_discount_total);
                let cart_shipping_total = Number(data.cart_shipping_total);
                let cart_total = Number(data.cart_total);
                let modalPdp = document.getElementById('modal-pdp');
                let charges = new ck.Charges(cart_subtotal, cart_shipping_total, cart_tax_total, cart_discount_total, cart_total);
                client.enhanced_pdp_modal(charges);
            },
        });
    });

    var current_amount = $('.price-rule-active').data('price-rules-amount');
    var price_table = $('.price-rules-table');
    
    if (price_table.length) {
        $('.qty').change(function () {
    
            let price = $('.price-rule-active').data('price-rules-price');
            let amount = $('.price-rule-active').data('price-rules-amount');
    
            if (current_amount != amount) {
                price = price.replace(/\s/g, '');
                current_amount = amount;
                if (typeof ck === 'undefined' || typeof client === 'undefined') {
                    return;
                }
                let charges = new ck.Charges(price, 0, 0, 0, price);
                var pdp = document.getElementById('pdp');
                if (pdp) {
                    pdp.innerHTML = client.get_pdp_display(charges);
                }
            }
    
        });
    }
    

});