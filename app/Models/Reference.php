<?php

namespace App\Models;

use App\Enums\ReferenceType;

class Reference extends BaseModel
{
    public static function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'abbreviation' => ['nullable', 'string', 'max:50'],
            'author' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'abbreviation',
        'author',
        'description',
    ];

    protected $casts = [
        'type' => ReferenceType::class,
    ];

    public function nodes()
    {
        return $this->hasMany(ReferenceNode::class);
    }
}

