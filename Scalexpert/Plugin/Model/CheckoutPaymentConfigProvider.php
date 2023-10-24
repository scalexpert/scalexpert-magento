<?php

namespace Scalexpert\Plugin\Model;


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

        $quotetotal = $quote->getBaseSubtotal();
        $countryId = $quote->getBillingAddress()->getCountryId();
        $financing = $this->restApi->getFinancingEligibleSolutions($quotetotal, $countryId);
        $codes_per_methods = [
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X => \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X => \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR => \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE => \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE,
        ];
        $method_per_code = [];
        foreach ($codes_per_methods as $method => $codes) {
            foreach ($codes as $code) {
                $method_per_code[$code] = $method;
            }
        }


        $data_per_method = [
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X => [
                'customTitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_TITLE),
                'customSubtitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => ''
            ],
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X => [
                'customTitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_TITLE),
                'customSubtitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => ''
            ],
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR => [
                'customTitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_TITLE),
                'customSubtitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => ''
            ],
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE => [
                'customTitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_TITLE),
                'customSubtitle' => $this->scopeConfig->getValue(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
                'visualTitle' => '',
                'visualDescription' => '',
                'visualInformationIcon' => '',
                'visualAdditionalInformation' => '',
                'visualLegalText' => '',
                'visualTableImage' => '',
                'visualLogo' => '',
                'visualInformationNoticeURL' => '',
                'visualProductTermsURL' => ''
            ],
        ];


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
                    }
                }
            }
        }

        return $data_per_method;
    }

}
