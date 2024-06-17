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

class Insurance extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Form
{
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
