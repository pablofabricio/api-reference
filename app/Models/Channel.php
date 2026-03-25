<?php

namespace App\Models;

use App\Enums\ChannelMemberRole;
use App\Enums\ChannelVisibility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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

    protected static function booted(): void
    {
        static::creating(function (Channel $channel) {
            if (! $channel->created_by && Auth::check()) {
                $channel->created_by = (int) Auth::id();
            }
        });

        static::created(function (Channel $channel) {
            if (! Auth::check()) {
                return;
            }

            if (! Schema::hasTable('channel_members')) {
                return;
            }

            $userId = (int) Auth::id();
            if ($userId <= 0) {
                return;
            }

            ChannelMember::query()->firstOrCreate(
                [
                    'channel_id' => (int) $channel->id,
                    'user_id' => $userId,
                ],
                [
                    'role' => ChannelMemberRole::OWNER->value,
                ]
            );
        });
    }

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
