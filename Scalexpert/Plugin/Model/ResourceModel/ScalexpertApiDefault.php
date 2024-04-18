<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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
