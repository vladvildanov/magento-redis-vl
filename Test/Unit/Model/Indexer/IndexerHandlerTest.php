<?php

namespace Vladvildanov\MagentoRedisVl\Unit\Model\Indexer;

use ArrayObject;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\SaveHandler\Batch;
use PHPUnit\Framework\TestCase;
use RedisVentures\RedisVl\Index\IndexInterface;
use RedisVentures\RedisVl\VectorHelper;
use RedisVentures\RedisVl\Vectorizer\VectorizerInterface;
use Vladvildanov\MagentoRedisVl\Model\Indexer\IndexerHandler;
use Vladvildanov\MagentoRedisVl\Model\Indexer\IndexFactory;
use Vladvildanov\MagentoRedisVl\Vectorizer\VectorizerFactory;

class IndexerHandlerTest extends TestCase
{
    /**
     * @var Batch
     */
    private Batch $mockBatch;

    /**
     * @var IndexFactory
     */
    private IndexFactory $mockIndexFactory;

    /**
     * @var VectorizerFactory
     */
    private VectorizerFactory $mockVectorizerFactory;

    /**
     * @var Collection
     */
    private Collection $mockProductCollection;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $mockConfig;

    protected function setUp(): void
    {
        $this->mockBatch = $this->getMockBuilder(Batch::class)->getMock();
        $this->mockIndexFactory = $this->getMockBuilder(IndexFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->mockVectorizerFactory = $this->getMockBuilder(VectorizerFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->mockProductCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->mockConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return void
     */
    public function testSaveIndex(): void
    {
        $documents = new ArrayObject([
            ['sku1', 'sku2', 'sku3'],
        ]);

        $vectorizer = $this->getMockBuilder(VectorizerInterface::class)->getMock();
        $index = $this->getMockBuilder(IndexInterface::class)->getMock();

        $product1 = new DataObject(['entity_id' => '0', 'name' => 'foo', 'description' => 'bar']);
        $product2 = new DataObject(['entity_id' => '1', 'name' => 'bar', 'description' => 'foo']);
        $product3 = new DataObject(['entity_id' => '2', 'name' => 'baz', 'description' => 'baz']);

        $this->mockConfig
            ->expects($this->exactly(4))
            ->method('get')
            ->withConsecutive(
                ['search_index/schema/fields'],
                ['search_index/configuration/vector/toVector'],
                ['search_index/configuration/vector/toVector'],
                ['search_index/configuration/vector/toVector'],
            )
            ->willReturnOnConsecutiveCalls([
                'entity_id' => [
                    'type' => 'text',
                ],
                'name' => [
                    'type' => 'text',
                ],
                'description' => [
                    'type' => 'text',
                ],
                'description_embeddings' => [
                    'type' => 'vector',
                    'algorithm' => 'flat',
                    'dims' => 1536,
                    'datatype' => 'float32',
                    'distance_metric' => 'cosine',
                ]
            ],
                ['description' => 'description_embeddings'],
                ['description' => 'description_embeddings'],
                ['description' => 'description_embeddings'],
            );

        $this->mockProductCollection
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['entity_id', 'name', 'description']);

        $this->mockBatch
            ->expects($this->once())
            ->method('getItems')
            ->with($documents, 50)
            ->willReturn($documents->getIterator());

        $this->mockProductCollection
            ->expects($this->exactly(3))
            ->method('getItemById')
            ->withConsecutive([0], [1], [2])
            ->willReturnOnConsecutiveCalls($product1, $product2, $product3);

        $this->mockVectorizerFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($vectorizer);

        $vectorizer
            ->expects($this->exactly(3))
            ->method('embed')
            ->withConsecutive(
                ['bar'],
                ['foo'],
                ['baz']
            )
            ->willReturnOnConsecutiveCalls(
                ['data' => [[
                    'embedding' => [0.001, 0.002, 0.003]
                ]]],
                ['data' => [[
                    'embedding' => [0.002, 0.003, 0.004]
                ]]],
                ['data' => [[
                    'embedding' => [0.003, 0.004, 0.005]
                ]]]
            );

        $this->mockIndexFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($index);

        $index
            ->expects($this->exactly(3))
            ->method('load')
            ->withConsecutive(
                ['0', ['entity_id' => '0', 'name' => 'foo', 'description' => 'bar', 'description_embeddings' => VectorHelper::toBytes([0.001, 0.002, 0.003])]],
                ['1', ['entity_id' => '1', 'name' => 'bar', 'description' => 'foo', 'description_embeddings' => VectorHelper::toBytes([0.002, 0.003, 0.004])]],
                ['2', ['entity_id' => '2', 'name' => 'baz', 'description' => 'baz', 'description_embeddings' => VectorHelper::toBytes([0.003, 0.004, 0.005])]]
            );

        $indexerHandler = new IndexerHandler(
            $this->mockBatch,
            [],
            $this->mockIndexFactory,
            $this->mockVectorizerFactory,
            $this->mockProductCollection,
            $this->mockConfig
        );

        $indexerHandler->saveIndex([], $documents);
    }
}
