<?php

namespace App\Services;

use GuzzleHttp\Client;

class ApiPlusRequestService extends RestRequestService
{
    public function __construct($token)
    {
        $this->client = new Client([
            'base_uri' => config('services.api-plus.url'),
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
    }

    public function get($uri, $params = [])
    {
        return json_decode($this->client->get($uri, ['query' => $params])->getBody()) ?? null;
    }
}
