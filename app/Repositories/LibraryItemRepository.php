<?php

namespace App\Repositories;

use App\Models\LibraryItem;

class LibraryItemRepository extends BaseRepository
{
    protected function model(): string
    {
        return LibraryItem::class;
    }
}
