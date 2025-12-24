<?php

namespace App\Services;

use App\Repositories\ChannelMemberRepository;

class ChannelMemberService extends BaseService
{
	public function __construct(ChannelMemberRepository $repository)
	{
		parent::__construct($repository);
	}
}
