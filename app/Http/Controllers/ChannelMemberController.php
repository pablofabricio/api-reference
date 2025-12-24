<?php

namespace App\Http\Controllers;

use App\Services\ChannelMemberService;
use App\Http\Resources\ChannelMemberResource;

class ChannelMemberController extends BaseController
{
    public function __construct(ChannelMemberService $service)
    {
        parent::__construct($service, ChannelMemberResource::class);
    }
}
