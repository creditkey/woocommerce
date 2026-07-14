jQuery(document).ready(function ($) {
    if (!window.creditKeyModalModeData) {
        return;
    }

    const settings = window.creditKeyModalModeData;

    const $body = $('body')
    const $form = $('form[name="checkout"]')

    const blockStyles = {
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6,
        },
    };

    $body.on('click', 'button[name="woocommerce_checkout_place_order"]', function (e) {
        if (!isGatewaySelected(settings.gateway_id)) {
            return;
        }

        e.preventDefault();

        $form.block(blockStyles)

        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: window.wc_checkout_params.checkout_url,
            data: $form.serialize(),
            beforeSend: function (xhr) {

            },
            success: function (res) {
                if ('success' === res.result) {
                    ck.checkout(res.redirect)

                    const ckContainer = document.querySelector('#creditkey-iframe');

                    if( ckContainer ) {
                        ckContainer.scrolling = '';
                        setInterval(() => ckContainer.style.height = 'calc(100vh - 100px)') //fux bug with modal height
                    }

                } else {
                    alert(res.message);
                }
            },
            error: function (request, status, error) {
                console.error(request, status, error);
                alert('Request error. Try again or contact us')
            },
            complete: function () {
                $form.unblock()
            },
        })
    })

    /**
     * Get gateway radio input in list of gateways
     *
     * @param {string} gateway_id
     *
     * @returns string
     */
    function getGarewayRadioSelector(gateway_id) {
        return 'input#payment_method_' + gateway_id;
    }

    function getGatewayRadio(gateway_id) {
        return jQuery(getGarewayRadioSelector(gateway_id));
    }

    /**
     * Check if gateway selected by gateway_id
     *
     * @param {string} gateway_id
     *
     * @returns boolean
     */
    function isGatewaySelected(gateway_id) {
        return getGatewayRadio(gateway_id).prop('checked')
    }
})