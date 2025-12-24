<?php

namespace App\Services;

use App\Repositories\ChannelReferenceRepository;

class ChannelReferenceService extends BaseService
{
	public function __construct(ChannelReferenceRepository $repository)
	{
		parent::__construct($repository);
	}
}
