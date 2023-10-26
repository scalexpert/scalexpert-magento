<?php
namespace Scalexpert\Plugin\Model\Config\Source;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreRepository;
use Scalexpert\Plugin\Model\SystemConfigData;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertContracts\CollectionFactory;
use Scalexpert\Plugin\Model\ResourceModel\ScalexpertApiDefault\CollectionFactory as DefaultApiCollectionFactory;

class Disable extends Field
{

    /**
     * @var SystemConfigData
     */
    protected $systemConfigData;

    /**
     * @var CollectionFactory
     */
    protected $contractsCollectionFactory;

    /**
     * @var DefaultApiCollectionFactory
     */
    protected $defaultApiCollectionFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    public function __construct(\Magento\Backend\Model\Auth\Session $authSession, SystemConfigData $systemConfigData,
                                CollectionFactory $contractsCollectionFactory, Context $context,
                                DefaultApiCollectionFactory $defaultApiCollectionFactory,
                                StoreRepository $storeRepository,
                                array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        $this->authSession = $authSession;
        $this->systemConfigData = $systemConfigData;
        $this->contractsCollectionFactory = $contractsCollectionFactory;
        $this->defaultApiCollectionFactory = $defaultApiCollectionFactory;
        $this->storeRepository = $storeRepository;
        parent::__construct($context, $data, $secureRenderer);
    }

    protected function _getElementHtml(AbstractElement $element)
    {

        $websiteId = $this->getRequest()->getParam('website');
        $storeId = $this->getRequest()->getParam('store');
        $inherit = $element->getData('inherit');

        if($websiteId == null && $storeId == null){
            $currentScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $currentScopeToLoadId = 0;
        }else{
            if($websiteId == null){
                if(!$inherit){
                    $currentScope = ScopeInterface::SCOPE_STORES;
                    $store = $this->storeRepository->getById($storeId);
                    $currentScopeToLoadId = $storeId;
                }else{
                    $currentScope = ScopeInterface::SCOPE_WEBSITES;
                    $store = $this->storeRepository->getById($storeId);
                    $currentScopeToLoadId = $store->getWebsiteId();
                }

            }else{
                if(!$inherit){
                    $currentScope = ScopeInterface::SCOPE_WEBSITES;
                    $currentScopeToLoadId = $websiteId;
                }else{
                    $currentScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                    $currentScopeToLoadId = 0;
                }

            }
        }

        $scope = $currentScope;
        $scopeId = $currentScopeToLoadId;


        $isWarrantyConfig = strpos($element->getName(), "[warranty_extension]") !== false;
        $isEfunding3XConfig = strpos($element->getName(), "[e_funding][groups][payment_3x]") !== false;
        $isEfunding4XConfig = strpos($element->getName(), "[e_funding][groups][payment_4x]") !== false;
        $isEfundingLongCreditFRConfig = strpos($element->getName(), "[e_funding][groups][long_credit_fr]") !== false;
        $isEfundingLongCreditDEConfig = strpos($element->getName(), "[e_funding][groups][long_credit_de]") !== false;


        $disableField = $this->disableFieldByBackendElement($element,$scope,$scopeId);
        if($disableField) {
            $element->setDisabled('disabled');
        }
        $html = $element->getElementHtml();
        if($disableField) {
            $html.= '<p>'.__('This option is not available in your contract').'</p>';
            $default = 'en';
            $locale = substr($this->getAdminUserLocale(),0,2);
            $locale = (in_array($locale, array('fr','en'))) ? $locale : $default;
            $financing = ($locale == 'fr') ? 'e-financement' : 'e-financing';
            $warranty = ($locale == 'fr') ? 'garantie' : 'warranty';
            if($isWarrantyConfig){
                $html.= "<a href ='https://scalexpert.societegenerale.com/app/".$locale."/page/".$warranty."' target='_blank'>".__('Subscribe to this offer').'</a>';
            }elseif ($isEfunding3XConfig || $isEfunding4XConfig || $isEfundingLongCreditFRConfig || $isEfundingLongCreditDEConfig){
                $html.= "<a href ='https://scalexpert.societegenerale.com/app/".$locale."/page/".$financing."' target='_blank'>".__('Subscribe to this offer').'</a>';
            }else{
                $html.= "<a href ='https://dev.scalexpert.societegenerale.com/".$locale."/prod' target='_blank'>".__('Activate this option').'</a>';
            }
            return $html;
        }

        $path = $this->getPathByBackendElement($element);
        if($path == null){
            return $html;
        }
        $html.= $this->getApiDefaultConfig($path);
        return $html;
    }

