<?php

namespace App\Services;

use App\Helpers\Utils;
use App\Repositories\ResourceRepository;

class BrandMigrateService extends MigrateService
{
    public function __construct(array $request, $page) 
    {
        $this->request = $request;
        $this->resource = '/brands';
        $this->resourceRepository = App(ResourceRepository::class);
    }

    public function importOne(object $item)
    {
        if ($this->get($item->id)) {
            return;
        }

        $data = [
            'name' => $item->name,
            'description' => $item->description,
            'image' => [
                'src' => Utils::image($item->image),
            ],
            'active' => $item->active,
        ];

        $apiPlus = new ApiPlusRequestService($this->request['toToken']);
        $res = $apiPlus->post($this->resource, $data);

        $this->insertMap($item->id, $res->id);
    }
}