<?php

namespace Scalexpert\Plugin\Model;

use Magento\Payment\Model\Method\AbstractMethod;

abstract class Scalexpert extends AbstractMethod
{
    protected $_infoBlockType = \Scalexpert\Plugin\Block\Payment\Info::class;
    /**
     * A flag to set that there will be redirect to third party after confirmation.
     *
     * @return bool
     */
    public function getOrderPlaceRedirectUrl()
    {
        return true;
    }
}
