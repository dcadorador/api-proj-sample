<?php
namespace App\Api\V1\Helpers;

use GuzzleHttp\Client;
use Facebook\HttpClients\FacebookHttpClientInterface;

class Guzzle6HttpClient implements FacebookHttpClientInterface
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send($url, $method, $body, array $headers, $timeOut)
    {
        $request = new \GuzzleHttp\Psr7\Request($method, $url, $headers, $body);
        try {
            $response = $this->client->send($request, ['timeout' => $timeOut, 'http_errors' => false]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \Facebook\Exceptions\FacebookSDKException($e->getMessage(), $e->getCode());
        }
        $httpStatusCode = $response->getStatusCode();
        $responseHeaders = $response->getHeaders();

        foreach ($responseHeaders as $key => $values) {
            $responseHeaders[$key] = implode(', ', $values);
        }

        $responseBody = $response->getBody()->getContents();

        return new \Facebook\Http\GraphRawResponse(
            $responseHeaders,
            $responseBody,
            $httpStatusCode);
    }
}