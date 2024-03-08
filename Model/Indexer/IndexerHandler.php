<?php

namespace Vladvildanov\MagentoRedisVl\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use RedisVentures\RedisVl\VectorHelper;
use Vladvildanov\MagentoRedisVl\Vectorizer\VectorizerFactory;
use RedisVentures\RedisVl\Index\IndexInterface;
use RedisVentures\RedisVl\Vectorizer\VectorizerInterface;

class IndexerHandler implements IndexerInterface
{
    /**
     * @var IndexInterface|null
     */
    private ?IndexInterface $index = null;

    /**
     * @var VectorizerInterface|null
     */
    private ?VectorizerInterface $vectorizer = null;

    public function __construct(
        private Batch $batch,
        private array $data,
        private IndexFactory $indexFactory,
        private VectorizerFactory $vectorizerFactory,
        private Collection $productCollection,
        private DeploymentConfig $config,
        private int $batchSize = 50
    ) {
    }

    /**
     * @inheritDoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $fields = $this->config->get('search_index/schema/fields');
        $productMatchingFields = array_filter($fields, static function ($field) {
            return $field['type'] !== 'vector';
        });

        $productFields = array_keys($productMatchingFields);
        $this->productCollection->addAttributeToSelect($productFields);

        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            foreach ($batchDocuments as $id => $fields) {
                $product = $this->productCollection->getItemById($id);

                if (null !== $product) {
                    $filteredProduct = array_intersect_key($product->toArray(), array_flip($productFields));
                    $this->createVectors($filteredProduct);

                    $this->getIndex()->load($filteredProduct['entity_id'], $filteredProduct);
                }
            }
        }
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

    /**
     * Creates vector representations of product fields.
     *
     * @param array $product
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function createVectors(array &$product): void
    {
        $toVector = $this->config->get('search_index/configuration/vector/toVector', []);

        foreach ($toVector as $field => $vectorField) {
            if (array_key_exists($field, $product)) {
                $product[$vectorField] = VectorHelper::toBytes($this->getVectorizer()->embed($product[$field])['data'][0]['embedding']);
            }
        }
    }

    /**
     * @return IndexInterface
     */
    private function getIndex(): IndexInterface
    {
        if (null === $this->index) {
            $this->index = $this->indexFactory->create();
        }

        return $this->index;
    }

    /**
     * @return VectorizerInterface
     */
    private function getVectorizer(): VectorizerInterface
    {
        if (null === $this->vectorizer) {
            $this->vectorizer = $this->vectorizerFactory->create();
        }

        return $this->vectorizer;
    }
}
