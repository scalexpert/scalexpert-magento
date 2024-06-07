<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Model;

use Magento\Store\Model\ScopeInterface;
use Scalexpert\Plugin\Model\SystemConfigData;

class CheckoutPaymentConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Scalexpert\Plugin\Model\RestApi
     */
    protected $restApi;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Scalexpert\Plugin\Model\RestApi $restApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->restApi = $restApi;
        $this->checkoutSession = $checkoutSession;
    }


    public function getConfig()
    {

        /** @var \Magento\Quote\Model\Quote  */
        $quote = $this->checkoutSession->getQuote();

        $quotetotal = $quote->getBaseGrandTotal();
        $countryId = $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
        $financing = $this->restApi->getFinancingEligibleSolutions($quotetotal, $countryId);

        $codes_per_methods = [
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X => SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X,
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X_WITH_FEES => SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X_WITH_FEES,
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X => SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X,
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X_WITH_FEES => SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X_WITH_FEES,
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR => SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR,
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITH_FEES => SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR_WITH_FEES,
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE => SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE,
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE_WITH_FEES => SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE_WITH_FEES,
        ];
        $method_per_code = [];
        foreach ($codes_per_methods as $method => $codes) {
            foreach ($codes as $code) {
                $method_per_code[$code] = $method;
            }
        }

        $data_per_method = [
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X_WITH_FEES => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X_WITH_FEES => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITH_FEES => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
            SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE_WITH_FEES => [
                'customTitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_TITLE
                ),
                'customSubtitle' => $this->scopeConfig->getValue(
                    SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_SUB_TITLE
                ),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => '',
            ],
        ];

        $finalSolutions = array();
        if ($financing['status']) {
            foreach ($financing['result']->solutions as $solution) {
                $solutionCode = $solution->solutionCode;
                if (isset($method_per_code[$solutionCode])) {
                    $method = $method_per_code[$solutionCode];
                    if (isset($data_per_method[$method])) {
                        $data_per_method[$method]['visualTitle'] = $solution->communicationKit->visualTitle;
                        $data_per_method[$method]['visualDescription'] = $solution->communicationKit->visualDescription;
                        $data_per_method[$method]['visualInformationIcon'] = $solution->communicationKit->visualInformationIcon;
                        $data_per_method[$method]['visualAdditionalInformation'] = $solution->communicationKit->visualAdditionalInformation;
                        $data_per_method[$method]['visualLegalText'] = $solution->communicationKit->visualLegalText;
                        $data_per_method[$method]['visualTableImage'] = $solution->communicationKit->visualTableImage;
                        $data_per_method[$method]['visualLogo'] = $solution->communicationKit->visualLogo;
                        $data_per_method[$method]['visualInformationNoticeURL'] = $solution->communicationKit->visualInformationNoticeURL;
                        $data_per_method[$method]['visualProductTermsURL'] = $solution->communicationKit->visualProductTermsURL;


                        if (!in_array($solution->solutionCode, $this->getDeSolution())) {
                            array_push($finalSolutions, $solution->solutionCode);
                        }
                    }
                }
            }

            $simulations = $this->restApi->getSimulateSolutions($quotetotal, $finalSolutions, false);

            if ($simulations['status']) {
                foreach ($simulations['result']->solutionSimulations as $sim) {
                    foreach ($financing['result']->solutions as $solution) {
                        $solutionCode = $solution->solutionCode;
                        if (isset($method_per_code[$solutionCode])) {
                            $method = $method_per_code[$solutionCode];
                            if (isset($data_per_method[$method])) {
                                if ($solutionCode === $sim->solutionCode) {
                                    $durations = $sim->simulations;
                                    foreach ($durations as $duration) {
                                        $duration = json_decode(json_encode ($duration) , true);
                                        $data_per_method[$method]['simulate'][$duration['duration']]['simulations'] = $duration;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data_per_method;
    }

    public function getDeSolution()
    {
        return array_merge(
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE_WITH_FEES
        );
    }
}
