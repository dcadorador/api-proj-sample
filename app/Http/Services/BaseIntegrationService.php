<?php

namespace App\Api\V1\Services;

use GuzzleHttp\Client;

use App\Api\V1\Models\Integration;

class BaseIntegrationService
{

    protected $integration;
    protected $client;

    public function __construct(Integration $integration) {
        $this->integration = $integration;
        $this->client = new \GuzzleHttp\Client([
            'exceptions' => false,
        ]);
    }


    protected function callApi($httpMethod, $uri, $headers) {
        app('log')->debug('Going to call URI: '.$uri);
        $response = $this->client->request($httpMethod, $uri, [
                                      'headers' => $headers
                                    ]);
        app('log')->debug('API request complete.');
        return $response;
    }

    protected function callApiWithParams($httpMethod, $uri, $params) {
        app('log')->debug('Going to call URI: '.$uri);
        $response = $this->client->request($httpMethod, $uri, $params);
        app('log')->debug('API request complete.');
        return $response;
    }
}