    private function disableFieldByBackendElement($element,$scope,$scopeId){
        $isActivationConfig = strpos($element->getName(), "groups[activation]") !== false;
        $isWarrantyConfig = strpos($element->getName(), "[warranty_extension]") !== false;
        $isEfunding3XConfig = strpos($element->getName(), "[e_funding][groups][payment_3x]") !== false;
        $isEfunding4XConfig = strpos($element->getName(), "[e_funding][groups][payment_4x]") !== false;
        $isEfundingLongCreditFRConfig = strpos($element->getName(), "[e_funding][groups][long_credit_fr]") !== false;
        $isEfundingLongCreditDEConfig = strpos($element->getName(), "[e_funding][groups][long_credit_de]") !== false;

        $isWarrantyEnabled = $this->systemConfigData->getScalexpertConfigData(
            $this->systemConfigData::XML_SCALEXPERT_WARRANTY_ENABLE,$scope,$scopeId);
        $contractWarranty = $this->contractsCollectionFactory->create()
            ->addFieldToSelect('is_allowed')
            ->addFieldToFilter('scope',['eq' => $scope])
            ->addFieldToFilter('store',['eq' => $scopeId])
            ->addFieldToFilter('path',['eq',$this->systemConfigData::XML_SCALEXPERT_WARRANTY_ENABLE])
            ->getFirstItem();
        $isWarrantyAllowed = $contractWarranty->getData('is_allowed');

        $is3xEnabled = $this->systemConfigData->getScalexpertConfigData($this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE,$scope,$scopeId);
        $contract3x = $this->contractsCollectionFactory->create()
            ->addFieldToSelect('is_allowed')
            ->addFieldToFilter('scope',['eq' => $scope])
            ->addFieldToFilter('store',['eq' => $scopeId])
            ->addFieldToFilter('path',['eq',$this->systemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE])
            ->getFirstItem();
        $is3xAllowed = $contract3x->getData('is_allowed');


        $is4xEnabled = $this->systemConfigData->getScalexpertConfigData($this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE,$scope,$scopeId);
        $contract4x = $this->contractsCollectionFactory->create()
            ->addFieldToSelect('is_allowed')
            ->addFieldToFilter('scope',['eq' => $scope])
            ->addFieldToFilter('store',['eq' => $scopeId])
            ->addFieldToFilter('path',['eq',$this->systemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE])
            ->getFirstItem();
        $is4xAllowed = $contract4x->getData('is_allowed');

        $isLongCreditFREnabled = $this->systemConfigData->getScalexpertConfigData($this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE,$scope,$scopeId);
        $contractLongCreditFR = $this->contractsCollectionFactory->create()
            ->addFieldToSelect('is_allowed')
            ->addFieldToFilter('scope',['eq' => $scope])
            ->addFieldToFilter('store',['eq' => $scopeId])
            ->addFieldToFilter('path',['eq',$this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE])
            ->getFirstItem();
        $isLongCreditFRAllowed = $contractLongCreditFR->getData('is_allowed');

        $isLongCreditDEEnabled = $this->systemConfigData->getScalexpertConfigData($this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE,$scope,$scopeId);
        $contractLongCreditDE = $this->contractsCollectionFactory->create()
            ->addFieldToSelect('is_allowed')
            ->addFieldToFilter('scope',['eq' => $scope])
            ->addFieldToFilter('store',['eq' => $scopeId])
            ->addFieldToFilter('path',['eq',$this->systemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE])
            ->getFirstItem();
        $isLongCreditDEAllowed = $contractLongCreditDE->getData('is_allowed');

        $statusFlag = $this->systemConfigData->getScalexpertConfigData($this->systemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_STATUS_ACCESS,$scope,$scopeId);


        return
            ($statusFlag == null || $statusFlag == 0) ||
            ($isWarrantyConfig && !$isWarrantyEnabled && ($isWarrantyAllowed == 0)) ||
            ($isEfunding3XConfig && !$is3xEnabled && ($is3xAllowed == 0)) ||
            ($isEfunding4XConfig && !$is4xEnabled && ($is4xAllowed == 0)) ||
            ($isEfundingLongCreditFRConfig && !$isLongCreditFREnabled && ($isLongCreditFRAllowed == 0)) ||
            ($isEfundingLongCreditDEConfig && !$isLongCreditDEEnabled && ($isLongCreditDEAllowed == 0));
    }

