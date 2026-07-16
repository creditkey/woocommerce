( function() {
    const registry = ( window.wc && window.wc.wcBlocksRegistry ) || ( window.wcBlocksRegistry ) || {};
    const registerPaymentMethod = registry.registerPaymentMethod || ( window.wc && window.wc.wcBlocksRegistry && window.wc.wcBlocksRegistry.registerPaymentMethod );
    const wcSettings = ( window.wc && window.wc.wcSettings ) || ( window.wcSettings );
    const settings = wcSettings && wcSettings.getSetting ? wcSettings.getSetting( 'credit_key_data', {} ) : {};

    if ( ! registerPaymentMethod || ! settings ) {
        return;
    }

    const Label = () => {
        if ( settings.icon ) {
            return wp.element.createElement(
                'img',
                { src: settings.icon, alt: settings.ariaLabel || 'Credit Key', className: 'ck-block-icon' }
            );
        }
        return wp.element.createElement( 'span', { className: 'ck-label' }, settings.ariaLabel || 'Credit Key' );
    };

    const Content = () => {
        return wp.element.createElement( 'div', { className: 'ck-content' }, settings.description || '' );
    };

    registerPaymentMethod( {
        name: 'credit_key',
        label: wp.element.createElement( Label ),
        content: wp.element.createElement( Content ),
        edit: wp.element.createElement( Content ),
        canMakePayment: ( { cartTotals } ) => {
            if ( ! settings.isEligible ) return false;
            const minorUnit = Number.isFinite( Number( cartTotals.currency_minor_unit ) )
                ? Number( cartTotals.currency_minor_unit )
                : 2;
            const cartTotal = parseInt( cartTotals.total_price, 10 ) / Math.pow( 10, minorUnit );
            return cartTotal > ( settings.minCheckout || 0 );
        },
        ariaLabel: settings.ariaLabel || 'Credit Key',
        placeOrderButtonLabel: settings.buttonLabel || 'Continue with Credit Key',
        icons: settings.icon ? [ settings.icon ] : [],
        supports: {
            features: [ 'products', 'refunds' ]
        },
    } );
} )();
