<?php

namespace App\Services;

use App\Enums\ChannelJoinRequestStatus;
use App\Enums\ChannelMemberRole;
use App\Enums\ChannelVisibility;
use App\Models\Channel;
use App\Models\ChannelJoinRequest;
use App\Models\ChannelMember;
use App\Repositories\ChannelJoinRequestRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChannelJoinRequestService extends BaseService
{
    public function __construct(ChannelJoinRequestRepository $repository)
    {
        parent::__construct($repository);
    }

    public function listForAuthenticatedUser(?string $status = null): Collection
    {
        $userId = (int) auth()->id();
        $manageableChannelIds = $this->manageableChannelIds($userId);

        $query = ChannelJoinRequest::query()
            ->with(['channel', 'requester', 'reviewer'])
            ->where(function ($builder) use ($userId, $manageableChannelIds) {
                $builder->where('requester_id', $userId);

                if (! empty($manageableChannelIds)) {
                    $builder->orWhereIn('channel_id', $manageableChannelIds);
                }
            })
            ->orderByDesc('created_at');

        $normalizedStatus = strtoupper((string) $status);
        if ($normalizedStatus !== '') {
            $validStatuses = array_map(fn (ChannelJoinRequestStatus $item) => $item->value, ChannelJoinRequestStatus::cases());
            if (in_array($normalizedStatus, $validStatuses, true)) {
                $query->where('status', $normalizedStatus);
            }
        }

        return $query->get();
    }

    public function createForChannel(int $channelId): Model
    {
        $requesterId = (int) auth()->id();
        $channel = Channel::query()->findOrFail($channelId);

        if ($channel->visibility !== ChannelVisibility::PUBLIC) {
            throw new AuthorizationException('Only public channels can receive join requests');
        }

        if ((int) $channel->created_by === $requesterId) {
            throw ValidationException::withMessages([
                'channel_id' => ['You already own this channel.'],
            ]);
        }

        $alreadyMember = ChannelMember::query()
            ->where('channel_id', $channelId)
            ->where('user_id', $requesterId)
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'channel_id' => ['You are already a member of this channel.'],
            ]);
        }

        $pendingRequestExists = ChannelJoinRequest::query()
            ->where('channel_id', $channelId)
            ->where('requester_id', $requesterId)
            ->where('status', ChannelJoinRequestStatus::PENDING->value)
            ->exists();

        if ($pendingRequestExists) {
            throw ValidationException::withMessages([
                'channel_id' => ['A pending request already exists for this channel.'],
            ]);
        }

        $joinRequest = ChannelJoinRequest::query()->create([
            'channel_id' => $channelId,
            'requester_id' => $requesterId,
            'status' => ChannelJoinRequestStatus::PENDING->value,
        ]);

        return $joinRequest->load(['channel', 'requester', 'reviewer']);
    }

    public function updateStatus(int $id, array $data): ?Model
    {
        /** @var ChannelJoinRequest|null $joinRequest */
        $joinRequest = ChannelJoinRequest::query()->with(['channel', 'requester', 'reviewer'])->find($id);
        if (! $joinRequest) {
            return null;
        }

        $validator = Validator::make($data, [
            'status' => ['required', Rule::in([
                ChannelJoinRequestStatus::APPROVED->value,
                ChannelJoinRequestStatus::REJECTED->value,
            ])],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $channelId = (int) $joinRequest->channel_id;
        if ($channelId <= 0 || ! $this->hasChannelManagementAccessByChannelId($channelId)) {
            throw new AuthorizationException('Unauthorized');
        }

        $currentStatus = $joinRequest->status instanceof ChannelJoinRequestStatus ? $joinRequest->status->value : (string) $joinRequest->status;
        if ($currentStatus !== ChannelJoinRequestStatus::PENDING->value) {
            throw ValidationException::withMessages([
                'status' => ['Only pending requests can be reviewed.'],
            ]);
        }

        $nextStatus = strtoupper((string) $data['status']);
        if ($nextStatus === ChannelJoinRequestStatus::APPROVED->value) {
            ChannelMember::query()->firstOrCreate(
                [
                    'channel_id' => $channelId,
                    'user_id' => (int) $joinRequest->requester_id,
                ],
                [
                    'role' => ChannelMemberRole::MEMBER->value,
                ],
            );
        }

        $joinRequest->fill([
            'status' => $nextStatus,
            'reviewed_by' => (int) auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);
        $joinRequest->save();

        return $joinRequest->load(['channel', 'requester', 'reviewer']);
    }

    public function cancel(int $id): bool
    {
        /** @var ChannelJoinRequest|null $joinRequest */
        $joinRequest = ChannelJoinRequest::query()->find($id);
        if (! $joinRequest) {
            return false;
        }

        if ((int) $joinRequest->requester_id !== (int) auth()->id()) {
            throw new AuthorizationException('Unauthorized');
        }

        $currentStatus = $joinRequest->status instanceof ChannelJoinRequestStatus ? $joinRequest->status->value : (string) $joinRequest->status;
        if ($currentStatus !== ChannelJoinRequestStatus::PENDING->value) {
            throw ValidationException::withMessages([
                'status' => ['Only pending requests can be canceled.'],
            ]);
        }

        return (bool) $joinRequest->delete();
    }

    protected function enforcesUserOwnership(): bool
    {
        return false;
    }

    private function manageableChannelIds(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $createdChannelIds = Channel::query()
            ->where('created_by', $userId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $managedMembershipChannelIds = ChannelMember::query()
            ->where('user_id', $userId)
            ->whereIn('role', [
                ChannelMemberRole::OWNER->value,
                ChannelMemberRole::MODERATOR->value,
            ])
            ->pluck('channel_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($createdChannelIds, $managedMembershipChannelIds)));
    }
}