<?php

namespace Scalexpert\Plugin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\ScopeInterface;

class SystemConfigData
{

    const XML_SCALEXPERT_ENABLE = 'scalexpert_administration/configuration/active';
    const XML_SCALEXPERT_PLATFORM_ACCESS_MODE = 'scalexpert_administration/platform_access/mode';
    const XML_SCALEXPERT_PLATFORM_ACCESS_ID_TEST = 'scalexpert_administration/platform_access/id_test';
    const XML_SCALEXPERT_PLATFORM_ACCESS_KEY_TEST = 'scalexpert_administration/platform_access/key_test';
    const XML_SCALEXPERT_PLATFORM_ACCESS_ID_PROD = 'scalexpert_administration/platform_access/id_prod';
    const XML_SCALEXPERT_PLATFORM_ACCESS_KEY_PROD = 'scalexpert_administration/platform_access/key_prod';

    const XML_SCALEXPERT_PLATFORM_ACCESS_STATUS_ACCESS = 'scalexpert_administration/platform_access/status_access';
    const XML_SCALEXPERT_WARRANTY_ENABLE = 'scalexpert_administration/activation/warranty_extension/active';
    const XML_SCALEXPERT_PAYMENT_3X_ENABLE = 'scalexpert_administration/activation/e_funding/payment_3x/active';
    const XML_SCALEXPERT_PAYMENT_4X_ENABLE = 'scalexpert_administration/activation/e_funding/payment_4x/active';
    const XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE = 'scalexpert_administration/activation/e_funding/long_credit_fr/active';
    const XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE = 'scalexpert_administration/activation/e_funding/long_credit_de/active';


    /**
     * xml path for warranty configuration
     */
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE = 'scalexpert_warranty/product_block/active';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION = 'scalexpert_warranty/product_block/position';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE = 'scalexpert_warranty/product_block/title';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_SUB_TITLE = 'scalexpert_warranty/product_block/subtitle';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE = 'scalexpert_warranty/product_block/show_logo';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_SHOW_CHECKOUT_CART_BLOCK_ENABLE = 'scalexpert_warranty/cart_block/active';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_TITLE = 'scalexpert_warranty/cart_block/title';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_LOGO_ENABLE = 'scalexpert_warranty/cart_block/show_logo';
    const XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_EXCLUDE_CATEGORY = 'scalexpert_warranty/cart_block/exclude_categories';

    /**
     * xml path for payment 3X configuration
     */
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE = 'scalexpert_payment3x/product_block/active';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION = 'scalexpert_payment3x/product_block/position';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE = 'scalexpert_payment3x/product_block/title';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE = 'scalexpert_payment3x/product_block/show_logo';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_ENABLE = 'scalexpert_payment3x/payment_method/active';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_POSITION = 'scalexpert_payment3x/payment_method/position';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_TITLE = 'scalexpert_payment3x/payment_method/title';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE = 'scalexpert_payment3x/payment_method/subtitle';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY = 'scalexpert_payment3x/payment_method/exclude_categories';

    /**
     * xml path for payment 4X configuration
     */
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE = 'scalexpert_payment4x/product_block/active';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION = 'scalexpert_payment4x/product_block/position';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE = 'scalexpert_payment4x/product_block/title';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE = 'scalexpert_payment4x/product_block/show_logo';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_ENABLE = 'scalexpert_payment4x/payment_method/active';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_POSITION = 'scalexpert_payment4x/payment_method/position';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_TITLE = 'scalexpert_payment4x/payment_method/title';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE = 'scalexpert_payment4x/payment_method/subtitle';
    const XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY = 'scalexpert_payment4x/payment_method/exclude_categories';

