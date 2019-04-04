<?php
namespace App\Api\V1\Services;

use App\Api\V1\Models\Concept;
use App\Api\V1\Models\Integration;

class HappyFoxFeedbackService
{
    private $client;

    private $options = array();

    public function __construct(Concept $concept, Integration $integration) {
        $this->integration = $integration;
        $this->concept = $concept;
        $this->changeLog = new \stdClass();
        $this->changeLog->added = array();
        $this->changeLog->updated = array();
        $this->changeLog->disabled = array();

        $apiKey = $integration->options['api_key'];
        $authCode = $integration->options['auth_code'];
        $baseUri = $integration->options['base_uri'];

        $this->headers = [
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $authCode)
        ];

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $baseUri,
            'exceptions' => false
        ]);

        $this->options = [
            'category' => 6,
            'priority' => 1
        ];
    }

    public function submitTicket($data)
    {
        $response = $this->client->request('POST', 'tickets', [
            'headers' => $this->headers,
            'json' => $this->options + $data
        ]);

        $result = json_decode($response->getBody());

        return $result;
    }
}