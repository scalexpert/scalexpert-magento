<?php

namespace Scalexpert\Plugin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ScalexpertApiDefault extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_api_default_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('scalexpert_api_default', 'id');
        $this->_useIsObjectNew = true;
    }
}
