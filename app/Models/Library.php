<?php

namespace App\Models;

class Library extends BaseModel
{
    protected $fillable = [
        'user_id', 
        'book_id', 
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
