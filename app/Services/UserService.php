<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
	public function __construct(UserRepository $repository)
	{
		parent::__construct($repository);
	}

	public function getProfile(int $id, ?int $viewerId): array
	{
		$profileUser = User::query()->findOrFail($id);
		$channels = $profileUser->visibleOwnedChannelsForViewer($viewerId);

		return [
			'user' => $profileUser,
			'channels' => $channels,
		];
	}

	public function updateProfile(int $id, ?int $viewerId, array $data): User
	{
		if ((int) $viewerId !== $id) {
			throw new AuthorizationException('Unauthorized');
		}

		$validator = Validator::make($data, [
			'description' => ['nullable', 'string'],
		]);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}

		$profileUser = User::query()->findOrFail($id);
		$profileUser->description = $data['description'] ?? null;
		$profileUser->save();

		return $profileUser;
	}
}
