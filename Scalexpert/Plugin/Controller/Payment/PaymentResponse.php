<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Controller\Payment;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\OrderRepository;
use Scalexpert\Plugin\Block\Payment\PaymentResponse as PaymentResponseBlock;
use Scalexpert\Plugin\Model\ResourceModel\PaymentRedirect\CollectionFactory;
use Scalexpert\Plugin\Model\RestApi;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;

class PaymentResponse implements HttpPostActionInterface, HttpGetActionInterface, CsrfAwareActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_redirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var RestApi
     */
    protected $restApi;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CollectionFactory
     */
    protected $paymentRedirectCollectionFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var OrderRepository
     */
    protected $salesOrderRepository;

    /**
     * @var PaymentResponseBlock
     */
    protected $block;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var Json|null
     */
    private $serializer;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param CollectionFactory $paymentRedirectCollectionFactory
     * @param RestApi $restApi
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param OrderRepository $orderRepository
     * @param PaymentResponseBlock $paymentResponseBlock
     * @param CheckoutSession $checkoutSession
     * @param CustomerRepository $customerRepository
     * @param Json $serializer
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     */
    public function __construct
    (
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RequestInterface $request,
        CollectionFactory $paymentRedirectCollectionFactory,
        RestApi $restApi,
        \Magento\Framework\App\Http\Context $httpContext,
        OrderRepository $orderRepository,
        PaymentResponseBlock $paymentResponseBlock,
        CheckoutSession $checkoutSession,
        CustomerRepository $customerRepository,
        Json $serializer,
        InvoiceService $invoiceService,
        Transaction $transaction
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_redirectFactory = $redirectFactory;
        $this->_messageManager = $messageManager;
        $this->request = $request;
        $this->paymentRedirectCollectionFactory = $paymentRedirectCollectionFactory;
        $this->restApi = $restApi;
        $this->httpContext = $httpContext;
        $this->salesOrderRepository = $orderRepository;
        $this->block = $paymentResponseBlock;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->serializer = $serializer;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
    }

    public function execute()
    {
        $searchId = $this->request->getParam('id');
        $title = 'Your order failed !';

        // payment response is post but cancel link is get
        if ($this->request->isPost()) {
            if($searchId != null) {
                $paymentRedirectCollection = $this->paymentRedirectCollectionFactory->create();
                $paymentRedirectCollection->addFieldToFilter('coordonates_id',['eq' => $searchId]);
                $paymentRedirect = $paymentRedirectCollection->getFirstItem();

                if($paymentRedirect == null) {
                    $this->restApi->writeLog("Something went wrong saving the api payment redirect",null);
                } else {
                    $orderId = $paymentRedirect->getOrderId();
                    try {
                        $order = $this->salesOrderRepository->get($orderId);
                        $incrementId = $order->getIncrementId();
                        $informationsApi = $this->block->getFinancialOrder($incrementId);
                        $status = $informationsApi['consolidated_status'];
                        $payment = $order->getPayment();
                        $dataPayment = array(
                            'credit_subscription_id' => $informationsApi['api_result']->subscriptions[0]->creditSubscriptionId,
                            'registration_timestamp' => $informationsApi['api_result']->subscriptions[0]->registrationTimestamp,
                            'last_update_timestamp' => $informationsApi['api_result']->subscriptions[0]->lastUpdateTimestamp,
                            'solution_code' => $informationsApi['api_result']->subscriptions[0]->solutionCode,
                            'merchant_basket_id' => $informationsApi['api_result']->subscriptions[0]->merchantBasketId,
                            'merchant_global_order_id' => $informationsApi['api_result']->subscriptions[0]->merchantGlobalOrderId,
                            'buyer_financedAmount' => $informationsApi['api_result']->subscriptions[0]->buyerFinancedAmount,
                            'consolidated_status' => $informationsApi['api_result']->subscriptions[0]->consolidatedStatus
                        );

                        $payment->setAdditionalData($this->serializer->serialize($dataPayment));

                        if(in_array(
                            $status, [
                                $this->block::PAYMENT_STATUS_NO_SUBSCRIPTION,
                                $this->block::PAYMENT_STATUS_ABORTED,
                                $this->block::PAYMENT_STATUS_CANCELLED
                            ]
                        )){
                            $order->cancel();
                        } else {
                            $paymentStatusIsAccepted = $status === $this->block::PAYMENT_STATUS_ACCEPTED;
                            $orderCouldBeInvoice = $order->canInvoice() && $paymentStatusIsAccepted;
                            if ($orderCouldBeInvoice) {
                                $invoice = $this->invoiceService->prepareInvoice($order);
                                $invoice->register();
                                $transactionSave = $this->transaction->addObject(
                                    $invoice
                                )->addObject(
                                    $invoice->getOrder()
                                );
                                $transactionSave->save();
                            }
                            $title = 'Thank you for your purchase!';
                        }

                        $this->salesOrderRepository->save($order);
                        $this->restApi->writeLog("Successfully Set status ".$status." to order ",$orderId);

                        if($paymentRedirect->getCustomerId() == null){
                            $this->restApi->writeLog("Checkout as guest",null);
                        } else {
                            $this->httpContext->setValue(Context::CONTEXT_AUTH,true,true);
                            $customerId = $paymentRedirect->getCustomerId();
                            $this->restApi->writeLog("Rebuild session for customer ", $customerId);
                            $this->_customerSession->setCustomerId($customerId);
                            $customer = $this->customerRepository->getById($customerId);
                            $this->checkoutSession->setCustomerData($customer);
                            $this->checkoutSession->setQuoteId($order->getQuoteId());
                            $this->checkoutSession->loadCustomerQuote();
                            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
                        }
                        $resultPage = $this->resultPageFactory->create();
                        $resultPage->getLayout()->getBlock('payment.response')->setData('scalexpert_order_id', $orderId);
                        $resultPage->getLayout()->getBlock('payment.response')->setData('title', $title);
                        $resultPage->getLayout()->getBlock('payment.response')->setData('increment_id', $incrementId);
                        return $resultPage;

                    } catch (\Exception $e){
                        $this->restApi->writeLog("Can't save order id ",$orderId);
                    }
                }
            }
        } else {
            //cancel link process from api
            $orderId = $this->request->getParam('order_id');

            if ($orderId !== null) {
                $order = $this->salesOrderRepository->get($orderId);
                $order->cancel();
                $this->salesOrderRepository->save($order);
                $this->_messageManager->addErrorMessage(__('Your order is cancelled!'));
                $resultRedirect = $this->_redirectFactory->create();
                return $resultRedirect->setPath('checkout/onepage/failure');
            }
        }


        $this->_messageManager->addErrorMessage(__('Invalid api return : id is missing !'));
        $this->restApi->writeLog("Invalid api return : id is missing",null);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('payment.response')->setData('title', 'An error has occurred !');
        $resultPage->getLayout()->getBlock('payment.response')->setData('error', true);
        return $resultPage;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
