<?php

namespace Scalexpert\Plugin\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\ConfigInterface;

class Insurance extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * Reorder helper
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $_reorderHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        ConfigInterface $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        $this->_reorderHelper = $reorderHelper;
        $this->_coreRegistry = $registry;
        $this->_salesConfig = $salesConfig;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper,$data);
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
