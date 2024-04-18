<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Model\ResourceModel\ScalexpertContracts;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertContracts as ResourceModel;
use Scalexpert\Plugin\Model\ScalexpertContracts as Model;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_contracts_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
