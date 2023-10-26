<?php

namespace Scalexpert\Plugin\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\App\State;

class AddInsuranceVirtualProduct implements DataPatchInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    protected $_appState;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        \Magento\Framework\App\State $appState
    )
    {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->_appState = $appState;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    public function apply()
    {
        $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $product = $this->productFactory->create();
        $product->setSku('Insurance');
        $product->setName('Assurance');
        $product->setAttributeSetId(4);
        $product->setStatus(1);
        $product->setVisibility(1);
        $product->setTypeId('virtual');
        $product->setPrice(1);
        $product->setWebsiteIds([1]);
        $product->setStockData(
            array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 0,
                'is_in_stock' => 1,
                'qty' => 0
            )
        );
        $product->save();
    }

}
