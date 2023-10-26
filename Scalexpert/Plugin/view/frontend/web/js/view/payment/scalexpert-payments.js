define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'scalexpert_payment_3x',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-payment-3x'
            },
            {
                type: 'scalexpert_payment_4x',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-payment-4x'
            },
            {
                type: 'scalexpert_long_credit_fr',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-long-credit-fr'
            },
            {
                type: 'scalexpert_long_credit_de',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-long-credit-de'
            }
        );
        return Component.extend({});
    }
);
