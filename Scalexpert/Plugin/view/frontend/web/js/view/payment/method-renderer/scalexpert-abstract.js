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
        'mage/url'
    ],
    function($, Component, url) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Scalexpert_Plugin/payment/default'
            },
            afterPlaceOrder: function() {
                $.mage.redirect(url.build('scalexpert/payment/redirect'));
            },

            getTitle: function () {
                return ((this.salexpertPaymentData.customTitle && this.salexpertPaymentData.customTitle.length>1)?this.salexpertPaymentData.customTitle:this.salexpertPaymentData.visualTitle);
            },

            getSubtitle: function () {
                return ((this.salexpertPaymentData.customSubtitle && this.salexpertPaymentData.customSubtitle.length>1)?this.salexpertPaymentData.customSubtitle:this.salexpertPaymentData.visualDescription);
            },

            getVisualInformationIcon: function () {
                return this.salexpertPaymentData.visualInformationIcon;
            },

            getVisualAdditionalInformation: function () {
                return this.salexpertPaymentData.visualAdditionalInformation;
            },

            getVisualLegalText: function () {
                return this.salexpertPaymentData.visualLegalText;
            },

            getVisualTableImage: function () {
                return this.salexpertPaymentData.visualTableImage;
            },

            getVisualLogo: function () {
                return this.salexpertPaymentData.visualLogo;
            },

            getVisualInformationNoticeURL: function () {
                return this.salexpertPaymentData.visualInformationNoticeURL;
            },

            getVisualProductTermsURL: function () {
                return this.salexpertPaymentData.visualProductTermsURL;
            },
            getVisualUmbrella: function () {
                return require.toUrl('Scalexpert_Plugin/images/umbrella.svg');
            },
            getVisualEmprunt: function () {
                return require.toUrl('Scalexpert_Plugin/images/emprunt.svg');
            }

        });
    }
);
