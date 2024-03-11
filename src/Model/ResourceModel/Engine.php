<?php

namespace Vladvildanov\MagentoRedisVl\src\Model\ResourceModel;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

class Engine implements EngineInterface
{
    /**
     * @param Visibility $catalogProductVisibility
     * @param IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        protected Visibility $catalogProductVisibility,
        protected IndexScopeResolver $indexScopeResolver
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAllowedVisibility()
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * @inheritDoc
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable(): bool
    {
        return true;
    }
}
