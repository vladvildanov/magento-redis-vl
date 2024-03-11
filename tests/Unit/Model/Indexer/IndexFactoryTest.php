<?php

namespace Vladvildanov\MagentoRedisVl\Unit\Model\Indexer;

use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use RedisVentures\RedisVl\Index\SearchIndex;
use Vladvildanov\MagentoRedisVl\src\Model\Indexer\IndexFactory;

class IndexFactoryTest extends TestCase
{
    /**
     * @var Client
     */
    private Client $mockClient;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $mockConfig;

    protected function setUp(): void
    {
        $this->mockClient = $this->getMockBuilder(Client::class)->getMock();
        $this->mockConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $this->mockConfig
            ->expects($this->once())
            ->method('get')
            ->with('search_index/schema')
            ->willReturn(
                [
                    'index' => [
                        'name' => 'foobar',
                        'storage_type' => 'hash',
                    ],
                    'fields' => [
                        'foo' => [
                            'type' => 'text',
                        ]
                    ]
                ]
            );

        $factory = new IndexFactory($this->mockClient, $this->mockConfig);

        $this->assertInstanceOf(
            SearchIndex::class,
            $factory->create()
        );
    }
}
