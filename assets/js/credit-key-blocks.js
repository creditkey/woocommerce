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
        canMakePayment: ( { cart } ) => {
            if ( ! settings.isEligible ) return false;
            const cartTotal = parseInt( cart.cartTotals.total_price, 10 ) / 100;
            return cartTotal > ( settings.minCheckout || 0 );
        },
        ariaLabel: settings.ariaLabel || 'Credit Key',
        icons: settings.icon ? [ settings.icon ] : [],
        supports: {
            features: [ 'products', 'refunds' ]
        },
    } );
} )();

