/**
 * Credit Key cart messaging for the WooCommerce Cart block.
 *
 * The classic `woocommerce_after_cart_totals` hook never fires for the
 * `wp:woocommerce/cart` block, so the messaging is rendered client-side here:
 * it waits for the cart block to mount, reads live totals from the
 * `wc/store/cart` data store, and re-renders whenever those totals change.
 */
(function () {
    function onReady(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    onReady(function () {
        var cfg = window.CreditKey || {};

        if (cfg.cartEnabled !== 'yes' || typeof ck === 'undefined') {
            return;
        }

        // Only handle the Cart block. The classic cart prints `#cartbanner`
        // server-side; bail out to avoid double-rendering.
        var cartBlock = document.querySelector('.wp-block-woocommerce-cart, .wc-block-cart');
        if (!cartBlock || document.getElementById('cartbanner')) {
            return;
        }

        var selector = cfg.cartSelector || '.wc-block-cart__submit';
        var minCart = parseFloat(cfg.minCart) || 0;
        var desktop = cfg.cartAlignmentDesktop || 'center';
        var mobile = cfg.cartAlignmentMobile || 'center';
        var client = new ck.Client(cfg.publicKey, cfg.environment);
        var bannerId = 'ck-cart-block-banner';
        var lastTotal = null;

        // Read the grand total (major units) from the WooCommerce Blocks store.
        function getCartTotal() {
            if (!window.wp || !wp.data || !wp.data.select) {
                return null;
            }
            var store = wp.data.select('wc/store/cart');
            if (!store) {
                return null;
            }
            var totals = typeof store.getCartTotals === 'function'
                ? store.getCartTotals()
                : (typeof store.getCartData === 'function' ? store.getCartData().totals : null);
            if (!totals || totals.total_price == null) {
                return null;
            }
            var minorUnit = totals.currency_minor_unit != null ? totals.currency_minor_unit : 2;
            return parseInt(totals.total_price, 10) / Math.pow(10, minorUnit);
        }

        function getBanner() {
            var banner = document.getElementById(bannerId);
            if (banner) {
                return banner;
            }
            var target = null;
            try {
                target = document.querySelector(selector);
            } catch (e) {
                target = document.querySelector('.wc-block-cart__submit');
            }
            if (!target || !target.parentNode) {
                return null;
            }
            banner = document.createElement('div');
            banner.id = bannerId;
            target.parentNode.insertBefore(banner, target);
            return banner;
        }

        function render() {
            var total = getCartTotal();
            if (total === null) {
                return;
            }
            var banner = getBanner();
            if (!banner) {
                return;
            }
            if (total < minCart) {
                banner.innerHTML = '';
                lastTotal = total;
                return;
            }
            // Skip re-render when nothing changed and the banner is still mounted.
            if (total === lastTotal && banner.innerHTML) {
                return;
            }
            lastTotal = total;
            var charges = new ck.Charges(total, 0, 0, 0, total);
            banner.innerHTML = client.get_cart_display(charges, desktop, mobile);
        }

        // Coalesce the frequent store/DOM notifications into one render per frame.
        var scheduled = false;
        function schedule() {
            if (scheduled) {
                return;
            }
            scheduled = true;
            (window.requestAnimationFrame || window.setTimeout)(function () {
                scheduled = false;
                render();
            });
        }

        // Re-render on cart total changes (quantity edits, item removal, etc.).
        if (window.wp && wp.data && typeof wp.data.subscribe === 'function') {
            wp.data.subscribe(schedule);
        }
        // Re-insert the banner if the block re-renders and drops it from the DOM.
        if (typeof MutationObserver === 'function') {
            new MutationObserver(schedule).observe(cartBlock, { childList: true, subtree: true });
        }

        render();
    });
})();
