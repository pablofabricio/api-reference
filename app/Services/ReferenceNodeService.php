<?php

namespace App\Services;

use App\Repositories\ReferenceNodeRepository;

class ReferenceNodeService extends BaseService
{
	public function __construct(ReferenceNodeRepository $repository)
	{
		parent::__construct($repository);
	}
}
