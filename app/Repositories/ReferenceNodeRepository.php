<?php

namespace App\Repositories;

use App\Models\ReferenceNode;

class ReferenceNodeRepository extends BaseRepository
{
    protected function model(): string
    {
        return ReferenceNode::class;
    }
}
