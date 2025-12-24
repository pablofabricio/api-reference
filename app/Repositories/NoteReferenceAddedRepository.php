<?php

namespace App\Repositories;

use App\Models\NoteReferenceAdded;

class NoteReferenceAddedRepository extends BaseRepository
{
    protected function model(): string
    {
        return NoteReferenceAdded::class;
    }
}
