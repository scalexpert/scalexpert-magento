<?php

namespace Scalexpert\Plugin\Model;

use Magento\Framework\Model\AbstractModel;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertApiDefault as ResourceModel;

class ScalexpertApiDefault extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_api_default_model';

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
