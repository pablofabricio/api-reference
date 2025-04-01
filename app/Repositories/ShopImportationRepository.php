<?php

namespace App\Repositories;

use App\Models\ShopImportation;

class ShopImportationRepository
{
    public function find($resource, $from_id)
    {
        return ShopImportation::where('resource', $resource)
            ->where('from_id', $from_id)
            ->first();
    }

    public function insert(array $data)
    {
        ShopImportation::create($data);
    }
}
