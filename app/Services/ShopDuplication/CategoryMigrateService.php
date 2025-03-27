<?php

namespace App\Services\ShopDuplication;

use App\Jobs\ShopDuplication\CategoryJob;
use App\Repositories\ResourceRepository;
use App\Services\ApiPlusRequestService;

class CategoryMigrateService extends MigrateService
{
    public function __construct(array $request, $page) 
    {
        $this->page = $page;
        $this->request = $request;
        $this->resource = '/categories';
        $this->job = CategoryJob::class;
        $this->nextJob = null;
        $this->resourceRepository = App(ResourceRepository::class);
    }

    public function importOne(object $item)
    {
        if ($this->get($item->id)) {
            return;
        }

        $parent_id = null;

        $apiPlusFrom = new ApiPlusRequestService($this->request['fromToken']);

        if ($item->parent_id) {
            $id = $this->get($item->parent_id);

            if ($id) {
                $parent_id = $id;
            } else {
                $res = $apiPlusFrom->getOne($this->resource . '/' . $item->parent_id);
                $res = $this->importOne($res);
                $parent_id = $res;
            }
        }

        $data = [
            'name' => $item->name,
            'parent_id' => $parent_id,
            'active' => $item->active,
        ];

        $apiPlusTo = new ApiPlusRequestService($this->request['toToken']);
        $res = $apiPlusTo->post($this->resource, $data);

        $this->insertMap($item->id, $res->id);
    }
}