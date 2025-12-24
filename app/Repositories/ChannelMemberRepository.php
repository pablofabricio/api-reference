<?php

namespace App\Repositories;

use App\Models\ChannelMember;

class ChannelMemberRepository extends BaseRepository
{
    protected function model(): string
    {
        return ChannelMember::class;
    }
}
