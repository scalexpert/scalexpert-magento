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
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;

class Warranty implements HttpGetActionInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * GetPopularBlock constructor.
     * @param Http $request
     * @param JsonFactory $jsonFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Http $request,
        JsonFactory $jsonFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $productId = $this->request->getParam('product_id');
        $price = $this->request->getParam('price');
        $html = '';
        $simulateLayout = 'scalexpert_simulate_product';

        if($productId){
            $html = $this->layoutFactory->create()
                ->createBlock('Scalexpert\Plugin\Block\FinancingAndInsurance\Product', $simulateLayout)
                ->setData('product_id', $productId)
                ->setData('price', $price)
                ->setTemplate('Scalexpert_Plugin::insurance/product.phtml')
                ->toHtml();
        }

        return $resultJson->setData([
            'result' => $html
        ]);
    }
}
