<?php

namespace App\Services;

use App\Repositories\NoteRepository;

class NoteService extends BaseService
{
    public function __construct(NoteRepository $repository)
    {
        parent::__construct($repository);
    }
}
