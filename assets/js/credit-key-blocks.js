( function() {
    const registry = ( window.wc && window.wc.wcBlocksRegistry ) || ( window.wcBlocksRegistry ) || {};
    const registerPaymentMethod = registry.registerPaymentMethod || ( window.wc && window.wc.wcBlocksRegistry && window.wc.wcBlocksRegistry.registerPaymentMethod );
    const wcSettings = ( window.wc && window.wc.wcSettings ) || ( window.wcSettings );
    const settings = wcSettings && wcSettings.getSetting ? wcSettings.getSetting( 'credit_key_data', {} ) : {};

    if ( ! registerPaymentMethod || ! settings ) {
        return;
    }

    const fallbackTitle = 'Credit Key';
    const placeOrderButtonLabel = settings.placeOrderButtonLabel || 'Continue with Credit Key';

    const Label = () => {
        if ( settings.icon ) {
            return wp.element.createElement(
                'span',
                { className: 'ck-label ck-label--image-only' },
                wp.element.createElement(
                    'img',
                    { src: settings.icon, alt: fallbackTitle, className: 'ck-block-icon' }
                )
            );
        }

        return wp.element.createElement( 'span', { className: 'ck-label' }, fallbackTitle );
    };

    const Content = () => {
        return wp.element.createElement( 'div', { className: 'ck-content' }, settings.description || '' );
    };

    registerPaymentMethod( {
        name: 'credit_key',
        label: wp.element.createElement( Label ),
        content: wp.element.createElement( Content ),
        edit: wp.element.createElement( Content ),
        canMakePayment: () => !! settings.isEligible,
        ariaLabel: fallbackTitle,
        placeOrderButtonLabel,
        icons: [],
        supports: {
            features: [ 'products', 'refunds' ]
        },
    } );
} )();
