<?php

namespace App\Models;

use App\Enums\ChannelMemberRole;
use Illuminate\Validation\Rule;

class ChannelMember extends BaseModel
{
    protected $fillable = [
        'channel_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => ChannelMemberRole::class,
    ];

    public static function rules(): array
    {
        $values = array_map(fn($e) => $e->value, ChannelMemberRole::cases());

        return [
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
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
