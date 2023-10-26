<?php

namespace Scalexpert\Plugin\Block\Sales\Order;
use Magento\Sales\Model\ConfigInterface;

class Insurance extends \Magento\Sales\Block\Order\View
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 101.0.0
     */
    protected $httpContext;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ) {
        $this->_paymentHelper = $paymentHelper;
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $registry, $httpContext, $paymentHelper,$data);
    }

    public function getInsuranceInformation()
    {
        $order = $this->getOrder();
        $orderItems = $order->getAllItems();
        $options = array();
        foreach($orderItems as $orderItem){
            if($orderItem->getSku() == \Scalexpert\Plugin\Observer\CreateQuoteItemQuotation::INSURANCE_SKU){
                $options[] = $orderItem->getProductOptions();
            }
        }
        return $options;
    }
}
