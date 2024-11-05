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
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/full-screen-loader',
    ],
    function($, Component, url, priceUtils, totals, fullScreenLoader) {
        'use strict';

        return Component.extend({

            // Données Méthode de paiement
            isBankCard: function () {
                return this.isBankcard;
            },
            getVisualTitle: function () {
                return ((this.salexpertPaymentData.customTitle && this.salexpertPaymentData.customTitle.length>1)?this.salexpertPaymentData.customTitle.replace(/(<([^>]+)>)/gi, ""):(this.salexpertPaymentData.visualTitle)?this.salexpertPaymentData.visualTitle.replace(/(<([^>]+)>)/gi, ""):'');
            },
            getVisualInformationIcon: function () {
                return this.salexpertPaymentData.visualInformationIcon;
            },
            getVisualLogo: function () {
                return this.salexpertPaymentData.visualLogo;
            },
            getSubtitle: function () {
                return ((this.salexpertPaymentData.customSubtitle && this.salexpertPaymentData.customSubtitle.length>1)?this.salexpertPaymentData.customSubtitle.replace(/(<([^>]+)>)/gi, ""):(this.salexpertPaymentData.visualDescription)?this.salexpertPaymentData.visualDescription.replace(/(<([^>]+)>)/gi, ""):'');
            },
            getVisualAdditionalInformation: function () {
                return this.salexpertPaymentData.visualAdditionalInformation;
            },
            getVisualLegalText: function () {
                return this.salexpertPaymentData.visualLegalText;
            },
            getMensualitesSimple: function () {
                let key = Object.keys(this.getSimulation())[0];
                let month1 = this.getAmount(0, key, false);
                let month2 = this.getAmount(1, key, false);
                if(month1 === month2) {
                    return '<b>soit ' + this.getFormattedPrice(month1) + '/mois</b>';
                }
            },
            getMensualites: function () {
                let key = Object.keys(this.getSimulation())[0];
                let month1 = this.getAmount(0, key, false);
                let month2 = this.getAmount(1, key, false);
                if(month1 !== month2) {
                    return 'soit un <b>1er prélèvement de ' + this.getFormattedPrice(month1) + '</b> (frais inclus) puis <b>' + (key - 1) + ' prélèvements de ' + this.getFormattedPrice(month2) + '</b>';
                }
            },
            getSimulation: function () {
                return this.salexpertPaymentData.simulate;
            },
            getDataAll: function () {
                return Object.entries(this.salexpertPaymentData.simulate);
            },

            // Données Simulation
            getDuration: function (data) {
                return data - 1;
            },
            getInitialAmount: function (data) {
                return this.getFormattedPrice(this.salexpertPaymentData.simulate[data]['simulations']['dueTotalAmount'] - this.salexpertPaymentData.simulate[data]['simulations']['totalCost']);
            },
            getEffectiveAnnualPercentageRate: function (data) {
                return this.getFormattedPercent(this.salexpertPaymentData.simulate[data]['simulations']['effectiveAnnualPercentageRate']);
            },
            getNominalPercentageRate: function (data) {
                return this.getFormattedPercent(this.salexpertPaymentData.simulate[data]['simulations']['nominalPercentageRate']);
            },
            getTotalCost: function (data) {
                return this.getFormattedPrice(this.salexpertPaymentData.simulate[data]['simulations']['totalCost']);
            },
            getFeesAmount: function (data) {
                return this.getFormattedPrice(this.salexpertPaymentData.simulate[data]['simulations']['feesAmount']);
            },
            getDueTotalAmount: function (data) {
                return this.getFormattedPrice(this.salexpertPaymentData.simulate[data]['simulations']['dueTotalAmount']);
            },
            getAmount: function (month, data, formatted) {
                let price = this.salexpertPaymentData.simulate[data]['simulations']['installments'][month]['amount'];
                if (formatted) {
                    return this.getFormattedPrice(price);
                }
                return price;
            },
            getMultipleInstallment: function (data) {
                return this.salexpertPaymentData.simulate[data]['simulations']['installments'].length > 1 && this.salexpertPaymentData.simulate[data]['simulations']['installments'][0]['amount'] !== this.salexpertPaymentData.simulate[data]['simulations']['installments'][1]['amount'];
            },
            getInstallments: function (data) {
                let installments = [];
                this.salexpertPaymentData.simulate[data]['simulations']['installments'].forEach((item, index) => {
                    installments[index] = [];
                    installments[index]['label'] = this.getLabelInstallment(item['number']);
                    installments[index]['amount'] = this.getFormattedPrice(item['amount']);
                });
                return installments;
            },

            // Formattage
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
            getLabelInstallment: function (number) {
                if (number === 1) {
                    return 'Aujourd’hui';
                } else if(number === 2) {
                    return '2ème prélèvement';
                } else if(number === 3) {
                    return '3ème prélèvement';
                } else if(number === 4) {
                    return '4ème prélèvement';
                }
            },

            afterPlaceOrder: function() {
                $.mage.redirect(url.build('scalexpert/payment/redirect'));
            },

            getNeedUpdate: function (data) {
                let init = this.getFormattedPrice(this.salexpertPaymentData.simulate[data]['simulations']['dueTotalAmount'] - this.salexpertPaymentData.simulate[data]['simulations']['totalCost']);
                let tot = this.getFormattedPrice(totals.getSegment('grand_total').value);
                if(init !== tot){
                    fullScreenLoader.startLoader();
                    jQuery(document).ready(function() {
                    window.location.reload();
                    });
                }
                return true;
            },
        });
    }
);
