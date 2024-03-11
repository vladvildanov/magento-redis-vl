<?php

namespace Vladvildanov\MagentoRedisVl\Unit\Vectorizer;

use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\TestCase;
use RedisVentures\RedisVl\Vectorizer\FactoryInterface;
use RedisVentures\RedisVl\Vectorizer\VectorizerInterface;
use Vladvildanov\MagentoRedisVl\Vectorizer\VectorizerFactory;

class VectorizerFactoryTest extends TestCase
{
    /**
     * @var FactoryInterface
     */
    private FactoryInterface $mockFactory;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $mockConfig;

    protected function setUp(): void
    {
        $this->mockFactory = $this->getMockBuilder(FactoryInterface::class)->getMock();
        $this->mockConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testCreate(): void
    {
        $expectedVectorizer = $this->getMockBuilder(VectorizerInterface::class)->getMock();

        $this->mockConfig
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                ['search_index/configuration/vector/vectorizer/name', ''],
                ['search_index/configuration/vector/vectorizer/model'],
                ['search_index/configuration/vector/configuration', []],
            )
            ->willReturnOnConsecutiveCalls('openai', null, ['token' => 'mockToken']);

        $this->mockFactory
            ->expects($this->once())
            ->method('createVectorizer')
            ->with('openai', null, ['token' => 'mockToken'])
            ->willReturn($expectedVectorizer);

        $vectorizerFactory = new VectorizerFactory($this->mockFactory, $this->mockConfig);

        $this->assertSame($expectedVectorizer, $vectorizerFactory->create());
    }
}
