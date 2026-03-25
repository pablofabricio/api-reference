<?php

namespace App\Repositories;

use App\Models\ChannelJoinRequest;

class ChannelJoinRequestRepository extends BaseRepository
{
    protected function model(): string
    {
        return ChannelJoinRequest::class;
    }
}