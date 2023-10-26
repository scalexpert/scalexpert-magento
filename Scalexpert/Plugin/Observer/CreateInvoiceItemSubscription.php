<?php

namespace Scalexpert\Plugin\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Scalexpert\Plugin\Logger\Logger;
use Scalexpert\Plugin\Model\RestApi;
use Scalexpert\Plugin\Model\SystemConfigData;

class CreateInvoiceItemSubscription implements ObserverInterface
{
    protected $logger;

    /**
     * @var SystemConfigData
     */
    protected $systemConfigData;

    /**
     * @var RestApi
     */
    protected $restApi;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $messageManager;

    /**
     * @var Json|null
     */
    private $serializer;

    /**
     * @param SystemConfigData $systemConfigData
     * @param RestApi $restApi
     * @param Logger $logger
     * @param RequestInterface $request
     * @param CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Json $serializer
     */
    public function __construct(
        SystemConfigData $systemConfigData,
        RestApi $restApi,
        Logger $logger,
        RequestInterface $request,
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        Json $serializer,
        ManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->systemConfigData = $systemConfigData;
        $this->restApi = $restApi;
        $this->request = $request;
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {

        $invoice_items =  $observer->getEvent()->getInvoice()->getItems();
        $order = $observer->getEvent()->getInvoice()->getOrder();
        foreach ($invoice_items as $invoice_item)
        {
            if ($invoice_item->getSku() === \Scalexpert\Plugin\Observer\CreateQuoteItemQuotation::INSURANCE_SKU) {
                $productOptions = $invoice_item->getOrderItem()->getProductOptions();
                foreach ($invoice_items as $invoiced_item)
                {
                    if($productOptions['additional_options'][0]['label'] == $invoiced_item->getName() && $invoiced_item->getQty() != $invoice_item->getQty()){
                        $this->messageManager->addNoticeMessage(__("La quantité de l'assurance ne peut pas être différente de la quantité du produit assuré."));
                        throw new \Exception("La quantité de l'assurance ne peut pas être différente de la quantité du produit assuré.");
                    }
                }
                $insuranceInformations = $productOptions['info_insurance'];

                if($insuranceInformations){
                    foreach ($insuranceInformations as $insuranceInformation) {
                        $solutionCode = $insuranceInformation['solution_code'];
                        $insuranceId = $insuranceInformation['insurance_id'];
                        $quote = $this->quoteRepository->get($order->getQuoteId());
                        $quotePrice = $insuranceInformation['insurance_price'];
                        $quoteExpiration = $insuranceInformation['insurance_expiration_date'];
                        $insuranceItemId = $insuranceInformation['insurance_item_id'];
                        $productItem = $quote->getItemById($insuranceInformation['quote_item_id'])->getProduct();
                        $quoteIds = $insuranceInformation['insurance_quote_id'];
                    }
                    $count = 1;
                    foreach ($quoteIds as $quoteId) {
                        if($count <= $invoice_item->getQty()) {
                            $subscription_result = $this->restApi->createInsuranceSubscription($solutionCode, $quoteId, $insuranceId, $order, $quoteExpiration, $quotePrice, $insuranceItemId, $productItem);
                            if ($subscription_result['result']) {
                                $productOptions['info_insurance'][0]['consolidated_status'][$subscription_result['result']->insuranceSubscriptionId] = $subscription_result['result']->consolidatedStatus;
                                $count ++;
                            }
                            else{
                                throw new \Exception($subscription_result['error-message']);
                            }
                        }
                    }
                    $invoice_item->getOrderItem()->setProductOptions($productOptions);
                }
            }
        }
        return $this;
    }

}
