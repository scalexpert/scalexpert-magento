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
    'mage/url',
    'jquery-ui-modules/widget'
], function ($,urlBuilder) {
    'use strict';

    return function (SwatchRenderer) {
        $.widget('mage.SwatchRenderer', SwatchRenderer, {

            /** @inheritdoc */
            _OnClick: function ($this, widget) {
                let oldPrice = $('.price')[0].innerText

                this._super($this, widget);

                let newPrice = $('.price')[0].innerText
                let productId = $('[name=product]').val();

                if (oldPrice !== newPrice) {
                    let price = newPrice
                    this.getAjaxSimulate(productId, price)
                    this.getAjaxWarranty(productId, price)
                }
            },
            _OnChange: function ($this, widget) {
                let oldPrice = $('.price')[0].innerText

                this._super($this, widget);

                let newPrice = $('.price')[0].innerText
                let productId = $('[name=product]').val();

                if (oldPrice !== newPrice) {
                    let price = newPrice
                    this.getAjaxSimulate(productId, price)
                    this.getAjaxWarranty(productId, price)
                }
            },
            getAjaxSimulate: function (productId, price) {
                let url = urlBuilder.build('scalexpert/ajax/index');

                let simulate_div =  $('.simulate_block');

                $.ajax({
                    url: url,
                    data: {
                        product_id: productId,
                        price: price,
                    },
                    success: function (data) {
                        simulate_div.empty();
                        $('.scalexpert-modal').remove();
                        $('.modals-overlay').removeClass('modals-overlay');
                        simulate_div.append(data.result);
                        simulate_div.first().trigger('contentUpdated');
                        if ($.fn.applyBindings !== undefined) {
                            $('#simulate').applyBindings();
                        }
                    },
                    error: function (err) {
                        console.error('scalexpert simulation render error', err);
                    }
                });
            },
            getAjaxWarranty: function (productId, price) {
                let url = urlBuilder.build('scalexpert/ajax/warranty');
                let warranty_div =  $('.warranty_block');

                $.ajax({
                    url: url,
                    data: {
                        product_id: productId,
                        price: price,
                    },
                    beforeSend: function () {
                        $('body').trigger('processStart');
                    },
                    success: function (data) {
                        warranty_div.empty();
                        warranty_div.append(data.result);
                        warranty_div.first().trigger('contentUpdated');
                        if ($.fn.applyBindings !== undefined) {
                            $('#warranty').applyBindings();
                        }
                        $('body').trigger('processStop');
                    },
                    error: function (err) {
                        console.error('scalexpert warranty render error', err);
                        $('body').trigger('processStop');
                    }
                });
            }
        });

        return $.mage.SwatchRenderer;
    };
});
