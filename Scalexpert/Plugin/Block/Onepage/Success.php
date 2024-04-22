<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Block\Onepage;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    public function isPaymentOffline()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        return $order->getPayment()
            ->getMethodInstance()
            ->isOffline();
    }

    public function orderHaveInsurance()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        $orderItems = $order->getAllItems();
        foreach($orderItems as $orderItem){
            if($orderItem->getSku() == \Scalexpert\Plugin\Observer\CreateQuoteItemQuotation::INSURANCE_SKU){
                return true;
            }
        }
        return false;
    }
}
