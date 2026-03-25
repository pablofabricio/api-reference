<?php

namespace Tests\Feature\ChannelJoinRequests;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChannelJoinRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createChannelSchema();
    }

    public function test_join_request_requires_authentication(): void
    {
        $owner = User::factory()->create();
        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');

        $this->postJson('/api/channels/' . $channelId . '/join')
            ->assertStatus(401);
    }

    public function test_join_request_creates_pending_request_for_public_channel(): void
    {
        $owner = User::factory()->create([
            'email' => 'join-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $requester = User::factory()->create([
            'email' => 'join-requester@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');

        $token = $this->login($requester->email);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channels/' . $channelId . '/join');

        $response->assertStatus(201)
            ->assertJsonPath('data.channel_id', $channelId)
            ->assertJsonPath('data.requester_id', (int) $requester->id)
            ->assertJsonPath('data.status', 'PENDING');

        $this->assertDatabaseHas('channel_join_requests', [
            'channel_id' => $channelId,
            'requester_id' => (int) $requester->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_join_request_rejects_private_channel(): void
    {
        $owner = User::factory()->create([
            'email' => 'private-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $requester = User::factory()->create([
            'email' => 'private-requester@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $channelId = $this->createChannel((int) $owner->id, 'PRIVATE');

        $token = $this->login($requester->email);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channels/' . $channelId . '/join')
            ->assertStatus(403);
    }

    public function test_index_returns_requests_related_to_authenticated_user(): void
    {
        $owner = User::factory()->create([
            'email' => 'list-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $requester = User::factory()->create([
            'email' => 'list-requester@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $otherRequester = User::factory()->create([
            'email' => 'list-other-requester@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $managedChannelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $foreignChannelId = $this->createChannel((int) $otherRequester->id, 'PUBLIC');

        $managedRequestId = $this->createJoinRequest($managedChannelId, (int) $requester->id, 'PENDING');
        $ownOutgoingRequestId = $this->createJoinRequest($foreignChannelId, (int) $owner->id, 'PENDING');
        $this->createJoinRequest($foreignChannelId, (int) $requester->id, 'PENDING');

        $token = $this->login($owner->email);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/channel-join-requests?status=PENDING');

        $response->assertOk()->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($managedRequestId, $ids);
        $this->assertContains($ownOutgoingRequestId, $ids);
    }

    public function test_manager_can_approve_request(): void
    {
        $owner = User::factory()->create([
            'email' => 'approve-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $requester = User::factory()->create([
            'email' => 'approve-requester@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $requestId = $this->createJoinRequest($channelId, (int) $requester->id, 'PENDING');

        $token = $this->login($owner->email);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/channel-join-requests/' . $requestId, [
                'status' => 'APPROVED',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'APPROVED')
            ->assertJsonPath('data.reviewed_by', (int) $owner->id);

        $this->assertDatabaseHas('channel_join_requests', [
            'id' => $requestId,
            'status' => 'APPROVED',
            'reviewed_by' => (int) $owner->id,
        ]);
        $this->assertDatabaseHas('channel_members', [
            'channel_id' => $channelId,
            'user_id' => (int) $requester->id,
            'role' => 'MEMBER',
        ]);
    }

    public function test_non_manager_cannot_review_request(): void
    {
        $owner = User::factory()->create([
            'email' => 'review-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $requester = User::factory()->create([
            'email' => 'review-requester@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $outsider = User::factory()->create([
            'email' => 'review-outsider@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $requestId = $this->createJoinRequest($channelId, (int) $requester->id, 'PENDING');

        $token = $this->login($outsider->email);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/channel-join-requests/' . $requestId, [
                'status' => 'REJECTED',
            ])
            ->assertStatus(403);

        $this->assertDatabaseHas('channel_join_requests', [
            'id' => $requestId,
            'status' => 'PENDING',
        ]);
    }

    public function test_requester_can_cancel_pending_request(): void
    {
        $owner = User::factory()->create([
            'email' => 'cancel-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $requester = User::factory()->create([
            'email' => 'cancel-requester@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $requestId = $this->createJoinRequest($channelId, (int) $requester->id, 'PENDING');

        $token = $this->login($requester->email);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/channel-join-requests/' . $requestId)
            ->assertNoContent();

        $this->assertDatabaseMissing('channel_join_requests', [
            'id' => $requestId,
        ]);
    }

    private function login(string $email): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => '12345678',
        ])->assertOk();

        return (string) $response->json('access_token');
    }

    private function createChannel(int $createdBy, string $visibility): int
    {
        return (int) DB::table('channels')->insertGetId([
            'name' => 'Canal de solicitacoes',
            'created_by' => $createdBy,
            'visibility' => $visibility,
            'description' => 'Canal para teste de solicitacoes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createJoinRequest(int $channelId, int $requesterId, string $status): int
    {
        return (int) DB::table('channel_join_requests')->insertGetId([
            'channel_id' => $channelId,
            'requester_id' => $requesterId,
            'status' => $status,
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

        if (!Schema::hasTable('channel_join_requests')) {
            Schema::create('channel_join_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('requester_id');
                $table->string('status', 20)->default('PENDING');
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }
}