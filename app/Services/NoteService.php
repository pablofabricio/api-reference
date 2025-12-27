<?php

namespace App\Services;

use App\Repositories\NoteRepository;

class NoteService extends BaseService
{
    public function __construct(NoteRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getByReferenceNode(int $referenceNodeId)
    {
        return $this->repository->paginateByReferenceNode($referenceNodeId);
    }
}
