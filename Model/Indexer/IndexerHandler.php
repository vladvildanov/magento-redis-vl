<?php

namespace Vladvildanov\MagentoRedisVl\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;

class IndexerHandler implements IndexerInterface
{
    public function __construct(
        private Batch $batch,
        private array $data,
        private IndexFactory $indexFactory,
        private int $batchSize = 50
    ) {
    }

    /**
     * @inheritDoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        var_dump('Test');
        die();
    }

    /**
     * @inheritDoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        // TODO: Implement deleteIndex() method.
    }

    /**
     * @inheritDoc
     */
    public function cleanIndex($dimensions)
    {
        // TODO: Implement cleanIndex() method.
    }

    /**
     * @inheritDoc
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }
}
