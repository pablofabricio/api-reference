<?php

namespace Tests\Feature\ChannelMembers;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChannelMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createChannelSchema();
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/channel-members', [
            'channel_id' => 1,
            'role' => 'MEMBER',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_store_follows_public_channel_and_adds_authenticated_user_as_member(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'member@example.com')->value('id');
        $channelId = $this->createChannel($userId, 'PUBLIC');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channel-members', [
                'channel_id' => $channelId,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.channel_id', $channelId)
            ->assertJsonPath('data.user_id', $userId)
            ->assertJsonPath('data.role', 'MEMBER');

        $this->assertDatabaseHas('channel_members', [
            'channel_id' => $channelId,
            'user_id' => $userId,
            'role' => 'MEMBER',
        ]);
    }

    public function test_store_rejects_following_private_channel(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'member@example.com')->value('id');
        $channelId = $this->createChannel($userId, 'PRIVATE');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channel-members', [
                'channel_id' => $channelId,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');
    }

    public function test_store_allows_following_public_channel_created_by_another_user(): void
    {
        $token = $this->authenticateAndGetToken();
        $authUserId = (int) User::where('email', 'member@example.com')->value('id');

        $channelOwner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $channelOwner->id, 'PUBLIC');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channel-members', [
                'channel_id' => $channelId,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.channel_id', $channelId)
            ->assertJsonPath('data.user_id', $authUserId)
            ->assertJsonPath('data.role', 'MEMBER');

        $this->assertDatabaseHas('channel_members', [
            'channel_id' => $channelId,
            'user_id' => $authUserId,
            'role' => 'MEMBER',
        ]);
    }

    public function test_store_returns_validation_error_for_invalid_payload(): void
    {
        $token = $this->authenticateAndGetToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channel-members', [
                'channel_id' => 999999,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation error')
            ->assertJsonStructure(['errors']);
    }

    public function test_store_rejects_creating_membership_for_another_user(): void
    {
        $token = $this->authenticateAndGetToken();
        $authUserId = (int) User::where('email', 'member@example.com')->value('id');
        $otherUser = User::factory()->create([
            'email' => 'other-member@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $channelId = $this->createChannel($authUserId, 'PUBLIC');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channel-members', [
                'channel_id' => $channelId,
                'user_id' => $otherUser->id,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');
    }

    private function authenticateAndGetToken(): string
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk();

        return (string) $response->json('access_token');
    }

    private function createChannel(int $createdBy, string $visibility): int
    {
        return (int) DB::table('channels')->insertGetId([
            'name' => 'Canal de membros',
            'created_by' => $createdBy,
            'visibility' => $visibility,
            'description' => 'Canal para teste de membros',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createChannelSchema(): void
    {
        if (!Schema::hasTable('channels')) {
            Schema::create('channels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('visibility', 20)->default('PRIVATE');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('channel_members')) {
            Schema::create('channel_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 20)->default('MEMBER');
                $table->timestamps();
                $table->unique(['channel_id', 'user_id']);
            });
        }
    }
}
