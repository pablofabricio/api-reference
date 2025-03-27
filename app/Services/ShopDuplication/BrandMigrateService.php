<?php

namespace App\Services\ShopDuplication;

use App\Helpers\Utils;
use App\Jobs\ShopDuplication\BrandJob;
use App\Jobs\ShopDuplication\CategoryJob;
use App\Repositories\ResourceRepository;
use App\Services\ApiPlusRequestService;

class BrandMigrateService extends MigrateService
{
    public function __construct(array $request, $page) 
    {
        $this->page = $page;
        $this->request = $request;
        $this->resource = '/brands';
        $this->job = BrandJob::class;
        $this->nextJob = CategoryJob::class;
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