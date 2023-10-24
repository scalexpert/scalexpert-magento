<?php

namespace Scalexpert\Plugin\Controller\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Redirect implements HttpGetActionInterface
{
    protected $_restApi;
    protected $_checkoutSession;
    protected $_redirectFactory;
    protected $_orderFactory;
    protected $_messageManager;

    public function __construct
    (
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_restApi = $restApi;
        $this->_checkoutSession = $checkoutSession;
        $this->_redirectFactory = $redirectFactory;
        $this->_orderFactory = $orderFactory;
        $this->_messageManager = $messageManager;
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
            $this->_messageManager->addErrorMessage(__('A problem occurred with the Scalexpert API call'));
            $this->_messageManager->addErrorMessage($result['error_code'].' : '.$result['error_message']);
            $resultRedirect->setPath('checkout/cart');
        }

        return $resultRedirect;
    }
}
