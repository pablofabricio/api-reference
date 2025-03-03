<?php

namespace App\Services;

use App\Repositories\ResourceRepository;

abstract class MigrateService 
{
    protected array $request; 
    protected $resource; 
    protected $getParams = [];
    protected ResourceRepository $resourceRepository;

    public function import()
    {
        $apiPlus = new ApiPlusRequestService($this->request['fromToken']);

        while ($rows = $apiPlus->getPaginate($this->resource, $this->getParams)) {
            foreach ($rows as $key => $value) {
                $this->importOne($value);
            }
        }
    }

    public function importOne(object $item) {}

    public function get($id)
    {
        return $this->resourceRepository->find(
            $this->resource, 
            $id, 
            $this->request['toToken']
        );

    }

    public function insertMap($from_id, $to_id)
    {
        $this->resourceRepository->insert([
            'resource' => $this->resource,
            'from_id' => $from_id,
            'to_id' => $to_id,
            'migration_id' => $this->request['migration_id'],
            'from_token' => $this->request['fromToken'],
            'to_token' => $this->request['toToken'],
        ]);
    }
}