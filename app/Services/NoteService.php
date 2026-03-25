<?php

namespace App\Services;

use App\Models\Note;
use App\Repositories\NoteRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

class NoteService extends BaseService
{
    public function __construct(NoteRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getByReferenceNode(int $referenceNodeId)
    {
        return $this->noteRepository()->paginateByReferenceNode($referenceNodeId);
    }

    private function noteRepository(): NoteRepository
    {
        /** @var NoteRepository $repository */
        $repository = $this->repository;

        return $repository;
    }

    /**
     * Authorize note access for owner OR channel OWNER/MODERATOR.
     */
    protected function authorizeModelAccess(Model $model): void
    {
        if (! $model instanceof Note) {
            parent::authorizeModelAccess($model);
            return;
        }

        $authUserId = (int) auth()->id();
        $noteUserId = (int) $model->getAttribute('user_id');

        if ($authUserId > 0 && $noteUserId === $authUserId) {
            return;
        }

        $referenceId = (int) optional($model->referenceNode)->reference_id;
        if ($referenceId > 0 && $this->hasChannelManagementAccessByReferenceId($referenceId)) {
            return;
        }

        throw new AuthorizationException('Unauthorized');
    }
}
