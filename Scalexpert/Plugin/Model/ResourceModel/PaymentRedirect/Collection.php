<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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
