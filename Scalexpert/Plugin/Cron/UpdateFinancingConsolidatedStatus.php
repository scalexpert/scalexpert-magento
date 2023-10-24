<?php

namespace Scalexpert\Plugin\Cron;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
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
     * @param Json $serializer
     */
    public function __construct(RestApi $restApi, PaymentResponse $paymentResponseBlock,
                                CollectionFactory $salesOrderCollectionFactory,
                                PaymentRedirectCollectionFactory $paymentRedirectCollectionFactory,
                                OrderRepository $orderRepository,
                                Json $serializer
    ) {
        $this->restApi = $restApi;
        $this->paymentResponseBlock = $paymentResponseBlock;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->paymentRedirectCollectionFactory = $paymentRedirectCollectionFactory;
        $this->salesOrderRepository = $orderRepository;
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
                $subscriptionIds[$dataPayment['credit_subscription_id']] = isset($dataPayment['last_update_timestamp']) ? $dataPayment['last_update_timestamp'] : null;
            }
        }

        $informationsApi = $this->restApi->getFinancingSubscriptions(9999,1);
        if($informationsApi['status'] && $informationsApi['result']){
            foreach ($informationsApi['result']->subscriptions as $k => $subscription){
                if(isset($subscriptionIds[$subscription->creditSubscriptionId]) && $subscriptionIds[$subscription->creditSubscriptionId] != $subscription->lastUpdateTimestamp){
                    $order = $this->salesOrderRepository->get($subscription->merchantGlobalOrderId);
                    try {
                        $payment = $order->getPayment();
                        $updatedDataPayment = $this->serializer->unserialize($payment->getAdditionalData());
                        $updatedDataPayment['last_update_timestamp'] = $subscription->lastUpdateTimestamp;
                        $updatedDataPayment['buyer_financedAmount'] = $subscription->buyerFinancedAmount;
                        $updatedDataPayment['consolidated_status'] = $subscription->consolidatedStatus;
                        $payment->setAdditionalData($this->serializer->serialize($updatedDataPayment));
                        $this->salesOrderRepository->save($order);
                        $this->restApi->writeLog("Order updated by cron, order id : ", $subscription->merchantGlobalOrderId);
                        $this->restApi->writeLog("Order updated by cron, status : ", $informationsApi['status']);
                        $this->restApi->writeLog("Order updated by cron, data : ", $subscription);
                    } catch(\Exception $e){
                    $this->restApi->writeLog("Can't save order id ",$subscription->merchantGlobalOrderId);
                }
                }
            }
        }
        else{
            $this->restApi->writeLog("Can't get list of subscriptions ",$informationsApi['error-message']);
        }
    }
}
