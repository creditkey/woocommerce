jQuery(document).ready(function($){

    let is_test = $('#woocommerce_credit_key_is_test').prop('checked');
    console.log(is_test);
    snow_plugin_options(is_test);

    $('#woocommerce_credit_key_is_test').on('change', function (){
        let is_test = $(this).prop('checked');
        snow_plugin_options(is_test);
    });

    function snow_plugin_options(is_test){
        if( is_test ){
            $('#woocommerce_credit_key_public_key').parents('tr').hide();
            $('#woocommerce_credit_key_shared_secret').parents('tr').hide();
            $('#woocommerce_credit_key_api_url').parents('tr').hide();

            $('#woocommerce_credit_key_test_public_key').parents('tr').show();
            $('#woocommerce_credit_key_test_shared_secret').parents('tr').show();
            $('#woocommerce_credit_key_test_api_url').parents('tr').show();
        } else {
            $('#woocommerce_credit_key_public_key').parents('tr').show();
            $('#woocommerce_credit_key_shared_secret').parents('tr').show();
            $('#woocommerce_credit_key_api_url').parents('tr').show();

            $('#woocommerce_credit_key_test_public_key').parents('tr').hide();
            $('#woocommerce_credit_key_test_shared_secret').parents('tr').hide();
            $('#woocommerce_credit_key_test_api_url').parents('tr').hide();
        }
    }
});