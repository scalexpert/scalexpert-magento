<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action\Context;
use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
use Magento\Config\Model\Config\Factory;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\WebsiteRepository;
use Scalexpert\Plugin\Logger\Logger;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertContracts\CollectionFactory;
use Scalexpert\Plugin\Model\RestApi;
use Scalexpert\Plugin\Model\ScalexpertContractsFactory;
use Scalexpert\Plugin\Model\SystemConfigData;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertApiDefault\CollectionFactory as DefaultApiCollectionFactory;
use Scalexpert\Plugin\Model\ScalexpertApiDefaultFactory;

class Save extends \Magento\Config\Controller\Adminhtml\System\Config\Save
{

    const INVALID_API_DATA = -1;
    const UPDATED_SUCCESSFULLY = 2;

    /**
     * @var RestApi
     */
    protected $restApi;

    /**
     * @var SystemConfigData
     */
    protected $systemConfigData;

    /**
     * @var CollectionFactory
     */
    protected $contractsCollectionFactory;

    /**
     * @var ScalexpertContractsFactory
     */
    protected $contractFactory;

    protected $logger;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var WebsiteRepository
     */
    protected $websiteRepository;


    /**
     * @var DefaultApiCollectionFactory
     */
    protected $defaultApiCollectionFactory;

    /**
     * @var ScalexpertApiDefaultFactory;
     */
    protected $defautApiFactory;


    public function __construct(Context $context, Structure $configStructure, ConfigSectionChecker $sectionChecker,
                                Factory $configFactory, FrontendInterface $cache, StringUtils $string, RestApi $restApi,
                                SystemConfigData $systemConfigData, CollectionFactory $collection,
                                ScalexpertContractsFactory $contractFactory,Logger $logger,
                                StoreRepository $storeRepository, WebsiteRepository $websiteRepository,
                                DefaultApiCollectionFactory $defaultApiCollectionFactory,
                                ScalexpertApiDefaultFactory $defaultApiFactory)
    {
        parent::__construct($context, $configStructure, $sectionChecker, $configFactory, $cache, $string);
        $this->restApi = $restApi;
        $this->systemConfigData = $systemConfigData;
        $this->contractsCollectionFactory = $collection;
        $this->contractFactory = $contractFactory;
        $this->logger = $logger;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        $this->defaultApiCollectionFactory = $defaultApiCollectionFactory;
        $this->defautApiFactory = $defaultApiFactory;
    }

    protected function getEligiblesSolutionCode($sendedId, $sendedKey,$mode,$scope = null,$store = null){
        $solutionsToCheck = [
            ['financialAmount' => 500, 'buyerBillingCountry' => 'FR' ],
            ['financialAmount' => 1000, 'buyerBillingCountry' => 'FR' ],
            ['financialAmount' => 500, 'buyerBillingCountry' => 'DE' ],
            ['financialAmount' => 1000, 'buyerBillingCountry' => 'DE' ],
        ];

        $insuranceBuyerBillingCountryToCheck = ['FR','DE'];
        $eligibleSolutionCodes = [];

        foreach ($solutionsToCheck as $solution){
            $eligibleSolution = $this->restApi->getFinancingEligibleSolutions($solution['financialAmount'],
                $solution['buyerBillingCountry'],$sendedId, $sendedKey,$mode,true);
            if($eligibleSolution['result'] != null){
                $eligibleSolutionResults = $eligibleSolution['result']->solutions;
                foreach ($eligibleSolutionResults as $eligibleSolution){
                    $solutionCode = $eligibleSolution->solutionCode;
                    if(!in_array($solutionCode,$eligibleSolutionCodes)){
                        $eligibleSolutionCodes[] = $eligibleSolution->solutionCode;
                        $this->writeApiDefaultConfigFromSolution($eligibleSolution,$scope,$store);

                    }
                }
            }
        }

        foreach ($insuranceBuyerBillingCountryToCheck as $buyerBillingCountry){
            $eligibleSolutionInsurance = $this->restApi->getInsuranceEligibleSolutions($buyerBillingCountry,$sendedId, $sendedKey,$mode,true);
            if($eligibleSolutionInsurance['result'] != null){
                $eligibleSolutionResults = $eligibleSolutionInsurance['result']->solutions;
                foreach ($eligibleSolutionResults as $eligibleSolution){
                    $solutionCode = $eligibleSolution->solutionCode;
                    if(!in_array($solutionCode,$eligibleSolutionCodes)){
                        $eligibleSolutionCodes[] = $eligibleSolution->solutionCode;
                        $this->writeApiDefaultConfigFromSolution($eligibleSolution,$scope,$store);
                    }
                }
            }
        }

        $this->systemConfigData->setScalexpertConfigData(
            $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_STATUS_ACCESS,
            count($eligibleSolutionCodes) > 0,
            $scope,
            $store
        );
        return $eligibleSolutionCodes;
    }

