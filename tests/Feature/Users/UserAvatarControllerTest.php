<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class UserAvatarControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_upload_url_requires_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/users/' . $user->id . '/avatar/upload-url', [
            'file_name' => 'avatar.png',
            'content_type' => 'image/png',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_avatar_upload_url_forbids_other_user(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-avatar-url@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $intruderToken = $this->authenticateAndGetToken('intruder-avatar-url@example.com');

        $response = $this->withHeader('Authorization', 'Bearer ' . $intruderToken)
            ->postJson('/api/users/' . $owner->id . '/avatar/upload-url', [
                'file_name' => 'avatar.png',
                'content_type' => 'image/png',
            ]);

        $response->assertStatus(403);
    }

    public function test_avatar_upload_url_rejects_unsupported_content_type(): void
    {
        $token = $this->authenticateAndGetToken('owner-avatar-invalid-mime@example.com');
        $ownerId = (int) User::where('email', 'owner-avatar-invalid-mime@example.com')->value('id');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users/' . $ownerId . '/avatar/upload-url', [
                'file_name' => 'avatar.gif',
                'content_type' => 'image/gif',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['content_type']]);
    }

    public function test_avatar_upload_url_returns_signed_payload_for_owner(): void
    {
        $token = $this->authenticateAndGetToken('owner-avatar-upload-url@example.com');
        $ownerId = (int) User::where('email', 'owner-avatar-upload-url@example.com')->value('id');

        $diskMock = Mockery::mock();
        $generatedKey = null;

        $diskMock->shouldReceive('temporaryUploadUrl')
            ->once()
            ->withArgs(function ($key, $expiration, $options) use (&$generatedKey, $ownerId) {
                $generatedKey = $key;

                return is_string($key)
                    && str_starts_with($key, 'avatars/' . $ownerId . '/')
                    && $expiration instanceof \DateTimeInterface
                    && ($options['ContentType'] ?? null) === 'image/png';
            })
            ->andReturn([
                'url' => 'http://localhost:9000/reference-app/' . $ownerId . '/signed-upload?signature=test',
                'headers' => [
                    'Host' => ['localhost:9000'],
                    'x-amz-acl' => 'private',
                ],
            ]);

        $diskMock->shouldReceive('url')
            ->once()
            ->withArgs(function ($key) use (&$generatedKey) {
                return is_string($key) && $key === $generatedKey;
            })
            ->andReturnUsing(function ($key) {
                return 'http://minio.test/reference-app/' . $key;
            });

        Storage::shouldReceive('disk')
            ->times(2)
            ->with('s3')
            ->andReturn($diskMock);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users/' . $ownerId . '/avatar/upload-url', [
                'file_name' => 'avatar.png',
                'content_type' => 'image/png',
            ]);

        $response->assertOk()
            ->assertJsonPath('upload_url', 'http://localhost:9000/reference-app/' . $ownerId . '/signed-upload?signature=test')
            ->assertJsonPath('headers.x-amz-acl', 'private')
            ->assertJsonMissingPath('headers.Host');

        $key = (string) $response->json('key');
        $this->assertNotSame('', $key);
        $this->assertStringStartsWith('avatars/' . $ownerId . '/', $key);
        $this->assertSame('http://minio.test/reference-app/' . $key, (string) $response->json('public_url'));
    }

    public function test_update_avatar_forbids_other_user(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-avatar-forbidden@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $intruderToken = $this->authenticateAndGetToken('intruder-avatar-forbidden@example.com');

        $response = $this->withHeader('Authorization', 'Bearer ' . $intruderToken)
            ->putJson('/api/users/' . $owner->id . '/avatar', [
                'key' => 'avatars/' . $owner->id . '/image.png',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_avatar_rejects_key_outside_user_scope(): void
    {
        $token = $this->authenticateAndGetToken('owner-avatar-invalid-key@example.com');
        $ownerId = (int) User::where('email', 'owner-avatar-invalid-key@example.com')->value('id');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $ownerId . '/avatar', [
                'key' => 'avatars/999/wrong.png',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['key']]);
    }

    public function test_update_avatar_rejects_when_storage_object_is_missing(): void
    {
        $token = $this->authenticateAndGetToken('owner-avatar-missing@example.com');
        $ownerId = (int) User::where('email', 'owner-avatar-missing@example.com')->value('id');
        $key = 'avatars/' . $ownerId . '/missing.png';

        $diskMock = Mockery::mock();
        $diskMock->shouldReceive('exists')
            ->once()
            ->with($key)
            ->andReturn(false);

        Storage::shouldReceive('disk')
            ->once()
            ->with('s3_internal')
            ->andReturn($diskMock);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $ownerId . '/avatar', [
                'key' => $key,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['key']]);
    }

    public function test_update_avatar_persists_avatar_url_for_owner(): void
    {
        $token = $this->authenticateAndGetToken('owner-avatar-success@example.com');
        $ownerId = (int) User::where('email', 'owner-avatar-success@example.com')->value('id');
        $key = 'avatars/' . $ownerId . '/avatar.png';
        $url = 'http://minio.test/reference-app/' . $key;

        $internalDiskMock = Mockery::mock();
        $internalDiskMock->shouldReceive('exists')
            ->once()
            ->with($key)
            ->andReturn(true);

        $publicDiskMock = Mockery::mock();
        $publicDiskMock->shouldReceive('url')
            ->once()
            ->with($key)
            ->andReturn($url);

        Storage::shouldReceive('disk')
            ->once()
            ->with('s3_internal')
            ->andReturn($internalDiskMock);

        Storage::shouldReceive('disk')
            ->once()
            ->with('s3')
            ->andReturn($publicDiskMock);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $ownerId . '/avatar', [
                'key' => $key,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $ownerId)
            ->assertJsonPath('data.avatar_url', $url);

        $this->assertDatabaseHas('users', [
            'id' => $ownerId,
            'avatar_url' => $url,
        ]);
    }

    private function authenticateAndGetToken(string $email): string
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk();

        return (string) $response->json('access_token');
    }
}
