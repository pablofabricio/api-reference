<?php

namespace Tests\Feature\Channels;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChannelControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createChannelsSchema();
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/channels', [
            'name' => 'Série em provérbios',
            'visibility' => 'PUBLIC',
            'description' => 'Canal de estudos',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_store_creates_channel_successfully(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'channels@example.com')->value('id');

        $payload = [
            'name' => 'Série em provérbios',
            'description' => 'Canal de estudos semanais',
            'created_by' => $userId,
            'visibility' => 'PUBLIC',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channels', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Série em provérbios')
            ->assertJsonPath('data.description', 'Canal de estudos semanais')
            ->assertJsonPath('data.created_by', $userId)
            ->assertJsonPath('data.visibility', 'PUBLIC');

        $this->assertDatabaseHas('channels', [
            'name' => 'Série em provérbios',
            'created_by' => $userId,
            'visibility' => 'PUBLIC',
        ]);
    }

    public function test_store_returns_validation_error_when_name_is_missing(): void
    {
        $token = $this->authenticateAndGetToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/channels', [
                'description' => 'Canal sem nome',
                'visibility' => 'PUBLIC',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation error')
            ->assertJsonStructure(['errors']);
    }

    public function test_index_returns_member_counts_for_user_channels(): void
    {
        $owner = User::factory()->create([
            'email' => 'channels-index@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $member = User::factory()->create([
            'email' => 'channels-index-member@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $this->createChannelMember($channelId, (int) $owner->id, 'OWNER');
        $this->createChannelMember($channelId, (int) $member->id, 'MEMBER');

        $login = $this->postJson('/api/auth/login', [
            'email' => $owner->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/channels');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $channelId,
                'member_count' => 2,
                'memberCount' => 2,
            ]);
    }

    public function test_destroy_allows_channel_creator_to_delete(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'channels@example.com')->value('id');
        $channelId = $this->createChannel($userId, 'PUBLIC');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/channels/' . $channelId);

        $response->assertNoContent();

        $this->assertDatabaseMissing('channels', [
            'id' => $channelId,
        ]);
    }

    public function test_destroy_rejects_moderator_deleting_channel(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-delete@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $moderator = User::factory()->create([
            'email' => 'moderator-delete@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $channelId = $this->createChannel((int) $owner->id, 'PUBLIC');
        $this->createChannelMember($channelId, (int) $moderator->id, 'MODERATOR');

        $response = $this->postJson('/api/auth/login', [
            'email' => $moderator->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $response->json('access_token');

        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/channels/' . $channelId);

        $deleteResponse->assertStatus(403);

        $this->assertDatabaseHas('channels', [
            'id' => $channelId,
        ]);
    }

    private function authenticateAndGetToken(): string
    {
        $user = User::factory()->create([
            'email' => 'channels@example.com',
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

    private function createChannel(int $createdBy, string $visibility): int
    {
        return (int) DB::table('channels')->insertGetId([
            'name' => 'Canal para exclusao',
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
