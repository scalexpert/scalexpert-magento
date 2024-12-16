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
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

class Cart extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    protected $restApi;
    protected $systemConfigData;
    protected $scalexpertHelper;
    /**
     * @var Json|null
     */
    private $serializer;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        ItemResolverInterface $itemResolver = null,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData,
        \Scalexpert\Plugin\Model\Helper $scalexpertHelper,
        Json $serializer,
        array $data = []
    )
    {
        $this->restApi = $restApi;
        $this->systemConfigData = $systemConfigData;
        $this->scalexpertHelper = $scalexpertHelper;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
        parent::__construct($context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );
    }
    /* @var $item \Magento\Quote\Model\Quote\Item */
    public function getFinancingEligibleSolutions($countryId = 'FR')
    {
        $amount = $this->getProduct()->getPrice();

        return $this->restApi->getFinancingEligibleSolutions($amount, $countryId);
    }

    public function getWarranty($insuranceInfos)
    {
        $quote = $this->checkoutSession->getQuote();
            if($insuranceInfos){
                $parentItem = $quote->getItemById($insuranceInfos[0]['quote_item_id']);
                $product = $parentItem->getProduct();
                $price = $product->getFinalPrice();
            }
            else{
                $product = $this->getProduct();
                $price = '';
            }
        return $this->scalexpertHelper->getWarranty($product, $price);
    }

    public function getConfigurationPayment3xBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        if($configurationExcludedCategory) {
            $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
            $productCategoryIds = $this->getProduct()->getCategoryIds();

            if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
                $excludedCategory = true;
            }
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        if ($configurationExcludedProduct) {
            $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
            $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
            $productSku = $this->getProduct()->getSku();

            if (in_array($productSku, $configurationExcludedProduct)) {
                $excludedProduct = true;
            }
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_TITLE),
            'subtitle' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationPayment4xBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        if($configurationExcludedCategory) {
            $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
            $productCategoryIds = $this->getProduct()->getCategoryIds();

            if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
                $excludedCategory = true;
            }
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        if ($configurationExcludedProduct) {
            $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
            $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
            $productSku = $this->getProduct()->getSku();

            if (in_array($productSku, $configurationExcludedProduct)) {
                $excludedProduct = true;
            }
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_TITLE),
            'subtitle' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditFrBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        if($configurationExcludedCategory) {
            $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
            $productCategoryIds = $this->getProduct()->getCategoryIds();

            if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
                $excludedCategory = true;
            }
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        if ($configurationExcludedProduct) {
            $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
            $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
            $productSku = $this->getProduct()->getSku();

            if (in_array($productSku, $configurationExcludedProduct)) {
                $excludedProduct = true;
            }
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_TITLE),
            'subtitle' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationLongCreditDeBlockProduct()
    {
        $excludedCategory = false;
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY);
        if($configurationExcludedCategory) {
            $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
            $productCategoryIds = $this->getProduct()->getCategoryIds();

            if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
                $excludedCategory = true;
            }
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_PRODUCT);
        if ($configurationExcludedProduct) {
            $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
            $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
            $productSku = $this->getProduct()->getSku();

            if (in_array($productSku, $configurationExcludedProduct)) {
                $excludedProduct = true;
            }
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_ENABLE),
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_POSITION),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_TITLE),
            'subtitle' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_SUB_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationWarrantyBlockProduct($insuranceInfos)
    {
        $excludedCategory = false;
        $quote = $this->checkoutSession->getQuote();
        $configurationExcludedCategory = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_EXCLUDE_CATEGORY);
        if($configurationExcludedCategory) {
            $configurationExcludedCategory = explode(',', $configurationExcludedCategory ?? '');
            if($insuranceInfos){
                $parentItem = $quote->getItemById($insuranceInfos[0]['quote_item_id']);
                $productCategoryIds = $parentItem->getProduct()->getCategoryIds();
            }
            else{
                $productCategoryIds = $this->getProduct()->getCategoryIds();
            }
            if (array_intersect($productCategoryIds, $configurationExcludedCategory)) {
                $excludedCategory = true;
            }
        }

        $excludedProduct = false;
        $configurationExcludedProduct = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_EXCLUDE_PRODUCT);
        if ($configurationExcludedProduct) {
            $configurationExcludedProduct = str_replace('|', ';', $configurationExcludedProduct ?? '');
            $configurationExcludedProduct = explode(';', $configurationExcludedProduct ?? '');
            if($insuranceInfos){
                $parentItem = $quote->getItemById($insuranceInfos[0]['quote_item_id']);
                $productSku = $parentItem->getProduct()->getSku();
            }
            else{
                $productSku = $this->getProduct()->getSku();
            }

            if (in_array($productSku, $configurationExcludedProduct)) {
                $excludedProduct = true;
            }
        }

        return array(
            'enable' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_SHOW_CHECKOUT_CART_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_TITLE),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_LOGO_ENABLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function isInsertEnabled($warranty, $insuranceInfos){
        $configurationWarrantyBlockProduct = $this->getConfigurationWarrantyBlockProduct($insuranceInfos);

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

    public function getProductWarranty($quoteItem) {
        $insuranceInfos = $this->scalexpertHelper->getCurrentInsuranceForQuoteItem($quoteItem);
        $configurationWarrantyBlockProduct = $this->getConfigurationWarrantyBlockProduct($insuranceInfos);
        $warranty = $this->getWarranty($insuranceInfos);

        $informations = [];

        if (false !== $warranty['solutions']['status']) {
            $configurationWarrantyBlockProductTitle = $configurationWarrantyBlockProduct['title'];
            $apiTitle = $warranty['solutions']['result']->solutions[0]->communicationKit->visualTitle;

            $configurationWarrantyBlockProductLogo = $configurationWarrantyBlockProduct['logo'];
            $apiLogo = $warranty['solutions']['result']->solutions[0]->communicationKit->visualLogo;

            $informations['enabled'] = $this->isInsertEnabled($warranty, $insuranceInfos);

            $informationTitle = ($configurationWarrantyBlockProductTitle != null &&
                $configurationWarrantyBlockProductTitle != '') ? $configurationWarrantyBlockProductTitle : $apiTitle;
            $informations['title'] = $informationTitle;

            $informationLogo = ($configurationWarrantyBlockProductLogo) ? $apiLogo : null;
            $informations['logo'] = $informationLogo;

            $informations['code'] = $warranty['solutions']['result']->solutions[0]->communicationKit->solutionCode;
            $informations['additional'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualAdditionalInformation;
            $informations['legal_text'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualLegalText;
            $informations['notice'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualInformationNoticeURL;
            $informations['terms'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualProductTermsURL;
            $informations['picto_info'] = $warranty['solutions']['result']->solutions[0]->communicationKit->visualInformationIcon;

        $insuranceInformations = [];
        $insuranceItemId = false;
        if(count($warranty['items']) > 0) {
            $insurances = $warranty['items'][0]['result']->insurances;
            $insuranceItemId = $warranty['items'][0]['result']->itemId;
            foreach ($insurances as $insurance) {
                $insuranceInformations[] = [
                    'insurance_id' => $insurance->id,
                    'description' => $insurance->description,
                    'price' => $insurance->price
                ];
            }
        }

            $insuranceItems = $this->scalexpertHelper->getCurrentInsuranceForQuote();
            $insuranceItemsProductName = false;
            $insuranceQuotation = false;
            if ($insuranceInfos) {
                $insuranceItemsProductName = $this->scalexpertHelper->getCurrentInsuranceProductNameForQuote($insuranceInfos[0]['quote_item_id']);
                $insuranceQuotation = $insuranceInfos[0]['insurance_id'];
            }
            if (count($insuranceInformations) > 1) {
                array_unshift($insuranceInformations, array(
                    'insurance_id' => 0,
                    'description' => __('No warranty extension'),
                    'price' => 0
                ));
            }

            $informations['insurances'] = $insuranceInformations;
            $informations['quotations_insurance_id'] = $insuranceQuotation;
            $informations['items_insurance_id'] = $insuranceItems;
            $informations['items_insurance_product_name'] = $insuranceItemsProductName;
            if ($insuranceItemId) {
                $informations['insurance_item_id'] = $insuranceItemId;
            }
        }
        return $informations;
    }

}
