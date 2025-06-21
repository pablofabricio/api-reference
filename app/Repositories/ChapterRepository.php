<?php

namespace App\Repositories;

use App\Models\Chapter;

class ChapterRepository extends BaseRepository
{
    protected function model(): string
    {
        return Chapter::class;
    }
}