    private function getPathByBackendElement($element){
        $path = null;

        /**
         * Warranty
         */
        $isWarrantyTitleConfig = $element->getName() ==
            "groups[warranty][groups][product][groups][customize_product_block][fields][title][value]";
        $isWarrantySubTitleConfig = $element->getName() ==
            "groups[warranty][groups][product][groups][customize_product_block][fields][sub_title][value]";
        $isWarrantyCartTitleConfig = $element->getName() ==
            "groups[warranty][groups][checkout_cart][groups][customize_checkout_cart_block][fields][title][value]";
        /**
         * Long Credit FR
         */
        $isCreditFrTitleConfig = $element->getName() ==
            "groups[e_funding][groups][long_credit_fr][groups][product][groups][customize_product_block][fields][title][value]";
        $isCreditLongFrPaymentTitleConfig = $element->getName() ==
            "groups[e_funding][groups][long_credit_fr][groups][payment_configuration][groups][customize_payment_method][fields][title][value]";
        $isCreditLongFrPaymentSubTitleConfig = $element->getName() ==
            "groups[e_funding][groups][long_credit_fr][groups][payment_configuration][groups][customize_payment_method][fields][sub_title][value]";
        /**
         * Long Credit DE
         */
        $isCreditDeTitleConfig = $element->getName() ==
            "groups[e_funding][groups][long_credit_de][groups][product][groups][customize_product_block][fields][title][value]";
        $isCreditLongDePaymentTitleConfig = $element->getName() ==
            "groups[e_funding][groups][long_credit_de][groups][payment_configuration][groups][customize_payment_method][fields][title][value]";
        $isCreditLongDePaymentSubTitleConfig = $element->getName() ==
            "groups[e_funding][groups][long_credit_de][groups][payment_configuration][groups][customize_payment_method][fields][sub_title][value]";
        /**
         * 4X
         */
        $is4XTitleConfig = $element->getName() ==
            "groups[e_funding][groups][payment_4x][groups][product][groups][customize_product_block][fields][title][value]";
        $is4XPaymentTitleConfig = $element->getName() ==
            "groups[e_funding][groups][payment_4x][groups][payment_configuration][groups][customize_payment_method][fields][title][value]";
        $is4XPaymentSubTitleConfig = $element->getName() ==
            "groups[e_funding][groups][payment_4x][groups][payment_configuration][groups][customize_payment_method][fields][sub_title][value]";
        /**
         * 3X
         */
        $is3XTitleConfig = $element->getName() ==
            "groups[e_funding][groups][payment_3x][groups][product][groups][customize_product_block][fields][title][value]";
        $is3XPaymentTitleConfig = $element->getName() ==
            "groups[e_funding][groups][payment_3x][groups][payment_configuration][groups][customize_payment_method][fields][title][value]";
        $is3XPaymentSubTitleConfig = $element->getName() ==
            "groups[e_funding][groups][payment_3x][groups][payment_configuration][groups][customize_payment_method][fields][sub_title][value]";
        switch (true){
            case $isWarrantyTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE;
                break;
            case $isWarrantySubTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_SUB_TITLE;
                break;
            case $isWarrantyCartTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_WARRANTY_CHECKOUT_CART_CUSTOMIZE_CHECKOUT_CART_BLOCK_TITLE;
                break;
            case $is3XTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE;
                break;
            case $is3XPaymentTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_TITLE;
                break;
            case $is3XPaymentSubTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_3X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE;
                break;
            case $is4XTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE;
                break;
            case $is4XPaymentTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_TITLE;
                break;
            case $is4XPaymentSubTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_PAYMENT_4X_PAYMENT_CONFIG_PAYMENT_SUB_TITLE;
                break;
            case $isCreditFrTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE;
                break;
            case $isCreditLongFrPaymentTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_TITLE;
                break;
            case $isCreditLongFrPaymentSubTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_FR_PAYMENT_CONFIG_PAYMENT_SUB_TITLE;
                break;
            case $isCreditDeTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PRODUCT_CUSTOMIZE_PRODUCT_BLOCK_TITLE;
                break;
            case $isCreditLongDePaymentTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_TITLE;
                break;
            case $isCreditLongDePaymentSubTitleConfig:
                $path = $this->systemConfigData::XML_SCALEXPERT_CUSTOMISATION_LONG_CREDIT_DE_PAYMENT_CONFIG_PAYMENT_SUB_TITLE;
                break;
            default:
                return null;
        }
        return $path;
    }

    public function getAdminUserLocale()
    {
        return $this->getCurrentAdminUser()->getInterfaceLocale();
    }

    public function getCurrentAdminUser()
    {
        return $this->authSession->getUser();
    }
    public function getApiDefaultConfig($path){
        $defaultConfigValue = $this->defaultApiCollectionFactory->create()
            ->addFieldToSelect('default_value')
            ->addFieldToFilter('path',['eq',$path])
            ->getFirstItem();
        if($defaultConfigValue->getData() != null){
            return "<p class='comment'>".__('Default value is : '). $defaultConfigValue->getData('default_value')."</p>";
        }else{
            return "";
        }
    }
}
