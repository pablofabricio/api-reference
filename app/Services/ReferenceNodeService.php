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

	public function getPaginate()
	{
		$paginator = parent::getPaginate();
		$paginator->getCollection()->loadMissing(['reference:id,title']);

		return $paginator;
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
			$model->loadMissing(['reference:id,title']);
		}

		return $model;
	}

	protected function authorizeModelAccess(Model $model): void
	{
		$referenceId = (int) $model->getAttribute('reference_id');
		if ($referenceId <= 0) {
			throw new AuthorizationException('Unauthorized');
		}

		// Reference owner always has full access to their own nodes.
		$reference = \App\Models\Reference::find($referenceId);
		if ($reference && (int) $reference->getAttribute('user_id') === (int) \Illuminate\Support\Facades\Auth::id()) {
			return;
		}

		if (! $this->hasChannelManagementAccessByReferenceId($referenceId)) {
			throw new AuthorizationException('Unauthorized');
		}
	}
}
