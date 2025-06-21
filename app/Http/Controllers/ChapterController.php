<?php

namespace App\Http\Controllers;

use App\Services\ChapterService;
use App\Http\Resources\ChapterResource;

class ChapterController extends BaseController
{
    public function __construct(ChapterService $service)
    {
        parent::__construct($service, ChapterResource::class);
    }
}
