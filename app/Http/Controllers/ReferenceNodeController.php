<?php

namespace App\Http\Controllers;

use App\Services\ReferenceNodeService;
use App\Http\Resources\ReferenceNodeResource;

class ReferenceNodeController extends BaseController
{
    public function __construct(ReferenceNodeService $service)
    {
        parent::__construct($service, ReferenceNodeResource::class);
    }
}
