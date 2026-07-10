( function() {
    const registry = ( window.wc && window.wc.wcBlocksRegistry ) || ( window.wcBlocksRegistry ) || {};
    const registerPaymentMethod = registry.registerPaymentMethod || ( window.wc && window.wc.wcBlocksRegistry && window.wc.wcBlocksRegistry.registerPaymentMethod );
    const wcSettings = ( window.wc && window.wc.wcSettings ) || ( window.wcSettings );
    const settings = wcSettings && wcSettings.getSetting ? wcSettings.getSetting( 'credit_key_data', {} ) : {};

    if ( ! registerPaymentMethod || ! settings ) {
        return;
    }

    const Label = () => {
        const children = [ settings.title || 'Credit Key' ];
        if ( settings.icon ) {
            children.push(
                wp.element.createElement(
                    'img',
                    { key: 'ck-icon', src: settings.icon, alt: settings.title || 'Credit Key', className: 'ck-block-icon' }
                )
            );
        }
        return wp.element.createElement( 'span', { className: 'ck-label' }, children );
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
        ariaLabel: settings.title || 'Credit Key',
        icons: settings.icon ? [ settings.icon ] : [],
        supports: {
            features: [ 'products', 'refunds' ]
        },
    } );
} )();


