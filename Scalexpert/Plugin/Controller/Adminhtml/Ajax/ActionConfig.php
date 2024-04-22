<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Controller\Adminhtml\Ajax;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Scalexpert\Plugin\Model\SystemConfigData;


abstract class ActionConfig extends Action implements FlushableConfigField
{

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;


    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * @var SystemConfigData
     */
    protected $systemConfigData;

    public function __construct(Context $context, TypeListInterface $cacheTypeList, SystemConfigData $systemConfigData,
                                Pool    $cacheFrontendPool)
    {
        parent::__construct($context);
        $this->systemConfigData = $systemConfigData;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }


    public function flushCache()
    {
        $_types = [
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice'
        ];

        foreach ($_types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
