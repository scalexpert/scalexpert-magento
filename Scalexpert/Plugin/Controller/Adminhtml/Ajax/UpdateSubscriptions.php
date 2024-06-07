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

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Scalexpert\Plugin\Model\RestApi;
use Scalexpert\Plugin\Model\SystemConfigData;


class UpdateSubscriptions extends ActionConfig
{
    /**
     * @var RestApi
     */
    protected $restApi;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(Context $context, TypeListInterface $cacheTypeList, SystemConfigData $systemConfigData,
                                RestApi $restApi,
                                JsonFactory $resultJsonFactory,
                                Pool $cacheFrontendPool)
    {
        $this->restApi = $restApi;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $cacheTypeList, $systemConfigData, $cacheFrontendPool);
    }


    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $solutionsToCheck = [
            ['financialAmount' => 500, 'buyerBillingCountry' => 'FR' ],
            ['financialAmount' => 1000, 'buyerBillingCountry' => 'FR' ],
            ['financialAmount' => 500, 'buyerBillingCountry' => 'DE' ],
            ['financialAmount' => 1000, 'buyerBillingCountry' => 'DE' ],
        ];
        $insuranceBuyerBillingCountryToCheck = ['FR','DE'];

        $matchingSolutionCodes = [
            'SCDELT-DXTS' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_WITH_FEES_ENABLE,
            'SCDELT-DXCO' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE,
            'SCFRLT-DXPS' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_WITH_FEES_ENABLE,
            'SCFRLT-DXNO' => $this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE,
            'SCFRSP-4XTS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE,
            'SCFRSP-4XPS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_WITH_FEES_ENABLE,
            'SCFRSP-3XTS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE,
            'SCFRSP-3XPS' => $this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_WITH_FEES_ENABLE,
            'CIFRWE-DXCO' => $this->systemConfigData::XML_SCALEXPERT_WARRANTY_ENABLE
        ];

        $eligibleSolutionCodes = [];
        if ($this->getRequest()->isAjax()) {

            foreach ($solutionsToCheck as $solution){
                $eligibleSolution = $this->restApi->getFinancingEligibleSolutions($solution['financialAmount'],$solution['buyerBillingCountry']);
                $eligibleSolutionResults = $eligibleSolution['result']->solutions;
                foreach ($eligibleSolutionResults as $eligibleSolution){
                    $solutionCode = $eligibleSolution->solutionCode;
                    if(!in_array($solutionCode,$eligibleSolutionCodes)){
                        $eligibleSolutionCodes[] = $eligibleSolution->solutionCode;
                    }
                }
            }
            foreach ($insuranceBuyerBillingCountryToCheck as $buyerBillingCountry){
                $eligibleSolutionInsurance = $this->restApi->getInsuranceEligibleSolutions($buyerBillingCountry);
                $eligibleSolutionResults = $eligibleSolutionInsurance['result']->solutions;
                foreach ($eligibleSolutionResults as $eligibleSolution){
                    $solutionCode = $eligibleSolution->solutionCode;
                    if(!in_array($solutionCode,$eligibleSolutionCodes)){
                        $eligibleSolutionCodes[] = $eligibleSolution->solutionCode;
                    }
                }
            }

            $this->systemConfigData->setScalexpertConfigData(
                $this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_STATUS_ACCESS,
                count($eligibleSolutionCodes) > 0
            );


            foreach ($eligibleSolutionCodes as $solutionCode){
                $this->systemConfigData->setScalexpertConfigData($matchingSolutionCodes[$solutionCode],true);
            }
            $this->flushCache();
            return $resultJson->setData([
                'codes' => $eligibleSolutionCodes
            ]);
        }else{
            return $resultJson->setData([
                'error' => __('Unexpected error with ajax')
            ]);
        }
    }
}
