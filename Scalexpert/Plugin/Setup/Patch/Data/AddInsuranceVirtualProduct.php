<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
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
    protected $_storeManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
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
        try {
            $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {

        }
        $product = $this->productFactory->create();
        $product->setSku('Insurance');
        $product->setName('Assurance');
        $product->setAttributeSetId($product->getDefaultAttributeSetId());
        $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL);
        $product->setPrice(1);
        $product->setWebsiteIds([$this->_storeManager->getDefaultStoreView()->getWebsiteId()]);
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
