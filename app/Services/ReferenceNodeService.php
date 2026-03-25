<?php

namespace App\Services;

use App\Repositories\ReferenceNodeRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

class ReferenceNodeService extends BaseService
{
	public function __construct(ReferenceNodeRepository $repository)
	{
		parent::__construct($repository);
	}

	/**
	 * Reference nodes are managed by channel role, not by node user_id ownership.
	 */
	protected function enforcesUserOwnership(): bool
	{
		return false;
	}

	/**
	 * Bypass global ownership scope for node lookups and apply custom authz.
	 */
	public function find(int $id): ?Model
	{
		$model = $this->repository->findWithoutGlobalScopes($id);

		if ($model) {
			$this->authorizeModelAccess($model);
		}

		return $model;
	}

	protected function authorizeModelAccess(Model $model): void
	{
		$referenceId = (int) $model->getAttribute('reference_id');
		if ($referenceId <= 0 || ! $this->hasChannelManagementAccessByReferenceId($referenceId)) {
			throw new AuthorizationException('Unauthorized');
		}
	}
}
