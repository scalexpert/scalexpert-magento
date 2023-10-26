<?php

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
