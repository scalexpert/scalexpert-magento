<?php

namespace Scalexpert\Plugin\Model\ResourceModel\PaymentRedirect;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Scalexpert\Plugin\Model\PaymentRedirect as Model;
use Scalexpert\Plugin\Model\ResourceModel\PaymentRedirect as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_payment_redirect_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
