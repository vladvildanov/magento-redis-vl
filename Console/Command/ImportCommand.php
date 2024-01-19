<?php

namespace Vlad\Test\Console\Command;

use GuzzleHttp\Client;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Predis\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    public function __construct(private ClientInterface $client, private Collection $productCollection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('index:import');
        $this->setDescription('Import products into index.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->productCollection->addAttributeToSelect(['id', 'name', 'sku', 'description']);
        $httpClient = new Client();

        foreach ($collection as $product) {
            $requestData = [
                'model' => 'mistral-embed',
                'input' => $product->getDescription(),
            ];

            $response = $httpClient->post('https://api.mistral.ai/v1/embeddings', [
                'headers' => [
                    'Authorization' => 'Bearer ' . getenv('LLM_API_TOKEN'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $requestData,
            ])->getBody()->getContents();

            $decodedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            $id = $product->getId();
            $mappedProduct = [
                'id' => (int) $id,
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'description' => $product->getDescription(),
                'description_embeddings' => $decodedResponse['data'][0]['embedding']
            ];

            $response = $this->client->jsonset("product:$id", '$', json_encode($mappedProduct));

            if ('OK' == $response) {
                $message = "Product with ID: $id was successfully imported into index\n";
            } else {
                $message = "Error on product $id. Response: $response\n";
            }

            echo $message;
        }

        return 0;
    }
}
