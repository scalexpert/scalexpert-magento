<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Block\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;
use Scalexpert\Plugin\Model\RestApi;

class PaymentResponse extends \Magento\Checkout\Block\Onepage\Success
{
    const PAYMENT_STATUS_NO_SUBSCRIPTION = self::PAYMENT_STATUS_REJECTED;
    const PAYMENT_STATUS_INITIALIZED = "INITIALIZED";

    const PAYMENT_STATUS_REQUESTED = "REQUESTED";
    const PAYMENT_STATUS_PRE_ACCEPTED = "PRE_ACCEPTED";
    const PAYMENT_STATUS_ACCEPTED = "ACCEPTED";
    const PAYMENT_STATUS_ABORTED = "ABORTED";
    const PAYMENT_STATUS_CANCELLED = "CANCELLED";
    const PAYMENT_STATUS_REJECTED = "REJECTED";


    protected $_restApi;
    protected $_checkoutSession;

    public function __construct(Context $context, Session $checkoutSession, Config $orderConfig,
                                \Magento\Framework\App\Http\Context $httpContext,
                                RestApi $restApi, array $data = [])
    {
        $this->_restApi = $restApi;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    public function getFinancialOrder($merchantGlobalOrderId)
    {
        $result = $this->_restApi->getFinancingSubscriptionsByOrderId($merchantGlobalOrderId);
        $apiResult = $result['result'];
        $apiReturn = [
            'api_result' => $apiResult,
            'totalItemCount' => $apiResult->totalItemCount
        ];
        if($apiReturn['totalItemCount'] > 0) {
            $apiReturn['consolidated_status'] = $apiResult->subscriptions[0]->consolidatedStatus;
            $apiReturn['merchant_global_order_id'] = $apiResult->subscriptions[0]->merchantGlobalOrderId;
        } else{
            $apiReturn['consolidated_status'] = self::PAYMENT_STATUS_NO_SUBSCRIPTION;
        }
        return $apiReturn;
    }
}
