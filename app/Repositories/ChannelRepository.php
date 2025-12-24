<?php

namespace App\Repositories;

use App\Models\Channel;

class ChannelRepository extends BaseRepository
{
    protected function model(): string
    {
        return Channel::class;
    }
}
