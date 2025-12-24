<?php

namespace App\Http\Controllers;

use App\Services\ChannelService;
use App\Http\Resources\ChannelResource;

class ChannelController extends BaseController
{
    public function __construct(ChannelService $service)
    {
        parent::__construct($service, ChannelResource::class);
    }
}
