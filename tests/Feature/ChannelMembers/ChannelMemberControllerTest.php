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
        $otherUser = User::factory()->create([
            'email' => 'other-member@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $channelOwner = User::factory()->create([
            'email' => 'unauthorized-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $channelId = $this->createChannel((int) $channelOwner->id, 'PUBLIC');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channel-members', [
                'channel_id' => $channelId,
                'user_id' => $otherUser->id,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');
    }

    public function test_index_filters_members_by_channel_id(): void
    {
        $token = $this->authenticateAndGetToken();
        $owner = User::factory()->create([
            'email' => 'channel-filter-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $memberA = User::factory()->create([
            'email' => 'channel-filter-a@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $memberB = User::factory()->create([
            'email' => 'channel-filter-b@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $firstChannelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $secondChannelId = $this->createChannel((int) $owner->id, 'PUBLIC');

        $ownerFirstMembershipId = $this->createChannelMember($firstChannelId, (int) $owner->id, 'OWNER');
        $memberFirstMembershipId = $this->createChannelMember($firstChannelId, (int) $memberA->id, 'MEMBER');
        $this->createChannelMember($secondChannelId, (int) $memberB->id, 'MEMBER');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/channel-members?channel_id=' . $firstChannelId);

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.channel_id', $firstChannelId)
            ->assertJsonPath('data.1.channel_id', $firstChannelId);

        $returnedIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($ownerFirstMembershipId, $returnedIds);
        $this->assertContains($memberFirstMembershipId, $returnedIds);
    }

    public function test_owner_can_update_member_role(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-role@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $member = User::factory()->create([
            'email' => 'member-role@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $ownerMembershipId = $this->createChannelMember($channelId, (int) $owner->id, 'OWNER');
        $memberMembershipId = $this->createChannelMember($channelId, (int) $member->id, 'MEMBER');

        $login = $this->postJson('/api/auth/login', [
            'email' => $owner->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/channel-members/' . $memberMembershipId, [
                'role' => 'MODERATOR',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $memberMembershipId)
            ->assertJsonPath('data.role', 'MODERATOR');

        $this->assertDatabaseHas('channel_members', [
            'id' => $memberMembershipId,
            'role' => 'MODERATOR',
        ]);
        $this->assertDatabaseHas('channel_members', [
            'id' => $ownerMembershipId,
            'role' => 'OWNER',
        ]);
    }

    public function test_cannot_promote_member_to_owner(): void
    {
        $owner = User::factory()->create([
            'email' => 'promote-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $member = User::factory()->create([
            'email' => 'promote-member@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $this->createChannelMember($channelId, (int) $owner->id, 'OWNER');
        $memberMembershipId = $this->createChannelMember($channelId, (int) $member->id, 'MEMBER');

        $login = $this->postJson('/api/auth/login', [
            'email' => $owner->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/channel-members/' . $memberMembershipId, [
                'role' => 'OWNER',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');

        $this->assertDatabaseHas('channel_members', [
            'id' => $memberMembershipId,
            'role' => 'MEMBER',
        ]);
    }

    public function test_channel_owner_role_cannot_be_changed(): void
    {
        $owner = User::factory()->create([
            'email' => 'channel-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $secondOwner = User::factory()->create([
            'email' => 'second-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $ownerMembershipId = $this->createChannelMember($channelId, (int) $owner->id, 'OWNER');
        $this->createChannelMember($channelId, (int) $secondOwner->id, 'OWNER');

        $login = $this->postJson('/api/auth/login', [
            'email' => $secondOwner->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/channel-members/' . $ownerMembershipId, [
                'role' => 'MEMBER',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');

        $this->assertDatabaseHas('channel_members', [
            'id' => $ownerMembershipId,
            'role' => 'OWNER',
        ]);
    }

    public function test_non_owner_cannot_update_member_role(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-role-blocked@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $moderator = User::factory()->create([
            'email' => 'moderator-role-blocked@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $member = User::factory()->create([
            'email' => 'member-role-blocked@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $this->createChannelMember($channelId, (int) $owner->id, 'OWNER');
        $this->createChannelMember($channelId, (int) $moderator->id, 'MODERATOR');
        $memberMembershipId = $this->createChannelMember($channelId, (int) $member->id, 'MEMBER');

        $login = $this->postJson('/api/auth/login', [
            'email' => $moderator->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/channel-members/' . $memberMembershipId, [
                'role' => 'MODERATOR',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('channel_members', [
            'id' => $memberMembershipId,
            'role' => 'MEMBER',
        ]);
    }

    public function test_last_owner_cannot_be_demoted(): void
    {
        $owner = User::factory()->create([
            'email' => 'last-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $manager = User::factory()->create([
            'email' => 'last-owner-manager@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $ownerMembershipId = $this->createChannelMember($channelId, (int) $owner->id, 'OWNER');
        $this->createChannelMember($channelId, (int) $manager->id, 'OWNER');

        $login = $this->postJson('/api/auth/login', [
            'email' => $manager->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/channel-members/' . $ownerMembershipId, [
                'role' => 'MEMBER',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');

        $this->assertDatabaseHas('channel_members', [
            'id' => $ownerMembershipId,
            'role' => 'OWNER',
        ]);
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

    private function createChannelMember(int $channelId, int $userId, string $role): int
    {
        return (int) DB::table('channel_members')->insertGetId([
            'channel_id' => $channelId,
            'user_id' => $userId,
            'role' => $role,
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
