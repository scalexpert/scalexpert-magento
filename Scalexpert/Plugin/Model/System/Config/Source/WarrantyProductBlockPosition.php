<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Model\System\Config\Source;

class WarrantyProductBlockPosition implements \Magento\Framework\Data\OptionSourceInterface

{
    const POS_BEFORE_QTY = 'catalog.product.view.before.qty.scalexpert.warranty.insert';
    const POS_AFTER_ADDTOCART = 'catalog.product.view.after.addtocart.scalexpert.warranty.insert';

    public function toOptionArray()
    {
        return [
            ['value' => SELF::POS_BEFORE_QTY, 'label' => __('Before Product Qty')],
            ['value' => SELF::POS_AFTER_ADDTOCART, 'label' => __('After Product AddToCart Button')]
        ];
    }
}
