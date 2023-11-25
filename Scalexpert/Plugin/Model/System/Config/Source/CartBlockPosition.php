<?php

namespace Scalexpert\Plugin\Model\System\Config\Source;

class CartBlockPosition implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('To define')
            ],
            [
                'value' => 'left',
                'label' => __('Left')
            ]
        ];
    }
}
