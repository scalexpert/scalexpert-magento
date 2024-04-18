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

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\OrderRepository;

class Redirect implements HttpGetActionInterface
{
    /**
     * @var \Scalexpert\Plugin\Model\RestApi
     */
    protected $_restApi;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_redirectFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var OrderRepository
     */
    protected $salesOrderRepository;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param \Scalexpert\Plugin\Model\RestApi $restApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param OrderRepository $orderRepository
     * @param Json $serializer
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct
    (
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        OrderRepository $orderRepository,
        Json $serializer,
        DataPersistorInterface $dataPersistor
    )
    {
        $this->_restApi = $restApi;
        $this->_checkoutSession = $checkoutSession;
        $this->_redirectFactory = $redirectFactory;
        $this->_orderFactory = $orderFactory;
        $this->_messageManager = $messageManager;
        $this->salesOrderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->dataPersistor = $dataPersistor;
    }


    public function execute()
    {
        $lastIncrementId = $this->_checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($lastIncrementId);
        $result = $this->_restApi->createFinancingSubscription($order);

        $resultRedirect = $this->_redirectFactory->create();

        if($result['status']){
            $redirectUrl = $result['result']->redirect->value;
            $resultRedirect->setUrl($redirectUrl);
        } else {
            $order->cancel();
            $payment = $order->getPayment();
            $dataPayment = array(
                'status_message_error' => __('Canceled credit')
            );

            $payment->setAdditionalData($this->serializer->serialize($dataPayment));
            $this->salesOrderRepository->save($order);
            $this->dataPersistor->set('scalexpert_failure', true);
            $this->_messageManager->addErrorMessage(__('Your order is cancelled!'));
            $resultRedirect->setPath('checkout/onepage/failure');
        }

        return $resultRedirect;
    }
}
