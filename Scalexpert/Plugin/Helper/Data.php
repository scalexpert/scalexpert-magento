<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    public $_categoryRepository;
    public $_categoryCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->_categoryRepository = $categoryRepository;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    public function getCategoryTree($categoryIds)
    {
        $categoryCollection = $this->_categoryCollectionFactory->create();
        $categories = $categoryCollection->addFieldToFilter('entity_id', ['in' => $categoryIds])
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('parent_id');

        $categoryTree = [];

        foreach ($categories as $category) {
            $pathIds = explode('/', $category->getPath() ?? '');
            $pathNames = [];

            foreach ($pathIds as $pathId) {
                if ($pathId != 1) {
                    $pathCategory = $this->_categoryRepository->get($pathId);
                    $pathNames[] = $pathCategory->getName();
                }
            }

            $categoryTree[] = implode('/', $pathNames);
        }

        return implode(';', $categoryTree);
    }

    /**
     * @param $sku
     * @return false|string
     */
    public function cleanSkuForApi($sku)
    {
        return substr($sku,0,36);
    }
}
