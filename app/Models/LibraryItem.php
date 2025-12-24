<?php

namespace App\Models;

class LibraryItem extends BaseModel
{
    protected $fillable = [
        'library_id',
        'reference_id',
    ];

    public static function rules(): array
    {
        return [
            'library_id' => ['required', 'integer', 'exists:libraries,id'],
            'reference_id' => ['required', 'integer', 'exists:references,id'],
        ];
    }

    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }
}
