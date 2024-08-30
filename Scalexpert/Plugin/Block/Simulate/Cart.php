<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Block\Simulate;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class Cart extends \Magento\Checkout\Block\Cart
{
    protected $systemConfigData;
    protected $productRepository;
    protected $restApi;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        ProductRepositoryInterface $productRepository,
        \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        parent::__construct($context, $customerSession, $checkoutSession, $catalogUrlBuilder, $cartHelper, $httpContext, $data);
        $this->systemConfigData = $systemConfigData;
        $this->productRepository = $productRepository;
        $this->restApi = $restApi;
        $this->scopeConfig = $scopeConfig;
    }

    public function getSimulateData()
    {
        $items = $this->getItems();

        return $this->getProductFinancing($this->getQuote()->getBaseGrandTotal(), $items);
    }


    public function getProductFinancing($amount, $items)
    {

        $configurationInsertBlockCart = $this->getConfigurationInsertBlockCart();

        $enabled_solutions = array();

        $financing = $this->getFinancingEligibleSolutions($amount);

        $finalSolutions = array();
        $excluded = array();
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

            foreach ($items as $item) {
                if ($item->getSku() === "Insurance") {
                    return false;
                }
                $productId = $item->getProductId();
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
                    } else {
                        continue 1;
                    }
                    $solution->communicationKit->magentoConfiguration['logo'] = $configurationInsertBlockCart['logo'];
                    if(!isset($solution->communicationKit->magentoConfiguration)
                        || !$solution->communicationKit->magentoConfiguration['enable']
                        || !$solution->communicationKit->magentoConfiguration['show']
                        || $solution->communicationKit->magentoConfiguration['excluded_category']
                        || $solution->communicationKit->magentoConfiguration['excluded_product']
                    ) {
                        if ($solution->communicationKit->magentoConfiguration['excluded_category']
                            || $solution->communicationKit->magentoConfiguration['excluded_product']) {
                            array_push($excluded, $solution->solutionCode);
                        }
                        continue 1;
                    }

                    if (!in_array($solution->solutionCode, $this->getDeSolution())) {
                        array_push($finalSolutions, $solution->solutionCode);
                    } else {
                        $enabled_solutions[] = $solution->communicationKit;
                    }
                }
            }
            $finalSolutions = array_unique($finalSolutions);

            if (!empty($excluded)) {
                $excluded = array_unique($excluded);
                $finalSolutions = array_diff( $finalSolutions, $excluded );
            }
            $simulations = false;
            if($finalSolutions != array()){
                $simulations = $this->restApi->getSimulateSolutions(
                    $amount,
                    $finalSolutions,
                    false
                );
            }
            if (isset($simulations['status']) && isset($simulations['result'])) {
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
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_CHECKOUT_SHOW_CHECKOUT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE),
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
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_CHECKOUT_SHOW_CHECKOUT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_WITH_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE),
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
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_CHECKOUT_SHOW_CHECKOUT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_CUSTOMIZE_CHECKOUT_BLOCK_TITLE),
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
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_CHECKOUT_SHOW_CHECKOUT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_WITH_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE),
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
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_CHECKOUT_SHOW_CHECKOUT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE),
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
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WiTH_FEES_CHECKOUT_SHOW_CHECKOUT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITH_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE),
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
            'show' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WiTHOUT_FEES_CHECKOUT_SHOW_CHECKOUT_BLOCK_ENABLE),
            'title' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_WITHOUT_FEES_CHECKOUT_CUSTOMIZE_CHECKOUT_BLOCK_TITLE),
            'excluded_category' => $excludedCategory,
            'excluded_product' => $excludedProduct
        );
    }

    public function getConfigurationInsertBlockCart()
    {
        return array(
            'position' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_INSERT_CHECKOUT_BLOCK_POSITION),
            'logo' => $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_CUSTOMISATION_INSERT_CHECKOUT_BLOCK_LOGO_ENABLE),
        );
    }

    public function getFinancingEligibleSolutions($amount, $countryId = 'FR')
    {
        return $this->restApi->getFinancingEligibleSolutions($amount, $countryId);
    }

    public function getDeSolution()
    {
        return \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_DE_SOLUTION;
    }

    public function getCountryId()
    {
        return $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $simulateLayout = 'scalexpert_simulate_product';
        $configurationInsertBlockCart = $this->getConfigurationInsertBlockCart();
        $positions = array($configurationInsertBlockCart['position'],$simulateLayout);
        return (in_array($this->getNameInLayout(),$positions) ? parent::_toHtml() : '');
    }


}
