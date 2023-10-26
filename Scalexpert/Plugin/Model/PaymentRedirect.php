<?php

namespace Scalexpert\Plugin\Model;

use Magento\Framework\Model\AbstractModel;
use Scalexpert\Plugin\Model\ResourceModel\PaymentRedirect as ResourceModel;

class PaymentRedirect extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_payment_redirect_model';

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
