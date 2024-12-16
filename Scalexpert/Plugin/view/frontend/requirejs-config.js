/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

var config = {
    config: {
        mixins: {
            'Magento_Ui/js/view/messages': {
                'Scalexpert_Plugin/js/view/messages-mixin': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Scalexpert_Plugin/js/swatch-renderer-mixin': true
            }

        }
    }
};
