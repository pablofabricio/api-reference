<?php

namespace App\Http\Controllers;

use App\Services\ChannelReferenceService;
use App\Http\Resources\ChannelReferenceResource;

class ChannelReferenceController extends BaseController
{
    public function __construct(ChannelReferenceService $service)
    {
        parent::__construct($service, ChannelReferenceResource::class);
    }
}
