<?php

namespace App\Http\Controllers;

use App\Services\LibraryService;
use App\Http\Resources\LibraryResource;

class LibraryController extends BaseController
{
    public function __construct(LibraryService $service)
    {
        parent::__construct($service, LibraryResource::class);
    }
}
