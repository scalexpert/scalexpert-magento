<?php

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
