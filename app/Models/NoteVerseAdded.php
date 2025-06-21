<?php

namespace App\Models;

class NoteVerseAdded extends BaseModel
{
    protected $fillable = [
        'verse_id', 
        'note_id_added', 
        'user_id'
    ];
}


