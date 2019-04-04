<?php

namespace App\Api\V1\Services;

use App\Api\V1\Models\Integration;

class HyperPayService
{
    /**
     * @var array
     */
    private $dummyData = [];

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var null
     */
    private $uri;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * HyperPayService constructor.
     * @param Integration $integration
     * @param array $options
     */
    public function __construct(Integration $integration, array $options, $baseUriOption = null)
    {
        $baseUri = $integration->options['base_uri'];

        $this->uri = $baseUriOption;

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $baseUri,
            'exceptions' => false,
            'verify' => false
        ]);

        $this->dummyData = [
            'authentication.userId' => $integration->options['user_id'],
            'authentication.password' => $integration->options['password'],
            'authentication.entityId' => $integration->options['entity_id']
        ];
    }

    /**
     * Send request to test url of hyperpay
     *
     * @return mixed
     */
    public function sendCheckout()
    {
        $response = $this->client->request('POST', $this->uri, [
            'headers' => [],
            'form_params' => $this->dummyData + $this->options
        ]);

        $result = json_decode($response->getBody());

        return $result;
    }

    public function sendPayment()
    {
        $response = $this->client->request('POST', $this->uri, [
            'headers' => [],
            'form_params' => $this->dummyData + $this->options
        ]);

        $result = json_decode($response->getBody());

        return $result;
    }

    public function getAuthUserId()
    {
        return $this->dummyData['authentication.userId'];
    }

    public function getAuthPassword()
    {
        return $this->dummyData['authentication.password'];
    }

    public function getAuthEntity()
    {
        return $this->dummyData['authentication.entityId'];
    }
}