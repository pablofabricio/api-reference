<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user' => new UserResource($this['user']),
            'channels' => ChannelResource::collection($this['channels']),
        ];
    }
}
