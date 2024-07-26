<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Plugin\Magento\Sales\Block\Adminhtml\Order;

use \Magento\Framework\Serialize\Serializer\Json;
use \Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * @param OrderView $view
     */
    public function beforeSetLayout(OrderView $view)
    {
        $paymentMethod = $view->getOrder()->getPayment()->getMethod();

        if ($view->getOrder()->getStatus() !== \Magento\Sales\Model\Order::STATE_CANCELED) {
            if ($paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR
                || $paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITH_FEES
                || $paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR_WITHOUT_FEES
                || $paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE
                || $paymentMethod === \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE_WITH_FEES
            ) {
                if ($view->getOrder()->getPayment()->getAdditionalData()) {
                    $methodAdditional = $this->json->unserialize($view->getOrder()->getPayment()->getAdditionalData());
                    if ($methodAdditional['consolidated_status'] !== 'ACCEPTED') {
                        $view->removeButton('order_ship');
                    }
                } else {
                    $view->removeButton('order_ship');
                }
            }
        }
    }
}
