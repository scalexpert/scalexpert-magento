<?php

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
