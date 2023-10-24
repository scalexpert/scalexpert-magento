<?php

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

    function getCategoryTree($categoryIds)
    {
        $categoryCollection = $this->_categoryCollectionFactory->create();
        $categories = $categoryCollection->addFieldToFilter('entity_id', ['in' => $categoryIds])
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('parent_id');

        $categoryTree = [];

        foreach ($categories as $category) {
            $pathIds = explode('/', $category->getPath());
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
}
