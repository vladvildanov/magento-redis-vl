<?php

namespace Vladvildanov\MagentoRedisVl\Console\Command;

use Predis\ClientInterface;
use Predis\Command\Argument\Search\CreateArguments;
use Predis\Command\Argument\Search\SchemaFields\NumericField;
use Predis\Command\Argument\Search\SchemaFields\TextField;
use Predis\Command\Argument\Search\SchemaFields\VectorField;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateIndexCommand extends Command
{
    public function __construct(private ClientInterface $client)
    {
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
        $this->client->flushdb();

        $arguments = (new CreateArguments())
            ->on('JSON')
            ->prefix(['product:']);

        $schema = [
            new NumericField('$.id'),
            new TextField('$.name'),
            new TextField('$.description'),
            new VectorField('$.description_embeddings', 'FLAT', [
                'TYPE', 'FLOAT32', 'DIM', 1024, 'DISTANCE_METRIC', 'COSINE',
            ]),
        ];

        $response = $this->client->ftcreate('product', $schema, $arguments);

        if ('OK' == $response) {
            $exitCode = 0;
            $message = "Index was successfully created\n";
        } else {
            $exitCode = 1;
            $message = "Error on index creation: $response\n";
        }

        echo $message;
        return $exitCode;
    }
}
