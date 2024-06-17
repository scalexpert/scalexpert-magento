<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Controller\Ajax;

use \Magento\Framework\App\Action\HttpGetActionInterface;

class Index implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * GetPopularBlock constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $productId = $this->request->getParam('product_id');
        $simulateLayout = 'scalexpert_simulate_product';

        $html = $this->layoutFactory->create()
            ->createBlock('Scalexpert\Plugin\Block\FinancingAndInsurance\Product', $simulateLayout)
            ->setData('product_id', $productId)
            ->setTemplate('Scalexpert_Plugin::simulate/product.phtml')
            ->toHtml();

        return $resultJson->setData([
            'result' => $html
        ]);
    }
}
