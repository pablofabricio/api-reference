<?php

namespace App\Models;

class Library extends BaseModel
{
    public static function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
    
    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(LibraryItem::class);
    }

    public function references()
    {
        return $this->belongsToMany(Reference::class, 'library_items');
    }
}
