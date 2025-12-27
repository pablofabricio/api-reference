<?php

namespace App\Services;

use App\Repositories\ChannelRepository;
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
}
