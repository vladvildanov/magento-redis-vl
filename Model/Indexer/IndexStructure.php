<?php

namespace Vladvildanov\MagentoRedisVl\Model\Indexer;

use Exception;
use Magento\Framework\Indexer\IndexStructureInterface;
use RedisVentures\RedisVl\Index\IndexInterface;
use Magento\Framework\App\DeploymentConfig;

class IndexStructure implements IndexStructureInterface
{
    /**
     * @var IndexInterface
     */
    private IndexInterface $index;

    public function __construct(private IndexFactoryInterface $indexFactory, private DeploymentConfig $config)
    {
    }

    /**
     * @inheritDoc
     */
    public function delete($index, array $dimensions = [])
    {
        throw new Exception('Index cannot be deleted, only overwritten');
    }

    /**
     * @inheritDoc
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $schema = [
            'index' => [
                'name' => $index,
                'storage_type' => $this->config->get('search_index/storage_type'),
            ]
        ];

        foreach ($fields as $field) {
            if (null === $this->config->get("search_index/fields/$field")) {
                throw new Exception('Current field is not a part of defined schema');
            }

            $schema['fields'][$field] = $this->config->get("search_index/fields/$field");
        }

        if ((int) $this->config->get('search_index/overwrite') === 1) {
            $this->getIndex($schema)->create(true);
        } else {
            $this->getIndex($schema)->create();
        }
    }

    /**
     * @param array $schema
     * @return IndexInterface
     */
    private function getIndex(array $schema): IndexInterface
    {
        if (null === $this->index) {
            $this->index = $this->indexFactory->create($schema);
        }

        return $this->index;
    }
}
