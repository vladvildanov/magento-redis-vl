<?php

namespace Vladvildanov\MagentoRedisVl\src\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vladvildanov\MagentoRedisVl\src\Model\Indexer\IndexFactoryInterface;

class CreateIndexCommand extends Command
{
    public function __construct(
        private IndexFactoryInterface $indexFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('index:create');
        $this->setDescription('Creates search index for products');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $this->indexFactory->create();

        if ($index->create(true)) {
            echo "Index successfully created\n";
            return 0;
        }

        echo "Error during index creation\n";
        return 1;
    }
}
