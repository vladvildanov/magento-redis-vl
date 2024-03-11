<?php

namespace Vladvildanov\MagentoRedisVl\src\Vectorizer;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use RedisVentures\RedisVl\Vectorizer\FactoryInterface;
use RedisVentures\RedisVl\Vectorizer\VectorizerInterface;

class VectorizerFactory
{
    public function __construct(
        private FactoryInterface $factory,
        private DeploymentConfig $config
    ){
    }

    /**
     * Creates vectorizer factory.
     *
     * @return VectorizerInterface
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function create(): VectorizerInterface
    {
        $name = $this->config->get('search_index/configuration/vector/vectorizer/name', '');
        $model = $this->config->get('search_index/configuration/vector/vectorizer/model');
        $configuration = $this->config->get('search_index/configuration/vector/configuration', []);

        return $this->factory->createVectorizer($name, $model, $configuration);
    }
}
