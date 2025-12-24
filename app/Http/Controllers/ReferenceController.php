<?php

namespace App\Http\Controllers;

use App\Services\ReferenceService;
use App\Http\Resources\ReferenceResource;

class ReferenceController extends BaseController
{
    public function __construct(ReferenceService $service)
    {
        parent::__construct($service, ReferenceResource::class);
    }
}
