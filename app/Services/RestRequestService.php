<?php

namespace App\Services;

abstract class RestRequestService
{
    protected $params = [];
    protected $getParams = [];
    protected $client;

    /**
     * @param array $post
     * @return object
     */
    public function post($uri, $body)
    {
        return json_decode($this->client->post($uri, ['form_params' => $body])->getBody());
    }

    public function get($uri, $params = [])
    {
        return json_decode($this->client->get($uri, ['query' => $params])->getBody())->data ?? null;
    }

    public function getOne($uri)
    {
        return json_decode($this->client->get($uri)->getBody()) ?? null;
    }

    public function getPaginate($uri, &$params = [])
    {
        $params['page'] = $params['page'] ?? 1;
        $res = $this->get($uri, $params);
        ++$params['page'];

        return $res;
    }
}
