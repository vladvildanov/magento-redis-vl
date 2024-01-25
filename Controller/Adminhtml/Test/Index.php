<?php

namespace Vlad\Test\Controller\Adminhtml\Test;

use GuzzleHttp\Client;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Predis\ClientInterface;
use Predis\Command\Argument\Search\SearchArguments;

class Index extends Action
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        protected PageFactory $resultPageFactory,
        private ClientInterface $client

    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $searchQuery = $this->getRequest()->getParam('searchQuery');

        if (!$searchQuery) {
            echo 'searchQuery parameter cannot be empty';
            die();
        }

        $httpClient = new Client();
        $requestData = [
            'model' => 'text-embedding-ada-002',
            'input' => $searchQuery,
        ];

        $response = $httpClient->post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('LLM_API_TOKEN'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $requestData,
        ])->getBody()->getContents();

        $decodedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $blob = pack('f*', ...$decodedResponse['data'][0]['embedding']);

        $ftSearchArguments = (new SearchArguments())
            ->params(['query_vector', $blob])
            ->sortBy('vector_score')
            ->addReturn(4, '$.id', '$.name', '$.description', 'vector_score')
            ->dialect(2);

        $response = $this->client->ftsearch('product', '(*)=>[KNN 3 @vector $query_vector AS vector_score]', $ftSearchArguments);

        echo '<pre>';
        var_dump($response);
        echo "</pre>";
        die();

        return $this->resultPageFactory->create();
    }
}
