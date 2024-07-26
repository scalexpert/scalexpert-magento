<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Scalexpert\Plugin\Model\RestApi;
use \Magento\Framework\Serialize\Serializer\Json;

class SalesOrderShipmentBefore implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RestApi
     */
    protected $restApi;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param ManagerInterface $messageManager
     * @param RestApi $restApi
     * @param Json $json
     */
    public function __construct(
        ManagerInterface $messageManager,
        RestApi $restApi,
        Json $json
    ) {
        $this->messageManager = $messageManager;
        $this->restApi = $restApi;
        $this->json = $json;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();

        if ($paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR
            || $paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITH_FEES
            || $paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITHOUT_FEES
        ) {
            //check if shiping has tracking number
            $tracking = $observer->getEvent()->getShipment()->getTracks();
            if (isset($tracking[0])) {
                $trackingNumber = $tracking[0]->getData()['track_number'];
                $carrierCode = $tracking[0]->getData()['carrier_code'];
                $methodAdditional = $this->json->unserialize($order->getPayment()->getAdditionalData());

                // api confirm delivery
                $confirmApiDelivery = $this->restApi->confirmDeliveryFinancingSubscription(
                    $methodAdditional['credit_subscription_id'],
                    $carrierCode,
                    $trackingNumber
                );

                if (!$confirmApiDelivery['status']) {
                    $this->messageManager->addErrorMessage(__('Cannot create confirm delivery scalexpert.'));
                    throw new \Exception('Cannot create confirm delivery scalexpert.');
                }

            } else {
                $this->messageManager->addErrorMessage(__('Tracking number cannot be empty.'));
                throw new \Exception('Tracking number cannot be empty.');
            }
        }
    }
}
