<?php

namespace Vladvildanov\MagentoRedisVl\src\Model\Indexer;

use Magento\Framework\App\DeploymentConfig;
use Predis\ClientInterface;
use RedisVentures\RedisVl\FactoryInterface;
use RedisVentures\RedisVl\Index\IndexInterface;
use RedisVentures\RedisVl\Index\SearchIndex;

class IndexFactory implements IndexFactoryInterface
{
    public function __construct(private ClientInterface $client, private DeploymentConfig $config)
    {
    }

    /**
     * @inheritDoc
     */
    public function create(FactoryInterface $factory = null): IndexInterface
    {
        return new SearchIndex($this->client, $this->config->get('search_index/schema'), $factory);
    }
}
