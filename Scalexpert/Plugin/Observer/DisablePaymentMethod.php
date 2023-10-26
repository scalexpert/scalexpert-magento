<?php

namespace Scalexpert\Plugin\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;

class DisablePaymentMethod implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Scalexpert\Plugin\Model\SystemConfigData
     */
    protected $systemConfigData;

    /**
     * @var \Scalexpert\Plugin\Model\RestApi
     */
    protected $restApi;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData
     * @param \Scalexpert\Plugin\Model\RestApi $restApi
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData,
        \Scalexpert\Plugin\Model\RestApi $restApi
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->systemConfigData = $systemConfigData;
        $this->restApi = $restApi;
    }

    private function quoteItemHasWarranty($quoteItem){
        return($quoteItem->getSku() == "Insurance");
    }

    private function shouldDisableScalexpertPaymentMethod($quoteItems,$paymentCode){
        $disable = false;
        $isScalexpertPayment = in_array($paymentCode,[
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE
        ]);
        if($isScalexpertPayment){
            foreach ($quoteItems as $quoteItem){
                $disable = $disable || ($this->quoteItemHasWarranty($quoteItem));
            }
        }
        return $disable;
    }

    public function execute(Observer $observer)
    {
        /** @var DataObject $result */
        $result = $observer->getResult();

        $methodInstance = $observer->getMethodInstance();

        /** @var \Magento\Quote\Model\Quote  */
        $quote = $this->checkoutSession->getQuote();
        $paymentCode = $methodInstance->getCode();

        $canBeDisable = false;
        $paymentCodeSolutionCode = array();



        switch ($paymentCode) {
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }
                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }
                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }
                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }
                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE;
                break;
            default:
                break;
        }

        $allItems = $quote->getAllItems();
        if ($canBeDisable) {
            $isExcludeCategory = false;
            if($configurationExcludedCategory) {
                foreach ($allItems as $item) {
                    $productCategoryIds = $item->getProduct()->getCategoryIds();
                    if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
                        $result->setData('is_available', false);
                        $isExcludeCategory = true;
                        break;
                    }
                }
            }
            if (!$isExcludeCategory) {
                $quotetotal = $quote->getBaseSubtotal();
                $countryId = $quote->getBillingAddress()->getCountryId();
                $financing = $this->restApi->getFinancingEligibleSolutions($quotetotal, $countryId);
                $financingCode = array();
                if ($financing['status']) {
                    foreach ($financing['result']->solutions as $solution) {
                        $solutionCode = $solution->solutionCode;
                        array_push($financingCode, $solutionCode);
                    }
                }
                if (!array_intersect($paymentCodeSolutionCode, $financingCode)) {
                    $result->setData('is_available', false);
                }
            }
        }

        if($this->shouldDisableScalexpertPaymentMethod($allItems,$paymentCode)){
            $result->setData('is_available', false);
        }
    }
}
