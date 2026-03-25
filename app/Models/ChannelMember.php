<?php

namespace App\Models;

use App\Enums\ChannelMemberRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ChannelMember extends BaseModel
{
	protected static array $filters = [
		'channel_id',
		'user_id',
	];

    protected $fillable = [
        'channel_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => ChannelMemberRole::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (ChannelMember $channelMember) {
            if (Auth::check() && ! $channelMember->user_id) {
                $channelMember->user_id = (int) Auth::id();
            }
        });
    }

    public static function rules(): array
    {
        $values = array_map(fn($e) => $e->value, ChannelMemberRole::cases());

        return [
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::in($values)],
            'joined_at' => ['nullable', 'date'],
        ];
    }


    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
