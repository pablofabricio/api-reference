<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createChannelsSchema();
        $this->ensureUsersDescriptionColumn();
    }

    public function test_profile_requires_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/users/' . $user->id . '/profile');

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_profile_returns_all_owned_channels_for_own_profile(): void
    {
        $token = $this->authenticateAndGetToken('owner-profile@example.com');
        $ownerId = (int) User::where('email', 'owner-profile@example.com')->value('id');

        $publicId = $this->createChannel($ownerId, 'PUBLIC', 'Publico meu');
        $privateId = $this->createChannel($ownerId, 'PRIVATE', 'Privado meu');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/' . $ownerId . '/profile');

        $response->assertOk()
            ->assertJsonPath('data.user.id', $ownerId);

        $returnedIds = collect($response->json('data.channels'))->pluck('id')->all();
        $publicChannel = collect($response->json('data.channels'))->firstWhere('id', $publicId);
        $privateChannel = collect($response->json('data.channels'))->firstWhere('id', $privateId);

        $this->assertContains($publicId, $returnedIds);
        $this->assertContains($privateId, $returnedIds);
        $this->assertSame(0, $publicChannel['memberCount'] ?? null);
        $this->assertSame(0, $privateChannel['memberCount'] ?? null);
    }

    public function test_profile_of_other_user_returns_public_and_private_channels_where_viewer_is_member(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-vis@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $viewer = User::factory()->create([
            'email' => 'viewer-vis@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $publicId = $this->createChannel((int) $owner->id, 'PUBLIC', 'Publico dono');
        $privateMemberId = $this->createChannel((int) $owner->id, 'PRIVATE', 'Privado membro');
        $privateHiddenId = $this->createChannel((int) $owner->id, 'PRIVATE', 'Privado oculto');

        $this->createChannelMember($privateMemberId, (int) $viewer->id, 'MEMBER');

        $login = $this->postJson('/api/auth/login', [
            'email' => $viewer->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/' . $owner->id . '/profile');

        $response->assertOk()
            ->assertJsonPath('data.user.id', (int) $owner->id);

        $returnedIds = collect($response->json('data.channels'))->pluck('id')->all();
        $publicChannel = collect($response->json('data.channels'))->firstWhere('id', $publicId);
        $privateMemberChannel = collect($response->json('data.channels'))->firstWhere('id', $privateMemberId);

        $this->assertContains($publicId, $returnedIds);
        $this->assertContains($privateMemberId, $returnedIds);
        $this->assertNotContains($privateHiddenId, $returnedIds);
        $this->assertSame(0, $publicChannel['memberCount'] ?? null);
        $this->assertSame(1, $privateMemberChannel['memberCount'] ?? null);
    }

    public function test_profile_response_uses_resource_shape(): void
    {
        $token = $this->authenticateAndGetToken('resource-shape@example.com');
        $ownerId = (int) User::where('email', 'resource-shape@example.com')->value('id');
        $this->createChannel($ownerId, 'PUBLIC', 'Canal resource shape');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/' . $ownerId . '/profile');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'description', 'created_at', 'updated_at'],
                    'channels' => [
                        '*' => ['id', 'name', 'description', 'created_by', 'visibility', 'member_count', 'memberCount'],
                    ],
                ],
            ]);
    }

    public function test_update_profile_allows_owner_to_update_description(): void
    {
        $token = $this->authenticateAndGetToken('update-owner@example.com');
        $ownerId = (int) User::where('email', 'update-owner@example.com')->value('id');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $ownerId . '/profile', [
                'description' => 'Descricao atualizada no teste',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $ownerId)
            ->assertJsonPath('data.description', 'Descricao atualizada no teste');

        $this->assertDatabaseHas('users', [
            'id' => $ownerId,
            'description' => 'Descricao atualizada no teste',
        ]);
    }

    public function test_update_profile_forbids_updating_another_user_profile(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-forbidden@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $intruder = User::factory()->create([
            'email' => 'intruder-forbidden@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $intruder->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $owner->id . '/profile', [
                'description' => 'Nao deveria atualizar',
            ]);

        $response->assertStatus(403);
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

    private function createChannelsSchema(): void
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

        DB::statement('CREATE INDEX IF NOT EXISTS channels_created_by_idx ON channels (created_by)');
    }

    private function ensureUsersDescriptionColumn(): void
    {
        if (!Schema::hasColumn('users', 'description')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }
    }

    private function createChannel(int $createdBy, string $visibility, string $name): int
    {
        return (int) DB::table('channels')->insertGetId([
            'name' => $name,
            'created_by' => $createdBy,
            'visibility' => $visibility,
            'description' => 'Canal de teste',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createChannelMember(int $channelId, int $userId, string $role): void
    {
        DB::table('channel_members')->insert([
            'channel_id' => $channelId,
            'user_id' => $userId,
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
