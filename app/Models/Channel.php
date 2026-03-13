<?php

namespace App\Models;

use App\Enums\ChannelVisibility;
use Illuminate\Validation\Rule;

class Channel extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'visibility',
    ];

    protected $casts = [
        'visibility' => ChannelVisibility::class,
    ];

    public static function rules(): array
    {
        $visibilityValues = array_map(fn($e) => $e->value, ChannelVisibility::cases());

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['required', Rule::in($visibilityValues)],
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
