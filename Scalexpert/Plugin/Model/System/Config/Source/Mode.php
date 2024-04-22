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

class Mode implements \Magento\Framework\Data\OptionSourceInterface
{
    const MODE_TEST = 'TEST';
    const MODE_PRODUCTION = 'PRODUCTION';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::MODE_TEST,
                'label' => __('TEST')
            ],
            [
                'value' => self::MODE_PRODUCTION,
                'label' => __('PRODUCTION')
            ]
        ];
    }
}
