<?php

namespace Vladvildanov\MagentoRedisVl\Model\Indexer;

use RedisVentures\RedisVl\FactoryInterface;
use RedisVentures\RedisVl\Index\IndexInterface;

interface IndexFactoryInterface
{
    /**
     * Creates index object.
     *
     * @param FactoryInterface|null $factory
     * @return IndexInterface
     */
    public function create(FactoryInterface $factory = null): IndexInterface;
}
