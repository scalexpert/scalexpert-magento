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
    const entries = Object.entries(window.scalexepertWarranty);

    return Component.extend({
        defaults: {
            template: 'Scalexpert_Plugin/insurance/product',
        },
        initialize: function () {
            this._super();
        },

        getTitle: function () {
            return window.scalexepertWarranty['title'];
        },
        getSubTitle: function () {
            return window.scalexepertWarranty['sub_title'];
        },
        getCode: function () {
            return window.scalexepertWarranty['code'];
        },
        getPictoInfo: function () {
            return window.scalexepertWarranty['picto_info'];
        },
        getLogo: function () {
            return window.scalexepertWarranty['logo'];
        },
        getInfoTerms: function () {
            return window.scalexepertWarranty['terms'];
        },
        getInfoNotice: function () {
            return window.scalexepertWarranty['notice'];
        },
        getAdditionalText: function () {
            return window.scalexepertWarranty['additional'];
        },
        getLegalText: function () {
            return window.scalexepertWarranty['legal_text'];
        },
        getInsurances: function () {
            let insurances = [];
            window.scalexepertWarranty['insurances'].forEach((item, index) => {
                insurances[index] = [];
                insurances[index]['id'] = 'insurance_' + item['insurance_id'];
                insurances[index]['code'] = this.getCode() + '|' + item['insurance_id'];
                insurances[index]['label'] = item['description'] + ' (' + this.getFormattedPrice(item['price']) + ')';
            });
            return insurances;
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

    });
});
