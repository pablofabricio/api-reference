<?php

namespace App\Services;

use App\Repositories\ChannelRepository;

class ChannelService extends BaseService
{
	public function __construct(ChannelRepository $repository)
	{
		parent::__construct($repository);
	}
}