    /**
     * xml path for long credit FR configuration
     */
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE = 'scalexpert_long_credit_fr/product_block/active';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION = 'scalexpert_long_credit_fr/product_block/position';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE = 'scalexpert_long_credit_fr/product_block/title';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE = 'scalexpert_long_credit_fr/product_block/show_logo';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_ENABLE = 'scalexpert_long_credit_fr/payment_method/active';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_POSITION = 'scalexpert_long_credit_fr/payment_method/position';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_TITLE = 'scalexpert_long_credit_fr/payment_method/title';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_SUB_TITLE = 'scalexpert_long_credit_fr/payment_method/subtitle';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY = 'scalexpert_long_credit_fr/payment__method/exclude_categories';

    /**
     * xml path for long credit DE configuration
     */
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_SHOW_PRODUCT_BLOCK_ENABLE = 'scalexpert_long_credit_de/product_block/active';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_SHOW_PRODUCT_BLOCK_POSITION = 'scalexpert_long_credit_de/product_block/position';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE = 'scalexpert_long_credit_de/product_block/title';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_LOGO_ENABLE = 'scalexpert_long_credit_de/product_block/show_logo';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_ENABLE = 'scalexpert_long_credit_de/payment_method/active';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_POSITION = 'scalexpert_long_credit_de/payment_method/position';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_TITLE = 'scalexpert_long_credit_de/payment_method/title';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_SUB_TITLE = 'scalexpert_long_credit_de/payment_method/subtitle';
    const XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_EXCLUDE_CATEGORY = 'scalexpert_long_credit_de/payment__method/exclude_categories';


    const XML_SCALEXPERT_DEBUG_ENABLE = 'scalexpert_support/debug/active';



    const SCALEXPERT_PAYMENT_CODES_3X = ['SCFRSP-3XTS', 'SCFRSP-3XPS'];
    const SCALEXPERT_PAYMENT_CODES_4X = ['SCFRSP-4XTS', 'SCFRSP-4XPS'];
    const SCALEXPERT_PAYMENT_CODES_LONG_FR = ['SCFRLT-TXPS', 'SCFRLT-TXNO'];
    const SCALEXPERT_PAYMENT_CODES_LONG_DE = ['SCDELT-DXTS', 'SCDELT-DXCO'];


    const SCALEXPERT_MAGENTO_CODE_3X = 'scalexpert_payment_3x';
    const SCALEXPERT_MAGENTO_CODE_4X = 'scalexpert_payment_4x';
    const SCALEXPERT_MAGENTO_CODE_LONG_FR = 'scalexpert_long_credit_fr';
    const SCALEXPERT_MAGENTO_CODE_LONG_DE = 'scalexpert_long_credit_de';
    const SCALEXPERT_MAGENTO_CODE_ALL_PAYMENT = ['scalexpert_payment_3x', 'scalexpert_payment_4x','scalexpert_long_credit_fr','scalexpert_long_credit_de'];

    const SCALEXPERT_MAGENTO_ALL_CODE = array(
        self::SCALEXPERT_MAGENTO_CODE_3X,
        self::SCALEXPERT_MAGENTO_CODE_4X,
        self::SCALEXPERT_MAGENTO_CODE_LONG_FR,
        self::SCALEXPERT_MAGENTO_CODE_LONG_DE
    );


    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @param $xmlPath
     * @param $storeScope
     * @param $storeId
     * @return mixed
     */
    public function getScalexpertConfigData($xmlPath, $storeScope = ScopeInterface::SCOPE_STORE, $storeId = null)
    {
        return $this->scopeConfig->getValue($xmlPath, $storeScope,$storeId);
    }


    /**
     * @param $xmlPath
     * @param $value
     * @param $scope
     * @param $scopeId
     * @return void
     */
    public function setScalexpertConfigData($xmlPath, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $this->configWriter->save($xmlPath, $value, $scope, $scopeId);
    }

    public function replaceDiv($str){
        if($str){
            $str = str_replace('<div class=scalexpert_title>', '', $str);
            $str = str_replace('</div>', '', $str);
        }
        return $str;
    }
}
