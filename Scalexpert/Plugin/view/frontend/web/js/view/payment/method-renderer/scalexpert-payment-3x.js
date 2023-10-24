define(
    [
        'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-abstract',
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            salexpertPaymentData: window.checkoutConfig.scalexpert_payment_3x
        });
    }
);
