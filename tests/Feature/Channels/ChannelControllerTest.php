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

        DB::statement('CREATE INDEX IF NOT EXISTS channels_created_by_idx ON channels (created_by)');
    }
}
