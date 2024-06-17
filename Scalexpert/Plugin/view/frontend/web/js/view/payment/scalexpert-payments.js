/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

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
                type: 'scalexpert_payment_3x_with_fees',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-payment-3x-with-fees'
            },
            {
                type: 'scalexpert_payment_4x',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-payment-4x'
            },
            {
                type: 'scalexpert_payment_4x_with_fees',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-payment-4x-with-fees'
            },
            {
                type: 'scalexpert_long_credit_fr',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-long-credit-fr'
            },
            {
                type: 'scalexpert_long_credit_fr_with_fees',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-long-credit-fr-with-fees'
            },
            {
                type: 'scalexpert_long_credit_de',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-long-credit-de'
            },
            {
                type: 'scalexpert_long_credit_de_with_fees',
                component: 'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-long-credit-de-with-fees'
            }
        );
        return Component.extend({});
    }
);
