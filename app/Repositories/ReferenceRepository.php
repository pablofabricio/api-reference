<?php

namespace App\Repositories;

use App\Models\Reference;

class ReferenceRepository extends BaseRepository
{
    protected function model(): string
    {
        return Reference::class;
    }
}
