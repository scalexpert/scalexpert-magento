<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\ScopeInterface;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData
     * @param \Scalexpert\Plugin\Model\RestApi $restApi
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->systemConfigData = $systemConfigData;
        $this->restApi = $restApi;
        $this->scopeConfig = $scopeConfig;
    }

    private function quoteItemHasWarranty($quoteItem){
        return($quoteItem->getSku() == "Insurance");
    }

    private function shouldDisableScalexpertPaymentMethod($quoteItems,$paymentCode){
        $disable = false;
        $isScalexpertPayment = in_array($paymentCode,[
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X_WITH_FEES,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X_WITH_FEES,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITH_FEES,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITHOUT_FEES,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE_WITH_FEES
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

        $configurationExcludedProduct = null;

        switch ($paymentCode) {
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X_WITH_FEES:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X_WITH_FEES;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X_WITH_FEES:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X_WITH_FEES;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITH_FEES:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR_WITH_FEES;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITHOUT_FEES:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(
                    \Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(
                    \Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR_WITHOUT_FEES;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE_WITH_FEES:
                $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
                if($configurationExcludedCategory) {
                    $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
                }

                $configurationExcludedProducts = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
                if ($configurationExcludedProducts) {
                    $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProducts ?? '');
                    $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
                }

                $canBeDisable = true;
                $paymentCodeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE_WITH_FEES;
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
                    if (!is_null($configurationExcludedProduct)) {
                        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
                        if (in_array($item->getSku(), $configurationExcludedProduct)) {
                            $result->setData('is_available', false);
                            $isExcludeCategory = true;
                            break;
                        }
                    }
                }
            }
            if (!$isExcludeCategory) {
                $quotetotal = $quote->getBaseSubtotal();
                $countryId = $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
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
