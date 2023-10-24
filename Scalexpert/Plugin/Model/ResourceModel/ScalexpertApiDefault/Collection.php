<?php

namespace Scalexpert\Plugin\Model\ResourceModel\ScalexpertApiDefault;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertApiDefault as ResourceModel;
use Scalexpert\Plugin\Model\ScalexpertApiDefault as Model;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_api_default_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
