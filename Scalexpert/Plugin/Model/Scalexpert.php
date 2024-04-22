<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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
