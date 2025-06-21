<?php

namespace App\Models;

class Verse extends BaseModel
{
    protected $fillable = ['content', 'chapter_id'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}

