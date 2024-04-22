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

use Magento\Framework\App\Request\DataPersistorInterface;

class Failure extends \Magento\Checkout\Block\Onepage\Failure
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param DataPersistorInterface $dataPersistor
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        DataPersistorInterface $dataPersistor,
        array $data = []
    )
    {
        parent::__construct($context, $checkoutSession, $data);
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return mixed
     */
    public function isScalexpertFailure()
    {
        return $this->dataPersistor->get('scalexpert_failure');
    }
}
