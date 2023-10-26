<?php

namespace Scalexpert\Plugin\Model;

use \Scalexpert\Plugin\Model\Scalexpert;

class Payment3x  extends Scalexpert
{
    /**
     * Payment Method code
     *
     * @var string
     */
    protected $_code = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X;
}
