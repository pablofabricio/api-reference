<?php

namespace App\Repositories;

use App\Models\Note;

class NoteRepository extends BaseRepository
{
    protected function model(): string
    {
        return Note::class;
    }
}
