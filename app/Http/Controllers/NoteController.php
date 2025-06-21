<?php

namespace App\Http\Controllers;

use App\Services\NoteService;
use App\Http\Resources\NoteResource;

class NoteController extends BaseController
{
    public function __construct(NoteService $service)
    {
        parent::__construct($service, NoteResource::class);
    }
}
