<?php

namespace App\Models;

class ChannelReference extends BaseModel
{
    protected $fillable = [
        'channel_id',
        'reference_id',
    ];

    public static function rules(): array
    {
        return [
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'reference_id' => ['required', 'integer', 'exists:references,id'],
        ];
    }


    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }
}
