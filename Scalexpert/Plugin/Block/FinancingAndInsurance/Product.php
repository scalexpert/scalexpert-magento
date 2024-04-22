<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Block\FinancingAndInsurance;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class Product extends \Magento\Catalog\Block\Product\View
{
    protected $restApi;
    protected $systemConfigData;
    protected $scalexpertHelper;
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Scalexpert\Plugin\Logger\Logger $logger,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData,
        \Scalexpert\Plugin\Model\Helper $scalexpertHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->logger = $logger;
        $this->restApi = $restApi;
        $this->systemConfigData = $systemConfigData;
        $this->scalexpertHelper = $scalexpertHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    public function getFinancingEligibleSolutions($countryId = 'FR')
    {
        $amount = $this->getProduct()->getPrice();

        return $this->restApi->getFinancingEligibleSolutions($amount, $countryId);
    }

    public function getWarranty($countryId = 'FR')
    {
        return $this->scalexpertHelper->getWarranty($this->getProduct(), $countryId);
    }

    public function getConfigurationPayment3xBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
        $productCategoryIds = $this->getProduct()->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace(';', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(',', $configurationExcludedProduct ?? '');
        $productSku = $this->getProduct()->getSku();

        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationPayment4xBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
        $productCategoryIds = $this->getProduct()->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
        $productSku = $this->getProduct()->getSku();
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditFrBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
        $productCategoryIds = $this->getProduct()->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
        $productSku = $this->getProduct()->getSku();

        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditDeBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
        $productCategoryIds = $this->getProduct()->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
        $productSku = $this->getProduct()->getSku();

        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationWarrantyBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
        $productCategoryIds = $this->getProduct()->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
        $productSku = $this->getProduct()->getSku();

        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'sub_title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_SUB_TITLE),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function isInsertEnabled($warranty){
        $configurationWarrantyBlockProduct = $this->getConfigurationWarrantyBlockProduct();

        $isEnabledIsConfigurationBlockProduct = $configurationWarrantyBlockProduct['enable'];
        $isProductCategoryInExcludedCategories = $configurationWarrantyBlockProduct['excluded_category'];
        $isProductExcluded = $configurationWarrantyBlockProduct['excluded_product'];
        $isWarrantyFirstItemStatusTrue = false;
        if(isset($warranty['items'][0]['status']) && $warranty['items'][0]['status']){
            $isWarrantyFirstItemStatusTrue = $warranty['items'][0]['status'];
        }

        return ($isEnabledIsConfigurationBlockProduct
            && !$isProductCategoryInExcludedCategories
            && !$isProductExcluded
            && $isWarrantyFirstItemStatusTrue);
    }

    public function getProductWarranty() {
        $configurationWarrantyBlockProduct = $this->getConfigurationWarrantyBlockProduct();
        $countryId = $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
        $warranty = $this->getWarranty($countryId);

        $informations = [];

        if (false !== $warranty['solutions']['status']) {
            $configurationWarrantyBlockProductTitle = $configurationWarrantyBlockProduct['title'];
            $apiTitle = $warranty['solutions']['result']->solutions[0]->communicationKit->visualTitle;

            $configurationWarrantyBlockProductSubTitle = $configurationWarrantyBlockProduct['sub_title'];
            $apiSubTitle = $warranty['solutions']['result']->solutions[0]->communicationKit->visualDescription;

            $configurationWarrantyBlockProductLogo = $configurationWarrantyBlockProduct['logo'];
            $apiLogo = $warranty['solutions']['result']->solutions[0]->communicationKit->visualLogo;

            $informations['enabled'] = $this->isInsertEnabled($warranty);

            $informationTitle = ($configurationWarrantyBlockProductTitle != null &&
                $configurationWarrantyBlockProductTitle != '') ? $configurationWarrantyBlockProductTitle : $apiTitle;
            $informations['title'] = $informationTitle;

            $informationSubTitle = ($configurationWarrantyBlockProductSubTitle != null &&
                $configurationWarrantyBlockProductSubTitle != '') ? $configurationWarrantyBlockProductSubTitle : $apiSubTitle;
            $informations['sub_title'] = $informationSubTitle;

            $informationLogo = ($configurationWarrantyBlockProductLogo) ? $apiLogo : null;
            $informations['logo'] = $informationLogo;

            $informations['code'] = $warranty['solutions']['result']->solutions[0]->communicationKit->solutionCode;
            $informations['additional'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualAdditionalInformation;
            $informations['legal_text'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualLegalText;
            $informations['notice'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualInformationNoticeURL;
            $informations['terms'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualProductTermsURL;
            $informations['picto_info'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualInformationIcon;
            $insuranceInformations = [];
            $quotationsInformationsId = false;
            if (count($warranty['items']) > 0) {
                $insurances = $warranty['items'][0]['result']->insurances;
                $productSku = $this->getProduct()->getSku();
                $insuranceQuotation = $this->scalexpertHelper->getCurrentInsuranceQuotationForProduct($productSku);
                foreach ($insurances as $insurance) {
                    $insuranceInformations[] = [
                        'insurance_id' => $insurance->id,
                        'description' => $insurance->description,
                        'price' => $insurance->price,
                    ];
                }
                if ($insuranceQuotation) {
                    $quotationsInformationsId = $insuranceQuotation;
                }
            }

            if (count($insuranceInformations) > 1) {
                array_unshift($insuranceInformations, array(
                    'insurance_id' => 0,
                    'description' => __('No warranty extension'),
                    'price' => 0
                ));
            }
            $informations['quotations_insurance_id'] = $quotationsInformationsId;
            $informations['insurances'] = $insuranceInformations;
        }
        return $informations;
    }

    public function getProductFinancing() {
        $configurationPayment3xBlockProduct = $this->getConfigurationPayment3xBlockProduct();
        $configurationPayment4xBlockProduct = $this->getConfigurationPayment4xBlockProduct();
        $configurationLongCreditFrBlockProduct = $this->getConfigurationLongCreditFrBlockProduct();
        $configurationLongCreditDeBlockProduct = $this->getConfigurationLongCreditDeBlockProduct();

        $enabled_solutions = array();
        $countryId = $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
        $fincancing = $this->getFinancingEligibleSolutions($countryId);
        if($fincancing['status']){
            $paymentCode3XSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X;
            $paymentCode4XSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X;
            $LongCreditFrSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR;
            $longCreditDeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE;

            foreach($fincancing['result']->solutions as $solution){
                if (in_array($solution->solutionCode, $paymentCode3XSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationPayment3xBlockProduct;
                }
                if (in_array($solution->solutionCode, $paymentCode4XSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationPayment4xBlockProduct;
                }
                if (in_array($solution->solutionCode, $LongCreditFrSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationLongCreditFrBlockProduct;
                }
                if (in_array($solution->solutionCode, $longCreditDeSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationLongCreditDeBlockProduct;
                }
                if(!isset($solution->communicationKit->magentoConfiguration)
                    || !$solution->communicationKit->magentoConfiguration['enable']
                    || !$solution->communicationKit->magentoConfiguration['show']
                    || $solution->communicationKit->magentoConfiguration['excluded_category']
                    || $solution->communicationKit->magentoConfiguration['excluded_product']
                ) {
                    continue 1;
                }
                $enabled_solutions[] = $solution->communicationKit;
            }
        }
        return $enabled_solutions;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {

        $configurationWarrantyBlockProduct = $this->getConfigurationWarrantyBlockProduct();
        $configurationPayment3xBlockProduct = $this->getConfigurationPayment3xBlockProduct();
        $configurationPayment4xBlockProduct = $this->getConfigurationPayment4xBlockProduct();
        $configurationLongCreditFrBlockProduct = $this->getConfigurationLongCreditFrBlockProduct();
        $configurationLongCreditDeBlockProduct = $this->getConfigurationLongCreditDeBlockProduct();
        $positions = array($configurationWarrantyBlockProduct['position'],$configurationPayment3xBlockProduct['position'],$configurationPayment4xBlockProduct['position'],$configurationLongCreditFrBlockProduct['position'],$configurationLongCreditDeBlockProduct['position']);

        return (in_array($this->getNameInLayout(),$positions) ? parent::_toHtml() : '');
    }

}
