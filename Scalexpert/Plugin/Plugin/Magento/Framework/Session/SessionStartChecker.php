<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Plugin\Magento\Framework\Session;

/**
 * Class: SessionChecker
 *
 * Exclude the return URL that our plugin is using to POST back the data to Magento.
 */
class SessionStartChecker
{

    /**
     * Array
     */
    const PAYMENT_RETURN_PATHS = [
        'scalexpert/payment/paymentresponse'
    ];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
    }

    public function afterCheck(
        \Magento\Framework\Session\SessionStartChecker $subject,
        $result
    ) {
        if ($result === false) {
            return false;
        }

        if ($this->request->getFrontName() === 'scalexpert') {
            foreach (self::PAYMENT_RETURN_PATHS as $path) {
                if (strpos((string)$this->request->getPathInfo(), $path) !== false) {
                    return false;
                }
            }
        }

        return true;
    }
}
