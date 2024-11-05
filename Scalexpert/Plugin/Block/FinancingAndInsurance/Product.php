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
    const TYPE_SIMPLE = 'simple';

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
        $amount = $this->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        return $this->restApi->getFinancingEligibleSolutions($amount, $countryId);
    }

    public function getWarranty($countryId = 'FR')
    {
        return $this->scalexpertHelper->getWarranty($this->getProduct(), $countryId);
    }

    public function getConfigurationPayment3xBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace(';', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(',', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();

        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationPayment3xWithFeesBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace(';', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(',', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();

        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_3X_WITH_FEES_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationPayment4xBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();
        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationPayment4xWithFeesBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();
        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_4X_WITH_FEES_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditFrBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();

        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditFrWithFeesBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();

        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_WITH_FEES_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditFrWithoutFeesBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(
            \Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(
            \Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();

        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_WITHOUT_FEES_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditDeBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();

        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditDeWithFeesBlockProduct($product)
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
//        $productCategoryIds = $this->getProduct()->getCategoryIds();
        $productCategoryIds = $product->getCategoryIds();

        if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
            $excludedCategory = true;
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
        $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
//        $productSku = $this->getProduct()->getSku();
        $productSku = $product->getSku();

        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
        if (in_array($productSku, $configurationExcludedProduct)) {
            $excludedProduct = true;
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_WITH_FEES_ENABLE),
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_WITH_FEES_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE),
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
        $configurationExcludedProduct = array_map('trim', $configurationExcludedProduct);
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

    public function getConfigurationInsertBlockProduct()
    {
        return array(
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_INSERT_PRODUCT_BLOCK_POSITION),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_INSERT_PRODUCT_BLOCK_LOGO_ENABLE),
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

        if (false !== $warranty['solutions']['status'] && isset($warranty['solutions']['result']->solutions[0])) {
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

    public function getProductFinancing($productId) {
        $product = $this->productRepository->getById($productId);
        $configurationPayment3xBlockProduct = $this->getConfigurationPayment3xBlockProduct($product);
        $configurationPayment3xWithFeesBlockProduct = $this->getConfigurationPayment3xWithFeesBlockProduct($product);
        $configurationPayment4xBlockProduct = $this->getConfigurationPayment4xBlockProduct($product);
        $configurationPayment4xWithFeesBlockProduct = $this->getConfigurationPayment4xWithFeesBlockProduct($product);
        $configurationLongCreditFrBlockProduct = $this->getConfigurationLongCreditFrBlockProduct($product);
        $configurationLongCreditFrWithFeesBlockProduct = $this->getConfigurationLongCreditFrWithFeesBlockProduct($product);
        $configurationLongCreditFrWithoutFeesBlockProduct = $this->getConfigurationLongCreditFrWithoutFeesBlockProduct($product);
        $configurationLongCreditDeBlockProduct = $this->getConfigurationLongCreditDeBlockProduct($product);
        $configurationLongCreditDeWithFeesBlockProduct = $this->getConfigurationLongCreditDeWithFeesBlockProduct($product);
        $configurationInsertBlockProduct = $this->getConfigurationInsertBlockProduct();

        $enabled_solutions = array();
        $countryId = $this->getCountryId();
        $financing = $this->getFinancingEligibleSolutions($countryId);
//        $amount = $this->getProduct()->getPrice();
        $amount = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $finalSolutions = array();
        if($financing['status']){
            $paymentCode3XSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X;
            $paymentCode3XWithFeesSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X_WITH_FEES;
            $paymentCode4XSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X;
            $paymentCode4XWithFeesSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X_WITH_FEES;
            $LongCreditFrSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR;
            $LongCreditFrWithFeesSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR_WITH_FEES;
            $LongCreditFrWithoutFeesSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR_WITHOUT_FEES;
            $longCreditDeSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE;
            $longCreditDeWithFeesSolutionCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE_WITH_FEES;

            foreach($financing['result']->solutions as $solution){
                if (in_array($solution->solutionCode, $paymentCode3XSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationPayment3xBlockProduct;
                }
                else if (in_array($solution->solutionCode, $paymentCode3XWithFeesSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationPayment3xWithFeesBlockProduct;
                }
                else if (in_array($solution->solutionCode, $paymentCode4XSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationPayment4xBlockProduct;
                }
                else if (in_array($solution->solutionCode, $paymentCode4XWithFeesSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationPayment4xWithFeesBlockProduct;
                }
                else if (in_array($solution->solutionCode, $LongCreditFrSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationLongCreditFrBlockProduct;
                }
                else if (in_array($solution->solutionCode, $LongCreditFrWithFeesSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationLongCreditFrWithFeesBlockProduct;
                }
                else if (in_array($solution->solutionCode, $LongCreditFrWithoutFeesSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationLongCreditFrWithoutFeesBlockProduct;
                }
                else if (in_array($solution->solutionCode, $longCreditDeSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationLongCreditDeBlockProduct;
                }
                else if (in_array($solution->solutionCode, $longCreditDeWithFeesSolutionCode)) {
                    $solution->communicationKit->magentoConfiguration = $configurationLongCreditDeWithFeesBlockProduct;
                }
                else{
                    continue 1;
                }
                $solution->communicationKit->magentoConfiguration['logo'] = $configurationInsertBlockProduct['logo'];
                if(!isset($solution->communicationKit->magentoConfiguration)
                    || !$solution->communicationKit->magentoConfiguration['enable']
                    || !$solution->communicationKit->magentoConfiguration['show']
                    || $solution->communicationKit->magentoConfiguration['excluded_category']
                    || $solution->communicationKit->magentoConfiguration['excluded_product']
                ) {
                    continue 1;
                }

                if (!in_array($solution->solutionCode, $this->getDeSolution())) {
                    array_push($finalSolutions, $solution->solutionCode);
                } else {
                    $enabled_solutions[] = $solution->communicationKit;
                }
            }
            $simulations = false;
            if($finalSolutions != array()){
                $simulations = $this->restApi->getSimulateSolutions(
                    $amount,
                    $finalSolutions,
                    false
                );
            }
            if (isset($simulations['status'])) {
                foreach ($simulations['result']->solutionSimulations as $sim) {
                    foreach($financing['result']->solutions as $solution) {
                        if ($solution->solutionCode === $sim->solutionCode) {
                            $durations = $sim->simulations;
                            foreach ($durations as $duration)
                            {
                                $code = $solution->communicationKit->solutionCode;
                                $merchantKit = json_decode(json_encode ($solution->communicationKit) , true);
                                $duration = json_decode(json_encode ($duration) , true);
                                $enabled_solutions[$duration['duration'].'-'.$code]['solutionCode'] = $code;
                                $enabled_solutions[$duration['duration'].'-'.$code]['merchantkit'] = $merchantKit;
                                $enabled_solutions[$duration['duration'].'-'.$code]['simulations'] = $duration;
                                if (!is_null($enabled_solutions[$duration['duration'].'-'.$code]['merchantkit']['magentoConfiguration']['title'])) {
                                    $enabled_solutions[$duration['duration'].'-'.$code]['merchantkit']['visualTitle'] =
                                        $enabled_solutions[$duration['duration'].'-'.$code]['merchantkit']['magentoConfiguration']['title'];
                                }
                                if ($enabled_solutions[$duration['duration'].'-'.$code]['merchantkit']['magentoConfiguration']['logo'] === '0') {
                                    $enabled_solutions[$duration['duration'].'-'.$code]['merchantkit']['visualLogo'] = null;
                                }
                            }
                        }
                    }
                }
            }
        }

        ksort($enabled_solutions, SORT_NATURAL);
        return $enabled_solutions;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {

        $configurationWarrantyBlockProduct = $this->getConfigurationWarrantyBlockProduct();
        $configurationInsertBlockProduct = $this->getConfigurationInsertBlockProduct();
        $simulateLayout = 'scalexpert_simulate_product';
        $insertDe = '';
        switch ($configurationInsertBlockProduct['position']) {
            case 'catalog.product.view.after.title.scalexpert.simulate':
                $insertDe = 'catalog.product.view.after.title.scalexpert.credit.de.insert';
                break;
            case 'catalog.product.view.before.qty.scalexpert.simulate':
                if($this->getProduct()->getTypeId() != self::TYPE_SIMPLE){
                    $configurationInsertBlockProduct['position'] = 'catalog.product.view.before.qty.scalexpert.simulate.conf';
                    $insertDe = 'catalog.product.view.before.qty.scalexpert.credit.de.insert.conf';
                }
                else{
                    $insertDe = 'catalog.product.view.before.qty.scalexpert.credit.de.insert';
                }
                break;
            case 'catalog.product.view.after.addtocart.scalexpert.simulate':
                $insertDe = 'catalog.product.view.after.addtocart.scalexpert.credit.de.insert';
                break;
        }
        if($this->getProduct()->getTypeId() != self::TYPE_SIMPLE && $configurationWarrantyBlockProduct['position'] == 'catalog.product.view.before.qty.scalexpert.warranty.insert'){
            $configurationWarrantyBlockProduct['position'] = 'catalog.product.view.before.qty.scalexpert.warranty.insert.conf';
        }
        $positions = array($configurationWarrantyBlockProduct['position'], $configurationInsertBlockProduct['position'], $simulateLayout, $insertDe);
        return (in_array($this->getNameInLayout(),$positions) ? parent::_toHtml() : '');
    }

    public function getDeSolution()
    {
        return \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_DE_SOLUTION;
    }

    public function getCountryId()
    {
        return $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
    }
}
