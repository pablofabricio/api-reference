<?php

namespace App\Services;

use App\Repositories\NoteReferenceAddedRepository;

class NoteReferenceAddedService extends BaseService
{
	public function __construct(NoteReferenceAddedRepository $repository)
	{
		parent::__construct($repository);
	}
}
