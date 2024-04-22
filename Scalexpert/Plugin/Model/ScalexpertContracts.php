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

use Magento\Framework\Model\AbstractModel;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertContracts as ResourceModel;

class ScalexpertContracts extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_contracts_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
