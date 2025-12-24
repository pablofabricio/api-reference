<?php

namespace App\Repositories;

use App\Models\Library;

class LibraryRepository extends BaseRepository
{
    protected function model(): string
    {
        return Library::class;
    }
}
