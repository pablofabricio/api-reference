<?php

namespace App\Repositories;

use App\Models\ChannelReference;

class ChannelReferenceRepository extends BaseRepository
{
    protected function model(): string
    {
        return ChannelReference::class;
    }
}
