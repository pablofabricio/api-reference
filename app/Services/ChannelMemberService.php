<?php

namespace App\Services;

use App\Enums\ChannelMemberRole;
use App\Enums\ChannelVisibility;
use App\Models\Channel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\ChannelMemberRepository;

class ChannelMemberService extends BaseService
{
	public function __construct(ChannelMemberRepository $repository)
	{
		parent::__construct($repository);
	}

	public function create(array $data): Model
	{
		if (isset($data['channel_id'])) {
			$channel = Channel::query()->find((int) $data['channel_id']);

			if ($channel && $channel->visibility !== ChannelVisibility::PUBLIC) {
				throw new AuthorizationException('Only public channels can be followed');
			}
		}

		$data['role'] = ChannelMemberRole::MEMBER->value;

		return parent::create($data);
	}
}
