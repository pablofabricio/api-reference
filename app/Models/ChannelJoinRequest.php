<?php

namespace App\Models;

use App\Enums\ChannelJoinRequestStatus;

class ChannelJoinRequest extends BaseModel
{
    protected static array $filters = [
        'channel_id',
        'requester_id',
        'status',
    ];

    protected $fillable = [
        'channel_id',
        'requester_id',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => ChannelJoinRequestStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}