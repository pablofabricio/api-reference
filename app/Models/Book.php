<?php

namespace App\Models;

class Book extends BaseModel
{
    protected $fillable = [
        'name', 
        'abbreviation',
        'author', 
        'description'
    ];

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
