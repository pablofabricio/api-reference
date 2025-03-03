<?php

namespace App\Repositories;

use App\Models\Resource;

class ResourceRepository
{
    public function find($resource, $from_id, $to_token)
    {
        return Resource::where('resource', $resource)
            ->where('from_id', $from_id)
            ->where('to_token', $to_token)
            ->first()
            ->to_id ?? null;
    }

    public function insert(array $data)
    {
        Resource::create($data);
    }
}
