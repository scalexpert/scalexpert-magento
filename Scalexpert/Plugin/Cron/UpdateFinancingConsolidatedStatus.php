<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Cron;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\OrderFactory;
use Scalexpert\Plugin\Block\Payment\PaymentResponse;
use Scalexpert\Plugin\Model\RestApi;
use Scalexpert\Plugin\Model\ResourceModel\PaymentRedirect\CollectionFactory as PaymentRedirectCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

class UpdateFinancingConsolidatedStatus
{
    /**
     * @var RestApi
     */
    protected $restApi;

    /**
     * @var PaymentResponse
     */
    protected $paymentResponseBlock;

    /**
     * @var CollectionFactory
     */
    protected $salesOrderCollectionFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var PaymentRedirectCollectionFactory
     */
    protected $paymentRedirectCollectionFactory;

    /**
     * @var OrderRepository
     */
    protected $salesOrderRepository;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @param RestApi $restApi
     * @param PaymentResponse $paymentResponseBlock
     * @param CollectionFactory $salesOrderCollectionFactory
     * @param PaymentRedirectCollectionFactory $paymentRedirectCollectionFactory
     * @param OrderRepository $orderRepository
     * @param OrderFactory $orderFactory
     * @param Json $serializer
     */
    public function __construct(RestApi $restApi, PaymentResponse $paymentResponseBlock,
                                CollectionFactory $salesOrderCollectionFactory,
                                PaymentRedirectCollectionFactory $paymentRedirectCollectionFactory,
                                OrderRepository $orderRepository,
                                OrderFactory $orderFactory,
                                Json $serializer
    ) {
        $this->restApi = $restApi;
        $this->paymentResponseBlock = $paymentResponseBlock;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->paymentRedirectCollectionFactory = $paymentRedirectCollectionFactory;
        $this->salesOrderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->serializer = $serializer;
    }

    public function execute() {
        $paymentMethod = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_ALL_PAYMENT;
        $subscriptionIds = array();
        $salesOrderCollection = $this->salesOrderCollectionFactory->create()
            ->join(["sop" => "sales_order_payment"],
            'main_table.entity_id = sop.parent_id',
            array('method','additional_data'))
            ->addAttributeToFilter('sop.method',['in' => $paymentMethod]);

        foreach ($salesOrderCollection as $salesOrder){
            $additional = $salesOrder->getData('additional_data');
            if($additional){
                $dataPayment = $this->serializer->unserialize($additional);
                if(isset($dataPayment['credit_subscription_id'])){
                    $subscriptionIds[$dataPayment['credit_subscription_id']] = isset($dataPayment['last_update_timestamp']) ? $dataPayment['last_update_timestamp'] : '2023-01-01T00:00:01.00001+01:00';
                }
            }
        }

        $informationsApi = $this->restApi->getFinancingSubscriptions(9999,1);
        if($informationsApi['status'] && $informationsApi['result']){
            foreach ($informationsApi['result']->subscriptions as $k => $subscription){
                if(isset($subscriptionIds[$subscription->creditSubscriptionId]) && $subscriptionIds[$subscription->creditSubscriptionId] != $subscription->lastUpdateTimestamp){
                    try {
                        $order = $this->orderFactory->create()->loadByIncrementId($subscription->merchantGlobalOrderId);
                        $payment = $order->getPayment();
                        if($payment){
                            $updatedDataPayment = $this->serializer->unserialize($payment->getAdditionalData());
                            $updatedDataPayment['last_update_timestamp'] = $subscription->lastUpdateTimestamp;
                            $updatedDataPayment['registration_timestamp'] = $subscription->registrationTimestamp;
                            $updatedDataPayment['buyer_financedAmount'] = $subscription->buyerFinancedAmount;
                            $updatedDataPayment['consolidated_status'] = $subscription->consolidatedStatus;
                            $payment->setAdditionalData($this->serializer->serialize($updatedDataPayment));
                            $this->salesOrderRepository->save($order);
                            $this->restApi->writeLog("Order updated by cron, order id : ", $subscription->merchantGlobalOrderId);
                            $this->restApi->writeLog("Order updated by cron, status : ", $informationsApi['status']);
                            $this->restApi->writeLog("Order updated by cron, data : ", $subscription);
                        }
                    } catch(\Exception $e){
                    $this->restApi->writeLog("Can't save order id ",$subscription->merchantGlobalOrderId);
                }
                }
            }
        }
        else{
            $this->restApi->writeLog("Can't get list of financing subscriptions",$informationsApi['error-message']);
        }


        $insuranceSubscriptionIds = array();
        $salesOrderItemCollection = $this->salesOrderCollectionFactory->create();
        foreach($salesOrderItemCollection as $salesOrder){
            $items = $salesOrder->getAllItems();
            foreach($items as $item){
                if($item->getSku() == \Scalexpert\Plugin\Observer\CreateQuoteItemQuotation::INSURANCE_SKU){
                    $productOptions = $item->getProductOptions();
                    if(isset($productOptions['info_insurance'][0]['consolidated_status']) && is_array($productOptions['info_insurance'][0]['consolidated_status'])) {
                        foreach ($productOptions['info_insurance'][0]['consolidated_status'] as $subscriptionId => $status) {
                            $insuranceSubscriptionIds[$subscriptionId] = $item->getId();
                        }
                    }
                }
            }
        }

        $infoInsuranceApi = $this->restApi->getInsuranceSubscriptions(9999,1);
        if($infoInsuranceApi['status'] && $infoInsuranceApi['result']){
            foreach ($infoInsuranceApi['result']->subscriptions as $k => $subscription){
                if(isset($insuranceSubscriptionIds[$subscription->insuranceSubscriptionId])) {
                    $orderCollection = $this->orderFactory->create();
                    $order = $orderCollection->loadByIncrementId($subscription->merchantGlobalOrderId);
                    try {
                        $item = $order->getItemById($insuranceSubscriptionIds[$subscription->insuranceSubscriptionId]);
                        if($item){
                            $productOptions = $item->getProductOptions();
                            $productOptions['info_insurance'][0]['consolidated_status'][$subscription->insuranceSubscriptionId] = $subscription->consolidatedStatus;
                            $item->setProductOptions($productOptions);
                            $item->save();
                            $this->restApi->writeLog("Order updated by cron, order id : ", $subscription->merchantGlobalOrderId);
                            $this->restApi->writeLog("Order updated by cron, status : ", $infoInsuranceApi['status']);
                            $this->restApi->writeLog("Order updated by cron, data : ", $subscription);
                        }
                    }catch(\Exception $e){
                        $this->restApi->writeLog("Can't save order id ",$subscription->merchantGlobalOrderId);
                    }
                }
            }
        }
        else{
            $this->restApi->writeLog("Can't get list of insurance subscriptions",$infoInsuranceApi['error-message']);
        }
    }
}
