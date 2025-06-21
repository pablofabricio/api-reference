<?php

namespace App\Models;

class Note extends BaseModel
{
    protected $fillable = ['user_id', 'content', 'book_id', 'is_public', 'created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'note_pages');
    }

    public function chapters()
    {
        return $this->belongsToMany(Chapter::class, 'note_chapters');
    }

    public function verses()
    {
        return $this->belongsToMany(Verse::class, 'note_verses');
    }
}


