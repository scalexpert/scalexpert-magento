<?php

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
