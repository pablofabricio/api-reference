<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

	public function generateAvatarUploadUrl(int $id, ?int $viewerId, array $data): array
	{
		if ((int) $viewerId !== $id) {
			throw new AuthorizationException('Unauthorized');
		}

		$validator = Validator::make($data, [
			'file_name' => ['required', 'string', 'max:255'],
			'content_type' => ['required', 'string', 'max:100'],
		]);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}

		$fileName = (string) $data['file_name'];
		$contentType = (string) $data['content_type'];

		$allowed = ['image/jpeg', 'image/png', 'image/webp'];
		if (! in_array(strtolower($contentType), $allowed, true)) {
			throw ValidationException::withMessages([
				'content_type' => ['Formato de arquivo nao suportado. Use JPG, PNG ou WEBP.'],
			]);
		}

		$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
		if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
			$extension = $this->extensionFromMime($contentType);
		}

		$key = sprintf('avatars/%d/%s.%s', $id, Str::uuid()->toString(), $extension);

		$upload = Storage::disk('s3')->temporaryUploadUrl(
			$key,
			now()->addMinutes(10),
			['ContentType' => $contentType]
		);

		$publicUrl = Storage::disk('s3')->url($key);
		$headers = $this->sanitizeUploadHeaders($upload['headers'] ?? []);

		return [
			'key' => $key,
			'upload_url' => $upload['url'],
			'headers' => $headers,
			'public_url' => $publicUrl,
		];
	}

	public function updateAvatar(int $id, ?int $viewerId, array $data): User
	{
		if ((int) $viewerId !== $id) {
			throw new AuthorizationException('Unauthorized');
		}

		$validator = Validator::make($data, [
			'key' => ['required', 'string', 'max:255'],
		]);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}

		$key = (string) $data['key'];
		if (! str_starts_with($key, "avatars/{$id}/")) {
			throw ValidationException::withMessages([
				'key' => ['Caminho de avatar invalido.'],
			]);
		}

		if (! Storage::disk('s3_internal')->exists($key)) {
			throw ValidationException::withMessages([
				'key' => ['Arquivo de avatar nao encontrado no storage.'],
			]);
		}

		$profileUser = User::query()->findOrFail($id);
		$profileUser->avatar_url = Storage::disk('s3')->url($key);
		$profileUser->save();

		return $profileUser;
	}

	private function extensionFromMime(string $contentType): string
	{
		return match (strtolower($contentType)) {
			'image/png' => 'png',
			'image/webp' => 'webp',
			default => 'jpg',
		};
	}

	private function sanitizeUploadHeaders(array $headers): array
	{
		$sanitized = [];

		foreach ($headers as $name => $value) {
			if (strtolower((string) $name) === 'host') {
				continue;
			}

			if (is_array($value)) {
				$value = $value[0] ?? null;
			}

			if ($value === null || $value === '') {
				continue;
			}

			$sanitized[(string) $name] = (string) $value;
		}

		return $sanitized;
	}
}
