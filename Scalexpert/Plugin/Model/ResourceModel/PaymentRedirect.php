<?php

namespace Scalexpert\Plugin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PaymentRedirect extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'scalexpert_payment_redirect_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('scalexpert_payment_redirect', 'redirect_id');
        $this->_useIsObjectNew = true;
    }
}
