<?php

namespace Scalexpert\Plugin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ScalexpertContracts extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_contracts_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('scalexpert_contracts', 'id');
        $this->_useIsObjectNew = true;
    }
}
