<?php

namespace App\Repositories;

use App\Models\Resource;

class ShopImportationRepository
{
    public function find($resource, $from_id)
    {
        return Resource::where('resource', $resource)
            ->where('from_id', $from_id)
            ->first();
    }

    public function insert(array $data)
    {
        Resource::create($data);
    }
}
