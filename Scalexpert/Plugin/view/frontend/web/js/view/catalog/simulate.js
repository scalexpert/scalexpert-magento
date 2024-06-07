/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Catalog/js/price-utils'
], function ($, Component, priceUtils) {
    'use strict';
    const entries = Object.entries(window.scalexepert);

    return Component.extend({
        defaults: {
            template: 'Scalexpert_Plugin/simulate/product',
            selectedOption : entries[0][0] ? entries[0][0] : null,
        },
        initialize: function () {
            this._super();
            this.selectedOption = entries[0][0];
            this.observe('selectedOption');
        },

        // Données Méthode de paiement
        getVisualTitle: function (data) {
            return window.scalexepert[data]['merchantkit']['visualTitle'].replace(/(<([^>]+)>)/gi, "");
        },
        getVisualInformationIcon: function (data) {
            return window.scalexepert[data]['merchantkit']['visualInformationIcon'];
        },
        getVisualLogo: function (data) {
            return window.scalexepert[data]['merchantkit']['visualLogo'];
        },
        getVisualLogoIsEnabled: function (data) {
            return window.scalexepert[data]['merchantkit']['magentoConfiguration']['logo'];
        },
        getVisualDescription: function (data) {
            return window.scalexepert[data]['merchantkit']['visualDescription'];
        },
        getVisualAdditionalInformation: function (data) {
            return window.scalexepert[data]['merchantkit']['visualAdditionalInformation'];
        },
        getVisualLegalText: function (data) {
            return window.scalexepert[data]['merchantkit']['visualLegalText'];
        },
        getDataAll: function () {
            return Object.entries(window.scalexepert);
        },

        // Données Simulation
        getDuration: function (data) {
            return window.scalexepert[data]['simulations']['duration'];
        },
        getRemainingDuration: function (data) {
            return this.getDuration(data) - 1;
        },
        getInitialAmount: function (data) {
            return this.getFormattedPrice(window.scalexepert[data]['simulations']['dueTotalAmount'] - window.scalexepert[data]['simulations']['totalCost']);
        },
        getEffectiveAnnualPercentageRate: function (data) {
            return this.getFormattedPercent(window.scalexepert[data]['simulations']['effectiveAnnualPercentageRate']);
        },
        getNominalPercentageRate: function (data) {
            return this.getFormattedPercent(window.scalexepert[data]['simulations']['nominalPercentageRate']);
        },
        getTotalCost: function (data) {
            return this.getFormattedPrice(window.scalexepert[data]['simulations']['totalCost']);
        },
        getFeesAmount: function (data) {
            return this.getFormattedPrice(window.scalexepert[data]['simulations']['feesAmount']);
        },
        getDueTotalAmount: function (data) {
            return this.getFormattedPrice(window.scalexepert[data]['simulations']['dueTotalAmount']);
        },
        getAmount: function (month, data, formatted) {
            let price = window.scalexepert[data]['simulations']['installments'][month]['amount'];
            if (formatted) {
                return this.getFormattedPrice(price);
            }
            return price;
        },
        getMultipleInstallment: function (data) {
            return window.scalexepert[data]['simulations']['installments'].length > 1 && window.scalexepert[data]['simulations']['installments'][0]['amount'] !== window.scalexepert[data]['simulations']['installments'][1]['amount'];
        },
        getInstallments: function (data) {
            let installments = [];
            window.scalexepert[data]['simulations']['installments'].forEach((item, index) => {
                installments[index] = [];
                installments[index]['label'] = this.getLabelInstallment(item['number']);
                installments[index]['amount'] = this.getFormattedPrice(item['amount']);
            });
            return installments;
        },
        isBankCard: function (data) {
            let creditCodes = ['SCFRLT-TXNO', 'SCFRLT-TXPS', 'SCDELT-DXCO', 'SCDELT-DXTS'];
            let result = $.inArray(window.scalexepert[data]['merchantkit']['solutionCode'], creditCodes);
            return result < 0;
        },

        // Formattage
        getLabelInstallment: function (data) {
            if(data === 1) {
                return 'Aujourd’hui';
            }
            else if(data === 2) {
                return '2ème prélèvement';
            }
            else if(data === 3) {
                return '3ème prélèvement';
            }
            else if(data === 4) {
                return '4ème prélèvement';
            }
        },
        getFormattedPrice: function (price) {
            let priceFormat = {
                decimalSymbol: ',',
                groupLength: 3,
                groupSymbol: " ",
                integerRequired: false,
                pattern: "%s €",
                precision: 2,
                requiredPrecision: 2
            };
            return priceUtils.formatPrice(price, priceFormat);
        },
        getFormattedPercent: function (percent) {
            let percentFormat = {
                decimalSymbol: ',',
                groupLength: 3,
                groupSymbol: " ",
                integerRequired: false,
                pattern: "%s %",
                precision: 2,
                requiredPrecision: 2
            };
            return priceUtils.formatPrice(percent, percentFormat);
        },

    });
});