    private function writeApiDefaultConfigFromSolution($eligibleSolution,$scope,$store){
        $matchingSolutionCodes = [
            'SCDELT-DXTS' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCDELT-DXCO' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCFRLT-TXTS' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'checkout_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCFRLT-TXPS' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'checkout_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCFRLT-TXNO' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'checkout_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCFRSP-4XTS' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'checkout_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_CUSTOMIZE_CHECKOUT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCFRSP-4XPS' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'checkout_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCFRSP-3XTS' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'checkout_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'SCFRSP-3XPS' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'checkout_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE,
                'payment_method_title' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE,
                'payment_method_subtitle' => systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
            ],
            'CIFRWE-DXCO' => [
                'product_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE,
                'product_block_subtitle' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_SUB_TITLE,
                'cart_block_title' => $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_TITLE
            ]
        ];

        if(isset($matchingSolutionCodes[$eligibleSolution->solutionCode])) {
            $solutionCommunicationKit = $eligibleSolution->communicationKit;
            $productBlockTitle = $this->systemConfigData->replaceDiv($solutionCommunicationKit->visualTitle);
            $pathProductBlockTitle = $matchingSolutionCodes[$eligibleSolution->solutionCode]['product_block_title'];

            $this->setBlockTitleDefaultValue($pathProductBlockTitle, $productBlockTitle, $scope, $store);

            if (isset($matchingSolutionCodes[$eligibleSolution->solutionCode]['checkout_block_title'])) {
                $pathCheckoutBlockTitle = $matchingSolutionCodes[$eligibleSolution->solutionCode]['checkout_block_title'];
                $this->setBlockTitleDefaultValue($pathCheckoutBlockTitle, $productBlockTitle, $scope, $store);
            }

            if ($eligibleSolution->solutionCode == 'CIFRWE-DXCO') {
                $cartBlockTitle = $this->systemConfigData->replaceDiv($solutionCommunicationKit->visualTitle);
                $pathCartBlockTitle = $matchingSolutionCodes[$eligibleSolution->solutionCode]['cart_block_title'];
                $apiDefault = $this->defaultApiCollectionFactory->create()
                    ->addFieldToFilter('path', ['eq' => $pathCartBlockTitle])
                    ->addFieldToFilter('scope', ['eq' => $scope])
                    ->addFieldToFilter('store', ['eq' => $store])
                    ->getFirstItem();
                if ($apiDefault != null) {
                    $apiDefault->setPath($pathCartBlockTitle);
                    $apiDefault->setScope($scope);
                    $apiDefault->setStore($store);
                    $apiDefault->setDefaultValue($cartBlockTitle);
                    $apiDefault->save();
                } else {
                    $apiDefault = $this->defautApiFactory->create();
                    $apiDefault->setPath($pathCartBlockTitle);
                    $apiDefault->setScope($scope);
                    $apiDefault->setStore($store);
                    $apiDefault->setDefaultValue($cartBlockTitle);
                }
                $apiDefault->save();

                $this->systemConfigData->setScalexpertConfigData(
                    $pathCartBlockTitle,
                    $cartBlockTitle,
                    $scope,
                    $store
                );

                if (isset($solutionCommunicationKit->visualDescription)) {
                    $productBlockSubtitle = $this->systemConfigData->replaceDiv($solutionCommunicationKit->visualDescription);
                    $pathProductBlockSubtitle = $matchingSolutionCodes[$eligibleSolution->solutionCode]['product_block_subtitle'];

                    $apiDefault = $this->defaultApiCollectionFactory->create()
                        ->addFieldToFilter('path', ['eq', $pathProductBlockSubtitle])
                        ->addFieldToFilter('scope', ['eq' => $scope])
                        ->addFieldToFilter('store', ['eq' => $store])
                        ->getFirstItem();
                    if ($apiDefault != null) {
                        $apiDefault->setPath($pathProductBlockSubtitle);
                        $apiDefault->setDefaultValue($productBlockSubtitle);
                        $apiDefault->setScope($scope);
                        $apiDefault->setStore($store);
                        $apiDefault->save();
                    } else {
                        $apiDefault = $this->defautApiFactory->create();
                        $apiDefault->setPath($pathProductBlockSubtitle);
                        $apiDefault->setDefaultValue($productBlockSubtitle);
                        $apiDefault->setScope($scope);
                        $apiDefault->setStore($store);
                    }
                    $apiDefault->save();

                    $this->systemConfigData->setScalexpertConfigData(
                        $pathProductBlockSubtitle,
                        $productBlockSubtitle,
                        $scope,
                        $store
                    );
                }
            } else {
                $paymentMethodBlockTitle = $this->systemConfigData->replaceDiv($solutionCommunicationKit->visualTitle);
                $pathPaymentMethodBlockTitle = $matchingSolutionCodes[$eligibleSolution->solutionCode]['payment_method_title'];

                $apiDefault = $this->defaultApiCollectionFactory->create()
                    ->addFieldToFilter('path', ['eq' => $pathPaymentMethodBlockTitle])
                    ->addFieldToFilter('scope', ['eq' => $scope])
                    ->addFieldToFilter('store', ['eq' => $store])
                    ->getFirstItem();
                if ($apiDefault != null) {
                    $apiDefault->setPath($pathPaymentMethodBlockTitle);
                    $apiDefault->setScope($scope);
                    $apiDefault->setStore($store);
                    $apiDefault->setDefaultValue($paymentMethodBlockTitle);
                    $apiDefault->save();
                } else {
                    $apiDefault = $this->defautApiFactory->create();
                    $apiDefault->setPath($pathPaymentMethodBlockTitle);
                    $apiDefault->setScope($scope);
                    $apiDefault->setStore($store);
                    $apiDefault->setDefaultValue($paymentMethodBlockTitle);
                }
                $apiDefault->save();

                $this->systemConfigData->setScalexpertConfigData(
                    $pathPaymentMethodBlockTitle,
                    $paymentMethodBlockTitle,
                    $scope,
                    $store
                );


                if (isset($solutionCommunicationKit->visualDescription)) {
                    $paymentMethodBlockSubtitle = $this->systemConfigData->replaceDiv($solutionCommunicationKit->visualDescription);
                    $pathPaymentMethodBlockSubtitle = $matchingSolutionCodes[$eligibleSolution->solutionCode]['payment_method_subtitle'];

                    $apiDefault = $this->defaultApiCollectionFactory->create()
                        ->addFieldToFilter('path', ['eq' => $pathPaymentMethodBlockSubtitle])
                        ->addFieldToFilter('scope', ['eq' => $scope])
                        ->addFieldToFilter('store', ['eq' => $store])
                        ->getFirstItem();
                    if ($apiDefault != null) {
                        $apiDefault->setPath($pathPaymentMethodBlockSubtitle);
                        $apiDefault->setScope($scope);
                        $apiDefault->setStore($store);
                        $apiDefault->setDefaultValue($paymentMethodBlockSubtitle);
                        $apiDefault->save();
                    } else {
                        $apiDefault = $this->defautApiFactory->create();
                        $apiDefault->setPath($pathPaymentMethodBlockSubtitle);
                        $apiDefault->setScope($scope);
                        $apiDefault->setStore($store);
                        $apiDefault->setDefaultValue($paymentMethodBlockSubtitle);
                    }
                    $apiDefault->save();

                    $this->systemConfigData->setScalexpertConfigData(
                        $pathPaymentMethodBlockSubtitle,
                        $paymentMethodBlockSubtitle,
                        $scope,
                        $store
                    );
                }
            }
        }
    }

    protected function setEligiblesSolution($eligibleSolutionCodes,$scope = null,$scopeId = null){
        $matchingSolutionCodes = [
            'SCDELT-DXTS' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_WITH_FEES_ENABLE,
            'SCDELT-DXCO' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE,
            'SCFRLT-TXPS' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_WITH_FEES_ENABLE,
            'SCFRLT-TXTS' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_WITHOUT_FEES_ENABLE,
            'SCFRLT-TXNO' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE,
            'SCFRSP-4XTS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE,
            'SCFRSP-4XPS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_WITH_FEES_ENABLE,
            'SCFRSP-3XTS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE,
            'SCFRSP-3XPS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_WITH_FEES_ENABLE,
            'CIFRWE-DXCO' => $this->systemConfigData::XML_SCALEXPERT_WARRANTY_ENABLE
        ];
        foreach ($eligibleSolutionCodes as $solutionCode){
            if(!isset($matchingSolutionCodes[$solutionCode])){
                $this->logger->info("Code ".$solutionCode. "is not recognized");
            }else{
                $this->systemConfigData->setScalexpertConfigData($matchingSolutionCodes[$solutionCode],true,$scope,$scopeId);

                $contract = $this->contractsCollectionFactory->create()
                    ->addFieldToFilter('path',['eq',$matchingSolutionCodes[$solutionCode]])
                    ->addFieldToFilter('scope',['eq' => $scope])
                    ->addFieldToFilter('store',['eq' => $scopeId])
                    ->getFirstItem();
                if($contract != null){
                    $contract->setPath($matchingSolutionCodes[$solutionCode]);
                    $contract->setStore($scopeId);
                    $contract->setScope($scope);
                    $contract->setIsAllowed(1);
                    $contract->save();
                }else {
                    $contract = $this->contractFactory->create();
                    $contract->setPath($matchingSolutionCodes[$solutionCode]);
                    $contract->setStore($scopeId);
                    $contract->setScope($scope);
                    $contract->setIsAllowed(1);
                }
                $apiDefault = $this->defaultApiCollectionFactory->create()
                    ->addFieldToFilter('path',['eq',$matchingSolutionCodes[$solutionCode]])
                    ->addFieldToFilter('scope',['eq' => $scope])
                    ->addFieldToFilter('store',['eq' => $scopeId])
                    ->getFirstItem();
                if($apiDefault != null){
                    $apiDefault->setPath($matchingSolutionCodes[$solutionCode]);
                    $apiDefault->setDefaultValue('Yes');
                    $apiDefault->setStore($scopeId);
                    $apiDefault->setScope($scope);
                }else {
                    $apiDefault = $this->defautApiFactory->create();

                    $apiDefault->setStore($scopeId);
                    $apiDefault->setScope($scope);
                    $apiDefault->setPath($matchingSolutionCodes[$solutionCode]);
                    $apiDefault->setDefaultValue('Yes');
                }
                $apiDefault->save();
            }
        }
        $unEligibleSolutionCodes = array_diff(array_keys($matchingSolutionCodes),$eligibleSolutionCodes);
        foreach ($unEligibleSolutionCodes as $solutionCode){
            $this->systemConfigData->setScalexpertConfigData($matchingSolutionCodes[$solutionCode],0,$scope,$scopeId);

            $contract = $this->contractsCollectionFactory->create()
                ->addFieldToFilter('path',['eq',$matchingSolutionCodes[$solutionCode]])
                ->addFieldToFilter('scope',['eq' => $scope])
                ->addFieldToFilter('store',['eq' => $scopeId])
                ->getFirstItem();
            if($contract != null){
                $contract->setPath($matchingSolutionCodes[$solutionCode]);
                $contract->setStore($scopeId);
                $contract->setScope($scope);
                $contract->setIsAllowed(0);
                $contract->save();
            }else {
                $contract = $this->contractFactory->create();
                $contract->setPath($matchingSolutionCodes[$solutionCode]);
                $contract->setStore($scopeId);
                $contract->setScope($scope);
                $contract->setIsAllowed(0);
            }
            $apiDefault = $this->defaultApiCollectionFactory->create()
                ->addFieldToFilter('path',['eq',$matchingSolutionCodes[$solutionCode]])
                ->addFieldToFilter('scope',['eq' => $scope])
                ->addFieldToFilter('store',['eq' => $scopeId])
                ->getFirstItem();
            if($apiDefault != null){
                $apiDefault->setPath($matchingSolutionCodes[$solutionCode]);
                $apiDefault->setStore($scopeId);
                $apiDefault->setScope($scope);
                $apiDefault->setDefaultValue('No');
            }else {
                $apiDefault = $this->defautApiFactory->create();
                $apiDefault->setPath($matchingSolutionCodes[$solutionCode]);
                $apiDefault->setStore($scopeId);
                $apiDefault->setScope($scope);
                $apiDefault->setDefaultValue('No');
            }
            $apiDefault->save();
        }
    }


    protected function validateScalespertAdminConfigData($configData){
        $this->logger->info("Validate admin config data");

        $currentWebsite = $configData['website'];
        $currentStore = $configData['store'];
        if($currentWebsite == null && $currentStore == null){
            $currentScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $currentScopeToLoadId = 0;
        }else{
            if($currentWebsite == null){
                $currentScope = ScopeInterface::SCOPE_STORES;
                $currentScopeToLoadId = $currentStore;
                $store = $this->storeRepository->getById($currentStore);
                $parentScope = ScopeInterface::SCOPE_WEBSITES;
                $scopeToLoadId = $store->getWebsiteId();
            }else{
                $currentScope = ScopeInterface::SCOPE_WEBSITES;
                $currentScopeToLoadId = $currentWebsite;
                $parentScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                $scopeToLoadId = 0;
            }
        }


        $isScalexpertConfigAdmin = $configData['section'] == "scalexpert_administration";

        /**
         * GET SENDED MODE
         */
        if($isScalexpertConfigAdmin){
            $this->logger->info("From section scalexpert_administration we take data from form");

            if(!isset($configData['groups']['platform_access']['fields']['mode']['value'])) {
                $sendedMode = $this->systemConfigData->getScalexpertConfigData(
                    $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_MODE,
                    $parentScope,
                    $scopeToLoadId
                );
            }else {
                $sendedMode = $configData['groups']['platform_access']['fields']['mode']['value'];
            }

            if(!isset($configData['groups']['platform_access']['fields']['id_test']['value'])) {
                $sendedIdTest = $this->systemConfigData->getScalexpertConfigData(
                    $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_TEST,
                    $parentScope,
                    $scopeToLoadId
                );
            }else {
                $sendedIdTest = $configData['groups']['platform_access']['fields']['id_test']['value'];
            }

            if(!isset($configData['groups']['platform_access']['fields']['key_test']['value'])) {
                $sendedKeyTest = $this->systemConfigData->getScalexpertConfigData(
                    $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_TEST,
                    $parentScope,
                    $scopeToLoadId
                );
            }else {
                $sendedKeyTest = $configData['groups']['platform_access']['fields']['key_test']['value'];
            }

            if(!isset($configData['groups']['platform_access']['fields']['id_prod']['value'])) {
                $sendedIdProd = $this->systemConfigData->getScalexpertConfigData(
                    $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_PROD,
                    $parentScope,
                    $scopeToLoadId
                );
            }else {
                $sendedIdProd = $configData['groups']['platform_access']['fields']['id_prod']['value'];
            }

            if(!isset($configData['groups']['platform_access']['fields']['key_prod']['value'])) {
                $sendedKeyProd = $this->systemConfigData->getScalexpertConfigData(
                    $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_PROD,
                    $parentScope,
                    $scopeToLoadId
                );
            }else {
                $sendedKeyProd = $configData['groups']['platform_access']['fields']['key_prod']['value'];
            }

            $this->logger->info("Sended mode :".$sendedMode);
        } else {
            $this->logger->info("We take data from already saved configuration");
            $sendedMode = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_MODE,
                $currentScope,
                $currentScopeToLoadId
            );

            $sendedIdTest = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_TEST,
                $currentScope,
                $currentScopeToLoadId
            );
            $sendedKeyTest = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_TEST,
                $currentScope,
                $currentScopeToLoadId
            );

            $sendedIdProd = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_PROD,
                $currentScope,
                $currentScopeToLoadId
            );
            $sendedKeyProd = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_PROD,
                $currentScope,
                $currentScopeToLoadId
            );

            $this->logger->info("Sended mode :".$sendedMode);
        }

        if($sendedMode == "PRODUCTION"){
            $sendedId = $sendedIdProd;
            $sendedKey = $sendedKeyProd;
            $idConfig = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_PROD,
                $currentScope,
                $currentScopeToLoadId
            );
            $keyConfig = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_PROD,
                $currentScope,
                $currentScopeToLoadId
            );
            $this->logger->info("Production mode check eligibles");
        }else {
            $sendedId = $sendedIdTest;
            $sendedKey = $sendedKeyTest;
            $idConfig = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_PROD,
                $currentScope,
                $currentScopeToLoadId
            );
            $keyConfig = $this->systemConfigData->getScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_PROD,
                $currentScope,
                $currentScopeToLoadId
            );
            $this->logger->info("Test mode check eligibles");
        }
        $modeConfig = $this->systemConfigData->getScalexpertConfigData(
            $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_MODE,
            $currentScope,
            $currentScopeToLoadId
        );
        $this->logger->info('Use id : '.$sendedId);
        $this->logger->info('Use Key : '.$sendedKey);
        $financingResult = $this->restApi->getBearer('financing', $sendedId, $sendedKey,$sendedMode,true);
        $resultFinancing = $financingResult['status'];
        $this->logger->info(print_r($financingResult,true));
        $isValidKey = $resultFinancing;
        $hasKeyChanged = ($sendedId != $idConfig ) || ( $sendedKey != $keyConfig );
        $hasModeChanged = ($sendedMode != $modeConfig);
        $this->logger->info("Has key changed :".$hasKeyChanged);
        $this->logger->info("Has mode changed :".$hasModeChanged);

        $matchingPaymentCodes = [
            'SCDELT-DXTS' => 'long_credit_de_with_fees',
            'SCDELT-DXCO' => 'long_credit_de',
            'SCFRLT-TXTS' => 'long_credit_fr_without_fees',
            'SCFRLT-TXPS' => 'long_credit_fr_with_fees',
            'SCFRLT-TXNO' => 'long_credit_fr',
            'SCFRSP-4XTS' => 'payment_4x',
            'SCFRSP-4XPS' => 'payment_4x_with_fees',
            'SCFRSP-3XTS' => 'payment_3x',
            'SCFRSP-3XPS' => 'payment_3x_with_fees',
            'CIFRWE-DXCO' =>  'warranty_extension',
        ];


        $this->systemConfigData->setScalexpertConfigData(
            $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_STATUS_ACCESS,
            $isValidKey,
            $currentScope,
            $currentScopeToLoadId);


        if($isScalexpertConfigAdmin){
            if(!$isValidKey){
                $this->logger->info("Invalid key");
                return false;
            }else{
                $hasEfundingValidConfig = true;
                $hasWarrantyExtensionValidConfig = true;
                $eligibleSolutionCodes = $this->getEligiblesSolutionCode(
                    $sendedId, $sendedKey,$sendedMode,$currentScope, $currentScopeToLoadId
                );

                $this->logger->info("Valid key");

                if(isset($configData['groups']['activation']['groups']['e_funding'])){

                    $this->logger->info("Check efunding data validation");
                    $activationEfundingGroups = $configData['groups']['activation']['groups']['e_funding']['groups'];
                    foreach ($activationEfundingGroups as $key =>  $paymentGroup){
                        if($key != 'show_in_checkout'){
                            $this->logger->info("Check ".$key);

                            $configPath = null;
                            switch ($key){
                                case "payment_3x":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE;
                                    break;
                                case "payment_3x_with_fees":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_WITH_FEES_ENABLE;
                                    break;
                                case "payment_4x":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE;
                                    break;
                                case "payment_4x_with_fees":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_WITH_FEES_ENABLE;
                                    break;
                                case "long_credit_fr":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE;
                                    break;
                                case "long_credit_fr_with_fees":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_WITH_FEES_ENABLE;
                                    break;
                                case "long_credit_fr_without_fees":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_WITHOUT_FEES_ENABLE;
                                    break;
                                case "long_credit_de":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE;
                                    break;
                                case "long_credit_de_with_fees":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_WITH_FEES_ENABLE;
                                    break;
                                case "warranty_extension":
                                    $configPath = $this->systemConfigData::XML_SCALEXPERT_WARRANTY_ENABLE;
                                    break;
                                default:
                                    $configPath = null;
                                    break;
                            }

                            if(!isset($paymentGroup['fields']['active']['value'])) {
                                $activate = $this->systemConfigData->getScalexpertConfigData(
                                    $configPath,
                                    $parentScope,
                                    $scopeToLoadId
                                );
                            }else {
                                $activate = $paymentGroup['fields']['active']['value'];
                            }

                            if($activate){
                                $found = false;
                                foreach ($eligibleSolutionCodes as $eligibleCode ){
                                    if(!isset($matchingPaymentCodes[$eligibleCode])){
                                        $this->logger->info("Code ".$eligibleCode. "is not recognized");
                                    }else{
                                        $found = ($key == $matchingPaymentCodes[$eligibleCode]);
                                        if($found){
                                            $this->logger->info("Found key for following eligibleCode ".$eligibleCode);
                                            break;
                                        }
                                    }
                                }
                                $hasEfundingValidConfig = $hasEfundingValidConfig && $found;
                            }
                        }
                    }
                    $this->logger->info("Efunding data validation : ".$hasEfundingValidConfig);
                }

                if(isset($configData['groups']['activation']['groups']['warrantly_extension'])){
                    $hasWarrantyExtensionValidConfig = false;
                    $this->logger->info("Check Warranty data validation");
                    foreach ($eligibleSolutionCodes as $eligibleCode ){
                        if(!isset($matchingPaymentCodes[$eligibleCode])){
                            $this->logger->info("Code ".$eligibleCode. "is not recognized");
                        }else{
                            $hasWarrantyExtensionValidConfig = $hasWarrantyExtensionValidConfig ||
                                ('warrantly_extension' == $matchingPaymentCodes[$eligibleCode]);
                        }
                    }
                    $this->logger->info("Warranty data validation : ".$hasWarrantyExtensionValidConfig);
                }

                $this->logger->info('Has Efunding Valid config: '.$hasEfundingValidConfig);
                $this->logger->info('Has Warranty Valid config: '.$hasWarrantyExtensionValidConfig);
                $this->logger->info('Has Mode Changed: '.$hasModeChanged);
                $this->logger->info('Has Key Changed: '.$hasKeyChanged);
                if((!$hasEfundingValidConfig || !$hasWarrantyExtensionValidConfig) &&
                    (!$hasModeChanged)
                ){
                    $this->logger->info("INVALID API DATA");
                    return self::INVALID_API_DATA;
                }

                if($hasKeyChanged || $hasModeChanged){
                    $this->logger->info("Valid key, valid data and key or mode changed ==> we update solutions");
                    $this->setEligiblesSolution($eligibleSolutionCodes,$currentScope, $currentScopeToLoadId);
                }
                $this->logger->info("UPDATE CONFIG SUCCESS");
                return self::UPDATED_SUCCESSFULLY;
            }
        }
        return false;
    }


    /**
     * Save configuration
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->_saveSection();

            $section = $this->getRequest()->getParam('section');

            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            $configData = [
                'section' => $section,
                'website' => $website,
                'store' => $store,
                'groups' => $this->_getGroupsForSave(),
            ];


            $configData = $this->filterNodes($configData);


            $hasScalexpertValidConfig = $this->validateScalespertAdminConfigData($configData);

            if (!$hasScalexpertValidConfig) {
                $configKeysToDisable = [
                    'long_credit_de' => 'e_funding',
                    'long_credit_de_with_fees' => 'e_funding',
                    'long_credit_fr' => 'e_funding',
                    'long_credit_fr_with_fees' => 'e_funding',
                    'long_credit_fr_without_fees' => 'e_funding',
                    'payment_4x' => 'e_funding',
                    'payment_4x_with_fees' => 'e_funding',
                    'payment_3x' => 'e_funding',
                    'payment_3x_with_fees' => 'e_funding',
                    'warranty_extension' => 'warranty_extension'
                ];
                $issetEFundingGroups = isset($configData['groups']['activation']['groups']['e_funding']['groups']);
                if ($issetEFundingGroups) {
                    foreach ($configKeysToDisable as $configKey => $type) {
                        if($type == 'e_funding'){
                            $configData['groups']['activation']['groups']['e_funding']['groups'][$configKey]['fields']['active']['value'] = 0;
                        }
                        else{
                            $configData['groups']['activation']['groups'][$configKey]['fields']['active']['value'] = 0;
                        }
                    }
                }
            }
            if($hasScalexpertValidConfig === self::INVALID_API_DATA){
                $this->messageManager->addError(__('Invalid Scalexpert Data Config'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath(
                    'adminhtml/system_config/edit',
                    [
                        '_current' => ['section', 'website', 'store'],
                        '_nosid' => true
                    ]
                );
            }


            /** @var \Magento\Config\Model\Config $configModel */
            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();
            $this->_eventManager->dispatch(
                'admin_system_config_save',
                ['configData' => $configData, 'request' => $this->getRequest()]
            );
            if($hasScalexpertValidConfig === self::UPDATED_SUCCESSFULLY){
                $this->messageManager->addSuccess(__('You saved the configuration and your API has been updated.'));
            }else{
                $this->messageManager->addSuccess(__('You saved the configuration.'));
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = explode("\n", $e->getMessage() ?? '');
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }

        /**
         * Testing libphonenumber class
         */
        if (!class_exists('\libphonenumber\PhoneNumberUtil')) {
            $message = __('Please, run the following command in Magento 2 root folder : composer require giggsey/libphonenumber-for-php');
            $this->messageManager->addErrorMessage($message);
        }

        $this->_saveState($this->getRequest()->getPost('config_state'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'adminhtml/system_config/edit',
            [
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true
            ]
        );
    }

    /**
     * Filters nodes by checking whether they exist in system.xml.
     *
     * @param array $configData
     * @return array
     */
    public function filterNodes(array $configData): array
    {
        if (!empty($configData['groups'])) {
            $systemXmlPathsFromKeys = array_keys($this->_configStructure->getFieldPaths());
            $systemXmlPathsFromValues = array_reduce(
                array_values($this->_configStructure->getFieldPaths()),
                'array_merge',
                []
            );

            $systemXmlConfig = array_merge($systemXmlPathsFromKeys, $systemXmlPathsFromValues);

            $configData['groups'] = $this->filterPaths($configData['section'], $configData['groups'], $systemXmlConfig);
        }

        return $configData;
    }

    /**
     * Filter paths that are not defined.
     *
     * @param string $prefix Path prefix
     * @param array $groups Groups data.
     * @param string[] $systemXmlConfig Defined paths.
     * @return array Filtered groups.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function filterPaths(string $prefix, array $groups, array $systemXmlConfig): array
    {
        $flippedXmlConfig = array_flip($systemXmlConfig);
        $filtered = [];
        foreach ($groups as $groupName => $childPaths) {

            $group = $this->_configStructure->getElement($prefix .'/' .$groupName);
            if (array_key_exists('clone_fields', $group->getData()) && $group->getData()['clone_fields']) {
                $filtered[$groupName] = $childPaths;
                continue;
            }

            $filtered[$groupName] = ['fields' => [], 'groups' => []];

            if (array_key_exists('fields', $childPaths)) {
                foreach ($childPaths['fields'] as $field => $fieldData) {

                    $path = $prefix .'/' .$groupName .'/' .$field;
                    $element = $this->_configStructure->getElement($path);
                    if ($element
                        && ($elementData = $element->getData())
                        && array_key_exists('config_path', $elementData)
                    ) {
                        $path = $elementData['config_path'];
                    }

                    if (array_key_exists($path, $flippedXmlConfig)) {
                        $filtered[$groupName]['fields'][$field] = $fieldData;
                    }
                }
            }

            if (array_key_exists('groups', $childPaths) && $childPaths['groups']) {
                $filteredGroups = $this->filterPaths(
                    $prefix .'/' .$groupName,
                    $childPaths['groups'],
                    $systemXmlConfig
                );
                if ($filteredGroups) {
                    $filtered[$groupName]['groups'] = $filteredGroups;
                }
            }

            $filtered[$groupName] = array_filter($filtered[$groupName]);
        }

        return array_filter($filtered);
    }


    public function setBlockTitleDefaultValue($pathBlockTitle, $blockTitle, $scope, $store)
    {
        $apiDefault = $this->defaultApiCollectionFactory->create()
            ->addFieldToFilter('path', ['eq' => $pathBlockTitle])
            ->addFieldToFilter('scope', ['eq' => $scope])
            ->addFieldToFilter('store', ['eq' => $store])
            ->getFirstItem();
        if ($apiDefault != null) {
            $apiDefault->setPath($pathBlockTitle);
            $apiDefault->setScope($scope);
            $apiDefault->setStore($store);
            $apiDefault->setDefaultValue($blockTitle);
            $apiDefault->save();
        } else {
            $apiDefault = $this->defautApiFactory->create();
            $apiDefault->setPath($pathBlockTitle);
            $apiDefault->setScope($scope);
            $apiDefault->setStore($store);
            $apiDefault->setDefaultValue($blockTitle);
        }
        $apiDefault->save();

        $this->systemConfigData->setScalexpertConfigData(
            $pathBlockTitle,
            $blockTitle,
            $scope,
            $store
        );
    }

}
