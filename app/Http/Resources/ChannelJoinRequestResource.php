<?php

namespace App\Http\Resources;

use App\Enums\ChannelJoinRequestStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelJoinRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = $this->status instanceof ChannelJoinRequestStatus ? $this->status->value : (string) $this->status;

        return [
            'id' => $this->id,
            'channel_id' => $this->channel_id,
            'requester_id' => $this->requester_id,
            'status' => $status,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'channel' => $this->channel ? [
                'id' => $this->channel->id,
                'name' => $this->channel->name,
                'visibility' => $this->channel->visibility,
                'created_by' => $this->channel->created_by,
            ] : null,
            'requester' => $this->requester ? [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'email' => $this->requester->email,
                'avatar_url' => $this->requester->avatar_url,
            ] : null,
            'reviewer' => $this->reviewer ? [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
                'avatar_url' => $this->reviewer->avatar_url,
            ] : null,
        ];
    }
}