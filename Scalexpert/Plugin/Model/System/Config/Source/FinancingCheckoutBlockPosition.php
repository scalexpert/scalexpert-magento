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

class FinancingCheckoutBlockPosition implements \Magento\Framework\Data\OptionSourceInterface

{
    const POS_AFTER = 'checkout.cart.view.after.scalexpert.simulate';
    const POS_BEFORE = 'checkout.cart.view.before.scalexpert.simulate';

    public function toOptionArray()
    {
        return [
            ['value' => self::POS_BEFORE, 'label' => __('Before Item')],
            ['value' => self::POS_AFTER, 'label' => __('After Item')]
        ];
    }
}
