<?php

namespace App\Http\Controllers;

use App\Services\LibraryItemService;
use App\Http\Resources\LibraryItemResource;

class LibraryItemController extends BaseController
{
    public function __construct(LibraryItemService $service)
    {
        parent::__construct($service, LibraryItemResource::class);
    }
}
