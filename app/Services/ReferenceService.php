<?php

namespace App\Services;

use App\Repositories\ReferenceRepository;

class ReferenceService extends BaseService
{
	public function __construct(ReferenceRepository $repository)
	{
		parent::__construct($repository);
	}
}
