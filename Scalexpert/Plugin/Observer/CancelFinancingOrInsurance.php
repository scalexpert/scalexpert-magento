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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CancelFinancingOrInsurance implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Scalexpert\Plugin\Model\RestApi
     */
    protected $restApi;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    protected $messageManager;

    /**
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Scalexpert\Plugin\Model\RestApi $restApi
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        ManagerInterface $messageManager
    ) {
        $this->json = $json;
        $this->restApi = $restApi;
        $this->storeRepository = $storeRepository;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {


        $payment = $observer->getEvent()->getCreditmemo()->getOrder()->getPayment();
        $method = $payment->getMethod();

        $scalExpertMethodsCode = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_ALL_CODE;


        if (in_array($method,$scalExpertMethodsCode)) {
            $methodAdditional = $this->json->unserialize($payment->getAdditionalData());
            $amount = $observer->getEvent()->getCreditmemo()->getBaseGrandTotal();
            $refundFinancing = $this->restApi->cancelFinancingSubscription($methodAdditional['credit_subscription_id'], $amount);
            $informationsApi = $this->restApi->getFinancingSubscriptionsByOrderId($observer->getEvent()->getCreditmemo()->getOrder()->getIncrementId());
            if ($refundFinancing['status'] && $informationsApi['status']) {
                $methodAdditional['consolidated_status'] = $informationsApi['result']->subscriptions[0]->consolidatedStatus;
                $methodAdditional['consolidated_sub_status'] = $informationsApi['result']->subscriptions[0]->consolidatedSubstatus;
                $methodAdditional['buyer_financedAmount'] = $informationsApi['result']->subscriptions[0]->buyerFinancedAmount;
                $methodAdditional['last_update_timestamp'] = $informationsApi['result']->subscriptions[0]->lastUpdateTimestamp;
                $payment->setAdditionalData($this->json->serialize($methodAdditional));
            }
        else{
                throw new \Exception($refundFinancing['error-message']);
            }
        } else {

            $items = $observer->getEvent()->getCreditmemo()->getItems();


            foreach ($items as $item)
            {
                if ($item->getSku() === \Scalexpert\Plugin\Observer\CreateQuoteItemQuotation::INSURANCE_SKU) {
                    $productOptions = $item->getOrderItem()->getProductOptions();
                    foreach ($items as $refundItem)
                    {
                        if($productOptions['additional_options'][0]['label'] == $refundItem->getName() && $refundItem->getQty() != $item->getQty()){
                            $this->messageManager->addNoticeMessage(__("The insurance qty cannot be different from the quantity of the insured product."));
                            throw new \Exception("The insurance qty cannot be different from the quantity of the insured product.");
                        }
                    }
                    if (floatval($item->getQty() * $item->getBasePriceInclTax()) === floatval($item->getBaseRowTotalInclTax())) {
                        $insuranceSubscriptionIds = $productOptions['info_insurance'][0]['consolidated_status'];
                        $store = $this->storeRepository->getById($item->getStoreId());
                        $count = 1;
                        foreach($insuranceSubscriptionIds as $insuranceSubscriptionId => $status){
                            if($status == 'SUBSCRIBED' && $count <= $item->getQty()){
                                $refundInsurance = $this->restApi->cancelInsuranceSubscription($insuranceSubscriptionId, $store->getName());
                                if ($refundInsurance['status']) {
                                    $productOptions['info_insurance'][0]['consolidated_status'][$insuranceSubscriptionId] = 'CANCELLED';
                                    $count ++;
                                }
                                else{
                                    throw new \Exception($refundInsurance['error-message']);
                                }
                            }
                        }
                        $item->getOrderItem()->setProductOptions($productOptions);
                    }
                }
            }
        }

        return $this;
    }
}
