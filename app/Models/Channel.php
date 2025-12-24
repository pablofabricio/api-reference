<?php

namespace App\Models;

class Channel extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function references()
    {
        return $this->belongsToMany(Reference::class, 'channel_references');
    }

    public function members()
    {
        return $this->hasMany(ChannelMember::class);
    }
}
