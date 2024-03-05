<?php

namespace Vladvildanov\MagentoRedisVl\Model\Indexer;

use Vladvildanov\PredisVl\FactoryInterface;
use Vladvildanov\PredisVl\Index\IndexInterface;

interface IndexFactoryInterface
{
    /**
     * Creates index object.
     *
     * @param array $schema
     * @param FactoryInterface|null $factory
     * @return IndexInterface
     */
    public function create(array $schema, FactoryInterface $factory = null): IndexInterface;
}
