jQuery(document).ready(function($){

    let is_test = $('#woocommerce_credit_key_is_test').prop('checked');
    console.log(is_test);
    snow_plugin_options(is_test);

    $('#woocommerce_credit_key_is_test').on('change', function (){
        let is_test = $(this).prop('checked');
        snow_plugin_options(is_test);
    });
    
    wp.editor.initialize('woocommerce_credit_key_promo_message', {
        tinymce: {
		wpautop  : true,
		theme    : 'modern',
		skin     : 'lightgray',
		formats  : {
			alignleft  : [
				{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'left' } },
				{ selector: 'img,table,dl.wp-caption', classes: 'alignleft' }
			],
			aligncenter: [
				{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'center' } },
				{ selector: 'img,table,dl.wp-caption', classes: 'aligncenter' }
			],
			alignright : [
				{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'right' } },
				{ selector: 'img,table,dl.wp-caption', classes: 'alignright' }
			],
			strikethrough: { inline: 'del' }
		},
		relative_urls       : false,
		remove_script_host  : false,
		convert_urls        : false,
		browser_spellcheck  : true,
		fix_list_elements   : true,
		entities            : '38,amp,60,lt,62,gt',
		entity_encoding     : 'raw',
		keep_styles         : false,
		paste_webkit_styles : 'font-weight font-style color',
		preview_styles      : 'font-family font-size font-weight font-style text-decoration text-transform',
		tabfocus_elements   : ':prev,:next',
		plugins    : 'charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview',
		resize     : 'vertical',
		menubar    : false,
		indent     : false,
		toolbar1   : 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
		toolbar2   : 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
		toolbar3   : '',
		toolbar4   : '',
		body_class : 'id post-type-post post-status-publish post-format-standard',
		wpeditimage_disable_captions: false,
		wpeditimage_html5_captions  : true
	},
        quicktags: true,
        mediaButtons: true
    });
    
    function snow_plugin_options(is_test){
        if( is_test ){
            $('#woocommerce_credit_key_public_key').parents('tr').hide();
            $('#woocommerce_credit_key_shared_secret').parents('tr').hide();

            $('#woocommerce_credit_key_test_public_key').parents('tr').show();
            $('#woocommerce_credit_key_test_shared_secret').parents('tr').show();
        } else {
            $('#woocommerce_credit_key_public_key').parents('tr').show();
            $('#woocommerce_credit_key_shared_secret').parents('tr').show();

            $('#woocommerce_credit_key_test_public_key').parents('tr').hide();
            $('#woocommerce_credit_key_test_shared_secret').parents('tr').hide();
        }
    }
});