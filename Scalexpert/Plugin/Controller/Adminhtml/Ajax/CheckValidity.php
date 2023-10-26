<?php

namespace Scalexpert\Plugin\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreRepository;
use Scalexpert\Plugin\Model\SystemConfigData;

class CheckValidity extends ActionConfig
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;


    public function __construct(Context $context, TypeListInterface $cacheTypeList, SystemConfigData $systemConfigData,
                                Pool $cacheFrontendPool, JsonFactory $resultJsonFactory, StoreRepository $storeRepository)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeRepository = $storeRepository;
        parent::__construct($context, $cacheTypeList, $systemConfigData, $cacheFrontendPool);
    }

    public function execute()
    {

        $resultJson = $this->resultJsonFactory->create();
        $status = false;
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');

        if($website == null && $store == null){
            $currentScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $currentScopeToLoadId = 0;
        }else{
            if($website == null){
                $currentScope = ScopeInterface::SCOPE_WEBSITES;
                $store = $this->storeRepository->getById($store);
                $currentScopeToLoadId = $store->getWebsiteId();
            }else{
                $currentScope = ScopeInterface::SCOPE_WEBSITES;
                $currentScopeToLoadId = $website;
            }
        }

        if ($this->getRequest()->isAjax()) {
            $status = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_STATUS_ACCESS,
                $currentScope,
                $currentScopeToLoadId
            );
        }
        return $resultJson->setData([
            'validity' => $status
        ]);
    }
}
