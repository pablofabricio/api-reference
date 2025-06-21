<?php

namespace App\Repositories;

use App\Models\Book;

class BookRepository extends BaseRepository
{
    protected function model(): string
    {
        return Book::class;
    }
}
