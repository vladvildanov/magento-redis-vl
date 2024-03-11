<?php

namespace Vladvildanov\MagentoRedisVl\SearchAdapter;

use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Vladvildanov\MagentoRedisVl\Model\Indexer\IndexFactory;

class Adapter implements AdapterInterface
{
    public function __construct(
        private IndexFactory $indexFactory,
        private ResponseFactory $responseFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function query(RequestInterface $request)
    {
        var_dump($request->getQuery());
        die();
    }
}