<?php

namespace Vladvildanov\MagentoRedisVl\Model\Indexer;

use Predis\ClientInterface;
use Vladvildanov\PredisVl\FactoryInterface;
use Vladvildanov\PredisVl\Index\IndexInterface;
use Vladvildanov\PredisVl\Index\SearchIndex;

class IndexFactory implements IndexFactoryInterface
{
    public function __construct(private ClientInterface $client)
    {
    }

    /**
     * @inheritDoc
     */
    public function create(array $schema, FactoryInterface $factory = null): IndexInterface
    {
        return new SearchIndex($this->client, $schema, $factory);
    }
}
