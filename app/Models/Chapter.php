<?php

namespace App\Models;

class Chapter extends BaseModel
{
    protected $fillable = ['content', 'book_id'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function verses()
    {
        return $this->hasMany(Verse::class);
    }
}
