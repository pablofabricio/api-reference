<?php

namespace App\Services;

use App\Repositories\ResourceRepository;

abstract class MigrateService 
{
    protected int $page; 
    protected array $request; 
    protected $resource; 
    protected ResourceRepository $resourceRepository;
    protected string $job; 
    protected string $nextJob; 

    public function import()
    {
        $apiPlus = new ApiPlusRequestService($this->request['fromToken']);
        $res = $apiPlus->get($this->resource, ['page' => $this->page]);

        foreach (($res->data ?? null) as $value) {
            $this->importOne($value);
        }

        if ($this->page == ($res->meta->last_page ?? null)) {
            return dispatch(new $this->nextJob($this->request));
        }

        $this->page++;

        dispatch(new $this->job($this->request, $this->page));
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