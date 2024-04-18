<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Plugin\Magento\Quote\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class QuoteManagement
{
    protected $manager;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $manager
    ) {
        $this->manager = $manager;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param Quote $quote
     * @param $orderData
     * @return array
     * @throws LocalizedException
     */
    public function beforeSubmit(\Magento\Quote\Model\QuoteManagement $subject, Quote $quote, $orderData = [])
    {
        $paymentCode = $quote->getPayment()->getMethod();
        $isScalexpertPayment = in_array($paymentCode,
            \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_ALL_CODE
        );

        if ($isScalexpertPayment) {
            $billingTelephone = $quote->getBillingAddress()->getTelephone();
            $shippingTelephone = $quote->getShippingAddress()->getTelephone();
            $regex = '/^(\+?\d{2}|0)?\d{9,12}$/';

            if (!preg_match($regex, $billingTelephone) || !preg_match($regex, $shippingTelephone)) {
                throw new LocalizedException(
                    __('Please specify a valid phone number for this payment')
                );
            }
        }

        return [$quote, $orderData];
    }
}
