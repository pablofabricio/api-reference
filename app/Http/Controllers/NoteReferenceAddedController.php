<?php

namespace App\Http\Controllers;

use App\Services\NoteReferenceAddedService;
use App\Http\Resources\NoteReferenceAddedResource;

class NoteReferenceAddedController extends BaseController
{
    public function __construct(NoteReferenceAddedService $service)
    {
        parent::__construct($service, NoteReferenceAddedResource::class);
    }
}
