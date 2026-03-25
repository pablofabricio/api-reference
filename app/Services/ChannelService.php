<?php

namespace App\Services;

use App\Repositories\ChannelRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChannelService extends BaseService
{
	public function __construct(ChannelRepository $repository)
	{
		parent::__construct($repository);
	}

	public function getPaginateForUser()
	{
		$userId = Auth::id();
		$model = $this->repository->getModel();
		$query = $model->newQuery();
		$query->where('created_by', $userId)
		      ->orWhereHas('members', function ($q) use ($userId) {
				$q->where('user_id', $userId);
			});

		return $query->paginate();
	}

	/**
	 * Channel access is role-based (creator/owner/moderator), not user_id ownership.
	 */
	protected function enforcesUserOwnership(): bool
	{
		return false;
	}

	protected function authorizeModelAccess(Model $model): void
	{
		$channelId = (int) $model->getAttribute('id');
		if ($channelId <= 0 || ! $this->hasChannelManagementAccessByChannelId($channelId)) {
			throw new AuthorizationException('Unauthorized');
		}
	}

	public function delete(int $id): bool
	{
		$record = $this->find($id);
		if (! $record) {
			return false;
		}

		$channelId = (int) $record->getAttribute('id');
		if ($channelId <= 0 || ! $this->hasChannelOwnershipAccessByChannelId($channelId)) {
			throw new AuthorizationException('Unauthorized');
		}

		return $this->repository->delete($id);
	}
}
