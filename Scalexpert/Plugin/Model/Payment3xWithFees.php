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

use \Scalexpert\Plugin\Model\Scalexpert;

class Payment3xWithFees extends Scalexpert
{
    /**
     * Payment Method code
     *
     * @var string
     */
    protected $_code = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X_WITH_FEES;
}
