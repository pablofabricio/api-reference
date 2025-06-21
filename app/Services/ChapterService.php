<?php

namespace App\Services;

use App\Repositories\ChapterRepository;

class ChapterService extends BaseService
{
    public function __construct(ChapterRepository $repository)
    {
        parent::__construct($repository);
    }
}
