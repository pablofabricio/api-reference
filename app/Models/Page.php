<?php

namespace App\Models;

class Page extends BaseModel
{
    protected $fillable = ['content', 'book_id'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}


