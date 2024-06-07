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
        'Scalexpert_Plugin/js/view/payment/method-renderer/scalexpert-abstract',
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            salexpertPaymentData: window.checkoutConfig.scalexpert_long_credit_de_with_fees,
            isBankcard: false,
            defaults: {
                template: 'Scalexpert_Plugin/payment/default',// todo change with old template
                redirectAfterPlaceOrder: false,
                // selectedOption : Object.entries(window.checkoutConfig.scalexpert_long_credit_de_with_fees.simulate)[0][0] ? Object.entries(window.checkoutConfig.scalexpert_long_credit_de_with_fees.simulate)[0][0] : null,
            },
            initialize: function () {
                this._super();
                // this.selectedOption = Object.entries(window.checkoutConfig.scalexpert_long_credit_de_with_fees.simulate)[0][0];
                // this.observe('selectedOption');
            }
        });
    }
);
