/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Ui/js/model/messageList',
    'jquery-ui-modules/effect-blind'
], function (ko, $, Component, globalMessages) {
    'use strict';
    var mixin = {
        /**
         * @param {Boolean} isHidden
         */
        onHiddenChange: function (isHidden) {
            // Hide message block if needed
            let methodPayment = this.containers[0].index;
            if (methodPayment === 'scalexpert_payment_3x'
                || methodPayment === 'scalexpert_payment_3x_with_fees'
                || methodPayment === 'scalexpert_payment_4x'
                || methodPayment === 'scalexpert_payment_4x_with_fees'
                || methodPayment === 'scalexpert_long_credit_fr'
                || methodPayment === 'scalexpert_long_credit_fr_with_fees'
                || methodPayment === 'scalexpert_long_credit_de'
                || methodPayment === 'scalexpert_long_credit_de_with_fees'
            ) {
                this.hideTimeout = 15000;
            }
            if (isHidden) {
                setTimeout(function () {
                    $(this.selector).hide('blind', {}, this.hideSpeed);
                }.bind(this), this.hideTimeout);
            }
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
