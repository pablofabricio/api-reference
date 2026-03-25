<?php

namespace App\Services;

use App\Enums\ChannelMemberRole;
use App\Enums\ChannelVisibility;
use App\Models\Channel;
use App\Models\ChannelMember;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\ChannelMemberRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ChannelMemberService extends BaseService
{
	public function __construct(ChannelMemberRepository $repository)
	{
		parent::__construct($repository);
	}

	public function create(array $data): Model
	{
		if (array_key_exists('user_id', $data) && (int) $data['user_id'] !== (int) auth()->id()) {
			throw new AuthorizationException('Unauthorized');
		}

		if (isset($data['channel_id'])) {
			$channel = Channel::query()->find((int) $data['channel_id']);

			if ($channel && $channel->visibility !== ChannelVisibility::PUBLIC) {
				throw new AuthorizationException('Only public channels can be followed');
			}
		}

		$data['role'] = ChannelMemberRole::MEMBER->value;

		return parent::create($data);
	}

	public function update(int $id, array $data): ?Model
	{
		/** @var ChannelMember|null $record */
		$record = $this->repository->find($id);
		if (! $record) {
			return null;
		}

		$channelId = (int) $record->getAttribute('channel_id');
		if ($channelId <= 0 || ! $this->hasChannelOwnershipAccessByChannelId($channelId)) {
			throw new AuthorizationException('Unauthorized');
		}

		$channel = Channel::query()->find($channelId);
		if ($channel && (int) $channel->getAttribute('created_by') === (int) $record->getAttribute('user_id')) {
			throw new AuthorizationException('The channel owner permission cannot be changed');
		}

		$payload = [
			'channel_id' => (int) $record->getAttribute('channel_id'),
			'user_id' => (int) $record->getAttribute('user_id'),
			'role' => $data['role'] ?? $record->getAttribute('role'),
		];

		$targetRoleValue = $payload['role'];
		$targetRole = $targetRoleValue instanceof ChannelMemberRole ? $targetRoleValue->value : strtoupper((string) $targetRoleValue);
		if ($targetRole === ChannelMemberRole::OWNER->value) {
			throw new AuthorizationException('Cannot assign OWNER role');
		}

		$validator = Validator::make($payload, ChannelMember::rules());
		if ($validator->fails()) {
			throw new ValidationException($validator);
		}

		return $this->repository->update($id, [
			'role' => $payload['role'],
		]);
	}

	protected function enforcesUserOwnership(): bool
	{
		return false;
	}
}
