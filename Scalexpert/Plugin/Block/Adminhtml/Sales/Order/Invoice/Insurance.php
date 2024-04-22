<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Block\Adminhtml\Sales\Order\Invoice;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\ConfigInterface;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Tax\Helper\Data as TaxHelper;

class Insurance extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Form
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_adminHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = [],
        ?ShippingHelper $shippingHelper = null,
        ?TaxHelper $taxHelper = null
    ) {
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        $data['shippingHelper'] = $shippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        $data['taxHelper'] = $taxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        parent::__construct($context, $registry, $adminHelper,$data);
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
