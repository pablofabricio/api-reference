<?php

namespace App\Http\Controllers;

use App\Services\BookService;
use App\Http\Resources\BookResource;

class BookController extends BaseController
{
    public function __construct(BookService $service)
    {
        parent::__construct($service, BookResource::class);
    }
}
