<?php

namespace Scalexpert\Plugin\Model\System\Config\Source;

class Payment4XProductBlockPosition implements \Magento\Framework\Data\OptionSourceInterface

{
    const POS_AFTER_TITLE = 'catalog.product.view.after.title.scalexpert.payment.4x.insert';
    const POS_BEFORE_QTY = 'catalog.product.view.before.qty.scalexpert.payment.4x.insert';
    const POS_AFTER_ADDTOCART = 'catalog.product.view.after.addtocart.scalexpert.payment.4x.insert';

    public function toOptionArray()
    {
        return [
            ['value' => SELF::POS_AFTER_TITLE, 'label' => __('After Product Title')],
            ['value' => SELF::POS_BEFORE_QTY, 'label' => __('Before Product Qty')],
            ['value' => SELF::POS_AFTER_ADDTOCART, 'label' => __('After Product AddToCart Button')]
        ];
    }
}
